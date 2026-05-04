<?php
// proses_pesan_kontak.php
include 'koneksi.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Ambil dan amankan data dari formulir Kontak
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $isi_pesan = mysqli_real_escape_string($conn, $_POST['isi_pesan']);
    
    // Data Otomatis oleh Server
    $tanggal_kirim = date('Y-m-d H:i:s'); 

    // Perintah INSERT INTO untuk mengisi tabel pesan_kontak secara otomatis
    $sql = "INSERT INTO pesan_kontak (nama, email, no_hp, isi_pesan, tanggal_kirim) 
            VALUES ('$nama', '$email', '$no_hp', '$isi_pesan', '$tanggal_kirim')";

    if (mysqli_query($conn, $sql)) {
        // Berhasil disimpan otomatis
        echo "Pesan Anda berhasil terkirim dan tersimpan di database!";
        // Redirect ke halaman kontak.php
        // header("Location: kontak.php?status=pesan_sukses"); 
    } else {
        echo "Error: Gagal menyimpan pesan. " . mysqli_error($conn);
    }
    
    mysqli_close($conn);
} else {
    echo "Akses tidak valid.";
}
?>