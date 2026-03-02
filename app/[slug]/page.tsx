import { Metadata } from 'next'
import { notFound } from 'next/navigation'
import WordPressPageContent from './WordPressPageContent'
import seoData from '@/lib/seo-data.json'

const WP_HEADERS = {
  'Accept': 'application/json',
  'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
}

interface WPPage {
  id: number
  title: { rendered: string }
  content: { rendered: string }
  excerpt: { rendered: string }
  slug: string
  parent: number
}

async function getPageBySlug(slug: string): Promise<WPPage | null> {
  try {
    const response = await fetch(
      `https://headless.twelvy.net/wp-json/wp/v2/pages?slug=${slug}`,
      { headers: WP_HEADERS, next: { revalidate: 30 } }
    )
    if (!response.ok) return null
    const pages = await response.json()
    if (!Array.isArray(pages) || pages.length === 0) return null
    return pages[0]
  } catch {
    return null
  }
}

async function getMenuStructure() {
  try {
    const response = await fetch(
      'https://headless.twelvy.net/wp-json/wp/v2/pages?per_page=100&status=publish&orderby=menu_order&order=asc',
      { headers: WP_HEADERS, next: { revalidate: 30 } }
    )
    if (!response.ok) return []
    const pages = await response.json()

    const filteredPages = pages.filter((page: { slug: string; parent: number }) => {
      if (page.slug === 'homepage') return false
      if (page.parent !== 0) return true
      const isCityStagesPage = page.slug.match(/^stages-[a-z]+-\d+/)
        || (page.slug.startsWith('stages-') && page.slug.split('-').length === 2)
      return !isCityStagesPage
    })

    const parentPages = filteredPages.filter((p: { parent: number }) => p.parent === 0)
    return parentPages
      .map((parent: { id: number; title: { rendered: string }; slug: string }) => ({
        id: parent.id,
        title: decodeEntities(parent.title.rendered),
        slug: parent.slug,
        children: filteredPages
          .filter((child: { parent: number }) => child.parent === parent.id)
          .map((child: { id: number; title: { rendered: string }; slug: string }) => ({
            id: child.id,
            title: decodeEntities(child.title.rendered),
            slug: child.slug,
          })),
      }))
      // Only include parent pages that have children (navigation categories)
      .filter(item => item.children.length > 0)
  } catch {
    return []
  }
}

function decodeEntities(text: string): string {
  const entities: Record<string, string> = {
    '&amp;': '&', '&lt;': '<', '&gt;': '>', '&quot;': '"',
    '&apos;': "'", '&nbsp;': ' ', '&laquo;': '«', '&raquo;': '»',
    '&ndash;': '–', '&mdash;': '—', '&rsquo;': "'", '&lsquo;': "'",
    '&rdquo;': '"', '&ldquo;': '"', '&hellip;': '…',
    '&eacute;': 'é', '&egrave;': 'è', '&ecirc;': 'ê', '&euml;': 'ë',
    '&agrave;': 'à', '&acirc;': 'â', '&ocirc;': 'ô', '&ugrave;': 'ù',
    '&ucirc;': 'û', '&ccedil;': 'ç', '&iuml;': 'ï', '&icirc;': 'î',
  }
  let result = text
  for (const [entity, char] of Object.entries(entities)) {
    result = result.split(entity).join(char)
  }
  // Decode numeric entities (&#8217; &#8211; etc.)
  result = result.replace(/&#(\d+);/g, (_, code) => {
    const c = parseInt(code, 10)
    // Normalize smart quotes to plain ASCII
    if (c === 8216 || c === 8217) return "'"
    if (c === 8220 || c === 8221) return '"'
    return String.fromCharCode(c)
  })
  // Decode hex entities (&#x2019; etc.)
  result = result.replace(/&#x([0-9a-fA-F]+);/g, (_, code) => {
    const c = parseInt(code, 16)
    if (c === 0x2018 || c === 0x2019) return "'"
    if (c === 0x201C || c === 0x201D) return '"'
    return String.fromCharCode(c)
  })
  return result
}

