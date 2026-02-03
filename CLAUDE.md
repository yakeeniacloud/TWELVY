# TWELVY Project Documentation

## Project Overview

**TWELVY** is a production-ready driving license points recovery course booking platform, live at **www.twelvy.net**.

### Current Status (February 2025)
- **Performance**: Desktop 100, Mobile 96 (PageSpeed Insights)
- **Production URL**: https://www.twelvy.net
- **Headless CMS**: https://headless.twelvy.net (WordPress)
- **Architecture**: Vercel (Next.js 15) + OVH (PHP API + MySQL) + WordPress Headless
- **Language**: French
- **Target Audience**: French drivers seeking points recovery courses

### Core Technology Stack
- **Frontend**: Next.js 15 with App Router (TypeScript)
- **Hosting**: Vercel (auto-deploy from main branch)
- **Backend API**: PHP on OVH shared hosting
- **Database**: MySQL on OVH
- **CMS**: WordPress Headless (headless.twelvy.net)
- **Styling**: Tailwind CSS
- **Maps**: Google Maps JavaScript API

---

## Architecture Overview

### Data Flow Architecture

```
User Browser
    ↓
Next.js Frontend (Vercel)
    ↓
├── /api/stages/* → OVH PHP API → MySQL Database
├── /api/wordpress/* → WordPress REST API (headless.twelvy.net)
└── /api/city-content/* → WordPress Proxy (bypass CORS)
```

### Key Components

1. **Next.js Application** (Vercel)
   - Client-side rendered pages for interactivity
   - Server-side API routes for proxying
   - Static assets and optimized images
   - Real-time search and filtering

2. **OVH PHP API** (o2switch shared hosting)
   - Endpoint: `https://www.digitalwebsuccess.com/api/stages.php`
   - Handles stage course queries
   - MySQL database connection
   - Returns JSON responses
   - Supports city, postal code, date filtering

3. **WordPress Headless CMS**
   - Endpoint: `https://headless.twelvy.net/wp-json/wp/v2`
   - Manages navigation menu structure
   - Stores page content (parent/child hierarchy)
   - City-specific content pages
   - No theme/frontend rendering

4. **MySQL Database** (OVH)
   - Table: `stages_recuperation_points`
   - 2,239+ stage courses across France
   - Real GPS coordinates (latitude/longitude)
   - Updated regularly with new courses

---

## Complete Feature Documentation

### 1. Homepage Search System

**File**: `app/page.tsx`

**Features**:
- Full-width hero section with gradient background
- WordPress-driven headline and subtitle
- City autocomplete search bar
- Responsive design (mobile/desktop)
- `[SEARCH_BAR]` delimiter splits WordPress content

**Implementation**:
```typescript
// WordPress content split at [SEARCH_BAR]
const parts = pageContent.split('[SEARCH_BAR]')
const aboveSearchBar = parts[0] // Headline/subtitle
const belowSearchBar = parts[1] // Full page description
```

**Search Bar Component**: `components/stages/CitySearchBar.tsx`
- Real-time autocomplete from 2,239 cities
- Keyboard navigation (Arrow Up/Down, Enter, Escape)
- Click outside to close dropdown
- Two variants: `large` (homepage), `small` (sidebar)

---

### 2. City Autocomplete System

**Hook**: `lib/useCities.ts`

**Data Source**: WordPress REST API
- Endpoint: `/wp-json/wp/v2/stages-cities`
- Returns 2,239 unique French cities
- Normalized uppercase format (e.g., "MARSEILLE", "AIX-EN-PROVENCE")

**Features**:
- Fuzzy search matching
- Accent-insensitive search (Marseille = marseille)
- Keyboard navigation support
- Auto-highlight on arrow keys
- Enter to select

**Performance**:
- Cities cached in component state
- No re-fetch on every keystroke
- Filters client-side for instant results

---

### 3. Results Page with Pagination

**File**: `app/stages-recuperation-points/[slug]/page.tsx`

**URL Pattern**: `/stages-recuperation-points/marseille`

**Critical Feature**: **Pagination (20 stages per page)**
- Implemented to solve DOM explosion on Paris (215 stages)
- Previous issue: 3,866 DOM elements → TBT 670ms mobile
- Solution: 20 stages per page → Desktop 100, Mobile 96

**Pagination Implementation**:
```typescript
// State management
const [currentPage, setCurrentPage] = useState(1)
const STAGES_PER_PAGE = 20

// Calculate visible stages
const totalPages = Math.ceil(stages.length / STAGES_PER_PAGE)
const startIndex = (currentPage - 1) * STAGES_PER_PAGE
const endIndex = startIndex + STAGES_PER_PAGE
const paginatedStages = stages.slice(startIndex, endIndex)

// Auto-scroll to top on page change
useEffect(() => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}, [currentPage])

// Reset to page 1 when filters change
useEffect(() => {
  setCurrentPage(1)
}, [sortBy, selectedCities, nearbyCities])
```

**Pagination UI**:
- Prev/Next buttons with disabled states
- Page counter: "Page 1 sur 11"
- Results summary: "Affichage de 1-20 sur 215 résultats"
- Smooth scroll to top on page change

**Layout**: 3-column responsive design
- Left sidebar: Filters (230px)
- Center: Results with pagination (flexible)
- Right sidebar: Engagements (260px)

**Filtering Options**:
- City (multi-select checkboxes)
- Date range (calendar picker)
- Proximity (nearby cities within 50km)
- Sort by: Date, Price

**Stage Card Design**:
- Compact 84px height
- Red accent vertical block (56×56px)
- City name (uppercase blue link)
- Address (grey truncated)
- Date display (French format: "Ven 24 et Sam 25 Octobre")
- Price (large bold: "219 €")
- Two action buttons:
  - "Plus d'infos" → Opens modal popup
  - "Sélectionner" → Navigates to inscription form

---

### 4. Stage Details Modal

**Component**: `components/stages/StageDetailsModal.tsx`

**Trigger**: Click "Plus d'infos" on any stage card

**Layout**: 3-column popup modal
1. **Left Column**: Google Maps embed with marker
2. **Center Column**: Stage details
   - Location name
   - Full address
   - Dates (2-day course)
   - Schedule (9h00-17h00)
   - Prefecture agreement info
3. **Right Column**: Price and CTA
   - Price breakdown
   - Guarantees included
   - "Sélectionner ce stage" button (green gradient)

**Functional Tabs** (below map):
- **Le prix du stage comprend**: Inclusions checklist
- **Programme**: 2-day detailed schedule
- **Agrément**: Prefecture agreement number
- **Accès - Parking**: Location access info
- **Paiement et conditions**: Payment methods, cancellation policy
- **Avis**: Customer reviews (empty state)

**Features**:
- Escape key to close
- Click outside to close
- Smooth animations (fadeIn/slideUp)
- Mobile responsive (full screen on mobile)

---

### 5. Inscription Form System

**File**: `app/stages-recuperation-points/[city]/[id]/inscription/page.tsx`

**URL Pattern**: `/stages-recuperation-points/marseille/abc123-def-456/inscription`

**4-Step Progress Indicator**:
1. **Formulaire** (active) ✓
2. Règlement (inactive)
3. Personnalisation (inactive)
4. Confirmation (inactive)

**Form Fields**:
- **Civilité**: Dropdown (Monsieur/Madame)
- **Nom**: Text input (required)
- **Prénom**: Text input (required)
- **Date de naissance**: 3 dropdowns (Jour/Mois/Année)
- **Adresse**: Text input (required)
- **Code Postal**: Text input (required)
- **Ville**: Text input (required)
- **Email**: Email input (required)
- **Confirmation email**: Email input (required, must match)
- **Téléphone mobile**: Tel input (required)
- **Garantie Sérénité**: Checkbox (optional +25€)
- **CGV**: Checkbox (required)

