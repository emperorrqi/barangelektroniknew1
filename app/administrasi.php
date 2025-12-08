<?php
include 'koneksi.php';

// ===== Generate Kode Admin =====
function generateKodeAdmin($mysqli) {
    $r = $mysqli->query("SELECT MAX(kode_admin) AS kode FROM master_administrasi");
    $d = $r->fetch_assoc();
    if ($d['kode']) {
        $num = intval(substr($d['kode'], 3)) + 1;
        return "ADM" . str_pad($num, 3, "0", STR_PAD_LEFT);
    }
    return "ADM001";
}

// ===== Tambah Admin =====
if (isset($_POST['tambah'])) {
    $kode = generateKodeAdmin($mysqli);
    $nama = $_POST['nama_admin'];

    $mysqli->query("INSERT INTO master_administrasi (kode_admin, nama_admin)
                    VALUES ('$kode', '$nama')");

    header("Location: administrasi.php");
    exit;
}

// ===== Hapus Admin =====
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $mysqli->query("DELETE FROM master_administrasi WHERE id_admin=$id");
    header("Location: administrasi.php");
    exit;
}

// ===== Ambil Data =====
$data = $mysqli->query("SELECT * FROM master_administrasi ORDER BY kode_admin ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Administrasi</title>

<style>
    body {
        font-family: "Segoe UI", Arial, sans-serif;
        background: #f5f6fa;
        margin: 0;
        padding: 30px;
    }
    .container {
        width: 850px;
        margin: auto;
        background: #ffffff;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    h2 {
        margin-top: 0;
        font-size: 22px;
        font-weight: 600;
        color: #333;
        text-align: center;
    }

    /* Tombol Kembali */
    .btn-back {
        padding: 8px 16px;
        background:#28a745;
        color:white;
        text-decoration:none;
        border-radius:6px;
        font-size:14px;
        float: right;
        margin-bottom: 15px;
    }
    .btn-back:hover {
        background:#218838;
    }

    /* Form */
    .form-box {
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e0e0e0;
    }
    label {
        font-weight: 500;
        display: block;
        margin-bottom: 5px;
    }
    input[type=text] {
        width: 98%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        margin-bottom: 10px;
        font-size: 14px;
    }
    button {
        padding: 10px 18px;
        background: #007bff;
        border: none;
        color: white;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
    }
    button:hover {
        background: #0067da;
    }

    /* Table */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    table th {
        background: #007bff;
        color: #fff;
        padding: 12px;
        font-size: 14px;
        text-align: center;
    }
    table td {
        padding: 12px;
        font-size: 14px;
        border-bottom: 1px solid #eaeaea;
        text-align: center;
        background: #fff;
    }
    table tr:hover td {
        background: #f3f8ff;
    }

    .hapus {
        color: red;
        font-weight: bold;
        text-decoration: none;
    }
    .hapus:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>

<div class="container">
    <h2>Master Administrasi</h2>

    <!-- Tombol Kembali -->
    <a href="index.php" class="btn-back">Kembali ke Halaman Utama</a>

    <div class="form-box">
        <form method="POST">
            <label>Nama Admin</label>
            <input type="text" name="nama_admin" required>

            <button type="submit" name="tambah">Tambah Admin</button>
        </form>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Kode Admin</th>
            <th>Nama Admin</th>
            <th>Aksi</th>
        </tr>

        <?php while ($row = $data->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['id_admin'] ?></td>
            <td><?= $row['kode_admin'] ?></td>
            <td><?= $row['nama_admin'] ?></td>
            <td>
                <a class="hapus" 
                   onclick="return confirm('Yakin menghapus admin ini?')"
                   href="administrasi.php?hapus=<?= $row['id_admin'] ?>">
                   Hapus
                </a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>
