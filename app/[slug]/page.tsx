import { Metadata } from 'next'
import { notFound } from 'next/navigation'
import WordPressPageContent from './WordPressPageContent'

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
    return parentPages.map((parent: { id: number; title: { rendered: string }; slug: string }) => ({
      id: parent.id,
      title: parent.title.rendered,
      slug: parent.slug,
      children: filteredPages
        .filter((child: { parent: number }) => child.parent === parent.id)
        .map((child: { id: number; title: { rendered: string }; slug: string }) => ({
          id: child.id,
          title: child.title.rendered,
          slug: child.slug,
        })),
    }))
  } catch {
    return []
  }
}

function stripHtml(html: string): string {
  return html.replace(/<[^>]+>/g, '').replace(/\s+/g, ' ').trim()
}

export async function generateMetadata(
  { params }: { params: Promise<{ slug: string }> }
): Promise<Metadata> {
  const { slug } = await params
  const page = await getPageBySlug(slug)

  if (!page) {
    return { title: 'Page non trouvée - Twelvy' }
  }

  const title = stripHtml(page.title.rendered)

  // Use excerpt if meaningful, otherwise generate from content
  let description = ''
  if (page.excerpt?.rendered) {
    description = stripHtml(page.excerpt.rendered)
  }
  if (!description || description.length < 20) {
    description = stripHtml(page.content.rendered).substring(0, 160) + '...'
  }
  // Cap description length for SEO
  if (description.length > 160) {
    description = description.substring(0, 157) + '...'
  }

  return {
    title: `${title} - Twelvy`,
    description,
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

  const content = {
    id: page.id,
    title: stripHtml(page.title.rendered),
    content: page.content.rendered,
    slug: page.slug,
  }

  return <WordPressPageContent content={content} menu={menu} slug={slug} />
}
