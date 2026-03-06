#!/usr/bin/env python3
"""
Blog migration: blog.prostagespermis.fr -> WordPress Headless (headless.twelvy.net)
Fetches all 70 published posts and imports them as WordPress posts.
"""

import json
import time
import base64
import urllib.request
import urllib.error
import re

# ============================================================
# CONFIGURATION
# ============================================================

SOURCE_API = 'https://blog.prostagespermis.fr/wp-json/wp/v2'
TARGET_API = 'https://headless.twelvy.net/wp-json/wp/v2'

WP_USER = 'Yakeen_admin'
WP_APP_PASSWORD = 'UaSM fH38 ONVn JWda 0YSp JBcx'
WP_AUTH = base64.b64encode(f'{WP_USER}:{WP_APP_PASSWORD}'.encode()).decode()

HEADERS_AUTH = {
    'Content-Type': 'application/json',
    'Authorization': f'Basic {WP_AUTH}',
    'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
}

HEADERS_READ = {
    'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
}

# ============================================================
# FETCH POSTS FROM SOURCE
# ============================================================

def fetch_all_posts():
    """Fetch all published posts from blog.prostagespermis.fr (paginated)."""
    posts = []
    page = 1
    per_page = 100

    while True:
        url = (f'{SOURCE_API}/posts?per_page={per_page}&page={page}'
               f'&status=publish&_fields=id,slug,title,content,excerpt,date,date_gmt')
        print(f'  Fetching page {page} from source...')

        req = urllib.request.Request(url, headers=HEADERS_READ, method='GET')
        try:
            with urllib.request.urlopen(req, timeout=30) as resp:
                total_pages = int(resp.headers.get('X-WP-TotalPages', 1))
                total_posts = int(resp.headers.get('X-WP-Total', 0))
                data = json.loads(resp.read().decode('utf-8'))
                posts.extend(data)
                print(f'  Got {len(data)} posts (total: {total_posts}, pages: {total_pages})')
                if page >= total_pages:
                    break
                page += 1
        except Exception as e:
            print(f'  ERROR fetching page {page}: {e}')
            break

    return posts


# ============================================================
# CONTENT CLEANING
# ============================================================

def clean_content(content_html):
    """Light cleaning of blog post HTML content."""
    if not content_html:
        return ''

    text = content_html

    # Fix internal links: blog.prostagespermis.fr/slug -> /blog/slug
    text = re.sub(
        r'href="https?://blog\.prostagespermis\.fr/([^"]+)"',
        r'href="/blog/\1"',
        text
    )
    # Fix links to main site: prostagespermis.fr/slug -> /slug
    text = re.sub(
        r'href="https?://(?:www\.)?prostagespermis\.fr/([^"]+)"',
        r'href="/\1"',
        text
    )

    return text.strip()


def strip_html_excerpt(html_str):
    """Strip HTML tags and trim excerpt to 200 chars."""
    text = re.sub(r'<[^>]+>', '', html_str or '')
    text = re.sub(r'\s+', ' ', text).strip()
    if len(text) > 200:
        text = text[:197] + '...'
    return text


# ============================================================
# WORDPRESS TARGET API
# ============================================================

def wp_get_post_by_slug(slug):
    """Check if a post already exists in headless.twelvy.net."""
    url = f'{TARGET_API}/posts?slug={slug}&status=publish,draft'
    req = urllib.request.Request(url, headers=HEADERS_AUTH, method='GET')
    try:
        with urllib.request.urlopen(req, timeout=30) as resp:
            posts = json.loads(resp.read().decode('utf-8'))
            if posts and len(posts) > 0:
                return posts[0]
    except urllib.error.HTTPError as e:
        print(f'  [WARN] GET slug={slug} -> HTTP {e.code}')
    except Exception as e:
        print(f'  [WARN] GET slug={slug} -> {e}')
    return None


def wp_create_post(title, slug, content, excerpt, date, date_gmt):
    """Create a new WordPress post in headless.twelvy.net."""
    data = json.dumps({
        'title': title,
        'slug': slug,
        'content': content,
        'excerpt': excerpt,
        'status': 'publish',
        'date': date,
        'date_gmt': date_gmt,
        'format': 'standard',
    }).encode('utf-8')

    req = urllib.request.Request(
        f'{TARGET_API}/posts', data=data, headers=HEADERS_AUTH, method='POST'
    )
    try:
        with urllib.request.urlopen(req, timeout=60) as resp:
            result = json.loads(resp.read().decode('utf-8'))
            return result.get('id'), 'CREATED'
    except urllib.error.HTTPError as e:
        body = e.read().decode('utf-8') if e.fp else ''
        print(f'  [ERROR] CREATE -> HTTP {e.code}: {body[:300]}')
        return None, f'ERROR {e.code}'
    except Exception as e:
        print(f'  [ERROR] CREATE -> {e}')
        return None, f'ERROR {e}'


