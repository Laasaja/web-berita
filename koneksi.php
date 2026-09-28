<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "db_berita";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}