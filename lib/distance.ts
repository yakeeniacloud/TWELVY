/**
 * Calculate distance between two coordinates using Haversine formula
 * @param lat1 Latitude of point 1
 * @param lon1 Longitude of point 1
 * @param lat2 Latitude of point 2
 * @param lon2 Longitude of point 2
 * @returns Distance in kilometers
 */
export function calculateDistance(
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

/**
 * Find all unique city coordinates and get cities within a radius
 */
export function getCitiesWithinRadius(
  stages: any[],
  searchedCity: string,
  radiusKm: number = 50
): { city: string; distance: number }[] {
  // Get the center point (coordinates) of the searched city
  const searchedCityStages = stages.filter(
    s => s.site.ville.toUpperCase() === searchedCity.toUpperCase()
  )

  if (searchedCityStages.length === 0) {
    return []
  }

  // Use the first stage's coordinates as the center point
  const centerLat = searchedCityStages[0].site.latitude
  const centerLon = searchedCityStages[0].site.longitude

  if (!centerLat || !centerLon) {
    return []
  }

  // Get all unique cities with their coordinates
  const uniqueCities = Array.from(
    new Map(
      stages.map(s => [
        s.site.ville,
        {
          city: s.site.ville,
          latitude: s.site.latitude,
          longitude: s.site.longitude,
        },
      ])
    ).values()
  )

  // Calculate distances and filter
  const citiesWithDistances = uniqueCities
    .map(cityData => ({
      city: cityData.city,
      distance: calculateDistance(
        centerLat,
        centerLon,
        cityData.latitude,
        cityData.longitude
      ),
    }))
    .filter(item => item.distance <= radiusKm)
    .sort((a, b) => a.distance - b.distance)

  return citiesWithDistances
}
