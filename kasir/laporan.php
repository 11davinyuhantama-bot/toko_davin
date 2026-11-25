<?php
require_once __DIR__ . '/../config/database.php';

if (($_SESSION['user']['role'] ?? '') !== 'kasir') {
    redirect('login.php');
}

$today = new DateTimeImmutable('today');
$defaultStart = $today->modify('first day of this month')->format('Y-m-d');
$defaultEnd = $today->format('Y-m-d');
$startDate = $_GET['mulai'] ?? $defaultStart;
$endDate = $_GET['sampai'] ?? $defaultEnd;
$dateIsValid = static function ($value): bool {
    if (!is_string($value)) return false;
    $parsed = DateTime::createFromFormat('!Y-m-d', $value);
    return $parsed && $parsed->format('Y-m-d') === $value;
};

$error = '';
if (!$dateIsValid($startDate) || !$dateIsValid($endDate)) {
    $error = 'Tanggal tidak valid. Silakan pilih periode yang benar.';
    $startDate = $defaultStart;
    $endDate = $defaultEnd;
} elseif ($startDate > $endDate) {
    $error = 'Tanggal mulai harus sebelum atau sama dengan tanggal sampai.';
    $startDate = $endDate;
}

$stmt = $conn->prepare("SELECT t.id_transaksi, t.tanggal, t.total_belanja, p.nama_pelanggan
    FROM tbl_transaksi t
    LEFT JOIN tbl_pelanggan p ON p.id_pelanggan = t.id_pelanggan
    WHERE t.payment_status = 'paid' AND t.id_kasir = ? AND DATE(t.tanggal) BETWEEN ? AND ?
    ORDER BY t.tanggal ASC, t.id_transaksi ASC");
$idKasir = (int) ($_SESSION['user']['id'] ?? 0);
$stmt->bind_param('iss', $idKasir, $startDate, $endDate);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$totalSales = 0;
foreach ($transactions as $transaction) {
    $totalSales += (float) $transaction['total_belanja'];
}
$cashierName = $_SESSION['user']['nama'] ?? 'Kasir';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Penjualan Kasir | Tokoku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body{background:#f1f5f9;color:#172033}.konten-kasir{margin-left:250px;padding:28px}.panel{border:0;border-radius:16px;box-shadow:0 8px 28px #0f172a0a}.report-banner{background:linear-gradient(120deg,#172554,#2563eb);color:#fff;border-radius:16px;padding:24px}.report-banner .subtle{color:#dbeafe}.metric{height:100%;padding:18px 20px;background:white;border:1px solid #e2e8f0;border-radius:14px}.metric-label{color:#64748b;font-size:.88rem}.metric-value{font-weight:750;font-size:1.4rem;color:#172554}.table thead th{background:#eaf2ff;color:#1e3a8a;white-space:nowrap}.table tfoot th{background:#f1f5f9;border-top:2px solid #cbd5e1}
        @media(max-width:768px){.konten-kasir{margin-left:0;padding:16px}.sidebar-kasir{position:relative;width:100%;height:auto}.sidebar-kasir .profil-kasir{margin-top:12px}}
        @media print{body{background:#fff!important}.sidebar-kasir,.report-filter,.print-button,.page-title{display:none!important}.konten-kasir{margin:0!important;padding:0!important}.report-banner{background:#fff!important;color:#111827!important;border:1px solid #94a3b8;border-radius:0;padding:14px}.report-banner .subtle{color:#475569!important}.panel{box-shadow:none;border:1px solid #cbd5e1;border-radius:0}.metric{border-radius:0;padding:10px 14px}.table{font-size:12px}.table th,.table td{padding:8px 10px!important}.table thead th,.table tfoot th{print-color-adjust:exact;-webkit-print-color-adjust:exact}}
    </style>
</head>
<body>
<?php require __DIR__ . '/../includes/sidebar_kasir.php'; ?>
<main class="konten-kasir">
    <header class="page-title d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div><h1 class="h3 fw-bold mb-1">Laporan Penjualan</h1><div class="text-secondary">Ringkasan transaksi lunas untuk <?= e($cashierName) ?>.</div></div>
        <button class="btn btn-outline-primary print-button" type="button" onclick="window.print()"><i class="bi bi-printer me-2"></i>Cetak laporan</button>
    </header>

    <section class="report-banner mb-4">
        <div class="small text-uppercase fw-semibold mb-1">TOKOKU · Laporan kasir</div>
        <h2 class="h4 fw-bold mb-1">Laporan Penjualan</h2>
        <div class="subtle">Periode <?= e(date('d/m/Y', strtotime($startDate))) ?> – <?= e(date('d/m/Y', strtotime($endDate))) ?></div>
    </section>

    <?php if ($error): ?><div class="alert alert-warning"><?= e($error) ?></div><?php endif; ?>

    <form method="get" class="card panel report-filter mb-4"><div class="card-body"><div class="row g-3 align-items-end">
        <div class="col-sm-5 col-lg-4"><label class="form-label" for="mulai">Tanggal mulai</label><input class="form-control" type="date" id="mulai" name="mulai" value="<?= e($startDate) ?>" required></div>
        <div class="col-sm-5 col-lg-4"><label class="form-label" for="sampai">Tanggal sampai</label><input class="form-control" type="date" id="sampai" name="sampai" value="<?= e($endDate) ?>" required></div>
        <div class="col-sm-2"><button class="btn btn-primary w-100" type="submit"><i class="bi bi-funnel me-1"></i>Terapkan</button></div>
    </div></div></form>

    <section class="row g-3 mb-4">
        <div class="col-md-6"><div class="metric"><div class="metric-label"><i class="bi bi-receipt me-2"></i>Transaksi lunas</div><div class="metric-value"><?= count($transactions) ?> transaksi</div></div></div>
        <div class="col-md-6"><div class="metric"><div class="metric-label"><i class="bi bi-cash-stack me-2"></i>Total penjualan</div><div class="metric-value"><?= rupiah($totalSales) ?></div></div></div>
    </section>

    <section class="card panel"><div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"><div><h2 class="h5 fw-bold mb-1">Rincian transaksi</h2><div class="text-secondary small">Transaksi berstatus lunas pada periode yang dipilih.</div></div><span class="badge text-bg-primary"><?= count($transactions) ?> data</span></div>
        <div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th>No.</th><th>ID Transaksi</th><th>Tanggal</th><th>Nama Pelanggan</th><th class="text-end">Total Penjualan</th></tr></thead>
            <tbody>
            <?php if (!$transactions): ?><tr><td colspan="5" class="text-center text-secondary py-4">Belum ada transaksi lunas pada periode ini.</td></tr>
            <?php else: foreach ($transactions as $index => $transaction): ?>
                <tr><td><?= $index + 1 ?></td><td class="fw-semibold">#<?= (int) $transaction['id_transaksi'] ?></td><td><?= e(date('d/m/Y', strtotime($transaction['tanggal']))) ?></td><td><?= e($transaction['nama_pelanggan'] ?: 'Pelanggan umum') ?></td><td class="text-end fw-semibold"><?= rupiah($transaction['total_belanja']) ?></td></tr>
            <?php endforeach; endif; ?>
            </tbody>
            <tfoot><tr><th colspan="4" class="text-end">Total Penjualan</th><th class="text-end"><?= rupiah($totalSales) ?></th></tr></tfoot>
        </table></div>
    </div></section>
</main>
</body>
</html>
