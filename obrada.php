<?php
// obrada.php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');

// DATABASE CONNECTION - InfinityFree
$host = "sql301.infinityfree.com";
$user = "if0_42199738";
$password = "kodmeda123";
$database = "if0_42199738_restoran_medo";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    http_response_code(500);
    error_log("Database Connection Error: " . $conn->connect_error);
    echo json_encode(["status" => "error", "message" => "Greška sa bazom: " . $conn->connect_error]);
    exit();
}

$conn->set_charset("utf8mb4");

// Prihvatanje JSON podataka poslatih preko JS fetch-a
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    error_log("Invalid JSON input received");
    echo json_encode(["status" => "error", "message" => "Nema poslatih podataka."]);
    exit();
}

$akcija = $input['akcija'] ?? '';
error_log("Akcija: " . $akcija);

// --- TEST ENDPOINT ---
if ($akcija === 'test') {
    echo json_encode(["status" => "success", "message" => "Konekcija radi!", "debug" => "obrada.php je dostupan"]);
    exit();
}

// --- 1. OBRADA REZERVACIJE ---
if ($akcija === 'nova_rezervacija') {
    $ime = trim($input['ime'] ?? '');
    $gosti = intval($input['gosti'] ?? 0);
    $datum = trim($input['datum'] ?? '');

    if (empty($ime) || $gosti <= 0 || empty($datum)) {
        echo json_encode(["status" => "error", "message" => "Sva polja su obavezna."]);
        exit();
    }

    // Provera da li je datum u budućnosti
    $reservationTime = strtotime($datum);
    $currentTime = time();
    if ($reservationTime <= $currentTime) {
        echo json_encode(["status" => "error", "message" => "Rezervacija mora biti na budući datum i vreme."]);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO rezervacije (ime_prezime, broj_osoba, datum_vreme) VALUES (?, ?, ?)");
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(["status" => "error", "message" => "Greška pri pripremi upita: " . $conn->error]);
        exit();
    }
    
    $stmt->bind_param("sis", $ime, $gosti, $datum);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Uspešno ste rezervisali mesto!"]);
    } else {
        error_log("Execute failed: " . $stmt->error);
        echo json_encode(["status" => "error", "message" => "Greška pri upisu u bazu: " . $stmt->error]);
    }
    $stmt->close();
}

// --- 2. UCITAVANJE OCENA ---
elseif ($akcija === 'ucitaj_ocene') {
    try {
        $result = $conn->query("SELECT ime, zvezdice, komentar FROM ocene ORDER BY id DESC");
        $ocene = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $ocene[] = $row;
            }
        }
        
        echo json_encode(["status" => "success", "ocene" => $ocene]);
    } catch (Exception $e) {
        error_log("Query error: " . $e->getMessage());
        echo json_encode(["status" => "error", "message" => "Greška pri učitavanju ocena: " . $e->getMessage()]);
    }
}

// --- 3. OBRADA UTISKA (OCENE) ---
elseif ($akcija === 'nova_ocena') {
    $ime = trim($input['ime'] ?? '');
    $zvezdice = intval($input['zvezdice'] ?? 5);
    $komentar = trim($input['komentar'] ?? '');

    if (empty($ime) || empty($komentar)) {
        echo json_encode(["status" => "error", "message" => "Sva polja su obavezna za slanje utiska."]);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO ocene (ime, zvezdice, komentar) VALUES (?, ?, ?)");
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(["status" => "error", "message" => "Greška pri pripremi upita: " . $conn->error]);
        exit();
    }
    
    $stmt->bind_param("sis", $ime, $zvezdice, $komentar);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Hvala vam na utisku!"]);
    } else {
        error_log("Execute failed: " . $stmt->error);
        echo json_encode(["status" => "error", "message" => "Greška pri upisu utiska: " . $stmt->error]);
    }
    $stmt->close();
} 

// Ako akcija ne postoji
else {
    echo json_encode(["status" => "error", "message" => "Nepoznata akcija."]);
}

$conn->close();

?>