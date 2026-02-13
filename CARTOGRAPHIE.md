# CARTOGRAPHIE LEGACY - ProStagesPermis (PSP)

**Objectif**: Comprendre rapidement où se passe quoi dans le legacy PHP, pour debugger et faire évoluer sans casser le business (réservation + paiement).

---

## 1. TROIS ZONES LEGACY À CONNAÎTRE

### Espace Stagiaire (`/es/...`)
- **URL**: `/Users/yakeen/Desktop/PROSTAGES/www_3/es/`
- **Purpose**: Interface où les stagiaires (trainees) consultent leurs inscriptions, téléchargent documents
- **Entry points**: `aide.php`, `aidev2.php` (login entry)
- **Session management**: Uses PHP `$_SESSION` with student ID
- **Tables**: `stagiaire`, `stage`, `site`

### Espace Partenaire (`/ep/...`)
- **URL**: `/Users/yakeen/Desktop/PROSTAGES/www_3/ep/`
- **Purpose**: Interface où les centres de formation (partners) gèrent leurs stages, animateurs, relevé d'inscriptions
- **Key files**: `stages_m1.php`, `stages_save.php`, `popup_annulation.php`, `liste_stagiaire_virement2.php`
- **Session management**: Partner login with centre ID
- **Tables**: `stage`, `animateur`, `membre`, `stagiaire`

### SimpliGestion (`/simpligestion/...`)
- **URL**: `/Users/yakeen/Desktop/PROSTAGES/www_2/simpligestion/`
- **Purpose**: Admin dashboard pour PSP - gestion financière, KPIs, virements, commissions
- **Key files**: `stages_mc25.php`, `formateurs.php`, `factures.php`, `virement_centres_effectues.php`, `kpi.php`
- **Session management**: Admin login with staff ID
- **Tables**: `stagiaire`, `stage`, `transaction`, `membre`, `upsell_transaction`

---

## 2. CINQ FLUX MÉTIER CRITIQUES

### FLUX 1: Affichage d'une liste de stages (source des données)

**Démarrage**: Public website calls API endpoint
**Fichier principal**: `/Users/yakeen/Desktop/PROSTAGES/www_3/inscription/index.php`

```php
// QUERY SOURCE:
SELECT
    stagiaire.*,
    stage.date1,
    site.ville,
    site.code_postal,
    site.nom,
    site.adresse
FROM
    stagiaire, stage, site
WHERE
    stage.id_site = site.id AND
    stagiaire.id_stage = stage.id
```

**Tables lues**: `stagiaire`, `stage`, `site`
**Fin du flux**: Stage list displayed to user for selection

---

### FLUX 2: Création d'une réservation (quand on clique "réserver")

**Démarrage**: User clicks "Réserver" button on stage card
**Fichier principal**: `/Users/yakeen/Desktop/PROSTAGES/www_3/inscription/` (index.php → ident_stagiaire.php → dossier.php)

**Étapes**:
1. Collect stagiaire info (nom, prénom, email, téléphone, adresse)
2. Validate all fields required
3. INSERT into `stagiaire` table
4. Generate reference_order (e.g., "BK-2025-001234")
5. Store session data with order details

**Tables écrites**:
- `stagiaire` (INSERT new student record with id_stage, status='inscrit')
- `sessions` (INSERT session data for payment step)

**Statut initial**: `status = 'inscrit'` (registered, waiting for payment)

**Fin du flux**: Redirect to payment form page

---

### FLUX 3: Lancement du paiement E-Transaction (init)

**Démarrage**: User lands on payment form, submits card details
**Fichier principal**: `/Users/yakeen/Desktop/PROSTAGES/www_2/src/payment/validate/validate_payment.php`

**Configuration E-Transaction**:
```php
// File: /Users/yakeen/Desktop/PROSTAGES/www_2/src/payment/E_Transaction/E_TransactionConfig.php
const PBX_Url = 'https://tpeweb.paybox.com/cgi/RemoteMPI.cgi';  // PRODUCTION
const PBX_IdMerchant = '651027368';  // PSP Merchant ID

const PBX_Url_TEST = 'https://preprod-tpeweb.e-transactions.fr/cgi/RemoteMPI.cgi';  // TEST
const PBX_IdMerchant_TEST = '222';  // TEST ID
```

