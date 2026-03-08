import { NextResponse } from 'next/server'

interface WordPressPage {
  id: number
  title: {
    rendered: string
  }
  slug: string
  parent: number
  menu_order: number
  link: string
}

function decodeEntities(text: string): string {
  let result = text
  // Decode numeric entities (&#8217; &#8211; etc.)
  result = result.replace(/&#(\d+);/g, (_, code) => {
    const c = parseInt(code, 10)
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
  // Decode named entities
  const entities: Record<string, string> = {
    '&amp;': '&', '&lt;': '<', '&gt;': '>', '&quot;': '"',
    '&apos;': "'", '&nbsp;': ' ', '&ndash;': '–', '&mdash;': '—',
    '&rsquo;': "'", '&lsquo;': "'", '&hellip;': '…',
    '&eacute;': 'é', '&egrave;': 'è', '&ecirc;': 'ê', '&agrave;': 'à',
    '&acirc;': 'â', '&ocirc;': 'ô', '&ugrave;': 'ù', '&ccedil;': 'ç',
  }
  for (const [entity, char] of Object.entries(entities)) {
    result = result.split(entity).join(char)
  }
  return result
}

const WP_HEADERS = {
  'Accept': 'application/json',
  'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
}

async function fetchAllPages(): Promise<WordPressPage[]> {
  const all: WordPressPage[] = []
  let page = 1
  while (true) {
    const response = await fetch(
      `https://headless.twelvy.net/wp-json/wp/v2/pages?per_page=100&page=${page}&status=publish&orderby=menu_order&order=asc`,
      { headers: WP_HEADERS, next: { revalidate: 30 } }
    )
    if (!response.ok) break
    const batch: WordPressPage[] = await response.json()
    if (!batch || batch.length === 0) break
    all.push(...batch)
    if (batch.length < 100) break
    page++
  }
  return all
}

export async function GET() {
  try {
    const pages = await fetchAllPages()

    // Filter out homepage and stages-CITY pages (e.g., stages-marseille, stages-paris)
    // But KEEP child pages with parent set (even if they start with "stages-")
    const filteredPages = pages.filter(page => {
      if (page.slug === 'homepage') return false
      if (page.parent !== 0) return true
      const isCityStagesPage = page.slug.match(/^stages-[a-z]+-\d+/)
        || (page.slug.startsWith('stages-') && page.slug.split('-').length === 2)
      return !isCityStagesPage
    })

    // Build hierarchical menu structure
    const parentPages = filteredPages.filter(p => p.parent === 0)
    const childPages = filteredPages.filter(p => p.parent !== 0)

    const menuStructure = parentPages
      .map(parent => {
        const children = childPages
          .filter(child => child.parent === parent.id)
          .map(child => ({
            id: child.id,
            title: decodeEntities(child.title.rendered),
            slug: child.slug,
          }))
          .sort((a, b) => a.id - b.id)

        return {
          id: parent.id,
          title: decodeEntities(parent.title.rendered),
          slug: parent.slug,
          children,
        }
      })
      // Only show parent pages that have children (navigation categories)
      // Standalone articles (parent=0, no children) are accessible via /[slug] but not in the menu
      .filter(item => item.children.length > 0)

    return NextResponse.json({
      menu: menuStructure,
      total: menuStructure.length,
    })
  } catch (error) {
    const errorMsg = error instanceof Error ? error.message : String(error)
    return NextResponse.json(
      { error: 'Failed to fetch menu', details: errorMsg },
      { status: 500 }
    )
  }
}
