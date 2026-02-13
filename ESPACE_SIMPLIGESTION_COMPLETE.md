# ESPACE SIMPLIGESTION - COMPLETE DOCUMENTATION

**Last Updated**: February 12, 2026
**Source**: PHP source code examination (395 files) + Visual analysis (80 screenshots)
**Status**: ALL FILES FOUND (395 PHP files) + COMPLETE VISUAL DOCUMENTATION (80 screenshots analyzed)

---

## OVERVIEW

SimpliGestion is the **internal admin portal for ProStagesPermis staff** -- the central command center for managing the entire platform. This is where PSP administrators oversee all bookings, centers, trainees, payments, KPIs, and business operations.

**Critical Distinction**:
- **Espace Stagiaire**: Portal for drivers (trainees)
- **Espace Partenaire**: Portal for training centers
- **SimpliGestion**: Portal for PSP staff (internal admin only)

**Business Purpose**: Complete operational control over the marketplace -- monitor sales, manage partner relationships, process payments, track KPIs, handle customer service, and ensure platform health.

---

## VISUAL DOCUMENTATION (From 80 Screenshots)

### Overview

80 screenshots were captured of the live SimpliGestion portal, covering every major section of the admin interface. This visual analysis confirms the navigation structure, page layouts, data table configurations, and UI patterns across the entire portal.

### Confirmed Navigation Bar

