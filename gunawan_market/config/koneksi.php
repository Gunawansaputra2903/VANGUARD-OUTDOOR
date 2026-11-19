<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "gunawan_market";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    exit("Koneksi database gagal. Periksa konfigurasi database.");
}
$conn->set_charset("utf8mb4");
