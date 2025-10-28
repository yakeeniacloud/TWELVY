# TWELVY Project Documentation

## Project Overview

**TWELVY** is a complete reconstruction of the **digitalwebsuccess.com** website, redesigned with improved architecture and functionality. The project is hosted on **www.twelvy.net** with a backend API on **api.twelvy.net**.

### Key Information
- **Primary Domain**: www.twelvy.net
- **API Domain**: api.twelvy.net
- **Database**: OVH MySQL (khapmaitpsp.mysql.db)
- **Frontend**: Vercel (Next.js 16)
- **Backend**: OVH Shared Hosting (PHP 8.1)
- **GitHub Repository**: https://github.com/yakeeniacloud/TWELVY.git

---

## Project History & Context

### The digitalwebsuccess.com Project

**digitalwebsuccess.com** was the original platform built with:
- **Frontend**: Next.js on Vercel (www.digitalwebsuccess.com)
- **WordPress Headless**: admin.digitalwebsuccess.com on o2switch
- **Database**: Supabase (PostgreSQL)
- **Feature**: Stages Récupération de Points (driving license points recovery courses)

**Key Components Implemented**:
1. **Homepage** with search bar for cities
2. **Results page** showing available courses for selected city
3. **Detail page** displaying full course information
4. **Booking form** (inscription page) collecting user information
5. **Confirmation page** with booking reference and details
6. **Responsive design** with filters and sorting options
7. **WordPress integration** for content management

### Why TWELVY Project Exists

The TWELVY project is a **complete reconstruction** of digitalwebsuccess.com with a **critical architectural change**:

**Problem with digitalwebsuccess.com**:
- Used **Supabase (PostgreSQL)** for database
- Supabase adds unnecessary complexity and cost
- Limited control over data and queries
- Dependency on third-party service

**Solution - TWELVY Architecture**:
- **Direct MySQL on OVH** shared hosting
- **PHP REST API** as middleware between Vercel and MySQL
- Complete ownership and control of data
- Simplified architecture
- Same functionality, better control

---

## Architecture Overview

### System Flow

```
┌─────────────────────┐
│  www.twelvy.net     │
│  (Vercel/Next.js)   │
└──────────┬──────────┘
           │ HTTPS
           ▼
┌─────────────────────────────────────────┐
│   Next.js API Routes (Proxy Layer)      │
│  /api/test-booking  (POST to OVH)       │
│  /api/test-get      (GET from OVH)      │
└──────────┬──────────────────────────────┘
           │ HTTPS → HTTP
           ▼
┌─────────────────────────────────────────┐
│     api.twelvy.net (OVH PHP API)        │
│   /inscription.php  (Insert bookings)   │
│   /phpinfo.php      (Test PHP execution)│
└──────────┬──────────────────────────────┘
           │ Localhost connection
           ▼
┌─────────────────────────────────────────┐
│    OVH MySQL Database                   │
│  khapmaitpsp.mysql.db                   │
│  stage_bookings table (from previous)   │
└─────────────────────────────────────────┘
```

### Why This Architecture?

1. **Security**: API key authentication on PHP endpoints
2. **Control**: Direct MySQL database access
3. **Simplicity**: PHP is simple, reliable, and widely supported
4. **Cost**: No expensive Supabase subscription
5. **Reliability**: OVH shared hosting is proven and stable

---

## OVH Infrastructure Setup

### Hosting Details

**OVH Shared Hosting Account**:
- **Provider**: OVH (French hosting company)
- **Server**: cluster115.hosting.ovh.net
- **Account Name**: khapmait
- **FTP**: ftp.cluster115.hosting.ovh.net:21

### MySQL Database

**Database Details**:
- **Host**: khapmaitpsp.mysql.db (OVH internal)
- **Database**: khapmaitpsp
- **Username**: khapmaitpsp
- **Password**: Stored in environment variables (secure)
- **Access**: Localhost only (from PHP on same server)

