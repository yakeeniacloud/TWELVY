import { MetadataRoute } from 'next'

const SITE_URL = 'https://www.twelvy.net'

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const entries: MetadataRoute.Sitemap = []

  // Homepage
  entries.push({
    url: SITE_URL,
    lastModified: new Date(),
    changeFrequency: 'daily',
    priority: 1.0,
  })

  // Fetch all WordPress pages for article URLs (paginated — WP caps at 100/page)
  try {
    const allPages: any[] = []
    for (let page = 1; page <= 3; page++) {
      const response = await fetch(
        `https://headless.twelvy.net/wp-json/wp/v2/pages?per_page=100&page=${page}&status=publish`,
        {
          headers: {
            'Accept': 'application/json',
            'User-Agent': 'Mozilla/5.0 (compatible; TwelvyBot/1.0)',
          },
          next: { revalidate: 3600 },
        }
      )

      if (!response.ok) break
      const pages = await response.json()
      if (pages.length === 0) break
      allPages.push(...pages)
    }

    for (const page of allPages) {
      if (page.slug === 'homepage') continue

      entries.push({
        url: `${SITE_URL}/${page.slug}`,
        lastModified: new Date(page.modified),
        changeFrequency: 'weekly',
        priority: page.parent === 0 ? 0.8 : 0.7,
      })
    }
  } catch {
    // Silently fail — sitemap still returns homepage
  }

  // Fetch WordPress posts for /blog/[slug] URLs
  try {
    const response = await fetch(
      'https://headless.twelvy.net/wp-json/wp/v2/posts?per_page=100&status=publish&_fields=slug,modified',
      {
        headers: {
          'Accept': 'application/json',
          'User-Agent': 'Mozilla/5.0 (compatible; TwelvyBot/1.0)',
        },
        next: { revalidate: 3600 },
      }
    )

    if (response.ok) {
      const posts = await response.json()

      // Blog listing page
      entries.push({
        url: `${SITE_URL}/blog`,
        lastModified: new Date(),
        changeFrequency: 'weekly',
        priority: 0.7,
      })

      // Individual blog posts
      for (const post of posts) {
        entries.push({
          url: `${SITE_URL}/blog/${post.slug}`,
          lastModified: new Date(post.modified),
          changeFrequency: 'monthly',
          priority: 0.6,
        })
      }
    }
  } catch {
    // Silently fail
  }

  // Fetch cities for stage listing pages from PHP API
  try {
    const response = await fetch(
      'https://api.twelvy.net/cities.php',
      {
        headers: {
          'Content-Type': 'application/json',
        },
        next: { revalidate: 3600 },
      }
    )

    if (response.ok) {
      const data = await response.json()
      const cities = data.cities || []

      for (const city of cities) {
        const name = typeof city === 'object' ? city.name : city
        const slug = name.toLowerCase().replace(/\s+/g, '-')
        entries.push({
          url: `${SITE_URL}/stages-recuperation-points/${slug}`,
          lastModified: new Date(),
          changeFrequency: 'daily',
          priority: 0.9,
        })
      }
    }
  } catch {
    // Silently fail
  }

  return entries
}
