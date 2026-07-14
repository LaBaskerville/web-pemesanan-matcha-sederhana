<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';
include 'auth.php';

//butuh role admin untuk mengakeses
require_admin();

if (isset($_POST['register'])) {
    $u = trim($_POST['new_username']);
    $p_raw = $_POST['new_password'];

    if ($u === '' || $p_raw === '') {
        echo "<script>alert('Username dan password wajib diisi!'); window.location='semantik.php';</script>";
        exit();
    }

    // Cek username sudah dipakai atau belum (prepared statement)
    $cek = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $cek->bind_param("s", $u);
    $cek->execute();
    if ($cek->get_result()->num_rows > 0) {
        echo "<script>alert('Username sudah digunakan, pilih username lain!'); window.location='semantik.php';</script>";
        exit();
    }

    // Enkripsi password (hash) sebelum disimpan
    $p_hashed = password_hash($p_raw, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
    $stmt->bind_param("ss", $u, $p_hashed);

    if ($stmt->execute()) {
        echo "<script>alert('User Baru Berhasil Dibuat!'); window.location='semantik.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
