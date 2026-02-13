# ESPACE PARTENAIRE - COMPLETE DOCUMENTATION

**Last Updated**: February 11, 2026
**Source**: Live portal screenshots + PHP source code examination
**Status**: ✅ ALL FILES FOUND (~250 PHP files) + Complete visual documentation

---

## OVERVIEW

The Espace Partenaire is the **private management portal for training centers** — the driving schools and accredited organizations that deliver the 2-day awareness courses. When a driver books a course, they're assigned to a specific center and venue. The center logs into the EP to manage everything: stages, trainees, venues, trainers, and payments.

**Business Model**: Centers are the suppliers in the marketplace. PSP brings customers; centers run the courses and recover the points.

---

## WHO USES IT

**Training center owners, managers, or administrators**

- Each center has one login (username/email + password)
- After login, sees only their own data (stages, trainees, venues, payments)
- Example center from screenshots: **"AUTO ECOLE D'OLIVE DU PRES"** in Pantin (93500)

---

## VISUAL LAYOUT

**Top Header**:
- **Left**: ProStages logo ("Permis" in red box)
- **Center-right**: Phone number `04 65 84 13 99` with blue help icon
- **Far right**: Warning notice in italics: *"Ne jamais communiquer ce numéro aux stagiaires. Merci"* (Never share this number with trainees)
- **Blue button far right**: "DÉCONNEXION AUTO ECOLE D'OLIVE DU PRES" (Logout with center name)

**Main Navigation Bar** (dark background):
- **STAGES** (red/active when selected)
- **STAGIAIRES** (red/active when selected)
- **SALLES** (red/active when selected)
- **ANIMATEURS** (dropdown)
- **VIREMENTS** (red/active when selected)
- **AIDE & CONTACT**
- **MON COMPTE** (dropdown, red/active when selected)

**Main Content Area**:
- Clean white background
- Filter bars at top
- Action buttons (green for primary, blue for filters)
- Data tables with light blue/purple headers
- Icon-based actions in each row

---

## PAGE-BY-PAGE BREAKDOWN

### 1. STAGES — Main Dashboard (`accueil3.php`)

**Purpose**: List and manage all center's courses

**Top Filters**:
- Date range: From/to date pickers (default shows wide range)
- Tous stages dropdown — Filter by stage type
- Tous dpts dropdown — Filter by department
- Tous Lieux dropdown — Filter by venue
- Tous dropdown — Additional filter
- Blue "Rafraîchir" button — Apply filters

**Top-Right Buttons**:
- **Green**: "AJOUTER UN STAGE" — Add new stage
- **Orange**: "SIMULATEUR DE RENVERSEMENT" — Revenue simulator (calculates payment after PSP commission)

**Table Columns**:

| Column | Content | Details |
|--------|---------|---------|
| **Date** | Stage date | Format: DD-MM-YYYY |
| **Ville** | City | Postal code + city name (e.g., "93500 PANTIN") |
| **Lieu** | Venue name | Full address (e.g., "AUTO ECOLE L OLIVE DU PRE 9 RUE BEAUREPAIRE") |
| **Animateurs** | Trainer assignment | Dropdowns: "BAFM à définir" and "Psy à définir" when unassigned |
| **GTA** | GTA status | Dropdown: "GTA à définir" (possibly Garantie or trainer agreement) |
| **Prix index TTC** | Public price | Price shown to customers (e.g., 176 €, 186 €, 99.60) |
| **Renversement TTC** | Center payout | Amount center receives after PSP commission (e.g., 85.52, 84.64) |
| **Statut** | Visibility status | "Hors ligne (Incomplet)" (orange) or "En ligne" (green) |
| **Inscrits** | Registrations | Number of trainees registered (e.g., 3, 1, 0) |
| **Actions** | Icon buttons | See below |

**Action Icons** (left to right):
- **Yellow download** — Download stage documents (attendance sheet, prefecture form)
- **Blue user+** — Add trainee manually
- **Red user-** — Remove/cancel trainee
- **Red trash** — Delete stage
- **Magnifying glass** — View stage details

