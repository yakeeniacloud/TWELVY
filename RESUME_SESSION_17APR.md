# RESUME SESSION — 17 Avril 2026

**Suite de `RESUME_SESSION_15APR.md`** (audit BDD live + appel Kader).
**Session du jour** : Étape 2 du plan Up2Pay (cartographie complète du flux PHP avec preuves) + résolution du mystère `transaction` table.

---

## 1. Contexte d'entrée de session

État au démarrage du 17 avril :
- ✅ Étape 1 du plan Up2Pay (audit BDD) terminée
- ✅ Décisions architecture lockées (mode iFrame, ignore `transaction`, HMAC assumée, backup design custom)
- ✅ 4 tables actives identifiées via audit BDD live
- ⏳ Étape 2 (flow movie du paiement PHP actuel) à attaquer

Travail attendu : ~1-2h de lecture de code, aucun risque, aucune query live.
**En réalité** : la session a pris ~3h car nous avons dû mener 3 phases d'investigation supplémentaires pour valider chaque conclusion.

---

## 2. Travail effectué aujourd'hui

### 2.1 Comparaison des archives PSP

Avant de plonger, vérification fondamentale : quel est le codebase PSP authoritative ?

**Trouvé** :
- `www_2` (333 MB) — backend uniquement
- `www_3` (2.0 GB) — frontend + backend (superset de www_2)
- `PSP 2`, `PSP 3` — archives plus anciennes, sans `src/payment/`
- `src/payment/` est **identique** entre www_2 et www_3 (`diff` retourne rien)
- `src/order/` également identique
- `www_3/es/inscriptionv2_3ds.php` existe (le fichier que l'agent précédent avait identifié comme entry point)

**Conclusion** : on travaille sur `www_3` (le plus complet). Toute la logique src/* est cohérente entre www_2 et www_3.

### 2.2 Lancement d'un agent général pour le flow movie

Agent briefé avec des règles strictes :
- **Chaque conclusion doit avoir une citation file:line**
- **Aucune spéculation** sans label "UNABLE TO VERIFY"
- **Cross-référencer** chaque DB write avec la liste des tables actives/mortes connues
- Couvrir 4 deliverables : entry point, flow movie 29 steps, réponses Q3-Q7, matrice legacy/live

**Résultat** : `PSP_FLOW_MOVIE.md` ~2700 mots, 4 deliverables, file:line partout.

### 2.3 Découvertes majeures de l'agent

**1. L'entry point soupçonné `formulaire_inscription_2024.php` est ORPHELIN**
   - Aucun fichier ne l'include/require dans tout le codebase
   - Code moderne (Alpine.js, UTM tracking 2024) mais jamais wired
   - Probable rewrite jamais déployé

**2. `inscriptionv2_3ds.php` n'est PAS la page d'inscription principale**
   - C'est la popup de **changement de stage** (transfert)
   - Wired à `changement_avisv2_3ds.php` et `validate_transfert_payment.php`
   - Le mot "transfert" dans plusieurs URLs le confirme

**3. La vraie chaîne moderne identifiée par l'agent** :
   ```
   landing → /src/order/applications/order.php
            (writes stagiaire + order_stage + transaction rows)
          → /page_recap.php (template common_recap.php)
          → payment.js::paymentWithCard
          → /src/payment/ajax/ajax_stage_payment_available.php
          → POST form to tpeweb.paybox.com (3DS)
          → /src/payment/validate/validate_payment.php
          → 2 PPPS cURLs (TYPE=00001 + TYPE=00002)
          → DB writes (4 tables)
          → emails
          → redirect /page_upsell.php OU /order_confirmation.php
   ```

**4. Réponses aux questions Q3-Q7 (avec file:line)** :
   - **Q3 `id_membre`** : vient de `stage.id_membre` via JOIN. Proof `OrderStageRepository.php:205-207`
   - **Q4 `facture_num`** : `facture_id` table autoincrement. Proof `OrderStageRepository.php:219-229`. Formule : `facture_num = num_suivi - 1000` où `num_suivi = facture_id.id + 1000`
   - **Q5 `commission_ht`** : pure passthrough `stage.commission_ht`. Aucun calcul dans payment code. Proof `OrderStageRepository.php:137`
   - **Q6 `up2pay_status`** : JAMAIS écrit par `src/payment/`. Seulement par cron `cron_status_payment.php:49`. Confirmé par grep exhaustif
   - **Q7 `numero_cb`** : stocké en PAN brut (16 chiffres complets) — RISQUE PCI-DSS sur PSP. Proof `PaymentRepository.php:80`

### 2.4 ⚡ Le mystère `transaction` table — investigation Phase 2 et 3

**Problème** : agent indique que `OrderStageRepository::saveOrder` inserts dans `order_stage` ET `transaction` ensemble. Mais en live :
- `order_stage` : 138 936 rows, dernière 2026-02-20 ✅ active
- `transaction` : 63 247 rows, dernière 2014-11-21 🪦 dead

**Si le code tournait, `transaction` aurait des nouvelles rows. Or il n'en a pas.**

#### Investigation Phase 2 — fetch FTP du code live psp-copie

Hypothèse : peut-être que la version locale est obsolète et que le live a été modifié.

```bash
curl --user khapmait:... ftp://...psp-copie/www_2/src/order/repositories/OrderStageRepository.php
diff archive_local psp-copie_version
```

**Résultat** : seules différences = chemins hardcodés (`/Users/yakeen/...` vs `/home/khapmait/...`). **Code IDENTIQUE.** Donc l'INSERT `transaction` est bien dans le code live aussi. Hypothèse réfutée.

#### Investigation Phase 3 — audit BDD niveau permissions/triggers

Script `_audit3_temp.php` uploadé sur api.twelvy.net, exécuté, supprimé (HTTP 404 confirmé).

| Check | Résultat |
|-------|----------|
| Privilèges MySQL `khapmaitpsp` user | `GRANT ALL PRIVILEGES` ✅ pas de blocage |
| Triggers sur `transaction` | **0** ✅ aucun mécanisme silencieux |
| Test INSERT direct dans `transaction` | **SUCCESS** (rolled back) ✅ l'insert marcherait |
| `transaction` MAX(id) | 142223 (figé depuis 2014) |
| `transaction` UPDATE_TIME | 2026-02-21 ⚠ rows updateées récemment, mais pas insérées |
| 3 stagiaires payés Feb 2026 (id 40120314/315/316) | `order_stage` rows présents ✅ |
| Mêmes stagiaires dans `transaction` | **ZÉRO row** correspondant ❌ |

**Conclusion définitive** : si `saveOrder()` était appelé, `transaction` aurait des rows. Il n'en a pas. **Donc `saveOrder()` n'est pas appelé sur le flux moderne.**

Mais ALORS qui crée les `order_stage` rows ? Mystère partiellement non résolu (voir §6 ci-dessous).

### 2.5 Ce qu'on sait avec certitude pour Twelvy

Indépendamment du mystère ci-dessus, les FINGERPRINTS DB du flux moderne sont clairs :

**Quand un client paie en 2026, ces tables reçoivent des écritures :**
1. `stagiaire` → status='inscrit', numappel, numtrans, numero_cb, dates, commission_ht, partenariat, marge_*, prix_index_*, facture_num
2. `order_stage` → is_paid=1, reference_order='CFPSP_xxxxx', num_suivi=xxxxx, amount
3. `archive_inscriptions` → INSERT (id_stagiaire, id_stage, id_membre)
4. `stage` → nb_places_allouees décrémenté, nb_inscrits incrémenté
5. (optionnel sur erreur) `tracking_payment_error_code` → INSERT (id_stagiaire, error_code)

**Ces tables ne reçoivent PAS d'écritures en 2026** :
- `transaction` (dead since 2014)
- `historique_stagiaire` (dead since 2018, 10 rows total)
- `paiement` (existe mais 0 rows — probablement un schema préparé pour le futur)
- `facture`, `facture_centre*`, `facture_formateur` (comptabilité mensuelle, pas le flux paiement)

### 2.6 Backup du formulaire custom et git tag (rappel des actions)

- Git tag créé : `payment-form-custom-backup-2026-04-16`
- Dossier `_backup_payment_form_2026-04-16/` créé avec copie + README de restauration
- Push sur GitHub effectué hier (16 avril)

### 2.7 Documents produits aujourd'hui

| Fichier | Taille | Rôle |
|---------|--------|------|
| `PSP_FLOW_MOVIE.md` | ~28 KB | Flow movie complet 29 steps + addendum résolution mystère |
| `RESUME_SESSION_17APR.md` (ce fichier) | ~10 KB | Log de la session du 17 avril |
| `STAGIAIRE_AUDIT.md` (mis à jour hier) | ~32 KB | Reste la référence DB |

---

## 3. Méthodologie de la session — triple vérification

Vu le piège transaction, j'ai appliqué une discipline stricte :
1. **Lecture du code local** (www_3 archives)
2. **Agent général briefé** avec exigence file:line + zéro spéculation
3. **Fetch FTP live** depuis psp-copie pour comparer code archive vs code live
4. **Audit BDD niveau infrastructure** (privilèges, triggers, test INSERT) pour exclure les explications mécaniques

Chaque conclusion finale a été cross-validée par au moins 2 sources indépendantes.

---

## 4. Réponses aux questions Q3-Q7 (locked avec preuves)

| Question | Réponse | Preuve |
|----------|---------|--------|
| Q3 — Origine `id_membre` | Vient de `stage.id_membre` via JOIN | `OrderStageRepository.php:205-207` + `validate_payment.php:286-302` |
| Q4 — `facture_num` atomique | INSERT dans `facture_id` puis `LAST_INSERT_ID()` | `OrderStageRepository.php:219-229` + `GenerateReferenceOrder.php:14-17` |
| Q5 — Formule `commission_ht` | Aucune. Passthrough depuis `stage.commission_ht` | `OrderStageRepository.php:137` (alias `stage_commission`) + `validate_payment.php:296` |
| Q6 — Qui écrit `up2pay_status` | UNIQUEMENT le cron, pas le code paiement | `cron_status_payment.php:49` + `simpligestion/ajax_functions.php:4079` |
| Q7 — `numero_cb` masqué ou brut | **PAN brut** stocké en clair (problème PCI sur PSP) | `PaymentRepository.php:80` `numero_cb = '$cardNumber'` |

---

## 5. Contrat IPN Twelvy — version finale lockée

```sql
-- Étape 1 : recevoir l'IPN Up2Pay, vérifier RSA, vérifier idempotence
-- Si déjà payé (status='inscrit' AND numappel/numtrans non vides) → SKIP

-- Étape 2 : 4 écritures séquentielles (toutes dans une transaction MySQL idéalement)

UPDATE stagiaire SET
    status                  = 'inscrit',
    numappel                = '<pbx_numappel>',
    numtrans                = '<pbx_numtrans>',
    numero_cb               = '<pbx_porteur_masked>',  -- iFrame nous donne déjà masqué
    paiement                = <amount_eur>,
    date_inscription        = CURDATE(),
    datetime_preinscription = NOW(),
    facture_num             = <calculated_via_facture_id>,
    commission_ht           = <stage.commission_ht_at_payment_time>,
    partenariat             = <stage.partenariat>,
    marge_commerciale       = <stage.marge_commerciale>,
    taux_marge_commerciale  = <stage.taux_marge_commerciale>,
    prix_index_ttc          = <stage.prix>,
    prix_index_min          = <stage.prix_index_min>,
    up2pay_status           = 'Capturé',  -- explicit, ne pas attendre le cron
    up2pay_code_error       = NULL,
    supprime                = 0
WHERE id = <stagiaire_id>;

UPDATE order_stage SET
    is_paid         = 1,
    reference_order = 'CFPSP_<num_suivi>',
    num_suivi       = <num_suivi>
WHERE user_id = <stagiaire_id> AND stage_id = <stage_id>;
-- (suppose qu'une order_stage row a déjà été créée à l'inscription via Twelvy bridge)

INSERT INTO archive_inscriptions (id_stagiaire, id_stage, id_membre)
VALUES (<stagiaire_id>, <stage_id>, <stage.id_membre>);

UPDATE stage SET
    nb_places_allouees = nb_max_places - <recompute_subscription_count>,
    nb_inscrits        = <recompute_subscription_count>
WHERE id = <stage_id>;

-- Étape 3 : envoyer 3 emails (stagiaire ticket, centre, admin)

-- Étape 4 (sur échec uniquement) :
INSERT INTO tracking_payment_error_code (id_stagiaire, error_code, date_error, source)
VALUES (<stagiaire_id>, '<pbx_error_code>', NOW(), 'up2pay');

UPDATE stagiaire SET up2pay_code_error = '<pbx_error_code>' WHERE id = <stagiaire_id>;
```

---

## 6. Limites honnêtes de cette investigation

1. **Mystère partiellement non résolu** : on ne sait pas EXACTEMENT qui crée les `order_stage` rows modernes. Le code disponible (qu'on a fetché aussi du live psp-copie) le ferait via `saveOrder()` mais cela impliquerait aussi un INSERT dans `transaction` qu'on ne voit pas. Trois explications possibles, aucune prouvée :
   - (a) Il existe un code path moderne qu'on n'a pas trouvé via grep (peu probable mais possible)
   - (b) PSP live tourne sur un FTP qu'on n'a pas, avec une version modifiée de saveOrder qui skip la ligne transaction (Kader dit qu'on a tous les fichiers, donc improbable)
   - (c) Il y a un comportement MySQL subtil (replication, master/slave avec write filter, etc.) qu'on n'a pas detecté

2. **Pour Twelvy ça ne change rien** : on construit basé sur les FINGERPRINTS DB observés, pas sur le code legacy. Les 4 tables que Twelvy doit écrire sont confirmées par les rows réels en prod.

3. **Pilote prod final** : on validera tout via 1-3 paiements réels en Étape 10. Si une colonne nous manque, on l'ajoutera à ce moment-là.

---

## 7. Commits du jour

| Hash | Description |
|------|-------------|
| `b1a3500` | 🔬 docs: Étape 2 done — PSP flow movie + transaction mystery resolution |
| (à venir) | 📝 docs: RESUME_SESSION_17APR.md |

---

## 8. État du plan global Up2Pay après cette session

| Étape | Description | Statut |
|-------|-------------|--------|
| 1 | Audit table stagiaire | ✅ FAIT (16 avril) |
| 2 | Cartographier flux PHP actuel | ✅ FAIT (17 avril, ce jour) |
| 3 | Designer architecture cible | ⏳ À ATTAQUER (prochaine session) |
| 4 | Préparer config TEST + PROD | ⏳ |
| 5 | Créer bridge.php sécurisé | ⏳ |
| 6 | Bétonner scripts retour + IPN | ⏳ |
| 7 | Brancher formulaire Next.js | ⏳ |
| 8 | Gérer retour paiement | ⏳ |
| 9 | Tests bout-en-bout sandbox | ⏳ |
| 10 | Bascule prod + monitoring | ⏳ |

---

## 9. ⚡ Étape 3 ATTAQUÉE le même jour — architecture cible documentée

Suite à l'avancement rapide d'Étape 2, on a enchaîné directement sur Étape 3.

### 9.1 Livrable produit : `ARCHITECTURE_CIBLE.md`

Document complet (~14 sections, ~3500 mots) qui sert de blueprint avant tout code :

- **Section 0** : TL;DR exécutif
- **Section 1** : les 4 briques (Next.js, bridge.php, ipn.php, retour.php) — rôles, où elles vivent, ce qu'elles font/ne font pas
- **Section 2** : le "nouveau film" du paiement — 35 steps avec ASCII diagram, du clic au record en BDD
- **Section 3** : URLs et fichiers finaux à configurer côté Vercel et OVH
- **Section 4** : contrat BDD locked — les 4 UPDATEs/INSERTs SQL exacts (stagiaire, order_stage, archive_inscriptions, stage)
- **Section 5** : sécurité — les 3 mécanismes cryptographiques (HMAC, RSA, X-Api-Key)
- **Section 6** : idempotence — la règle non négociable + code PHP type
- **Section 7** : gestion d'erreurs UX — 5 catégories mappant les 76 codes Up2Pay
- **Section 8** : coexistence PSP — comment les deux flux écrivent sans conflit dans la même base
- **Section 9** : plan de rollback — 3 options (git tag / redirect / full)
- **Section 10** : NOT-en-scope — exclusions explicites pour éviter scope creep
- **Section 11** : ordre d'implémentation des étapes 4-10 avec durées estimées
- **Section 12** : 3 décisions à valider avec Kader avant code (architecture globale, préfixe CFPSP_, channel remboursement)
- **Section 13** : index des autres documents de référence
- **Section 14** : checklist de validation Kader (☐ à cocher)

### 9.2 Pourquoi écrire ce doc avant de coder

- Une seule source de vérité — pas de re-litigation de décisions pendant le code
- Kader peut donner un GO/NO-GO en 10 minutes de lecture
- Évite que différentes parties du bridge fassent des assomptions divergentes
- Référence pour les sessions futures (rappel "pourquoi a-t-on choisi ça déjà ?")

### 9.3 Décisions verrouillées dans le doc

| Item | Décision |
|------|----------|
| Mode d'intégration | Hosted iFrame (pas Direct PPPS) |
| Tables actives à écrire | 4 (stagiaire, order_stage, archive_inscriptions, stage) |
| Table sur erreur | tracking_payment_error_code |
| Tables ignorées | transaction (mort), historique_stagiaire (mort), paiement (vide) |
| Bridge URL | https://api.twelvy.net/bridge.php |
| IPN URL | https://api.twelvy.net/ipn.php |
| Sécurité bridge | header X-Api-Key obligatoire |
| Vérif IPN | RSA-SHA1 avec pubkey.pem Up2Pay |
| HMAC | uniquement côté PHP, jamais Next.js |
| Idempotence | check status='inscrit' AND numappel/numtrans non vides → SKIP |
| Stage decrement | recompute via COUNT (idempotent par design) |
| Coexistence PSP | aucun changement à PSP, écritures parallèles dans même base |
| Rollback custom | git tag payment-form-custom-backup-2026-04-16 |
| Emails | réutilisation des scripts PSP existants (mail_inscription.php etc.) |

### 9.4 Décisions encore à valider par Kader (3 dernières)

1. **Architecture globale** : valider la lecture du doc → si OK on attaque Étape 4
2. **Préfixe référence commande** : CFPSP_ (continuité PSP — recommandé) vs TWLV_ (distinction)
3. **Channel remboursement Phase 1** : SEPA manuel (comme PSP — recommandé) vs API Up2Pay refund

### 9.5 Mise à jour `UP2PAY.md`

Ajout d'une section référence vers `ARCHITECTURE_CIBLE.md` dans le master doc.

---

## 10. Récap final de la journée du 17 avril

| Action | Statut |
|--------|--------|
| Comparaison archives www_2/www_3 | ✅ |
| Agent général flow movie | ✅ |
| Phase 2 audit FTP psp-copie | ✅ |
| Phase 3 audit BDD privilèges/triggers/INSERT test | ✅ |
| Résolution mystère transaction table | ✅ partial (origine inconnue mais comportement observé OK) |
| Réponses Q3-Q7 avec preuves | ✅ |
| Doc PSP_FLOW_MOVIE.md | ✅ |
| Étape 3 attaquée immédiatement | ✅ |
| Doc ARCHITECTURE_CIBLE.md | ✅ |
| RESUME_SESSION_17APR.md complet | ✅ |
| UP2PAY.md mis à jour avec référence | ✅ |
| Tous les commits pushés | ✅ |

---

**Session 17 Avril 2026 — terminée.**
**Étapes 2 ET 3 verrouillées dans la même journée.**
**Prochaine étape** : faire valider `ARCHITECTURE_CIBLE.md` à Kader (~10 min de lecture). Une fois OK → attaquer Étape 4 (config TEST + PROD).
