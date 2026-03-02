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

  // Fetch all WordPress pages for article URLs
  try {
    const response = await fetch(
      'https://headless.twelvy.net/wp-json/wp/v2/pages?per_page=100&status=publish',
      {
        headers: {
          'Accept': 'application/json',
          'User-Agent': 'Mozilla/5.0 (compatible; TwelvyBot/1.0)',
        },
        next: { revalidate: 3600 },
      }
    )

    if (response.ok) {
      const pages = await response.json()

      for (const page of pages) {
        if (page.slug === 'homepage') continue

        entries.push({
          url: `${SITE_URL}/${page.slug}`,
          lastModified: new Date(page.modified),
          changeFrequency: 'weekly',
          priority: page.parent === 0 ? 0.8 : 0.7,
        })
      }
    }
  } catch {
    // Silently fail — sitemap still returns homepage
  }

  // Fetch cities for stage listing pages
  try {
    const response = await fetch(
      'https://headless.twelvy.net/wp-json/wp/v2/stages-cities',
      {
        headers: {
          'Accept': 'application/json',
          'User-Agent': 'Mozilla/5.0 (compatible; TwelvyBot/1.0)',
        },
        next: { revalidate: 3600 },
      }
    )

    if (response.ok) {
      const data = await response.json()
      const cities: string[] = data.cities || data || []

      for (const city of cities) {
        const slug = city.toLowerCase().replace(/\s+/g, '-')
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
