import { NextResponse } from 'next/server'

export async function POST(request: Request) {
  try {
    const body = await request.json()
    const { id } = body

    if (!id) {
      return NextResponse.json(
        { error: 'Page ID is required' },
        { status: 400 }
      )
    }

    // WordPress credentials
    const wpUsername = 'Yakeen_admin'
    const wpAppPassword = 'UaSM fH38 ONVn JWda 0YSp JBcx'
    const credentials = Buffer.from(`${wpUsername}:${wpAppPassword}`).toString('base64')

    // Update page status to 'trash' via WordPress REST API (soft delete)
    const response = await fetch(`https://headless.twelvy.net/wp-json/wp/v2/pages/${id}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Basic ${credentials}`,
      },
      body: JSON.stringify({
        status: 'trash',
      }),
    })

    if (!response.ok) {
      const errorText = await response.text()
      console.error('WordPress API error:', errorText)
      return NextResponse.json(
        { error: 'Failed to trash page', details: errorText },
        { status: response.status }
      )
    }

    const data = await response.json()
    return NextResponse.json({
      success: true,
      trashed: {
        id: data.id,
        title: data.title?.rendered || 'Unknown',
        status: data.status,
      },
    })
  } catch (error) {
    const errorMsg = error instanceof Error ? error.message : String(error)
    console.error('Error trashing WordPress page:', errorMsg)
    return NextResponse.json(
      { error: 'Failed to trash page', message: errorMsg },
      { status: 500 }
    )
  }
}