**Validation**:
- Email match validation
- Required CGV checkbox
- All required fields enforced by HTML5
- Disabled submit button while submitting

**Submission Flow**:
```typescript
1. Validate emails match
2. Validate CGV accepted
3. Construct date_naissance from dropdowns
4. Insert into OVH MySQL stage_bookings table
5. Auto-generate booking_reference via trigger
6. Redirect to /merci page with reference
```

**Database Table**: `stage_bookings` (OVH MySQL)
```sql
CREATE TABLE stage_bookings (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  stage_id UUID NOT NULL REFERENCES stages_recuperation_points(id),
  booking_reference TEXT UNIQUE NOT NULL,  -- BK-YYYY-NNNNNN
  civilite TEXT NOT NULL CHECK (civilite IN ('M', 'Mme')),
  nom TEXT NOT NULL,
  prenom TEXT NOT NULL,
  date_naissance DATE NOT NULL,
  adresse TEXT NOT NULL,
  code_postal TEXT NOT NULL,
  ville TEXT NOT NULL,
  email TEXT NOT NULL,
  email_confirmation TEXT NOT NULL,
  telephone_mobile TEXT NOT NULL,
  guarantee_serenite BOOLEAN DEFAULT false,
  cgv_accepted BOOLEAN NOT NULL DEFAULT true,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);
```

#### Mobile Fiche Formulaire (February 2025 Update)

**File**: `app/stages-recuperation-points/[slug]/[id]/inscription/page.tsx`

**Purpose**: Optimized mobile view for the inscription/booking form with enhanced UX

