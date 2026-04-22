<?php
// =========================================================================
// test_ipn.php — comprehensive test runner for ipn.php
// Usage: php php/ipn_tests/test_ipn.php
// Exit 0 on all pass, non-zero if any test fails.
// =========================================================================

require_once __DIR__ . '/bootstrap.php';

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

// =========================================================================
// TEST SUITE
// =========================================================================

t_section('1. ipn_parse_body — raw body splitting');

// T1 — simple body with Sign at end
$raw1 = 'Mt=21900&Ref=CFPSP_275086&Erreur=00000&Sign=AAAAAAA%3D%3D';
$p1 = ipn_parse_body($raw1);
t_equal('21900', $p1['fields']['Mt'], 'T1.a Mt parsed');
t_equal('CFPSP_275086', $p1['fields']['Ref'], 'T1.b Ref parsed');
t_equal('00000', $p1['fields']['Erreur'], 'T1.c Erreur parsed');
t_equal('AAAAAAA==', $p1['sign_b64'], 'T1.d Sign URL-decoded');
t_equal('Mt=21900&Ref=CFPSP_275086&Erreur=00000', $p1['signed_msg'], 'T1.e Sign stripped from signed_msg');

// T2 — Sign in middle position
$raw2 = 'Mt=100&Sign=BBBB&Ref=CFPSP_1';
$p2 = ipn_parse_body($raw2);
t_equal('BBBB', $p2['sign_b64'], 'T2.a Sign in middle parsed');
t_equal('Mt=100&Ref=CFPSP_1', $p2['signed_msg'], 'T2.b Sign stripped from middle');

// T3 — Sign at start
$raw3 = 'Sign=CCCC&Mt=100&Ref=X';
$p3 = ipn_parse_body($raw3);
t_equal('CCCC', $p3['sign_b64'], 'T3.a Sign at start parsed');
t_equal('Mt=100&Ref=X', $p3['signed_msg'], 'T3.b Sign stripped from start, no leading &');

// T4 — empty body
$p4 = ipn_parse_body('');
t_equal(array(), $p4['fields'], 'T4.a empty body → empty fields');
t_equal('', $p4['sign_b64'], 'T4.b empty body → empty Sign');

// T5 — body with no Sign field
$raw5 = 'Mt=100&Ref=X';
$p5 = ipn_parse_body($raw5);
t_equal('', $p5['sign_b64'], 'T5 no Sign field → empty Sign');

t_section('2. ipn_verify_signature — RSA verification');

// T6 — valid signed body passes verification
$body_ok_fields = array('Mt' => '21900', 'Ref' => 'CFPSP_275086', 'Erreur' => '00000',
                        'Auto' => 'ABC123', 'NumAppel' => '0280072651', 'NumTrans' => '0620110018',
                        'Carte' => '497010XXXXXX0011');
$body_ok = ipn_test_build_signed_body($body_ok_fields);
$parsed_ok = ipn_parse_body($body_ok);
t_assert(ipn_verify_signature($parsed_ok['signed_msg'], $parsed_ok['sign_b64']),
    'T6 valid signature → verify true', 'signature rejected');

// T7 — tampered body fails verification
$body_tampered = str_replace('21900', '99999', $body_ok);
$parsed_tamp = ipn_parse_body($body_tampered);
t_assert(!ipn_verify_signature($parsed_tamp['signed_msg'], $parsed_tamp['sign_b64']),
    'T7 tampered body → verify false', 'tampered body was accepted — CRITICAL FAILURE');

// T8 — wrong signature (truncated)
t_assert(!ipn_verify_signature($parsed_ok['signed_msg'], substr($parsed_ok['sign_b64'], 10)),
    'T8 truncated Sign → verify false', 'truncated sig accepted');

// T9 — malformed base64 Sign
t_assert(!ipn_verify_signature('Mt=100', '!!!!not-base64!!!!'),
    'T9 invalid base64 Sign → verify false', 'invalid b64 accepted');

// T10 — empty Sign
t_assert(!ipn_verify_signature('Mt=100', ''),
    'T10 empty Sign → verify false', 'empty sign accepted');

// T11 — empty signed message
t_assert(!ipn_verify_signature('', 'AAAA'),
    'T11 empty message → verify false', 'empty msg accepted');

t_section('3. ipn_is_already_paid — idempotence check');

