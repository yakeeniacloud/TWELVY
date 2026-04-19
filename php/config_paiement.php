<?php
// =========================================================================
// config_paiement.php — Up2Pay + Bridge configuration
// PHP 5.6 compatible — no scalar types, no arrow functions, no nullsafe
// VERSIONED in git (no secrets here, only structure + non-sensitive values)
// Real secrets live in config_secrets.php (gitignored)
// =========================================================================

// Direct access guard — only loadable from another script that defines TWELVY_BRIDGE
if (!defined('TWELVY_BRIDGE')) {
    http_response_code(403);
    exit('Direct access forbidden');
}

// -------------------------------------------------------------------------
// Environment switch — 'test' (sandbox) or 'prod' (real money)
// Override via OS env var UP2PAY_ENV before deploying
// -------------------------------------------------------------------------
if (!defined('UP2PAY_ENV')) {
    $env_from_os = getenv('UP2PAY_ENV');
    define('UP2PAY_ENV', ($env_from_os === 'prod') ? 'prod' : 'test');
}

// -------------------------------------------------------------------------
// TEST credentials — public Verifone sandbox (anyone can use these)
// -------------------------------------------------------------------------
define('UP2PAY_SITE_ID_TEST',     '1999887');
define('UP2PAY_RANG_TEST',        '63');
define('UP2PAY_IDENTIFIANT_TEST', '222');
define('UP2PAY_PAYMENT_URL_TEST', 'https://preprod-tpeweb.up2pay.com/cgi/MYchoix_pagepaiement.cgi');
define('UP2PAY_KEY_VERSION_TEST', '1');

// -------------------------------------------------------------------------
// PROD credentials — AM FORMATION contract (UP2PAY N°0966892.02)
// HMAC PROD key lives in config_secrets.php
// -------------------------------------------------------------------------
define('UP2PAY_SITE_ID_PROD',     '0966892');
define('UP2PAY_RANG_PROD',        '02');
define('UP2PAY_IDENTIFIANT_PROD', '651027368');
define('UP2PAY_PAYMENT_URL_PROD', 'https://tpeweb.up2pay.com/cgi/MYchoix_pagepaiement.cgi');
define('UP2PAY_KEY_VERSION_PROD', '1');

// -------------------------------------------------------------------------
// Return URLs — Up2Pay calls these (same for test/prod for now)
// -------------------------------------------------------------------------
define('UP2PAY_NORMAL_RETURN_URL',      'https://api.twelvy.net/retour.php');
define('UP2PAY_AUTOMATIC_RESPONSE_URL', 'https://api.twelvy.net/ipn.php');

// -------------------------------------------------------------------------
// Active values — picked based on UP2PAY_ENV
// -------------------------------------------------------------------------
if (UP2PAY_ENV === 'prod') {
    define('UP2PAY_SITE_ID',     UP2PAY_SITE_ID_PROD);
    define('UP2PAY_RANG',        UP2PAY_RANG_PROD);
    define('UP2PAY_IDENTIFIANT', UP2PAY_IDENTIFIANT_PROD);
    define('UP2PAY_PAYMENT_URL', UP2PAY_PAYMENT_URL_PROD);
    define('UP2PAY_KEY_VERSION', UP2PAY_KEY_VERSION_PROD);
} else {
    define('UP2PAY_SITE_ID',     UP2PAY_SITE_ID_TEST);
    define('UP2PAY_RANG',        UP2PAY_RANG_TEST);
    define('UP2PAY_IDENTIFIANT', UP2PAY_IDENTIFIANT_TEST);
    define('UP2PAY_PAYMENT_URL', UP2PAY_PAYMENT_URL_TEST);
    define('UP2PAY_KEY_VERSION', UP2PAY_KEY_VERSION_TEST);
}

// -------------------------------------------------------------------------
// Constants imposed by Up2Pay specs (do not change)
// -------------------------------------------------------------------------
define('UP2PAY_DEVISE', '978');         // EUR (ISO-4217 numeric)
define('UP2PAY_HASH',   'SHA512');      // HMAC algorithm
define('UP2PAY_LANGUE', 'FRA');         // Payment page language
define('UP2PAY_RETOUR', 'Mt:M;Ref:R;Auto:A;Erreur:E;Sign:K'); // Fields Up2Pay echoes back (Sign:K required)

// -------------------------------------------------------------------------
// Booking reference prefix (decision locked: CFPSP_ for PSP compat)
// -------------------------------------------------------------------------
define('UP2PAY_REFERENCE_PREFIX', 'CFPSP_');

// -------------------------------------------------------------------------
// CORS — restrict bridge calls to twelvy.net only (security)
// -------------------------------------------------------------------------
define('BRIDGE_CORS_ORIGIN', 'https://www.twelvy.net');

// -------------------------------------------------------------------------
// Load secrets (must define UP2PAY_HMAC_KEY_*, MYSQL_*, BRIDGE_SECRET_TOKEN)
// -------------------------------------------------------------------------
$secrets_file = __DIR__ . '/config_secrets.php';
if (!file_exists($secrets_file)) {
    http_response_code(500);
    error_log('[CONFIG] FATAL: config_secrets.php not found at ' . $secrets_file);
    exit('Server configuration error');
}
require_once $secrets_file;

// -------------------------------------------------------------------------
// Active HMAC key (after secrets loaded)
// -------------------------------------------------------------------------
if (defined('UP2PAY_HMAC_KEY_TEST') && defined('UP2PAY_HMAC_KEY_PROD')) {
    if (UP2PAY_ENV === 'prod') {
        define('UP2PAY_HMAC_KEY', UP2PAY_HMAC_KEY_PROD);
    } else {
        define('UP2PAY_HMAC_KEY', UP2PAY_HMAC_KEY_TEST);
    }
}

// -------------------------------------------------------------------------
// Sanity check — fail loud if any required secret is missing
// -------------------------------------------------------------------------
$required = array(
    'UP2PAY_HMAC_KEY_TEST',
    'UP2PAY_HMAC_KEY_PROD',
    'MYSQL_HOST',
    'MYSQL_DB',
    'MYSQL_USER',
    'MYSQL_PASSWORD',
    'BRIDGE_SECRET_TOKEN',
);
foreach ($required as $const) {
    if (!defined($const) || constant($const) === '') {
        http_response_code(500);
        error_log('[CONFIG] FATAL: required constant ' . $const . ' is not defined or empty');
        exit('Server configuration error');
    }
}
