import { NextResponse } from 'next/server'
import { querySite } from '@/lib/mysql'

export async function GET() {
  try {
    // Fetch unique cities from site table, ordered alphabetically
    const results = (await querySite(
      'SELECT DISTINCT ville FROM site WHERE ville IS NOT NULL AND ville != "" ORDER BY ville ASC'
    )) as any[]

    const cities = results.map((row) => row.ville).filter(Boolean)

    return NextResponse.json({ cities }, { status: 200 })
  } catch (error) {
    console.error('Error fetching cities:', error)
    return NextResponse.json(
      { error: 'Failed to fetch cities' },
      { status: 500 }
    )
  }
}
