'use client'

import { useState, useEffect } from 'react'

interface City {
  name: string
  count: number
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
        console.log('✅ Cities loaded:', data.cities)
        setCities(data.cities)
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
