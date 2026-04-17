# UP2PAY.md — Master Reference for Twelvy Up2Pay Integration

> **Status** : recherche initiale terminée le 15 avril 2026.
> Aucun code écrit. Ce document est la **base de connaissances unique** pour l'étape 8 (intégration Up2Pay sur Twelvy).
> **NE JAMAIS commit de secrets ici.** Les valeurs réelles des clés HMAC restent dans `config_secrets.php` (non versionné) côté OVH et dans Vercel env vars côté Next.js.

---

## 0. TL;DR — Ce qu'il faut savoir en 2 minutes

- **Up2Pay = Paybox = E-Transactions** : trois noms pour le même produit. Crédit Agricole Mon Commerce le revend. Protocole inchangé depuis 15+ ans → docs Paybox/Verifone restent autoritatives.
- **PSP utilise Up2Pay aujourd'hui** en **mode Direct API (PPPS)** : carte transite par le serveur PSP. C'est le code à reprendre.
- **Cahier des charges Twelvy** prévoit le **mode Hosted iFrame (MYchoix)** : carte saisie sur page Up2Pay hébergée chez eux. ⚠️ **Décision à prendre avec Kader avant d'écrire la moindre ligne.**
- **Architecture cible** : Next.js (UI) ↔ `bridge.php` sur OVH (HMAC + MySQL writes) ↔ Up2Pay ↔ scripts PHP retour/IPN existants (sur OVH).
- **Règle d'or** : HMAC, MySQL et IPN restent **toujours côté PHP** (OVH). Next.js ne touche jamais à la BDD ni au HMAC.
- **PHP 5.6 obligatoire** côté OVH (pas de syntaxe moderne).
- **Idempotence IPN** non négociable : Up2Pay peut envoyer la même notif 2 fois → ne déclencher mails/MAJ qu'une seule fois par transaction.
- **Tests obligatoires en mode TEST Up2Pay** (sandbox publique) avant tout vrai paiement.

---

## 1. Qu'est-ce que Up2Pay E-Transactions ?

### Histoire des noms
- **Paybox System** (années 1990-2010) — gateway développé par Paybox Services
- **E-Transactions** — version white-labeled vendue par Crédit Agricole sur l'infra Paybox
- **Up2Pay e-Transactions** — branding actuel (depuis ~2020) sous Crédit Agricole Mon Commerce
- Verifone a racheté Paybox → la doc Verifone reste la référence technique

### Type de gateway
**Hosted Payment Page (HPP)** : le marchand construit un formulaire signé, le navigateur POST-redirige vers Up2Pay qui affiche la page de paiement. Avantage : la carte ne touche jamais le serveur marchand → **PCI-DSS hors scope**.

Le résultat revient via **deux canaux** :
1. **Browser redirect** (PBX_EFFECTUE / PBX_REFUSE / PBX_ANNULE) → UX seulement, **non fiable**
2. **Server-to-server IPN** (PBX_REPONDRE_A) → **autoritatif**, signé en RSA, retry automatique

### Règle absolue
**Ne JAMAIS marquer une commande payée depuis la redirection navigateur**. Seul l'IPN signé fait foi.

---

## 2. ⚠️ Décision architecturale critique : 2 modes Up2Pay

PSP actuel et le cahier des charges Twelvy ne sont **pas alignés**. Cette décision conditionne tout le reste.

### Mode A — Direct API "PPPS" (mode actuel PSP)
- Endpoint : `https://ppps.paybox.com/PPPS.php` (prod)
- Le formulaire CB est **dans le code PSP** (page `/es/inscriptionv2_3ds.php`)
- Le serveur PSP reçoit le numéro de carte → POST en cURL vers Up2Pay → reçoit `codereponse=00000` immédiatement → met à jour stagiaire
- Avantage : flux synchrone, pas de redirect navigateur, contrôle total
- Inconvénient : **le serveur PSP touche la carte** → PCI-DSS s'applique → audit obligatoire en théorie

### Mode B — Hosted Page "MYchoix" (mode cahier des charges)
- Endpoint : `https://tpeweb.up2pay.com/cgi/MYchoix_pagepaiement.cgi` (prod)
- Le formulaire CB est **chez Up2Pay** (iFrame intégré ou redirect)
- Le serveur Twelvy ne voit jamais la carte → **hors PCI-DSS**
- Flux asynchrone : redirect navigateur + IPN serveur séparés → idempotence indispensable
- Cahier des charges : **"On utilise le mode page de paiement hébergée intégrée (iFrame). Champs CB chez Up2Pay, pas dans notre code"**

