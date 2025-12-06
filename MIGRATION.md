# TWELVY → PROSTAGEPERMIS.FR MIGRATION PLAN

## 🎯 MIGRATION OBJECTIVE

Migrate the TWELVY Next.js website (currently on www.twelvy.net) to www.prostagepermis.fr while:
- **Preserving** all existing prostagepermis.fr data (thousands of courses, articles, pages)
- **Replacing** the frontend with TWELVY's modern Next.js interface
- **Connecting** to prostagepermis.fr's existing MySQL database
- **Maintaining** zero downtime (12+ year SEO ranking must be preserved)
- **Keeping** WordPress headless functional

---

## 📊 CURRENT ARCHITECTURE (BEFORE MIGRATION)

### TWELVY Website (www.twelvy.net)
- **Frontend**: Next.js 15 on Vercel
- **Domain**: www.twelvy.net
- **Database**: MySQL on OVH (hosting: neopermis.fr)
- **WordPress Headless**: headless.twelvy.net (PHP 8.3, OVH hosting: neopermis.fr)
- **Stage Data**: Supabase (ucvxfjoongglzikjlxde.supabase.co)
- **Repository**: https://github.com/yakeeniacloud/TWELVY.git

**Data Sources:**
- WordPress API: `https://headless.twelvy.net/wp-json/wp/v2`
- MySQL Database: Stage courses, form submissions (OVH - neopermis.fr hosting)
- Supabase: Stage bookings, supplementary data

### ProStagePermis.fr (CURRENT PRODUCTION)
- **Frontend**: PHP-based website
- **Domain**: www.prostagepermis.fr
- **Hosting**: OVH (prostagepermis.fr hosting account)
- **Database**: MySQL on OVH (same hosting as domain)
- **Data**: Thousands of stage courses, articles, pages
- **Traffic**: High volume, 12+ years SEO ranking
- **Status**: LIVE - cannot go offline

---

## 🔄 TARGET ARCHITECTURE (AFTER MIGRATION)

