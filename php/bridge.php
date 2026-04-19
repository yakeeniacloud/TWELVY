<?php
// =========================================================================
// bridge.php — Twelvy ↔ OVH gateway
// PHP 5.6 compatible
// VERSIONED in git (no secrets — only routing logic)
// Lives at https://api.twelvy.net/bridge.php
// =========================================================================
//
// Architecture role : single entry point that Next.js calls. Authenticates
// the caller via X-Api-Key header, then dispatches to one of the registered
// actions (?action=xxx). Each action returns a standardised JSON response.
//
// Hardened 2026-04-19 after security review (H1-H4 + M1-M5 + L4 fixed).
//
// Étape 5 scope (this file's current state) :
//   - Load config + secrets
//   - Verify X-Api-Key against BRIDGE_SECRET_TOKEN (constant-time + length-safe)
//   - Restrict CORS to BRIDGE_CORS_ORIGIN (with Vary: Origin)
//   - Method allowlist (GET, POST, OPTIONS only)
//   - Implement ONE action : "ping" → returns "pong" + meta
//
// Future scope (Étapes 6-7) :
//   - action=create_or_update_prospect  → INSERT/UPDATE stagiaire
//   - action=prepare_payment            → build PBX_* + sign HMAC
//   - action=get_stagiaire_status       → read DB, return statut + recap
// =========================================================================

// -------------------------------------------------------------------------
// Step 0 — Defensive PHP settings (in case OVH php.ini is misconfigured)
// -------------------------------------------------------------------------
@ini_set('display_errors', '0');
@ini_set('log_errors', '1');
error_reporting(E_ALL);

// -------------------------------------------------------------------------
// Step 1 — Load config (which loads secrets). Both config files refuse
// direct URL access unless TWELVY_BRIDGE is defined first.
// -------------------------------------------------------------------------
define('TWELVY_BRIDGE', true);
require_once __DIR__ . '/config_paiement.php';

// -------------------------------------------------------------------------
// Step 2 — Set HTTP headers for the response
// -------------------------------------------------------------------------
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: ' . BRIDGE_CORS_ORIGIN);
header('Vary: Origin');                                 // H3 — prevent CDN cache poisoning
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Api-Key');
header('Access-Control-Max-Age: 86400');
header('Cache-Control: no-store, no-cache, must-revalidate, private');  // M5
header('Pragma: no-cache');                                              // M5
header('Expires: 0');                                                    // M5
header('X-Content-Type-Options: nosniff');             // M3 — prevent MIME sniffing
header('Referrer-Policy: no-referrer');
header('X-Frame-Options: DENY');
header('X-Robots-Tag: noindex, nofollow');

// -------------------------------------------------------------------------
// Step 3 — Standardised JSON response helpers
// (H1 fix : two clearly separated functions, no auto-success magic)
// -------------------------------------------------------------------------
function bridge_send_success($data, $status_code) {
    http_response_code($status_code);
    echo json_encode(
        array('success' => true, 'data' => $data),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT  // M3
    );
    exit;
}

function bridge_send_error($error_code, $status_code, $details) {
    http_response_code($status_code);
    $resp = array('success' => false, 'error' => $error_code);
    if ($details !== null) {
        $resp['details'] = $details;
    }
    echo json_encode(
        $resp,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT  // M3
    );
    exit;
}

// -------------------------------------------------------------------------
// Step 4 — Method allowlist (H4 fix : reject HEAD/PUT/DELETE/PATCH/TRACE)
// Done BEFORE auth so unauthorized methods get a clean 405 fast.
// -------------------------------------------------------------------------
$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
$allowed_methods = array('GET', 'POST', 'OPTIONS');
if (!in_array($method, $allowed_methods, true)) {
    header('Allow: GET, POST, OPTIONS');
    bridge_send_error('method_not_allowed', 405, array('method' => $method));
}

