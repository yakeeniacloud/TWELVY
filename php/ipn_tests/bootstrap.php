<?php
// =========================================================================
// bootstrap.php — test-only bootstrapper for ipn.php
// Lives at /php/ipn_tests/bootstrap.php
// Never deployed to OVH (ipn_tests/ is gitignored)
// =========================================================================
//
// Responsibilities:
//   1. Define all constants ipn.php + config_paiement.php expect, with
//      fake/test values (we do NOT load the real config_secrets.php).
//   2. Generate an RSA test keypair if not yet present.
//   3. Require ipn.php with IPN_TESTING_NO_AUTOEXEC so the main flow is NOT
//      auto-executed on include.
//   4. Build a SQLite in-memory database with a minimal schema mirroring
//      the live MySQL tables (only columns ipn.php touches).
//   5. Seed a known stagiaire + stage + order_stage + membre row.
//   6. Override ipn_db() with the SQLite PDO so tests hit SQLite, not MySQL.
// =========================================================================

error_reporting(E_ALL);
ini_set('display_errors', '1');

// -------------------------------------------------------------------------
// 1. Fake constants — match what config_paiement.php + config_secrets.php
//    would normally provide. Values are fake/test-only.
// -------------------------------------------------------------------------
define('TWELVY_BRIDGE', true);
define('IPN_TESTING_NO_AUTOEXEC', true);
define('UP2PAY_IPN_TEST_MODE', true);

define('UP2PAY_ENV',          'test');
define('UP2PAY_SITE_ID',      '1999887');
define('UP2PAY_RANG',         '63');
define('UP2PAY_IDENTIFIANT',  '222');
define('UP2PAY_PAYMENT_URL',  'https://preprod-tpeweb.up2pay.com/cgi/MYchoix_pagepaiement.cgi');
define('UP2PAY_KEY_VERSION',  '1');
define('UP2PAY_DEVISE',       '978');
define('UP2PAY_HASH',         'SHA512');
define('UP2PAY_LANGUE',       'FRA');
define('UP2PAY_RETOUR',       'Mt:M;Ref:R;Auto:A;Erreur:E;NumAppel:T;NumTrans:S;Carte:C;Sign:K');
define('UP2PAY_REFERENCE_PREFIX', 'CFPSP_');
define('UP2PAY_NORMAL_RETURN_URL',      'https://api.twelvy.net/retour.php');
define('UP2PAY_AUTOMATIC_RESPONSE_URL', 'https://api.twelvy.net/ipn.php');

define('UP2PAY_HMAC_KEY_TEST', str_repeat('0123456789ABCDEF', 8));
define('UP2PAY_HMAC_KEY_PROD', str_repeat('F0F0F0F0F0F0F0F0', 8));
define('UP2PAY_HMAC_KEY',      UP2PAY_HMAC_KEY_TEST);

define('UP2PAY_PUBKEY_PATH',      __DIR__ . '/pubkey_test.pem');
define('UP2PAY_PUBKEY_PATH_TEST', __DIR__ . '/pubkey_test.pem');

define('MYSQL_HOST',     '127.0.0.1');
define('MYSQL_DB',       'ipn_test');
define('MYSQL_USER',     'test');
define('MYSQL_PASSWORD', 'test');

define('BRIDGE_SECRET_TOKEN', 'test-bridge-token-xxxx-xxxx-xxxx-xxxx');
define('BRIDGE_CORS_ORIGIN',  'https://www.twelvy.net');

define('EMAIL_ADMIN_NOTIFICATIONS', 'test-admin@example.invalid');
define('EMAIL_FROM',                'test-noreply@example.invalid');
define('EMAIL_FROM_NAME',           'TwelvyTest');

// -------------------------------------------------------------------------
// 2. Generate test RSA keypair if not present
// -------------------------------------------------------------------------
function ipn_test_ensure_keys() {
    $priv_path = __DIR__ . '/privkey_test.pem';
    $pub_path  = __DIR__ . '/pubkey_test.pem';
    if (is_readable($priv_path) && is_readable($pub_path)) {
        return array('priv' => $priv_path, 'pub' => $pub_path);
    }
    $config = array(
        'digest_alg'       => 'sha1',
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    );
    $res = openssl_pkey_new($config);
    if ($res === false) {
        throw new RuntimeException('openssl_pkey_new failed: ' . openssl_error_string());
    }
    openssl_pkey_export($res, $priv_pem);
    $details = openssl_pkey_get_details($res);
    $pub_pem = $details['key'];
    file_put_contents($priv_path, $priv_pem);
    file_put_contents($pub_path, $pub_pem);
    return array('priv' => $priv_path, 'pub' => $pub_path);
}

$IPN_TEST_KEYS = ipn_test_ensure_keys();

// -------------------------------------------------------------------------
// 3. Helper — sign a body with the test private key (simulates Up2Pay)
// -------------------------------------------------------------------------
function ipn_test_sign_body($body_without_sign) {
    global $IPN_TEST_KEYS;
    $priv_pem = file_get_contents($IPN_TEST_KEYS['priv']);
    $priv = openssl_pkey_get_private($priv_pem);
    if ($priv === false) {
        throw new RuntimeException('Cannot load test private key');
    }
    $sig_bin = '';
    $ok = openssl_sign($body_without_sign, $sig_bin, $priv, OPENSSL_ALGO_SHA1);
    if (!$ok) {
        throw new RuntimeException('openssl_sign failed');
    }
    if (PHP_MAJOR_VERSION < 8 && function_exists('openssl_free_key')) {
        @openssl_free_key($priv);
    }
    return base64_encode($sig_bin);
}

