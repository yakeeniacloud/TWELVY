import { NextRequest, NextResponse } from 'next/server'
import { querySite } from '@/lib/mysql'

export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ city: string; id: string }> }
) {
  try {
    const resolvedParams = await params
    const { id } = resolvedParams

    const stage = (await querySite(
      `SELECT s.*, st.nom as site_nom, st.ville, st.adresse, st.code_postal, st.latitude, st.longitude
       FROM stage s
       JOIN site st ON s.id_site = st.id
       WHERE s.id = ?`,
      [id]
    )) as any[]

    if (stage.length === 0) {
      return NextResponse.json(
        { error: 'Stage not found' },
        { status: 404 }
      )
    }

    return NextResponse.json({ stage: stage[0] }, { status: 200 })
  } catch (error) {
    console.error('Error fetching stage:', error)
    return NextResponse.json(
      { error: 'Failed to fetch stage' },
      { status: 500 }
    )
  }
}
