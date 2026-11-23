<?php
require_once "../config/database.php";

if (!in_array($_SESSION['user']['role'] ?? '', ['admin', 'kasir'], true)) {
    redirect('login.php');
}

$id = (int) ($_GET['id'] ?? 0);
$keyword = trim($_GET['keyword'] ?? '');
$kategori = trim($_GET['kategori'] ?? '');
$sql = 'SELECT kode_produk, nama_produk, harga FROM tbl_produk WHERE 1=1';
$params = [];
$types = '';
if ($id > 0) {
    $sql .= ' AND id_produk = ?';
    $params[] = $id;
    $types .= 'i';
} else {
    if ($keyword !== '') {
        $sql .= ' AND (kode_produk LIKE ? OR nama_produk LIKE ?)';
        $like = '%' . $keyword . '%';
        $params[] = $like;
        $params[] = $like;
        $types .= 'ss';
    }
    if ($kategori !== '') {
        $sql .= ' AND kategori = ?';
        $params[] = $kategori;
        $types .= 's';
    }
}
$sql .= ' ORDER BY nama_produk';
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Barcode Produk</title>
    <style>
        body { font: 14px Arial, sans-serif; margin: 20px; color: #111; }
        .toolbar { margin-bottom: 20px; }
        .labels { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .label { border: 1px dashed #777; padding: 12px 8px; text-align: center; break-inside: avoid; }
        .label strong, .label small { display: block; margin: 4px 0; }
        svg { max-width: 100%; height: auto; }
        @media print { .toolbar { display: none; } body { margin: 0; } }
        @media (max-width: 600px) { .labels { grid-template-columns: repeat(2, 1fr); } }
    </style>
</head>
<body>
    <div class="toolbar"><button onclick="window.print()">Cetak Barcode</button> <button onclick="window.close()">Tutup</button></div>
    <?php if (!$items): ?><p>Produk tidak ditemukan.</p><?php else: ?>
    <div class="labels">
        <?php foreach ($items as $item): ?>
            <div class="label">
                <strong><?= e($item['nama_produk']) ?></strong>
                <svg class="barcode" data-code="<?= e($item['kode_produk']) ?>"></svg>
                <small><?= e($item['kode_produk']) ?> · <?= rupiah($item['harga']) ?></small>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script>
    document.querySelectorAll('.barcode').forEach(function (svg) {
        JsBarcode(svg, svg.dataset.code, { format: 'CODE128', width: 2, height: 55, displayValue: false, margin: 4 });
    });
    </script>
</body>
</html>