t_assert(ipn_is_already_paid(array('status'=>'inscrit','numappel'=>'1','numtrans'=>'2')),
    'T12 inscrit + numappel + numtrans → paid', 'expected true');
t_assert(!ipn_is_already_paid(array('status'=>'pre-inscrit','numappel'=>'','numtrans'=>'')),
    'T13 pre-inscrit → NOT paid', 'expected false');
t_assert(!ipn_is_already_paid(array('status'=>'inscrit','numappel'=>'','numtrans'=>'2')),
    'T14 inscrit but empty numappel → NOT paid', 'expected false');
t_assert(!ipn_is_already_paid(array('status'=>'inscrit','numappel'=>'1','numtrans'=>'')),
    'T15 inscrit but empty numtrans → NOT paid', 'expected false');
t_assert(!ipn_is_already_paid(null),
    'T16 null row → NOT paid', 'expected false');
t_assert(!ipn_is_already_paid(array()),
    'T17 empty array → NOT paid', 'expected false');

t_section('4. ipn_classify_error — UX categorisation (Kader rule: never leak raw code)');

t_equal('erreur_saisie_carte', ipn_classify_error('00114')['category'], 'T18 00114 → erreur_saisie_carte');
t_equal('refus_banque',        ipn_classify_error('00021')['category'], 'T19 00021 → refus_banque');
t_equal('probleme_3ds',        ipn_classify_error('00204')['category'], 'T20 00204 → probleme_3ds');
t_equal('carte_bloquee',       ipn_classify_error('00143')['category'], 'T21 00143 → carte_bloquee');
t_equal('erreur_technique',    ipn_classify_error('00001')['category'], 'T22 00001 → erreur_technique');
t_equal('erreur_inconnue',     ipn_classify_error('99999')['category'], 'T23 99999 → erreur_inconnue');

// Kader rule: no message should contain the raw code string
$unknown = ipn_classify_error('99999');
t_assert(strpos($unknown['message'], '99999') === false,
    'T24 unknown code never leaks raw code to user (Kader rule)',
    'raw code found in message: ' . $unknown['message']);
t_assert(strlen(ipn_classify_error('00021')['message']) > 20,
    'T25 classifier returns non-empty French message', 'message too short');

t_section('5. DB layer — SQLite-backed integration (seeded fresh per test)');

// T26 — lookup existing reference
$ids = ipn_test_reset_db();
$row = ipn_lookup_by_reference(ipn_db(), 'CFPSP_275086');
t_assert($row !== null, 'T26.a lookup existing reference → row found', 'row null');
t_equal($ids['stagiaire_id'], (int)$row['stagiaire_id'], 'T26.b joined stagiaire_id matches');
t_equal($ids['order_id'],     (int)$row['order_id'],     'T26.c joined order_id matches');
t_equal(289,                  (int)$row['id_membre'],    'T26.d joined id_membre matches');

// T27 — lookup unknown reference
$row_null = ipn_lookup_by_reference(ipn_db(), 'CFPSP_DOES_NOT_EXIST');
t_assert($row_null === null, 'T27 lookup unknown reference → null', 'got non-null');

t_section('6. ipn_apply_success_writes — 4-write SQL contract');

$ids = ipn_test_reset_db();
$pdo = ipn_db();
$row = ipn_lookup_by_reference($pdo, 'CFPSP_275086');
$pdo->beginTransaction();
ipn_apply_success_writes($pdo, $row, '0280072651', '0620110018', '497010XXXXXX0011', 21900);
$pdo->commit();

// Write 1/4 — stagiaire promoted
$s = $pdo->query("SELECT * FROM stagiaire WHERE id = 40120317")->fetch();
t_equal('inscrit',          $s['status'],           'T28 stagiaire.status = inscrit');
t_equal('0280072651',       $s['numappel'],         'T29 stagiaire.numappel written');
t_equal('0620110018',       $s['numtrans'],         'T30 stagiaire.numtrans written');
t_equal('497010XXXXXX0011', $s['numero_cb'],        'T31 stagiaire.numero_cb written (masked)');
t_equal('Capturé',          $s['up2pay_status'],    'T32 stagiaire.up2pay_status = Capturé');
t_equal(null,               $s['up2pay_code_error'],'T33 stagiaire.up2pay_code_error nulled');
t_equal(219,                (int)$s['paiement'],    'T34 stagiaire.paiement = euros');
t_equal(274086,             (int)$s['facture_num'], 'T35 stagiaire.facture_num = num_suivi - 1000');
t_equal(0,                  (int)$s['supprime'],    'T36 stagiaire.supprime = 0');

