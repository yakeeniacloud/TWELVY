# STAGIAIRE_AUDIT.md — Live DB audit + Up2Pay payment contract

> **Étape 1 du plan Up2Pay** — produit avant écriture de la moindre ligne de code.
> **Sources** : queries directes sur la BDD live `khapmaitpsp` (16 avril 2026) + analyse exhaustive du code PSP (`/Volumes/Crucial X9/PROSTAGES/www_2/src/payment/`).
> **Méthode** : voir Section J (audit en live + agent en parallèle).
> ⚠️ Les dumps locaux (`khapmaitpsp_mysql_db.sql` 80 MB, `prostagepsp_mysql_db.sql` 2 GB) datent du 17 février et sont **complètement obsolètes**. Ne pas s'y fier.

---

## ⚡ UPDATE 2026-04-16 — Phase 2 audit : le mystère de `transaction` résolu

> **Correction majeure** : après un 2e passage d'audit plus approfondi suite à un retour utilisateur (Kader ne connaissait pas les tables à mettre à jour, et l'utilisateur a vu que la dernière ligne `transaction` datait de 2014), nous avons établi que **`transaction` est une table MORTE depuis fin 2014**.

### Ce que dit la phase 2 (queries par plages de dates)

| Table | Range dates | Row count | Statut |
|-------|-------------|-----------|--------|
| **`transaction`** | **2009-05-03 → 2014-11-21** (+ 1 outlier 2017) | 63 247 | 🪦 **MORTE depuis fin 2014** |
| `order_stage` | **2022-02-10 → 2026-02-20** | 138 936 | ✅ ACTIVE (remplace `transaction`) |
| `stagiaire` | `datetime_preinscription` 2024-09-23 → 2026-04-14 | 50 005 | ✅ ACTIVE |
| `archive_inscriptions` | 2018-09-10 → 2026-02-20 | 98 980 | ✅ ACTIVE |
| `tracking_payment_error_code` | 2025-02-13 → 2026-02-17 | 3 095 | ✅ ACTIVE (récent) |
| `historique_stagiaire` | 2017-11-21 → 2018-01-14 | 10 | 🪦 MORTE depuis 2018 |

### Distribution des types de paiement dans `transaction` (toutes années confondues)
- `cheque_en_attente` : 38 098 (60 %) — placeholders d'abandon
- `CB_OK` : 20 165 (32 %) — paiements CB réussis avant 2014
- `CB OK` : 4 979 (8 %) — même idée, ancien format de chaîne
- `cb_en_attente` : 5

### Distribution `order_stage` par année
- **2026** : 317, **2025** : 35 843, **2024** : 47 426, **2023** : 47 607, **2022** : 7 735

### Distribution `stagiaire` avec `numappel` rempli par année (= paiements Up2Pay)
- **2026** : 320 inscriptions, 153 payées Up2Pay (48 %)
- **2025** : 36 195 inscriptions, 18 262 payées Up2Pay (50 %)
- **2024** : 12 489 inscriptions, 6 131 payées Up2Pay (49 %)

### Exemple de ligne `order_stage` récente (VIVANTE)
```
id=138937, created='2026-02-20 06:34:51', reference_order='CFPSP_275086',
amount=189, is_paid=1, num_suivi=275086, stage_id=329207
```

### Conclusion critique
- L'ancien code PSP (`www_2/src/payment/validate_payment.php` + `PaymentRepository.php`) prétend faire UPDATE sur `transaction`, mais **ce code path n'est plus exécuté** — sinon la table aurait des lignes 2014-2026
- La table a dû être "abandonnée" silencieusement quand le site a migré vers un nouveau système, probablement lors d'une grosse refonte fin 2014. Les UPDATE continuent peut-être d'être appelés sur d'anciennes lignes zombies, mais aucun INSERT frais.
- **Le vrai flux moderne de paiement écrit dans 3 tables actives** : `stagiaire`, `order_stage`, `archive_inscriptions` — **PAS dans `transaction`**.

### Révision du "contrat paiement OK"

