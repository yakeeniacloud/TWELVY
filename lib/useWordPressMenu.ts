import { useState, useEffect } from 'react'

export interface MenuItem {
  id: number
  title: string
  slug: string
  children: {
    id: number
    title: string
    slug: string
  }[]
}

export function useWordPressMenu() {
  const [menu, setMenu] = useState<MenuItem[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    async function fetchMenu() {
      try {
        setLoading(true)
        setError(null)

        const response = await fetch('/api/wordpress/menu')

        if (!response.ok) {
          throw new Error(`Failed to fetch menu: ${response.status}`)
        }

        const data = await response.json()
        setMenu(data.menu || [])
      } catch (err) {
        const errorMsg = err instanceof Error ? err.message : 'Unknown error'
        setError(errorMsg)
        console.error('Error fetching WordPress menu:', errorMsg)
      } finally {
        setLoading(false)
      }
    }

    fetchMenu()

    // Poll every 30 seconds for updates (like OPTIMUS)
    const interval = setInterval(fetchMenu, 30000)
    return () => clearInterval(interval)
  }, [])

  return { menu, loading, error }
}