**Mobile Stage Card** (id="mobile-stage-card"):
- Grey widget with "Stage du [DATE]" (17px, centered, nowrap)
- Widget dimensions: 337px × 38px with 8px padding and 8px border-radius
- Background: #EFEFEF
- "+ 4 points en 48h" badge (15px font)
- Price display: 21px font, centered
- "Places disponibles" in green (#15803d), 14px font
- Grey separator line: 218px width, 1px height, #D9D9D9

**Icons and Details Row**:
- "Changer de date" link with calendar icon (14px text)
- Address with location icon (14px text)
- Time display with clock icon (14px text)
- French flag icon: 20×15px

**Agrément Section**:
- Prefecture agreement number (12px text)
- Spacing: 16px margin-top to benefits box

**Benefits Box**:
- Width: 363px, centered
- Icons: 28×28px (remboursement, paiement, satisfaction)
- Text: 14px font size
- Grey separator line: 363px width, centered
- Spacing: 16px margin-bottom after separator

**Form Section** (id="mobile-form-section"):
- "Étape 1/2" header: 18px font, centered
- Form labels: 14px font size
- Civilité select: white background (#FFFFFF)
- Field spacing: standard Tailwind gaps
- Garantie Sérénité checkbox content: 14px
- CGV checkbox text: 14px
- Green submit button: reduced size with 14px text
- Grey separator below button: 363px width, 16px margins

**Form Summary (Confirmed State)**:
- Name/Email/Tel display: 16px font
- "Modifier" button: 16px text, centered
- Grey separator: 363px width, 16px vertical margins

**Payment Section** (id="mobile-payment-section"):
- "Étape 2/2" header: 18px font, centered
- Card input labels: 14px font
- Card icons (Visa, Mastercard, etc.): h-8 (32px height)
- Grey price summary box content: 14px font
- "Après avoir cliqué" text with grey separator below
- "Payer" button: green gradient, centered

**Informations Pratiques Section**:
- Tab buttons: increased by 10%
- Tab content text: 14px
- "Pour en savoir plus" link: 14px
- Grey separator: 363px width

**Questions Fréquentes (FAQ)**:
- Section headings: centered text
- Question/answer text: 14px font
- Expandable accordion style

**Key Styling Patterns**:
```typescript
// Grey widget for stage date
<div style={{
  display: 'flex',
  width: '337px',
  height: '38px',
  padding: '8px 20px',
  justifyContent: 'center',
  alignItems: 'center',
  borderRadius: '8px',
  background: '#EFEFEF'
}}>

// Separator lines
<div style={{
  width: '363px',
  height: '1px',
  background: '#D9D9D9',
  marginTop: '16px',
  marginBottom: '16px'
}} />

// Benefits box icons
<img src="/icon.svg" alt="Icon" style={{ width: '28px', height: '28px' }} />
```

#### Mobile "Changer de Date" Popup Optimization (February 2025)

**File**: `app/stages-recuperation-points/[slug]/[id]/inscription/page.tsx`

**Purpose**: Optimized mobile date selection popup to maximize visible courses without scrolling

**Key Optimizations**:

1. **Top Section Space Reduction**:
   - "Choisissez une autre date..." text: Reduced from 15px to 12.5px (~17% reduction)
   - LineHeight: 22px → 18px
   - Current stage date: Removed grey background (#F5F5F5), reduced font 17px → 15px
   - Added `whiteSpace: 'nowrap'` to force single line display
   - Spacing reduction: date to "Liste des stages" (24px → 12px), added 4px padding-top to list

2. **Stage Cards Styling** (matching ville page mobile cards):
   - City names: Capitalize format (e.g., "Marseille" not "MARSEILLE")
   - Price: Reduced from 20px to 17px
   - Green button: Width 109px → 93px, font 15px → 12.5px, padding 7px → 6px
   - Card height: Reduced from 106px to 95px (~10% reduction)
   - Selected stage background: Changed from #F2DDDD to #F5EBE0 (light beige)
   - Selected stage border: Changed from #BC4747 to #BBB (grey)

3. **Bottom Container Optimization**:
   - "Fermer" button: Replaced grey button with underlined black text (centered)
   - Container padding: Reduced to 2px top/bottom (minimal spacing)

**Result**: Maximum courses visible without scrolling, matches ville page card styling

#### Mobile "Modifier" Form Matching (February 2025)

**File**: `app/stages-recuperation-points/[slug]/[id]/inscription/page.tsx`

**Purpose**: Ensure Modifier form matches original form styling when reopened

**When Modifier form reopens after validation, all styling matches initial form**:

**Font Sizes**:
- Field labels: 14px (Civilité, Nom, Prénom, Email, Téléphone mobile)
- Garantie Sérénité checkbox text: 13px
- Garantie Sérénité detail link: 13px
- CGV text: 13px
- Green button text: 14.4px (+20% from 12px)

**Spacing**:
- Téléphone → Garantie Sérénité: 24px marginTop
- Garantie Sérénité → CGV: 24px marginTop
- CGV → Buttons container: 36px marginTop

**Buttons**:
- "Annuler": Underlined black text (centered), no grey button background
- "Valider le formulaire et repasser au paiement": Green background, 14.4px font

**Consistency**: Modifier form now perfectly matches initial form appearance

#### Mobile Section Spacing Standardization (February 2025)

**File**: `app/stages-recuperation-points/[slug]/[id]/inscription/page.tsx`

**Purpose**: Consistent spacing with centered grey separator lines between all major sections

**Standard Spacing Pattern**:
```typescript
<div className="mx-auto" style={{
  width: '363px',
  height: '1px',
  background: '#D9D9D9',
  marginTop: '36px',
  marginBottom: '36px'
}} />
```

**Applied to Three Locations**:

1. **Stage Details → Étape 1/2 Form**:
   - Grey line with 36px above/below (reference spacing)

2. **Form/Green Button → Étape 2/2 Payment**:
   - Grey line with 36px above/below (NEW - added Feb 2025)
   - Only visible when `paymentBlockVisible` is true

3. **Payment Section → Informations Pratiques**:
   - Grey line with 36px above/below (UPDATED from 16px)

**Result**: All major sections have consistent, balanced spacing with perfectly centered grey separator lines

#### Desktop Form Page Visibility (February 2025)

**File**: `app/stages-recuperation-points/[slug]/[id]/inscription/page.tsx`

**Purpose**: Fix initial page load visibility for desktop inscription form - "Informations pratiques" and FAQ sections should be visible immediately, not hidden until payment step

**Problem (Before Fix)**:
- When landing on desktop form page, only the form was visible
- "Informations pratiques sur votre stage" section was hidden
- "Questions fréquentes" FAQ section was hidden
- Both sections were incorrectly wrapped inside `{currentStep === 2 && (` conditional
- Users could only see these sections after submitting the form

**Solution (After Fix)**:
- Moved "Informations pratiques" section outside `currentStep === 2` conditional
- Moved "Questions fréquentes" section outside `currentStep === 2` conditional
- Both sections now appear immediately when user lands on the page (Step 1)
- Sections remain visible after form submission (Step 2)
- Only the payment section (credit card form) is hidden until form submission

**Code Structure**:
```typescript
// Step 1 vs Step 2 ternary (form vs summary)
{currentStep === 1 ? (
  <form>...</form>
) : (
  <div>Vos coordonnées summary + Modifier button</div>
)}

// Informations pratiques - ALWAYS VISIBLE
<div>
  <h2>Informations pratiques sur votre stage</h2>
  <div>Tabs: Le prix du stage comprend | Programme | Agrément</div>
</div>

// Questions fréquentes - ALWAYS VISIBLE
<div>
  <h2>Questions Fréquentes</h2>
  <div>FAQ accordion with 3 questions</div>
</div>

// Payment section - ONLY VISIBLE when currentStep === 2
{currentStep === 2 && (
  <>
    <div>Separator line</div>
    <div>Étape 2/2 - paiement sécurisé</div>
    <form>Credit card fields...</form>
  </>
)}
```

**Visibility Logic**:
| Section | currentStep === 1 (Initial) | currentStep === 2 (After submit) |
|---------|----------------------------|----------------------------------|
| Form | ✅ Visible | ❌ Hidden (shows summary instead) |
| Informations pratiques | ✅ Visible | ✅ Visible |
| Questions fréquentes | ✅ Visible | ✅ Visible |
| Payment section | ❌ Hidden | ✅ Visible |

**Result**: Users now see helpful information about the stage and FAQs while filling out the form, improving UX and reducing confusion

---

### 6. Booking Confirmation Page

**File**: `app/stages-recuperation-points/[city]/[id]/merci/page.tsx`

**URL Pattern**: `/stages-recuperation-points/marseille/abc123-def-456/merci?ref=BK-2025-NNNNNN`

**Features**:
- Personalized greeting: "Merci [Prénom] [Nom]!"
- Success icon (green checkmark)
- Booking reference display (large, copyable)
- Full stage details recap:
  - Dates with French day/month names
  - Location with complete address
  - Price with guarantee if selected
- Email confirmation notice
- Important information section:
  - Must attend both days
  - Bring ID and driving license
  - Arrive 15 minutes early
  - Free cancellation up to 14 days
- Action buttons:
  - Return to homepage (blue)
  - Print confirmation (green)

**Print Functionality**:
```typescript
const handlePrint = () => {
  window.print()
}
```

**CSS for Print**:
```css
@media print {
  .no-print { display: none; }
  .print-only { display: block; }
}
```

---

### 7. WordPress Headless Navigation System

**Critical Feature**: WordPress-driven navigation with parent/child hierarchy

**Architecture**:
```
WordPress Admin (headless.twelvy.net)
    ↓
Create Parent Page: "LES STAGES PERMIS À POINTS"
    ↓
Create Child Pages: "Stages obligatoires", "Stages volontaires", etc.
    ↓
Next.js API Route (/api/wordpress/menu)
    ↓
Fetch pages via REST API
    ↓
Filter out city pages (stages-marseille, stages-paris)
    ↓
Build hierarchical menu structure
    ↓
Header component with mega menu dropdowns
```

**API Route**: `app/api/wordpress/menu/route.ts`

**Key Logic - Filter Bug Fix**:
```typescript
// CRITICAL: Check parent status FIRST
const filteredPages = pages.filter(page => {
  if (page.slug === 'homepage') return false

  // KEEP all pages that have a parent (child pages)
  if (page.parent !== 0) return true  // ← CRITICAL LINE

  // For parent pages (parent = 0), filter out city-specific stage pages
  const isCityStagesPage = page.slug.match(/^stages-[a-z]+-\d+/)  // stages-paris-75001
    || (page.slug.startsWith('stages-') && page.slug.split('-').length === 2)  // stages-marseille

  return !isCityStagesPage
})
```

**Why This Order Matters**:
- Child page "stages-obligatoires" has slug pattern `stages-{word}`
- City pages also have pattern `stages-{city}` (e.g., stages-marseille)
- If we filter by slug FIRST, we lose child pages
- Solution: **Check `parent !== 0` FIRST**, then filter only parent pages

**Hook**: `lib/useWordPressMenu.ts`
- Fetches menu structure from `/api/wordpress/menu`
- 30-second polling for real-time updates
- Returns hierarchical structure with children

**Header Component**: `components/layout/Header.tsx`

**Two-Bar Navigation**:
1. **Top thin bar** (40px, white background):
   - Left: TWELVY logo
   - Center: "STAGE DE RÉCUPÉRATION DE POINTS"
   - Right: User icon + "Espace Client" link

2. **Main dark nav** (56px, #222222 background):
   - Left: WordPress dynamic menu items
   - Dropdown arrows for parents with children
   - 4-column mega menu on hover
   - Right: "AIDE ET CONTACT" button (blue #2b85c9)

**Mega Menu Layout**:
```typescript
// Split children into 4 columns
const getMenuColumns = (children: any[]) => {
  const columns: any[][] = [[], [], [], []]
  children.forEach((child, index) => {
    columns[index % 4].push(child)
  })
  return columns.filter(col => col.length > 0)
}
```

**Dropdown Behavior**:
- Click parent item to toggle dropdown
- Click outside to close
- Escape key to close
- One dropdown open at a time
- Dropdown shows border-top-2 border-red-500

---

### 8. Dynamic WordPress Page Routing

**File**: `app/[slug]/page.tsx`

**URL Pattern**: `/{slug}` (e.g., `/les-stages-permis-a-points`, `/stages-obligatoires`)

**Two Display Modes**:

**Mode 1: Parent Page with Children**
- Displays page title with red bottom border
- Shows WordPress content (if any)
- Grid of child pages (2 columns)
- Each child card:
  - Title (hover → red color)
  - "Lire l'article" link with arrow
  - Border hover effect (gray → red)
  - Shadow on hover

**Mode 2: Regular Page (No Children)**
- Full-width content display
- Prose styling (Tailwind typography)
- Headings, paragraphs, lists, links
- Images from WordPress

**Logic**:
```typescript
// Find if this page is a parent with children
const parentPage = menu.find(item => item.slug === slug)
const hasChildren = parentPage && parentPage.children.length > 0

if (hasChildren && parentPage) {
  // Show child grid
} else {
  // Show normal content
}
```

**Content Hook**: `lib/useWordPressContent.ts`
- Fetches page by slug from WordPress API
- Graceful 404 handling (expected for pages without custom content)
- Returns: `{ content, title, loading, error }`

---

### 9. City-Specific WordPress Content

**Pattern**: Pages with slug `stages-{city}` (e.g., `stages-marseille`)

**Purpose**: Display custom city-specific content BELOW stage listings

**Implementation**:
- WordPress page created: "Stages Marseille" → slug: `stages-marseille`
- Content added in WordPress editor (WYSIWYG)
- API proxy route: `/api/city-content/[city]/route.ts`
- Displays in gray section below StageCard listings

**Mixed Content Fix** (Critical):
- **Problem**: WordPress on HTTP, Next.js on HTTPS
- **Browser**: Blocks HTTP requests from HTTPS pages (security)
- **Solution**: Server-side API proxy

**Proxy Flow**:
```
Browser (HTTPS) → /api/city-content/marseille (HTTPS, same origin ✅)
    ↓
Next.js Server → http://headless.twelvy.net/wp-json/wp/v2/pages?slug=stages-marseille
    ↓
WordPress (HTTP) → Returns content ✅
    ↓
Next.js Server → Browser (HTTPS) ✅
```

**API Route**: `app/api/city-content/[city]/route.ts`
```typescript
export async function GET(
  request: Request,
  { params }: { params: Promise<{ city: string }> }
) {
  const { city } = await params
  const slug = `stages-${city.toLowerCase()}`

  const response = await fetch(
    `https://headless.twelvy.net/wp-json/wp/v2/pages?slug=${slug}`,
    { next: { revalidate: 30 } }
  )

  if (!response.ok) {
    return NextResponse.json({ content: null })
  }

  const pages = await response.json()
  if (!pages || pages.length === 0) {
    return NextResponse.json({ content: null })
  }

  return NextResponse.json({
    content: pages[0].content.rendered,
    title: pages[0].title.rendered,
  })
}
```

---

## Performance Optimization Journey

### Initial Baseline (Before Optimizations)
- **Desktop**: 89 (Good)
- **Mobile**: 93 (Good)

### PageSpeed Issues Identified (Session 1)

**Issue 1: Console Errors (404s)**
- **Cause**: WordPress content requests for cities without custom pages
- **Impact**: Reduced Best Practices score
- **Fix**: Graceful 404 handling in `useWordPressContent.ts`
```typescript
if (response.status === 404) {
  setContent(null)  // Expected, not an error
  setLoading(false)
  return
}
```

**Issue 2: Wrong Heading Hierarchy**
- **Cause**: Using `<h3>` for city names instead of `<h2>`
- **Impact**: Accessibility score reduction
- **Fix**: Changed to semantic `<h2>` tags
```typescript
// BEFORE
<h3 className="text-lg font-semibold">{city}</h3>

// AFTER
<h2 className="text-lg font-semibold">{city}</h2>
```

**Issue 3: Identical Button Text**
- **Cause**: Multiple "Sélectionner" buttons without unique labels
- **Impact**: Accessibility (screen readers can't differentiate)
- **Fix**: Added unique `aria-label` attributes
```typescript
<button
  aria-label={`Sélectionner le stage à ${stage.city} le ${formatDate(stage.date_start)}`}
>
  Sélectionner
</button>
```

**Issue 4: Missing Landmark**
- **Cause**: No `<main>` element in page structure
- **Impact**: Accessibility score
- **Fix**: Wrapped page content in `<main>` tag
```typescript
<main className="min-h-screen bg-white">
  {/* Page content */}
</main>
```

**Issue 5: Production Console.logs**
- **Cause**: 24 debug console.log statements in production code
- **Impact**: Best Practices score
- **Fix**: Removed all using sed command
```bash
find app lib components -name "*.tsx" -o -name "*.ts" | \
  xargs sed -i '' '/console\.log/d'
```

**Issue 6: Missing robots.txt**
- **Cause**: No robots.txt file for SEO
- **Impact**: SEO score
- **Fix**: Created `public/robots.txt`
```txt
User-agent: *
Allow: /

Sitemap: https://www.twelvy.net/sitemap.xml
```

**Results After Session 1 Fixes**:
- Desktop: 89 (maintained)
- Mobile: 93 (maintained)

---

### Performance Regression (Session 2)

**The Disaster**:
- **Desktop**: 89 → 76 (-13 points)
- **Mobile**: 93 → 84 (-9 points)

**Root Cause Analysis**:
- Previous tests: Small cities (Marseille ~15-30 stages)
- New test: **PARIS with 215 stages**
- DOM explosion: **3,866 elements** (215 stages × ~18 elements each)
- **Total Blocking Time (TBT)**: 490ms desktop, 670ms mobile
- Largest Contentful Paint: Delayed by heavy DOM

**Performance Metrics**:
```
Desktop (Paris 215 stages):
- FCP: 0.6s (Good)
- LCP: 2.7s (Needs Improvement) ← DOM size impact
- TBT: 490ms (Needs Improvement) ← Main bottleneck
- CLS: 0.001 (Good)
- Speed Index: 1.4s (Good)

Mobile (Paris 215 stages):
- FCP: 1.9s (Good)
- LCP: 4.2s (Needs Improvement) ← DOM size impact
- TBT: 670ms (Poor) ← Main bottleneck
- CLS: 0.001 (Good)
- Speed Index: 3.5s (Needs Improvement)
```

**Why Paris Broke Performance**:
1. Rendering 215 StageCard components at once
2. Each card: ~18 DOM elements (div, h2, p, button, svg, etc.)
3. Total: 215 × 18 = 3,870 elements
4. Browser: Must calculate layout for all elements
5. Result: TBT skyrockets to 670ms

---

### Pagination Solution (Session 2)

**Implementation**: Show 20 stages per page instead of all

**Benefits**:
- DOM elements: 3,866 → 360 (90% reduction)
- TBT: 670ms → ~100ms (85% reduction)
- Initial render: Much faster
- User experience: Cleaner, less overwhelming

**Code Changes**: `app/stages-recuperation-points/[slug]/page.tsx`

**State Management**:
```typescript
const [currentPage, setCurrentPage] = useState(1)
const STAGES_PER_PAGE = 20

// Calculate pagination
const totalPages = Math.ceil(stages.length / STAGES_PER_PAGE)
const startIndex = (currentPage - 1) * STAGES_PER_PAGE
const endIndex = startIndex + STAGES_PER_PAGE
const paginatedStages = stages.slice(startIndex, endIndex)
```

**UX Enhancements**:
1. **Auto-scroll to top on page change**:
```typescript
useEffect(() => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}, [currentPage])
```

2. **Reset to page 1 when filters change**:
```typescript
useEffect(() => {
  setCurrentPage(1)
}, [sortBy, selectedCities, nearbyCities])
```

3. **Results summary**:
```typescript
<p className="text-gray-600">
  Affichage de {startIndex + 1}-{Math.min(endIndex, stages.length)} sur {stages.length} résultats
</p>
```

4. **Pagination controls**:
```typescript
<div className="flex items-center justify-center gap-4 mt-8">
  <button
    onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
    disabled={currentPage === 1}
    className="px-4 py-2 bg-blue-600 text-white rounded disabled:bg-gray-300"
  >
    ← Précédent
  </button>
  <span className="text-gray-700 font-medium">
    Page {currentPage} sur {totalPages}
  </span>
  <button
    onClick={() => setCurrentPage(prev => Math.min(prev + 1, totalPages))}
    disabled={currentPage === totalPages}
    className="px-4 py-2 bg-blue-600 text-white rounded"
  >
    Suivant →
  </button>
</div>
```

**Results After Pagination**:
- **Desktop**: 76 → **100** (+24 points) ✓
- **Mobile**: 84 → **96** (+12 points) ✓

**Performance Metrics After**:
```
Desktop (Paris 215 stages with pagination):
- FCP: 0.5s (Good) ✓
- LCP: 0.9s (Good) ✓
- TBT: 10ms (Good) ✓
- CLS: 0 (Good) ✓
- Speed Index: 0.9s (Good) ✓
- Score: 100/100 ✓

Mobile (Paris 215 stages with pagination):
- FCP: 1.7s (Good) ✓
- LCP: 2.3s (Good) ✓
- TBT: 80ms (Good) ✓
- CLS: 0 (Good) ✓
- Speed Index: 2.5s (Good) ✓
- Score: 96/100 ✓
```

---

## WordPress Integration Deep Dive

### WordPress Setup (headless.twelvy.net)

**Purpose**: Headless CMS for navigation and content management

**Configuration**:
1. **Permalinks**: Post name structure (enables REST API)
2. **Search Engine Visibility**: Discouraged (headless, not public)
3. **REST API**: Enabled by default
4. **CORS**: No restrictions needed (same domain proxy)

**Content Structure**:
```
Pages Hierarchy:
├── Homepage (excluded from menu)
├── LES STAGES PERMIS À POINTS (parent, ID: 12)
│   ├── Stages obligatoires (child, ID: 15, parent: 12)
│   ├── Stages volontaires (child, ID: 16, parent: 12)
│   └── Stage de sensibilisation (child, ID: 17, parent: 12)
├── INFORMATIONS PRATIQUES (parent, ID: 20)
│   ├── Comment s'inscrire (child, ID: 21, parent: 20)
│   └── Règlement et annulation (child, ID: 22, parent: 20)
└── stages-marseille (city content, excluded from menu)
└── stages-paris (city content, excluded from menu)
```

**REST API Endpoints Used**:
```
GET /wp-json/wp/v2/pages?per_page=100&status=publish&orderby=menu_order&order=asc
→ Returns all published pages with parent/child relationships

GET /wp-json/wp/v2/pages?slug=stages-marseille
→ Returns city-specific content page

GET /wp-json/wp/v2/stages-cities
→ Returns 2,239 unique French cities for autocomplete
```

---

### Filter Logic Bug (Critical Debugging Session)

**The Problem**: Child page "stages-obligatoires" not appearing in dropdown menu

**Debugging Timeline**:

**1. Initial Discovery**:
- Vercel logs showed parent page ID 12 with empty children array: `"children": []`
- Expected to see child ID 15 ("stages-obligatoires")

**2. First Hypothesis**: WordPress not returning child page
- Created test endpoint: `/api/test-wp/route.ts`
- Result: WordPress WAS returning page ID 15 with `parent: 12` ✓
- Conclusion: Filter logic was removing it ✗

**3. Filter Evolution**:

**Attempt 1 - Too Aggressive**:
```typescript
const filteredPages = pages.filter(page => {
  if (page.slug === 'homepage') return false
  if (page.slug.startsWith('stages-')) return false  // ← WRONG
  return true
})
```
**Problem**: Removed ALL pages starting with "stages-", including:
- stages-marseille (city pages) ✓ Wanted
- stages-obligatoires (child page) ✗ Unwanted removal

**Attempt 2 - Regex Pattern Matching**:
```typescript
const isCityStagesPage = page.slug.match(/^stages-[a-z]+-\d+/)  // stages-paris-75001
  || (page.slug.startsWith('stages-') && page.slug.split('-').length === 2 && page.slug.split('-')[1].match(/^[a-z]+$/))
```
**Problem**: `stages-obligatoires` splits to `['stages', 'obligatoires']`:
- Length: 2 ✓ (matches condition)
- Second part: 'obligatoires' (matches `^[a-z]+$`)
- Result: Page filtered out ✗ → FILTERED OUT ❌

**Attempt 3 - Final Solution**:
```typescript
const filteredPages = pages.filter(page => {
  if (page.slug === 'homepage') return false

  // CRITICAL: Check parent status FIRST
  if (page.parent !== 0) return true  // ← Keep ALL child pages

  // Only filter parent pages for city patterns
  const isCityStagesPage = page.slug.match(/^stages-[a-z]+-\d+/)
    || (page.slug.startsWith('stages-') && page.slug.split('-').length === 2)

  return !isCityStagesPage
})
```

**Why This Works**:
1. Check `parent !== 0` FIRST → All child pages kept regardless of slug
2. Only apply city filter to parent pages (`parent === 0`)
3. City pages filtered: stages-marseille, stages-paris, stages-paris-75001
4. Child pages kept: stages-obligatoires, stages-volontaires

**Key Insight**: Order of conditions matters when filtering hierarchical data

**Debug Logging Strategy**:
```typescript
console.log('📄 Raw WordPress pages:', pages.map(p => ({
  id: p.id,
  title: p.title.rendered,
  parent: p.parent
})))

console.log('🔍 Filtered pages:', filteredPages.map(p => ({
  id: p.id,
  title: p.title.rendered,
  parent: p.parent
})))

console.log(`👨‍👧 Parent "${parent.title.rendered}" (ID: ${parent.id}) has ${children.length} children:`, children)
```

**Emoji markers used**:
- 📄 Raw data from API
- 🔍 After filtering
- 👨‍👧 Parent/child relationships
- ✅ Final output

---

## Database Architecture

### MySQL Database (OVH)

**Table**: `stages_recuperation_points`

**Schema**:
```sql
CREATE TABLE stages_recuperation_points (
  id INT AUTO_INCREMENT PRIMARY KEY,
  city VARCHAR(255) NOT NULL,
  postal_code VARCHAR(10) NOT NULL,
  full_address TEXT NOT NULL,
  location_name VARCHAR(255),
  date_start DATE NOT NULL,
  date_end DATE NOT NULL,
  price DECIMAL(6,2) NOT NULL,
  latitude DECIMAL(10, 7) NOT NULL,
  longitude DECIMAL(10, 7) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_city (city),
  INDEX idx_postal_code (postal_code),
  INDEX idx_date_start (date_start),
  INDEX idx_price (price),
  INDEX idx_coordinates (latitude, longitude)
);
```

**Key Fields**:
- **city**: Uppercase normalized (e.g., "MARSEILLE", "AIX-EN-PROVENCE")
- **postal_code**: French format (e.g., "13000", "75001")
- **full_address**: Street address only (no city/postal)
- **location_name**: Venue name (optional)
- **date_start/date_end**: 2-day courses (usually Fri-Sat or Sat-Sun)
- **price**: Course price in euros (typically €199-€329)
- **latitude/longitude**: Real GPS coordinates for proximity filtering

**Data Volume**: 2,239+ stage courses across France

**City Distribution**:
- Paris: ~215 stages
- Marseille: ~30 stages
- Lyon: ~25 stages
- Toulouse, Bordeaux, Nice: ~15-20 each
- Smaller cities: 5-10 stages

**Date Range**: Rolling 6 months (continuously updated)

**Indexes for Performance**:
- City lookups (fast filtering)
- Postal code searches
- Date sorting
- Price sorting
- Geospatial queries (latitude/longitude)

---

### MySQL Database (OVH) - Bookings

**Table**: `stage_bookings`

**Purpose**: Store booking submissions from inscription form

**Schema**:
```sql
CREATE TABLE stage_bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  stage_id INT NOT NULL,
  booking_reference VARCHAR(20) UNIQUE NOT NULL,

  -- Personal info
  civilite VARCHAR(10) NOT NULL,
  nom VARCHAR(255) NOT NULL,
  prenom VARCHAR(255) NOT NULL,
  date_naissance DATE NOT NULL,

  -- Contact info
  adresse TEXT NOT NULL,
  code_postal VARCHAR(10) NOT NULL,
  ville VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  email_confirmation VARCHAR(255) NOT NULL,
  telephone_mobile VARCHAR(20) NOT NULL,

  -- Options
  guarantee_serenite BOOLEAN DEFAULT false,
  cgv_accepted BOOLEAN NOT NULL DEFAULT true,

  -- Timestamps
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  -- Foreign key
  FOREIGN KEY (stage_id) REFERENCES stages_recuperation_points(id),

  -- Indexes
  INDEX idx_stage_bookings_stage_id (stage_id),
  INDEX idx_stage_bookings_email (email),
  INDEX idx_stage_bookings_booking_ref (booking_reference),
  INDEX idx_stage_bookings_created_at (created_at)
);
```

**Booking Reference Format**: `BK-YYYY-NNNNNN`
- Example: `BK-2025-000001`
- Auto-generated via MySQL trigger or application logic

---

## API Routes Documentation

### 1. Stages Search API

**Route**: `app/api/stages/route.ts`

**Purpose**: Proxy to OVH PHP API for stage course queries

**Method**: GET

**Query Parameters**:
- `city`: City name (optional, normalized uppercase)
- `postalCode`: Postal code (optional)
- `date`: Start date filter (optional, YYYY-MM-DD)

**Example**:
```
GET /api/stages?city=MARSEILLE&postalCode=13001
```

**Response**:
```json
{
  "stages": [
    {
      "id": "123",
      "city": "MARSEILLE",
      "postal_code": "13001",
      "full_address": "156 rue de la république",
      "location_name": "Centre de Formation Auto-École Plus",
      "date_start": "2025-10-24",
      "date_end": "2025-10-25",
      "price": "219.00",
      "latitude": "43.296482",
      "longitude": "5.369780"
    }
  ],
  "total": 1
}
```

**Implementation**:
```typescript
export async function GET(request: Request) {
  const { searchParams } = new URL(request.url)
  const city = searchParams.get('city')
  const postalCode = searchParams.get('postalCode')
  const date = searchParams.get('date')

  // Build PHP API URL
  let apiUrl = 'https://www.digitalwebsuccess.com/api/stages.php?'
  if (city) apiUrl += `city=${encodeURIComponent(city)}&`
  if (postalCode) apiUrl += `postalCode=${encodeURIComponent(postalCode)}&`
  if (date) apiUrl += `date=${encodeURIComponent(date)}&`

  const response = await fetch(apiUrl, {
    headers: { 'Accept': 'application/json' },
    next: { revalidate: 30 }
  })

  if (!response.ok) {
    return NextResponse.json(
      { error: 'Failed to fetch stages' },
      { status: response.status }
    )
  }

  const data = await response.json()
  return NextResponse.json(data)
}
```

**Caching**: 30-second revalidation (Next.js ISR)

---

### 2. WordPress Menu API

**Route**: `app/api/wordpress/menu/route.ts`

**Purpose**: Fetch WordPress pages and build hierarchical menu structure

**Method**: GET

**Response**:
```json
{
  "menu": [
    {
      "id": 12,
      "title": "LES STAGES PERMIS À POINTS",
      "slug": "les-stages-permis-a-points",
      "children": [
        {
          "id": 15,
          "title": "Stages obligatoires",
          "slug": "stages-obligatoires"
        },
        {
          "id": 16,
          "title": "Stages volontaires",
          "slug": "stages-volontaires"
        }
      ]
    }
  ],
  "total": 1
}
```

**Implementation**: See "WordPress Integration" section above for full code

**Filtering Logic**:
1. Exclude homepage
2. Keep ALL child pages (parent !== 0)
3. Filter parent pages for city patterns
4. Build hierarchical structure

**Caching**: 30-second revalidation

---

### 3. City Content API

**Route**: `app/api/city-content/[city]/route.ts`

**Purpose**: Proxy WordPress city-specific content (bypasses Mixed Content security)

**Method**: GET

**Example**:
```
GET /api/city-content/marseille
```

**Response**:
```json
{
  "content": "<h2>Les lieux de stages à Marseille</h2><p>Marseille étant une grande ville...</p>",
  "title": "Stages Marseille"
}
```

**Implementation**:
```typescript
export async function GET(
  request: Request,
  { params }: { params: Promise<{ city: string }> }
) {
  const { city } = await params
  const slug = `stages-${city.toLowerCase()}`

  const response = await fetch(
    `https://headless.twelvy.net/wp-json/wp/v2/pages?slug=${slug}`,
    {
      headers: { 'Accept': 'application/json' },
      next: { revalidate: 30 }
    }
  )

  if (!response.ok || !response.json().length) {
    return NextResponse.json({ content: null })
  }

  const pages = await response.json()
  return NextResponse.json({
    content: pages[0].content.rendered,
    title: pages[0].title.rendered
  })
}
```

**Why Needed**: WordPress on HTTP, Next.js on HTTPS → Browser blocks Mixed Content

**Solution**: Server-side proxy (Next.js server can fetch HTTP)

---

### 4. Test WordPress API (Debug Tool)

**Route**: `app/api/test-wp/route.ts`

**Purpose**: Debug endpoint to inspect raw WordPress API response

**Method**: GET

**Response**:
```json
{
  "total": 15,
  "pages": [
    {
      "id": 15,
      "title": "Stages obligatoires",
      "slug": "stages-obligatoires",
      "parent": 12,
      "status": "publish"
    }
  ]
}
```

**Use Case**: Verify WordPress returning correct data when debugging filter issues

---

## React Hooks Documentation

### 1. useStages

**File**: `lib/useStages.ts`

**Purpose**: Fetch stage courses from API with filtering

**Parameters**:
- `city`: City name (optional)
- `postalCode`: Postal code (optional)
- `date`: Start date filter (optional)

**Returns**:
```typescript
{
  stages: Stage[]
  loading: boolean
  error: string | null
}
```

**Implementation**:
```typescript
export function useStages(city?: string, postalCode?: string, date?: string) {
  const [stages, setStages] = useState<Stage[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    async function fetchStages() {
      try {
        setLoading(true)
        setError(null)

        let url = '/api/stages?'
        if (city) url += `city=${encodeURIComponent(city)}&`
        if (postalCode) url += `postalCode=${encodeURIComponent(postalCode)}&`
        if (date) url += `date=${encodeURIComponent(date)}&`

        const response = await fetch(url)
        if (!response.ok) throw new Error('Failed to fetch stages')

        const data = await response.json()
        setStages(data.stages || [])
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Unknown error')
      } finally {
        setLoading(false)
      }
    }

    fetchStages()
  }, [city, postalCode, date])

  return { stages, loading, error }
}
```

---

### 2. useCities

**File**: `lib/useCities.ts`

**Purpose**: Fetch unique cities for autocomplete

**Returns**:
```typescript
{
  cities: string[]
  loading: boolean
  error: string | null
}
```

**Data Source**: WordPress REST API custom endpoint

**Implementation**:
```typescript
export function useCities() {
  const [cities, setCities] = useState<string[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    async function fetchCities() {
      try {
        setLoading(true)
        setError(null)

        const response = await fetch(
          'https://headless.twelvy.net/wp-json/wp/v2/stages-cities'
        )

        if (!response.ok) throw new Error('Failed to fetch cities')

        const data = await response.json()
        setCities(data.cities || [])
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Unknown error')
      } finally {
        setLoading(false)
      }
    }

    fetchCities()
  }, [])

  return { cities, loading, error }
}
```

**Cache**: Cities cached in component state, no re-fetch on every render

---

### 3. useWordPressMenu

**File**: `lib/useWordPressMenu.ts`

**Purpose**: Fetch WordPress menu structure with parent/child hierarchy

**Returns**:
```typescript
{
  menu: MenuItem[]
  loading: boolean
  error: string | null
}

