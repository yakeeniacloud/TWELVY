<?php
/**
 * Cities API - Returns all cities with their postal codes
 * Deploy to: api.twelvy.net/cities.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection - update with your credentials
$host = 'localhost';
$dbname = 'your_database_name';
$username = 'your_username';
$password = 'your_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to get unique cities with their postal codes from sites table
    // Groups by city name and takes one postal code per city
    $sql = "SELECT DISTINCT
                UPPER(s.ville) as name,
                s.code_postal as postal
            FROM sites s
            WHERE s.ville IS NOT NULL
              AND s.ville != ''
              AND s.code_postal IS NOT NULL
              AND s.code_postal != ''
            GROUP BY UPPER(s.ville)
            ORDER BY name ASC";

    $stmt = $pdo->query($sql);
    $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return JSON response with cities array containing name and postal
    echo json_encode([
        'cities' => $cities
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database connection failed',
        'message' => $e->getMessage()
    ]);
}
?>
