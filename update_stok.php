<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';
include 'auth.php';

//butuh role admin buat akses
require_admin();

if (isset($_POST['update'])) {
    $id     = $_POST['id_produk'];
    $tambah = $_POST['jumlah'];

    if (!is_numeric($tambah)) {
        echo "<script>alert('Jumlah harus berupa angka!'); window.location='semantik.php';</script>";
        exit();
    }

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
