#!/usr/bin/env python3
"""
Remove dead external links from WordPress articles.
Keeps anchor text, just removes the <a href="..."> wrapper.

Dead links to remove:
- cessionvehicule.fr (TIMEOUT - dead site)
- drivebox.fr (TIMEOUT - dead site)
- controletechnique-online.com (500 + malformed URL)
- nouveaupermis.info (TIMEOUT - dead site)
- www2.securiteroutiere.gouv.fr/data/radars (TIMEOUT - old URL)
- tele7.interieur.gouv.fr (TIMEOUT - old telepoints URL, replaced)
- formulaires.modernisation.gouv.fr (TIMEOUT - old URL)
- vosdroits.service-public.fr (TIMEOUT - old URL)
- psychotestspermis.fr/\ (404 - malformed URL with backslash)
- psychotestspermis.fr/permis-annule (404 - page removed)
"""

import re
import json
import urllib.request

WP_BASE = 'https://headless.twelvy.net/wp-json/wp/v2'
AUTH_USER = 'Yakeen_admin'
AUTH_PASS = 'UaSM fH38 ONVn JWda 0YSp JBcx'
HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Content-Type': 'application/json',
}

DEAD_DOMAINS = [
    'cessionvehicule.fr',
    'drivebox.fr',
    'controletechnique-online.com',
    'nouveaupermis.info',
    'www2.securiteroutiere.gouv.fr',
    'tele7.interieur.gouv.fr',
    'formulaires.modernisation.gouv.fr',
    'vosdroits.service-public.fr',
]

DEAD_URLS = [
    'psychotestspermis.fr/',    # malformed with backslash
    'psychotestspermis.fr/permis-annule',
]

# Articles to fix (slug -> known dead links found)
ARTICLES_TO_FIX = [
    'questions-frequentes',
    'toutes-les-questions',
    'stages-volontaires',
    'infractions',
    'linfraction',
    'les-points-de-permis',
    'radar-automatique',
    'radars-automatiques',
    'nombre-de-points-permis',
    'permis-de-conduire-candidat-libre',
    'permis-international',
    'tests-psychotechniques',
    'delit-fuite',
]


def make_auth_header():
    import base64
    creds = base64.b64encode(f'{AUTH_USER}:{AUTH_PASS}'.encode()).decode()
    return {'Authorization': f'Basic {creds}'}


def wp_get(endpoint):
    url = f'{WP_BASE}/{endpoint}'
    req = urllib.request.Request(url, headers={**HEADERS, **make_auth_header()})
    with urllib.request.urlopen(req, timeout=30) as resp:
        return json.loads(resp.read().decode())


def wp_put(endpoint, data):
    url = f'{WP_BASE}/{endpoint}'
    body = json.dumps(data).encode()
    req = urllib.request.Request(url, data=body, headers={**HEADERS, **make_auth_header()}, method='POST')
    with urllib.request.urlopen(req, timeout=30) as resp:
        return json.loads(resp.read().decode())


def is_dead_link(href):
    """Check if a URL contains a dead domain or URL pattern."""
    for domain in DEAD_DOMAINS:
        if domain in href:
            return True
    for url_fragment in DEAD_URLS:
        if url_fragment in href:
            return True
    return False


def remove_dead_links(content):
    """Remove <a href="...dead...">text</a> tags, keeping the anchor text."""
    def replace_anchor(m):
        attrs = m.group(1)
        text = m.group(2)
        href_match = re.search(r'href=["\']([^"\']*)["\']', attrs, re.IGNORECASE)
        if not href_match:
            return m.group(0)
        href = href_match.group(1)
        if is_dead_link(href):
            return text  # Keep text, strip the link
        return m.group(0)  # Keep as-is

    pattern = r'<a([^>]*)>(.*?)</a>'
    return re.sub(pattern, replace_anchor, content, flags=re.IGNORECASE | re.DOTALL)


def main():
    fixed = []
    skipped = []

    for slug in ARTICLES_TO_FIX:
        print(f'Fetching: {slug}')
        try:
            pages = wp_get(f'pages?slug={slug}&context=edit')
            if not pages:
                print(f'  SKIP: not found')
                skipped.append(slug)
                continue

            page = pages[0]
            original = page['content']['raw']
            cleaned = remove_dead_links(original)

            if cleaned == original:
                print(f'  OK: no dead links found')
                skipped.append(slug)
                continue

            # Count removed links
            removed = []
            for domain in DEAD_DOMAINS + DEAD_URLS:
                if domain in original and domain not in cleaned:
                    removed.append(domain)

            print(f'  FIXING: removed links to {removed}')

            # Update via WordPress API
            wp_put(f'pages/{page["id"]}', {'content': cleaned})
            fixed.append({'slug': slug, 'removed': removed})
            print(f'  DONE ✓')

        except Exception as e:
            print(f'  ERROR: {e}')
            skipped.append(slug)

    print()
    print('=' * 50)
    print(f'Fixed: {len(fixed)} articles')
    print(f'Skipped: {len(skipped)} articles')
    for f in fixed:
        print(f'  ✓ {f["slug"]}: removed {f["removed"]}')


if __name__ == '__main__':
    main()
