<?php
include 'koneksi.php';

// Ambil semua data retur
$sql = "SELECT r.*, b.kode_barang, b.nama_barang
        FROM trx_retur r
        JOIN master_barang_elektronik b ON r.id_barang = b.id_barang
        ORDER BY r.id_retur DESC";
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Cetak Retur Barang</title>
<style>
    body { font-family: Arial; padding: 20px; }
    h2 { text-align: center; text-transform: uppercase; margin-bottom: 5px; }
    h4 { text-align: center; margin-top: 0; font-weight: normal; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #000; padding: 8px; font-size: 14px; text-align: center; }
    th { background: #eee; }
    .btn-print {
        display: inline-block; margin-bottom: 15px; padding: 8px 16px;
        background:#28a745; color:white; border-radius:6px; text-decoration:none; 
        font-weight:bold;
    }
    .btn-print:hover { background:#1e7e34; }
    @media print {
        .btn-print { display: none; }
    }
</style>
</head>
<body>

<a href="#" onclick="window.print()" class="btn-print">🖨️ Print Halaman</a>

<h2>LAPORAN RETUR BARANG</h2>
<h4>Sistem Informasi Manajemen Barang Elektronik</h4>
<hr>

<table>
    <tr>
        <th>Kode Retur</th>
        <th>Tanggal</th>
        <th>Barang</th>
        <th>Jumlah</th>
        <th>Alasan</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['kode_retur'] ?></td>
        <td><?= $row['tanggal'] ?></td>
        <td><?= $row['nama_barang'] ?> (<?= $row['kode_barang'] ?>)</td>
        <td><?= $row['jumlah'] ?></td>
        <td><?= $row['alasan'] ?></td>
    </tr>
    <?php endwhile; ?>

</table>

<br><br><br>

<table style="width: 100%; border: none;">
    <tr>
        <td style="border:none; width:70%;"></td>
        <td style="border:none; text-align:center;">
            <p><?= date('d-m-Y') ?></p>
            <p><b>Mengetahui,</b></p>
            <br><br><br>
            <p>(_________________________)</p>
        </td>
    </tr>
</table>

</body>
</html>
