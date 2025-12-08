'use client'

import { useState, useEffect } from 'react'
import { useParams } from 'next/navigation'
import Link from 'next/link'
import Image from 'next/image'

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

  const lastHyphenIndex = fullSlug ? fullSlug.lastIndexOf('-') : -1
  const city = (lastHyphenIndex > 0 ? fullSlug.substring(0, lastHyphenIndex) : fullSlug).toUpperCase()

  const [stages, setStages] = useState<Stage[]>([])
  const [allStages, setAllStages] = useState<Stage[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [sortBy, setSortBy] = useState<'date' | 'prix' | 'proximite' | null>(null)
  const [nearbyCities, setNearbyCities] = useState<{ city: string; distance: number }[]>([])
  const [showCitiesDropdown, setShowCitiesDropdown] = useState(false)
  const [selectedCity, setSelectedCity] = useState<string | null>(null)
  const [searchQuery, setSearchQuery] = useState('')
  const [visibleCount, setVisibleCount] = useState(6)
  const [openFaqIndex, setOpenFaqIndex] = useState<number | null>(null)
  const [currentReviewIndex, setCurrentReviewIndex] = useState(0)

  const STAGES_PER_LOAD = 6

  useEffect(() => {
    async function fetchStages() {
      try {
        setLoading(true)
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
                if (s.site.ville !== city) citiesInResults.add(s.site.ville)
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

        const response = await fetch(`/api/stages/${city}`)
        if (!response.ok) throw new Error('Failed to fetch stages')

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
          if (s.site.ville !== city) citiesInResults.add(s.site.ville)
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

  useEffect(() => {
    let filtered = [...allStages]

    if (selectedCity) {
      filtered = filtered.filter(s => s.site.ville === selectedCity)
    }

    if (sortBy === 'proximite') {
      const stagesWithDistance = filtered.map(stage => ({
        stage,
        distance: nearbyCities.find(c => c.city === stage.site.ville)?.distance ?? 0,
      }))
      stagesWithDistance.sort((a, b) => a.distance - b.distance)
      filtered = stagesWithDistance.map(item => item.stage)
    } else if (sortBy === 'date') {
      filtered.sort((a, b) => new Date(a.date_start).getTime() - new Date(b.date_start).getTime())
    } else if (sortBy === 'prix') {
      filtered.sort((a, b) => a.prix - b.prix)
    }

    setStages(filtered)
    setVisibleCount(6)
  }, [sortBy, selectedCity, allStages, nearbyCities])

  const formatDate = (dateStart: string, dateEnd: string) => {
    const start = new Date(dateStart)
    const end = new Date(dateEnd)
    const dayStart = start.toLocaleDateString('fr-FR', { weekday: 'short' }).replace('.', '')
    const dayEnd = end.toLocaleDateString('fr-FR', { weekday: 'short' }).replace('.', '')
    const dayNumStart = start.getDate()
    const dayNumEnd = end.getDate()
    const month = start.toLocaleDateString('fr-FR', { month: 'long' })
    const capitalizedDayStart = dayStart.charAt(0).toUpperCase() + dayStart.slice(1)
    const capitalizedDayEnd = dayEnd.charAt(0).toUpperCase() + dayEnd.slice(1)
    const capitalizedMonth = month.charAt(0).toUpperCase() + month.slice(1)
    return `${capitalizedDayStart} ${dayNumStart} et ${capitalizedDayEnd} ${dayNumEnd} ${capitalizedMonth}`
  }

  const cheapestStage = stages.length > 0
    ? stages.reduce((min, stage) => stage.prix < min.prix ? stage : min, stages[0])
    : null

  const visibleStages = stages.slice(0, visibleCount)
  const hasMore = visibleCount < stages.length

  const faqData = [
    { id: 1, question: "A quel moment mes 4 points sont il crédités sur mon permis après un stage" },
    { id: 2, question: "A quel moment mes 4 points sont il crédités sur mon permis après un stage" },
    { id: 3, question: "A quel moment mes 4 points sont il crédités sur mon permis après un stage" },
    { id: 4, question: "A quel moment mes 4 points sont il crédités sur mon permis après un stage" },
  ]

  return (
    <div className="bg-white w-full min-h-screen">
      {/* Main Content */}
      <main className="max-w-5xl mx-auto px-4 py-8">
        <h1 className="text-2xl font-normal text-center mb-3">
          Stage Récupération de Points à {city.charAt(0) + city.slice(1).toLowerCase()}
        </h1>

        <p className="text-center text-gray-700 mb-6">
          Réservez votre stage agréé en quelques clics et récupérez 4 points en 2 jours
        </p>

        {/* Reassurance Icons */}
        <div className="flex items-center justify-center gap-8 mb-6">
          <Image
            src="/agree-prefecture.png"
            alt="Agréé Préfecture"
            width={200}
            height={50}
            className="h-10 w-auto"
          />
          <Image
            src="/4points-48h.png"
            alt="+ 4 points en 48h"
            width={200}
            height={50}
            className="h-10 w-auto"
          />
          <Image
            src="/prix-bas-garanti.png"
            alt="Prix le plus bas garanti"
            width={240}
            height={50}
            className="h-10 w-auto opacity-90"
          />
          <Image
            src="/14jours-changer-avis.png"
            alt="14 jours pour changer d'avis"
            width={280}
            height={50}
            className="h-10 w-auto"
          />
        </div>

        {/* Prefecture Badge */}
        <div className="flex items-center justify-center mb-6">
          <Image
            src="/prefecture-badge.png"
            alt="Stages Agréés par la Préfecture des Bouches-du-Rhône (13)"
            width={800}
            height={60}
            className="h-14 w-auto"
          />
        </div>

        {/* Filters Section */}
        <div className="flex items-center justify-center gap-4 mb-8">
          <div className="flex items-center gap-2 px-4 py-2 bg-white rounded-lg border border-gray-300">
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Ville ou code postal"
              className="bg-transparent border-none outline-none text-sm placeholder:text-gray-400"
            />
            <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>

          <span className="text-sm text-gray-700">Trier par :</span>

          {(['date', 'prix', 'proximite'] as const).map((option) => (
            <button
              key={option}
              onClick={() => setSortBy(sortBy === option ? null : option)}
              className={`px-6 py-1.5 text-xs rounded-lg border border-gray-400 transition-colors ${
                sortBy === option ? 'bg-[#c4cce1] text-gray-800' : 'bg-white text-gray-700'
              }`}
            >
              {option === 'date' ? 'Date' : option === 'prix' ? 'Prix' : 'Proximité'}
            </button>
          ))}

          <div className="relative">
            <button
              onClick={() => setShowCitiesDropdown(!showCitiesDropdown)}
              className="flex items-center justify-between gap-4 px-3 py-1.5 rounded-lg border border-black text-sm min-w-[120px]"
            >
              <span>{selectedCity || 'Ville'}</span>
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            {showCitiesDropdown && (
              <div className="absolute top-full mt-2 w-64 bg-white border border-gray-300 rounded shadow-lg z-10 max-h-96 overflow-y-auto">
                <button
                  onClick={() => { setSelectedCity(null); setShowCitiesDropdown(false) }}
                  className="w-full px-4 py-2 text-left hover:bg-gray-100 text-sm font-medium"
                >
                  Toutes les villes
                </button>
                <button
                  onClick={() => { setSelectedCity(city); setShowCitiesDropdown(false) }}
                  className="w-full px-4 py-2 text-left hover:bg-gray-100 text-sm"
                >
                  {city}
                </button>
                {nearbyCities.map((nearby) => (
                  <button
                    key={nearby.city}
                    onClick={() => { setSelectedCity(nearby.city); setShowCitiesDropdown(false) }}
                    className="w-full px-4 py-2 text-left hover:bg-gray-100 text-sm"
                  >
                    {nearby.city}
                  </button>
                ))}
              </div>
            )}
          </div>
        </div>

        {/* Stages List */}
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
          <>
            {visibleStages.map((stage) => (
                <article
                  key={stage.id}
                  className="flex w-[903px] h-[85px] p-[0_7px] items-center mb-3 rounded-[10px] border border-[#BBB] bg-white shadow-[0_4px_10px_0_rgba(0,0,0,0.15)] mx-auto"
                >
                  {/* Left: Date and Details Link */}
                  <div className="flex flex-col flex-shrink-0 gap-0 ml-3">
                    <p className="w-[223px] text-[rgba(0,0,0,0.89)] text-[15px] font-medium leading-[15px]" style={{ fontFamily: 'var(--font-poppins)' }}>
                      {formatDate(stage.date_start, stage.date_end)}
                    </p>
                    <button className="flex items-center gap-[5px] text-[rgba(90,106,147,0.86)] text-[13px] font-normal leading-[13px] hover:underline text-left mt-3" style={{ fontFamily: 'var(--font-poppins)' }}>
                      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none" className="w-[13.333px] h-[13.333px] flex-shrink-0">
                        <path d="M7.46665 10.1334V7.46672M7.46665 4.80005H7.47332M14.1333 7.46672C14.1333 11.1486 11.1486 14.1334 7.46665 14.1334C3.78476 14.1334 0.799988 11.1486 0.799988 7.46672C0.799988 3.78482 3.78476 0.800049 7.46665 0.800049C11.1486 0.800049 14.1333 3.78482 14.1333 7.46672Z" stroke="#5A6A93" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                      </svg>
                      <span>Détails du stage</span>
                    </button>
                  </div>

                  {/* Center: Location Pin + City + Address */}
                  <div className="flex items-center gap-2.5 flex-1 mx-[70px]">
                    <div className="flex w-[38px] h-[38px] p-[9px] justify-center items-center gap-2.5 flex-shrink-0 rounded-full bg-gray-200">
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" className="w-5 h-5 flex-shrink-0">
                        <g clipPath="url(#clip0_2180_399)">
                          <path d="M17.5 8.33337C17.5 14.1667 10 19.1667 10 19.1667C10 19.1667 2.5 14.1667 2.5 8.33337C2.5 6.34425 3.29018 4.4366 4.6967 3.03007C6.10322 1.62355 8.01088 0.833374 10 0.833374C11.9891 0.833374 13.8968 1.62355 15.3033 3.03007C16.7098 4.4366 17.5 6.34425 17.5 8.33337Z" stroke="#808080" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                          <path d="M10 10.8334C11.3807 10.8334 12.5 9.71409 12.5 8.33337C12.5 6.95266 11.3807 5.83337 10 5.83337C8.61929 5.83337 7.5 6.95266 7.5 8.33337C7.5 9.71409 8.61929 10.8334 10 10.8334Z" stroke="#808080" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                        </g>
                        <defs>
                          <clipPath id="clip0_2180_399">
                            <rect width="20" height="20" fill="white"/>
                          </clipPath>
                        </defs>
                      </svg>
                    </div>
                    <div className="flex flex-col justify-center gap-0">
                      <p className="w-[138px] flex-shrink-0 text-[rgba(0,0,0,0.98)] text-[15px] font-normal leading-[15px]" style={{ fontFamily: 'var(--font-poppins)' }}>{stage.site.ville}</p>
                      <p className="flex-shrink-0 self-stretch text-[rgba(6,6,6,0.56)] text-[12px] font-normal leading-[12px] mt-3" style={{ fontFamily: 'var(--font-poppins)' }}>{stage.site.adresse}</p>
                    </div>
                  </div>

                  {/* Right: Price */}
                  <div className="w-[121px] h-[31px] flex-shrink-0">
                    <p className="text-[rgba(6,6,6,0.86)] text-center text-[20px] font-normal leading-[35px]" style={{ fontFamily: 'var(--font-poppins)' }}>{stage.prix}€</p>
                  </div>

                  {/* Right: Green Button */}
                  <Link
                    href={`/stages-recuperation-points/${fullSlug}/${stage.id}/inscription`}
                    className="flex px-[15px] py-[7px] justify-center items-center gap-5 rounded-xl bg-[#41A334] text-white text-[11px] font-normal leading-normal tracking-[0.77px] hover:bg-[#389c2e] transition-colors whitespace-nowrap flex-shrink-0 ml-[70px] mr-[25px]"
                    style={{ fontFamily: 'var(--font-poppins)' }}
                  >
                    Sélectionner
                  </Link>
                </article>
            ))}

            <div className="flex items-center justify-center gap-4 mt-6">
              {visibleCount > STAGES_PER_LOAD && (
                <button
                  onClick={() => setVisibleCount(STAGES_PER_LOAD)}
                  className="px-8 py-2 bg-white border-2 border-[#c4cce1] text-gray-800 text-sm rounded-2xl hover:bg-gray-50 transition-colors"
                >
                  Afficher moins de stages
                </button>
              )}

              {hasMore && (
                <button
                  onClick={() => setVisibleCount(prev => prev + STAGES_PER_LOAD)}
                  className="px-8 py-2 bg-[#c4cce1] text-gray-800 text-sm rounded-2xl hover:bg-[#b3bdd4] transition-colors"
                >
                  Voir plus de stages
                </button>
              )}
            </div>
          </>
        )}

        {/* Pourquoi Réserver Section */}
        <section className="my-16 flex justify-center">
          <Image
            src="/pourquoi-reserver.png"
            alt="Pourquoi réserver votre stage chez ProStagesPermis"
            width={900}
            height={350}
            className="w-auto h-auto max-w-3xl"
          />
        </section>

        {/* Customer Reviews Section */}
        <section className="my-16">
          <div className="flex items-center justify-center gap-4 mb-8">
            <div className="h-px w-16 bg-gray-300" />
            <h2 className="text-center text-[20px] font-[250] leading-[35px]" style={{
              fontFamily: 'var(--font-poppins)',
              color: 'rgba(6, 6, 6, 0.86)',
              WebkitTextStrokeWidth: '1px',
              WebkitTextStrokeColor: '#000'
            }}>
              Avis <span style={{
                WebkitTextStrokeColor: 'rgba(201, 39, 39, 0.73)'
              }}>Clients</span>
            </h2>
            <div className="h-px w-16 bg-gray-300" />
          </div>

          <div className="bg-gray-100 rounded-lg p-12 flex items-center justify-center">
            <p className="text-gray-500">Section Avis Clients - Placeholder</p>
          </div>
        </section>

        {/* About Us Section */}
        <section className="my-16 flex flex-col items-center">
          {/* Top: Europe 1 Recommendation */}
          <div className="flex items-center justify-center mb-[120px]">
            {/* Left: Recommandé par Europe 1 */}
            <div className="flex flex-col items-center justify-center" style={{
              width: '176px',
              height: '77px',
              flexShrink: 0
            }}>
              <h3 className="text-center" style={{
                fontFamily: 'var(--font-poppins)',
                color: '#2C2C2C',
                fontSize: '20px',
                fontWeight: 500,
                lineHeight: '20px',
                letterSpacing: '1.4px'
              }}>
                Recommandé par Europe 1
              </h3>
            </div>

            {/* Subtitle with 35px gap */}
            <p className="text-center" style={{
              fontFamily: 'var(--font-poppins)',
              width: '333px',
              height: '56px',
              color: 'rgba(6, 6, 6, 0.80)',
              fontSize: '15px',
              fontWeight: 400,
              lineHeight: '25px',
              marginRight: '35px'
            }}>
              ProStagesPermis cité comme site de confiance par Europe 1
            </p>

            {/* Vertical Line */}
            <div style={{
              width: '1px',
              height: '172.502px',
              background: '#000',
              marginRight: '35px'
            }}></div>

            {/* Right: Europe 1 Logo */}
            <div className="flex flex-col items-center justify-center gap-4">
              <Image
                src="/europe1-logo.png"
                alt="Europe 1"
                width={200}
                height={100}
                className="w-auto h-20"
              />
              <a
                href="https://www.youtube.com/watch?v=z1AsmdcGTaw"
                target="_blank"
                rel="noopener noreferrer"
                className="text-red-700 hover:underline"
                style={{
                  fontFamily: 'var(--font-poppins)',
                  fontSize: '15px',
                  fontWeight: 400
                }}
              >
                Écouter l'extrait
              </a>
            </div>
          </div>

          {/* Bottom: Qui est ProStagesPermis */}
          <div className="flex justify-center items-center" style={{
            width: '626px',
            height: '358px',
            border: '1px solid #000'
          }}>
            <div className="text-center">
              <h2 className="text-center mb-4 text-[20px] font-[250] leading-[35px]" style={{
                fontFamily: 'var(--font-poppins)',
                color: 'rgba(6, 6, 6, 0.86)',
                WebkitTextStrokeWidth: '1px',
                WebkitTextStrokeColor: '#000'
              }}>
                Qui est <span style={{
                  WebkitTextStrokeColor: 'rgba(201, 39, 39, 0.73)'
                }}>ProStagesPermis</span>
              </h2>
              <div style={{
                width: '552px',
                height: '309px',
                flexShrink: 0,
                fontFamily: 'var(--font-poppins)',
                color: 'rgba(6, 6, 6, 0.94)',
                fontSize: '15px',
                fontWeight: 400,
                lineHeight: '25px',
                textAlign: 'left'
              }}>
                <p>
                  Depuis 2008, ProStagesPermis est le site n° 1 spécialisé dans les stages de récupération de points. Notre mission : vous aider à sauver votre permis dans les temps, avec un stage au meilleur prix proche de chez vous. Plus de 857 000 conducteurs nous ont déjà fait confiance.
                </p>
                <ul className="list-disc list-inside mt-4 space-y-1">
                  <li>Près de 18 ans d'expérience dans les stages de récupération de points</li>
                  <li>Des dizaines de milliers de conducteurs accompagnés partout en France</li>
                  <li>Un réseau de centres de formation agréés partout en France</li>
                  <li>Note Google 4,8/5 avis vérifiés</li>
                </ul>
              </div>
            </div>
          </div>
        </section>

        {/* FAQ Section */}
        <section className="my-16">
          <h2 className="text-center mb-8 text-[20px] font-[250] leading-[35px]" style={{
            fontFamily: 'var(--font-poppins)',
            color: 'rgba(6, 6, 6, 0.86)',
            WebkitTextStrokeWidth: '1px',
            WebkitTextStrokeColor: '#000'
          }}>
            Questions <span style={{
              WebkitTextStrokeColor: 'rgba(201, 39, 39, 0.73)'
            }}>Fréquentes</span>
          </h2>

          <div className="space-y-4">
            {faqData.map((faq, index) => (
              <article
                key={faq.id}
                className="rounded border border-black overflow-hidden"
              >
                <button
                  className="flex items-center justify-between p-4 w-full text-left hover:bg-gray-50 transition-colors"
                  onClick={() => setOpenFaqIndex(openFaqIndex === index ? null : index)}
                >
                  <div className="flex items-center gap-3 flex-1">
                    <svg className="w-6 h-6 text-gray-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p className="text-gray-900">{faq.question}</p>
                  </div>
                  <svg
                    className={`w-5 h-5 text-gray-600 transition-transform ${openFaqIndex === index ? 'rotate-180' : ''}`}
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                  </svg>
                </button>

                {openFaqIndex === index && (
                  <div className="px-4 pb-4 pt-2 bg-gray-50 border-t border-gray-200">
                    <p className="text-gray-700 leading-relaxed">
                      Ceci est un placeholder pour la réponse à la question. Le contenu sera ajouté ultérieurement.
                      Cette section peut contenir des informations détaillées sur la récupération de points,
                      les délais, les conditions et toutes les informations pertinentes pour répondre à la question posée.
                    </p>
                  </div>
                )}
              </article>
            ))}
          </div>
        </section>
      </main>

      {/* Footer */}
      <footer className="bg-[#343435] py-6 mt-32">
        <div className="max-w-7xl mx-auto px-4">
          <div className="flex items-center justify-center gap-6 mb-3">
            {[
              { text: 'Qui sommes-nous', href: '/qui-sommes-nous' },
              { text: 'Aide et contact', href: '/aide-et-contact' },
              { text: 'Conditions générales de vente', href: '/conditions-generales' },
              { text: 'Mentions légales', href: '/mentions-legales' },
              { text: 'Espace Client', href: '/espace-client' },
            ].map((link, index) => (
              <Link
                key={index}
                href={link.href}
                className="text-white text-xs hover:underline"
              >
                {link.text}
              </Link>
            ))}
          </div>
          <p className="text-center text-white text-xs">2025©ProStagesPermis</p>
        </div>
      </footer>
    </div>
  )
}
