<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/payment_status.php';
if (($_SESSION['user']['role'] ?? '') !== 'admin') redirect('login.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf'] ?? '', 'csrf_payment_review')) {
    set_flash('danger', 'Permintaan verifikasi tidak valid.');
    redirect('transaksi/pembayaran_pending.php');
}
$id = (int) ($_POST['id_transaksi'] ?? 0);
$aksi = $_POST['aksi'] ?? '';
$stmt = $conn->prepare("SELECT payment_order_id, payment_status FROM tbl_transaksi WHERE id_transaksi = ? AND payment_status IN ('pending', 'verification') LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$order) {
    set_flash('warning', 'Pesanan tidak ditemukan atau sudah diproses.');
    redirect('transaksi/pembayaran_pending.php');
}

try {
    if ($aksi === 'approve' && $order['payment_status'] === 'verification') {
        $update = $conn->prepare("UPDATE tbl_transaksi SET payment_status = 'paid' WHERE id_transaksi = ? AND payment_status = 'verification'");
        $update->bind_param('i', $id);
        $update->execute();
        $changed = $update->affected_rows === 1;
        $update->close();
        set_flash($changed ? 'success' : 'warning', $changed ? 'Pembayaran ditandai lunas.' : 'Status pesanan berubah.');
    } elseif ($aksi === 'reject') {
        applyPaymentStatus($conn, (string) $order['payment_order_id'], 'failed');
        set_flash('info', 'Bukti ditolak dan stok pesanan dikembalikan.');
    } else {
        set_flash('warning', 'Tindakan tidak dikenal.');
    }
} catch (Throwable $exception) {
    set_flash('danger', 'Status pembayaran gagal diperbarui.');
}
redirect('transaksi/pembayaran_pending.php');
