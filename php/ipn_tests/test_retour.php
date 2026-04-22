<?php
// =========================================================================
// test_retour.php — comprehensive test runner for retour.php
// Usage: php php/ipn_tests/test_retour.php
// =========================================================================

error_reporting(E_ALL);
ini_set('display_errors', '1');

// -------------------------------------------------------------------------
// Define constants retour.php expects (bypass real config_paiement.php)
// -------------------------------------------------------------------------
define('TWELVY_BRIDGE', true);
define('RETOUR_TESTING_NO_AUTOEXEC', true);
define('TWELVY_CONFIRMATION_URL', 'https://www.twelvy.net/paiement/confirmation');
define('TWELVY_HOMEPAGE_URL',     'https://www.twelvy.net/');

require_once __DIR__ . '/../retour.php';

// -------------------------------------------------------------------------
// Tiny assertion framework
// -------------------------------------------------------------------------
$TESTS_PASSED = 0;
$TESTS_FAILED = 0;
$FAILURES     = array();

function t_assert($condition, $name, $detail) {
    global $TESTS_PASSED, $TESTS_FAILED, $FAILURES;
    if ($condition) {
        $TESTS_PASSED++;
        echo "  \033[32m✓\033[0m " . $name . "\n";
    } else {
        $TESTS_FAILED++;
        $FAILURES[] = array('name' => $name, 'detail' => $detail);
        echo "  \033[31m✗\033[0m " . $name . " — " . $detail . "\n";
    }
}

function t_equal($expected, $actual, $name) {
    t_assert(
        $expected === $actual,
        $name,
        'expected ' . var_export($expected, true) . ', got ' . var_export($actual, true)
    );
}

function t_section($title) {
    echo "\n\033[36m=== " . $title . " ===\033[0m\n";
}

function t_last_redirect() {
    return isset($GLOBALS['RETOUR_LAST_REDIRECT']) ? $GLOBALS['RETOUR_LAST_REDIRECT'] : null;
}

function t_reset_redirect() {
    $GLOBALS['RETOUR_LAST_REDIRECT'] = null;
}

// =========================================================================
// TEST SUITE
// =========================================================================

t_section('1. retour_normalise_status — whitelist');

t_equal('ok',     retour_normalise_status('ok'),     'T1 "ok" accepted');
t_equal('refuse', retour_normalise_status('refuse'), 'T2 "refuse" accepted');
t_equal('annule', retour_normalise_status('annule'), 'T3 "annule" accepted');
t_equal('ok',     retour_normalise_status('OK'),     'T4 uppercase "OK" normalised');
t_equal('ok',     retour_normalise_status(' ok '),   'T5 whitespace trimmed');
t_equal('annule', retour_normalise_status('hack'),   'T6 unknown value → default "annule"');
t_equal('annule', retour_normalise_status(''),       'T7 empty → default "annule"');
t_equal('annule', retour_normalise_status(null),     'T8 null → default "annule"');
t_equal('annule', retour_normalise_status(array()),  'T9 array → default "annule" (no crash)');

t_section('2. retour_normalise_id — positive int, capped');

t_equal(12345,       retour_normalise_id('12345'),      'T10 valid int as string');
t_equal(1,           retour_normalise_id(1),            'T11 valid int as int');
t_equal(40120317,    retour_normalise_id('40120317'),   'T12 real stagiaire ID');
t_equal(0,           retour_normalise_id('0'),          'T13 zero rejected');
t_equal(0,           retour_normalise_id('-1'),         'T14 negative rejected (ctype_digit)');
t_equal(0,           retour_normalise_id('abc'),        'T15 non-numeric rejected');
t_equal(0,           retour_normalise_id(''),           'T16 empty rejected');
t_equal(0,           retour_normalise_id(null),         'T17 null rejected');
t_equal(0,           retour_normalise_id(array()),      'T18 array rejected');
t_equal(0,           retour_normalise_id('999999999999999'), 'T19 overflow cap');
t_equal(2147483647,  retour_normalise_id('2147483647'), 'T20 int32 max accepted');
t_equal(0,           retour_normalise_id('2147483648'), 'T21 int32 max + 1 rejected');
t_equal(0,           retour_normalise_id('1.5'),        'T22 decimal rejected (ctype_digit)');
t_equal(0,           retour_normalise_id('12345abc'),   'T23 suffix rejected');
t_equal(123,         retour_normalise_id(' 123 '),      'T24 whitespace-wrapped accepted after trim');

