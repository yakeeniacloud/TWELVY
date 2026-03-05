#!/usr/bin/env python3
"""
Twelvy Broken Links & Images Crawler

Reads sitemap.xml, fetches each URL, extracts internal links and images,
then tests each one for HTTP errors (4xx / 5xx).

Usage:
    python3 check_broken_links.py

Output:
    - Console: progress + broken items
    - broken_links_report.json: full report
    - broken_links_report.txt: human-readable summary
"""

import re
import json
import time
import urllib.request
import urllib.error
from urllib.parse import urljoin, urlparse

SITE_URL = 'https://www.twelvy.net'
SITEMAP_URL = f'{SITE_URL}/sitemap.xml'

# Domains to ignore (external, not our responsibility)
IGNORE_DOMAINS = {
    'prostagespermis.fr',
    'www.prostagespermis.fr',
    'google.com',
    'www.google.com',
    'maps.google.com',
    'maps.googleapis.com',
    'facebook.com',
    'twitter.com',
    'instagram.com',
    'youtube.com',
    'schema.org',
    'digitalwebsuccess.com',
}

# Extensions to skip (not real pages)
SKIP_EXTENSIONS = {'.pdf', '.doc', '.docx', '.xls', '.xlsx', '.zip', '.mp4', '.mp3'}

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'text/html,application/xhtml+xml,*/*',
}


def http_get(url, timeout=15):
    """Return (status_code, body_text) or (None, error_msg)."""
    try:
        req = urllib.request.Request(url, headers=HEADERS, method='GET')
        with urllib.request.urlopen(req, timeout=timeout) as resp:
            return resp.status, resp.read().decode('utf-8', errors='replace')
    except urllib.error.HTTPError as e:
        return e.code, ''
    except Exception as e:
        return None, str(e)


def http_head(url, timeout=10):
    """Return status_code or None on error."""
    try:
        req = urllib.request.Request(url, headers=HEADERS, method='HEAD')
        with urllib.request.urlopen(req, timeout=timeout) as resp:
            return resp.status
    except urllib.error.HTTPError as e:
        return e.code
    except Exception:
        # Fall back to GET
        code, _ = http_get(url, timeout=10)
        return code


def parse_sitemap(xml_text):
    """Extract all <loc> URLs from sitemap XML."""
    return re.findall(r'<loc>([^<]+)</loc>', xml_text)


def extract_links(html, base_url):
    """Extract all href and src attributes from HTML."""
    links = set()

    # href links
    for href in re.findall(r'href=["\']([^"\']+)["\']', html, re.IGNORECASE):
        href = href.strip()
        if href.startswith('#') or href.startswith('javascript:') or href.startswith('mailto:') or href.startswith('tel:'):
            continue
        full = urljoin(base_url, href)
        links.add(full)

    # src attributes (images, scripts, etc.)
    for src in re.findall(r'src=["\']([^"\']+)["\']', html, re.IGNORECASE):
        src = src.strip()
        if src.startswith('data:') or src.startswith('blob:'):
            continue
        full = urljoin(base_url, src)
        links.add(full)

    return links


def should_check(url):
    """Decide if we should check this URL for errors."""
    parsed = urlparse(url)

    # Skip ignored domains
    if parsed.netloc in IGNORE_DOMAINS:
        return False

    # Skip non-http(s)
    if parsed.scheme not in ('http', 'https'):
        return False

    # Skip specific file extensions
    path_lower = parsed.path.lower()
    for ext in SKIP_EXTENSIONS:
        if path_lower.endswith(ext):
            return False

    # Skip Next.js internals
    if parsed.path.startswith('/_next/'):
        return False

    # Skip WordPress admin
    if '/wp-admin' in parsed.path or '/wp-login' in parsed.path:
        return False

    return True


def is_internal(url):
    parsed = urlparse(url)
    return parsed.netloc in ('www.twelvy.net', 'twelvy.net', 'headless.twelvy.net', '')