**Key Observations**:
- Center sees public price vs actual payout (PSP takes commission)
- Stages marked "Hors ligne (Incomplet)" aren't visible until trainers assigned
- Center can manually add trainees (not just through public website)

---

### 2. STAGIAIRES — Trainee Management (`stagiaires_mc25.php`)

**Purpose**: View and manage all registered trainees

**Top Filters**:
- Date range: From/to (e.g., 11-02-2026 to 13-04-2026)
- Tous départements dropdown
- Tous dossiers dropdown — Filter by file status (complete/incomplete)
- Dossier ANTS dropdown — Filter by ANTS transmission status
- Blue "Rafraîchir" button

**Top-Right Search**:
- "RECHERCHER UN STAGIAIRE" — Search by trainee name

**Table Columns**:

| Column | Content | Details |
|--------|---------|---------|
| **IDENTITÉ** | Full name | e.g., "FERREYRA Gregory" |
| **INSCRIPTION** | Registration date | e.g., 09/02/2026 |
| **STAGE** | Stage date + city | e.g., "16/02/2026 93500 PANTIN" |
| **CAS** | Legal case | 1, 2, 3, or 4 (see Espace Stagiaire explanation) |
| **PRIX INDEX** | Price paid | e.g., 178 € |
| **RÉCEPTION PAIEMENT** | Payment status | e.g., "Reçu" |
| **PROVENANCE** | Booking source | e.g., "Prostagespermis" if booked through public site |
| **STATUT** | Registration status | "Inscrit" (orange) = registered but stage hasn't happened |
| **STATUT DOSSIER STAGIAIRE** | File status | "Complet" (green) or "Incomplet" (orange/red) |
| **ACTIONS** | Icon buttons | See below |

**Action Icons**:
- **Orange download** — Download trainee's documents/dossier
- **Blue document** — View/manage uploaded documents
- **Blue message** — Send message to trainee
- **Red X** — Cancel trainee's registration
- **Magnifying glass** — View full trainee details

**Key Observations**:
- Center tracks which files are complete vs incomplete
- CAS column determines administrative steps
- Direct communication with trainees via messaging
- Cancel bookings from center side

---

### 3. SALLES — Venue Management (`lieux.php`)

**Purpose**: Manage training locations (physical addresses)

**Top Filters**:
- Tous départements dropdown
- Blue "Rafraîchir" button

**Top-Right**:
- **Green button**: "AJOUTER UN LIEU" — Add new venue

**Table Columns**:

| Column | Content | Details |
|--------|---------|---------|
| **Adresse** | Venue name | e.g., "AUTO ECOLE L OLIVE DU PRE" |
| **(Address cont)** | Street address | e.g., "9 RUE BEAUREPAIRE" |
| **Ville** | Postal code + city | e.g., "93500 PANTIN" |
| **GPS** | Coordinates | lon: 2.4067694, lat: 48.8895172 (used for public site mapping) |
| **Commodités** | Amenities | Parking, accessibility (empty in screenshot) |
| **Agrément** | Prefecture agreement | R-format number (e.g., "R2509300070") — mandatory for legal compliance |
| **Actions** | Icon buttons | See below |

**Action Icons**:
- **Orange download** — Download venue documents
- **Blue mobile/phone** — Mobile preview or contact info
- **Red X** — Delete venue
- **Magnifying glass** — View/edit venue details

**Key Observations**:
- Each venue requires valid prefecture agreement number (R-format)
- GPS coordinates critical for public website mapping and proximity calculation
- Center can have multiple venues (different addresses in different cities)
- Agreement number tied to specific address — moving requires new agreement

---

### 4. VIREMENTS — Bank Transfers (`virements.php`, `factures_mc24.php`)

**Purpose**: Track payments received from PSP

**Top Notice** (pink background):
*"IMPORTANT: Les reversements sont effectués par virements bancaires tous les 2e et 4e mercredis du mois une fois le stage effectué. Chaque virement inclut les stages effectués jusqu'au samedi précédant le jour du virement."*

