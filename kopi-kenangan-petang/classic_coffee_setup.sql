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

-- Data Awal (Produk Unggulan & Menu)
INSERT INTO menu (id_menu, nama_menu, deskripsi, harga, gambar, unggulan) VALUES
(1, 'Robusta Brazil', 'Biji kopi pilihan dari perkebunan Brazil dengan cita rasa nutty dan cokelat yang kuat. Sangrai medium, cocok untuk espresso dan pour-over.', 20000.00, '1.jpg', 1),
(2, 'Arabika Blend', 'Perpaduan sempurna biji Arabika dari berbagai dataran tinggi Indonesia. Aroma floral yang memikat dengan keasaman lembut dan aftertaste manis.', 25000.00, '2.jpg', 1),
(3, 'Primo Passo', 'Kopi spesialti single-origin dengan profil rasa fruity dan bright acidity. Sangrai light untuk menonjolkan karakter unik biji pilihan.', 30000.00, '3.jpg', 1),
(4, 'Aceh Gayo', 'Kopi premium dari dataran tinggi Gayo, Aceh. Rasa earthy yang kompleks, body tebal, dan aroma rempah yang khas. Sangrai medium-dark.', 35000.00, '4.jpg', 1),
(5, 'Sumatra Mandheling', 'Kopi ikonik dari Sumatera dengan body penuh dan rasa dark chocolate yang dalam. Proses wet-hulled menghasilkan profil rasa yang unik dan bold.', 40000.00, '5.jpg', 1),
(6, 'Espresso', 'Ekstrak kopi murni yang pekat dan beraroma tajam dengan crema keemasan yang kaya rasa.', 15000.00, '1.jpg', 0),
(7, 'Cappuccino', 'Paduan seimbang antara espresso pekat, susu hangat, dan buih susu yang lembut melimpah.', 25000.00, '2.jpg', 0),
(8, 'Latte', 'Espresso klasik dengan kelembutan steamed milk creamy dan lapisan foam tipis di atasnya.', 28000.00, '3.jpg', 0),
(9, 'Americano', 'Espresso kaya rasa yang dilarutkan dengan air panas untuk kenikmatan kopi hitam yang ringan.', 18000.00, '4.jpg', 0),
(10, 'Mocha', 'Kombinasi lezat antara espresso berkualitas, cokelat kaya rasa, dan susu segar.', 30000.00, '5.jpg', 0),
(11, 'Macchiato', 'Espresso kuat yang diberi sentuhan sedikit buih susu lembut di atasnya.', 20000.00, '6.jpg', 0);

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
    midtrans_order_id VARCHAR(100) UNIQUE NULL,
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