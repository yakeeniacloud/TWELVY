# TWELVY / PROSTAGESPERMIS — MIGRATION & PHP FILE INVENTORY

**Last Updated**: February 2025
**Status**: ALL SOURCE FILES OBTAINED — Ready for Local Environment Setup

---

## 1. MIGRATION OBJECTIVE

Migrate the TWELVY Next.js website (currently on www.twelvy.net) to www.prostagespermis.fr while:
- **Replacing** the frontend with TWELVY's modern Next.js interface
- **Integrating** the legacy PHP backend (3 portals + shared infrastructure)
- **Connecting** to prostagespermis.fr's existing MySQL database
- **Maintaining** zero downtime (12+ year SEO ranking must be preserved)
- **Cohabiting** Next.js (public site) with legacy PHP (portals) on the same domain

---

## 2. COMPLETE SYSTEM ARCHITECTURE

### The 6 Parts of ProStagesPermis

The full PSP platform is made up of 6 interconnected parts:

```
1. PUBLIC WEBSITE          → Where customers search, book, and pay
2. BACKEND INFRASTRUCTURE  → Database, payments, SMS, PDF, provider APIs
3. ESPACE PARTENAIRE       → Where training centers manage stages and trainees
4. ESPACE STAGIAIRE        → Where trainees manage their booking and documents
5. SIMPLIGESTION           → Where PSP admin manages everything and processes payments
6. ESPACE FORMATEUR        → Where trainers manage their schedule (NOT NEEDED)
```

### Data Flow

```
[TRAINEE] → [PUBLIC WEBSITE] → payment → [PSP DATABASE]
                                              ↓
                         ┌──────────────┬──────────────┬──────────────┐
                    [ESPACE         [ESPACE        [SIMPLIGESTION]
                     STAGIAIRE]      PARTENAIRE]
                         ↓              ↓              ↓
                    Upload docs    View trainees    Process SEPA
                    Change date    Manage stages    Manage bookings
                    Get invoice    Track payments   KPI & analytics
```

### Target Hybrid Architecture

```
www.prostagespermis.fr
    ├── /* (public pages)           → Next.js on Vercel
    ├── /es/* (espace stagiaire)    → Legacy PHP on OVH
    ├── /ep/* (espace partenaire)   → Legacy PHP on OVH
    ├── /simpligestion/*            → Legacy PHP on OVH
    └── /callback/* (payment IPN)   → Legacy PHP on OVH
```

---

## 3. PHP FILE INVENTORY — COMPLETE SOURCE FILES

We received **5 folders** of PHP source files across 3 delivery batches. Here is what each one contains.

### 3.1 Folder 1: Prostagepermis (125 PHP files)

**Espace Formateur (Trainer Portal) — COMPLETE (100+ files) — NOT NEEDED**
- `login.php`, `planning.php`, `stages.php`, `documents.php`, `virements.php`, etc.
- Full trainer interface: login, scheduling, invoicing, evaluations, document management
- 25 AJAX handlers for various operations
- This space is irrelevant — trainers are managed by centers, not by PSP

**Espace Stagiaire — 3 files (supplemented by www_3/es/)**

| File | What it does |
|------|-------------|
| `charge_document_stagiaire.php` | Standalone page where the trainee uploads required documents (permis, 48N letter, etc.) via a secure URL sent by email — no login needed |
| `evaluations/html/formulaire_stagiaire_v1.php` | Post-stage satisfaction survey (6 steps): rate BAFM trainer, psychologist, training room, overall experience |
| `ajax_monstage_missing_documents2.php` | Background script that checks which documents are still missing for a trainee |

**SimpliGestion — 6 AJAX scripts (supplemented by www_2/simpligestion/)**

| File | What it does |
|------|-------------|
| `ajax_update_presence.php` | Marks a trainee as present/absent/refused/excluded |
| `ajax_update_retard.php` | Records exact lateness time (HH:MM) |
| `ajax_update_piece_manquante.php` | Updates the verified documents checklist |
| `ajax_upload_document_stagiaire.php` | Admin uploads a document on behalf of a trainee |
| `ajax_delete_document.php` | Deletes an uploaded file |
| `ajax_session.php` | Generic session variable setter |

**Security Issues Found:**
- 2 malware files detected (`evaluations/gep.php` and `evaluations/html/_.configurations.php`) — must be deleted
- Uses deprecated `mysql_*` functions
- SQL injection risks, no CSRF protection, hardcoded credentials

---

### 3.2 Folder 2: PSP 2 (2,125 PHP files)

This folder contains the **shared infrastructure layer** — the tools, libraries, and connections that all 3 portals depend on. Every page in Espace Partenaire, Espace Stagiaire, and SimpliGestion starts by including files from this folder.

| Folder | Files | What it provides | Used by |
|--------|-------|-----------------|---------|
| `connections/` | 7 | Database connections for LOCAL, SANDBOX, and PRODUCTION MySQL environments | All portals + public site |
| `common_bootstrap/` | 44 | Main bootstrap: session management, multi-site routing (20+ domains), mobile detection | All portals + public site |
| `common_bootstrap/functions.php` | 1 (1600+ lines) | Utility functions: encryption, French dates, geographic data, blacklist | All portals + public site |
| `common_bootstrap_new/` | 28 | Modern bootstrap with Bootstrap UI, autocomplete, FAQ, commission calculation | All portals + public site |
| `common/` | 30 | Legacy shared library: Google Maps wrapper, stage display, form generators | All portals + public site |
| `common2/` | 14 | Intermediate variant with feed export | Subset of portals |
| `payline/` | 9+ | Complete Payline payment gateway SDK (v1.2.2): web payments, 3D Secure, refunds | Public site + SimpliGestion |
| `soap/` | 9 | 7 training provider integrations (ACCA, Allopermis, CER-Bobillot, Autoclub x2, RPPC, Securoute) | Triggered after payment |
| `smsenvoi/` | 3 | SMS sending library (SMSENVOI.com API) | Email/SMS automation |
| `html2pdf_v4.02/` + `v4.03/` | ~100 | HTML-to-PDF conversion (TCPDF backend) for attestations, invoices, certificates | All portals |
| `geoloc/` + `ip2location/` | 9 | MaxMind GeoIP + IP2Location for IP-based geolocation | Public site |
| `blog-psp/` | 927 | Complete WordPress blog installation | Blog |
| Other (`callback/`, `mdp/`, `htpasswd/`, etc.) | ~20 | Access control, callbacks, debug, CGI scripts | Various |

