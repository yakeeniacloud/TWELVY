'use client'

import { useState, useEffect } from 'react'

interface City {
  name: string
  count: number
}

/**
 * Normalize city names to group arrondissements
 * MARSEILLE-1ER, MARSEILLE-2EME -> MARSEILLE
 * MARSEILLE 14, MARSEILLE-14EME-ARRONDISS -> MARSEILLE
 * PARIS-16EME -> PARIS
 * LYON-3EME -> LYON
 */
function normalizeCityName(city: string): string {
  const upperCity = city.toUpperCase().trim()

  // Pattern 1: CITY-NUMBER+SUFFIX (e.g., MARSEILLE-1ER, MARSEILLE-14EME, MARSEILLE-14EME-ARRONDISS)
  // Match: CITY followed by hyphen, then digits, then anything else
  const pattern1 = /^(.+?)-\d+.*$/
  const match1 = upperCity.match(pattern1)
  if (match1) {
    return match1[1].toUpperCase()
  }

  // Pattern 2: CITY SPACE NUMBER (e.g., MARSEILLE 14)
  const pattern2 = /^(.+?)\s+\d+$/
  const match2 = upperCity.match(pattern2)
  if (match2) {
    return match2[1].toUpperCase()
  }

  return upperCity
}

export function useCities() {
  const [cities, setCities] = useState<string[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    async function fetchCities() {
      try {
        console.log('🔄 Fetching cities from /api/cities...')
        const response = await fetch('/api/cities')
        console.log('📡 Response status:', response.status)

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: Failed to fetch cities`)
        }

        const data = (await response.json()) as { cities: string[] }
        console.log('✅ Raw cities loaded:', data.cities.length)

        // Normalize and deduplicate cities
        const normalizedSet = new Set<string>()
        data.cities.forEach(city => {
          const normalized = normalizeCityName(city)
          normalizedSet.add(normalized)
        })

        const uniqueCities = Array.from(normalizedSet).sort()
        console.log('✅ Unique cities after grouping arrondissements:', uniqueCities.length)
        console.log('📍 Sample cities:', uniqueCities.slice(0, 10))

        setCities(uniqueCities)
      } catch (err) {
        const errorMsg = err instanceof Error ? err.message : 'Unknown error'
        console.error('❌ Error fetching cities:', errorMsg)
        setError(errorMsg)
        setCities([])
      } finally {
        setLoading(false)
      }
    }

    fetchCities()
  }, [])

  return { cities, loading, error }
}
