# RESUME SESSION — 14 June 2026

**Focus:** Completeness audit of the payment funnel against the **original cahier des charges** + `ARCHITECTURE_CIBLE.md` + `errors.csv`. Goal = an honest answer to "is the payment funnel actually finished, even in test?" → **No.** It works once on the happy path (~70%); the "when it doesn't go perfectly" half is largely missing. This doc is the research record; solutions are decided problem-by-problem in §9.

> Yesterday (13 Jun) = *fixed* the funnel (the "Veuillez patienter" hang + the PPPS SITE bug) so it works end-to-end once. Today (14 Jun) = *audited* it against the spec to find everything still missing before we can call it complete.

---

## 0. TL;DR
- ✅ **The original requirements survived.** `errors.csv` (27 error codes), `Cahier des charges up2pay.pages`, `ARCHITECTURE_CIBLE.md`, `CARTOGRAPHIE.md`, `PSP_FLOW_MOVIE.md`, `PAYMENT_PSP_CONNEXION.md` are all still in the repo.
- ✅ **The happy path works in sandbox** (`4000…1000` → PPPS `00000` → `paye`; `4242…` → `00157` → `refuse`).
- ❌ **6 implementation problems** + a large **test gap** + several **owner decisions/data** still stand between us and "done."
- Verdict: a **working sandbox proof-of-concept at ~70%**.

---

## 1. Recovered source-of-truth (nothing lost)
| File | What it is |
|---|---|
| `errors.csv` (Apr 15, 27 codes) | The error-types spec: `CODE, LIBELLE DOC BRUT, LIBELLE POUR LE STAGIAIRE, MESSAGE PERSONNALISÉ`. |
| `Cahier des charges up2pay.pages` (Apr 15, 298 KB) | Your original spec doc (Apple Pages; page 1 read from `preview.jpg`, body recovered via `strings` on `Index/Document.iwa`). |
| `ARCHITECTURE_CIBLE.md` | The **locked target architecture** (IPN, idempotence, security, error categories, BDD contract). |
| `CARTOGRAPHIE.md` | Full legacy DB schema + 5 business flows (incl. `Upsell_Transaction` for Garantie Sérénité). |
| `PSP_FLOW_MOVIE.md`, `PAYMENT_PSP_CONNEXION.md`, `UP2PAY.md` | Evidence-backed PSP flow + the Option-B connection spec. |

## 2. Cahier des charges — key requirements (recovered)
- **PSP/Up2Pay identifiers:** Société AM FORMATION; Contrat UP2PAY N°0966892.02; Site 0966892; rang 002; identifiant paybox 651027368; URL retour https://www.prostagespermis.fr; **CLE HMAC de TEST = "à demander à Kader"** (open external dependency).
- **Explicit error requirement:** *"lorsque le paiement ne passe pas, Up2pay nous renvoie un code erreur (ex 00115). On ne peut PAS afficher ce code tel quel au client → il faut le traduire en message clair sur la page formulaire **au niveau du bloc CB**."* → `errors.csv`.
- **Original flow named BOTH** a browser return (`normalReturnUrl`) AND a server→server **IPN** (`automaticResponseUrl`).
- Strings also reference: **abandon + monitoring**, **idempotence**, **"fail-safe"**, **emails**, exports compta.
- `ARCHITECTURE_CIBLE.md` makes the **IPN "la source de vérité"** (§1.3) with **RSA-SHA1** verification (§5.2), **idempotence "règle non négociable"** (§6), **error UX categories** (§7), and a **locked BDD write contract** (§4).

## 3. Audit method
5-agent parallel workflow (`wf_ccd61f04-e2f`) cross-referencing the requirements above against the live code — `bridge.php`, `twelvy_payment.php`, `twelvy_validate.php`, the original PSP `validate_payment.php`, the Next.js inscription + confirmation pages + payment API routes — plus the test history, then a synthesis pass. Key claims were spot-verified by hand (Garantie charging, error-code count).