// -------------------------------------------------------------------------
// Step 5 — Handle CORS preflight (browser sends OPTIONS first)
// -------------------------------------------------------------------------
if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// -------------------------------------------------------------------------
// Step 6 — Read X-Api-Key from request headers (multi-fallback for compat)
// -------------------------------------------------------------------------
$api_key = '';
if (function_exists('getallheaders')) {
    $headers = getallheaders();
    if (is_array($headers)) {
        foreach ($headers as $k => $v) {
            if (strcasecmp($k, 'X-Api-Key') === 0) {
                $api_key = $v;
                break;
            }
        }
    }
}
if ($api_key === '' && isset($_SERVER['HTTP_X_API_KEY'])) {
    $api_key = $_SERVER['HTTP_X_API_KEY'];
}

// M1 — strip trailing whitespace in case a proxy added some
$api_key = is_string($api_key) ? trim($api_key) : '';

// M2 — reject any non-printable / control char (defensive)
if ($api_key !== '' && preg_match('/[^\x21-\x7E]/', $api_key)) {
    error_log('[bridge.php] Unauthorized: control chars in X-Api-Key from ' . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '?'));
    bridge_send_error('unauthorized', 403, null);
}

// -------------------------------------------------------------------------
// Step 7 — Verify X-Api-Key (H2 fix : SHA-256 both sides → equal lengths
// → no early-return length leak. Then hash_equals = timing-safe compare.)
// -------------------------------------------------------------------------
$expected_hash = hash('sha256', BRIDGE_SECRET_TOKEN);
$received_hash = hash('sha256', $api_key);
if ($api_key === '' || !hash_equals($expected_hash, $received_hash)) {
    error_log('[bridge.php] Unauthorized call from ' . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '?'));
    bridge_send_error('unauthorized', 403, null);
}

// =========================================================================
// HELPERS — DB connection, body parser, HMAC computation
// =========================================================================

/**
 * Lazy-loaded PDO connection to khapmaitpsp MySQL.
 * UTF-8 enforced at connection level.
 */
function bridge_db() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_DB . ';charset=utf8mb4',
                MYSQL_USER,
                MYSQL_PASSWORD,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                )
            );
        } catch (Exception $e) {
            error_log('[bridge.php] DB connection failed: ' . $e->getMessage());
            bridge_send_error('db_unavailable', 503, null);
        }
    }
    return $pdo;
}

/**
 * Read POST body — accepts both JSON and form-encoded. Falls back to $_POST.
 * Caps body size at 64 KB (defense — Étape 6 actions don't need more).
 */
function bridge_read_body() {
    if (isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 65536) {
        bridge_send_error('payload_too_large', 413, null);
    }
    $raw = file_get_contents('php://input');
    if ($raw === '' || $raw === false) {
        return is_array($_POST) ? $_POST : array();
    }
    $json = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
        return $json;
    }
    return is_array($_POST) ? $_POST : array();
}

/**
 * Compute HMAC-SHA-512 of $params concatenated as "K=V&K=V&..." in their array order.
 * Uses the env-active UP2PAY_HMAC_KEY (TEST or PROD per UP2PAY_ENV).
 * Returns uppercase hex string (128 chars).
 *
 * IMPORTANT : the order of $params MUST match the order of <input> fields
 * Next.js will submit. Up2Pay re-computes the HMAC on its side using the
 * exact same string and rejects on mismatch.
 */
function bridge_compute_pbx_hmac($params) {
    $msg = '';
    foreach ($params as $k => $v) {
        $msg .= ($msg === '' ? '' : '&') . $k . '=' . $v;
    }
    $bin_key = pack('H*', UP2PAY_HMAC_KEY);
    return strtoupper(hash_hmac('sha512', $msg, $bin_key));
}

/**
 * Mini map : Up2Pay error codes → UX category + user-friendly message.
 * Subset only — full mapping in errors.csv (76 codes). Will load CSV later.
 */
