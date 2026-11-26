<?php
require_once __DIR__ . '/../config/database.php';
if (($_SESSION['user']['role'] ?? '') !== 'pelanggan') {
    redirect('login.php');
}
$idPelanggan = (int) ($_SESSION['user']['id_pelanggan'] ?? 0);
$idTransaksi = (int) ($_GET['id'] ?? 0);
$stmt = $conn->prepare('SELECT t.id_transaksi, t.tanggal, t.total_belanja, t.payment_method, t.payment_status, t.payment_reference, t.payment_channel, p.nama_pelanggan FROM tbl_transaksi t LEFT JOIN tbl_pelanggan p ON p.id_pelanggan = t.id_pelanggan WHERE t.id_transaksi = ? AND t.id_pelanggan = ? LIMIT 1');
$stmt->bind_param('ii', $idTransaksi, $idPelanggan);
$stmt->execute();
$transaksi = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$transaksi) {
    set_flash('warning', 'Transaksi tidak ditemukan di akun Anda.');
    redirect('pelanggan/beranda.php');
}
$stmt = $conn->prepare('SELECT nama_produk, jumlah, harga FROM tbl_detail_transaksi WHERE id_transaksi = ? ORDER BY id_detail');
$stmt->bind_param('i', $idTransaksi);
$stmt->execute();
$detail = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$metodePembayaran = [
    'qris_static' => 'QRIS toko (verifikasi manual)',
    'duitku' => 'Duitku Payment Gateway',
    'cash' => 'Tunai',
    'transfer' => 'Transfer bank',
];
$statusPembayaran = [
    'pending' => 'Menunggu pembayaran',
    'verification' => 'Menunggu verifikasi',
    'paid' => 'Lunas',
    'expired' => 'Kedaluwarsa',
    'cancelled' => 'Dibatalkan',
    'failed' => 'Gagal',
];
$labelMetodePembayaran = $metodePembayaran[$transaksi['payment_method']] ?? ucfirst((string) $transaksi['payment_method']);
if ($transaksi['payment_method'] === 'duitku' && !empty($transaksi['payment_channel'])) {
    $labelMetodePembayaran = $transaksi['payment_channel'] . ' (Duitku)';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk #<?= (int) $transaksi['id_transaksi'] ?> | Tokoku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body{background:#eef2f7;color:#172033}.customer-main{min-height:100vh;padding:1rem}@media(min-width:992px){.customer-main{margin-left:320px;padding:2rem}}
        .receipt{width:min(100%,390px);margin:0 auto;background:#fff;padding:24px 25px;border-radius:8px;box-shadow:0 12px 36px #0f172a18;font:13px/1.45 "Courier New",monospace;color:#111}
        .receipt-title{text-align:center;border-bottom:1px dashed #333;padding-bottom:14px;margin-bottom:12px}.receipt-title h1{font:bold 23px/1.1 Arial,sans-serif;letter-spacing:2px;margin:5px 0}.receipt-title p{font:12px Arial,sans-serif;margin:3px 0;color:#555}.receipt-barcode{text-align:center;border-bottom:1px dashed #333;padding:9px 0 12px;margin-bottom:8px}.receipt-barcode svg{display:block;width:100%;height:48px}.receipt-barcode span{font-size:11px;letter-spacing:1px}
        .receipt-meta{display:flex;justify-content:space-between;gap:10px;margin:4px 0}.receipt-section{border-top:1px dashed #333;margin-top:11px;padding-top:10px}.item{padding:6px 0}.item-name{font-weight:bold;overflow-wrap:anywhere}.item-line{display:flex;justify-content:space-between;gap:8px}.muted{color:#555}.receipt-total{font-weight:bold;font-size:15px;border-top:1px dashed #333;margin-top:8px;padding-top:9px}.payment-status{font-weight:bold}.receipt-footer{text-align:center;border-top:1px dashed #333;padding-top:12px;margin-top:14px;color:#444}
        @media print{@page{size:80mm auto;margin:3mm}body,.bg-light{background:#fff!important}.sidebar-pelanggan,.no-print{display:none!important}.customer-main{min-height:0!important;margin:0!important;padding:0!important}.receipt{width:74mm;max-width:none;margin:0;padding:0;border-radius:0;box-shadow:none;font-size:10px}.receipt-title h1{font-size:18px}.receipt-title p{font-size:10px}.receipt-barcode svg{height:38px}.receipt-total{font-size:12px}}
    </style>
</head>
<body class="bg-light">
<?php $menuPelangganAktif = 'riwayat'; require __DIR__ . '/../includes/sidebar_pelanggan.php'; ?>
<main class="customer-main">
    <?php if ((int) ($_SESSION['payment_success_notice'] ?? 0) === $idTransaksi): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i>Pembayaran berhasil dikonfirmasi. Ini struk pesanan Anda.</div><?php endif; ?>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3 no-print">
        <div><h1 class="h3 fw-bold mb-1">Struk Pesanan #<?= (int) $transaksi['id_transaksi'] ?></h1><p class="text-secondary mb-0">Bukti transaksi pelanggan.</p></div>
        <div><a class="btn btn-outline-primary" href="<?= url('pelanggan/beranda.php') ?>"><i class="bi bi-arrow-left me-1"></i>Kembali</a></div>
    </div>
    <article class="receipt">
        <header class="receipt-title"><div>BUKTI PEMBELIAN</div><h1>TOKOKU</h1><p>Terima kasih telah berbelanja</p></header>
        <div class="receipt-barcode"><svg id="barcodeTransaksi" aria-label="Barcode transaksi #<?= (int) $transaksi['id_transaksi'] ?>"></svg><span>#<?= (int) $transaksi['id_transaksi'] ?></span></div>
        <div class="receipt-meta"><span>No. transaksi</span><strong>#<?= (int) $transaksi['id_transaksi'] ?></strong></div>
        <div class="receipt-meta"><span>Tanggal</span><strong><?= e(date('d/m/Y H:i', strtotime($transaksi['tanggal']))) ?></strong></div>
        <div class="receipt-meta"><span>Pelanggan</span><strong><?= e($transaksi['nama_pelanggan'] ?: 'Pelanggan') ?></strong></div>
        <section class="receipt-section" aria-label="Rincian belanja">
            <?php foreach ($detail as $item): $subtotal = (float) $item['harga'] * (int) $item['jumlah']; ?>
                <div class="item"><div class="item-name"><?= e($item['nama_produk']) ?></div><div class="item-line"><span class="muted"><?= (int) $item['jumlah'] ?> x <?= rupiah($item['harga']) ?></span><strong><?= rupiah($subtotal) ?></strong></div></div>
            <?php endforeach; ?>
        </section>
        <div class="item-line receipt-total"><span>TOTAL</span><span><?= rupiah($transaksi['total_belanja']) ?></span></div>
        <section class="receipt-section" aria-label="Informasi pembayaran">
            <div class="receipt-meta"><span>Metode bayar</span><strong><?= e($labelMetodePembayaran) ?></strong></div>
            <div class="receipt-meta"><span>Status bayar</span><strong class="payment-status"><?= e($statusPembayaran[$transaksi['payment_status']] ?? ucfirst((string) $transaksi['payment_status'])) ?></strong></div>
            <?php if ($transaksi['payment_method'] === 'duitku' && !empty($transaksi['payment_reference'])): ?><div class="receipt-meta"><span>Referensi Duitku</span><strong><?= e($transaksi['payment_reference']) ?></strong></div><?php endif; ?>
        </section>
        <footer class="receipt-footer"><div>Barang yang sudah dibeli tidak dapat</div><div>dikembalikan kecuali sesuai kebijakan toko.</div><strong>*** TERIMA KASIH ***</strong></footer>
    </article>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>JsBarcode('#barcodeTransaksi', 'TOKO-<?= (int) $transaksi['id_transaksi'] ?>', { format: 'CODE128', width: 1.6, height: 42, displayValue: false, margin: 2 });</script>
</body>
</html>
