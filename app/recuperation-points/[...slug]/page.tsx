'use client'

import { useState, useEffect } from 'react'
import { useParams } from 'next/navigation'
import Link from 'next/link'
import { getCitiesInRadius } from '@/lib/cityCoordinates'
import { useWordPressContent } from '@/lib/useWordPressContent'
import { parseRecuperationPointsSlug, buildRecuperationPointsUrl, buildWordPressSlug } from '@/lib/urlUtils'
import StageDetailsModal from '@/components/stages/StageDetailsModal'

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
  const slugArray = params.slug as string[]
  // Reconstruct slug from catch-all array: ['MARSEILLE', '13001'] -> 'MARSEILLE-13001'
  const slug = slugArray?.join('-') || ''
  const parsed = parseRecuperationPointsSlug(slug)

  if (!parsed) {
    return <div>Invalid URL format</div>
  }

  const { city, postal } = parsed
  const [stages, setStages] = useState<Stage[]>([])
  const [allStages, setAllStages] = useState<Stage[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [sortBy, setSortBy] = useState<'pertinence' | 'proximite' | 'date' | 'prix'>('pertinence')
  const [selectedCities, setSelectedCities] = useState<string[] | null>(null) // null = all nearby cities
  const [allCities, setAllCities] = useState<string[]>([])
  const [nearbyCities, setNearbyCities] = useState<{ city: string; distance: number }[]>([])
  const [searchInput, setSearchInput] = useState('')
  const [selectedStage, setSelectedStage] = useState<Stage | null>(null)
  const [modalOpen, setModalOpen] = useState(false)

  // Fetch city-specific WordPress content using city name
  const { content: cityContent, loading: cityContentLoading } = useWordPressContent(buildWordPressSlug(city))

  useEffect(() => {
    async function fetchStages() {
      try {
        setLoading(true)

        // Calculate nearby cities within 30-40km range FIRST
        const nearby = getCitiesInRadius(city, 40)
        setNearbyCities(nearby)

        // Build list of all cities to fetch: searched city + nearby cities
        const citiesToFetch = [city]
        nearby.forEach(n => citiesToFetch.push(n.city))

        // FETCH courses from ALL nearby cities (not just the searched city)
        // This ensures we have courses available for all selectable cities in the sidebar
        let allFetchedStages: Stage[] = []

        for (const fetchCity of citiesToFetch) {
          try {
            const response = await fetch(`/api/stages/${fetchCity}`)
            if (response.ok) {
              const data = (await response.json()) as { stages: Stage[] }
              allFetchedStages = allFetchedStages.concat(data.stages)
            }
          } catch (err) {
            // Continue fetching other cities if one fails
            console.error(`Failed to fetch stages for ${fetchCity}:`, err)
          }
        }

        // NORMALIZE: Convert all city names to UPPERCASE for consistency
        const normalizedStages = allFetchedStages.map(s => ({
          ...s,
          site: {
            ...s.site,
            ville: s.site.ville.toUpperCase()
          }
        }))

        // FILTER: Only keep courses from searched city + cities within 30-40km
        const citiesToInclude = new Set(citiesToFetch.map(c => c.toUpperCase()))

        // Also filter by date: only show courses after today
        const today = new Date()
        today.setHours(0, 0, 0, 0) // Reset to start of day for fair comparison

        const filteredStages = normalizedStages.filter(s => {
          // Check city is in range
          const inRange = citiesToInclude.has(s.site.ville)
          // Check date is after today
          const courseDate = new Date(s.date_start)
          courseDate.setHours(0, 0, 0, 0)
          const isAfterToday = courseDate >= today

          return inRange && isAfterToday
        })

        // Sort by pertinence (proximity + price blend) and limit to 100
        const stagesWithScore = filteredStages.map(stage => {
          const distance = nearby.find(c => c.city === stage.site.ville)?.distance ?? 0
          const isSearchedCity = stage.site.ville === city ? 0 : 1 // 0 for searched city, 1 for nearby

          // Simple pertinence score: prioritize searched city, then by price, then by distance
          const pertinenceScore = (isSearchedCity * 100) + (stage.prix / 10) + distance

          return { stage, pertinenceScore }
        })

        // Sort by pertinence and take top 100
        stagesWithScore.sort((a, b) => a.pertinenceScore - b.pertinenceScore)
        const top100Stages = stagesWithScore.slice(0, 100).map(item => item.stage)

        setAllStages(top100Stages)
        setStages(top100Stages)

        // Get ALL cities from database for autocomplete
        async function fetchAllCities() {
          try {
            const citiesResponse = await fetch('/api/cities')
            if (citiesResponse.ok) {
              const { cities } = (await citiesResponse.json()) as { cities: string[] }
              setAllCities(cities.map(c => c.toUpperCase()).sort())
            } else {
              const citiesSet = new Set(top100Stages.map(s => s.site.ville))
              setAllCities(Array.from(citiesSet).sort())
            }
          } catch {
            const citiesSet = new Set(top100Stages.map(s => s.site.ville))
            setAllCities(Array.from(citiesSet).sort())
          }
        }

        fetchAllCities()

        // DEFAULT STATE: "Toutes les villes" is pre-selected (null = all nearby cities)
        setSelectedCities(null)
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

    // Filter by selected cities
    // selectedCities = null means "Toutes les villes" (all nearby cities - show all)
    // selectedCities = [] means no cities selected (shouldn't happen, but treat as show all)
    // selectedCities = [array] means user selected specific cities (show only those)
    if (selectedCities !== null && selectedCities.length > 0) {
      filtered = filtered.filter(s => selectedCities.includes(s.site.ville))
    }

    // Apply sorting
    if (sortBy === 'proximite') {
      const stagesWithDistance = filtered.map(stage => ({
        stage,
        distance: nearbyCities.find(c => c.city === stage.site.ville)?.distance ?? 0,
      }))
      stagesWithDistance.sort((a, b) => a.distance - b.distance)
      filtered = stagesWithDistance.map(item => item.stage)
    } else if (sortBy === 'date') {
      filtered.sort((a, b) => {
        const aDate = new Date(a.date_start).getTime()
        const bDate = new Date(b.date_start).getTime()
        return aDate - bDate
      })
    } else if (sortBy === 'prix') {
      filtered.sort((a, b) => a.prix - b.prix)
    }
    // 'pertinence' doesn't need re-sorting (already sorted by pertinence score)

    setStages(filtered)
  }, [sortBy, selectedCities, allStages, nearbyCities])

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
    setSelectedCities(prev => {
      if (prev === null) {
        // Currently showing all cities, start selection with this city
        return [cityName]
      } else if (prev.includes(cityName)) {
        // City is selected, remove it
        const updated = prev.filter(c => c !== cityName)
        // If no cities left, go back to "all cities"
        return updated.length === 0 ? null : updated
      } else {
        // City not selected, add it
        return [...prev, cityName]
      }
    })
  }

  const handleCitySearch = (searchedCity: string) => {
    // Find first postal code for this city from stages
    const firstStageForCity = allStages.find(s => s.site.ville === searchedCity.toUpperCase())
    if (firstStageForCity) {
      const newUrl = buildRecuperationPointsUrl(searchedCity, firstStageForCity.site.code_postal)
      window.location.href = newUrl
    }
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
            {/* City Search with Autocomplete */}
            <div className="relative mb-6">
              <input
                type="text"
                placeholder="Ville ou CP"
                value={searchInput}
                onChange={(e) => setSearchInput(e.target.value)}
                onKeyDown={(e) => {
                  if (e.key === 'Enter' && searchInput.trim()) {
                    const matchedCity = allCities.find(c =>
                      c.toUpperCase().startsWith(searchInput.toUpperCase())
                    )
                    if (matchedCity) {
                      handleCitySearch(matchedCity)
                    }
                  }
                }}
                className="w-full px-3 py-2 border border-gray-300 rounded text-sm"
              />
              {searchInput && (
                <div className="absolute top-full left-0 right-0 bg-white border border-gray-300 border-t-0 rounded-b shadow-lg z-10">
                  {allCities
                    .filter(c => c.toUpperCase().startsWith(searchInput.toUpperCase()))
                    .slice(0, 5)
                    .map(filteredCity => (
                      <button
                        key={filteredCity}
                        onClick={() => {
                          handleCitySearch(filteredCity)
                        }}
                        className="w-full text-left px-3 py-2 hover:bg-gray-100 text-sm text-gray-700"
                      >
                        {filteredCity}
                      </button>
                    ))}
                </div>
              )}
            </div>

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
                {/* "Toutes les villes" - DEFAULT state */}
                <label className="flex items-center gap-2 cursor-pointer border-b pb-3 font-medium">
                  <input
                    type="checkbox"
                    checked={selectedCities === null}
                    onChange={() => setSelectedCities(null)}
                    className="w-4 h-4 text-blue-600"
                  />
                  <span className="text-sm text-gray-900">Toutes les villes</span>
                </label>

                {/* Searched city first */}
                <label className="flex items-center gap-2 cursor-pointer font-medium">
                  <input
                    type="checkbox"
                    checked={selectedCities !== null && selectedCities.includes(city)}
                    onChange={() => toggleCity(city)}
                    className="w-4 h-4 text-blue-600"
                  />
                  <span className="text-sm text-gray-900">{city}</span>
                </label>

                {/* Nearby cities with distances (only show within 30-40km) */}
                {nearbyCities.map(nearby => (
                  <label key={nearby.city} className="flex items-center gap-2 cursor-pointer">
                    <input
                      type="checkbox"
                      checked={selectedCities !== null && selectedCities.includes(nearby.city)}
                      onChange={() => toggleCity(nearby.city)}
                      className="w-4 h-4 text-blue-600"
                    />
                    <span className="text-sm text-gray-600">
                      {nearby.city} ({nearby.distance} km)
                    </span>
                  </label>
                ))}

                {/* NOTE: Removed "Other cities not in range" section - sidebar now only shows nearby cities (30-40km) */}
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
                          <button
                            onClick={() => {
                              setSelectedStage(stage)
                              setModalOpen(true)
                            }}
                            className="text-blue-700 hover:underline text-xs cursor-pointer"
                          >
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
                    <p className="text-sm font-semibold text-gray-900">Stages Agréés</p>
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

      {/* City-Specific WordPress Content Below Courses */}
      {cityContent && (
        <div className="bg-gray-50 border-t border-gray-200 py-8 px-4 mt-8">
          <div className="mx-auto max-w-3xl">
            <div
              className="prose prose-sm max-w-none text-gray-700"
              dangerouslySetInnerHTML={{ __html: cityContent.content }}
            />
          </div>
        </div>
      )}

      {/* Google Maps Modal */}
      {selectedStage && (
        <StageDetailsModal
          stage={selectedStage}
          isOpen={modalOpen}
          onClose={() => setModalOpen(false)}
          city={city.toLowerCase()}
        />
      )}
    </div>
  )
}
