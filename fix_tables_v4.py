#!/usr/bin/env python3
"""
fix_tables_v4.py — Final table structure fix (stack-based approach)

Two root causes identified:
  A) "En savoir plus" table opener in article body — closing tags were removed
     by migration Step 2.10 bug. Fix: insert </td></tr></tbody></table> after
     the first </p> following the unclosed <table>.

  B) Orphan </td></tr></tbody></table> somewhere in article body — opening
     <table> was partially removed by migration bug. Fix: remove the orphan
     closing sequence.

Uses a stack to precisely locate each unclosed opener and orphan closer.

Also fixes:
  - avocat-permis-de-conduire: broken iframe src="iframe_avocat.php"
  - questions-frequentes / toutes-les-questions: dead AddThis script
  - stationnement-interdit / alcool-au-volant / agrements-du-stage: PSP links
  - sens-interdit: broken Google thumbnail
"""

import base64
import json
import re
import time
import urllib.request
import urllib.error

WP_API_BASE = 'https://headless.twelvy.net/wp-json/wp/v2/pages'
WP_USER = 'Yakeen_admin'
WP_APP_PASSWORD = 'UaSM fH38 ONVn JWda 0YSp JBcx'
WP_AUTH = base64.b64encode(f'{WP_USER}:{WP_APP_PASSWORD}'.encode()).decode()
WP_HEADERS = {
    'Authorization': f'Basic {WP_AUTH}',
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'User-Agent': 'Mozilla/5.0 TwelvyFixer/4.0',
}


def wp_get_all_pages():
    all_pages = []
    page_num = 1
    while True:
        url = f'{WP_API_BASE}?per_page=100&page={page_num}&status=publish&context=edit'
        try:
            req = urllib.request.Request(url, headers=WP_HEADERS)
            with urllib.request.urlopen(req, timeout=30) as resp:
                data = json.loads(resp.read().decode('utf-8'))
                total_pages = int(resp.headers.get('X-WP-TotalPages', 1))
                all_pages.extend(data)
                if page_num >= total_pages:
                    break
                page_num += 1
        except Exception as e:
            print(f'  ERROR: {e}')
            break
    return all_pages


def wp_update_page(page_id, raw_content):
    url = f'{WP_API_BASE}/{page_id}'
    payload = json.dumps({'content': raw_content}).encode('utf-8')
    req = urllib.request.Request(url, data=payload, headers=WP_HEADERS, method='POST')
    req.add_header('X-HTTP-Method-Override', 'PUT')
    with urllib.request.urlopen(req, timeout=30) as resp:
        return resp.status


def count_tag(html, tag):
    opens = len(re.findall(rf'<{tag}[\s>]', html, re.IGNORECASE))
    closes = len(re.findall(rf'</{tag}>', html, re.IGNORECASE))
    return opens, closes


def imbalance_score(html):
    total = 0
    for tag in ['table', 'tbody', 'tr', 'td', 'th']:
        o, c = count_tag(html, tag)
        total += abs(o - c)
    return total


# ─────────────────────────────────────────────────────────────────────
# Stack-based table structure fix
# ─────────────────────────────────────────────────────────────────────

def fix_html_tables(content):
    """
    Stack-based fix for table imbalance:
      - Unclosed <table> openers → insert </td></tr></tbody></table> after first </p>
      - Orphan </table> closers → remove along with preceding </td></tr></tbody>
    """
    if imbalance_score(content) == 0:
        return content

    # Collect all table-level events (open/close) in document order
    opens = list(re.finditer(r'<table[^>]*>', content, re.IGNORECASE))
    closes = list(re.finditer(r'</table>', content, re.IGNORECASE))

    events = sorted(
        [(m.start(), 'open', m) for m in opens] +
        [(m.start(), 'close', m) for m in closes],
        key=lambda x: x[0]
    )

    stack = []
    orphan_closes = []   # </table> with no matching opener

    for pos, typ, m in events:
        if typ == 'open':
            stack.append(m)
        else:
            if stack:
                stack.pop()   # Properly matched
            else:
                orphan_closes.append(m)

    unclosed_opens = list(stack)   # <table> with no matching </table>

    if not unclosed_opens and not orphan_closes:
        return content

    # Build list of changes: (start, end, replacement), apply in reverse
    changes = []

    # Fix A: unclosed openers — insert closing after first </p>
    for opener in unclosed_opens:
        search_from = opener.end()

        # Look for first </p> after the opener's <td> content
        p_close = content.find('</p>', search_from)
        if p_close == -1:
            # Fallback: find first </td>
            td_close = content.find('</td>', search_from)
            if td_close != -1:
                insert_at = td_close + len('</td>')
            else:
                continue  # Cannot determine, skip
        else:
            insert_at = p_close + len('</p>')

        changes.append((insert_at, insert_at, '\n</td></tr></tbody></table>'))

    # Fix B: orphan closers — remove along with preceding </td></tr></tbody>
    for closer in orphan_closes:
        close_start = closer.start()
        close_end = closer.end()

        # Greedily consume any </td></tr></tbody> that precede </table>
        before = content[:close_start]
        prefix = re.search(
            r'(\s*</td>)?(\s*</tr>)?(\s*</tbody>)?\s*$',
            before,
            re.IGNORECASE | re.DOTALL,
        )
        if prefix and prefix.group().strip():
            remove_from = prefix.start()
        else:
            remove_from = close_start

        changes.append((remove_from, close_end, ''))

    if not changes:
        return content

    # Apply changes in reverse document order to preserve positions
    changes.sort(key=lambda x: x[0], reverse=True)
    result = content
    for start, end, replacement in changes:
        result = result[:start] + replacement + result[end:]

    return result


