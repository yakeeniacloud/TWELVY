import { NextResponse } from 'next/server'

export async function GET() {
  try {
    console.log('📍 /api/cities called - proxying to PHP API')

    // Call PHP API on api.twelvy.net
    const response = await fetch('https://api.twelvy.net/cities.php', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    })

    console.log('📡 PHP API response status:', response.status)

    if (!response.ok) {
      throw new Error(`PHP API returned ${response.status}`)
    }

    const data = (await response.json()) as { cities: string[] }
    console.log('✅ Cities loaded from PHP API:', data.cities)

    return NextResponse.json(data, { status: 200 })
  } catch (error) {
    const errorMsg = error instanceof Error ? error.message : String(error)
    console.error('❌ Error fetching cities:')
    console.error('   Message:', errorMsg)
    console.error('   Stack:', error instanceof Error ? error.stack : 'N/A')

    return NextResponse.json(
      { error: 'Failed to fetch cities', details: errorMsg },
      { status: 500 }
    )
  }
}
