<?php
require_once __DIR__ . '/../config/database.php';
if (($_SESSION['user']['role'] ?? '') !== 'admin') redirect('login.php');
$pageTitle = 'Verifikasi Pembayaran QRIS';
$rows = $conn->query("SELECT t.id_transaksi, t.tanggal, t.total_belanja, t.payment_status, t.payment_proof, p.nama_pelanggan FROM tbl_transaksi t LEFT JOIN tbl_pelanggan p ON p.id_pelanggan = t.id_pelanggan WHERE t.payment_status IN ('pending', 'verification') AND t.payment_method = 'qris_static' ORDER BY t.tanggal ASC, t.id_transaksi ASC");
$pendingCount = (int) $conn->query("SELECT COUNT(*) AS total FROM tbl_transaksi WHERE payment_status = 'pending' AND payment_method = 'qris_static'")->fetch_assoc()['total'];
$csrfPayment = csrf_token('csrf_payment_review');
include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div><h1 class="h4 fw-bold mb-1">Verifikasi Pembayaran QRIS</h1><p class="text-secondary mb-0">Cocokkan bukti dengan dana yang benar-benar masuk ke merchant.</p></div>
    <span class="badge text-bg-warning"><?= $pendingCount ?> pesanan menunggu bukti</span>
</div>
<div class="card content-card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th class="ps-3">Pesanan</th><th>Pelanggan</th><th>Tanggal</th><th>Total</th><th>Status</th><th>Bukti pembayaran</th><th class="text-end pe-3">Tindakan</th></tr></thead>
    <tbody>
    <?php if (!$rows->num_rows): ?><tr><td colspan="7" class="text-center text-secondary py-5">Tidak ada pesanan QRIS yang menunggu diproses.</td></tr>
    <?php else: while ($row = $rows->fetch_assoc()): ?>
        <tr><td class="ps-3 fw-semibold">#<?= (int) $row['id_transaksi'] ?></td><td><?= e($row['nama_pelanggan'] ?: 'Pelanggan') ?></td><td><?= e($row['tanggal']) ?></td><td><?= rupiah($row['total_belanja']) ?></td><td><?= $row['payment_status'] === 'verification' ? 'Bukti menunggu verifikasi' : 'Menunggu pelanggan' ?></td><td><?php if ($row['payment_proof']): ?><a target="_blank" rel="noopener" href="bukti_pembayaran.php?id=<?= (int) $row['id_transaksi'] ?>">Lihat bukti</a><?php else: ?>—<?php endif; ?></td><td class="text-end pe-3"><div class="d-flex justify-content-end gap-2"><?php if ($row['payment_status'] === 'verification'): ?><form method="post" action="verifikasi_pembayaran.php"><input type="hidden" name="csrf" value="<?= e($csrfPayment) ?>"><input type="hidden" name="id_transaksi" value="<?= (int) $row['id_transaksi'] ?>"><input type="hidden" name="aksi" value="approve"><button class="btn btn-sm btn-success">Konfirmasi lunas</button></form><?php endif; ?><form method="post" action="verifikasi_pembayaran.php" onsubmit="return confirm('Batalkan pesanan dan kembalikan stok?')"><input type="hidden" name="csrf" value="<?= e($csrfPayment) ?>"><input type="hidden" name="id_transaksi" value="<?= (int) $row['id_transaksi'] ?>"><input type="hidden" name="aksi" value="reject"><button class="btn btn-sm btn-outline-danger">Batalkan</button></form></div></td></tr>
    <?php endwhile; endif; ?>
    </tbody>
</table></div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
