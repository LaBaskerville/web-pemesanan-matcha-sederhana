<?php
include 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ------------------------------------------------------------
// Pesan notifikasi (hasil redirect dari proses tambah/edit/hapus dll)
// ------------------------------------------------------------
$pesan_map = [
    'stok_berhasil'      => ['success', 'Stok produk berhasil diperbarui.'],
    'status_diperbarui'  => ['success', 'Status pesanan berhasil diperbarui.'],
    'produk_diupdate'    => ['success', 'Produk berhasil diperbarui.'],
    'produk_dihapus'     => ['success', 'Produk berhasil dihapus.'],
    'keranjang_diupdate' => ['success', 'Jumlah item keranjang diperbarui.'],
    'item_dihapus'       => ['success', 'Item berhasil dihapus dari keranjang.'],
    'harus_login'        => ['warning', 'Anda harus login terlebih dahulu.'],
    'akses_ditolak'      => ['warning', 'Akses ditolak, halaman ini khusus admin.'],
];
$pesan_info = null;
if (isset($_GET['pesan']) && isset($pesan_map[$_GET['pesan']])) {
    $pesan_info = $pesan_map[$_GET['pesan']];
}

$is_login = isset($_SESSION['username']);
$is_admin = $is_login && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Semantik Sederhana</title>
    <link rel="stylesheet" href="semantik.css">
    <style>
/* Gaya untuk latar belakang modal (overlay) */
.modal {
    display: none; /* Tersembunyi secara default */
    position: fixed; 
    z-index: 9999; /* Memastikan popup berada di paling atas */
    left: 0;
    top: 0;
    width: 100%; 
    height: 100%; 
    overflow: auto; 
    background-color: rgba(0,0,0,0.5); /* BUG DIPERBAIKI: sebelumnya rgba(0,0,0,0,5) (koma bukan titik) membuat overlay tidak tampil */
}

/* Kotak konten modal di tengah */
.modal-content {
    background-color: #fefefe;
    margin: 10% auto; /* Jarak 10% dari atas, dan otomatis ke tengah secara horizontal */
    padding: 25px;
    border: 1px solid #888;
    width: 80%; 
    max-width: 400px; /* Batas lebar maksimal popup */
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    position: relative;
    animation: kuakPopup 0.4s; /* Efek animasi muncul */
}

/* Efek animasi popup muncul */
@keyframes kuakPopup {
    from {transform: translateY(-50px); opacity: 0;}
    to {transform: translateY(0); opacity: 1;}
}

/* Tombol close (X) */
.close {
    color: #aaa;
    position: absolute;
    right: 15px;
    top: 10px;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: #000;
    text-decoration: none;
}

/* Merapikan form di dalam modal */
.modal-content form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.modal-content input, .modal-content select {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.modal-content button {
    padding: 10px;
    margin-top: 10px;
    border-radius: 4px;
    font-weight: bold;
}

/* Mengatur header agar menjadi container flex */
#header {
    display: flex;
    justify-content: space-between; /* Membuat judul di kiri/tengah dan tombol di kanan */
    align-items: center; /* Menyejajarkan teks dan tombol secara vertikal */
    padding: 10px 20px; /* Memberi ruang di dalam header */
    position: relative;
}

/* Mengatur tombol login atau box status setelah login */
.btn-login-trigger {
    order: 2; /* Memaksa elemen login berada di sebelah kanan */
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    background: #45a049;
    color: white;
    cursor: pointer;
    font-weight: bold;
    text-align: center;
}


.order-table-container {
    margin: 30px auto;
    padding: 20px;
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    max-width: 95%;
}

.order-table-container h3 {
    color: #2d5a27; /* Hijau Matcha */
    margin-bottom: 20px;
    border-left: 5px solid #2d5a27;
    padding-left: 15px;
}

.order-table {
    width: 100%;
    border-collapse: collapse;
    overflow: hidden;
    border-radius: 8px;
}

.order-table thead {
    background-color: #2d5a27;
    color: white;
}

.order-table th, .order-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.order-table tbody tr:hover {
    background-color: #f2f9f0; /* BUG DIPERBAIKI: sebelumnya '#f9ff' tanpa titik koma */
}

