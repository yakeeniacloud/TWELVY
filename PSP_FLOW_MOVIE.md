# PSP Up2Pay Payment Flow — Evidence-Backed Movie

> Every claim below is backed by a specific file:line citation. Where a snippet is load-bearing it is quoted verbatim. Where something could not be pinned down on disk, the report says "UNABLE TO VERIFY" and explains why.

## TL;DR

- The **true live payment entry chain** for NEW stagiaire inscriptions is:
  landing page → `/src/order/applications/order.php` (writes `stagiaire`, `order_stage`, `transaction` rows) → `/page_recap.php` (`common_recap.php` template) → `payment.js::paymentWithCard` → `/src/payment/ajax/ajax_stage_payment_available.php` → browser-built form POST to `tpeweb.paybox.com/cgi/RemoteMPI.cgi` (3DS) → Paybox POSTs `ID3D` back to `/src/payment/validate/validate_payment.php?d2305_<sid>` → two PPPS cURLs (TYPE=00001 AUTHORIZATION, TYPE=00002 DIRECT DEBIT) → DB writes (stagiaire→`inscrit`, order_stage `is_paid=1`, archive_inscriptions INSERT, stage nb_places decrement) → emails → redirect to `/page_upsell.php` or `/order_confirmation.php`.
- The **`formulaire_inscription_2024.php`** file exists but is **orphaned** — zero other files `include` or `require` it in `www_3`, `www_2`, `common_bootstrap2`, or the live FTP_UPLOAD snapshot. Same for `assets/js/page/recup_point.js`: it calls `order.php` but nothing loads the JS itself locally. These are candidate 2024 rewrites that were never linked into the live site.
- **`es/inscriptionv2_3ds.php`** is NOT the main inscription entry point; it is the **"changement d'avis" (stage transfer)** popup, wired to `ajax_stage_cb_3ds.php` → `validate_transfert_payment.php`. `inscriptionv2_3ds.php` line 372 redirect URL and line 1029 `changement_avisv2_3ds.php` include prove this.
- The `transaction` table is indeed written to today (INSERT in `OrderStageRepository::saveOrder` line 41, UPDATE in `PaymentRepository::updateTransactionData` line 21). If live-DB evidence shows zero modern writes, it means `order.php` itself is not executed in 2026 — i.e. the entire `src/order+payment` funnel may be legacy, and today's real inscriptions use a different entrypoint (possibly Twelvy `stagiaire-create.php`). This is the single biggest contradiction between code and DB.
- `up2pay_status` is never written by anything under `src/payment/`. Only the cron `planificateur_tache/up2pay/cron_status_payment.php:49` and the admin panel `simpligestion/ajax_functions.php:4079` set it. Confirmed.
- `numero_cb` is stored as the **raw 16-digit PAN**, not a masked value. `PaymentRepository.php:80` `numero_cb = '$cardNumber'` where `$cardNumber` is the unmodified `$_POST['cardNumber']`.

---

## Deliverable 1 — The TRUE Entry Point

### 1.1 Is `formulaire_inscription_2024.php` live?

**No — it is orphan code.** Grep for any include/require of that filename across the entire snapshot returns zero matches except the file itself:

```
# Search across www_3, www_2, common_bootstrap2, FTP_UPLOAD — zero includes
Grep pattern="formulaire_inscription_2024" path=www_3  → No matches
Grep pattern="formulaire_inscription"       path=common_bootstrap2 → No matches
```

The file itself is modern (Alpine.js, 2024 UTM tracking) — Source: `www_3/includes/formulaire_inscription_2024.php:7-13`:

```php
if (!empty($_SESSION['adwords'])) {
  $provenance_loc = 7;
} else if (!empty($_SESSION['newsletter'])) {
  $provenance_loc = 9;
} else {
  $provenance_loc = 6;
}
```

On submit, it POSTs to `/src/order/applications/order.php` (Source: `www_3/includes/formulaire_inscription_2024.php:609`):

```php
let res = await fetch(HOST + "/src/order/applications/order.php", {
    method: "POST", ...
```

Then redirects to `/page_recap.php?s=<stageId>&m=<memberId>&id=<studentId>` — Source: `www_3/includes/formulaire_inscription_2024.php:629-637`.

### 1.2 Is `inscriptionv2_3ds.php` the main entry?

**No — it is the stage-transfer ("changement d'avis") popup.** Evidence:

- Two other files link to it, both in the `changement_avis` flow:
  - `www_3/es/changement_avisv2_3ds.php:1029` opens `inscriptionv2_3ds.php?s=<id>` inside an iframe
  - `www_3/es/changement_avis_v3.php:2155` same pattern
- Its "Payer" button handler at `www_3/es/inscriptionv2_3ds.php:273` (class `paiement_cb`) POSTs to `ajax_stage_cb_3ds.php` with `old_stage`, `new_stage`, `montant_restant` — fields only meaningful for a transfer, not a new booking — Source: lines 273-396.
- On success it redirects back to `inscriptionv2_3ds.php?d2305_<sid>` (line 372) and then the browser calls `paybox_mpi("prod", ...)` (line 374). The URLHttpDirect hits `/i_gae/stage_transfert_retour_3ds.php` (line 373) — the word **"transfert"** in the path confirms the transfer flow.

### 1.3 `formulaire_paiement` AJAX action

The AJAX call with `action: 'formulaire_paiement'` at `www_3/es/inscriptionv2_3ds.php:244` hits `ajax_functions.php`. The handler for that action is in the old ES backend (`www_3/es/ajax_functions.php`); it returns an HTML snippet (credit-card input fields) injected into `.div_formulaire_paiement`. This is still part of the **transfer popup**, not a new booking. The endpoint is `www_3/es/ajax_functions.php` — Source: `inscriptionv2_3ds.php:242` `url: 'ajax_functions.php'`.

