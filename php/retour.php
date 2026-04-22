<?php
// =========================================================================
// retour.php — Up2Pay browser-return redirect router
// PHP 5.6 compatible
// Lives at https://api.twelvy.net/retour.php
// Receives GET (customer browser) when customer finishes on Up2Pay payment page
// =========================================================================
//
// How it's called:
//   bridge.php / prepare_payment builds these three URLs into the Up2Pay form:
//     PBX_EFFECTUE = https://api.twelvy.net/retour.php?status=ok&id=12345
//     PBX_REFUSE   = https://api.twelvy.net/retour.php?status=refuse&id=12345
//     PBX_ANNULE   = https://api.twelvy.net/retour.php?status=annule&id=12345
//   Up2Pay ALSO appends PBX_RETOUR fields: &Mt=...&Ref=...&Auto=...&Erreur=...
//   &NumAppel=...&NumTrans=...&Carte=...
//   (no Sign on the browser redirect — the Sign is only on the server-to-server IPN)
//
// Security model:
//   - PUBLIC endpoint, no auth
//   - We do NOT trust ANY of the query params for DB actions (IPN is source of truth).
//   - We only use our own pre-appended `status` + `id` as UX hints for the next page.
//   - Paybox-appended fields are IGNORED (no signature on browser redirect = untrusted).
//
// What it does:
//   1. Validate HTTP method (GET or POST accepted — Up2Pay may use either)
//   2. Read + validate `status` (whitelist: ok/refuse/annule; default annule)
//   3. Read + validate `id` (positive int, sane upper bound)
//   4. Build destination URL on www.twelvy.net and respond with HTTP 302 redirect
//   5. That's it. No DB, no emails, no state writes.
//
// Why this file is small:
//   The HARD work (payment confirmation, writing to DB, sending emails) is all
//   in ipn.php. retour.php is just a traffic cop. The Twelvy confirmation page
//   on the other end polls bridge.php `get_stagiaire_status` for the REAL state
//   from DB — it doesn't trust our `status` query param either.
// =========================================================================

// -------------------------------------------------------------------------
// Step 0 — defensive PHP settings
// -------------------------------------------------------------------------
@ini_set('display_errors', '0');
@ini_set('log_errors', '1');
error_reporting(E_ALL);
@date_default_timezone_set('Europe/Paris');

// -------------------------------------------------------------------------
// Step 1 — load config (skipped in test mode; tests define constants manually)
// -------------------------------------------------------------------------
if (!defined('TWELVY_BRIDGE')) {
    define('TWELVY_BRIDGE', true);
}
if (!defined('RETOUR_TESTING_NO_AUTOEXEC')) {
    require_once __DIR__ . '/config_paiement.php';
}

// =========================================================================
// HELPERS
// =========================================================================

/**
 * Lightweight log helper — tag events with [retour.php][LEVEL] for grep-ability.
 */
function retour_log($level, $message, $context = array()) {
    $safe = array();
    if (is_array($context)) {
        foreach ($context as $k => $v) {
            if (is_scalar($v) || $v === null) {
                $safe[$k] = $v;
            } else {
                $safe[$k] = '[non-scalar]';
            }
        }
    }
    $ctx = empty($safe) ? '' : ' ' . json_encode($safe);
    error_log('[retour.php][' . $level . '] ' . $message . $ctx);
}

/**
 * Issue the HTTP 302 redirect (or capture it in test mode).
 * No content body needed — browsers follow the Location header.
 */
function retour_redirect($url) {
    if (defined('RETOUR_TESTING_NO_AUTOEXEC')) {
        $GLOBALS['RETOUR_LAST_REDIRECT'] = $url;
        return;
    }
    // Security hardening — no caching, no referrer leak, no framing
    header('Cache-Control: no-store, no-cache, must-revalidate, private');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
    header('X-Frame-Options: DENY');
    header('X-Robots-Tag: noindex, nofollow');
    header('Location: ' . $url, true, 302);
    // Body shown only if the browser doesn't follow Location (rare)
    echo "Redirecting...\n";
    exit;
}

/**
 * Normalise & validate the `status` param (whitelist-based — never trust raw input).
 * Returns one of: 'ok', 'refuse', 'annule'. Falls back to 'annule' (safest UX default)
 * when value is missing / unknown.
 */
function retour_normalise_status($raw) {
    $raw = is_string($raw) ? strtolower(trim($raw)) : '';
    $allowed = array('ok', 'refuse', 'annule');
    return in_array($raw, $allowed, true) ? $raw : 'annule';
}

/**
 * Normalise & validate the `id` param.
 * Returns positive int or 0 if invalid.
 */
function retour_normalise_id($raw) {
    if (!is_scalar($raw)) return 0;
    $s = trim((string)$raw);
    if ($s === '' || !ctype_digit($s)) return 0;
    $id = (int)$s;
    // 2147483647 = signed-int32 max, safety cap against injection / overflow probes
    if ($id <= 0 || $id > 2147483647) return 0;
    return $id;
}

// =========================================================================
// MAIN REQUEST HANDLER
// =========================================================================

function retour_handle_request($params_override = null, $method_override = null) {

    // Step 1 — method check. Accept GET (normal) and POST (some Paybox configs).
    $method = $method_override !== null
        ? $method_override
        : (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '');
    if ($method !== 'GET' && $method !== 'POST') {
        retour_log('WARN', 'unsupported method', array('method' => $method));
        retour_redirect(TWELVY_HOMEPAGE_URL);
        return;
    }

    // Step 2 — gather params (Paybox appends PBX_RETOUR fields; we ignore them all
    // except our own pre-appended status+id — those are UX hints only).
    if ($params_override !== null) {
        $params = is_array($params_override) ? $params_override : array();
    } else {
        // Merge $_GET and $_POST (Up2Pay can use either; our pre-appended params are in $_GET).
        $params = array();
        if (is_array($_GET))  $params = array_merge($params, $_GET);
        if (is_array($_POST)) $params = array_merge($params, $_POST);
    }

    // Step 3 — validate id (the only param we genuinely need)
    $id = retour_normalise_id(isset($params['id']) ? $params['id'] : null);
    if ($id === 0) {
        retour_log('WARN', 'invalid or missing id — redirecting to homepage',
            array('id_raw' => isset($params['id']) ? (string)$params['id'] : '(null)'));
        retour_redirect(TWELVY_HOMEPAGE_URL);
        return;
    }

    // Step 4 — normalise status (never trust, whitelist-map)
    $status = retour_normalise_status(isset($params['status']) ? $params['status'] : '');

    // Step 5 — build destination URL on the Twelvy frontend.
    // The confirmation page will ignore our status hint and poll bridge.php for truth.
    $dest = TWELVY_CONFIRMATION_URL
          . '?id=' . urlencode((string)$id)
          . '&status=' . urlencode($status);

    retour_log('INFO', 'redirecting to Twelvy', array(
        'id'     => $id,
        'status' => $status,
        'dest'   => $dest,
    ));
    retour_redirect($dest);
}

// -------------------------------------------------------------------------
// Auto-execute unless we're being required from a test harness
// -------------------------------------------------------------------------
if (!defined('RETOUR_TESTING_NO_AUTOEXEC')) {
    retour_handle_request();
}
