<?php
include 'koneksi.php';

$pesan_error = '';

// Logika Simpan Pesanan (pemesanan tamu, tanpa perlu login)
if (isset($_POST['pesan'])) {
    $nama_pelanggan = trim($_POST['nama_pelanggan']);
    $id_produk      = (int) $_POST['id_produk'];
    $jumlah         = (int) $_POST['jumlah_pesan'];

    if ($nama_pelanggan === '' || $id_produk <= 0 || $jumlah < 1) {
        $pesan_error = "Data pesanan tidak valid!";
    } else {
        $cek_stok = $conn->prepare("SELECT nama_produk, harga, stok FROM produk WHERE id = ?");
        $cek_stok->bind_param("i", $id_produk);
        $cek_stok->execute();
        $data_produk = $cek_stok->get_result()->fetch_assoc();

        if (!$data_produk) {
            $pesan_error = "Produk tidak ditemukan!";
        } elseif ($data_produk['stok'] < $jumlah) {
            $pesan_error = "Maaf, Stok tidak mencukupi! Sisa stok: {$data_produk['stok']}";
        } else {
            // Gunakan TRANSACTION supaya insert pesanan + kurangi stok konsisten
            $conn->begin_transaction();
            try {
                $subtotal = $data_produk['harga'] * $jumlah;

                $stmtPesanan = $conn->prepare(
                    "INSERT INTO pesanan (id_user, nama_pelanggan, total_harga, status) VALUES (NULL, ?, ?, 'Belum Diproses')"
                );
                $stmtPesanan->bind_param("sd", $nama_pelanggan, $subtotal);
                $stmtPesanan->execute();
                $id_pesanan = $conn->insert_id;

                $stmtDetail = $conn->prepare(
                    "INSERT INTO pesanan_detail (id_pesanan, id_produk, jenis_matcha, jumlah_pesan, harga_satuan, subtotal)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmtDetail->bind_param(
                    "iisidd",
                    $id_pesanan, $id_produk, $data_produk['nama_produk'],
                    $jumlah, $data_produk['harga'], $subtotal
                );
                $stmtDetail->execute();

                $stmtStok = $conn->prepare("UPDATE produk SET stok = stok - ? WHERE id = ? AND stok >= ?");
                $stmtStok->bind_param("iii", $jumlah, $id_produk, $jumlah);
                $stmtStok->execute();

                if ($stmtStok->affected_rows === 0) {
                    throw new Exception("Stok berubah, pemesanan dibatalkan.");
                }

                $conn->commit();
                echo "<script>alert('Pesanan Berhasil!'); window.location='semantik.php';</script>";
                exit();

            } catch (Exception $e) {
                $conn->rollback();
                $pesan_error = "Gagal menyimpan pesanan: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pemesanan Barang</title>
    <style>
        .form-box { width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        input, select, button { width: 100%; padding: 10px; margin: 10px 0; display: block; }
        button { background-color: #28a745; color: white; border: none; cursor: pointer; }
        .error { color: #c62828; font-weight: bold; }
    </style>
</head>
<body>

<div class="form-box">
    <h2>Formulir Pemesanan</h2>

    <?php if ($pesan_error): ?>
        <p class="error"><?= htmlspecialchars($pesan_error) ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <label>Nama Pemesan:</label>
        <input type="text" name="nama_pelanggan" placeholder="Masukkan nama Anda" required>

        <label>Pilih Barang:</label>
        <select name="id_produk" required>
            <option value="">-- Pilih Produk --</option>
            <?php
            $tampil_produk = mysqli_query($conn, "SELECT * FROM produk WHERE stok > 0");
            while ($row = mysqli_fetch_assoc($tampil_produk)) {
                echo "<option value='" . htmlspecialchars($row['id']) . "'>"
                    . htmlspecialchars($row['nama_produk']) . " (Stok: " . htmlspecialchars($row['stok']) . ")</option>";
            }
            ?>
        </select>

        <label>Jumlah Pesan:</label>
        <input type="number" name="jumlah_pesan" min="1" required>

        <button type="submit" name="pesan">Kirim Pesanan</button>
    </form>
    <p style="text-align:center"><a href="semantik.php">&larr; Kembali ke halaman utama</a></p>
</div>
</body>
</html>
