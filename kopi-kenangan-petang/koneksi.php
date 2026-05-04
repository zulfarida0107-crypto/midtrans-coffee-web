<?php
// koneksi.php

$servername = "localhost";
$username = "root";
$password = ""; 
$database = "db_classiccoffee"; // Ganti dengan nama database Anda

// Buat Koneksi
$conn = mysqli_connect($servername, $username, $password, $database);

// Cek Koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
} 

// Set Zona Waktu
date_default_timezone_set('Asia/Jakarta');
?>