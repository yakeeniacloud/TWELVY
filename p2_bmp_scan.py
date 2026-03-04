#!/usr/bin/env python3
"""
P2 - BMP Images Comprehensive Scan

Fetches all WordPress pages and scans content for:
  - Any .bmp image references (should have been converted to .png/.webp)
  - Any relative image paths (not starting with http) → likely broken
  - Any src attributes referencing old PSP/external domains

Also queries the WordPress media library for any .bmp files still hosted there.

Output:
  - Console: findings
  - bmp_scan_report.json: full structured report
  - bmp_scan_report.txt: human-readable report
"""

import json
import re
import urllib.request
import urllib.error

WP_BASE = 'https://headless.twelvy.net/wp-json/wp/v2'
HEADERS = {
    'Accept': 'application/json',
    'User-Agent': 'Mozilla/5.0 (compatible; TwelvyBMPBot/1.0)',
}

# Domains that are OK (not broken)
OK_DOMAINS = {
    'headless.twelvy.net',
    'www.twelvy.net',
    'twelvy.net',
    'maps.googleapis.com',
    'maps.google.com',
}


def wp_get(url):
    req = urllib.request.Request(url, headers=HEADERS)
    with urllib.request.urlopen(req, timeout=30) as resp:
        data = json.loads(resp.read().decode('utf-8'))
        total_pages = int(resp.headers.get('X-WP-TotalPages', 1))
        return data, total_pages


def fetch_all_pages():
    all_pages = []
    page = 1
    while True:
        url = f'{WP_BASE}/pages?per_page=100&status=publish&page={page}&context=view'
        try:
            data, total_pages = wp_get(url)
            if not data:
                break
            all_pages.extend(data)
            if page >= total_pages:
                break
            page += 1
        except Exception as e:
            print(f'  ERROR fetching page {page}: {e}')
            break
    return all_pages


def fetch_media_library():
    """Try to list media from WP REST API (public read-only)."""
    media_items = []
    page = 1
    while True:
        url = f'{WP_BASE}/media?per_page=100&page={page}&mime_type=image/bmp'
        try:
            data, total_pages = wp_get(url)
            if not data:
                break
            media_items.extend(data)
            if page >= total_pages:
                break
            page += 1
        except urllib.error.HTTPError as e:
            if e.code == 401:
                print('  (Media library requires auth — skipping library scan, content scan only)')
            break
        except Exception as e:
            print(f'  Media library error: {e}')
            break
    return media_items


def scan_content(page):
    """Scan page content for image issues. Returns list of findings."""
    findings = []
    slug = page.get('slug', 'unknown')
    content = page.get('content', {}).get('rendered', '')

    if not content:
        return findings

    # Find all src attributes
    srcs = re.findall(r'src=["\']([^"\']+)["\']', content, re.IGNORECASE)

    for src in srcs:
        src = src.strip()

        # Skip data URIs and blob URLs
        if src.startswith('data:') or src.startswith('blob:'):
            continue

        # Skip Next.js static assets
        if src.startswith('/_next/'):
            continue

        # 1. BMP reference (any domain)
        if re.search(r'\.bmp(\?|$)', src, re.IGNORECASE):
            findings.append({'type': 'BMP_IMAGE', 'src': src})

        # 2. Relative path (not starting with http)
        elif not src.startswith('http'):
            findings.append({'type': 'RELATIVE_PATH', 'src': src})

        # 3. External domain that's not OK
        else:
            # Extract domain
            domain_match = re.match(r'https?://([^/]+)', src)
            if domain_match:
                domain = domain_match.group(1)
                if domain not in OK_DOMAINS:
                    # Flag non-OK external image domains
                    findings.append({'type': 'EXTERNAL_DOMAIN', 'src': src[:100], 'domain': domain})

    # Also check href attributes for .bmp links
    hrefs = re.findall(r'href=["\']([^"\']+\.bmp)["\']', content, re.IGNORECASE)
    for href in hrefs:
        findings.append({'type': 'BMP_HREF', 'src': href})

    return findings


