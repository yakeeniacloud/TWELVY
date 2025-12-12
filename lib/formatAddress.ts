/**
 * Remove street number from the beginning of an address
 * Examples:
 * "106 avenue de Saint-Mené" → "avenue de Saint-Mené"
 * "42 rue de la République" → "rue de la République"
 * "avenue de Saint-Mené" → "avenue de Saint-Mené" (no change if no number)
 */
export function removeStreetNumber(address: string): string {
  if (!address) return address

  // Remove leading numbers and spaces/commas
  // Matches: "106 ", "106, ", "42-", etc.
  return address.replace(/^\d+[\s,\-]+/, '').trim()
}
