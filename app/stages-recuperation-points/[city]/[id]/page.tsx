'use client'

import { useState, useEffect } from 'react'
import { useParams } from 'next/navigation'
import Link from 'next/link'

interface Stage {
  id: number
  id_site: number
  date1: string
  date2: string
  prix: number
  nb_places_allouees: number
  nb_inscrits: number
  site_nom: string
  ville: string
  adresse: string
  code_postal: string
  latitude?: number
  longitude?: number
}

export default function StageDetailPage() {
  const params = useParams()
  const city = (params.city as string).toLowerCase()
  const id = params.id as string
  const [stage, setStage] = useState<Stage | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    async function fetchStage() {
      try {
        setLoading(true)
        const response = await fetch(`/api/stages/${city.toUpperCase()}/${id}`)
        if (!response.ok) {
          throw new Error('Failed to fetch stage')
        }
        const data = (await response.json()) as { stage: Stage }
        setStage(data.stage)
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Unknown error')
      } finally {
        setLoading(false)
      }
    }

    fetchStage()
  }, [city, id])

  const formatDate = (dateString: string) => {
    const date = new Date(dateString)
    const options: Intl.DateTimeFormatOptions = {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    }
    return date.toLocaleDateString('fr-FR', options)
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-white flex items-center justify-center">
        <p className="text-gray-600">Chargement des détails du stage...</p>
      </div>
    )
  }

  if (error || !stage) {
    return (
      <div className="min-h-screen bg-white flex items-center justify-center">
        <p className="text-red-600">Erreur: {error || 'Stage non trouvé'}</p>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-white">
      {/* Back Button */}
      <div className="bg-gray-50 border-b border-gray-200 py-4">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Link
            href={`/stages-recuperation-points/${city}`}
            className="text-blue-600 hover:text-blue-800 font-semibold"
          >
            ← Retour aux résultats
          </Link>
        </div>
      </div>

      {/* Main Content */}
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        {/* Header */}
        <h1 className="text-4xl font-bold text-gray-900 mb-2">
          {stage.site_nom}
        </h1>
        <p className="text-gray-600 mb-8">
          {stage.adresse}, {stage.code_postal} {stage.ville}
        </p>

        {/* Price Banner */}
        <div className="bg-gradient-to-r from-green-500 to-green-600 text-white p-8 rounded-lg mb-8">
          <div className="flex justify-between items-center">
            <div>
              <p className="text-sm opacity-90">Prix du stage</p>
              <p className="text-5xl font-bold">{stage.prix}€</p>
            </div>
            <div className="text-right">
              <p className="text-2xl font-bold">+4 points</p>
              <p className="text-sm opacity-90">en 48h</p>
            </div>
          </div>
        </div>

        {/* Dates */}
        <div className="bg-gray-50 p-6 rounded-lg mb-8">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Dates</h2>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <p className="text-sm text-gray-600">Début</p>
              <p className="font-semibold text-gray-900">
                {formatDate(stage.date1)}
              </p>
            </div>
            <div>
              <p className="text-sm text-gray-600">Fin</p>
              <p className="font-semibold text-gray-900">
                {formatDate(stage.date2)}
              </p>
            </div>
          </div>
        </div>

        {/* Places */}
        <div className="bg-gray-50 p-6 rounded-lg mb-8">
          <h2 className="text-lg font-semibold text-gray-900 mb-2">
            Places disponibles
          </h2>
          <p className="text-2xl font-bold text-green-600">
            {stage.nb_places_allouees - stage.nb_inscrits}/{stage.nb_places_allouees}
          </p>
        </div>

        {/* CTA Button */}
        <Link
          href={`/stages-recuperation-points/${city}/${id}/inscription`}
          className="inline-block w-full bg-gradient-to-b from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-4 px-6 rounded-lg text-center text-lg transition-all"
        >
          Sélectionner et S'inscrire
        </Link>
      </div>
    </div>
  )
}
