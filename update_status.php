<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';
include 'auth.php';

// BUG DIPERBAIKI: endpoint ini sebelumnya bisa diakses tanpa login admin.
require_admin();

if (isset($_POST['id_pesanan'])) {
    $id          = $_POST['id_pesanan'];
    $status_baru = $_POST['status_baru'];

    // Validasi nilai status hanya boleh salah satu dari whitelist ini
    $status_valid = ['Belum Diproses', 'Sudah Diproses'];
    if (!in_array($status_baru, $status_valid, true)) {
        echo "<script>alert('Status tidak valid!'); window.location='semantik.php';</script>";
        exit();
    }

    // BUG DIPERBAIKI: sebelumnya rentan SQL Injection karena variabel
    // disisipkan langsung ke query. Sekarang pakai prepared statement.
    $stmt = $conn->prepare("UPDATE pesanan SET status = ? WHERE id_pesanan = ?");
    $stmt->bind_param("si", $status_baru, $id);

    if ($stmt->execute()) {
        header("location:semantik.php?pesan=status_diperbarui");
        exit();
    } else {
        echo "Gagal update status: " . $conn->error;
        exit();
    }
}
?>
