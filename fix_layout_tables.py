#!/usr/bin/env python3
"""
Fix layout tables in WordPress articles.
Works from current WordPress content (preserves image URLs and previous fixes).

PSP used HTML tables for page layout (2-column designs).
The migration script preserved these layout tables — this script strips them.

Strategy:
- Fetch each WordPress article
- Detect layout tables (tables used for layout, not data)
- Extract td content and inline it as regular HTML
- Leave data tables (price tables, speed limits, etc.) unchanged
- Update WordPress with fixed content
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


# ================================================================
# TABLE DETECTION
# ================================================================

def is_layout_table(table_html):
    """
    Determine if a table is a layout table (should be stripped)
    vs a data table (should be kept).

    Layout table: used for page layout, contains paragraphs/headings/long text
    Data table: contains structured data (prices, speeds, penalties)
    """
    # Strong data table indicators
    has_th = bool(re.search(r'<th[\s>]', table_html, re.IGNORECASE))
    has_thead = bool(re.search(r'<thead[\s>]', table_html, re.IGNORECASE))

    # Extract td contents
    td_contents = re.findall(r'<td[^>]*>(.*?)</td>', table_html, re.DOTALL | re.IGNORECASE)

    if not td_contents:
        return True  # Empty table = layout

    layout_score = 0
    data_score = 0

    if has_th or has_thead:
        data_score += 5  # Strong data indicator

    for td in td_contents:
        td_text = re.sub(r'<[^>]+>', '', td).strip()
        td_len = len(td_text)

        # Layout indicators
        if re.search(r'<h[1-6][\s>]', td, re.IGNORECASE):
            layout_score += 3
        if re.search(r'<p[\s>]', td, re.IGNORECASE):
            layout_score += 2
        if re.search(r'<ul[\s>]|<ol[\s>]', td, re.IGNORECASE):
            layout_score += 1
        if td_len > 200:
            layout_score += 3
        if td_len > 500:
            layout_score += 3

        # Data indicators
        if re.search(r'\d+[,.]?\d*\s*(?:€|km/h|%\b)', td_text):
            data_score += 2
        if re.search(r'\b(?:retrait|amende|vitesse|alcool|contravention|délit|classe)\b',
                     td_text, re.IGNORECASE):
            data_score += 1
        if td_len < 80 and td_len > 2:
            data_score += 1

    # Decision
    if has_th or has_thead:
        # Even with th/thead, if it contains heavy layout content, it's hybrid
        if layout_score > data_score * 2:
            return True
        return False

    if layout_score > data_score:
        return True

    # Tiebreak: single large td = layout
    row_count = len(re.findall(r'<tr[\s>]', table_html, re.IGNORECASE))
    if row_count <= 2 and layout_score >= data_score:
        large_tds = [td for td in td_contents
                     if len(re.sub(r'<[^>]+>', '', td).strip()) > 100]
        if large_tds:
            return True

    return False


def find_table_end(html, start):
    """Find the index after the closing </table> tag, handling nesting."""
    depth = 0
    i = start

    while i < len(html):
        # Look for <table or </table>
        lower = html[i:i+7].lower()

        if lower == '<table>' or (html[i:i+6].lower() == '<table' and
                                   i + 6 < len(html) and html[i+6] in ' \t\n\r>'):
            depth += 1
            next_gt = html.find('>', i)
            i = next_gt + 1 if next_gt != -1 else i + 7

        elif lower == '</table':
            depth -= 1
            next_gt = html.find('>', i)
            end = next_gt + 1 if next_gt != -1 else i + 8
            if depth == 0:
                return end
            i = end
        else:
            i += 1

    return -1  # Unclosed table


def extract_layout_table_content(table_html):
    """
    Extract content from a layout table.
    Removes the table/tr/td wrapper, keeps all td inner HTML in sequence.
    """
    # Remove nested tables temporarily (we'll handle them in the main loop)
    # Just get the direct td contents of this table

    # Strip the outer table/tbody/tr/td structure
    # Strategy: remove table, tbody, tr tags; keep td content separated by newlines
    result = table_html

    # Remove <table ...> and </table>
    result = re.sub(r'^<table[^>]*>', '', result.strip(), flags=re.IGNORECASE)
    result = re.sub(r'</table>$', '', result.strip(), flags=re.IGNORECASE)

    # Remove <tbody> and </tbody>
    result = re.sub(r'<tbody[^>]*>', '', result, flags=re.IGNORECASE)
    result = re.sub(r'</tbody>', '', result, flags=re.IGNORECASE)

    # Remove <tr> and </tr> (replace </tr> with newline to separate rows)
    result = re.sub(r'<tr[^>]*>', '', result, flags=re.IGNORECASE)
    result = re.sub(r'</tr>', '\n', result, flags=re.IGNORECASE)

    # Extract td content: remove <td ...> and </td> wrappers
    result = re.sub(r'<td[^>]*>', '', result, flags=re.IGNORECASE)
    result = re.sub(r'</td>', '\n', result, flags=re.IGNORECASE)

    # Clean up excessive whitespace/newlines
    result = re.sub(r'\n{3,}', '\n\n', result)
    result = result.strip()

    return result


def strip_layout_tables(html_content):
    """
    Iteratively find and strip layout tables from HTML content.
    Processes one table at a time, restarting after each fix.
    Data tables are left unchanged.

    Returns (fixed_html, tables_stripped_count)
    """
    result = html_content
    tables_stripped = 0
    max_iterations = 30  # Safety limit

    for iteration in range(max_iterations):
        # Find the first <table in content
        match = re.search(r'<table', result, re.IGNORECASE)
        if not match:
            break

        start = match.start()
        end = find_table_end(result, start)

        if end == -1:
            # Unclosed table - just remove it
            next_start = result.find('<table', start + 1)
            if next_start == -1:
                # No more tables, remove the broken one
                result = result[:start]
            else:
                result = result[:start] + result[next_start:]
            break

        table_html = result[start:end]

        if is_layout_table(table_html):
            # Extract content and replace table
            inner_content = extract_layout_table_content(table_html)
            result = result[:start] + inner_content + result[end:]
            tables_stripped += 1
        else:
            # Data table - skip past it by temporarily marking it
            # We need to skip this table and continue looking for layout tables after it
            # Replace table marker temporarily to avoid re-processing
            marker = f'__DATA_TABLE_{tables_stripped}__'
            result = result[:start] + marker + result[end:]

            # Continue looking - but after all iterations, restore markers
            # Actually this approach is complex. Let's use a different strategy:
            # Process tables in reverse order (last first) to avoid index shifting

    # Restore any data table markers (if we used them)
    # Actually the iterative approach without markers works if we track position

    return result, tables_stripped


def strip_layout_tables_v2(html_content):
    """
    Better approach: process all tables, decide per-table.
    Builds result by going through the HTML sequentially.
    """
    result = []
    pos = 0
    tables_stripped = 0
    tables_kept = 0
    html = html_content

    while pos < len(html):
        # Find next table
        table_start = html.find('<table', pos)
        if table_start == -1:
            result.append(html[pos:])
            break

        # Append content before this table
        result.append(html[pos:table_start])

        # Find end of this table
        table_end = find_table_end(html, table_start)

        if table_end == -1:
            # Broken/unclosed table — skip to end
            result.append('')  # Remove broken table
            pos = len(html)
            tables_stripped += 1
            break

        table_html = html[table_start:table_end]

        if is_layout_table(table_html):
            # Extract td content and inline it
            inner = extract_layout_table_content(table_html)
            result.append(inner)
            tables_stripped += 1
        else:
            # Keep data table as-is
            result.append(table_html)
            tables_kept += 1

        pos = table_end

    return ''.join(result), tables_stripped, tables_kept


# ================================================================
# WORDPRESS API
# ================================================================

def wp_get_all_pages():
    """Fetch all published pages from WordPress."""
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
        except urllib.error.HTTPError as e:
            print(f'  [ERROR] Fetching pages page {page}: HTTP {e.code}')
            break
        except Exception as e:
            print(f'  [ERROR] Fetching pages page {page}: {e}')
            break

    return all_pages


def wp_update_content(page_id, content):
    """Update a page's content only."""
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
    print('FIX LAYOUT TABLES IN WORDPRESS')
    print('=' * 60)
    print()

    # Step 1: Fetch all WordPress pages
    print('[1/3] Fetching all WordPress pages...')
    pages = wp_get_all_pages()
    print(f'  Found {len(pages)} pages total')

    # Step 2: Identify pages with tables
    pages_with_tables = []
    for page in pages:
        slug = page.get('slug', '')
        title = page.get('title', {}).get('rendered', '')
        content_raw = page.get('content', {}).get('raw', '') or page.get('content', {}).get('rendered', '')

        # Skip category/parent pages and city pages
        if not content_raw:
            continue
        if slug in ['les-stages-permis-a-points', 'les-contraventions', 'le-retrait-de-points',
                    'services', 'homepage']:
            continue
        if slug.startswith('stages-') and len(slug.split('-')) <= 2:
            # City page like stages-marseille
            continue

        if '<table' in content_raw.lower():
            pages_with_tables.append({
                'id': page['id'],
                'slug': slug,
                'title': title,
                'content': content_raw,
            })

    print(f'  Pages with tables: {len(pages_with_tables)}')
    print()

    # Step 3: Process each page
    print('[2/3] Processing pages...')
    print()

    results = {
        'fixed': [],
        'no_change_needed': [],
        'errors': [],
    }

    for idx, page in enumerate(pages_with_tables, 1):
        slug = page['slug']
        title = page['title']
        content = page['content']
        page_id = page['id']

        print(f'[{idx}/{len(pages_with_tables)}] {slug} — {title[:50]}')

        # Count tables before
        table_count_before = len(re.findall(r'<table', content, re.IGNORECASE))

        # Apply fix
        fixed_content, stripped, kept = strip_layout_tables_v2(content)

        table_count_after = len(re.findall(r'<table', fixed_content, re.IGNORECASE))

        if stripped == 0:
            print(f'  → No layout tables found (kept {kept} data tables)')
            results['no_change_needed'].append(slug)
            print()
            continue

        print(f'  Tables before: {table_count_before} | Layout tables stripped: {stripped} | Data tables kept: {kept}')

        # Sanity check: make sure we didn't lose too much content
        content_len_before = len(re.sub(r'<[^>]+>', '', content).strip())
        content_len_after = len(re.sub(r'<[^>]+>', '', fixed_content).strip())
        ratio = content_len_after / content_len_before if content_len_before > 0 else 0

        print(f'  Content length: {content_len_before} → {content_len_after} chars ({ratio:.1%} retained)')

        if ratio < 0.7:
            print(f'  ⚠ WARNING: Lost >30% of content! Skipping this page.')
            results['errors'].append(f'{slug}: content loss {1-ratio:.0%}')
            print()
            continue

        # Update WordPress
        result_id, status = wp_update_content(page_id, fixed_content)
        if result_id:
            print(f'  ✓ Updated (WP ID {result_id})')
            results['fixed'].append({
                'slug': slug,
                'title': title,
                'layout_tables_removed': stripped,
                'data_tables_kept': kept,
            })
        else:
            print(f'  ✗ Error: {status}')
            results['errors'].append(f'{slug}: {status}')

        print()

        # Rate limit
        if idx < len(pages_with_tables):
            time.sleep(1.0)

    # Step 4: Summary
    print()
    print('=' * 60)
    print('[3/3] SUMMARY')
    print('=' * 60)
    print(f'  Fixed (layout tables removed): {len(results["fixed"])}')
    for r in results['fixed']:
        print(f'    ✓ {r["slug"]}: removed {r["layout_tables_removed"]} layout, kept {r["data_tables_kept"]} data')
    print()
    print(f'  No change needed: {len(results["no_change_needed"])}')
    print(f'  Errors: {len(results["errors"])}')
    for e in results['errors']:
        print(f'    ✗ {e}')

    # Save results
    with open('fix_tables_results.json', 'w', encoding='utf-8') as f:
        json.dump(results, f, indent=2, ensure_ascii=False)
    print()
    print('Results saved to fix_tables_results.json')


if __name__ == '__main__':
    main()
