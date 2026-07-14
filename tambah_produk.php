<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';
include 'auth.php';
require_admin();

if (isset($_POST['tambah_produk'])) {
    $nama      = trim($_POST['nama_produk']);
    $deskripsi = trim($_POST['deskripsi']);
    $harga     = $_POST['harga'];
    $stok      = $_POST['stok'];

    if ($nama === '' || !is_numeric($harga) || $harga < 0 || !is_numeric($stok) || $stok < 0) {
        echo "<script>alert('Data produk tidak valid! Pastikan harga dan stok berupa angka positif.'); window.location='semantik.php';</script>";
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO produk (nama_produk, deskripsi, harga, stok) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $nama, $deskripsi, $harga, $stok);

    if ($stmt->execute()) {
        echo "<script>alert('Produk baru berhasil ditambahkan!'); window.location='semantik.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    header("location:semantik.php");
}
?>