### Recommandation
**Mode B (Hosted iFrame)** est ce que dit le cahier des charges et c'est aussi le standard moderne. C'est ce qu'utilisent Magento, WooCommerce, Prestashop côté Up2Pay. Le passage de A → B :
- supprime la complexité PCI
- nécessite de **réécrire** la couche paiement (le code PSP actuel ne sert plus que comme référence métier)
- nécessite un **vrai script IPN robuste** (PSP n'en a pas vraiment, le retour est synchrone en mode A)

**Action** : faire valider ce choix avec Kader avant d'écrire `bridge.php`.

---

## 3. Identifiants & credentials

### Production (Société AM FORMATION)
| Paramètre | Valeur | Source |
|-----------|--------|--------|
| Société | AM FORMATION | Cahier des charges p.1 |
| Contrat | UP2PAY N°0966892.02 | Cahier des charges p.1 |
| PBX_SITE | `0966892` | Cahier des charges + PSP `E_TransactionConfig.php` |
| PBX_RANG | `02` (sur 2 chars dans le code PSP, doc dit `002` sur 3) | PSP `E_TransactionConfig.php` |
| PBX_IDENTIFIANT | `651027368` | Cahier des charges + PSP `E_TransactionConfig.php` |
| **PBX_HMAC PROD (clé)** | **DANS** `/Volumes/Crucial X9/PROSTAGES/www_2/src/payment/E_Transaction/E_TransactionConfig.php` | **NE JAMAIS commiter ici** |
| URL de redirection (config back-office Up2Pay) | `https://www.prostagespermis.fr` | Cahier des charges p.1 |
| Gateway URL (mode A actuel) | `https://ppps.paybox.com/PPPS.php` | PSP source |
| Gateway URL (mode B futur) | `https://tpeweb.up2pay.com/cgi/MYchoix_pagepaiement.cgi` | Doc Up2Pay |

> **Important** : la clé HMAC PROD est de la forme 128 caractères hexa (HMAC-SHA-512 64 bytes). Elle existe dans le code PSP mais doit migrer vers `config_secrets.php` non versionné dès qu'on touche à ce projet.

### Test / Sandbox
| Paramètre | Valeur PSP (héritée) | Valeur publique standard Verifone |
|-----------|---------------------|-----------------------------------|
| PBX_SITE | `1999887` | `1999888` |
| PBX_RANG | `63` | `32` |
| PBX_IDENTIFIANT | `222` | `110647233` |
| PBX_HMAC TEST | `0123456789ABCDEF...` (clé dummy 128 chars répétée) | Idem |
| Gateway URL (mode A) | `https://recette-ppps.e-transactions.fr/PPPS.php` | — |
| Gateway URL (mode B) | `https://preprod-tpeweb.up2pay.com/cgi/MYchoix_pagepaiement.cgi` | — |

> Le cahier des charges dit "CLÉ HMAC TEST : à demander à Kader". En pratique, on a déjà la clé test dans le code PSP, et la clé publique Verifone marche aussi pour les tests. **À confirmer avec Kader si on prend la clé Up2Pay back-office TEST de PSP, ou la clé publique Verifone**.

### Cartes de test (sandbox)
| PAN | Expiry | CVV | Résultat |
|-----|--------|-----|----------|
| `1111222233334444` | n'importe | `123` | Succès (`00000`) |
| `1111222233335555` | n'importe | `123` | Refusée |
| `4012001037141112` | n'importe | `123` | Déclenche challenge 3DS2 |
| `5555555555554444` | n'importe | `123` | Mastercard succès |

> Forçage de code : utiliser `PBX_ERRORCODETEST=00100` etc. pour forcer un code précis (ignoré en prod).

---

## 4. Le flux complet (mode B Hosted iFrame, cible Twelvy)

```
┌──────────┐  1. Step 1 Coordonnées soumis     ┌──────────────┐
│  Buyer   │ ────────────────────────────────► │ Next.js      │
└──────────┘                                   │ (Vercel)     │
     ▲                                         └──────┬───────┘
     │                                                │ 2. POST /api/bridge?action=create_or_update_prospect
     │                                                │    header X-Api-Key
     │                                                ▼
     │                                         ┌──────────────┐
     │                                         │ bridge.php   │ → INSERT/UPDATE stagiaire (statut "prospect")
     │                                         │ (OVH PHP 5.6)│ → return {success, data.stagiaire_id}
     │                                         └──────┬───────┘
     │ 3. Step 2 CB visible + bouton "Payer"          │
     │ ◄──────────────────────────────────────────────┘
     │
     │  4. Click "Payer"                       ┌──────────────┐
     │ ──────────────────────────────────────► │ Next.js      │
     │                                         └──────┬───────┘
     │                                                │ 5. POST /api/bridge?action=prepare_payment&id=12345
     │                                                ▼
     │                                         ┌──────────────┐
     │                                         │ bridge.php   │ → calc montant, prépare PBX_*, signe HMAC
     │                                         │              │ → return {paymentFields: {PBX_SITE, PBX_TOTAL, ...}}
     │                                         └──────┬───────┘
     │                                                │
     │ 6. Form auto-POST vers Up2Pay (vu navigateur)  │
     │ ◄──────────────────────────────────────────────┘
     │
     │ 7. Saisie CB + 3DS2 sur page Up2Pay
     ▼
┌─────────────────────────────────────────┐
│ Up2Pay tpeweb.up2pay.com                │
│ - autorisation                          │
│ - 3DS2 challenge                        │
└──────┬──────────────────────────┬───────┘
       │                          │
   8a. Browser redirect       8b. IPN serveur (PBX_REPONDRE_A)
       │                          │
       ▼                          ▼
┌──────────────┐          ┌──────────────────┐
│ retour.php   │          │ ipn.php (OVH)    │
│ (OVH)        │          │ - vérifie sig RSA│
│ - lit URL    │          │ - check idempot. │
│ - redirect   │          │ - UPDATE stagiair│
│ vers Next.js │          │ - send emails    │
└──────┬───────┘          └──────────────────┘
       │
       ▼
┌──────────────────────────────────────────┐
│ Next.js /confirmation?id=12345           │
│ - polling get_stagiaire_status (2-3 sec) │
│ - jusqu'à status="paye"                  │
│ - affiche récap                          │
└──────────────────────────────────────────┘
```

---

## 5. Paramètres POST envoyés à Up2Pay (mode B)

### Obligatoires
| Champ | Format | Max | Description |
|-------|--------|-----|-------------|
| `PBX_SITE` | numérique 7 chars | 7 | Site marchand (`0966892`) |
| `PBX_RANG` | numérique 2-3 chars | 3 | Rang (`02`) |
| `PBX_IDENTIFIANT` | numérique | 9 | ID marchand (`651027368`) |
| `PBX_TOTAL` | entier en CENTIMES | 10 | 219.00€ → `21900`. **JAMAIS de virgule** |
| `PBX_DEVISE` | ISO-4217 numérique | 3 | `978` = EUR |
| `PBX_CMD` | string | 250 | Référence commande unique (ex: `BK-2026-000123`) |
| `PBX_PORTEUR` | email | 60 | Email du client |
| `PBX_RETOUR` | template | 250 | `Mt:M;Ref:R;Auto:A;Erreur:E;Sign:K` (Sign:K obligatoire pour vérif IPN) |
| `PBX_HASH` | string | — | `SHA512` |
| `PBX_TIME` | ISO-8601 UTC | — | `gmdate('c')` — anti-replay |
| `PBX_HMAC` | hex upper | 128 | Signature HMAC-SHA-512 |

### URLs de retour (toutes optionnelles mais recommandées)
| Champ | Quand appelé |
|-------|-------------|
| `PBX_EFFECTUE` | Browser redirect après succès |
| `PBX_REFUSE` | Browser redirect après refus |
| `PBX_ANNULE` | Browser redirect après annulation client |
| `PBX_ATTENTE` | Browser redirect si en attente |
| `PBX_REPONDRE_A` | **IPN** server-to-server. **C'est lui qui fait foi** |
| `PBX_RUF1` | Méthode HTTP utilisée pour PBX_REPONDRE_A (`POST` recommandé) |

### Optionnels mais recommandés (3DS2 / PSD2)
- `PBX_BILLING` — bloc XML adresse acheteur (souvent requis pour 3DS2 frictionless)
- `PBX_SHOPPINGCART` — `<shoppingcart><total><totalQuantity>1</totalQuantity></total></shoppingcart>`
- `PBX_LANGUE` — `FRA`
- `PBX_TYPEPAIEMENT` / `PBX_TYPECARTE` — forcer un moyen de paiement
- `PBX_AUTOSEULE` — `O` = pré-autorisation seule (pas de capture)

---

## 6. Algorithme HMAC

### Règles
1. Concaténer **tous** les champs PBX_* qui seront POST, **dans le même ordre** que les `<input>` du form
2. Format `KEY=VALUE&KEY=VALUE` — **pas d'URL encoding** dans la chaîne signée
3. `PBX_HASH=SHA512` doit être présent dans la chaîne signée
4. La clé HMAC est 128 chars hex → convertir en 64 bytes via `pack("H*", ...)`
5. Résultat en **hex MAJUSCULE** dans `PBX_HMAC`

### Exemple PHP 5.6 compatible
```php
<?php
// Toujours côté PHP. JAMAIS côté Next.js.
$hmacKey = '78f9db5d...'; // 128 hex chars depuis config_secrets.php

$params = array(
    'PBX_SITE'        => '0966892',
    'PBX_RANG'        => '02',
    'PBX_IDENTIFIANT' => '651027368',
    'PBX_TOTAL'       => '21900',                        // 219,00 € en centimes
    'PBX_DEVISE'      => '978',
    'PBX_CMD'         => 'BK-2026-000123',
    'PBX_PORTEUR'     => 'client@example.com',
    'PBX_RETOUR'      => 'Mt:M;Ref:R;Auto:A;Erreur:E;Sign:K',
    'PBX_HASH'        => 'SHA512',
    'PBX_TIME'        => gmdate('c'),
    'PBX_EFFECTUE'    => 'https://www.prostagespermis.fr/api/retour.php?status=ok',
    'PBX_REFUSE'      => 'https://www.prostagespermis.fr/api/retour.php?status=refuse',
    'PBX_ANNULE'      => 'https://www.prostagespermis.fr/api/retour.php?status=annule',
    'PBX_REPONDRE_A'  => 'https://www.prostagespermis.fr/api/ipn.php',
);

// 1. Construire la chaîne signée dans l'ordre exact du formulaire
$toSign = '';
foreach ($params as $k => $v) {
    $toSign .= ($toSign === '' ? '' : '&') . $k . '=' . $v;
}

// 2. Convertir hex → binaire
$binKey = pack('H*', $hmacKey);

// 3. HMAC-SHA-512 → hex majuscule
$params['PBX_HMAC'] = strtoupper(hash_hmac('sha512', $toSign, $binKey));

// 4. Renvoyer les params à Next.js (qui fera l'auto-submit)
echo json_encode(array('success' => true, 'data' => array(
    'paymentUrl' => 'https://tpeweb.up2pay.com/cgi/MYchoix_pagepaiement.cgi',
    'paymentFields' => $params,
)));
```

### Pièges classiques
| Symptôme | Cause |
|----------|-------|
| Erreur `00001` "Identification problem" | Mauvais site/rang/identifiant OU HMAC mismatch OU mauvaise clé d'environnement |
| HMAC valide localement mais rejeté | Ordre des champs dans `$toSign` différent de l'ordre des `<input>` |
| HMAC mismatch avec accents | URL-encoding fait avant la signature (ne JAMAIS encoder avant de signer) |
| Clé "ne marche pas" | Oubli du `pack("H*", ...)` (utiliser la clé en tant que string) |
| `PBX_TOTAL` rejeté | Pas en centimes (virgule au lieu d'entier) |
| `PBX_TIME` rejeté | Pas UTC ISO-8601 ou drift d'horloge serveur > 30 min |

---

## 7. IPN (PBX_REPONDRE_A) — handler PHP

### Différences vs HMAC sortant
- L'IPN est signée en **RSA-SHA1** (pas HMAC), avec la **clé privée Up2Pay**
- On vérifie avec la **clé publique Up2Pay** : https://www1.paybox.com/wp-content/uploads/2014/03/pubkey.pem
- La signature couvre **uniquement les champs demandés dans `PBX_RETOUR`**, dans cet ordre, URL-encoded comme reçus, **moins `Sign=`**

### Handler PHP 5.6 type
```php
<?php
// php/ipn_up2pay.php
$pubKeyPath = __DIR__ . '/pubkey_up2pay.pem'; // téléchargée depuis paybox.com
$pubKey     = openssl_pkey_get_public(file_get_contents($pubKeyPath));

// 1. Lire la requête brute (préserve l'ordre)
$raw = file_get_contents('php://input');
parse_str($raw, $data);

// 2. Extraire et décoder la signature
if (empty($data['Sign'])) {
    http_response_code(400);
    error_log('[IPN] Sign manquant');
    exit('missing sign');
}
$sig = base64_decode(urldecode($data['Sign']));

// 3. Reconstruire le message signé (raw query string moins Sign=...)
$msg = preg_replace('/(^|&)Sign=[^&]*/', '', $raw);
$msg = ltrim($msg, '&');

// 4. Vérifier la signature RSA-SHA1
$ok = openssl_verify($msg, $sig, $pubKey, OPENSSL_ALGO_SHA1);
if ($ok !== 1) {
    http_response_code(403);
    error_log('[IPN] Signature invalide pour ref=' . (isset($data['Ref']) ? $data['Ref'] : '?'));
    exit('bad signature');
}

// 5. Extraire les données métier
$ref     = $data['Ref'];     // PBX_CMD échoé
$amount  = $data['Mt'];      // en centimes
$auth    = isset($data['Auto']) ? $data['Auto'] : '';
$erreur  = $data['Erreur'];  // "00000" = succès

// 6. Charger le stagiaire
$stagiaire = StagiaireRepository::findByReference($ref);
if (!$stagiaire) {
    http_response_code(404);
    error_log('[IPN] Stagiaire introuvable pour ref=' . $ref);
    exit('not found');
}

// 7. IDEMPOTENCE — règle non négociable
if ($stagiaire['status'] === 'paye' && !empty($stagiaire['numtrans'])) {
    error_log('[IPN] Déjà payé, no-op pour ref=' . $ref);
    http_response_code(200);
    exit('already paid');
}

// 8. Appliquer la mise à jour
if ($erreur === '00000') {
    StagiaireRepository::markPaid($stagiaire['id'], $amount, $auth, $data);
    EmailService::sendStudentTicket($stagiaire);
    EmailService::sendCenterNotification($stagiaire);
    EmailService::sendAdminCopy($stagiaire);
} else {
    StagiaireRepository::markRefused($stagiaire['id'], $erreur, $data);
    EmailService::sendStudentFailure($stagiaire, $erreur);
}

// 9. Répondre 200 — Up2Pay retry sinon (jusqu'à 24h)
http_response_code(200);
echo 'OK';
```

### Bonnes pratiques IPN
- **Idempotence** : Up2Pay retry si non-2xx ou unreachable. La règle dans le code PSP existant est :
  ```php
  if ($status === 'inscrit' && $supprime === 0 && $numappel !== '' && $numtrans !== '') {
      // déjà payé, on no-op
  }
  ```
- **Réponse attendue** : HTTP 200 avec body court. Tout autre code = retries.
- **Retries Up2Pay** : jusqu'à ~24h en cas d'échec, configurable via support.
- **IPs source** (allow-list optionnel) : `194.2.122.158`, `194.2.122.190`, `195.25.7.166` + ranges Verifone actuels. La vérif RSA est la vraie sécurité, pas le filtrage IP.
- **Timeout handler** : < 10 secondes. Si gros traitement → queue async.
- **Ne JAMAIS faire confiance au browser redirect seul** (PBX_EFFECTUE) — un client peut fermer son onglet avant.

---

## 8. Architecture cible Twelvy (cahier des charges étape 3)

### Rôles
| Brique | Hébergement | Rôle |
|--------|-------------|------|
| **Next.js** | Vercel | UI uniquement (formulaire, récap, page success/error). Appelle `bridge.php`. Ne touche JAMAIS MySQL ni HMAC. |
| **Bridge PHP** | OVH (PHP 5.6) | Passerelle Next.js ↔ MySQL/Up2Pay. Insère/met à jour `stagiaire`. Signe les params Up2Pay. Renvoie statuts à Next.js. Protégé par `X-Api-Key`. |
| **Scripts retour + IPN PHP** | OVH (PHP 5.6) | Reçoivent les retours Up2Pay. Vérifient HMAC/RSA. Mettent à jour `stagiaire`. Envoient emails. Sont la **source de vérité du paiement**. |
| **MySQL** | OVH | Table `stagiaire`. Modifiée uniquement par PHP (bridge + IPN), jamais par Next.js. |

### Bridge.php — actions exposées
| Action | Quand | Input | Output |
|--------|-------|-------|--------|
| `ping` | Test santé | — | `{success:true, data:{message:"pong"}}` |
| `create_or_update_prospect` | Étape 7.1 (validation Step 1 Coordonnées) | nom, prénom, email, tel, stage_id, etc. | `{success, data:{stagiaire_id}}` |
| `prepare_payment` | Étape 7.2 (clic "Payer") | `id_stagiaire` | `{success, data:{stagiaire_id, paymentUrl, paymentFields:{PBX_SITE, PBX_TOTAL, PBX_HMAC, ...}}}` |
| `get_stagiaire_status` | Étape 8 (page confirmation/échec) | `id_stagiaire` | `{success, data:{status, errorCategory?, errorMessage?, stagiaire:{...recap}}}` |

### Sécurité
- Header `X-Api-Key: <BRIDGE_SECRET_TOKEN>` obligatoire sur chaque appel
- Si manquant/incorrect → `{success:false, error:"unauthorized"}` + HTTP 403
- Token = chaîne random 32-64 chars (UUID ou similaire)
- Stocké en :
  - **Vercel** : env var `BRIDGE_API_KEY`
  - **OVH** : `config_secrets.php` (NON versionné, ignoré par Git)

---

## 8.quater — Architecture cible documentée (Étape 3 réalisée le 17 avril) ⚡

**Voir le document dédié `ARCHITECTURE_CIBLE.md`** pour le blueprint complet du nouveau système Twelvy + Bridge PHP + Up2Pay.

### Résumé en 5 points
1. **4 briques** : Next.js (Vercel) ↔ bridge.php (OVH) ↔ ipn.php (OVH) + retour.php (OVH) ↔ Up2Pay
2. **4 tables actives** au paiement : `stagiaire`, `order_stage`, `archive_inscriptions`, `stage` (+ `tracking_payment_error_code` sur erreur)
3. **3 mécanismes cryptographiques** : HMAC-SHA-512 (Twelvy → Up2Pay), RSA-SHA1 (Up2Pay → IPN), X-Api-Key (Next.js → bridge)
4. **Idempotence locked** : check `status='inscrit' AND numappel/numtrans non vides` avant toute action dans `ipn.php`
5. **Coexistence PSP** : zéro changement côté PSP, écritures parallèles dans la même base MySQL

### Décisions verrouillées (depuis ARCHITECTURE_CIBLE.md)
- Mode iFrame ✅
- Bridge à `https://api.twelvy.net/bridge.php` ✅
- IPN à `https://api.twelvy.net/ipn.php` ✅
- Retour à `https://api.twelvy.net/retour.php` ✅
- HMAC uniquement côté PHP ✅
- Préfixe référence : à valider Kader (CFPSP_ recommandé)
- Channel remboursement : à valider Kader (SEPA manuel recommandé pour Phase 1)

### Plan rollback en 3 options (cf. ARCHITECTURE_CIBLE.md §9)
1. Git tag `payment-form-custom-backup-2026-04-16` → revert formulaire custom (5 min)
2. Redirect Twelvy → PSP en attendant fix (10 min)
3. Suppression scripts OVH → 404 explicite (5 min)

---

## 8.ter — Audit BDD live (Étape 1 réalisée le 16 avril) ⚡

**Voir le document dédié `STAGIAIRE_AUDIT.md`** pour le détail complet (1500+ lignes).

### Faits clés
- BDD live `khapmaitpsp` : **315 tables** (les dumps locaux du 17 février ne contenaient que 4 tables — totalement obsolètes)
- MySQL 8.4.8 + PHP 5.6.40 sur OVH cluster115
- Table `stagiaire` : **163 colonnes**, 50 005 lignes
- 4 tables critiques pour Up2Pay : `stagiaire`, `transaction` (63 247 rows), `order_stage` (138 936 rows), `archive_inscriptions` (98 980 rows)
- 3 statuts effectifs : `inscrit` (24 548), `supprime` (25 452), `pre-inscrit` (4)

### Le contrat "paiement OK" : 5 écritures SQL
À l'IPN succès, PSP exécute 5 écritures :
1. `UPDATE transaction SET type_paiement='CB_OK', autorisation=…, paiement_interne=1`
2. `UPDATE order_stage SET is_paid=TRUE, reference_order='CFPSP_xxx', num_suivi=…`
3. `UPDATE stagiaire SET status='inscrit', numappel=…, numtrans=…, date_inscription=…, facture_num=numSuivi-1000, …`
4. `INSERT INTO archive_inscriptions (id_stagiaire, id_stage, id_membre)`
5. `UPDATE stage SET nb_places_allouees=…, nb_inscrits=…` (recalcul COUNT, idempotent par design)

### Idempotence
Avant les 5 écritures, vérifier `status='inscrit' AND numappel != '' AND numtrans != ''` → SKIP si déjà payé.

### 10 décisions à clarifier avec Kader
Voir `STAGIAIRE_AUDIT.md` Section I (10 questions critiques détaillées).

### Méthodologie
Script PHP read-only `_audit_temp.php` uploadé via FTP, exécuté une fois, **supprimé immédiatement** (404 confirmé). Aucune modification de données. Voir `STAGIAIRE_AUDIT.md` Section J pour le protocole complet.

---

## 8.bis — État actuel : un mini-bridge existe déjà ⚠️ (découvert le 15 avril)

**Important** : contrairement à ce que disait la première rédaction de ce doc, **un mini-bridge existe déjà** sur `api.twelvy.net`. Il s'appelle `stagiaire-create.php` et il fait déjà fonctionner le pattern Vercel → PHP OVH → MySQL.

### Comment ça marche aujourd'hui

```
Formulaire inscription Next.js
    ↓ POST
Next.js route /api/stagiaire/create
    ↓ proxy fetch
https://api.twelvy.net/stagiaire-create.php
    ↓ PDO
MySQL khapmaitpsp / table `stagiaire`
    INSERT avec status='pre-inscrit'
    ↓
Renvoie {success, stagiaire_id, booking_reference}
```

Fichier source : `php/stagiaire-create.php` (151 lignes).
Route Next.js proxy : `app/api/stagiaire/create/route.ts`.

### Comparaison existant vs cible

| Capacité | `stagiaire-create.php` (actuel) | `bridge.php` (cible) |
|----------|---------------------------------|----------------------|
| Créer un stagiaire en BDD | ✅ Oui | ✅ Oui (à reprendre) |
| Routeur d'actions multiples | ❌ Non, fait UNE seule chose | ✅ Oui (`?action=...`) |
| Préparer params Up2Pay + HMAC | ❌ Non | ✅ Oui |
| Lire statut paiement | ❌ Non | ✅ Oui |
| Sécurisé par X-Api-Key | ❌ Non | ✅ Oui |
| CORS restrictif | ❌ Non — `Access-Control-Allow-Origin: *` | ✅ Limité à `https://www.twelvy.net` |
| Mot de passe MySQL en clair | ⚠️ **Oui** (`Lretouiva1226` ligne 66) | À déplacer dans `config_secrets.php` |
| Format JSON `{success, data, error}` | ⚠️ Partiel | ✅ Standard |

### Stratégie : Option 1 (recommandée par le cahier)

**Étendre `stagiaire-create.php` en `bridge.php` complet**, plutôt que créer un fichier en parallèle. Le code existant de création stagiaire devient simplement l'action `create_or_update_prospect`. On ajoute les autres actions (`prepare_payment`, `get_stagiaire_status`, `ping`).

### À corriger en passant (3 problèmes de sécurité)

Quand on touchera à ce fichier, **3 corrections obligatoires** :

1. **Mot de passe MySQL en clair dans le code**
   - Ligne 66 de `stagiaire-create.php` : `'Lretouiva1226'` lisible par quiconque accède au fichier source
   - **Fix** : créer `config_secrets.php` (non versionné, dans `.gitignore`) qui contient les credentials MySQL ; faire un `require_once` dans bridge.php
   - Mettre à jour `MEMORY.md` pour ne PAS commiter cette valeur (déjà présente actuellement)

2. **CORS ouvert à tous (`Access-Control-Allow-Origin: *`)**
   - N'importe quel site malveillant peut appeler `stagiaire-create.php` depuis le navigateur d'un visiteur et créer des faux stagiaires
   - **Fix** : remplacer par `Access-Control-Allow-Origin: https://www.twelvy.net` (et éventuellement le domaine de dev)
   - Ne pas mettre `*` ET `Allow-Credentials: true` ensemble (interdit par les navigateurs)

3. **Aucune vérification d'API key**
   - Pas de header `X-Api-Key` exigé → spam de la BDD trivial pour qui découvre l'URL
   - **Fix** : ajouter en début de bridge.php :
     ```php
     $apiKey = isset($_SERVER['HTTP_X_API_KEY']) ? $_SERVER['HTTP_X_API_KEY'] : '';
     if (!hash_equals(BRIDGE_SECRET_TOKEN, $apiKey)) {
         http_response_code(403);
         echo json_encode(array('success' => false, 'error' => 'unauthorized'));
         exit;
     }
     ```
   - Côté Next.js : la route `/api/stagiaire/create/route.ts` doit ajouter ce header dans son `fetch` vers OVH

### Bonne nouvelle

Le pattern technique (Vercel → OVH PHP → MySQL) **fonctionne déjà**. On n'invente rien d'architectural. On **étend** un fichier qui marche, en le renommant et en ajoutant des actions + de la sécurité. Beaucoup moins risqué que partir de zéro.

---

## 9. Variables d'environnement à prévoir

### Côté PHP (OVH) — `config_paiement.php` (versionné, structure)
```
UP2PAY_ENV ('test' | 'prod')

// TEST
UP2PAY_SITE_ID_TEST          = 1999887
UP2PAY_RANG_TEST             = 63
UP2PAY_IDENTIFIANT_TEST      = 222
UP2PAY_KEY_VERSION_TEST      = (à confirmer back-office)
UP2PAY_PAYMENT_URL_TEST      = https://preprod-tpeweb.up2pay.com/cgi/MYchoix_pagepaiement.cgi

// PROD
UP2PAY_SITE_ID_PROD          = 0966892
UP2PAY_RANG_PROD             = 02
UP2PAY_IDENTIFIANT_PROD      = 651027368
UP2PAY_KEY_VERSION_PROD      = (à confirmer back-office)
UP2PAY_PAYMENT_URL_PROD      = https://tpeweb.up2pay.com/cgi/MYchoix_pagepaiement.cgi

// COMMUN
UP2PAY_NORMAL_RETURN_URL     = https://www.prostagespermis.fr/api/retour.php
UP2PAY_AUTOMATIC_RESPONSE_URL = https://www.prostagespermis.fr/api/ipn.php
```

### Côté PHP (OVH) — `config_secrets.php` (NON versionné, .gitignore)
```
UP2PAY_HMAC_KEY_TEST         = 0123456789ABCDEF... (128 hex)
UP2PAY_HMAC_KEY_PROD         = 78f9db5d... (128 hex, déjà existante dans PSP)
BRIDGE_SECRET_TOKEN          = (UUID à générer)
```

### Côté Next.js (Vercel) — env vars
```
BRIDGE_URL_DEV               = https://dev.prostagespermis.com/api/bridge.php
BRIDGE_URL_PROD              = https://www.prostagespermis.fr/api/bridge.php
BRIDGE_API_KEY               = (même valeur que BRIDGE_SECRET_TOKEN)
```

### Côté Next.js — `.env.local` (NON commité)
```
BRIDGE_URL=https://dev.prostagespermis.com/api/bridge.php
BRIDGE_API_KEY=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

---

## 10. Code PSP existant — cartographie complète (mode A actuel)

Recherché par agent dans `/Volumes/Crucial X9/PROSTAGES/www_2/` et `www_3/`.

### Configuration & gateway
- `www_2/src/payment/E_Transaction/E_TransactionConfig.php` (lignes 11-74) — credentials + URLs + switch DEBUG
- `www_2/src/payment/E_Transaction/E_TransactionConfig_2023.php` — variante 2023

### HMAC + autorisation (mode Direct PPPS)
- `www_2/src/payment/E_Transaction/E_TransactionPayment.php`
  - lignes 143-199 : autorisation TYPE=00001 (signature HMAC + cURL POST)
  - lignes 257-311 : confirmation débit TYPE=00002
  - lignes 165-166 : `$binKey = pack("H*", $this->PBX_KEY); $hmac = strtoupper(hash_hmac('sha512', $msg, $binKey));`

### Mapping codes erreur
- `www_2/src/payment/E_Transaction/E_TransactionError.php` (lignes 5-83) — 50+ codes Up2Pay → message FR

### Endpoints retour/validation
- `www_2/src/payment/validate/validate_payment.php` (347 lignes — script principal)
  - lignes 70-77 : retrouve session via `?d2305_{session_id}`
  - lignes 129-150 : **idempotence check** (status='inscrit' && numappel/numtrans non vides)
  - lignes 163-170 : appel `validateTransaction()`
  - lignes 188-220 : handler échec (log + email échec + tracking erreur)
  - lignes 222-333 : handler succès (SMS + emails + UPDATE 5 tables + redirect)
- `www_2/src/payment/validate/validate_upsell_payment.php` — flux upsell
- `www_2/src/payment/validate/validate_product_upsell_payment.php` — flux produit

### Mise à jour BDD
- `www_2/src/payment/services/UpdateStagePaymentData.php` — orchestrateur des 5 UPDATEs
- `www_2/src/payment/repositories/PaymentRepository.php` — UPDATE `stagiaire` + `transaction`
- Tables touchées :
  1. `order_stage` — `is_paid = true`
  2. `transaction` — `type_paiement='CB_OK'`, `autorisation`, `paiement_interne=1`
  3. `stagiaire` — `status='inscrit'`, `numero_cb`, `numappel`, `numtrans`, `date_inscription`, `facture_num`, etc.
  4. `archive_inscriptions` — INSERT audit
  5. `stage` — `nb_places_allouees -= 1`, `nb_inscrits += 1`

### Email
- `www_2/src/payment/services/email/SendTicketPaymentEmail.php` — ticket paiement au stagiaire
- `www_2/src/payment/services/email/SendPaymentSuccessEmail.php` — orchestre les 2 ci-dessous
- `www_2/mails_v3/mail_inscription.php` — confirmation inscription stagiaire
- `www_2/mails_v3/mail_inscription_centre.php` — alerte au centre partenaire (sauf member_id=837)
- `www_2/mails_v3/mail_echec_paiement.php` — échec paiement avec lien retry
- `www_2/src/payment/emails/SendAdminTransactionSuccessInError.php` — alerte anomalie (succès Up2Pay mais message d'erreur) → contact@prostagespermis.fr

### Logs
- `www_2/src/logging/LogPayment.php`
  - `errorPaymentMessage()` → `/logs/e_transaction_error.log` + `/logs/paiements_erreurs.txt`
  - `successPaymentMessage()` → `/logs/e_transaction_success.log` + `/logs/paiements_reussi.txt`

### Tracking erreurs DB
- `www_2/src/payment/repositories/TrackingUserPaymentErrorCode.php` — INSERT dans `tracking_payment_error_code`

### Cron
- `www_2/planificateur_tache/up2pay/cron_status_payment.php` — vérifie statut Up2Pay sur stagiaires récents (2 jours) avec hash auth `?hash=6e395m74ng`

### Legacy / référence historique (mode B utilisé avant 2023)
- `PSP 3/backup code cb/pbx_repondre_a.php` — **vrai script IPN historique** (mode hosted page) → **lecture obligatoire avant écriture du nouvel IPN**
- `PSP 3/backup code cb/lien_cb.php` — préparation form Up2Pay historique

---

## 11. Plan d'intégration — 10 étapes du cahier des charges

> Source : `Cahier des charges up2pay.pages` (37 pages). Document complet lu et digéré le 15 avril 2026.

| Étape | Objectif | Livrable | Durée estimée |
|-------|----------|----------|---------------|
| **1** | Auditer table `stagiaire` | Doc texte 1 page : colonnes liées paiement + leur rôle métier | 1-2h |
| **2** | Cartographier le flux paiement PHP actuel | Schéma texte du flux, fichier par fichier (déjà fait à 80% par agent — cf §10) | 30 min |
| **3** | Designer architecture cible (Next.js + Bridge + Up2Pay) | Doc court : rôles + nouveau "film" + choix mode iFrame vs Direct | 1h |
| **4** | Préparer config (TEST + PROD séparés) | `config_paiement.php` squelette + `config_secrets.php` (vide) + `.env.local` exemple | 1h |
| **5** | Créer `bridge.php` sécurisé | Bridge avec router d'actions + ping + auth X-Api-Key + JSON standardisé | 2-3h |
| **6** | Bétonner scripts retour + IPN PHP + mapping erreurs | IPN robuste avec idempotence + catégories d'erreurs UX | 4-6h |
| **7** | Brancher formulaire Next.js (Coordonnées + CB) sur bridge | Step 1 + Step 2 fonctionnels en local | 4-6h |
| **8** | Gérer retour paiement (success/échec) côté Next.js | Page `/confirmation` avec polling + page `/formulaire?result=error` | 3-4h |
| **9** | Tests bout-en-bout en sandbox + comparaison side-by-side avec ancien tunnel | Checklist 4+ scénarios validés + diff caractère par caractère vs ancien stagiaire | 4-6h |
| **10** | Bascule production + monitoring serré | Bascule PROD + 1-3 paiements pilote + plan rollback + monitoring premiers jours | 2-4h + 1 semaine surveillance |

**Total estimé** : 25-40h de dev + 1 semaine monitoring.

---

## 12. Catégories d'erreurs UX (étape 6.6)

Mapping codes Up2Pay → catégorie + message côté front. Source : `errors.csv` (76 codes mappés).

| Catégorie | Codes Up2Pay typiques | Message UX |
|-----------|----------------------|------------|
| `erreur_saisie_carte` | 00114, 00007, 00020 | "Numéro/date/CVV erroné. Vérifiez vos infos bancaires." |
| `refus_banque` | 00021, 00022, 00151 | "Votre banque n'a pas autorisé le paiement. Réessayez avec une autre carte." |
| `probleme_3ds` | (codes 3DS spécifiques) | "Échec de l'authentification 3D Secure. Réessayez." |
| `erreur_technique` | 00001, plateforme down | "Plateforme momentanément indisponible. Réessayez plus tard." |
| `en_attente` | (rare, status intermédiaire) | "Paiement en cours de validation. Patientez quelques instants." |

→ Stockage : colonnes `payment_error_code` + `payment_error_message` dans `stagiaire` (à ajouter si pas déjà là), ou table log dédiée.
→ Next.js ne voit JAMAIS les codes Up2Pay bruts.

Pour le mapping complet code → message, voir `errors.csv` (76 codes traduits avec messages personnalisés).

---

## 13. Tests obligatoires (étape 9)

### Pré-requis
- Up2Pay en mode TEST (clé HMAC TEST + URL preprod)
- Bridge fonctionne (étapes 5-8 OK)
- IPN/retour PHP joignables par Up2Pay → **localhost ne marche pas** → utiliser :
  - **Sous-domaine OVH** dev (ex: `dev.prostagespermis.com`) — recommandé
  - OU `ngrok`/`localtunnel` pour exposer le bridge local

### 4 scénarios minimum
1. **Paiement OK (cas nominal)** — carte test OK → vérifier front (récap) + BDD (statut "payé", date, montant, n° transaction)
2. **Paiement refusé** — carte test refusée → vérifier front (retour formulaire bloc CB ouvert + message) + BDD (statut "refusé", code/message cohérent)
3. **Erreur carte / 3DS** — scénarios test Up2Pay → vérifier message bloc CB (erreur saisie / 3DS) + BDD
4. **Abandon paiement** — quitter avant payer → vérifier BDD (`stagiaire` reste "prospect/en_attente", JAMAIS "payé") + front (pas de message confirmation)

### Test "side-by-side" avec ANCIEN tunnel (très important)
Pour chaque scénario principal :
- Faire l'inscription sur le SITE PHP ANCIEN → exporter ligne `stagiaire` correspondante
- Faire la MÊME inscription sur le NOUVEAU site Next.js + bridge → exporter ligne `stagiaire`
- Diff caractère par caractère via VS Code (clic droit → "Compare Selected")
- Champs à comparer : statut, dates/formats, montants, codes, NULL vs `''`
- **Objectif** : les deux lignes doivent être identiques caractère par caractère

### Vérifications métier en plus de `stagiaire`
- **Simpligestion** : stage apparaît correctement, statut/paiement cohérents
- **Espace centre** (si utilisé) : stagiaire remonte bien
- **Espace stagiaire / mails** (si déjà branchés) : rien de cassé

---

## 14. Bascule production (étape 10)

### Pré-requis avant bascule
- Tous scénarios étape 9 OK en TEST
- Diff "ancien vs nouveau stagiaire" propre
- Polling page confirmation fonctionne
- Validation Kader explicite : UX, BDD, Simpligestion, espaces

### Bascule
1. **Up2Pay PROD** : récupérer credentials PROD (déjà connus, cf §3) + vérifier dans le back-office Up2Pay que les URLs `PBX_REPONDRE_A` / `PBX_EFFECTUE` pointent bien sur les scripts PROD (pas TEST)
2. **Bridge PHP PROD** : vérifier qu'il pointe sur la BDD PROD + `BRIDGE_SECRET_TOKEN` PROD en place dans `config_secrets.php`
3. **Next.js PROD** (Vercel) :
   - `BRIDGE_URL` = URL bridge prod (`https://www.prostagespermis.fr/api/bridge.php`)
   - `BRIDGE_API_KEY` = même token que côté PHP PROD
   - Déployer la version avec nouveau tunnel activé
4. Vérifier sur le site PROD (sans payer) : formulaire s'affiche, pas de 404/CORS/mixed-content

### Plan rollback
À clarifier avec Kader **avant** la bascule :
- Garder l'ancienne page formulaire PHP accessible derrière une autre URL
- OU switch routing simple (repointer le lien "Réserver" vers l'ancien)
- En cas de taux d'échec anormal ou incohérences BDD → **désactiver le nouveau tunnel rapidement**

### Paiements pilote
1-3 vrais paiements (petits montants si possible) avec Kader pour valider :
- UX : formulaire → bloc CB → page CB Up2Pay → page confirmation
- BDD `stagiaire` : statut, date, montant, n° transaction, colonnes liées paiement
- Simpligestion / espaces : stagiaire apparaît, infos cohérentes
- Comparaison rapide avec une inscription ancien tunnel

### Monitoring premiers jours
**Côté technique** :
- Logs bridge : erreurs auth (X-Api-Key), erreurs DB, temps de réponse
- Logs IPN/retour : HMAC invalides, réponses d'erreur envoyées à Up2Pay
- Back-office Up2Pay : taux d'échec vs avant bascule

**Côté métier** :
- Retours support : "paiement OK mais pas de confirmation", "n'apparaît pas chez le centre"
- Retours centres : incohérences sur listes de stagiaires

**Réaction en cas de problème** :
- Noter date/heure, `id_stagiaire`, type de problème
- Si bug isolé → corriger
- Si problème systémique → discuter avec Kader d'un retour temporaire à l'ancien tunnel

---

## 15. ⚠️ Liste exhaustive des inconnues / décisions à prendre

Avant de commencer à coder, **ces points doivent être clarifiés avec Kader** :

| # | Question | Impact |
|---|----------|--------|
| 1 | **Mode A (Direct PPPS) vs Mode B (Hosted iFrame)** ? Le PSP actuel est en A, le cahier des charges dit B. | Détermine TOUT le code à écrire. |
| 2 | Hébergement du bridge : sur `prostagespermis.fr` (OVH actuel) ou nouveau hosting Twelvy ? | Détermine les URLs de bridge et de retour. |
| 3 | Domaine de DEV pour les tests (Up2Pay ne peut pas appeler localhost) : créer `dev.prostagespermis.com` sur OVH ? | Bloque l'étape 9 (tests bout-en-bout). |
| 4 | BDD : on garde `stagiaire` PSP intacte (recommandé) ou on crée une table Twelvy parallèle ? | Cahier dit "garde `stagiaire`". À confirmer. |
| 5 | Email infra : on réutilise les `mail_inscription.php` etc. de PSP, ou on construit côté Twelvy avec Resend/SendGrid ? | Cahier dit "rejouer à l'identique". À confirmer. |
| 6 | Clé HMAC TEST : on prend celle du back-office Up2Pay (Kader) ou la dummy publique Verifone (`0123456789ABCDEF...`) ? | Si on prend la dummy → il faut s'assurer que les tests passent quand même (la sandbox Verifone l'accepte). |
| 7 | Plan rollback : quelle est la stratégie exacte ? Garder l'ancien `/es/inscriptionv2_3ds.php` accessible ? | Sécurité bascule. |
| 8 | Qui a accès au back-office Up2Pay pour vérifier/modifier les URLs `PBX_REPONDRE_A` ? | Bloque la bascule prod. |
| 9 | Y a-t-il un environnement de **staging Up2Pay** distinct (back-office propre) ou seulement TEST/PROD ? | Pour valider les changements de config sans impacter la prod. |
| 10 | Le `cron_status_payment.php` actuel doit-il continuer à tourner sur l'ancien hébergement, ou être migré ? | Couvre les cas où l'IPN n'arrive pas. À garder. |

---

## 16. Liens & ressources

### Documentation officielle Up2Pay
- Portail marchand (login requis) : https://www.ca-moncommerce.com/espace-client-mon-commerce/
- Doc Up2Pay (login-walled) : https://www.ca-moncommerce.com/espace-client-mon-commerce/up2pay-e-transactions/ma-documentation/
- Tests d'intégration : https://www.ca-moncommerce.com/espace-client-mon-commerce/up2pay-e-transactions/ma-documentation/realisation-des-tests-dintegration/

### Documentation Paybox/Verifone (autoritative, publique)
- Manuel d'intégration v8.0 FR (PDF) : https://www1.paybox.com/wp-content/uploads/2017/08/ManuelIntegrationVerifone_PayboxSystem_V8.0_FR.pdf
- Manuel d'intégration v8.0 EN (PDF) : https://www1.paybox.com/wp-content/uploads/2017/08/ManuelIntegrationVerifone_PayboxSystem_V8.0_EN.pdf
- Paramètres de test v8.1 : https://www.paybox.com/wp-content/uploads/2022/01/ParametresTestVerifone_Paybox_V8.1_FR-1.pdf
- Dictionnaire de données (tous les PBX_*) : https://www.paybox.com/espace-integrateur-documentation/dictionnaire-des-donnees/paybox-system/
- Gestion de la réponse / IPN : https://www.paybox.com/espace-integrateur-documentation/la-solution-paybox-system/gestion-de-la-reponse/
- Plateformes de test : https://www.paybox.com/espace-integrateur-documentation/les-plateformes-de-test/
- Clé publique Up2Pay (vérif IPN) : https://www1.paybox.com/wp-content/uploads/2014/03/pubkey.pem

### Implémentations de référence (open-source)
- **Crédit Agricole GitHub officiel** (modules CMS) : https://github.com/E-Transactions-CA
- **WooCommerce E-Transactions** (référence WordPress) : https://wordpress.org/plugins/e-transactions-wc/
- **LexikPayboxBundle** (Symfony, mature) : https://github.com/lexik/LexikPayboxBundle
- **highcanfly-club/up2pay** (TypeScript 3DS2) : https://github.com/highcanfly-club/up2pay
- **wp-paybox/controller-ipn.php** (exemple IPN) : https://github.com/drzraf/wp-paybox/blob/master/controller-ipn.php

### Local — fichiers du projet
- Cahier des charges complet : `/Volumes/Crucial X9/PROSTAGES/TWELVY_LOCAL_PHP/Cahier des charges up2pay.pages`
- Liste codes erreur : `/Volumes/Crucial X9/PROSTAGES/TWELVY_LOCAL_PHP/errors.csv`
- Code PSP référence : `/Volumes/Crucial X9/PROSTAGES/www_2/src/payment/`
- Code PSP IPN historique (mode hosted) : `/Volumes/Crucial X9/PROSTAGES/PSP 3/backup code cb/`

---

## 17. Glossaire

| Terme | Définition |
|-------|-----------|
| **Up2Pay** | Branding actuel (depuis ~2020) du gateway de paiement de Crédit Agricole Mon Commerce. |
| **E-Transactions** | Ancien nom du même produit. Toujours utilisé en interne et dans la doc legacy. |
| **Paybox** | Nom historique du produit (avant rachat par Crédit Agricole puis Verifone). Doc technique toujours publique sous ce nom. |
| **HPP** (Hosted Payment Page) | Page de paiement hébergée chez le gateway. Le marchand n'héberge jamais le formulaire CB. |
| **HMAC** | Hash-based Message Authentication Code. Up2Pay utilise HMAC-SHA-512 pour signer les params sortants. |
| **IPN** (Instant Payment Notification) | Notification serveur-à-serveur envoyée par Up2Pay au marchand après le paiement. **Source de vérité.** |
| **3DS / 3DS2** | 3-D Secure : authentification forte de l'acheteur (SMS, biométrie, app banque). Obligatoire en Europe (PSD2). |
| **Idempotence** | Propriété d'une opération qui produit le même résultat qu'elle soit exécutée 1 ou N fois. Critique pour l'IPN car Up2Pay peut retry. |
| **Bridge PHP** | Script PHP unique côté OVH qui sert de passerelle entre Next.js (Vercel) et la BDD MySQL + scripts Up2Pay. |
| **PBX_*** | Préfixe de tous les paramètres du protocole Paybox/Up2Pay (ex: `PBX_SITE`, `PBX_TOTAL`, `PBX_HMAC`). |
| **stagiaire** | Table MySQL principale de PSP qui contient les inscriptions clients. C'est elle qui doit être mise à jour à l'IPN. |
| **Simpligestion** | Outil métier interne PSP qui lit la table `stagiaire` pour la gestion centres/compta. |

---

**Document rédigé le 15 avril 2026 par session Claude Code.**
Sources : Cahier des charges (37 pages), code source PSP (`www_2/src/payment/`), documentation publique Paybox/Verifone v8.0/v8.1, recherche web Up2Pay/E-Transactions/Paybox.

---

# 📚 Explication pédagogique pour débutants (session 15 avril)

Cette section reprend les 5 sous-catégories expliquées en session "comme à un enfant". Si tu lis ce doc pour la première fois, commence par ici avant d'aller dans les sections techniques au-dessus.

---

## 📦 Sous-catégorie 1 — C'est quoi Up2Pay et pourquoi on en a besoin

### Le problème de base
En tant que commerçant, tu n'as pas le droit de prendre directement le numéro de carte d'un client et d'aller demander à sa banque "envoie-moi 219 €". Tu serais responsable en cas de vol, fraude, fuite. Il te faut un **intermédiaire** autorisé légalement à manipuler les cartes.

### L'analogie du magasin physique
Dans une boutique, le caissier ne tape pas ton numéro de carte sur sa caisse. Il a un **petit boîtier** (le terminal de paiement) fourni par la banque. Tu insères ta carte, tu tapes ton code, le boîtier appelle la banque tout seul, et affiche "ACCEPTÉ" ou "REFUSÉ". Le caissier ne voit jamais ton code.

> **Up2Pay c'est exactement ce boîtier, mais pour les sites internet.**

### Pourquoi 3 noms (Up2Pay / E-Transactions / Paybox)
- **Paybox System** (années 1990) — nom original
- **E-Transactions** — nom intermédiaire chez Crédit Agricole
- **Up2Pay e-Transactions** — nom actuel (depuis ~2020) sous Crédit Agricole Mon Commerce

**C'est exactement le même produit.** Protocole inchangé.

### Les 5 acteurs d'un paiement
1. Le client (acheteur)
2. Toi (commerçant — Twelvy / AM FORMATION)
3. La banque du client (BNP, Société Générale, etc.)
4. Ta banque (Crédit Agricole)
5. Up2Pay (l'intermédiaire qui orchestre tout)

### Pourquoi Up2Pay et pas Stripe/PayPal
PSP a déjà un contrat signé avec Up2Pay sous "AM FORMATION", contrat N°0966892.02. Changer = nouveau contrat, transition risquée, nouvelle compta. On garde le même → **même argent sur le même compte AM FORMATION**.

### Le trajet de l'argent
```
Compte client (BNP)
  ↓ (Up2Pay déclenche le prélèvement)
Up2Pay (garde qq jours)
  ↓ (versement périodique)
Compte AM FORMATION au Crédit Agricole
```
Up2Pay prélève au passage une commission (quelques centimes + petit %).

---

## 🛠️ Sous-catégorie 2 — Les 2 façons d'intégrer Up2Pay

### Mode 1 — "Direct PPPS" (PSP aujourd'hui)
La carte est tapée dans un formulaire de TON serveur, qui l'envoie ensuite à Up2Pay en cURL backend. Réponse immédiate. **Le serveur PSP touche la carte.**

Endpoint : `https://ppps.paybox.com/PPPS.php`

### Mode 2 — "Hébergé iFrame" (cible Twelvy)
La carte est tapée dans une fenêtre Up2Pay embarquée dans la page. **Ton serveur ne voit jamais la carte.** Up2Pay te prévient ensuite (via IPN) que le paiement est passé.

Endpoint : `https://tpeweb.up2pay.com/cgi/MYchoix_pagepaiement.cgi`

### Pourquoi c'est critique : la règle PCI-DSS
PCI-DSS = réglementation bancaire mondiale. **Si tu touches la carte → lourdes obligations** (audit, coffre numérique, assurance). **Si tu ne la touches jamais → tu es hors scope**.

### Pourquoi on change de mode pour Twelvy
- Twelvy tourne sur **Vercel**, hébergeur moderne pas conçu pour manipuler des cartes
- Mode 1 sur Vercel = audit PCI obligatoire, ingérable
- Mode 2 = aucune obligation PCI, infrastructure simplifiée

### Bonne nouvelle : PSP a DÉJÀ utilisé le mode 2 dans le passé
Vieux code disponible dans `/Volumes/Crucial X9/PROSTAGES/PSP 3/backup code cb/pbx_repondre_a.php` → modèle pour écrire les nouveaux scripts Twelvy.

### Pour l'utilisateur final
Différence quasi invisible. Un formulaire CB avec numéro/date/CVV/bouton payer. Il ne sait pas si c'est "chez toi" ou "chez Up2Pay embarqué chez toi".

---

## 🏗️ Sous-catégorie 3 — Qui fait quoi dans l'architecture

### Les 3 endroits du décor

**Vercel (la "salle de restaurant")** — hébergeur américain. Fait tourner Next.js (twelvy.net). Joli, rapide, moderne, mais pas de BDD, pas de PHP, pas de secrets longue durée.

**OVH (la "cuisine + le stock")** — hébergeur français traditionnel. PHP 5.6 + MySQL. C'est là que PSP vit depuis des années. Deux sous-domaines en service :
- `prostagespermis.fr` → site PSP actuel
- `api.twelvy.net` → backend Twelvy (déjà existant, héberge `stages-geo.php`)

**Up2Pay (le "grossiste/banque")** — serveurs Crédit Agricole, externes. Tu fais des appels chez eux, ils te rappellent.

### La BDD MySQL : `stagiaire`
Table centrale avec nom, prénom, email, stage, statut paiement, numéro transaction, etc. Tout le business (Simpligestion, espace centre, compta) lit dedans. **Modifiée uniquement par PHP, jamais par Next.js.**

### Le problème Vercel ↔ OVH
Vercel ne peut pas parler directement à MySQL OVH pour 4 raisons :
1. Sécurité (ouvrir MySQL à Internet = trou)
2. Code (Next.js c'est du JS, PSP c'est du PHP)
3. Confiance (ne pas mettre les creds MySQL dans un hébergeur tiers)
4. HMAC (la clé secrète ne doit jamais quitter OVH)

### La solution : bridge.php
**Un seul fichier PHP sur OVH** qui fait l'intermédiaire. Adresse : `https://api.twelvy.net/bridge.php`.

Actions exposées :
| Action | Rôle |
|--------|------|
| `ping` | Test santé (retourne "pong") |
| `create_or_update_prospect` | INSERT stagiaire, retourne ID |
| `prepare_payment` | Calcule montant + signe HMAC, retourne paymentFields |
| `get_stagiaire_status` | Lit statut (paye/refuse/en_attente) + infos récap |

### Le cadenas : X-Api-Key
Header HTTP secret exigé à chaque appel. Une longue chaîne random type `f3a8b9c2-d4e5-46f7-...`. Stocké en env var Vercel ET dans `config_secrets.php` OVH (pas dans Git).

Si mauvais ou absent → `403 Forbidden`, stop.

### Les 2 autres scripts PHP à côté
| Fichier | Rôle |
|---------|------|
| `bridge.php` | Sert Next.js pour toutes les actions courantes |
| `retour.php` | Reçoit le navigateur après paiement, redirige vers Next.js |
| `ipn.php` | Reçoit la notification serveur d'Up2Pay, **fait foi** pour le statut |

### ⚠️ Découverte importante (15 avril)
**Un mini-bridge existe déjà** : `stagiaire-create.php` sur `api.twelvy.net`. Il fait UNE seule chose (créer un stagiaire). Stratégie : **l'étendre** en bridge complet plutôt que créer un fichier parallèle. Voir section 8.bis ci-dessus pour les 3 corrections de sécurité à appliquer en passant.

---

## 🎬 Sous-catégorie 4 — Le parcours complet d'un paiement

### Acte 1 — Les coordonnées
1. Client arrive sur `/stages-recuperation-points/.../inscription`
2. Remplit le bloc 1 : civilité, nom, email, tel, adresse, CGV
3. Clique "Valider mes coordonnées"
4. Next.js appelle `/api/stagiaire/create` → proxy vers `bridge.php?action=create_or_update_prospect`
5. bridge.php fait `INSERT INTO stagiaire` avec `status='pre-inscrit'`, retourne `stagiaire_id=12345`
6. Next.js stocke l'ID, cache le bloc 1, affiche le bloc CB

### Acte 2 — Le paiement
7. Client clique "Payer 219 €"
8. Next.js appelle `bridge.php?action=prepare_payment&id=12345`
9. bridge.php construit tous les params Up2Pay (PBX_SITE, PBX_TOTAL en centimes, PBX_CMD, etc.) et les **signe avec HMAC**
10. Retourne à Next.js : `{paymentUrl, paymentFields: {PBX_SITE:..., PBX_HMAC:...}}`
11. Next.js construit un formulaire HTML caché avec tous ces champs, et **l'auto-soumet** vers l'URL Up2Pay
12. Le navigateur bascule sur la page Up2Pay (ou iFrame)
13. Client tape sa carte + 3DS → Up2Pay traite

### Acte 3 — La confirmation (2 canaux parallèles)

**Canal A (navigateur — UX seulement)** :
- Up2Pay redirige le navigateur vers `retour.php?status=ok&...`
- `retour.php` redirige vers `https://www.twelvy.net/confirmation?id=12345`
- Next.js affiche "Vérification en cours..."
- Next.js fait du **polling** : appelle `bridge.php?action=get_stagiaire_status` toutes les 2-3 secondes
- Dès que le bridge répond `status='paye'` → affiche le récap final

**Canal B (IPN serveur-à-serveur — autoritatif)** :
- Up2Pay envoie une requête HTTP directe à `ipn.php` avec la signature RSA
- `ipn.php` vérifie la signature, vérifie l'idempotence, met à jour `stagiaire`, envoie les 3 emails
- Répond HTTP 200

### Cas d'erreur
Si banque refuse : IPN reçoit `Erreur=00114`, met `status='refuse'` + stocke code/catégorie d'erreur. Navigateur redirigé vers formulaire avec `?result=error&id=12345`. Next.js rouvre le bloc CB avec message clair. Le client peut retenter sans retaper ses coordonnées.

---

## 🔐 Focus — C'est quoi la clé HMAC ?

### L'idée
HMAC = une façon de **signer un message** pour prouver (1) qu'il vient de toi, (2) que personne ne l'a modifié.

### Analogie : le sceau de cire médiéval
Le roi envoie une lettre, appose son sceau avec son anneau royal. Impossible à reproduire sans l'anneau. Le destinataire vérifie le sceau → lettre authentique + non modifiée. **L'anneau ne quitte jamais le roi.**

### Les 3 ingrédients
- **Clé secrète** : 128 chars hexa, partagée entre toi et Up2Pay, ne voyage JAMAIS
- **Message** : les params Up2Pay concaténés (`PBX_SITE=0966892&PBX_TOTAL=21900&...`)
- **Fonction** : HMAC-SHA-512 (imposée par Up2Pay)

### Processus
```php
$cleHmac = pack("H*", "78f9db5d0b421f...");  // convertir hex → binaire
$message = "PBX_SITE=0966892&PBX_TOTAL=21900&...";
$signature = strtoupper(hash_hmac('sha512', $message, $cleHmac));
// → "78E9B5A2F1D8C3E7..." (128 chars)
```

### Pourquoi c'est sécurisé
1. La clé **ne voyage jamais** sur le réseau (seule la signature est envoyée)
2. Sans la clé, **impossible de produire la bonne signature** (propriété mathématique de HMAC)
3. Toute modification du message casse la signature → Up2Pay rejette
4. `PBX_TIME` empêche de rejouer une ancienne signature (rejet si > 30 min)

### 2 clés dans notre cas
- **Clé TEST** (sandbox) : pour les tests
- **Clé PROD** (réelle) : pour les vrais paiements

La clé PROD existe déjà dans le code PSP (`www_2/.../E_TransactionConfig.php`) — à migrer vers `config_secrets.php` non versionné.

### Si la clé est volée
Impact limité (le voleur ne peut pas détourner l'argent, il va toujours sur ton compte). Mais procédure d'urgence : régénérer dans le back-office Up2Pay + remplacer dans `config_secrets.php`.

---

## 🔔 Sous-catégorie 5 — L'IPN et l'idempotence

### Pourquoi pas le navigateur
Le navigateur est non fiable :
- Onglet fermé après paiement
- Wi-Fi coupé
- Pirate qui falsifie l'URL de retour (`?status=ok` à la main)
- Navigateur plante

**Règle absolue** : seul l'IPN met `status='paye'` en BDD. Jamais le navigateur.

### L'IPN — l'appel que personne ne peut bloquer
Up2Pay serveur → ton serveur, direct, sans navigateur. Impossible à falsifier. Signé en RSA.

### RSA vs HMAC (petite différence)
- HMAC = clé symétrique (toi + Up2Pay la connaissez tous deux)
- RSA = clé asymétrique (Up2Pay garde la privée, tout le monde a la publique)

Pour l'IPN : tu télécharges la clé publique Up2Pay (fichier `pubkey.pem`), tu vérifies la signature avec.

### Le système de retry
Si ton `ipn.php` ne répond pas 200, Up2Pay **réessaie jusqu'à 24h**. Excellente sécurité contre les pannes temporaires.

### Le problème des doublons
Up2Pay peut envoyer la **même notification 2-3 fois** (timeout, retry après 500, bug réseau). Si ton code traite chaque appel naïvement :
- 2 mails de confirmation au stagiaire
- 2 notifs au centre
- 2 commissions en compta
- Stock décrémenté 2 fois

**Solution : idempotence.**

### C'est quoi l'idempotence

**"Traiter l'action 1 fois ou 10 fois → même résultat final."**

Analogie : le bouton d'ascenseur. Appuie 1 fois ou 10 fois → l'ascenseur vient **une seule fois**.

### Implémentation dans `ipn.php`

```php
$stagiaire = StagiaireRepository::findByReference($ref);

// CHECK IDEMPOTENCE
if ($stagiaire['status'] === 'inscrit'
    && !empty($stagiaire['numappel'])
    && !empty($stagiaire['numtrans'])) {
    // DÉJÀ TRAITÉ → no-op
    error_log("[IPN] Doublon détecté pour $ref");
    http_response_code(200);
    echo 'already paid';
    exit;
}

// Sinon, 1er passage → traiter
UPDATE stagiaire SET status='inscrit', numappel=..., numtrans=... WHERE id=$id;
send_ticket_email(...);
send_center_notification(...);
send_admin_copy(...);

http_response_code(200);
echo 'OK';
```

**Important** : on répond toujours **200** même sur un doublon (sinon Up2Pay réessaie inutilement).

### Contrat exact avec Up2Pay
| Ta réponse | Up2Pay fait |
|------------|-------------|
| `HTTP 200` | Arrête ✅ |
| `HTTP 403` (signature bad) | Arrête aussi |
| `HTTP 500` | Réessaie plus tard |
| Timeout > 10s | Réessaie plus tard |

### Cas particuliers de timing
- **IPN avant navigateur** (rare) : quand Next.js charge `/confirmation`, status est déjà "paye", récap immédiat
- **Navigateur avant IPN** (fréquent) : status="en_attente" au début → polling Next.js toutes les 2-3s jusqu'à "paye"

---

## 🎯 Synthèse visuelle du mental model

```
┌─ 5 ACTEURS ─────────────────────────────────────┐
│ Client, Toi, Banque client, Ta banque, Up2Pay  │
└─────────────────────────────────────────────────┘

┌─ 2 MODES D'INTÉGRATION ─────────────────────────┐
│ Direct PPPS (PSP auj.) vs Hébergé iFrame (cible)│
└─────────────────────────────────────────────────┘

┌─ 3 ENDROITS OÙ VIT LE CODE ─────────────────────┐
│ Vercel (UI) ↔ OVH (logique+BDD) ↔ Up2Pay (banque)│
└─────────────────────────────────────────────────┘

┌─ 3 SCRIPTS PHP À ÉCRIRE/ÉTENDRE ────────────────┐
│ bridge.php │ retour.php │ ipn.php              │
│ (Next→BDD) │ (browser)  │ (serveur, VÉRITÉ)    │
└─────────────────────────────────────────────────┘

┌─ 2 SIGNATURES CRYPTOGRAPHIQUES ─────────────────┐
│ HMAC-SHA-512 (toi → Up2Pay, clé symétrique)    │
│ RSA-SHA1     (Up2Pay → toi, clé asymétrique)   │
└─────────────────────────────────────────────────┘

┌─ 1 RÈGLE D'OR ──────────────────────────────────┐
│ IDEMPOTENCE : check "déjà payé ?" avant d'agir │
└─────────────────────────────────────────────────┘
```

---

**Fin de l'explication pédagogique. Si tu as compris cette section, tu as tout le mental model dont tu as besoin pour attaquer le code.**
