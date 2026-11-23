<?php
require_once __DIR__ . '/../config/database.php';
if (($_SESSION['user']['role'] ?? '') !== 'pelanggan') redirect('login.php');

$idTransaksi = (int) ($_GET['id'] ?? 0);
$idPelanggan = (int) ($_SESSION['user']['id_pelanggan'] ?? 0);
$stmt = $conn->prepare('SELECT id_transaksi, total_belanja, payment_status, payment_proof FROM tbl_transaksi WHERE id_transaksi = ? AND id_pelanggan = ? LIMIT 1');
$stmt->bind_param('ii', $idTransaksi, $idPelanggan);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$payment) {
    set_flash('warning', 'Pesanan tidak ditemukan di akun Anda.');
    redirect('pelanggan/beranda.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf'] ?? '', 'csrf_pelanggan')) {
        $error = 'Sesi formulir tidak valid. Muat ulang halaman dan coba kembali.';
    } elseif ($payment['payment_status'] !== 'pending') {
        $error = 'Pesanan ini tidak lagi menunggu bukti pembayaran.';
    } elseif (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Pilih gambar bukti pembayaran terlebih dahulu.';
    } elseif ($_FILES['bukti']['size'] > 5 * 1024 * 1024) {
        $error = 'Ukuran bukti pembayaran maksimal 5 MB.';
    } else {
        $tmp = $_FILES['bukti']['tmp_name'];
        $imageInfo = @getimagesize($tmp);
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!$imageInfo || !isset($extensions[$mime])) {
            $error = 'Bukti harus berupa gambar JPG, PNG, atau WebP.';
        } else {
            $folder = __DIR__ . '/../storage/payment_proofs';
            if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
                $error = 'Folder penyimpanan bukti pembayaran tidak dapat dibuat.';
            } else {
                $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
                if (!move_uploaded_file($tmp, $folder . '/' . $filename)) {
                    $error = 'Bukti pembayaran gagal disimpan.';
                } else {
                    $proofPath = 'payment_proofs/' . $filename;
                    $update = $conn->prepare("UPDATE tbl_transaksi SET payment_status = 'verification', payment_proof = ? WHERE id_transaksi = ? AND id_pelanggan = ? AND payment_status = 'pending'");
                    $update->bind_param('sii', $proofPath, $idTransaksi, $idPelanggan);
                    $update->execute();
                    $changed = $update->affected_rows === 1;
                    $update->close();
                    if ($changed) {
                        redirect('pelanggan/pembayaran.php?id=' . $idTransaksi);
                    }
                    @unlink($folder . '/' . $filename);
                    $error = 'Status pesanan berubah. Muat ulang halaman.';
                }
            }
        }
    }
    $stmt = $conn->prepare('SELECT id_transaksi, total_belanja, payment_status, payment_proof FROM tbl_transaksi WHERE id_transaksi = ? AND id_pelanggan = ? LIMIT 1');
    $stmt->bind_param('ii', $idTransaksi, $idPelanggan);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
$labels = ['pending' => 'Menunggu pembayaran', 'verification' => 'Menunggu verifikasi admin', 'paid' => 'Pembayaran berhasil', 'expired' => 'Pembayaran kedaluwarsa', 'cancelled' => 'Pembayaran dibatalkan', 'failed' => 'Pembayaran gagal'];
$label = $labels[$payment['payment_status']] ?? 'Status pembayaran';
?>
<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Pembayaran Pesanan #<?= (int) $payment['id_transaksi'] ?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"></head>
<body class="bg-light"><main class="container py-5" style="max-width:640px"><div class="card border-0 shadow-sm"><div class="card-body p-4 p-md-5 text-center">
    <div class="text-primary display-5 mb-3"><i class="bi bi-credit-card"></i></div>
    <h1 class="h3 fw-bold">Pesanan #<?= (int) $payment['id_transaksi'] ?></h1>
    <p class="text-secondary">Total pembayaran <strong class="text-dark"><?= rupiah($payment['total_belanja']) ?></strong></p>
    <div class="alert <?= $payment['payment_status'] === 'paid' ? 'alert-success' : ($payment['payment_status'] === 'pending' ? 'alert-info' : 'alert-warning') ?>"><?= e($label) ?></div>
    <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <?php if ($payment['payment_status'] === 'pending'): ?>
        <p class="text-secondary">Pindai QRIS merchant dengan aplikasi bank atau e-wallet Anda, lalu masukkan nominal persis seperti total pesanan.</p>
        <img src="<?= url('assets/img/qris_merchant.jpg') ?>" alt="QRIS pembayaran toko" class="img-fluid border rounded mb-3" style="max-width:420px">
        <form method="post" enctype="multipart/form-data" class="text-start">
            <input type="hidden" name="csrf" value="<?= e(csrf_token('csrf_pelanggan')) ?>">
            <label class="form-label" for="bukti">Unggah bukti pembayaran (JPG, PNG, WebP; maks. 5 MB)</label>
            <input class="form-control mb-3" type="file" id="bukti" name="bukti" accept="image/jpeg,image/png,image/webp" required>
            <button class="btn btn-primary btn-lg w-100" type="submit">Kirim bukti pembayaran</button>
        </form>
    <?php elseif ($payment['payment_status'] === 'verification'): ?>
        <p class="text-secondary">Bukti pembayaran sudah dikirim. Admin akan mencocokkan dana masuk sebelum menandai pesanan lunas.</p>
        <div class="small text-secondary">Bukti pembayaran tersimpan dan hanya dapat dilihat oleh admin.</div>
    <?php endif; ?>
    <a class="d-block mt-3" href="beranda.php">Kembali ke toko</a>
</div></div></main></body></html>
