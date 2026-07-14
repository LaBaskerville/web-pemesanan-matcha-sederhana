<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';
include 'auth.php';
require_login();

$id_user  = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Ambil semua isi keranjang user ini beserta info produk
$stmt = $conn->prepare(
    "SELECT k.id AS id_keranjang, k.id_produk, k.jumlah, p.nama_produk, p.harga, p.stok
     FROM keranjang k
     JOIN produk p ON p.id = k.id_produk
     WHERE k.id_user = ?"
);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($items)) {
    echo "<script>alert('Keranjang Anda kosong!'); window.location='semantik.php';</script>";
    exit();
}

// Validasi stok untuk semua item sebelum melakukan perubahan apa pun
foreach ($items as $item) {
    if ($item['jumlah'] > $item['stok']) {
        echo "<script>alert('Stok {$item['nama_produk']} tidak mencukupi (sisa {$item['stok']})!'); window.location='semantik.php';</script>";
        exit();
    }
}

// Gunakan TRANSACTION agar semua perubahan (insert pesanan, insert detail,
// update stok, hapus keranjang) berhasil semua atau batal semua.
$conn->begin_transaction();

try {
    $total_harga = 0;
    foreach ($items as $item) {
        $total_harga += $item['harga'] * $item['jumlah'];
    }

    $stmtPesanan = $conn->prepare(
        "INSERT INTO pesanan (id_user, nama_pelanggan, total_harga, status) VALUES (?, ?, ?, 'Belum Diproses')"
    );
    $stmtPesanan->bind_param("isd", $id_user, $username, $total_harga);
    $stmtPesanan->execute();
    $id_pesanan = $conn->insert_id;

    $stmtDetail = $conn->prepare(
        "INSERT INTO pesanan_detail (id_pesanan, id_produk, jenis_matcha, jumlah_pesan, harga_satuan, subtotal)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmtStok = $conn->prepare("UPDATE produk SET stok = stok - ? WHERE id = ? AND stok >= ?");

    foreach ($items as $item) {
        $subtotal = $item['harga'] * $item['jumlah'];
        $stmtDetail->bind_param(
            "iisidd",
            $id_pesanan, $item['id_produk'], $item['nama_produk'],
            $item['jumlah'], $item['harga'], $subtotal
        );
        $stmtDetail->execute();

        // WHERE stok >= jumlah mencegah stok jadi minus akibat race condition
        $stmtStok->bind_param("iii", $item['jumlah'], $item['id_produk'], $item['jumlah']);
        $stmtStok->execute();

        if ($stmtStok->affected_rows === 0) {
            throw new Exception("Stok {$item['nama_produk']} berubah/tidak cukup, checkout dibatalkan.");
        }
    }

    // Kosongkan keranjang user setelah berhasil checkout
    $stmtHapus = $conn->prepare("DELETE FROM keranjang WHERE id_user = ?");
    $stmtHapus->bind_param("i", $id_user);
    $stmtHapus->execute();

    $conn->commit();
    echo "<script>alert('Checkout berhasil! Pesanan #$id_pesanan sedang diproses.'); window.location='semantik.php';</script>";

} catch (Exception $e) {
    $conn->rollback();
    echo "<script>alert('Checkout gagal: " . addslashes($e->getMessage()) . "'); window.location='semantik.php';</script>";
}
?>
