#!/usr/bin/env python3
"""
Audit script: Analyze all PSP articles for table issues and title mismatches.
Compares title_menu vs titre, scans contenu for layout vs data tables.
"""

import re
import html
import json
from collections import defaultdict

SQL_DUMP_FILE = 'prostagepsp_mysql_db.sql'

def fix_encoding(text):
    if not text:
        return text
    try:
        return text.encode('latin-1').decode('utf-8')
    except (UnicodeDecodeError, UnicodeEncodeError):
        return text

def clean_text(text):
    if not text:
        return ''
    t = fix_encoding(text)
    t = t.replace('\\r\\n', '\n').replace('\\n', '\n').replace('\\r', '\n')
    t = t.replace('\\"', '"').replace("\\'", "'").replace('\\\\', '\\')
    t = html.unescape(t)
    t = html.unescape(t)
    return t.strip()

def parse_sql_fields(record_str):
    fields = []
    current = ''
    in_string = False
    escape_next = False

    for i, c in enumerate(record_str):
        if escape_next:
            current += c
            escape_next = False
            continue
        if c == '\\':
            current += c
            escape_next = True
            continue
        if c == "'" and not in_string:
            in_string = True
            continue
        if c == "'" and in_string:
            if i + 1 < len(record_str) and record_str[i + 1] == "'":
                current += "'"
                continue
            in_string = False
            continue
        if c == ',' and not in_string:
            fields.append(current.strip())
            current = ''
            continue
        current += c

    fields.append(current.strip())
    return fields

def parse_insert_buffer(buffer):
    articles = []
    pos = 0
    while True:
        start = buffer.find('(', pos)
        if start == -1:
            break
        depth = 0
        in_string = False
        escape_next = False
        i = start
        while i < len(buffer):
            c = buffer[i]
            if escape_next:
                escape_next = False
                i += 1
                continue
            if c == '\\':
                escape_next = True
                i += 1
                continue
            if c == "'" and not in_string:
                in_string = True
                i += 1
                continue
            if c == "'" and in_string:
                if i + 1 < len(buffer) and buffer[i + 1] == "'":
                    i += 2
                    continue
                in_string = False
                i += 1
                continue
            if not in_string:
                if c == '(':
                    depth += 1
                elif c == ')':
                    depth -= 1
                    if depth == 0:
                        break
            i += 1
        record_str = buffer[start + 1:i]
        pos = i + 1

        if "'www.prostagespermis.fr'" not in record_str:
            continue

        fields = parse_sql_fields(record_str)
        if len(fields) < 20:
            continue

        articles.append(fields)
    return articles


def is_layout_table(table_html):
    """
    Determine if a table is a layout table (shouldn't be a table)
    vs a data table (should remain a table).

    Layout table heuristics:
    - Contains <h2>, <h3>, <p> tags inside <td>
    - Has only 1-2 rows with large text blocks
    - <td> contains more than 200 chars of text

    Data table heuristics:
    - Contains <th> or <thead>
    - Multiple rows (>3) with short cell content
    - Contains numbers/prices/percentages
    """
    # Strong indicators of data table
    if '<th' in table_html.lower() or '<thead' in table_html.lower():
        # Check if it also has paragraph content - if so, mixed/broken
        td_content = re.findall(r'<td[^>]*>(.*?)</td>', table_html, re.DOTALL | re.IGNORECASE)
        has_paragraphs = any('<p>' in td or '<h2>' in td or '<h3>' in td for td in td_content)
        if not has_paragraphs:
            return False  # Clean data table

    # Count rows
    row_count = len(re.findall(r'<tr', table_html, re.IGNORECASE))

    # Extract td content
    td_contents = re.findall(r'<td[^>]*>(.*?)</td>', table_html, re.DOTALL | re.IGNORECASE)

    if not td_contents:
        return True  # Empty table = layout table to remove

    # Check for layout indicators inside tds
    layout_indicators = 0
    data_indicators = 0

    for td in td_contents:
        td_stripped = re.sub(r'<[^>]+>', '', td).strip()
        td_len = len(td_stripped)

        # Layout: contains headings or paragraphs
        if re.search(r'<h[1-6]', td, re.IGNORECASE):
            layout_indicators += 3
        if '<p>' in td.lower() or '</p>' in td.lower():
            layout_indicators += 2
        if '<ul>' in td.lower() or '<ol>' in td.lower():
            layout_indicators += 1
        # Layout: large text block
        if td_len > 200:
            layout_indicators += 2
        # Layout: contains another table (nested)
        if '<table' in td.lower():
            layout_indicators += 1

        # Data: short content (typical for data cells)
        if td_len < 50 and td_len > 0:
            data_indicators += 1
        # Data: contains numbers/prices
        if re.search(r'\d+[,.]?\d*\s*(?:€|km/h|%|pts|points)', td_stripped):
            data_indicators += 2
        # Data: contains "retrait", "amende", "vitesse" keywords typical of data tables
        if re.search(r'(?:retrait|amende|vitesse|alcool|km/h|classe|délit|contravention)', td_stripped, re.IGNORECASE):
            data_indicators += 1

    # Decision
    if layout_indicators > data_indicators:
        return True
    elif data_indicators > layout_indicators:
        return False
    else:
        # Tiebreak: if only 1-2 rows and tds have lots of text, it's layout
        if row_count <= 2 and any(len(re.sub(r'<[^>]+>', '', td).strip()) > 100 for td in td_contents):
            return True
        return False


