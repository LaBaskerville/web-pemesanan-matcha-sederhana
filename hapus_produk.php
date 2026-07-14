<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';
include 'auth.php';
require_admin();

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM produk WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("location:semantik.php?pesan=produk_dihapus");
        exit();
    } else {
        echo "Gagal menghapus produk: " . $conn->error;
        exit();
    }
}

header("location:semantik.php");
exit();
?>