// Write 2/4 — order_stage paid
$o = $pdo->query("SELECT * FROM order_stage WHERE id = 150001")->fetch();
t_equal(1, (int)$o['is_paid'], 'T37 order_stage.is_paid = 1');

// Write 3/4 — archive row
$a = $pdo->query("SELECT COUNT(*) AS c FROM archive_inscriptions WHERE id_stagiaire = 40120317")->fetch();
t_equal(1, (int)$a['c'], 'T38 archive_inscriptions: exactly one row inserted');
$a2 = $pdo->query("SELECT * FROM archive_inscriptions WHERE id_stagiaire = 40120317")->fetch();
t_equal(329207, (int)$a2['id_stage'],  'T39 archive_inscriptions.id_stage correct');
t_equal(289,    (int)$a2['id_membre'], 'T40 archive_inscriptions.id_membre correct');

// Write 4/4 — stage decremented
$st = $pdo->query("SELECT * FROM stage WHERE id = 329207")->fetch();
t_equal(19, (int)$st['nb_places_allouees'], 'T41 stage.nb_places_allouees = 20 - 1 = 19');
t_equal(4,  (int)$st['nb_inscrits'],        'T42 stage.nb_inscrits = 3 + 1 = 4');
t_equal(4,  (int)$st['taux_remplissage'],   'T43 stage.taux_remplissage = 3 + 1 = 4');

t_section('7. ipn_apply_refuse_writes — limited SQL contract');

$ids = ipn_test_reset_db();
$pdo = ipn_db();
$row = ipn_lookup_by_reference($pdo, 'CFPSP_275086');
$pdo->beginTransaction();
ipn_apply_refuse_writes($pdo, $row, '00021');
$pdo->commit();

$s = $pdo->query("SELECT * FROM stagiaire WHERE id = 40120317")->fetch();
t_equal('00021',      $s['up2pay_code_error'], 'T44 stagiaire.up2pay_code_error written');
t_equal('Refusé',     $s['up2pay_status'],     'T45 stagiaire.up2pay_status = Refusé');
t_equal('pre-inscrit',$s['status'],            'T46 stagiaire.status UNCHANGED (still pre-inscrit)');
t_equal('',           $s['numappel'],          'T47 stagiaire.numappel stays empty');
t_equal('',           $s['numtrans'],          'T48 stagiaire.numtrans stays empty');

// order_stage/stage/archive must NOT be touched
$o = $pdo->query("SELECT * FROM order_stage WHERE id = 150001")->fetch();
t_equal(0, (int)$o['is_paid'], 'T49 order_stage.is_paid unchanged (still 0)');

$st = $pdo->query("SELECT * FROM stage WHERE id = 329207")->fetch();
t_equal(20, (int)$st['nb_places_allouees'], 'T50 stage.nb_places_allouees unchanged');
t_equal(3,  (int)$st['nb_inscrits'],        'T51 stage.nb_inscrits unchanged');

$a = $pdo->query("SELECT COUNT(*) AS c FROM archive_inscriptions")->fetch();
t_equal(0, (int)$a['c'], 'T52 archive_inscriptions stays empty on refuse');

// tracking row inserted
$tr = $pdo->query("SELECT * FROM tracking_payment_error_code WHERE id_stagiaire = 40120317")->fetch();
t_assert($tr !== false, 'T53 tracking_payment_error_code row inserted', 'no row found');
t_equal('00021',  $tr['error_code'], 'T54 tracking error_code correct');
t_equal('up2pay', $tr['source'],     'T55 tracking source = up2pay');

t_section('8. ipn_handle_request — end-to-end integration');

// T56 — success path (full handler)
ipn_test_reset_captures();
ipn_test_reset_db();
$body = ipn_test_build_signed_body(array(
    'Mt' => '21900', 'Ref' => 'CFPSP_275086', 'Auto' => 'ABC123',
    'Erreur' => '00000', 'NumAppel' => '0280072651', 'NumTrans' => '0620110018',
    'Carte' => '497010XXXXXX0011',
));
ipn_handle_request($body, 'POST');
$resp = $GLOBALS['IPN_LAST_RESPONSE'];
t_assert($resp !== null, 'T56.a handler produced response', 'no response');
t_equal(200, $resp['status'], 'T56.b success → HTTP 200');
t_equal('OK', $resp['body'], 'T56.c success → body "OK"');

