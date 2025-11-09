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
    $stmt = $pdo->prepare("
        SELECT s.id, s.id_site, s.date1 as date_start, s.date2 as date_end, s.prix,
               s.nb_places_allouees as nb_places, s.nb_inscrits, s.visible,
               st.id as site_id, st.nom as site_nom, st.ville, st.adresse, st.code_postal, st.latitude, st.longitude
        FROM stage s
        JOIN site st ON s.id_site = st.id
        WHERE UPPER(st.ville) = ? AND s.visible = 1 AND s.annule = 0
        ORDER BY s.date1 ASC
    ");
    $stmt->execute([$city]);
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