---

### 3.3 Folder 3: www_3 — Espace Partenaire + Espace Stagiaire + Public Site Components

**Location**: `/Volumes/Crucial X9/PROSTAGES/www_3/`

#### 3.3.1 Espace Partenaire (`www_3/ep/`) — ~250 PHP files — COMPLETE

The full training center management portal. Every file that was previously listed as missing is now found.

**Core Pages:**
| File | Purpose |
|------|---------|
| `index.php` | Login page (redirects based on session) |
| `accueil3.php` | Main stage management dashboard (add, edit, view stages, trainee lists) |
| `accueilmc24.php` / `accueilmc25.php` / `accueilmc26.php` | Version variants of the dashboard |
| `stagiaires_mc25.php` | Trainee list: view info, documents, transfer, cancel bookings |
| `lieux.php` | Venue management: add/edit training locations, addresses, agreement numbers (R-format validation) |
| `formateurs.php` | Trainer management: add/edit/delete trainers for stage assignment |
| `factures_mc24.php` | Payment tracking: view SEPA transfers from PSP, download invoices |
| `compte.php` | Account settings: company info, address, TVA, IBAN, password |
| `stages.php` / `stages2024.php` | Stage listing and management (multiple versions) |
| `virements.php` | View all transfers/payments received from PSP |
| `statistiques.php` | Center performance statistics |

**AJAX Handlers (100+ files):**
- `ajax_ajout_stage.php` — Add new stage
- `ajax_annule_stagiaire.php` — Cancel a trainee's booking
- `ajax_update_visibilite.php` — Toggle stage visibility on public site
- `ajax_update_prix.php` / `ajax_update_prix_new.php` — Update stage pricing
- `ajax_update_places.php` — Update available seats
- `ajax_remboursement.php` — Process refund
- `ajax_attestations.php` — Generate stage attestations
- `ajax_boost_stage.php` — Boost stage visibility
- `ajax_commission2024_process.php` — Commission processing (2024 model)
- `ajax_virement_sepa_animateurs.php` — SEPA transfers for trainers
- `ajax_mails_confirmation_stage.php` — Send stage confirmation emails
- `ajax_send_sms.php` / `ajax_send_sms2.php` — Send SMS to trainees
- `ajax_zip_stage.php` — ZIP download of all stage documents

**Popups/Modals (30+ files):**
- `popup_ajouter_stage2025.php` — Add stage dialog (2025 version)
- `popup_transferer_inscription.php` — Transfer booking popup
- `popup_annulation.php` — Cancellation dialog
- `popup_valider_paiement.php` — Validate payment popup
- `popup_modifier_lieu.php` — Edit venue popup
- `popup_simulateur_renversement2024.php` — Revenue simulator

**Additional Features:**
- `ants_transmissions.php` / `ants_dossiers.php` — ANTS (French vehicle registration) integration
- `feuille_emargement.php` — Attendance sheet generation
- `feuille_prefecture.php` — Prefecture form generation
- `feuille_synthese.php` — Synthesis document generation
- `attestation_stage.php` — Stage certificate generation
- `calendar.php` — Calendar view of stages
- `desistements.php` — Withdrawal management
- `bilan-annuel.php` — Annual report
- `Sepa_credit_XML_Transfer_initation.class.php` — SEPA XML generation class

**Includes (15 files):**
- `includes/header.php`, `footer.php`, `topbar.php`, `sidebar_aide.php`, `sidebar_compte.php`
- `includes/search_bar.php`, `search_bar_home.php`, `search_bar_left.php`
- `includes/admin_options/popup_info_centre.php` — Admin center info popup

---

#### 3.3.2 Espace Stagiaire (`www_3/es/`) — ~130 PHP files — COMPLETE

The full trainee portal. Every file that was previously listed as missing is now found.

**Core Pages:**
| File | Purpose |
|------|---------|
| `loginv2.php` | Trainee login via ID + MD5 key (sent by email after booking). Sets session and redirects. |
| `stagev3.php` | "Mon Stage" dashboard: stage dates, venue, price, program, point recovery timeline |
| `profil/situation.php` | Case selection: voluntary, mandatory probation (48N), prosecution alternative, court order |
| `profil/permis.php` | License info: permit number, date obtained, issuing prefecture |
| `profil/documents.php` | Full document management within portal (upload, view, delete documents) |
| `changement_avis_v3.php` | Date change or refund request (14-day window). Triggers Payline refund + email notifications. |
| `factures.php` | Download booking invoice as PDF |
| `inscriptionv2.php` | Registration page within portal (with 3D Secure variant) |
| `documents.php` | Alternative document management view |
| `donnees_personnelles.php` | Personal data management |
| `coordonnees_bancaires.php` | Bank details management |

**AJAX Handlers (40+ files):**
- `ajax_functions.php` / `ajax_functionsv2.php` — Core AJAX handler with multiple actions
- `ajax_facture_stagiaire.php` — Generate trainee invoice
- `ajax_upload.php` / `ajax_upload_document_stagiaire.php` — Document uploads
- `ajax_delete_document.php` — Document deletion
- `ajax_stage_cb_3ds.php` — 3D Secure card payment
- `ajax_update_presence.php` / `ajax_update_retard.php` — Attendance tracking
- `ajax_animateur_postule.php` / `ajax_confirme_animation.php` — Trainer application system
- `ajax_enregistre_infos_perso.php` — Save personal info
- `ajax_enregistre_rib.php` — Save bank details

