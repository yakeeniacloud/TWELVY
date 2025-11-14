/**
 * Parse city slug from URL format: MARSEILLE-13015 or AIX-EN-PROVENCE-13100
 * Returns: { city: "MARSEILLE", postal: "13015" }
 * Splits on the LAST hyphen since postal is always 5 digits
 */
export function parseRecuperationPointsSlug(slug: string): { city: string; postal: string } | null {
  if (!slug) return null

  // Format: CITY-POSTAL where postal is 5 digits after last dash
  // Handle city names with hyphens (e.g., AIX-EN-PROVENCE-13100)
  const lastHyphenIndex = slug.lastIndexOf('-')
  if (lastHyphenIndex === -1) return null

  const postal = slug.substring(lastHyphenIndex + 1)
  const city = slug.substring(0, lastHyphenIndex).toUpperCase()

  if (!postal || !city || postal.length !== 5) return null

  return { city, postal }
}

/**
 * Build new URL format: /recuperation-points-MARSEILLE-13015
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
