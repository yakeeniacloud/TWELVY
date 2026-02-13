# ESPACE STAGIAIRE - COMPLETE DOCUMENTATION

**Last Updated**: February 11, 2026
**Source**: Live portal analysis + PHP source code examination
**Status**: ✅ ALL FILES FOUND (~130 PHP files) + Complete visual documentation

---

## OVERVIEW

The Espace Stagiaire is the **private portal for drivers who booked a points recovery course**. After payment, trainees receive an email with a unique login link to access their personal space where they complete their file, track their course, and manage their booking.

---

## AUTHENTICATION

**Two login methods exist**:

1. **Standard Login** (ID + Password):
   - Trainee enters their numeric ID + numeric password
   - Credentials shown in sidebar after login

2. **Email Link** (One-click login):
   - Link format: `https://www.prostagespermis.fr/es/loginv2.php?id=12345&k=a3f9b`
   - `id` = trainee ID
   - `k` = First 5 characters of MD5 hash (`md5($id_stagiaire . '!psp13#')`)
   - If key matches, session created automatically

---

## VISUAL LAYOUT

**Design Style**: Bootstrap 3/4 with professional, progress-oriented interface

**Top Header**:
- Minimal design
- Logo on left
- Logout button on right

**Left Sidebar** (Dark blue #2f4050):
- **Header section**: Displays trainee info in white text
  - Full name
  - Email
  - Numeric ID
  - Numeric password
  - Phone number
- **Navigation menu** below with FontAwesome icons

**Main Content Area**:
- Card-based layout
- Extensive use of accordions to manage dense information
- Clean white background

**Floating Widget**:
- "Tom" virtual assistant in bottom-right corner
- External chat tool (Zendesk or similar)

**Color Scheme**:
- Primary Blue: `#2f4050` (sidebar background)
- Active Green: `#1ab394` (buttons, success badges, active menu items)
- Alert Pink: `#fcf2f2` (important information boxes)
- Text: Dark grey `#676a6c`

---

## NAVIGATION MENU

| Menu Item | Icon | File | Purpose |
|-----------|------|------|---------|
| **Mon stage** | shopping cart | `stagev3.php` | Main dashboard |
| **Section: Informations personnelles** | | | |
| Ma situation | checkmark | `profil/situation.php` | Legal case selection |
| Mon permis de conduire | checkmark | `profil/permis.php` | License details |
| **Section: Documents légaux** | | | |
| Mes factures | file | `factures.php` | Invoice downloads |
| **Aide & Contact** | question circle | External | Help center/FAQ |
| **Je change d'avis** | refresh | `changement_avisv2.php` | Cancellation/date change |
| **Déconnexion** | sign-out | `loginv2.php` | Logout |

**Consolidated Profile** (`infosv2.php`):
- Modern replacement for individual pages
- Combines situation, permit, documents, and personal info in one scrollable page with accordions
- Individual pages kept for backward compatibility

---

## PAGE-BY-PAGE BREAKDOWN

### 1. MON STAGE — Dashboard (`stagev3.php`)

**Purpose**: Central hub showing course status and next steps

**Layout**:
- **Top**: Summary card with course date, venue address, and price
- **Alert box**: Pink background with important operational info (currently about ANTS processing delays)
- **5-step progress bar** (horizontal stepper):
  1. **Inscription** — Booking completed
  2. **Dossier** — Documents and forms to fill
  3. **Stage** — Attend 2-day course
  4. **Gestion** — Post-course admin processing
  5. **Points** — Points restored on license

**Progress Tracking**:
- Green checkmarks for completed steps
- Active step highlighted
- If file incomplete: "Dossier Incomplet" status

**Accordion Sections** (expandable):
- **Détails de mon stage** — Full course details (address, schedule, what to bring)
- **Mon dossier** — Status of documents and forms
- **Programme** — 2-day course schedule
- Various "En savoir plus" toggles for additional info

**Action Buttons**:
- "Consulter" — View invoices/documents
- "En savoir plus" — Toggle additional details
- "Découvrir" — Course program and amenities

---

### 2. MA SITUATION (`profil/situation.php`)

**Purpose**: Declare legal reason for taking the course

**Interface**: Interactive questionnaire with binary choices

**Four Case Types**:

| Case | Meaning | Requirements |
|------|---------|--------------|
| **Cas 1** | Voluntary — driver chose to recover points | Standard documents |
| **Cas 2** | Mandatory probationary — received "lettre 48N" | Must upload 48N letter |
| **Cas 3** | Alternative to prosecution — judge offered course instead of penalty | Court documents required |
| **Cas 4** | Court order — course ordered as part of sentence | Court order required |

**Workflow**:
1. Binary questions narrow down case type
2. System displays determined case (e.g., "CAS 1")
3. "Enregistrer" button confirms selection

**Critical Business Logic**: Case determines required documents and administrative steps

---

### 3. MON PERMIS (`profil/permis.php`)

**Purpose**: Enter driving license details for ANTS transmission

**Form Fields**:
- **Status** — Valid / Suspended / Invalid (dropdown)
- **License number** — 12-digit number (text input)
- **Date of issue** — Date picker
- **Prefecture** — Searchable dropdown of all French prefectures

**Required Fields**: Marked with red asterisks

**Data Usage**: Transmitted to ANTS by training center via Espace Partenaire

---

### 4. DOCUMENTS (`profil/documents.php`)

**Purpose**: Upload required legal documents

**Interface**: Grid of document slots

**Required Documents** (4 slots):
1. **Pièce d'identité recto** (ID front)
2. **Pièce d'identité verso** (ID back)
3. **Permis de conduire recto** (License front)
4. **Permis de conduire verso** (License back)

**States**:
- **Empty**: Placeholder with cloud/arrow upload icon
- **Filled**: Thumbnail with red "Supprimer" (Delete) button

**Upload Process**:
- AJAX handler: `ajax_upload_document_stagiaire.php`
- Server-side file validation
- Status updates in real-time

**Status Trigger**:
- When all 4 documents uploaded AND forms filled → **"Dossier COMPLET"** (green badge)
- Triggers confirmation emails to trainee and center

**Reminder System**:
- If documents missing: 14-email sequence over 60 days
- Timing: 15min, J+1, J+2, J+4, J+6, J+8, J+10, J+15, J+20, J+25, J+30, J+45, J+60

---

### 5. FACTURES (`factures.php`)

**Purpose**: Invoice history and downloads

**Layout**: Two tables

**Table 1: Factures** (Invoices)
- Columns: Date, Amount (e.g., €220), Product Name, PDF Download Icon
- Shows booking invoice

**Table 2: Avoirs** (Credit Notes)
- Shows refund credit notes if applicable

**PDF Generation**: `ajax_facture_stagiaire.php` using HTML2PDF/TCPDF library

---

### 6. JE CHANGE D'AVIS (`changement_avisv2.php`)

**Purpose**: Self-service cancellation/refund/reschedule

**Legal Context**: French 14-day cooling-off period ("droit de rétractation")

**Within 14 Days**:
- Request date change (transfer to another stage)
- Request full refund
- Automatic Payline refund processing
- Notification emails sent to trainee and center

**After 14 Days**:
- Big red banner: **"DÉLAI DE RÉTRACTATION ÉCOULÉ"**
- No self-service cancellation
- Must contact support via messaging

**Backend Logic**: Checks registration date vs current date, triggers Payline refund + emails when applicable

---

### 7. MESSAGES (`messages.php` / `messagesv2.php`)

**Purpose**: Internal messaging/ticket system

**Interface**:
- **Top right**: Orange "Contacter notre service client" button (create new message)
- **Below**: List of past messages (sender, date, snippet)

**Use Cases**:
- Request transfer after 14-day window
- Report problems
- Ask questions
- Administrative requests

**Visibility**: SimpliGestion admin team responds from back-office

---

### 8. CONSOLIDATED PROFILE (`infosv2.php`)

**Purpose**: Newer unified profile interface

**Content**: Combines into single scrollable page with accordions:
- Personal situation (Cas 1-4)
- Permit details
- Document uploads
- Personal information (name, address, contact)

**Status**: Modern replacement for individual pages
- Individual pages still exist (backward compatibility)
- Both versions coexist during gradual rollout

---

### 9. DONNÉES PERSONNELLES (`donnees_personnelles.php`)

**Status**: Appears to redirect to `infosv2.php` (effectively deprecated)

**Original Purpose**: Standalone page for viewing/editing personal info

---

### 10. COORDONNÉES BANCAIRES (`coordonnees_bancaires.php`)

**Purpose**: Enter bank details (IBAN/RIB) for receiving refunds

**Use Case**: Only relevant when refund processed via bank transfer instead of card reversal

---

## TRAINEE WORKFLOW

### Complete Journey from Booking to Points Recovery

**Step 1 — Booking** (public website)
→ Driver finds course, fills form, pays by card

**Step 2 — Email Received**
→ Confirmation email with course details + unique login link

**Step 3 — First Login**
→ Lands on dashboard, progress bar shows "Dossier" active, status "Incomplet"

**Step 4 — Complete the File**
→ Fill situation, permit, upload 4 documents
→ Each step gets green checkmark
→ Reminder emails sent if documents missing

**Step 5 — Dossier Complete**
→ Status changes to **"Dossier COMPLET"** (green)
→ Confirmation emails sent

**Step 6 — Pre-Course Reminder**
→ Automatic emails at J-4 and J-1 with venue, time, what to bring

**Step 7 — Attend Course**
→ 2-day stage (Friday-Saturday or Saturday-Sunday, 9h-17h)
→ Center marks attendance in Espace Partenaire

**Step 8 — Post-Course**
→ Center generates attestation, transmits to ANTS
→ Trainee receives attestation by email
→ Follow-up sequence (J+8, J+15, J+30, J+60, J+90)

**Step 9 — Points Restored**
→ ANTS processes restoration (~12 weeks)
→ Trainee checks via Telepoint

---

## TECHNICAL NOTES

**Authentication**:
- `$_SESSION['id_stagiaire']` stores trainee ID after login
- MD5 key salt: `!psp13#`
- Both traditional login and email link supported

**Bootstrap Include**:
```php
require_once('../../common_bootstrap2/config.php');
require_once('includes/functions.php');
```

**Database Tables**:
- `stagiaire` — Trainee records
- `evaluation_stagiaire` — Post-stage evaluations
- `stage_bookings` — Booking references

**Critical Business Logic**:
- "Dossier Complet" status triggers emails and unlocks next steps
- 14-day cooling-off period calculation must be exact
- Cas 1-4 determines entire administrative path

**External Integrations**:
- "Tom" virtual assistant (third-party widget, not in PHP code)
- Payline refund API
- Email system (PHPMailer)

**Upsells Module** (`upsells/` folder):
- Files exist: `formations.php`, `twelvy_application.php`, `order_bump.php`
- Not visible in live portal (conditionally shown or currently disabled)

---

## FILES INVENTORY

**Total**: ~130 PHP files in `www_3/es/` folder

**Core Pages**: 10+ main pages
**AJAX Handlers**: 40+ files
**Profile System**: 2 versions (current + legacy)
**Includes**: 30+ layout/utility files
**Upsells**: Separate module
**Additional**: Messaging, planning, documents, stages browsing

---

## MIGRATION CONSIDERATIONS

1. **Dossier Complet logic is the engine** — Everything revolves around document status
2. **14-day cooling-off period** is legally mandated — must be exact
3. **Cas 1-4 business logic** determines administrative path — must replicate exactly
4. **infosv2.php is the future** — Individual pages likely to be deprecated
5. **"Tom" widget** is external — decision needed: keep/replace/remove
6. **Upsells module** exists but not visible — investigate conditions

---

**END OF ESPACE STAGIAIRE DOCUMENTATION**