**Profile System (2 versions):**
- `profil/` — Current version with `situation.php`, `permis.php`, `documents.php`, `dossier.php`, `informations.php`
- `oldprofil/` — Legacy version (kept for backward compatibility)
- `profil/includes/` — Profile-specific includes (header, footer, topbar, doc_item, complete_folder_message)
- `profil/server/ajax.php` — Profile AJAX handler

**Upsells Module:**
- `upsells/formations.php` — Additional training offers
- `upsells/twelvy_application.php` — Twelvy app upsell
- `upsells/order_bump.php` — Order bump offers
- `upsells/cancel_order.php` / `cancel_subscription.php` — Cancellation handling

**Additional Features:**
- `stages.php` / `stages2.php` — Stage browsing within portal
- `planning.php` — Planning view
- `coanimateurs.php` — Co-animator management
- `offre_stagesv2.php` — Stage offers
- `messages.php` / `messagesv2.php` — Internal messaging
- `telechargement_dossier_stage.php` — Download complete stage dossier
- `attestation_stagiaire.php` — Trainee attestation

**Includes (30 files):**
- `includes/config.php` — ES configuration
- `includes/espace_stagiaire.php` / `gestion_stagiaires.php` — Core ES logic
- `includes/headerv2.php`, `footerv2.php`, `topbarv2.php` — Layout
- `includes/nav_leftv2.php` — Left navigation
- `includes/liste_stages.php` / `stage_en_cours.php` / `stages_pourvoir.php` — Stage listings
- `includes/modal_facture.php` / `modal_pieces_manquantes.php` — Modals
- `includes/n8n/init.php` — N8N automation integration

---

### 3.4 Folder 4: www_2 — SimpliGestion + Email Automation + SEPA + Supporting Systems

**Location**: `/Volumes/Crucial X9/PROSTAGES/www_2/`

#### 3.4.1 SimpliGestion (`www_2/simpligestion/`) — 534 PHP files — COMPLETE

The full admin management portal. Every file that was previously listed as missing is now found.

**Core Pages:**
| File | Purpose |
|------|---------|
| `index.php` | Login/landing page |
| `inscriptions3.php` | Main booking dashboard: all daily bookings, trainee dossiers, transfer/cancel/refund actions |
| `centres.php` / `centres_m3.php` | Center directory: all partner centers, contact info, portal access |
| `virement_sepa_centres_v2.php` | SEPA payment processing: verification table, tabs (VA/VCE/SV/CE/SB), XML generation |
| `vente_psp.php` / `vente_psp_2.php` | Accounting exports for the accountant |
| `kpi.php` | KPI analytics dashboard |
| `stages.php` / `stages_mc25.php` | Stage management |
| `stagiairesv2.php` | Trainee management |
| `formateurs.php` | Trainer management |
| `lieux.php` | Venue management |
| `virements.php` | Transfer overview |

**KPI & Analytics (30+ files in `kpi/`):**
- `kpi_centre.php` / `kpi_ville_2024.php` / `kpi_department_2024.php` — Analytics by center/city/department
- `kpi_dossiers_complets.php` — Completed dossier tracking
- `kpi/ajax_details_kpi_centre.php` — Center KPI details
- `kpi/ajax_kpi_ville_2024.php` — City KPI data
- `kpi/ajax_pricing_tracking.php` — Pricing analytics
- `kpi/ajax_renta_kpi_ville.php` — Revenue per city
- `kpi/export_csv_file.php` — CSV export

**Accounting Module (`compta/` — 20+ files):**
- `compta/load_vente.php` / `load_vente_export.php` — Sales data
- `compta/load_achat.php` / `load_achat_export.php` — Purchases data
- `compta/load_avoirs.php` / `load_avoirs_export.php` — Credit notes
- `compta/load_batch.php` / `load_batch_export.php` — Batch processing
- `compta/load_bilan_psp.php` — PSP balance sheet
- `compta/load_renta.php` — Revenue analysis
- `compta/load_kpi_compta_psp_jourv2.php` / `mensuelv2.php` / `semainev2.php` — Daily/monthly/weekly KPI
- `compta_vente_stage_jourv2.php` / `compta_vente_stage_moisv2.php` — Stage sales by day/month

**SEPA/Transfer Management (30+ files):**
- `virement_sepa_centres.php` / `virement_sepa_centres_v2.php` — SEPA center transfers
- `virement_sepa_animateurs.php` — SEPA trainer transfers
- `virement_centres_effectues.php` — Completed transfers view
- `ajax_sepa_centres.php` / `ajax_virement_sepa_centres.php` — SEPA AJAX handlers
- `ajax_sepa_download_excel.php` — Excel export
- `ajax_sepa_remboursement_stagiaires.php` — Trainee refund SEPA
- `upload_sepa.php` / `upload_sepa_centres.php` / `upload_sepa_remboursement_stagiaire.php` — SEPA file uploads
- `Sepa_credit_XML_Transfer_initation.class.php` — SEPA XML generation

**Commission Management:**
- `commission2024.php` / `commission/index.php` — Commission management UI
- `ajax_commission2024_process.php` / `add.php` / `update.php` — Commission CRUD
- `mc24/commission/` — MC24 commission module
- `accord_centre_commission2024.php` — Center commission agreements

**Funnel/Upsell Management (15+ files):**
- `funnel_appli_twelvy.php` — Twelvy app funnel
- `funnel_carte_radars.php` — Radar map funnel
- `funnel_formation_*.php` — Training upsells
- `funnel_paiement_amende.php` — Fine payment funnel
- `parametre_upsell.php` — Upsell parameters
- `order_upsell.php` / `order_upsell_bait.php` / `order_upsell_down_sell.php` — Order management

