<?php
// obrada.php
header('Content-Type: application/json');
require_once 'db.php';

// Prihvatanje JSON podataka poslatih preko JS fetch-a
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(["status" => "error", "message" => "Nema poslatih podataka."]);
    exit();
}

$akcija = $input['akcija'] ?? '';

// --- 1. OBRADA REZERVACIJE ---
if ($akcija === 'nova_rezervacija') {
    $ime = trim($input['ime'] ?? '');
    $gosti = intval($input['gosti'] ?? 0);
    $datum = trim($input['datum'] ?? '');

    if (empty($ime) || $gosti <= 0 || empty($datum)) {
        echo json_encode(["status" => "error", "message" => "Sva polja su obavezna."]);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO rezervacije (ime_prezime, broj_osoba, datum_vreme) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $ime, $gosti, $datum);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Uspešno ste rezervisali mesto!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Greška pri upisu u bazu."]);
    }
    $stmt->close();
}

// --- 2. OBRADA UTISKA (OCENE) ---
elseif ($akcija === 'nova_ocena') {
    $ime = trim($input['ime'] ?? '');
    $zvezdice = intval($input['zvezdice'] ?? 5);
    $komentar = trim($input['komentar'] ?? '');

    if (empty($ime) || empty($komentar)) {
        echo json_encode(["status" => "error", "message" => "Sva polja su obavezna za slanje utiska."]);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO ocene (ime, zvezdice, komentar) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $ime, $zvezdice, $komentar);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Hvala vam na utisku!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Greška pri upisu utiska."]);
    }
    $stmt->close();
} 

// Ako akcija ne postoji
else {
    echo json_encode(["status" => "error", "message" => "Nepoznata akcija."]);
}

$conn->close();

?>