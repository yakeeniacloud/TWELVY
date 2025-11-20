<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get city from query parameter
$city = isset($_GET['city']) ? strtoupper(trim($_GET['city'])) : null;

if (!$city) {
    http_response_code(400);
    echo json_encode(["error" => "City parameter required"]);
    exit;
}

try {
    $dsn = 'mysql:host=khapmaitpsp.mysql.db;dbname=khapmaitpsp;charset=utf8mb4';
    $pdo = new PDO($dsn, 'khapmaitpsp', 'Lretouiva1226', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    exit;
}

try {
    // STEP 1: Get coordinates for the searched city (any stage in that city)
    // Handle Paris arrondissements: treat "PARIS" search as any "PARIS*" city
    $cityPattern = $city;
    if ($city === 'PARIS') {
        $cityPattern = 'PARIS%'; // Match PARIS, PARIS-16EME, etc.
    } else {
        $cityPattern = $city; // Exact match for other cities
    }

    $coordStmt = $pdo->prepare("
        SELECT st.latitude, st.longitude
        FROM site st
        WHERE UPPER(st.ville) LIKE ?
          AND st.latitude IS NOT NULL
          AND st.longitude IS NOT NULL
        LIMIT 1
    ");
    $coordStmt->execute([$cityPattern]);
    $coordResult = $coordStmt->fetch(PDO::FETCH_ASSOC);

    if (!$coordResult) {
        // No coordinates found - fall back to exact city match only
        $stmt = $pdo->prepare("
            SELECT s.id, s.id_site, s.date1 as date_start, s.date2 as date_end, s.prix,
                   s.nb_places_allouees as nb_places, s.nb_inscrits, s.visible,
                   st.id as site_id, st.nom as site_nom, st.ville, st.adresse, st.code_postal, st.latitude, st.longitude
            FROM stage s
            JOIN site st ON s.id_site = st.id
            WHERE UPPER(st.ville) LIKE ?
              AND s.visible = 1
              AND s.annule = 0
              AND s.date1 >= CURDATE()
              AND s.date1 <= DATE_ADD(CURDATE(), INTERVAL 6 MONTH)
            ORDER BY s.date1 ASC
        ");
        $stmt->execute([$cityPattern]);
    } else {
        // STEP 2: Use Haversine formula in SQL to find all stages within 100km
        $centerLat = $coordResult['latitude'];
        $centerLon = $coordResult['longitude'];
        $radiusKm = 100;

        $stmt = $pdo->prepare("
            SELECT s.id, s.id_site, s.date1 as date_start, s.date2 as date_end, s.prix,
                   s.nb_places_allouees as nb_places, s.nb_inscrits, s.visible,
                   st.id as site_id, st.nom as site_nom, st.ville, st.adresse, st.code_postal, st.latitude, st.longitude,
                   (6371 * ACOS(
                       COS(RADIANS(?)) * COS(RADIANS(st.latitude)) *
                       COS(RADIANS(st.longitude) - RADIANS(?)) +
                       SIN(RADIANS(?)) * SIN(RADIANS(st.latitude))
                   )) AS distance_km
            FROM stage s
            JOIN site st ON s.id_site = st.id
            WHERE s.visible = 1
              AND s.annule = 0
              AND st.latitude IS NOT NULL
              AND st.longitude IS NOT NULL
              AND s.date1 >= CURDATE()
              AND s.date1 <= DATE_ADD(CURDATE(), INTERVAL 6 MONTH)
            HAVING distance_km <= ?
            ORDER BY distance_km ASC, s.date1 ASC
        ");
        $stmt->execute([$centerLat, $centerLon, $centerLat, $radiusKm]);
    }

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stages = array_map(function($stage) {
        return [
            'id' => (int)$stage['id'],
            'id_site' => (int)$stage['id_site'],
            'date_start' => $stage['date_start'],
            'date_end' => $stage['date_end'],
            'prix' => (float)$stage['prix'],
            'nb_places' => (int)$stage['nb_places'],
            'nb_inscrits' => (int)$stage['nb_inscrits'],
            'visible' => (int)$stage['visible'],
            'site' => [
                'id' => (int)$stage['site_id'],
                'nom' => $stage['site_nom'],
                'ville' => $stage['ville'],
                'adresse' => $stage['adresse'],
                'code_postal' => $stage['code_postal'],
                'latitude' => $stage['latitude'] ? (float)$stage['latitude'] : null,
                'longitude' => $stage['longitude'] ? (float)$stage['longitude'] : null
            ]
        ];
    }, $results);

    echo json_encode(["stages" => $stages, "city" => $city]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Query failed: " . $e->getMessage()]);
    exit;
}
?>
