<?php
require_once '../config/database.php';
$pageTitle='Data Transaksi';
$perHalaman = 15;
$halaman = max(1, (int) ($_GET['halaman'] ?? 1));
$jumlahTransaksi = (int) $conn->query('SELECT COUNT(*) AS total FROM view_transaksi_pelanggan')->fetch_assoc()['total'];
$totalHalaman = max(1, (int) ceil($jumlahTransaksi / $perHalaman));
$halaman = min($halaman, $totalHalaman);
$offset = ($halaman - 1) * $perHalaman;
$stmtRows = $conn->prepare('SELECT * FROM view_transaksi_pelanggan ORDER BY tanggal DESC, id_transaksi DESC LIMIT ? OFFSET ?');
$stmtRows->bind_param('ii', $perHalaman, $offset);
$stmtRows->execute();
$rows = $stmtRows->get_result();
include '../includes/header.php';
?>
<div class="card content-card"><div class="card-body">
 <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="fw-bold mb-0">Daftar Transaksi</h5><a href="../laporan/index.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-bar-graph me-1"></i>Laporan</a></div>
 <div class="table-responsive"><table class="table table-hover"><thead><tr><th>ID Transaksi</th><th>Tanggal</th><th>Pelanggan</th><th>Kota</th><th class="text-end">Total Belanja</th><th>Aksi</th></tr></thead><tbody>
 <?php if($rows->num_rows):while($r=$rows->fetch_assoc()): ?>
 <tr><td>#<?= e($r['id_transaksi']) ?></td><td><?= e($r['tanggal']) ?></td><td><?= e($r['nama_pelanggan']) ?></td><td><?= e($r['kota']) ?></td><td class="text-end fw-semibold"><?= rupiah($r['total_belanja']) ?></td><td><div class="d-flex gap-1"><a class="btn btn-sm btn-outline-primary" href="<?= url('transaksi/detail.php?id=' . (int) $r['id_transaksi']) ?>">Detail</a><a class="btn btn-sm btn-dark" href="<?= url('transaksi/cetak_struk.php?id=' . (int) $r['id_transaksi']) ?>" target="_blank" rel="noopener"><i class="bi bi-printer me-1"></i>Cetak struk</a></div></td></tr>
 <?php endwhile;else: ?><tr><td colspan="6" class="text-center text-secondary py-4">Belum ada transaksi.</td></tr><?php endif; ?>
 </tbody></table></div>
 <?php if ($totalHalaman > 1): ?><nav class="mt-3" aria-label="Navigasi halaman transaksi"><ul class="pagination justify-content-end mb-0"><li class="page-item <?= $halaman <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="?halaman=<?= max(1, $halaman - 1) ?>">Sebelumnya</a></li><li class="page-item disabled"><span class="page-link">Halaman <?= $halaman ?> dari <?= $totalHalaman ?></span></li><li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : '' ?>"><a class="page-link" href="?halaman=<?= min($totalHalaman, $halaman + 1) ?>">Berikutnya</a></li></ul></nav><?php endif; ?>
</div></div>
<?php include '../includes/footer.php'; ?>