t_section('3. retour_handle_request — success redirects');

// T25 — normal success
t_reset_redirect();
retour_handle_request(array('status' => 'ok', 'id' => '12345'), 'GET');
t_equal('https://www.twelvy.net/paiement/confirmation?id=12345&status=ok',
    t_last_redirect(), 'T25 status=ok&id=12345 → correct redirect');

// T26 — refuse
t_reset_redirect();
retour_handle_request(array('status' => 'refuse', 'id' => '12345'), 'GET');
t_equal('https://www.twelvy.net/paiement/confirmation?id=12345&status=refuse',
    t_last_redirect(), 'T26 status=refuse → redirect with status=refuse');

// T27 — annule
t_reset_redirect();
retour_handle_request(array('status' => 'annule', 'id' => '12345'), 'GET');
t_equal('https://www.twelvy.net/paiement/confirmation?id=12345&status=annule',
    t_last_redirect(), 'T27 status=annule → redirect with status=annule');

// T28 — POST method also accepted (some Paybox configs)
t_reset_redirect();
retour_handle_request(array('status' => 'ok', 'id' => '12345'), 'POST');
t_equal('https://www.twelvy.net/paiement/confirmation?id=12345&status=ok',
    t_last_redirect(), 'T28 POST method also accepted');

t_section('4. retour_handle_request — input validation');

// T29 — unknown status → defaults to annule
t_reset_redirect();
retour_handle_request(array('status' => 'hack', 'id' => '12345'), 'GET');
t_equal('https://www.twelvy.net/paiement/confirmation?id=12345&status=annule',
    t_last_redirect(), 'T29 unknown status → status=annule (safe default)');

// T30 — missing status → defaults to annule
t_reset_redirect();
retour_handle_request(array('id' => '12345'), 'GET');
t_equal('https://www.twelvy.net/paiement/confirmation?id=12345&status=annule',
    t_last_redirect(), 'T30 missing status → status=annule');

// T31 — missing id → homepage
t_reset_redirect();
retour_handle_request(array('status' => 'ok'), 'GET');
t_equal('https://www.twelvy.net/', t_last_redirect(), 'T31 missing id → homepage');

// T32 — invalid id → homepage
t_reset_redirect();
retour_handle_request(array('status' => 'ok', 'id' => 'abc'), 'GET');
t_equal('https://www.twelvy.net/', t_last_redirect(), 'T32 non-numeric id → homepage');

// T33 — negative id → homepage
t_reset_redirect();
retour_handle_request(array('status' => 'ok', 'id' => '-5'), 'GET');
t_equal('https://www.twelvy.net/', t_last_redirect(), 'T33 negative id → homepage');

// T34 — zero id → homepage
t_reset_redirect();
retour_handle_request(array('status' => 'ok', 'id' => '0'), 'GET');
t_equal('https://www.twelvy.net/', t_last_redirect(), 'T34 zero id → homepage');

// T35 — overflow id → homepage
t_reset_redirect();
retour_handle_request(array('status' => 'ok', 'id' => '999999999999999'), 'GET');
t_equal('https://www.twelvy.net/', t_last_redirect(), 'T35 id overflow → homepage');

// T36 — SQL-injection-style id → homepage (ctype_digit catches everything)
t_reset_redirect();
retour_handle_request(array('status' => 'ok', 'id' => "12345' OR 1=1--"), 'GET');
t_equal('https://www.twelvy.net/', t_last_redirect(), 'T36 SQL injection attempt → homepage');

// T37 — unsupported HTTP method → homepage
t_reset_redirect();
retour_handle_request(array('status' => 'ok', 'id' => '12345'), 'PUT');
t_equal('https://www.twelvy.net/', t_last_redirect(), 'T37 PUT method → homepage');

