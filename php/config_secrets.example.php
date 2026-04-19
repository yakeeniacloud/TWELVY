<?php
// =========================================================================
// config_secrets.example.php — TEMPLATE for config_secrets.php
// On the OVH server, copy this file to config_secrets.php and fill in
// the real values. config_secrets.php is in .gitignore — never commit it.
//
// To generate a fresh BRIDGE_SECRET_TOKEN: openssl rand -hex 32
// HMAC PROD key is found in PSP code: www_2/src/payment/E_Transaction/E_TransactionPayment.php:27
// HMAC TEST key: use the public Verifone sandbox key (128 chars, repeat
// "0123456789ABCDEF" 8 times) OR pull a TEST key from Up2Pay back-office.
// =========================================================================

if (!defined('TWELVY_BRIDGE')) {
    http_response_code(403);
    exit('Direct access forbidden');
}

// -------------------------------------------------------------------------
// Up2Pay HMAC keys — 128 hex chars each (HMAC-SHA-512, 64 bytes)
// -------------------------------------------------------------------------
define('UP2PAY_HMAC_KEY_TEST', 'REPLACE_WITH_128_HEX_CHARS_TEST_KEY');
define('UP2PAY_HMAC_KEY_PROD', 'REPLACE_WITH_128_HEX_CHARS_PROD_KEY');

// -------------------------------------------------------------------------
// MySQL credentials (khapmaitpsp on OVH cluster115)
// -------------------------------------------------------------------------
define('MYSQL_HOST',     'khapmaitpsp.mysql.db');
define('MYSQL_DB',       'khapmaitpsp');
define('MYSQL_USER',     'khapmaitpsp');
define('MYSQL_PASSWORD', 'REPLACE_WITH_REAL_PASSWORD');

// -------------------------------------------------------------------------
// Bridge auth token (Next.js sends this in X-Api-Key header)
// Same value MUST be set on Vercel as env var BRIDGE_API_KEY
// -------------------------------------------------------------------------
define('BRIDGE_SECRET_TOKEN', 'REPLACE_WITH_64_HEX_CHARS_RANDOM_TOKEN');