$s = ipn_db()->query("SELECT status, numappel FROM stagiaire WHERE id = 40120317")->fetch();
t_equal('inscrit',    $s['status'],   'T56.d stagiaire promoted to inscrit end-to-end');
t_equal('0280072651', $s['numappel'], 'T56.e numappel stored end-to-end');

// Emails captured
$mails = $GLOBALS['IPN_TEST_SENT_MAILS'];
t_assert(count($mails) >= 2, 'T57 success sends at least 2 emails (customer + center)',
    'got ' . count($mails) . ' emails');
$to_list = array();
foreach ($mails as $m) { $to_list[] = $m['to']; }
t_assert(in_array('stagiaire-test@example.invalid', $to_list, true),
    'T58 customer email sent', 'customer email not found in ' . implode(',', $to_list));
t_assert(in_array('centre-test@example.invalid', $to_list, true),
    'T59 center email sent', 'center email not found in ' . implode(',', $to_list));

// T60 — idempotence: re-POST same body, expect already-paid + no double-writes
ipn_test_reset_captures();
$places_before = (int)ipn_db()->query("SELECT nb_places_allouees FROM stage WHERE id = 329207")->fetch()['nb_places_allouees'];
$archive_before = (int)ipn_db()->query("SELECT COUNT(*) AS c FROM archive_inscriptions")->fetch()['c'];
ipn_handle_request($body, 'POST');
$resp2 = $GLOBALS['IPN_LAST_RESPONSE'];
t_equal(200, $resp2['status'],                 'T60.a idempotent replay → HTTP 200');
t_equal('already paid', $resp2['body'],        'T60.b idempotent replay → body "already paid"');
$places_after = (int)ipn_db()->query("SELECT nb_places_allouees FROM stage WHERE id = 329207")->fetch()['nb_places_allouees'];
$archive_after = (int)ipn_db()->query("SELECT COUNT(*) AS c FROM archive_inscriptions")->fetch()['c'];
t_equal($places_before, $places_after, 'T60.c stage NOT decremented twice (race-safe)');
t_equal($archive_before, $archive_after, 'T60.d archive NOT inserted twice');
t_equal(0, count($GLOBALS['IPN_TEST_SENT_MAILS']), 'T60.e idempotent replay sends NO emails');

// T61 — refuse path (full handler)
ipn_test_reset_captures();
ipn_test_reset_db();
$body_ref = ipn_test_build_signed_body(array(
    'Mt' => '21900', 'Ref' => 'CFPSP_275086', 'Auto' => '',
    'Erreur' => '00021', 'NumAppel' => '', 'NumTrans' => '',
    'Carte' => '',
));
ipn_handle_request($body_ref, 'POST');
$resp3 = $GLOBALS['IPN_LAST_RESPONSE'];
t_equal(200, $resp3['status'], 'T61.a refuse → HTTP 200 (we acknowledge receipt)');
$s = ipn_db()->query("SELECT status, up2pay_code_error FROM stagiaire WHERE id = 40120317")->fetch();
t_equal('pre-inscrit', $s['status'], 'T61.b refuse leaves status=pre-inscrit (retryable)');
t_equal('00021',       $s['up2pay_code_error'], 'T61.c refuse writes error code');
$mails = $GLOBALS['IPN_TEST_SENT_MAILS'];
t_equal(1, count($mails), 'T61.d refuse sends exactly 1 email (customer only)');
t_assert(strpos($mails[0]['subject'], 'Échec') !== false, 'T61.e refuse email subject mentions Échec',
    'subject: ' . $mails[0]['subject']);

// T62 — bad signature rejected + no DB writes
ipn_test_reset_captures();
ipn_test_reset_db();  // fresh DB: status=pre-inscrit
$tampered = str_replace('21900', '99999', $body);
ipn_handle_request($tampered, 'POST');
t_equal(403, $GLOBALS['IPN_LAST_RESPONSE']['status'], 'T62.a bad signature → HTTP 403');
$s_bad = ipn_db()->query("SELECT status FROM stagiaire WHERE id = 40120317")->fetch();
t_equal('pre-inscrit', $s_bad['status'], 'T62.b bad signature: NO DB writes (still pre-inscrit)');