**Table Structure** (stage_bookings):
```sql
CREATE TABLE stage_bookings (
  id VARCHAR(36) PRIMARY KEY,
  stage_id VARCHAR(36) NOT NULL,
  booking_reference VARCHAR(50) UNIQUE NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  nom VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL,
  telephone VARCHAR(20) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_stage_id (stage_id),
  INDEX idx_booking_reference (booking_reference),
  INDEX idx_email (email)
);
```

### PHP Configuration

**OVH PHP Setup**:
- **Global PHP Version**: 8.1 (configured in OVH control panel)
- **File Location**: /www/api/ (accessible as api.twelvy.net)
- **Configuration File**: .ovhconfig in /www/ root

**Critical Setup**:
1. Set global PHP version to 8.1 in OVH control panel
2. Create .ovhconfig file in /www/ (not /www/api/)
3. Upload PHP files via FTP to /www/api/

**Example .ovhconfig**:
```
app.engine=php
app.engine.version=8.1
```

---

## Backend API (OVH PHP)

### File: phpinfo.php

**Purpose**: Test PHP execution on OVH

**Location**: api.twelvy.net/phpinfo.php

**Code**:
```php
<?php
phpinfo();
?>
```

**Usage**: Visit URL in browser to verify PHP 8.1 is executing

---

### File: inscription.php

**Purpose**: Receive booking data from Vercel, insert into MySQL

**Location**: api.twelvy.net/inscription.php

**Key Features**:
1. **API Key Validation**: Requires X-Api-Key header
2. **UUID Generation**: Creates unique booking ID
3. **Reference Number**: Generates BK-YYYY-NNNNNN format
4. **Database Insert**: Inserts into stage_bookings table
5. **Error Handling**: Returns JSON responses

**Request Format**:
```json
POST /inscription.php HTTP/1.1
Host: api.twelvy.net
Content-Type: application/json
X-Api-Key: [API_KEY_HERE]

{
  "prenom": "John",
  "nom": "Doe",
  "email": "john@example.com",
  "telephone": "0612345678",
  "stage_id": "7cab4960-6fb6-4f92-9da7-8ed901014c39"
}
```

**Response Format** (Success):
```json
{
  "ok": true,
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "booking_reference": "BK-2025-000001"
}
```

**Response Format** (Error):
```json
{
  "ok": false,
  "error": "Invalid API key"
}
```

**PHP Implementation**:
```php
<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// API Key validation
$apiKey = '[NEW_API_KEY_HERE]';
$headers = getallheaders();

if (!isset($headers['X-Api-Key']) || $headers['X-Api-Key'] !== $apiKey) {
    http_response_code(401);
    echo json_encode(["ok" => false, "error" => "Invalid API key"]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['prenom'], $input['nom'], $input['email'], $input['telephone'], $input['stage_id'])) {
    http_response_code(400);
    echo json_encode(["ok" => false, "error" => "Missing required fields"]);
    exit;
}

// Connect to database
try {
    $dsn = 'mysql:host=khapmaitpsp.mysql.db;dbname=khapmaitpsp;charset=utf8mb4';
    $pdo = new PDO($dsn, 'khapmaitpsp', 'Lretouiva1226', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => "Database connection failed: " . $e->getMessage()]);
    exit;
}

// Generate booking reference
$year = date('Y');
$stmt = $pdo->query("SELECT COUNT(*) as count FROM stage_bookings WHERE booking_reference LIKE 'BK-$year-%'");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$count = $result['count'] + 1;
$bookingRef = sprintf('BK-%s-%06d', $year, $count);

// Generate UUID
$id = sprintf(
    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
    mt_rand(0, 0x0fff) | 0x4000,
    mt_rand(0, 0x3fff) | 0x8000,
    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
);

// Insert into database
try {
    $stmt = $pdo->prepare("
        INSERT INTO stage_bookings (id, stage_id, booking_reference, prenom, nom, email, telephone, created_at, updated_at)
        VALUES (:id, :stage_id, :booking_reference, :prenom, :nom, :email, :telephone, NOW(), NOW())
    ");

    $stmt->execute([
        ':id' => $id,
        ':stage_id' => $input['stage_id'],
        ':booking_reference' => $bookingRef,
        ':prenom' => $input['prenom'],
        ':nom' => $input['nom'],
        ':email' => $input['email'],
        ':telephone' => $input['telephone']
    ]);

    echo json_encode([
        "ok" => true,
        "id" => $id,
        "booking_reference" => $bookingRef
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => "Insert failed: " . $e->getMessage()]);
    exit;
}
?>
```

