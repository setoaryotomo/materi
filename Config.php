<?php
// config.php
// Konfigurasi koneksi database MySQL

$host   = "localhost";
$user   = "root";
$pass   = "";       // default XAMPP kosong
$dbname = "db_nasabah";

$koneksi = mysqli_connect($host, $user, $pass, $dbname);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($koneksi, "utf8mb4");