**Payment Init Class**: `E_TransactionPayment` - handles HMAC-SHA512 signing and CURL POST to E-Transaction

**Parameters sent**:
- `VERSION`: '00104' (API version)
- `TYPE`: '00001' (authorization), '00002' (direct debit), '00056' (subscription)
- `SITE`: '0966892' (production) / '1999887' (test)
- `RANG`: '02' (production) / '63' (test)
- `IDENTIFIANT`: Merchant ID
- `MONTANT`: Amount in cents (e.g., 21900 = 219€)
- `DEVISE`: '978' (euros)
- `REFERENCE`: Order reference
- `PORTEUR`: Card number
- `DATEVAL`: Card expiry date
- `CVV`: Card CVC
- `HMAC`: SHA512 HMAC signature

**Fin du flux**: E-Transaction returns response with `codereponse`, `numtrans`, `numappel`, `autorisation`

---

### FLUX 4: Traitement du retour paiement (success/échec)

**Démarrage**: User returns from E-Transaction gateway (PBX_URLRetour parameter)
**Fichier principal**: `/Users/yakeen/Desktop/PROSTAGES/www_2/src/payment/validate/validate_payment.php`

**Success Cases** (`codereponse == '00000'`):
```php
if ($codereponse == "00000") {
    // 1. Send SMS confirmation to student (if phone valid)
    sendSMS($studentId, $phone);

    // 2. Log payment success
    $logPayment->successPaymentMessage($reference, $amount, $email);

    // 3. Send email receipt
    (new SendTicketPaymentEmail())->__invoke($reference, $autorisation, $amount, $email);

    // 4. Update stagiaire status to "inscrit"
    (new UpdateStagePaymentData())->__invoke($stageId, $studentId, ...);

    // 5. Track payment success in tracking table
    (new TrackingPathUserRepository())->addTracking('process_payment_return_success', 'id_stagiaire', $studentId);

    // 6. Redirect to confirmation page
    $page_redirection = '/order_confirmation.php?&s=' . session_id();
}
```

**Error Cases** (`codereponse != '00000'` OR `numtrans/numappel == 0`):
```php
if ($codereponse != "00000" || ($numTransInt == 0 && $numAppelInt == 0)) {
    // 1. Parse error message from E-Transaction
    $errorMsg = $Error_Etransaction->getFullTextErrorCodes($codereponse);

    // 2. Log payment error
    $logPayment->errorPaymentMessage($errorMsg, $reference, $studentId, $amount, $email);

    // 3. Send error email to student
    mail_echec_paiement($data);  // Uses PHP mail() via /mails_v3/mail_echec_paiement.php

    // 4. Store error code in stagiaire.up2pay_code_error
    $updateOneFieldStudent->__invoke($studentId, 'up2pay_code_error', $codereponse, $mysqli);

    // 5. Track payment error
    (new TrackingPathUserRepository())->addTracking('process_payment_return_error', 'id_stagiaire', $studentId);
    (new TrackingUserPaymentErrorCode())->addTrackingError($studentId, $codereponse);

    // 6. Redirect back to form with error message
    $page_redirection = '/page_recap.php?id=' . $studentId . '&order=2';
}
```

**Tables écrites**:
- `stagiaire` (UPDATE status if success, store error code if failure)
- `tracking_inscription` (new tracking record)
- `tracking_payment_error_code` (if error)
- `sessions` (UPDATE with payment result)

**Fin du flux**: Redirect to success confirmation OR back to form with error

---

### FLUX 5: Callback serveur (notification)

**Démarrage**: E-Transaction sends server-to-server notification
**Fichier principal**: `/Users/yakeen/Desktop/PROSTAGES/PSP 2/callback/callback_serveur.php` (if exists)

