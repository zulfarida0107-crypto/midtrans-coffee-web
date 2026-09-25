# Classic Coffee Web ☕

Aplikasi web *e-commerce* modern dan responsif untuk kedai kopi (**Classic Coffee / Kopi Kenangan Petang**). Dilengkapi dengan katalog menu dinamis berbasis database MySQL, keranjang belanja interaktif *real-time* (Alpine.js), sistem pembayaran QR Code mandiri menggunakan library **ZXing**, serta halaman **Invoice Digital** bertema senada yang dapat diunduh langsung sebagai file gambar (**PNG/JPG**).

---

## 🌟 Fitur Utama

- **Katalog Menu & Produk Unggulan Dinamis:** Menu kopi dan produk unggulan dimuat langsung dari database MySQL (`menu`) dengan harga, deskripsi, dan gambar.
- **Keranjang Belanja Interaktif:** Menggunakan **Alpine.js** untuk manajemen state keranjang (*add, minus, remove*) secara *real-time* tanpa perlu memuat ulang (*reload*) halaman.
- **Pencarian Produk Cerdas:** Fitur pencarian langsung (*real-time highlight glow*) yang memudahkan pelanggan menemukan kopi favorit.
- **Sistem Pembayaran QR Code (ZXing Library):**
  - Pembuatan QR Code otomatis di sisi browser menggunakan library **`@zxing/library`** (mendukung scan QRIS via BCA, Mandiri, BRI, GoPay, OVO, DANA, dll.).
  - Tanpa memerlukan akun merchant perantara atau API key berbayar.
- **Pencegahan Manipulasi Harga (*Anti-Price Tampering*):** Validasi harga setiap item belanja di sisi server dari database sebelum pesanan disimpan.
- **Pencatatan Pesanan Lengkap:** Transaksi header disimpan ke tabel `pesanan` dan rincian item ke tabel `detail_pesanan` dengan status `Pending`.
- **Halaman Invoice Digital Terintegrasi (`invoice.php`):**
  - Desain bertema gelap (*dark theme*) elegan dengan aksen warna kopi keemasan (`#b6895b`) yang senada dengan landing page.
  - Menampilkan nomor order, detail pelanggan, tabel rincian menu, total bayar, dan status transaksi.
  - Tampilan QR Code pembayaran aktif selama status pesanan masih `Pending`.
- **Fitur Download Invoice (PNG/JPG):**
  - Pelanggan dapat mengunduh invoice dalam format gambar beresolusi tinggi (HD) secara instan menggunakan library **`html2canvas`**.
- **Formulir Kontak Aman:** Dilengkapi sanitasi input, validasi format email, dan perlindungan terhadap SQL Injection menggunakan **Prepared Statements** (`mysqli_stmt`).
- **Desain Responsif:** Tampilan optimal di berbagai perangkat (*Desktop, Tablet, dan Smartphone*).

---

## 🛠️ Teknologi yang Digunakan

| Kategori | Teknologi |
|---|---|
| **Frontend** | HTML5, CSS3, Vanilla JavaScript (ES6+), [Alpine.js](https://alpinejs.dev/), [Feather Icons](https://feathericons.com/) |
| **Libraries** | [@zxing/library](https://github.com/zxing-js/library) (QR Code Generator/Reader), [html2canvas](https://html2canvas.hertzen.com/) (Download Gambar Invoice) |
| **Backend** | PHP 8.x (Native) |
| **Database** | MySQL / MariaDB |
| **Web Server** | Apache (XAMPP) atau PHP Built-in Server |

---

## 🚀 Cara Instalasi & Menjalankan di Lokal

### 1. Prasyarat
- Telah menginstal **XAMPP** (Apache, PHP, dan MySQL) di komputer Anda.

### 2. Konfigurasi Database MySQL
1. Buka **XAMPP Control Panel** dan klik **Start** pada modul **MySQL**.
2. Buka phpMyAdmin di browser: `http://localhost/phpmyadmin` (atau `http://localhost:8080/phpmyadmin`).
3. Buat database baru dengan nama:
   ```sql
   CREATE DATABASE db_classiccoffee;
   ```
4. Impor file skema dan data awal:
   - Pilih tab **Import** pada database `db_classiccoffee`.
   - Pilih file `kopi-kenangan-petang/classic_coffee_setup.sql`.
   - Klik tombol **Import**.
5. Sesuaikan konfigurasi di `kopi-kenangan-petang/koneksi.php` jika port MySQL Anda berbeda (default aplikasi menggunakan port `3307` untuk XAMPP lokal, ubah ke `3306` jika menggunakan port default).

---

### 3. Menjalankan Aplikasi

Anda dapat memilih salah satu dari dua cara berikut:

#### Opsi A: Menggunakan PHP Built-in Server (Sangat Mudah & Cepat)
1. Buka Terminal / PowerShell / Command Prompt.
2. Masuk ke direktori aplikasi:
   ```bash
   cd "C:\xampp\htdocs\Midtrans Classic Coffee Web\kopi-kenangan-petang"
   ```
3. Jalankan server:
   ```bash
   php -S localhost:8000
   ```
4. Buka peramban di: **[http://localhost:8000](http://localhost:8000)**

---

#### Opsi B: Menggunakan XAMPP Apache
1. Pastikan modul **Apache** dan **MySQL** pada XAMPP Control Panel dalam keadaan berjalan (**Running**).
2. Akses melalui browser:  
   👉 **`http://localhost/Midtrans Classic Coffee Web/kopi-kenangan-petang/`**  
   *(atau sesuaikan dengan port Apache Anda, misal: `http://127.0.0.1:8080/kopi/`)*.

---

## ⚙️ Pengaturan Toko & QRIS

Untuk menyesuaikan nomor kontak WhatsApp toko atau data string QRIS Anda, buka file **`kopi-kenangan-petang/php/config.php`**:

```php
// Nama Kedai Kopi
define('TOKO_NAMA', 'Classic Coffee');

// Nomor WhatsApp Admin/Kasir Toko (diawali kode negara 62)
define('TOKO_WA_NUMBER', '6281234567890');

// String QRIS Statis Toko Anda (bisa didapat dari QRIS BCA/GoPay/DANA toko)
define('DEFAULT_QRIS_DATA', '00020101021126590011ID.CO.GOPAY.WWW...');
```

---

## 📝 Alur Pemesanan & Pembayaran

1. Pelanggan memilih menu kopi pada katalog dan menambahkannya ke keranjang.
2. Pelanggan membuka keranjang belanja, mengisi **Nama, Email, dan No HP**, lalu menekan tombol **Checkout**.
3. Sistem secara otomatis memvalidasi keaslian harga dari database dan mencatat pesanan ke tabel `pesanan` dan `detail_pesanan`.
4. Halaman **Invoice Digital** (`invoice.php?order_id=...`) langsung terbuka menampilkan rincian pesanan dan gambar **QR Code pembayaran ZXing**.
5. Pelanggan dapat men-scan QR Code untuk membayar, lalu menekan tombol **"Download Gambar (PNG)"** untuk menyimpan bukti pesanan ke galeri HP/komputer mereka.

---

## 📄 Lisensi & Kontributor

- **Pengembang:** *namiraassalwa & zulfarida*
- **Tahun:** 2026
