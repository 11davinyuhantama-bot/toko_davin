<?php
require_once __DIR__ . '/helpers.php';

function duitkuConfig(): array
{
    static $config;
    if ($config === null) {
        $config = require __DIR__ . '/duitku.php';
    }
    return $config;
}

function duitkuReady(): bool
{
    $config = duitkuConfig();
    return !empty($config['merchant_code'])
        && !empty($config['api_key'])
        && !empty($config['base_url'])
        && !str_contains((string) $config['merchant_code'], 'ISI_')
        && !str_contains((string) $config['base_url'], 'example.com');
}

function duitkuPost(string $path, array $payload): array
{
    $config = duitkuConfig();
    if (!function_exists('curl_init')) {
        throw new RuntimeException('Ekstensi cURL PHP belum aktif.');
    }
    $base = ($config['mode'] ?? 'sandbox') === 'production'
        ? 'https://passport.duitku.com/webapi/api/merchant/'
        : 'https://sandbox.duitku.com/webapi/api/merchant/';
    $curl = curl_init($base . $path);
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 25,
    ]);
    $body = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    $response = is_string($body) ? json_decode($body, true) : null;
    if ($status < 200 || $status >= 300 || !is_array($response)) {
        $message = is_array($response)
            ? (string) ($response['Message'] ?? $response['message'] ?? $response['statusMessage'] ?? '')
            : '';
        throw new RuntimeException('Layanan Duitku menolak permintaan (HTTP ' . $status . '). ' . ($message !== '' ? $message : ($error ?: 'Periksa konfigurasi dan format permintaan.')));
    }
    return $response;
}

function duitkuCreatePayment(array $order, string $method): array
{
    $config = duitkuConfig();
    $merchantCode = (string) $config['merchant_code'];
    $orderId = (string) $order['payment_order_id'];
    $amount = (int) round((float) $order['total_belanja']);
    $sign = hash_hmac('sha256', $merchantCode . $orderId . $amount, (string) $config['api_key']);
    $baseUrl = rtrim((string) $config['base_url'], '/');
    $payload = [
        'merchantCode' => $merchantCode,
        'paymentAmount' => $amount,
        'paymentMethod' => $method,
        'merchantOrderId' => $orderId,
        'productDetails' => 'Pesanan Tokoku #' . (int) $order['id_transaksi'],
        'customerVaName' => substr((string) ($order['nama'] ?? 'Pelanggan Tokoku'), 0, 20),
        'email' => (string) ($order['email'] ?? 'pelanggan@tokoku.local'),
        'callbackUrl' => $baseUrl . url('payment/duitku_callback.php'),
        'returnUrl' => $baseUrl . url('payment/duitku_return.php?id=' . (int) $order['id_transaksi']),
        'expiryPeriod' => 60,
        'signature' => $sign,
    ];
    $response = duitkuPost('v2/inquiry', $payload);
    if (($response['statusCode'] ?? '') !== '00' || empty($response['paymentUrl'])) {
        throw new RuntimeException('Duitku menolak permintaan pembayaran: ' . ($response['statusMessage'] ?? 'respons tidak valid'));
    }
    return $response;
}

function duitkuGetPaymentMethods(int $amount): array
{
    $config = duitkuConfig();
    $merchantCode = (string) $config['merchant_code'];
    $datetime = date('Y-m-d H:i:s');
    $signature = hash_hmac('sha256', $merchantCode . $amount . $datetime, (string) $config['api_key']);
    $response = duitkuPost('paymentmethod/getpaymentmethod', [
        'merchantcode' => $merchantCode,
        'amount' => $amount,
        'datetime' => $datetime,
        'signature' => $signature,
    ]);
    if (($response['responseCode'] ?? '') !== '00' || !is_array($response['paymentFee'] ?? null)) {
        throw new RuntimeException('Daftar kanal pembayaran Duitku tidak tersedia: ' . ($response['responseMessage'] ?? 'respons tidak valid'));
    }
    return $response['paymentFee'];
}

function duitkuVerifyOrder(string $merchantOrderId, int $amount): array
{
    $config = duitkuConfig();
    $merchantCode = (string) $config['merchant_code'];
    return duitkuPost('transactionStatus', [
        'merchantCode' => $merchantCode,
        'merchantOrderId' => $merchantOrderId,
        'signature' => hash_hmac('sha256', $merchantCode . $merchantOrderId, (string) $config['api_key']),
    ]);
}