### ProStagePermis.fr (NEW)
- **Frontend**: TWELVY Next.js app on Vercel
- **Domain**: www.prostagepermis.fr (pointed to Vercel)
- **Database**: prostagepermis.fr MySQL (OVH) - **UNCHANGED**
- **WordPress Headless**: headless.twelvy.net (stays as-is, pointed to prostagepermis.fr)
- **Stage Data**: prostagepermis.fr MySQL database (replaces TWELVY's MySQL)
- **Supabase**: Keeps supplementary data (bookings, etc.)

**Key Changes:**
1. Vercel project domain: `www.twelvy.net` → `www.prostagepermis.fr`
2. Database connection: TWELVY MySQL → ProStagePermis MySQL
3. All PHP API endpoints: Update to prostagepermis.fr MySQL credentials
4. WordPress API URLs: Keep `headless.twelvy.net` (no change needed)

---

## 📝 MIGRATION STRATEGY: BLUE-GREEN DEPLOYMENT

### Why Blue-Green?
- **Zero Downtime**: Old site stays live until new site is 100% ready
- **Instant Rollback**: If issues occur, revert DNS immediately
- **SEO Safe**: No downtime = no ranking loss
- **Testing**: Fully test new site on staging before switching

### Phases Overview
1. **Phase 1**: Prepare staging environment (test.prostagepermis.fr)
2. **Phase 2**: Database integration and testing
3. **Phase 3**: Vercel configuration and domain setup
4. **Phase 4**: DNS switch (instant cutover)
5. **Phase 5**: Monitoring and cleanup

---

## 🛠️ DETAILED MIGRATION STEPS

### PHASE 1: STAGING PREPARATION (No impact on production)

#### Step 1.1: Create Staging Subdomain
**Action**: Set up `test.prostagepermis.fr` for testing

**OVH DNS Configuration:**
```
Type: A Record
Subdomain: test
Target: [Vercel IP or CNAME to Vercel]
TTL: 300 (5 minutes for quick changes)
```

**Vercel Configuration:**
- Add domain: `test.prostagepermis.fr`
- Deploy TWELVY codebase to this domain
- Test all functionality before touching production

#### Step 1.2: Database Access Setup
**Action**: Get prostagepermis.fr MySQL credentials from OVH

**Required Information:**
```
MYSQL_HOST: [OVH server hostname, e.g., mysqlXX.perso.ovh.net]
MYSQL_DATABASE: [prostagepermis.fr database name]
MYSQL_USER: [database username]
MYSQL_PASSWORD: [database password]
MYSQL_PORT: 3306 (default)
```

**Where to find**: OVH Control Panel → Databases → prostagepermis.fr database

#### Step 1.3: Database Schema Analysis
**Action**: Compare TWELVY vs ProStagePermis database structures

**Export ProStagePermis Schema:**
```bash
# From OVH phpMyAdmin or SSH
mysqldump -h [host] -u [user] -p --no-data [database] > prostagepermis_schema.sql
```

**Compare Tables:**
- Identify course/stages table name in prostagepermis.fr
- Map column names (e.g., TWELVY's `city` → prostagepermis.fr's `ville`)
- Note differences in data types

**Expected Tables to Preserve:**
- `stages` or `courses` (thousands of existing courses)
- `articles` or `posts` (existing content)
- `form_submissions` or `bookings` (user data)
- Any other business-critical tables

---

### PHASE 2: DATABASE INTEGRATION

#### Step 2.1: Update Environment Variables
**Action**: Configure test.prostagepermis.fr to use prostagepermis.fr MySQL

**Files to Update:**
- `/Users/yakeen/Desktop/TWELVY/.env.local` (for local testing)
- Vercel Environment Variables (for test.prostagepermis.fr)

**New Variables:**
```bash
# Replace TWELVY MySQL credentials with ProStagePermis credentials
MYSQL_HOST="[prostagepermis.fr MySQL host]"
MYSQL_DATABASE="[prostagepermis.fr database name]"
MYSQL_USER="[prostagepermis.fr user]"
MYSQL_PASSWORD="[prostagepermis.fr password]"

# Keep Supabase for bookings (unchanged)
NEXT_PUBLIC_SUPABASE_URL="https://ucvxfjoongglzikjlxde.supabase.co"
NEXT_PUBLIC_SUPABASE_ANON_KEY="[existing key]"

# Keep WordPress headless (unchanged)
NEXT_PUBLIC_WORDPRESS_API_URL="https://headless.twelvy.net/wp-json/wp/v2"
```

#### Step 2.2: Update API Endpoints
**Action**: Modify PHP API files to match prostagepermis.fr database schema

**Files to Update:**
```
/Users/yakeen/Desktop/TWELVY/php/inscription.php
/Users/yakeen/Desktop/TWELVY/php/[any other API files]
```

**Changes Needed:**
1. **Database Connection**: Update credentials to prostagepermis.fr MySQL
2. **Table Names**: Change table references (e.g., `stages_recuperation_points` → `[prostagepermis table name]`)
3. **Column Mapping**: Adjust column names to match prostagepermis.fr schema

**Example Mapping (to be determined after schema analysis):**
```php
// BEFORE (TWELVY schema)
$sql = "SELECT * FROM stages_recuperation_points WHERE city = ?";

// AFTER (ProStagePermis schema - example)
$sql = "SELECT * FROM stages WHERE ville = ?";
```

#### Step 2.3: Test Database Connectivity
**Action**: Verify test.prostagepermis.fr can read/write to prostagepermis.fr MySQL

**Test Checklist:**
- [ ] Homepage loads stages from prostagepermis.fr database
- [ ] Search functionality returns correct courses
- [ ] Form submissions write to prostagepermis.fr database
- [ ] All existing courses visible (thousands of records)
- [ ] No data corruption or missing fields

---

### PHASE 3: VERCEL & DOMAIN CONFIGURATION

#### Step 3.1: Vercel Production Setup
**Action**: Add www.prostagepermis.fr to Vercel project

**Vercel Dashboard Steps:**
1. Go to TWELVY project → Settings → Domains
2. Add custom domain: `www.prostagepermis.fr`
3. Add custom domain: `prostagepermis.fr` (redirect to www)
4. Vercel will provide DNS instructions (A record or CNAME)

**Vercel DNS Instructions (example):**
```
Type: CNAME
Name: www
Target: cname.vercel-dns.com
```

#### Step 3.2: Update Production Environment Variables
**Action**: Set prostagepermis.fr MySQL credentials in Vercel production

**Vercel → Settings → Environment Variables:**
- Set all variables for "Production" environment
- Copy exact values from test.prostagepermis.fr
- Double-check MySQL credentials

#### Step 3.3: WordPress Headless Update (Optional)
**Action**: Update WordPress API references if needed

**Option A (Recommended): Keep headless.twelvy.net**
- No changes needed
- WordPress stays at headless.twelvy.net
- prostagepermis.fr fetches from headless.twelvy.net/wp-json

**Option B (Advanced): Move to headless.prostagepermis.fr**
- Create new subdomain in OVH DNS
- Point to neopermis.fr hosting
- Update WordPress site URL in wp-config.php
- Update all API references in code
- **Risk**: More complex, higher chance of errors

**Recommendation**: Use Option A for simplicity and safety

---

### PHASE 4: DNS SWITCH (INSTANT CUTOVER)

#### Step 4.1: Pre-Switch Checklist
**Action**: Verify everything before DNS change

**Critical Checks:**
- [ ] test.prostagepermis.fr fully functional
- [ ] Database reads/writes working
- [ ] All forms submitting correctly
- [ ] WordPress content loading
- [ ] Navigation menus working
- [ ] Search bar functional
- [ ] Stage booking flow complete
- [ ] No console errors
- [ ] Mobile responsive
- [ ] Page load times acceptable

#### Step 4.2: Lower DNS TTL (24 hours before switch)
**Action**: Reduce TTL for faster propagation

**OVH DNS Panel:**
```
Domain: prostagepermis.fr
Record: www (A or CNAME)
Current TTL: 3600 (1 hour) or higher
New TTL: 300 (5 minutes)
```

**Why**: Lower TTL = faster DNS propagation when you switch

**Wait 24 hours** for old TTL to expire globally

#### Step 4.3: Execute DNS Switch
**Action**: Point www.prostagepermis.fr to Vercel

**Timing**: Choose low-traffic time (e.g., 2-4 AM Paris time)

**OVH DNS Changes:**
```
BEFORE (pointing to OVH hosting):
Type: A
Name: www
Target: [OVH server IP]

AFTER (pointing to Vercel):
Type: CNAME
Name: www
Target: cname.vercel-dns.com
```

**Also update root domain:**
```
Type: A
Name: @ (or blank for root)
Target: 76.76.21.21 (Vercel's A record)
```

**Save Changes** - DNS propagation begins immediately

#### Step 4.4: Immediate Verification (First 5 minutes)
**Action**: Test new site as DNS propagates

**Tests:**
```bash
# Check DNS propagation
nslookup www.prostagepermis.fr

# Test HTTP response
curl -I https://www.prostagepermis.fr

# Check if Vercel is serving
curl https://www.prostagepermis.fr | grep -i "vercel"
```

**Browser Tests:**
- Clear browser cache (Cmd+Shift+R on Mac)
- Visit www.prostagepermis.fr
- Verify new Next.js site loads (not old PHP site)
- Test all critical functions

#### Step 4.5: Monitor (First 24 hours)
**Action**: Watch for issues during full propagation

**Monitoring:**
- Vercel Analytics: Check traffic and errors
- Browser DevTools: Monitor console errors
- User Reports: Have support team ready
- Database Logs: Watch for connection errors

**Rollback Plan (if major issues):**
- Revert DNS to old OVH IP immediately
- Old site comes back within 5 minutes (due to low TTL)
- Fix issues on staging, try again later

---

### PHASE 5: POST-MIGRATION CLEANUP

#### Step 5.1: SSL Certificate Verification
**Action**: Ensure HTTPS works correctly

**Vercel Auto-SSL:**
- Vercel automatically provisions Let's Encrypt SSL
- Check https://www.prostagepermis.fr loads with padlock
- No mixed content warnings

#### Step 5.2: SEO Preservation
**Action**: Verify search engine visibility

**Checks:**
- [ ] robots.txt accessible
- [ ] sitemap.xml exists and loads
- [ ] Meta tags present on all pages
- [ ] Google Search Console: No sudden errors
- [ ] Bing Webmaster Tools: No alerts

**Submit New Sitemap:**
```
https://www.prostagepermis.fr/sitemap.xml
```
Submit to Google Search Console and Bing

#### Step 5.3: Performance Optimization
**Action**: Ensure site is fast

**Vercel Performance:**
- Check Vercel Analytics for Core Web Vitals
- Optimize images if needed
- Enable Vercel Edge caching

#### Step 5.4: Increase DNS TTL (After 7 days)
**Action**: Restore normal TTL after migration stable

**OVH DNS:**
```
TTL: 300 (5 minutes) → 3600 (1 hour)
```

**Why**: Reduce DNS query load after migration is stable

#### Step 5.5: Decommission Old Infrastructure (Optional)
**Action**: Clean up old TWELVY resources (ONLY after 30 days of stability)

**Safe to Remove:**
- www.twelvy.net domain (or keep as redirect)
- TWELVY MySQL database on neopermis.fr (backup first!)
- Old PHP files on prostagepermis.fr hosting

**Keep Forever:**
- headless.twelvy.net (WordPress still needed)
- Supabase data (bookings, etc.)
- GitHub repository

---

## 🚨 RISK MITIGATION

### Critical Risks & Solutions

#### Risk 1: Database Schema Mismatch
**Scenario**: TWELVY code expects different column names than prostagepermis.fr

**Solution**:
- Thoroughly map schema in Phase 2.3
- Create database view or adapter layer if needed
- Test extensively on staging

#### Risk 2: SEO Ranking Loss
**Scenario**: DNS issues cause downtime, Google penalties

**Solution**:
- Use low TTL for fast rollback
- Monitor Search Console during migration
- Keep old site ready for instant revert

#### Risk 3: Data Corruption
**Scenario**: New site writes bad data to prostagepermis.fr database

**Solution**:
- **CRITICAL**: Full database backup before Phase 4.3
- Read-only mode for first 1 hour after switch
- Test all write operations on staging first

**Backup Command:**
```bash
mysqldump -h [host] -u [user] -p [database] > prostagepermis_backup_$(date +%Y%m%d).sql
```

#### Risk 4: WordPress Headless Breaks
**Scenario**: Menu/content stops loading after domain change

**Solution**:
- Keep headless.twelvy.net unchanged (no domain migration)
- WordPress API URLs remain the same
- No code changes needed for WordPress integration

---

## 📋 PRE-MIGRATION CHECKLIST

### Before Starting Phase 1:
- [ ] Full backup of prostagepermis.fr MySQL database
- [ ] Full backup of prostagepermis.fr files (PHP site)
- [ ] Access to OVH control panel (DNS + hosting)
- [ ] Access to Vercel dashboard
- [ ] Access to prostagepermis.fr MySQL credentials
- [ ] Downtime notification plan (if needed)
- [ ] Rollback plan documented and tested

### Before Phase 4 DNS Switch:
- [ ] test.prostagepermis.fr 100% functional
- [ ] All stakeholders notified of switch timing
- [ ] Support team on standby
- [ ] Database backup completed (< 1 hour old)
- [ ] DNS TTL lowered 24+ hours ago
- [ ] All tests passing on staging

---

## 🔧 TECHNICAL DETAILS

### Database Schema Mapping (TO BE COMPLETED)

**TWELVY Schema (Current):**
```sql
-- Example - actual schema may differ
stages_recuperation_points (
  id UUID PRIMARY KEY,
  city TEXT,
  postal_code TEXT,
  full_address TEXT,
  location_name TEXT,
  date_start DATE,
  date_end DATE,
  price NUMERIC
)
```

**ProStagePermis Schema (TO BE DETERMINED):**
```sql
-- To be documented after Step 1.3
-- Example - actual schema unknown until analysis
stages (
  id INT PRIMARY KEY,
  ville VARCHAR(255),
  code_postal VARCHAR(10),
  adresse TEXT,
  nom_lieu VARCHAR(255),
  date_debut DATE,
  date_fin DATE,
  prix DECIMAL(10,2)
)
```

**Mapping Rules:**
- `city` → `ville`
- `postal_code` → `code_postal`
- `full_address` → `adresse`
- `location_name` → `nom_lieu`
- `date_start` → `date_debut`
- `date_end` → `date_fin`
- `price` → `prix`

### API Endpoints to Update

**Current TWELVY Endpoints:**
- `/api/wordpress/*` - Keep unchanged (uses headless.twelvy.net)
- `/php/inscription.php` - Update MySQL credentials + table names
- `/php/*` - Update all PHP files with new database config

### Environment Variables Changes

**Remove (TWELVY-specific):**
```bash
# Old MySQL on neopermis.fr
MYSQL_HOST=vautour.o2switch.net
MYSQL_DATABASE=maab3521_optimus-stages
MYSQL_USER=maab3521_stages-user
MYSQL_PASSWORD='Vratouy1214!'
```

**Add (ProStagePermis):**
```bash
# New MySQL on prostagepermis.fr
MYSQL_HOST=[to be determined]
MYSQL_DATABASE=[to be determined]
MYSQL_USER=[to be determined]
MYSQL_PASSWORD=[to be determined]
```

---

## 📞 SUPPORT CONTACTS

### Critical Services
- **OVH Support**: [OVH ticket system]
- **Vercel Support**: [Vercel dashboard support]
- **Domain Registrar**: OVH
- **DNS Provider**: OVH

### Escalation Plan
1. Technical issues → Check Vercel logs + MySQL logs
2. DNS issues → OVH support ticket
3. Database errors → Rollback DNS immediately
4. SEO concerns → Monitor Search Console, wait 48 hours

---

## 📊 SUCCESS METRICS

### Migration Success Criteria:
- [ ] www.prostagepermis.fr loads new Next.js site
- [ ] All courses from prostagepermis.fr database visible
- [ ] Search functionality works
- [ ] Form submissions save to database
- [ ] WordPress menus/content loading
- [ ] Zero data loss
- [ ] < 5 minutes total downtime (DNS propagation)
- [ ] No SEO ranking drop (monitor 30 days)
- [ ] Page load time < 2 seconds
- [ ] No JavaScript console errors

### Post-Migration Monitoring (30 days):
- Daily: Vercel analytics for errors
- Weekly: Google Search Console for ranking changes
- Weekly: Database integrity checks
- Monthly: Performance audit

---

## 🗓️ ESTIMATED TIMELINE

### Realistic Schedule:
- **Phase 1** (Staging Setup): 1-2 days
- **Phase 2** (Database Integration): 2-4 days (depends on schema complexity)
- **Phase 3** (Vercel Config): 1 day
- **Phase 4** (DNS Switch): 1 day (5 minutes active, 24 hours monitoring)
- **Phase 5** (Cleanup): 1 week

**Total**: 1-2 weeks for safe, tested migration

**Aggressive Schedule**: 3-5 days (higher risk)

---

## 📝 NOTES & ASSUMPTIONS

### Key Assumptions:
1. prostagepermis.fr MySQL database is accessible remotely (or via PHP API)
2. Database schema is compatible with TWELVY's data model
3. No major code refactoring needed for database integration
4. WordPress headless can remain at headless.twelvy.net
5. Vercel deployment pipeline already working for TWELVY

### Unknown Factors (Requires Investigation):
- Exact prostagepermis.fr database schema
- Number of API endpoints using MySQL
- Compatibility of existing course data format
- Current prostagepermis.fr site architecture
- Any hardcoded domain references in code

---

**Document Version**: 1.0
**Last Updated**: 2025-11-28
**Next Review**: Before Phase 1 execution
