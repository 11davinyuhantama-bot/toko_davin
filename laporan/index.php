<?php
require_once __DIR__ . '/../config/database.php';

if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    redirect('login.php');
}

$pageTitle = 'Laporan Penjualan';
$today = new DateTimeImmutable('today');
$defaultStart = $today->modify('first day of this month')->format('Y-m-d');
$defaultEnd = $today->format('Y-m-d');
$startDate = $_GET['mulai'] ?? $defaultStart;
$endDate = $_GET['sampai'] ?? $defaultEnd;
$cashierFilter = $_GET['kasir'] ?? 'semua';
$validDate = static function ($value): bool {
    if (!is_string($value)) return false;
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value;
};
$filterError = '';
if (!$validDate($startDate) || !$validDate($endDate)) {
    $filterError = 'Tanggal tidak valid. Silakan pilih periode yang benar.';
    $startDate = $defaultStart;
    $endDate = $defaultEnd;
} elseif ($startDate > $endDate) {
    $filterError = 'Tanggal mulai harus sebelum atau sama dengan tanggal sampai.';
    $startDate = $endDate;
}

$cashiers = $conn->query("SELECT id_user, nama, username FROM tbl_user WHERE role = 'kasir' ORDER BY nama ASC")->fetch_all(MYSQLI_ASSOC);
$cashierMap = [];
foreach ($cashiers as $cashier) {
    $cashierMap[(string) $cashier['id_user']] = $cashier['nama'];
}
if ($cashierFilter !== 'semua' && !isset($cashierMap[(string) $cashierFilter])) {
    $cashierFilter = 'semua';
    $filterError = 'Kasir yang dipilih tidak tersedia.';
}

$sql = "SELECT t.id_transaksi, t.tanggal, t.total_belanja, p.nama_pelanggan,
               u.nama AS nama_kasir
        FROM tbl_transaksi t
        LEFT JOIN tbl_pelanggan p ON p.id_pelanggan = t.id_pelanggan
        LEFT JOIN tbl_user u ON u.id_user = t.id_kasir AND u.role = 'kasir'
        WHERE t.payment_status = 'paid' AND DATE(t.tanggal) BETWEEN ? AND ?";
$types = 'ss';
$params = [$startDate, $endDate];
if ($cashierFilter !== 'semua') {
    $sql .= ' AND t.id_kasir = ?';
    $types .= 'i';
    $params[] = (int) $cashierFilter;
}
$sql .= ' ORDER BY t.tanggal ASC, t.id_transaksi ASC';
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$totalSales = 0;
foreach ($transactions as $transaction) {
    $totalSales += (float) $transaction['total_belanja'];
}
$selectedCashierName = $cashierFilter === 'semua' ? 'Semua Kasir' : $cashierMap[(string) $cashierFilter];
include __DIR__ . '/../includes/header.php';
?>

