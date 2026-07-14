CREATE DATABASE IF NOT EXISTS dbmatcha
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE dbmatcha;

-- ---------------------------------------------------------
-- Tabel: users
-- Menyimpan akun pengguna (admin & user/pelanggan)
-- ---------------------------------------------------------
DROP TABLE IF EXISTS pesanan_detail;
DROP TABLE IF EXISTS keranjang;
DROP TABLE IF EXISTS pesanan;
DROP TABLE IF EXISTS produk;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,               -- disimpan dalam bentuk hash (password_hash)
    role        ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel: produk
-- Menyimpan data produk / inventaris matcha
-- ---------------------------------------------------------
CREATE TABLE produk (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    nama_produk  VARCHAR(100)   NOT NULL,
    deskripsi    TEXT           NULL,
    harga        DECIMAL(10,2)  NOT NULL DEFAULT 0,
    stok         INT            NOT NULL DEFAULT 0,
    gambar       VARCHAR(255)   NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel: keranjang
-- Keranjang belanja milik masing-masing user (belum checkout)
-- ---------------------------------------------------------
CREATE TABLE keranjang (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    id_user     INT NOT NULL,
    id_produk   INT NOT NULL,
    jumlah      INT NOT NULL DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_keranjang_user   FOREIGN KEY (id_user)   REFERENCES users(id)  ON DELETE CASCADE,
    CONSTRAINT fk_keranjang_produk FOREIGN KEY (id_produk) REFERENCES produk(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_produk (id_user, id_produk)   -- 1 produk hanya 1 baris per user (jumlah ditambah, bukan duplikat)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel: pesanan (header transaksi)
-- ---------------------------------------------------------
CREATE TABLE pesanan (
    id_pesanan      INT AUTO_INCREMENT PRIMARY KEY,
    id_user         INT NULL,                         -- NULL jika pesanan tamu (tanpa login)
    nama_pelanggan  VARCHAR(100) NOT NULL,
    total_harga     DECIMAL(10,2) NOT NULL DEFAULT 0,
    status          ENUM('Belum Diproses','Sudah Diproses') NOT NULL DEFAULT 'Belum Diproses',
    tanggal_pesan   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pesanan_user FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel: pesanan_detail (rincian item per transaksi)
-- Relasi one-to-many terhadap pesanan, sehingga 1 transaksi
-- bisa berisi banyak produk (hasil checkout keranjang)
-- ---------------------------------------------------------
CREATE TABLE pesanan_detail (
    id_detail      INT AUTO_INCREMENT PRIMARY KEY,
    id_pesanan     INT NOT NULL,
    id_produk      INT NULL,
    jenis_matcha   VARCHAR(100) NOT NULL,              
    jumlah_pesan   INT NOT NULL,
    harga_satuan   DECIMAL(10,2) NOT NULL DEFAULT 0,
    subtotal       DECIMAL(10,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_detail_pesanan FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan) ON DELETE CASCADE,
    CONSTRAINT fk_detail_produk  FOREIGN KEY (id_produk)  REFERENCES produk(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Data awal (seed) untuk tabel produk 
-- ---------------------------------------------------------
INSERT INTO produk (nama_produk, deskripsi, harga, stok) VALUES
('Matcha Premium Ceremonial', 'Matcha kualitas upacara, warna hijau cerah, rasa umami kuat.', 85000, 25),
('Matcha Latte Grade',        'Matcha kelas latte, cocok dicampur susu.',                    45000, 40),
('Matcha Culinary Grade',     'Matcha untuk bahan kue, es krim, dan dessert.',                35000, 60),
('Matcha Organik',            'Matcha organik tanpa pestisida, rasa lembut.',                 95000, 15),
('Matcha Genmaicha',          'Campuran matcha dengan beras panggang.',                       40000, 30);

-- ---------------------------------------------------------
-- CATATAN:
-- Akun users (admin & user demo) TIDAK dibuat lewat SQL ini
-- karena password wajib disimpan ter-hash (password_hash di PHP).
-- Buka file install.php sekali lewat browser untuk membuat
-- akun default secara otomatis:
--   admin / admin123   (role: admin)
--   user1 / user123    (role: user)
-- ---------------------------------------------------------
