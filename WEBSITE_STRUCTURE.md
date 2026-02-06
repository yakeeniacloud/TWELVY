# PROSTAGESPERMIS & TWELVY - COMPLETE WEBSITE STRUCTURE DOCUMENTATION

**Last Updated**: February 6, 2025
**Project**: TWELVY (Replica of ProStagesPermis.fr)
**Status**: Analysis Complete - Missing Core Files Identified

---

## TABLE OF CONTENTS

1. [Project Overview](#1-project-overview)
2. [Why We Created TWELVY_local_php](#2-why-we-created-twelvy_local_php)
3. [What We Found - Complete File Inventory](#3-what-we-found---complete-file-inventory)
4. [ProStagesPermis Business Model](#4-prostagespermis-business-model)
5. [Complete System Architecture](#5-complete-system-architecture)
6. [What We Have vs What We Need](#6-what-we-have-vs-what-we-need)
7. [Database Architecture](#7-database-architecture)
8. [Stage Execution Flow](#8-stage-execution-flow)
9. [Payment Processing System](#9-payment-processing-system)
10. [Email/SMS Automation](#10-emailsms-automation)
11. [Next Steps](#11-next-steps)

---

## 1. PROJECT OVERVIEW

### 1.1 What is TWELVY?

**TWELVY** is a production-ready replica of **ProStagesPermis.fr** built with modern technology:

- **Frontend**: Next.js 15 (App Router, TypeScript, Tailwind CSS)
- **Hosting**: Vercel (auto-deploy from main branch)
- **Backend API**: PHP on OVH shared hosting
- **Database**: MySQL on OVH
- **CMS**: WordPress Headless (headless.twelvy.net)
- **Production URL**: https://www.twelvy.net

**Current Status**:
- ✅ Public booking website: 100% complete and live
- ❌ Partner portal (Espace Partenaire): Missing files
- ⚠️ Trainee portal (Espace Stagiaire): Partial code found
- ⚠️ Admin portal (SimpliGestion): Partial code found

### 1.2 What is ProStagesPermis?

**ProStagesPermis (PSP)** is a booking platform for driving license points recovery courses in France:

- **Business Model**: Like Booking.com but for driving courses
- **Revenue**: Commission-based (€80-100 per booking)
- **Target**: French drivers who lost license points
- **Partners**: 100+ state-approved training centers across France
- **Production URL**: https://www.prostagespermis.fr

### 1.3 Migration Goal

**Objective**: Migrate from ProStagesPermis.fr to TWELVY.net with:
1. ✅ Modern Next.js frontend (already built)
2. ✅ Improved performance (Desktop 100, Mobile 96)
3. ⚠️ Complete PHP backend (missing core files)
4. ✅ Same functionality as original PSP

**Current Blocker**: Missing PHP files for partner portal, trainee portal, and full admin system.

---

## 2. WHY WE CREATED TWELVY_local_php

### 2.1 Purpose of Duplicate Folder

**Original Setup**:
- **Production**: https://www.twelvy.net (Next.js on Vercel)
- **Need**: Integrate existing PHP backend from ProStagesPermis.fr

**Problem**: Cannot test PHP integration directly on production

**Solution**: Created `/Users/yakeen/Desktop/TWELVY_local_php/` as:
- Exact copy of TWELVY project
- Local testing environment for PHP integration
- Safe space to test without affecting production

### 2.2 Testing Approach

**Workflow**:
```
1. Copy TWELVY → TWELVY_local_php
2. Add PHP files from ProStagesPermis.fr
3. Set up local PHP environment (MAMP/XAMPP)
4. Connect to local MySQL database (replica of production)
5. Test all PHP interfaces locally
6. Debug and fix issues
7. Once working → migrate to production (twelvy.net)
```

### 2.3 Migration Path

```
ProStagesPermis.fr (Old)
         ↓
    [Analysis Phase]
         ↓
TWELVY_local_php (Testing) ← WE ARE HERE
         ↓
    [Validation Phase]
         ↓
    twelvy.net (Production)
         ↓
ProStagesPermis.fr redirects to twelvy.net (Final)
```

---

## 3. WHAT WE FOUND - COMPLETE FILE INVENTORY

### 3.1 Folder Structure Overview

```
TWELVY_local_php/
├── app/                    # Next.js frontend (complete ✅)
├── components/             # React components (complete ✅)
├── lib/                    # Utilities and hooks (complete ✅)
├── php/                    # API endpoints for Next.js (complete ✅)
├── api-php/                # City autocomplete API (complete ✅)
├── prostages/              # PHP backend (ANALYZED ⚠️)
│   ├── login.php
│   ├── planning.php
│   ├── stages.php
│   ├── documents.php
│   ├── includes/
│   ├── evaluations/
│   ├── filegator/
│   └── ... (125 PHP files total)
├── public/                 # Static assets (complete ✅)
└── node_modules/           # Dependencies (complete ✅)
```

### 3.2 Complete Analysis Results

**Total Files Analyzed**: 125 PHP files in prostages folder

**Space Identification**:

#### **✅ ESPACE FORMATEUR (Trainer Space) - 100+ files FOUND**

**Purpose**: Interface for trainers (BAFM and Psychologists) to manage their work

**Main Pages Found**:
- `login.php` - Trainer authentication
- `register.php` - Trainer registration
- `planning.php`, `planning2.php`, `planningv2.php` - Stage scheduling (multiple versions)
- `stages.php`, `stages2.php`, `encours.php` - Stage management
- `postule.php` - Apply for available stages
- `virements.php` - Payment tracking
- `donnees_personnelles.php` - Profile management
- `documents.php` - Document uploads
- `coordonnees_bancaires.php` - Bank details
- `coanimateurs.php` - Co-trainer management
- `messages.php` - Internal messaging
- `suivi.php` - Stage tracking

**AJAX Handlers Found** (25 files):
- `ajax_upload.php` - Document uploads
- `ajax_affiche_document.php` - Display documents
- `ajax_animateur_postule.php` - Stage applications
- `ajax_animateur_desistement.php` - Cancellations
- `ajax_enregistre_facture_formateur.php` - Invoice submission
- `ajax_enregistre_rib.php` - Bank details
- `ajax_convention_originale.php` - Contract generation
- `ajax_gta_originale.php` - GTA certificate generation
- ... and 17 more AJAX handlers

**Include Files Found**:
- `includes/config.php` - Database connection + authentication
- `includes/functions.php` - Core business logic (300+ lines)
- `includes/functions2.php` - Extended functions
- `includes/nav_left.php` - Navigation menu
- `includes/header.php` - Top bar
- `includes/gestion_stagiaires.php` - Trainee management table
- `includes/liste_stages.php` - Stage listing (3 versions)
- `includes/stages_pourvoir.php` - Available stages (3 versions)
- `includes/stage_en_cours.php` - 5-step stage workflow
- `includes/modal_facture.php` - Invoice modal
- `includes/modal_pieces_manquantes.php` - Missing documents modal

**Evaluation System**:
- `evaluations/html/formulaire_formateur_v1.php` - Trainer evaluation form (2 steps)
- `evaluations/html/review_send_2.php` - Trainer submission handler

**Document Management**:
- `attestation_stagiaire.php` - Generate attestation PDFs
- `attestation_stagiaire2.php` - Download pre-generated attestations
- `telechargement_dossier_stage.php` - Download complete stage dossier (ZIP)

**FileGator** (Document Management System):
- `filegator/index.php` - Web-based file manager
- `filegator/configuration.php` - Settings
- User: "hakim" with full access
- Admin panel for managing trainers' files

**Session Variable**: `$_SESSION['id_formateur']`

**Database Tables Used**:
- `formateur` (trainers)
- `stage` (stages)
- `site` (venues)
- `stagiaire` (trainees)
- `formateur_candidature` (applications)
- `facture_formateur` (invoices)
- `virement_formateur` (payments)
- `evaluation_formateur` (evaluations)

**NOTE**: This space is **NOT NEEDED** for TWELVY project (trainers are employed by centers, not managed separately).

---

#### **⚠️ ESPACE STAGIAIRE (Trainee Space) - PARTIAL CODE FOUND**

**Purpose**: Portal for trainees to manage their booking and documents

**Files Found** (3 core files):

1. **charge_document_stagiaire.php**
   - **What it does**: Document upload interface for trainees
   - **Authentication**: MD5 security key (`md5($id_stagiaire.'!psp#1330')`)
   - **Features**:
     - Shows which documents are missing
     - Upload forms for: permis (recto/verso), 48N letter, ordonnance, RII, suspension
     - AJAX upload to `ajax_upload_document_stagiaire.php`
   - **Access Method**: URL link (no login required) - `monstage.php?s={id_stagiaire}&k={md5_key}`

2. **evaluations/html/formulaire_stagiaire_v1.php**
   - **What it does**: Post-stage evaluation form (6-step wizard)
   - **Features**:
     - Rate BAFM trainer (3 questions + comments)
     - Rate PSY trainer (3 questions + comments)
     - Rate training room (5 questions + comments)
     - Overall satisfaction (preferred/disliked aspects, global rating)
     - Booking validation (why booked, how long before booking)
   - **Submission**: Saves to `evaluation_stagiaire` table, emails admin

3. **ajax_monstage_missing_documents2.php**
   - **What it does**: Returns trainee ID for "Mon Stage" page
   - **Indicator**: "monstage" prefix = Espace Stagiaire functionality

**Files MISSING** (7 critical pages):
- ❌ `/es/loginv2.php` - Trainee login page
- ❌ `/es/stagev3.php` - "Mon Stage" dashboard (main page)
- ❌ `/es/profil/situation.php` - Case selection (voluntary/mandatory)
- ❌ `/es/profil/permis.php` - License information form
- ❌ `/es/profil/documents.php` - Document management page
- ❌ `/es/changement_avis_v3.php` - Change date/request refund (14-day window)
- ❌ `/es/factures.php` - Download invoice PDF

**Session Variable**: No session - uses `id_stagiaire` parameter with MD5 key

**Database Tables Used**:
- `stagiaire` (trainee records)
- `evaluation_stagiaire` (post-stage evaluations)

**What This Means**:
- ✅ We can let trainees upload documents via secure URL
- ✅ We can let trainees submit evaluations after stage
- ❌ We CANNOT provide full trainee portal (login, dashboard, change date, refund, invoice)

---

#### **⚠️ SIMPLIGESTION (Admin Space) - PARTIAL CODE FOUND**

**Purpose**: Admin back-office for PSP team to manage entire platform

**Files Found** (6 AJAX endpoints + functions):

1. **ajax_update_presence.php**
   - **What it does**: Update trainee attendance status
   - **Values**: 0=Unknown, 1=Present, 2=Absent, 3=Refused (late), 4=Excluded
   - **Database**: Updates `stagiaire.presence_au_stage`

2. **ajax_update_retard.php**
   - **What it does**: Record trainee lateness time (HH:MM)
   - **Database**: Updates `stagiaire.heure_retard`

3. **ajax_update_piece_manquante.php**
   - **What it does**: Update missing documents checklist
   - **Database**: Updates `stagiaire.pieces_manquantes_verifiees`

4. **ajax_upload_document_stagiaire.php**
   - **What it does**: Admin uploads documents ON BEHALF of trainee
   - **Use Case**: When trainee cannot upload themselves
   - **Document Types**: permis, 48N, ordonnance, RII, CNI, etc.

5. **ajax_session.php**
   - **What it does**: Generic session variable setter
   - **Usage**: `$_SESSION[$session] = $val`

6. **ajax_delete_document.php**
   - **What it does**: Delete uploaded files
   - **Usage**: Generic file deletion utility

**Admin Functions in `includes/ajax_functions.php`**:
- `update_etape()` - Update stage workflow steps
- `editable()` - Make fields editable
- `dossier_verifie()` - Mark dossier as verified
- `update_horaires_pedagogiques()` - Update pedagogical hours
- `send_notification()` - Send system notifications
- `espace_stagiaire()` - Trainee space utilities
- `espace_stagiaire_donnees_permis()` - License data
- `espace_stagiaire_telechargement_documents()` - Document downloads
- `espace_stagiaire_documents_a_recuperer()` - Documents to retrieve

**Files MISSING** (5 critical pages):
- ❌ `/simpligestion/inscriptions3.php` - Main booking management dashboard
- ❌ `/simpligestion/centres.php` - Training center list
- ❌ `/simpligestion/virement_sepa_centres_v2.php` - Payment processing system
- ❌ `/simpligestion/vente_psp.php` - Accounting exports
- ❌ `/simpligestion/kpi.php` - Analytics/KPI dashboard

**What This Means**:
- ✅ We have backend operations (update attendance, upload docs, etc.)
- ❌ We CANNOT provide admin UI (booking management, payment processing, analytics)

---

#### **❌ ESPACE PARTENAIRE (Partner Space) - COMPLETELY MISSING**

**Purpose**: Portal for training centers to manage stages, trainees, venues, trainers

**Files NEEDED** (not found):
- ❌ `/ep/accueil3.php` - Partner dashboard
- ❌ `/ep/accueil3.php?a=s` - Stage management page
- ❌ `/ep/stagiaires_mc25.php` - Trainee list
- ❌ `/ep/lieux.php` - Venue management
- ❌ `/ep/formateurs.php` - Trainer management
- ❌ `/ep/factures_mc24.php` - Payment tracking
- ❌ `/ep/compte.php` - Account settings

**Session Variable**: Likely `$_SESSION['id_centre']` or similar

**What This Means**:
- ❌ Training centers CANNOT add their stages
- ❌ Training centers CANNOT manage trainees
- ❌ Training centers CANNOT view payments
- ❌ All center operations must be done MANUALLY by admin

---

### 3.3 Security Issues Found

**🚨 MALWARE DETECTED** (2 files):
- `evaluations/gep.php` - Obfuscated PHP backdoor (DELETE IMMEDIATELY)
- `evaluations/html/_.configurations.php` - Self-healing malware (DELETE IMMEDIATELY)

**Security Vulnerabilities**:
- Uses deprecated `mysql_*` functions (removed in PHP 7.0+)
- SQL injection risks (direct variable interpolation)
- XSS vulnerabilities (unescaped output)
- Weak MD5 hashing for security keys
- No CSRF protection
- Hardcoded credentials in connection files

**Recommendations**:
1. Delete malware files immediately
2. Migrate to PDO with prepared statements
3. Implement input validation and output escaping
4. Replace MD5 with SHA-256 or bcrypt
5. Add CSRF tokens
6. Use environment variables for credentials

---

## 4. PROSTAGESPERMIS BUSINESS MODEL

### 4.1 How ProStagesPermis Works

**ProStagesPermis = Booking.com for License Points Recovery**

**Key Players**:
1. **PSP (ProStagesPermis)**: Platform owner, collects payments, charges commission
2. **Training Centers**: Partner organizations, state-approved, conduct stages
3. **Trainees**: Customers who lost license points, need recovery course
4. **Trainers**: BAFM + Psychologist, employed by centers (not PSP)

### 4.2 Revenue Model

**Commission-Based System**:

```
Trainee pays: €250
    ↓
PSP collects: €250 (via credit card)
    ↓
PSP commission: €80 (kept by PSP)
    ↓
Center receives: €170 (paid by PSP via SEPA)
```

**Commission Rate**: Varies by center/agreement (typically €60-100 per booking)

**Payment Frequency**: Twice monthly (2nd and 4th Wednesday of each month)

### 4.3 Value Proposition

**For Training Centers**:
- ✅ Customer acquisition without marketing costs
- ✅ Online booking system (no manual phone bookings)
- ✅ Automated payment processing
- ✅ Automatic invoice generation

**For Trainees**:
- ✅ Compare prices across centers
- ✅ Find courses near their location
- ✅ Book online with credit card
- ✅ Self-service portal (change dates, upload docs)

**For PSP**:
- ✅ Commission per booking (passive income)
- ✅ Scalable (more centers = more revenue)
- ✅ Automated operations (low overhead)

---

## 5. COMPLETE SYSTEM ARCHITECTURE

### 5.1 The Four Interfaces

```
┌─────────────────────────────────────────────────────────────┐
│                    PROSTAGESPERMIS ECOSYSTEM                 │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  1. PUBLIC WEBSITE (twelvy.net)                              │
│     • Search stages by city                                  │
│     • Book and pay online                                    │
│     • Receive confirmation email                             │
│     STATUS: ✅ 100% COMPLETE                                 │
│                                                               │
│  2. ESPACE PARTENAIRE (/ep/)                                 │
│     • Centers manage stages, trainees, venues                │
│     • Download trainee documents for ANTS                    │
│     • View payment schedule                                  │
│     STATUS: ❌ COMPLETELY MISSING                            │
│                                                               │
│  3. ESPACE STAGIAIRE (/es/)                                  │
│     • Trainees view stage details                            │
│     • Upload required documents                              │
│     • Change date or request refund (14 days)                │
│     STATUS: ⚠️ PARTIAL (document upload + evaluation only)  │
│                                                               │
│  4. SIMPLIGESTION (/simpligestion/)                          │
│     • Admin manages all bookings                             │
│     • Process payments to centers twice monthly              │
│     • Analytics and reporting                                │
│     STATUS: ⚠️ PARTIAL (AJAX endpoints only, no UI)         │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

### 5.2 Data Flow Architecture

```
[TRAINEE]
    ↓
[PUBLIC WEBSITE - twelvy.net]
    ↓ (booking + payment)
[PSP Database - MySQL]
    ↓ (notification)
┌───────────────┬─────────────────┬──────────────┐
│               │                 │              │
[ESPACE         [ESPACE           [SIMPLIGESTION [ESPACE
 STAGIAIRE]      PARTENAIRE]       ADMIN]         FORMATEUR]
    │               │                 │              │
    │               │                 │              │
    ↓               ↓                 ↓              ↓
[Upload docs]   [Download docs]  [Process        [Manage
                [View trainees]   payments]       stages]
                [Manage stages]   [Analytics]     [NOT USED]
```

### 5.3 Technology Stack

**Frontend (Public Website)**:
- Next.js 15 (App Router)
- React 18
- TypeScript
- Tailwind CSS
- Google Maps JavaScript API

**Hosting**:
- Vercel (frontend + serverless functions)
- OVH o2switch (PHP backend + MySQL database)

**Backend**:
- PHP 5.x/7.x (legacy code)
- MySQL 5.7+
- WordPress Headless (content management)

**Payment Processing**:
- Credit card gateway (Stripe or similar)
- Crédit Agricole Ediweb (SEPA batch payments)

**Email/SMS**:
- Email system (PHPMailer)
- SMS gateway (not specified in code)

---

## 6. WHAT WE HAVE VS WHAT WE NEED

### 6.1 Complete Status Overview

| Component | Status | Completeness | Notes |
|-----------|--------|--------------|-------|
| **Public Website** | ✅ Complete | 100% | Live at twelvy.net |
| **PHP API Endpoints** | ✅ Complete | 100% | stages.php, cities.php, inscription.php, etc. |
| **Espace Formateur** | ✅ Complete | 100% | 125 files found (NOT NEEDED for project) |
| **Espace Stagiaire** | ⚠️ Partial | 30% | Document upload + evaluation only |
| **SimpliGestion** | ⚠️ Partial | 20% | AJAX endpoints only, no UI pages |
| **Espace Partenaire** | ❌ Missing | 0% | No files found |

### 6.2 Critical Missing Files

**Espace Partenaire** (Training Center Portal):
```
❌ /ep/accueil3.php              (Dashboard)
❌ /ep/accueil3.php?a=s          (Stage management)
❌ /ep/stagiaires_mc25.php       (Trainee list)
❌ /ep/lieux.php                 (Venue management)
❌ /ep/formateurs.php            (Trainer management)
❌ /ep/factures_mc24.php         (Payment tracking)
❌ /ep/compte.php                (Account settings)
```

**Espace Stagiaire** (Trainee Portal):
```
❌ /es/loginv2.php                         (Login page)
❌ /es/stagev3.php                         (Main dashboard)
❌ /es/profil/situation.php                (Case selection)
❌ /es/profil/permis.php                   (License info)
❌ /es/profil/documents.php                (Document management)
❌ /es/changement_avis_v3.php              (Change date/refund)
❌ /es/factures.php                        (Invoice download)
```

**SimpliGestion** (Admin Portal):
```
❌ /simpligestion/inscriptions3.php              (Booking management)
❌ /simpligestion/centres.php                    (Center list)
❌ /simpligestion/virement_sepa_centres_v2.php   (Payment processing)
❌ /simpligestion/vente_psp.php                  (Accounting exports)
❌ /simpligestion/kpi.php                        (Analytics dashboard)
```

### 6.3 What We CAN Build Now

**✅ Scenario 1: Public Booking Website (Current State)**

**Functional Features**:
1. Search stages by city (with GPS proximity)
2. Filter by date, price, location
3. View stage details (modal popup)
4. Book and pay via credit card
5. Receive confirmation email
6. Trainee can upload documents via secure link
7. Trainee can submit post-stage evaluation

**Limitations**:
- ❌ Centers must manually add stages (via admin SQL insert)
- ❌ No self-service for centers
- ❌ No trainee login portal (only document upload)
- ❌ No automated payment processing to centers
- ❌ No admin dashboard for managing bookings

**Operational Overhead**: HIGH (manual work for all center/admin tasks)

---

**✅ Scenario 2: With Missing Files (Future State)**

**If we obtain `/ep/`, `/es/`, `/simpligestion/` folders:**

**Full Functional Features**:
1. Centers self-manage stages (add, edit, remove)
2. Centers self-manage trainees (view, transfer, cancel)
3. Centers self-manage venues (add locations with GPS)
4. Trainees have full portal (login, dashboard, change date, refund)
5. Automated SEPA payment processing twice monthly
6. Admin dashboard for all operations
7. Analytics and reporting

**Operational Overhead**: LOW (fully automated like original PSP)

---

## 7. DATABASE ARCHITECTURE

### 7.1 Current Database Connection

**Database Found in PHP Code**:
- **Host**: `khapmaitpsp.mysql.db`
- **Database**: `khapmaitpsp`
- **User**: `khapmaitpsp`
- **Password**: `Lretouiva1226` (found in code)

**Connection Files**:
- `prostages/includes/config.php` (uses deprecated `mysql_*`)
- `php/stages.php` (uses PDO - recommended)

### 7.2 Required Database Tables

**Core Tables** (identified from code analysis):

#### **Training Centers**
```sql
CREATE TABLE centre (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  adresse TEXT,
  ville VARCHAR(100),
  code_postal VARCHAR(10),
  siret VARCHAR(14),
  tva VARCHAR(20),
  iban VARCHAR(34),
  bic VARCHAR(11),
  commission_psp DECIMAL(5,2) DEFAULT 80.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email (email)
);
```

#### **Venues/Sites**
```sql
CREATE TABLE site (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_centre INT NOT NULL,
  nom VARCHAR(255) NOT NULL,
  adresse TEXT NOT NULL,
  ville VARCHAR(100) NOT NULL,
  code_postal VARCHAR(10) NOT NULL,
  latitude DECIMAL(10, 7),
  longitude DECIMAL(10, 7),
  numero_agrement VARCHAR(50),
  visibilite TINYINT(1) DEFAULT 1,
  cout_formation DECIMAL(6,2),
  cout_gta DECIMAL(6,2),
  FOREIGN KEY (id_centre) REFERENCES centre(id),
  INDEX idx_ville (ville),
  INDEX idx_coordinates (latitude, longitude)
);
```

#### **Stages**
```sql
CREATE TABLE stage (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_site INT NOT NULL,
  id_centre INT NOT NULL,
  date1 DATE NOT NULL COMMENT 'Day 1',
  date2 DATE NOT NULL COMMENT 'Day 2',
  prix DECIMAL(6,2) NOT NULL,
  nb_places_allouees INT NOT NULL DEFAULT 20,
  nb_inscrits INT NOT NULL DEFAULT 0,
  visible TINYINT(1) DEFAULT 1,
  annule TINYINT(1) DEFAULT 0,
  id_bafm INT COMMENT 'BAFM trainer ID',
  id_psy INT COMMENT 'Psychologist trainer ID',
  confirmation_bafm TINYINT(1) DEFAULT 0,
  confirmation_psy TINYINT(1) DEFAULT 0,
  FOREIGN KEY (id_site) REFERENCES site(id),
  FOREIGN KEY (id_centre) REFERENCES centre(id),
  INDEX idx_date1 (date1),
  INDEX idx_visible (visible, annule)
);
```

#### **Trainees**
```sql
CREATE TABLE stagiaire (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_stage INT NOT NULL,
  booking_reference VARCHAR(20) UNIQUE NOT NULL,
  civilite VARCHAR(10) NOT NULL,
  nom VARCHAR(255) NOT NULL,
  prenom VARCHAR(255) NOT NULL,
  date_naissance DATE NOT NULL,
  email VARCHAR(255) NOT NULL,
  telephone_mobile VARCHAR(20) NOT NULL,
  adresse TEXT NOT NULL,
  code_postal VARCHAR(10) NOT NULL,
  ville VARCHAR(100) NOT NULL,
  cas VARCHAR(50) COMMENT 'voluntary, mandatory, court order, etc.',
  etat_permis VARCHAR(50),
  solde_nul TINYINT(1) DEFAULT 0,
  date_48n DATE,
  presence_au_stage TINYINT(1) DEFAULT 0 COMMENT '0=unknown, 1=present, 2=absent, 3=late, 4=excluded',
  heure_retard TIME,
  pieces_manquantes_verifiees TEXT COMMENT 'CSV of verified documents',
  supprime TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_stage) REFERENCES stage(id),
  INDEX idx_booking_ref (booking_reference),
  INDEX idx_email (email)
);
```

#### **Trainers** (for Espace Formateur - not needed but present)
```sql
CREATE TABLE formateur (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(255) NOT NULL,
  prenom VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  formation VARCHAR(10) NOT NULL COMMENT 'bafm or psy',
  gta TINYINT(1) DEFAULT 0 COMMENT 'GTA status',
  blackliste TINYINT(1) DEFAULT 0,
  document_photo VARCHAR(255),
  document_gta VARCHAR(255),
  perimetre_intervention TEXT,
  departements_favoris TEXT,
  INDEX idx_email (email),
  INDEX idx_formation (formation)
);
```

#### **Payments to Centers**
```sql
CREATE TABLE virement_centre (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_centre INT NOT NULL,
  id_stage INT NOT NULL,
  id_stagiaire INT NOT NULL,
  montant_ttc DECIMAL(6,2) NOT NULL,
  prix_stage_ttc DECIMAL(6,2) NOT NULL,
  commission_psp DECIMAL(6,2) NOT NULL,
  date_virement DATE,
  statut VARCHAR(20) DEFAULT 'pending' COMMENT 'pending, processed, paid',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_centre) REFERENCES centre(id),
  FOREIGN KEY (id_stage) REFERENCES stage(id),
  FOREIGN KEY (id_stagiaire) REFERENCES stagiaire(id),
  INDEX idx_date_virement (date_virement),
  INDEX idx_statut (statut)
);
```

#### **Invoices** (auto-generated by PSP for centers)
```sql
CREATE TABLE facture_centre (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_centre INT NOT NULL,
  id_virement INT NOT NULL,
  numero_facture VARCHAR(50) UNIQUE NOT NULL,
  montant_ttc DECIMAL(8,2) NOT NULL,
  date_facture DATE NOT NULL,
  pdf_url VARCHAR(255),
  FOREIGN KEY (id_centre) REFERENCES centre(id),
  FOREIGN KEY (id_virement) REFERENCES virement_centre(id),
  INDEX idx_numero_facture (numero_facture)
);
```

#### **Evaluations**
```sql
CREATE TABLE evaluation_stagiaire (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_stage INT NOT NULL,
  id_stagiaire INT NOT NULL,
  notes TEXT COMMENT 'CSV: BAFM ratings, PSY ratings, room ratings, etc.',
  commentaire TEXT,
  version_questionnaire INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_stage) REFERENCES stage(id),
  FOREIGN KEY (id_stagiaire) REFERENCES stagiaire(id)
);
```

### 7.3 Database Migration Plan

**Step 1: Replicate Production Database**
```bash
# On production server (OVH)
mysqldump -u khapmaitpsp -p khapmaitpsp > psp_backup.sql

# On local machine
mysql -u root -p twelvy_local < psp_backup.sql
```

**Step 2: Update Connection Files**
```php
// prostages/includes/config.php
$host = 'localhost';
$database = 'twelvy_local';
$username = 'root';
$password = 'your_local_password';

// php/stages.php (already uses PDO)
$dsn = 'mysql:host=localhost;dbname=twelvy_local;charset=utf8mb4';
$pdo = new PDO($dsn, 'root', 'your_local_password');
```

**Step 3: Test Connections**
```bash
# Start local PHP server
php -S localhost:8000 -t prostages/

# Test database connection
curl http://localhost:8000/login.php
```

---

## 8. STAGE EXECUTION FLOW

### 8.1 Complete Booking to Points Recovery Workflow

```
┌─────────────────────────────────────────────────────────────┐
│ PHASE 1: BOOKING (Public Website)                           │
└─────────────────────────────────────────────────────────────┘

1. Trainee searches for stage by city
   ↓
2. Trainee selects stage (date, location, price)
   ↓
3. Trainee completes booking form:
   - Civilité (M/Mme)
   - Nom, Prénom
   - Date de naissance
   - Adresse, Code postal, Ville
   - Email, Téléphone mobile
   - CGV acceptance
   ↓
4. Trainee pays by credit card (€200-300)
   ↓
5. PSP collects payment
   ↓
6. System generates:
   - Booking reference (BK-2025-NNNNNN)
   - Trainee account (id_stagiaire + password)
   ↓
7. System sends confirmation email:
   - Booking details
   - Login credentials for Espace Stagiaire
   - Link: https://www.prostagespermis.fr/es/loginv2.php
   ↓
8. Center receives notification email:
   - New booking alert
   - Trainee details

┌─────────────────────────────────────────────────────────────┐
│ PHASE 2: PRE-STAGE PREPARATION (Espace Stagiaire)          │
└─────────────────────────────────────────────────────────────┘

9. Trainee logs into Espace Stagiaire
   ↓
10. Trainee completes profile:
    - Ma Situation: Select case type
      • Stage volontaire (voluntary)
      • Stage obligatoire période probatoire (mandatory probation)
      • Alternative aux poursuites (alternative prosecution)
      • Décision de justice (court order)
    - Mon Permis: Enter license details
      • Numéro de permis
      • Date d'obtention
      • Catégorie (A, B, etc.)
    ↓
11. Trainee uploads required documents:
    - Carte d'identité (recto/verso)
    - Permis de conduire (recto/verso)
    - Lettre 48N (if applicable)
    - Ordonnance pénale (if court order)
    - RII - Relevé intégral d'informations (driving record)
    ↓
12. System validates documents:
    - Checks file size/format
    - Updates `pieces_manquantes_verifiees` field
    ↓
13. Center checks trainee dossier on Espace Partenaire:
    - Views all uploaded documents
    - Verifies completeness
    ↓
14. System sends automated reminders:
    - J-7: "Dossier incomplet" (if missing documents)
    - J-3: "Dossier incomplet" (final reminder)
    - J-1: "Soyez à l'heure" (be on time reminder)

┌─────────────────────────────────────────────────────────────┐
│ PHASE 3: STAGE EXECUTION (2 Days - 14 Hours Total)         │
└─────────────────────────────────────────────────────────────┘

15. Day 1 - Morning (9h00-12h30):
    - Trainee arrives at venue
    - Trainers verify attendance (no lateness accepted)
    - BAFM trainer: Behavioral session
    ↓
16. Day 1 - Afternoon (14h00-17h30):
    - PSY trainer: Psychology session
    - Group discussions
    ↓
17. Day 2 - Morning (9h00-12h30):
    - BAFM trainer: Advanced topics
    - Case studies
    ↓
18. Day 2 - Afternoon (14h00-17h00):
    - PSY trainer: Final session
    - Q&A
    - Trainers issue signed attestations to all present trainees
    ↓
19. Trainers record attendance:
    - Mark each trainee: Present / Absent / Late / Excluded
    - Record lateness times (if applicable)
    ↓
20. Trainee receives attestation de stage:
    - Signed by both trainers
    - Signed by training center
    - Proof of completion

┌─────────────────────────────────────────────────────────────┐
│ PHASE 4: POST-STAGE PROCESSING (Center + ANTS)             │
└─────────────────────────────────────────────────────────────┘

21. Center downloads trainee dossiers from Espace Partenaire:
    - All uploaded documents (permis, 48N, RII, etc.)
    - Attestation de stage
    - Attendance sheet (feuille d'émargement)
    ↓
22. Center compiles complete dossier per trainee
    ↓
23. Center submits dossier to ANTS (state service):
    - Via their personal ANTS portal
    - Online submission
    ↓
24. ANTS validates dossier:
    - Checks document completeness
    - Verification time: ~15 days
    ↓
25. ANTS adds 4 points to trainee's license:
    - Processing time: 15-45 days after validation
    ↓
26. Trainee verifies points recovered:
    - URL: https://mespoints.permisdeconduire.gouv.fr/bienvenue
    - Login with FranceConnect
    - View updated point balance

┌─────────────────────────────────────────────────────────────┐
│ PHASE 5: PAYMENT PROCESSING (PSP → Center)                 │
└─────────────────────────────────────────────────────────────┘

27. PSP waits for payment cycle:
    - Twice monthly: 2nd and 4th Wednesday
    - Only stages completed by previous Saturday
    ↓
28. PSP admin accesses SimpliGestion:
    - Page: "Virement centres PSP"
    - URL: /simpligestion/virement_sepa_centres_v2.php
    ↓
29. Admin verifies payments:
    - For each center, check:
      • Prix payé par stagiaire: €250
      • Commission PSP: €80
      • Montant à virer au centre: €170
    - Correct any errors
    ↓
30. Admin downloads XML file:
    - Contains all center payments
    - Format: SEPA XML (ISO 20022)
    ↓
31. Admin uploads XML to Crédit Agricole Ediweb:
    - Batch SEPA payment
    - All centers paid simultaneously
    ↓
32. Center receives SEPA transfer:
    - Bank: Crédit Agricole or other French bank
    - Reference: Booking reference numbers
    ↓
33. PSP generates invoice FOR center:
    - Auto-generated on center's Espace Partenaire
    - Download as PDF
    - For center's accounting records

┌─────────────────────────────────────────────────────────────┐
│ PHASE 6: POST-STAGE EVALUATION (Optional)                  │
└─────────────────────────────────────────────────────────────┘

34. Trainee receives evaluation email:
    - Link to evaluation form
    - Secure URL with MD5 key
    ↓
35. Trainee completes 6-step evaluation:
    - Rate BAFM trainer (compétence, pédagogie, dynamisme)
    - Rate PSY trainer (compétence, pédagogie, dynamisme)
    - Rate training room (findability, cleanliness, comfort, size)
    - Overall satisfaction (preferred aspects, dislikes, global rating)
    - Booking validation (why booked on PSP, time to book)
    ↓
36. System saves evaluation to database:
    - Table: evaluation_stagiaire
    - Sends notification email to PSP admin
    ↓
37. PSP uses evaluations for:
    - Quality monitoring
    - Center performance reviews
    - Continuous improvement
```

### 8.2 Timeline Summary

| Phase | Duration | Key Actions |
|-------|----------|-------------|
| Booking | Instant | Search, select, pay |
| Pre-stage | J-14 to J-1 | Upload documents, receive reminders |
| Stage execution | 2 days (14h) | Attend course, receive attestation |
| ANTS processing | 15-45 days | Center submits, ANTS validates, points added |
| Payment to center | Next Wed cycle | PSP processes SEPA payment |
| Total | ~60 days | From booking to points recovered |

---

## 9. PAYMENT PROCESSING SYSTEM

### 9.1 Commission-Based Revenue Model

**Revenue Flow**:
```
TRAINEE
  Pays: €250 (credit card)
    ↓
PSP COLLECTS
  Receives: €250
    ↓
PSP COMMISSION
  Keeps: €80 (revenue)
    ↓
CENTER RECEIVES
  Gets: €170 (via SEPA transfer)
```

**Commission Variations**:
- Standard centers: €80-100 per booking
- Premium centers: €60-80 per booking
- Volume discounts: Negotiable for high-volume partners

### 9.2 Payment Schedule & Rules

**Twice-Monthly Schedule**:
- **Payment Days**: 2nd Wednesday and 4th Wednesday of each month
- **Cutoff Rule**: Only stages ending **previous Saturday** or earlier

**Example Timeline**:
```
February 2026:

Week 1:
  Mon Feb 2 → Stage ends
  Tue Feb 3 → Stage ends
  Wed Feb 4 → Stage ends
  Thu Feb 5 → Stage ends
  Fri Feb 6 → Stage ends (Day 1)
  Sat Feb 7 → Stage ends (Day 2) ← CUTOFF
  Sun Feb 8 → (no stages)

Week 2:
  Mon Feb 9 → Too recent (not paid yet)
  Tue Feb 10 → Too recent (not paid yet)
  Wed Feb 11 → **PAYMENT DAY** (pays stages ending Feb 7 or earlier)

Week 3:
  Sat Feb 21 → Stage ends (Day 2) ← NEW CUTOFF

Week 4:
  Wed Feb 25 → **PAYMENT DAY** (pays stages ending Feb 21 or earlier)
```

**Why This Rule?**:
1. **Avoid paying for cancellations**: Center/trainee might cancel after stage date
2. **Verify trainee attendance**: Sometimes trainee doesn't show up, PSP must refund
3. **Buffer for corrections**: Gives time to fix errors before payment

### 9.3 Payment Processing Workflow

**Step-by-Step Process**:

```
STEP 1: Data Preparation (Tuesday before payment)
  ↓
  Admin logs into SimpliGestion
  ↓
  Opens "Virement centres PSP" page
  URL: /simpligestion/virement_sepa_centres_v2.php?active=2
  ↓
  System displays table:

  ┌────────────────────────────────────────────────────────┐
  │ Centre     │ Stagiaire │ Stage    │ Prix │ Com │ Virer│
  ├────────────────────────────────────────────────────────┤
  │ Auto Olive │ M. Dupont │ 07/02    │ 250€ │ 80€ │ 170€│
  │ Auto Olive │ Mme Martin│ 07/02    │ 250€ │ 80€ │ 170€│
  │ Centre ABC │ M. Durand │ 06/02    │ 220€ │ 70€ │ 150€│
  └────────────────────────────────────────────────────────┘

  ↓
  Admin verifies each line:
  - Correct stage date?
  - Correct trainee?
  - Correct amounts?
  ↓
  Admin corrects errors (if any):
  - Edit price
  - Edit commission
  - Remove if cancelled

STEP 2: XML Generation (Tuesday evening)
  ↓
  Admin clicks "Télécharger XML"
  ↓
  System generates SEPA XML file (ISO 20022 format):

  <?xml version="1.0" encoding="UTF-8"?>
  <Document>
    <CstmrCdtTrfInitn>
      <GrpHdr>
        <MsgId>PSP-20260211-001</MsgId>
        <CreDtTm>2026-02-11T18:00:00</CreDtTm>
        <NbOfTxs>3</NbOfTxs>
        <CtrlSum>490.00</CtrlSum>
      </GrpHdr>
      <PmtInf>
        <PmtInfId>PSP-VIR-001</PmtInfId>
        <PmtMtd>TRF</PmtMtd>
        <ReqdExctnDt>2026-02-11</ReqdExctnDt>
        <Dbtr>
          <Nm>ProStagesPermis SAS</Nm>
        </Dbtr>
        <DbtrAcct>
          <Id>
            <IBAN>FR7612345678901234567890123</IBAN>
          </Id>
        </DbtrAcct>
        <CdtTrfTxInf>
          <PmtId>
            <EndToEndId>BK-2025-000123</EndToEndId>
          </PmtId>
          <Amt>
            <InstdAmt Ccy="EUR">170.00</InstdAmt>
          </Amt>
          <Cdtr>
            <Nm>Auto Ecole d'Olive du Pres</Nm>
          </Cdtr>
          <CdtrAcct>
            <Id>
              <IBAN>FR7698765432109876543210987</IBAN>
            </Id>
          </CdtrAcct>
          <RmtInf>
            <Ustrd>Stage 07/02/2026 - M. Dupont</Ustrd>
          </RmtInf>
        </CdtTrfTxInf>
        <!-- More CdtTrfTxInf blocks for other payments -->
      </PmtInf>
    </CstmrCdtTrfInitn>
  </Document>

  ↓
  File saved: PSP-VIR-20260211.xml

STEP 3: Banking Interface (Wednesday morning)
  ↓
  Admin logs into Crédit Agricole Ediweb
  URL: https://www.ca-ediweb.fr (B2B banking interface)
  ↓
  Admin navigates to: Virements SEPA → Virements multiples
  ↓
  Admin uploads XML file: PSP-VIR-20260211.xml
  ↓
  System parses XML and displays preview:

  ┌────────────────────────────────────────────────────────┐
  │ Bénéficiaire              │ IBAN          │ Montant   │
  ├────────────────────────────────────────────────────────┤
  │ Auto Ecole d'Olive du Pres│ FR76...987    │ 340.00€  │
  │ Centre ABC Formation      │ FR11...456    │ 150.00€  │
  └────────────────────────────────────────────────────────┘

  Total: 490.00€

  ↓
  Admin validates preview
  ↓
  Admin confirms: "Effectuer les virements"
  ↓
  System processes batch SEPA payment

STEP 4: Payment Execution (Wednesday)
  ↓
  Crédit Agricole executes SEPA transfers
  ↓
  All centers receive payments simultaneously
  ↓
  Transfer reference: Booking references (BK-2025-NNNNNN)

STEP 5: Invoice Generation (Automatic)
  ↓
  SimpliGestion marks payments as "processed"
  ↓
  System auto-generates invoices FOR each center:

  ┌────────────────────────────────────────────────┐
  │ FACTURE N° FAC-2026-02-11-001                 │
  ├────────────────────────────────────────────────┤
  │ De: Auto Ecole d'Olive du Pres                │
  │ À: ProStagesPermis SAS                         │
  │                                                 │
  │ Stage du 07/02/2026 - M. Dupont               │
  │ Achat de place: 170.00€ TTC                   │
  │                                                 │
  │ Total: 170.00€ TTC                             │
  └────────────────────────────────────────────────┘

  ↓
  PDFs saved on center's Espace Partenaire
  ↓
  Center receives email notification:
  "Votre virement de 340€ a été effectué"
  ↓
  Center downloads invoices for accounting
```

### 9.4 Database Schema for Payments

```sql
-- Payment tracking table
CREATE TABLE virement_centre (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_centre INT NOT NULL,
  id_stage INT NOT NULL,
  id_stagiaire INT NOT NULL,

  -- Amounts
  prix_stage_ttc DECIMAL(6,2) NOT NULL COMMENT 'Trainee paid',
  commission_psp DECIMAL(6,2) NOT NULL COMMENT 'PSP keeps',
  montant_virement DECIMAL(6,2) NOT NULL COMMENT 'Center receives',

  -- Payment info
  date_stage_fin DATE NOT NULL COMMENT 'Stage end date (Day 2)',
  date_virement_prevu DATE NOT NULL COMMENT '2nd or 4th Wednesday',
  date_virement_effectue DATE COMMENT 'Actual payment date',
  statut VARCHAR(20) DEFAULT 'pending' COMMENT 'pending, xml_generated, processed, paid',

  -- Banking
  xml_batch_id VARCHAR(50) COMMENT 'XML file reference',
  reference_bancaire VARCHAR(100) COMMENT 'Bank transaction ID',

  -- Invoice
  numero_facture VARCHAR(50) UNIQUE COMMENT 'Invoice number',
  facture_pdf_url VARCHAR(255) COMMENT 'Invoice PDF path',

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (id_centre) REFERENCES centre(id),
  FOREIGN KEY (id_stage) REFERENCES stage(id),
  FOREIGN KEY (id_stagiaire) REFERENCES stagiaire(id),

  INDEX idx_date_virement_prevu (date_virement_prevu),
  INDEX idx_statut (statut),
  INDEX idx_centre (id_centre)
);
```

### 9.5 Key Implementation Files

**Files MISSING** (needed for payment system):
- ❌ `/simpligestion/virement_sepa_centres_v2.php` - Main payment interface
- ❌ Backend logic to calculate payment schedule
- ❌ XML generation script (SEPA ISO 20022 format)
- ❌ Invoice PDF generation for centers

**What This Means**:
- ❌ Cannot process automated payments to centers
- ❌ Must do manual SEPA transfers via bank website
- ❌ Must manually track which centers have been paid
- ❌ Must manually generate invoices

**Workaround** (manual process):
1. Export paid bookings from database (SQL query)
2. Calculate amounts manually (Excel)
3. Create SEPA XML manually or use bank's manual transfer interface
4. Track payments in spreadsheet
5. Generate invoices manually (Word/PDF)

---

## 10. EMAIL/SMS AUTOMATION

### 10.1 Automated Trainee Communications

**Email 1: "Finalisez votre inscription"**
- **Trigger**: Trainee fills form but doesn't pay
- **Status**: Prospect (not confirmed)
- **Timing**: 2 emails sent over 3 days
- **Purpose**: Remind to complete payment
- **Content**: Link to resume booking + payment

**Email 2: Confirmation d'inscription**
- **Trigger**: Payment received
- **Timing**: Immediate (within 1 minute)
- **Content**:
  - Booking confirmation
  - Stage details (date, location, trainers)
  - Espace Stagiaire login credentials
  - Link: https://www.prostagespermis.fr/es/loginv2.php
  - Booking reference
  - What to bring (ID, license)

**SMS 1: Confirmation d'inscription**
- **Trigger**: Payment received
- **Timing**: Immediate (within 1 minute)
- **Content**: "Confirmation stage [DATE] à [VILLE]. Login: [REFERENCE]. Consultez votre email."

**Email 3: "Dossier incomplet"**
- **Trigger**: Missing documents on Espace Stagiaire
- **Timing**: J-7 and J-3 (7 days before, 3 days before)
- **Content**:
  - List of missing documents
  - Link to Espace Stagiaire
  - Warning: Complete dossier required to attend stage

**Email 4: "Soyez à l'heure"**
- **Trigger**: J-1 (day before stage)
- **Timing**: 18h00 day before
- **Content**:
  - Reminder to be on time (9h00 sharp)
  - Address + Google Maps link
  - What to bring
  - No lateness accepted

**Email 5: Transfert de date**
- **Trigger**: Trainee changes stage date on Espace Stagiaire
- **Timing**: Immediate
- **Content**:
  - Old date cancelled
  - New date confirmed
  - Updated booking details

**Email 6: Annulation**
- **Trigger**: Center or trainee cancels booking
- **Timing**: Immediate
- **Content**:
  - Cancellation confirmation
  - Refund information (if applicable)
  - Contact info for questions

**Email 7: Demande de remboursement**
- **Trigger**: Trainee requests refund on Espace Stagiaire
- **Timing**: Immediate
- **Content**:
  - Refund request received
  - Processing time (5-10 business days)
  - Amount to be refunded

**Email 8: Remboursement effectué**
- **Trigger**: Admin processes refund in SimpliGestion
- **Timing**: When refund executed
- **Content**:
  - Refund confirmation
  - Amount refunded
  - Bank account/card used
  - Timeline: 5-10 days to appear on statement

### 10.2 Automated Center Communications

**Email 1: Nouvelle inscription**
- **Trigger**: Trainee books and pays
- **Timing**: Immediate
- **Recipient**: Center (email on file)
- **Content**:
  - New booking notification
  - Trainee name
  - Stage date
  - Link to view trainee on Espace Partenaire

**Email 2: Transfert de stagiaire**
- **Trigger**: Trainee changes date
- **Timing**: Immediate
- **Recipients**: Old center + new center
- **Content**:
  - Transfer notification
  - Trainee details
  - Old date vs new date

**Email 3: Annulation de stagiaire**
- **Trigger**: Trainee or center cancels
- **Timing**: Immediate
- **Content**:
  - Cancellation notification
  - Trainee name
  - Reason (if provided)

### 10.3 Email/SMS Implementation

**Email System**:
- **Library**: PHPMailer (found in `prostages/includes/class.phpmailer.php`)
- **SMTP**: Likely using PSP's email server
- **Templates**: HTML emails with PSP branding

**SMS System**:
- **Not found in code**: Likely uses external SMS API
- **Providers**: OVH SMS API, Twilio, or similar

**Automation Logic**:
- **Cron jobs**: Scheduled tasks for J-7, J-3, J-1 reminders
- **Triggers**: Database hooks or application-level events

**Files MISSING**:
- ❌ Email template files
- ❌ SMS sending logic
- ❌ Cron job scripts
- ❌ Email/SMS configuration

---

## 11. NEXT STEPS

### 11.1 Immediate Actions Required

**Priority 1: Obtain Missing PHP Files**

**Critical Files Needed**:
```
1. Espace Partenaire (/ep/ folder)
   - accueil3.php
   - stagiaires_mc25.php
   - lieux.php
   - formateurs.php
   - factures_mc24.php
   - compte.php

2. Espace Stagiaire (/es/ folder)
   - loginv2.php
   - stagev3.php
   - profil/*.php
   - changement_avis_v3.php
   - factures.php

3. SimpliGestion (/simpligestion/ folder)
   - inscriptions3.php
   - centres.php
   - virement_sepa_centres_v2.php
   - vente_psp.php
   - kpi.php
```

**How to Obtain**:
- **Option A**: Access ProStagesPermis.fr production server (FTP/SSH)
- **Option B**: Contact whoever provided the `prostages` folder originally
- **Option C**: Rebuild from scratch (significant development effort)

---

**Priority 2: Security Cleanup**

```bash
# Delete malware immediately
rm /Users/yakeen/Desktop/TWELVY_local_php/prostages/evaluations/gep.php
rm /Users/yakeen/Desktop/TWELVY_local_php/prostages/evaluations/html/_.configurations.php

# Scan for additional malware
grep -r "eval(base64_decode" /Users/yakeen/Desktop/TWELVY_local_php/prostages/
grep -r "create_function" /Users/yakeen/Desktop/TWELVY_local_php/prostages/
```

---

**Priority 3: Database Setup**

```bash
# 1. Export production database (if accessible)
mysqldump -u khapmaitpsp -p khapmaitpsp > psp_production.sql

# 2. Create local database
mysql -u root -p
CREATE DATABASE twelvy_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 3. Import data
mysql -u root -p twelvy_local < psp_production.sql

# 4. Update connection files
# Edit: prostages/includes/config.php
# Edit: php/stages.php
```

---

**Priority 4: Local PHP Environment**

```bash
# Option A: MAMP (macOS)
Download: https://www.mamp.info/en/downloads/
Install and point to: /Users/yakeen/Desktop/TWELVY_local_php/

# Option B: Laravel Valet (macOS, recommended)
brew install php@8.1
composer global require laravel/valet
valet install
cd /Users/yakeen/Desktop/TWELVY_local_php/
valet link twelvy-local
# Access at: http://twelvy-local.test

# Option C: Docker
docker-compose up
# Use docker-compose.yml with PHP + MySQL + phpMyAdmin
```

---

### 11.2 Development Roadmap

**Phase 1: Analysis & Setup (COMPLETE ✅)**
- ✅ Analyze prostages folder
- ✅ Identify spaces and files
- ✅ Document business model
- ✅ Map database structure
- ✅ Understand workflows

**Phase 2: File Acquisition (CURRENT STEP)**
- ⚠️ Obtain missing `/ep/` files
- ⚠️ Obtain missing `/es/` files (full version)
- ⚠️ Obtain missing `/simpligestion/` files (full version)
- ⚠️ Verify file completeness

**Phase 3: Local Integration (NEXT)**
- Setup local PHP environment
- Configure database connections
- Test Espace Partenaire locally
- Test Espace Stagiaire locally
- Test SimpliGestion locally
- Fix bugs and compatibility issues

**Phase 4: Modernization (FUTURE)**
- Migrate from `mysql_*` to PDO
- Implement prepared statements
- Add input validation
- Add output escaping
- Implement CSRF protection
- Update to PHP 8.x

**Phase 5: Production Migration (FINAL)**
- Deploy to twelvy.net subdirectories
- Configure production database
- Test all interfaces on production
- Redirect prostagespermis.fr → twelvy.net
- Monitor and fix issues

---

### 11.3 Decision Matrix

**Scenario A: Files Available Within Days**
- ✅ **Best Case**: Full system operational in 1-2 weeks
- **Action**: Proceed with Phase 3 (Local Integration)
- **Timeline**: 2 weeks development + 1 week testing = 3 weeks to production

**Scenario B: Files Available Within Weeks**
- ⚠️ **Delayed Case**: Launch public website only, build portals later
- **Action**: Deploy TWELVY public site, manual backend operations
- **Timeline**: Launch now, add portals in 4-6 weeks

**Scenario C: Files Not Available**
- ❌ **Worst Case**: Must rebuild from scratch
- **Action**: Rebuild Espace Partenaire + SimpliGestion in Next.js
- **Timeline**: 3-6 months development
- **Cost**: Significant development effort

---

### 11.4 Recommended Path Forward

**Step 1: Immediate (Today)**
```
1. Delete malware files
2. Contact whoever provided prostages folder
3. Request missing /ep/, /es/, /simpligestion/ folders
4. Set up local PHP environment (MAMP/Valet)
```

**Step 2: Short-term (This Week)**
```
1. If files obtained → Proceed with local integration
2. If files delayed → Deploy public website only
3. Set up local database replica
4. Test existing partial code (document upload, evaluation)
```

**Step 3: Medium-term (Next 2-4 Weeks)**
```
1. Complete local testing of all interfaces
2. Fix compatibility issues
3. Update database connections
4. Test payment workflows
5. Verify email/SMS automation
```

**Step 4: Long-term (Next 1-2 Months)**
```
1. Deploy to production (twelvy.net)
2. Migrate data from prostagespermis.fr
3. Configure production email/SMS
4. Set up payment gateway (Stripe + Ediweb)
5. Go live with full system
```

---

## 12. CONCLUSION

### 12.1 Current State Summary

**✅ What We Have:**
- Complete public booking website (TWELVY)
- Complete PHP API for frontend
- Complete Espace Formateur (not needed)
- Partial Espace Stagiaire (document upload + evaluation)
- Partial SimpliGestion (backend operations only)

**❌ What We're Missing:**
- Complete Espace Partenaire (0% - critical)
- Complete Espace Stagiaire (70% missing - important)
- Complete SimpliGestion (80% missing - critical)

**⚠️ Impact:**
- **Can launch public website**: Yes ✅
- **Can operate platform fully**: No ❌
- **Manual workaround possible**: Yes, but high overhead ⚠️

### 12.2 Key Insights

1. **ProStagesPermis is a sophisticated platform** with 4 distinct interfaces working together
2. **Commission-based model** requires careful payment tracking and automation
3. **Twice-monthly SEPA payments** are critical for center satisfaction
4. **Trainee autonomy** reduces support overhead (automated date changes, refunds, document uploads)
5. **Missing files are blockers** for full platform operation

### 12.3 Risk Assessment

**High Risk**:
- ❌ Cannot launch without Espace Partenaire (centers can't add stages)
- ❌ Cannot scale without SimpliGestion (manual payment processing doesn't scale)

**Medium Risk**:
- ⚠️ Can launch with partial Espace Stagiaire (document upload works, but no portal)
- ⚠️ Can launch with manual workflows (high operational overhead)

**Low Risk**:
- ✅ Public website is production-ready
- ✅ Database structure is well-understood
- ✅ Payment flow is documented

### 12.4 Final Recommendation

**Obtain the missing PHP files ASAP.** Without them:
- You have a beautiful booking website
- But not a functional platform business

**Alternative path**: If files unavailable, rebuild Espace Partenaire and SimpliGestion in Next.js (3-6 months).

---

**END OF DOCUMENTATION**

*Last Updated: February 6, 2025*
*Status: Awaiting missing PHP files for /ep/, /es/, /simpligestion/*
*Next Action: Contact file provider or access production server*
