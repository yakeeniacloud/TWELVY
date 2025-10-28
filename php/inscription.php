<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// API Key validation
$apiKey = '82193ec2e06757dc73f34785a0f46df12e88250430dc72927befb128ef4fb496';
$headers = getallheaders();

if (!isset($headers['X-Api-Key']) || $headers['X-Api-Key'] !== $apiKey) {
    http_response_code(401);
    echo json_encode(["ok" => false, "error" => "Invalid API key"]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['prenom'], $input['nom'], $input['email'], $input['telephone'], $input['stage_id'])) {
    http_response_code(400);
    echo json_encode(["ok" => false, "error" => "Missing required fields"]);
    exit;
}

// Connect to database
try {
    $dsn = 'mysql:host=khapmaitpsp.mysql.db;dbname=khapmaitpsp;charset=utf8mb4';
    $pdo = new PDO($dsn, 'khapmaitpsp', 'Lretouiva1226', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => "Database connection failed: " . $e->getMessage()]);
    exit;
}

// Generate booking reference
$year = date('Y');
$stmt = $pdo->query("SELECT COUNT(*) as count FROM stage_bookings WHERE booking_reference LIKE 'BK-$year-%'");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$count = $result['count'] + 1;
$bookingRef = sprintf('BK-%s-%06d', $year, $count);

// Generate UUID
$id = sprintf(
    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
    mt_rand(0, 0x0fff) | 0x4000,
    mt_rand(0, 0x3fff) | 0x8000,
    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
);

// Insert into database
try {
    $stmt = $pdo->prepare("
        INSERT INTO stage_bookings (id, stage_id, booking_reference, prenom, nom, email, telephone, created_at, updated_at)
        VALUES (:id, :stage_id, :booking_reference, :prenom, :nom, :email, :telephone, NOW(), NOW())
    ");

    $stmt->execute([
        ':id' => $id,
        ':stage_id' => $input['stage_id'],
        ':booking_reference' => $bookingRef,
        ':prenom' => $input['prenom'],
        ':nom' => $input['nom'],
        ':email' => $input['email'],
        ':telephone' => $input['telephone']
    ]);

    echo json_encode([
        "ok" => true,
        "id" => $id,
        "booking_reference" => $bookingRef
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => "Insert failed: " . $e->getMessage()]);
    exit;
}
?>
