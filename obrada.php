<?php
// obrada.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');

try {
    // VRAĆENO NA LOCALHOST - Tvoj kompjuter
    $host = "localhost";
    $user = "root";
    $password = ""; // Na XAMPP-u je ovde prazno
    $database = "restoran_medo"; // Tvoja lokalna baza

    $conn = new mysqli($host, $user, $password, $database);

    if ($conn->connect_error) {
        throw new Exception("Database Connection Error: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");

    // Prihvatanje JSON podataka poslatih preko JS fetch-a
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception("Nema poslatih podataka.");
    }

    $akcija = $input['akcija'] ?? '';
    
    if (empty($akcija)) {
        throw new Exception("Akcija nije specificirana.");
    }

    // --- TEST ENDPOINT ---
    if ($akcija === 'test') {
        echo json_encode(["status" => "success", "message" => "Konekcija radi!", "debug" => "obrada.php je na localhostu"]);
        exit();
    }

    // --- 1. OBRADA REZERVACIJE ---
    if ($akcija === 'nova_rezervacija') {
        $ime = trim($input['ime'] ?? '');
        $gosti = intval($input['gosti'] ?? 0);
        $datum = trim($input['datum'] ?? '');

        if (empty($ime) || $gosti <= 0 || empty($datum)) {
            throw new Exception("Sva polja su obavezna.");
        }

        $reservationTime = strtotime($datum);
        $currentTime = time();
        if ($reservationTime <= $currentTime) {
            throw new Exception("Rezervacija mora biti na budući datum i vreme.");
        }

        $stmt = $conn->prepare("INSERT INTO rezervacije (ime_prezime, broj_osoba, datum_vreme) VALUES (?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Prepare error: " . $conn->error);
        }
        
        $stmt->bind_param("sis", $ime, $gosti, $datum);

        if (!$stmt->execute()) {
            throw new Exception("Execute error: " . $stmt->error);
        }
        
        $stmt->close();
        echo json_encode(["status" => "success", "message" => "Uspešno ste rezervisali mesto!"]);
    }
    // --- 2. UCITAVANJE OCENA ---
    elseif ($akcija === 'ucitaj_ocene') {
        $result = $conn->query("SELECT ime, zvezdice, komentar FROM ocene ORDER BY id DESC");
        
        if (!$result) {
            throw new Exception("Query error: " . $conn->error);
        }
        
        $ocene = [];
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $ocene[] = $row;
            }
        }
        
        echo json_encode(["status" => "success", "ocene" => $ocene]);
    }
    // --- 3. OBRADA UTISKA (OCENE) ---
    elseif ($akcija === 'nova_ocena') {
        $ime = trim($input['ime'] ?? '');
        $zvezdice = intval($input['zvezdice'] ?? 5);
        $komentar = trim($input['komentar'] ?? '');

        if (empty($ime) || empty($komentar)) {
            throw new Exception("Sva polja su obavezna za slanje utiska.");
        }

        $stmt = $conn->prepare("INSERT INTO ocene (ime, zvezdice, komentar) VALUES (?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Prepare error: " . $conn->error);
        }
        
        $stmt->bind_param("sis", $ime, $zvezdice, $komentar);

        if (!$stmt->execute()) {
            throw new Exception("Execute error: " . $stmt->error);
        }
        
        $stmt->close();
        echo json_encode(["status" => "success", "message" => "Hvala vam na utisku!"]);
    }
    else {
        throw new Exception("Nepoznata akcija: " . $akcija);
    }

    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => $e->getMessage(),
        "debug_code" => $e->getCode()
    ]);
}
?>