function stripHtml(html: string): string {
  return decodeEntities(html.replace(/<[^>]+>/g, '').replace(/\s+/g, ' ').trim())
}

export async function generateMetadata(
  { params }: { params: Promise<{ slug: string }> }
): Promise<Metadata> {
  const { slug } = await params
  const page = await getPageBySlug(slug)

  if (!page) {
    return { title: 'Page non trouvée - Twelvy' }
  }

  // Use SEO data from SQL dump if available, otherwise fall back to WordPress content
  const seoEntry = (seoData as Record<string, { meta_title?: string; meta_desc?: string; meta_keywords?: string }>)[slug]

  let title = ''
  if (seoEntry?.meta_title) {
    title = seoEntry.meta_title
  } else {
    title = stripHtml(page.title.rendered)
  }

  let description = ''
  if (seoEntry?.meta_desc) {
    description = seoEntry.meta_desc
  } else {
    if (page.excerpt?.rendered) {
      description = stripHtml(page.excerpt.rendered)
    }
    if (!description || description.length < 20) {
      description = stripHtml(page.content.rendered).substring(0, 160) + '...'
    }
  }
  // Cap description length for SEO
  if (description.length > 160) {
    description = description.substring(0, 157) + '...'
  }

  const keywords = seoEntry?.meta_keywords || undefined

  return {
    title,
    description,
    keywords,
    alternates: {
      canonical: `https://www.twelvy.net/${slug}`,
    },
    openGraph: {
      title: `${title} - Twelvy`,
      description,
      type: 'article',
      url: `https://www.twelvy.net/${slug}`,
      siteName: 'Twelvy',
    },
  }
}

export default async function WordPressPage(
  { params }: { params: Promise<{ slug: string }> }
) {
  const { slug } = await params
  const [page, menu] = await Promise.all([
    getPageBySlug(slug),
    getMenuStructure(),
  ])

  if (!page) {
    notFound()
  }

  // Use SEO title if available
  const seoEntryPage = (seoData as Record<string, { meta_title?: string; faq?: { q: string; a: string }[] }>)[slug]
  const pageTitle = seoEntryPage?.meta_title || stripHtml(page.title.rendered)

  const content = {
    id: page.id,
    title: stripHtml(page.title.rendered),
    content: page.content.rendered,
    slug: page.slug,
  }

  // Build JSON-LD structured data
  const jsonLd: Record<string, unknown> = {
    '@context': 'https://schema.org',
    '@type': 'Article',
    headline: pageTitle,
    url: `https://www.twelvy.net/${slug}`,
    publisher: {
      '@type': 'Organization',
      name: 'Twelvy',
      url: 'https://www.twelvy.net',
    },
  }

  // Build BreadcrumbList
  const breadcrumbJsonLd = {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: [
      {
        '@type': 'ListItem',
        position: 1,
        name: 'Accueil',
        item: 'https://www.twelvy.net',
      },
      {
        '@type': 'ListItem',
        position: 2,
        name: pageTitle,
        item: `https://www.twelvy.net/${slug}`,
      },
    ],
  }

  // Build FAQ JSON-LD if FAQ data available
  const faqItems = seoEntryPage?.faq
  const faqJsonLd = faqItems && faqItems.length > 0 ? {
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    mainEntity: faqItems.map(item => ({
      '@type': 'Question',
      name: item.q.replace(/^[«»"\s]+|[«»"\s]+$/g, ''),
      acceptedAnswer: {
        '@type': 'Answer',
        text: item.a,
      },
    })),
  } : null

  return (
    <>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }}
      />
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(breadcrumbJsonLd) }}
      />
      {faqJsonLd && (
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(faqJsonLd) }}
        />
      )}
      <WordPressPageContent content={content} menu={menu} slug={slug} />
    </>
  )
}
