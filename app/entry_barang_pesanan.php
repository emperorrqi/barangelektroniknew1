<?php
include 'koneksi.php';

// ==========================
// Generate Kode Pesanan Otomatis
// ==========================
function generateKodePesanan($mysqli) {
    $result = $mysqli->query("SELECT MAX(kode_pesanan) AS max_kode FROM trx_barang_pesanan");
    $row = $result->fetch_assoc();
    $last = $row['max_kode'];

    if ($last) {
        $num = (int)substr($last, 3) + 1;
        return "PSN" . str_pad($num, 3, "0", STR_PAD_LEFT);
    } else {
        return "PSN001";
    }
}

$kode_pesanan = generateKodePesanan($mysqli);

// ==========================
// Tambah Data Pesanan
// ==========================
if (isset($_POST['tambah'])) {
    $kode = $_POST['kode_pesanan'];
    $tanggal = $_POST['tanggal'];
    $id_barang = $_POST['id_barang'];
    $id_gudang = $_POST['id_gudang'];
    $id_admin = $_POST['id_admin'];
    $jumlah = $_POST['jumlah'];

    $stmt = $mysqli->prepare("INSERT INTO trx_barang_pesanan (kode_pesanan, tanggal, id_barang, id_gudang, id_admin, jumlah) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiiii", $kode, $tanggal, $id_barang, $id_gudang, $id_admin, $jumlah);
    $stmt->execute();
    $stmt->close();

    header("Location: entry_barang_pesanan.php");
    exit;
}

// ==========================
// Hapus Data Pesanan
// ==========================
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $mysqli->prepare("DELETE FROM trx_barang_pesanan WHERE id_pesanan=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: entry_barang_pesanan.php");
    exit;
}

// ==========================
// Ambil Data Pesanan Join
// ==========================
$data = $mysqli->query("
    SELECT p.*, b.nama_barang, b.kode_barang, b.spesifikasi, g.nama_gudang
    FROM trx_barang_pesanan p
    LEFT JOIN master_barang_elektronik b ON p.id_barang = b.id_barang
    LEFT JOIN master_gudang g ON p.id_gudang = g.id_gudang
    ORDER BY p.id_pesanan DESC
");

// Dropdown barang & gudang
$barang = $mysqli->query("SELECT * FROM master_barang_elektronik ORDER BY nama_barang ASC");
$gudang = $mysqli->query("SELECT * FROM master_gudang ORDER BY nama_gudang ASC");

// Default admin
$id_admin = 1;
?>

<!DOCTYPE html>
<html>
<head>
    <title>📦 Entry Barang Pesanan</title>
    <style>
        body { font-family: Arial; background:#eef0f2; padding:25px; }
        .card { background:white; padding:25px; border-radius:12px; width:750px; margin:auto; box-shadow:0 3px 15px rgba(0,0,0,0.1); }
        input, select { width:100%; padding:10px; margin:7px 0 14px; border-radius:6px; border:1px solid #bbb; }
        button { padding:12px 20px; border:none; background:#007bff; color:white; border-radius:6px; cursor:pointer; font-size:15px; }
        button:hover { background:#0056b3; }
        table { width:100%; border-collapse:collapse; margin-top:25px; background:white; }
        th,td { border:1px solid #ddd; padding:10px; text-align:left; }
        th { background:#007bff; color:white; }
        .btn-del { color:red; font-weight:bold; text-decoration:none; }
        .btn-del:hover { text-decoration:underline; }
        .btn-back, .btn-print { display:inline-block; margin-bottom:15px; padding:10px 18px; color:white; border-radius:6px; text-decoration:none; }
        .btn-back { background:#6c757d; }
        .btn-back:hover { background:#495057; }
        .btn-print { background:#28a745; }
        .btn-print:hover { background:#218838; }
    </style>
</head>
<body>

<div class="card">
    <h2>📦 Entry Barang Pesanan</h2>

    <a href="index.php" class="btn-back">⬅ Kembali ke Halaman Utama</a>
    <a href="cetak_barang_pesanan.php" target="_blank" class="btn-print">🖨️ Cetak Pesanan</a>

    <form method="post">
        <label>Kode Pesanan</label>
        <input type="text" name="kode_pesanan" value="<?= $kode_pesanan ?>" readonly>

        <label>Tanggal</label>
        <input type="date" name="tanggal" required>

        <label>Pilih Barang</label>
        <select name="id_barang" required>
            <option value="">-- Pilih Barang Elektronik --</option>
            <?php while($b = $barang->fetch_assoc()): ?>
                <option value="<?= $b['id_barang'] ?>"><?= $b['kode_barang'] ?> - <?= $b['nama_barang'] ?></option>
            <?php endwhile; ?>
        </select>

        <label>Pilih Gudang</label>
        <select name="id_gudang" required>
            <option value="">-- Pilih Gudang --</option>
            <?php while($g = $gudang->fetch_assoc()): ?>
                <option value="<?= $g['id_gudang'] ?>"><?= $g['nama_gudang'] ?></option>
            <?php endwhile; ?>
        </select>

        <label>Jumlah Barang</label>
        <input type="number" name="jumlah" min="1" required>

        <input type="hidden" name="id_admin" value="<?= $id_admin ?>">

        <button name="tambah">+ Tambah Pesanan</button>
    </form>

    <h3>📄 Data Barang Pesanan</h3>
    <table>
        <tr>
            <th>Kode Pesanan</th>
            <th>Tanggal</th>
            <th>Barang</th>
            <th>Gudang</th>
            <th>Jumlah</th>
            <th>Aksi</th>
        </tr>

        <?php while ($row = $data->fetch_assoc()): ?>
        <tr>
            <td><?= $row['kode_pesanan'] ?></td>
            <td><?= $row['tanggal'] ?></td>
            <td><?= $row['kode_barang'] ?> - <?= $row['nama_barang'] ?><br><small><?= $row['spesifikasi'] ?></small></td>
            <td><?= $row['nama_gudang'] ?></td>
            <td><?= $row['jumlah'] ?></td>
            <td>
                <a class="btn-del" href="entry_barang_pesanan.php?hapus=<?= $row['id_pesanan'] ?>" onclick="return confirm('Hapus data?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