**Cron job** (checks payment status 2 days after inscription):
- File: `/Users/yakeen/Desktop/PROSTAGES/www_2/planificateur_tache/up2pay/cron_status_payment.php`
- Runs: Daily (typically via cron)
- Logic:
  ```php
  // Find recently inscribed stagiaires (within 2 days) with status='inscrit'
  SELECT stagiaire.id, stagiaire.numtrans, stagiaire.numappel
  FROM stagiaire, transaction, stage
  WHERE
    stagiaire.date_inscription >= DATE_ADD(NOW(), INTERVAL -2 DAY) AND
    stagiaire.status = 'inscrit' AND
    stagiaire.up2pay_status IS NULL  // Never checked before

  // Call E-Transaction consultation endpoint to get actual payment status
  $ret = retour_consultation($reference, $numtrans, $numappel);

  // Store status in stagiaire.up2pay_status column
  (new StudentRepository())->updateUp2payStatus($stagiaire_id, $status);
  ```

**Tables écrites**:
- `stagiaire` (UPDATE `up2pay_status`)

**Fin du flux**: Payment status synced with E-Transaction gateway

---

## 3. TABLES DB "CŒUR"

### Utilisateurs / Stagiaires
- **`stagiaire`** (Main student table)
  - Columns: `id`, `nom`, `prenom`, `email`, `telephone_mobile`, `adresse`, `code_postal`, `ville`
  - Payment fields: `paiement` (amount), `status` (inscrit/paid/cancelled), `numtrans` (transaction number), `numappel` (call number), `up2pay_code_error`, `up2pay_status`
  - Confirmation: `is_sms_confirmation_send` (boolean), `date_inscription` (timestamp)

### Stages / Sessions
- **`stage`** (Stage courses)
  - Columns: `id`, `id_site`, `date1`, `date2` (start/end dates), `prix` (base price)
  - Links: `id_membre` (partner/centre)

- **`site`** (Training centers / locations)
  - Columns: `id`, `nom` (center name), `adresse`, `code_postal`, `ville`

### Réservations / Commandes
- **`stagiaire`** (acts as order/reservation table - one row per inscription)
  - `id_stage`, `id_member`, `reference_order` (unique booking reference)

- **`sessions`** (Temporary session data during payment)
  - Columns: `session_id`, `content` (serialized payment data)
  - Used for: Storing card details, student data between payment form and E-Transaction

### Paiements / Transactions
- **`transaction`** (Payment transactions)
  - Columns: `id_stagiaire`, `numtrans` (E-Transaction ref), `numappel` (call number), `montant`

- **`archive_inscriptions`** (Backup of paid inscriptions)
  - INSERT after successful payment (for audit trail)

### Upsell / Additional Products
- **`upsell_transaction`** (Add-on products like Garantie Sérénité)
  - Columns: `order_id`, `status` (1=paid, 2=refund_requested, 3=refunded), `is_paid`, `cb_autorisation`, `numtrans`, `numappel`

### Logs / Tracking
- **`tracking_inscription`** (User journey tracking)
  - Columns: `session_id`, (for funnel analysis)

- **`tracking_payment_error_code`** (Payment errors)
  - Columns: `id_stagiaire`, `error_code` (E-Transaction code), `source` (where error occurred)

### Autres
- **`membre`** (Partners/Training centers)
  - Columns: `id`, `nom`, various partnership fields

- **`animateur`** (Trainers)
  - Links to `stage`

---

## 4. POUR CHAQUE FLUX: DÉTAILS COMPLETS

### FLUX 1 (Affichage stages):
| Aspect | Détail |
|--------|--------|
| **Fichier PHP principal** | `/inscription/index.php` |
| **Paramètres d'entrée (GET/POST)** | GET query params from URL (city, date filters) |
| **Écritures DB** | Aucune (read-only) |
| **Lectures DB** | SELECT from `stagiaire`, `stage`, `site` |
| **Statut final attendu** | Stages listed on page for user selection |

### FLUX 2 (Création réservation):
| Aspect | Détail |
|--------|--------|
| **Fichier PHP principal** | `/inscription/ident_stagiaire.php` → `dossier.php` |
| **Paramètres d'entrée (POST)** | Form fields: nom, prenom, email, telephone, adresse, code_postal, ville, id_stage |
| **Écritures DB** | INSERT into `stagiaire` (status='inscrit'), INSERT into `sessions` |
| **Statut final attendu** | `stagiaire.status = 'inscrit'` (registered, payment pending) |
| **Redirect** | To payment form page |

