<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';
include 'auth.php';
require_login();

if (isset($_POST['id_keranjang']) && isset($_POST['jumlah_baru'])) {
    $id_user      = $_SESSION['user_id'];
    $id_keranjang = (int) $_POST['id_keranjang'];
    $jumlah_baru  = (int) $_POST['jumlah_baru'];

    if ($jumlah_baru < 1) {
        echo "<script>alert('Jumlah minimal 1! Gunakan tombol hapus jika ingin menghilangkan item.'); window.location='semantik.php';</script>";
        exit();
    }

    // Ambil id_produk dari baris keranjang ini, sekaligus pastikan
    // baris ini benar-benar milik user yang sedang login.
    $cek = $conn->prepare("SELECT id_produk FROM keranjang WHERE id = ? AND id_user = ?");
    $cek->bind_param("ii", $id_keranjang, $id_user);
    $cek->execute();
    $row = $cek->get_result()->fetch_assoc();

    if (!$row) {
        echo "<script>alert('Item keranjang tidak ditemukan!'); window.location='semantik.php';</script>";
        exit();
    }

    // Cek stok cukup untuk jumlah baru
    $cekStok = $conn->prepare("SELECT stok FROM produk WHERE id = ?");
    $cekStok->bind_param("i", $row['id_produk']);
    $cekStok->execute();
    $produk = $cekStok->get_result()->fetch_assoc();

    if (!$produk || $produk['stok'] < $jumlah_baru) {
        $sisa = $produk ? $produk['stok'] : 0;
        echo "<script>alert('Stok tidak mencukupi! Sisa stok: $sisa'); window.location='semantik.php';</script>";
        exit();
    }

    $stmt = $conn->prepare("UPDATE keranjang SET jumlah = ? WHERE id = ? AND id_user = ?");
    $stmt->bind_param("iii", $jumlah_baru, $id_keranjang, $id_user);

    if ($stmt->execute()) {
        header("location:semantik.php?pesan=keranjang_diupdate");
        exit();
    } else {
        echo "Error: " . $conn->error;
        exit();
    }
}

header("location:semantik.php");
exit();
?>
