<?php
// konekcija.php
$host = "localhost";
$user = "root";
$password = ""; // Na XAMPP-u je prazno, ako imaš šifru na lokalnoj bazi upiši je ovde
$database = "restoran_medo"; // Naziv tvoje baze na kompjuteru

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    // Ako konekcija pukne, vraćamo JSON grešku jer JS to očekuje
    header('Content-Type: application/json');
    error_log("Database Connection Error: " . $conn->connect_error);
    echo json_encode(["status" => "error", "message" => "Greška sa lokalnom bazom: " . $conn->connect_error]);
    exit();
}

$conn->set_charset("utf8mb4");
?>