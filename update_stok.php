<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';
include 'auth.php';

// BUG DIPERBAIKI: sebelumnya endpoint ini bisa diakses siapa saja
// (tanpa login admin) untuk mengubah stok produk.
require_admin();

if (isset($_POST['update'])) {
    $id     = $_POST['id_produk'];
    $tambah = $_POST['jumlah'];

    if (!is_numeric($tambah)) {
        echo "<script>alert('Jumlah harus berupa angka!'); window.location='semantik.php';</script>";
        exit();
    }

    // BUG DIPERBAIKI: query sebelumnya menyisipkan variabel langsung
    // (rentan SQL Injection). Sekarang pakai prepared statement.
    $stmt = $conn->prepare("UPDATE produk SET stok = stok + ? WHERE id = ?");
    $stmt->bind_param("ii", $tambah, $id);

    if ($stmt->execute()) {
        header("location:semantik.php?pesan=stok_berhasil");
        exit();
    } else {
        echo "Error: " . $conn->error;
        exit();
    }
}
?>