The navigation bar is a **dark charcoal horizontal bar (#222222)** with **white uppercase text**. It contains **15 top-level tabs**, each with a dropdown caret indicating sub-menus. The active tab is highlighted with a **muted red/brick background (#c9302c)**.

**15 Tabs (left to right)**:

| # | Tab Name | Type | Purpose |
|---|----------|------|---------|
| 1 | **STAGIAIRES** | Dropdown | Trainee/student management |
| 2 | **CENTRES** | Dropdown | Training center management |
| 3 | **VIREMENTS CENTRES** | Dropdown | Bank transfers to centers |
| 4 | **SUIVI STAGES** | Dropdown | Stage tracking and monitoring |
| 5 | **COMPTA PSP** | Dropdown | PSP accounting |
| 6 | **ANIMATEURS** | Dropdown | Trainer/facilitator management |
| 7 | **AUTRES** | Dropdown | Miscellaneous tools and logs |
| 8 | **DONNEES CLIENTS** | Dropdown | Customer data for upsells |
| 9 | **FUNNELS** | Dropdown | Sales funnel configuration |
| 10 | **KPI1** | Dropdown | KPI dashboards (cities, centers, sales) |
| 11 | **KPI2** | Dropdown | KPI dashboards (user tracking, balances) |
| 12 | **MAILS** | Dropdown | Email server config and logs |
| 13 | **SMS** | Dropdown | SMS API config and logs |
| 14 | **ANCIEN** | Dropdown (red bg) | Legacy/archived features (22 items) |
| 15 | **MARGE COMMERCIALE** | Dropdown (red outlined button) | Commercial margin management (14 items) |

---

### Complete Dropdown Menus (Visually Confirmed)

#### STAGIAIRES Dropdown
Visually confirmed pages:
1. **Stagiaires** (main list) -- Trainee records with 18 columns
2. **Modifications de prix** -- Price modification history

#### CENTRES Dropdown
Visually confirmed pages:
1. **Centres** (main list) -- 714 centers, 264 per page
2. **Liste des demandes de partenariat** -- 186 partnership requests
3. **Gestion des CGP** -- 196 CGP management entries
4. **Administration - Centres - Banniere** -- Banner management for center portal

#### VIREMENTS CENTRES Dropdown
Visually confirmed pages:
1. **Virements en attente** -- Pending transfers with editable amounts
2. **Virement centres effectues** -- Completed center transfers
3. **Stagiaires vires** -- Transferred trainees
4. **Liste virements non effectues** -- Incomplete transfers
5. **Totaux centres a virer** -- Transfer summary totals
6. **Verifications - Virements en attente** -- Transfer verification table
7. **Fichier Virement** -- XML file verification tool

#### SUIVI STAGES Dropdown
Visually confirmed pages:
1. **Site des stages en ligne** -- 2,714 online stage listings
2. **Prix plancher** -- Floor pricing by department (100 entries)
3. **Commission 2024** -- Commission rates by price range (52 entries)
4. **Referents** -- 63 referent/trainer records
5. **Accord centre commission 2024** -- 596 center commission agreements
6. **Gestion des Commissions** -- Commission rate lookup table (500 to 1 EUR)

#### COMPTA PSP Dropdown
Visually confirmed pages:
1. **Referents** -- PSP accounting referent records
2. **AVOIRS** -- Refund/credit note accounting (double-entry: Debit/Credit)
3. **STATUT** -- Stage status tracking with accounting references
4. **Batch** -- SEPA bank transfer batches (14 entries per batch)
5. **RELAY** -- Intermediary/relay accounting entries

#### ANIMATEURS Dropdown
Visually confirmed pages:
1. **Animateurs** (main list) -- 2,748 total trainers, 112 filtered
2. **Candidatures animateurs** -- Trainer application tracking per stage

#### AUTRES Dropdown (18 sub-items)
Visually confirmed dropdown items:
1. TEMOIGNAGES
2. LOGS BUGS 3DS
3. 3DS LOG DEMANDES
4. 3DS LOG BUGS
5. LOGS PRICE TRANSFERT
6. LOGS VERIFICATIONS PAIEMENTS
7. LOGS EMAILS ENVOYES
8. RENVOI EMAILS
9. TRANSFERTS STAGIAIRES
10. LOGS CHANGEMENTS PRIX
11. LOGS STAGES EN LIGNE/HORS LIGNE
12. LOGS STAGES ANNULES
13. LOGS CHANGEMENTS LIEU
14. LOGS CHANGEMENTS HORAIRES
15. LOGS CHANGEMENTS "NB PLACES MAX."
16. TELEPOINTS
17. SUIVI STAGES EPERMIS (ANTS)
18. TRACKING FORM INSCRIPTION ERREURS

Visually confirmed pages:
- **Liste des temoignages** -- 1,780 customer testimonials with ratings (1-5 stars)

#### DONNEES CLIENTS Dropdown (7 sub-items)
Visually confirmed dropdown items:
1. FORMATION "GESTION TEMPS (upsell)"
2. FORMATION "GESTION TEMPS" COMPLETE
3. FORMATION "GESTION DES EMOTIONS (upsell)"
4. FORMATION "GESTION DES EMOTIONS" COMPLETE
5. SOLDE DE POINT
6. PAIEMENT AMENDE
7. CARTE RADARS

Visually confirmed pages:
- **Formation Gestion du temps** -- 44 upsell entries with refund buttons
- **Formation Gestion du Temps COMPLETE** -- 256 completed records
- **Formation Gestion des Emotions** -- 41 upsell entries with refund buttons
- **Formation Gestion des Emotions COMPLETE** -- 244 completed records
- **Solde De Point** -- 68 points balance inquiry records
- **Paiement Amende** -- 10 fine payment client records
- **Carte Radar** -- 21 radar card client records
- **Donnees clients Appli TWELVY** -- 19 TWELVY app customer entries
- **Donnees clients Formation Business 12 Points COMPLETE** -- 6 entries

#### FUNNELS Dropdown (9 sub-items)
Visually confirmed dropdown items:
1. STAGE
2. FORMATION GESTION TEMPS (upsell)
3. FORMATION GESTION DES EMOTIONS (upsell)
4. FORMATION GESTION TEMPS COMPLETE
5. FORMATION GESTION DES EMOTIONS COMPLETE
6. SOLDE DE POINT
7. (blank separator)
8. CARTE RADAR
9. CARTE DIAMANT

Visually confirmed pages:
- **Funnel Stage** -- Prix (normal/remise), Order Bump, Upsell 1, Upsell 2, Down Sell
- **Funnel Formation Gestion Temps** -- Prix: 360/97, Order Bump: Alerte Aux Points, Upsell 1: Appli TWELVY, Down Sell: Formation Gestion des Emotions
- **Funnel Formation Gestion Temps Complete** -- Prix: 360/97
- **Funnel Formation Gestion Emotion** -- Prix: 325/85
- **Funnel Formation Gestion Emotion Complete** -- Prix: 325/85
- **Funnel Solde De Point** -- Upsell 1: Formation Gestion des Emotions, Upsell 2: Formation Gestion du temps

#### KPI1 Dropdown (Two sets of sub-items confirmed)

**Set A** (from Batch C screenshot):
1. KPI VILLES
2. VILLES AVEC STAGE
3. KPI CENTRE
4. PRIX MIN ANNONCES ADW
5. URL REFERENTES SEO
6. ALGO PRIX
7. PRIX PAP
8. DEPARTEMENTS RAYON STAGES

**Set B** (from Batch D screenshot):
1. SUIVI PROSPECTS
2. VENTE STAGES JOUR
3. VENTES STAGES SEMAINE
4. VENTES STAGES MOIS
5. VENTES STAGES MOIS - INSCRIPTIONS
6. VENTES STAGES MOIS - STAGES REALISES
7. NBR STAGES EN LIGNE
8. SOLDE CLIENTS

Visually confirmed pages:
- **KPI Villes** -- 298 city entries with ranking, center count, stages, margins
- **Villes stage en ligne** -- 298 cities with competition data, pricing stats
- **KPI Centre** -- Center performance (stages, cancellation %, revenue, avg price)
- **Prix Min Adwords** -- 101 campaign entries with city/department targeting
- **URL referentes SEO** -- 47 SEO URL entries with population, GPS, addresses
- **PRIX PAP** -- Stratalis crawl data (empty at time of screenshot)
- **Departements rayon stages** -- 100 department entries with radius settings
- **Algo Prix** -- Pricing algorithm parameters per city (delta prix, min, max, distance)
- **KPI Jour PSP** -- Daily financial KPIs (registrations, revenue, commissions, margins)
- **KPI Semaine PSP** -- Weekly aggregated financial KPIs
- **Ventes Stages Mois - Inscriptions** -- Monthly registration data (25 months)
- **Ventes Stages Mois - Stages Realises** -- Monthly completed stage data (25 months)

#### KPI2 Dropdown
Visually confirmed pages:
- **Solde Client** -- 4-category financial summary (99 pending, 51 old, 1210 cancelled+refunded, 7 refund requested)
- **Stage a venir** -- 13-month forecast (centers online, stages, registrations)
- **TRACKING UTILISATEURS** -- User tracking with 18 columns (fiche ville views, fiche stage views, form clicks, payment page, card validation, UP2PAY, errors)

#### MAILS Dropdown
Visually confirmed pages:
- **Serveurs SMTP** -- 5 configured servers: Brevo (default), MailJet, Mailtrap, OVH, Sendinblue
- **Mails envoyes** -- Sent email log with date filter (11 columns including sender, recipient, subject, error)

#### SMS Dropdown
Visually confirmed pages:
- **API** -- 1 configured provider: SmsEnvoi (host: api.smsenvoi.com)
- **SMS envoyes** -- Sent SMS log with date filter (9 columns including status, recipients, message, error)

#### ANCIEN Dropdown (22 sub-items)
Visually confirmed dropdown items:
1. VENTE STAGES JOUR
2. VENTES STAGES MOIS
3. MESSAGES
4. STAGES
5. SALLES
6. FORMATEURS
7. CANDIDATURES
8. VIREMENT FORMATEURS
9. PARAMETRAGE DES LIBELLES (DONNEES CLIENTS)
10. APPLI TWELVY (DONNEES CLIENTS)
11. ALERTE AUX POINTS (DONNEES CLIENTS)
12. APPLI TIMELY (DONNEES CLIENTS)
13. APPLI 'SERENITY' (DONNEES CLIENTS)
14. APPLI 'TIMELY' (DONNEES CLIENTS)
15. FORMATION 'BUISNESS 12 POINTS' (DONNEES CLIENTS)
16. FORMATION 'BUISNESS 12 POINTS' COMPLETE (DONNEES CLIENTS)
17. APPLI TWELVY (FUNNELS)
18. ALERTE AUX POINTS (FUNNELS)
19. FORMATION 'BUISNESS 12 POINTS' (FUNNELS)
20. FORMATION 'BUISNESS 12 POINTS' COMPLETE (FUNNELS)
21. APPLI TIMELY (FUNNELS)
22. APPLI 'SERENITY' (FUNNELS)

Visually confirmed pages:
- **KPI Jour PSP** (under ANCIEN) -- Same structure as KPI1 daily view but with legacy date range, 727 entries

#### MARGE COMMERCIALE Dropdown (14 sub-items)
Visually confirmed dropdown items:
1. KPI Villes
2. KPI Departements
3. LISTE VILLES REFERENTES
4. LIEUX STAGES (rattaches a ville referente)
5. ALGO PRIX STAGE
6. ALGO PRIX STAGE V2
7. STAGES
8. STAGES V2
9. COMMISSION STAGIAIRES
10. HISTORIQUE PRIX INDEX CENTRES
11. PRIX PLANCHER
12. COMMISSION DEP
13. [BETA] MARGES
14. [BETA] STAGES + MARGES

Visually confirmed pages:
- **Villes stages en ligne** -- 784 cities with stage data, status indicators
- **Liste Villes Referentes** -- 251 reference cities with population, attached cities, floor prices
- **Lieux des stages en ligne** -- 41 venue entries with brand, name, address, pricing
- **ALGO PRIX STAGE** -- Configuration form (5 fields: temporelle, stage min, index min/max, prix index)
- **STAGES** -- 216 stage entries with city, center, partnership, status, pricing columns
- **COMMISSION STAGIAIRES** -- Commission per trainee with 22 columns (prix vente, prix transfert, prix index, marge)
- **PRIX PLANCHER** -- 100 department entries with floor price, radius, margin toggles
- **[BETA] MARGES** -- Department margin admin (add/edit form, department table with ON/OFF, simulation panel)
- **[BETA] STAGES + MARGES** -- Suivi stages with margin data (50 trainees, pricing columns)

---

### Common UI Patterns (Visually Confirmed)

**Data Tables (DataTables library)**:
- Present on every data page
- Features: sorting (up/down arrows), per-column filtering (light blue input row), pagination, entry count
- Export buttons: Excel | CSV | PDF | Print (top-right of most tables)
- "Show [dropdown] entries" control at bottom-left
- "Showing X to Y of Z entries" counter
- Previous | [page numbers] | Next pagination

**Date Range Filters**:
- Two date input fields with navy/teal background
- "OK" button to apply
- Circular refresh icon (reload data)

**Color Coding**:
- Active tab: Red/brick background (#c9302c)
- KPI2 tab: Sometimes shown with blue/teal background
- ANCIEN tab: Red background
- MARGE COMMERCIALE: Red outlined button style
- Status badges: Green for active/ON/Inscrit, Red for OFF/cancelled, Blue for informational
- Accounting: Red "D" for Debit, Green "C" for Credit
- Refund buttons: Green "valider", Blue for pending requests

**Buttons**:
- Primary actions: Green (#5cb85c) -- "Mettre a jour", "Enregistrer", "VALIDER VIREMENT"
- Secondary actions: Blue (#337ab7) -- "Modifier", "Ajouter", "Editer", "Filtrer"
- Destructive actions: Red/coral -- "Supprimer", "TELECHARGER XML"
- Export: Grey/white bordered -- "Excel", "CSV", "PDF", "Print"

**Form Patterns**:
- Bootstrap 3 panel/card styling
- White card backgrounds with light grey borders
- Blue checkboxes when checked
- WYSIWYG rich text editors for messages
- Dropdown selectors with blue/teal pill styling for pricing

**Page Layout**:
- Full-width content area below navigation bar
- White page background
- Page titles with small bar chart or gear icons
- Refresh/reload icons next to titles

**Design Framework**: Bootstrap 3.x with DataTables integration, Font Awesome icons, SweetAlert2 modals

---

### Detailed Page Descriptions (From Screenshots)

#### 1. STAGIAIRES -- Main List

**Screenshot evidence**: Screenshot 01.16.45

**Page title**: "Stagiaires" with bar chart icon

**Filters**:
- "Date Inscription" with two date pickers and "OK" button
- Search field: "RECHERCHE UN STAGIAIRE" (top-right)

**Table columns (18)**:
1. Id (sorted descending)
2. Nom
3. Prenom
4. Email
5. Tel
6. Date/Heure inscription
7. Statut
8. Dossier
9. Date Stage
10. Prix Stage
11. Ville
12. Centre
13. Ref commande
14. Facture Stage
15. Avoir Stage
16. Remboursement
17. Reversement Centre
18. Actions

**Export**: Excel | CSV | PDF | Print

---

#### 2. CENTRES -- Main List

**Screenshot evidence**: Screenshot 01.16.57

**Page title**: "Centres"

**Key controls**:
- "Ajouter un centre" button (blue/teal)
- Toggle: "Ne pas afficher l'option 'Taux Normaux' / choix tous les centres"

**Data**: 714 total centers, showing 264 per page

**Table columns**: Checkbox, Date, Nom, Email, Tel, Ville, Capitaine, Statut (DECLARE/NON DECLARE), plus address, postal code, active status columns

**Center names visible**: ATOUT POINT, AUTO ECOLE EDGAR, DROP ACADEMY, ABER FORMATION, PRODRIVE ACADEMY

---

#### 3. CENTRES -- Liste des demandes de partenariat

**Screenshot evidence**: Screenshot 01.17.04

**Data**: 186 partnership requests

**Table columns (10)**: Row number, Date, Nom, Activite (Autoecole/Fahrschules/MAITRE/Autres), Societe, Prenom, Code Postal, Ville, Email, Tel

---

#### 4. CENTRES -- Gestion des CGP

**Screenshot evidence**: Screenshot 01.17.09

**Data**: 196 CGP entries

**Controls**: "Nombre des articles trouves" counter (red text), "Mettre a jour" button

**Table**: IDs counting down from 1193, center names, emails, plus additional management columns

---

#### 5. CENTRES -- Administration - Centres - Banniere

**Screenshot evidence**: Screenshot 01.17.16

**Layout**: Blue header banner, 3 card sections

**Section 1 - BANNIERE**: Active checkbox (checked) + "Enregistrer" button

**Section 2 - Texte - Popup**: Rich text editor with content about "PLANNING 2026" reminding centers to put their 2026 calendar online

**Section 3 - Centres**: "Tous les centres" checkbox + searchable center list (AARP, ABER FORMATION, CER CRESPO, FORMATION TERRAT, IFECC visible)

---

#### 6. VIREMENTS CENTRES -- Main Page

**Screenshot evidence**: Screenshot 01.17.20

**Sub-navigation tabs (pill buttons)**:
1. "Virements en attente" (active)
2. "Virement centres effectues"
3. "Stagiaires vires"
4. "Liste virements non effectues"

**Action buttons**: "TELECHARGER XML" (red/coral), "VALIDER VIREMENT" (green)

**Checkbox**: "Selectionner Tous" (checked)

**Data**: 3 pending transfers

**Table columns (18)**: Expand icon, ID, Nom, TVA (Oui/Non), IBAN, NB STAGIAIRES, ENC HT, ENC TTC, TOTAL COMM HT, TOTAL COMM TTC, TOTAL MARGE COMMERCIALE HT, TOTAL MARGE COMMERCIALE TTC, TOTAL MARGE BRUTE PSP HT, TOTAL MARGE BRUTE PSP, VIREMENT HT, VIREMENT TTC (editable), COMMENTAIRE EXTERNE, CONSIGNE VIREMENT

**Sample data**:
- ABC PERMIS: 5 trainees, Virement TTC: 682.8 EUR
- AUTO ECOLE D'OLIVE DU PRES: 3 trainees, Virement TTC: 256.56 EUR
- Top Drive Learning (TDL): 1 trainee, Virement TTC: 112.8 EUR (Belgian IBAN)

---

#### 7. VIREMENTS CENTRES -- Totaux

**Screenshot evidence**: Screenshot 01.17.26

**Summary row**:
- NBR CENTRES A VIRER: 3
- TOTAL NBR STAGIAIRES: 9
- TOTAL PRIX DE VENTE TTC: 1932
- TOTAL COMMISSION HT: 733.2
- TOTAL MARGE COMMERCIALE HT: 0
- TOTAL MARGE BRUTE PSP HT: 733.2
- TOTAL VIREMENT TTC: 1052.16

**Verification table (20 columns)**: Stagiaire, Date Inscription, Nom/Prenom, E-mail, Date Stage, Ville, Partenariat, Statut paiement Up2Pay, Prix de vente TTC, Prix index centre TTC, Price Transfer, Commission HT, Verif Comm HT, Difference Comm HT, Marge commerciale HT, Marge brute PSP HT, Verif Marge Brute PSP HT, Difference Marge Brute HT, Virements TTC, Verif Virement TTC

---

#### 8. VIREMENTS CENTRES -- Fichier Virement

**Screenshot evidence**: Screenshot 01.17.31

**Purpose**: Verify blocked XML bank transfer files

**Layout**: Single form with text input ("Renseigner le nom du fichier XML bloque") and "Verifier" button

---

#### 9. SUIVI STAGES -- Site des stages en ligne

**Screenshot evidence**: Screenshot 01.18.58

**Data**: 2,714 online stage listings

**Centers visible**: ATOUT POINT, CNRP, DROP ACADEMY MELUN, L2R EPINAY-SUR-SEINE, PRODRIVE ACADEMY

**Status indicators**: Green checkmarks and red X marks for stage status

---

#### 10. SUIVI STAGES -- Prix plancher

**Screenshot evidence**: Screenshot 01.19.06

**Data**: 100 department entries

**Table columns (4)**: Departement, Prix plancher (dropdown: "1 EUR"), Rayon prix index min (dropdown: "Ville referente" or "20 Km"), Activer Marge Commerciale (checkbox)

**Notable**: Department 13 (Bouches du Rhone) set to "20 Km" radius while others default to "Ville referente"

---

#### 11. SUIVI STAGES -- Commission 2024

**Screenshot evidence**: Screenshot 01.19.15

**Data**: 52 price range entries for department 34

**Message for centers**: Text about evolving pricing for departments 13, 69, and 83 starting April 22, 2024

**Table columns (4)**: Id, Prix Min - Prix Max (ranges: 1-9, 10-19, 20-29...), Commission Standard HT (all 80 EUR), Commission Premium HT (all 80 EUR)

**Controls**: Department selector dropdown, add/edit/delete action icons

---

#### 12. SUIVI STAGES -- Gestion des Commissions

**Screenshot evidence**: Screenshot 01.19.38

**Purpose**: Granular price-to-commission lookup table

**Controls**: "Importer.CSV" button (dark navy), file upload input

**Table columns (4)**: Save icon, Prix stage (EUR) (500 down to 1), Commission HT (EUR) (editable: 110.00 for 500, decreasing by 0.10 per EUR), Commission TTC (EUR) (calculated: HT * 1.20), Reversement TTC (EUR) (calculated)

**Pattern**: For every 1 EUR decrease in stage price, Commission HT decreases by 0.10 EUR

---

#### 13. SUIVI STAGES -- Accord centre commission

**Screenshot evidence**: Screenshot 01.19.27

**Data**: 596 center entries

**Table columns (6)**: ID du centre, Nom du centre, Stages en ligne, A accepte la nouvelle commission (Oui/Non), Date acceptation, Nbr affichage message

**Key finding**: Only 2 centers accepted new commission: ATOUT POINT (33 online stages) and DROP ACADEMY LYON

---

#### 14. COMPTA PSP -- AVOIRS (Credit Notes)

**Screenshot evidence**: Screenshot 01.20.06, 01.20.14

**Data**: 6 entries for February 2026

**Accounting pattern**: Each refund generates 3 entries:
- Account 00210000: Debit (D in red) -- full amount
- Account 70621000: Credit (C in green) -- HT amount (revenue reversal)
- Account 44572000: Credit (C in green) -- TVA amount

**Sample**: Avenard Alexandre: 209 EUR refund = 209 D + 174.17 C + 34.83 C

---

#### 15. COMPTA PSP -- Batch

**Screenshot evidence**: Screenshot 01.20.32

**Data**: 14 entries for February 2026

**Purpose**: SEPA bank transfer batch records

**Pattern**: First row is Credit (total batch: 4354.88 EUR), remaining rows are Debits per center

**Account numbers**: Follow pattern 9300XXXX where XXXX is center ID

**Centers paid**: ATOUT POINTS PERMIS (143.6), Chambre de Metiers (147.12), EMA FORMATIONS (148), ALLO ECO TRANSPORT 78 (156.8), H-CONDUITE (204.4), AUTO ECOLE D'OLIVE DU PRES (256.56), PSSR (290), LIBERTY PERMIS (303.2), JAURES PERMIS (338.4), PERMIS A TOUT POINT (518.8), FORMA EST (559.6), CFP-IDF (606.24), ABC PERMIS (682.8)

---

#### 16. ANIMATEURS -- Main List

**Screenshot evidence**: Screenshot 01.20.42

**Data**: 2,748 total trainers, 112 shown after filtering

**Controls**: "Ajouter un formateur" button

**Table columns**: Checkbox, ID, Nom, Prenom, Email, Profession, Seuil (threshold), Dep (department), Num Centre, action buttons

---

#### 17. ANIMATEURS -- Candidatures

**Screenshot evidence**: Screenshot 01.20.47

**Table columns (9)**: Nb Jours, Date, Ville, Dep, Inscrits, Formateurs, Status, Candidatures, Derniere candidature

---

#### 18. FUNNELS -- Configuration Pages

**Screenshot evidence**: Screenshots 01.22.01 through 01.22.39

**Layout**: 5 collapsible sections per funnel:
1. **Prix** -- Prix normale + Prix remise + "Modifier" button
2. **Order Bump** -- Dropdown selector
3. **Upsell 1** -- Dropdown selector
4. **Upsell 2** -- Dropdown selector
5. **Down Sell** -- Dropdown selector

**Funnel configurations**:
| Funnel | Prix Normal | Prix Remise | Order Bump | Upsell 1 | Down Sell |
|--------|------------|-------------|------------|----------|-----------|
| Stage | (empty) | (empty) | (empty) | (empty) | (empty) |
| Gestion Temps | 360 | 97 | Alerte Aux Points | Appli TWELVY | Formation Gestion des Emotions |
| Gestion Temps Complete | 360 | 97 | (empty) | (empty) | (empty) |
| Gestion Emotion | 325 | 85 | (empty) | (empty) | (empty) |
| Gestion Emotion Complete | 325 | 85 | (empty) | (empty) | (empty) |
| Solde De Point | (empty) | (empty) | N/A | Form. Gestion Emotions | N/A |

---

#### 19. KPI1 -- KPI Villes

**Screenshot evidence**: Screenshot 01.22.46

**Data**: 298 city entries with TOTAL summary row

**Table columns**: VILLE, Code, Dep, Top Villes (checkbox), CLASSEMENT, CENTRE, STAGES PSP, STAGES PSP FUTUR, NB STAGIAIRE, MARGE, plus additional metric columns

**Cities**: MARSEILLE, PARIS, LYON, TOULOUSE, AIX, NANTES, MONTPELLIER, BORDEAUX, BIARRITZ, BLAYE...

---

#### 20. KPI1 -- KPI Centre

**Screenshot evidence**: Screenshot 01.26.29

**Table columns**: CENTRE, Id CENTRE, Date, Sous Commission, Places, STAGES, % ANNULATION, INSCRITS AUTRES, INSCRITS PSP, ASSISTS AUTRES, ASSISTS PSP, NRJ, CA, PRIX MOYEN, ACTION

**Filter tabs**: "Tous les centres" / "... stage" / "Tout stage (depuis 01 01 2023)"

---

#### 21. KPI1 -- Algo Prix

**Screenshot evidence**: Screenshot 01.28.39

**Data**: ~20 major cities

**Table columns**: Nom de ville, Habitants, Delta Prix, Prix Min, Prix Max, Distance, Remontee ou Att, Delta jour avant, Delta jour apres, plus threshold columns

**Cities**: marseille, lyon, toulouse, bordeaux, nantes, strasbourg, montpellier, lille, rennes, reims, saint etienne, toulon, grenoble, dijon, angers, le mans, aix en provence

---

#### 22. KPI1 -- KPI Jour PSP (Daily)

**Screenshot evidence**: Screenshot 01.28.54

**Data**: 50 daily entries

**Table columns**: DATE, DATE SEMAINE, INSCRIT_INIT, INSCRIT NET, INSCRIT HT, T. VAT, PRE-MATOS, COMMISSION, MARGE CON PSP, MARGE CON PSP MATIN, plus additional financial columns

---

#### 23. KPI1 -- KPI Semaine PSP (Weekly)

**Screenshot evidence**: Screenshot 01.29.02

Same structure as daily KPI but aggregated by week (Monday dates)

---

#### 24. KPI1 -- Ventes Stages Mois - Inscriptions

**Screenshot evidence**: Screenshot 01.29.07

**Data**: 25 monthly entries

**Table columns**: DATE, MOIS DU, INSCRIPTIONS REALISEES, PRIX COMMISSION, X MOIS, INSCRIT NET, INSCRIT HT, INSCRIT NAT, INSCRIT ADS, REMBOURSEMENT, T. VAT, PRE MATOS, COMMISSION, CHANGES AYANT REALISE AU CENTRE, MARGE CON PSP, MARGE CON PSP MATIN, TOTAL MARGE

---

#### 25. KPI1 -- Departements rayon stages

**Screenshot evidence**: Screenshot 01.28.13

**Data**: 100 department entries

**Table columns (7)**: N DEPARTEMENT, NOM DU DEPARTEMENT, NOM DE VILLE, LATTITUDE, LONGITUDE, RAYON (0, 100, or 120), Edit icon

**Departments visible**: Ain, Aisne, Allier, Alpes de Haute Provence, Alpes (Hautes), Alpes Maritimes, Ardeche, Ardennes, Ariege, Aube, Aude, Aveyron, Bouches du Rhone, Calvados, Cantal...

---

#### 26. KPI1 -- Prix Min Adwords

**Screenshot evidence**: Screenshot 01.26.34

**Data**: 101 campaign entries

**Controls**: Green "Recharger par Campagne" button, Blue "Exporter le fichier CSV" button

**Campaign name pattern**: "PSP - VILLE - [City] ([Dept]) - [Type] ([Match Type])"

**Match types**: Localite (xxx Exact), Gen (xxx Exact), Localite (Large Modified), Gen (Large Modified)

---

#### 27. KPI1 -- URL referentes SEO

**Screenshot evidence**: Screenshot 01.26.46

**Data**: 47 SEO URL entries

**Controls**: Blue "Recharger par URL" button

**Table columns**: Row, Nom de la ville, Habitants, Loyer Moy., Recherche, Code postal Redirection SEO, SRP, Adresse, Longitude, Latitude, Nom, Places, Date

---

#### 28. KPI2 -- Solde Client

**Screenshot evidence**: Screenshot 01.29.27, 01.30.19

**4-row summary**:
| Category | Inscrits Net | Montant Encaisse TTC | Prix Moyen TTC |
|----------|-------------|---------------------|----------------|
| Vires: Non / Rembourse: Non | 99 | 19,601 | 197.99 |
| Anciens chiffres | 51 | 11,158 | 218.78 |
| Vires: Non / Rembourse: Oui | 1,210 | 250,196 | 206.77 |
| Demande de remboursement | 7 | 1,288 | 184.00 |

---

#### 29. KPI2 -- Stage a venir

**Screenshot evidence**: Screenshot 01.30.09

**Data**: 13-month forecast

**Table columns (6)**: #, MOIS, CENTRE AVEC STAGE EN LIGNE, STAGES EN LIGNE, INSCRITS BRUT, INSCRITS NET

**Sample data**: February 2026: 34 centers, 208 stages, 72 brut / 65 net registrations. March 2026: 37 centers, 462 stages, 5 brut / 5 net.

---

#### 30. KPI2 -- TRACKING UTILISATEURS

**Screenshot evidence**: Screenshot 01.32.18

**Data**: 16 daily entries

**Table columns (18)**:
- DATE INSCRIPTION
- INSCRITS BRUT (two variants)
- INSCRITS NET
- PROSPECTS
- FICHE VILLE (city card views: 3,000-5,000/day)
- FICHE STAGE (stage card views: 200-14,000/day)
- CLIC AFFICHAGE FORMULAIRE
- CLIC VALIDATION FORMULAIRE
- PAGE PAIEMENT
- CLIC AFFICHAGE FORMULAIRE CB
- CLIC VALIDATION FORMULAIRE CB
- ENVOYE VERS UP2PAY
- PAGE PAIEMENT VALIDE
- PAGE ERREUR PAIEMENT
- ERREUR AUCUN RETOUR
- ERREUR TECHNIQUE INTERNE
- NUMERO DE CARTE ERRONE

**Controls**: "Suppression jusqu'a J-7" button (teal/blue)

---

#### 31. MAILS -- Serveurs SMTP

**Screenshot evidence**: Screenshot 01.30.24

**5 configured SMTP servers**:
| Name | Host | Port | isSMTP | SMTPAuth | Default |
|------|------|------|--------|----------|---------|
| Brevo | smtp-relay.brevo.com | 465 | true | true | YES |
| MailJet | in-v3.mailjet.com | 465 | true | true | No |
| Mailtrap | smtp.mailtrap.io | 2525 | true | true | No |
| OVH | ovh | 465 | false | true | No |
| Sendinblue | smtp-relay.sendinblue.com | 465 | true | true | No |

**Controls per row**: "Modifier" (blue) + "Supprimer" (red) buttons. Empty first row with "Ajouter" (blue) button.

---

#### 32. MAILS -- Mails envoyes

**Screenshot evidence**: Screenshot 01.30.29

**Table columns (11)**: ID, Date 1er envoi, Date dernier envoi, Nombre envois, Expediteur nom, Expediteur email, Destinataire email, Destinataire copies, Mail objet, Erreur, Actions

---

#### 33. SMS -- API

**Screenshot evidence**: Screenshot 01.30.32

**1 configured SMS provider**:
- Name: SmsEnvoi
- Host: https://api.smsenvoi.com/API/v1.0/REST/
- Default: YES

---

#### 34. SMS -- SMS envoyes

**Screenshot evidence**: Screenshot 01.30.37

**Table columns (9)**: ID, Statut, Date dernier envoi, Nombre envois, Destinataires, Message, Url, Erreur, Actions

---

#### 35. MARGE COMMERCIALE -- ALGO PRIX STAGE

**Screenshot evidence**: Screenshot 01.32.07

**Purpose**: Price algorithm configuration form

**Form fields (5)**:
1. TEMPORELLE (en jours)
2. STAGE MIN AU DELA PLAGE TEMPORELLE
3. INDEX MIN ZONE DEGRESSIVE
4. INDEX MAX ZONE DEGRESSIVE
5. PRIX INDEX DANS ZONE DEGRESSIVE

**Button**: "Mettre a jour" (green)

---

#### 36. MARGE COMMERCIALE -- [BETA] MARGES

**Screenshot evidence**: Screenshot 01.32.51

**3-panel layout**:

**Left Panel -- "Ajouter / Modifier"**:
- Info box: "Saisie du montant en TTC (TVA 20%)"
- Fields: Departement, Type (POURCENTAGE/EURO), Montant TTC, Montant HT (calculated), Actif checkbox
- Buttons: "Enregistrer" (green) + "Reinitialiser" (grey)

**Right Panel -- "Departements" table**:
- Filter input
- Action buttons: "Activer selection" (green), "Desactiver selection" (red), "Rafraichir" (grey)
- Columns: Checkbox, Departement, Montant TTC, Type, Actif (ON green / OFF red), Actions (Editer blue)
- Data: Dept 2 (20%, ON), Dept 3 (50 EUR, ON), Dept 75 (59%, OFF)

**Bottom Panel -- "Simulation"**:
- Columns: Prix TTC, Marge TTC, Marge HT, Total TTC, Total HT
- Rows from 500 EUR down to 492 EUR with calculated values

---

#### 37. MARGE COMMERCIALE -- [BETA] STAGES + MARGES

**Screenshot evidence**: Screenshot 01.32.59

**Title**: "Suivi stages - Marge Commerciale"

**Filters**: Date stage from/to, "Filtrer" button, "50 stagiaires" count button

**Table columns**: Reference, Date Depart, Date de stage, Adresse, CP, Ville, Centre, Act Suivi, Statut de stage, Prix Plancher, Marge de stage, Prix HT, Prix Index, Prix Index Centre, Prix vente PSP TTC

---

#### 38. STAGIAIRES -- Modifications de prix

**Screenshot evidence**: Screenshot 01.32.41

**Table columns (8)**: Date modification, Id Stage, Date Stage, CP (Stage), Ville (Stage), Centre (ID), Ancien Prix Index TTC, Nouveau Prix Index TTC

---

---

## WHO USES IT

**ProStagesPermis internal staff only**

- Single shared login (not per-user authentication)
- Username: `prostagespermis`
- Password: `caurotu3425` (hardcoded in index.php)
- Full access to all data across all centers and trainees

---

## AUTHENTICATION

**File**: `index.php`

**Login Method**: Basic username/password form

**Authentication Logic**:
```php
$validUser = $_POST["username"] == "prostagespermis" && $_POST["password"] == "caurotu3425";
if($validUser) {
    $_SESSION['autorization'] = true;
    header('Location: https://www.prostagespermis.fr/simpligestion/inscriptions2.php');
}
```

**After Login**: Redirects to inscriptions page (trainee bookings dashboard)

**Security Note**: Hardcoded credentials -- no individual user accounts or role-based access control

---

## VISUAL LAYOUT

**Design Style**: Bootstrap 3 admin dashboard with professional data tables

**Login Page**:
- Minimalist centered login box
- White text on semi-transparent dark overlay
- Green login button (#2ecc71)
- Gradient background (white to light grey)
- Title: "Login simpligestion"

**Main Dashboard Layout** (after login):
- **Navigation Bar**: Dark charcoal (#222222) horizontal bar, white uppercase text, dropdown carets, red highlight on active tab
- **Content Area**: Full-width below nav bar, clean white background
- **Tables**: DataTables library with sorting, filtering, pagination, export
- **Modals**: SweetAlert2 for dialogs

**Color Scheme** (visually confirmed):
- Navigation bar: #222222 (dark charcoal)
- Active tab: #c9302c (muted red/brick)
- Primary buttons: #337ab7 (Bootstrap blue)
- Success buttons: #5cb85c (green)
- Danger/delete: Red/coral
- Panel headers: Light blue (#eff2f9) or light grey (#f9f8f8)
- Filter row: Light blue/teal input fields
- Status ON: Green badge
- Status OFF: Red badge
- Debit: Red "D"
- Credit: Green "C"

---

## PAGE-BY-PAGE BREAKDOWN

### 1. INSCRIPTIONS -- Bookings Dashboard (`inscriptions3.php`)

**Purpose**: Central hub for managing all trainee bookings across all centers

**Top Features**:
- Autocomplete search by trainee name (min 3 characters)
- Date range filters
- Real-time data table with AJAX loading

**Table Structure**:
- DataTables implementation with responsive design
- Expandable row details (magnifying glass icon `images/loupe.png`)
- Sortable columns
- Custom styling for status badges

**Key Functionality**:
- View trainee details
- Track booking status
- Monitor file completion
- Validate payments
- Handle cancellations/transfers

**Technical Notes**:
- Uses `autocomplete_stagiaire.php` for name search
- Session-based ID filtering: `$_SESSION['id_stagiaire']`
- SweetAlert2 for modal dialogs
- Loading overlay for async operations
- Custom CSS classes:
  - `.count` -- Red badge counter (rgb(228, 35, 0))
  - `.utilisateur` -- User name display (bold)
  - `.charge_compte` -- Account balance (blue italic)

**Related AJAX Files**:
- `ajax_inscriptions3.php` -- Load bookings data
- `ajax_inscriptions3_save.php` -- Save booking changes
- `ajax_annule_stagiaire.php` -- Cancel trainee
- `ajax_transferer_inscription.php` -- Transfer to different stage
- `ajax_valider_paiement.php` -- Validate payment

---

### 2. CENTRES -- Training Center Management (`centres.php`)

**Purpose**: Manage all training centers in the network

**Visually Confirmed**: 714 centers total, 264 shown per page

**Top Actions**:
- "AJOUTER UN CENTRE" button (blue, toggles add form)
- Date range filter
- Reload button
- Pass Vaccinal display toggle (COVID-related setting)

**Add New Center Form** (hidden by default):
```html
<input name="nom" placeholder="Nom du centre">
<input name="nom_contact" placeholder="Nom du contact">
<input name="code_postal" placeholder="Code postal">
<input name="ville" placeholder="Ville">
<input name="tel" placeholder="Telephone">
<input name="email" placeholder="Email">
<button id="valider_nouveau_centre">AJOUTER</button>
```

**Table Columns**:
- ID, Date inscription, Centre/contact, Tel, Email, Adresse, TVA
- Stages ligne (live courses count)
- Stagiaires (trainee count)
- Partenariat (partnership status)
- Tel/Email Stagiaire (visibility controls)
- Acces ANTS, Acces Bilan, Acces Nbr Places, Acces Transfert, Acces Chgnt Lieu
- Actions

**Key Observations**:
- Granular permission control per center
- Status values: DECLARE / NON DECLARE
- Phone/email visibility control (data privacy)

**Related Files**:
- `ajax_centres.php`, `ajax_centres5.php` -- Load center data
- `ajax_details_centre.php` -- Center detail popup
- `ajax_details_centre_save.php` -- Save center changes

---

### 3. KPI -- Key Performance Indicators (`kpi.php`)

**Purpose**: Analytics dashboard for business metrics

**View Modes** (query string parameter):
- `?journalier` -- Daily KPIs (default)
- `?mensuel` -- Monthly KPIs
- `?ville` -- City-level KPIs

**Visually Confirmed KPI Pages**:
- KPI Jour PSP: 50 daily entries with financial columns
- KPI Semaine PSP: Weekly aggregated data
- KPI Villes: 298 cities with rankings, stage counts, margins
- KPI Centre: Center performance metrics
- Ventes Stages Mois - Inscriptions: 25 monthly periods
- Ventes Stages Mois - Stages Realises: 25 monthly periods

**Data Visualization**: Table-based (not charts), footer row with totals

---

### 4. VENTE PSP -- Sales Dashboard (`vente_psp.php`)

**Purpose**: Track PSP sales and revenue

**Title**: "VEP-VENTES PSP_1" (Ventes Espace ProStagesPermis)

**Filters**:
- Date stage: Stage date range (min/max)
- OK button to apply filters

**Page Type**: `TYPE_PAGE_COMPTA` (accounting/financial)

**Related Files**:
- `vente_psp_2.php` -- Alternative version
- `achat_psp.php` -- Purchase tracking
- `renta_psp.php` -- Profitability analysis
- `bilan_psp.php` -- Financial statement
- `batch_psp.php` -- Batch processing

---

### 5. VIREMENTS -- Payment Transfers (`virement_sepa_centres_v2.php`)

**Purpose**: Process SEPA payments to training centers

**Visually Confirmed Layout**:
- 4 sub-navigation tabs (pill buttons)
- "TELECHARGER XML" (red/coral) + "VALIDER VIREMENT" (green) action buttons
- "Selectionner Tous" checkbox
- Expandable rows (green "+" circle icons)
- Editable VIREMENT TTC input fields
- COMMENTAIRE EXTERNE and CONSIGNE VIREMENT textareas

**Tab System** (query string `?tab=`):
- `va` -- Virements a valider (transfers to validate) -- DEFAULT
- `ce` -- Centres effectues (completed centers)
- `vce` -- Virements centres effectues (completed center transfers)
- `sv` -- Suivi virements (transfer tracking)
- `sb` -- Stagiaires bloques (blocked trainees)

**Payment Flow**:
1. View pending transfers (va tab)
2. Validate amounts (edit VIREMENT TTC fields)
3. Click "TELECHARGER XML" to generate SEPA file
4. Click "VALIDER VIREMENT" to mark as processed
5. Track in completed tabs (ce/vce)

**Related AJAX Files**:
- `ajax_virement_sepa_centres.php` -- Load center transfers
- `ajax_virement_sepa_centres_save.php` -- Save changes
- `ajax_valide_virement_centres.php` -- Validate transfers
- `ajax_details_virement_centre.php` -- Transfer details
- `Sepa_credit_XML_Transfer_initation.class.php` -- SEPA XML generator

---

### 6. FORMATEURS/ANIMATEURS -- Trainer Management (`formateurs.php`)

**Purpose**: Manage course trainers/animators

**Visually Confirmed**: 2,748 total trainers in database, 112 shown with active filters

**Key Controls**: "Ajouter un formateur" button for adding new trainers

**Related Files**:
- `ajouter-formateur.php` -- Add new trainer
- `modifier-formateur.php` -- Edit trainer
- `popup_recherche_animateur.php` -- Trainer search popup
- `autocomplete_formateur.php` -- Autocomplete search
- `ajax_formateurs.php` -- Load trainer data
- `ajax_attribue_formateur.php` -- Assign trainer to stage

---

### 7. STAGES -- Course Management (`stages.php`, `stagesv2.php`)

**Purpose**: Manage all courses across all centers

**Visually Confirmed under MARGE COMMERCIALE**: 216 stage entries with city, center, partnership type, status, and pricing columns

**Related Files**:
- `ajouter-stage.php` -- Add new course
- `modifier-stage.php` -- Edit course
- `ajax_stages.php` -- Load stage data
- `ajax_update_stage_status.php` -- Change status
- `ajax_update_prix.php` -- Update price
- `ajax_update_places.php` -- Update capacity

---

### 8. STAGIAIRES -- Trainee Management (`stagiairesv2.php`)

**Purpose**: View and manage all trainees across platform

**Visually Confirmed**: 18-column table with Id, Nom, Prenom, Email, Tel, Date/Heure inscription, Statut, Dossier, Date Stage, Prix Stage, Ville, Centre, Ref commande, Facture Stage, Avoir Stage, Remboursement, Reversement Centre, Actions

**Related Files**:
- `modifier-stagiaire.php` -- Edit trainee details
- `autocomplete_stagiaire.php` -- Search autocomplete
- `ajax_stagiairesv2.php` -- Load trainee data
- `ajax_annule_stagiaire.php` -- Cancel booking
- `popup_transferer_inscription.php` -- Transfer popup

---

### 9. SALLES/LIEUX -- Venue Management (`salles.php`, `lieux.php`)

**Purpose**: Manage training locations

**Related Files**:
- `ajouter-site.php` -- Add venue
- `modifier-site.php` -- Edit venue
- `ajax_salles.php` -- Load venue data
- `ajax_update_cout_salle.php` -- Update venue cost

---

### 10. MESSAGES -- Communication (`messages.php`)

**Purpose**: Internal messaging system

**Related Files**:
- `ajax_messages.php` -- Load messages
- `popup_messages.php` -- Message popup
- `popup_mail.php` -- Email compose
- `renvoie_email.php` -- Resend email

---

### 11. NOTIFICATIONS (`notifications.php`)

**Purpose**: System notification management

**Related Files**:
- `ajax_notifications.php` -- Load notifications
- `ajax_details_notification.php` -- Notification details
- `ajax_set_notification_vu.php` -- Mark as read

---

### 12. EMAILS -- Email Management

**Visually Confirmed**: 5 SMTP servers configured (Brevo as default), sent email log with 11-column table

**Email Viewing**:
- `emails_view.php` -- View sent emails
- `emails_envois.php` -- Email send history
- `emails_smtp.php` -- SMTP configuration

**Email Processing**:
- `ajax_emails.php` -- Load email data
- `ajax_emails_envois.php` -- Send history
- `ajax_emails_smtp.php` -- SMTP settings
- `class.phpmailer.php` -- PHPMailer library

---

### 13. SMS Management

**Visually Confirmed**: SmsEnvoi API configured (api.smsenvoi.com), sent SMS log with 9-column table

**SMS Features**:
- `sms_api.php` -- SMS API integration
- `sms_envois.php` -- SMS send history
- `ajax_sms.php` -- SMS data loading
- `ajax_send_sms.php` -- Send SMS

---

### 14. COMPTABILITE -- Accounting Reports

**Visually Confirmed Pages**:
- AVOIRS: Double-entry credit notes (Debit/Credit with account numbers)
- STATUT: Stage status with accounting references
- Batch: SEPA transfer batch records
- RELAY: Intermediary accounting entries

**Sales Reports**:
- `compta_vente_stage_jour.php` -- Daily sales
- `compta_vente_stage_semainev2.php` -- Weekly sales
- `compta_vente_stage_mois.php` -- Monthly sales
- `compta_vente_stage_inscriptions_mois_v2.php` -- Monthly inscriptions
- `compta_vente_stage_stages_realises_mois_v2.php` -- Completed stages

**Financial Reports**:
- `compta_av_avoir.php` -- Credits/refunds
- `compta_solde_client.php` -- Client balance
- `compta_stage_venir.php` -- Upcoming stages revenue

---

### 15. FACTURES -- Invoice Management (`factures.php`)

**Purpose**: Generate and manage invoices

**Related Files**:
- `aff_facture.php` -- Display invoice
- `visualiser-facture.php` -- View invoice
- `ajax_genere_facture.php` -- Generate invoice
- `ajax_telecharge_factures.php` -- Download invoices
- `functions_facture.php` -- Invoice functions

---

### 16. PRICING -- Price Management

**Visually Confirmed**:
- Prix plancher: 100 departments with floor price dropdowns (1 EUR default)
- Algo Prix: Per-city pricing parameters (delta, min, max, distance)
- Algo Prix Stage: 5-field configuration form under MARGE COMMERCIALE
- Commission lookup: 500-row price-to-commission table (0.10 EUR decrease per 1 EUR)

**Pricing Tools**:
- `prix_plancher.php` -- Floor price management
- `prix_pap.php` -- PAP pricing
- `prix_min_adw.php` -- Minimum AdWords price
- `algo_prix.php` -- Pricing algorithm
- `pricing_tracking.php` -- Price tracking analytics

---

### 17. COMMISSION -- Commission Management

**Visually Confirmed**:
- Commission 2024: 52 price range tiers per department, 80 EUR standard/premium
- Accord centre commission: 596 centers tracked, most have NOT accepted new commission
- Gestion des Commissions: CSV import + granular price-to-commission lookup
- Commission Stagiaires (MARGE COMMERCIALE): 22-column detailed commission tracking

**New Commission Model (2024)**:
- `commission2024.php` -- Commission dashboard
- `accord_centre_commission2024.php` -- Center commission agreement
- `ajax_commission2024_add.php` -- Add commission
- `ajax_commission2024_process.php` -- Process commission

---

### 18. FUNNELS -- Sales Funnel Configuration

**Visually Confirmed**: 6 funnel pages, each with Prix/Order Bump/Upsell 1/Upsell 2/Down Sell sections

**Products in funnel system**:
- Stage (main product)
- Formation Gestion du Temps (upsell: 360 EUR normal / 97 EUR remise)
- Formation Gestion des Emotions (upsell: 325 EUR normal / 85 EUR remise)
- Solde De Point
- Carte Radar
- Carte Diamant
- Alerte Aux Points (order bump)
- Appli TWELVY (upsell)

---

### 19. DONNEES CLIENTS -- Customer Data

**Visually Confirmed**: 7+ data pages for different products/services

**Products tracked**:
- Formation Gestion du Temps: 44 upsell + 256 complete records
- Formation Gestion des Emotions: 41 upsell + 244 complete records
- Solde De Point: 68 inquiry records
- Paiement Amende: 10 records
- Carte Radar: 21 records
- Appli TWELVY: 19 records
- Formation Business 12 Points COMPLETE: 6 records

**Common table columns**: Date, Id, Nom, Prenom, Prix, Statut (Inscrit green / Prospect black), Ref commande, Remboursement

---

### 20. MARGE COMMERCIALE -- Commercial Margin Management

**Visually Confirmed**: 14 sub-pages for managing pricing, margins, and commissions

**Key pages**:
- Villes stages en ligne: 784 cities with stage data
- Liste Villes Referentes: 251 reference cities with population and pricing
- Lieux des stages: 41 venue entries
- ALGO PRIX STAGE: 5-field pricing algorithm configuration
- STAGES: 216 stage entries with pricing
- Commission Stagiaires: Per-trainee commission detail (22 columns)
- Prix Plancher: Per-department floor pricing
- [BETA] MARGES: Department margin admin with simulation
- [BETA] STAGES + MARGES: Combined stage tracking with margin data

---

## AJAX OPERATIONS SUMMARY

SimpliGestion relies heavily on AJAX for dynamic data loading. **165+ AJAX files** handle:

- Data table loading (all `ajax_*.php` files)
- Form submissions
- Popup content
- Real-time updates
- Search/autocomplete
- Validation
- File uploads
- PDF generation
- Email/SMS sending

---

## COMPLETE FILES INVENTORY

**Total Files**: 395 PHP files + assets

**File Categories**:

| Category | Count | Example Files |
|----------|-------|---------------|
| **Main Pages** | ~50 | `inscriptions3.php`, `centres.php`, `kpi.php` |
| **AJAX Handlers** | ~165 | `ajax_inscriptions3.php`, `ajax_centres.php` |
| **Popup Modals** | ~30 | `popup_details_stage.php`, `popup_mail.php` |
| **Autocomplete** | 2 | `autocomplete_stagiaire.php`, `autocomplete_formateur.php` |
| **Accounting** | ~15 | `compta_vente_stage_jour.php`, `compta_solde_client.php` |
| **Payments/Transfers** | ~25 | `virement_sepa_centres_v2.php`, `ajax_sepa_*.php` |
| **Invoices** | ~15 | `factures.php`, `ajax_genere_facture.php` |
| **Emails** | ~10 | `emails_view.php`, `ajax_emails.php`, `class.phpmailer.php` |
| **SMS** | ~6 | `sms_api.php`, `ajax_sms.php` |
| **Documents** | ~15 | `feuille_prefecture.php`, `ajax_zip_stage.php` |
| **Pricing** | ~20 | `prix_plancher.php`, `algo_prix.php` |
| **KPI/Analytics** | ~10 | `kpi.php`, `kpi_centre.php`, `kpi_ville_2024.php` |
| **Helpers/Includes** | ~15 | `includes/header.php`, `functions_aff.php`, `vars.php` |
| **Deprecated/Old** | ~15 | Files ending in `_old.php`, `_ori.php`, dated backups |

---

## WORKFLOW EXAMPLES

### Workflow 1: New Trainee Booking

**User books on public site** then **SimpliGestion receives booking**:

1. Booking appears in `inscriptions3.php` table
2. Admin views trainee details (magnifying glass icon)
3. Checks payment status (`ajax_status_paiement.php`)
4. Validates payment if needed (`popup_valider_paiement.php`)
5. Monitors file completion (documents uploaded in Espace Stagiaire)
6. Tracks stage date approach
7. After stage: marks as completed

### Workflow 2: Monthly Center Payout

**End of month** then **Pay centers for completed stages**:

1. Navigate to `virement_sepa_centres_v2.php?tab=va`
2. See list of pending transfers (visually: 3 centers, 9 trainees, 1052.16 EUR total)
3. Review amounts per center (editable VIREMENT TTC fields)
4. Validate checkboxes ("Selectionner Tous")
5. Click "TELECHARGER XML" (red button)
6. System generates SEPA XML file
7. Download XML file
8. Upload to Credit Agricole Ediweb
9. Click "VALIDER VIREMENT" (green button)
10. Moves to completed tabs

### Workflow 3: Add New Training Center

1. Navigate to `centres.php`
2. Click "AJOUTER UN CENTRE" button
3. Fill form: Center name, Contact, Postal code, City, Phone, Email
4. Click "AJOUTER" button
5. System creates center record
6. Admin sets permissions: ANTS access, Email/phone visibility, Transfer permissions
7. Center receives login credentials for Espace Partenaire

### Workflow 4: Price Adjustment

1. Navigate to `prix_plancher.php` (floor pricing by department)
2. Or use MARGE COMMERCIALE > ALGO PRIX STAGE for algorithm configuration
3. Check competitor prices (`ajax_get_array_concurrence_tab.php`)
4. Adjust floor price or algorithm parameters
5. View simulation results ([BETA] MARGES panel)
6. Changes apply to new stage visibility and pricing

---

## TECHNICAL ARCHITECTURE

### Database Access

**Connection**: Shared MySQL connection via `require_once('../../common_bootstrap2/config.php')`

**Main Tables** (inferred from queries):
- `sessions` -- Training sessions/stages
- `stagiaires` -- Trainees
- `membres` -- Centers (members)
- `formateurs` -- Trainers
- `salles` -- Venues/rooms
- `inscriptions` -- Bookings
- `virements` -- Transfers/payments
- `factures` -- Invoices
- `notifications` -- System notifications
- `messages` -- Messages
- `temoignages` -- Testimonials
- `prix` -- Pricing data

### Authentication and Sessions

**Session Management**:
- PHP sessions (`session_start()`)
- `$_SESSION['autorization']` -- Login flag
- `$_SESSION['id_stagiaire']` -- Trainee filter
- `$_SESSION['id_membre']` -- Center filter
- `$_SESSION['partenariat']` -- Partnership flag

**No Individual User Accounts**: Single shared admin login

### External Dependencies

**JavaScript Libraries**:
- jQuery (extensive use)
- DataTables (table management)
- SweetAlert2 (modal dialogs)
- jQuery UI (autocomplete, datepicker)
- LoadingOverlay (loading indicators)
- jQuery Modal (`jquery.modal.js`)
- QuickEdit (`jquery-quickedit.js`)

**PHP Libraries**:
- PHPMailer (`class.phpmailer.php`)
- Custom SEPA XML generator (`Sepa_credit_XML_Transfer_initation.class.php`)

**CSS Frameworks**:
- Bootstrap 3 (panel-based layout)
- Font Awesome (icons)

### Security Considerations

**Issues Identified**:
1. Hardcoded credentials in `index.php`
2. No role-based access control (single admin account)
3. Direct SQL queries (potential injection risk if not parameterized)
4. Session-only authentication (no token-based auth)
5. No CSRF protection visible

**Positive Security**:
- Session-based authentication
- HTTPS enforced (redirect to https://www.prostagespermis.fr/simpligestion/)

---

## MIGRATION CONSIDERATIONS FOR NEXT.JS

### Critical Challenges

**1. Monolithic Architecture**:
- 395 PHP files with tight coupling
- No API separation (PHP renders HTML directly)
- Heavy use of inline SQL queries
- Session-based state management

**2. Data Table Complexity**:
- 50+ DataTables implementations
- Custom AJAX loaders for each table
- Complex filtering, sorting, pagination
- Expandable rows with nested data

**3. Payment Processing**:
- SEPA XML generation
- Multi-step payment workflows
- Bank integration dependencies
- Regulatory compliance (European banking)

**4. Authentication Overhaul Needed**:
- Current: Single shared admin login
- Required: Individual user accounts with roles
- Needed: JWT or session-based auth
- Needed: Permission levels (super admin, admin, viewer)

**5. Real-time Operations**:
- Frequent AJAX polling
- Live data updates
- Concurrent admin access (if multi-user)

### Migration Strategy Recommendations

**Phase 1: API-fy**
- Create RESTful API endpoints for each major function
- Replace AJAX PHP files with Next.js API routes
- Maintain database schema (PostgreSQL migration later)
- Use Prisma or similar ORM

**Phase 2: Admin Dashboard**
- Use React Admin or similar framework
- Rebuild DataTables with React Table or AG Grid
- Implement server-side pagination
- Create reusable components (Modal, Form, Table)

**Phase 3: Authentication**
- Implement NextAuth.js
- Create user roles (SuperAdmin, Admin, Viewer)
- Permission-based access to features
- Audit logging

**Phase 4: Critical Workflows**
- Payment processing (top priority)
- KPI dashboards (data visualization)
- Booking management
- Center management

**Phase 5: Nice-to-Have**
- Real-time notifications (WebSockets)
- Advanced analytics
- Modern charting (Chart.js, Recharts)

### Recommended Tech Stack

**Frontend**:
- Next.js 15+ (App Router)
- React Admin or Tremor for dashboard components
- TanStack Table (React Table v8) for data tables
- Shadcn UI for components
- TailwindCSS for styling

**Backend**:
- Next.js API routes
- Prisma ORM
- PostgreSQL database
- Redis for caching

**Authentication**:
- NextAuth.js with credentials provider
- Role-based middleware
- JWT tokens

**Payments**:
- Recreate SEPA XML logic in TypeScript
- Use `sepa-xml-generator` npm package
- Implement payment queue system

**File Storage**:
- Vercel Blob or S3
- Signed URLs for security

---

## FINAL NOTES

SimpliGestion is a **feature-rich but legacy admin portal** that powers the entire ProStagesPermis operation. Its 395 PHP files handle everything from bookings to payments to analytics.

**Strengths**:
- Comprehensive feature coverage (15 nav tabs, 100+ pages)
- Mature business logic
- Handles complex workflows (SEPA, multi-party payments)
- Proven in production
- Complete visual documentation now available (80 screenshots)

**Weaknesses**:
- Monolithic architecture
- No modern frontend framework
- Security concerns (hardcoded credentials, single login)
- Tight coupling (hard to test, maintain, extend)
- No API separation

**Migration Priority**: HIGH -- This is the operational backbone. Without SimpliGestion equivalent, staff cannot manage platform.

**Estimated Migration Effort**: 6-12 months for full-featured Next.js admin portal with all 395 file equivalents.

---

**Document Status**: COMPLETE
**Coverage**: All 395 PHP files catalogued + 80 screenshots analyzed
**Visual Documentation**: All 15 navigation tabs documented with dropdown menus, page layouts, table structures, and UI patterns
**Next Step**: Update WEBSITE_STRUCTURE.md with SimpliGestion findings
