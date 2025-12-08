<?php
include 'koneksi.php';

// ==========================
// Generate Kode Surat Otomatis
// ==========================
function generateKodeSurat($mysqli) {
    $result = $mysqli->query("SELECT MAX(kode_surat) AS max_kode FROM trx_surat_jalan");
    $data = $result->fetch_assoc();
    $maxKode = $data['max_kode'];

    if ($maxKode) {
        $num = (int) substr($maxKode, 2);
        $num++;
        return "SJ" . str_pad($num, 3, "0", STR_PAD_LEFT);
    } else {
        return "SJ001";
    }
}

$kodeSuratOtomatis = generateKodeSurat($mysqli);

// ==========================
// Tambah Data
// ==========================
if (isset($_POST['tambah'])) {
    $kode_surat = $_POST['kode_surat'];
    $tanggal = $_POST['tanggal'];
    $id_driver = $_POST['id_driver'];
    $id_gudang = $_POST['id_gudang'];
    $keterangan = $_POST['keterangan'];
    $id_barang = $_POST['id_barang'];
    $jumlah = (int)$_POST['jumlah'];

    // Insert Surat Jalan
    $stmt = $mysqli->prepare("INSERT INTO trx_surat_jalan (kode_surat, tanggal, id_driver, id_gudang, keterangan)
                              VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiis", $kode_surat, $tanggal, $id_driver, $id_gudang, $keterangan);
    $stmt->execute();
    $id_surat = $mysqli->insert_id;

    // Insert Detail
    $stmt2 = $mysqli->prepare("INSERT INTO trx_surat_jalan_detail (id_surat, id_barang, jumlah)
                               VALUES (?, ?, ?)");
    $stmt2->bind_param("iii", $id_surat, $id_barang, $jumlah);
    $stmt2->execute();

    $stmt->close();
    $stmt2->close();

    header("Location: entry_surat_jalan.php?success=1");
    exit;
}

// ==========================
// Hapus Data Surat
// ==========================
if (isset($_GET['hapus'])) {
    $id_surat = (int)$_GET['hapus'];
    $mysqli->query("DELETE FROM trx_surat_jalan_detail WHERE id_surat=$id_surat");
    $mysqli->query("DELETE FROM trx_surat_jalan WHERE id_surat=$id_surat");
    header("Location: entry_surat_jalan.php");
    exit;
}

// ==========================
// Ambil Data Driver & Gudang
// ==========================
$driver = $mysqli->query("SELECT * FROM master_driver ORDER BY nama_driver ASC");
$gudang = $mysqli->query("SELECT * FROM master_gudang ORDER BY nama_gudang ASC");

// ==========================
// Ambil Barang dari BASTerima (Sisa Stok)
// ==========================
$barangBasterima = $mysqli->query("
    SELECT b.id_barang, b.kode_barang, b.nama_barang,
           SUM(bs.jumlah) AS total_terima,
           IFNULL((SELECT SUM(jumlah) FROM trx_surat_jalan_detail WHERE id_barang = bs.id_barang), 0) AS total_kirim
    FROM trx_berita_serah_terima bs
    JOIN master_barang_elektronik b ON bs.id_barang = b.id_barang
    GROUP BY b.id_barang
    HAVING total_terima - total_kirim > 0
    ORDER BY b.nama_barang ASC
");

// ==========================
// Ambil Data Surat Jalan + Detail Barang
// ==========================
$data = $mysqli->query("
    SELECT s.id_surat, s.kode_surat, s.tanggal, s.keterangan,
           d.nama_driver, d.kode_driver,
           g.kode_gudang, g.lokasi,
           b.kode_barang, b.nama_barang, sjd.jumlah
    FROM trx_surat_jalan s
    JOIN master_driver d ON s.id_driver = d.id_driver
    JOIN master_gudang g ON s.id_gudang = g.id_gudang
    LEFT JOIN trx_surat_jalan_detail sjd ON s.id_surat = sjd.id_surat
    LEFT JOIN master_barang_elektronik b ON sjd.id_barang = b.id_barang
    ORDER BY s.id_surat DESC
");

// Grouping manual
$listSurat = [];
while ($row = $data->fetch_assoc()) {
    $id = $row['id_surat'];
    if (!isset($listSurat[$id])) {
        $listSurat[$id] = [
            "kode_surat" => $row["kode_surat"],
            "tanggal" => $row["tanggal"],
            "keterangan" => $row["keterangan"],
            "driver" => $row["nama_driver"] . " (" . $row["kode_driver"] . ")",
            "gudang" => $row["kode_gudang"] . " - " . $row["lokasi"],
            "barang" => []
        ];
    }
    if ($row['nama_barang']) {
        $listSurat[$id]["barang"][] = [
            "nama_barang" => $row["nama_barang"],
            "kode_barang" => $row["kode_barang"],
            "jumlah" => $row["jumlah"]
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Entry Surat Jalan</title>
<style>
body { font-family: Arial; background:#eef1f7; padding:25px; }
.container { width:750px; margin:auto; background:#fff; padding:25px; border-radius:12px; box-shadow:0 3px 10px rgba(0,0,0,0.1); }
h2,h3{margin-bottom:10px;color:#2c3e50;}
label{font-weight:bold;margin-top:10px;display:block;}
input,select,textarea{width:100%;padding:10px;margin-top:5px;border-radius:6px;border:1px solid #cfcfcf;}
button{background:#3498db;padding:10px 18px;color:white;border:none;margin-top:12px;border-radius:6px;cursor:pointer;font-weight:bold;transition:0.2s;}
button:hover{background:#2980b9;}
table{width:100%;margin-top:25px;border-collapse:collapse;font-size:14px;}
th{background:#3498db;color:white;padding:9px;}
td{padding:8px;border:1px solid #ddd;}
.alert-success{background:#2ecc71;color:white;padding:10px;margin-bottom:15px;border-radius:6px;}
.btn-back {display:inline-block;margin-bottom:15px;padding:8px 16px;background:#6c757d;color:white;border-radius:6px;text-decoration:none;font-weight:bold;}
.btn-back:hover {background:#495057;}
</style>
</head>
<body>

<div class="container">

    <h2>Entry Surat Jalan</h2>

    <a href="index.php" class="btn-back">⬅ Kembali ke Halaman Utama</a>
    <a href="cetak_surat_jalan.php?id_surat=<?= $id_surat ?>" target="_blank">🖨 Cetak</a><br>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert-success">✔ Data berhasil disimpan!</div>
    <?php endif; ?>

    <form method="POST">
        <label>Kode Surat Jalan</label>
        <input type="text" name="kode_surat" value="<?= $kodeSuratOtomatis ?>" readonly>

        <label>Tanggal</label>
        <input type="date" name="tanggal" required>

        <label>Driver</label>
        <select name="id_driver" required>
            <option value="">-- Pilih Driver --</option>
            <?php while($d = $driver->fetch_assoc()): ?>
                <option value="<?= $d['id_driver'] ?>"><?= $d['nama_driver'] ?> (<?= $d['kode_driver'] ?>)</option>
            <?php endwhile; ?>
        </select>

        <label>Gudang</label>
        <select name="id_gudang" required>
            <option value="">-- Pilih Gudang --</option>
            <?php while($g = $gudang->fetch_assoc()): ?>
                <option value="<?= $g['id_gudang'] ?>"><?= $g['kode_gudang'] ?> - <?= $g['lokasi'] ?></option>
            <?php endwhile; ?>
        </select>

        <label>Pilih Barang dari BASTerima (Sisa Stok)</label>
        <select name="id_barang" required>
            <option value="">-- Pilih Barang --</option>
            <?php while($b = $barangBasterima->fetch_assoc()):
                $sisa = $b['total_terima'] - $b['total_kirim'];
            ?>
                <option value="<?= $b['id_barang'] ?>">
                    <?= $b['kode_barang'] ?> - <?= $b['nama_barang'] ?> (Tersisa: <?= $sisa ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <label>Jumlah Kirim</label>
        <input type="number" name="jumlah" min="1" required>

        <label>Keterangan</label>
        <textarea name="keterangan" rows="3"></textarea>

        <button type="submit" name="tambah">Simpan</button>
    </form>

    <hr>

    <h3>Data Surat Jalan</h3>
    <table>
        <tr>
            <th>Kode Surat</th>
            <th>Tanggal</th>
            <th>Driver</th>
            <th>Gudang</th>
            <th>Barang & Jumlah</th>
            <th>Keterangan</th>
            <th>Aksi</th>
        </tr>

        <?php foreach ($listSurat as $id_surat => $s): ?>
            <tr>
                <td><?= $s['kode_surat'] ?></td>
                <td><?= $s['tanggal'] ?></td>
                <td><?= $s['driver'] ?></td>
                <td><?= $s['gudang'] ?></td>

                <td>
                    <?php foreach ($s['barang'] as $brg): ?>
                        • <?= $brg['nama_barang'] ?> (<?= $brg['kode_barang'] ?>) — <b><?= $brg['jumlah'] ?></b><br>
                    <?php endforeach; ?>
                </td>

                <td><?= $s['keterangan'] ?></td>

                <td>
                    <a href="cetak_surat_jalan.php?id_surat=<?= $id_surat ?>" target="_blank">🖨 Cetak</a><br>
                    <a href="entry_surat_jalan.php?hapus=<?= $id_surat ?>" style="color:red;"
                       onclick="return confirm('Hapus surat ini?')">❌ Hapus</a>
                </td>
            </tr>
        <?php endforeach; ?>

    </table>

</div>

</body>
</html>