**Translation**: Payments made by bank transfer every 2nd and 4th Wednesday of month, once stage completed. Each transfer includes stages completed up to Saturday before transfer date.

**Table Columns**:

| Column | Content | Details |
|--------|---------|---------|
| **Date émission facture** | Invoice issue date | Currently shows "-" (no invoice yet) |
| **Montant facture TTC** | Total invoice amount | Currently "-" |
| **Montant virement TTC** | Transfer amount | Currently "-" |
| **Virement** | Transfer status | "En attente" (pending) |
| **Commentaire** | Comments/notes | Currently "-" |
| **Documents** | PDF download | Blue PDF icon (download invoice once issued) |

**Key Observations**:
- Payments batched bi-monthly (2nd and 4th Wednesdays)
- Must wait until stage completed before payment
- Lag: stage happens → Saturday cutoff → next Wednesday payment
- "En attente" = stage happened but payment not processed yet
- Once paid, center downloads PDF invoice

---

### 5. MON COMPTE — Account Settings (`compte.php`)

**Purpose**: Manage company information, manager details, login credentials

**Four Sections** (each with blue "MODIFIER" button):

**Section 1: INFORMATIONS ENTREPRISE** (Company Information)
- **Entreprise**: Company name (e.g., "AUTO ECOLE D'OLIVE DU PRES")
- **Siret**: French company registration (e.g., 83812225700036)
- **Code Ape**: Business activity code (e.g., 8532)
- **N° TVA**: VAT number (e.g., FR04838122257)
- **Texte TVA non récoltable**: VAT-exempt training notice
- **Adresse**: Street address (e.g., "9 rue BEAUREPAIRE")
- **Code Postal**: Postal code (e.g., 93500)
- **Ville**: City (e.g., Pantin)
- **Email**: Company email (e.g., fakrazanne@gmail.com)
- **Tél**: Phone (e.g., 01-48-44-69-69)
- **Mobile**: Mobile number

**Section 2: COORDONNÉES DU GÉRANT** (Manager Details)
- **Nom**: Last name (e.g., FAKRAZANNE)
- **Prénom**: First name (e.g., STEFFI)
- **Email**: Manager's email (e.g., fakrazanne@gmail.com)
- **Tél**: Phone (e.g., 0627536605)
- **Mobile**: Mobile number

**Section 3: CHANGEMENT MOT DE PASSE** (Password Change)
- **Login**: Email used for login (e.g., fakrazanne@gmail.com)
- **Mot de passe**: Current password (hidden: H****)

**Section 4: COORDONNÉES DE FACTURATION** (Billing Information)
- Likely contains IBAN/bank details for receiving payments

**Key Observations**:
- All company details editable
- Login is email-based (not username)
- SIRET, VAT, agreement numbers required for invoicing
- Email critical for login, notifications, official correspondence

---

## ADDITIONAL FEATURES (From PHP Files)

### ANIMATEURS (Trainers) — `formateurs.php`

Manage trainers (BAFM instructors and psychologists):
- Name, qualifications, availability
- Assignment to specific stages
- Payment tracking (if freelance)

### ANTS Transmission — `ants_transmissions.php`, `ants_dossiers.php`

Transmit trainee data to ANTS (national vehicle registration system):
- Batch transmission of trainee files
- Tracking transmission status
- Viewing ANTS responses and errors

### Document Generation

Generate and download PDF documents:
- **Feuille d'émargement** (`feuille_emargement.php`) — Attendance sheet for trainees to sign
- **Feuille préfecture** (`feuille_prefecture.php`) — Prefecture reporting form
- **Attestation de stage** (`attestation_stage.php`) — Stage completion certificate
- **Factures** — Invoices for center

### Statistics — `statistiques.php`, `bilan-annuel.php`

Performance dashboards:
- Fill rate (how full stages are)
- Revenue over time
- Cancellation rate
- Annual summary report

### Calendar View — `calendar.php`

Calendar visualization of upcoming stages

### Withdrawal Management — `desistements.php`

