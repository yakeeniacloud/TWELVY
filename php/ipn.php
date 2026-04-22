<?php
// =========================================================================
// ipn.php — Up2Pay IPN (Instant Payment Notification) handler
// PHP 5.6 compatible
// Lives at https://api.twelvy.net/ipn.php
// Called by Up2Pay's servers (NOT the customer's browser) via PBX_REPONDRE_A
// =========================================================================
//
// Security model:
//   - No X-Api-Key (this endpoint is PUBLIC — Up2Pay's servers hit it directly)
//   - RSA-SHA1 signature verification against Up2Pay's public key IS the auth
//   - Never trust the POST body until openssl_verify returns 1
//
// Inputs (POST body — form-encoded, per PBX_RETOUR config):
//   Mt       = amount in cents
//   Ref      = booking reference (CFPSP_<num_suivi>)
//   Auto     = authorization code
//   Erreur   = "00000" on success, otherwise numeric error code
//   NumAppel = Up2Pay call number
//   NumTrans = Up2Pay transaction number
//   Carte    = masked PAN (last 4 digits)
//   Sign     = URL-encoded base64 RSA-SHA1 signature
//
// Outputs:
//   HTTP 200 + "OK"                     → processed successfully (or idempotent skip)
//   HTTP 403 + "bad signature"          → signature invalid, stop retrying
//   HTTP 400 + "malformed"              → body unparseable / missing fields
//   HTTP 404 + "not found"              → reference does not match any order
//   HTTP 500 + short error              → transient error, Up2Pay should retry
//
// Idempotence guarantee:
//   Up2Pay may POST the same IPN multiple times (network retries up to 24 h).
//   The handler SELECT ... FOR UPDATE locks the stagiaire row; if status is
//   already 'inscrit' with numappel+numtrans filled, we rollback + respond 200
//   without touching ANY other table, without sending ANY emails.
//
// Transaction guarantee:
//   All DB writes happen inside a single BEGIN/COMMIT. On any exception, the
//   transaction rolls back entirely — no partial state.
//
// Emails:
//   Sent AFTER the DB commit. If email sending fails, the DB state is already
//   correct; the failure is logged but does NOT roll back or re-raise.
//
// Test mode:
//   Tests define IPN_TESTING_NO_AUTOEXEC before require_once → main flow does
//   not execute. Tests can then call individual functions directly.
//   Tests may also set UP2PAY_IPN_TEST_MODE=true to use a test RSA key pair.
// =========================================================================

// -------------------------------------------------------------------------
// Step 0 — defensive PHP settings (override misconfigured php.ini)
// -------------------------------------------------------------------------
@ini_set('display_errors', '0');
@ini_set('log_errors', '1');
error_reporting(E_ALL);
// L6 fix — lock timezone so date('Y-m-d') in SQL writes is Paris-local regardless of php.ini
@date_default_timezone_set('Europe/Paris');

// -------------------------------------------------------------------------
// Step 1 — load config + secrets
// In test mode (IPN_TESTING_NO_AUTOEXEC), bootstrap.php has already defined
// every constant we need, so we skip the real config_paiement.php require.
// -------------------------------------------------------------------------
if (!defined('TWELVY_BRIDGE')) {
    define('TWELVY_BRIDGE', true);
}
if (!defined('IPN_TESTING_NO_AUTOEXEC')) {
    require_once __DIR__ . '/config_paiement.php';
}

// =========================================================================
// HELPERS (all pure, all testable in isolation)
// =========================================================================

/**
 * Send a plain-text response with status code and stop execution.
 * Up2Pay only inspects the HTTP status, body is for logs.
 */
function ipn_respond($status_code, $body_text) {
    if (defined('IPN_TESTING_NO_AUTOEXEC')) {
        // In test mode, stash the response instead of exiting
        $GLOBALS['IPN_LAST_RESPONSE'] = array('status' => $status_code, 'body' => $body_text);
        return;
    }
    http_response_code($status_code);
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, private');
    header('Pragma: no-cache');
    header('X-Content-Type-Options: nosniff');
    echo $body_text;
    exit;
}

/**
 * Lightweight structured log to error_log.
 * Keeps tags consistent for grep-ability.
 */
