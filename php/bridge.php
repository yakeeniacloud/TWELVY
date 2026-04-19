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
// Étape 5 scope (this file's current state) :
//   - Load config + secrets
//   - Verify X-Api-Key against BRIDGE_SECRET_TOKEN
//   - Restrict CORS to BRIDGE_CORS_ORIGIN
//   - Implement ONE action : "ping" → returns "pong" + meta
//
// Future scope (Étapes 6-7) :
//   - action=create_or_update_prospect  → INSERT/UPDATE stagiaire
//   - action=prepare_payment            → build PBX_* + sign HMAC
//   - action=get_stagiaire_status       → read DB, return statut + recap
// =========================================================================

// -------------------------------------------------------------------------
// Step 1 — Load config (which loads secrets). The config files refuse
// direct access unless TWELVY_BRIDGE is defined first.
// -------------------------------------------------------------------------
define('TWELVY_BRIDGE', true);
require_once __DIR__ . '/config_paiement.php';

// -------------------------------------------------------------------------
// Step 2 — Set HTTP headers for the response
// -------------------------------------------------------------------------
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: ' . BRIDGE_CORS_ORIGIN);
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Api-Key');
header('Access-Control-Max-Age: 86400');
header('Cache-Control: no-store, no-cache, must-revalidate');

// -------------------------------------------------------------------------
// Step 3 — Handle CORS preflight (browser sends OPTIONS first)
// -------------------------------------------------------------------------
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// -------------------------------------------------------------------------
// Step 4 — Standardised JSON response helpers
// -------------------------------------------------------------------------
function bridge_send_response($data, $status_code) {
    http_response_code($status_code);
    echo json_encode(
        array('success' => true, 'data' => $data),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

function bridge_send_error($error_code, $status_code, $details) {
    http_response_code($status_code);
    $resp = array('success' => false, 'error' => $error_code);
    if ($details !== null) {
        $resp['details'] = $details;
    }
    echo json_encode($resp, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// -------------------------------------------------------------------------
// Step 5 — Read X-Api-Key from request headers (multi-fallback for compat)
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

// -------------------------------------------------------------------------
// Step 6 — Verify X-Api-Key (timing-safe comparison via hash_equals)
// -------------------------------------------------------------------------
if ($api_key === '' || !hash_equals(BRIDGE_SECRET_TOKEN, $api_key)) {
    error_log('[bridge.php] Unauthorized call from ' . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '?'));
    bridge_send_error('unauthorized', 403, null);
}

// -------------------------------------------------------------------------
// Step 7 — Read action from query string (and optionally body for POST)
// -------------------------------------------------------------------------
$action = '';
if (isset($_GET['action'])) {
    $action = $_GET['action'];
} elseif (isset($_POST['action'])) {
    $action = $_POST['action'];
}
$action = is_string($action) ? trim($action) : '';

// -------------------------------------------------------------------------
// Step 8 — Action router
// -------------------------------------------------------------------------
switch ($action) {

    // ---------------------------------------------------------------------
    // ping — health check, returns metadata. No DB, no Up2Pay.
    // ---------------------------------------------------------------------
    case 'ping':
        bridge_send_response(array(
            'message'      => 'pong',
            'environment'  => UP2PAY_ENV,         // 'test' or 'prod'
            'php_version'  => PHP_VERSION,
            'timestamp'    => date('c'),          // ISO-8601 with timezone
            'bridge_ready' => true,
        ), 200);
        break;

    // ---------------------------------------------------------------------
    // Future actions go here in Étapes 6-7 :
    //   case 'create_or_update_prospect': … break;
    //   case 'prepare_payment':           … break;
    //   case 'get_stagiaire_status':      … break;
    // ---------------------------------------------------------------------

    default:
        bridge_send_error(
            'unknown_action',
            400,
            array(
                'action_received' => $action,
                'available_actions' => array('ping'),
            )
        );
}