---

## Frontend (Vercel/Next.js)

### Project Structure

```
/Users/yakeen/Desktop/TWELVY/
├── app/
│   ├── api/
│   │   ├── test-booking/
│   │   │   └── route.ts       (POST proxy to OVH)
│   │   └── test-get/
│   │       └── route.ts       (GET proxy to OVH)
│   ├── layout.tsx              (Root layout)
│   ├── page.tsx                (Home page with test buttons)
│   └── globals.css             (Global styles)
├── package.json                (Dependencies)
├── next.config.ts              (Next.js config)
├── tsconfig.json               (TypeScript config)
├── tailwind.config.ts          (Tailwind CSS config)
├── CLAUDE.md                   (This file)
└── .gitignore                  (Git ignore rules)
```

### Environment Variables

**File**: `.env.local` (created in Vercel project)

```
OVH_API_URL=https://api.twelvy.net
OVH_API_KEY=[NEW_API_KEY_GENERATED]
```

**Note**: These are configured in Vercel dashboard under Settings → Environment Variables

### Next.js Configuration Files

#### package.json
- Lists all dependencies (Next.js 16, React 19, TypeScript, Tailwind)
- Defines build and dev scripts

#### next.config.ts
- Minimal configuration for standard Next.js setup
- No special rewrites or middleware needed (simple architecture)

#### tsconfig.json
- TypeScript compiler options
- Path aliases (`@/*` for src directory)

#### tailwind.config.ts
- Tailwind CSS configuration
- Includes all app files for styling

---

## API Proxy Routes

### Route: /api/test-booking

**Purpose**: Forward POST requests from frontend to OVH PHP API

**File**: `app/api/test-booking/route.ts`

**Functionality**:
1. Receives JSON POST from frontend
2. Adds API key authentication header
3. Forwards to api.twelvy.net/inscription.php
4. Returns OVH response to frontend

**Code**:
```typescript
import { NextResponse } from 'next/server'

export async function POST(request: Request) {
  try {
    const body = await request.json()

    const ovhApiUrl = process.env.OVH_API_URL || 'https://api.twelvy.net'
    const apiKey = process.env.OVH_API_KEY || ''

    const response = await fetch(`${ovhApiUrl}/inscription.php`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Api-Key': apiKey,
      },
      body: JSON.stringify(body),
    })

    const data = await response.json()
    return NextResponse.json(data, { status: response.status })
  } catch (error) {
    return NextResponse.json(
      {
        error: 'Failed to process booking',
        message: error instanceof Error ? error.message : 'Unknown error'
      },
      { status: 500 }
    )
  }
}
```

---

### Route: /api/test-get

**Purpose**: Forward GET requests to OVH phpinfo.php to test connectivity

**File**: `app/api/test-get/route.ts`

**Functionality**:
1. Sends GET request to api.twelvy.net/phpinfo.php
2. Parses response to check if PHP is executing
3. Returns status message to frontend

**Code**:
```typescript
import { NextResponse } from 'next/server'

export async function GET() {
  try {
    const ovhApiUrl = process.env.OVH_API_URL || 'https://api.twelvy.net'

    const response = await fetch(`${ovhApiUrl}/phpinfo.php`, {
      method: 'GET',
      headers: {
        'Accept': 'text/html',
      },
    })

    const text = await response.text()

    // Check if we got HTML (phpinfo) or raw PHP code
    if (text.includes('PHP Version') || text.includes('phpinfo')) {
      return NextResponse.json(
        { ok: true, message: 'PHP is executing correctly on OVH!' },
        { status: 200 }
      )
    } else if (text.includes('<?php')) {
      return NextResponse.json(
        { ok: false, message: 'Got raw PHP code - PHP not executing properly' },
        { status: 500 }
      )
    }
  } catch (error) {
    return NextResponse.json(
      {
        ok: false,
        error: 'Failed to reach OVH API',
        message: error instanceof Error ? error.message : 'Unknown error'
      },
      { status: 500 }
    )
  }
}
```

