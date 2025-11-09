# Vercel Environment Variables Setup

## Required Environment Variables for TWELVY

Add these variables to Vercel dashboard: **TWELVY project → Settings → Environment Variables**

### MySQL Connection Variables (CRITICAL)
These are required for the database API routes to work:

```
MYSQL_HOST=khapmaitpsp.mysql.db
MYSQL_USER=khapmaitpsp
MYSQL_PASSWORD=Lretouiva1226
MYSQL_DATABASE=khapmaitpsp
```

**Apply to:** Production, Preview, and Development environments

### WordPress Headless Integration (Optional - add later)
When connecting to WordPress:

```
NEXT_PUBLIC_WORDPRESS_API_URL=https://headless.twelvy.net/wp-json/wp/v2
NEXT_PUBLIC_SITE_URL=https://www.twelvy.net
```

**Apply to:** Production, Preview, and Development environments

## Steps to Add Variables

1. Go to **Vercel Dashboard** → Select **TWELVY** project
2. Click **Settings** → **Environment Variables**
3. For each variable:
   - Key: `MYSQL_HOST`
   - Value: `khapmaitpsp.mysql.db`
   - Check boxes: ☑️ Production | ☑️ Preview | ☑️ Development
   - Click **Add**
4. Repeat for all 4 MySQL variables
5. **Save** and trigger a redeploy

## Troubleshooting

### If build still fails after adding variables:
1. Go to **Deployments** tab
2. Click **Redeploy** on the latest failed build
3. Vercel will pick up the new environment variables

### If MySQL connection fails at runtime:
1. Verify credentials are correct in OVH panel
2. Check MySQL connection status on OVH
3. Verify Vercel IP is not blocked (OVH allows all IPs by default)

## Verification

After redeploy, test the API routes:
- `https://www.twelvy.net/api/cities` - Should return JSON with cities list
- `https://www.twelvy.net/api/stages/marseille` - Should return stages for Marseille

Both endpoints should respond within 5 seconds.
