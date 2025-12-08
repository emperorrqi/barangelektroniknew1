<?php
include 'config.php';

// Handle form submission
if(isset($_POST['submit'])) {
    $kode_pesanan = $_POST['kode_pesanan'];
    $tanggal      = $_POST['tanggal'];
    $id_barang    = $_POST['id_barang'];
    $jumlah       = $_POST['jumlah'];

    $stmt = $conn->prepare("INSERT INTO trx_barang_pesanan (kode_pesanan, tanggal, id_barang, jumlah) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $kode_pesanan, $tanggal, $id_barang, $jumlah);
    $stmt->execute();
    $stmt->close();
    header("Location: barang_pesanan.php");
}

// Ambil data barang untuk dropdown
$barang = $conn->query("SELECT id_barang, nama_barang FROM master_barang_elektronik");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Entry Barang Pesanan</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
<div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Entry Barang Pesanan</h1>

    <form method="POST" class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="text" name="kode_pesanan" placeholder="Kode Pesanan" required class="border p-2 rounded w-full">
        <input type="date" name="tanggal" required class="border p-2 rounded w-full">
        <select name="id_barang" required class="border p-2 rounded w-full">
            <option value="">Pilih Barang</option>
            <?php while($b = $barang->fetch_assoc()): ?>
                <option value="<?= $b['id_barang'] ?>"><?= $b['nama_barang'] ?></option>
            <?php endwhile; ?>
        </select>
        <input type="number" name="jumlah" placeholder="Jumlah" min="1" required class="border p-2 rounded w-full">
        <button type="submit" name="submit" class="bg-blue-600 text-white px-4 py-2 rounded col-span-full">Simpan</button>
    </form>

    <table class="w-full border-collapse border border-gray-300">
        <thead class="bg-gray-200">
            <tr>
                <th class="border p-2">No</th>
                <th class="border p-2">Kode Pesanan</th>
                <th class="border p-2">Tanggal</th>
                <th class="border p-2">Barang</th>
                <th class="border p-2">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $data = $conn->query("SELECT p.*, b.nama_barang FROM trx_barang_pesanan p JOIN master_barang_elektronik b ON p.id_barang = b.id_barang ORDER BY id_pesanan DESC");
            $no = 1;
            while($d = $data->fetch_assoc()):
            ?>
            <tr>
                <td class="border p-2"><?= $no++ ?></td>
                <td class="border p-2"><?= $d['kode_pesanan'] ?></td>
                <td class="border p-2"><?= $d['tanggal'] ?></td>
                <td class="border p-2"><?= $d['nama_barang'] ?></td>
                <td class="border p-2"><?= $d['jumlah'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
