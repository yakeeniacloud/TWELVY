import { NextResponse } from 'next/server'

export async function POST(request: Request) {
  try {
    const body = await request.json()
    const { title, content, parent, status = 'publish' } = body

    // WordPress credentials
    const wpUsername = 'Yakeen_admin'
    const wpAppPassword = 'UaSM fH38 ONVn JWda 0YSp JBcx'
    const credentials = Buffer.from(`${wpUsername}:${wpAppPassword}`).toString('base64')

    // Create page via WordPress REST API
    const response = await fetch('https://headless.twelvy.net/wp-json/wp/v2/pages', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Basic ${credentials}`,
      },
      body: JSON.stringify({
        title,
        content,
        status,
        parent: parent || 0,
      }),
    })

    if (!response.ok) {
      const errorText = await response.text()
      console.error('WordPress API error:', errorText)
      return NextResponse.json(
        { error: 'Failed to create page', details: errorText },
        { status: response.status }
      )
    }

    const data = await response.json()
    return NextResponse.json({
      success: true,
      page: {
        id: data.id,
        title: data.title.rendered,
        slug: data.slug,
        link: data.link,
      },
    })
  } catch (error) {
    const errorMsg = error instanceof Error ? error.message : String(error)
    console.error('Error creating WordPress page:', errorMsg)
    return NextResponse.json(
      { error: 'Failed to create page', message: errorMsg },
      { status: 500 }
    )
  }
}