function ipn_log_event($level, $message, $context = array()) {
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
    error_log('[ipn.php][' . $level . '] ' . $message . $ctx);
}

/**
 * PDO accessor with override hook for tests.
 * Pass a PDO instance to replace the live DB (used by the SQLite test harness).
 */
function ipn_db($override_pdo = null) {
    static $pdo = null;
    if ($override_pdo !== null) {
        $pdo = $override_pdo;
        return $pdo;
    }
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
            ipn_log_event('ERROR', 'DB connection failed', array('err' => $e->getMessage()));
            ipn_respond(500, 'db unavailable');
            return null;
        }
    }
    return $pdo;
}

/**
 * Parse a raw form-encoded body and split out Sign from the rest.
 *
 * Returns:
 *   array(
 *     'fields'     => associative array of all POST fields (URL-decoded),
 *     'sign_b64'   => URL-decoded base64 signature string (or '' if missing),
 *     'signed_msg' => raw body with &Sign=... stripped (the string Up2Pay signed),
 *   )
 */
function ipn_parse_body($raw) {
    $result = array('fields' => array(), 'sign_b64' => '', 'signed_msg' => '');
    if (!is_string($raw) || $raw === '') {
        return $result;
    }
    // C1 fix — extract Sign from RAW body BEFORE parse_str, via regex.
    // parse_str is safe for this specific field but we use regex-then-urldecode
    // for explicit control (defensive: no dependency on parse_str's quirks
    // like `.` / `[` key mangling or max_input_vars).
    if (preg_match('/(?:^|&)Sign=([^&]*)/', $raw, $m)) {
        $result['sign_b64'] = urldecode($m[1]);
    }
    // Strip any Sign= segment (first/middle/last position — defense-in-depth).
    $msg = preg_replace('/(^|&)Sign=[^&]*/', '', $raw);
    $msg = ltrim($msg, '&');
    $result['signed_msg'] = $msg;
    // Parse business fields (Sign still in $parsed but we don't use it from there)
    $parsed = array();
    parse_str($raw, $parsed);
    if (is_array($parsed)) {
        $result['fields'] = $parsed;
    }
    return $result;
}

/**
 * Verify RSA-SHA1 signature using Up2Pay's public key.
 * Returns true only if openssl_verify returns exactly 1.
 *
 * In UP2PAY_IPN_TEST_MODE, uses the test key at UP2PAY_PUBKEY_PATH_TEST.
 */
function ipn_verify_signature($signed_msg, $sign_b64) {
    if ($sign_b64 === '' || $signed_msg === '') {
        return false;
    }
    // base64_decode with strict=true rejects invalid chars (avoids silent pass-through)
    $sig_bin = base64_decode($sign_b64, true);
    if ($sig_bin === false || $sig_bin === '') {
        return false;
    }
    // H3 fix — guard with defined() (undefined constant in PHP <8 is truthy → wrong key)
    $test_mode = defined('UP2PAY_IPN_TEST_MODE') && UP2PAY_IPN_TEST_MODE;
    $key_path = $test_mode ? UP2PAY_PUBKEY_PATH_TEST : UP2PAY_PUBKEY_PATH;
    if (!is_readable($key_path)) {
        ipn_log_event('ERROR', 'public key not readable', array('path' => $key_path));
        return false;
    }
    $pem = file_get_contents($key_path);
    if ($pem === false || $pem === '') {
        ipn_log_event('ERROR', 'public key empty/unreadable', array('path' => $key_path));
        return false;
    }
    $pub = openssl_pkey_get_public($pem);
    if ($pub === false) {
        // H2 fix — surface openssl diagnostics instead of silent false
        ipn_log_event('ERROR', 'openssl_pkey_get_public failed', array('err' => openssl_error_string()));
        return false;
    }
    $ok = openssl_verify($signed_msg, $sig_bin, $pub, OPENSSL_ALGO_SHA1);
    if ($ok === -1) {
        // H2 fix — openssl returned hard error (not just "sig mismatch")
        ipn_log_event('ERROR', 'openssl_verify error -1', array('err' => openssl_error_string()));
    }
    // Resource cleanup (no-op on PHP 8+, explicit on 5.6)
    if (PHP_MAJOR_VERSION < 8 && function_exists('openssl_free_key')) {
        @openssl_free_key($pub);
    }
    return $ok === 1;
}