**Transfer Module (`transfert/`):**
- `transfert/ajax_find_stage.php` / `ajax_find_stagiaire.php` — Search for stage/trainee
- `transfert/ajax_transfert.php` — Execute transfer
- `transfert/complement_prix.php` — Price difference handling

**Guarantee Module (`guarantee/`):**
- `guarantee/index.php` — Guarantee management
- `guarantee/scripts/ajax_save_guarantee_activation.php` — Toggle guarantee
- `guarantee/scripts/ajax_save_guarantee_price.php` — Set guarantee price

**Email Management:**
- `emails_view.php` / `emails_envois.php` / `emails_smtp.php` — Email logs and management
- `renvoie_email.php` — Resend emails
- `emails/resend_email.php` — Email resending with student list

**Additional Features:**
- `cas_force_majeure.php` — Force majeure handling
- `listing_demandes_remboursements.php` — Refund requests listing
- `listing_stagiaires_bloques.php` — Blocked trainees
- `notifications.php` — Notification management
- `temoignages.php` — Testimonial management
- `telepoint.php` — Telepoint integration (point balance checking)
- `timely.php` — Timely scheduling integration
- `algo_prix.php` / `algo/` — Pricing algorithm management
- `prix_plancher.php` — Floor pricing
- `pricing_tracking.php` — Price tracking
- `suivi_annulation_centres.php` — Center cancellation tracking
- `departement_rayon.php` / `villes_referentes.php` — Geographic management
- `admin_options/` — Admin configuration panel

---

#### 3.4.2 Email/SMS Automation (`www_2/mails_v3/`) — ~90 template files — COMPLETE

All 10 previously missing email automations are now found, plus many more.

**Booking Confirmation:**
| File | Purpose |
|------|---------|
| `mail_inscription.php` | Confirmation email to trainee after payment (stage details, ES login links, schedule) |
| `mail_inscription_centre.php` | Notification to center of new booking |

**Transfer/Change Notifications:**
| File | Purpose |
|------|---------|
| `mail_transfert_stagiaire.php` | Notify trainee of date transfer |
| `mail_transfert_centre.php` | Notify center of trainee transfer |
| `mail_transfert_lieu_stagiaire.php` | Notify trainee of venue change |
| `mail_transfert_lieu_centre.php` | Notify center of venue change |
| `mail_changement_horaire_stagiaire.php` | Schedule change notification to trainee |
| `mail_changement_horaire_centre.php` | Schedule change notification to center |

**Cancellation & Refund:**
| File | Purpose |
|------|---------|
| `mail_annulation_stagiaire.php` | Cancellation confirmation to trainee |
| `mail_annulation_centre.php` / `mail_annulation_centre2.php` | Cancellation notification to center |
| `mail_demande_remboursement_stagiaire.php` | Refund request confirmation |
| `mail_remboursement_effectue.php` | Refund processed confirmation |
| `mail_centre_cas_force_majeure_cancel.php` / `_waiting.php` | Force majeure handling |

**Document Reminders (14-step sequence!):**
| File | Purpose |
|------|---------|
| `mail_relance_docs_15mn.php` | 15 minutes after booking |
| `mail_relance_docs_j1.php` through `mail_relance_docs_j60.php` | Day 1, 2, 4, 6, 8, 10, 15, 20, 25, 30, 45, 60 |
| `mail_relance_docs_jmoins1.php` | Day before stage |
| `mail_relance_stagiaire_dossier_incomplet1/2/3.php` | Escalating "dossier incomplet" emails |
| `mail_stagiaire_dossier_complet.php` / `_centre.php` | Dossier complete confirmation |

**Pre-Stage Reminders:**
| File | Purpose |
|------|---------|
| `mail_avant_stage_jmoins1.php` | Day-before reminder with venue and arrival time |
| `mail_avant_stage_jmoins4.php` | 4-day reminder |

**Post-Stage Sequence:**
| File | Purpose |
|------|---------|
| `mail_post_stage_attestation.php` | Send attestation after stage |
| `mail_post_stage_avis_google.php` | Request Google review |
| `mail_post_stage_j8/j15/j30/j60/j90.php` | Follow-up sequence over 90 days |
| `mail_post_stage_temoignage.php` | Request testimonial |
| `mail_post_stage_remboursement_amende.php` | Fine refund info |

**Prospect/Marketing:**
| File | Purpose |
|------|---------|
| `mail_relance_prospect.php` | "Finalisez votre inscription" reminder |
| `mail_echec_paiement.php` | Failed payment notification |
| `mail_info_stagiaire1.php` / `mail_info_stagiaire2.php` | Information emails |

**CB Dispute Handling:**
| File | Purpose |
|------|---------|
| `mail_opposition_cb_injustifiee.php` | Unjustified chargeback |
| `mail_opposition_cb_justifiee.php` | Justified chargeback |
| `mail_opposition_cb_volee.php` | Stolen card chargeback |

**Other:**
- `mail_exclusion_stagiaire.php` — Trainee exclusion
- `mail_retard_stagiaire.php` — Late arrival notice
- `mail_mise_en_attente_stagiaire.php` — Waiting list
- `mail_message_centre.php` / `mail_nouveau_message.php` — Messaging
- `sms_info_stagiaire3.php` — SMS template
- `cron.php` / `cron_plus2jours.php` — Main cron runners
- `functions.php` — Email utility functions
- `class.phpmailer_v3.php` / `SMTP.php` — PHPMailer library

---

#### 3.4.3 Task Scheduler (`www_2/planificateur_tache/`) — ~150 PHP files — COMPLETE

Cron jobs and automated tasks that run the platform in the background.

