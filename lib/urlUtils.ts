/**
 * Parse city slug from URL format: MARSEILLE-13015
 * Returns: { city: "MARSEILLE", postal: "13015" }
 */
export function parseRecuperationPointsSlug(slug: string): { city: string; postal: string } | null {
  if (!slug) return null

  // Format: CITY-POSTAL (postal code is last 5 digits after last dash)
  const parts = slug.split('-')
  if (parts.length < 2) return null

  const postal = parts[parts.length - 1]
  const city = parts.slice(0, -1).join('-').toUpperCase()

  if (!postal || !city) return null

  return { city, postal }
}

/**
 * Build new URL format: recuperation-points-MARSEILLE-13015
 */
export function buildRecuperationPointsUrl(city: string, postal: string): string {
  return `/recuperation-points-${city.toUpperCase()}-${postal}`
}

/**
 * Extract city name for WordPress slug: stages-marseille
 */
export function buildWordPressSlug(city: string): string {
  return `stages-${city.toLowerCase()}`
}