/**
 * Lookup the stagiaire + stage by reference_order.
 * Uses order_stage.reference_order to find the booking (reference = "CFPSP_<num_suivi>").
 * Returns the joined row or null.
 */
function ipn_lookup_by_reference($pdo, $reference) {
    $stmt = $pdo->prepare(
        "SELECT s.id AS stagiaire_id, s.status, s.numappel, s.numtrans,
                s.email, s.nom, s.prenom, s.id_stage,
                o.id AS order_id, o.num_suivi, o.reference_order, o.is_paid,
                st.id_membre, st.prix
         FROM order_stage o
         INNER JOIN stagiaire s ON s.id = o.user_id
         INNER JOIN stage st   ON st.id = o.stage_id
         WHERE o.reference_order = :ref
         LIMIT 1"
    );
    $stmt->execute(array(':ref' => $reference));
    $row = $stmt->fetch();
    return $row ? $row : null;
}

/**
 * Idempotence: return true if this booking is already finalized as paid.
 * Matches PSP's contract: status='inscrit' AND numappel != '' AND numtrans != ''
 */
function ipn_is_already_paid($row) {
    if (!is_array($row)) return false;
    $status = isset($row['status']) ? (string)$row['status'] : '';
    $numappel = isset($row['numappel']) ? (string)$row['numappel'] : '';
    $numtrans = isset($row['numtrans']) ? (string)$row['numtrans'] : '';
    // PSP parity — validate_payment.php:129-134 also checks supprime=0 before treating as already paid.
    // If supprime key not present (e.g. legacy callers), default to 0 (assume not-deleted).
    $supprime = isset($row['supprime']) ? (int)$row['supprime'] : 0;
    return ($status === 'inscrit' && $supprime === 0 && $numappel !== '' && $numtrans !== '');
}

/**
 * Classify an Up2Pay error code into a UX category + French message.
 * KADER'S ABSOLUTE RULE: the raw code NEVER reaches the user directly.
 * Full mapping lives in errors.csv (76 codes). This is a subset used for UX.
 */
function ipn_classify_error($code) {
    $code = (string)$code;
    $card_input_codes  = array('00007', '00008', '00020', '00114', '00115', '00130', '00133', '00154');
    $bank_refuse_codes = array('00021', '00022', '00151', '00163', '00187');
    $threeds_codes     = array('00204', '00205', '00207');
    $fraud_codes       = array('00138', '00141', '00143');
    $tech_codes        = array('00001', '00002', '00003');

    if (in_array($code, $card_input_codes, true)) {
        return array('category' => 'erreur_saisie_carte',
                     'message'  => "Votre paiement n'a pas pu aboutir : le numéro, la date ou le cryptogramme de votre carte est incorrect. Vérifiez vos informations bancaires et réessayez.");
    }
    if (in_array($code, $bank_refuse_codes, true)) {
        return array('category' => 'refus_banque',
                     'message'  => "Votre banque n'a pas autorisé le paiement. Réessayez avec une autre carte ou contactez votre banque.");
    }
    if (in_array($code, $threeds_codes, true)) {
        return array('category' => 'probleme_3ds',
                     'message'  => "L'authentification 3D Secure a échoué. Réessayez en validant correctement l'étape 3D Secure.");
    }
    if (in_array($code, $fraud_codes, true)) {
        return array('category' => 'carte_bloquee',
                     'message'  => "Votre carte a été signalée (perdue, volée ou bloquée). Utilisez une autre carte ou contactez votre banque.");
    }
    if (in_array($code, $tech_codes, true)) {
        return array('category' => 'erreur_technique',
                     'message'  => "Plateforme de paiement momentanément indisponible. Veuillez réessayer dans quelques minutes.");
    }
    return array('category' => 'erreur_inconnue',
                 'message'  => "Une erreur est survenue lors du paiement. Veuillez réessayer ou contacter le support.");
}

/**
 * SUCCESS PATH — the 4 SQL writes, all inside the active transaction.
 * Caller is responsible for beginTransaction + commit/rollback.
 */
