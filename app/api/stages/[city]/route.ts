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
    const response = await fetch(`https://api.twelvy.net/stages.php?city=${encodeURIComponent(city)}`, {
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
    console.log('✅ Raw stages from PHP API:', data.stages.length, 'stages')

    // ⚡ OPTIMIZATION: Filter and reduce payload size
    const today = new Date()
    const todayStr = today.toISOString().split('T')[0]
    const sixMonthsFromNow = new Date(today.getTime() + 180 * 24 * 60 * 60 * 1000)
    const sixMonthsStr = sixMonthsFromNow.toISOString().split('T')[0]

    // Filter: only future stages within next 6 months
    const filteredStages = data.stages.filter((stage: any) => {
      if (!stage.date_start || stage.date_start === '0000-00-00') return false
      return stage.date_start >= todayStr && stage.date_start <= sixMonthsStr
    })

    console.log('✅ Filtered stages (active only):', filteredStages.length, 'stages')
    console.log(`   📊 Payload reduced: ${data.stages.length} → ${filteredStages.length} (${Math.round((1 - filteredStages.length / data.stages.length) * 100)}% smaller)`)

    // Return optimized payload
    return NextResponse.json({
      stages: filteredStages,
      city: data.city
    }, {
      status: 200,
      headers: {
        'Cache-Control': 'public, s-maxage=300, stale-while-revalidate=600'
      }
    })
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