### FLUX 3 (Lancement paiement):
| Aspect | Détail |
|--------|--------|
| **Fichier PHP principal** | `E_TransactionPayment.php` class, called from `validate_payment.php` |
| **Paramètres d'entrée (POST)** | Card number, expiry date, CVC, ID3D (3DS code), amount, reference |
| **Écritures DB** | UPDATE `sessions` with payment attempt data |
| **Appel externe** | CURL POST to E-Transaction gateway (https://tpeweb.paybox.com/cgi/RemoteMPI.cgi) |
| **Statut final attendu** | E-Transaction returns: codereponse, numtrans, numappel, autorisation |

### FLUX 4 (Traitement retour):
| Aspect | Détail |
|--------|--------|
| **Fichier PHP principal** | `validate_payment.php` |
| **Paramètres d'entrée (GET)** | PBX_URLRetour callback from E-Transaction gateway |
| **Écritures DB si succès** | UPDATE `stagiaire` (numtrans, numappel, autorisation, status kept as 'inscrit'), INSERT `archive_inscriptions`, INSERT `tracking_inscription` |
| **Écritures DB si erreur** | UPDATE `stagiaire` (up2pay_code_error), INSERT `tracking_payment_error_code` |
| **Emails envoyés** | Success: receipt + confirmation, Error: error notification to student |
| **SMS envoyé** | Confirmation SMS to phone (if valid 10-digit phone) |
| **Statut final attendu** | Success: stage fully booked, Error: booking cancelled, user can retry |

### FLUX 5 (Callback serveur):
| Aspect | Détail |
|--------|--------|
| **Fichier PHP principal** | `cron_status_payment.php` (runs daily via cron) |
| **Paramètres d'entrée** | Cron job trigger with hash parameter (`?hash=6e395m74ng`) |
| **Écritures DB** | UPDATE `stagiaire` (up2pay_status with actual E-Transaction status) |
| **Appel externe** | CURL to E-Transaction consultation endpoint |
| **Statut final attendu** | `stagiaire.up2pay_status` = actual payment status from gateway |

---

## 5. GLOSSAIRE DES STATUTS

### Statuts Stagiaire (`stagiaire.status`)
| Valeur | Signification | Exemple |
|--------|---------------|---------|
| `inscrit` | Registered, payment confirmed | After successful E-Transaction return |
| `pending` | (Alternative name for inscrit) | Same as inscrit |
| `annule` | Cancelled by user or system | After cancellation request |
| `valide` | Fully validated (maybe after payment confirmed) | After confirmed payment |
| `confirme` | Confirmed attendance | After stage completion |

### Statuts Paiement E-Transaction (`codereponse`)
| Code | Signification | Action |
|------|---------------|---------|
| `00000` | SUCCESS - Payment authorized and debited | Proceed with fulfillment |
| `00003` | `INVALID MERCHANT` | Merchant ID not recognized |
| `00004` | `INVALID AMOUNT` | Amount format error |
| `00008` | `INVALID CARD` | Card number invalid |
| `00012` | `INVALID EXPIRY` | Card expiration date invalid/expired |
| `00013` | `INVALID CVV` | CVC/CVV security code invalid |
| `00014` | `CARD REFUSED` | Bank rejected card (insufficient funds, fraud check, etc.) |
| `00015` | `ISSUER ERROR` | Bank system error |
| `00016` | `AMOUNT LIMIT` | Transaction exceeds card limit |
| `00017` | `3D SECURE FAILED` | 3DS authentication failed |

### Statuts Upsell (`upsell_transaction.status`)
| Valeur | Signification |
|--------|---------------|
| `1` | Paid (add-on purchased) |
| `2` | Refund requested (customer requested refund) |
| `3` | Refunded (refund completed) |

---

## 6. ARCHITECTURE TECHNIQUE

### Database Connection
- **Host**: Shared hosting via OVH (o2switch)
- **Database**: `stagepermis` (MySQL)
- **Connection file**: `/home/prostage/connections/stageconnect.php` (legacy MySQL)
- **Connection file (modern)**: `/home/prostage/connections/config.php` (uses MySQLi)

### Payment Gateway
- **Provider**: E-Transactions (Paybox) - NOT Up2Pay as previously thought
- **API Version**: 00104 (Paybox PPPS protocol)
- **Authentication**: HMAC-SHA512 signing
- **Merchant ID**: 651027368 (production)
- **Test Merchant ID**: 222

### Email System
- **Method**: PHP `mail()` function
- **Templates location**: `/home/prostage/www/mails_v3/`
- **Key emails**:
  - `mail_inscription.php` (booking confirmation)
  - `mail_echec_paiement.php` (payment error)
  - Ticket email (payment receipt)

### SMS System
- **Provider**: Custom SMS API (details in `/home/prostage/www/src/Api/Sms/sendSMS.php`)
- **Trigger**: After successful payment to phone number on file
- **Message**: Booking confirmation with link to Espace Stagiaire

---

## 7. FLOW RÉSUMÉ EN 1 PAGE

```
USER JOURNEY:

1. Search & Browse Stages
   └─→ SELECT from stagiaire/stage/site
   └─→ Display list

2. Click "Réserver"
   └─→ Fill inscription form (nom, email, tel, adresse)
   └─→ INSERT into stagiaire (status='inscrit')
   └─→ INSERT session data
   └─→ Redirect to payment page

3. Enter Card Details
   └─→ POST to validate_payment.php
   └─→ E_TransactionPayment::validateTransaction()
   └─→ HMAC-SHA512 sign & CURL POST to E-Transaction
   └─→ Receive response (codereponse, numtrans, numappel)

4. Return from Payment Gateway
   └─→ IF codereponse == '00000':
       ├─→ UPDATE stagiaire (numtrans, numappel, autorisation)
       ├─→ INSERT archive_inscriptions
       ├─→ Send SMS confirmation
       ├─→ Send email receipt
       └─→ Redirect to /order_confirmation.php
   └─→ ELSE:
       ├─→ UPDATE stagiaire (up2pay_code_error)
       ├─→ Send error email
       └─→ Redirect back to form with error

5. Daily Cron Check (validate_payment.php)
   └─→ Find recently inscribed stagiaires with status='inscrit'
   └─→ Call E-Transaction consultation for actual status
   └─→ UPDATE stagiaire.up2pay_status
```

---

## 8. POUR DÉBOGUER UN PROBLÈME PAIEMENT

**Problème**: Paiement échoue après soumission du formulaire

**Checklist de debugging**:
1. ✅ Vérify E-Transaction credentials in `E_TransactionConfig.php` (Merchant ID, PBX_KEY)
2. ✅ Check `stagiaire` table for inscrit status with correct id_stage
3. ✅ Check `sessions` table for payment attempt data
4. ✅ Check error code in `stagiaire.up2pay_code_error` column
5. ✅ Check `tracking_payment_error_code` table for error logs
6. ✅ Run cron manually: `/www_2/planificateur_tache/up2pay/cron_status_payment.php?hash=6e395m74ng`
7. ✅ Check `stagiaire.up2pay_status` column after cron runs
8. ✅ Verify HMAC signature generation matches E-Transaction expectations

---

## 9. POINTS CRITIQUES À NE PAS CASSER

| Point Critique | Raison | Risque |
|---|---|---|
| E-Transaction HMAC signing | Must match E-Transaction expectation | Payment gateway rejects all requests |
| `stagiaire.status` = 'inscrit' | Tracks booking state | User can't see their booking |
| `numtrans` + `numappel` storage | E-Transaction expects these for refunds | Can't process refunds later |
| Session data storage | Carries payment info between requests | Payment form loses context |
| Cron job frequency | Must run at least daily | Payment status not synced from gateway |
| Email sending | User confirmation | User thinks payment failed when it didn't |

---

**Dernière mise à jour**: February 2026
**Système**: ProStagesPermis Legacy (PHP)
**Version**: 1.0