def extract_tables(html_content):
    """Extract all top-level table blocks from HTML."""
    tables = []
    pos = 0
    content = html_content

    while True:
        start = content.find('<table', pos)
        if start == -1:
            break

        # Find matching closing </table> accounting for nesting
        depth = 0
        i = start
        while i < len(content):
            if content[i:i+7].lower() == '<table>':
                depth += 1
                i += 7
            elif content[i:i+7].lower() == '</table':
                depth -= 1
                end = content.find('>', i) + 1
                if depth == 0:
                    tables.append(content[start:end])
                    pos = end
                    break
                i = end
            elif content[i:i+6].lower() == '<table':
                depth += 1
                i = content.find('>', i) + 1
            else:
                i += 1
        else:
            break

    return tables


def main():
    print('Parsing SQL dump...')

    all_records = []

    with open(SQL_DUMP_FILE, 'r', encoding='latin-1') as f:
        in_contenu = False
        buffer = ''

        for line in f:
            if 'INSERT INTO `contenu`' in line:
                in_contenu = True
                vals_idx = line.find('VALUES')
                buffer = line[vals_idx + 6:] if vals_idx > 0 else ''
                continue

            if in_contenu:
                buffer += line
                if line.strip().endswith(';'):
                    in_contenu = False
                    all_records.extend(parse_insert_buffer(buffer))
                    buffer = ''

    print(f'Found {len(all_records)} raw records')

    # Filter to active, non-deleted PSP articles
    articles = []
    for fields in all_records:
        try:
            actif = int(fields[1]) if fields[1].isdigit() else 0
            deleted = int(fields[2]) if fields[2].isdigit() else 0
        except:
            continue

        if actif != 1 or deleted != 0:
            continue

        url = clean_text(fields[4])
        title_menu = clean_text(fields[7])
        num_menu = fields[8]
        meta_title = clean_text(fields[11])
        titre = clean_text(fields[15])
        contenu = clean_text(fields[16])

        # Skip montpellier and skip-listed
        if not url or 'montpellier' in url.lower() or url.endswith('.html'):
            continue
        if not contenu or len(contenu) < 100:
            continue

        articles.append({
            'url': url,
            'title_menu': title_menu,
            'titre': titre,
            'meta_title': meta_title,
            'has_table': '<table' in contenu.lower(),
            'contenu_len': len(contenu),
            'contenu': contenu,
        })

    print(f'Filtered to {len(articles)} active articles')

    # ================================================================
    # ANALYSIS 1: Title mismatches
    # ================================================================
    title_mismatches = []

    for a in articles:
        url = a['url']
        title_menu = a['title_menu']
        titre = a['titre']
        meta_title = a['meta_title']

        issues = []

        # Check if title_menu differs significantly from titre
        if title_menu and titre:
            # Normalize for comparison
            t1 = title_menu.lower().strip()
            t2 = titre.lower().strip()

            if t1 != t2:
                # Check if one contains the other
                if t1 in t2 or t2 in t1:
                    # Minor variation - title_menu is truncated version
                    issues.append(f'title_menu is truncated version of titre')
                else:
                    # Genuinely different
                    issues.append(f'DIFFERENT title_menu vs titre')

        if issues:
            title_mismatches.append({
                'url': url,
                'title_menu': title_menu,
                'titre': titre,
                'meta_title': meta_title,
                'issues': issues,
            })

    # ================================================================
    # ANALYSIS 2: Table issues
    # ================================================================
    table_issues = []
    layout_table_articles = []
    data_table_articles = []

    for a in articles:
        if not a['has_table']:
            continue

        url = a['url']
        contenu = a['contenu']

        # Extract all tables
        tables = extract_tables(contenu)

        if not tables:
            continue

        article_layout_tables = []
        article_data_tables = []

        for t in tables:
            if is_layout_table(t):
                # Get first 200 chars of text content to describe it
                preview = re.sub(r'<[^>]+>', ' ', t[:500]).strip()
                preview = re.sub(r'\s+', ' ', preview)[:150]
                article_layout_tables.append(preview)
            else:
                preview = re.sub(r'<[^>]+>', ' ', t[:300]).strip()
                preview = re.sub(r'\s+', ' ', preview)[:100]
                article_data_tables.append(preview)

        if article_layout_tables:
            layout_table_articles.append({
                'url': url,
                'titre': a['titre'],
                'layout_table_count': len(article_layout_tables),
                'data_table_count': len(article_data_tables),
                'layout_table_previews': article_layout_tables,
            })
        elif article_data_tables:
            data_table_articles.append({
                'url': url,
                'titre': a['titre'],
                'data_table_count': len(article_data_tables),
            })

    # ================================================================
    # OUTPUT RESULTS
    # ================================================================

    print()
    print('=' * 60)
    print('TITLE MISMATCH ANALYSIS')
    print('=' * 60)
    print(f'Articles with title_menu ≠ titre: {len(title_mismatches)}')

    genuinely_different = [t for t in title_mismatches if 'DIFFERENT' in ' '.join(t['issues'])]
    truncated = [t for t in title_mismatches if 'truncated' in ' '.join(t['issues'])]

    print(f'  - Genuinely different (not truncation): {len(genuinely_different)}')
    print(f'  - title_menu is truncated titre: {len(truncated)}')

    if genuinely_different:
        print()
        print('GENUINELY DIFFERENT titles:')
        for t in genuinely_different[:20]:
            print(f"  {t['url']}")
            print(f"    title_menu : {t['title_menu']}")
            print(f"    titre      : {t['titre']}")
            print(f"    meta_title : {t['meta_title']}")
            print()

    print()
    print('=' * 60)
    print('TABLE ANALYSIS')
    print('=' * 60)
    print(f'Total articles: {len(articles)}')
    print(f'Articles with any table: {sum(1 for a in articles if a["has_table"])}')
    print(f'Articles with LAYOUT tables (broken): {len(layout_table_articles)}')
    print(f'Articles with only DATA tables (ok): {len(data_table_articles)}')

    print()
    print('ARTICLES WITH BROKEN LAYOUT TABLES:')
    for a in layout_table_articles:
        print(f"  {a['url']} — {a['titre']}")
        print(f"    Layout tables: {a['layout_table_count']}, Data tables: {a['data_table_count']}")
        for i, preview in enumerate(a['layout_table_previews'][:2]):
            print(f"    Layout table {i+1} preview: {preview}")
        print()

    # Save full results to JSON
    results = {
        'total_articles': len(articles),
        'title_mismatches': {
            'genuinely_different': genuinely_different,
            'truncated': [{'url': t['url'], 'title_menu': t['title_menu'], 'titre': t['titre']} for t in truncated],
        },
        'tables': {
            'layout_table_articles': layout_table_articles,
            'data_table_articles': [{'url': a['url'], 'titre': a['titre']} for a in data_table_articles],
        }
    }

    with open('audit_results.json', 'w', encoding='utf-8') as f:
        json.dump(results, f, indent=2, ensure_ascii=False)

    print(f'Full results saved to audit_results.json')


if __name__ == '__main__':
    main()