def main():
    print('=' * 65)
    print('P2 — BMP IMAGES COMPREHENSIVE SCAN')
    print('=' * 65)
    print()

    # 1. Check media library for BMP files
    print('[1/2] Checking WordPress media library for BMP files...')
    media_bmps = fetch_media_library()
    if media_bmps:
        print(f'  Found {len(media_bmps)} BMP file(s) in media library:')
        for m in media_bmps:
            print(f'    ID {m.get("id")}: {m.get("source_url", "?")}')
    else:
        print('  No BMP files found in media library (or auth required).')
    print()

    # 2. Scan all page content
    print('[2/2] Fetching all WordPress pages and scanning content...')
    pages = fetch_all_pages()
    print(f'  Found {len(pages)} pages — scanning...')
    print()

    results = []
    bmp_articles = []
    relative_articles = []
    external_articles = []

    for i, page in enumerate(pages, 1):
        slug = page.get('slug', f'unknown-{i}')
        findings = scan_content(page)

        if findings:
            entry = {
                'slug': slug,
                'id': page.get('id'),
                'url': f'https://www.twelvy.net/{slug}',
                'findings': findings,
            }
            results.append(entry)

            has_bmp = any(f['type'] in ('BMP_IMAGE', 'BMP_HREF') for f in findings)
            has_relative = any(f['type'] == 'RELATIVE_PATH' for f in findings)
            has_external = any(f['type'] == 'EXTERNAL_DOMAIN' for f in findings)

            if has_bmp:
                bmp_articles.append(entry)
            if has_relative:
                relative_articles.append(entry)
            if has_external:
                external_articles.append(entry)

    print('=' * 65)
    print('RESULTS')
    print('=' * 65)
    print(f'  BMP references in content:     {len(bmp_articles)} article(s)')
    print(f'  Relative image paths:          {len(relative_articles)} article(s)')
    print(f'  External domain images:        {len(external_articles)} article(s)')
    print()

    if bmp_articles:
        print('BMP REFERENCES:')
        for a in bmp_articles:
            print(f'  🔴 [{a["id"]}] {a["slug"]}')
            for f in a['findings']:
                if f['type'] in ('BMP_IMAGE', 'BMP_HREF'):
                    print(f'       {f["src"]}')
        print()

    if relative_articles:
        print('RELATIVE IMAGE PATHS (likely broken):')
        for a in relative_articles:
            print(f'  🟡 [{a["id"]}] {a["slug"]}')
            for f in a['findings']:
                if f['type'] == 'RELATIVE_PATH':
                    print(f'       {f["src"][:80]}')
        print()

    if external_articles:
        print('EXTERNAL DOMAIN IMAGES (informational):')
        seen_domains = set()
        for a in external_articles:
            for f in a['findings']:
                if f['type'] == 'EXTERNAL_DOMAIN':
                    d = f['domain']
                    if d not in seen_domains:
                        print(f'  ℹ️  Domain: {d}')
                        seen_domains.add(d)
        print()

    # Save reports
    report = {
        'media_bmp_files': [m.get('source_url') for m in media_bmps],
        'total_pages_scanned': len(pages),
        'bmp_articles': bmp_articles,
        'relative_path_articles': relative_articles,
        'external_domain_articles': external_articles,
    }

    with open('bmp_scan_report.json', 'w', encoding='utf-8') as f:
        json.dump(report, f, indent=2, ensure_ascii=False)

    with open('bmp_scan_report.txt', 'w', encoding='utf-8') as f:
        f.write('BMP IMAGES COMPREHENSIVE SCAN REPORT\n')
        f.write('=' * 65 + '\n\n')
        f.write(f'Pages scanned: {len(pages)}\n\n')

        if media_bmps:
            f.write('BMP FILES IN MEDIA LIBRARY:\n')
            for m in media_bmps:
                f.write(f'  {m.get("source_url", "?")}\n')
            f.write('\n')

        if bmp_articles:
            f.write('BMP REFERENCES IN CONTENT:\n')
            for a in bmp_articles:
                f.write(f'  [{a["id"]}] {a["slug"]}\n')
                for fi in a['findings']:
                    if fi['type'] in ('BMP_IMAGE', 'BMP_HREF'):
                        f.write(f'    {fi["src"]}\n')
            f.write('\n')

        if relative_articles:
            f.write('RELATIVE IMAGE PATHS:\n')
            for a in relative_articles:
                f.write(f'  [{a["id"]}] {a["slug"]}\n')
                for fi in a['findings']:
                    if fi['type'] == 'RELATIVE_PATH':
                        f.write(f'    {fi["src"][:80]}\n')
            f.write('\n')

        if not bmp_articles and not relative_articles:
            f.write('No BMP or broken relative image references found.\n')

    print('Reports saved: bmp_scan_report.json / bmp_scan_report.txt')


if __name__ == '__main__':
    main()
