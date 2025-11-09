import { NextRequest, NextResponse } from 'next/server'

export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ city: string }> }
) {
  try {
    const resolvedParams = await params
    const city = resolvedParams.city.toUpperCase()

    console.log('📍 /api/stages/[city] called with city:', city)
    console.log('🔄 Proxying to PHP API...')

    // Call PHP API on api.twelvy.net
    const response = await fetch(`https://api.twelvy.net/www/api/stages.php?city=${encodeURIComponent(city)}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    })

    console.log('📡 PHP API response status:', response.status)

    if (!response.ok) {
      throw new Error(`PHP API returned ${response.status}`)
    }

    const data = (await response.json()) as { stages: any[]; city: string }
    console.log('✅ Stages loaded from PHP API:', data.stages.length, 'stages')

    return NextResponse.json(data, { status: 200 })
  } catch (error) {
    const errorMsg = error instanceof Error ? error.message : String(error)
    console.error('❌ Error fetching stages:')
    console.error('   Message:', errorMsg)
    console.error('   Stack:', error instanceof Error ? error.stack : 'N/A')

    return NextResponse.json(
      { error: 'Failed to fetch stages', details: errorMsg },
      { status: 500 }
    )
  }
}
