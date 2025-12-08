<?php
include 'koneksi.php';

// Ambil daftar driver untuk filter
$driverList = $mysqli->query("SELECT id_driver, nama_driver, kode_driver FROM master_driver ORDER BY nama_driver ASC");

// Inisialisasi filter
$tanggalAwal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : '';
$tanggalAkhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : '';
$id_driver = isset($_GET['id_driver']) ? $_GET['id_driver'] : '';

// Bangun query
$sql = "
    SELECT s.*, d.nama_driver, d.kode_driver, 
           g.nama_gudang, g.kode_gudang, g.lokasi
    FROM trx_surat_jalan s
    JOIN master_driver d ON s.id_driver = d.id_driver
    JOIN master_gudang g ON s.id_gudang = g.id_gudang
    WHERE 1=1
";

if ($tanggalAwal != '') $sql .= " AND s.tanggal >= '$tanggalAwal'";
if ($tanggalAkhir != '') $sql .= " AND s.tanggal <= '$tanggalAkhir'";
if ($id_driver != '') $sql .= " AND s.id_driver = $id_driver";

$sql .= " ORDER BY s.id_surat DESC";

$data = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Cetak Surat Jalan</title>
<style>
table{width:100%;border-collapse:collapse;margin-top:15px;}
th,td{border:1px solid #000;padding:8px;text-align:center;}
th{background:#3498db;color:white;}
form{margin-bottom:15px;}
input, select{padding:5px;margin-right:10px;}
button{padding:6px 12px;background:#28a745;color:white;border:none;border-radius:4px;cursor:pointer;}
button:hover{background:#218838;}
</style>
</head>
<body>

<h2>Daftar Surat Jalan</h2>
<p>Tanggal Cetak: <?= date('d-m-Y') ?></p>

<!-- Form Filter -->
<form method="get">
    <label>Tanggal Awal:</label>
    <input type="date" name="tanggal_awal" value="<?= $tanggalAwal ?>">

    <label>Tanggal Akhir:</label>
    <input type="date" name="tanggal_akhir" value="<?= $tanggalAkhir ?>">

    <label>Driver:</label>
    <select name="id_driver">
        <option value="">-- Semua Driver --</option>
        <?php while($d = $driverList->fetch_assoc()): ?>
            <option value="<?= $d['id_driver'] ?>" <?= $d['id_driver']==$id_driver?'selected':'' ?>>
                <?= $d['nama_driver'] ?> (<?= $d['kode_driver'] ?>)
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit">Filter</button>
    <button type="button" onclick="window.print()">🖨️ Cetak</button>
</form>

<!-- Tabel Surat Jalan -->
<table>
<tr>
    <th>Kode Surat</th>
    <th>Tanggal</th>
    <th>Driver</th>
    <th>Gudang</th>
    <th>Keterangan</th>
</tr>

<?php while($r = $data->fetch_assoc()): ?>
<tr>
    <td><?= $r['kode_surat'] ?></td>
    <td><?= $r['tanggal'] ?></td>
    <td><?= $r['nama_driver'] ?> (<?= $r['kode_driver'] ?>)</td>
    <td><?= $r['kode_gudang'] ?> - <?= $r['lokasi'] ?></td>
    <td><?= $r['keterangan'] ?></td>
</tr>
<?php endwhile; ?>

</table>

</body>
</html>
