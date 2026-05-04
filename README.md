# Classic Coffee Web ☕

Sebuah aplikasi web e-commerce sederhana untuk kedai kopi (Classic Coffee / Kopi Kenangan Petang), dilengkapi dengan fitur keranjang belanja interaktif dan integrasi payment gateway Midtrans.

## Fitur Utama ✨
- **Katalog Menu:** Menampilkan daftar produk kopi dan menu lainnya dengan antarmuka yang menarik dan responsif.
- **Keranjang Belanja:** Menggunakan **Alpine.js** untuk manajemen state keranjang belanja (tambah, kurangi, hapus item) secara *real-time* tanpa perlu reload halaman.
- **Payment Gateway Midtrans:** Integrasi pembayaran yang mudah dan aman menggunakan Snap API Midtrans.
- **Desain Responsif:** Tampilan yang menyesuaikan dengan berbagai ukuran layar perangkat (Desktop, Tablet, Mobile).
- **Pencarian Produk:** Fitur pencarian untuk memudahkan pelanggan menemukan menu favorit.

## Teknologi yang Digunakan 🛠️
- **Frontend:** HTML5, CSS3, Vanilla JavaScript, Alpine.js (via CDN), Feather Icons.
- **Backend:** PHP (Native).
- **Payment Gateway:** Midtrans PHP Library.
- **Database:** MySQL (tersedia file setup `classic_coffee_setup.sql`).

## Cara Instalasi & Menjalankan di Lokal 🚀

1. **Clone repositori ini:**
   ```bash
   git clone https://github.com/zulfarida0107-crypto/midtrans-coffee-web.git
   ```
2. **Pindahkan ke direktori server lokal:**
   Pindahkan folder project ke dalam direktori server lokal Anda (misal: `htdocs` untuk XAMPP, `www` untuk WAMP).
3. **Konfigurasi Database:**
   - Buka phpMyAdmin (http://localhost/phpmyadmin).
   - Buat database baru (sesuaikan dengan nama di aplikasi jika diperlukan).
   - Import file `kopi-kenangan-petang/classic_coffee_setup.sql` ke dalam database tersebut.
   - Sesuaikan konfigurasi koneksi database di file `koneksi.php` (jika ada).
4. **Konfigurasi Midtrans:**
   - Daftar dan login ke dashboard [Midtrans](https://midtrans.com/).
   - Dapatkan **Server Key** dan **Client Key** Anda (Gunakan kredensial Sandbox untuk testing).
   - Buka file `kopi-kenangan-petang/php/placeOrder.php` dan masukkan **Server Key** Anda di variabel konfigurasi:
     ```php
     \Midtrans\Config::$serverKey = 'SERVER_KEY_ANDA';
     ```
   - (Opsional) Jika di aplikasi frontend juga membutuhkan Client Key, pastikan disesuaikan.
5. **Jalankan Aplikasi:**
   Buka browser dan akses URL project Anda, misalnya: `http://localhost/kopi-kenangan-petang-2/kopi-kenangan-petang/`

## Catatan Keamanan 🔒
Pastikan untuk **tidak mengunggah (commit/push)** kunci rahasia (seperti Server Key Production) ke GitHub publik demi menjaga keamanan akun Midtrans Anda. Gunakan key Sandbox untuk keperluan testing publik.
