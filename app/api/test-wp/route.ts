import { NextResponse } from 'next/server'

export async function GET() {
  try {
    const response = await fetch(
      'https://headless.twelvy.net/wp-json/wp/v2/pages?per_page=100&status=publish',
      { headers: { 'Accept': 'application/json' }, cache: 'no-store' }
    )

    if (!response.ok) {
      return NextResponse.json({ error: 'WordPress API failed', status: response.status })
    }

    const pages = await response.json()

    return NextResponse.json({
      total: pages.length,
      pages: pages.map((p: any) => ({
        id: p.id,
        title: p.title.rendered,
        slug: p.slug,
        parent: p.parent,
        status: p.status,
      }))
    })
  } catch (error) {
    return NextResponse.json({ error: String(error) }, { status: 500 })
  }
}
