# RESUME SESSION — 15 Avril 2026

**Suite de `RESUME_SESSION_14APR.md`** (qui contient toute la recherche Up2Pay initiale faite tard le 14 au soir).
**Session du jour** : pédagogie Up2Pay + découverte d'un mini-bridge existant.

---

## 1. Travail du jour (en cours)

### 1.1 Session pédagogique Up2Pay (5 sous-catégories)

L'utilisateur a demandé une explication "comme pour un enfant" vu qu'il n'a aucune connaissance du sujet. Structure adoptée : 5 sous-catégories, une à la fois, avec Q&A entre chaque.

| # | Sous-catégorie | Statut |
|---|----------------|--------|
| 1 | C'est quoi Up2Pay et pourquoi on en a besoin | ✅ Validée |
| 2 | Les 2 façons d'intégrer Up2Pay (Direct vs Hébergé iFrame) | ✅ Validée |
| 3 | Qui fait quoi (Vercel, OVH, le bridge) | ✅ Validée (avec question de fond — voir 1.2) |
| 4 | Le parcours complet d'un paiement, clic par clic | ⏳ En cours |
| 5 | Pourquoi l'IPN existe et c'est quoi l'idempotence | ⏳ À venir |

### 1.2 ⚠️ Découverte importante pendant la sous-catégorie 3

L'utilisateur a posé LA bonne question : **"si on n'a pas de bridge.php, comment ça se fait que le formulaire Twelvy arrive déjà à écrire dans la BDD aujourd'hui ?"**

**Réponse** : j'étais imprécis dans ma première rédaction. **Un mini-bridge existe déjà** sur `api.twelvy.net`, il s'appelle `stagiaire-create.php`.

**Flux actuel vérifié** :
```
Formulaire inscription Next.js
  ↓ POST /api/stagiaire/create (route Next.js)
  ↓ fetch vers https://api.twelvy.net/stagiaire-create.php
  ↓ PDO → MySQL khapmaitpsp → table stagiaire
  ↓ INSERT avec status='pre-inscrit'
  ↓ Retourne {stagiaire_id, booking_reference}
```

**Ce qu'il fait** : création stagiaire UNIQUEMENT (une seule action).
**Ce qu'il ne fait pas** : paiement, HMAC, lecture statut, sécurité.

### 1.3 Stratégie retenue — Option 1 (cohérente avec cahier)

**Étendre `stagiaire-create.php` en `bridge.php` complet**, plutôt que créer un fichier en parallèle. Le code actuel devient l'action `create_or_update_prospect`, on ajoute `prepare_payment`, `get_stagiaire_status`, `ping`.

Avantage : pattern Vercel → OVH PHP → MySQL déjà testé en production. On étend, on ne refait pas.

### 1.4 ⚠️ 3 corrections de sécurité à appliquer en passant

Quand on touchera à ce fichier pour l'étendre en bridge, **3 problèmes de sécurité obligatoires à régler** :

1. **Mot de passe MySQL en clair dans le code** (ligne 66 de `stagiaire-create.php` : `'Lretouiva1226'`)
   → Déplacer vers `config_secrets.php` non versionné + `require_once`
2. **CORS ouvert à tous** (`Access-Control-Allow-Origin: *`)
   → Restreindre à `https://www.twelvy.net` (+ domaine de dev si nécessaire)
3. **Aucune vérification d'API key**
   → Ajouter header `X-Api-Key` obligatoire avec `hash_equals(BRIDGE_SECRET_TOKEN, $apiKey)` en début de script

### 1.5 Fichier modifié : `UP2PAY.md`

Ajout d'une nouvelle section **"8.bis — État actuel : un mini-bridge existe déjà"** avec :
- Explication du flux actuel
- Tableau comparatif existant vs cible
- Stratégie Option 1 (étendre le fichier existant)
- 3 corrections de sécurité à appliquer
- Pattern technique déjà validé → on réduit le risque d'architecture

---

## 2. Session pédagogique — les 5 sous-catégories validées

| # | Sous-catégorie | Statut |
|---|----------------|--------|
| 1 | C'est quoi Up2Pay et pourquoi on en a besoin | ✅ Validée |
| 2 | Les 2 façons d'intégrer Up2Pay (Direct vs Hébergé iFrame) | ✅ Validée |
| 3 | Qui fait quoi (Vercel, OVH, le bridge) + découverte mini-bridge existant | ✅ Validée |
| 3.5 | Focus : c'est quoi la clé HMAC | ✅ Ajouté sur demande |
| 4 | Le parcours complet d'un paiement, clic par clic | ✅ Validée |
| 5 | L'IPN et l'idempotence | ✅ Validée |

**Contenu intégral archivé dans `UP2PAY.md`** sous une nouvelle section "📚 Explication pédagogique pour débutants (session 15 avril)". Toute la session est désormais relisible hors contexte chat.

---

## 3. Récapitulatif du mental model construit

### Les 5 acteurs
Client · Toi (commerçant) · Banque du client · Ta banque · Up2Pay

