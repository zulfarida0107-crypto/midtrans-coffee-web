<?php
// proses_kontak.php
// Asumsi data formulir dikirim menggunakan metode POST

// --- Langkah 1: Panggil file koneksi ---
include 'koneksi.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil dan bersihkan data input dari form
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $isi_pesan = mysqli_real_escape_string($conn, $_POST['isi_pesan']);
    
    // Tentukan tanggal kirim otomatis
    $tanggal_kirim = date('Y-m-d H:i:s'); // Mengambil waktu server saat ini

    // --- Langkah 2: Jalankan Query INSERT ke tabel 'pesan_kontak' ---
    $sql = "INSERT INTO pesan_kontak (nama, email, no_hp, isi_pesan, tanggal_kirim) 
            VALUES ('$nama', '$email', '$no_hp', '$isi_pesan', '$tanggal_kirim')";

    if (mysqli_query($conn, $sql)) {
        echo "Pesan Anda berhasil terkirim! Terima kasih.";
        // Di aplikasi nyata, Anda akan redirect pengguna kembali ke halaman kontak
        // header("Location: kontak.php?status=sukses");
    } else {
        echo "Error: Gagal menyimpan pesan. " . mysqli_error($conn);
    }
    
    // --- Langkah 3: Tutup koneksi ---
    mysqli_close($conn);
} else {
    // Jika diakses tanpa method POST (langsung diakses via URL)
    echo "Akses tidak sah.";
}
?>