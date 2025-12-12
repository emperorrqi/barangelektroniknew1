<?php
include 'koneksi.php';

// Ambil filter tanggal dari GET
$tanggal_awal = $_GET['tanggal_awal'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';

$where = "";
$judul = "Laporan Barang Masuk dari vendor";

// Tambahkan filter tanggal jika diisi
if ($tanggal_awal != '' && $tanggal_akhir != '') {
    $where = "WHERE m.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";
    $judul = "Barang Masuk dari $tanggal_awal s/d $tanggal_akhir";
}

// Ambil data barang masuk
$data = $mysqli->query("
    SELECT m.*, 
           b.nama_barang, b.kode_barang, b.spesifikasi,
           v.nama_vendor
    FROM trx_barang_masuk m
    LEFT JOIN master_barang_elektronik b ON m.id_barang = b.id_barang
    LEFT JOIN master_vendor v ON m.id_vendor = v.id_vendor
    $where
    ORDER BY m.tanggal ASC, m.id_masuk ASC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>🖨️ <?= $judul ?></title>
<style>
body { font-family: Arial; padding:20px; }
h2 { text-align:center; margin-bottom:20px; }
form { margin-bottom:15px; }
input { padding:6px; margin-right:10px; }
button { padding:6px 12px; cursor:pointer; background:#007bff; color:white; border:none; border-radius:5px; }
button:hover { background:#0056b3; }
table { width:100%; border-collapse:collapse; margin-top:10px; }
th, td { border:1px solid #000; padding:8px; text-align:left; font-size:14px; }
th { background:#007bff; color:white; }
@media print { .no-print { display:none; } }
.btn-back { display:inline-block; padding:8px 16px; background:#6c757d; color:white; border-radius:6px; text-decoration:none; margin-bottom:15px; }
.btn-back:hover { background:#495057; }
</style>
</head>
<body>

<a href="index.php" class="btn-back no-print">⬅ Kembali ke Halaman Utama</a>

<h2><?= $judul ?></h2>

<!-- Form Filter Tanggal -->
<form method="get" class="no-print">
    <label>Tanggal Awal:</label>
    <input type="date" name="tanggal_awal" value="<?= htmlspecialchars($tanggal_awal) ?>">
    <label>Tanggal Akhir:</label>
    <input type="date" name="tanggal_akhir" value="<?= htmlspecialchars($tanggal_akhir) ?>">
    <button type="submit">Filter</button>
    <button type="button" onclick="window.print()">🖨️ Print</button>
</form>

<table>
    <tr>
        <th>No</th>
        <th>Kode Masuk</th>
        <th>Tanggal</th>
        <th>Barang</th>
        <th>Vendor</th>
        <th>Jumlah</th>
    </tr>
    <?php $no=1; while($row = $data->fetch_assoc()): ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><?= $row['kode_masuk'] ?></td>
        <td><?= $row['tanggal'] ?></td>
        <td><?= $row['kode_barang'] ?> - <?= $row['nama_barang'] ?><br><small><?= $row['spesifikasi'] ?></small></td>
        <td><?= $row['nama_vendor'] ?></td>
        <td><?= $row['jumlah'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
