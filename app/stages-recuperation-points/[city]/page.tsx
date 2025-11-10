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
  const [allStages, setAllStages] = useState<Stage[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [sortBy, setSortBy] = useState<'pertinence' | 'proximite' | 'date' | 'prix'>('pertinence')
  const [selectedCities, setSelectedCities] = useState<string[]>([])
  const [allCities, setAllCities] = useState<string[]>([])

  useEffect(() => {
    async function fetchStages() {
      try {
        setLoading(true)
        const response = await fetch(`/api/stages/${city}`)
        if (!response.ok) {
          throw new Error('Failed to fetch stages')
        }
        const data = (await response.json()) as { stages: Stage[] }
        setAllStages(data.stages)
        setStages(data.stages)

        // Extract unique cities from stages
        const cities = Array.from(new Set(data.stages.map(s => s.site.ville))).sort()
        setAllCities(cities)
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Unknown error')
        setStages([])
      } finally {
        setLoading(false)
      }
    }

    fetchStages()
  }, [city])

  // Filter and sort stages
  useEffect(() => {
    let filtered = [...allStages]

    // Filter by selected cities (if any selected, show only those; if none, show all)
    if (selectedCities.length > 0) {
      filtered = filtered.filter(s => selectedCities.includes(s.site.ville))
    }

    // Sort
    if (sortBy === 'date') {
      filtered.sort((a, b) => {
        const aDate = new Date(a.date_start).getTime()
        const bDate = new Date(b.date_start).getTime()
        return aDate - bDate
      })
    } else if (sortBy === 'prix') {
      filtered.sort((a, b) => a.prix - b.prix)
    }
    // pertinence and proximite don't need sorting, keep original order

    setStages(filtered)
  }, [sortBy, selectedCities, allStages])

  const formatDate = (dateStart: string, dateEnd: string) => {
    const start = new Date(dateStart)
    const end = new Date(dateEnd)

    const dayStart = start.toLocaleDateString('fr-FR', { weekday: 'short' })
    const dayEnd = end.toLocaleDateString('fr-FR', { weekday: 'short' })
    const dateFormatStart = start.toLocaleDateString('fr-FR', { day: '2-digit', month: 'long' })
    const dateFormatEnd = end.toLocaleDateString('fr-FR', { day: '2-digit', month: 'long' })

    return `${dayStart} ${dateFormatStart} et ${dayEnd} ${dateFormatEnd}`
  }

  const toggleCity = (cityName: string) => {
    setSelectedCities(prev =>
      prev.includes(cityName)
        ? prev.filter(c => c !== cityName)
        : [...prev, cityName]
    )
  }

  return (
    <div className="min-h-screen bg-white">
      {/* Header Section */}
      <div className="bg-white py-8 border-b border-gray-200">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-4">
            Stages de Récupération de Points à {city}: réservez en quelques clics !
          </h1>
          <p className="text-gray-700 max-w-3xl">
            Vous avez besoin de récupérer rapidement 4 points sur votre permis de conduire (stage volontaire) ou vous devez suivre un stage obligatoire ? Retrouvez les stages de récupération de points à {city} agréés par la Préfecture.
          </p>
        </div>
      </div>

      {/* Main Content Grid */}
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
          {/* Left Sidebar - Filters */}
          <div className="lg:col-span-2">
            {/* City Search */}
            <input
              type="text"
              placeholder="Ville ou CP"
              className="w-full px-3 py-2 border border-gray-300 rounded mb-6 text-sm"
            />

            {/* Sort Section */}
            <div className="mb-6">
              <div className="bg-gray-800 text-white px-3 py-2 text-xs uppercase font-bold rounded-t">
                Trier par
              </div>
              <div className="bg-white border border-gray-300 border-t-0 rounded-b p-4 space-y-3">
                <label className="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="sort"
                    checked={sortBy === 'pertinence'}
                    onChange={() => setSortBy('pertinence')}
                    className="w-4 h-4 text-blue-600"
                  />
                  <span className="text-sm font-medium text-gray-900">Pertinence</span>
                </label>
                <label className="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="sort"
                    checked={sortBy === 'proximite'}
                    onChange={() => setSortBy('proximite')}
                    className="w-4 h-4 text-blue-600"
                  />
                  <span className="text-sm font-medium text-gray-900">Proximité</span>
                </label>
                <label className="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="sort"
                    checked={sortBy === 'date'}
                    onChange={() => setSortBy('date')}
                    className="w-4 h-4 text-blue-600"
                  />
                  <span className="text-sm font-medium text-gray-900">Date</span>
                </label>
                <label className="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="sort"
                    checked={sortBy === 'prix'}
                    onChange={() => setSortBy('prix')}
                    className="w-4 h-4 text-blue-600"
                  />
                  <span className="text-sm font-medium text-gray-900">Prix</span>
                </label>
              </div>
            </div>

            {/* Filter Section */}
            <div>
              <div className="bg-gray-800 text-white px-3 py-2 text-xs uppercase font-bold rounded-t">
                Filtrer par
              </div>
              <div className="bg-white border border-gray-300 border-t-0 rounded-b p-4 space-y-3 max-h-96 overflow-y-auto">
                <label className="flex items-center gap-2 cursor-pointer border-b pb-3 font-medium">
                  <input
                    type="checkbox"
                    checked={selectedCities.length === 0}
                    onChange={() => setSelectedCities([])}
                    className="w-4 h-4 text-blue-600"
                  />
                  <span className="text-sm text-gray-900">Toutes les villes</span>
                </label>
                {allCities.map(cityName => (
                  <label key={cityName} className="flex items-center gap-2 cursor-pointer">
                    <input
                      type="checkbox"
                      checked={selectedCities.includes(cityName)}
                      onChange={() => toggleCity(cityName)}
                      className="w-4 h-4 text-blue-600"
                    />
                    <span className="text-sm text-gray-600">{cityName}</span>
                  </label>
                ))}
              </div>
            </div>
          </div>

          {/* Center Content */}
          <div className="lg:col-span-7">
            {/* Sort Inline */}
            <div className="mb-6 flex items-center gap-4">
              <span className="text-sm font-medium text-gray-700">Trier par:</span>
              <div className="flex items-center gap-4">
                <label className="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="sort-inline"
                    checked={sortBy === 'pertinence'}
                    onChange={() => setSortBy('pertinence')}
                    className="w-4 h-4 text-blue-600"
                  />
                  <span className="text-sm font-medium text-gray-900">Pertinence</span>
                </label>
                <label className="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="sort-inline"
                    checked={sortBy === 'proximite'}
                    onChange={() => setSortBy('proximite')}
                    className="w-4 h-4 text-blue-600"
                  />
                  <span className="text-sm font-medium text-gray-900">Proximité</span>
                </label>
                <label className="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="sort-inline"
                    checked={sortBy === 'date'}
                    onChange={() => setSortBy('date')}
                    className="w-4 h-4 text-blue-600"
                  />
                  <span className="text-sm font-medium text-gray-900">Date</span>
                </label>
                <label className="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="sort-inline"
                    checked={sortBy === 'prix'}
                    onChange={() => setSortBy('prix')}
                    className="w-4 h-4 text-blue-600"
                  />
                  <span className="text-sm font-medium text-gray-900">Prix</span>
                </label>
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
                {stages.map((stage) => (
                  <div
                    key={stage.id}
                    className="bg-white border border-gray-200 rounded p-4 hover:shadow-md transition-shadow flex items-stretch gap-4"
                  >
                    {/* Red Star Block */}
                    <div className="w-16 h-16 bg-red-600 rounded flex items-center justify-center flex-shrink-0 relative">
                      <span className="text-white text-3xl">★</span>
                      <span className="absolute bottom-1 right-1 text-white text-xs bg-red-700 px-1 rounded">
                        Bon plan
                      </span>
                    </div>

                    {/* Stage Info */}
                    <div className="flex-1 flex flex-col justify-between">
                      <div>
                        <h3 className="text-base font-bold text-blue-700 uppercase mb-1">
                          {stage.site.ville}
                        </h3>
                        <p className="text-sm text-gray-600 mb-1">
                          {stage.site.nom}
                        </p>
                        <p className="text-xs text-gray-500">
                          {stage.site.adresse}
                        </p>
                      </div>
                      <div className="flex items-center gap-2 mt-2">
                        <span className="text-red-600 text-xs flex items-center gap-1">
                          ⊕
                          <button className="text-blue-700 hover:underline text-xs">
                            Plus d'infos
                          </button>
                        </span>
                      </div>
                    </div>

                    {/* Dates */}
                    <div className="flex flex-col justify-center items-end">
                      <p className="text-sm text-gray-600 text-right">
                        {formatDate(stage.date_start, stage.date_end)}
                      </p>
                    </div>

                    {/* Price and Button */}
                    <div className="flex flex-col justify-between items-end gap-2">
                      <p className="text-2xl font-bold text-gray-900">
                        {stage.prix.toFixed(0)} €
                      </p>
                      <Link
                        href={`/stages-recuperation-points/${city.toLowerCase()}/${stage.id}`}
                        className="bg-gradient-to-b from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold px-5 py-2 rounded text-sm transition-all"
                      >
                        Sélectionner
                      </Link>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>

          {/* Right Sidebar - Engagements */}
          <div className="lg:col-span-3">
            <div className="bg-white">
              <h2 className="text-lg font-bold text-gray-900 mb-4">
                NOS <span className="text-red-600">ENGAGEMENTS</span>
              </h2>
              <div className="space-y-4">
                {/* Engagement 1 */}
                <div className="flex gap-3">
                  <div className="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span className="text-amber-900 font-bold text-lg">+4</span>
                  </div>
                  <div>
                    <p className="text-sm font-semibold text-gray-900">+4 Points en 48h</p>
                    <button className="text-xs text-blue-700 hover:underline">
                      ▼ plus d'infos
                    </button>
                  </div>
                </div>

                {/* Engagement 2 */}
                <div className="flex gap-3">
                  <div className="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span className="text-amber-900 text-lg">✓</span>
                  </div>
                  <div>
                    <p className="text-sm font-semibold text-gray-900">Stages Agréés sur MARSEILLE-15EME 13015</p>
                    <button className="text-xs text-blue-700 hover:underline">
                      ▼ plus d'infos
                    </button>
                  </div>
                </div>

                {/* Engagement 3 */}
                <div className="flex gap-3">
                  <div className="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span className="text-amber-900 text-lg">€</span>
                  </div>
                  <div>
                    <p className="text-sm font-semibold text-gray-900">Prix le Plus Bas Garanti</p>
                    <button className="text-xs text-blue-700 hover:underline">
                      ▼ plus d'infos
                    </button>
                  </div>
                </div>

                {/* Engagement 4 */}
                <div className="flex gap-3">
                  <div className="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span className="text-amber-900 text-lg">↺</span>
                  </div>
                  <div>
                    <p className="text-sm font-semibold text-gray-900">14 Jours pour Changer d'Avis</p>
                    <button className="text-xs text-blue-700 hover:underline">
                      ▼ plus d'infos
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
