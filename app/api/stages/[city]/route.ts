import { NextRequest, NextResponse } from 'next/server'
import { querySite } from '@/lib/mysql'

interface Stage {
  id: number
  id_site: number
  date1: string
  date2: string
  prix: number
  nb_places_allouees: number
  nb_inscrits: number
  visible: number
  site?: Site
}

interface Site {
  id: number
  nom: string
  ville: string
  adresse: string
  code_postal: string
  latitude?: number
  longitude?: number
}

export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ city: string }> }
) {
  try {
    const resolvedParams = await params
    const city = resolvedParams.city.toUpperCase()

    console.log('📍 /api/stages/[city] called with city:', city)
    console.log('🔧 Environment check:')
    console.log('  MYSQL_HOST:', process.env.MYSQL_HOST || 'NOT SET')
    console.log('  MYSQL_USER:', process.env.MYSQL_USER || 'NOT SET')
    console.log('  MYSQL_DATABASE:', process.env.MYSQL_DATABASE || 'NOT SET')

    // Fetch stages for this city with site details
    console.log('🔄 Executing query...')
    const stages = (await querySite(
      `SELECT s.*, st.nom as site_nom, st.ville, st.adresse, st.code_postal, st.latitude, st.longitude
       FROM stage s
       JOIN site st ON s.id_site = st.id
       WHERE UPPER(st.ville) = ? AND s.visible = 1 AND s.annule = 0
       ORDER BY s.date1 ASC`,
      [city]
    )) as any[]

    // Transform results to match expected structure
    const formattedStages = stages.map((stage) => ({
      id: stage.id,
      id_site: stage.id_site,
      date_start: stage.date1,
      date_end: stage.date2,
      prix: stage.prix,
      nb_places: stage.nb_places_allouees,
      nb_inscrits: stage.nb_inscrits,
      visible: stage.visible,
      site: {
        id: stage.id_site,
        nom: stage.site_nom,
        ville: stage.ville,
        adresse: stage.adresse,
        code_postal: stage.code_postal,
        latitude: stage.latitude,
        longitude: stage.longitude,
      },
    }))

    console.log('✅ Query successful, formatted:', formattedStages.length, 'stages')
    return NextResponse.json(
      { stages: formattedStages, city },
      { status: 200 }
    )
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
