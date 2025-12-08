<?php
include 'koneksi.php';

// Ambil filter tanggal (opsional)
$tanggal_awal = $_GET['tanggal_awal'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';

// Query untuk ambil data pesanan dan basterima
$query = "
    SELECT 
        b.kode_barang, 
        b.nama_barang, 
        SUM(bp.jumlah) AS total_pesanan,
        SUM(IFNULL(br.jumlah,0)) AS total_basterima
    FROM master_barang_elektronik b
    LEFT JOIN trx_barang_pesanan bp ON bp.id_barang = b.id_barang
    LEFT JOIN trx_berita_serah_terima br ON br.id_barang = b.id_barang
    WHERE 1=1
";

if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    $query .= " AND bp.tanggal BETWEEN '".$mysqli->real_escape_string($tanggal_awal)."' AND '".$mysqli->real_escape_string($tanggal_akhir)."'";
}

$query .= "
    GROUP BY b.id_barang
    ORDER BY b.nama_barang ASC
";

$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Barang Pesanan vs Basterima</title>
<style>
    body { font-family: 'Segoe UI', sans-serif; background:#f4f6f8; padding:20px; }
    .container { max-width: 900px; margin:auto; background:white; padding:25px; border-radius:12px; box-shadow:0 3px 10px rgba(0,0,0,0.1);}
    h2 { text-align:center; color:#333; margin-bottom:20px; }
    table { width:100%; border-collapse:collapse; margin-top:20px; }
    th, td { padding:10px; border:1px solid #ddd; text-align:center; }
    th { background:#3498db; color:white; }
    .no-print { margin-bottom:15px; text-align:center; }
    .btn { padding:8px 15px; background:#3498db; color:white; text-decoration:none; border-radius:6px; font-weight:bold; margin:2px; display:inline-block; }
    .btn:hover { background:#2980b9; }
    @media print { .no-print { display:none; } }
</style>
</head>
<body>
<div class="container">
    <h2>📊 Laporan: Jumlah Barang Pesanan vs Basterima</h2>

    <div class="no-print">
        <!-- Tombol Kembali -->
        <a href="index.php" class="btn">⬅ Kembali ke Halaman Utama</a>

        <form method="GET" style="text-align:center; margin-top:10px;">
            <label>Tanggal Awal:</label>
            <input type="date" name="tanggal_awal" value="<?= htmlspecialchars($tanggal_awal) ?>">
            <label>Tanggal Akhir:</label>
            <input type="date" name="tanggal_akhir" value="<?= htmlspecialchars($tanggal_akhir) ?>">
            <button type="submit" class="btn">🔍 Filter</button>
            <a href="#" onclick="window.print()" class="btn">🖨️ Cetak</a>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Jumlah Pesanan</th>
                <th>Jumlah Basterima</th>
                <th>Sisa</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['kode_barang']) ?></td>
                        <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                        <td><?= $row['total_pesanan'] ?? 0 ?></td>
                        <td><?= $row['total_basterima'] ?? 0 ?></td>
                        <td><?= ($row['total_pesanan'] - $row['total_basterima']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="color:gray;">Data tidak ditemukan.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
