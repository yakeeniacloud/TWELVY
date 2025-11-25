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

    // Delete page via WordPress REST API
    const response = await fetch(`https://headless.twelvy.net/wp-json/wp/v2/pages/${id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Basic ${credentials}`,
      },
    })

    if (!response.ok) {
      const errorText = await response.text()
      console.error('WordPress API error:', errorText)
      return NextResponse.json(
        { error: 'Failed to delete page', details: errorText },
        { status: response.status }
      )
    }

    const data = await response.json()
    return NextResponse.json({
      success: true,
      deleted: {
        id: data.id,
        title: data.title?.rendered || 'Unknown',
      },
    })
  } catch (error) {
    const errorMsg = error instanceof Error ? error.message : String(error)
    console.error('Error deleting WordPress page:', errorMsg)
    return NextResponse.json(
      { error: 'Failed to delete page', message: errorMsg },
      { status: 500 }
    )
  }
}
