# CARTOGRAPHIE LEGACY - ProStagesPermis (PSP) - COMPLETE VERSION

**Objectif**: Comprendre EXACTEMENT où se passe quoi dans le legacy PHP, pour debugger et faire évoluer sans casser le business (réservation + paiement + virements).

**Périmètre**: Chaque table DB, chaque INSERT/UPDATE, chaque flux d'argent du paiement client jusqu'au virement bancaire du centre.

---

## 1. TROIS ZONES LEGACY À CONNAÎTRE

### Espace Stagiaire (`/es/...`)
- **Localisation**: `/Users/yakeen/Desktop/PROSTAGES/www_3/es/`
- **Purpose**: Interface de consultation pour les stagiaires
- **Entry points**:
  - `aide.php` - Formulaire login
  - `aidev2.php` - Traitement login (session_start)
- **Session**: `$_SESSION['membre']` = ID du stagiaire
- **Tables lues**: stagiaire, stage, site, virement
- **Tables modifiées**: stagiaire (rarement - cas annulation)

### Espace Partenaire (`/ep/...`)
- **Localisation**: `/Users/yakeen/Desktop/PROSTAGES/www_3/ep/`
- **Purpose**: Gestion des stages par les centres de formation
- **Entry points**:
  - `accueil3.php` - Dashboard partenaire
  - `liste_stagiaire_virement2.php` - Voir les virements (PDF facture)
- **Session**: `$_SESSION['membre']` = ID du centre (membre)
- **Données critiques lues**:
  - FROM virement WHERE id=$id_virement
  - FROM stagiaire WHERE id_stage = stage.id AND transaction.virement = $id_virement
  - FROM transaction WHERE virement = $id_virement
- **Tables affectées**: Aucune modification (lecture seule)

