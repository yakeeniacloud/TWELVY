# RESUME SESSION — 20 Avril 2026

**Suite de `RESUME_SESSION_19APR.md`** (Étape 6 chunk A : les 3 actions bridge.php avant-paiement).
**Session du jour** : Étape 6 chunk B partie 1/3 → **`ipn.php` — le receiver de notification de paiement Up2Pay**.

---

## 1. Contexte d'entrée de session

État au démarrage du 20 avril :
- ✅ Étapes 1-5 du plan Up2Pay terminées
- ✅ Étape 6 chunk A terminée (19 avril) — les 3 actions BDD avant paiement (`create_or_update_prospect`, `prepare_payment`, `get_stagiaire_status`) opérationnelles dans bridge.php
- ⏳ Étape 6 chunk B (les 3 fichiers après paiement : ipn.php, retour.php, pubkey.pem) à attaquer

Aujourd'hui : **`ipn.php`**, le morceau le plus critique du projet entier. Si la vérification de signature merde → fraudeurs peuvent simuler des paiements. Si l'idempotence merde → doubles facturations + doubles emails. C'est pour ça que la session a inclus un harness de test très complet (94 assertions) avant tout déploiement.

---

## 2. ⚡ Phase 1 de chunk B — `ipn.php` créé + testé exhaustivement

### 2.1 Discovery préalable (parallel agent sweep)

Lancement de 2 agents Explore en parallèle pour collecter tout le contexte avant d'écrire la moindre ligne :

- **Agent 1** : trouver le handler IPN existant côté PSP. Verdict : **PSP n'a pas d'IPN** au sens strict. Ils utilisent un modèle redirect-only (`validate_payment.php`) où le navigateur de l'utilisateur revient sur le serveur après paiement. Pas de webhook serveur-à-serveur, pas de vérification RSA. Pour Twelvy, on construit un **vrai handler IPN** qui n'a pas d'équivalent côté PSP — c'est une amélioration architecturale.
- **Agent 2** : trouver tous les scripts d'envoi d'email PSP post-paiement. Trouvé : `mail_inscription.php` (client), `mail_inscription_centre.php` (centre), `mail_echec_paiement.php` (échec), tous dans `/mails_v3/`. Dépendances lourdes (helpers `getInfosStagiaire_v3`, `MySQLDateToExplicitDate_v3`, classe `Mail` avec config SMTP en BDD). Décision : on écrit nos propres emails légers dans ipn.php pour ne pas porter PSP sur OVH Twelvy. À voir avec Kader si on rebranche les templates PSP plus tard.

Lecture parallèle de `STAGIAIRE_AUDIT.md §E` (les 5 écritures SQL exactes que PSP fait au paiement OK), `PaymentRepository.php` (le SQL brut), `errors.csv` (76 codes mappés), `bridge.php` (pour mirror le style sécurité).

### 2.2 Fichier créé : `php/ipn.php` (~480 lignes)

PHP 5.6 compatible, 11 fonctions internes toutes testables isolément, organisation en couches.

**Endpoint cible** : `https://api.twelvy.net/ipn.php` (à uploader sur OVH).

### 2.3 Décisions architecturales clés

| Décision | Pourquoi |
|----------|----------|
| Pas de X-Api-Key | Endpoint public — Up2Pay n'a pas notre token. La signature RSA EST l'authentification. |
| `openssl_verify` RSA-SHA1 | Standard Up2Pay/Paybox documenté depuis 15 ans, immuable. |
| `base64_decode($sign, true)` (strict) | Refuse les chars invalides au lieu de silent-pass. |
| `SELECT ... FOR UPDATE` sur stagiaire | Race-safe : si 2 IPN arrivent en parallèle, la 2ème bloque puis voit déjà-payé → skip. |
| Idempotence AVANT toute écriture | Règle non-négociable : `status='inscrit' AND numappel != '' AND numtrans != ''` → rollback + 200, 0 écriture, 0 email. |
| Transaction PDO autour des 4 writes succès | Atomique : tout commit ou tout rollback, jamais d'état partiel. |
| Emails APRÈS commit (best-effort) | Si email crash, la BDD est déjà cohérente. Email failure = WARN log, pas rollback. |
| `display_errors=0` forcé en début | Defense-in-depth : si OVH php.ini change, on n'expose pas de stack traces. |
| Body cap 16 KB | Up2Pay IPN est minuscule (~500 octets). 16 KB est ultra-large mais empêche les attaques de gonflement. |
| Test mode via `IPN_TESTING_NO_AUTOEXEC` | Permet aux tests d'inclure ipn.php sans déclencher l'auto-exécution. |

