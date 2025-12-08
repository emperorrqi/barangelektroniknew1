<?php
include 'koneksi.php';

// =========================
// Fungsi Generate Kode Retur Otomatis
// =========================
function generateKodeRetur($mysqli) {
    $result = $mysqli->query("SELECT MAX(kode_retur) AS max_kode FROM trx_retur");
    $data = $result->fetch_assoc();
    $maxKode = $data['max_kode'];

    if ($maxKode) {
        $num = (int) substr($maxKode, 2);
        $num++;
        return "RT" . str_pad($num, 3, "0", STR_PAD_LEFT);
    } else {
        return "RT001";
    }
}

$kodeReturOtomatis = generateKodeRetur($mysqli);

// =========================
// Tambah Data Retur
// =========================
if (isset($_POST['tambah'])) {
    $kode_retur = $_POST['kode_retur'];
    $tanggal = $_POST['tanggal'];
    $id_barang = $_POST['id_barang']; 
    $jumlah = $_POST['jumlah'];
    $alasan = $_POST['alasan'];

    $stmt = $mysqli->prepare("INSERT INTO trx_retur (kode_retur, tanggal, id_barang, jumlah, alasan)
                              VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiis", $kode_retur, $tanggal, $id_barang, $jumlah, $alasan);

    if ($stmt->execute()) {
        header("Location: entry_retur_barang.php?success=1");
        exit;
    } else {
        echo "Error: " . $mysqli->error;
    }
}

// Ambil daftar barang
$barang = $mysqli->query("SELECT * FROM master_barang_elektronik ORDER BY nama_barang ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Entry Retur Barang</title>
<style>
    body { font-family: Arial; background: #eef1f7; padding: 25px; }
    .container {
        width: 750px; margin: auto; background: #fff;
        padding: 25px; border-radius: 12px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    h2, h3 { margin-bottom: 10px; color: #2c3e50; }
    label { font-weight: bold; margin-top: 10px; display: block; }
    input, select, textarea {
        width: 100%; padding: 10px; margin-top: 5px;
        border-radius: 6px; border: 1px solid #cfcfcf;
    }
    button {
        background: #e74c3c; padding: 10px 18px; color: white;
        border: none; margin-top: 12px; border-radius: 6px;
        cursor: pointer; font-weight: bold; transition: 0.2s;
    }
    button:hover { background: #c0392b; }

    table { width: 100%; margin-top: 25px; border-collapse: collapse; font-size: 14px; }
    th { background: #e74c3c; color: white; padding: 9px; }
    td { padding: 8px; border: 1px solid #ddd; text-align: center; }

    .alert-success {
        background: #2ecc71; color: white; padding: 10px;
        margin-bottom: 15px; border-radius: 6px;
    }

    .btn-back, .btn-print {
        display:inline-block;margin-bottom:15px;padding:8px 16px;
        color:white;border-radius:6px;text-decoration:none;font-weight:bold;
    }
    .btn-back { background:#6c757d; }
    .btn-back:hover { background:#495057; }

    .btn-print { background:#28a745; }
    .btn-print:hover { background:#218838; }
</style>
</head>
<body>
<div class="container">

    <h2>Entry Retur Barang</h2>

    <a href="index.php" class="btn-back">⬅ Kembali ke Halaman Utama</a>
    <a href="cetak_retur_barang.php" target="_blank" class="btn-print">🖨️ Cetak Retur</a>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert-success">✔ Data berhasil disimpan!</div>
    <?php endif; ?>

    <form method="POST">
        <label>Kode Retur</label>
        <input type="text" name="kode_retur" value="<?= $kodeReturOtomatis ?>" readonly>

        <label>Tanggal</label>
        <input type="date" name="tanggal" required>

        <label>Barang</label>
        <select name="id_barang" required>
            <option value="">-- Pilih Barang --</option>
            <?php while ($b = $barang->fetch_assoc()): ?>
                <option value="<?= $b['id_barang'] ?>">
                    <?= $b['nama_barang'] ?> (<?= $b['kode_barang'] ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <label>Jumlah</label>
        <input type="number" name="jumlah" min="1" required>

        <label>Alasan</label>
        <textarea name="alasan" rows="3" placeholder="Alasan retur..." required></textarea>

        <button type="submit" name="tambah">Simpan</button>
    </form>

    <hr>

    <h3>Data Retur Barang</h3>

    <table>
        <tr>
            <th>Kode Retur</th>
            <th>Tanggal</th>
            <th>Barang</th>
            <th>Jumlah</th>
            <th>Alasan</th>
        </tr>

        <?php
        $sql = "SELECT r.*, b.nama_barang, b.kode_barang
                FROM trx_retur r
                JOIN master_barang_elektronik b ON r.id_barang = b.id_barang
                ORDER BY r.id_retur DESC";
        $result = $mysqli->query($sql);
        while ($row = $result->fetch_assoc()):
        ?>
        <tr>
            <td><?= $row['kode_retur'] ?></td>
            <td><?= $row['tanggal'] ?></td>
            <td><?= $row['nama_barang'] ?> (<?= $row['kode_barang'] ?>)</td>
            <td><?= $row['jumlah'] ?></td>
            <td><?= $row['alasan'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

</div>
</body>
</html>
