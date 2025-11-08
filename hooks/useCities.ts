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
        const response = await fetch('/api/cities')
        if (!response.ok) {
          throw new Error('Failed to fetch cities')
        }
        const data = (await response.json()) as { cities: string[] }
        setCities(data.cities)
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Unknown error')
        setCities([])
      } finally {
        setLoading(false)
      }
    }

    fetchCities()
  }, [])

  return { cities, loading, error }
}