### 2.4 Les 4 écritures SQL succès (dans la transaction atomique)

```sql
-- Write 1/4 — promotion stagiaire
UPDATE stagiaire SET
  supprime=0, status='inscrit',
  numero_cb=:cb, numappel=:na, numtrans=:nt,
  up2pay_status='Capturé', up2pay_code_error=NULL,
  date_inscription=:d, date_preinscription=:d, datetime_preinscription=:dt,
  facture_num=:fn, paiement=:p
WHERE id=:id;

-- Write 2/4 — order
UPDATE order_stage SET is_paid=1 WHERE id=:order_id;

-- Write 3/4 — archive
INSERT INTO archive_inscriptions (id_stagiaire, id_stage, id_membre)
VALUES (:sid, :stid, :mid);

-- Write 4/4 — décrément stage (formule exacte PSP)
UPDATE stage SET
  nb_places_allouees = nb_places_allouees - 1,
  nb_inscrits = nb_inscrits + 1,
  taux_remplissage = taux_remplissage + 1
WHERE id=:id;
```

### 2.5 Les 2 écritures SQL refus

```sql
UPDATE stagiaire SET up2pay_code_error=:c, up2pay_status='Refusé' WHERE id=:id;
INSERT INTO tracking_payment_error_code (id_stagiaire, error_code, date_error, source)
VALUES (:sid, :code, :now, 'up2pay');
```

Status reste `pre-inscrit` → ligne réutilisable pour un retry du client.

### 2.6 Mises à jour de `config_paiement.php`