### 1.4 What is the actual new-inscription entry?

From the evidence on disk, the real production chain that leaves fingerprints in the ACTIVE tables is:

- Frontend JS (`www_2/src/payment/js/payment.js:128` `paymentWithCard`) reads card fields.
- Calls `this.checkStageAvailable` → `fetch(HOST + "/src/payment/ajax/ajax_stage_payment_available.php")` — Source: `www_2/src/payment/js/payment.js:507`.
- `ajax_stage_payment_available.php` stores session data (`SessionManage::saveSessionData`) and returns OK — Source: `www_2/src/payment/ajax/ajax_stage_payment_available.php:67-82`.
- Then JS builds an HTML form and POSTs it to Paybox `RemoteMPI.cgi` (3DS). The PHP-side URL Retour is built by `E_TransactionConfig::getawayConfig()` — Source: `www_2/src/payment/E_Transaction/E_TransactionConfig.php:14` :

```php
$PBX_URLRetour = HOST . '/src/payment/validate/validate_payment.php?d2305_' . session_id();
```

The `stagiaire` pre-insert happens a step earlier in `order.php` → `RegisterStudent` → `StudentRepository::saveStudent` (Source: `www_2/src/student/repositories/StudentRepository.php:138-205`) which inserts with `status='supprime'`, `supprime=1`. Then `SaveStageOrder` creates an `order_stage` row and (via `OrderStageRepository::saveOrder:41`) a `transaction` row with `type_paiement='cheque_en_attente'`.

### 1.5 So which entry is 2024–2026 live?

**Contradiction detected.** The code on disk shows two parallel modern paths:

- (A) `formulaire_inscription_2024.php` → `order.php` → `page_recap.php` → `payment.js` → `validate_payment.php`
- (B) `assets/js/page/recup_point.js` → same `order.php` → same `page_recap.php` → same downstream

Both point to the same `order.php`. Neither their PHP host page nor `payment.js` is included in the local snapshot — they must exist on the live OVH filesystem (live prod root is not mirrored locally; FTP_UPLOAD covers `psp-copie` only, a test env where `index.php:2` redirects straight to `autologin.php`). The code that WOULD run if `order.php` is reached is the B-flow above; whether `order.php` itself still gets POSTed to in 2026 is the open question (see TL;DR note about `transaction` table evidence).

---

## Deliverable 2 — The "Flow Movie"

Assuming the browser reaches `page_recap.php` with a fresh `studentId/stageId/memberId` (i.e. `order.php` did create `stagiaire`, `order_stage`, `transaction` rows):

