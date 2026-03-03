#!/usr/bin/env python3
"""
Fix layout tables v2 - conservative heuristic.

Only strips tables where a <td> contains <h2> or <h3> headings.
This is the most reliable indicator of PSP layout tables vs data tables.

Targets only the 26 articles that failed in v1 due to incorrect detection.
"""

import re
import json
import base64
import time
import urllib.request
import urllib.error

WP_API_BASE = 'https://headless.twelvy.net/wp-json/wp/v2/pages'
WP_USER = 'Yakeen_admin'
WP_APP_PASSWORD = 'UaSM fH38 ONVn JWda 0YSp JBcx'
WP_AUTH = base64.b64encode(f'{WP_USER}:{WP_APP_PASSWORD}'.encode()).decode()

WP_HEADERS = {
    'Content-Type': 'application/json',
    'Authorization': f'Basic {WP_AUTH}',
    'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
}

# The 26 articles that failed in v1 (need targeted fixing)
FAILED_SLUGS = [
    'stage-rattrapage-points', 'delit-fuite', 'amende-feu-rouge',
    'loi-permis-a-points-2011', 'nombre-de-points-permis', 'legalite',
    'annulation-permis', 'retrait-de-permis', 'suspension-de-permis-et-retrait-de-permis',
    'conduite-sans-permis', 'permis-etranger', 'permis-probatoire',
    'lettre-48n', 'les-points-de-permis', 'amende-forfaitaire-majoree',
    'radar-feu-rouge', 'radar-fixe', 'sens-interdit', 'non-respect-du-stop',
    'feu-rouge-et-feu-orange', 'non-port-ceinture-de-securite', 'telephone-au-volant',
    'refus-de-priorite', 'les-conditions-dinscription',
    'stage-de-sensibilisation-a-la-securite-routiere', 'stage-de-recuperation-de-points',
]


def find_table_end(html, start):
    """Find index after closing </table>, handling nesting."""
    depth = 0
    i = start

    while i < len(html):
        chunk = html[i:i+7].lower()

        if chunk == '<table>' or (html[i:i+6].lower() == '<table' and
                                   i + 6 < len(html) and html[i+6] in ' \t\n\r>'):
            depth += 1
            next_gt = html.find('>', i)
            i = next_gt + 1 if next_gt != -1 else i + 7

        elif chunk == '</table':
            depth -= 1
            next_gt = html.find('>', i)
            end = next_gt + 1 if next_gt != -1 else i + 8
            if depth == 0:
                return end
            i = end
        else:
            i += 1

    return -1


def get_direct_td_contents(table_html):
    """
    Extract content of DIRECT child <td> elements only.
    Does not recurse into nested tables.
    Returns list of (td_content_string) for each direct td.
    """
    contents = []
    depth = 0  # table nesting depth (outer table = depth 1)
    i = 0

    while i < len(table_html):
        lower = table_html[i:i+8].lower()

        # Entering a table
        if lower[:6] == '<table' and (len(table_html) <= i+6 or table_html[i+6] in ' \t\n\r>'):
            depth += 1
            tag_end = table_html.find('>', i)
            i = tag_end + 1 if tag_end != -1 else i + 7

        # Leaving a table
        elif lower[:7] == '</table':
            depth -= 1
            tag_end = table_html.find('>', i)
            i = tag_end + 1 if tag_end != -1 else i + 8

        # Direct child <td> (at depth 1, since outer table is depth 1)
        elif lower[:3] == '<td' and (len(table_html) <= i+3 or table_html[i+3] in ' \t\n\r>') and depth == 1:
            tag_end = table_html.find('>', i)
            td_content_start = tag_end + 1 if tag_end != -1 else i + 4

            # Find the matching </td> at same depth (depth 1)
            # We need to find </td> that's not inside a nested table
            j = td_content_start
            td_depth = 1  # we're at the outer table level

            while j < len(table_html):
                inner_lower = table_html[j:j+8].lower()

                if inner_lower[:6] == '<table' and (len(table_html) <= j+6 or table_html[j+6] in ' \t\n\r>'):
                    td_depth += 1
                    inner_end = table_html.find('>', j)
                    j = inner_end + 1 if inner_end != -1 else j + 7

                elif inner_lower[:7] == '</table':
                    td_depth -= 1
                    inner_end = table_html.find('>', j)
                    j = inner_end + 1 if inner_end != -1 else j + 8

                elif inner_lower[:5] == '</td>' and td_depth == 1:
                    # Found the closing </td> at the outer table level
                    td_content = table_html[td_content_start:j]
                    contents.append(td_content.strip())
                    i = j + 5
                    break
                else:
                    j += 1
            else:
                # Unclosed td
                i = len(table_html)
        else:
            i += 1

    return contents


def is_layout_table_conservative(table_html):
    """
    CONSERVATIVE heuristic: a table is a layout table ONLY if
    a direct child <td> contains <h2> or <h3> headings.

    This is the most reliable indicator for PSP's content.
    PSP layout tables wrap article sections (which have h2/h3 headers).
    PSP data tables contain rows of data (never h2/h3 in cells).
    """
    # Get direct child td contents
    td_contents = get_direct_td_contents(table_html)

    for td in td_contents:
        # Check if this td contains h2 or h3
        if re.search(r'<h[23][\s>]', td, re.IGNORECASE):
            return True

    return False