interface MenuItem {
  id: number
  title: string
  slug: string
  children: {
    id: number
    title: string
    slug: string
  }[]
}
```

**Polling**: 30-second intervals for real-time updates

**Implementation**:
```typescript
export function useWordPressMenu() {
  const [menu, setMenu] = useState<MenuItem[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    async function fetchMenu() {
      try {
        setLoading(true)
        setError(null)

        const response = await fetch('/api/wordpress/menu')
        if (!response.ok) throw new Error('Failed to fetch menu')

        const data = await response.json()
        setMenu(data.menu || [])
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Unknown error')
      } finally {
        setLoading(false)
      }
    }

    fetchMenu()

    // Poll every 30 seconds for updates
    const interval = setInterval(fetchMenu, 30000)
    return () => clearInterval(interval)
  }, [])

  return { menu, loading, error }
}
```

---

### 4. useWordPressContent

**File**: `lib/useWordPressContent.ts`

**Purpose**: Fetch WordPress page content by slug

**Parameters**:
- `slug`: Page slug (e.g., "stages-obligatoires")

**Returns**:
```typescript
{
  content: string | null
  title: string | null
  loading: boolean
  error: string | null
}
```

**Graceful 404 Handling**:
```typescript
if (!response.ok) {
  // 404 is expected for pages without custom content
  if (response.status === 404) {
    setContent(null)
    setLoading(false)
    return
  }
  throw new Error('Failed to fetch WordPress content')
}
```

**Implementation**:
```typescript
export function useWordPressContent(slug: string) {
  const [content, setContent] = useState<string | null>(null)
  const [title, setTitle] = useState<string | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    async function fetchContent() {
      try {
        setLoading(true)
        setError(null)

        const response = await fetch(
          `https://headless.twelvy.net/wp-json/wp/v2/pages?slug=${slug}`
        )

        if (!response.ok) {
          if (response.status === 404) {
            setContent(null)
            setTitle(null)
            setLoading(false)
            return
          }
          throw new Error('Failed to fetch content')
        }

        const pages = await response.json()
        if (!pages || pages.length === 0) {
          setContent(null)
          setTitle(null)
        } else {
          setContent(pages[0].content.rendered)
          setTitle(pages[0].title.rendered)
        }
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Unknown error')
      } finally {
        setLoading(false)
      }
    }

    if (slug) fetchContent()
  }, [slug])

  return { content, title, loading, error }
}
```

---

## Component Library

### Stage Components

**1. CitySearchBar** (`components/stages/CitySearchBar.tsx`)
- Real-time autocomplete (2,239 cities)
- Keyboard navigation (↑↓ Enter Escape)
- Click outside to close
- Two variants: large (homepage), small (sidebar)

**2. StageCard** (`components/stages/StageCard.tsx`)
- Compact 84px height design
- Red accent block (56×56px)
- City/address display
- French date formatting
- Price display
- Two action buttons: "Plus d'infos", "Sélectionner"

**3. StageDetailsModal** (`components/stages/StageDetailsModal.tsx`)
- 3-column layout
- Google Maps embed
- Functional tabs
- Escape/click-outside to close
- Mobile responsive (full screen)

**4. FiltersSidebar** (`components/stages/FiltersSidebar.tsx`)
- City search (small variant)
- Sort options (Date, Price)
- Multi-select city checkboxes
- "Toutes les villes" select all

**5. EngagementsSidebar** (`components/stages/EngagementsSidebar.tsx`)
- 4 key benefits
- Expandable details
- Icon + text layout
- Right sidebar (260px)

### Layout Components

**1. Header** (`components/layout/Header.tsx`)
- Two-bar navigation
- WordPress-driven menu
- Mega menu dropdowns (4 columns)
- Mobile responsive
- Click outside / Escape to close

---

## Environment Variables

### Required Variables

**File**: `.env.local`

```bash
# Google Maps
NEXT_PUBLIC_GOOGLE_MAPS_API_KEY="your-google-maps-key"

