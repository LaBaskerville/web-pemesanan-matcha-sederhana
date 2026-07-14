<?php
/**
 * auth.php
 * Kumpulan fungsi bantu untuk proteksi akses.
 * PENTING: panggil session_start() SEBELUM include file ini.
 *
 * BUG YANG DIPERBAIKI:
 * Sebelumnya file seperti update_stok.php, update_status.php,
 * tambah_user.php bisa diakses/dieksekusi langsung oleh siapa pun
 * (bahkan tanpa login) karena tidak ada pengecekan session sama
 * sekali. Ini celah keamanan (broken access control).
 */

function require_login() {
    if (!isset($_SESSION['username'])) {
        header("Location: semantik.php?pesan=harus_login");
        exit();
    }
}

function require_admin() {
    if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: semantik.php?pesan=akses_ditolak");
        exit();
    }
}
