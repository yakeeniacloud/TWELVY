#!/usr/bin/env python3
"""
Fix layout tables v3 — Clean up orphaned table tags.

Problem: The original migration script's Step 2.10 regex stripped the OPENING
tags of layout tables (<table><tbody><tr><td>) but left orphaned CLOSING tags
(</td><td>[summary]</td></tr></tbody></table>) at the start of content.

This causes browsers to implicitly create tables for orphaned <td> elements,
rendering the summary box content as a table cell.

Fix: Remove all orphaned table structural tags that appear BEFORE the first
properly-opened <table> tag in the content. Keep the actual content (images,
paragraphs, cararra summary boxes) — just strip the table wrappers.
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


def fix_orphaned_table_tags(content):
    """
    Remove orphaned table structural tags that appear before the first
    properly-opened <table> tag in the content.

    These are remnants of layout table opening tags that were incorrectly
    removed by the migration script's Step 2.10 regex.

    Returns (fixed_content, was_modified)
    """
    # Find the first properly-opened <table> tag
    first_table_match = re.search(r'<table[\s>]', content, re.IGNORECASE)
    if not first_table_match:
        # No tables at all — check for orphaned closing tags to clean up
        has_orphaned = bool(re.search(r'</td>|</tr>|</tbody>|</table>', content, re.IGNORECASE))
        if not has_orphaned:
            return content, False
        # Strip all orphaned table tags
        fixed = re.sub(r'</?(table|tbody|tr|td)[^>]*>', '', content, flags=re.IGNORECASE)
        return fixed, True

    pre_table_pos = first_table_match.start()

    # Check if there are any orphaned table tags before the first <table>
    pre_content = content[:pre_table_pos]
    has_orphaned = bool(re.search(r'</?(table|tbody|tr|td)[^>]*>', pre_content, re.IGNORECASE))

    if not has_orphaned:
        return content, False

    # Strip only the table structural tags from the pre-table section
    # Keep all actual content (p, img, div, span, h1-h6, ul, li, a, strong, em, etc.)
    fixed_pre = re.sub(r'</?(table|tbody|tr|td)[^>]*>\s*', '', pre_content, flags=re.IGNORECASE)

    # Clean up extra whitespace/newlines in the fixed section
    fixed_pre = re.sub(r'\n{3,}', '\n\n', fixed_pre)
    fixed_pre = fixed_pre.strip()

    # Reconstruct: fixed pre-table content + rest of content (from first <table> onwards)
    fixed_content = fixed_pre + '\n' + content[pre_table_pos:]

    return fixed_content, True


def strip_remaining_layout_tables(content):
    """
    After fixing orphaned tags, check if there are still any layout tables
    (tables containing h2/h3 in a direct child td) and strip them.
    Uses the conservative h2/h3 heuristic.
    """
    stripped = 0
    kept = 0
    result_parts = []
    pos = 0
    html = content

    while pos < len(html):
        table_start = html.find('<table', pos)
        if table_start == -1:
            result_parts.append(html[pos:])
            break

        result_parts.append(html[pos:table_start])

        # Find end of table
        depth = 0
        i = table_start
        table_end = -1

        while i < len(html):
            chunk = html[i:i+7].lower()
            if chunk[:6] == '<table' and (len(html) <= i+6 or html[i+6] in ' \t\n\r>'):
                depth += 1
                gt = html.find('>', i)
                i = gt + 1 if gt != -1 else i + 7
            elif chunk[:7] == '</table':
                depth -= 1
                gt = html.find('>', i)
                end = gt + 1 if gt != -1 else i + 8
                if depth == 0:
                    table_end = end
                    break
                i = end
            else:
                i += 1

        if table_end == -1:
            result_parts.append(html[pos:])
            break

        table_html = html[table_start:table_end]

        # Check if direct child td contains h2/h3
        is_layout = False
        td_depth = 0
        j = 0
        while j < len(table_html):
            lower_j = table_html[j:j+8].lower()
            if lower_j[:6] == '<table' and (len(table_html) <= j+6 or table_html[j+6] in ' \t\n\r>'):
                td_depth += 1
                gt = table_html.find('>', j)
                j = gt + 1 if gt != -1 else j + 7
            elif lower_j[:7] == '</table':
                td_depth -= 1
                gt = table_html.find('>', j)
                j = gt + 1 if gt != -1 else j + 8
            elif lower_j[:3] == '<td' and (len(table_html) <= j+3 or table_html[j+3] in ' \t\n\r>') and td_depth == 1:
                # Found direct child td — check for h2/h3 inside
                gt = table_html.find('>', j)
                td_start = gt + 1 if gt != -1 else j + 4

                # Find matching </td>
                k = td_start
                inner_depth = 1
                td_end_pos = -1
                while k < len(table_html):
                    inner_chunk = table_html[k:k+8].lower()
                    if inner_chunk[:6] == '<table' and (len(table_html) <= k+6 or table_html[k+6] in ' \t\n\r>'):
                        inner_depth += 1
                        igt = table_html.find('>', k)
                        k = igt + 1 if igt != -1 else k + 7
                    elif inner_chunk[:7] == '</table':
                        inner_depth -= 1
                        igt = table_html.find('>', k)
                        k = igt + 1 if igt != -1 else k + 8
                    elif inner_chunk[:5] == '</td>' and inner_depth == 1:
                        td_end_pos = k
                        break
                    else:
                        k += 1

                if td_end_pos != -1:
                    td_content = table_html[td_start:td_end_pos]
                    if re.search(r'<h[23][\s>]', td_content, re.IGNORECASE):
                        is_layout = True
                        break
                    j = td_end_pos + 5
                else:
                    j += 1
            else:
                j += 1

        if is_layout:
            # Extract all direct td contents
            contents = []
            td_d = 0
            j = 0
            while j < len(table_html):
                lower_j = table_html[j:j+8].lower()
                if lower_j[:6] == '<table' and (len(table_html) <= j+6 or table_html[j+6] in ' \t\n\r>'):
                    td_d += 1
                    gt = table_html.find('>', j)
                    j = gt + 1 if gt != -1 else j + 7
                elif lower_j[:7] == '</table':
                    td_d -= 1
                    gt = table_html.find('>', j)
                    j = gt + 1 if gt != -1 else j + 8
                elif lower_j[:3] == '<td' and (len(table_html) <= j+3 or table_html[j+3] in ' \t\n\r>') and td_d == 1:
                    gt = table_html.find('>', j)
                    td_cs = gt + 1 if gt != -1 else j + 4
                    k = td_cs
                    inner_d = 1
                    while k < len(table_html):
                        ic = table_html[k:k+8].lower()
                        if ic[:6] == '<table' and (len(table_html) <= k+6 or table_html[k+6] in ' \t\n\r>'):
                            inner_d += 1
                            igt = table_html.find('>', k)
                            k = igt + 1 if igt != -1 else k + 7
                        elif ic[:7] == '</table':
                            inner_d -= 1
                            igt = table_html.find('>', k)
                            k = igt + 1 if igt != -1 else k + 8
                        elif ic[:5] == '</td>' and inner_d == 1:
                            contents.append(table_html[td_cs:k].strip())
                            j = k + 5
                            break
                        else:
                            k += 1
                    else:
                        j += 1
                else:
                    j += 1

            result_parts.append('\n'.join(contents))
            stripped += 1
        else:
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
            print(f'  [ERROR] {e}')
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
    print('FIX LAYOUT TABLES v3 — Orphaned tag cleanup')
    print('=' * 60)
    print()

    print('[1/3] Fetching WordPress pages...')
    all_pages = wp_get_all_pages()
    target_pages = [p for p in all_pages if p.get('slug') in FAILED_SLUGS]
    print(f'  Targeting {len(target_pages)} articles')
    print()

    results = {'fixed': [], 'no_change': [], 'errors': []}

    for idx, page in enumerate(target_pages, 1):
        slug = page.get('slug', '')
        title = page.get('title', {}).get('rendered', '')
        content_raw = page.get('content', {}).get('raw', '') or page.get('content', {}).get('rendered', '')
        page_id = page['id']

        print(f'[{idx}/{len(target_pages)}] {slug}')

        if not content_raw:
            print(f'  → Empty content, skipping')
            results['no_change'].append(slug)
            print()
            continue

        # Step 1: Fix orphaned table tags before first proper <table>
        step1_result, was_modified = fix_orphaned_table_tags(content_raw)

        # Step 2: Strip remaining layout tables (h2/h3 in td)
        step2_result, layout_stripped, data_kept = strip_remaining_layout_tables(step1_result)

        final_content = step2_result

        if not was_modified and layout_stripped == 0:
            print(f'  → No changes needed')
            results['no_change'].append(slug)
            print()
            continue

        # Content sanity check
        before_text = re.sub(r'<[^>]+>', '', content_raw).strip()
        after_text = re.sub(r'<[^>]+>', '', final_content).strip()
        before_len = len(before_text)
        after_len = len(after_text)
        ratio = after_len / before_len if before_len > 0 else 0

        changes = []
        if was_modified:
            changes.append('orphaned tags stripped')
        if layout_stripped > 0:
            changes.append(f'{layout_stripped} layout tables stripped')
        if data_kept > 0:
            changes.append(f'{data_kept} data tables kept')

        print(f'  Changes: {", ".join(changes)}')
        print(f'  Content: {before_len} → {after_len} chars ({ratio:.1%} retained)')

        if ratio < 0.85:
            print(f'  ⚠ Unexpected content loss. Let\'s inspect...')
            # Show what was stripped
            print(f'  First 200 chars before: {repr(content_raw[:200])}')
            print(f'  First 200 chars after:  {repr(final_content[:200])}')
            results['errors'].append(f'{slug}: {1-ratio:.0%} content loss')
            print()
            continue

        # Update WordPress
        result_id, status = wp_update_content(page_id, final_content)
        if result_id:
            print(f'  ✓ Updated (WP ID {result_id})')
            results['fixed'].append({'slug': slug, 'changes': changes})
        else:
            print(f'  ✗ Error: {status}')
            results['errors'].append(f'{slug}: {status}')

        print()
        if idx < len(target_pages):
            time.sleep(1.0)

    # Summary
    print()
    print('=' * 60)
    print('SUMMARY')
    print('=' * 60)
    print(f'  Fixed: {len(results["fixed"])}')
    for r in results['fixed']:
        print(f'    ✓ {r["slug"]}: {", ".join(r["changes"])}')
    print(f'  No change needed: {len(results["no_change"])}')
    print(f'  Errors: {len(results["errors"])}')
    for e in results['errors']:
        print(f'    ✗ {e}')

    with open('fix_tables_v3_results.json', 'w', encoding='utf-8') as f:
        json.dump(results, f, indent=2, ensure_ascii=False)
    print()
    print('Saved to fix_tables_v3_results.json')


if __name__ == '__main__':
    main()
