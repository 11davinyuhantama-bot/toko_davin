<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/duitku_client.php';
require_once __DIR__ . '/../config/payment_status.php';
if (($_SESSION['user']['role'] ?? '') !== 'pelanggan') redirect('login.php');

$idTransaksi = (int) ($_GET['id'] ?? 0);
$idPelanggan = (int) ($_SESSION['user']['id_pelanggan'] ?? 0);
$stmt = $conn->prepare('SELECT id_transaksi, total_belanja, payment_status, payment_proof, payment_method, payment_url, payment_reference, payment_order_id, payment_channel, payment_va_number, payment_qr_string, payment_app_url FROM tbl_transaksi WHERE id_transaksi = ? AND id_pelanggan = ? LIMIT 1');
$stmt->bind_param('ii', $idTransaksi, $idPelanggan);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$payment) {
    set_flash('warning', 'Pesanan tidak ditemukan di akun Anda.');
    redirect('pelanggan/beranda.php');
}
$inquiryKey = 'duitku_inquiry_' . $idTransaksi;
if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    && $payment['payment_method'] === 'duitku'
    && $payment['payment_status'] === 'pending'
    && !empty($payment['payment_url'])
    && duitkuReady()
    && (time() - (int) ($_SESSION[$inquiryKey] ?? 0) >= 30)) {
    $_SESSION[$inquiryKey] = time();
    try {
        $statusResult = duitkuVerifyOrder((string) $payment['payment_order_id'], (int) round((float) $payment['total_belanja']));
        if (($statusResult['statusCode'] ?? '') === '00' && (int) ($statusResult['amount'] ?? 0) === (int) round((float) $payment['total_belanja'])) {
            applyPaymentStatus($conn, (string) $payment['payment_order_id'], 'paid');
            $_SESSION['payment_success_notice'] = $idTransaksi;
            redirect('pelanggan/detail_transaksi.php?id=' . $idTransaksi);
        } elseif (($statusResult['statusCode'] ?? '') === '02') {
            applyPaymentStatus($conn, (string) $payment['payment_order_id'], 'failed');
        }
        $stmt = $conn->prepare('SELECT id_transaksi, total_belanja, payment_status, payment_proof, payment_method, payment_url, payment_reference, payment_order_id, payment_channel, payment_va_number, payment_qr_string, payment_app_url FROM tbl_transaksi WHERE id_transaksi = ? AND id_pelanggan = ? LIMIT 1');
        $stmt->bind_param('ii', $idTransaksi, $idPelanggan);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } catch (Throwable $exception) {
        // Status inquiry is best effort; Duitku's callback can still update the order later.
    }
}
$error = '';
$duitkuMethods = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf'] ?? '', 'csrf_pelanggan')) {
        $error = 'Sesi formulir tidak valid. Muat ulang halaman dan coba kembali.';
    } elseif ($payment['payment_status'] !== 'pending') {
        $error = 'Pesanan ini tidak lagi menunggu bukti pembayaran.';
    } elseif (($payment['payment_method'] ?? '') === 'duitku' && ($_POST['aksi'] ?? '') === 'check_status') {
        try {
            $statusResult = duitkuVerifyOrder((string) $payment['payment_order_id'], (int) round((float) $payment['total_belanja']));
            if (($statusResult['statusCode'] ?? '') === '00' && (int) ($statusResult['amount'] ?? 0) === (int) round((float) $payment['total_belanja'])) {
                applyPaymentStatus($conn, (string) $payment['payment_order_id'], 'paid');
                $_SESSION['payment_success_notice'] = $idTransaksi;
                redirect('pelanggan/detail_transaksi.php?id=' . $idTransaksi);
            }
            if (($statusResult['statusCode'] ?? '') === '02') {
                applyPaymentStatus($conn, (string) $payment['payment_order_id'], 'failed');
                set_flash('warning', 'Pembayaran gagal atau kedaluwarsa. Pesanan dibatalkan dan stok dikembalikan.');
                redirect('pelanggan/beranda.php');
            }
            set_flash('info', 'Duitku belum mengonfirmasi pembayaran. Coba cek lagi beberapa saat.');
            redirect('pelanggan/pembayaran.php?id=' . $idTransaksi);
        } catch (Throwable $exception) {
            set_flash('warning', 'Status pembayaran belum dapat diperiksa. Coba lagi beberapa saat.');
            redirect('pelanggan/pembayaran.php?id=' . $idTransaksi);
        }
    } elseif (($payment['payment_method'] ?? '') === 'duitku') {
        $channel = trim((string) ($_POST['duitku_channel'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Masukkan alamat email yang valid.';
        } elseif ($channel === '') {
            $error = 'Pilih kanal pembayaran Duitku.';
        } else {
            try {
                $duitkuMethods = duitkuGetPaymentMethods((int) round((float) $payment['total_belanja']));
                $activeChannel = null;
                foreach ($duitkuMethods as $method) {
                    if (($method['paymentMethod'] ?? '') === $channel) {
                        $activeChannel = $method;
                        break;
                    }
                }
                if (!$activeChannel) {
                    throw new RuntimeException('Kanal pembayaran tersebut tidak aktif. Muat ulang halaman dan pilih kanal lain.');
                }
                $newOrderId = 'TOKO-' . (int) $payment['id_transaksi'] . '-' . date('ymdHis') . strtoupper(bin2hex(random_bytes(3)));
                $rotate = $conn->prepare("UPDATE tbl_transaksi SET payment_order_id = ?, payment_reference = NULL, payment_url = NULL, payment_va_number = NULL, payment_qr_string = NULL, payment_app_url = NULL WHERE id_transaksi = ? AND id_pelanggan = ? AND payment_status = 'pending'");
                $rotate->bind_param('sii', $newOrderId, $idTransaksi, $idPelanggan);
                $rotate->execute();
                $rotated = $rotate->affected_rows === 1;
                $rotate->close();
                if (!$rotated) {
                    throw new RuntimeException('Status pesanan berubah. Muat ulang halaman.');
                }
                $duitkuResponse = duitkuCreatePayment([
                    'id_transaksi' => (int) $payment['id_transaksi'],
                    'payment_order_id' => $newOrderId,
                    'total_belanja' => (float) $payment['total_belanja'],
                    'nama' => (string) ($_SESSION['user']['nama'] ?? 'Pelanggan Tokoku'),
                    'email' => $email,
                ], $channel);
                $reference = (string) ($duitkuResponse['reference'] ?? '');
                $paymentUrl = (string) $duitkuResponse['paymentUrl'];
                $paymentChannel = (string) ($activeChannel['paymentName'] ?? $channel);
                $vaNumber = (string) ($duitkuResponse['vaNumber'] ?? '');
                $qrString = (string) ($duitkuResponse['qrString'] ?? '');
                $appUrl = (string) ($duitkuResponse['appUrl'] ?? $duitkuResponse['AppUrl'] ?? '');
                $update = $conn->prepare("UPDATE tbl_transaksi SET payment_reference = ?, payment_url = ?, payment_channel = ?, payment_va_number = ?, payment_qr_string = ?, payment_app_url = ? WHERE id_transaksi = ? AND id_pelanggan = ? AND payment_status = 'pending'");
                $update->bind_param('ssssssii', $reference, $paymentUrl, $paymentChannel, $vaNumber, $qrString, $appUrl, $idTransaksi, $idPelanggan);
                $update->execute();
                $updated = $update->affected_rows === 1;
                $update->close();
                if (!$updated) {
                    throw new RuntimeException('Status pesanan berubah. Muat ulang halaman.');
                }
                redirect('pelanggan/pembayaran.php?id=' . $idTransaksi);
            } catch (Throwable $exception) {
                $error = $exception->getMessage();
            }
        }
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
    $stmt = $conn->prepare('SELECT id_transaksi, total_belanja, payment_status, payment_proof, payment_method, payment_url, payment_reference, payment_order_id, payment_channel, payment_va_number, payment_qr_string, payment_app_url FROM tbl_transaksi WHERE id_transaksi = ? AND id_pelanggan = ? LIMIT 1');
    $stmt->bind_param('ii', $idTransaksi, $idPelanggan);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
if (($payment['payment_method'] ?? '') === 'duitku' && $payment['payment_status'] === 'pending' && !$duitkuMethods && duitkuReady()) {
    try {
        $duitkuMethods = duitkuGetPaymentMethods((int) round((float) $payment['total_belanja']));
    } catch (Throwable $exception) {
        if ($error === '') $error = $exception->getMessage();
    }
}
$labels = ['pending' => 'Menunggu pembayaran', 'verification' => 'Menunggu verifikasi admin', 'paid' => 'Pembayaran berhasil', 'expired' => 'Pembayaran kedaluwarsa', 'cancelled' => 'Pembayaran dibatalkan', 'failed' => 'Pembayaran gagal'];
$label = $labels[$payment['payment_status']] ?? 'Status pembayaran';
?>
<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Pembayaran Pesanan #<?= (int) $payment['id_transaksi'] ?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"></head>
<body class="bg-light"><main class="container py-5" style="max-width:640px"><div class="card border-0 shadow-sm"><div class="card-body p-4 p-md-5 text-center">
    <?php show_flash(); ?>
    <div class="text-primary display-5 mb-3"><i class="bi bi-credit-card"></i></div>
    <h1 class="h3 fw-bold">Pesanan #<?= (int) $payment['id_transaksi'] ?></h1>
    <p class="text-secondary">Total pembayaran <strong class="text-dark"><?= rupiah($payment['total_belanja']) ?></strong></p>
    <div class="alert <?= $payment['payment_status'] === 'paid' ? 'alert-success' : ($payment['payment_status'] === 'pending' ? 'alert-info' : 'alert-warning') ?>"><?= e($label) ?></div>
    <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <?php if ($payment['payment_status'] === 'pending'): ?>
        <?php if (($payment['payment_method'] ?? '') === 'duitku'): ?>
            <?php if (!empty($payment['payment_channel'])): ?><div class="rounded-3 bg-primary-subtle text-primary p-3 mb-3"><div class="small">Kanal pembayaran</div><strong><?= e($payment['payment_channel']) ?></strong></div><?php endif; ?>
            <?php if (!empty($payment['payment_va_number'])): ?><div class="border rounded-3 p-3 mb-3 text-start"><div class="small text-secondary mb-1">Nomor Virtual Account</div><div class="d-flex align-items-center justify-content-between gap-2"><strong class="fs-5" id="vaNumber"><?= e($payment['payment_va_number']) ?></strong><button class="btn btn-sm btn-outline-primary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('vaNumber').textContent)">Salin</button></div><div class="small text-secondary mt-2">Bayar sesuai nominal pesanan melalui aplikasi atau ATM bank yang dipilih.</div></div><?php endif; ?>
            <?php if (!empty($payment['payment_qr_string'])): ?><div class="border rounded-3 p-3 mb-3"><div class="small text-secondary mb-2">Pindai QRIS berikut</div><canvas id="duitkuQr" data-qr="<?= e($payment['payment_qr_string']) ?>" class="img-fluid" aria-label="QRIS pembayaran Duitku"></canvas><div class="small text-secondary">Gunakan aplikasi bank atau dompet digital yang mendukung QRIS.</div></div><?php endif; ?>
            <?php if (!empty($payment['payment_reference'])): ?><p class="small text-secondary">Referensi pembayaran: <strong><?= e($payment['payment_reference']) ?></strong></p><?php endif; ?>
            <?php if (!empty($payment['payment_app_url'])): ?><a class="btn btn-success w-100 mb-2" href="<?= e($payment['payment_app_url']) ?>" target="_blank" rel="noopener"><i class="bi bi-phone me-1"></i>Buka aplikasi pembayaran</a><?php endif; ?>
            <?php if (!empty($payment['payment_url'])): ?><a class="btn btn-outline-primary w-100 mb-3" href="<?= e($payment['payment_url']) ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-1"></i>Buka instruksi Duitku</a><form method="post" class="mb-3"><input type="hidden" name="csrf" value="<?= e(csrf_token('csrf_pelanggan')) ?>"><input type="hidden" name="aksi" value="check_status"><button class="btn btn-primary btn-lg w-100" type="submit"><i class="bi bi-arrow-repeat me-1"></i>Saya sudah bayar, cek status</button></form><?php endif; ?>
            <?php if ($duitkuMethods): ?>
                <details class="text-start mt-3"><summary class="small fw-semibold text-primary">Pilih kanal lain atau buat pembayaran baru</summary>
                    <form method="post" class="pt-3"><input type="hidden" name="csrf" value="<?= e(csrf_token('csrf_pelanggan')) ?>"><input type="hidden" name="aksi" value="create_payment">
                        <label class="form-label" for="duitkuChannel">Kanal pembayaran</label><select class="form-select mb-3" id="duitkuChannel" name="duitku_channel" required><option value="">Pilih kanal pembayaran</option>
                            <?php foreach ($duitkuMethods as $method): ?><option value="<?= e($method['paymentMethod'] ?? '') ?>"><?= e($method['paymentName'] ?? $method['paymentMethod'] ?? 'Kanal pembayaran') ?><?= isset($method['totalFee']) ? ' — biaya ' . e(rupiah($method['totalFee'])) : '' ?></option><?php endforeach; ?>
                        </select>
                        <label class="form-label" for="emailPembayaran">Email</label><input class="form-control mb-3" id="emailPembayaran" type="email" name="email" maxlength="50" placeholder="nama@email.com" required>
                        <button class="btn btn-outline-primary w-100" type="submit"><?= empty($payment['payment_url']) ? 'Buat pembayaran' : 'Ganti kanal dan buat ulang' ?></button>
                    </form>
                </details>
            <?php elseif (empty($payment['payment_url'])): ?><div class="alert alert-warning">Daftar kanal Duitku belum tersedia. Muat ulang halaman atau periksa konfigurasi Duitku.</div><?php endif; ?>        <?php else: ?>
        <p class="text-secondary">Pindai QRIS merchant dengan aplikasi bank atau e-wallet Anda, lalu masukkan nominal persis seperti total pesanan.</p>
        <img src="<?= url('assets/img/qris_merchant.jpg') ?>" alt="QRIS pembayaran toko" class="img-fluid border rounded mb-3" style="max-width:420px">
        <form method="post" enctype="multipart/form-data" class="text-start">
            <input type="hidden" name="csrf" value="<?= e(csrf_token('csrf_pelanggan')) ?>">
            <label class="form-label" for="bukti">Unggah bukti pembayaran (JPG, PNG, WebP; maks. 5 MB)</label>
            <input class="form-control mb-3" type="file" id="bukti" name="bukti" accept="image/jpeg,image/png,image/webp" required>
            <button class="btn btn-primary btn-lg w-100" type="submit">Kirim bukti pembayaran</button>
        </form>
        <?php endif; ?>
    <?php elseif ($payment['payment_status'] === 'verification'): ?>
        <p class="text-secondary">Bukti pembayaran sudah dikirim. Admin akan mencocokkan dana masuk sebelum menandai pesanan lunas.</p>
        <div class="small text-secondary">Bukti pembayaran tersimpan dan hanya dapat dilihat oleh admin.</div>
    <?php endif; ?>
    <a class="d-block mt-3" href="beranda.php">Kembali ke toko</a>
</div></div></main>
<?php if (($payment['payment_method'] ?? '') === 'duitku' && !empty($payment['payment_qr_string'])): ?><script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.4/build/qrcode.min.js"></script><script>QRCode.toCanvas(document.getElementById('duitkuQr'), document.getElementById('duitkuQr').dataset.qr, {width:220,margin:1});</script><?php endif; ?>
</body></html>