function ipn_apply_success_writes($pdo, $row, $num_appel, $num_trans, $carte_masked, $amount_cents) {
    $stagiaire_id = (int)$row['stagiaire_id'];
    $stage_id     = (int)$row['id_stage'];
    $order_id     = (int)$row['order_id'];
    $num_suivi    = (int)$row['num_suivi'];
    $id_membre    = (int)$row['id_membre'];
    $facture_num  = $num_suivi - 1000;
    $today        = date('Y-m-d');
    $now          = date('Y-m-d H:i:s');

    // Write 1/4 — promote stagiaire to 'inscrit'
    $stmt = $pdo->prepare(
        "UPDATE stagiaire SET
            supprime              = 0,
            status                = 'inscrit',
            numero_cb             = :cb,
            numappel              = :na,
            numtrans              = :nt,
            up2pay_status         = 'Capturé',
            up2pay_code_error     = NULL,
            date_inscription      = :d,
            date_preinscription   = :d,
            datetime_preinscription = :dt,
            facture_num           = :fn,
            paiement              = :p
         WHERE id = :id"
    );
    $stmt->execute(array(
        ':cb' => $carte_masked,
        ':na' => $num_appel,
        ':nt' => $num_trans,
        ':d'  => $today,
        ':dt' => $now,
        ':fn' => $facture_num,
        ':p'  => (int)($amount_cents / 100),
        ':id' => $stagiaire_id,
    ));

    // Write 2/4 — flip order_stage.is_paid
    $stmt = $pdo->prepare("UPDATE order_stage SET is_paid = 1 WHERE id = :id");
    $stmt->execute(array(':id' => $order_id));

    // Write 3/4 — archive audit row
    $stmt = $pdo->prepare(
        "INSERT INTO archive_inscriptions (id_stagiaire, id_stage, id_membre)
         VALUES (:sid, :stid, :mid)"
    );
    $stmt->execute(array(
        ':sid'  => $stagiaire_id,
        ':stid' => $stage_id,
        ':mid'  => $id_membre,
    ));

    // Write 4/4 — decrement stage spots (mirrors PSP's exact formula)
    $stmt = $pdo->prepare(
        "UPDATE stage SET
            nb_places_allouees = nb_places_allouees - 1,
            nb_inscrits        = nb_inscrits + 1,
            taux_remplissage   = taux_remplissage + 1
         WHERE id = :id"
    );
    $stmt->execute(array(':id' => $stage_id));
}

/**
 * REFUSE PATH — minimal SQL writes for a failed payment.
 * Stagiaire stays pre-inscrit (line remains reusable for a retry).
 */
function ipn_apply_refuse_writes($pdo, $row, $error_code) {
    $stagiaire_id = (int)$row['stagiaire_id'];

    // Mark up2pay error status on stagiaire
    $stmt = $pdo->prepare(
        "UPDATE stagiaire SET
            up2pay_code_error = :c,
            up2pay_status     = 'Refusé'
         WHERE id = :id"
    );
    $stmt->execute(array(':c' => $error_code, ':id' => $stagiaire_id));

    // Log in tracking table (mirrors PSP behaviour) — PHP-computed timestamp for driver portability
    $stmt = $pdo->prepare(
        "INSERT INTO tracking_payment_error_code (id_stagiaire, error_code, date_error, source)
         VALUES (:sid, :code, :now, 'up2pay')"
    );
    try {
        $stmt->execute(array(':sid' => $stagiaire_id, ':code' => $error_code, ':now' => date('Y-m-d H:i:s')));
    } catch (Exception $e) {
        // Table might not exist in some environments — log but do not fail IPN
        ipn_log_event('WARN', 'tracking_payment_error_code insert failed', array('err' => $e->getMessage()));
    }
}

/**
 * Send a simple confirmation email to the customer.
 * Lightweight fallback — Kader may swap for full PSP templates later.
 */
