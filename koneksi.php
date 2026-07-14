<?php
// Konfigurasi Database
$host     = "localhost"; // Biasanya localhost
$username = "root";      // Username default database
$password = "";          // Password default (kosongkan jika tidak ada)
$database = "dbmatcha";  // Nama database (lihat database.sql)

// Membuat koneksi ke database
$conn = new mysqli($host, $username, $password, $database);

// Memeriksa apakah koneksi berhasil
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset agar karakter (misal huruf non-ASCII) tersimpan dengan benar
$conn->set_charset("utf8mb4");
?>
