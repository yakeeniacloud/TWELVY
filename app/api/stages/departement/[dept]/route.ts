import { NextRequest, NextResponse } from 'next/server'
import { getDeptBySlug } from '@/lib/departements'

export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ dept: string }> }
) {
  try {
    const { dept: deptSlug } = await params

    const dept = getDeptBySlug(deptSlug)
    if (!dept) {
      return NextResponse.json({ error: 'Département not found', slug: deptSlug }, { status: 404 })
    }

    const apiBaseUrl = process.env.PHP_API_URL || 'https://api.twelvy.net'
    const response = await fetch(
      `${apiBaseUrl}/stages-geo.php?dept_codes=${encodeURIComponent(dept.code)}`,
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
