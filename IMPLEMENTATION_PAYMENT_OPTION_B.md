# Implementation — Option B Payment (PSP redirect) — LIVING DOC

**Started:** 13 Jun 2026. **Owner:** Yakeen. **Status:** IN PROGRESS.
This is the single source of truth for the payment-funnel rebuild. Updated continuously so work can resume after any interruption. If you're resuming, read this top-to-bottom first.

---

## 0. THE GOAL (what we are building)

Replace the abandoned Up2Pay **iframe** with a **redirect to a separate payment page**, reusing PSP's own custom card form + banking code.

Customer funnel:
1. **Twelvy details form** (civilité, nom, prénom, email, téléphone, CGV, Garantie Sérénité) — EXISTS, kept as-is.
2. On **validate** → create the prospect (existing `bridge.php create_or_update_prospect`) → **full-page redirect** to a payment page on `psp-copie.twelvy.net`.
3. That page (`twelvy_payment.php`, NEW) creates the order via PSP's own `SaveStageOrder`, renders **PSP's own card form** (restyled to Twelvy), and runs PSP's unchanged 3DS + PPPS + DB-writes + emails.
4. → redirect back to **twelvy.net/paiement/confirmation** which polls payment status.

**Two distinct forms:** (#1) details form = Twelvy, kept. (#2) card-entry form = PSP's, on the separate page. The iframe currently IS the card surface; we remove it.

**Mode:** everything runs in TEST/sandbox now (PSP `DEBUG=true`, fake cards). Go-live = flip DEBUG→false + switch DB creds khapmaitpsp→prostagepsp + scoped re-enable. Payment system is NOT rebuilt at cutover, just "promoted."

---

## 1. SECURITY DESIGN (non-negotiable — user's #1 priority)

| Concern | Weak default (rejected) | Our secure design |
|---|---|---|
| Handoff Twelvy→payment page | `md5(id + '!psp#13')` — public salt, forgeable, enumerable | **HMAC-SHA256(`s|exp`, BRIDGE_SECRET_TOKEN)** + short TTL (~15 min). Secret already shared (Vercel `BRIDGE_API_KEY` == OVH `BRIDGE_SECRET_TOKEN` = `c6759c1f…fc55`). `twelvy_payment.php` recomputes + verifies + checks expiry with `hash_equals`. No enumeration. |
| Confirmation status polling (`get_stagiaire_status`) | open by integer id → IDOR/PII leak of 50k stagiaires | Status proxy route requires the **same signed token**; bridge verifies before returning; PII minimised in payload. |
| psp-copie Basic Auth | "remove it entirely" (exposes real customer PII + autologin) | **SCOPED**: keep Basic Auth on `/es/`,`/ep/`,`autologin.php`; allow public ONLY to `twelvy_payment.php` + the specific `/src/` assets it needs. |
| Secrets | n/a | HMAC keys + DB pwd + token stay in `config_secrets.php` (gitignored) on OVH and Vercel env only. Never in client JS, never committed. |
| PAN handling | n/a | Card typed on PSP's form → PSP's existing flow. We do NOT add new card storage. (PSP's legacy plaintext-PAN-in-sessions is a pre-existing PSP issue, out of scope to fix now, flagged.) |
| Bridge transport | n/a | Vercel→OVH already X-Api-Key (sha256+hash_equals), CORS pinned to www.twelvy.net. Reused. |

---

## 2. VERIFIED GROUND TRUTH (13 Jun, live checks)

- ✅ **FTP works** from dev env: `ftp.cluster115.hosting.ovh.net` / khapmait. Full account access.
  - api.twelvy.net root = `/www/api/` (bridge.php, ipn.php, retour.php, config_paiement.php, config_secrets.php, **check-stagiaire.php** [GDPR leak — still there], stages.php, etc.)
  - psp-copie root = `/psp-copie/` (payment code under `/psp-copie/www/` + `/psp-copie/www_2/`)
- ✅ `bridge.php?action=ping` without key → **HTTP 403** (correct: X-Api-Key enforced).
- ✅ psp-copie `/` → **HTTP 401** (Basic Auth active).
- ⚠️ `/src/payment/js/payment.js` (with basic auth) → **HTTP 403** — the `Options +SymLinksIfOwnerMatch` fix is NOT yet applied; /src/ assets unreachable over HTTP. MUST fix (scoped) or copy assets to /www/.
- ✅ **PHP 5.6.40** on OVH; **`mysql_*` extension AVAILABLE** (`function_exists('mysql_connect')`=true) → SessionManage (legacy mysql_*) WILL work. ✅
- ✅ **`sessions` table EXISTS** on khapmaitpsp: `(id int, session_id varchar(255), content text)` latin1. (id not auto-inc but harmless — keyed by session_id.) ✅
- ⚠️ **`sql_mode = NO_ENGINE_SUBSTITUTION`** — NON-strict. Zero-dates ('0000-00-00'), empty-string casts, and missing-no-default inserts all silently succeed. Explains why bridge's `'0000-00-00'` inserts work — but also why the id=0 bug below is silent.

### 🔴 2a. CRITICAL DB FINDING (live, 13 Jun) — staging DB lost AUTO_INCREMENT + PRIMARY KEY on the payment tables
The 21 Feb chunked import stripped AUTO_INCREMENT + PRIMARY KEY from almost every payment table on **khapmaitpsp** (only `stagiaire` survived intact). Live `SHOW COLUMNS`/`SHOW KEYS`:

| table | id auto_inc | id PK | rows | zero-id rows | max id | verdict |
|---|---|---|---|---|---|---|
| `stagiaire` | ✅ yes | ✅ yes | — | — | 40120322+ | OK (prospect creation works) |
| `order_stage` | ❌ NO | ❌ NO | 138,959 | **23** | 138,937 | 🔴 every new row gets id=0 → collisions |
| `facture_id` | ❌ NO | ❌ NO | 274,107 | **23** | 274,086 | 🔴 lastInsertId()=0 → **num_suivi=1000 bug CONFIRMED** |
| `transaction` | ❌ NO | ❌ NO | — | — | — | new rows id=0 (harmless: keyed by id_stagiaire+id_stage) |
| `archive_inscriptions` | ❌ NO | ❌ NO | — | — | — | new rows id=0 (harmless: never read by id) |
| `stage` | ❌ NO | ❌ NO | — | — | — | ⚠️ perf: UPDATE WHERE id=X is a full scan; we never INSERT stage |

**ROOT CAUSE of num_suivi=1000 is now PROVEN:** `facture_id.id` is `bigint NOT NULL` with no auto-increment, non-strict sql_mode → `INSERT INTO facture_id(id_stagiaire)` sets id=0 → `lastInsertId()=0` → `num_suivi = 0+1000 = 1000` every time. The 23 zero-id rows in BOTH facture_id and order_stage are the test garbage from the iframe-era bridge `prepare_payment` runs (reference CFPSP_1000). 

**REQUIRED DB FIX (Task 7, staging khapmaitpsp) — exact SQL, run after confirming the 23 zero rows are test garbage:**
```sql
-- facture_id: drop 23 test-garbage zero rows, restore counter
DELETE FROM `facture_id` WHERE id = 0;                 -- removes 23 rows (verify first)
ALTER TABLE `facture_id` MODIFY `id` bigint NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`), AUTO_INCREMENT = 274087;
-- order_stage: same
DELETE FROM `order_stage` WHERE id = 0;                -- removes 23 rows (verify first — these are CFPSP_1000 test orders)
ALTER TABLE `order_stage` MODIFY `id` int NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`), AUTO_INCREMENT = 138938;
```
(`transaction`/`archive_inscriptions` id=0 is harmless for the flow; `stage` PK is a perf follow-up. On PROD prostagepsp these PKs presumably still exist — re-verify at cutover, do NOT blindly run these on prod.)
**Until this fix is applied, NO Option-B payment can complete (duplicate references → ipn/validate matches wrong booking).**

---

## 3. TASK TRACKER

Legend: ☐ todo · ◐ in progress · ☑ done

- ☑ **V. Live read-only DB/server verify** — DONE (see §2a). FTP works; mysql_* present; sessions exists; facture_id+order_stage lost AUTO_INCREMENT.
- ☑ **0. Living plan doc** (this file)
- ☑ **1. Secure handoff token** — `lib/paymentToken.ts` (HMAC-SHA256 over `s|exp` with BRIDGE_SECRET_TOKEN, 15-min TTL; `confToken` for status gating). Signed `redirect_url` FOLDED INTO `app/api/payment/create-prospect/route.ts` response (no standalone arbitrary-id signer). _Built._
- ☑ **2. Twelvy `/api/payment/status`** proxy (`app/api/payment/status/route.ts`) — requires valid conf token else 403; proxies get_stagiaire_status; no-store. _Built._
- ☑ **3. Twelvy `/paiement/confirmation`** page (`app/paiement/confirmation/page.tsx` + `app/paiement/layout.tsx` noindex) — reads id+status+t, polls status (2.5s, 90s budget), renders paye/refuse/pending_timeout/error. _Built (pending typecheck)._
- ☑ **4. Modify inscription page** — DONE. `prepareAndShowPayment` now: create-prospect (with `stage.id` not stale URL param) → `window.location.href = redirect_url`. Both `<Up2PayIframe>` renders removed; import reduced to `{ type PaymentData }`; `isRedirectingRef` double-submit guard added; spinner copy → "Redirection vers le paiement sécurisé…". Spinner stays up during nav (button effectively locked). **tsc --noEmit = 0 errors** across all new+changed files. (Dead `setPaymentData`/`paymentData` state left harmless — cleanup Task 10. Trigger-button `disabled={isPreparingPayment}` is optional polish, not added — ref guard suffices.)
- ☑ **5. PHP `twelvy_payment.php`** — WRITTEN at `php/psp-copie/twelvy_payment.php` (repo source; deploys to `/psp-copie/www/twelvy_payment.php`). PHP 5.6. Verifies HMAC `s|exp` token + expiry (hash_equals); overrides HOST/DEBUG via define() before params.php (no file edit needed); loads config(`$mysqli`)+params+SaveStageOrder+RetrieveFullOrderByStageStudent+E_TransactionConfig (NOT common_bootstrap_new → dodges the 3-byte crash); already-paid guard → redirects to confirmation; idempotent order creation (only SaveStageOrder if no existing order); sets session vars; emits ALL 24 payment.js globals incl. 3DSv2 + `var tracking = new TrackingPathUser()` + jQuery + swal + card.js + payment.js; Twelvy-restyled card form (DOM ids cardNumber/month_expiration/year_expiration/cardCVC/rowError/loading-overlay). Secret in `/psp-copie/twelvy_secret.php` (template `php/psp-copie/twelvy_secret.example.php`). **urlRetour overridden to `/twelvy_validate.php`** (Basic-Auth-exempt /www/ copy) so the 3DS return dodges the /src/ 403.

### 5a. ⚠️ DEPLOYMENT-PHASE RISKS / DECISIONS (resolve during live deploy)
- **/src/ 403**: payment.js loads from `/src/payment/js/` AND its AJAX (checkStageAvailable/checkStagePrice) fetches `HOST + /src/payment/ajax/*`. So `/src/` MUST be HTTP-reachable. Plan: add `Options +SymLinksIfOwnerMatch` to `/psp-copie/www/.htaccess`. Verify with `curl` it returns 200 not 500 (AllowOverride may forbid Options on OVH — fallback: copy the 3 JS + 2 ajax files to a /www/ public folder + patch payment.js fetch URLs).
- **Basic Auth scoping (SECURITY — do NOT remove wholesale)**: exempt ONLY the payment paths from Basic Auth via `<FilesMatch>`/`Satisfy Any/Allow from all` for `twelvy_payment.php`, `twelvy_validate.php`, and a `.htaccess` in `www_2/src/payment/` (`Satisfy Any`) so `/src/payment/*` is public; keep Basic Auth on `/es/`,`/ep/`,`autologin.php`, everything else.
- **`tracking_path_user.js`** hardcodes `baseUrl='https://www.prostagespermis.fr/src/payment/ajax/tracking_path_user_api.php'` and payment.js does `await tracking.addTracking(...)` right before form.submit() — a cross-origin/404 reject would THROW and block submit. → must serve a patched tracking_path_user.js whose baseUrl points to a reachable psp-copie endpoint, OR a stub `tracking.addTracking` that returns a resolved promise. **Verify in E2E.**
- **params.php edit**: alternatively edit `/psp-copie/www/params.php` once on server: `HOST=https://psp-copie.twelvy.net`, `DEBUG=true` — needed because the ajax + validate copies also `require params.php` and would otherwise see DEBUG=false/HOST=localhost. (twelvy_payment.php pre-defines them, but the OTHER PSP files in the chain don't.) → **Edit params.php on server: C1 HOST + C4 DEBUG.** Reversible at cutover.
- **E_TransactionPayment SITE typo** 1999887→1999888 (C2) + **TEST HMAC/PPPS key is a placeholder** → sandbox PPPS may reject until real Verifone test creds set. Flagged for E2E.
- **DEBUG test endpoint** = `preprod-tpeweb.e-transactions.fr/cgi/RemoteMPI.cgi` merchant 222 (from E_TransactionConfig).

- ☐ **5b. `twelvy_validate.php`** (patched copy of `validate_payment.php` → /www/) — redirect success/refuse/error to `https://www.twelvy.net/paiement/confirmation?id=<id>&status=ok|refuse&t=<HMAC(id|conf)>`; neutralise FSP webhook + SMS while DEBUG; require twelvy_secret.php for the conf token. NOT yet written.
- ☐ **6. PSP-copie patches** — scoped .htaccess (+SymLinksIfOwnerMatch, public allow ONLY twelvy_payment.php + needed /src/ assets, keep Basic Auth elsewhere); params.php HOST + DEBUG=true; E_TransactionPayment SITE typo 1999887→1999888; validate_payment.php redirects→twelvy.net/paiement/confirmation + neutralise FSP/SMS in test.
- ☐ **7. facture_id fix** (if live check shows no AUTO_INCREMENT) — ALTER TABLE on khapmaitpsp; re-verify on prod at cutover.
- ☐ **8. Adversarial security review** of all new code.
- ☐ **9. E2E sandbox test** (test card 4000 0000 0000 1000 frictionless / 4000 0000 0000 1091 challenge).
- ☐ **10. Cleanup orphans** (after E2E green): Up2PayIframe.tsx, /api/payment/prepare, bridge prepare_payment, ipn.php, retour.php, pubkey; delete check-stagiaire.php + stagiaire-create.php + inscription.php + /api/stagiaire/create + /api/test-booking from server.

---

## 4. KEY FACTS / CREDS (for resume)
- Shared HMAC secret (token signing): BRIDGE_SECRET_TOKEN = `c6759c1f4f2f51d24d601eb85c575177f3d411c82e4f5e175d4816975d63fc55` (OVH config_secrets.php == Vercel BRIDGE_API_KEY).
- FTP: ftp.cluster115.hosting.ovh.net / khapmait / 08Bremlicastora6346. api=/www/api/, psp-copie=/psp-copie/.
- DB (staging): khapmaitpsp.mysql.db / khapmaitpsp / Lretouiva1226 (only reachable from inside OVH → verify via uploaded temp scripts).
- psp-copie Basic Auth: psp / copie2025.
- PSP order creator: `www_2/src/order/services/SaveStageOrder.php` (bug: line ~19-20 doesn't set orderId on return — add `$order->updateOrderId($orderId);`). Creates order_stage + transaction('cheque_en_attente').
- payment.js needs globals: stageId, studentId, amount, url, MerchandId, IdSession, urlRetour, urlRedirect, URL_FERERENCE, email, session_id, HOST, + 3DSv2: Address1, City, CountryCode(250), EmailPorteur, FirstName, LastName, TotalQuantity(1), ZipCode, NumTelephone, + `var tracking = new TrackingPathUser()`, + jQuery + SweetAlert. DOM ids: cardNumber, month_expiration, year_expiration, cardCVC, rowError, loading-overlay.
- Test cards: 4000 0000 0000 1000 (frictionless), 4000 0000 0000 1091 (3DS challenge), exp 01/27, CVV 123.

## 4a. REAL DEPLOYED PSP-COPIE FACTS (downloaded via FTP 13 Jun — authoritative, supersedes local _legacy copies)
- Web root `/psp-copie/www/`; has symlinks `src`→www_2/src, `v2`, `connections`, `params.php`. psp-copie account hosts many other OVH sites too (architecte, bois, telepointspermis…) — leave those alone.
- **`/psp-copie/connections/config.php`** (7 lines): `include stageconnect0.php; $mysqli = new mysqli(...); $mysqli->set_charset('utf8');` → exposes **`$mysqli`** (mysqli, utf8). This is the connection twelvy_payment.php uses.
- **`/psp-copie/www/params.php`**: defines `APP='/home/khapmait/psp-copie/www_2/src/'`, `ROOT='/home/khapmait/psp-copie/www/'`, **`DEBUG=false`** (🔴 must set true for sandbox), **`HOST='http://localhost:8081'`** (🔴 C1 must set to `https://psp-copie.twelvy.net`), HOST_FILE_CENTER. All guarded by `if(!defined(...))` → can be pre-defined by twelvy_payment.php BEFORE requiring params.php (clean override, no file edit needed for HOST/DEBUG!).
- **`SaveStageOrder->__invoke($studentId, $amount, $stageId, $memberId, $mysqli)`** requires `APP.'order/repositories/OrderStageRepository.php'` + `APP.'order/domain/OrderStage.php'`. Creates OrderStage, calls saveOrder, returns `$order` (⚠️ BUG: never sets orderId on it — but we don't rely on the return; the row is created).
- **`OrderStageRepository::saveOrder($order,$memberId)`**: `INSERT INTO order_stage(user_id,amount,is_paid,stage_id,created) VALUES(...,0,...,now)` then `$orderId=$mysqli->insert_id`; then `INSERT INTO transaction(id_stage,id_stagiaire,id_membre,type_paiement='cheque_en_attente',date_transaction)`. 🔴 **On staging, order_stage.id has NO auto_increment → insert_id=0 → saveOrder returns null AND every order_stage row gets id=0 → validate_payment's `updateReferenceOrder ... WHERE id=0` corrupts all zero rows. CONFIRMS the DB fix (Task 8) is a HARD blocker.**
- KEY INSIGHT: HOST + DEBUG can be overridden by `define()` in twelvy_payment.php BEFORE `require params.php` (guards are `if(!defined)`) → fewer live file edits. Only `validate_payment.php` (3DS return) genuinely needs editing for the Twelvy redirect + conf-token.

## 6. DEPLOYMENT RUNBOOK + LIVE LOG (rollback-first)

**HMAC verified:** Node `createHmac('sha256',secret)` === PHP `hash_hmac('sha256',...,secret)` → IDENTICAL. Token scheme correct. ✅
**All new code authored + lint/tsc clean:** Twelvy (paymentToken.ts, create-prospect, status, /paiement/confirmation, inscription page) + PHP (`php/psp-copie/twelvy_payment.php`, `twelvy_validate.php`, `twelvy_secret.example.php`).

### Deploy order (each step: BACKUP → mutate → curl-verify → log below)
1. **DB ALTER** (staging khapmaitpsp) — verify 23 zero rows are CFPSP_1000 test garbage → back up table defs → `DELETE WHERE id=0` + restore AUTO_INCREMENT+PK on `facture_id` (AUTO_INCREMENT=274087) and `order_stage` (=138938). Reverse: drop the PK/auto_inc (zero-row delete not reversible — but they're garbage).
2. **Upload** `/psp-copie/twelvy_secret.php` (filled, NOT web-served — outside /www), `/psp-copie/www/twelvy_payment.php`, `/psp-copie/www/twelvy_validate.php`. Rollback: delete.
3. **BACKUP + edit** `/psp-copie/www/params.php`: `DEBUG=true` (🔴 sandbox-bank safety — without this twelvy_validate hits the REAL bank), `HOST=https://psp-copie.twelvy.net`. Backup to `php/_backups/params.php.<ts>`.
4. **BACKUP + edit** `/psp-copie/www_2/src/payment/E_Transaction/E_TransactionPayment.php`: SITE typo 1999887→1999888 (C2). Backup first.
5. **BACKUP + edit** `/psp-copie/www/.htaccess`: add `Options +SymLinksIfOwnerMatch` + scoped `<FilesMatch>` Satisfy Any for twelvy_payment.php/twelvy_validate.php; add `.htaccess` in `www_2/src/payment/` (Satisfy Any) to expose /src/payment/* publicly. KEEP Basic Auth on /es/,/ep/,autologin. curl-verify: /src/payment/js/payment.js → 200 (not 403/500), site root still 401.
6. **Smoke test**: build a valid token (Node), GET twelvy_payment.php?s&exp&sig → renders form (200); assets load; /twelvy_validate.php reachable.
7. **Frontend**: confirm Vercel has BRIDGE_API_KEY (test live create-prospect) + add PSP_COPIE_URL (defaults ok). Commit Twelvy work to branch `payment-option-b`; push for preview; then main when E2E green.
8. **E2E**: test card 4000 0000 0000 1000 (use test email ismaelkhapeo@gmail.com so no centre email fires).

### 🔴 LIVE DEPLOYMENT LOG (append each action with timestamp + undo)
- **13 Jun — PREP (read-only, no mutation yet):**
  - Confirmed the 23 zero-id rows in `order_stage` + `facture_id` are test garbage: all `reference_order='CFPSP_1000'`, `num_suivi=1000`, `is_paid=0`, **0 paid**, stagiaires 40120322–40120332. DELETE is safe.
  - Backed up to `php/_backups/`: `psp-copie.www.htaccess.ORIG-<ts>` (148 B: Basic Auth + Options -Indexes), `psp-copie.params.php.ORIG-<ts>`, `E_TransactionPayment.php.ORIG-<ts>`.
  - SITE typo at `E_TransactionPayment.php:24` `$this->PBX_SITE = '1999887';` (DEBUG branch) → change to `'1999888'`. Prod (line 30) `'0966892'` stays.
  - `.htaccess` planned edit: `Options -Indexes +SymLinksIfOwnerMatch` + `<FilesMatch "^(twelvy_payment|twelvy_validate)\.php$"> Satisfy Any / Allow from all` (keep global Basic Auth); + new `.htaccess` in `www_2/src/payment/` with `Satisfy Any / Allow from all` to expose `/src/payment/*`. **curl-test after: /src/payment/js/payment.js=200, site root=401, nothing=500.** (If Satisfy/Allow 500s on Apache 2.4, switch to `Require all granted`.)
  - **Security review DONE (wf_85863afa-b25). Verdict: sandbox-safe AFTER fixes. Fixing the cheap high/medium now:**
    - 🔴 CRITICAL (PHP): `twelvy_validate.php` had NO DEBUG/HOST define → would use params.php DEBUG=false → REAL BANK. FIX: shared `twelvy_env.php` required FIRST by both PHP files (single source of truth, flip to false at cutover).
    - HIGH: open-redirect passthrough in create-prospect → build FRESH response (never forward upstream redirect_url) + client-side origin check before window.location.
    - HIGH: single secret signs 3 trust domains → derive purpose-separated subkeys `HMAC('twelvy-handoff-v1',secret)` and `HMAC('twelvy-conf-v1',secret)`; X-Api-Key stays the raw key.
    - MED: conf-token had no expiry → add `exp` (2h TTL), carried as `&t=<sig>&te=<exp>`; status route verifies exp.
    - MED: fake `?status=ok` success screen → confirmation page NEVER renders success from the unsigned hint; only a verified status poll yields 'paye'.
    - MED: status route returned raw bridge JSON incl. email/nom → project an allowlist (status, prenom, facture_num, stage subset); Referrer-Policy no-referrer.
    - MED (PHP): already-paid guard used `!== ''` (NULL-unsafe) → use empty()+supprime=0 (added supprime to SELECT).
    - HIGH (PHP): cleartext PAN in logs/sessions → mask `$cardNumber` (first6+last4) right AFTER the charge, before any log/UpdateStagePaymentData.
    - LOW (PHP): json_encode into &lt;script&gt; → add JSON_HEX_* flags.
    - DEFERRED to pre-PROD hardening (noted, not blocking sandbox/fake cards): per-IP rate limit on /api/payment/status, move conf-token out of query string into a cookie, validate the d2305 session-id regex + confirm server SessionManage is parameterised, charset-whitelist nom/prenom at prospect creation.
- **13 Jun — ✅ STEPS 2-5 DEPLOYED + SMOKE-TESTED (sandbox):**
  - Uploaded: `/psp-copie/twelvy_secret.php` + `twelvy_env.php` (outside web root), `/psp-copie/www/twelvy_payment.php` + `twelvy_validate.php`. params.php → DEBUG=true + HOST=psp-copie. E_TransactionPayment SITE typo → 1999888.
  - **/src/ stayed 403 even with +SymLinksIfOwnerMatch (OVH won't follow the symlink) → pivoted to ASSET-COPY:** copied patched `payment.js` (AJAX URLs → /twelvy_pay_available.php + /twelvy_pay_price.php) + `card.js` to `/www/twelvy_assets/` (+ its own `.htaccess` Satisfy Any); copied `ajax_stage_payment_available.php`→`/www/twelvy_pay_available.php` and `ajax_stage_check_price.php`→`/www/twelvy_pay_price.php`; `/www/.htaccess` FilesMatch exempts twelvy_payment/twelvy_validate/twelvy_pay_available/twelvy_pay_price.
  - **Fixed a real bug:** twelvy_payment.php used `mysqli_stmt::get_result()` → fatal (OVH PHP 5.6 has no mysqlnd). Rewrote as direct `$mysqli->query()` with the validated-int id (injection-safe).
  - ✅ **SMOKE TESTS ALL GREEN:** site root still 401 (espaces protected); /twelvy_assets/payment.js + card.js = 200; twelvy_payment.php with a valid token renders the card form (200), `url`=preprod test endpoint, MerchandId=222, urlRetour→twelvy_validate.php, DEBUG=true confirmed; twelvy_pay_price.php = `{changed:false, 189==189}`; **full happy path: fresh prospect 40120333 on current stage 326300 → twelvy_payment.php (order created via SaveStageOrder) → twelvy_pay_available.php = `{isAvailable:true}`.**
  - ⏭️ REMAINING: (a) frontend deploy (commit + push) so twelvy.net inscription page redirects here; (b) the actual card→RemoteMPI 3DS→twelvy_validate→PPPS capture→confirmation = a BROWSER test (test card 4000 0000 0000 1000, exp 01/27, cvc 123). PPPS sandbox auth may need the exact Verifone recette creds if it rejects (open item).
  - Undo for psp-copie: restore `php/_backups/*.ORIG-*` for .htaccess/params/E_TransactionPayment; delete twelvy_*.php + /www/twelvy_assets/ + twelvy_pay_*.php + /psp-copie/twelvy_{secret,env}.php.
- **13 Jun — ✅ STEP 1 DB ALTER DONE (live mutation on khapmaitpsp staging):** deleted 23 zero-id rows from `facture_id` + `order_stage` (all CFPSP_1000, is_paid=0), restored `AUTO_INCREMENT`+`PRIMARY KEY` (facture_id AUTO_INCREMENT=274087, order_stage=138938). Verify test-insert returned id=274087 → **auto_increment WORKS → num_suivi=1000 bug FIXED.** Undo: `ALTER TABLE facture_id DROP PRIMARY KEY, MODIFY id bigint NOT NULL;` (+ order_stage int) — the deleted 23 garbage rows are NOT restored (intentional). Re-verify/apply same on PROD prostagepsp at cutover (prod likely already has it).

## 5. RESUME INSTRUCTIONS
Read this doc. Check the TASK TRACKER for ◐/☐. The Twelvy-side tasks (1-4) are pure git, reversible, no live-server risk — safe to do anytime. The PHP/server tasks (5-7) need Task V done first. NEVER remove Basic Auth wholesale. NEVER commit config_secrets. Card data never touches Twelvy.
