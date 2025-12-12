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
    $id_admin = $_POST['id_admin']; // NEW FIELD
    $id_barang = $_POST['id_barang'];
    $id_gudang = $_POST['id_gudang'];
    $jumlah = $_POST['jumlah'];
    $serial_number = $_POST['serial_number'];

    // ==========================
    // Validasi stok
    // ==========================
    $stokQuery = $mysqli->prepare("
        SELECT SUM(jumlah) AS total_masuk
        FROM trx_barang_masuk
        WHERE id_barang=?
    ");
    $stokQuery->bind_param("i", $id_barang);
    $stokQuery->execute();
    $stokResult = $stokQuery->get_result()->fetch_assoc();
    $stokQuery->close();

    $stok_tersedia = $stokResult['total_masuk'] ?? 0;

    $pesananQuery = $mysqli->prepare("
        SELECT SUM(jumlah) AS total_pesanan
        FROM trx_barang_pesanan
        WHERE id_barang=?
    ");
    $pesananQuery->bind_param("i", $id_barang);
    $pesananQuery->execute();
    $pesananResult = $pesananQuery->get_result()->fetch_assoc();
    $pesananQuery->close();

    $total_pesanan = $pesananResult['total_pesanan'] ?? 0;

    $stok_sisa = $stok_tersedia - $total_pesanan;

    if ($jumlah > $stok_sisa) {
        echo "<script>alert('Jumlah pesanan melebihi stok tersedia ($stok_sisa)'); window.history.back();</script>";
        exit;
    }

    // ==========================
    // Insert data pesanan (termasuk id_admin)
    // ==========================
    $stmt = $mysqli->prepare("
        INSERT INTO trx_barang_pesanan 
        (kode_pesanan, tanggal, id_admin, id_barang, id_gudang, jumlah, serial_number) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssiiiss", $kode, $tanggal, $id_admin, $id_barang, $id_gudang, $jumlah, $serial_number);
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
// Ambil Data Join
// ==========================
$data = $mysqli->query("
    SELECT p.*, 
           b.nama_barang, b.kode_barang, b.spesifikasi, 
           g.nama_gudang,
           a.nama_admin, a.kode_admin
    FROM trx_barang_pesanan p
    LEFT JOIN master_barang_elektronik b ON p.id_barang = b.id_barang
    LEFT JOIN master_gudang g ON p.id_gudang = g.id_gudang
    LEFT JOIN master_administrasi a ON p.id_admin = a.id_admin
    ORDER BY p.id_pesanan DESC
");

// Dropdown data
$barang = $mysqli->query("SELECT * FROM master_barang_elektronik ORDER BY nama_barang ASC");
$gudang = $mysqli->query("SELECT * FROM master_gudang ORDER BY nama_gudang ASC");
$admin  = $mysqli->query("SELECT * FROM master_administrasi ORDER BY nama_admin ASC");

?>

<!DOCTYPE html>
<html>
<head>
    <title>📦 Entry Barang Pesanan</title>
    <style>
        body { font-family: Arial; background:#eef0f2; padding:25px; }
        .card { background:white; padding:25px; border-radius:12px; width:750px; margin:auto; box-shadow:0 3px 15px rgba(0,0,0,0.1); }
        input, select, textarea { width:100%; padding:10px; margin:7px 0 14px; border-radius:6px; border:1px solid #bbb; }
        table { width:100%; border-collapse:collapse; margin-top:25px; background:white; }
        th,td { border:1px solid #ddd; padding:10px; text-align:left; }
        th { background:#007bff; color:white; }
        button { padding:12px 20px; border:none; background:#007bff; color:white; border-radius:6px; cursor:pointer; }
        button:hover { background:#0056b3; }
        .btn-del { color:red; font-weight:bold; text-decoration:none; }
        .btn-del:hover { text-decoration:underline; }
        .btn-back, .btn-print { display:inline-block; margin-bottom:15px; padding:10px 18px; color:white; border-radius:6px; text-decoration:none; }
        .btn-back { background:#6c757d; }
        .btn-print { background:#28a745; }
    </style>
</head>
<body>

<div class="card">
    <h2>📦 Entry Barang Pesanan</h2>

    <a href="index.php" class="btn-back">⬅ Kembali ke Halaman Utama</a>
    <a href="export_serial_number.php" class="btn-print">⬇ Export Serial Number CSV</a>
    <a href="cetak_barang_pesanan.php" target="_blank" class="btn-print">🖨️ Cetak Pesanan</a>

    <!-- FORM INPUT -->
    <form method="post">
        <label>Kode Pesanan</label>
        <input type="text" name="kode_pesanan" value="<?= $kode_pesanan ?>" readonly>

        <label>Tanggal</label>
        <input type="date" name="tanggal" required>

        <label>Admin barang pesanan</label>
        <select name="id_admin" required>
            <option value="">-- Pilih Admin Entry --</option>
            <?php while($a = $admin->fetch_assoc()): ?>
                <option value="<?= $a['id_admin'] ?>">
                    <?= $a['kode_admin'] ?> - <?= $a['nama_admin'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Pilih Barang</label>
        <select name="id_barang" required>
            <option value="">-- Pilih Barang Elektronik --</option>
            <?php while($b = $barang->fetch_assoc()): ?>
                <option value="<?= $b['id_barang'] ?>">
                    <?= $b['kode_barang'] ?> - <?= $b['nama_barang'] ?>
                </option>
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

        <label>Serial Number</label>
        <textarea name="serial_number" rows="3" placeholder="Isi serial number jika ada"></textarea>

        <button name="tambah">+ Tambah Pesanan</button>
    </form>

    <!-- TABLE -->
    <h3>📄 Data Barang Pesanan</h3>
    <table>
        <tr>
            <th>Kode Pesanan</th>
            <th>Tanggal</th>
            <th>Admin</th>
            <th>Barang</th>
            <th>Gudang</th>
            <th>Jumlah</th>
            <th>Serial Number</th>
            <th>Aksi</th>
        </tr>

        <?php while ($row = $data->fetch_assoc()): ?>
        <tr>
            <td><?= $row['kode_pesanan'] ?></td>
            <td><?= $row['tanggal'] ?></td>

            <td><?= $row['kode_admin'] ?> - <?= $row['nama_admin'] ?></td>

            <td>
                <?= $row['kode_barang'] ?> - <?= $row['nama_barang'] ?>
                <br><small><?= $row['spesifikasi'] ?></small>
            </td>

            <td><?= $row['nama_gudang'] ?></td>
            <td><?= $row['jumlah'] ?></td>
            <td><?= $row['serial_number'] ?></td>

            <td>
                <a class="btn-del" href="entry_barang_pesanan.php?hapus=<?= $row['id_pesanan'] ?>" 
                   onclick="return confirm('Hapus data?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
