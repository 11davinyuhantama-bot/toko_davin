<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/duitku_client.php';
require_once __DIR__ . '/../config/payment_status.php';

http_response_code(400);
$config = duitkuConfig();
$merchantCode = (string) ($_POST['merchantCode'] ?? '');
$amount = (int) ($_POST['amount'] ?? 0);
$orderId = (string) ($_POST['merchantOrderId'] ?? '');
$signature = (string) ($_POST['signature'] ?? '');
if (!duitkuReady() || $merchantCode !== (string) $config['merchant_code'] || $amount < 1 || $orderId === '') {
    exit('Invalid callback');
}
$expected = hash_hmac('sha256', $merchantCode . $amount . $orderId, (string) $config['api_key']);
if (!hash_equals($expected, $signature)) {
    exit('Invalid signature');
}

$stmt = $conn->prepare('SELECT id_transaksi, total_belanja FROM tbl_transaksi WHERE payment_order_id = ? LIMIT 1');
$stmt->bind_param('s', $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$order || (int) round((float) $order['total_belanja']) !== $amount) {
    exit('Order mismatch');
}

try {
    // Callback payload alone does not mark an order paid; confirm against Duitku status API.
    $verified = duitkuVerifyOrder($orderId, $amount);
    if ((string) ($verified['amount'] ?? '') !== (string) $amount) {
        exit('Amount mismatch');
    }
    if (($verified['statusCode'] ?? '') === '00') {
        applyPaymentStatus($conn, $orderId, 'paid');
    } elseif (($verified['statusCode'] ?? '') === '02') {
        applyPaymentStatus($conn, $orderId, 'failed');
    }
    http_response_code(200);
    echo 'SUCCESS';
} catch (Throwable $exception) {
    http_response_code(500);
    echo 'RETRY';
}
