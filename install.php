<?php
/**
 * install.php
 * -------------------------------------------------------------
 * Jalankan file ini SEKALI lewat browser (http://localhost/.../install.php)
 * setelah mengimpor database.sql, untuk membuat akun default.
 * Password dibuat dengan password_hash() supaya aman (tidak disimpan
 * sebagai teks polos di database).
 *
 * Setelah berhasil dijalankan, sebaiknya file ini dihapus atau
 * dipindahkan dari folder public agar tidak bisa diakses ulang.
 * -------------------------------------------------------------
 */
include 'koneksi.php';

function buat_akun($conn, $username, $password_plain, $role) {
    // Cek apakah username sudah ada (pakai prepared statement)
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        return "Akun '$username' sudah ada, dilewati.";
    }

    $hash = password_hash($password_plain, PASSWORD_DEFAULT);
    $stmt2 = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt2->bind_param("sss", $username, $hash, $role);

    if ($stmt2->execute()) {
        return "Akun '$username' (role: $role) berhasil dibuat.";
    } else {
        return "Gagal membuat akun '$username': " . $conn->error;
    }
}

$hasil = [];
$hasil[] = buat_akun($conn, 'admin', 'admin123', 'admin');
$hasil[] = buat_akun($conn, 'user1', 'user123', 'user');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Setup Awal - Matcha App</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 60px auto; background:#F0E491; padding: 20px; }
        .box { background: #fff; border-radius: 8px; padding: 20px 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        li { margin-bottom: 8px; }
        a.btn { display:inline-block; margin-top:15px; background:#2d5a27; color:white; padding:10px 18px; border-radius:5px; text-decoration:none; }
    </style>
</head>
<body>
<div class="box">
    <h2>Setup Akun Awal</h2>
    <ul>
        <?php foreach ($hasil as $h) echo "<li>" . htmlspecialchars($h) . "</li>"; ?>
    </ul>
    <p><b>Login default:</b></p>
    <ul>
        <li>Admin &rarr; username: <code>admin</code>, password: <code>admin123</code></li>
        <li>User &rarr; username: <code>user1</code>, password: <code>user123</code></li>
    </ul>
    <p style="color:#c62828;">Demi keamanan, hapus atau pindahkan file <code>install.php</code> ini setelah selesai digunakan.</p>
    <a class="btn" href="semantik.php">Kembali ke Halaman Utama</a>
</div>
</body>
</html>
