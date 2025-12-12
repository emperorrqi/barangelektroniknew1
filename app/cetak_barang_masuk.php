<?php
include 'koneksi.php';

// Ambil filter dari GET
$kode_masuk = $_GET['kode_masuk'] ?? '';
$hariIni = isset($_GET['hari_ini']) ? true : false;
$id_admin = $_GET['id_admin'] ?? '';

$where = '';
$today = date('Y-m-d');

// Judul default
$judul = "Cetak Barang Masuk";

// Filter Hari Ini
if ($hariIni) {
    $where .= "WHERE m.tanggal = '$today'";
    $judul = "Barang Masuk ($today)";
}

// Filter Kode Masuk
if ($kode_masuk != '') {
    $kode_esc = $mysqli->real_escape_string($kode_masuk);
    $where .= ($where == '' ? "WHERE " : " AND ") . "m.kode_masuk LIKE '%$kode_esc%'";
}

// Filter Admin
if ($id_admin != '') {
    $id_admin_int = (int)$id_admin;
    $where .= ($where == '' ? "WHERE " : " AND ") . "m.id_admin = $id_admin_int";
}

// Ambil daftar admin untuk dropdown
$admin = $mysqli->query("SELECT * FROM master_administrasi ORDER BY nama_admin ASC");

// Ambil data barang masuk + join kode admin
$data = $mysqli->query("
    SELECT m.*, 
           b.nama_barang, b.kode_barang, b.spesifikasi,
           v.nama_vendor,
           a.nama_admin, a.kode_admin
    FROM trx_barang_masuk m
    LEFT JOIN master_barang_elektronik b ON m.id_barang = b.id_barang
    LEFT JOIN master_vendor v ON m.id_vendor = v.id_vendor
    LEFT JOIN master_administrasi a ON m.id_admin = a.id_admin
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
input, select { padding:6px; margin-right:10px; }
button { padding:6px 12px; cursor:pointer; background:#007bff; color:white; border:none; border-radius:5px; }
button:hover { background:#0056b3; }
table { width:100%; border-collapse:collapse; }
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

<!-- FORM FILTER -->
<form method="get" class="no-print">
    <label>Kode Masuk:</label>
    <input type="text" name="kode_masuk" value="<?= htmlspecialchars($kode_masuk) ?>" placeholder="Kosongkan untuk semua">

    <label>
        <input type="checkbox" name="hari_ini" value="1" <?= $hariIni ? 'checked' : '' ?>> Hari Ini
    </label>

    <label>Pilih Admin:</label>
    <select name="id_admin">
        <option value="">-- Semua Admin --</option>
        <?php while($a = $admin->fetch_assoc()): ?>
            <option value="<?= $a['id_admin'] ?>" <?= ($id_admin == $a['id_admin']) ? 'selected' : '' ?>>
                <?= $a['kode_admin'] ?> - <?= $a['nama_admin'] ?>
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit">Filter</button>
    <button type="button" onclick="window.print()">🖨️ Print</button>
</form>

<!-- TABEL -->
<table>
    <tr>
        <th>No</th>
        <th>Kode Masuk</th>
        <th>Tanggal</th>
        <th>Admin</th>
        <th>Barang</th>
        <th>Vendor</th>
        <th>Jumlah</th>
    </tr>

    <?php $no=1; while($row = $data->fetch_assoc()): ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><?= $row['kode_masuk'] ?></td>
        <td><?= $row['tanggal'] ?></td>

        <td>
            <?= $row['kode_admin'] ?> - <?= $row['nama_admin'] ?>
        </td>

        <td>
            <?= $row['kode_barang'] ?> - <?= $row['nama_barang'] ?>
            <br><small><?= $row['spesifikasi'] ?></small>
        </td>

        <td><?= $row['nama_vendor'] ?></td>

        <td><?= $row['jumlah'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
