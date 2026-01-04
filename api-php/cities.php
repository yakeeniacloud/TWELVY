<?php
/**
 * Cities API - Returns all cities with their postal codes
 * Deploy to: api.twelvy.net/cities.php
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
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
    // Query to get unique cities with their postal codes from site table
    // Groups by city name and takes one postal code per city
    $sql = "SELECT DISTINCT
                UPPER(ville) as name,
                code_postal as postal
            FROM site
            WHERE ville IS NOT NULL
              AND ville != ''
              AND code_postal IS NOT NULL
              AND code_postal != ''
            GROUP BY UPPER(ville)
            ORDER BY name ASC";

    $stmt = $pdo->query($sql);
    $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return JSON response with cities array containing name and postal
    echo json_encode(["cities" => $cities], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Query failed: " . $e->getMessage()]);
    exit;
}
?>