def wp_update_post(post_id, title, content, excerpt):
    """Update an existing WordPress post in headless.twelvy.net."""
    url = f'{TARGET_API}/posts/{post_id}?_method=PUT'
    data = json.dumps({
        'title': title,
        'content': content,
        'excerpt': excerpt,
        'status': 'publish',
    }).encode('utf-8')

    req = urllib.request.Request(url, data=data, headers=HEADERS_AUTH, method='POST')
    try:
        with urllib.request.urlopen(req, timeout=60) as resp:
            result = json.loads(resp.read().decode('utf-8'))
            return result.get('id'), 'UPDATED'
    except urllib.error.HTTPError as e:
        body = e.read().decode('utf-8') if e.fp else ''
        print(f'  [ERROR] UPDATE {post_id} -> HTTP {e.code}: {body[:300]}')
        return None, f'ERROR {e.code}'
    except Exception as e:
        print(f'  [ERROR] UPDATE {post_id} -> {e}')
        return None, f'ERROR {e}'


# ============================================================
# MAIN
# ============================================================

def main():
    print('=' * 70)
    print('BLOG MIGRATION: blog.prostagespermis.fr -> headless.twelvy.net')
    print('=' * 70)
    print()

    # Step 1: Fetch all posts from source
    print('[1/3] Fetching posts from blog.prostagespermis.fr...')
    posts = fetch_all_posts()
    print(f'  Total: {len(posts)} posts fetched')
    print()

    if not posts:
        print('No posts found. Exiting.')
        return

    # Step 2: Import each post
    print('[2/3] Importing posts to headless.twelvy.net...')
    print()

    results = {'created': [], 'updated': [], 'errors': [], 'skipped': []}
    total = len(posts)

    for idx, post in enumerate(posts, 1):
        slug = post.get('slug', '')
        title = post.get('title', {}).get('rendered', '')
        content_raw = post.get('content', {}).get('rendered', '')
        excerpt_raw = post.get('excerpt', {}).get('rendered', '')
        date = post.get('date', '')
        date_gmt = post.get('date_gmt', '')

        excerpt_clean = strip_html_excerpt(excerpt_raw)
        content_clean = clean_content(content_raw)

        print(f'[{idx}/{total}] {slug}')
        print(f'  Title: {title[:70]}')
        print(f'  Content: {len(content_clean)} chars | Excerpt: {len(excerpt_clean)} chars')

        if not content_clean or len(content_clean) < 50:
            print(f'  -> SKIP (empty content)')
            results['skipped'].append(slug)
            print()
            continue

        # Check if already exists
        existing = wp_get_post_by_slug(slug)

        if existing:
            wp_id = existing['id']
            print(f'  Found existing post ID {wp_id} -> Updating...')
            result_id, status = wp_update_post(wp_id, title, content_clean, excerpt_clean)
        else:
            print(f'  -> Creating new post...')
            result_id, status = wp_create_post(
                title, slug, content_clean, excerpt_clean, date, date_gmt
            )

        if result_id:
            key = 'updated' if existing else 'created'
            results[key].append(f'{slug} (WP ID {result_id})')
            print(f'  -> {status} (WP ID {result_id})')
        else:
            results['errors'].append(f'{slug}: {status}')

        print()

        # Rate limit: avoid overwhelming OVH server
        if idx < total:
            time.sleep(1.0)

    # Step 3: Summary
    print()
    print('=' * 70)
    print('[3/3] MIGRATION SUMMARY')
    print('=' * 70)
    print(f'  Created:  {len(results["created"])}')
    for item in results['created']:
        print(f'    + {item}')
    print(f'  Updated:  {len(results["updated"])}')
    for item in results['updated']:
        print(f'    ~ {item}')
    print(f'  Skipped:  {len(results["skipped"])}')
    print(f'  Errors:   {len(results["errors"])}')
    for item in results['errors']:
        print(f'    x {item}')
    print()
    print(f'Total migrated: {len(results["created"]) + len(results["updated"])}')

    with open('migrate_blog_log.json', 'w', encoding='utf-8') as f:
        json.dump(results, f, indent=2, ensure_ascii=False)
    print('Log saved to migrate_blog_log.json')


if __name__ == '__main__':
    main()
