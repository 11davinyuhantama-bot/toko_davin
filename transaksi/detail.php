
<?php
require_once '../config/database.php';
require_once '../config/helpers.php';

// Ambil ID transaksi dari URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    set_flash('danger', 'ID transaksi tidak valid.');
    header('Location: index.php');
    exit;
}

// Ambil data transaksi dan pelanggan
$queryTransaksi = "
    SELECT 
        t.id_transaksi,
        t.tanggal,
        t.id_pelanggan,
        t.total_belanja,
        t.payment_method,
        t.payment_channel,
        t.payment_status,
        t.payment_reference,
        p.nama_pelanggan,
        p.kota
    FROM tbl_transaksi t
    LEFT JOIN tbl_pelanggan p 
        ON t.id_pelanggan = p.id_pelanggan
    WHERE t.id_transaksi = $id
";

$resultTransaksi = $conn->query($queryTransaksi);

if (!$resultTransaksi || $resultTransaksi->num_rows == 0) {
    set_flash('danger', 'Transaksi tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$transaksi = $resultTransaksi->fetch_assoc();
$metodePembayaran = ['qris_static' => 'QRIS toko (verifikasi manual)', 'duitku' => 'Duitku Payment Gateway', 'cash' => 'Tunai', 'transfer' => 'Transfer bank'];
$statusPembayaran = ['pending' => 'Menunggu pembayaran', 'verification' => 'Menunggu verifikasi', 'paid' => 'Lunas', 'expired' => 'Kedaluwarsa', 'cancelled' => 'Dibatalkan', 'failed' => 'Gagal'];
$labelMetodePembayaran = $metodePembayaran[$transaksi['payment_method']] ?? ucfirst((string) ($transaksi['payment_method'] ?? 'Tidak diketahui'));
if (($transaksi['payment_method'] ?? '') === 'duitku' && !empty($transaksi['payment_channel'])) {
    $labelMetodePembayaran = $transaksi['payment_channel'] . ' (Duitku)';
}

// Ambil detail barang transaksi
$queryDetail = "
    SELECT 
        id_detail,
        nama_produk,
        jumlah,
        harga
    FROM tbl_detail_transaksi
    WHERE id_transaksi = $id
";

$resultDetail = $conn->query($queryDetail);

if (!$resultDetail) {
    die('Gagal mengambil detail transaksi: ' . $conn->error);
}

$detail = [];
$totalItem = 0;

while ($row = $resultDetail->fetch_assoc()) {
    $detail[] = $row;
    $totalItem += (int) $row['jumlah'];
}

include '../includes/header.php';
?>

<div class="container-fluid">

    <!-- Judul dan tombol -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Detail Transaksi</h4>
            <p class="text-secondary mb-0">
                Informasi lengkap transaksi penjualan
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= url('transaksi/cetak_struk.php?id=' . (int) $transaksi['id_transaksi']) ?>"
               target="_blank"
               class="btn btn-dark">
                <i class="bi bi-printer me-1"></i>
                Cetak Struk
            </a>

            <a href="<?= url('transaksi/index.php') ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Informasi transaksi -->
    <div class="row g-4 mb-4">

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-receipt me-2"></i>
                        Informasi Transaksi
                    </h6>

                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-secondary">ID Transaksi</td>
                            <td class="fw-semibold">
                                #<?= e($transaksi['id_transaksi']) ?>
                            </td>
                        </tr>

                        <tr>
                            <td class="text-secondary">Tanggal</td>
                            <td class="fw-semibold">
                                <?= date('d-m-Y', strtotime($transaksi['tanggal'])) ?>
                            </td>
                        </tr>

                        <tr>
                            <td class="text-secondary">Total Item</td>
                            <td class="fw-semibold">
                                <?= number_format($totalItem, 0, ',', '.') ?> item
                            </td>
                        </tr>

                        <tr>
                            <td class="text-secondary">Total Belanja</td>
                            <td class="fw-bold text-primary">
                                <?= rupiah($transaksi['total_belanja']) ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-secondary">Metode Pembayaran</td>
                            <td class="fw-semibold"><?= e($labelMetodePembayaran) ?></td>
                        </tr>
                        <tr>
                            <td class="text-secondary">Status Pembayaran</td>
                            <td class="fw-semibold"><?= e($statusPembayaran[$transaksi['payment_status']] ?? ucfirst((string) $transaksi['payment_status'])) ?></td>
                        </tr>
                        <?php if (($transaksi['payment_method'] ?? '') === 'duitku' && !empty($transaksi['payment_reference'])): ?>
                        <tr>
                            <td class="text-secondary">Referensi Duitku</td>
                            <td class="fw-semibold"><?= e($transaksi['payment_reference']) ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- Informasi pelanggan -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-person me-2"></i>
                        Informasi Pelanggan
                    </h6>

                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-secondary">Nama Pelanggan</td>
                            <td class="fw-semibold">
                                <?= e($transaksi['nama_pelanggan'] ?? 'Pelanggan umum') ?>
                            </td>
                        </tr>

                        <tr>
                            <td class="text-secondary">Kota</td>
                            <td class="fw-semibold">
                                <?= e($transaksi['kota'] ?? '-') ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Daftar barang -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <div class="mb-3">
                <h6 class="fw-bold mb-1">Daftar Barang</h6>
                <p class="text-secondary small mb-0">
                    Barang yang dibeli dalam transaksi ini
                </p>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60">No</th>
                            <th>Nama Produk</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-end">Harga</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (count($detail) > 0): ?>

                            <?php foreach ($detail as $index => $item): ?>
                                <?php
                                $subtotal = (int) $item['jumlah'] * (float) $item['harga'];
                                ?>

                                <tr>
                                    <td><?= $index + 1 ?></td>

                                    <td class="fw-semibold">
                                        <?= e($item['nama_produk']) ?>
                                    </td>

                                    <td class="text-center">
                                        <?= number_format($item['jumlah'], 0, ',', '.') ?>
                                    </td>

                                    <td class="text-end">
                                        <?= rupiah($item['harga']) ?>
                                    </td>

                                    <td class="text-end fw-semibold">
                                        <?= rupiah($subtotal) ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">
                                    Belum ada detail barang untuk transaksi ini.
                                </td>
                            </tr>

                        <?php endif; ?>
                    </tbody>

                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4" class="text-end">
                                Total Belanja
                            </th>
                            <th class="text-end text-primary">
                                <?= rupiah($transaksi['total_belanja']) ?>
                            </th>
                        </tr>
                    </tfoot>

                </table>
            </div>

        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