## 4. Verdict + what's already solid
**Solid (done):** the happy-path capture works; the "Veuillez patienter" hang is fixed; the SITE `1999887` bug is fixed; the decline path renders a message; the "déjà réservé" UX is live; and the earlier security criticals are closed (DEBUG-split-real-bank, fake-`?status=ok` success screen, conf-token expiry, purpose-separated HMAC subkeys).

**Not done:** the 6 problems below + the test matrix + owner decisions.

---

## 5. THE 6 IMPLEMENTATION PROBLEMS

### Problem 1 — No server-to-server safety net (capture-without-record risk)
- **Spec:** IPN = *source de vérité* (`ARCHITECTURE_CIBLE.md:56-89`), so confirmation never depends on the customer's browser.
- **Reality (Option B):** the actual charge (PPPS auth+capture) happens **inside `twelvy_validate.php`**, on the **browser return**. There is no IPN / reconciliation / abandon job.
- **Precise Option-B risk window** (narrower than a hosted-page flow, but real): if the customer's browser disconnects **while `twelvy_validate.php` is running** — after the PPPS capture (~`:167`) but before the DB write (`UpdateStagePaymentData ~:292`) / redirect — and PHP aborts on the disconnect, the customer is **charged but the booking stays `pre-inscrit`**, with nothing to detect or repair it. (If the browser dies *before* reaching validate, no PPPS call happens → no charge → harmless abandon.)
- **Status:** MISSING. The single biggest dropped requirement + the "fail-safe"/monitoring the cahier asked for.

### Problem 2 — Error messages barely wired (and shown on the wrong page)
- **Spec:** translate every Up2Pay code to a clear French message **on the CB block of the form** (`errors.csv`).
- **Reality:** `bridge.php:216-236` hardcodes ~20 codes into **4 coarse buckets**, never loads `errors.csv` (comment literally says *"Will load CSV later"*), and falls back to a generic *"Code : X"*. `errors.csv` itself has **27 codes** (not the ~75 the doc implies). And on a decline, `twelvy_validate.php:159` redirects to a **separate** `/paiement/confirmation?status=refuse` page — **not** back to the card form's CB block — so there's no in-context retry; the customer must navigate back and re-key.
- **Status:** PARTIAL (classification) + MISSING (per-code messages + on-form display + in-context retry).

### Problem 3 — Garantie Sérénité shown but never charged
- **Reality:** the form shows **+57€** (`inscription page:554`, label *"+57€ TTC, supplément facturé en plus du stage"* `:930/:1130`), but `create-prospect` never receives the garantie flag, and `bridge.php:360` stores `paiement = (int)stage.prix` only; PSP charges that (`twelvy_payment.php:98`) and even prints *"Total à payer : prix € TTC"*. → customer ticks +57€, **is charged base price**, option silently lost.
- **Note:** code says **+57€**; old `CLAUDE.md` said **+25€** → figure must be confirmed. Also `(int)` cast risks truncating decimal prices.
- **Status:** MISSING / data-loss bug.

### Problem 4 — Idempotence is thin
- **Reality:** double-charge protection rests on the validate-side already-paid guard (`twelvy_validate.php:130-151`, loose compare + `supprime=0`) + PSP per-`stagiaire_id` behaviour — **never tested**. No one-time nonce on the 15-min handoff token (a captured `s/exp/sig` link **replays** for the whole window). The in-tab `isRedirectingRef` doesn't cover two tabs / Back-button. And the `facture_id`/`order_stage` **AUTO_INCREMENT** (num_suivi=1000 collision) was *just* hand-patched on **staging** — **prod must be re-verified, not assumed.**
- **Status:** PARTIAL.

