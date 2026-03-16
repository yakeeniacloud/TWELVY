<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Accept: ?dept_codes=13,83,04,05,06 (comma-separated, validated whitelist)
$dept_codes_raw = isset($_GET['dept_codes']) ? trim($_GET['dept_codes']) : '';

if (!$dept_codes_raw) {
    http_response_code(400);
    echo json_encode(["error" => "dept_codes parameter required"]);
    exit;
}

// Whitelist of all valid French department codes (security: prevents SQL injection via any code not in list)
$valid_codes = [
    '01','02','03','04','05','06','07','08','09','10',
    '11','12','13','14','15','16','17','18','19',
    '2A','2B',
    '21','22','23','24','25','26','27','28','29','30',
    '31','32','33','34','35','36','37','38','39','40',
    '41','42','43','44','45','46','47','48','49','50',
    '51','52','53','54','55','56','57','58','59','60',
    '61','62','63','64','65','66','67','68','69','70',
    '71','72','73','74','75','76','77','78','79','80',
    '81','82','83','84','85','86','87','88','89','90',
    '91','92','93','94','95',
    '971','972','973','974','976'
];

// Parse and validate dept codes
$input_codes = explode(',', $dept_codes_raw);
$dept_codes = [];
foreach ($input_codes as $code) {
    $code = trim(strtoupper($code));
    if (in_array($code, $valid_codes)) {
        $dept_codes[] = $code;
    }
}

if (empty($dept_codes)) {
    http_response_code(400);
    echo json_encode(["error" => "No valid dept_codes provided"]);
    exit;
}

// Database connection
$env = getenv('ENV') ?: 'production';

if ($env === 'local') {
    $dsn = 'mysql:host=127.0.0.1;dbname=khapmaitpsp;charset=utf8mb4';
    $username = 'root';
    $password = '';
} else {
    $dsn = 'mysql:host=khapmaitpsp.mysql.db;dbname=khapmaitpsp;charset=utf8mb4';
    $username = 'khapmaitpsp';
    $password = 'Lretouiva1226';
}

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

try {
    // Build WHERE clause per department code
    // Special cases:
    //   2A (Corse-du-Sud): postal codes 20000-20199
    //   2B (Haute-Corse):  postal codes 20200-20999
    //   DOM-TOM (971/972/973/974/976): first 3 chars of postal code
    //   Standard: first 2 chars of postal code
    $conditions = [];
    $params = [];

    foreach ($dept_codes as $code) {
        if ($code === '2A') {
            $conditions[] = "(st.code_postal LIKE '20%' AND st.code_postal < '20200')";
        } elseif ($code === '2B') {
            $conditions[] = "(st.code_postal LIKE '20%' AND st.code_postal >= '20200')";
        } elseif (strlen($code) === 3) {
            // DOM-TOM: 971, 972, 973, 974, 976
            $conditions[] = "LEFT(st.code_postal, 3) = ?";
            $params[] = $code;
        } else {
            // Standard 2-digit department code
            $conditions[] = "LEFT(st.code_postal, 2) = ?";
            $params[] = $code;
        }
    }

    $where = '(' . implode(' OR ', $conditions) . ')';

    $sql = "
        SELECT s.id, s.id_site, s.date1 as date_start, s.date2 as date_end, s.prix,
               s.nb_places_allouees as nb_places, s.nb_inscrits, s.visible,
               st.id as site_id, st.nom as site_nom, st.ville, st.adresse, st.code_postal,
               st.latitude, st.longitude
        FROM stage s
        JOIN site st ON s.id_site = st.id
        WHERE $where
          AND s.visible = 1
          AND s.annule = 0
          AND s.date1 >= CURDATE()
          AND s.date1 <= DATE_ADD(CURDATE(), INTERVAL 6 MONTH)
        ORDER BY s.date1 ASC, st.ville ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stages = array_map(function($stage) {
        return [
            'id'         => (int)$stage['id'],
            'id_site'    => (int)$stage['id_site'],
            'date_start' => $stage['date_start'],
            'date_end'   => $stage['date_end'],
            'prix'       => (float)$stage['prix'],
            'nb_places'  => (int)$stage['nb_places'],
            'nb_inscrits'=> (int)$stage['nb_inscrits'],
            'visible'    => (int)$stage['visible'],
            'site' => [
                'id'          => (int)$stage['site_id'],
                'nom'         => $stage['site_nom'],
                'ville'       => $stage['ville'],
                'adresse'     => $stage['adresse'],
                'code_postal' => $stage['code_postal'],
                'latitude'    => $stage['latitude']  ? (float)$stage['latitude']  : null,
                'longitude'   => $stage['longitude'] ? (float)$stage['longitude'] : null,
            ]
        ];
    }, $results);

    echo json_encode(["stages" => $stages]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Query failed: " . $e->getMessage()]);
    exit;
}
?>