# ─────────────────────────────────────────────────────────────────────
# Special fixes
# ─────────────────────────────────────────────────────────────────────

def fix_avocat_iframe(content):
    """Remove broken <iframe src="iframe_avocat.php"> (PSP-specific file)."""
    content = re.sub(
        r'<iframe[^>]*iframe_avocat\.php[^>]*>.*?</iframe>',
        '', content, flags=re.IGNORECASE | re.DOTALL,
    )
    content = re.sub(
        r'<iframe[^>]*iframe_avocat\.php[^>]*/?>',
        '', content, flags=re.IGNORECASE,
    )
    return content


def fix_addthis_widget(content):
    """Remove dead AddThis social sharing script tags."""
    content = re.sub(
        r'<script[^>]*addthis[^>]*>.*?</script>',
        '', content, flags=re.IGNORECASE | re.DOTALL,
    )
    content = re.sub(
        r'<script[^>]*addthis[^>]*/?>',
        '', content, flags=re.IGNORECASE,
    )
    return content


def fix_psp_references(content):
    """Remove <a href="...psp...">text</a> links to PSP domain."""
    content = re.sub(
        r'<a[^>]*(?:psp-copie\.twelvy\.net|prostagespermis\.fr)[^>]*>.*?</a>',
        '', content, flags=re.IGNORECASE | re.DOTALL,
    )
    return content


def fix_broken_gstatic_image(content):
    """Remove broken Google thumbnail from sens-interdit."""
    content = re.sub(
        r'<img[^>]*encrypted-tbn2\.gstatic\.com[^>]*/?>',
        '', content, flags=re.IGNORECASE,
    )
    # Remove empty <p> or <figure> wrappers left behind
    content = re.sub(r'<p>\s*</p>', '', content, flags=re.IGNORECASE)
    content = re.sub(r'<figure[^>]*>\s*</figure>', '', content,
                     flags=re.IGNORECASE | re.DOTALL)
    return content


# ─────────────────────────────────────────────────────────────────────
# Main
# ─────────────────────────────────────────────────────────────────────

SPECIAL_FIXES = {
    'avocat-permis-de-conduire': [fix_avocat_iframe],
    'questions-frequentes':      [fix_addthis_widget],
    'toutes-les-questions':      [fix_addthis_widget],
    'stationnement-interdit':    [fix_psp_references],
    'alcool-au-volant':          [fix_psp_references],
    'agrements-du-stage':        [fix_psp_references],
    'sens-interdit':             [fix_broken_gstatic_image],
}


def main():
    print('=' * 65)
    print('FIX TABLES V4 — STACK-BASED PASS')
    print('=' * 65)
    print()

    try:
        with open('article_quality_report.json', 'r', encoding='utf-8') as f:
            p1 = json.load(f)
        problem_ids = {p['id'] for p in p1['problems']}
        print(f'P1 report: {len(problem_ids)} articles flagged with issues')
    except FileNotFoundError:
        print('ERROR: article_quality_report.json not found. Run p1 audit first.')
        return

    print('Fetching pages (raw content)...')
    pages = wp_get_all_pages()
    print(f'  {len(pages)} pages loaded')
    print()

    fixed = []
    unchanged = []
    errors = []

    for page in pages:
        pid = page.get('id')
        slug = page.get('slug', '?')
        raw = page.get('content', {}).get('raw', '')

        has_table_issue = pid in problem_ids
        has_special_fix = slug in SPECIAL_FIXES

        if not has_table_issue and not has_special_fix:
            continue

        original = raw
        content = raw
        score_before = imbalance_score(content)

        # ── Apply table structure fix ──────────────────────────
        if has_table_issue:
            content = fix_html_tables(content)

        # ── Apply special fixes ────────────────────────────────
        if has_special_fix:
            for fn in SPECIAL_FIXES[slug]:
                content = fn(content)

        score_after = imbalance_score(content)

        # ── Safety check: content retention ───────────────────
        if len(content) < len(original) * 0.85:
            print(f'  ⛔ [{pid}] {slug} — content dropped too much '
                  f'({len(original)} → {len(content)} chars), SKIPPED')
            unchanged.append(slug)
            continue

        if content == original:
            unchanged.append(slug)
            continue

        # ── Update WordPress ───────────────────────────────────
        changed_flags = []
        if content != original and has_table_issue:
            changed_flags.append(f'table {score_before}→{score_after}')
        if has_special_fix and content != raw:
            changed_flags.append('special')

        print(f'  🔧 [{pid}] {slug} ({", ".join(changed_flags)})', end='')

        try:
            status = wp_update_page(pid, content)
            if status in (200, 201):
                fixed.append({'id': pid, 'slug': slug,
                               'score_before': score_before, 'score_after': score_after})
                print(' ✓')
            else:
                print(f' ⚠️  status {status}')
                errors.append(slug)
        except Exception as e:
            print(f'\n    ❌ {e}')
            errors.append(slug)

        time.sleep(0.3)

    print()
    print('=' * 65)
    print('RESULTS')
    print('=' * 65)
    print(f'  ✅ Fixed:      {len(fixed)}')
    print(f'  ⏭️  Unchanged: {len(unchanged)}')
    print(f'  ❌ Errors:    {len(errors)}')

    if fixed:
        print('\nFIXED:')
        for a in fixed:
            print(f'  ✓ [{a["id"]}] {a["slug"]}  imbalance {a["score_before"]}→{a["score_after"]}')

    if errors:
        print('\nERRORS:')
        for s in errors:
            print(f'  ✗ {s}')


if __name__ == '__main__':
    main()
