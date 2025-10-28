# OVH PHP API Files

These PHP files need to be uploaded to your OVH hosting via FTP.

## Files

### phpinfo.php
- **Purpose**: Test PHP execution on OVH
- **Location on OVH**: `/www/api/phpinfo.php`
- **URL**: `https://api.twelvy.net/phpinfo.php`
- **Usage**: Visit in browser to verify PHP 8.1 is running

### inscription.php
- **Purpose**: API endpoint for booking submissions
- **Location on OVH**: `/www/api/inscription.php`
- **URL**: `https://api.twelvy.net/inscription.php`
- **Method**: POST
- **Auth**: Requires `X-Api-Key` header with value `82193ec2e06757dc73f34785a0f46df12e88250430dc72927befb128ef4fb496`

## Upload Instructions

1. Open VS Code FTP-Simple or your FTP client
2. Connect to OVH FTP: `ftp.cluster115.hosting.ovh.net`
3. Username: `khapmait`
4. Navigate to `/www/api/` folder
5. Upload both `phpinfo.php` and `inscription.php`
6. Test by visiting:
   - `https://api.twelvy.net/phpinfo.php` (should show PHP info)
   - Test POST request with correct API key

## API Key

**New API Key for TWELVY**:
```
82193ec2e06757dc73f34785a0f46df12e88250430dc72927befb128ef4fb496
```

This key is already configured in:
- `inscription.php` (line 6)
- Vercel environment variables
- Next.js API proxy routes

## Database

The PHP files connect to the existing OVH MySQL database:
- **Host**: khapmaitpsp.mysql.db
- **Database**: khapmaitpsp
- **User**: khapmaitpsp
- **Table**: stage_bookings

No changes needed to the database - it's already set up from the previous project.