// T63 — missing Sign
ipn_test_reset_captures();
ipn_test_reset_db();
ipn_handle_request('Mt=100&Ref=CFPSP_275086&Erreur=00000', 'POST');
t_equal(400, $GLOBALS['IPN_LAST_RESPONSE']['status'], 'T63 missing Sign → 400');

// T64 — missing Ref
ipn_test_reset_captures();
ipn_test_reset_db();
$body_noref = ipn_test_build_signed_body(array('Mt' => '21900', 'Erreur' => '00000'));
ipn_handle_request($body_noref, 'POST');
t_equal(400, $GLOBALS['IPN_LAST_RESPONSE']['status'], 'T64 missing Ref → 400');

// T65 — unknown reference
ipn_test_reset_captures();
ipn_test_reset_db();
$body_unknown = ipn_test_build_signed_body(array(
    'Mt' => '21900', 'Ref' => 'CFPSP_9999999', 'Auto' => 'X',
    'Erreur' => '00000', 'NumAppel' => '1', 'NumTrans' => '2', 'Carte' => '',
));
ipn_handle_request($body_unknown, 'POST');
t_equal(404, $GLOBALS['IPN_LAST_RESPONSE']['status'], 'T65 unknown reference → 404');

// T66 — GET method rejected
ipn_test_reset_captures();
ipn_handle_request('anything', 'GET');
t_equal(405, $GLOBALS['IPN_LAST_RESPONSE']['status'], 'T66 GET → 405');

// T67 — empty body
ipn_test_reset_captures();
ipn_handle_request('', 'POST');
t_equal(400, $GLOBALS['IPN_LAST_RESPONSE']['status'], 'T67 empty body → 400');

// T68 — missing Erreur field (edge)
ipn_test_reset_captures();
ipn_test_reset_db();
$body_noerreur = ipn_test_build_signed_body(array('Mt' => '100', 'Ref' => 'CFPSP_275086'));
ipn_handle_request($body_noerreur, 'POST');
t_equal(400, $GLOBALS['IPN_LAST_RESPONSE']['status'], 'T68 missing Erreur → 400');

// T69 — success code but missing NumAppel/NumTrans should fall through to refuse-like behavior
// (safer to refuse than to register without proof of transaction)
ipn_test_reset_captures();
ipn_test_reset_db();
$body_nonumbers = ipn_test_build_signed_body(array(
    'Mt' => '21900', 'Ref' => 'CFPSP_275086', 'Erreur' => '00000',
    'NumAppel' => '', 'NumTrans' => '',
));
ipn_handle_request($body_nonumbers, 'POST');
t_equal(200, $GLOBALS['IPN_LAST_RESPONSE']['status'], 'T69.a handler completes');
$s = ipn_db()->query("SELECT status FROM stagiaire WHERE id = 40120317")->fetch();
t_assert($s['status'] !== 'inscrit', 'T69.b incomplete success (no numappel/numtrans) NOT promoted',
    'stagiaire wrongly marked inscrit');

// T70 — Sign with URL-encoded special chars survives round-trip
ipn_test_reset_captures();
ipn_test_reset_db();
// Find a body whose signature contains + or / (base64 special chars) — try a few
$body_special = null;
for ($i = 0; $i < 20; $i++) {
    $candidate = ipn_test_build_signed_body(array(
        'Mt' => (string)(21900 + $i), 'Ref' => 'CFPSP_275086',
        'Auto' => 'Z', 'Erreur' => '00000',
        'NumAppel' => (string)$i, 'NumTrans' => (string)$i, 'Carte' => '',
    ));
    $parsed = ipn_parse_body($candidate);
    if (strpos($parsed['sign_b64'], '+') !== false || strpos($parsed['sign_b64'], '/') !== false) {
        $body_special = $candidate;
        break;
    }
}
if ($body_special !== null) {
    // This body has Mt != 21900 so reset + update order_stage.amount for it
    $parsed = ipn_parse_body($body_special);
    ipn_db()->exec("UPDATE order_stage SET amount = " . (int)$parsed['fields']['Mt'] . "/100 WHERE reference_order = 'CFPSP_275086'");
    ipn_handle_request($body_special, 'POST');
    t_equal(200, $GLOBALS['IPN_LAST_RESPONSE']['status'],
        'T70 Sign with special chars (+ or /) survives urlencode round-trip');
} else {
    echo "  \033[33m(T70 skipped — no Sign with special chars generated)\033[0m\n";
}

