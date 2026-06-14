<?php
/**
 * twelvy_payment.php — Option B secure payment entry point.
 * DEPLOY TO: /psp-copie/www/twelvy_payment.php  (served by psp-copie.twelvy.net)
 * PHP 5.6 compatible (no null-coalescing, no arrow fns).
 *
 * FLOW
 *   Twelvy details form → /api/payment/create-prospect (creates stagiaire, returns
 *   a signed redirect_url) → browser full-page nav here:
 *     twelvy_payment.php?s=<stagiaire_id>&exp=<epoch>&sig=<hmac>
 *   We verify the HMAC capability token, ensure the order_stage row exists (PSP's own
 *   SaveStageOrder), then render PSP's own card form + payment.js (restyled).
 *   payment.js → /src/payment/ajax/* (3DS check) → RemoteMPI → twelvy_validate.php
 *   (patched copy of validate_payment.php) → twelvy.net/paiement/confirmation.
 *
 * SECURITY
 *   - Capability token: hash_hmac('sha256', "<s>|<exp>", TWELVY_HANDOFF_SECRET) with
 *     a 15-min expiry, verified with hash_equals. Replaces the legacy public md5 salt.
 *   - Card data is typed here and handled ONLY by PSP's existing code (same as prod).
 *   - DEBUG/HOST are overridden via define() BEFORE params.php (its guards are if(!defined)).
 */

// --- 0. Hardening: no error output to the browser -------------------------------
@ini_set('display_errors', '0');
error_reporting(E_ALL);
header('X-Content-Type-Options: nosniff');
header('X-Robots-Tag: noindex, nofollow');
header('Referrer-Policy: no-referrer');

// --- 1. Shared HOST + DEBUG (single source of truth, required BEFORE params.php) --
require_once '/home/khapmait/psp-copie/twelvy_env.php'; // defines HOST + DEBUG (=true sandbox)

// --- 2. Shared secret (NOT in git; deploy alongside, see twelvy_secret.example.php) ---
$secretFile = '/home/khapmait/psp-copie/twelvy_secret.php';
if (!is_file($secretFile)) { http_response_code(500); error_log('[twelvy_payment] missing twelvy_secret.php'); exit('Erreur de configuration.'); }
require_once $secretFile; // defines TWELVY_HANDOFF_SECRET
if (!defined('TWELVY_HANDOFF_SECRET') || TWELVY_HANDOFF_SECRET === '') { http_response_code(500); exit('Erreur de configuration.'); }

// --- 3. Validate the signed capability token ------------------------------------
$s   = isset($_GET['s'])   ? $_GET['s']   : '';
$exp = isset($_GET['exp']) ? $_GET['exp'] : '';
$sig = isset($_GET['sig']) ? $_GET['sig'] : '';

if (!ctype_digit((string)$s) || !ctype_digit((string)$exp) || !preg_match('/^[a-f0-9]{64}$/', (string)$sig)) {
    http_response_code(403); exit('Lien de paiement invalide.');
}
$studentId = (int)$s;
$expTs     = (int)$exp;
if ($studentId <= 0)        { http_response_code(403); exit('Lien de paiement invalide.'); }
if ($expTs < time())        { http_response_code(403); exit('Ce lien de paiement a expiré. Merci de recommencer votre réservation.'); }

// Purpose-separated subkey (matches lib/paymentToken.ts handoffKey).
$handoffKey = hash_hmac('sha256', 'twelvy-handoff-v1', TWELVY_HANDOFF_SECRET);
$expected = hash_hmac('sha256', $studentId . '|' . $expTs, $handoffKey);
if (!hash_equals($expected, (string)$sig)) {
    http_response_code(403); exit('Lien de paiement invalide.');
}

// --- 4. Bootstrap PSP code (paths already OVH-adapted) ---------------------------
session_start();
require_once '/home/khapmait/psp-copie/connections/config.php'; // -> $mysqli (mysqli, utf8)
require_once '/home/khapmait/psp-copie/www/params.php';         // APP, ROOT (HOST/DEBUG already set)
require_once APP . 'order/services/SaveStageOrder.php';
require_once APP . 'order/services/RetrieveFullOrderByStageStudent.php';
require_once APP . 'payment/E_Transaction/E_TransactionConfig.php';

if (!isset($mysqli) || $mysqli->connect_errno) { http_response_code(500); exit('Service indisponible.'); }

// --- 5. Load stagiaire + stage; confirm it belongs to a real, payable booking ----
// OVH PHP 5.6 has no mysqlnd → mysqli_stmt::get_result() is unavailable. $studentId is a
// strictly-validated integer (ctype_digit + (int) cast above) so a direct query is injection-safe.
$sql = "SELECT s.id, s.id_stage, s.email, s.nom, s.prenom, s.adresse, s.code_postal, s.ville,
               s.mobile, s.paiement, s.status, s.numappel, s.numtrans, s.supprime,
               st.prix, st.id_membre, st.date1, st.date2
        FROM stagiaire s
        INNER JOIN stage st ON st.id = s.id_stage
        WHERE s.id = " . $studentId . " LIMIT 1";