function ipn_test_build_signed_body($fields) {
    // Build the un-signed body in array order
    $pairs = array();
    foreach ($fields as $k => $v) {
        $pairs[] = $k . '=' . urlencode((string)$v);
    }
    $body_without_sign = implode('&', $pairs);
    $sig_b64 = ipn_test_sign_body($body_without_sign);
    // Append Sign, URL-encoding the base64 (mimics real Up2Pay IPN)
    return $body_without_sign . '&Sign=' . urlencode($sig_b64);
}

// -------------------------------------------------------------------------
// 4. Require ipn.php (IPN_TESTING_NO_AUTOEXEC blocks main flow)
// -------------------------------------------------------------------------
require_once __DIR__ . '/../ipn.php';

// -------------------------------------------------------------------------
// 5. Set up SQLite in-memory DB with minimal schema
// -------------------------------------------------------------------------
function ipn_test_build_sqlite() {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $schema = array(
        "CREATE TABLE stagiaire (
            id INTEGER PRIMARY KEY,
            email TEXT,
            nom TEXT,
            prenom TEXT,
            id_stage INTEGER,
            status TEXT DEFAULT 'pre-inscrit',
            numappel TEXT DEFAULT '',
            numtrans TEXT DEFAULT '',
            numero_cb TEXT DEFAULT '',
            up2pay_status TEXT,
            up2pay_code_error TEXT,
            paiement INTEGER DEFAULT 0,
            facture_num INTEGER DEFAULT 0,
            date_inscription TEXT,
            date_preinscription TEXT,
            datetime_preinscription TEXT,
            supprime INTEGER DEFAULT 0
        )",
        "CREATE TABLE stage (
            id INTEGER PRIMARY KEY,
            id_membre INTEGER,
            prix INTEGER,
            nb_places_allouees INTEGER DEFAULT 20,
            nb_inscrits INTEGER DEFAULT 0,
            taux_remplissage INTEGER DEFAULT 0
        )",
        "CREATE TABLE order_stage (
            id INTEGER PRIMARY KEY,
            user_id INTEGER,
            amount INTEGER,
            is_paid INTEGER DEFAULT 0,
            num_suivi INTEGER,
            stage_id INTEGER,
            reference_order TEXT,
            created TEXT
        )",
        "CREATE TABLE archive_inscriptions (
            id INTEGER PRIMARY KEY,
            date_inscription TEXT DEFAULT CURRENT_TIMESTAMP,
            id_stagiaire INTEGER,
            id_stage INTEGER,
            id_membre INTEGER
        )",
        "CREATE TABLE tracking_payment_error_code (
            id INTEGER PRIMARY KEY,
            id_stagiaire INTEGER,
            error_code TEXT,
            date_error TEXT,
            source TEXT
        )",
        "CREATE TABLE membre (
            id INTEGER PRIMARY KEY,
            email TEXT
        )",
    );
    foreach ($schema as $sql) {
        $pdo->exec($sql);
    }
    return $pdo;
}

/**
 * Seed the SQLite DB with one known stagiaire + stage + order + membre.
 * Returns a hash of the seeded IDs for test assertions.
 */
function ipn_test_seed($pdo) {
    $pdo->exec("INSERT INTO membre (id, email) VALUES (289, 'centre-test@example.invalid')");
    $pdo->exec("INSERT INTO stage (id, id_membre, prix, nb_places_allouees, nb_inscrits, taux_remplissage)
                VALUES (329207, 289, 219, 20, 3, 3)");
    $pdo->exec("INSERT INTO stagiaire (id, email, nom, prenom, id_stage, status)
                VALUES (40120317, 'stagiaire-test@example.invalid', 'DUPONT', 'Jean', 329207, 'pre-inscrit')");
    $pdo->exec("INSERT INTO order_stage (id, user_id, amount, is_paid, num_suivi, stage_id, reference_order, created)
                VALUES (150001, 40120317, 219, 0, 275086, 329207, 'CFPSP_275086', '2026-04-20 10:00:00')");
    return array(
        'stagiaire_id'    => 40120317,
        'stage_id'        => 329207,
        'order_id'        => 150001,
        'num_suivi'       => 275086,
        'id_membre'       => 289,
        'reference_order' => 'CFPSP_275086',
    );
}

/**
 * Reset SQLite DB to a clean seeded state — call before each test.
 */
function ipn_test_reset_db() {
    $pdo = ipn_test_build_sqlite();
    ipn_db($pdo);
    return ipn_test_seed($pdo);
}

// -------------------------------------------------------------------------
// 6. Initialize global tracking arrays for captured test output
// -------------------------------------------------------------------------
$GLOBALS['IPN_LAST_RESPONSE']   = null;
$GLOBALS['IPN_TEST_SENT_MAILS'] = array();

/**
 * Reset captured state between tests.
 */
function ipn_test_reset_captures() {
    $GLOBALS['IPN_LAST_RESPONSE']   = null;
    $GLOBALS['IPN_TEST_SENT_MAILS'] = array();
}