- **PBX_RETOUR étendu** : `Mt:M;Ref:R;Auto:A;Erreur:E;Sign:K` → `Mt:M;Ref:R;Auto:A;Erreur:E;NumAppel:T;NumTrans:S;Carte:C;Sign:K`. Maintenant Up2Pay nous renverra aussi le numéro d'appel, numéro de transaction, et numéro de carte masqué (qu'on stocke en BDD).
- **`UP2PAY_PUBKEY_PATH`** ajouté : `__DIR__ . '/pubkey_up2pay.pem'`
- **`UP2PAY_PUBKEY_PATH_TEST`** ajouté : pour tests unitaires
- **`UP2PAY_IPN_TEST_MODE`** : flag pour basculer entre clé publique prod et test
- **`EMAIL_ADMIN_NOTIFICATIONS`** / `EMAIL_FROM` / `EMAIL_FROM_NAME` : constantes mail à confirmer avec Kader (placeholders pour l'instant : `contact@twelvy.net`, `nepasrepondre@twelvy.net`)

### 2.7 Test harness — 94 tests, 0 échec

Fichiers : `php/ipn_tests/bootstrap.php` (~190 lignes) + `php/ipn_tests/test_ipn.php` (~340 lignes).

**Approche** : on génère localement une paire de clés RSA test (privée + publique) au premier run. La clé publique remplace pubkey.pem en mode test, la privée signe les payloads de test (simulant ce qu'Up2Pay ferait). DB mockée par PDO SQLite in-memory avec un schéma minimal qui mirror les 6 tables touchées (stagiaire, stage, order_stage, archive_inscriptions, tracking_payment_error_code, membre).

| Section | # | Couvre |
|---------|---|--------|
| 1. parse_body | 12 | Sign à n'importe quelle position (début/milieu/fin), body vide, no-Sign field, Sign URL-decoded correctement |
| 2. verify_signature | 6 | RSA valide → OK, body altéré (changement Mt) → KO, Sign tronqué/invalide/vide → KO |
| 3. is_already_paid | 6 | Toutes combinaisons {status × numappel × numtrans} → règle PSP exacte |
| 4. classify_error | 8 | 6 catégories UX + règle Kader (le code 99999 inconnu ne fuite jamais dans le message) |
| 5. lookup_by_reference | 5 | JOIN stagiaire+stage+order correct, ref inconnue → null |
| 6. apply_success_writes | 16 | Vérif des 4 tables × toutes colonnes touchées (numéro CB, dates, status, etc.) |
| 7. apply_refuse_writes | 12 | Refuse touche stagiaire+tracking, ne touche PAS order/stage/archive |
| 8. handle_request E2E | 29 | Succès complet, **idempotence (replay → 0 double-write, 0 double-email)**, refus, mauvaise sig (403, 0 write), ref inconnue (404), GET (405), body vide (400), missing Sign (400), missing Ref (400), missing Erreur (400), succès incomplet sans NumAppel (NOT promu), Sign avec chars spéciaux `+`/`/` (round-trip OK) |

Run :
```bash
$ php php/ipn_tests/test_ipn.php
...
============================================================
RESULTS: 94 passed, 0 failed
All tests passed ✓
```

### 2.8 Linting

```bash
$ php -l php/ipn.php
No syntax errors detected in php/ipn.php

$ php -l php/config_paiement.php
No syntax errors detected in php/config_paiement.php
```

### 2.9 Sécurité — checklist

- ✅ Vérification RSA-SHA1 stricte avant TOUT accès BDD
- ✅ `base64_decode` strict (rejette caractères invalides)
- ✅ POST only — HEAD/GET/PUT/DELETE/PATCH/TRACE → 405
- ✅ Cap body 16 KB
- ✅ Toutes requêtes SQL en prepared statements PDO bind
- ✅ `display_errors=0` forcé
- ✅ Idempotence race-safe via SELECT FOR UPDATE
- ✅ Transaction atomique → rollback total sur exception
- ✅ Logs structurés via error_log avec tag `[ipn.php][LEVEL]`
- ✅ Emails best-effort (jamais bloquant pour la BDD)
- ✅ Code brut Up2Pay JAMAIS leakké à l'utilisateur (règle Kader)

---

## 3. Fichiers produits / modifiés aujourd'hui

| Fichier | Action | Lignes |
|---------|--------|--------|
| `php/ipn.php` | NOUVEAU | ~480 |
| `php/config_paiement.php` | UPDATE — PBX_RETOUR étendu, pubkey path, email constants | +20 |
| `php/ipn_tests/bootstrap.php` | NOUVEAU — fake constants, RSA keygen, SQLite seed | ~190 |
| `php/ipn_tests/test_ipn.php` | NOUVEAU — 94 assertions sur 8 sections | ~340 |
| `.gitignore` | UPDATE — exclut privkey_test.pem + pubkey_test.pem | +4 |
| `UP2PAY.md` | UPDATE — section 8.octies (~100 lignes ajoutées) | +100 |
| `RESUME_SESSION_20APR.md` | NOUVEAU (ce fichier) | — |

**Pas encore uploadé sur OVH** — sera fait au moment de l'étape 9 (tests bout-en-bout sandbox), quand on disposera aussi de `pubkey_up2pay.pem`.

---

## 4. Reste à faire — Étape 6 chunk B parties 2/3 et 3/3

**Phase 2 : `retour.php`** (estimation 30 min)
Routeur de redirection navigateur. Reçoit GET avec `?status=ok|refuse|annule&id=12345`, fait HTTP 302 vers la bonne page Twelvy (`https://www.twelvy.net/confirmation?id=12345` / `/erreur-paiement` / etc.). Beaucoup plus simple qu'ipn.php, pas de signature à vérifier, pas d'écritures BDD.

**Phase 3 : `pubkey_up2pay.pem`** (estimation 5 min)
Téléchargement de la clé publique Up2Pay depuis https://www1.paybox.com/wp-content/uploads/2014/03/pubkey.pem et upload sur OVH `/www/api/pubkey_up2pay.pem`. Sans ça, ipn.php rejette tout (verify_signature retourne false).

---

## 5. État du plan Up2Pay après cette session

| Étape | Description | Statut |
|-------|-------------|--------|
| 1 | Audit table `stagiaire` | ✅ FAIT (16 avril) |
| 2 | Cartographier flux PHP actuel | ✅ FAIT (17 avril) |
| 3 | Designer architecture cible | ✅ FAIT (17 avril) |
| 4 | Préparer config TEST + PROD | ✅ FAIT (18 avril) |
| 5 | Créer bridge.php sécurisé | ✅ FAIT (18 avril) |
| **6** | **Bétonner scripts retour + IPN** | **🟢 75 % (chunk A + chunk B partie 1/3 fait)** |
| 7 | Brancher formulaire Next.js | ⏳ |
| 8 | Gérer retour paiement | ⏳ |
| 9 | Tests bout-en-bout sandbox | ⏳ |
| 10 | Bascule prod + monitoring | ⏳ |

**5,75/10 étapes terminées.** Foundation extrêmement solide : **112 tests cumulés passent** (11 hardening + 7 actions chunk A + 94 ipn.php).

---

## 6. Points d'attention / questions à valider avec Kader

- 🟡 **Stratégie emails** : on a pour l'instant des emails légers PHP `mail()` dans ipn.php (confirmation client + notification centre + admin BCC). Kader veut-il qu'on rebranche sur les templates PSP (mail_inscription.php avec le portail stagiaire, etc.) ?
- 🟡 **Adresses emails** : `EMAIL_ADMIN_NOTIFICATIONS=contact@twelvy.net`, `EMAIL_FROM=nepasrepondre@twelvy.net` — confirmer ces adresses (peut-être contact@prostagespermis.fr en transition ?)
- 🟡 **id_membre 837 exclu** des notifications centre (mirror du comportement PSP). Kader doit confirmer si la même règle s'applique côté Twelvy.
- 🟢 **HMAC PROD** : transmise hier, en attente de confirmation Kader.
- 🟢 **Suppression `check-stagiaire.php`** sur OVH (fuite RGPD potentielle) — toujours à faire dès que possible.

---

## 7. Cross-vérification de pubkey.pem (clé publique Up2Pay) — addendum fin de session

Yakeen a soulevé un point critique : *"comment être 100% sûr qu'on a la bonne clé publique et qu'on ne va pas refuser des vrais IPN à cause d'une mauvaise clé ?"* — bonne pression, j'étais effectivement trop confiant initialement.

### Cross-vérification effectuée

Téléchargement de `pubkey.pem` depuis **4 sources indépendantes** + comparaison cryptographique :

| Source | Type | Résultat |
|--------|------|----------|
| `paybox.com/2014/03/pubkey.pem` | URL autoritative Verifone | ✅ |
| `github.com/PayboxByVerifone/Magento-2.0.x-2.2.x` | Repo OFFICIEL Verifone | ✅ identique |
| `github.com/BenMorel/Paybox` | Lib PHP indépendante | ✅ identique |
| `github.com/EsupPortail/esup-pay` | Consortium universités françaises | ✅ identique |

**DER SHA-256 (empreinte cryptographique) — toutes les 4 sources :**
```
e3e24366a97653cb40f9ed2ee2b91fe6d8c28ee81d062ca1508c9e32abe141bb
```

Caractéristiques : RSA 1024 bits, exposant 65537, en usage continu depuis 2014, identique pour TEST et PROD.

### Safeguards documentés dans UP2PAY.md §8.octies.bis

1. **Procédure de vérification pré-upload** : commande `python3` qui calcule le DER SHA-256 du fichier téléchargé. Doit matcher la valeur canonique avant tout upload sur OVH.
2. **Monitoring des rejets** : grep `[ipn.php][ERROR] signature invalid` dans error_log. > 10 par jour avec refs CFPSP_ valides = anomalie.
3. **Re-vérification trimestrielle** : tous les 3 mois, re-télécharger + comparer le hash. Si changement → cross-check sur 2 dépôts GitHub officiels avant tout déploiement.

### Pourquoi c'est solide

- 4 sources indépendantes byte-identiques = preuve par accumulation
- 12 ans sans rotation (URL contient `2014/03`)
- Une rotation casserait des milliers de marchands worldwide → Verifone ne peut pas le faire silencieusement
- Le seul scénario d'échec restant (rotation silencieuse) est détecté en quelques heures par les logs

---

## 8. Audit sécurité paranoïaque de ipn.php — agent code review

Yakeen a explicitement demandé un audit en profondeur du chunk B avant de continuer. Lancé un agent en mode `general-purpose` avec un prompt très exigeant ("be paranoid, this is the most security-critical file"). Verdict : **8 issues réelles** trouvées + 2 issues additionnelles que j'ai vu en appliquant les fixes.

### Fixes appliqués à ipn.php (toutes critiques d'abord)

| ID | Sévérité | Issue | Fix |
|----|----------|-------|-----|
| **C1** | Critical | Sign extrait via `parse_str` au lieu de regex sur raw body — risque de corruption du base64 par le quirk PHP `+ → space` | Regex `(?:^|&)Sign=([^&]*)` sur raw body, urldecode explicite |
| **C3** | Critical | Mt jamais validé contre stage.prix → un attaquant avec une vraie sig Paybox pourrait payer €1 pour un stage à €219 | Validation `(int)Mt === (int)round(stage.prix*100)` avant transaction |
| **C4** | Critical (additionnelle) | Mt non-numérique ou négatif accepté tel quel | `ctype_digit` + `> 0` |
| **H2** | High | `openssl_pkey_get_public` failure et `openssl_verify(-1)` silencieux → debug impossible si la clé pem est corrompue | `openssl_error_string()` loggé via `ipn_log_event` |
| **H3** | High | `UP2PAY_IPN_TEST_MODE ? ... : ...` sans `defined()` — si la constante n'est pas définie en prod (oubli de config), PHP 5.6 traite "UP2PAY_IPN_TEST_MODE" comme string truthy → utilise la **clé test** au lieu de prod ! | `defined() && UP2PAY_IPN_TEST_MODE` |
| **M1** | High | Si stagiaire supprimé entre outer lookup et FOR UPDATE → `$locked = false` mais `ipn_is_already_paid(false)` = false → writes silencieux sur row inexistante (archive_inscriptions orphelin, stage décrémenté pour rien) | `if ($locked === false) { rollback; 404; return; }` |
| **M7** | Medium | Email recipient pas validé → si `stagiaire.email` contenait `\r\nBcc: attacker` (via une autre faille en amont), header injection possible | `filter_var(FILTER_VALIDATE_EMAIL)` avant `mail()` |
| **M8** | Medium (additionnelle) | Full PAN (numéro carte complet) accepté si Paybox bug → stockage data PCI illégal | Auto-masquage si `^\d{7,}` détecté |
| **L6** | Low | Dates en SQL via `date('Y-m-d')` — sans `date_default_timezone_set` les valeurs sont en UTC, pas Paris | `@date_default_timezone_set('Europe/Paris')` au top |
| **Cosm** | Cosmétique | Idempotence sans `supprime=0` (divergence PSP) | Ajouté à `ipn_is_already_paid` + locked SELECT |

### 20 nouveaux tests pour verrouiller les fixes

T71-T82 ajoutés à `test_ipn.php`. Couvre : amount mismatch (7 sub-tests), refuse path ignore Mt, non-numeric Mt, negative Mt, zero Mt, deleted stagiaire (404), supprime=1 idempotence, full-PAN auto-mask, masked-PAN preserved, email header injection rejected, valid email accepted.

**Total ipn.php : 114 tests, 0 échec.**

---

## 9. retour.php — phase B partie 2/3 terminée

Fichier `php/retour.php` (~170 lignes). Le routeur de redirection navigateur.

### Design — pourquoi c'est minuscule par rapport à ipn.php

ipn.php fait le boulot critique (vérif sig, idempotence, 4 writes atomiques, emails). retour.php est juste un agent de la circulation : il regarde un panneau et indique où aller. **Aucune écriture DB, aucun email, aucune action métier**. Il ne fait CONFIANCE à AUCUN paramètre URL — Up2Pay envoie une ribambelle de fields (Mt, Ref, Auto, Erreur, NumAppel, NumTrans, Carte) sur la redirection navigateur, mais comme **il n'y a pas de signature Sign sur la redirection navigateur** (Sign est uniquement sur l'IPN serveur-à-serveur), on ne peut RIEN trust de ces fields.

Donc retour.php :
1. Valide HTTP method (GET ou POST OK — Paybox supporte les deux)
2. Lit notre propre `?status=ok|refuse|annule&id=12345` qu'on a pré-appendé
3. Whitelist `status` (sinon défaut `annule` — le plus safe)
4. Valide `id` (positif, ≤ int32 max, ctype_digit)
5. Si id invalide → fallback homepage Twelvy
6. Sinon → HTTP 302 vers `https://www.twelvy.net/paiement/confirmation?id=X&status=Y`
7. Fin. La page de confirmation Twelvy poll bridge.php `get_stagiaire_status` qui lit la DB (source de vérité posée par ipn.php).

### Sécurité

- Endpoint PUBLIC sans auth (URL customer-facing)
- Header injection défendue : status whitelist élimine CRLF
- Path traversal défendu : id ctype_digit élimine `/`
- SQL injection : impossible (aucune query SQL dans retour.php)
- Open redirect défendu : URL construite côté serveur avec const `TWELVY_CONFIRMATION_URL`, jamais avec input
- Cache-Control: no-store + Referrer-Policy: no-referrer + X-Frame-Options: DENY + X-Content-Type-Options: nosniff
- Logs tagged `[retour.php][LEVEL]`

### 47 tests pour retour.php

Fichier `php/ipn_tests/test_retour.php`. Run : `php php/ipn_tests/test_retour.php`.

| Section | # | Couvre |
|---------|---|--------|
| 1. normalise_status | 9 | whitelist, uppercase, trim, unknown→default, null, array |
| 2. normalise_id | 15 | valid, zero, negative, non-numeric, empty, null, array, overflow, int32 max, decimal, suffix, whitespace |
| 3. handle_request success | 4 | ok / refuse / annule, POST also accepted |
| 4. input validation | 10 | unknown status, missing id, non-numeric id, negative id, overflow id, **SQL injection**, PUT, DELETE |
| 5. Paybox fields ignored | 3 | Mt/Ref/Sign ignored, Erreur cannot override status, attacker `dest` ignored |
| 6. edge cases | 6 | **CRLF in status (header injection)**, path traversal in id, empty params, non-array params |

**Total retour.php : 47 tests, 0 échec.**

### Constants ajoutées à config_paiement.php

```php
define('TWELVY_CONFIRMATION_URL', 'https://www.twelvy.net/paiement/confirmation');
define('TWELVY_HOMEPAGE_URL',     'https://www.twelvy.net/');
```

À l'étape 8, on créera la route Next.js `/paiement/confirmation` qui poll bridge.php pour le statut réel et affiche l'UI appropriée.

---

## 10. pubkey_up2pay.pem téléchargée + vérifiée

```bash
$ curl -fsSL https://www1.paybox.com/wp-content/uploads/2014/03/pubkey.pem -o php/pubkey_up2pay.pem
$ python3 hash_check.py
Actual:   e3e24366a97653cb40f9ed2ee2b91fe6d8c28ee81d062ca1508c9e32abe141bb
Expected: e3e24366a97653cb40f9ed2ee2b91fe6d8c28ee81d062ca1508c9e32abe141bb
✅ HASH MATCH

$ php -r "var_dump(openssl_pkey_get_public(file_get_contents('php/pubkey_up2pay.pem')));"
object(OpenSSLAsymmetricKey)#1  // PHP can load it
```

Fichier local prêt. Protégé par `*.pem` dans `.gitignore` (jamais commit). Doit être uploadé sur OVH `/www/api/pubkey_up2pay.pem` (mode 644) en même temps que les 3 autres fichiers (ipn.php, retour.php, config_paiement.php).

---

## 11. Récap final — ce qui est dans ce repo après le 20 avril

| Fichier | Statut | Lignes |
|---------|--------|--------|
| `php/ipn.php` | ✅ Audité, 10 fixes appliqués, 114 tests | ~530 |
| `php/retour.php` | ✅ Nouveau, 47 tests | ~170 |
| `php/config_paiement.php` | ✅ Étendu (PBX_RETOUR + pubkey path + emails + Twelvy URLs) | ~155 |
| `php/pubkey_up2pay.pem` | ✅ Téléchargé, hash vérifié, PHP-loadable | (bin) |
| `php/ipn_tests/bootstrap.php` | ✅ Test infrastructure | ~190 |
| `php/ipn_tests/test_ipn.php` | ✅ 114 tests | ~470 |
| `php/ipn_tests/test_retour.php` | ✅ 47 tests | ~200 |
| `UP2PAY.md` | ✅ §8.octies + §8.octies.bis + §8.nonies | ~1430 lignes total |
| `RESUME_SESSION_20APR.md` | ✅ Ce fichier | — |

### Tests cumulés (totaux)
- Étape 5 hardening bridge.php : 11 tests ✅
- Étape 6 chunk A actions bridge.php : 7 tests ✅
- Étape 6 chunk B ipn.php : **114 tests ✅** (94 originaux + 20 audit)
- Étape 6 chunk B retour.php : **47 tests ✅**
- **Total projet : 179 tests, 0 échec**

### Reste à faire pour finaliser Étape 6
**Upload OVH bloc atomique** :
1. `php/ipn.php` → `/www/api/ipn.php`
2. `php/retour.php` → `/www/api/retour.php`
3. `php/config_paiement.php` → `/www/api/config_paiement.php`
4. `php/pubkey_up2pay.pem` → `/www/api/pubkey_up2pay.pem` (mode 644)

Une fois fait, **Étape 6 = 100% terminée**.

---

---

## 12. Upload OVH + tests live — 21 avril (étape 6 COMPLÈTE)

### Procédure d'upload

FTP target : `ftp.cluster115.hosting.ovh.net` → `/www/api/` (sert `https://api.twelvy.net/`).

Méthode : `.netrc` temporaire en `/tmp/` (mode 600), curl avec `--netrc-file`, credentials jamais exposés dans la process list.

### 4 fichiers uploadés + vérification SHA-256 byte-identique

| Fichier | Taille | SHA-256 | Statut |
|---------|--------|---------|--------|
| `pubkey_up2pay.pem` | 272 B | `f6652b87...1927036` | ✅ match byte-à-byte |
| `config_paiement.php` | 7 689 B | `f056e23e...6a86576` | ✅ match byte-à-byte |
| `ipn.php` | 29 046 B | `2d7bd9ef...fd016889` | ✅ match byte-à-byte |
| `retour.php` | 7 678 B | `b75ccac4...d4848650` | ✅ match byte-à-byte |

Backup de l'ancien `config_paiement.php` (6 131 B, `040e84fe...0ba008`) sauvegardé en `php/_backups/config_paiement.php.ovh-backup-2026-04-21` pour rollback.

### Smoke tests live — 15/15 passent

Testés contre `https://api.twelvy.net/` :
- ipn.php : GET→405, empty POST→400, POST-no-Sign→400, POST-fake-Sign→403 "bad signature", 133KB body→413, 50 concurrents→all 400 no leak
- retour.php : toutes combinaisons status/id/methods (GET/POST OK, HEAD/PUT/DELETE→fallback homepage, CRLF header injection→status=annule sans leak, SQL injection→homepage)
- config_paiement.php / config_secrets.php direct → 403 "Direct access forbidden" (TWELVY_BRIDGE guard)
- pubkey_up2pay.pem → 200 + PEM content
- bridge.php ping (token OK) → `{success:true, php_version:5.6.40}` ; sans token → 403 unauthorized ; CORS depuis origin evil.com → allow-origin reste twelvy.net (correct)

### Tests adversarial (agent indépendant, 32 probes, mode "attacker")

**0 finding critique. 0 finding high. Quelques findings mineurs non-bloquants** :
1. **False positive investigated** : `.env` / `.git/config` / `.htpasswd` → 403 avec 4 731 B body. Enquête via FTP listing : ces fichiers n'existent PAS dans `/www/api/`. Les 4 731 B sont la page OVH WAF par défaut ("Your request has been blocked") qui intercepte les patterns dotfile. Pas un leak, comportement OVH attendu.
2. Header `X-Powered-By: PHP/5.6` exposé sur toutes les réponses → recommandation future (low priority).
3. Pas de HSTS header → recommandation future.

### Comparaison live-vs-local (agent #2)

17/22 tests atteignables en black-box → **17/17 PASS**. Les 5 tests non-atteignables sont des branches post-vérification RSA (nécessitent la clé privée Up2Pay pour forger une sig valide) — couvertes par le harness local via `UP2PAY_IPN_TEST_MODE`.

**Confiance que live match local : HIGH.**

### Impact utilisateur

**Zéro.** Les 4 fichiers uploadés sont des orphelins jusqu'à Étape 7 (branchement Next.js). Aucun frontend ne les appelle encore. prostagespermis.fr (live production) totalement non-affecté (domaine + serveur différents).

### Étape 6 = 100% TERMINÉE ✅

| Sub-step | Status |
|----------|--------|
| Chunk A — 3 actions bridge.php (pré-paiement) | ✅ 19 avr |
| Chunk B.1 — ipn.php + 94 tests | ✅ 20 avr |
| Chunk B.2 — audit sécurité + 10 fixes + 20 tests (114 total) | ✅ 21 avr |
| Chunk B.3 — retour.php + 47 tests | ✅ 21 avr |
| Chunk B.4 — pubkey téléchargé + hash canonical vérifié | ✅ 21 avr |
| Chunk B.5 — upload OVH + 15 smoke tests + 32 adversarial probes | ✅ 21 avr |

### Tests cumulés projet

| Phase | Tests |
|-------|-------|
| Étape 5 hardening bridge.php | 11 ✅ |
| Étape 6 chunk A actions bridge.php | 7 ✅ |
| Étape 6 chunk B ipn.php | 114 ✅ |
| Étape 6 chunk B retour.php | 47 ✅ |
| Étape 6 chunk B live smoke tests | 15 ✅ |
| Étape 6 chunk B adversarial probes (agent) | 32 ✅ |
| **TOTAL** | **226 tests, 0 échec** |

---

---

## 13. Hotfix URL Up2Pay (22 avril, en pré-Étape 7)

### Bug découvert

Yakeen a demandé "comment tu sais où est l'iframe Up2Pay et que ce n'est pas obsolète". J'ai fait un curl direct sur les URLs de mon config et catastrophe : `tpeweb.up2pay.com` et `preprod-tpeweb.up2pay.com` **ne résolvent pas en DNS** (NXDOMAIN). Le domaine `up2pay.com` n'existe tout simplement pas. "Up2pay" est le nom commercial Crédit Agricole pour le produit, pas un hostname.

Les vrais hostnames Verifone/Paybox (5 URLs candidates testées, toutes HTTP 200) :
- `tpeweb.paybox.com` (Verifone branding, prod)
- `tpeweb.e-transactions.fr` (Crédit Agricole branding)
- `preprod-tpeweb.e-transactions.fr` (Crédit Agricole branding, test/preprod)
- `preprod-tpeweb.paybox.com` (Verifone branding, preprod)
- `recette-tpeweb.e-transactions.fr` (alias staging)

Convention PSP confirmée : test=`e-transactions.fr`, prod=`paybox.com`. PSP utilise `recette-ppps.e-transactions.fr` (test) et `ppps.paybox.com` (prod) en mode PPPS — même pattern à transposer en MYchoix.

### Décision

- **TEST** : `https://preprod-tpeweb.e-transactions.fr/cgi/MYchoix_pagepaiement.cgi`
- **PROD** : `https://tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi`

### Re-déploiement

1. Edit local `config_paiement.php` (lignes 30 et 43)
2. Lint OK (`php -l`)
3. Re-upload sur OVH (`config_paiement.php` seul, 1 fichier)
4. Re-download + SHA-256 verify byte-identique (`ac8b864300b1e02fd147abd1c7e7b7cbbdfdd05de596f9e6ee040cf430812256`) ✅
5. Regression smoke tests :
   - `bridge.php` ping → ✅ `{success:true, php_version:5.6.40}`
   - `ipn.php` POST bad-sig → ✅ HTTP 403
   - `retour.php` GET avec id valide → ✅ 302 vers confirmation URL
6. `.netrc` cleanup ✅
7. UP2PAY.md : 8 occurrences `up2pay.com` remplacées par `paybox.com` ou `e-transactions.fr` selon contexte
8. UP2PAY.md §8.undecies créée pour documenter le hotfix
9. Procédure de vérification URL ajoutée au workflow futur (curl HTTP 200 check obligatoire avant changement de config)

### Impact

**Zéro impact production : bug dormant.** L'URL n'est appelée que par `bridge.php prepare_payment`, qui n'a aucun consommateur tant qu'Étape 7 n'est pas faite. Si on avait fait Étape 7 sans corriger, la première tentative de paiement aurait montré une page d'erreur DNS dans l'iframe customer (rien n'aurait fonctionné).

**Bonne pression de Yakeen** — encore une fois, la double-vérification a évité un bug bloquant. Pattern pubkey.pem qui se répète : ne jamais déployer une URL externe sans curl HTTP 200 check préalable.

---

**Session 20-22 Avril 2026 — Étape 6 entièrement terminée + déployée + hotfix URL appliqué.**
**Foundation extrêmement solide : 226 tests cumulés passent.**
**Audit paranoïaque de ipn.php → 10 issues fixées avant déploiement.**
**Hotfix URL Up2Pay → 100% des paiements sauvés avant même la première tentative.**
**Backend paiement live sur api.twelvy.net. Zero impact utilisateur (orphelins jusqu'à Étape 7).**
**Prochaine session : Étape 7 — brancher le formulaire Twelvy Next.js sur bridge.php.**