$res = $mysqli->query($sql);
$row = $res ? $res->fetch_assoc() : null;

if (!$row) { http_response_code(404); exit('Réservation introuvable.'); }

// Already paid? → straight to the confirmation page (no double charge).
// empty() (not !== '') so a NULL numappel/numtrans isn't mistaken for "paid"; require supprime=0.
// &already=1 is a COSMETIC hint so the confirmation page shows "Vous avez déjà réservé ce stage"
// instead of a fresh "Merci, paiement confirmé" (this booking was paid on a PRIOR visit, not now).
// The authoritative paid/refuse state still comes only from the token-verified status poll.
if ($row['status'] === 'inscrit' && !empty($row['numappel']) && !empty($row['numtrans']) && (int)$row['supprime'] === 0) {
    $confExp = time() + 7200;
    $confKey = hash_hmac('sha256', 'twelvy-conf-v1', TWELVY_HANDOFF_SECRET);
    $t = hash_hmac('sha256', $studentId . '|conf|' . $confExp, $confKey);
    header('Location: https://www.twelvy.net/paiement/confirmation?id=' . $studentId . '&status=ok&already=1&t=' . $t . '&te=' . $confExp);
    exit;
}

$stageId  = (int)$row['id_stage'];
$memberId = (int)$row['id_membre'];
$amount   = (int)$row['paiement'];          // euros — payment.js does *100. Set by bridge = stage.prix.
if ($amount <= 0) { $amount = (int)$row['prix']; }

// --- 6. Ensure the order_stage row exists (PSP's own creator) --------------------
// PSP's payment chain INNER-JOINs order_stage; create it once, idempotently.
$existingOrder = (new RetrieveFullOrderByStageStudent())->__invoke($studentId, $stageId, $mysqli);
if (!$existingOrder) {
    $saver = new SaveStageOrder();
    $saver->__invoke($studentId, $amount, $stageId, $memberId, $mysqli);
}

// --- 7. Session vars PSP's ajax/validate expect ---------------------------------
$_SESSION['id_stagiaire'] = $studentId;
$_SESSION['studentId']    = $studentId;
$_SESSION['stageId']      = $stageId;
$_SESSION['memberId']     = $memberId;

// --- 8. Up2Pay gateway config ---------------------------------------------------
list($MerchandId, $IdSession, $URLRetour_src, $URLHttpDirect, $PBX_Url) = E_TransactionConfig::getawayConfig();
// Override the 3DS return URL to our patched, Basic-Auth-exempt validate copy in /www/.
$URLRetour = HOST . '/twelvy_validate.php?d2305_' . session_id();

// --- 9. Render the card form (PSP form fields + payment.js), Twelvy-restyled ------
$h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
// JSON for inline <script> — HEX flags neutralise a customer-supplied "</script>" breakout.
$j = function ($v) { return json_encode($v, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); };
$prix    = (int)$row['prix'];
$dateTxt = $row['date1'];