### Les 2 modes d'intégration
- **Direct PPPS** = PSP aujourd'hui, carte passe par ton serveur (PCI-DSS obligatoire)
- **Hébergé iFrame** = cible Twelvy, carte saisie chez Up2Pay (hors scope PCI)

### Les 3 endroits où vit le code
- **Vercel** = UI Next.js (pas de BDD, pas de PHP)
- **OVH** = PHP 5.6 + MySQL (là où vit toute la logique métier)
- **Up2Pay** = serveurs externes Crédit Agricole

### Les 3 scripts PHP à construire/étendre sur `api.twelvy.net`
- **bridge.php** — passerelle Next.js ↔ MySQL (évolution de `stagiaire-create.php`)
- **retour.php** — reçoit le navigateur après paiement, redirige vers Next.js
- **ipn.php** — reçoit la notification serveur d'Up2Pay, **fait foi** pour la BDD

### Les 2 signatures cryptographiques
- **HMAC-SHA-512** pour les appels sortants (toi → Up2Pay, clé symétrique)
- **RSA-SHA1** pour l'IPN entrante (Up2Pay → toi, clé asymétrique, clé publique téléchargée)

### La règle d'or
**Idempotence IPN** : vérifier `status==='inscrit' && numtrans rempli` avant d'agir, sinon SKIP. Sinon Up2Pay peut envoyer la même notif 2-3 fois → doublons → mails en double, commissions en double, stock décrémenté en double.

---

## 4. Commits du jour

- `2c7e751` — Existing mini-bridge discovery + 3 security fixes to apply
- (à venir) — Session pédagogique complète ajoutée à UP2PAY.md

---

## 5. État du projet Étape 8 après la session pédagogique

| Item | Statut |
|------|--------|
| Compréhension générale utilisateur | ✅ Fondations posées (5 sous-catégories) |
| Mini-bridge existant identifié | ✅ `stagiaire-create.php` |
| Stratégie technique | ✅ Étendre le mini-bridge en bridge.php complet |
| 3 corrections de sécurité listées | ✅ Mot de passe MySQL, CORS, X-Api-Key |
| Documentation master (`UP2PAY.md`) | ✅ Section 8.bis + Section pédagogique ajoutées |
| Validation Kader | ⏳ En attente (10 questions critiques, notamment mode Direct vs iFrame) |
| Attaque Étape 1 (audit table stagiaire) | ⏳ En attente de validation Kader |

---

## 6. ⚡ Étape 1 du plan Up2Pay — RÉALISÉE (16 avril)

Suite du débrief : nous avons attaqué l'Étape 1 du cahier des charges (audit table `stagiaire`).

### 6.1 Découverte critique sur les dumps locaux
Les dumps `khapmaitpsp_mysql_db.sql` (80 MB) et `prostagepsp_mysql_db.sql` (2 GB) datent du 17 février et ne contiennent que 4 / 256 tables respectivement. **Totalement obsolètes**. La BDD live OVH a beaucoup évolué depuis.

### 6.2 Audit live (read-only) sur OVH
Pour avoir la vérité :
1. Script PHP read-only `_audit_temp.php` créé localement
2. Upload via FTP `curl -T` sur `ftp.cluster115.hosting.ovh.net/www/api/`
3. Exécution unique via `https://api.twelvy.net/_audit_temp.php?key=<random>`
4. **Suppression immédiate** via FTP DELE (HTTP 404 confirmé)
5. JSON 835 KB sauvegardé localement, 315 tables auditées

Aucune modification de données — uniquement SHOW/DESCRIBE/SELECT.

### 6.3 Constats clés
- **315 tables** dans la BDD live `khapmaitpsp` (vs 4 dans le dump)
- MySQL **8.4.8** + PHP **5.6.40** sur OVH
- `stagiaire` : **163 colonnes**, 50 005 lignes
- 4 tables critiques pour Up2Pay (toutes dans khapmaitpsp live) :
  - `stagiaire` (50 005 rows)
  - `transaction` (63 247 rows)
  - `order_stage` (138 936 rows)
  - `archive_inscriptions` (98 980 rows)
- Statuts effectifs : `inscrit` (24 548), `supprime` (25 452), `pre-inscrit` (4) — seulement 3 valeurs utiles
- `up2pay_status` observé : NULL (29k), `'Capturé'` (20k), quelques bugs/refunds rares

### 6.4 Le contrat "paiement OK" — 5 écritures SQL
À l'IPN succès, le code PSP fait :
1. `UPDATE transaction` (flippe `type_paiement` de `'cheque_en_attente'` à `'CB_OK'`)
2. `UPDATE order_stage SET is_paid=TRUE`
3. `UPDATE stagiaire SET status='inscrit', numappel, numtrans, ...` (~14 colonnes)
4. `INSERT INTO archive_inscriptions`
5. `UPDATE stage SET nb_places_allouees, nb_inscrits` (recalcul COUNT — **idempotent par design**)

