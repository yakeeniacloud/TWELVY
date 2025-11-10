import { NextResponse } from 'next/server'

export async function GET(
  request: Request,
  { params }: { params: Promise<{ slug: string }> }
) {
  try {
    const { slug } = await params

    const response = await fetch(
      `https://headless.twelvy.net/wp-json/wp/v2/pages?slug=${slug}`,
      {
        headers: {
          'Accept': 'application/json',
        },
      }
    )

    if (!response.ok) {
      return NextResponse.json(
        { error: 'Page not found', slug },
        { status: 404 }
      )
    }

    const pages = await response.json()

    if (!Array.isArray(pages) || pages.length === 0) {
      return NextResponse.json(
        { error: 'No content found', slug },
        { status: 404 }
      )
    }

    // Return first matching page
    const page = pages[0]
    return NextResponse.json({
      id: page.id,
      title: page.title.rendered,
      content: page.content.rendered,
      slug: page.slug,
    })
  } catch (error) {
    const errorMsg = error instanceof Error ? error.message : String(error)
    console.error('Error fetching WordPress content:', errorMsg)
    return NextResponse.json(
      { error: 'Failed to fetch content', details: errorMsg },
      { status: 500 }
    )
  }
}