def strip_layout_tables(html_content):
    """
    Strip layout tables using conservative h2/h3 heuristic.
    Returns (fixed_html, stripped_count, kept_count)
    """
    result_parts = []
    pos = 0
    html = html_content
    stripped = 0
    kept = 0

    while pos < len(html):
        # Find next table
        table_start = html.find('<table', pos)
        if table_start == -1:
            result_parts.append(html[pos:])
            break

        # Append content before this table
        result_parts.append(html[pos:table_start])

        # Find end of this table
        table_end = find_table_end(html, table_start)
        if table_end == -1:
            # Broken table - skip to end
            result_parts.append('')
            pos = len(html)
            stripped += 1
            break

        table_html = html[table_start:table_end]

        if is_layout_table_conservative(table_html):
            # Extract direct td contents and concatenate
            td_contents = get_direct_td_contents(table_html)
            replacement = '\n'.join(td_contents)
            result_parts.append(replacement)
            stripped += 1
        else:
            # Keep data table as-is
            result_parts.append(table_html)
            kept += 1

        pos = table_end

    return ''.join(result_parts), stripped, kept


# ================================================================
# WORDPRESS API
# ================================================================

def wp_get_all_pages():
    all_pages = []
    page = 1
    while True:
        url = f'{WP_API_BASE}?per_page=100&page={page}&status=publish&context=edit'
        req = urllib.request.Request(url, headers=WP_HEADERS, method='GET')
        try:
            with urllib.request.urlopen(req, timeout=30) as resp:
                data = json.loads(resp.read().decode())
                if not data:
                    break
                all_pages.extend(data)
                if len(data) < 100:
                    break
                page += 1
        except Exception as e:
            print(f'  [ERROR] Fetching page {page}: {e}')
            break
    return all_pages


def wp_update_content(page_id, content):
    url = f'{WP_API_BASE}/{page_id}?_method=PUT'
    data = json.dumps({'content': content}).encode('utf-8')
    req = urllib.request.Request(url, data=data, headers=WP_HEADERS, method='POST')
    try:
        with urllib.request.urlopen(req, timeout=60) as resp:
            result = json.loads(resp.read().decode())
            return result.get('id'), 'OK'
    except urllib.error.HTTPError as e:
        body = e.read().decode() if e.fp else ''
        return None, f'HTTP {e.code}: {body[:100]}'
    except Exception as e:
        return None, str(e)


# ================================================================
# MAIN
# ================================================================

def main():
    print('=' * 60)
    print('FIX LAYOUT TABLES v2 — Conservative heuristic (h2/h3 in td)')
    print('=' * 60)
    print()

    print('[1/3] Fetching WordPress pages...')
    all_pages = wp_get_all_pages()
    print(f'  Found {len(all_pages)} pages total')

    # Filter to only the slugs that failed in v1
    target_pages = [p for p in all_pages if p.get('slug') in FAILED_SLUGS]
    print(f'  Targeting {len(target_pages)} articles from v1 failures')
    print()

    results = {'fixed': [], 'no_layout_tables': [], 'errors': []}

    for idx, page in enumerate(target_pages, 1):
        slug = page.get('slug', '')
        title = page.get('title', {}).get('rendered', '')
        content_raw = page.get('content', {}).get('raw', '') or page.get('content', {}).get('rendered', '')
        page_id = page['id']

        print(f'[{idx}/{len(target_pages)}] {slug} — {title[:45]}')

        if not content_raw or '<table' not in content_raw.lower():
            print(f'  → No tables in content')
            results['no_layout_tables'].append(slug)
            print()
            continue

        table_count = len(re.findall(r'<table', content_raw, re.IGNORECASE))

        # Apply conservative fix
        fixed_content, stripped, kept = strip_layout_tables(content_raw)

        print(f'  Tables: {table_count} total | Layout stripped (h2/h3 rule): {stripped} | Data kept: {kept}')

        if stripped == 0:
            print(f'  → No layout tables found (data tables only)')
            results['no_layout_tables'].append(slug)
            print()
            continue

        # Content retention check
        before_len = len(re.sub(r'<[^>]+>', '', content_raw).strip())
        after_len = len(re.sub(r'<[^>]+>', '', fixed_content).strip())
        ratio = after_len / before_len if before_len > 0 else 0

        print(f'  Content: {before_len} → {after_len} chars ({ratio:.1%} retained)')

        if ratio < 0.80:
            print(f'  ⚠ Unexpected content loss ({1-ratio:.0%}). Skipping.')
            results['errors'].append(f'{slug}: unexpected loss {1-ratio:.0%}')
            print()
            continue

        # Update WordPress
        result_id, status = wp_update_content(page_id, fixed_content)
        if result_id:
            print(f'  ✓ Updated (WP ID {result_id})')
            results['fixed'].append({
                'slug': slug,
                'title': title,
                'stripped': stripped,
                'kept': kept,
            })
        else:
            print(f'  ✗ Error: {status}')
            results['errors'].append(f'{slug}: {status}')

        print()
        if idx < len(target_pages):
            time.sleep(1.0)

    # Summary
    print()
    print('=' * 60)
    print('[3/3] SUMMARY')
    print('=' * 60)
    print(f'  Fixed: {len(results["fixed"])}')
    for r in results['fixed']:
        print(f'    ✓ {r["slug"]}: removed {r["stripped"]} layout tables, kept {r["kept"]} data tables')
    print()
    print(f'  No layout tables (data only): {len(results["no_layout_tables"])}')
    for s in results['no_layout_tables']:
        print(f'    — {s}')
    print()
    print(f'  Errors / Skipped: {len(results["errors"])}')
    for e in results['errors']:
        print(f'    ✗ {e}')

    with open('fix_tables_v2_results.json', 'w', encoding='utf-8') as f:
        json.dump(results, f, indent=2, ensure_ascii=False)
    print()
    print('Results saved to fix_tables_v2_results.json')


if __name__ == '__main__':
    main()
