#!/usr/bin/env python3
"""
P1 - Article Quality Audit

Fetches all WordPress pages and checks each one for HTML structure issues
that could be left over from the layout table fixes (fix_layout_tables_v3.py).

Checks:
  - Mismatched table tags (<table> vs </table>, <tr> vs </tr>, <td> vs </td>)
  - Content starting with an orphan closing tag (</td>, </tr>, etc.)
  - Very short content after stripping HTML (possible content loss)
  - Relative image paths not starting with http (broken image refs)
  - Images referencing old PSP domain (prostagespermis.fr or similar)

Output:
  - Console: summary + problem list
  - article_quality_report.json: full structured report
  - article_quality_report.txt: human-readable report
"""

import json
import re
import urllib.request
import urllib.error

WP_BASE = 'https://headless.twelvy.net/wp-json/wp/v2'
HEADERS = {
    'Accept': 'application/json',
    'User-Agent': 'Mozilla/5.0 (compatible; TwelvyQualityBot/1.0)',
}

PSP_DOMAINS = ['prostagespermis.fr', 'www.prostagespermis.fr', 'psp-copie.twelvy.net']


def wp_get(url):
    req = urllib.request.Request(url, headers=HEADERS)
    with urllib.request.urlopen(req, timeout=30) as resp:
        data = json.loads(resp.read().decode('utf-8'))
        total_pages = int(resp.headers.get('X-WP-TotalPages', 1))
        return data, total_pages


def fetch_all_pages():
    """Fetch all published pages from WordPress (handles pagination)."""
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


def count_tags(html, tag):
    """Count opening and closing occurrences of a tag."""
    opens = len(re.findall(rf'<{tag}[\s>]', html, re.IGNORECASE))
    closes = len(re.findall(rf'</{tag}>', html, re.IGNORECASE))
    return opens, closes


def check_article(page):
    """Analyze a WordPress page and return a list of issues."""
    issues = []
    slug = page.get('slug', 'unknown')
    content = page.get('content', {}).get('rendered', '')

    if not content or not content.strip():
        issues.append('EMPTY: page has no rendered content')
        return issues

    # 1. Check for mismatched table structure tags
    for tag in ['table', 'tbody', 'thead', 'tr', 'td', 'th']:
        opens, closes = count_tags(content, tag)
        if opens != closes:
            issues.append(f'MISMATCHED <{tag}>: {opens} open vs {closes} close')

    # 2. Check if content starts with an orphan closing tag
    stripped = content.strip()
    orphan_match = re.match(r'^(</?(?:td|tr|tbody|thead|table|th)\b[^>]*>)', stripped, re.IGNORECASE)
    if orphan_match and stripped.startswith('</'):
        issues.append(f'ORPHAN_TAG: content starts with {orphan_match.group(1)[:40]}')

    # 3. Check for relative image paths (not http/https, not data:)
    img_srcs = re.findall(r'<img[^>]+src=["\']([^"\']+)["\']', content, re.IGNORECASE)
    for src in img_srcs:
        src_stripped = src.strip()
        if src_stripped.startswith('data:') or src_stripped.startswith('blob:'):
            continue
        if not src_stripped.startswith('http'):
            issues.append(f'RELATIVE_IMG: {src_stripped[:80]}')

    # 4. Check for images still pointing to PSP domain
    for psp_domain in PSP_DOMAINS:
        if psp_domain in content:
            issues.append(f'PSP_REF: content still references {psp_domain}')
            break  # Report once per article

    # 5. Check for .bmp image references (any domain)
    bmp_refs = re.findall(r'src=["\'][^"\']*\.bmp["\']', content, re.IGNORECASE)
    for bmp in bmp_refs:
        issues.append(f'BMP_REF: {bmp[:80]}')

    # 6. Content length sanity check (after stripping HTML)
    text_only = re.sub(r'<[^>]+>', ' ', content)
    text_only = re.sub(r'\s+', ' ', text_only).strip()
    if len(text_only) < 150:
        issues.append(f'SHORT_CONTENT: only {len(text_only)} chars of text')

    return issues


def main():
    print('=' * 65)
    print('P1 — ARTICLE QUALITY AUDIT')
    print('=' * 65)
    print()

    print('Fetching all WordPress pages...')
    pages = fetch_all_pages()
    print(f'  Found {len(pages)} pages')
    print()

    problems = []
    clean = []

    for i, page in enumerate(pages, 1):
        slug = page.get('slug', f'unknown-{i}')
        pid = page.get('id', '?')
        print(f'  [{i:3}/{len(pages)}] Checking: {slug[:55]}', end='')

        issues = check_article(page)

        if issues:
            problems.append({
                'slug': slug,
                'id': pid,
                'url': f'https://www.twelvy.net/{slug}',
                'issues': issues,
            })
            print(f' → {len(issues)} issue(s) ⚠️')
        else:
            clean.append(slug)
            print(' → OK ✓')

    print()
    print('=' * 65)
    print('RESULTS')
    print('=' * 65)
    print(f'  ✅ Clean:          {len(clean)}')
    print(f'  ⚠️  With issues:   {len(problems)}')
    print()

    if problems:
        print('PROBLEMS FOUND:')
        print('-' * 65)
        for p in problems:
            print(f'\n🔴 [{p["id"]}] {p["slug"]}')
            print(f'   {p["url"]}')
            for issue in p['issues']:
                print(f'   ⚠️  {issue}')

    # Save reports
    report = {
        'total_pages': len(pages),
        'clean': len(clean),
        'with_issues': len(problems),
        'problems': problems,
    }

    with open('article_quality_report.json', 'w', encoding='utf-8') as f:
        json.dump(report, f, indent=2, ensure_ascii=False)

    with open('article_quality_report.txt', 'w', encoding='utf-8') as f:
        f.write('ARTICLE QUALITY AUDIT REPORT\n')
        f.write('=' * 65 + '\n\n')
        f.write(f'Total pages checked: {len(pages)}\n')
        f.write(f'Clean: {len(clean)}\n')
        f.write(f'With issues: {len(problems)}\n\n')
        if problems:
            f.write('PROBLEMS:\n')
            f.write('-' * 65 + '\n')
            for p in problems:
                f.write(f'\n[{p["id"]}] {p["slug"]}\n')
                f.write(f'  {p["url"]}\n')
                for issue in p['issues']:
                    f.write(f'  - {issue}\n')
        else:
            f.write('No problems found.\n')

    print()
    print('Reports saved: article_quality_report.json / article_quality_report.txt')


if __name__ == '__main__':
    main()
