# ARCHITECTURE_CIBLE.md — Twelvy × Up2Pay (Étape 3 du plan)

> **Livrable de l'Étape 3 du cahier des charges** — designer l'architecture cible "Next.js + Bridge PHP + Up2Pay" avant d'écrire la moindre ligne de code.
>
> **Statut** : draft initial rédigé le 17 avril 2026. **À valider par Kader** avant de passer à l'Étape 4 (config).
>
> **Sources** : `UP2PAY.md` (master technique), `STAGIAIRE_AUDIT.md` (audit BDD live), `PSP_FLOW_MOVIE.md` (flux PSP actuel analysé), Cahier des charges Up2Pay 37 pages.

---

## 0. TL;DR (résumé exécutif)

- **UI** : Next.js sur Vercel, formulaire 2 étapes (coordonnées → paiement iFrame Up2Pay).
- **Bridge PHP** : `api.twelvy.net/bridge.php` sur OVH, protégé par `X-Api-Key`, passerelle Next.js ↔ MySQL.
- **IPN PHP** : `api.twelvy.net/ipn.php` sur OVH, reçoit la notification Up2Pay, vérifie RSA, met à jour la BDD, envoie les emails. **Source de vérité du paiement.**
- **Retour PHP** : `api.twelvy.net/retour.php` sur OVH, reçoit le navigateur après paiement, redirige vers la page Next.js appropriée.
- **4 tables BDD touchées au paiement OK** : `stagiaire`, `order_stage`, `archive_inscriptions`, `stage`. **1 table supplémentaire sur échec** : `tracking_payment_error_code`.
- **Coexistence PSP** : aucun changement à PSP pendant la construction. Les deux flux écrivent dans la même base MySQL sans conflit.
- **Rollback** : le formulaire custom Twelvy actuel est préservé via git tag `payment-form-custom-backup-2026-04-16` et dossier `_backup_payment_form_2026-04-16/`.

---

## 1. Les 4 briques de l'architecture

### 1.1 Next.js (Vercel) — la couche UI

**Rôle** : présentation uniquement. Affiche le formulaire d'inscription, le bloc CB, la page de confirmation, la page d'erreur. Fait des appels HTTP sécurisés au bridge PHP.

**Ne fait JAMAIS** :
- Connexion directe à MySQL OVH
- Calcul de HMAC
- Stockage de secrets long-terme
- Manipulation de numéros de carte bancaire

**Hébergement** : Vercel (région européenne).
**URL publique** : `https://www.twelvy.net/stages-recuperation-points/.../inscription`
**Langage** : TypeScript (Next.js 15 App Router).
**Dépendances sensibles** : `BRIDGE_URL` et `BRIDGE_API_KEY` injectées via env vars Vercel.

### 1.2 Bridge PHP (OVH) — la passerelle

**Rôle** : point d'entrée unique du backend pour Next.js. Traduit les requêtes HTTP JSON en queries MySQL + appels Up2Pay.

**Actions exposées** :
| Action | Déclenchée par | Effet |
|--------|---------------|-------|
| `ping` | Test de santé | Retourne `{success:true, data:{message:"pong"}}` |
| `create_or_update_prospect` | Validation Step 1 Coordonnées | INSERT/UPDATE `stagiaire` avec `status='pre-inscrit'`, retourne `stagiaire_id` |
| `prepare_payment` | Clic "Payer" Step 2 | Construit tous les `PBX_*`, signe HMAC-SHA-512, retourne `paymentFields` |
| `get_stagiaire_status` | Page confirmation Next.js | Lit statut courant, retourne `{status, errorCategory, errorMessage, stagiaire_recap}` |

**Fichier unique** : `/www/api/bridge.php` sur OVH (qui devient `https://api.twelvy.net/bridge.php`).
**Sécurité** : header `X-Api-Key: <BRIDGE_SECRET_TOKEN>` obligatoire. Si absent/incorrect → HTTP 403, stop.
**PHP 5.6 compatible obligatoire** (contrainte OVH khapmait cluster115).

### 1.3 IPN PHP (OVH) — le source de vérité

