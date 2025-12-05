'use client'

import { useState, useEffect } from 'react'
import { useParams } from 'next/navigation'
import Link from 'next/link'
import Image from 'next/image'
import { useWordPressMenu } from '@/lib/useWordPressMenu'

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
  const fullSlug = (params.slug as string) || ''

  // Parse slug: "MARSEILLE-13001" or "PARIS-75001"
  const lastHyphenIndex = fullSlug ? fullSlug.lastIndexOf('-') : -1
  const city = (lastHyphenIndex > 0 ? fullSlug.substring(0, lastHyphenIndex) : fullSlug).toUpperCase()
  const postal = lastHyphenIndex > 0 ? fullSlug.substring(lastHyphenIndex + 1) : ''

  const [stages, setStages] = useState<Stage[]>([])
  const [allStages, setAllStages] = useState<Stage[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [sortBy, setSortBy] = useState<'date' | 'prix' | 'proximite' | null>(null)
  const [nearbyCities, setNearbyCities] = useState<{ city: string; distance: number }[]>([])
  const [showCitiesDropdown, setShowCitiesDropdown] = useState(false)
  const [selectedCity, setSelectedCity] = useState<string | null>(null)
  const [searchQuery, setSearchQuery] = useState('')

  // Load more pagination (6 per page)
  const [visibleCount, setVisibleCount] = useState(6)
  const STAGES_PER_LOAD = 6

  // Fetch WordPress menu
  const { menu } = useWordPressMenu()

  // FAQ state
  const [openFaqIndex, setOpenFaqIndex] = useState<number | null>(null)

  useEffect(() => {
    async function fetchStages() {
      try {
        setLoading(true)

        // Check cache first
        const cacheKey = `stages_cache_${city}`
        const cached = sessionStorage.getItem(cacheKey)

        if (cached) {
          try {
            const { data: cachedData, timestamp } = JSON.parse(cached)
            const age = Date.now() - timestamp

            if (age < 5 * 60 * 1000) {
              let allFetchedStages = cachedData.stages
              sessionStorage.removeItem(cacheKey)

              const normalizedStages = allFetchedStages.map(s => ({
                ...s,
                site: { ...s.site, ville: s.site.ville.toUpperCase() }
              }))

              const today = new Date()
              const todayStr = today.toISOString().split('T')[0]

              const filteredStages = normalizedStages.filter(s => {
                if (!s.date_start || s.date_start === '0000-00-00') return false
                return s.date_start >= todayStr
              })

              const citiesInResults = new Set<string>()
              filteredStages.forEach(s => {
                if (s.site.ville !== city) {
                  citiesInResults.add(s.site.ville)
                }
              })
              const nearbyCitiesList = Array.from(citiesInResults).sort().map(c => ({ city: c, distance: 0 }))
              setNearbyCities(nearbyCitiesList)

              setAllStages(filteredStages)
              setStages(filteredStages)
              setLoading(false)
              return
            } else {
              sessionStorage.removeItem(cacheKey)
            }
          } catch (e) {
            sessionStorage.removeItem(cacheKey)
          }
        }

        // Fetch from API
        const response = await fetch(`/api/stages/${city}`)

        if (!response.ok) {
          throw new Error('Failed to fetch stages')
        }

        const data = (await response.json()) as { stages: Stage[] }
        let allFetchedStages = data.stages

        const normalizedStages = allFetchedStages.map(s => ({
          ...s,
          site: { ...s.site, ville: s.site.ville.toUpperCase() }
        }))

        const today = new Date()
        const todayStr = today.toISOString().split('T')[0]

        const filteredStages = normalizedStages.filter(s => {
          if (!s.date_start || s.date_start === '0000-00-00') return false
          return s.date_start >= todayStr
        })

        const citiesInResults = new Set<string>()
        filteredStages.forEach(s => {
          if (s.site.ville !== city) {
            citiesInResults.add(s.site.ville)
          }
        })
        const nearbyCitiesList = Array.from(citiesInResults).sort().map(c => ({ city: c, distance: 0 }))
        setNearbyCities(nearbyCitiesList)

        setAllStages(filteredStages)
        setStages(filteredStages)
      } catch (err) {
        const errorMsg = err instanceof Error ? err.message : 'Unknown error'
        setError(errorMsg)
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

    // Filter by selected city
    if (selectedCity) {
      filtered = filtered.filter(s => s.site.ville === selectedCity)
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

    setStages(filtered)
    setVisibleCount(6) // Reset to 6 when filters change
  }, [sortBy, selectedCity, allStages, nearbyCities])

  const formatDate = (dateStart: string, dateEnd: string) => {
    const start = new Date(dateStart)
    const end = new Date(dateEnd)

    const dayStart = start.toLocaleDateString('fr-FR', { weekday: 'short' }).replace('.', '')
    const dayEnd = end.toLocaleDateString('fr-FR', { weekday: 'short' }).replace('.', '')
    const dayNumStart = start.getDate()
    const dayNumEnd = end.getDate()
    const month = start.toLocaleDateString('fr-FR', { month: 'long' })

    // Capitalize first letter
    const capitalizedDayStart = dayStart.charAt(0).toUpperCase() + dayStart.slice(1)
    const capitalizedDayEnd = dayEnd.charAt(0).toUpperCase() + dayEnd.slice(1)
    const capitalizedMonth = month.charAt(0).toUpperCase() + month.slice(1)

    return `${capitalizedDayStart} ${dayNumStart} et ${capitalizedDayEnd} ${dayNumEnd} ${capitalizedMonth}`
  }

  // Get the cheapest stage for badge
  const cheapestStage = stages.length > 0
    ? stages.reduce((min, stage) => stage.prix < min.prix ? stage : min, stages[0])
    : null

  const visibleStages = stages.slice(0, visibleCount)
  const hasMore = visibleCount < stages.length

  const handleLoadMore = () => {
    setVisibleCount(prev => prev + STAGES_PER_LOAD)
  }

  return (
    <div className="min-h-screen bg-white">
      {/* Header */}
      <header className="bg-white border-b">
        {/* Top bar */}
        <div className="bg-white border-b border-gray-200 py-3">
          <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <Link href="/">
              <Image
                src="/prostagespermis-logo.png"
                alt="ProStagesPermis"
                width={200}
                height={60}
                className="h-12 w-auto"
              />
            </Link>
            <Link href="/espace-client" className="flex items-center gap-2 text-gray-700 hover:text-gray-900">
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <span className="text-sm">Espace Client</span>
            </Link>
          </div>
        </div>

        {/* Navigation bar */}
        <nav className="bg-gray-900 text-white">
          <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div className="flex items-center justify-between h-14">
              <div className="flex items-center gap-8">
                {menu.map((item) => (
                  <Link
                    key={item.id}
                    href={`/${item.slug}`}
                    className="text-sm hover:text-gray-300 transition-colors"
                  >
                    {item.title}
                  </Link>
                ))}
              </div>
              <Link
                href="/aide-et-contact"
                className="text-sm hover:text-gray-300 transition-colors"
              >
                Aide et contact
              </Link>
            </div>
          </div>
        </nav>
      </header>

      {/* Hero Section */}
      <section className="bg-white py-12">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
          <h1 className="text-4xl font-bold text-gray-900 mb-4">
            Stage Récupération de Points à {city.charAt(0) + city.slice(1).toLowerCase()}
          </h1>
          <p className="text-gray-600 mb-8">
            Réservez votre stage agréé en quelques clics et récupérez 4 points en 2 jours
          </p>

          {/* Feature Icons */}
          <div className="flex items-center justify-center gap-8 mb-8">
            <div className="flex items-center gap-2">
              <div className="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                <svg className="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 12l-2-2m0 0l-2 2m2-2V6m12 6a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <span className="text-sm font-medium text-gray-700">Agréé Préfecture</span>
            </div>

            <div className="flex items-center gap-2">
              <div className="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                <span className="text-yellow-600 font-bold">+4</span>
              </div>
              <span className="text-sm font-medium text-gray-700">+ 4 points en 48h</span>
            </div>

            <div className="flex items-center gap-2">
              <div className="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                <svg className="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clipRule="evenodd"/>
                </svg>
              </div>
              <span className="text-sm font-medium text-gray-700">Prix le plus bas garanti</span>
            </div>

            <div className="flex items-center gap-2">
              <div className="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                <svg className="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clipRule="evenodd"/>
                </svg>
              </div>
              <span className="text-sm font-medium text-gray-700">14 jours pour changer d'avis</span>
            </div>
          </div>

          {/* Prefecture Badge */}
          <div className="inline-flex items-center gap-2 bg-blue-100 px-6 py-3 rounded-full">
            <div className="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center">
              <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <span className="text-blue-900 font-medium text-sm">
              Stages Agréés par la Préfecture des Bouches-du-Rhône (13)
            </span>
          </div>
        </div>
      </section>

      {/* Filter Bar */}
      <section className="bg-gray-50 border-y border-gray-200 py-6">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="flex items-center gap-4 flex-wrap">
            {/* Search Input */}
            <div className="relative flex-1 min-w-[200px]">
              <input
                type="text"
                placeholder="Ville ou code postal"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full pl-4 pr-10 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <svg
                className="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>

            {/* Filter Buttons */}
            <button
              onClick={() => setSortBy(sortBy === 'date' ? null : 'date')}
              className={`px-6 py-2 rounded border transition-colors ${
                sortBy === 'date'
                  ? 'bg-blue-600 text-white border-blue-600'
                  : 'bg-white text-gray-700 border-gray-300 hover:border-gray-400'
              }`}
            >
              Date
            </button>

            <button
              onClick={() => setSortBy(sortBy === 'prix' ? null : 'prix')}
              className={`px-6 py-2 rounded border transition-colors ${
                sortBy === 'prix'
                  ? 'bg-blue-600 text-white border-blue-600'
                  : 'bg-white text-gray-700 border-gray-300 hover:border-gray-400'
              }`}
            >
              Prix
            </button>

            <button
              onClick={() => setSortBy(sortBy === 'proximite' ? null : 'proximite')}
              className={`px-6 py-2 rounded border transition-colors ${
                sortBy === 'proximite'
                  ? 'bg-blue-600 text-white border-blue-600'
                  : 'bg-white text-gray-700 border-gray-300 hover:border-gray-400'
              }`}
            >
              Proximité
            </button>

            {/* Ville Dropdown */}
            <div className="relative">
              <button
                onClick={() => setShowCitiesDropdown(!showCitiesDropdown)}
                className="px-6 py-2 rounded border border-gray-300 bg-white text-gray-700 hover:border-gray-400 transition-colors flex items-center gap-2"
              >
                <span>{selectedCity || 'Ville'}</span>
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              {showCitiesDropdown && (
                <div className="absolute top-full mt-2 w-64 bg-white border border-gray-300 rounded shadow-lg z-10 max-h-96 overflow-y-auto">
                  <button
                    onClick={() => {
                      setSelectedCity(null)
                      setShowCitiesDropdown(false)
                    }}
                    className="w-full px-4 py-2 text-left hover:bg-gray-100 text-sm font-medium"
                  >
                    Toutes les villes
                  </button>
                  <button
                    onClick={() => {
                      setSelectedCity(city)
                      setShowCitiesDropdown(false)
                    }}
                    className="w-full px-4 py-2 text-left hover:bg-gray-100 text-sm"
                  >
                    {city}
                  </button>
                  {nearbyCities.map((nearby) => (
                    <button
                      key={nearby.city}
                      onClick={() => {
                        setSelectedCity(nearby.city)
                        setShowCitiesDropdown(false)
                      }}
                      className="w-full px-4 py-2 text-left hover:bg-gray-100 text-sm"
                    >
                      {nearby.city}
                    </button>
                  ))}
                </div>
              )}
            </div>
          </div>
        </div>
      </section>

      {/* Stages List */}
      <section className="py-8">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          {loading && (
            <div className="text-center py-12">
              <p className="text-gray-600">Chargement des stages...</p>
            </div>
          )}

          {error && (
            <div className="text-center py-12">
              <p className="text-red-600">Erreur: {error}</p>
            </div>
          )}

          {!loading && !error && stages.length === 0 && (
            <div className="text-center py-12">
              <p className="text-gray-600">Aucun stage trouvé pour cette ville.</p>
            </div>
          )}

          {!loading && !error && stages.length > 0 && (
            <div className="space-y-4">
              {visibleStages.map((stage) => (
                <div
                  key={stage.id}
                  className="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow"
                >
                  <div className="flex items-start gap-6">
                    {/* Date Column */}
                    <div className="flex-shrink-0 w-48">
                      <p className="text-base font-semibold text-gray-900">
                        {formatDate(stage.date_start, stage.date_end)}
                      </p>
                      <button className="flex items-center gap-1 text-blue-600 text-sm mt-2 hover:underline">
                        <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd"/>
                        </svg>
                        Détails du stage
                      </button>
                    </div>

                    {/* Location Column */}
                    <div className="flex-1 flex items-center gap-4">
                      <div className="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                        <svg className="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                          <path fillRule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clipRule="evenodd"/>
                        </svg>
                      </div>
                      <div>
                        <p className="text-base font-semibold text-gray-900">{stage.site.ville}</p>
                        <p className="text-sm text-gray-600">{stage.site.adresse}</p>
                      </div>
                    </div>

                    {/* Price Column */}
                    <div className="flex-shrink-0 text-right">
                      <p className="text-3xl font-bold text-gray-900">{stage.prix}€</p>
                      {cheapestStage?.id === stage.id && (
                        <p className="text-xs text-red-600 font-medium mt-1">
                          Notre prix bas à {city.charAt(0) + city.slice(1).toLowerCase()}
                        </p>
                      )}
                    </div>

                    {/* Action Button */}
                    <div className="flex-shrink-0">
                      <Link
                        href={`/stages-recuperation-points/${fullSlug}/${stage.id}`}
                        className="block bg-green-600 hover:bg-green-700 text-white font-semibold px-8 py-3 rounded transition-colors"
                      >
                        Sélectionner
                      </Link>
                    </div>
                  </div>
                </div>
              ))}

              {/* Load More Button */}
              {hasMore && (
                <div className="text-center py-8">
                  <button
                    onClick={handleLoadMore}
                    className="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-12 py-3 rounded-full transition-colors"
                  >
                    Voir plus de stages
                  </button>
                </div>
              )}
            </div>
          )}
        </div>
      </section>

      {/* Pourquoi Réserver Section */}
      <section className="bg-gray-100 py-12">
        <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
          <Image
            src="/pourquoi-reserver.png"
            alt="Pourquoi réserver votre stage chez ProStagesPermis"
            width={1200}
            height={400}
            className="w-full h-auto"
          />
        </div>
      </section>

      {/* Google Reviews Section */}
      <section className="py-16 bg-white">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <h2 className="text-center mb-2">
            <span className="text-gray-900 text-3xl font-normal">Avis </span>
            <span className="text-red-600 text-3xl font-bold">Clients</span>
          </h2>

          <div className="grid grid-cols-1 md:grid-cols-4 gap-8 mt-12">
            {/* Google Rating Card */}
            <div className="text-center">
              <p className="text-2xl font-bold text-gray-900 mb-2">Excellent</p>
              <div className="flex justify-center gap-1 mb-2">
                {[...Array(5)].map((_, i) => (
                  <svg key={i} className="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                  </svg>
                ))}
              </div>
              <p className="text-gray-600 text-sm mb-4">4.7/5</p>
              <p className="text-gray-600 text-xs">Basé sur 499 avis</p>
              <div className="mt-4 flex justify-center">
                <div className="w-24 h-8 bg-gray-200 rounded flex items-center justify-center text-xs text-gray-500">
                  Google Logo
                </div>
              </div>
            </div>

            {/* Review Cards (Placeholders) */}
            {[1, 2, 3].map((i) => (
              <div key={i} className="bg-white border border-gray-200 rounded-lg p-6">
                <div className="flex items-center gap-3 mb-3">
                  <div className="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                    {i === 1 ? 'K' : i === 2 ? 'J' : 'M'}
                  </div>
                  <p className="font-semibold text-gray-900">
                    {i === 1 ? 'Katia rbenreguig' : i === 2 ? 'Joe Labaise' : 'Maeva Raviot'}
                  </p>
                </div>
                <div className="flex gap-1 mb-3">
                  {[...Array(5)].map((_, j) => (
                    <svg key={j} className="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                  ))}
                </div>
                <p className="text-sm text-gray-600">
                  {i === 1
                    ? 'Bonjour, je vous recommande vivement ProStagesPermis, ont peux y trouver des stages rapidement, pas très loin de chez nous et pas très cher comparé à d\'autre.'
                    : i === 2
                    ? 'Merci à vous pour votre compréhension je recommence pro stage permis Alex'
                    : 'Je recommande ! Le service de cette équipe est juste extraordinaire. Réactif et très arrangeant. Merci pour votre professionnalisme et votre écoute !'}
                </p>
              </div>
            ))}
          </div>

          <div className="text-center mt-8">
            <Link href="#" className="text-red-600 hover:underline font-medium">
              Lire les autres avis
            </Link>
          </div>
        </div>
      </section>

      {/* Qui est ProStagesPermis Section */}
      <section className="py-16 bg-gray-50">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <h2 className="text-center mb-12">
            <span className="text-gray-900 text-3xl font-normal">Qui est </span>
            <span className="text-red-600 text-3xl font-bold">ProStagesPermis</span>
          </h2>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
              <p className="text-gray-700 mb-4">
                Depuis 2008, ProStagesPermis est le site n° 1 spécialisé dans les stages de récupération de points. Notre mission : vous aider à sauver votre permis dans les temps, avec un stage au meilleur prix proche de chez vous. Plus de 857 000 conducteurs nous ont déjà fait confiance.
              </p>
              <ul className="space-y-3 text-gray-700">
                <li className="flex items-start gap-2">
                  <span className="text-green-600 mt-1">•</span>
                  <span>Près de 18 ans d'expérience dans les stages de récupération de points</span>
                </li>
                <li className="flex items-start gap-2">
                  <span className="text-green-600 mt-1">•</span>
                  <span>Des dizaines de milliers de conducteurs accompagnés partout en France</span>
                </li>
                <li className="flex items-start gap-2">
                  <span className="text-green-600 mt-1">•</span>
                  <span>Un réseau de centres de formation agréés partout en France</span>
                </li>
                <li className="flex items-start gap-2">
                  <span className="text-green-600 mt-1">•</span>
                  <span>Note Google 4,8/5 avis vérifiés</span>
                </li>
              </ul>
            </div>

            <div className="bg-white rounded-lg p-8 text-center border border-gray-200">
              <div className="w-32 h-32 bg-blue-600 rounded-lg mx-auto mb-4 flex items-center justify-center text-white font-bold text-lg">
                Europe 1 Logo
              </div>
              <p className="text-gray-900 font-medium mb-4">
                ProStagesPermis cité comme site de confiance par Europe 1
              </p>
              <button className="text-blue-600 hover:underline text-sm">
                Écouter l'extrait
              </button>
            </div>
          </div>
        </div>
      </section>

      {/* FAQ Section */}
      <section className="py-16 bg-white">
        <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
          <h2 className="text-center mb-12">
            <span className="text-gray-900 text-3xl font-normal">Questions </span>
            <span className="text-red-600 text-3xl font-bold">Fréquentes</span>
          </h2>

          <div className="space-y-4">
            {[1, 2, 3, 4].map((index) => (
              <div key={index} className="border border-gray-300 rounded-lg overflow-hidden">
                <button
                  onClick={() => setOpenFaqIndex(openFaqIndex === index ? null : index)}
                  className="w-full px-6 py-4 flex items-center justify-between bg-white hover:bg-gray-50 transition-colors"
                >
                  <div className="flex items-center gap-3">
                    <div className="w-8 h-8 rounded-full border-2 border-gray-400 flex items-center justify-center flex-shrink-0">
                      <span className="text-gray-600 text-lg">?</span>
                    </div>
                    <span className="text-left text-gray-900 font-medium">
                      A quel moment mes 4 points sont il crédités sur mon permis après un stage
                    </span>
                  </div>
                  <svg
                    className={`w-5 h-5 text-gray-600 transition-transform ${
                      openFaqIndex === index ? 'rotate-180' : ''
                    }`}
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
                {openFaqIndex === index && (
                  <div className="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <p className="text-gray-700 text-sm">
                      Vos 4 points seront crédités sur votre permis de conduire dans un délai de 48h à 72h après la fin du stage, à condition que l'attestation de stage soit envoyée à la préfecture.
                    </p>
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="bg-gray-900 text-white py-8">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between flex-wrap gap-4">
            <p className="text-sm text-gray-400">
              2025©ProStagesPermis
            </p>
            <div className="flex items-center gap-6 text-sm">
              <Link href="/qui-sommes-nous" className="text-gray-300 hover:text-white">
                Qui sommes-nous
              </Link>
              <Link href="/aide-et-contact" className="text-gray-300 hover:text-white">
                Aide et contact
              </Link>
              <Link href="/conditions-generales" className="text-gray-300 hover:text-white">
                Conditions générales de vente
              </Link>
              <Link href="/mentions-legales" className="text-gray-300 hover:text-white">
                Mentions légales
              </Link>
              <Link href="/espace-client" className="text-gray-300 hover:text-white">
                Espace Client
              </Link>
            </div>
          </div>
        </div>
      </footer>
    </div>
  )
}
