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

export async function GET() {
  try {
    // Fetch all published pages from WordPress
    const response = await fetch(
      'https://headless.twelvy.net/wp-json/wp/v2/pages?per_page=100&status=publish&orderby=menu_order&order=asc',
      {
        headers: {
          'Accept': 'application/json',
        },
        next: { revalidate: 30 }, // Cache for 30 seconds
      }
    )

    if (!response.ok) {
      return NextResponse.json(
        { error: 'Failed to fetch pages from WordPress' },
        { status: response.status }
      )
    }

    const pages: WordPressPage[] = await response.json()

    console.log('📄 Raw WordPress pages:', pages.map(p => ({ id: p.id, title: p.title.rendered, parent: p.parent })))

    // Filter out homepage and stages-CITY pages (e.g., stages-marseille, stages-paris)
    // But KEEP pages like "stages-obligatoires" (child pages about stages)
    const filteredPages = pages.filter(page => {
      if (page.slug === 'homepage') return false

      // Only filter out stages-{city} pattern (stages-marseille, stages-paris-75001, etc.)
      // These are city-specific stage listing pages, not menu items
      // Keep everything else, including "stages-obligatoires" (child pages)
      const isCityStagesPage = page.slug.match(/^stages-[a-z]+-\d+/)  // stages-paris-75001
        || (page.slug.startsWith('stages-') && page.slug.split('-').length === 2 && page.slug.split('-')[1].match(/^[a-z]+$/))  // stages-marseille

      return !isCityStagesPage
    })

    console.log('🔍 Filtered pages:', filteredPages.map(p => ({ id: p.id, title: p.title.rendered, parent: p.parent })))

    // Build hierarchical menu structure
    const parentPages = filteredPages.filter(p => p.parent === 0)
    const childPages = filteredPages.filter(p => p.parent !== 0)

    const menuStructure = parentPages.map(parent => {
      const children = childPages
        .filter(child => child.parent === parent.id)
        .map(child => ({
          id: child.id,
          title: child.title.rendered,
          slug: child.slug,
        }))
        .sort((a, b) => a.id - b.id)

      console.log(`👨‍👧 Parent "${parent.title.rendered}" (ID: ${parent.id}) has ${children.length} children:`, children)

      return {
        id: parent.id,
        title: parent.title.rendered,
        slug: parent.slug,
        children,
      }
    })

    console.log('✅ Final menu structure:', JSON.stringify(menuStructure, null, 2))

    return NextResponse.json({
      menu: menuStructure,
      total: menuStructure.length,
    })
  } catch (error) {
    const errorMsg = error instanceof Error ? error.message : String(error)
    console.error('Error fetching WordPress menu:', errorMsg)
    return NextResponse.json(
      { error: 'Failed to fetch menu', details: errorMsg },
      { status: 500 }
    )
  }
}
