'use client'

import { useState, useEffect } from 'react'
import { useParams } from 'next/navigation'
import Link from 'next/link'

interface Stage {
  id: number
  id_site: number
  date_start: string
  date_end: string
  prix: number
  nb_places: number
  nb_inscrits: number
  visible: number
  site: {
    id: number
    nom: string
    ville: string
    adresse: string
    code_postal: string
    latitude?: number
    longitude?: number
  }
}

export default function StagesResultsPage() {
  const params = useParams()
  const city = (params.city as string).toUpperCase()
  const [stages, setStages] = useState<Stage[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [sortBy, setSortBy] = useState<'date' | 'prix'>('date')
  const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>('asc')

  useEffect(() => {
    async function fetchStages() {
      try {
        setLoading(true)
        const response = await fetch(`/api/stages/${city}`)
        if (!response.ok) {
          throw new Error('Failed to fetch stages')
        }
        const data = (await response.json()) as { stages: Stage[] }
        setStages(data.stages)
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Unknown error')
        setStages([])
      } finally {
        setLoading(false)
      }
    }

    fetchStages()
  }, [city])

  // Sort stages
  const sortedStages = [...stages].sort((a, b) => {
    if (sortBy === 'date') {
      const aDate = new Date(a.date_start).getTime()
      const bDate = new Date(b.date_start).getTime()
      return sortOrder === 'asc' ? aDate - bDate : bDate - aDate
    } else {
      return sortOrder === 'asc' ? a.prix - b.prix : b.prix - a.prix
    }
  })

  const formatDate = (dateString: string) => {
    const date = new Date(dateString)
    const options: Intl.DateTimeFormatOptions = {
      weekday: 'short',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    }
    return date.toLocaleDateString('fr-FR', options)
  }

  return (
    <div className="min-h-screen bg-white">
      {/* Header Section */}
      <div className="bg-gray-50 border-b border-gray-200 py-8">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <h1 className="text-4xl font-bold text-gray-900 mb-2">
            Stages de Récupération de Points - {city}
          </h1>
          <p className="text-gray-600">
            Trouvez les stages disponibles près de chez vous
          </p>
        </div>
      </div>

      {/* Main Content */}
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        {/* Sort Controls */}
        <div className="mb-6 flex gap-4 items-center">
          <label className="text-sm font-medium text-gray-700">Trier par:</label>
          <div className="flex gap-3">
            <button
              onClick={() => setSortBy('date')}
              className={`px-4 py-2 rounded-lg transition-colors ${
                sortBy === 'date'
                  ? 'bg-blue-600 text-white'
                  : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
              }`}
            >
              Date
            </button>
            <button
              onClick={() => setSortBy('prix')}
              className={`px-4 py-2 rounded-lg transition-colors ${
                sortBy === 'prix'
                  ? 'bg-blue-600 text-white'
                  : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
              }`}
            >
              Prix
            </button>
            <button
              onClick={() =>
                setSortOrder(sortOrder === 'asc' ? 'desc' : 'asc')
              }
              className="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
            >
              {sortOrder === 'asc' ? '↑' : '↓'}
            </button>
          </div>
        </div>

        {/* Loading State */}
        {loading && (
          <div className="text-center py-12">
            <p className="text-gray-600">Chargement des stages...</p>
          </div>
        )}

        {/* Error State */}
        {error && (
          <div className="text-center py-12">
            <p className="text-red-600">Erreur: {error}</p>
          </div>
        )}

        {/* Empty State */}
        {!loading && !error && stages.length === 0 && (
          <div className="text-center py-12">
            <p className="text-gray-600">Aucun stage trouvé pour cette ville.</p>
          </div>
        )}

        {/* Stages List */}
        {!loading && !error && stages.length > 0 && (
          <div className="space-y-4">
            {sortedStages.map((stage) => (
              <div
                key={stage.id}
                className="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow flex items-center gap-4"
              >
                {/* Red Accent Block */}
                <div className="w-14 h-14 bg-gradient-to-b from-red-500 to-red-600 rounded flex items-center justify-center flex-shrink-0">
                  <span className="text-white font-bold text-lg">
                    {stage.prix.toFixed(0)}€
                  </span>
                </div>

                {/* Stage Info */}
                <div className="flex-1">
                  <h3 className="text-lg font-semibold text-blue-700 uppercase mb-1">
                    {stage.site.nom}
                  </h3>
                  <p className="text-sm text-gray-600">
                    {stage.site.adresse}, {stage.site.code_postal}
                  </p>
                  <p className="text-sm text-gray-600">
                    {formatDate(stage.date_start)} au{' '}
                    {formatDate(stage.date_end)}
                  </p>
                </div>

                {/* Places Available */}
                <div className="text-right">
                  <p className="text-sm text-gray-600">
                    Places disponibles:{' '}
                    {stage.nb_places - stage.nb_inscrits}
                  </p>
                </div>

                {/* Button */}
                <Link
                  href={`/stages-recuperation-points/${city.toLowerCase()}/${stage.id}`}
                  className="bg-gradient-to-b from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold px-6 py-2 rounded-lg transition-all inline-block"
                >
                  Plus d'infos
                </Link>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  )
}