#### Ancien contrat (Section E — issu du code PSP www_2, partiellement obsolète)
Le code PSP fait prétendument :
1. UPDATE transaction ← **OBSOLÈTE** (cette table n'est plus écrite depuis 2014)
2. UPDATE order_stage ← ✅ actif
3. UPDATE stagiaire ← ✅ actif
4. INSERT archive_inscriptions ← ✅ actif
5. UPDATE stage ← ✅ actif

#### Nouveau contrat (réalité production 2026)
1. ~~UPDATE transaction~~ — **on n'écrit plus là**
2. INSERT/UPDATE order_stage
3. UPDATE stagiaire (status='inscrit', numappel, numtrans, etc.)
4. INSERT archive_inscriptions
5. UPDATE stage (nb_places_allouees — recompute)
6. INSERT tracking_payment_error_code en cas d'échec

**Pour l'IPN Twelvy** : 4 écritures réelles, pas 5. L'UPDATE `transaction` est en fait optionnel (on peut le garder pour backward compat si on veut, mais ce n'est pas critique — aucun reporting moderne ne lit cette table vu qu'elle ne bouge plus).

### Décision prise (17 avril 2026) ✅
> **"On ignore la table `transaction`."**

Yakeen a validé : Twelvy IPN n'écrira PAS dans `transaction`. La table est figée depuis 2014, aucun reporting moderne ne la lit, inutile de continuer à la toucher. Les données modernes sont dans `order_stage`.

**Contrat IPN Twelvy finalisé → 4 écritures actives + 1 conditionnelle :**
1. UPDATE `stagiaire` (status, numappel, numtrans, dates, commission, facture_num)
2. INSERT/UPDATE `order_stage` (is_paid=1, reference_order, num_suivi, amount)
3. INSERT `archive_inscriptions` (id_stagiaire, id_stage, id_membre)
4. UPDATE `stage` (nb_places_allouees, nb_inscrits)
5. *Conditionnel* — INSERT `tracking_payment_error_code` si échec paiement

### Méthodologie de cette phase 2
- Script `_audit2_temp.php` read-only uploadé sur `api.twelvy.net` puis supprimé (HTTP 404 confirmé)
- Queries : MIN/MAX date par colonne + distribution par année pour chaque table candidate
- Aucune modification de données
- Données JSON brutes dans `/tmp/audit2_result.json` (non versionné)

---

## TL;DR

- **315 tables** dans la BDD live `khapmaitpsp` (les dumps locaux n'en ont que 4 — totalement obsolètes).
- Server : MySQL **8.4.8** + PHP **5.6.40** sur OVH cluster115.
- Table `stagiaire` : **163 colonnes**, **50 005 lignes**.
- 4 tables critiques pour le flux paiement, toutes dans `khapmaitpsp` :
  - `stagiaire` (50 005 rows) — table centrale
  - `transaction` (63 247 rows) — log CB
  - `order_stage` (138 936 rows) — commande/référence facture
  - `archive_inscriptions` (98 980 rows) — audit log
- Statuts effectivement utilisés : seulement **3 valeurs** (`supprime` 25 452, `inscrit` 24 548, `pre-inscrit` 4).
- Modèle "payé" : `status='inscrit'`, `numappel/numtrans` remplis, `up2pay_status='Capturé'`, `paiement>0`.
- À l'IPN, PSP exécute **5 écritures SQL** (transaction, order_stage, stagiaire, archive_inscriptions, stage). Voir Section E pour le détail exact.

---

## Section A — Inventaire BDD live

### A.1 Connexion vérifiée
- Host : `khapmaitpsp.mysql.db` (OVH cluster115, hostname résoluble uniquement depuis OVH)
- Database : `khapmaitpsp`
- User : `khapmaitpsp` (mot de passe dans `MEMORY.md` + en clair dans `php/stagiaire-create.php` — à corriger)
- 315 tables au total

### A.2 Les 75 tables liées au paiement (filtre par pattern)

| Table | Cols | Lignes | Rôle |
|-------|------|--------|------|
| **`stagiaire`** | **163** | **50 005** | **LA table centrale du flux paiement** |
| **`transaction`** | 11 | 63 247 | Log transactionnel CB (autorisation, type) |
| **`order_stage`** | 8 | 138 936 | Commande facturable (is_paid, num_suivi) |
| **`archive_inscriptions`** | 5 | 98 980 | Audit log léger inscriptions |
| `tracking_payment_error_code` | 5 | 3 095 | Historique codes erreur Up2Pay |
| `commission_main` | 8 | 411 | Commissions principales |
| `commission_effective` | 4 | 500 | Commissions effectivement versées |
| `commission_effective_historique` | 4 | 1 000 | Historique commissions |
| `commission_effective_announcement` | 3 | 91 | Annonces commissions |
| `commission2024` | 6 | 102 | Plan commissions 2024 |
| `facture` | 21 | 11 583 | Factures émises |
| `facture_id` | 2 | 274 084 | Compteur séquence factures |
| `facture_centre` | 14 | 1 431 | Factures centres |
| `facture_centre_produit` | 6 | 1 879 | Lignes factures centres |
| `facture_formateur` | 7 | 4 079 | Factures formateurs |
| `historique_stagiaire` | 5 | 10 | Audit log stagiaire (très peu de lignes) |
| `sepa_remboursement_stagiaires` | 5 | 12 | Remboursements SEPA |
| `marge_commerciale` | 5 | 4 | Config marges par dépt |
| `tracking_pricing_stages` | 7 | 8 667 | Historique pricing |
| `stage` | 135 | 125 075 | Catalogue stages |
| `pap_stages` / `pap_stages2` | 9/6 | 22 058 / 9 274 | Pricing automatique |
| `stage_algo` | 34 | 24 586 | Algo pricing |
| `recapitulatif_virement_centre_stagiaire` | 9 | 6 632 | Récap virements centres |

D'autres tables (`stagiaire_externe`, `stagiaire_avant_premium`, `stage_archive`, `stage_avant_premium`, `stage_backup_*`, `stage_2024*`, `stage_old`, `mc24_*`, `mc25_*`) sont des copies historiques ou backups — pas du flux courant.

### A.3 Hypothèse réfutée — pas de "deux serveurs"
Le rapport de l'agent (basé sur les dumps stale) supposait que PSP utilisait deux serveurs MySQL distincts (`khapmaitpsp` pour stagiaire, `prostagepsp` pour le reste). **C'est faux**. La BDD live `khapmaitpsp` contient bien toutes les 315 tables. Les dumps de février étaient juste partiels.

---

## Section B — Schéma complet de `stagiaire` (163 colonnes)

### B.1 Catégorisation des colonnes

#### 👤 Identité (~25 cols)
`id`, `civilite`, `nom`, `jeune_fille`, `prenom`, `prenom2`/`3`/`4`/`5`, `date_naissance`, `lieu_naissance`, `pays_naissance`, `departement_naissance`, `adresse`, `adresse_extensionVoie`, `adresse_typeVoie`, `adresse_complementVoie`, `adresse_complement`, `adresse_numeroVoie`, `adresse_nomVoie`, `code_postal`, `ville`, `tel`, `mobile`, `email`, `fax`, `profession`, `profession_type`, `nom_usage`, `ip`, `iban`, `bic`

#### 📚 Stage / dossier (~30 cols)
`id_stage`, `id_utilisateur`, `id_autoecole`, `id_externe`, `id_boost`, `siid_allopermis`, `cas`, `nb_points`, `date_infraction`, `heure_infraction`, `lieu_infraction`, `motif_infraction`, `date_lettre`, `date_48n`, `date_composition_penale`, `date_jugement`, `numero_parquet`, `num_permis`, `lieu_permis`, `date_permis`, `date_obtention_permis`, `points_restant`, `type_permis`, `permis_probatoire`, `permis_indispensable`, `permis_ancien_plus_trois_an`, `etat_permis`, `situation_permis`, `dossier_recu`, `dossier_complet`, `dossier_verifie`, `documents_verifies`, `nb_documents`, `pieces_manquantes_verifiees`, `validations_stagiaire`, `comm_autoecole`

#### 💳 PAIEMENT — colonnes critiques pour Up2Pay (28 cols)

**Statut & lifecycle**
| Col | Type | Prospect | Payé | Échec |
|-----|------|----------|------|-------|
| `status` | text | `'pre-inscrit'` | `'inscrit'` | `'supprime'` ou reste `'pre-inscrit'` |
| `supprime` | smallint | `0` | `0` | `1` (si abandonné) |
| `dossier_recu` | tinyint | `0` | `0` puis `1` plus tard | `0` |
| `annulation` | tinyint | `0` | `0` | éventuellement `1` |
| `invalidation` | tinyint | `0` | `0` | — |
| `attente` | tinyint | `0` | `0` | — |

**Données Up2Pay**
| Col | Type | Prospect | Payé | Échec |
|-----|------|----------|------|-------|
| `paiement` | smallint | `0` | `189` (ex.) | `190` ou `0` |
| `ajout_paiement` | smallint UNSIGNED | `0` | `0` | `0` |
| `numappel` | text | `''` | `'0280072651'` (ex.) | `''` |
| `numtrans` | text | `''` | `'0620110018'` (ex.) | `''` |
| `numero_cb` | text | `''` | rempli (PAN masqué idéalement) | `''` |
| `up2pay_status` | varchar(100) | `NULL` | `'Capturé'` | `NULL` |
| `up2pay_code_error` | varchar(10) | `NULL` | `NULL` | `'00017'` (ex.) |
| `e_transaction_token` | varchar(200) | `NULL` | `NULL` ou rempli | `NULL` |
| `email_paiement_echoue` | tinyint | `0` | `0` | `1` (set par cron plus tard) |
| `opposition_cb` | tinyint UNSIGNED | `0` | `0` | éventuellement `1` |

**Dates**
| Col | Type | Prospect | Payé |
|-----|------|----------|------|
| `date_preinscription` | date | jour J du form | jour J |
| `date_inscription` | date | `'0000-00-00'` | jour du paiement OK |
| `datetime_preinscription` | timestamp | NOW() | NOW() |
| `date_modification` | timestamp | auto-update | auto-update |
| `last_timestamp` | timestamp | auto CURRENT_TIMESTAMP | auto |

**Commission & facturation**
| Col | Type | Prospect | Payé |
|-----|------|----------|------|
| `commission` | smallint | NULL | NULL ou rempli |
| `commission_ht` | float | `0` | `78.9` (ex.) |
| `partenariat` | tinyint | `0` | `1` (si centre partenaire) |
| `marge_commerciale` | float | `0` | snapshot depuis `marge_commerciale` table par dept |
| `taux_marge_commerciale` | tinyint | `0` | snapshot |
| `prix_index_ttc` | int | `0` | snapshot prix stage |
| `prix_index_min` | int | `0` | snapshot prix min |
| `total_guarantee` | float | `0` | rempli si garantie sérénité |
| `facture` | int | `0` | n° facture |
| `facture_num` | bigint | `0` | `274085` (ex.) |
| `remise` / `reduction` / `code_reduction` | smallint/tinyint/text | `0`/`0`/NULL | éventuellement |

**Remboursement** (post-paiement, hors scope étape 8)
`remboursement`, `date_remboursement`, `type_remboursement`, `attente_remboursement`, `remboursement_prioritaire`, `date_demande_remboursement`, `sepa_remboursement`

#### 🗂️ Bookkeeping / système (~30 cols)
`provenance`, `provenance_site`, `provenance_suppression`, `date_suppression`, `divers`, `commentaire`, `comment_presence_student`, `action_espace_standard`, `cout_ads`, `autoriseDonneesPersonnelles`, `is_sms_*` (4 flags), `presence_*` (4 flags), `animator_validate_data`, `is_directory_validate_member`, `modality_send_ants`, `is_send_directory_to_ants`, `fsp_annulation`, `send_contact_infos`, `late_cancel_48h`, `virement_bloque*` (5 cols), `error_rppc`, `timestamp_verif_rppc`

#### 🇫🇷 ANTS (administration française) (5 cols)
`ants_idDemande`, `ants_numeroDemande`, `ants_statut`, `ants_erreurs`, `presence_au_stage`

#### Newsletter (3 cols)
`newsletter_active`, `newsletter_date_dernier_envoi`, `newsletter_type_dernier_envoi`

#### Reversement (2 cols)
`option_reversement`, `reversement`

#### Pénalité (1 col)
`penalite`

### B.2 Index existants sur `stagiaire`
```
PRIMARY KEY (id),
KEY IDX_idStage         (id_stage),
KEY IDX_supprime        (supprime),
KEY IDX_ants_idDemande  (ants_idDemande),
KEY IDX_status          (status(200)),
KEY IDX_cas             (cas(50)),
KEY IDX_paiement        (paiement),
KEY IDX_virementBloque  (virement_bloque)
```

→ Pas d'index sur `numtrans` ou `numappel` — pour l'idempotence IPN, on cherche par `id` directement (le PBX_CMD = booking ref qui contient l'id), donc PRIMARY KEY suffit.

---

## Section C — Statuts effectifs en production

### C.1 `stagiaire.status`
```sql
SELECT status, COUNT(*) FROM stagiaire GROUP BY status;
```
| `status` | Nombre | Sens |
|----------|--------|------|
| `supprime` | 25 452 | Soft-deleted (annulé, abandonné, refusé...) |
| `inscrit` | 24 548 | **Paiement OK + inscription validée** ✅ |
| `pre-inscrit` | 4 | Prospect en attente de paiement (très peu en BDD à l'instant T) |
| `''` (vide) | 1 | Cas dégénéré |

**Conclusion** : seulement **3 statuts utiles** dans le flux courant. Pas de `'refuse'` séparé — un échec de paiement met le statut à `supprime` (ou laisse à `pre-inscrit` si jamais le client n'a pas tenté).

### C.2 `stagiaire.up2pay_status`
| Valeur | Nombre | Sens |
|--------|--------|------|
| NULL | 29 746 | Anciens paiements (avant ajout colonne) ou jamais initiés |
| `'Capturé'` | 20 247 | Paiement débité avec succès ✅ |
| `'PAYBOX : Numéro de question invalide'` | 8 | Bug rare |
| `'PAYBOX : Transaction non trouvée'` | 2 | Bug rare |
| `'HMAC invalide'` | 1 | Bug critique signature |
| `'Remboursé'` | 1 | Remboursement effectué |

→ Twelvy doit **continuer à écrire `'Capturé'`** sur succès pour rester compatible avec le reporting.
→ `up2pay_status` n'est **PAS écrit par le code visible PSP** dans `validate_payment.php` — set ailleurs (cron `cron_status_payment.php` qui réinterroge Up2Pay). Recommandation : **explicitement** écrire `'Capturé'` / `'Refusé'` / `'Annulé'` côté Twelvy IPN pour avoir une table self-describing.

### C.3 `stagiaire.paiement` (montants en EUR, smallint)
Plages observées : `0` (8 265 lignes — prospect ou stage gratuit), puis `170-260` EUR pour la grande majorité. Max smallint = 32 767 → suffisant pour les stages actuels mais risque overflow si jamais un upsell > 327 €.

⚠️ **Risque** : `smallint` ne supporte pas les centimes. Si Twelvy vend un jour avec décimales, la colonne tronque.

---

## Section D — Sample rows (anonymisés)

### D.1 Cas `inscrit` (paiement OK)
```
id                          = 40120316
id_stage                    = 329207
status                      = 'inscrit'
paiement                    = 189
numappel                    = '0280072651'
numtrans                    = '0620110018'
numero_cb                   = 'XXX' (anonymisé)
up2pay_status               = 'Capturé'
up2pay_code_error           = NULL
facture_num                 = 274085
commission_ht               = 78.9
partenariat                 = 1
supprime                    = 0
```

### D.2 Cas `pre-inscrit` (prospect, pas encore payé)
```
id                          = 40120321
id_stage                    = 329092
status                      = 'pre-inscrit'
paiement                    = 0
numappel                    = ''
numtrans                    = ''
numero_cb                   = ''
up2pay_status               = NULL
facture_num                 = 0
commission_ht               = 0
partenariat                 = 0
supprime                    = 0
```

### D.3 Cas `supprime` avec code erreur Up2Pay
```
id                          = 40120262
id_stage                    = 329448
status                      = 'supprime'
paiement                    = 190             ← montant prévu conservé
numappel                    = ''              ← jamais débité
numtrans                    = ''
up2pay_code_error           = '00017'         ← code Up2Pay capturé
facture_num                 = 274050          ← n° facture pré-réservé même sur échec
supprime                    = 1
```

### D.4 Sample `transaction`
```
id=142223, id_stage=96588, id_stagiaire=142644, id_membre=44,
type_paiement='CB OK', erreur='00000', autorisation='706427',
date_transaction='2014-11-21'
```

⚠️ Le format `type_paiement` n'est pas standardisé — `'CB OK'` (avec espace) ET `'CB_OK'` (avec underscore) coexistent dans la même base. Selon le code PSP, on écrit `'CB_OK'` avec underscore désormais. À reproduire à l'identique pour ne pas casser le reporting.

### D.5 Sample `order_stage`
```
id=138937, user_id=40120317, reference_order='CFPSP_275086',
amount=189, is_paid=1, num_suivi=275086, stage_id=329207,
created='2026-02-20 06:34:51'
```

→ **Insight clé** : `reference_order` = `'CFPSP_' + num_suivi`. `num_suivi` correspond à `stagiaire.facture_num`.
→ Selon le code PSP : `facture_num = num_suivi - 1000` (formule à vérifier — peut-être obsolète vu `facture_num=274085` et `num_suivi=275086` ne diffèrent que de 1001).

### D.6 Sample `archive_inscriptions`
```
id=98980, date_inscription='XXX', id_stagiaire=40120317, id_stage=329207, id_membre=289
```

Une ligne par paiement OK. Très léger.

---

## Section E — Le contrat exact "paiement OK" (5 écritures SQL)

Source : `/Volumes/Crucial X9/PROSTAGES/www_2/src/payment/` analysé par agent + cross-référencé avec données live.

Quand Up2Pay renvoie `codereponse == "00000"` (succès), PSP exécute **5 écritures SQL**. Twelvy doit les reproduire (ou un sous-ensemble documenté avec Kader).

### E.1 `transaction` — UPDATE (la ligne a été insérée plus tôt avec `'cheque_en_attente'`)

Source : `PaymentRepository.php:21`
```sql
UPDATE transaction
   SET type_paiement   = 'CB_OK',
       autorisation    = '$autorisation',     -- Up2Pay 'autorisation'
       paiement_interne = 1
 WHERE id_stagiaire = $studentId
   AND id_stage     = $stageId;
```

**Note** : PSP fait d'abord un INSERT au moment du prospect (`OrderStageRepository.php:41`) avec `type_paiement='cheque_en_attente'`. L'UPDATE ici flippe la même ligne.

### E.2 `order_stage` — UPDATE (la ligne existe depuis le prospect)

Source : `PaymentRepository.php:24` + `OrderStageRepository::updateReferenceOrder()`
```sql
UPDATE order_stage SET is_paid = TRUE WHERE id = $orderId;
UPDATE order_stage SET reference_order = '$reference', num_suivi = $numSuivi WHERE id = $orderId;
```

`$reference` est généré par `GenerateReferenceOrder` (ex. `CFPSP_172888`).
`$numSuivi` = même nombre sans le préfixe `CFPSP_` (ex. `172888`).

### E.3 `stagiaire` — UPDATE (promotion vers `inscrit`) ⚡ **Le contrat le plus critique**

Source : `PaymentRepository.php:77`
```sql
UPDATE stagiaire SET
    supprime              = 0,
    status                = 'inscrit',
    numero_cb             = '$cardNumber',         -- masked PAN
    numappel              = '$numAppel',
    numtrans              = '$numTrans',
    partenariat           = '$partenariat',
    commission_ht         = '$commission_ht',
    date_inscription      = '$date',                -- date('Y-m-d')
    date_preinscription   = '$date',
    datetime_preinscription = '$dateTime',          -- date('Y-m-d H:i:s')
    facture_num           = $facture_num,           -- = $numSuivi - 1000
    marge_commerciale     = $marge_commerciale,    -- snapshot depuis marge_commerciale table
    taux_marge_commerciale = $taux_marge_commerciale,
    prix_index_ttc        = $prix_index_ttc,        -- snapshot stage.prix
    prix_index_min        = $prix_index_min
 WHERE id = $studentId;
```

Source : `validate_payment.php:215-216` et `:304-305`
```php
// On error
UpdateOneFieldStudent($studentId, 'up2pay_code_error', $codereponseFlat);  // e.g. '00100'
// On success
UpdateOneFieldStudent($studentId, 'up2pay_code_error', NULL);
```

**Recommandation Twelvy** : ajouter explicitement `up2pay_status='Capturé'` dans cette UPDATE, même si PSP ne le fait pas (cron le fait à postériori). Plus robuste.

### E.4 `archive_inscriptions` — INSERT (audit log)

Source : `PaymentRepository.php:96`
```sql
INSERT INTO archive_inscriptions (id_stagiaire, id_stage, id_membre)
VALUES ($studentId, $stageId, $memberId);
```

`date_inscription` est auto-populé (DEFAULT CURRENT_TIMESTAMP).

### E.5 `stage` — UPDATE (recalcul capacité — IDEMPOTENT par design)

Source : `StageStateRepository.php:48` (centre partenaire 1060 / FSP) ou `:50` (autres)
```sql
-- Centres standard
UPDATE stage SET nb_places_allouees = $nb_max_places - $nbSubscription,
                 nb_inscrits        = $nbSubscription
 WHERE id = $stageId;

-- Centre partenaire 1060 uniquement
UPDATE stage SET nb_places_allouees = capacite - (nb_inscrits_partenaire + $nbSubscription),
                 nb_inscrits        = nb_inscrits_partenaire + $nbSubscription
 WHERE id = $stageId;
```

`$nbSubscription` est recalculé par `countCurrentSubscription()` — un `SELECT COUNT(*)` frais sur `stagiaire` + `stagiaire_externe`. **C'est ce qui rend cette update idempotente** : re-jouer l'IPN ne décrémente pas deux fois.

Si `stage.nb_places_allouees <= 0`, `is_online` passe à 0 (stage caché du catalogue).

### E.6 ⚡ Idempotence — règle non négociable

**AVANT** d'exécuter les 5 écritures, vérifier dans `ipn.php` :
```sql
SELECT status, numappel, numtrans
FROM stagiaire
WHERE id = $studentId
```

Si `status='inscrit' AND numappel != '' AND numtrans != ''` → **SKIP**, répondre HTTP 200, ne RIEN refaire (sinon : doublon facture, doublon mail, doublon décrément stage si le code de E.5 n'est pas atomique côté DB).

---

## Section F — Le contrat échec de paiement

Source : `validate_payment.php:188-219`

Quand Up2Pay renvoie un code != `'00000'` :

| Action | Effet DB |
|--------|----------|
| `up2pay_code_error` ← `$codereponseFlat` | UPDATE single field sur `stagiaire` |
| `mail_echec_paiement($data)` envoyé | Cron met plus tard `email_paiement_echoue=1` |
| `TrackingPathUserRepository::addTracking('process_payment_return_error', …)` | INSERT dans `tracking_path_user` |
| `TrackingUserPaymentErrorCode::addTrackingError($studentId, $codereponseFlat)` | INSERT dans `tracking_payment_error_code` |

**`status` reste à sa valeur précédente, `numtrans` et `numappel` restent vides.** La ligne reste réutilisable pour un retry par le client.

---

## Section G — Le contrat remboursement (hors scope étape 8 immédiat)

Quand l'opérateur émet un remboursement :
1. `UPDATE stagiaire SET remboursement = $amount, date_demande_remboursement = NOW(), attente_remboursement = 1, iban, bic`
2. Une fois le batch SEPA construit : `INSERT INTO sepa_remboursement_stagiaires (id_stagiaire, sepa, montant)`, puis `UPDATE stagiaire SET sepa_remboursement = $newId, date_remboursement = NOW(), type_remboursement = 1`
3. `INSERT INTO historique_stagiaire ('Remboursement', '<details>')`
4. **Note** : Up2Pay supporte un `IDOPER=4` (refund) avec `numappel`/`numtrans` mais PSP ne l'utilise pas — refunds via SEPA bancaire actuellement.

---

## Section H — Tables connexes (schémas)

### `transaction` (63 247 lignes)
```
id                int          PK auto
id_stage          int          FK stage
id_stagiaire      int          FK stagiaire
id_membre         mediumint    centre partenaire
type_paiement     text         'CB OK', 'CB_OK', 'CB Refusé', 'cheque_en_attente'…
erreur            text         '00000' = succès, sinon code erreur
autorisation      text         numéro autorisation Up2Pay
date_transaction  date
id_externe        int unsigned 0 par défaut
paiement_interne  tinyint      flag interne
virement          int unsigned numéro virement vers centre
```

### `order_stage` (138 936 lignes)
```
id              int             PK auto
user_id         int             id_stagiaire
reference_order varchar(50)     'CFPSP_275086'
amount          float           montant EUR
is_paid         tinyint(1)      0/1
num_suivi       int             275086 ← match facture_num
stage_id        int             FK stage
created         datetime
```

### `archive_inscriptions` (98 980 lignes)
```
id                int unsigned    PK auto
date_inscription  timestamp       DEFAULT CURRENT_TIMESTAMP
id_stagiaire      int             FK stagiaire
id_stage          int             FK stage
id_membre         int             centre partenaire
```

### `tracking_payment_error_code` (3 095 lignes)
```
id            int             PK auto
id_stagiaire  int             FK stagiaire
error_code    varchar(10)     code Up2Pay
date_error    datetime
source        varchar(50)     'up2pay'
```

### `marge_commerciale` (4 lignes — config)
```
id        int             PK
department int            code département (13, 75, etc.)
amount    decimal(10,0)   25 (en EUR ou %)
type      varchar(30)     'PERCENT' | 'EUROS'
active    tinyint(1)      0/1
```

Sample : `(8, 13, 25, 'EUROS', 1)` → Bouches-du-Rhône : marge = 25 €.
Lue par `PaymentRepository::updateStudentData()` via JOIN avec `stage` pour snapshot dans `stagiaire.marge_commerciale` / `taux_marge_commerciale`.

### `historique_stagiaire` (10 lignes — peu utilisé en pratique)
```
id            int unsigned    PK
id_stagiaire  int unsigned    FK
date          timestamp       DEFAULT CURRENT_TIMESTAMP
action        text            'Transfert de stage', 'Annulation', etc.
description   text            free-text
```

Sample : `(11, 305640, '2018-01-13 10:39:54', 'Transfert de stage', 'Ancien: 179426 ... => Nouveau: 179430 ...')`
→ Recommandé pour le nouveau IPN Twelvy : logger `'Up2Pay capture OK'`, `'Refund SEPA'`, `'Chargeback'`.

### `facture_id` (274 084 lignes)
```
id            bigint NOT NULL    -- séquence
id_stagiaire  bigint NOT NULL
```
Compteur séquentiel facture → stagiaire. Probablement géré par un cron / accounting batch.

### Tables NON liées au paiement (à exclure du scope)
- `paiementamende` (lead-capture form "payer mon amende") — pas de id_stagiaire
- `commande_formation` (lead form contact) — pas de paiement
- `facture` / `facture_centre` / `facture_formateur` — comptabilité mensuelle, pas du flux paiement temps réel
- Tous les `*_archive`, `*_avant_premium`, `*_old`, `*_backup_*`, `stage_2024*`, `mc24_*`, `mc25_*` — snapshots historiques

---

## Section I.bis — FAQ pédagogique (clarifications post-audit)

Cette section répond aux questions concrètes posées en relisant l'audit. À garder pour référence rapide.

### Q : Pourquoi `id_membre` est dans `transaction` alors que c'est juste un log de paiement ?

`transaction` n'est pas QUE un log de paiement. C'est aussi l'endroit où PSP enregistre **à qui** le paiement est destiné (le centre partenaire qui hébergera le stagiaire). Schéma rappel :
```
id, id_stage, id_stagiaire, id_membre, type_paiement,
erreur, autorisation, date_transaction, id_externe,
paiement_interne, virement
```

`id_membre` est obligatoire à l'INSERT. **D'où vient cette valeur** ? Elle vient de `stage.id_membre` — chaque stage est hébergé par UN centre, et `stage.id_membre` indique lequel. Quand quelqu'un paie pour stage #329207, on lit `stage.id_membre` pour ce stage et on copie dans `transaction.id_membre`.

### Q : Pourquoi la majorité des lignes `transaction` ont `type_paiement = 'cheque_en_attente'` ?

Cycle de vie d'une ligne `transaction` :
```
1. Form rempli → INSERT transaction avec type_paiement = 'cheque_en_attente'
                 (placeholder, pas un vrai paiement par chèque)
2. Tentative paiement Up2Pay :
   - Succès → UPDATE transaction SET type_paiement = 'CB_OK'
   - Abandon ou échec → la ligne RESTE 'cheque_en_attente' à vie
```

Donc `cheque_en_attente` rows = paiements abandonnés ou jamais finalisés. `CB_OK` rows = paiements CB réussis. Le ratio observé (majorité `cheque_en_attente`) est normal pour de l'e-commerce — beaucoup commencent le formulaire sans aller au bout.

### Q : Où sont les factures dans la BDD ?

⚠️ **Il y a 2 sortes complètement différentes de "factures"** :

**Type 1 — Numéro de facture client (CE QUI NOUS CONCERNE)**
Juste un numéro séquentiel attribué à chaque client payé.
- Stocké dans `stagiaire.facture_num` (bigint, ex. `274085`)
- Compteur dans la table `facture_id` (2 cols `id` + `id_stagiaire`, 274 084 lignes)
- Au paiement, on prend le prochain numéro du compteur, on l'écrit dans `facture_num`
- **Pas de génération de PDF au moment du paiement** — la "facture" envoyée par email au client est un texte/PDF généré qui utilise ce numéro comme référence

**Type 2 — Vraies factures comptables pour centres partenaires (PAS NOTRE PROBLÈME)**
Factures mensuelles que PSP envoie à ses centres partenaires.
- `facture` (11 583 lignes) — entête facture par centre
- `facture_centre` (1 431 lignes), `facture_centre_produit` (1 879 lignes) — détails
- `facture_formateur` (4 079 lignes) — factures formateurs
- **Ces tables ne sont PAS touchées au moment du paiement.** Générées par un batch comptable (cron mensuel probablement). Hors scope Up2Pay.

### Q : C'est quoi `commission_ht` exactement, où vit-il ?

C'est juste **une colonne sur la table `stagiaire`** (type `float`, default 0). Pas une table séparée.

Exemple ligne payée :
```
paiement       = 189   (le client a payé 189 €)
commission_ht  = 78.9  (78,90 € reviennent au centre partenaire)
partenariat    = 1     (c'est un stage en partenariat)
```

→ ~42% de commission sur le prix payé. Le calcul est **figé dans la ligne stagiaire au moment du paiement** (snapshot — la commission historique reste correcte même si le taux change ensuite).

**Où est calculé le montant ?** Dans le code PSP (probablement `PaymentRepository::updateStudentData()`). Le calcul dépend de :
- Prix du stage (`stage.prix`)
- Département (via `marge_commerciale` table — 4 lignes seulement, ex. Bouches-du-Rhône = 25 €)
- Si stage partenariat (`partenariat = 0 ou 1`)
- Possiblement type de contrat partenaire

**On a besoin d'extraire la formule exacte du code PSP** pour la reproduire dans le bridge Twelvy.

### Q : Qui écrit "Capturé" / "Remboursé" dans `up2pay_status` ?

**PAS le script de paiement immédiat (`validate_payment.php`).** Vérifié — ce script écrit `numappel`, `numtrans`, `status='inscrit'`, `numero_cb`, etc. mais ne touche jamais `up2pay_status`.

**C'est un cron job qui le fait** :
```
/Volumes/Crucial X9/PROSTAGES/www_2/planificateur_tache/up2pay/cron_status_payment.php
```

Ce script tourne périodiquement (toutes les quelques heures probablement). Il :
1. Liste les stagiaires payés récemment dont `up2pay_status` est encore NULL
2. Appelle l'API Up2Pay pour réinterroger : "quel est le statut actuel de cette transaction ?"
3. Up2Pay répond `"Capturé"` / `"Remboursé"` / `"Refusé"` / etc.
4. Le cron met à jour `stagiaire.up2pay_status` avec cette valeur

**C'est pour ça que 29 746 lignes ont NULL** — soit ce sont d'anciens paiements pré-cron, soit des paiements très récents que le cron n'a pas encore traités.

**Recommandation Twelvy** : ne pas attendre un cron. Notre `ipn.php` doit écrire `up2pay_status` directement au moment du paiement (`'Capturé'` sur succès, `'Refusé'` sur échec). La ligne stagiaire est self-describing immédiatement.

### Q : C'est quoi `paiement`, où vit-il ?

Juste **une colonne sur `stagiaire`** (type `smallint`, NOT NULL). Stocke le montant payé en EUR (entier, pas de décimales).

Valeurs réelles observées en prod :
- 189, 209, 219, 229, 249 EUR — prix de stage typiques
- 0 EUR — prospect non payé OU stage gratuit

Limite `smallint` : 0 à 32 767. OK pour les stages actuels (€169–€259) mais overflow si un jour on vend > €327 ou avec des centimes.

### Q : Tableau récapitulatif — où vit chaque truc ?

| Truc | Table | Type colonne |
|------|-------|--------------|
| `paiement` (montant) | `stagiaire` | smallint |
| `numappel` (Up2Pay #1) | `stagiaire` | text |
| `numtrans` (Up2Pay #2) | `stagiaire` | text |
| `numero_cb` (CB masquée) | `stagiaire` | text |
| `up2pay_status` ("Capturé"...) | `stagiaire` | varchar(100) |
| `up2pay_code_error` | `stagiaire` | varchar(10) |
| `commission_ht` | `stagiaire` | float |
| `partenariat` (0 ou 1) | `stagiaire` | tinyint |
| `facture_num` (n° facture client) | `stagiaire` | bigint |
| Compteur n° facture client | `facture_id` | séquence bigint |
| Log paiement CB (auto/auth) | `transaction` | ligne complète |
| Référence commande | `order_stage` | ligne complète |
| Audit log inscriptions | `archive_inscriptions` | ligne complète |
| Config commissions partenaires | `commission_main`, `commission_effective` | tables (PAS touchées au paiement) |
| Factures mensuelles partenaires | `facture`, `facture_centre*` | tables (PAS touchées au paiement) |

**Tout ce dont on a besoin pour Up2Pay au moment du paiement = la ligne `stagiaire` + 4 tables sœurs**. Les tables comptables (`facture`, `commission_*`) sont downstream et hors scope.

---

## Section I — 10 décisions critiques à clarifier avec Kader

1. **Scope BDD.** Twelvy IPN écrit dans les 5 tables (transaction, order_stage, stagiaire, archive_inscriptions, stage), ou seulement `stagiaire` ?
   - Si seulement `stagiaire` : `transaction.type_paiement` reste à `'cheque_en_attente'`, `order_stage.is_paid` reste à `0`, `stage.nb_places_allouees` n'est pas décrémenté → casse Simpligestion + l'espace centre.
   - **Recommandation** : reproduire les 5 écritures.

2. **Statut `pre-inscrit` vs `supprime`.** Twelvy écrit aujourd'hui `'pre-inscrit'` via `stagiaire-create.php`. Mais PSP filtre les listings sur `status='inscrit' AND supprime=0`, donc les `'pre-inscrit'` sont invisibles côté back-office PSP jusqu'à promotion. Décision : on garde `'pre-inscrit'` (l'IPN promeut à `'inscrit'` au paiement) ou on mime PSP exactement (création avec `status='supprime'`, `supprime=1`) ?

3. **Le mystère `id_membre`.** Pour `transaction` et `archive_inscriptions`, on a besoin du `id_membre` du centre partenaire. Il vient probablement de `stage.id_membre` — à confirmer en regardant le schéma `stage` (135 cols).

4. **Numéro de facture client (compteur `facture_id`).** Comment l'incrémenter atomiquement pour éviter que deux paiements simultanés aient le même numéro ?
   - Option A : `INSERT INTO facture_id (id_stagiaire) VALUES ($studentId); SELECT LAST_INSERT_ID();`
   - Option B : un cron PSP gère ça déjà — Twelvy n'a peut-être qu'à reprendre une valeur déjà calculée.
   - À vérifier dans le code PSP. **Note** : il s'agit uniquement du numéro client (la "facture" partenaire mensuelle est hors scope).

5. **Calcul de `commission_ht`.** Quelle est la formule exacte ? Probablement dans `PaymentRepository::updateStudentData()`. Dépend du prix stage, du département (table `marge_commerciale`), et du flag `partenariat`. À extraire du code PSP et reproduire dans le bridge Twelvy.

6. **Liste canonique des `up2pay_status`.** Le dump observe : `'Capturé'`, `'Remboursé'`, `'Refusé'` (1 cas), plus quelques messages d'erreur PAYBOX bruts. **Quelle est la liste officielle ?** `'Capturé'`, `'Refusé'`, `'Annulé'`, `'Remboursé'`, `'Pré-autorisé'` ? À confirmer.
   - **NB** : ce champ n'est PAS écrit par le code PSP de paiement immédiat — c'est le cron `cron_status_payment.php` qui le remplit a posteriori. Twelvy doit l'écrire directement à l'IPN pour avoir une ligne self-describing.

7. **PCI / `numero_cb`.** PSP stockait historiquement le PAN complet (sample row 1 de l'agent montre full PAN). Twelvy doit stocker uniquement un PAN masqué (`45XXXX...XX5251`) renvoyé par Up2Pay.

8. **`paiement` smallint.** Limite 32 767. Si jamais Twelvy vend un jour avec décimales ou >327€, la colonne tronque. Migrer vers `decimal(7,2)` avant lancement ?

9. **Référence / `num_suivi`.** PSP utilise `'CFPSP_xxxxx'`. Quel préfixe pour Twelvy ? `'TWLV_xxxxx'` ? Même pool de numérotation ou dédié ?

10. **Refund channel.** Twelvy reste sur SEPA (comme PSP) ou attaque l'API Up2Pay `IDOPER=refund` (plus moderne) ?

---

## Section J — Méthodologie

### J.1 Audit live (16 avril 2026)
1. Script PHP read-only `_audit_temp.php` créé localement
2. Upload via `curl -T` FTP sur `ftp.cluster115.hosting.ovh.net/www/api/`
3. Exécution unique via `https://api.twelvy.net/_audit_temp.php?key=<random_token>`
4. Récupération JSON (835 KB, 315 tables auditées)
5. **Suppression immédiate** du script via `curl --user … -Q "DELE /www/api/_audit_temp.php"` — confirmé HTTP 404 après deletion
6. Données sauvegardées localement à `/tmp/audit_result.json`
7. **Aucune modification, INSERT, UPDATE ou DELETE** — uniquement SHOW/DESCRIBE/SELECT
8. Anonymisation côté script PHP avant export (email/nom/prénom/mobile/adresse/ip/numero_cb → `'XXX'`)

### J.2 Analyse code PSP (en parallèle, agent Explore)
Agent général lancé en parallèle pour cartographier le code PSP en détail. Identifié 10+ fichiers clés :
- `validate_payment.php` (347 lignes, le coordinateur)
- `PaymentRepository.php`, `OrderStageRepository.php`, `StageStateRepository.php` (les writers)
- `UpdateStagePaymentData.php`, `UpdateStageAfterPayment.php` (les services)
- `E_TransactionPayment.php`, `E_TransactionConfig.php`, `E_TransactionError.php` (la couche Up2Pay)
- `LogPayment.php`, `TrackingUserPaymentErrorCode.php` (logging)

L'agent travaillait initialement sur les dumps locaux stale → ses conclusions sur "deux serveurs MySQL séparés" et "transaction n'existe pas" sont **fausses**. Mais son analyse du **code PSP lui-même** (sections E.1-E.5, F, G ci-dessus) est intacte et a été reprise dans ce document.

### J.3 Versions serveur
- MySQL : `8.4.8-8` (LTS récent, supporte utf8mb4 nativement)
- PHP : `5.6.40` (legacy — contrainte du cahier des charges)

---

## Section K — Annexes — Fichiers PSP référencés

```
/Volumes/Crucial X9/PROSTAGES/www_2/src/payment/validate/validate_payment.php
/Volumes/Crucial X9/PROSTAGES/www_2/src/payment/services/UpdateStagePaymentData.php
/Volumes/Crucial X9/PROSTAGES/www_2/src/payment/services/UpdateStageAfterPayment.php
/Volumes/Crucial X9/PROSTAGES/www_2/src/payment/repositories/PaymentRepository.php
/Volumes/Crucial X9/PROSTAGES/www_2/src/payment/repositories/TrackingUserPaymentErrorCode.php
/Volumes/Crucial X9/PROSTAGES/www_2/src/payment/E_Transaction/E_TransactionConfig.php
/Volumes/Crucial X9/PROSTAGES/www_2/src/payment/E_Transaction/E_TransactionPayment.php
/Volumes/Crucial X9/PROSTAGES/www_2/src/payment/E_Transaction/E_TransactionError.php
/Volumes/Crucial X9/PROSTAGES/www_2/src/order/repositories/OrderStageRepository.php
/Volumes/Crucial X9/PROSTAGES/www_2/src/stage/services/UpdateStageAfterPayment.php
/Volumes/Crucial X9/PROSTAGES/www_2/src/stage/repositories/StageStateRepository.php
/Volumes/Crucial X9/PROSTAGES/www_2/src/student/repositories/StudentRepository_mc24.php
/Volumes/Crucial X9/PROSTAGES/www_2/src/logging/LogPayment.php
```

---

**Document rédigé le 16 avril 2026 — Étape 1 du plan Up2Pay terminée.**
**Prochaine étape** : valider ce contrat avec Kader (Section I, décisions 1-10), puis attaquer Étape 2 (cartographie complète du flux PHP en mode dynamique avec exemples runtime).