def main():
    print('=' * 60)
    print('TWELVY BROKEN LINKS & IMAGES CRAWLER')
    print('=' * 60)
    print()

    # Step 1: Fetch sitemap
    print(f'[1/3] Fetching sitemap: {SITEMAP_URL}')
    sitemap_status, sitemap_xml = http_get(SITEMAP_URL)
    if sitemap_status != 200 or not sitemap_xml:
        print(f'  ERROR: Could not fetch sitemap (status {sitemap_status})')
        return

    page_urls = parse_sitemap(sitemap_xml)
    print(f'  Found {len(page_urls)} URLs in sitemap')
    print()

    # Step 2: Crawl each page and collect all links/images
    print('[2/3] Crawling pages and extracting links...')
    all_links = {}   # url -> set of source pages that reference it
    page_errors = []

    for idx, page_url in enumerate(page_urls, 1):
        print(f'  [{idx}/{len(page_urls)}] {page_url}', end='', flush=True)

        status, html = http_get(page_url)
        if status != 200:
            print(f' → HTTP {status} ✗')
            page_errors.append({'url': page_url, 'status': status, 'type': 'page'})
            continue

        print(f' → {status} OK')

        # Extract all links and images
        links = extract_links(html, page_url)
        for link in links:
            if not should_check(link):
                continue
            if link not in all_links:
                all_links[link] = set()
            all_links[link].add(page_url)

        time.sleep(0.3)  # Be polite

    print(f'\n  Total unique URLs to verify: {len(all_links)}')
    print()

    # Step 3: Test each extracted URL
    print('[3/3] Testing extracted URLs for broken links...')
    broken = []
    checked = 0
    skipped = 0

    for link, sources in sorted(all_links.items()):
        checked += 1
        parsed = urlparse(link)

        # Skip already-crawled sitemap pages (we checked them above)
        if link in page_urls:
            skipped += 1
            continue

        print(f'  [{checked}/{len(all_links)}] {link[:80]}...', end='\r', flush=True)

        status = http_head(link)

        if status is None:
            broken.append({
                'url': link,
                'status': 'TIMEOUT/ERROR',
                'type': 'image' if any(link.endswith(ext) for ext in ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg', '.ico']) else 'link',
                'found_on': sorted(sources),
            })
        elif status >= 400:
            broken.append({
                'url': link,
                'status': status,
                'type': 'image' if any(link.endswith(ext) for ext in ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg', '.ico']) else 'link',
                'found_on': sorted(sources),
            })

        time.sleep(0.2)

    print(' ' * 100, end='\r')  # Clear the progress line

    # Summary
    print()
    print('=' * 60)
    print('RESULTS')
    print('=' * 60)
    print()

    if page_errors:
        print(f'PAGES WITH ERRORS ({len(page_errors)}):')
        for e in page_errors:
            print(f'  ✗ [{e["status"]}] {e["url"]}')
        print()
    else:
        print('  ✓ All sitemap pages loaded successfully')
        print()

    broken_images = [b for b in broken if b['type'] == 'image']
    broken_links_list = [b for b in broken if b['type'] == 'link']

    if broken_images:
        print(f'BROKEN IMAGES ({len(broken_images)}):')
        for b in broken_images:
            print(f'  ✗ [{b["status"]}] {b["url"]}')
            for src in b['found_on'][:2]:
                print(f'       Found on: {src}')
        print()
    else:
        print('  ✓ No broken images found')
        print()

    if broken_links_list:
        print(f'BROKEN LINKS ({len(broken_links_list)}):')
        for b in broken_links_list:
            print(f'  ✗ [{b["status"]}] {b["url"]}')
            for src in b['found_on'][:2]:
                print(f'       Found on: {src}')
        print()
    else:
        print('  ✓ No broken links found')
        print()

    # Save JSON report
    report = {
        'site': SITE_URL,
        'pages_checked': len(page_urls),
        'links_checked': checked,
        'page_errors': page_errors,
        'broken_images': broken_images,
        'broken_links': broken_links_list,
        'total_broken': len(page_errors) + len(broken),
    }

    with open('broken_links_report.json', 'w', encoding='utf-8') as f:
        json.dump(report, f, indent=2, ensure_ascii=False)

    # Save text report
    with open('broken_links_report.txt', 'w', encoding='utf-8') as f:
        f.write(f'TWELVY BROKEN LINKS REPORT\n')
        f.write(f'{"=" * 60}\n\n')
        f.write(f'Pages in sitemap: {len(page_urls)}\n')
        f.write(f'Links/images checked: {checked}\n')
        f.write(f'Total broken: {len(page_errors) + len(broken)}\n\n')

        if page_errors:
            f.write(f'BROKEN PAGES ({len(page_errors)}):\n')
            for e in page_errors:
                f.write(f'  [{e["status"]}] {e["url"]}\n')
            f.write('\n')

        if broken_images:
            f.write(f'BROKEN IMAGES ({len(broken_images)}):\n')
            for b in broken_images:
                f.write(f'  [{b["status"]}] {b["url"]}\n')
                for src in b['found_on'][:3]:
                    f.write(f'    → found on: {src}\n')
            f.write('\n')

        if broken_links_list:
            f.write(f'BROKEN LINKS ({len(broken_links_list)}):\n')
            for b in broken_links_list:
                f.write(f'  [{b["status"]}] {b["url"]}\n')
                for src in b['found_on'][:3]:
                    f.write(f'    → found on: {src}\n')

    print(f'Reports saved: broken_links_report.json / broken_links_report.txt')
    print()
    print(f'TOTAL BROKEN: {len(page_errors) + len(broken)}')


if __name__ == '__main__':
    main()
