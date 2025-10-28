import { NextResponse } from 'next/server'

export async function POST(request: Request) {
  try {
    const body = await request.json()

    console.log('📨 Proxying booking request to OVH...')
    console.log('Request body:', body)

    const ovhApiUrl = process.env.OVH_API_URL || 'https://api.twelvy.net'
    const apiKey = process.env.OVH_API_KEY || ''

    console.log('🌐 Calling OVH API at:', ovhApiUrl)

    // Call the OVH PHP API
    const response = await fetch(`${ovhApiUrl}/inscription.php`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Api-Key': apiKey,
      },
      body: JSON.stringify(body),
    })

    const data = await response.json()

    console.log('✅ OVH Response:', response.status, data)

    return NextResponse.json(data, { status: response.status })
  } catch (error) {
    console.error('❌ Error:', error)
    return NextResponse.json(
      {
        error: 'Failed to process booking',
        message: error instanceof Error ? error.message : 'Unknown error'
      },
      { status: 500 }
    )
  }
}