# WordPress Headless (Optional if hardcoded)
NEXT_PUBLIC_WORDPRESS_API_URL="https://headless.twelvy.net/wp-json/wp/v2"

# OVH Database (if needed for direct connections)
# All database operations go through PHP API on OVH

# Production Domain
NEXT_PUBLIC_SITE_URL="https://www.twelvy.net"
```

### Vercel Production Variables

**Dashboard**: Vercel → Settings → Environment Variables

**Required**:
- `NEXT_PUBLIC_GOOGLE_MAPS_API_KEY`

**Optional**:
- `NEXT_PUBLIC_WORDPRESS_API_URL` (can be hardcoded in code)

---

## Build and Deployment

### Local Development

**Start dev server**:
```bash
cd /Users/yakeen/Desktop/TWELVY
npm run dev
```

**Access**: http://localhost:3000

### Production Build

**Build command**:
```bash
npm run build
```

**Output**:
```
Route                                          Size      First Load JS
┌ ○ /                                         3.06 kB    148 kB
├ ○ /_not-found                               0 B        0 B
├ ƒ /api/city-content/[city]                 0 B        0 B
├ ƒ /api/stages                              0 B        0 B
├ ƒ /api/test-wp                             0 B        0 B
├ ƒ /api/wordpress/menu                      0 B        0 B
├ ƒ /stages-recuperation-points/[slug]       4.44 kB    153 kB
├ ƒ /stages-recuperation-points/[...]/inscription  2.91 kB  151 kB
├ ƒ /stages-recuperation-points/[...]/merci  1.88 kB    150 kB
└ ƒ /[slug]                                  2.15 kB    150 kB

