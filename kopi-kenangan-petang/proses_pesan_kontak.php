<?php
// proses_pesan_kontak.php
require_once dirname(__FILE__) . '/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama      = trim($_POST['nama'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $no_hp     = trim($_POST['no_hp'] ?? '');
    $isi_pesan = trim($_POST['isi_pesan'] ?? '');

    // Validasi kelengkapan
    if (empty($nama) || empty($email) || empty($no_hp) || empty($isi_pesan)) {
        http_response_code(400);
        echo "Error: Semua kolom wajib diisi!";
        exit;
    }

    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Error: Format email tidak valid!";
        exit;
    }

    $tanggal_kirim = date('Y-m-d H:i:s');
    $status_baca   = 'belum';

    // Gunakan Prepared Statement untuk mencegah SQL Injection
    $stmt = mysqli_prepare(
        $conn, 
        "INSERT INTO pesan_kontak (nama, email, no_hp, isi_pesan, tanggal_kirim, status_baca) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssss", $nama, $email, $no_hp, $isi_pesan, $tanggal_kirim, $status_baca);
        if (mysqli_stmt_execute($stmt)) {
            echo "Pesan Anda berhasil terkirim dan tersimpan di database!";
        } else {
            http_response_code(500);
            echo "Error: Gagal menyimpan pesan: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        http_response_code(500);
        echo "Error: Gagal menyiapkan query: " . mysqli_error($conn);
    }

    mysqli_close($conn);
} else {
    http_response_code(405);
    echo "Akses tidak valid. Gunakan metode POST.";
}
?>