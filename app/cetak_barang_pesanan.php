<?php
include 'koneksi.php';

// Ambil filter dari form
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$id_barang = $_GET['id_barang'] ?? '';
$id_gudang = $_GET['id_gudang'] ?? '';

// Ambil daftar barang & gudang untuk filter
$barang_list = $mysqli->query("SELECT * FROM master_barang_elektronik ORDER BY nama_barang ASC");
$gudang_list = $mysqli->query("SELECT * FROM master_gudang ORDER BY nama_gudang ASC");

// Query data dengan filter
$query = "
    SELECT p.*, b.nama_barang, b.kode_barang, b.spesifikasi, g.nama_gudang
    FROM trx_barang_pesanan p
    LEFT JOIN master_barang_elektronik b ON p.id_barang = b.id_barang
    LEFT JOIN master_gudang g ON p.id_gudang = g.id_gudang
    WHERE 1
";

if ($tgl_awal && $tgl_akhir) {
    $query .= " AND p.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}
if ($id_barang) {
    $query .= " AND p.id_barang = " . intval($id_barang);
}
if ($id_gudang) {
    $query .= " AND p.id_gudang = " . intval($id_gudang);
}

$query .= " ORDER BY p.id_pesanan ASC";

$data = $mysqli->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Barang Pesanan</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        h2 { text-align: center; }
        form { margin-bottom: 20px; }
        input, select { padding:5px; margin-right:10px; }
        table { width:100%; border-collapse: collapse; margin-top:20px; }
        th, td { border:1px solid #333; padding:8px; text-align:left; }
        th { background:#007bff; color:white; }
        button { padding:5px 10px; cursor:pointer; }
        @media print {
            form, button { display:none; }
        }
    </style>
</head>
<body>

<h2>📄 Laporan Barang Pesanan</h2>
<p>Tanggal Cetak: <?= date('d-m-Y') ?></p>

<!-- Filter Form -->
<form method="get">
    <label>Tanggal Awal:</label>
    <input type="date" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>">

    <label>Tanggal Akhir:</label>
    <input type="date" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>">

    <label>Barang:</label>
    <select name="id_barang">
        <option value="">-- Semua Barang --</option>
        <?php while($b = $barang_list->fetch_assoc()): ?>
            <option value="<?= $b['id_barang'] ?>" <?= ($id_barang == $b['id_barang']) ? 'selected' : '' ?>>
                <?= $b['kode_barang'] ?> - <?= $b['nama_barang'] ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Gudang:</label>
    <select name="id_gudang">
        <option value="">-- Semua Gudang --</option>
        <?php while($g = $gudang_list->fetch_assoc()): ?>
            <option value="<?= $g['id_gudang'] ?>" <?= ($id_gudang == $g['id_gudang']) ? 'selected' : '' ?>>
                <?= $g['nama_gudang'] ?>
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit">🔍 Filter</button>
    <button type="button" onclick="window.print()">🖨️ Cetak Halaman</button>
</form>

<table>
    <tr>
        <th>Kode Pesanan</th>
        <th>Tanggal</th>
        <th>Barang</th>
        <th>Gudang</th>
        <th>Jumlah</th>
    </tr>
    <?php while($row = $data->fetch_assoc()): ?>
    <tr>
        <td><?= $row['kode_pesanan'] ?></td>
        <td><?= $row['tanggal'] ?></td>
        <td><?= $row['kode_barang'] ?> - <?= $row['nama_barang'] ?><br><small><?= $row['spesifikasi'] ?></small></td>
        <td><?= $row['nama_gudang'] ?></td>
        <td><?= $row['jumlah'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