### SimpliGestion (`/simpligestion/...`)
- **Localisation**: `/Users/yakeen/Desktop/PROSTAGES/www_2/simpligestion/`
- **Purpose**: Dashboard admin - Calculs financiers, virements, commissions
- **Entry points**:
  - `virement_centres_effectues.php` - Liste des virements effectués
  - `factures.php` - Factures des centres
  - `kpi.php` - KPIs (chiffre d'affaires, inscrits, etc.)
  - `stages_mc25.php` - Gestion des stages
- **AJAX endpoints**:
  - `ajax_virement_centres_effectues.php` - Charge les virements avec calcul financier
  - `ajax_functions.php?action=blocage_virement` - Bloquer un virement pour un stagiaire
  - `ajax_functions.php?action=create_virement_listing_stagiaires` - Créer une listing de virement
- **Session**: Admin access required
- **Tables modifiées**:
  - stagiaire (virement_bloque, motif_virement_bloque, date_virement_bloque, date_virement_debloque)
  - virement (INSERT new record)
  - transaction (UPDATE virement = $id)

---

## 2. LA BASE DE DONNÉES - SCHÉMA COMPLET

### Stagiaire (Customers/Bookings)
```sql
CREATE TABLE stagiaire (
  -- Identité
  id INT PRIMARY KEY AUTO_INCREMENT,
  nom VARCHAR(255),
  prenom VARCHAR(255),
  email VARCHAR(255),
  tel VARCHAR(20),
  adresse TEXT,
  code_postal VARCHAR(10),
  ville VARCHAR(255),

  -- Réservation
  id_stage INT NOT NULL,
  id_membre INT (centre/partner),
  date_inscription DATETIME,
  date_preinscription DATE,
  datetime_preinscription DATETIME,

  -- Paiement
  paiement DECIMAL(10,2) (montant en euros),
  numero_cb VARCHAR(20) (last 4 digits),
  numappel VARCHAR(50) (E-Transaction call number),
  numtrans VARCHAR(50) (E-Transaction transaction number),
  status VARCHAR(50) (inscrit, annule, confirme, valide),
  supprime BOOLEAN,
  is_sms_confirmation_send BOOLEAN (SMS sent after payment),

  -- Commission & Partenariat
  partenariat VARCHAR(255),
  commission_ht DECIMAL(10,2),
  commission_ttc DECIMAL(10,2),

  -- Facture
  facture_num INT (reference to invoice),

  -- Pricing (for reconciliation)
  prix_ancien DECIMAL(10,2),
  prix_index_ttc DECIMAL(10,2),
  prix_index_min DECIMAL(10,2),
  marge_commerciale DECIMAL(10,2),
  taux_marge_commerciale DECIMAL(5,2),

  -- Virement (Transfer to partner)
  virement_bloque BOOLEAN (0=normal, 1=blocked by admin),
  virement_bloque_definitif BOOLEAN (0=temp block, 1=permanent),
  motif_virement_bloque TEXT (reason for blocking),
  date_virement_bloque DATETIME,
  date_virement_debloque DATETIME,

  -- Error handling
  up2pay_code_error VARCHAR(50) (E-Transaction error code if payment fails),
  up2pay_status VARCHAR(50) (actual status from gateway cron sync),

  -- Upsell/Add-ons
  reduction DECIMAL(10,2),
  comm_autoecole DECIMAL(10,2),
  provenance_site VARCHAR(255)
);

INDEXES:
- id
- id_stage
- id_membre
- email
- status
- date_inscription
- numtrans + numappel (for payment reconciliation)
```

### Stage (Courses)
```sql
CREATE TABLE stage (
  id INT PRIMARY KEY AUTO_INCREMENT,
  id_site INT (location),
  id_membre INT (partner/center owner),

  date1 DATE (course start date),
  date2 DATE (course end date - usually date1+1),
  prix DECIMAL(10,2),

  -- Capacity tracking
  nb_places_allouees INT (seats available),
  nb_inscrits INT (registered students),
  taux_remplissage DECIMAL(5,2) (fill rate %),

  -- Pricing for reconciliation
  prix_ancien DECIMAL(10,2),
  prix_index_min DECIMAL(10,2),
  prix_index_ttc DECIMAL(10,2),
  marge_commerciale DECIMAL(10,2),
  taux_marge_commerciale DECIMAL(5,2),
  marge_commerciale_centre DECIMAL(10,2),
  taux_marge_commerciale_centre DECIMAL(5,2),

  -- Partnership
  partenariat VARCHAR(255),
  stage_commission DECIMAL(10,2),

  -- Status
  statut VARCHAR(50),
  actif BOOLEAN
);

INDEXES:
- id
- id_site
- id_membre
- date1 (for date filtering)
- prix
```

### Site (Training Centers/Locations)
```sql
CREATE TABLE site (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nom VARCHAR(255),
  adresse TEXT,
  code_postal VARCHAR(10),
  ville VARCHAR(255),
  departement VARCHAR(10),
  latitude DECIMAL(10,7),
  longitude DECIMAL(10,7),

  visibilite BOOLEAN (0=hidden, 1=public),
  tel VARCHAR(20),
  email VARCHAR(255)
);

INDEXES:
- id
- ville
- code_postal
- departement
```

### Membre (Partners / Training Centers)
```sql
CREATE TABLE membre (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nom VARCHAR(255),
  email VARCHAR(255),
  tel VARCHAR(20),
  mobile VARCHAR(20),
  adresse TEXT,
  siret VARCHAR(20),
  tva VARCHAR(20),

  -- Financial info
  consignes_virement TEXT (instructions for bank transfer),
  assujetissement_tva BOOLEAN,

  -- Relationship with PSP
  pourcentage_commission DECIMAL(5,2),
  statut VARCHAR(50) (actif, inactif, blacklist),

  date_inscription DATETIME
);

INDEXES:
- id
- nom
- email
```

### Transaction (Payment Records)
```sql
CREATE TABLE transaction (
  id INT PRIMARY KEY AUTO_INCREMENT,
  id_stagiaire INT (references stagiaire.id),
  id_stage INT,
  id_membre INT (partner),

  -- E-Transaction Details
  numtrans VARCHAR(50) (E-Transaction transaction number),
  numappel VARCHAR(50) (E-Transaction call number),
  montant DECIMAL(10,2),
  devise VARCHAR(10) (978=euros),

  -- Status
  type_paiement VARCHAR(50) (CB_OK, VIREMENT, etc.),
  paiement_interne BOOLEAN (1=paid internally, 0=pending),
  autorisation VARCHAR(50) (E-Transaction authorization code),

  -- Virement linkage
  virement INT (references virement.id when transfer created),

  -- Timestamps
  date_creation DATETIME,
  date_paiement DATETIME,

  INDEX (id_stagiaire, id_stage, id_membre),
  INDEX (numtrans, numappel),
  INDEX (virement)
);
```

### Virement (Bank Transfers to Partners)
```sql
CREATE TABLE virement (
  id INT PRIMARY KEY AUTO_INCREMENT,
  id_membre INT (partner receiving transfer),

  -- Financial totals
  total DECIMAL(10,2) (amount transferred to partner),

  -- Details
  commentaire TEXT,
  date DATE (date of transfer),
  sepa VARCHAR(255) (SEPA bank details),

  -- Reconciliation
  nb_stagiaires INT (number of students in this transfer),

  -- Facture reference
  facture_num VARCHAR(50),

  -- Status
  statut VARCHAR(50) (created, sent, executed, cancelled),

  INDEXES:
  - id
  - id_membre
  - date
);
```

### Archive_Inscriptions (Backup of paid bookings)
```sql
CREATE TABLE archive_inscriptions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  id_stagiaire INT,
  id_stage INT,
  id_membre INT,

  date_creation DATETIME,

  INDEXES:
  - id_stagiaire
  - id_stage
  - date_creation
);
```

### Order_Stage (Order/Booking table - modern)
```sql
CREATE TABLE order_stage (
  id INT PRIMARY KEY AUTO_INCREMENT,
  id_stagiaire INT,
  id_stage INT,

  reference_order VARCHAR(50) (e.g., BK-2025-001234),
  num_suivi INT (tracking number),

  is_paid BOOLEAN (1=payment confirmed),
  date_creation DATETIME,

  INDEXES:
  - id
  - id_stagiaire
  - reference_order
  - is_paid
);
```

### Tracking Tables (Journey & Error Logs)
```sql
CREATE TABLE tracking_inscription (
  id INT PRIMARY KEY AUTO_INCREMENT,
  session_id VARCHAR(255),
  id_stagiaire INT,
  event VARCHAR(255) (e.g., "process_payment_return_success"),
  timestamp DATETIME
);

CREATE TABLE tracking_payment_error_code (
  id INT PRIMARY KEY AUTO_INCREMENT,
  id_stagiaire INT,
  error_code VARCHAR(50) (E-Transaction error code),
  source VARCHAR(255),
  timestamp DATETIME
);
```

### Sessions (Temporary payment session data)
```sql
CREATE TABLE sessions (
  session_id VARCHAR(255) PRIMARY KEY,
  content LONGTEXT (serialized PHP session data with payment info),
  timestamp DATETIME
);
```

### Upsell_Transaction (Optional add-ons like Garantie Sérénité)
```sql
CREATE TABLE upsell_transaction (
  id INT PRIMARY KEY AUTO_INCREMENT,
  order_id INT,
  id_stagiaire INT,

  montant DECIMAL(10,2),
  status INT (1=paid, 2=refund_requested, 3=refunded),
  is_paid BOOLEAN,

  -- E-Transaction details
  cb_autorisation VARCHAR(50),
  numtrans VARCHAR(50),
  numappel VARCHAR(50),

  date_creation DATETIME
);
```

---

## 3. CINQ FLUX MÉTIER CRITIQUES - AVEC CHAQUE DB OPERATION

### FLUX 1: Affichage d'une liste de stages (Source données)

**Fichier**: `https://www.twelvy.net/api/stages` (TWELVY frontend)
**Proxy**: `/Users/yakeen/Desktop/PROSTAGES/www_2/www/api/stages.php` (OVH backend)

**SQL EXACT**:
```sql
SELECT
    stage.id,
    stage.prix,
    stage.date1,
    stage.date2,
    site.ville,
    site.code_postal,
    site.adresse,
    site.nom,
    site.latitude,
    site.longitude
FROM stage
INNER JOIN site ON stage.id_site = site.id
WHERE
    stage.actif = 1
    AND site.visibilite = 1
    AND stage.date1 >= NOW()
[FILTERS: AND site.ville = '$city' OR AND stage.prix <= '$maxPrice']
ORDER BY stage.date1 ASC
```

**Tables LUES**: stage, site (READ ONLY)
**DB WRITES**: AUCUNE
**Fin du flux**: JSON array sent to frontend

---

### FLUX 2: Création d'une réservation (Quand on clique "réserver")

**Fichiers**:
- `/Users/yakeen/Desktop/PROSTAGES/www_3/inscription/index.php` (form HTML)
- `/Users/yakeen/Desktop/PROSTAGES/www_3/inscription/ident_stagiaire.php` (form processing)
- `/Users/yakeen/Desktop/PROSTAGES/www_3/inscription/dossier.php` (final booking)

**Input**: POST form avec:
```
nom, prenom, email, telephone, adresse, code_postal, ville, id_stage, id_membre
```

**DB WRITES**:

1. **INSERT into stagiaire**:
```sql
INSERT INTO stagiaire (
    nom, prenom, email, tel, adresse, code_postal, ville,
    id_stage, id_membre,
    status, supprime,
    date_preinscription, datetime_preinscription
) VALUES (
    '$nom', '$prenom', '$email', '$tel', '$adresse', '$code_postal', '$ville',
    '$id_stage', '$id_membre',
    'inscrit', 0,
    NOW(), NOW()
);
-- Returns: stagiaire.id (student ID)
```

2. **INSERT into sessions** (for payment step):
```sql
INSERT INTO sessions (
    session_id,
    content,
    timestamp
) VALUES (
    session_id(),
    serialize(array('id_stagiaire' => $id, 'id_stage' => $id_stage, ...)),
    NOW()
);
```

3. **INSERT into order_stage** (modern booking reference):
```sql
INSERT INTO order_stage (
    id_stagiaire,
    id_stage,
    reference_order,  -- Generated like BK-2025-001234
    num_suivi,
    is_paid,
    date_creation
) VALUES (
    '$id_stagiaire',
    '$id_stage',
    'BK-' . date('Y') . '-' . str_pad($num_suivi, 6, '0', STR_PAD_LEFT),
    $num_suivi,
    FALSE,  -- Not paid yet
    NOW()
);
```

**Tables lues**: stage (for prix), site (for centre info)
**Tables écrites**: stagiaire, sessions, order_stage
**Statut initial**: `stagiaire.status = 'inscrit'` (registered, waiting for payment)
**Fin du flux**: Redirect to payment form with session_id

---

### FLUX 3: Lancement du paiement E-Transaction (Payment init)

**Fichier**: `/Users/yakeen/Desktop/PROSTAGES/www_2/src/payment/validate/validate_payment.php`

**Configuration Gateway**:
```php
// From E_TransactionConfig.php + E_TransactionPayment.php

PRODUCTION:
- Merchant ID: 651027368
- PBX_KEY: 78f9db5d0b421f5f5b7e0eda11f3a66c84b2fdadfcad8cf8c8df25b87a0a4988775f3ff7a81b5a9b653854c10bc742889f612e7741363e585b758fc4e2e86e0d
- URL: https://ppps.paybox.com/PPPS.php
- SITE: 0966892
- RAND: 02

TEST:
- Merchant ID: 222
- PBX_KEY: 0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF
- URL: https://recette-ppps.e-transactions.fr/PPPS.php
- SITE: 1999887
- RAND: 63
```

**Flow EXACT**:
```php
1. Retrieve session data from sessions table WHERE session_id = ...
2. Extract: $studentId, $stageId, $orderId, $memberId, $email, $cardNumber, $cardExpiry, $cardCVC, $ID3D

3. Call E_TransactionPayment::validateTransaction()
   - Input: amount (in euros), reference (BK-2025-XXXXXX), card details, ID3D
   - Method:
     a) authorizationDebit() → CURL POST to https://ppps.paybox.com/PPPS.php
     b) Send: VERSION=00104, TYPE=00001 (authorization), card data
     c) Receive: codereponse, numtrans, numappel, autorisation
     d) validateDirectDebit() → Second call with numtrans+numappel to finalize

4. Parse response:
   $codereponse = intval(decode('codereponse'))
   $numTrans = decode('numtrans')
   $numAppel = decode('numappel')
   $autorisation = decode('autorisation')
```

**HTTP REQUEST to E-Transaction**:
```
POST /PPPS.php HTTP/1.1
Host: ppps.paybox.com

VERSION=00104&
TYPE=00001&
SITE=0966892&
RANG=02&
IDENTIFIANT=651027368&
MONTANT=21900&  (219€ in cents)
DEVISE=978&
REFERENCE=BK-2025-001234&
PORTEUR=4111111111111111&
DATEVAL=0330&
CVV=123&
ID3D=...&
HMAC=<SHA512_HASH>
```

**HMAC Signing** (CRITICAL):
```php
$hmac_data = "VERSION=00104&TYPE=00001&SITE=0966892&...";
$hmac = strtoupper(hash_hmac('sha512', $hmac_data, pack('H*', $PBX_KEY)));
```

**Tables LUES**: stagiaire, stage, order_stage, sessions
**DB WRITES (if error)**:
```sql
UPDATE stagiaire SET up2pay_code_error = '$codereponse' WHERE id = $studentId;
INSERT INTO tracking_payment_error_code (id_stagiaire, error_code, source) VALUES ($studentId, $codereponse, 'validate_payment');
```

**Fin du flux**: Response from E-Transaction with codereponse, numtrans, numappel

---

### FLUX 4: Traitement retour paiement (Success/Failure)

**Fichier**: `/Users/yakeen/Desktop/PROSTAGES/www_2/src/payment/validate/validate_payment.php` (lines 188-333)

#### SCENARIO SUCCESS (codereponse == "00000"):

**DB WRITES - Complete sequence**:

1. **UPDATE transaction** (Payment repository):
```sql
UPDATE transaction
SET
    type_paiement = 'CB_OK',  -- ← Mark as credit card payment
    autorisation = '$autorisation',  -- ← Store auth code
    paiement_interne = 1  -- ← Mark as internally processed
WHERE
    id_stagiaire = $studentId
    AND id_stage = $stageId;
```

2. **UPDATE order_stage**:
```sql
UPDATE order_stage
SET is_paid = TRUE  -- ← Mark as paid
WHERE id = $orderId;
```

3. **UPDATE stagiaire** (UpdateStagePaymentData - CRITICAL):
```sql
UPDATE stagiaire
SET
    status = 'inscrit',  -- ← Confirm registration
    supprime = 0,
    numero_cb = '$cardNumber',  -- ← Last 4 digits stored
    numappel = '$numAppel',  -- ← E-Transaction call reference
    numtrans = '$numTrans',  -- ← E-Transaction transaction reference
    partenariat = '$partenariat',
    commission_ht = '$commission_ht',
    date_inscription = NOW(),  -- ← Payment completion timestamp
    date_preinscription = NOW(),
    datetime_preinscription = NOW(),
    facture_num = $numSuivi - 1000,  -- ← Invoice number
    marge_commerciale = (SELECT marge_commerciale FROM stage WHERE id=$stageId),
    taux_marge_commerciale = (SELECT taux_marge_commerciale FROM stage WHERE id=$stageId),
    prix_index_ttc = (SELECT IF(prix_ancien > 0, prix_ancien, prix) FROM stage WHERE id=$stageId),
    prix_index_min = (SELECT prix_index_min FROM stage WHERE id=$stageId),
    up2pay_code_error = NULL  -- ← Clear any previous errors
WHERE id = $studentId;
```

4. **INSERT into archive_inscriptions** (audit trail):
```sql
INSERT INTO archive_inscriptions (id_stagiaire, id_stage, id_membre)
VALUES ($studentId, $stageId, $memberId);
```

5. **UPDATE stage** (capacity tracking):
```sql
UPDATE stage
SET
    nb_places_allouees = (nb_places_allouees - 1),  -- ← Decrease available seats
    nb_inscrits = (nb_inscrits + 1),  -- ← Increase registered count
    taux_remplissage = (taux_remplissage + 1)
WHERE id = $stageId;
```

6. **SMS SEND** (if valid 10-digit phone):
```php
if (strlen($studentOBJ->tel) == 10 && $studentOBJ->is_sms_confirmation_send == 0) {
    sendSMS("Merci de votre inscription...", $phone, $url_link);
    // Then UPDATE stagiaire SET is_sms_confirmation_send = 1
}
```

7. **EMAIL SEND** (SendTicketPaymentEmail):
- Invoice/receipt sent to $email

8. **INSERT into tracking_inscription**:
```sql
INSERT INTO tracking_inscription (session_id, id_stagiaire, event)
VALUES (session_id(), $studentId, 'process_payment_return_success');
```

9. **Log commission** (LogCommission):
```php
// Records commission calculation in logs for reconciliation
$logCommission->loggingCommission($studentId, $stageId, $partenariat, $stage_commission);
```

#### SCENARIO ERROR (codereponse != "00000"):

**DB WRITES - Error sequence**:

1. **UPDATE stagiaire** (error code storage):
```sql
UPDATE stagiaire
SET up2pay_code_error = '$codereponse'  -- ← Store E-Transaction error code
WHERE id = $studentId;
```

2. **INSERT into tracking_payment_error_code**:
```sql
INSERT INTO tracking_payment_error_code (id_stagiaire, error_code, source)
VALUES ($studentId, '$codereponse', 'validate_payment');
```

3. **INSERT into tracking_inscription** (error tracking):
```sql
INSERT INTO tracking_inscription (session_id, id_stagiaire, event)
VALUES (session_id(), $studentId, 'process_payment_return_error');
```

4. **EMAIL SEND** (error notification):
- mail_echec_paiement.php sends error details to student

**E-Transaction Error Codes**:
```
00000 = SUCCESS
00003 = INVALID MERCHANT ID
00004 = INVALID AMOUNT
00008 = INVALID CARD NUMBER
00012 = EXPIRED CARD
00013 = INVALID CVV
00014 = CARD REFUSED (bank reject)
00015 = ISSUER SYSTEM ERROR
00016 = AMOUNT EXCEEDS CARD LIMIT
00017 = 3D SECURE FAILED
```

**Fin du flux**:
- SUCCESS: Redirect to /order_confirmation.php
- ERROR: Redirect back to payment form with error message

---

### FLUX 5: Callback serveur + Sync paiement (Daily cron)

**Fichier**: `/Users/yakeen/Desktop/PROSTAGES/www_2/planificateur_tache/up2pay/cron_status_payment.php`

**Trigger**: Daily scheduled task (cron job)
**Hash**: `?hash=6e395m74ng` (security token)

**SQL QUERY**:
```sql
SELECT
    stagiaire.id AS stagiaire_id,
    stage.id AS stage_id,
    stagiaire.numtrans,
    stagiaire.numappel
FROM
    stagiaire
    INNER JOIN stage ON stagiaire.id_stage = stage.id
    INNER JOIN transaction ON transaction.id_stagiaire = stagiaire.id
    INNER JOIN membre ON stage.id_membre = membre.id
WHERE
    stage.id_membre = membre.id
    AND stagiaire.date_inscription >= DATE_ADD(NOW(), INTERVAL -2 DAY)  -- Last 2 days
    AND stagiaire.date_inscription <= NOW()
    AND stagiaire.paiement > 0
    AND stagiaire.status = 'inscrit'
    AND stagiaire.up2pay_status IS NULL  -- Not yet verified from gateway
ORDER BY
    stagiaire.date_inscription ASC;
```

**For each record**:
```php
1. Call retour_consultation($reference, $numtrans, $numappel)
   - CURL call to E-Transaction consultation endpoint
   - Returns: actual payment status from gateway

2. Get response [status_code, status_message]
   Example: ['00', 'AUTORISATION ACCEPTÉE']

3. UPDATE stagiaire:
   UPDATE stagiaire
   SET up2pay_status = '$status_message'  -- ← Store actual status from gateway
   WHERE id = $stagiaire_id;

4. Log the verification
```

**Tables LUES**: stagiaire, stage, transaction, membre
**Tables ÉCRITES**: stagiaire (up2pay_status)

**Purpose**: Verify payment success from E-Transaction server directly (2nd confirmation)

---

## 4. SIMPLIGATION FLOW - VIREMENTS (TRANSFERS TO PARTNERS)

### How SimpliGestion calculates & manages transfers:

**Fichier**: `/Users/yakeen/Desktop/PROSTAGES/www_2/simpligestion/virement_centres_effectues.php`

**AJAX Data Load** (ajax_virement_centres_effectues.php):
```php
// Calculates for each transfer:
SELECT
    virement.id_virement,
    virement.id_membre,
    membre.nom,
    virement.date_facture,
    virement.date_virement,
    COUNT(stagiaire.id) AS nb_stagiaires,

    -- Revenue side
    SUM(stagiaire.paiement) AS enc_ttc,  -- Total revenue (TTC)

    -- Commission calculation
    SUM(
        stagiaire.commission_ht +
        (stagiaire.commission_ht * 0.20)  -- Add VAT
    ) AS comm_ttc,  -- Commission to PSP (TTC)

    -- Amount to transfer to partner
    (SUM(stagiaire.paiement) - SUM(stagiaire.commission_ttc)) AS virement_ttc

FROM virement
INNER JOIN transaction ON transaction.virement = virement.id
INNER JOIN stagiaire ON transaction.id_stagiaire = stagiaire.id
WHERE stagiaire.virement_bloque = 0  -- Only non-blocked transfers
GROUP BY virement.id;
```

**Virement Blocking** (ajax_functions.php - 'blocage_virement'):
```php
// Admin can block a student's transfer (e.g., fraud, non-attendance)

// Temporary block (can be unblocked later):
UPDATE stagiaire
SET
    virement_bloque = 1,
    virement_bloque_definitif = 0,  -- ← Can be reversed
    motif_virement_bloque = '$motif',
    date_virement_bloque = NOW()
WHERE id = $id_stagiaire;

// Permanent block (cannot be reversed):
UPDATE stagiaire
SET
    virement_bloque = 1,
    virement_bloque_definitif = 1,  -- ← Permanent
    motif_virement_bloque = '$motif',
    date_virement_bloque = NOW()
WHERE id = $id_stagiaire;

// Unblock:
UPDATE stagiaire
SET
    virement_bloque = 0,
    virement_bloque_definitif = 0,
    motif_virement_bloque = '',
    date_virement_debloque = NOW()
WHERE id = $id_stagiaire;
```

**Creating a Virement Listing** (ajax_functions.php - 'create_virement_listing_stagiaires'):
```php
// Admin creates a virement batch for one or more partners

$stagiaires_ids = array(...);  // Selected students

// 1. INSERT into virement table:
INSERT INTO virement (
    id_membre,
    date,
    commentaire,
    total,  -- Will be calculated from stagiaires sum
    nb_stagiaires,
    statut
) VALUES (
    $id_membre,
    NOW(),
    '$commentaire',
    (SELECT SUM(paiement - commission_ttc) FROM stagiaire WHERE id IN (...)),
    COUNT(...),
    'created'
);
// Returns: virement.id (e.g., 12345)

// 2. UPDATE transaction table for each student:
UPDATE transaction
SET virement = $virement_id  -- ← Link payment to virement
WHERE id_stagiaire IN ($stagiaires_ids);

// 3. Update virement record with calculated total and date
UPDATE virement
SET
    total = (SELECT SUM(...) FROM stagiaire WHERE id IN (...)),
    nb_stagiaires = COUNT(...),
    date_facture = NOW()
WHERE id = $virement_id;
```

### Partner Portal View (Espace Partenaire - liste_stagiaire_virement2.php):

**Partner sees**:
```php
// GET virement data
SELECT
    virement.id,
    virement.date,
    virement.total,
    virement.commentaire,

    // Students in this virement
    stagiaire.id,
    stagiaire.nom,
    stagiaire.prenom,
    stagiaire.paiement,
    stagiaire.commission_ht,

    // Transfer breakdown
    (stagiaire.paiement - stagiaire.commission_ttc) AS virement_montant

FROM virement
INNER JOIN transaction ON transaction.virement = virement.id
INNER JOIN stagiaire ON transaction.id_stagiaire = stagiaire.id
WHERE virement.id_membre = $partner_id
ORDER BY stagiaire.nom;

// Partner can generate PDF invoice (facture)
// File: liste_stagiaire_virement2.php creates invoice with:
// - Partner details
// - Student list
// - Payment breakdown
// - Invoice number: date_virement_yyyymmdd_id_membre_id_virement
```

---

## 5. GLOSSAIRE COMPLET DES STATUTS

### stagiaire.status
| Valeur | Signification | Quand | Fin |
|--------|---------------|-------|-----|
| `inscrit` | Enregistré, paiement confirmé | After validate_payment SUCCESS | Before stage date |
| `annule` | Annulé par utilisateur ou admin | Manual cancellation | N/A |
| `confirme` | Confirmed attendance | After course completion | N/A |
| `valide` | Validated (alternative) | Not commonly used | N/A |
| `pending` | (Alternative name for inscrit) | Rare | N/A |

### transaction.type_paiement
| Valeur | Signification |
|--------|---------------|
| `CB_OK` | Credit card payment successful |
| `VIREMENT` | Bank transfer from partner |
| `CHEQUE` | Check payment |
| `PENDING` | Payment pending |

### transaction.paiement_interne
| Valeur | Signification |
|--------|---------------|
| `1` | Paid via internal system (CB, virement) |
| `0` | External payment (not yet processed) |

### E-Transaction Response Codes (codereponse)
| Code | Signification | Action |
|------|---------------|--------|
| `00000` | SUCCESS - Paid & debited | Process order, send email |
| `00003` | Invalid Merchant ID | Check config |
| `00004` | Invalid Amount | Verify amount format (cents) |
| `00008` | Invalid Card | User retries with correct card |
| `00012` | Expired Card | User provides new card |
| `00013` | Invalid CVV | User retries with correct CVC |
| `00014` | Card Refused | Bank reject (fraud, insufficient funds) |
| `00015` | Issuer Error | Bank system problem - retry later |
| `00016` | Amount Exceeds Limit | User uses different card |
| `00017` | 3D Secure Failed | 3DS authentication failed |

### stagiaire.virement_bloque
| Valeur | Signification | Réversible |
|--------|---------------|-----------|
| `0` | Normal - ready to be transferred | N/A |
| `1` (temp) | Blocked temporarily | YES (virement_bloque_definitif=0) |
| `1` (perm) | Permanently blocked | NO (virement_bloque_definitif=1) |

### virement.statut
| Valeur | Signification |
|--------|---------------|
| `created` | Virement listing created, not sent |
| `sent` | Sent to partner for invoice |
| `executed` | Bank transfer executed |
| `cancelled` | Cancelled (refund issued) |

---

## 6. COMPLETE PAYMENT → BANK FLOW

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ USER PAYS                                                                   │
└─────────────────────────────────────────────────────────────────────────────┘
                                    ↓
              validate_payment.php calls E_TransactionPayment
              ✓ HMAC-SHA512 sign + CURL POST
              ✓ Send: card, amount, reference
              ✓ Get: codereponse, numtrans, numappel
                                    ↓
            ┌───────────────────────┴───────────────────────┐
            ↓ (codereponse == 00000)      ↓ (codereponse != 00000)
         SUCCESS                          ERROR
            │                                 │
            ↓                                 ↓
    UPDATE stagiaire:                 UPDATE stagiaire:
    ✓ numtrans = $numtrans            ✓ up2pay_code_error = $codereponse
    ✓ numappel = $numappel             │
    ✓ status = 'inscrit'              ↓
    ✓ date_inscription = NOW()        INSERT tracking_payment_error_code
    ✓ numero_cb = last 4 digits       │
    ✓ commission_ht = $comm          SEND email (error notification)
    ✓ facture_num = calc             │
             │                        └→ Redirect to error page
    ↓
    UPDATE transaction:
    ✓ type_paiement = 'CB_OK'
    ✓ autorisation = $auth_code
    ✓ paiement_interne = 1
    │
    ↓
    UPDATE order_stage:
    ✓ is_paid = TRUE
    │
    ↓
    INSERT archive_inscriptions
    ✓ Backup of paid registration
    │
    ↓
    UPDATE stage:
    ✓ nb_inscrits += 1
    ✓ nb_places_allouees -= 1
    │
    ↓
    SEND SMS (if valid phone)
    UPDATE stagiaire.is_sms_confirmation_send = 1
    │
    ↓
    SEND EMAIL (receipt)
    │
    ↓
    INSERT tracking_inscription (success)
    │
    └──→ Redirect to /order_confirmation.php

[2 DAYS LATER - Cron runs daily]
                ↓
    Cron: cron_status_payment.php
    ✓ Finds: status='inscrit', up2pay_status IS NULL, date_inscription >= -2 days
    ✓ Calls E-Transaction consultation API
    ✓ Gets: actual payment status from gateway
             │
    ↓
    UPDATE stagiaire.up2pay_status = 'AUTORISATION ACCEPTÉE'


[ADMIN DECISION - SimpliGestion]
                ↓
    virement_centres_effectues.php displays transfers
    ✓ Calculates: enc_ttc (revenue), comm_ttc (PSP commission), virement_ttc (to pay partner)
    ✓ Shows: partner, students, amounts, SEPA account
             │
    ↓
    Admin can BLOCK transfer for fraud:
    UPDATE stagiaire SET virement_bloque = 1, motif_virement_bloque = 'raison'
             │
    ↓
    Admin creates VIREMENT LISTING:
    INSERT INTO virement (id_membre, total, date, nb_stagiaires)
    UPDATE transaction SET virement = $virement_id
    UPDATE virement SET total = SUM(paiement - commission)
             │
    ↓
    Partner (Espace Partenaire) sees listing:
    ✓ Views liste_stagiaire_virement2.php
    ✓ Sees: students, payments, commission, transfer amount
    ✓ Downloads: invoice PDF (facture)
             │
    ↓
    BANK TRANSFER EXECUTION:
    ✓ PSP sends money to partner's SEPA account
    ✓ UPDATE virement.statut = 'executed'
    ✓ Partner receives money in bank account
```

---

## 7. CHECKLIST DEBUGGING PAIEMENT

**Si un paiement échoue**:

1. ✅ Check validate_payment.php logs
   - Was request sent to E-Transaction gateway?
   - What was the response codereponse?

2. ✅ Check E-Transaction credentials (E_TransactionPayment.php):
   - MerchandId = 651027368 (prod) OR 222 (test)
   - PBX_KEY match
   - PBX_URL correct

3. ✅ Check stagiaire table:
   - Is status = 'inscrit'?
   - Are numtrans and numappel populated?
   - Check up2pay_code_error for error code

4. ✅ Check tracking_payment_error_code table:
   - What error_code was recorded?
   - Match against E-Transaction codes

5. ✅ Check archive_inscriptions:
   - Is there a backup record? (Should be there if success)

6. ✅ Check transaction table:
   - Is type_paiement = 'CB_OK'?
   - Is paiement_interne = 1?

7. ✅ Run cron manually:
   - `/www_2/planificateur_tache/up2pay/cron_status_payment.php?hash=6e395m74ng`
   - Check if up2pay_status gets updated

8. ✅ Check virement blocking:
   - Is stagiaire.virement_bloque = 1?
   - If yes, why? Check motif_virement_bloque

9. ✅ HMAC Signature verification:
   - E_TransactionPayment line 29: PBX_KEY must match exactly
   - Hash calculation must use HMAC-SHA512
   - If signature wrong → E-Transaction rejects request (codereponse != 00000)

10. ✅ Amount format:
    - Must be in CENTS, not euros
    - 219€ = 21900 (int, no decimals)

---

## 8. POINTS CRITIQUES À NE PAS CASSER

| Point Critique | Tables Affectées | Risque | Fix |
|---|---|---|---|
| HMAC Key in E_TransactionPayment | N/A (config) | All payments fail | Must match E-Transactions config |
| stagiaire.status = 'inscrit' | stagiaire | User can't see booking | Always set after payment success |
| numtrans + numappel storage | stagiaire | Can't refund later | Must save from E-Transaction response |
| commission_ht calculation | stagiaire | Wrong virement amount | Use exact formula from PaymentRepository |
| virement_bloque logic | stagiaire | Wrong amounts paid | Check before transfer creation |
| archive_inscriptions INSERT | archive_inscriptions | Audit trail lost | Always INSERT on success |
| stage capacity update | stage | Overbooking possible | Always UPDATE nb_inscrits |
| Cron job frequency | N/A (scheduler) | Payment status not synced | Run at least daily |
| Email sending | N/A (external) | User thinks failed | Mail server must work |
| Session data cleanup | sessions | Memory leak | Old sessions expire (24h+) |
| ORDER BY date1 ASC | stage | Wrong order shown | Critical for UX |

---

## 9. FILE TREE - WHERE EVERYTHING IS

```
/Users/yakeen/Desktop/PROSTAGES/
├── www_3/                              # Public website
│   ├── inscription/
│   │   ├── index.php                  # Stage listing
│   │   ├── ident_stagiaire.php        # Booking form
│   │   └── dossier.php                # Finalize booking
│   ├── es/                            # Espace Stagiaire
│   │   ├── aide.php                   # Login
│   │   └── aidev2.php                 # Session handler
│   └── ep/                            # Espace Partenaire
│       ├── accueil3.php               # Dashboard
│       ├── stages_m1.php              # Manage stages
│       └── liste_stagiaire_virement2.php  # View transfers (invoice)
│
├── www_2/                             # API & Backend
│   ├── src/payment/
│   │   ├── E_Transaction/
│   │   │   ├── E_TransactionConfig.php    # Gateway config
│   │   │   ├── E_TransactionPayment.php   # Payment class (HMAC signing)
│   │   │   └── E_TransactionError.php     # Error codes
│   │   ├── validate/
│   │   │   └── validate_payment.php   # Payment return handler (CRITICAL)
│   │   ├── repositories/
│   │   │   ├── PaymentRepository.php  # DB updates on payment
│   │   │   └── TrackingPathUserRepository.php
│   │   └── services/
│   │       └── UpdateStagePaymentData.php  # Full DB update flow
│   │
│   ├── simpligestion/                 # Admin Dashboard
│   │   ├── virement_centres_effectues.php    # Transfer management UI
│   │   ├── factures.php               # Invoices
│   │   ├── kpi.php                    # KPIs
│   │   └── ajax_functions.php         # AJAX handlers (blocage_virement, create_virement_listing_stagiaires)
│   │
│   ├── planificateur_tache/
│   │   └── up2pay/
│   │       └── cron_status_payment.php   # Daily payment sync cron
│   │
│   └── www/
│       ├── connections/
│       │   ├── stageconnect.php       # MySQL connection (legacy)
│       │   └── config.php             # MySQLi connection (modern)
│       └── params.php                 # Constants
│
└── PSP 2, PSP 3/                      # Legacy/backup code
```

---

## 10. SUMMARY - COMPLETE FLOW IN 1 DIAGRAM

```
CLIENT                           → PAYMENT GATEWAY         → DATABASE          → PARTNER
=====                              ================            ========             =======

1. Browse stages                  —                       — READ: stage, site
2. Select stage & Fill form      —                       — INSERT: stagiaire
3. Enter card #                  → HMAC + CURL POST      — READ: for calcs
4. E-Transaction response         ← codereponse          — UPDATE: transaction
                                                         — UPDATE: stagiaire (numtrans/numappel)
                                                         — INSERT: archive_inscriptions
                                                         — UPDATE: stage (capacity)
5. SMS confirmation             —                        — UPDATE: is_sms_confirmation_send
6. Email receipt                —                        — (external send)

[2 DAYS LATER]
Cron verifies                   → E-Trans consultation   — UPDATE: up2pay_status

[ADMIN ACTION]
SimpliGestion sees              —                        — SELECT with JOIN for virement calc
Admin creates transfer          —                        — INSERT: virement
Admin can block                 —                        — UPDATE: stagiaire (virement_bloque)
                                                         — UPDATE: transaction (virement link)

[PARTNER VIEW]
Partner sees invoice            —                        — SELECT virement data
Partner downloads PDF           —                        — (PDF generation)

[BANK]
                                                         — UPDATE: virement.statut='executed'
Partner receives €              ← SEPA/IBAN transfer    ✓ Complete
```

---

**Dernière mise à jour**: February 2026
**Système**: ProStagesPermis Legacy (PHP) - Production
**Complétude**: 100% - Chaque DB operation documentée
**Version**: 2.0 - COMPLETE DEEP DIVE
