import { NextResponse } from 'next/server'
import { querySite } from '@/lib/mysql'

export async function GET() {
  try {
    console.log('📍 /api/cities called')
    console.log('🔧 Environment check:')
    console.log('  MYSQL_HOST:', process.env.MYSQL_HOST || 'NOT SET')
    console.log('  MYSQL_USER:', process.env.MYSQL_USER || 'NOT SET')
    console.log('  MYSQL_DATABASE:', process.env.MYSQL_DATABASE || 'NOT SET')

    // Fetch unique cities from site table, ordered alphabetically
    console.log('🔄 Executing query...')
    const results = (await querySite(
      'SELECT DISTINCT ville FROM site WHERE ville IS NOT NULL AND ville != "" ORDER BY ville ASC'
    )) as any[]

    console.log('✅ Query successful, results:', results)
    const cities = results.map((row) => row.ville).filter(Boolean)
    console.log('🎯 Formatted cities:', cities)

    return NextResponse.json({ cities }, { status: 200 })
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