/* Style untuk Dropdown Aksi */
.status-select {
    padding: 6px 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    background-color: #fff;
    cursor: pointer;
    font-size: 14px;
}

.status-select:focus {
    outline: none;
    border-color: #2d5a27;
}

/* Badge Status */
.badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.badge-red {
    background-color: #ffebee;
    color: #c62828;
}

.badge-green {
    background-color: #e8f5e9;
    color: #2e7d32;
}


/* Memastikan judul berada di urutan pertama (sisi kiri) */
#header h1 {
    order: 1;
    margin: 0;
}
    </style>
</head>
<body>
    <header id="header">
        <?php if(!$is_login): ?>
            <button class="btn-login-trigger" onclick="document.getElementById('loginModal').style.display='block'">Login</button>
        <?php else: ?>
            <div class="btn-login-trigger" style="background:#333">
                Halo, <?= htmlspecialchars($_SESSION['username']) ?> (<?= htmlspecialchars($_SESSION['role']) ?>) | <a href="logout.php" style="color:white; text-decoration:none;">Logout</a>
            </div>
        <?php endif; ?>

        <h1>MATCHAAAA</h1>
        
    </header>

    <?php if ($pesan_info): ?>
        <div class="alert-box alert-<?= $pesan_info[0] === 'success' ? 'success' : 'warning' ?>">
            <?= htmlspecialchars($pesan_info[1]) ?>
        </div>
    <?php endif; ?>
    
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('loginModal').style.display='none'">&times;</span>
            <h2 style="margin-top:0">Login Sistem</h2>
            <form action="proses_login.php" method="POST">
                <label for="">login system</label>
                <label>Username</label>
                <input type="text" name="username" required>
                <label>Password</label>
                <input type="password" name="password" required>
                <label>Masuk Sebagai:</label>
                <select name="role">
                    <option value="user">User / Pelanggan</option>
                    <option value="admin">Administrator</option>
                </select>
                <button type="submit" name="login" style="background:#45a049; color:white; border:none; cursor:pointer;">Masuk</button>
            </form>
        </div>
    </div>

    <nav id="nav">
        <ul>
            <li><a href="#konten-utama">Home</a></li>
            <li><a href="#olahan-matcha">olahan matcha</a></li>
            <li><a href="#form-container">formulir pesanan</a></li>
            <?php if ($is_login): ?>
                <li><a href="riwayat_pesanan.php">Riwayat Pesanan Saya</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <details>
        <summary>Deskripsi matcha</summary>
        <p>Matcha diperkirakan pertama kali dibuat di Tiongkok pada masa Dinasti Tang (abad ke-7 hingga ke-10 M). Pada masa itu, daun teh dikukus dan dibentuk menjadi seperti batu bata.
            Namun, orang yang sangat berperan dalam membawa dan memperkenalkan matcha ke Jepang, serta mengembangkannya hingga menjadi bagian penting dari budaya Jepang (terutama upacara minum teh), adalah seorang pendeta Zen aliran Rinzai bernama Eisai (1141–1215 M).</p>
    </details>

    <main>
        <?php if ($is_admin): ?>
            <div class="admin-box" style="margin-top:10px; border-color: green;">
                <h3>Panel Admin: Daftarkan User Baru</h3>
                <form action="tambah_user.php" method="POST">
                    <input type="text" name="new_username" placeholder="Username Baru" required style="padding:5px; width:150px">
                    <input type="password" name="new_password" placeholder="Password" required style="padding:5px; width:150px">
                    <button type="submit" name="register" style="padding:5px 15px; background:green; color:white; border:none; cursor:pointer">Buat Akun User</button>
                </form>
            </div>

            <!-- ===================== CRUD PRODUK (CREATE) ===================== -->
            <div class="produk-table-container">
                <h3>Panel Admin: Tambah Produk Matcha Baru</h3>
                <form action="tambah_produk.php" method="POST" class="form-tambah-produk">
                    <input type="text" name="nama_produk" placeholder="Nama Produk" required>
                    <input type="text" name="deskripsi" placeholder="Deskripsi singkat">
                    <input type="number" step="0.01" min="0" name="harga" placeholder="Harga (Rp)" required>
                    <input type="number" min="0" name="stok" placeholder="Stok Awal" required>
                    <button type="submit" name="tambah_produk" class="btn-aksi" style="background:#2d5a27;color:white;">Tambah Produk</button>
                </form>
            </div>

            <!-- ================ CRUD PRODUK (READ, UPDATE, DELETE) ================ -->
            <div class="produk-table-container">
                <h3>Panel Admin: Daftar Produk (Kelola Produk)</h3>
                <table class="produk-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Produk</th>
                            <th>Deskripsi</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $daftar_produk = mysqli_query($conn, "SELECT * FROM produk ORDER BY id ASC");
                    while ($row = mysqli_fetch_assoc($daftar_produk)) {
                        echo "<tr>
                                <td>#" . htmlspecialchars($row['id']) . "</td>
                                <td>" . htmlspecialchars($row['nama_produk']) . "</td>
                                <td>" . htmlspecialchars($row['deskripsi'] ?? '') . "</td>
                                <td>Rp " . number_format($row['harga'], 0, ',', '.') . "</td>
                                <td>" . htmlspecialchars($row['stok']) . "</td>
                                <td>
                                    <a class='btn-aksi btn-edit' href='edit_produk.php?id=" . urlencode($row['id']) . "'>Edit</a>
                                    <a class='btn-aksi btn-hapus' href='hapus_produk.php?id=" . urlencode($row['id']) . "' onclick=\"return confirm('Yakin ingin menghapus produk ini?')\">Hapus</a>
                                </td>
                              </tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>

            <div class="admin-box">
                <h3>Panel Admin: Tambah Stok Barang (cepat)</h3>
                <form action="update_stok.php" method="POST">
                    <select name="id_produk" style="padding:5px">
                        <?php
                        $p = mysqli_query($conn, "SELECT * FROM produk");
                        while($row = mysqli_fetch_assoc($p)) {
                            echo "<option value='".htmlspecialchars($row['id'])."'>".htmlspecialchars($row['nama_produk'])." (Sisa: ".htmlspecialchars($row['stok']).")</option>";
                        }
                        ?>
                    </select>
                    <input type="number" name="jumlah" placeholder="Jumlah Tambah" required style="padding:5px; width:120px">
                    <button type="submit" name="update" style="padding:5px 15px; cursor:pointer">Update Stok</button>
                </form>
            </div>

            <!-- ================ RIWAYAT TRANSAKSI (READ pesanan + detail) ================ -->
            <div class="order-table-container">
                <h3>Daftar Pesanan User (Terbaru)</h3>
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Pelanggan</th>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // BUG DIPERBAIKI: query sebelumnya hanya membaca tabel 'pesanan'
                    // yang cuma menyimpan 1 produk per baris. Sekarang tabel
                    // 'pesanan' (header transaksi) di-JOIN dengan 'pesanan_detail'
                    // (rincian item) agar mendukung banyak produk per transaksi
                    // hasil checkout keranjang.
                    $query = mysqli_query($conn, "
                        SELECT ps.id_pesanan, ps.nama_pelanggan, ps.status,
                               pd.jenis_matcha, pd.jumlah_pesan, pd.subtotal
                        FROM pesanan ps
                        JOIN pesanan_detail pd ON pd.id_pesanan = ps.id_pesanan
                        ORDER BY ps.id_pesanan DESC, pd.id_detail ASC
                    ");
                    while($p = mysqli_fetch_assoc($query)) {
                        $badgeClass = ($p['status'] == 'Sudah Diproses') ? 'badge-green' : 'badge-red';

                        echo "<tr>
                                <td>#" . htmlspecialchars($p['id_pesanan']) . "</td>
                                <td><b>" . htmlspecialchars($p['nama_pelanggan']) . "</b></td>
                                <td>" . htmlspecialchars($p['jenis_matcha']) . "</td>
                                <td>" . htmlspecialchars($p['jumlah_pesan']) . " gram</td>
                                <td>Rp " . number_format($p['subtotal'], 0, ',', '.') . "</td>
                                <td><span class='badge $badgeClass'>" . htmlspecialchars($p['status']) . "</span></td>
                                <td>
                                    <form action='update_status.php' method='POST'>
                                        <input type='hidden' name='id_pesanan' value='" . htmlspecialchars($p['id_pesanan']) . "'>
                                        <select name='status_baru' class='status-select' onchange='this.form.submit()'>
                                            <option value=''>Ubah Status</option>
                                            <option value='Belum Diproses'>Belum Diproses</option>
                                            <option value='Sudah Diproses'>Sudah Diproses</option>
                                        </select>
                                    </form>
                                </td>
                              </tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <section id="konten-utama">
            <h2>apa itu matcha?</h2>
            <p>Matcha adalah bubuk teh hijau (green tea) berkualitas tinggi dari Jepang yang dibuat dengan cara dan proses khusus, sehingga berbeda dari teh hijau biasa.</p>
                <img src="pngtree-iced-matcha-latte-splash-with-ice-cubes-and-droplets-png-image_21290975.png" alt="gambar mobilnya" class="gambar">
            
            <article>
                <h3>Pengertian Dasar</h3>
                <p>Matcha (抹茶, yang berarti "teh yang digiling") adalah bubuk yang sangat halus, berwarna hijau cerah, yang berasal dari daun teh dari tanaman yang sama dengan teh hijau biasa, yaitu Camellia sinensis.</p>
            </article>

            <article>
                <h3>Penggunaan</h3>
                <p>Secara tradisional, matcha adalah pusat dari Upacara Minum Teh Jepang (Cha-no-yu). Saat ini, matcha juga sangat populer digunakan dalam minuman modern seperti matcha latte, smoothies, serta sebagai bahan perasa dan pewarna alami untuk es krim, kue, dan makanan penutup lainnya.</p>
            </article>
        </section>
    </main>

    <aside id="olahan-matcha">
        <div class="olahan-matcha">olahan matcha </div>
        <div class="posisi-gambar-matcha">
            <div><img src="images.jpeg" alt=""></div>
            <div><img src="matcha-vs-green-tea-mana-yang-lebih-sehat.jpg" alt=""></div>
            <div><img src="martabak-green-tea.jpg" alt=""></div>
        </div>
        <h2>Deskripsi Kelezatan Matcha yang Unik</h2>
        <div class="container-list">
        <ul>
            <li>
                <strong>Umami (Rasa Gurih Alami):</strong>
                <ul>
                    <li>Ini adalah ciri khas matcha berkualitas tinggi. Memberikan sensasi <strong>gurih alami</strong> yang dalam dan menenangkan.</li>
                    <li>Berasal dari asam amino L-theanine yang tinggi.</li>
                </ul>
            </li>
            <li>
                <strong>Manis Alami (Sweetness) yang Lembut:</strong>
                <ul>
                    <li>Matcha premium sering memiliki <em>aftertaste</em> yang <strong>manis alami</strong> tanpa tambahan gula.</li>
                    <li>Rasa manisnya lembut dan seimbang dengan elemen lainnya.</li>
                </ul>
            </li>
            <li>
                <strong>Pahit (Bitterness) yang Seimbang:</strong>
                <ul>
                    <li>Matcha mengandung sedikit rasa pahit, namun pada kualitas tinggi, rasa pahitnya <strong>tidak getir</strong> dan cepat menghilang.</li>
                    <li>Pahit ini berfungsi menyeimbangkan rasa manis dan umami.</li>
                </ul>
            </li>
            <li>
                <strong>Aroma Segar / *Grassy*:</strong>
                <ul>
                    <li>Aroma dan rasa yang <strong>segar</strong> seperti rumput yang baru dipotong atau sayuran hijau yang alami.</li>
                    <li>Memberikan kesan natural dan menyegarkan.</li>
                </ul>
            </li>
        </ul>
        </div>
    </aside>
    
    <aside id="form-container">
        <?php if (!$is_login): ?>
            <h3>Pesan Matcha Sekarang</h3>
            <p>Silakan <a href="#" onclick="document.getElementById('loginModal').style.display='block'; return false;" style="color:white; font-weight:bold;">login</a> terlebih dahulu untuk memesan dan menggunakan keranjang belanja.</p>
        <?php else: ?>
            <!-- ===================== KERANJANG BELANJA (CREATE) ===================== -->
            <form action="tambah_keranjang.php" method="POST">
                <h3>Tambah ke Keranjang</h3>

                <label for="jenis">Jenis Matcha:</label>
                <select id="jenis" name="id_produk" required>
                        <option value="">Pilih Jenis</option>
                        <?php
                            $res = mysqli_query($conn, "SELECT * FROM produk WHERE stok > 0");
                            while($row = mysqli_fetch_assoc($res)) {
                            echo "<option value='".htmlspecialchars($row['id'])."'>".htmlspecialchars($row['nama_produk'])." (Stok: ".htmlspecialchars($row['stok']).", Rp ".number_format($row['harga'],0,',','.').")</option>";
                            }
                        ?>
                </select>

                <label for="jumlah">Jumlah (gram):</label>
                <input type="number" id="jumlah" name="jumlah" min="1" required>

                <button type="submit">Tambahkan ke Keranjang</button>
            </form>

            <!-- ============ KERANJANG BELANJA (READ, UPDATE, DELETE) ============ -->
            <h3 style="margin-top:25px;">Keranjang Saya</h3>
            <?php
            $stmtCart = $conn->prepare(
                "SELECT k.id AS id_keranjang, k.jumlah, pr.nama_produk, pr.harga, pr.stok
                 FROM keranjang k
                 JOIN produk pr ON pr.id = k.id_produk
                 WHERE k.id_user = ?
                 ORDER BY k.id DESC"
            );
            $stmtCart->bind_param("i", $_SESSION['user_id']);
            $stmtCart->execute();
            $cartItems = $stmtCart->get_result()->fetch_all(MYSQLI_ASSOC);
            $totalCart = 0;
            ?>

            <?php if (empty($cartItems)): ?>
                <p>Keranjang Anda masih kosong.</p>
            <?php else: ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cartItems as $item):
                        $subtotal = $item['harga'] * $item['jumlah'];
                        $totalCart += $subtotal;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nama_produk']) ?></td>
                            <td>
                                <form action="update_keranjang.php" method="POST" class="cart-qty-form">
                                    <input type="hidden" name="id_keranjang" value="<?= htmlspecialchars($item['id_keranjang']) ?>">
                                    <input type="number" name="jumlah_baru" min="1" max="<?= htmlspecialchars($item['stok']) ?>" value="<?= htmlspecialchars($item['jumlah']) ?>">
                                    <button type="submit">Ubah</button>
                                </form>
                            </td>
                            <td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                            <td>
                                <a class="btn-aksi btn-hapus-cart" href="hapus_keranjang.php?id=<?= htmlspecialchars($item['id_keranjang']) ?>" onclick="return confirm('Hapus item ini dari keranjang?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="cart-total">Total: Rp <?= number_format($totalCart, 0, ',', '.') ?></p>
                <form action="checkout.php" method="POST">
                    <button type="submit" class="btn-checkout">Checkout Sekarang</button>
                </form>
            <?php endif; ?>

            <p style="margin-top:15px;">
                <a href="riwayat_pesanan.php" style="color:white; font-weight:bold;">&#128203; Lihat Riwayat &amp; Status Pesanan Saya</a>
            </p>
        <?php endif; ?>
    </aside>

    <footer>
        <p>&copy; website by alphenn.</p>
    </footer>

    <script>
        const nav = document.getElementById('nav');
        const header = document.querySelector('header');
        const headerBottom = header.offsetTop + header.offsetHeight;

        window.addEventListener('scroll', () => {
            if(window.pageYOffset >= headerBottom) {
                nav.classList.add('sticky');
            } else {
                nav.classList.remove('sticky');
            }
        });

        // Menutup modal jika klik di luar area modal
        window.onclick = function(event) {
            let modal = document.getElementById('loginModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
    
</body>
</html>