function ipn_send_customer_success($row, $amount_cents) {
    if (empty($row['email'])) return false;
    $to      = (string)$row['email'];
    $nom     = isset($row['prenom']) ? trim($row['prenom'] . ' ' . $row['nom']) : '';
    $montant = number_format($amount_cents / 100, 2, ',', ' ');
    $ref     = isset($row['reference_order']) ? $row['reference_order'] : '';
    $subject = 'Confirmation de votre inscription Twelvy';
    $body    = "Bonjour " . $nom . ",\n\n"
             . "Votre paiement de " . $montant . " EUR a bien été reçu.\n"
             . "Votre inscription au stage de récupération de points est confirmée.\n\n"
             . "Référence : " . $ref . "\n\n"
             . "Vous recevrez prochainement les détails pratiques de votre stage.\n\n"
             . "L'équipe Twelvy";
    return ipn_send_mail($to, $subject, $body);
}

function ipn_send_customer_refused($row, $error_code) {
    if (empty($row['email'])) return false;
    $to      = (string)$row['email'];
    $nom     = isset($row['prenom']) ? trim($row['prenom'] . ' ' . $row['nom']) : '';
    $err     = ipn_classify_error($error_code);
    $subject = "Échec de votre paiement Twelvy";
    $body    = "Bonjour " . $nom . ",\n\n"
             . $err['message'] . "\n\n"
             . "Vous pouvez réessayer votre inscription depuis votre espace Twelvy.\n\n"
             . "L'équipe Twelvy";
    return ipn_send_mail($to, $subject, $body);
}

function ipn_send_center_notification($pdo, $row, $amount_cents) {
    $id_membre = isset($row['id_membre']) ? (int)$row['id_membre'] : 0;
    if ($id_membre <= 0) return false;
    // Skip PSP test-member 837 (same filter PSP uses)
    if ($id_membre === 837) return false;
    try {
        $stmt = $pdo->prepare("SELECT email FROM membre WHERE id = :id LIMIT 1");
        $stmt->execute(array(':id' => $id_membre));
        $m = $stmt->fetch();
        if (!$m || empty($m['email'])) return false;
        $to = (string)$m['email'];
    } catch (Exception $e) {
        ipn_log_event('WARN', 'center lookup failed', array('err' => $e->getMessage()));
        return false;
    }
    $subject = 'Nouvelle inscription Twelvy';
    $body    = "Nouveau stagiaire inscrit via Twelvy :\n\n"
             . "Nom   : " . $row['nom'] . "\n"
             . "Prénom: " . $row['prenom'] . "\n"
             . "Email : " . $row['email'] . "\n"
             . "Réf   : " . $row['reference_order'] . "\n"
             . "Montant: " . number_format($amount_cents / 100, 2, ',', ' ') . " EUR\n";
    return ipn_send_mail($to, $subject, $body);
}

/**
 * Thin wrapper around mail(). Set appropriate From headers.
 * Returns true on success, false on failure (no throwing — email is best-effort).
 */
function ipn_send_mail($to, $subject, $body) {
    // M7 fix — validate recipient before calling mail(). Defends against
    // email-header injection if a stagiaire.email ever contained \r\n.
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        ipn_log_event('WARN', 'invalid recipient email', array('to' => $to));
        return false;
    }
    if (defined('IPN_TESTING_NO_AUTOEXEC') && !defined('IPN_TESTING_ALLOW_MAIL')) {
        // Never send real emails from tests
        $GLOBALS['IPN_TEST_SENT_MAILS'][] = array('to' => $to, 'subject' => $subject, 'body' => $body);
        return true;
    }
    $from = defined('EMAIL_FROM') ? EMAIL_FROM : 'noreply@twelvy.net';
    $from_name = defined('EMAIL_FROM_NAME') ? EMAIL_FROM_NAME : 'Twelvy';
    $bcc = defined('EMAIL_ADMIN_NOTIFICATIONS') ? EMAIL_ADMIN_NOTIFICATIONS : '';
    $headers  = 'From: ' . $from_name . ' <' . $from . '>' . "\r\n";
    $headers .= 'Reply-To: ' . $from . "\r\n";
    $headers .= 'Content-Type: text/plain; charset=utf-8' . "\r\n";
    $headers .= 'MIME-Version: 1.0' . "\r\n";
    if ($bcc !== '' && $bcc !== $to) {
        $headers .= 'Bcc: ' . $bcc . "\r\n";
    }
    $ok = @mail($to, $subject, $body, $headers);
    if (!$ok) {
        ipn_log_event('WARN', 'mail() returned false', array('to' => $to, 'subject' => $subject));
    }
    return (bool)$ok;
}

