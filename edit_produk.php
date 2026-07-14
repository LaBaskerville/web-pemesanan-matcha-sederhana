<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';
include 'auth.php';
require_admin();

// ------------------------------------------------------------
// Proses simpan perubahan (submit form edit)
// ------------------------------------------------------------
if (isset($_POST['simpan_edit'])) {
    $id        = $_POST['id_produk'];
    $nama      = trim($_POST['nama_produk']);
    $deskripsi = trim($_POST['deskripsi']);
    $harga     = $_POST['harga'];
    $stok      = $_POST['stok'];

    if ($nama === '' || !is_numeric($harga) || $harga < 0 || !is_numeric($stok) || $stok < 0) {
        echo "<script>alert('Data produk tidak valid!'); window.location='semantik.php';</script>";
        exit();
    }

    $stmt = $conn->prepare("UPDATE produk SET nama_produk = ?, deskripsi = ?, harga = ?, stok = ? WHERE id = ?");
    $stmt->bind_param("ssdii", $nama, $deskripsi, $harga, $stok, $id);

    if ($stmt->execute()) {
        header("location:semantik.php?pesan=produk_diupdate");
        exit();
    } else {
        echo "Error: " . $conn->error;
        exit();
    }
}

// ------------------------------------------------------------
// Tampilkan form edit (dipanggil lewat ?id=...)
// ------------------------------------------------------------
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM produk WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$produk = $stmt->get_result()->fetch_assoc();

if (!$produk) {
    echo "Produk tidak ditemukan.";
    echo "<br><a href='semantik.php'>Kembali</a>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk</title>
    <link rel="stylesheet" href="semantik.css">
    <style>
        body { display:block; }
        .form-container { margin: 60px auto; }
    </style>
</head>
<body>
    <div class="form-container">
        <h3>Edit Produk</h3>
        <form action="edit_produk.php" method="POST">
            <input type="hidden" name="id_produk" value="<?= htmlspecialchars($produk['id']) ?>">

            <label>Nama Produk:</label>
            <input type="text" name="nama_produk" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required>

            <label>Deskripsi:</label>
            <textarea name="deskripsi" rows="3"><?= htmlspecialchars($produk['deskripsi'] ?? '') ?></textarea>

            <label>Harga (Rp):</label>
            <input type="number" step="0.01" min="0" name="harga" value="<?= htmlspecialchars($produk['harga']) ?>" required>

            <label>Stok:</label>
            <input type="number" min="0" name="stok" value="<?= htmlspecialchars($produk['stok']) ?>" required>

            <button type="submit" name="simpan_edit">Simpan Perubahan</button>
        </form>
        <p><a href="semantik.php" style="color:white;">&larr; Batal, kembali ke halaman utama</a></p>
    </div>
</body>
</html>