<style>
.admin-sales-report .report-banner{background:linear-gradient(120deg,#172554,#2563eb);color:#fff;border-radius:16px;padding:22px 24px}
.admin-sales-report .report-banner .subtle{color:#dbeafe}
.admin-sales-report .metric{height:100%;padding:17px 20px;background:#fff;border:1px solid #e2e8f0;border-radius:14px}
.admin-sales-report .metric-label{color:#64748b;font-size:.86rem}
.admin-sales-report .metric-value{font-size:1.35rem;font-weight:750;color:#172554}
.admin-sales-report .report-table thead th{background:#eaf2ff;color:#1e3a8a;white-space:nowrap}
.admin-sales-report .report-table tfoot th{background:#f1f5f9;border-top:2px solid #cbd5e1}
@media print{
    body{background:#fff!important}.topbar,.sidebar,#mobileMenu,.main-content>.container-fluid>.mb-4,.report-filter,.report-actions,footer{display:none!important}
    .main-content,.main-content>.container-fluid{margin:0!important;padding:0!important;max-width:none!important}
    .admin-sales-report .report-banner{background:#fff!important;color:#111827!important;border:1px solid #94a3b8;border-radius:0;padding:14px}
    .admin-sales-report .report-banner .subtle{color:#475569!important}.admin-sales-report .metric{border-radius:0;padding:10px 14px}
    .admin-sales-report .card{box-shadow:none!important;border:1px solid #cbd5e1!important;border-radius:0!important}
    .admin-sales-report .card-body{padding:14px!important}.admin-sales-report .report-table{font-size:12px}
    .admin-sales-report .report-table th,.admin-sales-report .report-table td{padding:8px 10px!important}
    .admin-sales-report .report-table thead th,.admin-sales-report .report-table tfoot th{print-color-adjust:exact;-webkit-print-color-adjust:exact}
}
</style>

<div class="admin-sales-report">
    <section class="report-banner mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div><div class="small text-uppercase fw-semibold mb-1">TOKOKU · Laporan admin</div><h2 class="h4 fw-bold mb-1">Laporan Penjualan</h2><div class="subtle"><?= e($selectedCashierName) ?> · <?= e(date('d/m/Y', strtotime($startDate))) ?> – <?= e(date('d/m/Y', strtotime($endDate))) ?></div></div>
            <button class="btn btn-light report-actions" type="button" onclick="window.print()"><i class="bi bi-printer me-2"></i>Cetak laporan</button>
        </div>
    </section>

    <?php if ($filterError): ?><div class="alert alert-warning"><?= e($filterError) ?></div><?php endif; ?>

    <form method="get" class="card report-filter mb-4"><div class="card-body"><div class="row g-3 align-items-end">
        <div class="col-md-3"><label class="form-label" for="kasir">Kasir</label><select class="form-select" id="kasir" name="kasir"><option value="semua" <?= $cashierFilter === 'semua' ? 'selected' : '' ?>>Semua kasir · laporan lengkap</option><?php foreach ($cashiers as $cashier): ?><option value="<?= (int) $cashier['id_user'] ?>" <?= (string) $cashierFilter === (string) $cashier['id_user'] ? 'selected' : '' ?>><?= e($cashier['nama']) ?> (@<?= e($cashier['username']) ?>)</option><?php endforeach; ?></select></div>
        <div class="col-md-3"><label class="form-label" for="mulai">Tanggal mulai</label><input class="form-control" type="date" id="mulai" name="mulai" value="<?= e($startDate) ?>" required></div>
        <div class="col-md-3"><label class="form-label" for="sampai">Tanggal sampai</label><input class="form-control" type="date" id="sampai" name="sampai" value="<?= e($endDate) ?>" required></div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-primary flex-grow-1" type="submit"><i class="bi bi-funnel me-1"></i>Terapkan</button><a class="btn btn-outline-secondary" href="<?= e(url('laporan/index.php')) ?>" title="Atur ulang filter"><i class="bi bi-arrow-counterclockwise"></i></a></div>
    </div></div></form>

    <section class="row g-3 mb-4">
        <div class="col-md-6"><div class="metric"><div class="metric-label"><i class="bi bi-receipt me-2"></i>Jumlah transaksi lunas</div><div class="metric-value"><?= count($transactions) ?> transaksi</div></div></div>
        <div class="col-md-6"><div class="metric"><div class="metric-label"><i class="bi bi-cash-stack me-2"></i>Total penjualan</div><div class="metric-value"><?= rupiah($totalSales) ?></div></div></div>
    </section>

    <section class="card content-card"><div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"><div><h3 class="h5 fw-bold mb-1">Rincian transaksi · <?= e($selectedCashierName) ?></h3><div class="text-secondary small">Transaksi berstatus lunas pada periode terpilih.</div></div><span class="badge text-bg-primary"><?= count($transactions) ?> data</span></div>
        <div class="table-responsive"><table class="table report-table align-middle mb-0">
            <thead><tr><th>No.</th><th>ID Transaksi</th><th>Tanggal</th><th>Nama Pelanggan</th><th>Kasir</th><th class="text-end">Total Penjualan</th></tr></thead>
            <tbody>
            <?php if (!$transactions): ?><tr><td colspan="6" class="text-center text-secondary py-4">Tidak ada transaksi lunas untuk filter ini.</td></tr>
            <?php else: foreach ($transactions as $index => $transaction): ?>
                <tr><td><?= $index + 1 ?></td><td class="fw-semibold">#<?= (int) $transaction['id_transaksi'] ?></td><td><?= e(date('d/m/Y', strtotime($transaction['tanggal']))) ?></td><td><?= e($transaction['nama_pelanggan'] ?: 'Pelanggan umum') ?></td><td><?= e($transaction['nama_kasir'] ?: 'Kasir tidak tercatat') ?></td><td class="text-end fw-semibold"><?= rupiah($transaction['total_belanja']) ?></td></tr>
            <?php endforeach; endif; ?>
            </tbody>
            <tfoot><tr><th colspan="5" class="text-end">Total Penjualan</th><th class="text-end"><?= rupiah($totalSales) ?></th></tr></tfoot>
        </table></div>
    </div></section>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
