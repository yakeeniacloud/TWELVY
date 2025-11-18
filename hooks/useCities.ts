'use client'

import { useState, useEffect } from 'react'

interface City {
  name: string
  count: number
}

/**
 * Normalize city names to group arrondissements
 * MARSEILLE-1ER, MARSEILLE-2EME -> MARSEILLE
 * PARIS-16EME -> PARIS
 * LYON-3EME -> LYON
 */
function normalizeCityName(city: string): string {
  // Pattern: CITY-NUMBER (with optional ER/EME/E suffix)
  const arrondissementPattern = /^(.+)-\d+(ER|EME|E)?$/i
  const match = city.match(arrondissementPattern)

  if (match) {
    return match[1].toUpperCase() // Return base city name
  }

  return city.toUpperCase()
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
