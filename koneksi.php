<?php
// Konfigurasi Database
$host     = "localhost"; 
$username = "root"; 
$password = "";          
$database = "dbmatcha"; 

$conn = new mysqli($host, $username, $password, $database);

// Memeriksa apakah koneksi berhasil
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