○ (Static)  prerendered as static content
ƒ (Dynamic) server-rendered on demand
```

**Build time**: ~45 seconds

### Vercel Deployment

**Auto-deploy**: Push to `main` branch triggers automatic deployment

**Deployment flow**:
```bash
git add .
git commit -m "Your message"
git push origin main
```

**Vercel**: Detects push → Builds → Deploys → Live in ~2 minutes

**Deployment URL**: https://www.twelvy.net

**Preview URLs**: Automatic for non-main branches

---

## Troubleshooting Guide

### Issue 1: Performance Drop with Large Cities

**Symptoms**:
- Desktop score drops from 89 to 76
- Mobile score drops from 93 to 84
- TBT increases to 400-600ms
- Page feels sluggish

**Diagnosis**:
```bash
# Check how many stages are rendering
# In browser console:
document.querySelectorAll('[data-stage-card]').length
```

**Cause**: Too many DOM elements (>100 stages)

**Solution**: Already implemented - pagination (20 per page)

**Verification**:
```bash
# Should show ~20 elements per page
document.querySelectorAll('[data-stage-card]').length
```

---

### Issue 2: Child Pages Not Showing in Menu

**Symptoms**:
- Parent page shows dropdown arrow
- Dropdown opens but is empty
- Child pages exist in WordPress admin

**Diagnosis**:
1. Check Vercel logs for debug output:
```
📄 Raw WordPress pages: [...]
🔍 Filtered pages: [...]
👨‍👧 Parent "..." has N children: [...]
```

2. Check WordPress page settings:
   - Parent field is set correctly
   - Status is "Published"
   - Slug doesn't match city filter pattern

3. Test raw API:
```bash
curl https://headless.twelvy.net/wp-json/wp/v2/pages?per_page=100
```

**Common Causes**:
- Page status is "Draft" instead of "Published"
- Parent field not set correctly
- Slug matches city filter pattern (stages-*)
- Filter logic removing child pages

**Solution**: Check filter logic in `/api/wordpress/menu/route.ts`:
```typescript
// Must check parent status FIRST
if (page.parent !== 0) return true
```

---

### Issue 3: Mixed Content Errors

**Symptoms**:
- Browser console: "Mixed Content" error
- City-specific content not loading
- HTTPS page trying to load HTTP resource

**Cause**: WordPress on HTTP, Next.js on HTTPS

**Solution**: Already implemented - API proxy route

**Verification**:
```bash
# Should work (proxied through Next.js)
curl https://www.twelvy.net/api/city-content/marseille

