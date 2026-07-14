<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

if (isset($_POST['login'])) {
    $u = trim($_POST['username']);
    $p = $_POST['password'];
    $r = $_POST['role'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
    $stmt->bind_param("ss", $u, $r);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data && password_verify($p, $data['password'])) {
        session_regenerate_id(true); 
        $_SESSION['user_id']  = $data['id'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['role']     = $data['role'];

        header("location:semantik.php");
        exit();
    } else {
        echo "<script>alert('Username, password, atau role tidak cocok!'); window.location='semantik.php';</script>";
        exit();
    }
}
?>