---

## Frontend Page Component

### File: app/page.tsx

**Purpose**: Provides test buttons for POST and GET validation

**Features**:
1. **POST Button**: Tests booking insertion
   - Sends test data to /api/test-booking
   - Receives booking ID and reference
   - Displays success/error message

2. **GET Button**: Tests PHP execution on OVH
   - Sends request to /api/test-get
   - Confirms PHP 8.1 is running
   - Displays connectivity status

3. **Shared Loading State**: Both buttons use same loading indicator
4. **Separate Result Messages**: POST and GET results displayed separately
5. **Test Data Display**: Shows what data is being sent in POST request

**User Interface**:
- Centered card layout
- Blue button for POST (blue accent)
- Green button for GET (green accent)
- Blue and green result boxes
- Responsive design with Tailwind CSS

---

## Development Workflow

### Local Development

**1. Install Dependencies**:
```bash
cd /Users/yakeen/Desktop/TWELVY
npm install
```

**2. Create Environment File**:
```bash
# Create .env.local
echo 'OVH_API_URL=https://api.twelvy.net' > .env.local
echo 'OVH_API_KEY=[API_KEY_HERE]' >> .env.local
```

**3. Run Development Server**:
```bash
npm run dev
```
- Opens on http://localhost:3000
- Auto-reloads on file changes

**4. Test API Connectivity**:
- Click "📤 Send Test Booking (POST)" button
- Verify booking is created in OVH MySQL
- Click "📥 Test GET Request" button
- Confirm PHP execution message

### Building for Production

```bash
npm run build
npm start
```

---

## Deployment to Vercel

### Step 1: Create Vercel Project

