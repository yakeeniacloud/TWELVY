import { useState, useEffect } from 'react'

interface WordPressContent {
  id: number
  title: string
  content: string
  slug: string
}

export function useWordPressContent(slug: string) {
  const [content, setContent] = useState<WordPressContent | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    async function fetchContent() {
      try {
        setLoading(true)
        setError(null)

        const response = await fetch(`/api/wordpress/${slug}`)

        if (!response.ok) {
          throw new Error('Failed to fetch WordPress content')
        }

        const data = await response.json()
        setContent(data)
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Unknown error')
        setContent(null)
      } finally {
        setLoading(false)
      }
    }

    if (slug) {
      fetchContent()
    }
  }, [slug])

  return { content, loading, error }
}
