<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} // Memulai session agar bisa dihapus

// Menghapus semua data session yang tersimpan (username & role)
session_unset();
session_destroy();

// Mengarahkan kembali ke halaman utama
header("location:semantik.php");
exit();
?>