**Rôle** : reçoit la notification server-to-server d'Up2Pay après le paiement. **Seul endroit où le statut `paye/refuse` est écrit en base.**

**Flux logique** :
1. Reçoit POST de Up2Pay (params dans body ou query string)
2. Vérifie la signature RSA avec la clé publique Paybox (`pubkey.pem`)
3. **Check idempotence** : si `stagiaire.status='inscrit' AND numappel,numtrans remplis` → SKIP, répondre `HTTP 200 already paid`
4. Si premier passage :
   - UPDATE `stagiaire` (15 colonnes)
   - UPDATE `order_stage` (is_paid=1, reference, num_suivi)
   - INSERT `archive_inscriptions`
   - UPDATE `stage` (nb_places_allouees, nb_inscrits)
   - Envoie 3 emails (stagiaire, centre, admin copy)
5. Répond `HTTP 200 OK` (sinon Up2Pay retry jusqu'à 24h)
6. Sur échec paiement :
   - UPDATE `stagiaire.up2pay_code_error`
   - INSERT `tracking_payment_error_code`
   - `status` reste à `pre-inscrit` (le client peut retenter)

**Fichier** : `/www/api/ipn.php` sur OVH (= `https://api.twelvy.net/ipn.php`).
**Sécurité** : vérification RSA obligatoire avant tout. Aucun secret X-Api-Key ici (Up2Pay ne peut pas en envoyer).

### 1.4 Retour PHP (OVH) — le router navigateur

**Rôle** : micro-script qui reçoit la redirection navigateur Up2Pay après paiement et route le client vers la bonne page Next.js.

**Flux logique** :
1. Reçoit GET/POST de Up2Pay (navigateur)
2. Lit `?status=ok|refuse|annule` depuis l'URL
3. Récupère l'`id_stagiaire` depuis la query string
4. Redirige vers `https://www.twelvy.net/confirmation?id=12345` (succès) OU `https://www.twelvy.net/inscription/...?id=12345&result=error` (échec)

**Ne fait PAS** : mise à jour BDD, vérification signature, envoi d'email. Ces actions sont exclusivement à `ipn.php`.

**Fichier** : `/www/api/retour.php` sur OVH.

---

## 2. Le "nouveau film" du paiement (flow movie cible)

Basé sur le PSP_FLOW_MOVIE.md (flux legacy) mais adapté à l'architecture iFrame cible.

```
ACTE 1 — Coordonnées (Step 1)
────────────────────────────────────────────────────────────────

STEP 1  [Next.js] Client arrive sur /stages-recuperation-points/.../inscription
STEP 2  [Next.js] Remplit le formulaire bloc 1 (civilité, nom, email, tel, CGV)
STEP 3  [Next.js] Clique "Valider mes coordonnées"
STEP 4  [Next.js API] POST /api/stagiaire/create (route interne)
STEP 5  [Next.js API → OVH] fetch bridge.php?action=create_or_update_prospect
                           header X-Api-Key: <secret>
STEP 6  [bridge.php] Vérifie X-Api-Key → 403 si faux
STEP 7  [bridge.php] INSERT INTO stagiaire (...) SET status='pre-inscrit',
                    supprime=0, datetime_preinscription=NOW(), paiement=<prix>
STEP 8  [bridge.php] Retourne { success:true, data:{ stagiaire_id:12345 }}
STEP 9  [Next.js]    Stocke stagiaire_id en state, cache bloc 1, affiche bloc CB


ACTE 2 — Paiement (Step 2)
────────────────────────────────────────────────────────────────

STEP 10 [Next.js] Client clique "Payer 219 €"
STEP 11 [Next.js API → OVH] bridge.php?action=prepare_payment&id=12345
STEP 12 [bridge.php] SELECT FROM stagiaire + stage (prix, partenariat, id_membre)
STEP 13 [bridge.php] Construit params Up2Pay :
            PBX_SITE=0966892, PBX_RANG=02, PBX_IDENTIFIANT=651027368,
            PBX_TOTAL=21900, PBX_DEVISE=978, PBX_CMD=BK-2026-012345,
            PBX_PORTEUR=client@email.fr, PBX_TIME=<ISO-8601>,
            PBX_RETOUR='Mt:M;Ref:R;Auto:A;Erreur:E;Sign:K',
            PBX_REPONDRE_A='https://api.twelvy.net/ipn.php',
            PBX_EFFECTUE='https://api.twelvy.net/retour.php?status=ok&id=12345',
            PBX_REFUSE='https://api.twelvy.net/retour.php?status=refuse&id=12345',
            PBX_ANNULE='https://api.twelvy.net/retour.php?status=annule&id=12345',
            PBX_HASH='SHA512'
STEP 14 [bridge.php] Charge UP2PAY_HMAC_KEY_PROD depuis config_secrets.php
STEP 15 [bridge.php] Calcule PBX_HMAC = strtoupper(hash_hmac('sha512', $msg, pack('H*', $key)))
STEP 16 [bridge.php] Retourne { success:true, data:{ paymentUrl:'...', paymentFields:{...} }}
STEP 17 [Next.js] Construit un formulaire HTML caché avec tous les paymentFields
STEP 18 [Next.js] Auto-submit le formulaire vers tpeweb.up2pay.com
STEP 19 [Browser] Redirigé vers la page Up2Pay iFrame (ou plein écran selon intégration)
STEP 20 [Up2Pay] Client saisit CB + valide 3DS
STEP 21 [Up2Pay] Traite le paiement avec la banque


ACTE 3 — Confirmation (2 canaux parallèles)
────────────────────────────────────────────────────────────────

CANAL A — Navigateur (UX seulement, non fiable)
STEP 22a [Up2Pay → Browser] Redirection vers PBX_EFFECTUE
STEP 23a [retour.php] Lit ?status=ok&id=12345
STEP 24a [retour.php] HTTP 302 Redirect vers https://www.twelvy.net/confirmation?id=12345

CANAL B — IPN server-to-server (AUTORITATIF)
STEP 22b [Up2Pay → OVH] POST https://api.twelvy.net/ipn.php
STEP 23b [ipn.php] Vérifie signature RSA avec pubkey.pem → 403 si invalide
STEP 24b [ipn.php] Check idempotence :
             SELECT status, numappel, numtrans FROM stagiaire WHERE id=12345
             Si status='inscrit' AND numappel<>'' AND numtrans<>'' → log + HTTP 200 'already paid'
STEP 25b [ipn.php] Sur 'Erreur=00000' (succès) :
             - UPDATE stagiaire SET status='inscrit', numappel, numtrans, numero_cb (masqué),
               dates, facture_num, commission_ht, partenariat, marge_*,
               up2pay_status='Capturé', up2pay_code_error=NULL, supprime=0
             - UPDATE order_stage SET is_paid=1, reference_order='CFPSP_...', num_suivi
             - INSERT INTO archive_inscriptions
             - UPDATE stage SET nb_places_allouees = nb_max - <count>, nb_inscrits = <count>
STEP 26b [ipn.php] Envoie 3 emails via les scripts PSP existants (mail_inscription.php,
                   mail_inscription_centre.php, copie admin)
STEP 27b [ipn.php] HTTP 200 'OK'
STEP 28b [ipn.php] Sur 'Erreur != 00000' (échec) :
             - UPDATE stagiaire SET up2pay_code_error='<code>'
             - INSERT INTO tracking_payment_error_code
             - status reste 'pre-inscrit' (client peut retenter)


ACTE 4 — Page de confirmation Next.js (polling)
────────────────────────────────────────────────────────────────

STEP 29 [Next.js] /confirmation?id=12345 monte
STEP 30 [Next.js] Affiche "Nous vérifions la confirmation de votre paiement…"
STEP 31 [Next.js] Appelle bridge.php?action=get_stagiaire_status&id=12345
STEP 32 [bridge.php] SELECT et retourne { status, errorCategory, errorMessage, stagiaire_recap }
STEP 33 [Next.js] Si status='paye' → affiche récap final (nom, stage, date, montant, référence)
STEP 34 [Next.js] Si status='en_attente' (IPN pas encore arrivé) → polling toutes les 2-3s (max 5-10 essais)
STEP 35 [Next.js] Si status='refuse' → redirige vers la page inscription avec result=error


Durée totale attendue : 10-30 secondes (dont 3DS côté client : 5-15s)
```

---

## 3. Les URLs finales (à configurer)

### Côté Vercel (env vars)
```
BRIDGE_URL_PROD = https://api.twelvy.net/bridge.php
BRIDGE_API_KEY  = <UUID random 64 chars>        ← stocké en env var Vercel
```

### Côté OVH (fichiers)
```
/www/api/bridge.php      → https://api.twelvy.net/bridge.php
/www/api/ipn.php         → https://api.twelvy.net/ipn.php
/www/api/retour.php      → https://api.twelvy.net/retour.php
/www/api/config_paiement.php     (versionné, avec TEST/PROD switch)
/www/api/config_secrets.php      (NON versionné, .gitignore, contient HMAC + BRIDGE_SECRET_TOKEN)
/www/api/pubkey.pem      (clé publique Up2Pay pour vérif RSA IPN)
```

### Config back-office Up2Pay (à modifier le jour J)
Dans le portail `https://www.ca-moncommerce.com/espace-client-mon-commerce/` ou Supervision `https://guerr.e-transactions.fr/Vision/` :
- URL IPN par défaut → `https://api.twelvy.net/ipn.php` (filet de sécurité, même si on passe aussi `PBX_REPONDRE_A` par transaction)

---

## 4. Contrat BDD locked (les 4 tables actives)

### 4.1 Au paiement OK
```sql
-- 1. stagiaire (UPDATE de ~15 colonnes)
UPDATE stagiaire SET
    status                  = 'inscrit',
    supprime                = 0,
    numappel                = '<pbx_numappel>',
    numtrans                = '<pbx_numtrans>',
    numero_cb               = '<pbx_porteur_masked>',  -- iFrame renvoie déjà masqué
    paiement                = <amount_eur>,
    date_inscription        = CURDATE(),
    date_preinscription     = CURDATE(),
    datetime_preinscription = NOW(),
    facture_num             = <facture_id.id - 1000 recalcul>,
    commission_ht           = <stage.commission_ht_snapshot>,
    partenariat             = <stage.partenariat>,
    marge_commerciale       = <stage.marge_commerciale>,
    taux_marge_commerciale  = <stage.taux_marge_commerciale>,
    prix_index_ttc          = <stage.prix>,
    prix_index_min          = <stage.prix_index_min>,
    up2pay_status           = 'Capturé',
    up2pay_code_error       = NULL
WHERE id = <stagiaire_id>;

-- 2. order_stage (UPDATE d'une row préexistante, ou INSERT si créé à l'étape 1)
UPDATE order_stage SET
    is_paid         = 1,
    reference_order = 'CFPSP_<num_suivi>',
    num_suivi       = <num_suivi>
WHERE user_id = <stagiaire_id> AND stage_id = <stage_id>;

-- 3. archive_inscriptions (INSERT)
INSERT INTO archive_inscriptions (id_stagiaire, id_stage, id_membre)
VALUES (<stagiaire_id>, <stage_id>, <stage.id_membre>);

-- 4. stage (UPDATE)
UPDATE stage SET
    nb_places_allouees = nb_max_places - <SELECT COUNT(*) FROM stagiaire WHERE id_stage=<stage_id> AND status='inscrit' AND supprime=0>,
    nb_inscrits        = <même count>
WHERE id = <stage_id>;
```

### 4.2 Sur échec paiement
```sql
UPDATE stagiaire SET up2pay_code_error = '<pbx_error_code>' WHERE id = <stagiaire_id>;

INSERT INTO tracking_payment_error_code (id_stagiaire, error_code, date_error, source)
VALUES (<stagiaire_id>, '<pbx_error_code>', NOW(), 'up2pay');
-- status reste 'pre-inscrit', le client peut retenter sans tout resaisir
```

### 4.3 Tables explicitement NON touchées
- `transaction` — morte depuis 2014, on ignore (décision locked 16 avril)
- `historique_stagiaire` — morte depuis 2018
- `paiement` — table vide, probable schema futur non adopté
- `facture`, `facture_centre*`, `facture_formateur` — comptabilité mensuelle, flux séparé
- `commission_effective`, `commission_main` — reversement centres, flux séparé

---

## 5. Sécurité — les 3 mécanismes cryptographiques

### 5.1 HMAC-SHA-512 (sortant — Twelvy → Up2Pay)
- **Clé** : 128 chars hex, partagée entre nous et Up2Pay
- **Algo** : HMAC-SHA-512
- **Usage** : signer les params `PBX_*` envoyés quand on lance un paiement
- **Où vit la clé** : `config_secrets.php` sur OVH (non versionné). JAMAIS dans Next.js, JAMAIS dans Git, JAMAIS dans les logs

### 5.2 RSA-SHA1 (entrant — Up2Pay → Twelvy via IPN)
- **Clé publique** : `pubkey.pem` téléchargé depuis Paybox, stocké dans `/www/api/pubkey.pem` (versionné = OK, clé publique)
- **Usage** : vérifier que l'IPN reçu vient bien d'Up2Pay et n'a pas été modifié
- **Si signature invalide** : HTTP 403, aucune écriture BDD, log d'alerte

### 5.3 X-Api-Key (Next.js → bridge)
- **Token** : UUID random 32-64 chars
- **Stockage** : env var `BRIDGE_API_KEY` côté Vercel + `config_secrets.php` côté OVH
- **Usage** : header HTTP obligatoire sur chaque appel bridge.php
- **Si incorrect** : HTTP 403, response `{success:false, error:"unauthorized"}`, stop
- **Rotation** : changer la valeur sur Vercel et OVH en même temps (5 secondes de downtime)

---

## 6. Idempotence — la règle non négociable

**Problème** : Up2Pay peut envoyer la même IPN 2 ou 3 fois (timeout, retry après 500, bug réseau). Si notre `ipn.php` traite chaque appel naïvement, on aurait :
- 2 mails de confirmation au client
- 2 notifications au centre partenaire
- 2 commissions en compta
- Stock décrémenté 2 fois

**Solution** : check `"déjà payé ?"` au début de `ipn.php`.

```php
// Au début d'ipn.php, APRÈS la vérif RSA :
$stagiaire = $pdo->query("SELECT status, numappel, numtrans FROM stagiaire WHERE id = $stagiaireId")->fetch();

if ($stagiaire['status'] === 'inscrit'
    && !empty($stagiaire['numappel'])
    && !empty($stagiaire['numtrans'])) {
    // Doublon détecté — ne rien refaire
    error_log("[IPN] Doublon détecté pour ref=$ref, stagiaire=$stagiaireId");
    http_response_code(200);
    echo 'already paid';
    exit;
}

// Sinon, 1er passage → exécuter les 4 UPDATEs et envoyer les emails
```

**Règle d'or** : répondre `HTTP 200` même sur un doublon (sinon Up2Pay retry inutilement).

L'UPDATE sur `stage.nb_places_allouees` est **idempotent par design** car il recalcule via `SELECT COUNT(*)`. Même si exécuté 2 fois, il donne le même résultat.

---

## 7. Gestion des erreurs — catégories UX

Pour l'utilisateur, afficher un message compréhensible (pas un code Up2Pay brut). Mapping code → catégorie → message :

| Catégorie | Codes Up2Pay typiques | Message UX (FR) |
|-----------|----------------------|-----------------|
| `erreur_saisie_carte` | 00114, 00007, 00020 | "Numéro/date/CVV erroné. Vérifiez vos informations bancaires." |
| `refus_banque` | 00021, 00022, 00151 | "Votre banque n'a pas autorisé le paiement. Réessayez avec une autre carte." |
| `probleme_3ds` | (codes 3DS spécifiques) | "Échec de l'authentification 3D Secure. Réessayez." |
| `erreur_technique` | 00001, 5xx, plateforme down | "Plateforme momentanément indisponible. Réessayez plus tard." |
| `en_attente` | (rare, état intermédiaire) | "Paiement en cours de validation. Patientez quelques instants." |

**Mapping exhaustif** : 76 codes dans `errors.csv` (fichier à la racine du projet).

**Stockage** : `ipn.php` met à jour `stagiaire.up2pay_code_error` avec le code brut. Le bridge traduit côté `get_stagiaire_status` en `{errorCategory, errorMessage}` avant de répondre à Next.js. Next.js n'a jamais à manipuler les codes bruts.

---

## 8. Coexistence avec PSP pendant la transition

**Règle d'or** : zéro modification côté PSP. Twelvy et PSP écrivent dans la même base MySQL sans se marcher dessus.

### Comment ils cohabitent sans conflit
- **Même table `stagiaire`** : PSP crée des rows avec `status='supprime', supprime=1` (puis passe à `inscrit` au paiement). Twelvy crée des rows avec `status='pre-inscrit', supprime=0`. Pas de collision car les `id` sont auto-increment uniques.
- **Même compte Up2Pay** : chaque transaction passe son propre `PBX_REPONDRE_A` dans les params — donc les IPN PSP vont à `validate_payment.php` PSP, les IPN Twelvy vont à notre `ipn.php`. Indépendants.
- **Mêmes emails** : Twelvy réutilise `mail_inscription.php`, `mail_inscription_centre.php`, `mail_echec_paiement.php` de PSP via require_once. Aucune duplication.
- **`facture_id` compteur partagé** : OK car AUTO_INCREMENT atomique MySQL. Les num_suivi ne se recoupent pas.

### Flux concurrents possibles
| Client | Entrée | BDD |
|--------|--------|-----|
| Paiement PSP classique | prostagespermis.fr → order.php → ... → validate_payment.php | Écrit dans stagiaire + order_stage + archive_inscriptions + stage (ancien flux) |
| Paiement Twelvy | twelvy.net → bridge.php → ... → ipn.php | Écrit dans stagiaire + order_stage + archive_inscriptions + stage + tracking (nouveau flux) |

→ Aucun conflit. La compta, Simpligestion, l'espace centre continuent de fonctionner sur l'union des deux sources.

---

## 9. Plan de rollback

**Si le nouveau tunnel Twelvy casse en prod** :

### Option 1 — Rollback du formulaire Twelvy (revenir au design custom)
- Git tag disponible : `payment-form-custom-backup-2026-04-16`
- Commande : `git checkout payment-form-custom-backup-2026-04-16 -- app/stages-recuperation-points/[slug]/[id]/inscription/`
- Commit + push → Vercel redéploie → UI revient au design custom
- Temps : ~5 minutes

### Option 2 — Redirection temporaire vers PSP
- Sur `www.twelvy.net/stages-recuperation-points/.../inscription`, renvoyer en redirect 302 vers `https://www.prostagespermis.fr/...`
- Les clients finissent leur inscription sur PSP (ancien flux qui marche toujours)
- Désactive le nouveau tunnel le temps de fixer le bug
- Temps : ~10 minutes (1 commit redirect + Vercel deploy)

### Option 3 — Full rollback (désactivation complète)
- Supprimer `bridge.php`, `ipn.php`, `retour.php` du FTP OVH
- Le bridge retourne 404, les paiements Twelvy échouent explicitement côté UX
- Option hardcore, seulement si Options 1 et 2 ne suffisent pas
- Temps : ~5 minutes

---

## 10. Points NOT-en-scope (explicitement exclus)

Pour éviter le scope creep :
- ❌ Pas de modification du code PSP existant
- ❌ Pas de changement du compte bancaire / config Crédit Agricole
- ❌ Pas de nouveau système d'envoi d'emails (on réutilise PSP)
- ❌ Pas de changement de formule commission (on snapshot `stage.commission_ht` comme PSP)
- ❌ Pas d'intégration des remboursements via API Up2Pay (`IDOPER=refund`) — on garde le flux SEPA manuel de PSP pour le moment
- ❌ Pas d'écriture dans les tables `transaction`, `historique_stagiaire`, `facture`, `commission_*`
- ❌ Pas de migration de la table `paiement` (vide, schema futur non adopté)
- ❌ Pas de pages "espace stagiaire" / "espace centre" sur Twelvy — elles restent sur prostagespermis.fr

---

## 11. Ordre d'implémentation (Étapes 4-10)

Étapes suivantes du plan, dans l'ordre, avec durées estimées :

| # | Étape | Livrable | Durée |
|---|-------|----------|-------|
| 4 | Config TEST + PROD | `config_paiement.php` squelette + `config_secrets.php` (vide) + `.env.local` | 1h |
| 5 | Bridge PHP squelette | `bridge.php` avec actions `ping`, `create_or_update_prospect`, `prepare_payment`, `get_stagiaire_status` + X-Api-Key | 2-3h |
| 6 | IPN + retour scripts | `ipn.php` (vérif RSA + idempotence + 4 writes + emails) + `retour.php` (redirect) | 4-6h |
| 7 | Branchement Next.js | Modifier la page inscription pour appeler bridge | 4-6h |
| 8 | Pages retour Next.js | `/confirmation?id=X` avec polling + rouverture CB sur erreur | 3-4h |
| 9 | Tests bout-en-bout sandbox | 4 scénarios + side-by-side diff avec PSP | 4-6h |
| 10 | Bascule prod | Switch credentials + 1-3 paiements pilote + monitoring 1 semaine | 2-4h + 1 semaine |

**Total estimé** : 25-40h dev + 1 semaine monitoring actif.

---

## 12. Décisions à valider avant code (3 derniers points)

Avant d'attaquer l'Étape 4, Kader doit valider :

### Validation 1 — Cette architecture dans sa globalité
Kader lit ce document et donne un OK global. Si ajustement, on itère.

### Validation 2 — Préfixe de référence commande
Deux options :
- (a) Garder `CFPSP_xxxxx` (comme PSP) pour continuité visuelle
- (b) Utiliser `TWLV_xxxxx` pour distinguer les transactions Twelvy dans les logs Up2Pay
- **Recommandation** : (a) `CFPSP_` pour compatibilité maximale avec le reporting existant

### Validation 3 — Channel de remboursement
- (a) Garder SEPA bancaire (comme PSP) → `attente_remboursement=1` dans stagiaire, flux manuel
- (b) Utiliser l'API Up2Pay `IDOPER=refund` → remboursement direct sur la CB d'origine
- **Recommandation** : (a) pour le lancement. Migration vers (b) en Phase 2 si demandé.

---

## 13. Liens vers autres documents

- **`UP2PAY.md`** — master reference technique exhaustif Up2Pay (credentials, HMAC algo, IPN handler, références code PSP)
- **`STAGIAIRE_AUDIT.md`** — audit BDD live avec 163 colonnes de stagiaire + 4 tables actives
- **`PSP_FLOW_MOVIE.md`** — flow movie PSP legacy 29 steps avec file:line + résolution mystère transaction
- **`RESUME_SESSION_15APR.md` + `RESUME_SESSION_17APR.md`** — logs de sessions
- **Cahier des charges Up2Pay** (`Cahier des charges up2pay.pages`, 37 pages PDF) — source initiale des décisions architecturales
- **`errors.csv`** — 76 codes erreur Up2Pay → message UX

---

## 14. Validation Kader (à remplir après lecture)

```
☐ Architecture globale validée
☐ Flow movie cible OK
☐ 4 tables actives + 1 conditionnelle OK
☐ Idempotence strategy OK
☐ Coexistence PSP validée
☐ Rollback plan validé
☐ Préfixe CFPSP_ confirmé
☐ SEPA manuel confirmé pour remboursements phase 1
☐ Pas d'objection aux éléments NOT-en-scope §10
```

---

**Document rédigé le 17 avril 2026.**
**Prochaine étape après validation** : Étape 4 du plan — préparer la config TEST + PROD avec séparation stricte des secrets.
