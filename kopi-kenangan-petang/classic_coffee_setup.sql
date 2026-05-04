-- classic_coffee_setup.sql
-- Digunakan untuk membuat struktur 5 tabel (Harus diimpor ke phpMyAdmin dulu)

-- Pastikan Anda sudah membuat database, misalnya db_classiccoffee, sebelum mengimpor file ini.
-- USE db_classiccoffee;

-- 1. Tabel MENU (Menggabungkan Menu & Produk)
CREATE TABLE menu (
    id_menu INT(11) PRIMARY KEY AUTO_INCREMENT,
    nama_menu VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    harga DECIMAL(10,2) NOT NULL,
    gambar VARCHAR(255),
    unggulan TINYINT(1) DEFAULT 0, -- 1 = Ya, 0 = Tidak (untuk Produk Unggulan)
    UNIQUE KEY uk_nama_menu (nama_menu)
);

-- Data Awal (Contoh pengisian manual)
INSERT INTO menu (nama_menu, harga, unggulan) VALUES
('Espresso', 18000.00, 0),
('Cappuccino', 28000.00, 1),
('Latte', 28000.00, 1);

-- 2. Tabel USER (Staf Admin/Kasir)
CREATE TABLE user (
    id_user INT(11) PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Harus menyimpan hash (misalnya, bcrypt)
    level ENUM('admin', 'kasir') NOT NULL DEFAULT 'kasir'
);

-- Data Awal (Contoh: Password '123456' yang sudah di-hash, Anda harus ganti hash-nya)
INSERT INTO user (username, password, level) VALUES
('superadmin', 'password_hash_admin', 'admin'),
('kasir_utama', 'password_hash_kasir', 'kasir');

-- 3. Tabel PESANAN (Header Transaksi)
CREATE TABLE pesanan (
    id_pesanan INT(11) PRIMARY KEY AUTO_INCREMENT,
    tanggal_pesanan DATETIME NOT NULL,
    total_harga DECIMAL(10,2) NOT NULL,
    nama_pelanggan VARCHAR(100),
    email_pelanggan VARCHAR(100), -- Tambahan dari UI Checkout
    no_hp_pelanggan VARCHAR(15), -- Tambahan dari UI Checkout
    status_pesanan ENUM('Pending', 'Diproses', 'Selesai', 'Dibatalkan') NOT NULL DEFAULT 'Pending'
);

-- 4. Tabel DETAIL_PESANAN (Rincian Item)
CREATE TABLE detail_pesanan (
    id_detail INT(11) PRIMARY KEY AUTO_INCREMENT,
    id_pesanan INT(11) NOT NULL,
    id_menu INT(11) NOT NULL,
    jumlah INT(3) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan) ON DELETE CASCADE,
    FOREIGN KEY (id_menu) REFERENCES menu(id_menu) ON DELETE RESTRICT
);

-- 5. Tabel PESAN_KONTAK
CREATE TABLE pesan_kontak (
    id_pesan INT(11) PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    no_hp VARCHAR(15),
    isi_pesan TEXT NOT NULL,
    tanggal_kirim DATETIME NOT NULL,
    status_baca ENUM('belum', 'sudah') NOT NULL DEFAULT 'belum'
);