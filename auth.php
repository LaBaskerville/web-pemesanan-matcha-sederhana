<?php

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
