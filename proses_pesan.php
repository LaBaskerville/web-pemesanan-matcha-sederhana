<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';
include 'auth.php';

require_login();

if (isset($_POST['submit_pesan'])) {
    $_POST['id_produk'] = $_POST['id_produk'] ?? null;
    $_POST['jumlah']    = $_POST['jumlah_pesan'] ?? null;

    include 'tambah_keranjang.php';
    exit();
}

header("location:semantik.php");
exit();
?>
