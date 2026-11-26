<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/duitku_client.php';
require_once __DIR__ . '/../config/payment_status.php';
if (($_SESSION['user']['role'] ?? '') !== 'pelanggan') {
    redirect('login.php');
}
$idTransaksi = (int) ($_GET['id'] ?? 0);
$idPelanggan = (int) ($_SESSION['user']['id_pelanggan'] ?? 0);
$stmt = $conn->prepare("SELECT payment_order_id, total_belanja, payment_status FROM tbl_transaksi WHERE id_transaksi = ? AND id_pelanggan = ? AND payment_method = 'duitku' LIMIT 1");
$stmt->bind_param('ii', $idTransaksi, $idPelanggan);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Return URL berasal dari browser pelanggan; status tetap dicek ke API Duitku, bukan dari parameter URL.
if ($order && $order['payment_status'] === 'pending' && duitkuReady()) {
    try {
        $result = duitkuVerifyOrder((string) $order['payment_order_id'], (int) round((float) $order['total_belanja']));
        if (($result['statusCode'] ?? '') === '00' && (int) ($result['amount'] ?? 0) === (int) round((float) $order['total_belanja'])) {
            applyPaymentStatus($conn, (string) $order['payment_order_id'], 'paid');
            $order['payment_status'] = 'paid';
        }
    } catch (Throwable $exception) {
        // Callback tetap dapat memperbarui status jika inquiry sementara gagal.
    }
}

if ($order && $order['payment_status'] === 'paid') {
    $_SESSION['payment_success_notice'] = $idTransaksi;
    redirect('pelanggan/detail_transaksi.php?id=' . $idTransaksi);
}
if ($order && $order['payment_status'] === 'pending') {
    redirect('pelanggan/pembayaran.php?id=' . $idTransaksi);
}
redirect('pelanggan/beranda.php');
