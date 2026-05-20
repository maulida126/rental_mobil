<?php
$host     = 'localhost';
$db       = 'rental_mobil';
$user     = 'root';
$password = 'dloifatulmaulida';

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die('<div class="alert alert-danger text-center">Koneksi gagal: ' . $conn->connect_error . '</div>');
}

$conn->set_charset('utf8mb4');
?>