**Email Automation Crons:**
- `emails/stagiaires/mail_send_daily.php` / `mail_send_minutly.php` — Main email dispatchers
- `emails/stagiaires/mail_avant_stage_jmoins1.php` through `mail_relance_docs_j60.php` — Complete email sequence runners
- `email_paiement_echoue.php` — Failed payment follow-up
- `email_newsletter.php` / `_2jours.php` / `_15jours.php` / `_30jours.php` — Newsletter campaigns
- `cron_relance_prospect.php` — Prospect reminder cron

**Provider Data Sync:**
- `flux_stages.php` / `flux_stages_new.php` — Stage data sync from providers
- `flux_sites.php` — Venue data sync
- `flux_rppc/` — RPPC provider flux (stages, lieux, verification)
- `flux_stages_cer_bobillot.php` / `flux_stages_securoute.php` — Provider-specific sync
- `flux_ac.php` / `flux_acca2.php` — Autoclub/ACCA sync
- `update_stagiaires.php` / `update_stagiaires_actiroute.php` — Trainee data updates

**Pricing Algorithm:**
- `algo.php` / `algo-new.php` — Main pricing algorithm
- `algo/algo_prix.php` / `algo_prix_automatique.php` — Automatic pricing
- `algo_prix_idstages/` — Provider-specific pricing (RPPC variants)
- `cron_algo.php` — Pricing algorithm cron
- `cron_min_prix_golden_villes.php` / `cron_prix_min_adw.php` / `cron_prix_min_psp.php` — Minimum pricing

**KPI & Analytics:**
- `kpi/journalierv2.php` / `mensuelv2.php` / `semainev2.php` — Daily/monthly/weekly KPI aggregation
- `kpi/ranking.php` — Center ranking
- `cron_rentabilite.php` — Revenue calculation
- `cron_taux_remplissage.php` / `_20.php` — Fill rate calculation
- `cron_taux_annulation.php` — Cancellation rate
- `cron_total_paiement.php` — Total payment aggregation

**ANTS Integration:**
- `cron_ants_stagiaire.php` — ANTS trainee data processing

**Monitoring & Maintenance:**
- `cron_check_stage_visibily.php` — Stage visibility check
- `cron_monitoring_idstages.php` — Provider monitoring
- `cron_places_idstages.php` — Available seats sync
- `internal/alert_database.php` / `alert_size.php` — Database alerts
- `cron_gps.php` — GPS coordinate updates
- `cron_harmonisation.php` — Data harmonization

**Invoice Generation:**
- `cron_genere_facture_pour_partenaire.php` — Auto-generate partner invoices
- `synthese_facture.php` — Invoice synthesis

**Newsletter (Mailchimp):**
- `newsletter/mailchimp_functions.php` — Mailchimp API integration
- `newsletter/lib/drewn/MailChimp.php` — Mailchimp library

**Other:**
- `confirmation_formateur.php` / `confirmation_recherche_formateur.php` — Trainer confirmation
- `rappel_agenda.php` — Calendar reminders
- `ekomi_get_avis.php` / `ekomi_send_emails.php` — Ekomi review system
- `telepoint_*.php` — Telepoint integration
- `stages_pap.php` / `stages_papv2.php` — Pages Automobiles Professionnelles
- SEO position tracking: `position_tracking/` with Google scraper and Semrush integration
- Competitor scraping: `portailpointspermis/scrap.php`

---

#### 3.4.4 SEPA Transfers (`www_2/virements/` + `virements2/`) — 9 files each — COMPLETE

Complete SEPA transfer system for batch bank payments:

| File | Purpose |
|------|---------|
| `Sepa_credit_XML_Transfer_initation.class.php` | SEPA XML (ISO 20022) generation class |
| `index.php` | Transfer management interface |
| `ajax_sepa.php` | Generate SEPA XML for batch transfer |
| `ajax_sepa_single.php` | Single SEPA transfer |
| `ajax_confirm_virement.php` | Confirm transfer execution |
| `ajax_email_virement.php` | Send transfer notification email |
| `ajax_getlistestagiaire.php` | Get trainee list for transfer |
| `download.php` | Download SEPA XML file |
| `sql_defines.php` | SQL query definitions |

---

### 3.5 Additional Components in www_3

#### 3.5.1 REST API (`www_3/api/v1/`) — Modern MVC API

A properly structured REST API with JWT authentication:
- `controllers/`: ApiController, AuthController, LieuController, StagesController, StagiairesController
- `core/`: JWT auth, RateLimiter, Router, DB, Config, Logger, AuthMiddleware
- `models/`: RefreshToken
- `transformers/`: DTO transformers for Lieu, Stage, Stagiaire
- `validators/`: Input validators for stage operations
- `routes.php` — API route definitions
- `documentation/index.php` — API documentation

#### 3.5.2 Public Site Includes (`www_3/includes/`) — ~95 files

All shared components for the public-facing website:
- Headers/Footers: `header_v2.php` through `header_v4.php`, `footer_v2.php` through `footer_v5.php`
- Navigation: `nav2.php`, `nav3.php`, `topbar_v2.php` through `topbar_v6.php`
- Search: `search_bar.php`, `search_bar_home.php`, `searchcity.php`
- Maps: `map.php`, `map_ville.php`, `map_with_areas.php`
- Forms: `formulaire_form.php`, `formulaire_inscription_2024.php`, `formulaire_contact.php`
- Engagements: `engagements.php` through `engagements_v4.php`, mobile variants
- Content: `ville_contenu0.php`, `ville_contenu1.php`, `liens_departements.php`, `liens_articles.php`
- Widgets: `promotions.php`, `promotions_widget.php`, `ekomi_widget.php`

#### 3.5.3 Inscription Flow (`www_3/inscription/`) — 4 files

Public booking/registration process:
- `index.php` — Main inscription page
- `admin.php` — Admin booking
- `dossier.php` — Dossier management
- `ident_stagiaire.php` — Trainee identification