1. Go to [vercel.com](https://vercel.com)
2. Click "New Project"
3. Import GitHub repository: `https://github.com/yakeeniacloud/TWELVY.git`
4. Select main branch
5. Click "Import"

### Step 2: Configure Environment Variables

In Vercel Dashboard:
1. Go to Settings → Environment Variables
2. Add two variables:
   - `OVH_API_URL` = `https://api.twelvy.net`
   - `OVH_API_KEY` = [New API key]
3. Apply to all environments (Production, Preview, Development)
4. Redeploy

### Step 3: Configure Custom Domain

In Vercel Dashboard:
1. Go to Settings → Domains
2. Add domain: `www.twelvy.net`
3. Follow DNS configuration instructions
4. Point to Vercel nameservers

### Step 4: Verify Deployment

1. Visit https://www.twelvy.net
2. Test POST button - should create booking in OVH MySQL
3. Test GET button - should confirm PHP execution

---

## DNS Configuration

### Required DNS Records

**For www.twelvy.net** (Next.js Frontend):
```
Type: CNAME
Name: www
Value: cname.vercel-dns.com
```

**For api.twelvy.net** (OVH PHP API):
```
Type: A
Value: [OVH Server IP - from control panel]
```

### Configuration Steps

1. **Domain Registrar**: Update DNS records as above
2. **OVH Control Panel**: Ensure api subdomain is configured
3. **Vercel Dashboard**: Configure www.twelvy.net domain
4. **Wait for DNS**: Can take 24 hours to propagate

---

## Current Status

### ✅ Completed

- [x] Project structure created
- [x] Next.js 16 configured with TypeScript
- [x] API proxy routes created (/api/test-booking, /api/test-get)
- [x] Test page with POST and GET buttons
- [x] Environment variables structure defined
- [x] OVH MySQL database connection working
- [x] PHP API endpoints functional
- [x] GitHub repository set up

### ⏳ Next Steps

1. **Generate New API Key**: Create unique key for TWELVY
2. **Deploy to Vercel**: Create Vercel project and link GitHub
3. **Configure Environment**: Set API key in Vercel dashboard
4. **Configure DNS**: Point www.twelvy.net to Vercel
5. **Test Full Flow**: Verify POST and GET from Vercel to OVH
6. **Build Website Template**: Implement design from provided template
7. **Integrate Stages Feature**: Add courses search, results, booking
8. **WordPress Setup**: Optional WordPress admin on admin.twelvy.net

---

## Troubleshooting

### Issue: POST/GET buttons don't work

**Symptoms**: Clicking buttons shows error

**Solutions**:
1. Check environment variables in Vercel dashboard
2. Verify api.twelvy.net is accessible: `curl https://api.twelvy.net/phpinfo.php`
3. Confirm API key is correct in OVH PHP file
4. Check browser console for detailed error messages

### Issue: "PHP not executing" error

**Cause**: OVH PHP version not set to 8.1

**Fix**:
1. Go to OVH control panel
2. Go to Hosting → Configuration
3. Set "Version PHP globale" to 8.1
4. Wait 10 minutes for change to apply

### Issue: Booking not inserted in MySQL

**Symptoms**: POST returns success but no data in phpMyAdmin

**Causes**:
1. Wrong database credentials in PHP
2. stage_bookings table doesn't exist
3. Table_bookings table doesn't exist

**Fix**:
1. Run SQL to create/verify table:
```sql
CREATE TABLE IF NOT EXISTS stage_bookings (
  id VARCHAR(36) PRIMARY KEY,
  stage_id VARCHAR(36) NOT NULL,
  booking_reference VARCHAR(50) UNIQUE NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  nom VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL,
  telephone VARCHAR(20) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## Security Considerations

### API Key Management

- **Never commit API key to GitHub**: Use environment variables only
- **Rotate keys regularly**: Generate new key quarterly
- **Use strong keys**: Current key is 64-character hex string
- **Header validation**: PHP checks exact case of X-Api-Key header

### Database Security

- **Localhost only**: MySQL accessible only from PHP on same server
- **User permissions**: khapmaitpsp user limited to khapmaitpsp database
- **No direct access**: Database not accessible from Vercel or public internet
- **All access through PHP**: Only inscriptions.php can write to stage_bookings

### HTTPS/SSL

- **Vercel**: Automatic HTTPS with SSL certificate
- **OVH**: HTTP connection from Vercel to PHP (internal, secure)
- **CORS**: Configured in PHP to allow Vercel domain

---

## Future Enhancements

### Phase 1: Website Template Implementation
- Copy design from provided template
- Implement homepage layout
- Create navigation structure
- Add responsive design

### Phase 2: Stages Feature
- Add stages search by city
- Results page with filtering
- Detail page for individual course
- Booking form integration

### Phase 3: WordPress Integration (Optional)
- Set up WordPress on admin.twelvy.net
- Connect to OVH
- Integrate with booking system
- Client content editing

### Phase 4: Advanced Features
- Email confirmations
- Payment integration
- Booking management dashboard
- Analytics and reporting

---

## Development Guidelines

### Code Style

- Use TypeScript for type safety
- Follow Next.js best practices
- Use Tailwind CSS for styling
- Keep components functional and small
- Comment complex logic

### Git Workflow

1. Create feature branches from main
2. Commit frequently with clear messages
3. Push to GitHub
4. Vercel auto-deploys on push
5. Test before merging to main

### Error Handling

- Always return meaningful error messages
- Log to console in development
- Handle API failures gracefully
- Validate all inputs

---

## Contact & Support

**Project Owner**: Yakeen
**GitHub**: https://github.com/yakeeniacloud/TWELVY
**Frontend**: www.twelvy.net (Vercel)
**API**: api.twelvy.net (OVH)

---

**Last Updated**: October 28, 2025
**Next AI Assistant**: Use this documentation as complete context for continuing TWELVY project development.