### 6.5 Pédagogie agent — l'agent en background était sur les dumps stale
Un agent `general-purpose` lancé en parallèle sur les dumps locaux a produit une analyse incroyablement détaillée du **code PSP** (10+ fichiers, file:line refs des 5 écritures SQL, identification de `facture_num = numSuivi - 1000`, etc.) — ces analyses ont été **intégrées** au document final.

Mais ses conclusions sur l'architecture BDD ("deux serveurs MySQL séparés", "transaction n'existe pas") étaient fausses car basées sur les dumps stale. Le document final corrige ces erreurs.

### 6.6 Livrables
- `STAGIAIRE_AUDIT.md` (~28 KB, 12 sections) — rapport d'audit complet
- `UP2PAY.md` mis à jour avec section 8.ter référençant l'audit
- Script `_audit_temp.php` supprimé du serveur OVH
- Données JSON brutes sauvegardées en local à `/tmp/audit_result.json` (non versionné)

### 6.7 10 décisions critiques pour Kader (avant de coder)
Synthétisées dans `STAGIAIRE_AUDIT.md` Section I :
1. Scope BDD : 5 tables (PSP exact) ou seulement `stagiaire` ?
2. Statut `pre-inscrit` vs `supprime` à la création prospect
3. Origine du `id_membre` (probablement `stage.id_membre`)
4. Comment incrémenter atomiquement `facture_id` ?
5. Calcul `commission_ht` à reproduire
6. Liste canonique des `up2pay_status`
7. PCI : masquage `numero_cb`
8. `paiement` smallint — migrer vers `decimal(7,2)` ?
9. Préfixe référence Twelvy (`TWLV_xxxxx` ?)
10. Refund : SEPA (comme PSP) ou Up2Pay API ?

---

## 7. Session de clarification — précision des explications post-audit

L'utilisateur a relu le résumé et posé de bonnes questions de précision. Réponses apportées et intégrées dans `STAGIAIRE_AUDIT.md` Section I.bis (FAQ pédagogique) :

### 7.1 Pourquoi `id_membre` dans `transaction` ?
La table `transaction` contient l'`id_membre` (centre partenaire) en plus de `id_stagiaire`/`id_stage`/`type_paiement`. Cette valeur vient de `stage.id_membre` — chaque stage est hébergé par un centre, et le paiement doit savoir à qui la commission revient.

### 7.2 Pourquoi tant de lignes `cheque_en_attente` ?
Cycle : INSERT au moment du form avec `'cheque_en_attente'` → UPDATE à `'CB_OK'` si paiement OK, sinon reste `'cheque_en_attente'` à vie. Donc majorité = paiements abandonnés. Normal en e-commerce.

### 7.3 Les "factures" — 2 sortes complètement différentes
- **Type 1 (NOTRE PROBLÈME)** : numéro de facture client, stocké dans `stagiaire.facture_num`, compteur dans `facture_id` (274 084 lignes). Juste un numéro séquentiel attribué à chaque client payé.
- **Type 2 (PAS NOTRE PROBLÈME)** : factures mensuelles partenaires dans `facture`, `facture_centre`, `facture_centre_produit`, `facture_formateur`. Générées par un batch comptable, jamais touchées au paiement.

### 7.4 `commission_ht` — c'est juste une colonne
`commission_ht` = colonne float sur `stagiaire`, calculée et snapshottée au moment du paiement. Pas une table séparée. Formule dans le code PSP (à extraire).

### 7.5 `up2pay_status` — écrit par un CRON, pas par le code de paiement
`validate_payment.php` ne touche jamais `up2pay_status`. C'est le cron `cron_status_payment.php` qui réinterroge Up2Pay périodiquement et remplit ce champ. **Pour Twelvy, recommandation : écrire `'Capturé'` directement à l'IPN** (ne pas attendre un cron).

### 7.6 `paiement` — colonne smallint sur `stagiaire`
Montant en EUR (entier, pas de décimales). Limite `smallint` = 32 767. OK aujourd'hui, overflow potentiel si stage > €327 ou décimales un jour.

### 7.7 Tableau récap "où vit chaque truc"
Ajouté dans Section I.bis du `STAGIAIRE_AUDIT.md` — référence rapide pour savoir si un champ est sur `stagiaire` (la majorité), sur `facture_id` (le compteur), ou sur les 4 tables sœurs (transaction, order_stage, archive_inscriptions, stage).

### 7.8 Mises à jour des questions Kader (Section I)
Questions 4, 5, 6 affinées avec plus de précision :
- Q4 reformulée : compteur atomique pour `facture_id` (pas confondre avec factures partenaires)
- Q5 reformulée : où est le code du calcul commission_ht
- Q6 enrichie : note que le champ est rempli par cron, pas par paiement immédiat

---

**Session 15-16 Avril 2026 — terminée.**
**Étapes 0-1 du plan Up2Pay : faites.** Restent les étapes 2-10.
**Prochaine étape** : valider `STAGIAIRE_AUDIT.md` + `UP2PAY.md` avec Kader (appel téléphonique prévu), obtenir réponses aux 10 questions, puis attaquer Étape 2 (cartographie dynamique du flux PHP avec un vrai paiement test).