// Twelvy (Problem 2 — "message au niveau du bloc CB"): a declined/failed payment redirects BACK here;
// twelvy_validate.php left the precise reason in $_SESSION['paiement_error']. That value is ALWAYS HTML
// built server-side — E_TransactionError::getFullTextErrorCodes() (a fixed code→message lookup table) or
// a hardcoded literal — so it carries NO user input and NO card data. We render it as trusted HTML, but
// pass it through strip_tags with a formatting-only allowlist as defense-in-depth, and consume it once.
$paiementError = '';
if (isset($_SESSION['paiement_error']) && is_string($_SESSION['paiement_error']) && $_SESSION['paiement_error'] !== '') {
    $paiementError = strip_tags($_SESSION['paiement_error'], '<u><br><b><strong><div><p><span>');
    unset($_SESSION['paiement_error']);
}
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Paiement sécurisé — Twelvy</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert@1.1.3/dist/sweetalert.min.css">
<style>
  :root { --tw-green:#41A334; --tw-blue:#2b85c9; }
  *{box-sizing:border-box} body{margin:0;font-family:Poppins,system-ui,Arial,sans-serif;background:#f6f7f9;color:#1f2937}
  .wrap{max-width:520px;margin:0 auto;padding:24px 16px}
  .card{background:#fff;border:1px solid #eef0f2;border-radius:16px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
  h1{font-size:20px;margin:0 0 4px;text-align:center}
  .sub{text-align:center;color:#6b7280;font-size:13px;margin-bottom:16px}
  .recap{background:#f3f4f6;border-radius:12px;padding:12px 16px;text-align:center;margin-bottom:20px;font-size:14px}
  .recap .total{font-weight:600;font-size:17px;margin-top:4px}
  label{display:block;font-size:13px;font-weight:500;margin:14px 0 6px}
  input,select{width:100%;height:46px;border:1px solid #d1d5db;border-radius:10px;padding:0 12px;font-size:15px}
  .exp{display:flex;gap:10px}
  .btn{width:100%;height:50px;border:none;border-radius:30px;background:var(--tw-green);color:#fff;font-size:16px;font-weight:600;margin-top:20px;cursor:pointer}
  .btn:disabled{opacity:.6;cursor:default}
  #rowError{display:none;background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:10px;padding:10px;font-size:13px;margin-top:12px}
  .pay-error{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:10px;padding:12px 14px;font-size:13.5px;line-height:1.45;margin-bottom:16px}
  .pay-error u{text-decoration:underline}
  .secure{text-align:center;color:#6b7280;font-size:12px;margin-top:14px}
  #loading-overlay{display:none;position:fixed;inset:0;background:rgba(255,255,255,.75);z-index:9999;align-items:center;justify-content:center}
  #loading-overlay .spin{width:46px;height:46px;border:4px solid #e5e7eb;border-bottom-color:var(--tw-green);border-radius:50%;animation:r 1s linear infinite}
  @keyframes r{to{transform:rotate(360deg)}}
</style>
</head>
<body>
<div id="loading-overlay"><div class="spin"></div></div>
<div class="wrap">
  <div class="card">
    <h1>Paiement sécurisé</h1>
    <div class="sub">Paiement chiffré — Crédit Agricole / Up2Pay e-Transactions</div>
    <div class="recap">
      <div>Stage du <?php echo $h($dateTxt); ?></div>
      <div class="total">Total à payer : <?php echo $h($prix); ?> € TTC</div>
    </div>

    <?php if ($paiementError !== '') { /* trusted server-built HTML (getFullTextErrorCodes), strip_tags'd above */ ?>
      <div class="pay-error" role="alert"><?php echo $paiementError; ?></div>
    <?php } ?>

    <form id="paiement_gae_div" onsubmit="return false;">
      <label for="cardNumber">Numéro de carte</label>
      <input id="cardNumber" type="text" inputmode="numeric" autocomplete="cc-number" maxlength="19" placeholder="1234 5678 9012 3456" required>

      <label>Date d'expiration</label>
      <div class="exp">
        <select id="month_expiration" autocomplete="cc-exp-month" required>
          <?php for ($m = 1; $m <= 12; $m++) { $mm = str_pad($m, 2, '0', STR_PAD_LEFT); echo '<option value="' . $mm . '">' . $mm . '</option>'; } ?>
        </select>
        <select id="year_expiration" autocomplete="cc-exp-year" required>
          <?php $yy = (int)date('y'); for ($y = $yy; $y <= $yy + 10; $y++) { $ys = str_pad($y, 2, '0', STR_PAD_LEFT); echo '<option value="' . $ys . '">20' . $ys . '</option>'; } ?>
        </select>
      </div>

      <label for="cardCVC">Cryptogramme (CVC)</label>
      <input id="cardCVC" type="text" inputmode="numeric" autocomplete="cc-csc" maxlength="4" placeholder="123" required>

      <div id="rowError"></div>

      <button type="button" class="btn" onclick="Payment.paymentWithCard(event)">Payer <?php echo $h($prix); ?> € TTC</button>
    </form>

    <div class="secure">🔒 Vos données bancaires ne sont jamais stockées par Twelvy.</div>
  </div>
</div>

<script>
  // Globals consumed by PSP's payment.js (HEX-escaped via $j — see above).
  var HOST          = <?php echo $j(HOST); ?>;
  var url           = <?php echo $j($PBX_Url); ?>;
  var MerchandId    = <?php echo $j($MerchandId); ?>;
  var IdSession     = <?php echo $j((string)$IdSession); ?>;
  var urlRetour     = <?php echo $j($URLRetour); ?>;
  var urlRedirect   = <?php echo $j($URLHttpDirect); ?>;
  var amount        = <?php echo $j((float)$amount); ?>;
  var stageId       = <?php echo $j((string)$stageId); ?>;
  var studentId     = <?php echo $j((string)$studentId); ?>;
  var email         = <?php echo $j($row['email']); ?>;
  var session_id    = <?php echo $j(session_id()); ?>;
  var URL_FERERENCE = <?php echo $j('https://www.twelvy.net'); ?>;
  var funnelId      = '';
  var upsellId      = '';
  // 3DSv2 cardholder fields (from the stagiaire row; may be empty on a fresh prospect).
  var FirstName     = <?php echo $j($row['prenom']); ?>;
  var LastName      = <?php echo $j($row['nom']); ?>;
  var Address1      = <?php echo $j($row['adresse']); ?>;
  var ZipCode       = <?php echo $j($row['code_postal']); ?>;
  var City          = <?php echo $j($row['ville']); ?>;
  var CountryCode   = '250';
  var EmailPorteur  = <?php echo $j($row['email']); ?>;
  var NumTelephone  = <?php echo $j($row['mobile']); ?>;
  var TotalQuantity = 1;
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert@1.1.3/dist/sweetalert.min.js"></script>
<script>
  // PSP's payment.js does `await tracking.addTracking(...)` right before form.submit().
  // The real TrackingPathUser POSTs cross-origin to prostagespermis.fr and would REJECT
  // (CORS/404) → the await would throw → the payment form would never submit. Stub it.
  var tracking = { addTracking: function () { return Promise.resolve(); } };
</script>
<script src="/twelvy_assets/card.js"></script>
<script src="/twelvy_assets/payment.js"></script>
</body>
</html>
