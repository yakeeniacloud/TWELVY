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

    // Filter out homepage and stages-* pages (same as OPTIMUS)
    const filteredPages = pages.filter(page =>
      page.slug !== 'homepage' &&
      !page.slug.startsWith('stages-')
    )

    // Build hierarchical menu structure
    const parentPages = filteredPages.filter(p => p.parent === 0)
    const childPages = filteredPages.filter(p => p.parent !== 0)

    const menuStructure = parentPages.map(parent => ({
      id: parent.id,
      title: parent.title.rendered,
      slug: parent.slug,
      children: childPages
        .filter(child => child.parent === parent.id)
        .map(child => ({
          id: child.id,
          title: child.title.rendered,
          slug: child.slug,
        }))
        .sort((a, b) => a.id - b.id), // Sort children by creation order
    }))

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
