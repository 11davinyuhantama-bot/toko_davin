<?php
require_once __DIR__ . '/../config/database.php';
if (($_SESSION['user']['role'] ?? '') !== 'admin') redirect('login.php');
$id = (int) ($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT payment_proof FROM tbl_transaksi WHERE id_transaksi = ? AND payment_status = 'verification' LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$filename = basename((string) ($row['payment_proof'] ?? ''));
if (!$filename || !preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $filename)) {
    http_response_code(404);
    exit('Bukti pembayaran tidak ditemukan.');
}
$path = __DIR__ . '/../storage/payment_proofs/' . $filename;
if (!is_file($path)) {
    http_response_code(404);
    exit('Bukti pembayaran tidak ditemukan.');
}
$mime = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'][pathinfo($filename, PATHINFO_EXTENSION)];
header('Content-Type: ' . $mime);
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
readfile($path);
