<?php
/**
 * proses_pesan.php
 * -------------------------------------------------------------
 * CATATAN PERUBAHAN:
 * Sebelumnya file ini langsung membuat 1 baris "pesanan" tanpa
 * memeriksa stok terlebih dahulu (bug), dan tanpa keranjang belanja
 * sama sekali (tidak sesuai kebutuhan "manajemen keranjang belanja").
 *
 * Sekarang alur pemesanan dari halaman utama diarahkan menjadi
 * "tambah ke keranjang" (lihat tambah_keranjang.php), lalu user
 * menyelesaikan pembelian lewat tombol Checkout pada bagian
 * "Keranjang Saya". File ini tetap dipertahankan agar form lama
 * (field: id_produk, jumlah_pesan) tetap berfungsi.
 * -------------------------------------------------------------
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';
include 'auth.php';

require_login();

if (isset($_POST['submit_pesan'])) {
    // Petakan nama field lama ke field yang dipakai tambah_keranjang.php
    $_POST['id_produk'] = $_POST['id_produk'] ?? null;
    $_POST['jumlah']    = $_POST['jumlah_pesan'] ?? null;

    include 'tambah_keranjang.php';
    exit();
}

header("location:semantik.php");
exit();
?>