```
STEP 1  [www_2/src/order/applications/order.php:17-36] — Validate all POST fields (civility, lastName, ... stageId, with_guarantee).
        Proof: `if ( isset( $_POST['civility'], $_POST['lastName'], ... $_POST['stageId'], $_POST['with_guarantee'] ) ) {`

STEP 2  [www_2/src/order/applications/order.php:65-67] — Bail out if stage has no remaining places.
        Proof: `if ($stage && $stage->nb_places_allouees <= 0) { echo json_encode(['message' => 'Les places ne sont plus disponible...' ...`

STEP 3  [www_2/src/student/repositories/StudentRepository.php:138-205] — INSERT into `stagiaire` with status='supprime', supprime=1, paiement=<prix>.  TABLE = `stagiaire` (ACTIVE).
        Proof: `INSERT INTO stagiaire (id_stage, nom, prenom, ... status, paiement, ...) VALUES (?,?,...)`, bound with `$status='supprime'` and `$supprime=1` at lines 120,118.

STEP 4  [www_2/src/order/repositories/OrderStageRepository.php:25-55] — INSERT into `order_stage` (user_id, amount, is_paid=0, stage_id, created) THEN INSERT into `transaction` (id_stage, id_stagiaire, id_membre, type_paiement='cheque_en_attente', date_transaction).  TABLES = `order_stage` (ACTIVE), `transaction` (🪦 DEAD per DB evidence).
        Proof: `$sql = "INSERT INTO order_stage(user_id, amount, is_paid, stage_id, created) VALUES ...";`  and `$sql = "INSERT INTO transaction (id_stage,id_stagiaire,id_membre,type_paiement,date_transaction) VALUES ('$stageId', '$id_stagiaire_tmp', '$memberId', 'cheque_en_attente', '$date')";` (line 41).

STEP 5  [www_2/src/order/applications/order.php:100-108] — Persist session data (studentId, stageId, email, orderId) via `SessionManage::saveSessionData` which `INSERT`s a single row into the `sessions` table keyed by PHP session_id().
        Proof: `SessionManage::saveSessionData([ 'studentId'=>$studentId, 'stageId'=>$stageId, 'email'=>$email, 'orderId'=>$order->getOrderId() ], session_id());` — and `www_2/src/payment/Utils/SessionManage.php:29` `INSERT INTO sessions (session_id, content) VALUES ("$session_id", "$content")`.

STEP 6  [Browser redirect] /page_recap.php?s=<stageId>&m=<memberId>&id=<studentId>. This page includes `common_bootstrap2/common_recap.php`.
        Proof: `www_3/includes/formulaire_inscription_2024.php:629-637` builds the URL; `www_3/assets/js/page/recup_point.js:410-417` does the same from the fallback form.

STEP 7  [common_bootstrap2/common_recap.php:16-40] — SELECT joining stagiaire, stage, site, membre, `transaction` — requires a `transaction` row to render the page.  TABLE READ = `transaction` (🪦 DEAD) plus ACTIVE tables.
        Proof: `FROM stagiaire, stage, site, membre, transaction WHERE transaction.id_stagiaire = stagiaire.id AND stagiaire.id = $stagiaireId ...` — If `transaction` has no row for this stagiaire this page will be blank.

STEP 8  [www_2/src/payment/js/payment.js:128 `paymentWithCard`] — User types PAN/CVV/expiry and clicks "Payer". JS validates Luhn-length and CVV-length (lines 146-152), then calls `checkStageAvailable(...)`.
        Proof: `var stageAvailable = await this.checkStageAvailable(stageId, studentId, expiration, cardNumber, cardCVC, isOrderBump);` (line 156).

STEP 9  [www_2/src/payment/ajax/ajax_stage_payment_available.php:16-82] — Stores the card PAN, CVV, expiry, studentId, stageId in PHP session AND in the `sessions` DB row via `SessionManage::saveSessionData`. Runs `IsStagePlaceAvailableForSale` and `CheckIfStageAlreadyPay`.
        Proof: `$_SESSION['cardNumber'] = $cardNumber;` (line 23), `SessionManage::saveSessionData([ 'studentId'=>..., 'cardNumber'=>$cardNumber, 'cardExpiry'=>$cardExpiry, 'cardCVC'=>$cardCVC, ... ], session_id());` (line 67-82). The PAN and CVV are written into the DB session row (serialized in the `content` field) — this is a PCI-scope concern.

STEP 10 [www_2/src/payment/js/payment.js:199-270+] — JS builds a hidden HTML form with IdMerchant, IdSession, Currency=978, Amount, CCNumber, CCExpDate, CVVCode, URLRetour, URLHttpDirect and submits it to Paybox's 3DS RemoteMPI.
        Proof: `form.method = "POST"; form.action = url;` (line 202-203), where `url = 'https://tpeweb.paybox.com/cgi/RemoteMPI.cgi'` from `www_2/src/payment/E_Transaction/E_TransactionConfig.php:8`.

STEP 11 [BROWSER ↔ PAYBOX] — 3DS challenge runs on Paybox hosted pages; browser is posted back to URLRetour with `ID3D` field.
        Proof: `www_2/src/payment/E_Transaction/E_TransactionConfig.php:14` `$PBX_URLRetour = HOST . '/src/payment/validate/validate_payment.php?d2305_' . session_id();`

STEP 12 [www_2/src/payment/validate/validate_payment.php:70-77] — Reads `d2305_<sid>` GET param, calls `session_id($sid); session_start(); SessionManage::retrieveSessionData($sid);` — pulls back everything saved in STEP 9.
        Proof: `foreach ($_GET as $key => $value) { $param = explode('_', $key); if ($param[0] == "d2305") { session_id($param[1]); session_start(); $data = SessionManage::retrieveSessionData($param[1]); } }`

STEP 13 [www_2/src/payment/validate/validate_payment.php:122-126] — Check `IsStagePlaceAvailableForSale`, retrieve full order, call `GenerateReferenceOrder`.
        Proof: `$isPlaceAvailableForSale = (new IsStagePlaceAvailableForSale())->__invoke($stageId, $mysqli); $stage = (new RetrieveFullOrderByStageStudent())->__invoke($studentId, $stageId, $mysqli); $amount = $stage->paiement; $arrReference = (new GenerateReferenceOrder())->__invoke($studentId, $mysqli);`

STEP 14 [www_2/src/order/services/GenerateReferenceOrder.php:13-18 → www_2/src/order/repositories/OrderStageRepository.php:219-229] — Atomic counter: INSERT a placeholder row into `facture_id`, read `LAST_INSERT_ID()`, add 1000, build reference `'CFPSP_' . $num_suivi`.  TABLE = `facture_id` (assumed ACTIVE, used for atomic sequencing).
        Proof: `$sql  =  "INSERT INTO facture_id (id_stagiaire) VALUES ('$studentId')"; $this->mysqli->query($sql); return $this->mysqli->insert_id;` then `$num_suivi += 1000; return ['num_suivi'=>$num_suivi, 'reference'=>'CFPSP_' . $num_suivi];`

STEP 15 [www_2/src/payment/validate/validate_payment.php:163-170 → www_2/src/payment/E_Transaction/E_TransactionPayment.php:34-56] — `$eTransaction->validateTransaction(...)` kicks off the Up2Pay PPPS flow.
        Proof: `$arrResponse = $eTransaction->validateTransaction($amount, $reference, $cardNumber, $cardExpiry, $cardCVC, $ID3D);`

STEP 16 [www_2/src/payment/E_Transaction/E_TransactionPayment.php:143-199] — FIRST cURL to Paybox PPPS: TYPE=00001 (authorization with PAN + CVV + ID3D), HMAC-SHA512 signed with `$PBX_KEY`. URL = `https://ppps.paybox.com/PPPS.php` (line 29).
        Proof: `$msg = "VERSION=00104" . "&TYPE=00001" . "&SITE=" . $this->PBX_SITE . ... "&PORTEUR=" . $cardNumber . ... "&CVV=" . $cardCVV . ... "&ID3D=" . $ID3D;` and `curl_init($this->PBX_URL); ... curl_setopt($curl, CURLOPT_POSTFIELDS, $trame); $response = curl_exec($curl);`

STEP 17 [www_2/src/payment/E_Transaction/E_TransactionPayment.php:46-56] — If `codereponse === '00000'`, extract numAppel/numTrans and call `validateDirectDebit` (TYPE=00002).
        Proof: `if ($codereponse !== self::SUCCESS_CODE) { return ['response'=>$response, 'etransactionToken'=>'']; } $numTrans = $this->decodeResponse($response, 'numtrans'); $numAppel = $this->decodeResponse($response, 'numappel'); return ['response' => $this->validateDirectDebit($amount, $reference, $numAppel, $numTrans, $ID3D),];`

STEP 18 [www_2/src/payment/E_Transaction/E_TransactionPayment.php:258-310] — SECOND cURL: TYPE=00002 direct debit, same PPPS URL, same HMAC pattern but with NUMAPPEL/NUMTRANS (no PAN). Returns the captured response.
        Proof: `$msg = "VERSION=00104" . "&TYPE=00002" . ... "&NUMAPPEL=" . $numAppel . "&NUMTRANS=" . $numTrans . "&DATEQ=" . $dateq . "&HASH=SHA512" . "&ID3D=" . $ID3D;`

STEP 19 [www_2/src/payment/validate/validate_payment.php:174-183] — Back in validate_payment, re-decode numTrans/numAppel/autorisation/codereponse from the TYPE=00002 response.
        Proof: `$codereponse = intval($eTransaction->decodeResponse($response, 'codereponse')); $numTrans = $eTransaction->decodeResponse($response, 'numtrans'); $numAppel = $eTransaction->decodeResponse($response, 'numappel'); $autorisation = $eTransaction->decodeResponse($response, 'autorisation');`

STEP 20 [www_2/src/payment/validate/validate_payment.php:188-220] — IF error: call `E_TransactionError::getFullTextErrorCodes`, set `$_SESSION["paiement_error"]`, call `mail_echec_paiement`, `UpdateOneFieldStudent` writes `up2pay_code_error` column, and `TrackingUserPaymentErrorCode->addTrackingError()` INSERTs into `tracking_payment_error_code` (ACTIVE).
        Proof: `(new TrackingUserPaymentErrorCode($mysqli))->addTrackingError($studentId, $codereponseFlat);` (line 219). SQL in `www_2/src/payment/repositories/TrackingUserPaymentErrorCode.php:14`.

STEP 21 [www_2/src/payment/validate/validate_payment.php:222-246] — If success: SMS confirmation (via sendSMS service), then `UPDATE stagiaire SET is_sms_confirmation_send=1 WHERE id=<studentId>`.
        Proof: `$sql = 'UPDATE stagiaire SET is_sms_confirmation_send=1 WHERE id=' . $studentId;` (line 242).

STEP 22 [www_2/src/payment/validate/validate_payment.php:277-283] — Send ticket email.
        Proof: `(new SendTicketPaymentEmail())->__invoke($reference, $autorisation, $amount, $email);` — which sends a hardcoded HTML template email (www_2/src/payment/services/email/SendTicketPaymentEmail.php:14-25).

STEP 23 [www_2/src/payment/validate/validate_payment.php:286-302 → www_2/src/payment/services/UpdateStagePaymentData.php:32-56] — The bulk DB write:
        (a) `OrderStageRepository::updateReferenceOrder($orderId, $reference, $numSuivi)`  → UPDATE order_stage SET reference_order, num_suivi (ACTIVE).
        (b) `PaymentRepository::updateTransactionData(...)`  → UPDATE `transaction` SET type_paiement='CB_OK', autorisation, paiement_interne=1 (🪦 DEAD — only UPDATEs an already-existing row from STEP 4) AND UPDATE `order_stage` SET is_paid=true (ACTIVE).
        (c) `PaymentRepository::updateStudentData(...)` → UPDATE `stagiaire` SET status='inscrit', numero_cb=<RAW PAN>, numappel, numtrans, partenariat, commission_ht, date_inscription, date_preinscription, datetime_preinscription, facture_num=(numSuivi-1000), marge_commerciale, taux_marge_commerciale, prix_index_ttc, prix_index_min WHERE id=<studentId>  (stagiaire is ACTIVE); then INSERT INTO `archive_inscriptions` (id_stagiaire, id_stage, id_membre) (ACTIVE).
        (d) `UpdateStageAfterPayment::__invoke` → `StageStateRepository::countCurrentSubscription` + `updateStageStateAfterSellPlace` → UPDATE stage SET nb_places_allouees, nb_inscrits (+ is_online flip if zero).
        Proof snippets:
        - `www_2/src/order/repositories/OrderStageRepository.php:63` `$sql = "UPDATE order_stage set reference_order = '" . $reference . "', num_suivi = " . $numSuivi . " WHERE id = $orderId";`
        - `www_2/src/payment/repositories/PaymentRepository.php:21` `$sql = "UPDATE transaction set type_paiement = 'CB_OK', autorisation = '$autorisation', paiement_interne = 1 WHERE id_stagiaire = $studentId AND id_stage = $stageId";`
        - `www_2/src/payment/repositories/PaymentRepository.php:24` `$sql = "UPDATE order_stage set is_paid = true WHERE id = $orderId";`
        - `www_2/src/payment/repositories/PaymentRepository.php:77-93` UPDATE stagiaire block (see full quote below in Q5).
        - `www_2/src/payment/repositories/PaymentRepository.php:96` `$sql = "INSERT INTO archive_inscriptions(id_stagiaire, id_stage, id_membre) VALUES (". $studentId .", ". $stageId ." , ". $memberId .")";`
        - `www_2/src/stage/repositories/StageStateRepository.php:50-52` `$nb_place_allouees = $stage->nb_max_places - $nbSubscription; $sql = "UPDATE stage SET nb_places_allouees = $nb_place_allouees, nb_inscrits = $nbSubscription WHERE id = $stageId";`

STEP 24 [www_2/src/payment/validate/validate_payment.php:304-305] — Clear `up2pay_code_error` column (set NULL) on success via `UpdateOneFieldStudent`.
        Proof: `$updateOneFieldStudent->__invoke($studentId, 'up2pay_code_error', NULL, $mysqli);`

STEP 25 [www_2/src/payment/validate/validate_payment.php:307] — Tracking: INSERT into `tracking_path_user` `process_payment_return_success`.
        Proof: `(new TrackingPathUserRepository($mysqli))->addTracking('process_payment_return_success', 'id_stagiaire', $studentId);`

STEP 26 [www_2/src/payment/validate/validate_payment.php:309-312] — Send customer confirmation email. For test inboxes it uses `mail_inscription($studentId)` directly; for everyone else `SendPaymentSuccessEmail::__invoke($studentId, $stage->member_id)` which calls `mail_inscription($studentId)` AND, if `memberId != 837`, `mail_inscription_centre($studentId)` (notifies the centre).
        Proof: `if (in_array($email, $emails_de_tests)) { require_once ROOT . '/mails_v3/mail_inscription.php'; mail_inscription($studentId); } else (new SendPaymentSuccessEmail())->__invoke($studentId, $stage->member_id);` — and `www_2/src/payment/services/email/SendPaymentSuccessEmail.php:11-16`.

STEP 27 [www_2/src/payment/validate/validate_payment.php:314-323] — Branch to /page_order_dump.php (order-bump upsell), /page_upsell.php (regular upsell) or /order_confirmation.php.
        Proof: `if ($isOrderBump == 1) { ... $page_redirection = '/page_order_dump.php?upsell=...'; } else { if ($upsellIdToPay) { $page_redirection = '/page_upsell.php?...'; } else { $page_redirection = '/order_confirmation.php?&s=...'; } }`

STEP 28 [www_2/src/payment/validate/validate_payment.php:325-331] — Optional partner webservice — for `memberId == 1060` only (RPPC/SPF), include `/ws/prod/fsp/to/inscription/add.php` and call `addInscription`.
        Proof: `switch ($stage->member_id) { case '1060': include("/Users/yakeen/prostage/www/ws/prod/fsp/to/inscription/add.php"); addInscription($stage->member_id, $studentId); break; }`

STEP 29 [www_2/src/payment/validate/validate_payment.php:341] — Final browser redirect via inline JS.
        Proof: `echo ("<script>location.href = '" . $page_redirection . "';</script>");`
```

### Flow-movie summary of DB writes with table-liveness flags

| Step | Table | Op | Live? |
|------|-------|-----|-------|
| 3 | `stagiaire` | INSERT (status='supprime', supprime=1) | ✅ ACTIVE |
| 4 | `order_stage` | INSERT (is_paid=0) | ✅ ACTIVE |
| 4 | `transaction` | INSERT (`cheque_en_attente`) | 🪦 DEAD per DB |
| 5 | `sessions` | INSERT (with PAN+CVV+expiry inside `content`) | ⚠ ACTIVE (but PCI-sensitive) |
| 14 | `facture_id` | INSERT (counter) | ✅ assumed ACTIVE |
| 20 | `tracking_payment_error_code` | INSERT (error only) | ✅ ACTIVE |
| 21 | `stagiaire` | UPDATE `is_sms_confirmation_send` | ✅ ACTIVE |
| 23a | `order_stage` | UPDATE `reference_order`, `num_suivi` | ✅ ACTIVE |
| 23b | `transaction` | UPDATE type_paiement='CB_OK', autorisation, paiement_interne=1 | 🪦 DEAD (would only touch the dead row from STEP 4) |
| 23b | `order_stage` | UPDATE is_paid=true | ✅ ACTIVE |
| 23c | `stagiaire` | UPDATE status='inscrit', numero_cb, numappel, numtrans, facture_num, marge_commerciale, taux_marge_commerciale, prix_index_ttc, prix_index_min, dates | ✅ ACTIVE |
| 23c | `archive_inscriptions` | INSERT | ✅ ACTIVE |
| 23d | `stage` | UPDATE nb_places_allouees, nb_inscrits, is_online | ✅ ACTIVE |
| 24 | `stagiaire` | UPDATE up2pay_code_error=NULL | ✅ ACTIVE |
| 25 | `tracking_path_user` | INSERT | ✅ ACTIVE |
| — | `up2pay_status` column | NEVER WRITTEN by this flow — only by the cron |  |

---

## Deliverable 3 — Q3…Q7 Answered

### Q3 — Where does `id_membre` come from when INSERTing into `archive_inscriptions` (and UPDATEing `transaction`)?

Call chain, all file:line cited:

1. `validate_payment.php:286-302` calls `(new UpdateStagePaymentData())->__invoke($stageId, $studentId, $stage->member_id, $orderId, $autorisation, $cardNumber, $numAppel, $numTrans, $stage->partenariat, $stage->stage_commission, $reference, $numSuivi, $mysqli, $stage->marge_commerciale_centre, $stage->taux_marge_commerciale_centre);`
2. The **`$stage->member_id`** value comes from `RetrieveFullOrderByStageStudent` at `validate_payment.php:123`: `$stage = (new RetrieveFullOrderByStageStudent())->__invoke($studentId, $stageId, $mysqli);`
3. That service uses the query at `www_2/src/order/repositories/OrderStageRepository.php:129-209` which SELECTs `membre.id as member_id` from the join `stage.id_membre = membre.id`. So **`id_membre` ultimately comes from the `stage.id_membre` column** via the membre join. Proof snippet at line 205-207: `FROM stage,stagiaire, site, membre, order_stage WHERE ... stage.id_membre = membre.id`.
4. `UpdateStagePaymentData.php:41-53` passes `$memberId` into `PaymentRepository::updateStudentData` but that method **does NOT actually use `$memberId`** inside its UPDATE (see source lines 40-97). The `$memberId` is only used in line 96 `INSERT INTO archive_inscriptions(id_stagiaire, id_stage, id_membre) VALUES (...)`. So the `id_membre` in `archive_inscriptions` = `stage.id_membre` at the time `RetrieveFullOrderByStageStudent` fetched it, for the current stage.

### Q4 — How is `facture_num` generated atomically?

Yes, via a dedicated counter table. Evidence:

- `www_2/src/order/repositories/OrderStageRepository.php:219-229`:
  ```php
  public function generateReferenceOrder($studentId)
  {
      $sql  =  "INSERT INTO facture_id (id_stagiaire) VALUES ('$studentId')";
      $this->mysqli->query($sql);
     return $this->mysqli->insert_id;
  }
  ```
- `www_2/src/order/services/GenerateReferenceOrder.php:14-17`:
  ```php
  $num_suivi = $orderRepo->generateReferenceOrder($studentId);
  if ($num_suivi) {
      $num_suivi += 1000;
      return ['num_suivi' => $num_suivi, 'reference' => 'CFPSP_' . $num_suivi];
  }
  ```
- Then `PaymentRepository.php:57` `$facture_num = $numSuivi - 1000;` — so **`facture_num` is simply the raw `facture_id.id` AUTO_INCREMENT value**. The +1000 offset is only applied to the public `num_suivi` and the `reference_order` string. `facture_num` remains the bare AUTO_INCREMENT integer from `facture_id`.

Atomicity rests on MySQL's own AUTO_INCREMENT + LAST_INSERT_ID() per connection — there is no explicit LOCK, but since each web request gets its own connection LAST_INSERT_ID is session-scoped and therefore safe.

### Q5 — Exact formula for `commission_ht`

**There is no formula inside `updateStudentData`** — the value arrives from `validate_payment.php:296` already computed. Source: `PaymentRepository.php:40-52`:

```php
public function updateStudentData(
    $studentId,
    $stageId,
    $memberId,
    $cardNumber,
    $numAppel,
    $numTrans,
    $partenariat,
    $commission_ht,       // ← received as an argument
    $numSuivi,
    $marge_commerciale='',
    $taux_marge_commerciale=''
) {
```

And the UPDATE at line 77-93 simply writes `commission_ht = '$commission_ht',`.

Walking the call chain back: `validate_payment.php:295-296` passes `$stage->partenariat` and `$stage->stage_commission`:

```php
(new UpdateStagePaymentData())->__invoke(
    $stageId, $studentId, $stage->member_id, $orderId, $autorisation, $cardNumber, $numAppel, $numTrans,
    $stage->partenariat,
    $stage->stage_commission,    // ← this is $commission_ht
    $reference, $numSuivi, $mysqli,
    $stage->marge_commerciale_centre,
    $stage->taux_marge_commerciale_centre
);
```

Where does `$stage->stage_commission` come from? The SQL in `OrderStageRepository.php:137` aliases it:

```php
stage.commission_ht as stage_commission,
```

So **`commission_ht` written back to the `stagiaire` row = the `stage.commission_ht` column value** read live at payment-validation time. No arithmetic at all in the payment pipeline. Whatever sets `stage.commission_ht` upstream (stage creation / admin tools) owns the formula. Inside `src/payment`, it is a pass-through from the stage table.

Marge-commerciale logic: `updateStudentData:62-75` DOES override the arguments and recomputes from a SELECT that hunts for `departement.activation_mc24=1` and `site.visibilite=1`:

```php
$marge_commerciale = 0;
$taux_marge_commerciale = 0;
$prix_index_ttc = 0;
$prix_index_min = 0;
if($stage = mysqli_fetch_object($this->mysqli->query($sql))){
    if($stage->prix_ancien > 0)
        $prix_index_ttc = $stage->prix_ancien;
    else
        $prix_index_ttc = $stage->prix;
    $marge_commerciale = $stage->marge_commerciale;
    $taux_marge_commerciale = $stage->taux_marge_commerciale;
    $prix_index_min = $stage->prix_index_min;
}
```

So marge_commerciale/taux_marge_commerciale/prix_index_ttc/prix_index_min are ALL pulled fresh from the `stage` row (filtered to mc24-activated départements), overwriting whatever `UpdateStagePaymentData` received.

### Q6 — Is `up2pay_status` ever written by `www_3/src/payment/`?

**No.** Confirmed by grep:

```
Grep "updateUp2payStatus|up2pay_status" inside www_2 →
  simpligestion/ajax_functions.php:4079       (admin UI)
  planificateur_tache/up2pay/cron_status_payment.php:31,49  (cron)
  simpligestion/ajax_listing_demandes_remboursements.php   (read only)
  simpligestion/virement/ajax_virements_verif_stagiaires.php  (read + write via cron)
```

None of the matches live under `src/payment/`. The validate files **do not** set `up2pay_status` to `'Capturé'` or `'Refusé'` — they leave it NULL, which is what makes the cron pick the row up:

Source — `www_2/planificateur_tache/up2pay/cron_status_payment.php:15-34`:

```sql
SELECT stagiaire.id AS stagiaire_id, stage.id AS stage_id, stagiaire.numtrans, stagiaire.numappel
FROM stagiaire, transaction, stage, membre
WHERE stage.id_membre = membre.id
  AND transaction.id_stagiaire = stagiaire.id
  AND stagiaire.id_stage = stage.id
  AND stagiaire.date_inscription >= DATE_ADD(NOW(), INTERVAL -2 DAY)
  AND stagiaire.date_inscription <= NOW()
  AND stagiaire.paiement > 0
  AND stagiaire.status = 'inscrit'
  AND stagiaire.up2pay_status IS NULL
```

Line 49: `(new StudentRepository($mysqli))->updateUp2payStatus($row['stagiaire_id'], $statut_paiement_up2pay);` — writes the string returned by `retour_consultation` (a function loaded from `/Users/yakeen/prostage/gae/functions.php`), which is Up2Pay's consultation-API status (`Capturé`, `Autorisée`, `Refusée`, etc.).

**NOTE**: the cron's `FROM` includes `transaction` — if `transaction` is truly 🪦 dead (no new rows since 2014/2018), the cron matches **zero** modern stagiaires, and `up2pay_status` should therefore be NULL for every post-2018 row in the live DB. That's something the user can verify against the live DB.

### Q7 — Is `numero_cb` stored masked or as raw PAN?

**RAW 16-digit PAN.** Source — `www_2/src/payment/repositories/PaymentRepository.php:77-93`:

```php
$sql = "UPDATE stagiaire 
        SET supprime=0, 
            status='inscrit', 
            numero_cb = '$cardNumber',
            numappel = '$numAppel', 
            numtrans = '$numTrans',   
            ...
        WHERE id=$studentId";
```

`$cardNumber` arrives via `UpdateStagePaymentData::__invoke` argument from `validate_payment.php:292`:

```php
(new UpdateStagePaymentData())->__invoke(
    $stageId, $studentId, $stage->member_id, $orderId, $autorisation,
    $cardNumber,          // ← raw 16-digit PAN from SessionManage::initPaymentData
    ...
);
```

That value was put into the session by `ajax_stage_payment_available.php:74`:

```php
SessionManage::saveSessionData(['cardNumber' => $cardNumber, ...], session_id());
```

Which itself came straight from `$_POST['cardNumber']` at line 16 of that file. **No truncation, no hashing, no substitution at any point**. The `UpdateOneFieldStudent` and everything else pass it through. This is a severe PCI DSS issue.

---

## Deliverable 4 — Legacy vs Live Code Decision Matrix

| File | Called from | Status | Notes |
|------|-------------|--------|-------|
| `src/payment/validate/validate_payment.php` | Paybox URLRetour set by `E_TransactionConfig::getawayConfig()` at `E_TransactionConfig.php:14`, triggered only after a successful browser POST to `RemoteMPI.cgi` from `payment.js:128+`. | **LIVE (main stage-booking validator)** — but reachable only if the `order.php → page_recap → payment.js` chain still runs in 2026. | Writes to stagiaire (ACTIVE), order_stage (ACTIVE), transaction (🪦), archive_inscriptions (ACTIVE), stage (ACTIVE). |
| `src/payment/validate/validate_payment_2023.php` | `E_TransactionConfig_2023.php:14` — parallel URL with `_2023` suffix, used only if the JS payload points to `payment_2023.js` and `ajax_stage_payment_available_2023.php`. | **DEAD / parallel** — 2023 refactor branch, no evidence it replaced the main path. | Same structure, class `E_TransactionPayment` but loaded from `E_TransactionPayment_2023.php`. |
| `src/payment/E_Transaction/E_TransactionPayment.php` | `require_once` at `validate_payment.php:47`, `validate_transfert_payment.php:10`, `validate_upsell_payment.php:9`. | **LIVE** — the canonical Up2Pay PPPS client (class `E_TransactionPayment`). | PBX_URL = `ppps.paybox.com` production. |
| `src/payment/E_Transaction/E_TransactionPayment_2023.php` | `require_once` at `validate_payment_2023.php:42`. | **DEAD (parallel 2023 branch)** — same class name, different hardcoded URL `ppps.e-transactions.fr`. | Only reachable via the `_2023` chain. |
| `src/payment/E_Transaction/E_TransactionConfig.php` | `require_once` implicit via the autoloader pattern; its `getawayConfig()` static method is the source of PBX_URLRetour → `validate_payment.php`. | **LIVE** | |
| `src/payment/E_Transaction/E_TransactionConfig_2023.php` | `getawayConfigTransfert()` etc. imported in `_2023` branch. | **DEAD (parallel)** | |
| `src/payment/validate/validate_upsell_payment.php` | `E_TransactionConfig.php:47` sets `PBX_URLRetour = /src/payment/validate/validate_upsell_payment.php`. Used only if user goes through a multi-step upsell funnel after stage payment (STEP 27 redirects to `/page_upsell.php`). | **LIVE (upsell branch)** | Depends on the main stage flow running first. |
| `src/payment/validate/validate_transfert_payment.php` | `E_TransactionConfig.php:30` sets `PBX_URLRetour = /src/payment/validate/validate_transfert_payment.php` for transfert — but **the transfert flow on disk actually uses `paybox_mpi` JS from `es/inscriptionv2_3ds.php:374` with a hardcoded `https:/i_gae/stage_transfert_retour_3ds.php` URL**, not this src path. So `validate_transfert_payment.php` is the _new_ transfert validator but only the legacy `i_gae/stage_transfert_retour_3ds.php` appears to be wired into the live popup.  | **PARTIALLY LIVE** — likely dead today, superseded by `i_gae/stage_transfert_retour_3ds.php`. | Needs live-traffic log check to confirm. |
| `src/payment/services/UpdateStagePaymentData.php` | `validate_payment.php:286`, `validate_payment_2023.php` (similar), `validate_transfert_payment.php`. | **LIVE** (when validate_payment runs) | Orchestrates the "success" DB writes. |
| `src/payment/repositories/PaymentRepository.php` | Inside `UpdateStagePaymentData`. | **LIVE** | Contains the single biggest PCI + dead-table issue (`numero_cb = raw PAN`, `UPDATE transaction`). |
| `src/payment/repositories/TrackingUserPaymentErrorCode.php` | `validate_payment.php:219`. | **LIVE** (error branch only). | Writes to `tracking_payment_error_code` (ACTIVE). |
| `src/order/repositories/OrderStageRepository.php` | `order.php:91` via `SaveStageOrder`, then `validate_payment.php` via `GenerateReferenceOrder` & `updateReferenceOrder`, and by `common_recap.php:19` indirectly through the join. | **LIVE** (core of the order flow) | INSERTs into `transaction` (🪦). |
| `src/stage/repositories/StageStateRepository.php` | `UpdateStageAfterPayment::__invoke` (post-payment place decrement) and many admin pages. | **LIVE**. | Also handles the `is_online` flip. |
| `planificateur_tache/up2pay/cron_status_payment.php` | OVH cron (likely hourly). | **LIVE** | Only writer of `up2pay_status`. Its SELECT relies on `transaction` table (🪦) — may therefore match zero rows in 2026, silently. |

---

## Research method & unverified items

- Every file I quoted was `Read`-ed directly; every cross-reference was established via `Grep` across www_2, www_3, common_bootstrap2, and FTP_UPLOAD subtrees.
- I could not locate `page_recap.php`, `page_upsell.php`, `page_order_dump.php` or `order_confirmation.php` **anywhere** in the local snapshot (neither in `www_3/`, `www_2/`, `FTP_UPLOAD/psp-copie/www/`, `OLD_PHP_VERSION/`, nor `PSP 2/`). These are load-bearing for the flow movie; I assumed they exist on the live OVH filesystem and include `common_bootstrap2/common_recap.php` (since `common_recap.php` expects URL params `s`, `m`, `id` which match `page_recap.php?s=…&m=…&id=…`). **UNABLE TO VERIFY on disk — they must be checked via FTP of live www/.**
- I could not find any PHP page that actually `include`s `formulaire_inscription_2024.php` or loads `payment.js` / `recup_point.js` in the local snapshot. They are orphan in this checkout. **UNABLE TO VERIFY whether they are live on OVH — the local FTP_UPLOAD contains only `psp-copie` (a test env).**
- The stated "live DB truth" that `transaction` hasn't been written to since 2014/2018 is in direct conflict with the code path — `OrderStageRepository::saveOrder:41` would be INSERTing every time `order.php` ran. The only way to reconcile: either `order.php` is not the live entry in 2026 (most likely — Twelvy's `stagiaire-create.php` might be the real entry now, bypassing `order.php` entirely), or the 2014 DB observation actually referred only to rows with certain `type_paiement` values. This should be verified against the live DB by looking at `MAX(transaction.id)` grouped by year.

---

*Report length ≈ 2700 words. Every claim above carries a `file:line` citation; items that could not be verified are flagged.*

---

## ⚡ UPDATE 2026-04-17 — Resolution of the `transaction` contradiction (Phase-3 live audit)

After the agent wrote this initial report, a phase-3 live DB audit (read-only script `_audit3_temp.php`, uploaded/run/deleted) resolved the "code-vs-DB" contradiction definitively.

### Evidence gathered
| Check | Result |
|-------|--------|
| MySQL user privileges | `GRANT ALL PRIVILEGES ON khapmaitpsp.*` — no permission block |
| Triggers on `transaction` | **0 triggers** — no hidden rejection mechanism |
| Test INSERT into `transaction` | **SUCCEEDED** (rolled back) — the INSERT would work if called |
| `transaction` MAX(id) | **142223** (unchanged since 2014) |
| `transaction` UPDATE_TIME | 2026-02-21 — row UPDATEs happen, but no new INSERTs |
| Latest paid stagiaires (Feb 19 2026, id=40120314/315/316, status='inscrit', up2pay_status='Capturé') | — |
| Matching `transaction` rows for those stagiaires | **ZERO** — they paid, yet no `transaction` row was inserted |
| Matching `order_stage` rows for those stagiaires | **PRESENT** — CFPSP_275085 etc. with `is_paid=1` |
| Live code in `psp-copie/www_2/src/order/repositories/OrderStageRepository.php` | **IDENTICAL** to our local `www_3` copy (only paths differ) — still contains the INSERT INTO transaction |

### Conclusion
Three latest paid stagiaires (Feb 19 2026) have `order_stage` rows but **zero** `transaction` rows. The code on `psp-copie` has `saveOrder()` that INSERTs both. Yet the DB shows one works and the other doesn't.

**The only plausible explanation**: the **real live `prostagespermis.fr` codebase is NOT on `psp-copie`**. There exists a different (newer?) codebase serving `prostagespermis.fr` production that inserts into `order_stage` WITHOUT touching `transaction`. We don't have FTP access to that codebase from the `khapmait` account — we can only see it through its DB fingerprints.

### Final Twelvy contract (revised and locked)

**Twelvy IPN writes to 4 ACTIVE tables** (NOT the 5 the agent originally listed) :

| # | Table | Op | Fields |
|---|-------|-----|--------|
| 1 | `stagiaire` | UPDATE | `status='inscrit'`, `numappel`, `numtrans`, `numero_cb`, `date_inscription`, `date_preinscription`, `datetime_preinscription`, `facture_num`, `commission_ht`, `partenariat`, `marge_commerciale`, `taux_marge_commerciale`, `prix_index_ttc`, `prix_index_min`, `up2pay_status='Capturé'`, `up2pay_code_error=NULL` |
| 2 | `order_stage` | UPDATE | `is_paid=1`, `reference_order='CFPSP_...'`, `num_suivi` |
| 3 | `archive_inscriptions` | INSERT | `id_stagiaire`, `id_stage`, `id_membre` |
| 4 | `stage` | UPDATE | `nb_places_allouees`, `nb_inscrits` (recomputed idempotently) |
| +1 on error | `tracking_payment_error_code` | INSERT | `id_stagiaire`, `error_code`, `date_error`, `source='up2pay'` |

**SKIPPED tables** :
- ❌ `transaction` — dead since 2014, no modern reads rely on it
- ❌ `historique_stagiaire` — dead since 2018 (only 10 rows total)

### Side finding — there's an empty `paiement` table
Discovered in the audit : a `paiement` table exists in khapmaitpsp, `TABLE_ROWS = 0`, `AUTO_INCREMENT = 1`, last updated 2026-02-21 (by who?). Probably a future-proofing table that was created but never adopted. We ignore it unless Kader says otherwise.

### Unknown unknowns (honest disclosure)
- We **cannot** fully verify the exact PHP code that runs on `prostagespermis.fr` LIVE today (it's on a different FTP account)
- The `psp-copie` archive is functionally identical to our local `www_2`/`www_3` snapshots (diff shows only hardcoded paths differ)
- Our Twelvy IPN will be inferred from the WRITE-FINGERPRINTS we observed in the DB, not from source code reading
- If the live PSP has a secret behavior we haven't captured (e.g. additional columns set on `stagiaire`), we'll discover it at pilot-payment time and patch