### Problem 5 — Emails wired but never send
- **Reality:** success / failure / convocation emails + SMS are all **`if(!DEBUG)`-skipped** in sandbox (the PSP `mails_v3/*` templates aren't deployed on psp-copie; see 13 Jun). So today **no customer email is sent at all.**
- **Status:** PARTIAL / stubbed. (PROD: deploy real `mails_v3/*` + `modules/module.php` + verify SMTP, OR have Twelvy send its own — owner's call.)

### Problem 6 — No abandon tracking / monitoring (the "fail-safe")
- **Reality:** nothing tracks stuck `pre-inscrit`, captured-but-unconfirmed, or abandoned-at-bank bookings; no alerting/dashboard. Tightly coupled to Problem 1 (the reconciliation job *is* the fail-safe).
- **Status:** MISSING.

> **Separate from the 6** (tracked but not part of this problem-by-problem pass): pre-prod **security hardening** (strip/allowlist redirect_url + origin check, rate-limit `/api/payment/status`, conf-token out of query string, status field-allowlist, **cleartext-PAN masking / PCI**, JSON_HEX_* on inline PII) and the **prod-cutover gates** (DEBUG=false, prod PPPS creds, prod `facture_id` AUTO_INCREMENT, cross-account DB reachability, `check-stagiaire.php` GDPR leak removal). These live in `IMPLEMENTATION_PAYMENT_OPTION_B.md` / `RESUME_SESSION_13JUN.md`.

---

## 6. TEST GAPS (almost nothing beyond one happy path is exercised)
VERIFIED only: one frictionless happy path (`4000…1000` → `paye`, facture 274091) + one decline mapping (`4242…`/`00157` → `refuse`) — both sandbox, headless/single-run. **Never tested:**
- 3DS **challenge** flow (`4000…1091`) — only frictionless `…1000` was driven.
- **Browser closed after capture** (the Problem-1 scenario).
- **Double-submit / two tabs / Back-button** re-submit (idempotence).
- **Token expiry** (15-min handoff, 2h conf).
- **Stage full / price changed** mid-funnel ("Changer de date" stale stage_id).
- The **real interactive browser E2E** (3DS is interactive — never done by a human).
- **Emails / SMS actually delivering** (100% DEBUG-skipped).
- The **full error-code sweep** (only `00157` seen; ~26 other messages never rendered).
- **Concurrency / num_suivi collision** under parallel bookings post-ALTER (only a single test-insert, never on prod).
- **Zero automated tests** for the Option-B path.

---

## 7. MISSING DATA / EXTERNAL DEPENDENCIES (owner must supply)
1. **TEST recette HMAC/PPPS creds from Kader** (cahier: "à demander à Kader"). Sandbox runs on a public placeholder key.
2. **Decision: keep the IPN safety net (build reconciliation), or formally accept the dropped-IPN risk?** (the central architectural call — Problem 1).
3. **Garantie Sérénité figure** — +57€ (code) vs +25€ (old doc) — and confirm it must be charged.
4. **The final/complete error-code list + agreed wording** (CSV has 27; doc implies more).
5. **Email strategy for prod** — PSP `mails_v3` on prod vs Twelvy-owned mail.
6. **PROD PPPS/Up2Pay creds** (0966892/02/651027368/prod key, `ppps.paybox.com`) + confirm prod `prostagepsp` AUTO_INCREMENT on `facture_id`+`order_stage` (don't assume).

---

## 8. DEFINITION OF DONE
**TEST mode:** real recette creds in place; 3DS challenge completes in a real browser; browser-closed-after-capture reaches a correct final status (reconciliation/abandon job exists & tested, or risk signed off); Garantie carried through & actually charged; full `errors.csv` loaded + a decline-code sweep renders the right message **on the card form** with in-context retry; double-submit/two-tab/expired-token all fail safe; emails actually send & are received; AUTO_INCREMENT stable under a concurrent-booking test; a scripted E2E covering happy+decline+challenge+abandon.

**PROD mode (additional):** `DEBUG=false` + single shared env file; prod PPPS creds verified + one reconciled pilot real-card payment; prod AUTO_INCREMENT confirmed; PCI (stop storing cleartext PAN, delete `sessions` row post-debit); pre-prod security hardening applied; `mails_v3` + SMTP deployed/verified (or Twelvy mail); monitoring/alerting live; compta export confirmed to include Twelvy bookings; `check-stagiaire.php` GDPR leak removed.

---

## 9. SOLUTIONS RETENUES (à remplir problème par problème)
> On décide une solution par problème, on la note ici, puis on passe au suivant. Implémentation seulement une fois les 6 décidées.

- **Problème 1 — IPN / fail-safe : ✅ SOLUTION RETENUE (inherit PSP + 1 cheap improvement).**
  - **How PSP actually works (verified in the legacy code):** PSP's payment→confirmation is **synchronous** — the browser-return handler (`validate_payment.php`, which we copied into `twelvy_validate.php`) charges the card AND records the result (numtrans/numappel/status=inscrit) in the same step, then redirects. **There is NO Up2Pay IPN in the RemoteMPI/PPPS direct flow we copied** (the `automaticResponseUrl`/IPN from `ARCHITECTURE_CIBLE.md` was a design for a *different*, hosted-page approach we did not take). PSP does **not** even protect the window (no `ignore_user_abort`). PSP's ONLY safety net is a **daily cron** `planificateur_tache/up2pay/cron_status_payment.php` that, for bookings `inscrit` in the last 2 days with `up2pay_status IS NULL`, calls `retour_consultation(reference, numtrans, numappel)` — a CURL to the bank's **consultation endpoint** — and stamps `stagiaire.up2pay_status` with the gateway's true status. It is a **2nd-confirmation / verification** pass; it only re-checks payments that were already recorded (it needs numtrans/numappel), so it does **not** recover a charged-but-unrecorded booking. **PSP simply accepts that tiny risk.**
  - **What we inherit vs must build:** we already inherited the synchronous flow ✅. We have the cron code (`FTP_UPLOAD/psp-copie/www_2/planificateur_tache/up2pay/cron_status_payment.php`) but it needs: path-adaptation for psp-copie, the `retour_consultation` function (lives in a `gae/functions.php` we did NOT copy → reconstruct it as a PPPS **consultation** call via the existing `E_TransactionPayment` HMAC/CURL machinery), and to be **scheduled** on OVH.
  - **HONEST RE-SCOPE (after pushback):** the "browser disconnects" case is **already handled** by the synchronous server-side flow we inherited — if the customer's browser drops, the server still finishes the charge **and** the DB save. So there is **no real new protection to build** here. (`ignore_user_abort(true)` was over-sold earlier — it does NOT meaningfully close a gap the synchronous flow leaves open, and it does NOT help the only genuinely-uncovered case: the server itself crashing in the ~1s between charge and save. A crash kills the script regardless.) **Net: we are already ~at PSP's safety level by inheritance.**
  - **DECIDED implementation (minimal):**
    1. *(optional hygiene, ~0 cost)* `ignore_user_abort(true)` + `set_time_limit` at the top of `twelvy_validate.php` — harmless insurance, not a real fix.
    2. **Wire up PSP's daily verification cron** (adapted): rebuild `retour_consultation` (PPPS consultation TYPE via `E_TransactionPayment`), path-fix, schedule it → populates `up2pay_status` (PSP's 2nd-confirmation) AND catches the rare straggler. **The main reason to do it is MONITORING, not the edge case.**
    3. **Extend the cron to also scan stuck `pre-inscrit`** (started-but-never-confirmed): if actually charged → complete idempotently; else → mark abandon. → this IS **Problem 6** (abandon tracking + monitoring). The two problems collapse into one cron.
  - **Net:** Problem 1 is much smaller than the audit implied — **essentially already solved by inheritance.** The only worthwhile build is the daily cron, whose real value is **monitoring/abandon (Problem 6)**, not the rare crash case. No Up2Pay IPN needed. _(Owner to confirm.)_
- **Problème 2 — Messages d'erreur : ✅ PLAN ARRÊTÉ (inherit PSP — the messages already exist & are already computed).**
  - **KEY DISCOVERY (verified):** PSP's `E_TransactionError::getFullTextErrorCodes($code)` (in `www_2/src/payment/E_Transaction/E_TransactionError.php`, deployed on psp-copie) **IS `errors.csv`** — identical "MESSAGE PERSONNALISÉ" wording for all **27** customer codes (`00114,00115,00117,00130,00133,00138,00141,00143,00151,00154,00155,00156,00157,00159,00161,00168,00175,00190,00196,00201,00004,00007,00008,00020,00021,00022,00097`), with HTML `<u>`/`<br>`, + a generic technical fallback for unknown codes. errors.csv ⟺ getFullTextErrorCodes is a 1:1 match (the CSV was the source).
  - **And `twelvy_validate.php` ALREADY uses it:** on decline (`codereponse != 00000`) it calls `getFullTextErrorCodes($codereponseFlat)` and stores the full message in `$_SESSION["paiement_error"]` (≈ line 197-199). **The correct message is already produced on every decline today.**
  - **Why the customer never sees it:** on decline we redirect to `www.twelvy.net/paiement/confirmation?status=refuse` (Next.js, **different domain**) which can't read the psp-copie PHP session → the confirmation page falls back to `bridge.php` `bridge_classify_up2pay_error` (4 coarse buckets, ~11 codes, comment *"Will load CSV later"*). So the good message is computed then **discarded**. This also violates the cahier's *"message au niveau du bloc CB de la page formulaire"* (it shows on a separate page, no in-context retry).
  - **SOLUTION (Path A — PSP-native; the messages already exist, we just stop throwing them away):**
    1. **`twelvy_payment.php`** (the card form): at the top of the CB block, if `$_SESSION['paiement_error']` is set → render it as a red banner (it's trusted HTML built by `getFullTextErrorCodes`), then `unset()` it. The card form is already there → customer enters another card and **retries in-context**. (≈ reuse the existing `#rowError` zone.)
    2. **`twelvy_validate.php`** (decline branch / final redirect ~347-352): when `isInError` (decline, not success, not already-paid), instead of redirecting to `…/paiement/confirmation?status=refuse`, **redirect back to the card form** `HOST.'/twelvy_payment.php?s=<id>&exp=<fresh>&sig=<fresh>'` — minting a fresh handoff token with the same `TWELVY_HANDOFF_SECRET` (`handoffKey=hash_hmac('sha256','twelvy-handoff-v1',SECRET)`, `exp=time()+900`, `sig=hash_hmac('sha256', id.'|'.exp, handoffKey)`). Keep writing `up2pay_code_error` to the DB (for cron/monitoring). This mirrors exactly what PSP does (it redirects a decline to `page_recap.php` which displays `$_SESSION["paiement_error"]`).
    3. *(Optional, low priority — defensive consistency)* port the full message set into `bridge.php` (replace the coarse `bridge_classify_up2pay_error` with `getFullTextErrorCodes`'s map) so any residual confirmation-page `refuse` (stale link / direct URL) still shows the right text. Not on the normal path anymore.
  - **Effect:** success → confirmation page (unchanged); **decline → back to the card form with the precise message + immediate retry** (cahier-compliant); already-paid → confirmation "déjà réservé" (unchanged). No CSV loading needed (PSP already baked it into `E_TransactionError`). Minimal, low-risk: 1 display block + 1 redirect change (+ 1 optional bridge tidy).
  - **Session continuity check (verified-by-design):** `twelvy_validate.php` runs under `session_id(d2305)` = the original `twelvy_payment.php` session = the `PHPSESSID` cookie; the retry redirect re-renders `twelvy_payment.php` which `session_start()`s the same cookie → reads the `$_SESSION['paiement_error']` validate just set. PHP's per-session file lock means the retry request blocks until validate has finished writing the session → no race. ✅
  - **✅ IMPLEMENTED + TESTED (14 Jun, sandbox) — exact changes:**
    - **`php/psp-copie/twelvy_payment.php`** (3 edits):
      1. After `$dateTxt = $row['date1'];` — capture block: `$paiementError = ''; if (isset($_SESSION['paiement_error']) && is_string(...) && !== '') { $paiementError = strip_tags($_SESSION['paiement_error'], '<u><br><b><strong><div><p><span>'); unset($_SESSION['paiement_error']); }`. (SECURITY: the value is always server-built HTML from `getFullTextErrorCodes()` or a hardcoded literal — no user input, no PAN — but `strip_tags` with a formatting-only allowlist is added as defense-in-depth; consumed once via `unset`.)
      2. `<style>` — added `.pay-error{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:10px;padding:12px 14px;font-size:13.5px;line-height:1.45;margin-bottom:16px}` + `.pay-error u{text-decoration:underline}`.
      3. Between the `.recap` and the `<form>`: `<?php if ($paiementError !== '') { ?><div class="pay-error" role="alert"><?php echo $paiementError; ?></div><?php } ?>` — the banner at the CB block.
    - **`php/psp-copie/twelvy_validate.php`** (1 edit, the final-redirect block): when `$tw_status === 'refuse'`, instead of redirecting to `…/paiement/confirmation?status=refuse`, mint a FRESH 15-min handoff token (`$rk_key = hash_hmac('sha256','twelvy-handoff-v1', TWELVY_HANDOFF_SECRET); $rk_sig = hash_hmac('sha256', intval($studentId).'|'.$rk_exp, $rk_key);`) and `location.href = HOST.'/twelvy_payment.php?s=<id>&exp=<exp>&sig=<sig>'`. The `'ok'` branch (success) is unchanged → confirmation page. (SECURITY: HOST is a server constant → no open-redirect; token verifies in the same scheme `twelvy_payment.php` already uses; `up2pay_code_error` is still written to the DB for the Problem-1 cron.)
    - **Routing now:** success → `/paiement/confirmation?status=ok`; **decline → `twelvy_payment.php` with the precise message banner + in-context retry**; already-paid → confirmation `&already=1` ("déjà réservé"); no-place / already-inscrit → confirmation via the early `die()` (unchanged, retry wouldn't help).
    - **Deploy:** FTP → `/psp-copie/www/{twelvy_payment.php,twelvy_validate.php}` (both `php -l` clean, rc 226).
    - **Test (headless replay, sandbox):** card `4242…` → validate redirected to `https://psp-copie.twelvy.net/twelvy_payment.php?s=40120349&exp=…&sig=…` (NOT confirmation); fetching it (same session cookie) rendered `<div class="pay-error">…Votre paiement n'a pas pu aboutir car <u>les informations de votre carte n'ont pas été reconnues</u>.<br>…</div>` (the exact 00157 message, formatting preserved, NOT the generic fallback) + the card fields + Payer button (retry works). Card `4000…` → `…/paiement/confirmation?…status=ok` (regression OK). Test prospects soft-deleted.
  - **Where the messages live (for future edits):** `www_2/src/payment/E_Transaction/E_TransactionError.php::getFullTextErrorCodes()` (deployed on psp-copie) — it IS `errors.csv`. To change wording, edit that function (no CSV loading involved).
  - **DEFERRED (change 3, low priority):** porting the full message set into `bridge.php` `bridge_classify_up2pay_error` (for the confirmation page's `refuse` branch) — no longer on the normal path since declines route to the card form. If done later, use the **plain-text** `errors.csv` "MESSAGE PERSONNALISÉ" (the React confirmation page escapes HTML, so `<u>/<br>` would show literally).
- **Problème 3 — Garantie Sérénité : ✅ IMPLEMENTED + TESTED (inherit PSP's real mechanism).**
  - **PSP DOES have it (I first looked in the wrong tree — it's in `www_3`, the live site, not `www_2/src`).** Verified mechanism: price is **admin-configured** in `simpligestion → Garantie Sérénité` (table **`cancel_guarantee_params`**, keys `price`/`active`, read by `GuaranteeParamsService`); the form field is **`with_guarantee`**; the garantie is stored SEPARATELY in the **`stagiaire.total_guarantee`** column (NOT folded into `paiement` — `RegisterStudent`/`StudentRepository::saveStudent` set `paiement = base prix`, `total_guarantee = price`); the charge = base + garantie; the **Espace Stagiaire honours it** via `changement_avis_v3.php` checking `total_guarantee > 0`.
  - **Owner decisions:** price = **57€**; **config-driven** (form + charge both read the config → can never drift).
  - **Blocker found + fixed:** the config price on the DB was **268€** (looked like an error — more than a stage); set to **57** (`UPDATE cancel_guarantee_params SET param_value='57' WHERE param_key='price'`). The `stagiaire.total_guarantee` column already exists (float) ✅.
  - **⚠️ CRITICAL CATCH (would have silently kept the bug):** `twelvy_validate.php` computes the PPPS **capture** amount from `$stage->paiement` via `RetrieveFullOrderByStageStudent` → `getFullOrderStudentStageOption` which selects **`stagiaire.*`** ⇒ that's the BASE price. So the card page + RemoteMPI 3DS authorised base+garantie (296) but the capture would have taken only the base (239). Fixed: `$amount = round((float)$stage->paiement + (float)$stage->total_guarantee, 2)` (stagiaire.* also carries total_guarantee).
  - **Files changed (all deployed):**
    - **`php/bridge.php`** (api.twelvy.net): `bridge_guarantee_config($pdo)` helper (reads cancel_guarantee_params); new `case 'guarantee_params'` (returns {active, price}); `create_or_update_prospect` — reads `with_guarantee`, computes `$total_guarantee = (active && with_guarantee) ? round(config price,2) : 0`, `$paiement = round((float)stage.prix,2)`, stores BOTH; **paid-booking guard** (only refresh paiement/total_guarantee when the existing row is NOT paid — never corrupt a paid record); `get_stagiaire_status` returns `total_guarantee` + `montant_total` (= paiement+garantie). SECURITY: client sends only the boolean; the price is ALWAYS server-side.
    - **`php/psp-copie/twelvy_payment.php`**: SELECT adds `s.total_guarantee`; `$amount = base + garantie`; recap shows "Stage : X € · Garantie Sérénité : +Y €" + "Total à payer : Z € TTC"; button "Payer Z €".
    - **`php/psp-copie/twelvy_validate.php`**: the capture-amount fix above.
    - **`app/api/payment/guarantee/route.ts`** (new): proxies bridge `guarantee_params` (X-Api-Key server-side) → {active, price}; fallback {active:true, price:57}.
    - **inscription page**: fetches the config (price+active) on load; `totalPrice` + the +X€ label use `garantiePrice` (no more hardcoded 57); sends `with_guarantee: garantieSerenite && garantieActive`.
    - **status route + confirmation page**: pass + show `montant_total` ("Montant : 296€ TTC (dont Garantie Sérénité : 57€)").
  - **TESTS (sandbox, live):** WITH garantie → bridge stored `paiement=239 / total_guarantee=57`; card page showed/charged **296**; instrumented validate logged **`CAPTURE amount=296 (paiement=239 garantie=57)`**, PPPS `codereponse=00000`; status API `status=paye montant_total=296 total_guarantee=57`. WITHOUT → 239 throughout. `/api/payment/guarantee` → `{active:true, price:57}`. Test prospects soft-deleted; clean validate redeployed.
  - **Commits:** `f5cb171` (bridge + twelvy_payment + Next.js) + the twelvy_validate capture fix.
  - **Minor follow-ups (noted, non-blocking):** (a) the form checkbox isn't hidden when `active=0` — but the charge is still correct then (bridge enforces active → garantie 0; totalPrice gated on garantieActive), only the checkbox stays visible; (b) `order_stage.amount` can be stale if garantie is toggled after the order row exists, but the CHARGE reads `stagiaire` (always correct) so it doesn't affect money; (c) the guarantee-route fallback price (57) should track the config if the admin ever changes it.
  - **PROD note:** the `total_guarantee` column exists on prod (clone); set `cancel_guarantee_params.price` to the intended value on prod before go-live.
- **Problème 4 — Idempotence :** _(à venir)_
- **Problème 5 — Emails :** _(à venir)_
- **Problème 6 — Abandon / monitoring :** _(à venir)_
