<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';
include 'auth.php';

// Hanya user yang sudah login yang boleh mengisi keranjang
require_login();

if (isset($_POST['id_produk']) && isset($_POST['jumlah'])) {
    $id_user   = $_SESSION['user_id'];
    $id_produk = (int) $_POST['id_produk'];
    $jumlah    = (int) $_POST['jumlah'];

    if ($jumlah < 1) {
        echo "<script>alert('Jumlah pesanan minimal 1!'); window.location='semantik.php';</script>";
        exit();
    }

    // Cek ketersediaan stok produk
    $cek = $conn->prepare("SELECT stok FROM produk WHERE id = ?");
    $cek->bind_param("i", $id_produk);
    $cek->execute();
    $produk = $cek->get_result()->fetch_assoc();

    if (!$produk) {
        echo "<script>alert('Produk tidak ditemukan!'); window.location='semantik.php';</script>";
        exit();
    }

    if ($produk['stok'] < $jumlah) {
        echo "<script>alert('Maaf, stok tidak mencukupi! Sisa stok: {$produk['stok']}'); window.location='semantik.php';</script>";
        exit();
    }

    // Jika produk sudah ada di keranjang user ini, tambahkan jumlahnya.
    // Jika belum, buat baris baru. (memakai UNIQUE KEY id_user+id_produk)
    $stmt = $conn->prepare(
        "INSERT INTO keranjang (id_user, id_produk, jumlah) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE jumlah = jumlah + VALUES(jumlah)"
    );
    $stmt->bind_param("iii", $id_user, $id_produk, $jumlah);

    if ($stmt->execute()) {
        echo "<script>alert('Produk berhasil ditambahkan ke keranjang!'); window.location='semantik.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    header("location:semantik.php");
}
?>