t_reset_redirect();
retour_handle_request(array('status' => 'ok', 'id' => '12345'), 'DELETE');
t_equal('https://www.twelvy.net/', t_last_redirect(), 'T38 DELETE method → homepage');

t_section('5. Paybox-appended fields are ignored');

// T39 — Paybox-appended fields (Mt, Ref, Auto, Erreur, NumAppel, NumTrans, Carte) ignored
t_reset_redirect();
retour_handle_request(array(
    'status'   => 'ok',
    'id'       => '12345',
    'Mt'       => '21900',
    'Ref'      => 'CFPSP_275086',
    'Auto'     => 'ABC123',
    'Erreur'   => '00000',
    'NumAppel' => '0280072651',
    'NumTrans' => '0620110018',
    'Carte'    => '497010XXXXXX0011',
), 'GET');
t_equal('https://www.twelvy.net/paiement/confirmation?id=12345&status=ok',
    t_last_redirect(), 'T39 Paybox fields ignored, redirect unchanged');

// T40 — attacker tries to override status via Paybox fields (can't; our status wins)
t_reset_redirect();
retour_handle_request(array(
    'status' => 'ok',
    'id'     => '12345',
    'Erreur' => '00021',  // refusal code — must not affect redirect status
), 'GET');
t_equal('https://www.twelvy.net/paiement/confirmation?id=12345&status=ok',
    t_last_redirect(), 'T40 Paybox Erreur field cannot override our status');

// T41 — attacker tries to inject their own URL — we build URL server-side
t_reset_redirect();
retour_handle_request(array(
    'status' => 'ok',
    'id'     => '12345',
    'dest'   => 'https://evil.example/phish',  // malicious param
), 'GET');
t_equal('https://www.twelvy.net/paiement/confirmation?id=12345&status=ok',
    t_last_redirect(), 'T41 attacker-injected `dest` param ignored (URL is server-built)');

t_section('6. Edge cases');

// T42 — status with newline (header injection attempt)
t_reset_redirect();
retour_handle_request(array('status' => "ok\r\nLocation: https://evil.com", 'id' => '12345'), 'GET');
$redirect = t_last_redirect();
t_assert(strpos($redirect, "\r\n") === false,
    'T42.a redirect has no CRLF (header injection defense)', 'got: ' . var_export($redirect, true));
t_assert(strpos($redirect, 'evil.com') === false,
    'T42.b attacker URL not in redirect', 'evil.com leaked');
t_equal('https://www.twelvy.net/paiement/confirmation?id=12345&status=annule',
    $redirect, 'T42.c status with CRLF falls back to "annule"');

// T43 — id with URL-unsafe characters (urlencode handles it, but id should've been rejected earlier)
t_reset_redirect();
retour_handle_request(array('status' => 'ok', 'id' => '12345/../../etc/passwd'), 'GET');
t_equal('https://www.twelvy.net/',
    t_last_redirect(), 'T43 path traversal attempt in id → rejected at validation');

// T44 — empty params array
t_reset_redirect();
retour_handle_request(array(), 'GET');
t_equal('https://www.twelvy.net/', t_last_redirect(), 'T44 empty params → homepage');

// T45 — non-array params override defaults to empty
t_reset_redirect();
retour_handle_request('not an array', 'GET');
t_equal('https://www.twelvy.net/', t_last_redirect(), 'T45 non-array params → homepage (no crash)');

// =========================================================================
// SUMMARY
// =========================================================================
echo "\n" . str_repeat('=', 60) . "\n";
echo "RESULTS: " . $TESTS_PASSED . " passed, " . $TESTS_FAILED . " failed\n";
if ($TESTS_FAILED > 0) {
    echo "\nFailures:\n";
    foreach ($FAILURES as $f) {
        echo "  - " . $f['name'] . ": " . $f['detail'] . "\n";
    }
    exit(1);
}
echo "\033[32mAll retour.php tests passed ✓\033[0m\n";
exit(0);