#### 3.5.4 Document System (`www_3/document/`) — 10 files

Document upload/download system:
- `ajax/upload.php`, `upload_animator.php`, `upload_ants_pj.php`, `upload_member.php`
- `ajax/delete.php`, `delete_uploaded_ants_pj.php`
- `download/print.php`, `print_mail.php`, `download_attestation_signee.php`

#### 3.5.5 Content Management (`www_3/contenu/`) — CMS

Built-in CMS for managing SEO content:
- `contenu.php` / `index.php` — Content editor
- `ville.php` / `ville2.php` — City-specific content
- TinyMCE editor with Bootstrap plugin
- File manager with upload capabilities

#### 3.5.6 Other www_3 Folders

| Folder | Purpose |
|--------|---------|
| `affilies2/` + `affilies_bootstrap/` | Affiliate/partner program |
| `kpi_ads/` | Advertising KPI tracking |
| `ants/` | ANTS (vehicle registration) integration |
| `lhc_web/` | Live help/chat system |
| `invite/` | Invitation system |
| `flux/` | Data feed exports |
| `adress/` | Address handling |
| `dl/` | Downloads |
| `geo/` | Geolocation |
| `lib/` | Additional libraries |
| `Connections/` | Database connections |
| `bootstrap3-editable/` | Bootstrap editable library |
| `google-api-php-client-2-2-2/` | Google API PHP client |
| `logs/` | Log files |

---

### 3.6 Folder 5: PSP 3 — Backup Payment Code (3 files)

**Location**: `/Volumes/Crucial X9/PROSTAGES/PSP 3/backup code cb/`

Minimal backup of credit card payment code:
- `pbx_repondre_a.php` — Payment server response handler
- `lien_cb.php` — Credit card payment link generator
- `common_fiche_pre_inscription.php` — Pre-inscription form

These are already covered by the Payline SDK in PSP 2.

---

### 3.7 www_2 Supporting Systems

| Folder | Files | Purpose |
|--------|-------|---------|
| `src/` | Custom source code modules (site management, etc.) |
| `vendor/` | Composer PHP dependencies |
| `modules/` | Custom modules |
| `webservices/` + `ws/` | Web service integrations |
| `template/` | Page templates |
| `themes/` | Theme files |
| `ratings/` | Rating/review system |
| `partenariat/` | Partnership management |
| `tools/` | Utility tools |
| `sync/` | Data synchronization scripts |
| `upload/` | Upload handling |
| `scripts/` | Automation scripts (`traitement_email.php`, `library.php`) |
| `optimisationonpage/` | On-page SEO optimization tools |
| `smsenvoi/` | SMS library (may differ from PSP 2 version) |
| `v2/` | Version 2 legacy code |
| `wp/` | WordPress files |
| `test/` | Test files |
| `upgrade/` | Upgrade migration scripts |

---

### 3.8 Database Credentials Found

**Production** (from `connections/stageconnect0.php`):
- Host: `prostagepsp.mysql.db`
- Database: `prostagepsp`
- User: `prostagepsp`
- URL: `https://www.prostagespermis.fr/`

**Sandbox** (staging/testing):
- Host: `ma27831-001.privatesql:35300`
- Database: `sandbox_prostagepsp`
- User: `sandbox_psp`
- URL: `https://sandbox.prostagespermis.fr/`

**Local** (Docker development):
- Host: `prostage-db`
- Database: `prostage`
- User: `root`

**Espace Formateur** (from `prostages/includes/config.php`):
- Host: `khapmaitpsp.mysql.db`
- Database: `khapmaitpsp`
- User: `khapmaitpsp`

---

## 4. WHAT WE HAVE VS WHAT WE NEED — FINAL STATUS

### 4.1 Status Summary

| Component | Previous Status | Current Status | Source |
|-----------|----------------|----------------|--------|
| **Public Website** | COMPLETE | COMPLETE | TWELVY Next.js (live at twelvy.net) |
| **Backend Infrastructure** | COMPLETE | COMPLETE | PSP 2 folder |
| **Espace Partenaire** | 0% — MISSING | **100% — COMPLETE** | `www_3/ep/` (~250 files) |
| **Espace Stagiaire** | ~30% — PARTIAL | **100% — COMPLETE** | `www_3/es/` (~130 files) |
| **SimpliGestion** | ~20% — PARTIAL | **100% — COMPLETE** | `www_2/simpligestion/` (534 files) |
| **Email/SMS Automation** | 0% — MISSING | **100% — COMPLETE** | `www_2/mails_v3/` (~90 files) + `planificateur_tache/` (~150 files) |
| **SEPA Transfers** | MISSING | **100% — COMPLETE** | `www_2/virements/` + `virements2/` (18 files) |
| **Espace Formateur** | 100% — NOT NEEDED | 100% — NOT NEEDED | Folder 1 |
| **REST API** | Not known | **FOUND** | `www_3/api/v1/` (25 files) |
| **Public Site Includes** | Partial | **COMPLETE** | `www_3/includes/` (~95 files) |
| **CMS Content System** | Not known | **FOUND** | `www_3/contenu/` |
| **Cron Jobs/Automation** | MISSING | **100% — COMPLETE** | `www_2/planificateur_tache/` (~150 files) |

### 4.2 Previously Missing → Now Found (Cross-Reference)

Every single file listed as missing in the previous version of this document has been located:

**Espace Partenaire — ALL 7 items found:**
1. ✅ Login page → `www_3/ep/index.php`
2. ✅ `accueil3.php` → `www_3/ep/accueil3.php`
3. ✅ `stagiaires_mc25.php` → `www_3/ep/stagiaires_mc25.php`
4. ✅ `lieux.php` → `www_3/ep/lieux.php`
5. ✅ `formateurs.php` → `www_3/ep/formateurs.php`
6. ✅ `factures_mc24.php` → `www_3/ep/factures_mc24.php`
7. ✅ `compte.php` → `www_3/ep/compte.php`