function bridge_classify_up2pay_error($code) {
    $code = (string)$code;
    $card_input_codes  = array('00007', '00008', '00020', '00114', '00115', '00133');
    $bank_refuse_codes = array('00021', '00022', '00151', '00163', '00187');
    $threeds_codes     = array('00204', '00205', '00207');
    $tech_codes        = array('00001', '00002', '00003');

    if (in_array($code, $card_input_codes, true)) {
        return array('category' => 'erreur_saisie_carte', 'message' => 'Numéro, date ou cryptogramme erroné. Vérifiez vos informations bancaires et réessayez.');
    }
    if (in_array($code, $bank_refuse_codes, true)) {
        return array('category' => 'refus_banque', 'message' => "Votre banque n'a pas autorisé le paiement. Réessayez avec une autre carte.");
    }
    if (in_array($code, $threeds_codes, true)) {
        return array('category' => 'probleme_3ds', 'message' => "Échec de l'authentification 3D Secure. Réessayez.");
    }
    if (in_array($code, $tech_codes, true)) {
        return array('category' => 'erreur_technique', 'message' => 'Plateforme momentanément indisponible. Réessayez plus tard.');
    }
    return array('category' => 'erreur_inconnue', 'message' => 'Une erreur est survenue lors du paiement. Code : ' . $code);
}

// -------------------------------------------------------------------------
// Step 8 — Read action from query string (and optionally body for POST)
// -------------------------------------------------------------------------
$action = '';
if (isset($_GET['action'])) {
    $action = $_GET['action'];
} elseif (isset($_POST['action'])) {
    $action = $_POST['action'];
}
$action = is_string($action) ? trim($action) : '';
// M3 — cap action length to prevent log/response bloat
if (strlen($action) > 64) {
    bridge_send_error('action_too_long', 400, null);
}