t_section('9. AUDIT FIX tests — new invariants added after code review');

// T71 — Amount mismatch on success → 200 + no DB writes
ipn_test_reset_captures();
ipn_test_reset_db();
// Stage prix = 219, so expected Mt = 21900. Send 99999 instead.
$body_wrong_amount = ipn_test_build_signed_body(array(
    'Mt' => '99999', 'Ref' => 'CFPSP_275086', 'Auto' => 'X',
    'Erreur' => '00000', 'NumAppel' => '1', 'NumTrans' => '2', 'Carte' => '',
));
ipn_handle_request($body_wrong_amount, 'POST');
t_equal(200, $GLOBALS['IPN_LAST_RESPONSE']['status'], 'T71.a amount mismatch → HTTP 200 (stop retries)');
t_equal('amount mismatch', $GLOBALS['IPN_LAST_RESPONSE']['body'], 'T71.b body says "amount mismatch"');
$s_mm = ipn_db()->query("SELECT status, numappel FROM stagiaire WHERE id = 40120317")->fetch();
t_equal('pre-inscrit', $s_mm['status'], 'T71.c amount mismatch: stagiaire NOT promoted');
t_equal('', $s_mm['numappel'], 'T71.d amount mismatch: numappel untouched');
$o_mm = ipn_db()->query("SELECT is_paid FROM order_stage WHERE id = 150001")->fetch();
t_equal(0, (int)$o_mm['is_paid'], 'T71.e amount mismatch: order_stage untouched');
$a_mm = ipn_db()->query("SELECT COUNT(*) AS c FROM archive_inscriptions")->fetch();
t_equal(0, (int)$a_mm['c'], 'T71.f amount mismatch: no archive row inserted');
t_equal(0, count($GLOBALS['IPN_TEST_SENT_MAILS']), 'T71.g amount mismatch: no emails sent');

// T72 — Amount mismatch on REFUSE path: doesn't apply (we skip the guard on refuse)
ipn_test_reset_captures();
ipn_test_reset_db();
$body_wrong_amount_refuse = ipn_test_build_signed_body(array(
    'Mt' => '99999', 'Ref' => 'CFPSP_275086', 'Auto' => '',
    'Erreur' => '00021', 'NumAppel' => '', 'NumTrans' => '', 'Carte' => '',
));
ipn_handle_request($body_wrong_amount_refuse, 'POST');
t_equal(200, $GLOBALS['IPN_LAST_RESPONSE']['status'], 'T72.a refuse with any Mt → 200 (Mt ignored on refuse)');
$s_r = ipn_db()->query("SELECT up2pay_code_error FROM stagiaire WHERE id = 40120317")->fetch();
t_equal('00021', $s_r['up2pay_code_error'], 'T72.b refuse still writes error code');

// T73 — Non-numeric Mt → 400
ipn_test_reset_captures();
ipn_test_reset_db();
$body_bad_mt = ipn_test_build_signed_body(array(
    'Mt' => 'abc', 'Ref' => 'CFPSP_275086', 'Auto' => 'X',
    'Erreur' => '00000', 'NumAppel' => '1', 'NumTrans' => '2', 'Carte' => '',
));
ipn_handle_request($body_bad_mt, 'POST');
t_equal(400, $GLOBALS['IPN_LAST_RESPONSE']['status'], 'T73 non-numeric Mt → 400');

// T74 — Negative Mt → 400 (ctype_digit rejects the minus sign)
ipn_test_reset_captures();
ipn_test_reset_db();
$body_neg_mt = ipn_test_build_signed_body(array(
    'Mt' => '-100', 'Ref' => 'CFPSP_275086', 'Auto' => 'X',
    'Erreur' => '00000', 'NumAppel' => '1', 'NumTrans' => '2', 'Carte' => '',
));
ipn_handle_request($body_neg_mt, 'POST');
t_equal(400, $GLOBALS['IPN_LAST_RESPONSE']['status'], 'T74 negative Mt → 400');

// T75 — Zero Mt → 400
ipn_test_reset_captures();
ipn_test_reset_db();
$body_zero_mt = ipn_test_build_signed_body(array(
    'Mt' => '0', 'Ref' => 'CFPSP_275086', 'Auto' => 'X',
    'Erreur' => '00000', 'NumAppel' => '1', 'NumTrans' => '2', 'Carte' => '',
));
ipn_handle_request($body_zero_mt, 'POST');
t_equal(400, $GLOBALS['IPN_LAST_RESPONSE']['status'], 'T75 zero Mt → 400');