**Espace Stagiaire — ALL 7 items found:**
1. ✅ `loginv2.php` → `www_3/es/loginv2.php`
2. ✅ `stagev3.php` → `www_3/es/stagev3.php`
3. ✅ `profil/situation.php` → `www_3/es/profil/situation.php`
4. ✅ `profil/permis.php` → `www_3/es/profil/permis.php`
5. ✅ `profil/documents.php` → `www_3/es/profil/documents.php`
6. ✅ `changement_avis_v3.php` → `www_3/es/changement_avis_v3.php`
7. ✅ `factures.php` → `www_3/es/factures.php`

**SimpliGestion — ALL 5 items found:**
1. ✅ `inscriptions3.php` → `www_2/simpligestion/inscriptions3.php`
2. ✅ `centres.php` → `www_2/simpligestion/centres.php`
3. ✅ `virement_sepa_centres_v2.php` → `www_2/simpligestion/virement_sepa_centres_v2.php`
4. ✅ `vente_psp.php` → `www_2/simpligestion/vente_psp.php`
5. ✅ `kpi.php` → `www_2/simpligestion/kpi.php`

**Email/SMS — ALL 10 automations found:**
1. ✅ "Finalisez votre inscription" → `mails_v3/mail_relance_prospect.php`
2. ✅ Confirmation email → `mails_v3/mail_inscription.php`
3. ✅ Transfer email → `mails_v3/mail_transfert_stagiaire.php` + `mail_transfert_centre.php`
4. ✅ Cancellation email → `mails_v3/mail_annulation_stagiaire.php` + `mail_annulation_centre.php`
5. ✅ Refund emails → `mails_v3/mail_demande_remboursement_stagiaire.php` + `mail_remboursement_effectue.php`
6. ✅ "Dossier incomplet" → `mails_v3/mail_relance_stagiaire_dossier_incomplet1/2/3.php` + 14 `mail_relance_docs_j*.php`
7. ✅ "Soyez a l'heure" → `mails_v3/mail_avant_stage_jmoins1.php`
8. ✅ Center: new booking → `mails_v3/mail_inscription_centre.php`
9. ✅ Center: transfer → `mails_v3/mail_transfert_centre.php`
10. ✅ Center: cancellation → `mails_v3/mail_annulation_centre.php`

---

## 5. HOW ALL THE FOLDERS CONNECT

```
                        ┌─────────────────────────┐
                        │    PSP 2 (Folder 2)     │
                        │  Shared Infrastructure  │
                        │  connections/ payline/   │
                        │  common_bootstrap/       │
                        │  soap/ smsenvoi/ html2pdf│
                        └────────┬────────────────┘
                                 │ included by all
           ┌─────────────────────┼─────────────────────┐
           │                     │                      │
    ┌──────▼──────┐      ┌──────▼──────┐      ┌────────▼────────┐
    │  www_3/ep/  │      │  www_3/es/  │      │ www_2/simpli-   │
    │  Espace     │      │  Espace     │      │  gestion/       │
    │  Partenaire │      │  Stagiaire  │      │  SimpliGestion  │
    │  ~250 files │      │  ~130 files │      │  534 files      │
    └─────────────┘      └─────────────┘      └─────────────────┘
                                 │                      │
                          ┌──────▼──────┐      ┌────────▼────────┐
                          │  www_2/     │      │  www_2/         │
                          │  mails_v3/  │      │  virements/     │
                          │  ~90 files  │      │  virements2/    │
                          └─────────────┘      └─────────────────┘
                                 │
                          ┌──────▼──────────────┐
                          │  www_2/             │
                          │  planificateur_     │
                          │  tache/ ~150 files  │
                          └─────────────────────┘

    ┌─────────────────────────────────────────────────────────────┐
    │  www_3/ (shared public components)                          │
    │  includes/ ~95 files  |  api/v1/  |  inscription/          │
    │  document/  |  contenu/  |  dossier/  |  flux/             │
    └─────────────────────────────────────────────────────────────┘

    ┌───────────────┐     ┌───────────────┐
    │  Folder 1:    │     │  PSP 3:       │
    │  Prostages-   │     │  3 backup     │
    │  permis       │     │  payment      │
    │  (Formateur)  │     │  files        │
    └───────────────┘     └───────────────┘
```

---

## 6. MIGRATION STRATEGY

### 6.1 Current Architecture

```
TWELVY (www.twelvy.net)
├── Frontend: Next.js 15 on Vercel
├── Database: MySQL on OVH (neopermis.fr)
├── WordPress Headless: headless.twelvy.net
└── Repository: https://github.com/yakeeniacloud/TWELVY.git

ProStagesPermis (www.prostagespermis.fr)
├── Frontend: Legacy PHP on OVH
├── Database: MySQL on OVH (prostagepsp.mysql.db)
├── 3 Portals: /es/, /ep/, /simpligestion/ (PHP on OVH)
└── Status: LIVE — cannot go offline
```

### 6.2 Target Architecture

```
www.prostagespermis.fr (UNIFIED DOMAIN)
├── /* (public pages)           → Next.js on Vercel
├── /es/* (espace stagiaire)    → PHP on OVH
├── /ep/* (espace partenaire)   → PHP on OVH
├── /simpligestion/*            → PHP on OVH
├── /callback/* (payment IPN)   → PHP on OVH
├── Database: prostagepsp MySQL (OVH) — UNCHANGED
├── WordPress: headless.twelvy.net — UNCHANGED
└── All PHP shared code: deployed on OVH alongside portals
```

### 6.3 Routing Rules

```
ROUTE                    → DESTINATION
─────────────────────────────────────
/es/*                    → OVH (PHP legacy)
/ep/*                    → OVH (PHP legacy)
/simpligestion*          → OVH (PHP legacy)
/callback/*              → OVH (PHP legacy)
Everything else          → Vercel (Next.js)
```