# Would fail in browser (Mixed Content)
# fetch('http://headless.twelvy.net/wp-json/wp/v2/pages?slug=stages-marseille')
```

---

### Issue 4: Console.log in Production

**Symptoms**:
- PageSpeed Best Practices score reduced
- Console cluttered with debug logs

**Check**:
```bash
# Search for console.log statements
grep -r "console\.log" app lib components
```

**Fix**:
```bash
# Remove all console.log statements
find app lib components \( -name "*.tsx" -o -name "*.ts" \) | \
  xargs sed -i '' '/console\.log/d'
```

**Verification**:
```bash
# Should return nothing
grep -r "console\.log" app lib components
```

---

### Issue 5: Build Errors with Dynamic Routes

**Symptoms**:
- Build fails with "params must be awaited"
- TypeScript errors about Promise types

**Cause**: Next.js 15 changed params to async

**Solution**: Always await params in dynamic routes:
```typescript
// WRONG (Next.js 14)
export default function Page({ params }: { params: { city: string } }) {
  const city = params.city
}

// CORRECT (Next.js 15)
export default async function Page({ params }: { params: Promise<{ city: string }> }) {
  const { city } = await params
}
```

---

### Issue 6: Missing robots.txt

**Symptoms**:
- PageSpeed warning about missing robots.txt
- SEO score reduction

**Check**:
```bash
ls -la public/robots.txt
```

**Create if missing**:
```bash
cat > public/robots.txt << 'EOF'
User-agent: *
Allow: /

