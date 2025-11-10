// City coordinates for proximity filtering
// Coordinates are for city centers (WGS84)

export interface CityCoords {
  latitude: number
  longitude: number
}

export const CITY_COORDINATES: Record<string, CityCoords> = {
  // Marseille area
  'MARSEILLE': { latitude: 43.2965, longitude: 5.3698 },
  'AIX-EN-PROVENCE': { latitude: 43.5297, longitude: 5.4474 },
  'AUBAGNE': { latitude: 43.2928, longitude: 5.5706 },
  'VITROLLES': { latitude: 43.4553, longitude: 5.2478 },
  'LA CIOTAT': { latitude: 43.1747, longitude: 5.6064 },

  // Lyon area
  'LYON': { latitude: 45.7640, longitude: 4.8357 },
  'VILLEURBANNE': { latitude: 45.7667, longitude: 4.8797 },

  // Other major cities
  'PARIS': { latitude: 48.8566, longitude: 2.3522 },
  'TOULOUSE': { latitude: 43.6047, longitude: 1.4442 },
  'NICE': { latitude: 43.7102, longitude: 7.2620 },
  'NANTES': { latitude: 47.2184, longitude: -1.5536 },
  'BORDEAUX': { latitude: 44.8378, longitude: -0.5792 },
  'MONTPELLIER': { latitude: 43.6108, longitude: 3.8767 },
  'STRASBOURG': { latitude: 48.5734, longitude: 7.7521 },
  'LILLE': { latitude: 50.6292, longitude: 3.0573 },
}

/**
 * Get coordinates for a city
 * @param cityName City name (case insensitive)
 * @returns City coordinates or null if not found
 */
export function getCityCoordinates(cityName: string): CityCoords | null {
  const normalizedName = cityName.toUpperCase().trim()
  return CITY_COORDINATES[normalizedName] || null
}

/**
 * Check if a city has coordinates available
 * @param cityName City name (case insensitive)
 * @returns True if coordinates are available
 */
export function hasCityCoordinates(cityName: string): boolean {
  return getCityCoordinates(cityName) !== null
}

/**
 * Get all cities within a radius of a search city
 * @param searchedCity The city to search from
 * @param radiusKm Radius in kilometers (default 50)
 * @returns Array of cities with their distances
 */
export function getCitiesInRadius(
  searchedCity: string,
  radiusKm: number = 50
): { city: string; distance: number }[] {
  const searchCoords = getCityCoordinates(searchedCity)
  if (!searchCoords) {
    return []
  }

  const results: { city: string; distance: number }[] = []

  for (const [cityName, coords] of Object.entries(CITY_COORDINATES)) {
    // Calculate distance
    const distance = calculateDistance(
      searchCoords.latitude,
      searchCoords.longitude,
      coords.latitude,
      coords.longitude
    )

    // Include if within radius and not the searched city itself
    if (distance <= radiusKm && cityName !== searchedCity.toUpperCase()) {
      results.push({ city: cityName, distance: Math.round(distance) })
    }
  }

  return results.sort((a, b) => a.distance - b.distance)
}

/**
 * Calculate distance between two coordinates using Haversine formula
 */
function calculateDistance(
  lat1: number,
  lon1: number,
  lat2: number,
  lon2: number
): number {
  const R = 6371 // Earth's radius in kilometers
  const dLat = (lat2 - lat1) * (Math.PI / 180)
  const dLon = (lon2 - lon1) * (Math.PI / 180)

  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(lat1 * (Math.PI / 180)) *
      Math.cos(lat2 * (Math.PI / 180)) *
      Math.sin(dLon / 2) *
      Math.sin(dLon / 2)

  const c = 2 * Math.asin(Math.sqrt(a))
  return R * c
}
