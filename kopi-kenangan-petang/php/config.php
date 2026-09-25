<?php
// php/config.php
// Konfigurasi Terpusat Classic Coffee Web (Sistem Pembayaran QR Code ZXing)

// 1. Sertakan konfigurasi database
require_once dirname(__FILE__) . '/../koneksi.php';

// 2. Pengaturan Kedai Kopi & Pembayaran QR
define('TOKO_NAMA', 'Classic Coffee');
define('TOKO_WA_NUMBER', '6281234567890'); // Ganti dengan nomor WhatsApp Admin/Kasir toko (diawali 62)

// String QRIS Statis Merchant (Contoh format standar QRIS Nasional Indonesia)
// Anda dapat mengganti ini dengan string QRIS merchant toko Anda (GoPay/Shopee/BCA/Dana)
define('DEFAULT_QRIS_DATA', '00020101021126590011ID.CO.GOPAY.WWW01189360091100000000000208123456785204549953033605802ID5914Classic Coffee6007Jakarta61051234062070703A016304');
?>
