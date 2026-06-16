<?php
// konekcija.php
$host = "sql301.infinityfree.com";
$user = "if0_42199738";
$password = "kodmeda123";
$database = "if0_42199738_restoran_medo"; // InfinityFree baza

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    // Ako konekcija pukne, vraćamo JSON grešku jer JS to očekuje
    header('Content-Type: application/json');
    error_log("Database Connection Error: " . $conn->connect_error);
    echo json_encode(["status" => "error", "message" => "Greška sa bazom: " . $conn->connect_error]);
    exit();
}

// Postavljanje karaktera na UTF-8 zbog naših slova
$conn->set_charset("utf8mb4");

// Debug: Log successful connection
error_log("Database connected successfully to " . $database);
?>
