<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get stage ID from query parameter
$stageId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$stageId) {
    http_response_code(400);
    echo json_encode(["error" => "Stage ID parameter required"]);
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
        WHERE s.id = ? AND s.visible = 1 AND s.annule = 0
    ");
    $stmt->execute([$stageId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        http_response_code(404);
        echo json_encode(["error" => "Stage not found"]);
        exit;
    }

    $stage = [
        'id' => (int)$result['id'],
        'id_site' => (int)$result['id_site'],
        'date_start' => $result['date_start'],
        'date_end' => $result['date_end'],
        'prix' => (float)$result['prix'],
        'nb_places' => (int)$result['nb_places'],
        'nb_inscrits' => (int)$result['nb_inscrits'],
        'visible' => (int)$result['visible'],
        'site' => [
            'id' => (int)$result['site_id'],
            'nom' => $result['site_nom'],
            'ville' => $result['ville'],
            'adresse' => $result['adresse'],
            'code_postal' => $result['code_postal'],
            'latitude' => $result['latitude'] ? (float)$result['latitude'] : null,
            'longitude' => $result['longitude'] ? (float)$result['longitude'] : null
        ]
    ];

    echo json_encode($stage);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Query failed: " . $e->getMessage()]);
    exit;
}
?>
