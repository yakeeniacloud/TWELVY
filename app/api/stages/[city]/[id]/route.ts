import { NextRequest, NextResponse } from 'next/server'

export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ city: string; id: string }> }
) {
  try {
    const resolvedParams = await params
    const { id } = resolvedParams

    console.log('📍 /api/stages/[city]/[id] called with id:', id)
    console.log('🔄 Proxying to PHP API...')

    // Call PHP API on api.twelvy.net
    const response = await fetch(`https://api.twelvy.net/stage-detail.php?id=${encodeURIComponent(id)}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    })

    console.log('📡 PHP API response status:', response.status)

    if (!response.ok) {
      if (response.status === 404) {
        return NextResponse.json(
          { error: 'Stage not found' },
          { status: 404 }
        )
      }
      throw new Error(`PHP API returned ${response.status}`)
    }

    const stage = await response.json()
    console.log('✅ Stage loaded from PHP API:', stage.id)

    return NextResponse.json(stage, { status: 200 })
  } catch (error) {
    const errorMsg = error instanceof Error ? error.message : String(error)
    console.error('❌ Error fetching stage:', errorMsg)
    return NextResponse.json(
      { error: 'Failed to fetch stage', details: errorMsg },
      { status: 500 }
    )
  }
}