// =========================================================================
// MAIN REQUEST HANDLER
// =========================================================================

function ipn_handle_request($body_override = null, $method_override = null) {

    // Step 1 — method check (overridable for tests)
    $method = $method_override !== null
        ? $method_override
        : (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '');
    if ($method !== 'POST') {
        ipn_log_event('WARN', 'non-POST method', array('method' => $method));
        if (!defined('IPN_TESTING_NO_AUTOEXEC')) {
            header('Allow: POST');
        }
        ipn_respond(405, 'method not allowed');
        return;
    }

    // Step 2 — read raw body with size cap (body_override for tests bypasses php://input)
    if ($body_override === null && isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 16384) {
        ipn_log_event('WARN', 'oversized body', array('len' => (int)$_SERVER['CONTENT_LENGTH']));
        ipn_respond(413, 'too large');
        return;
    }
    $raw = $body_override !== null ? $body_override : file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        ipn_log_event('WARN', 'empty body');
        ipn_respond(400, 'empty body');
        return;
    }

    // Step 3 — parse
    $parsed = ipn_parse_body($raw);
    $fields     = $parsed['fields'];
    $sign_b64   = $parsed['sign_b64'];
    $signed_msg = $parsed['signed_msg'];

    if ($sign_b64 === '') {
        ipn_log_event('WARN', 'missing Sign');
        ipn_respond(400, 'missing sign');
        return;
    }
    if (empty($fields['Ref'])) {
        ipn_log_event('WARN', 'missing Ref', array('fields' => implode(',', array_keys($fields))));
        ipn_respond(400, 'missing ref');
        return;
    }

    // Step 4 — RSA-SHA1 signature verification (THE security boundary)
    if (!ipn_verify_signature($signed_msg, $sign_b64)) {
        ipn_log_event('ERROR', 'signature invalid', array('ref' => $fields['Ref']));
        ipn_respond(403, 'bad signature');
        return;
    }

    // Step 5 — extract business fields (all URL-decoded already by parse_str)
    $ref          = (string)$fields['Ref'];
    $mt_raw       = isset($fields['Mt']) ? (string)$fields['Mt'] : '';
    $erreur       = isset($fields['Erreur']) ? (string)$fields['Erreur'] : '';
    $auto         = isset($fields['Auto']) ? (string)$fields['Auto'] : '';
    $num_appel    = isset($fields['NumAppel']) ? (string)$fields['NumAppel'] : '';
    $num_trans    = isset($fields['NumTrans']) ? (string)$fields['NumTrans'] : '';
    $carte        = isset($fields['Carte']) ? (string)$fields['Carte'] : '';

    // C4 fix — strict integer validation on amount (reject negative / non-numeric)
    if ($mt_raw === '' || !ctype_digit($mt_raw)) {
        ipn_log_event('WARN', 'invalid Mt field', array('ref' => $ref, 'mt' => $mt_raw));
        ipn_respond(400, 'invalid amount');
        return;
    }
    $amount_cents = (int)$mt_raw;
    if ($amount_cents <= 0) {
        ipn_log_event('WARN', 'non-positive Mt', array('ref' => $ref, 'mt' => $mt_raw));
        ipn_respond(400, 'invalid amount');
        return;
    }

    // M8 fix — defense-in-depth on Carte field. Paybox guarantees masked PAN
    // but we enforce it: if more than 6 leading digits aren't X'd, mask them ourselves.
    if ($carte !== '' && preg_match('/^\d{7,}/', $carte)) {
        $last4 = substr($carte, -4);
        $carte = str_repeat('X', strlen($carte) - 4) . $last4;
    }

    if ($erreur === '') {
        ipn_log_event('WARN', 'missing Erreur', array('ref' => $ref));
        ipn_respond(400, 'missing erreur');
        return;
    }

    // Step 6 — lookup stagiaire by reference (read outside the transaction)
    $pdo = ipn_db();
    if ($pdo === null) return; // ipn_db already responded 500
    try {
        $row = ipn_lookup_by_reference($pdo, $ref);
    } catch (Exception $e) {
        ipn_log_event('ERROR', 'lookup exception', array('err' => $e->getMessage(), 'ref' => $ref));
        ipn_respond(500, 'lookup error');
        return;
    }
    if ($row === null) {
        ipn_log_event('ERROR', 'reference not found', array('ref' => $ref));
        ipn_respond(404, 'not found');
        return;
    }

    // C3 fix — amount-mismatch guard.
    // Paybox signs the full body (incl. Mt + Ref) so a replay attack is impossible,
    // but we still defend against: partial auth acquirers, Paybox misconfig,
    // and genuine merchant-account-mismatch. Only runs on success path — on refuse
    // Paybox may echo any Mt, we don't care because we're not crediting anything.
    if ($erreur === '00000') {
        $expected_cents = (int)round(((float)$row['prix']) * 100);
        if ($amount_cents !== $expected_cents) {
            ipn_log_event('ERROR', 'amount mismatch — manual review required', array(
                'ref'          => $ref,
                'stagiaire_id' => (int)$row['stagiaire_id'],
                'expected'     => $expected_cents,
                'got'          => $amount_cents,
            ));
            // 200 so Paybox stops retrying (retry won't fix a merchant-side price mismatch).
            // Ops must investigate via the error log.
            ipn_respond(200, 'amount mismatch');
            return;
        }
    }

    // Step 7 — transaction + idempotent row lock + apply writes
    try {
        $pdo->beginTransaction();

        // Re-select with FOR UPDATE to lock the stagiaire row (race-safe idempotence)
        // FOR UPDATE is MySQL/PgSQL — SQLite's implicit row-level locking handles this natively
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $lock_clause = ($driver === 'mysql' || $driver === 'pgsql') ? ' FOR UPDATE' : '';
        $stmt = $pdo->prepare(
            "SELECT id, status, numappel, numtrans, supprime
             FROM stagiaire WHERE id = :id" . $lock_clause
        );
        $stmt->execute(array(':id' => (int)$row['stagiaire_id']));
        $locked = $stmt->fetch();

        // M1 fix — stagiaire deleted between outer lookup and locked SELECT.
        // Without this guard, writes would silently target a non-existent row
        // but the stage decrement + archive insert would still fire → inconsistent state.
        if ($locked === false) {
            $pdo->rollBack();
            ipn_log_event('ERROR', 'stagiaire gone after lookup', array('ref' => $ref, 'id' => $row['stagiaire_id']));
            ipn_respond(404, 'stagiaire gone');
            return;
        }

        if (ipn_is_already_paid($locked)) {
            $pdo->rollBack();
            ipn_log_event('INFO', 'already paid, no-op', array('ref' => $ref, 'stagiaire_id' => $row['stagiaire_id']));
            ipn_respond(200, 'already paid');
            return;
        }

        $is_success = ($erreur === '00000' && $num_appel !== '' && $num_trans !== '');

        if ($is_success) {
            ipn_apply_success_writes($pdo, $row, $num_appel, $num_trans, $carte, $amount_cents);
        } else {
            ipn_apply_refuse_writes($pdo, $row, $erreur);
        }

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        ipn_log_event('ERROR', 'transaction failed', array('err' => $e->getMessage(), 'ref' => $ref));
        ipn_respond(500, 'transaction error');
        return;
    }

    // Step 8 — send emails AFTER commit (best-effort, never roll back)
    try {
        if (isset($is_success) && $is_success) {
            ipn_send_customer_success($row, $amount_cents);
            ipn_send_center_notification($pdo, $row, $amount_cents);
        } else {
            ipn_send_customer_refused($row, $erreur);
        }
    } catch (Exception $e) {
        ipn_log_event('WARN', 'email send failed', array('err' => $e->getMessage(), 'ref' => $ref));
    }

    ipn_log_event('INFO', ($is_success ? 'paid' : 'refused'),
        array('ref' => $ref, 'stagiaire_id' => $row['stagiaire_id'], 'erreur' => $erreur));
    ipn_respond(200, 'OK');
}

// -------------------------------------------------------------------------
// Auto-execute unless we're being required from a test harness
// -------------------------------------------------------------------------
if (!defined('IPN_TESTING_NO_AUTOEXEC')) {
    ipn_handle_request();
}
