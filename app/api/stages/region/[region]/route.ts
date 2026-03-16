import { NextRequest, NextResponse } from 'next/server'
import { getRegionBySlug } from '@/lib/regions'

export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ region: string }> }
) {
  try {
    const { region: regionSlug } = await params

    const region = getRegionBySlug(regionSlug)
    if (!region) {
      return NextResponse.json({ error: 'Région not found', slug: regionSlug }, { status: 404 })
    }

    const deptCodesParam = region.depts.join(',')
    const apiBaseUrl = process.env.PHP_API_URL || 'https://api.twelvy.net'
    const response = await fetch(
      `${apiBaseUrl}/stages-geo.php?dept_codes=${encodeURIComponent(deptCodesParam)}`,
      {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' },
      }
    )

    if (!response.ok) {
      throw new Error(`PHP API returned ${response.status}`)
    }

    const data = await response.json()

    return NextResponse.json(data, {
      status: 200,
      headers: {
        'Cache-Control': 'public, s-maxage=300, stale-while-revalidate=600',
      },
    })
  } catch (error) {
    const errorMsg = error instanceof Error ? error.message : String(error)
    return NextResponse.json(
      { error: 'Failed to fetch stages', details: errorMsg },
      { status: 500 }
    )
  }
}
