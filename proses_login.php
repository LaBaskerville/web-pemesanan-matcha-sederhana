<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

if (isset($_POST['login'])) {
    $u = trim($_POST['username']);
    $p = $_POST['password'];
    $r = $_POST['role'];

    // BUG DIPERBAIKI: query sebelumnya menyisipkan variabel langsung ke
    // dalam string SQL (rentan SQL Injection). Sekarang pakai prepared statement.
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
    $stmt->bind_param("ss", $u, $r);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    // BUG DIPERBAIKI: kode lama punya "backdoor" -> password akan dianggap
    // benar jika sama persis dengan teks polos ATAU jika diisi "admin123",
    // apa pun akun yang dituju. Ini celah keamanan besar dan sudah dihapus.
    // Sekarang HANYA password_verify() terhadap hash di database yang dipakai.
    if ($data && password_verify($p, $data['password'])) {
        session_regenerate_id(true); // cegah session fixation
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
