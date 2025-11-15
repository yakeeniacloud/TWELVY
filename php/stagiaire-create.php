<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON"]);
    exit;
}

// Validate required fields
$required = ['id_stage', 'civilite', 'nom', 'prenom', 'adresse', 'code_postal', 'ville', 'date_naissance', 'email', 'mobile'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required field: $field"]);
        exit;
    }
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
    // Insert into stagiaire table
    $stmt = $pdo->prepare("
        INSERT INTO stagiaire (
            id_stage,
            civilite,
            nom,
            prenom,
            adresse,
            code_postal,
            ville,
            date_naissance,
            email,
            mobile,
            date_inscription,
            datetime_preinscription,
            status
        ) VALUES (
            :id_stage,
            :civilite,
            :nom,
            :prenom,
            :adresse,
            :code_postal,
            :ville,
            :date_naissance,
            :email,
            :mobile,
            NOW(),
            NOW(),
            'pending'
        )
    ");

    $result = $stmt->execute([
        ':id_stage' => $data['id_stage'],
        ':civilite' => $data['civilite'],
        ':nom' => $data['nom'],
        ':prenom' => $data['prenom'],
        ':adresse' => $data['adresse'],
        ':code_postal' => $data['code_postal'],
        ':ville' => $data['ville'],
        ':date_naissance' => $data['date_naissance'],
        ':email' => $data['email'],
        ':mobile' => $data['mobile']
    ]);

    if ($result) {
        $stagiaire_id = $pdo->lastInsertId();
        http_response_code(201);
        echo json_encode([
            "success" => true,
            "stagiaire_id" => $stagiaire_id,
            "message" => "Stagiaire créé avec succès"
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to insert stagiaire"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Query failed: " . $e->getMessage()]);
    exit;
}
?>