// -------------------------------------------------------------------------
// Step 9 — Action router
// -------------------------------------------------------------------------
switch ($action) {

    // ---------------------------------------------------------------------
    // ping — health check, returns metadata. No DB, no Up2Pay.
    // ---------------------------------------------------------------------
    case 'ping':
        bridge_send_success(array(
            'message'      => 'pong',
            'environment'  => UP2PAY_ENV,         // 'test' or 'prod'
            'php_version'  => PHP_VERSION,
            'timestamp'    => date('c'),          // ISO-8601 with timezone
            'bridge_ready' => true,
        ), 200);
        break;

    // ---------------------------------------------------------------------
    // create_or_update_prospect — Step 1 of inscription form
    // INSERT or UPDATE into stagiaire with status='pre-inscrit'
    // Returns stagiaire_id + booking_reference
    // ---------------------------------------------------------------------
    case 'create_or_update_prospect':
        $body = bridge_read_body();
        $required = array('civilite', 'nom', 'prenom', 'email', 'mobile', 'stage_id');
        foreach ($required as $f) {
            if (empty($body[$f])) {
                bridge_send_error('missing_field', 400, array('field' => $f));
            }
        }
        $civilite      = (string)$body['civilite'];
        $nom           = (string)$body['nom'];
        $prenom        = (string)$body['prenom'];
        $email         = trim((string)$body['email']);
        $mobile        = (string)$body['mobile'];
        $stage_id      = (int)$body['stage_id'];
        $adresse       = isset($body['adresse']) ? (string)$body['adresse'] : '';
        $code_postal   = isset($body['code_postal']) ? (string)$body['code_postal'] : '';
        $ville         = isset($body['ville']) ? (string)$body['ville'] : '';
        $date_naiss    = isset($body['date_naissance']) ? (string)$body['date_naissance'] : '';
        $cgv_ok        = !empty($body['cgv_accepted']) ? 1 : 0;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            bridge_send_error('invalid_email', 400, null);
        }
        if ($stage_id <= 0) {
            bridge_send_error('invalid_stage_id', 400, null);
        }

        $pdo = bridge_db();
        try {
            // Look up stage to confirm it exists + get prix
            $stmt = $pdo->prepare("SELECT id, prix, id_membre FROM stage WHERE id = :sid LIMIT 1");
            $stmt->execute(array(':sid' => $stage_id));
            $stage = $stmt->fetch();
            if (!$stage) {
                bridge_send_error('stage_not_found', 404, array('stage_id' => $stage_id));
            }

            // Check if a prospect already exists for this email + stage_id
            $stmt = $pdo->prepare("SELECT id FROM stagiaire WHERE email = :email AND id_stage = :sid LIMIT 1");
            $stmt->execute(array(':email' => $email, ':sid' => $stage_id));
            $existing = $stmt->fetch();

            $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

            if ($existing) {
                // UPDATE existing prospect (refresh data + reset to pre-inscrit if not paid)
                $stmt = $pdo->prepare(
                    "UPDATE stagiaire SET
                        civilite = :civilite, nom = :nom, prenom = :prenom,
                        mobile = :mobile, adresse = :adresse, code_postal = :cp,
                        ville = :ville, date_naissance = :dnais,
                        autoriseDonneesPersonnelles = :cgv,
                        ip = :ip, last_timestamp = NOW()
                     WHERE id = :id"
                );
                $stmt->execute(array(
                    ':civilite' => $civilite, ':nom' => $nom, ':prenom' => $prenom,
                    ':mobile' => $mobile, ':adresse' => $adresse, ':cp' => $code_postal,
                    ':ville' => $ville, ':dnais' => $date_naiss,
                    ':cgv' => $cgv_ok, ':ip' => $ip,
                    ':id' => $existing['id'],
                ));
                $stagiaire_id = (int)$existing['id'];
            } else {
                // INSERT new prospect
                $stmt = $pdo->prepare(
                    "INSERT INTO stagiaire (
                        id_stage, civilite, nom, prenom, email, mobile,
                        adresse, code_postal, ville, date_naissance,
                        date_inscription, date_preinscription, datetime_preinscription,
                        status, supprime, paiement, ip,
                        autoriseDonneesPersonnelles, provenance_site
                     ) VALUES (
                        :sid, :civilite, :nom, :prenom, :email, :mobile,
                        :adresse, :cp, :ville, :dnais,
                        '0000-00-00', CURDATE(), NOW(),
                        'pre-inscrit', 0, :prix, :ip,
                        :cgv, 1
                     )"
                );
                $stmt->execute(array(
                    ':sid' => $stage_id, ':civilite' => $civilite, ':nom' => $nom,
                    ':prenom' => $prenom, ':email' => $email, ':mobile' => $mobile,
                    ':adresse' => $adresse, ':cp' => $code_postal, ':ville' => $ville,
                    ':dnais' => $date_naiss, ':prix' => (int)$stage['prix'],
                    ':ip' => $ip, ':cgv' => $cgv_ok,
                ));
                $stagiaire_id = (int)$pdo->lastInsertId();
            }

            $booking_ref = 'BK-' . date('Y') . '-' . str_pad($stagiaire_id, 6, '0', STR_PAD_LEFT);

            bridge_send_success(array(
                'stagiaire_id'      => $stagiaire_id,
                'booking_reference' => $booking_ref,
                'mode'              => $existing ? 'updated' : 'created',
            ), 200);
        } catch (Exception $e) {
            error_log('[bridge.php] create_or_update_prospect failed: ' . $e->getMessage());
            bridge_send_error('db_error', 500, null);
        }
        break;

    // ---------------------------------------------------------------------
    // prepare_payment — Step 2 of inscription form (after click "Payer")
    // Generates num_suivi, builds all PBX_* params, signs HMAC.
    // Returns paymentUrl + paymentFields (Next.js auto-submits to Up2Pay).
    // ---------------------------------------------------------------------
    case 'prepare_payment':
        $body = bridge_read_body();
        if (empty($body['stagiaire_id'])) {
            bridge_send_error('missing_field', 400, array('field' => 'stagiaire_id'));
        }
        $stagiaire_id = (int)$body['stagiaire_id'];
        if ($stagiaire_id <= 0) {
            bridge_send_error('invalid_stagiaire_id', 400, null);
        }

        $pdo = bridge_db();
        try {
            // Fetch stagiaire + stage info
            $stmt = $pdo->prepare(
                "SELECT s.id, s.email, s.id_stage, s.paiement,
                        st.prix, st.id_membre, st.date1
                 FROM stagiaire s
                 INNER JOIN stage st ON st.id = s.id_stage
                 WHERE s.id = :id LIMIT 1"
            );
            $stmt->execute(array(':id' => $stagiaire_id));
            $row = $stmt->fetch();
            if (!$row) {
                bridge_send_error('stagiaire_not_found', 404, null);
            }
            $amount_eur = (int)$row['prix'];
            if ($amount_eur <= 0) {
                bridge_send_error('invalid_amount', 500, null);
            }
            $amount_cents = $amount_eur * 100;

            // Generate num_suivi via facture_id atomic counter
            $stmt = $pdo->prepare("INSERT INTO facture_id (id_stagiaire) VALUES (:id)");
            $stmt->execute(array(':id' => $stagiaire_id));
            $num_suivi = (int)$pdo->lastInsertId() + 1000;
            $reference = UP2PAY_REFERENCE_PREFIX . $num_suivi;

            // INSERT order_stage row (NOT touching transaction table — confirmed dead)
            $stmt = $pdo->prepare(
                "INSERT INTO order_stage (user_id, amount, is_paid, num_suivi, stage_id, reference_order, created)
                 VALUES (:uid, :amt, 0, :ns, :sid, :ref, NOW())"
            );
            $stmt->execute(array(
                ':uid' => $stagiaire_id, ':amt' => $amount_eur,
                ':ns' => $num_suivi, ':sid' => (int)$row['id_stage'],
                ':ref' => $reference,
            ));

            // Build PBX_* params — ORDER MATTERS for HMAC
            $params = array(
                'PBX_SITE'        => UP2PAY_SITE_ID,
                'PBX_RANG'        => UP2PAY_RANG,
                'PBX_IDENTIFIANT' => UP2PAY_IDENTIFIANT,
                'PBX_TOTAL'       => (string)$amount_cents,
                'PBX_DEVISE'      => UP2PAY_DEVISE,
                'PBX_CMD'         => $reference,
                'PBX_PORTEUR'     => $row['email'],
                'PBX_RETOUR'      => UP2PAY_RETOUR,
                'PBX_HASH'        => UP2PAY_HASH,
                'PBX_TIME'        => gmdate('c'),
                'PBX_LANGUE'      => UP2PAY_LANGUE,
                'PBX_REPONDRE_A'  => UP2PAY_AUTOMATIC_RESPONSE_URL,
                'PBX_RUF1'        => 'POST',
                'PBX_EFFECTUE'    => UP2PAY_NORMAL_RETURN_URL . '?status=ok&id=' . $stagiaire_id,
                'PBX_REFUSE'      => UP2PAY_NORMAL_RETURN_URL . '?status=refuse&id=' . $stagiaire_id,
                'PBX_ANNULE'      => UP2PAY_NORMAL_RETURN_URL . '?status=annule&id=' . $stagiaire_id,
            );

            // Compute HMAC over params (order = same as form submission order)
            $params['PBX_HMAC'] = bridge_compute_pbx_hmac($params);

            bridge_send_success(array(
                'stagiaire_id'  => $stagiaire_id,
                'paymentUrl'    => UP2PAY_PAYMENT_URL,
                'paymentFields' => $params,
                'environment'   => UP2PAY_ENV,
                'reference'     => $reference,
                'amount_eur'    => $amount_eur,
            ), 200);
        } catch (Exception $e) {
            error_log('[bridge.php] prepare_payment failed: ' . $e->getMessage());
            bridge_send_error('db_error', 500, null);
        }
        break;

    // ---------------------------------------------------------------------
    // get_stagiaire_status — read DB, return statut + recap
    // Used by the Next.js confirmation page (polling) and error page.
    // ---------------------------------------------------------------------
    case 'get_stagiaire_status':
        $body = bridge_read_body();
        $stagiaire_id = 0;
        if (!empty($body['stagiaire_id'])) {
            $stagiaire_id = (int)$body['stagiaire_id'];
        } elseif (!empty($_GET['id'])) {
            $stagiaire_id = (int)$_GET['id'];
        }
        if ($stagiaire_id <= 0) {
            bridge_send_error('missing_field', 400, array('field' => 'stagiaire_id'));
        }

        $pdo = bridge_db();
        try {
            $stmt = $pdo->prepare(
                "SELECT s.id, s.nom, s.prenom, s.email, s.status, s.numappel, s.numtrans,
                        s.paiement, s.up2pay_status, s.up2pay_code_error,
                        s.date_inscription, s.facture_num,
                        st.id AS stage_id, st.date1 AS date_debut, st.date2 AS date_fin, st.prix,
                        si.nom AS lieu_nom, si.adresse AS lieu_adresse,
                        si.ville AS lieu_ville, si.code_postal AS lieu_cp
                 FROM stagiaire s
                 INNER JOIN stage st ON st.id = s.id_stage
                 LEFT JOIN site si ON si.id = st.id_site
                 WHERE s.id = :id LIMIT 1"
            );
            $stmt->execute(array(':id' => $stagiaire_id));
            $row = $stmt->fetch();
            if (!$row) {
                bridge_send_error('stagiaire_not_found', 404, null);
            }

            // Determine simple status enum
            $status_simple = 'en_attente';
            $error_category = null;
            $error_message  = null;
            if ($row['status'] === 'inscrit' && !empty($row['numappel']) && !empty($row['numtrans'])) {
                $status_simple = 'paye';
            } elseif (!empty($row['up2pay_code_error'])) {
                $status_simple = 'refuse';
                $err = bridge_classify_up2pay_error($row['up2pay_code_error']);
                $error_category = $err['category'];
                $error_message  = $err['message'];
            } elseif ($row['status'] === 'pre-inscrit') {
                $status_simple = 'en_attente';
            } elseif ($row['status'] === 'supprime') {
                $status_simple = 'refuse';
            }

            $resp = array(
                'status' => $status_simple,
                'stagiaire' => array(
                    'id'       => (int)$row['id'],
                    'nom'      => $row['nom'],
                    'prenom'   => $row['prenom'],
                    'email'    => $row['email'],
                    'paiement' => (int)$row['paiement'],
                    'facture_num' => (int)$row['facture_num'],
                ),
                'stage' => array(
                    'id'         => (int)$row['stage_id'],
                    'date_debut' => $row['date_debut'],
                    'date_fin'   => $row['date_fin'],
                    'prix'       => (int)$row['prix'],
                    'lieu_nom'   => $row['lieu_nom'],
                    'lieu_adresse' => $row['lieu_adresse'],
                    'lieu_ville' => $row['lieu_ville'],
                    'lieu_cp'    => $row['lieu_cp'],
                ),
            );
            if ($error_category !== null) {
                $resp['errorCategory'] = $error_category;
                $resp['errorMessage']  = $error_message;
            }

            bridge_send_success($resp, 200);
        } catch (Exception $e) {
            error_log('[bridge.php] get_stagiaire_status failed: ' . $e->getMessage());
            bridge_send_error('db_error', 500, null);
        }
        break;

    default:
        bridge_send_error(
            'unknown_action',
            400,
            array(
                'action_received' => $action,
                'available_actions' => array('ping', 'create_or_update_prospect', 'prepare_payment', 'get_stagiaire_status'),
            )
        );
}