Sitemap: https://www.twelvy.net/sitemap.xml
EOF
```

**Verification**:
```bash
curl https://www.twelvy.net/robots.txt
```

---

## Testing Checklist

### Pre-Deployment Testing

**1. Homepage**:
- [ ] Search bar visible
- [ ] Autocomplete shows cities
- [ ] WordPress content displays
- [ ] Hero section gradient renders

**2. Search Functionality**:
- [ ] Type city → Suggestions appear
- [ ] Arrow keys navigate suggestions
- [ ] Enter key searches
- [ ] Escape closes dropdown
- [ ] Redirects to results page

**3. Results Page**:
- [ ] Stages load correctly
- [ ] Pagination shows (if >20 results)
- [ ] Page counter displays
- [ ] Results summary shows
- [ ] Filters work (city, date, sort)
- [ ] Click stage card → Modal opens
- [ ] Click "Sélectionner" → Inscription page

**4. Modal Popup**:
- [ ] Google Maps renders
- [ ] Stage details display
- [ ] Tabs functional
- [ ] Escape closes modal
- [ ] Click outside closes modal

**5. Inscription Form**:
- [ ] All fields editable
- [ ] Email validation works
- [ ] CGV checkbox required
- [ ] Submit button disabled while submitting
- [ ] Redirects to merci page after submit

**6. Confirmation Page**:
- [ ] Booking reference displays
- [ ] Stage details accurate
- [ ] Print button works

**7. WordPress Navigation**:
- [ ] Menu items load
- [ ] Dropdown arrows for parents
- [ ] Mega menu opens on click
- [ ] Child pages display in 4 columns
- [ ] Click child → Navigates to page
- [ ] Escape closes dropdown
- [ ] Click outside closes dropdown

**8. WordPress Pages**:
- [ ] Parent pages show child grid
- [ ] Child pages show content
- [ ] 404 for non-existent pages

**9. Performance**:
- [ ] Desktop score >90
- [ ] Mobile score >90
- [ ] No console errors
- [ ] No console.logs in production

---

## Git Workflow

### Commit Message Convention

**Format**: `{emoji} {type}: {description}`

**Examples**:
```bash
git commit -m "✨ feat: Add pagination to results page"
git commit -m "🐛 fix: Child pages not showing in menu dropdown"
git commit -m "⚡ perf: Reduce DOM elements with pagination"
git commit -m "📝 docs: Update CLAUDE.md with all features"
git commit -m "🎨 style: Improve mega menu spacing"
git commit -m "♻️ refactor: Simplify filter logic in menu API"
```

**Emoji Guide**:
- ✨ New feature
- 🐛 Bug fix
- ⚡ Performance improvement
- 📝 Documentation
- 🎨 UI/Style changes
- ♻️ Refactoring
- 🔧 Configuration changes

### Git Commands

**Stage changes**:
```bash
git add .
# or specific files
git add app/page.tsx lib/useStages.ts
```

**Commit**:
```bash
git commit -m "Your message"
```

**Push to production** (triggers Vercel deploy):
```bash
git push origin main
```

**Check status**:
```bash
git status
```

**View recent commits**:
```bash
git log --oneline -10
```

**Undo last commit** (keep changes):
```bash
git reset HEAD~1
```

---

## Future Enhancements (Planned)

### Short Term
- [ ] Email confirmation system for bookings
- [ ] Admin dashboard for booking management
- [ ] Payment integration (Stripe/PayPal)
- [ ] Mobile app (React Native)

### Medium Term
- [ ] User accounts (login/register)
- [ ] Booking history
- [ ] Proximity-based sorting (use lat/lng)
- [ ] Advanced filters (price range slider, date range picker)
- [ ] Calendar view for stages

### Long Term
- [ ] Multi-language support (English, Spanish)
- [ ] Reviews and ratings system
- [ ] Loyalty program
- [ ] Partner training center dashboard
- [ ] Real-time availability updates

---

## Key Metrics and Statistics

### Performance
- **Desktop PageSpeed**: 100/100 ✓
- **Mobile PageSpeed**: 96/100 ✓
- **Average Load Time**: <1 second
- **Total Blocking Time**: <100ms
- **Largest Contentful Paint**: <1 second

### Data
- **Total Stages**: 2,239+ courses
- **Cities Covered**: All French metropolitan areas
- **Largest City**: Paris (215 stages)
- **Average Stages per City**: 10-30
- **Date Range**: Rolling 6 months

### Technical
- **Build Time**: ~45 seconds
- **Bundle Size**: 148 kB First Load JS
- **DOM Elements**: ~360 (with pagination)
- **API Response Time**: <100ms
- **Cache Duration**: 30 seconds (Next.js ISR)

---

## Contact and Support

### Developer
- **Name**: Yakeen
- **Project**: TWELVY
- **Repository**: GitHub (private)

### Infrastructure
- **Frontend Hosting**: Vercel
- **Backend Hosting**: OVH (o2switch shared)
- **CMS Hosting**: OVH (headless.twelvy.net)
- **Domain Registrar**: OVH
- **Database**: MySQL (OVH) - both stages and bookings

### Important URLs
- **Production**: https://www.twelvy.net
- **WordPress Admin**: https://headless.twelvy.net/wp-admin
- **Vercel Dashboard**: https://vercel.com/dashboard
- **OVH Dashboard**: https://www.ovh.com/manager/

---

**Last Updated**: February 2025
**Version**: 2.0
**Status**: Production Ready ✓