### 6.4 Migration Phases

**Phase 1: Local Environment Setup** ← WE ARE HERE
- Docker compose: PHP + MySQL
- Import production database (anonymized)
- Deploy all PHP folders in correct directory structure:
  - `/home/prostage/connections/` ← PSP 2 connections
  - `/home/prostage/common_bootstrap/` ← PSP 2 bootstrap
  - `/home/prostage/www/ep/` ← www_3/ep
  - `/home/prostage/www/es/` ← www_3/es
  - `/home/prostage/www/simpligestion/` ← www_2/simpligestion
  - `/home/prostage/www/mails_v3/` ← www_2/mails_v3
  - `/home/prostage/www/virements/` ← www_2/virements
  - etc.
- Fix include paths if needed
- Test all portal pages locally

**Phase 2: Security Cleanup**
- Delete malware files (`evaluations/gep.php`, `evaluations/html/_.configurations.php`)
- Audit hardcoded credentials
- Review deprecated `mysql_*` function usage
- Assess SQL injection risks

**Phase 3: Staging**
- Deploy to `staging.prostagespermis.fr`
- Test hybrid routing (Next.js + PHP)
- Verify session/cookie handling across domains
- Test payment flow (Payline test environment)
- Verify all email/SMS automations
- Test SEPA XML generation

**Phase 4: SEO Parity**
- Reproduce all existing URLs in Next.js
- Match title/description/H1/canonical/robots
- Preserve internal linking structure
- Generate sitemap.xml
- Block staging from indexation

**Phase 5: DNS Switch (Blue-Green)**
- Lower TTL 24h before
- Switch DNS at low-traffic time
- Smoke test: homepage, search, booking, payment, login to all 3 portals
- Monitor 24h
- Rollback plan: revert DNS in < 5 minutes

**Phase 6: Stabilization (7-14 days)**
- Monitor Google Search Console
- Fix 404/500 errors
- Verify payment callbacks
- Confirm email automations and cron jobs
- Re-crawl P1 pages at J+7 and J+14

---

## 7. RISK MITIGATION

| Risk | Impact | Mitigation |
|------|--------|-----------|
| Include path mismatches | Portal pages crash | Map all `require`/`include` paths during Phase 1 |
| Database schema mismatch | Portal pages crash | Map TWELVY schema to PSP schema in Phase 1 |
| SEO ranking loss | Traffic drop | Blue-green deployment, low TTL, instant rollback |
| Payment callback failure | Lost transactions | Keep callback on stable legacy domain, test with Payline test env |
| Session breaks across Next.js/PHP | Users get logged out | Share cookies on same domain, test in staging |
| Deprecated PHP functions | Crashes on modern PHP | Test on PHP 7.4 first, migrate to 8.x later |
| Cron jobs not running | Emails/sync stop | Document all cron entries from production, replicate |

---

## 8. DECISION LOG

| Decision | Rationale |
|----------|-----------|
| Keep WordPress at headless.twelvy.net | Simpler, no migration risk |
| Route portals to OVH PHP, public to Vercel | Hybrid cohabitation — minimal changes to legacy |
| All PHP code deploys on OVH | Portals + shared code + email automation all on same server |
| Espace Formateur excluded | Trainers are managed by centers, not PSP |
| Payment callback stays on legacy PHP | Must be stable, server-to-server, no dependency on Next.js |
| PSP 3 files not needed | Already covered by Payline SDK in PSP 2 |

---

## 9. TOTAL FILE COUNT

| Source | PHP Files | Status |
|--------|-----------|--------|
| Folder 1: Prostagepermis | ~125 | Espace Formateur (not needed) + ES/SG fragments |
| Folder 2: PSP 2 | ~2,125 | Shared infrastructure — COMPLETE |
| www_3/ep/ (Espace Partenaire) | ~250 | **COMPLETE** |
| www_3/es/ (Espace Stagiaire) | ~130 | **COMPLETE** |
| www_2/simpligestion/ | ~534 | **COMPLETE** |
| www_2/mails_v3/ | ~90 | **COMPLETE** |
| www_2/planificateur_tache/ | ~150 | **COMPLETE** |
| www_2/virements/ + virements2/ | ~18 | **COMPLETE** |
| www_3/ other (includes, api, etc.) | ~200+ | **COMPLETE** |
| www_2/ other (src, vendor, etc.) | varies | Supporting code |
| PSP 3 | 3 | Backup (not needed) |
| **TOTAL** | **~3,600+** | **ALL OBTAINED** |

---

## 10. NEXT STEPS (PRIORITY ORDER)

1. ~~**Obtain missing PHP files**~~ ✅ DONE — All files obtained across 5 folders
2. **Delete malware** — `evaluations/gep.php` and `evaluations/html/_.configurations.php`
3. **Map directory structure** — Document the exact `/home/prostage/` directory layout expected by include paths
4. **Set up local environment** — Docker with PHP 7.4 + MySQL + correct directory structure
5. **Fix include paths** — Verify all `require_once`/`include` paths resolve correctly
6. **Test portal pages locally** — Verify all 3 portals work with shared dependencies
7. **Set up cron jobs** — Document and replicate all `planificateur_tache/` cron entries
8. **Set up staging** — `staging.prostagespermis.fr` with hybrid routing
9. **SEO inventory** — Crawl current PSP site, document all URLs/balises/maillage
10. **Payment integration** — Test Payline bridge (init → redirect → callback → DB update)
11. **Go/No-Go checklist** — Validate all critical paths before DNS switch

---

**Document Version**: 3.0
**Last Updated**: February 2025
**Previous Versions**:
- 2.0 (February 2025) — Analysis with missing files identified
- 1.0 (November 2025) — Basic DNS migration plan only
**Next Review**: After local environment setup is complete
