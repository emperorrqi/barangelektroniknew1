<?php
include 'koneksi.php';

// Ambil daftar barang untuk filter
$barangList = $mysqli->query("SELECT id_barang, nama_barang, kode_barang FROM master_barang_elektronik ORDER BY nama_barang ASC");

// Inisialisasi filter
$tanggalAwal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : '';
$tanggalAkhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : '';
$id_barang = isset($_GET['id_barang']) ? $_GET['id_barang'] : '';

// Bangun query dengan filter
$sql = "
    SELECT t.*, b.nama_barang, b.kode_barang
    FROM trx_berita_serah_terima t
    JOIN master_barang_elektronik b ON t.id_barang = b.id_barang
    WHERE 1=1
";
if ($tanggalAwal != '') $sql .= " AND t.tanggal >= '$tanggalAwal'";
if ($tanggalAkhir != '') $sql .= " AND t.tanggal <= '$tanggalAkhir'";
if ($id_barang != '') $sql .= " AND t.id_barang = $id_barang";

$sql .= " ORDER BY t.id_serah DESC";
$data = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Cetak Basterima</title>
<style>
table{width:100%;border-collapse:collapse;margin-top:15px;}
th,td{border:1px solid #000;padding:8px;text-align:center;}
th{background:#8e44ad;color:white;}
form{margin-bottom:15px;}
input, select{padding:5px;margin-right:10px;}
button{padding:6px 12px;background:#28a745;color:white;border:none;border-radius:4px;cursor:pointer;}
button:hover{background:#218838;}
</style>
</head>
<body>
<h2>Berita Serah Terima</h2>
<p>Tanggal Cetak: <?= date('d-m-Y') ?></p>

<!-- Form Filter -->
<form method="get">
    <label>Tanggal Awal:</label>
    <input type="date" name="tanggal_awal" value="<?= $tanggalAwal ?>">

    <label>Tanggal Akhir:</label>
    <input type="date" name="tanggal_akhir" value="<?= $tanggalAkhir ?>">

    <label>Barang:</label>
    <select name="id_barang">
        <option value="">-- Semua Barang --</option>
        <?php while($b = $barangList->fetch_assoc()): ?>
            <option value="<?= $b['id_barang'] ?>" <?= $b['id_barang']==$id_barang?'selected':'' ?>>
                <?= $b['nama_barang'] ?> (<?= $b['kode_barang'] ?>)
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit">Filter</button>
    <button type="button" onclick="window.print()">🖨️ Cetak</button>
</form>

<!-- Tabel Basterima -->
<table>
<tr>
<th>Kode Basterima</th>
<th>Tanggal</th>
<th>Penerima</th>
<th>Barang</th>
<th>Jumlah</th>
</tr>
<?php while($r=$data->fetch_assoc()): ?>
<tr>
<td><?= $r['kode_basterima'] ?></td>
<td><?= $r['tanggal'] ?></td>
<td><?= $r['penerima'] ?></td>
<td><?= $r['nama_barang'] ?> (<?= $r['kode_barang'] ?>)</td>
<td><?= $r['jumlah'] ?></td>
</tr>
<?php endwhile; ?>
</table>


</body>
</html>
