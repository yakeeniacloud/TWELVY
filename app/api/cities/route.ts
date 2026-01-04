import { NextResponse } from 'next/server'

// Type for the new API response format with postal codes
interface CityWithPostal {
  name: string
  postal: string
}

export async function GET() {
  try {
    // Call PHP API on api.twelvy.net
    const response = await fetch('https://api.twelvy.net/cities.php', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    })

    if (!response.ok) {
      throw new Error(`PHP API returned ${response.status}`)
    }

    const data = await response.json()

    // Handle both old format (string[]) and new format ({name, postal}[])
    // This ensures backwards compatibility during transition
    if (data.cities && data.cities.length > 0) {
      // Check if it's the new format with postal codes
      if (typeof data.cities[0] === 'object' && data.cities[0].name) {
        // New format - pass through as-is
        return NextResponse.json(data, { status: 200 })
      } else {
        // Old format - convert to new format without postal codes
        const citiesWithPostal = (data.cities as string[]).map(city => ({
          name: city,
          postal: ''
        }))
        return NextResponse.json({ cities: citiesWithPostal }, { status: 200 })
      }
    }

    return NextResponse.json({ cities: [] }, { status: 200 })
  } catch (error) {
    const errorMsg = error instanceof Error ? error.message : String(error)
    console.error('Error fetching cities:', errorMsg)

    return NextResponse.json(
      { error: 'Failed to fetch cities', details: errorMsg },
      { status: 500 }
    )
  }
}
