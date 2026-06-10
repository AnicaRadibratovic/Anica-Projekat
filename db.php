<?php
// db.php
$host = "localhost";
$user = "root";
$password = "";
$database = "restoran_medo"; // Promeni ime baze ako se zove drugačije

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    // Ako konekcija pukne, vraćamo JSON grešku jer JS to očekuje
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Greška sa bazom: " . $conn->connect_error]);
    exit();
}

// Postavljanje karaktera na UTF-8 zbog naših slova
$conn->set_charset("utf8mb4");
?>