// T76 — Stagiaire deleted between outer lookup and locked SELECT → 404
ipn_test_reset_captures();
ipn_test_reset_db();
// We simulate by deleting the stagiaire row. The lookup will fail at the OUTER stage
// (JOIN-based reference lookup) → returns null → 404 from that earlier path.
// To trigger the "deleted between lookup and lock" branch (M1), we'd need to override
// the PDO to inject a delete mid-flight — complex. Instead we assert the deletion
// path itself is handled correctly at the outer stage (which is the realistic scenario).
ipn_db()->exec("DELETE FROM stagiaire WHERE id = 40120317");
$body_ok = ipn_test_build_signed_body(array(
    'Mt' => '21900', 'Ref' => 'CFPSP_275086', 'Auto' => 'X',
    'Erreur' => '00000', 'NumAppel' => '1', 'NumTrans' => '2', 'Carte' => '',
));
ipn_handle_request($body_ok, 'POST');
t_equal(404, $GLOBALS['IPN_LAST_RESPONSE']['status'], 'T76.a deleted stagiaire (outer lookup miss) → 404');
// Order_stage still has reference but stagiaire gone → JOIN fails → lookup returns null
$archive_count = (int)ipn_db()->query("SELECT COUNT(*) AS c FROM archive_inscriptions")->fetch()['c'];
t_equal(0, $archive_count, 'T76.b no archive row inserted for deleted stagiaire');

// T77 — supprime=1 on "inscrit" stagiaire → is_already_paid returns false (idempotence allows retry)
t_assert(!ipn_is_already_paid(array('status'=>'inscrit','numappel'=>'1','numtrans'=>'2','supprime'=>1)),
    'T77 inscrit + supprime=1 → NOT considered already-paid (PSP parity)', 'expected false');
t_assert(ipn_is_already_paid(array('status'=>'inscrit','numappel'=>'1','numtrans'=>'2','supprime'=>0)),
    'T78 inscrit + supprime=0 → is already-paid', 'expected true');

// T79 — Full PAN in Carte field auto-masked before DB write
ipn_test_reset_captures();
ipn_test_reset_db();
$body_full_pan = ipn_test_build_signed_body(array(
    'Mt' => '21900', 'Ref' => 'CFPSP_275086', 'Auto' => 'X',
    'Erreur' => '00000', 'NumAppel' => '1', 'NumTrans' => '2',
    'Carte' => '4970100000001234',  // simulated full PAN (Paybox shouldn't send this but defend)
));
ipn_handle_request($body_full_pan, 'POST');
$s_pan = ipn_db()->query("SELECT numero_cb FROM stagiaire WHERE id = 40120317")->fetch();
t_equal('XXXXXXXXXXXX1234', $s_pan['numero_cb'], 'T79 full-PAN in Carte is auto-masked before DB write');

// T80 — Already-masked Carte is left alone
ipn_test_reset_captures();
ipn_test_reset_db();
$body_masked = ipn_test_build_signed_body(array(
    'Mt' => '21900', 'Ref' => 'CFPSP_275086', 'Auto' => 'X',
    'Erreur' => '00000', 'NumAppel' => '1', 'NumTrans' => '2',
    'Carte' => '497010XXXXXX1234',
));
ipn_handle_request($body_masked, 'POST');
$s_mk = ipn_db()->query("SELECT numero_cb FROM stagiaire WHERE id = 40120317")->fetch();
t_equal('497010XXXXXX1234', $s_mk['numero_cb'], 'T80 already-masked Carte preserved as-is');

// T81 — invalid recipient email → no crash, mail() rejected (M7 defense)
ipn_test_reset_captures();
// Directly call ipn_send_mail to avoid full flow
$result = ipn_send_mail("legit@x.com\r\nBcc: attacker@evil.com", 'Test', 'Body');
t_assert($result === false, 'T81 header-injection recipient rejected', 'mail should have returned false');

// T82 — valid email accepted
ipn_test_reset_captures();
$result = ipn_send_mail('ok@example.com', 'Test', 'Body');
t_assert($result === true, 'T82 valid email accepted', 'mail should have returned true in test mode');

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
echo "\033[32mAll tests passed ✓\033[0m\n";
exit(0);