Track trainees who canceled or didn't show up

### Commission Simulator — `popup_simulateur_renversement2024.php`

Calculate expected revenue:
- Based on price index (public price)
- PSP commission structure (varies by department and tier)
- Number of trainees

**Complex Formula**: PSP commission percentage depends on price tier and department

---

## CENTER WORKFLOW

### Complete Journey of a Training Center

**Step 1 — Setup** (one-time)
→ Create account, fill company info (SIRET, VAT, bank), add venues (addresses + prefecture agreements), add trainers

**Step 2 — Publish Stages**
→ Click "AJOUTER UN STAGE", fill date, venue, price, seats
→ Assign BAFM + psychologist
→ Stage goes "En ligne" (visible on public website)

**Step 3 — Trainees Book**
→ Customers book on prostagespermis.fr
→ Payment via PSP/Payline
→ New bookings appear in STAGIAIRES tab

**Step 4 — Pre-Stage Management**
→ Monitor STAGIAIRES tab for "Complet" vs "Incomplet" files
→ Send reminders via messaging
→ Download attendance sheet and prefecture form

**Step 5 — Stage Happens**
→ Trainees attend
→ Mark attendance, check IDs, collect signatures

**Step 6 — Post-Stage Processing**
→ Go to ANTS section, transmit trainee data
→ Generate and send attestations to trainees
→ SimpliGestion (PSP admin) validates stage

**Step 7 — Get Paid**
→ Next payment cycle (2nd or 4th Wednesday)
→ PSP sends SEPA bank transfer
→ Transfer appears in VIREMENTS with invoice PDF

**Step 8 — Repeat**
→ Publish more stages, manage more trainees, receive more payments

---

## TECHNICAL NOTES

**Authentication**:
- `$_SESSION['membre']` stores center ID
- Every page checks session at top
- If not set, redirect to login

**Bootstrap Include**:
```php
require_once('../../common_bootstrap2/config.php');
require_once('includes/functions.php');
```
Loads database connections, session management, utilities from PSP 2

**Deprecated MySQL Functions**:
- Uses `mysql_query()`, `mysql_fetch_assoc()` (removed in PHP 7.0)
- Site must run on PHP 5.6 or earlier, or use compatibility layer
- Migration to `mysqli_*` or PDO required

**Type Page Constants**:
- Each page sets `$type_page` (e.g., `TYPE_PAGE_STAGES`, `TYPE_PAGE_INSCRIPTIONS`)
- Used for navigation highlighting and permissions

**Commission Calculation**:
- Complex logic depending on department, price tier, commission model (2024 vs legacy), special agreements
- Logic scattered across multiple files and database tables
- Must map carefully during migration

**ANTS Integration**:
- Critical for point restoration (legal requirement)
- Uses SOAP/XML to communicate with government system
- Without successful transmission, points don't get restored

**Agreement Number Validation**:
- R-format (e.g., R2509300070)
- Validation logic checks format
- Legally required — can't run course without valid prefecture agreement

---

## FILES INVENTORY

**Total**: ~250 PHP files in `www_3/ep/` folder

**Core Pages**: 7 main pages
**AJAX Handlers**: 100+ files
**Popups/Modals**: 30+ files
**Includes**: 15+ layout/utility files
**Additional Features**: ANTS, document generation, statistics, calendar, withdrawals, commission simulator

---

## MIGRATION CONSIDERATIONS

1. **Session-based authentication** — Every page checks `$_SESSION['membre']`
2. **All pages include same bootstrap** — Loads from PSP 2 shared infrastructure
3. **Deprecated MySQL functions** — Must migrate to mysqli/PDO
4. **Type page constants** — Used for navigation highlighting
5. **Commission calculation is complex** — Depends on multiple factors, scattered across files
6. **ANTS integration critical** — Legal requirement for point restoration
7. **Agreement number validation** — R-format required, legally mandated
8. **SEPA payment system** — Twice-monthly schedule, XML generation

---

**END OF ESPACE PARTENAIRE DOCUMENTATION**
