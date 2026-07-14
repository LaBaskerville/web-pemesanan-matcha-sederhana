<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';
include 'auth.php';
require_login();

if (isset($_GET['id'])) {
    $id_user      = $_SESSION['user_id'];
    $id_keranjang = (int) $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM keranjang WHERE id = ? AND id_user = ?");
    $stmt->bind_param("ii", $id_keranjang, $id_user);

    if ($stmt->execute()) {
        header("location:semantik.php?pesan=item_dihapus");
        exit();
    } else {
        echo "Gagal menghapus item: " . $conn->error;
        exit();
    }
}

header("location:semantik.php");
exit();
?>
