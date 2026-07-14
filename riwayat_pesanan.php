<?php
/**
 * riwayat_pesanan.php
 * -------------------------------------------------------------
 * Panel USER: menampilkan riwayat pesanan & status pesanan
 * milik user yang sedang login saja (bukan milik user lain).
 * Ini melengkapi panel admin yang sudah bisa melihat SEMUA
 * pesanan di semantik.php.
 * -------------------------------------------------------------
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';
include 'auth.php';

// Wajib login. Pesanan tamu (id_user NULL, dari form_pemesanan.php)
// tidak bisa dilacak di sini karena memang tidak terhubung ke akun manapun.
require_login();

$id_user = $_SESSION['user_id'];

// Ambil header pesanan milik user ini
$stmtPesanan = $conn->prepare(
    "SELECT id_pesanan, total_harga, status, tanggal_pesan
     FROM pesanan
     WHERE id_user = ?
     ORDER BY id_pesanan DESC"
);
$stmtPesanan->bind_param("i", $id_user);
$stmtPesanan->execute();
$daftar_pesanan = $stmtPesanan->get_result()->fetch_all(MYSQLI_ASSOC);

// Ambil semua detail item sekaligus, lalu kelompokkan per id_pesanan
// (menghindari query berulang di dalam loop / N+1 query)
$detail_per_pesanan = [];
if (!empty($daftar_pesanan)) {
    $ids = array_column($daftar_pesanan, 'id_pesanan');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $stmtDetail = $conn->prepare(
        "SELECT id_pesanan, jenis_matcha, jumlah_pesan, harga_satuan, subtotal
         FROM pesanan_detail
         WHERE id_pesanan IN ($placeholders)
         ORDER BY id_detail ASC"
    );
    $stmtDetail->bind_param($types, ...$ids);
    $stmtDetail->execute();
    $rows = $stmtDetail->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($rows as $row) {
        $detail_per_pesanan[$row['id_pesanan']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan Saya</title>
    <link rel="stylesheet" href="semantik.css">
    <style>
        body { display: block; padding: 20px; }
        .riwayat-container {
            max-width: 900px;
            margin: 30px auto;
        }
        .riwayat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .riwayat-header a {
            color: #2d5a27;
            font-weight: bold;
            text-decoration: none;
        }
        .pesanan-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 18px 20px;
            margin-bottom: 20px;
        }
        .pesanan-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .pesanan-card-header h3 { color: #2d5a27; margin: 0; }
        .pesanan-card-header .tanggal { color: #777; font-size: 13px; }
        .item-list { width: 100%; border-collapse: collapse; }
        .item-list td { padding: 6px 4px; color: #333; }
        .item-list .kanan { text-align: right; }
        .pesanan-total {
            text-align: right;
            font-weight: bold;
            margin-top: 10px;
            color: #2d5a27;
        }
        .empty-box {
            background: white;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="riwayat-container">
        <div class="riwayat-header">
            <h2 style="color:#2d5a27;">Riwayat Pesanan Saya</h2>
            <a href="semantik.php">&larr; Kembali ke Halaman Utama</a>
        </div>

        <?php if (empty($daftar_pesanan)): ?>
            <div class="empty-box">
                <p>Anda belum pernah melakukan pemesanan.</p>
            </div>
        <?php else: ?>
            <?php foreach ($daftar_pesanan as $p):
                $badgeClass = ($p['status'] === 'Sudah Diproses') ? 'badge-green' : 'badge-red';
                $items = $detail_per_pesanan[$p['id_pesanan']] ?? [];
            ?>
            <div class="pesanan-card">
                <div class="pesanan-card-header">
                    <div>
                        <h3>Pesanan #<?= htmlspecialchars($p['id_pesanan']) ?></h3>
                        <span class="tanggal"><?= htmlspecialchars(date('d M Y, H:i', strtotime($p['tanggal_pesan']))) ?></span>
                    </div>
                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($p['status']) ?></span>
                </div>

                <table class="item-list">
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['jenis_matcha']) ?></td>
                            <td class="kanan"><?= htmlspecialchars($item['jumlah_pesan']) ?> gram</td>
                            <td class="kanan">Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?> / gram</td>
                            <td class="kanan">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <p class="pesanan-total">Total: Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></p>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
