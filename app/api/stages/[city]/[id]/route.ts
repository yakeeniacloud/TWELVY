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

    // Call PHP API
    const response = await fetch(`https://www.twelvy.net/php/stage-detail.php?id=${encodeURIComponent(id)}`, {
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

    const data = await response.json()
    console.log('✅ Stage loaded from PHP API:', data.id)

    // Map PHP response to expected format
    // PHP returns: date_start/date_end and nested site object
    // Frontend expects: date1/date2 and flattened structure
    const stage = {
      id: data.id,
      id_site: data.id_site,
      date1: data.date_start,
      date2: data.date_end,
      prix: data.prix,
      nb_places_allouees: data.nb_places,
      nb_inscrits: data.nb_inscrits,
      site_nom: data.site.nom,
      ville: data.site.ville,
      adresse: data.site.adresse,
      code_postal: data.site.code_postal,
      latitude: data.site.latitude,
      longitude: data.site.longitude
    }

    return NextResponse.json({ stage }, { status: 200 })
  } catch (error) {
    const errorMsg = error instanceof Error ? error.message : String(error)
    console.error('❌ Error fetching stage:', errorMsg)
    return NextResponse.json(
      { error: 'Failed to fetch stage', details: errorMsg },
      { status: 500 }
    )
  }
}
