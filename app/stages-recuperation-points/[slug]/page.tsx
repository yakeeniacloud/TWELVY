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
  const [visibleCount, setVisibleCount] = useState(6)
  const [openFaqIndex, setOpenFaqIndex] = useState<number | null>(null)

  const { menu } = useWordPressMenu()

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
    <div className="bg-[#fefefe] w-full min-w-[1468px] flex flex-col">
      {/* Header */}
      <header className="flex ml-[9px] w-[1459px] h-[38px] relative items-center justify-center gap-[1100px] px-0 py-[3px] bg-transparent">
        <Link href="/">
          <Image
            src="/prostagespermis-logo.png"
            alt="ProStagesPermis"
            width={138}
            height={29}
            className="w-[138px] h-[29.3px] relative object-cover"
          />
        </Link>

        <Link
          href="/espace-client"
          className="flex w-[169px] items-center justify-center px-4 py-1.5 relative mt-[-7.00px] mb-[-7.00px] cursor-pointer hover:opacity-80 transition-opacity"
        >
          <svg className="relative w-[25px] h-[25px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <span className="relative w-[93px] h-[34px] mt-[-1.00px] font-['Poppins'] font-normal text-[#0303038f] text-xs text-center tracking-[0] leading-[35px] whitespace-nowrap">
            Espace Client
          </span>
        </Link>
      </header>

      {/* Navigation */}
      <nav className="flex w-[1468px] h-[38px] relative items-center justify-center gap-[350px] bg-[#3d3d3d]">
        <ul className="inline-flex items-center justify-center gap-[5px] px-0 py-[3px] relative">
          {menu.slice(0, 3).map((item) => (
            <li key={item.id}>
              <Link
                href={`/${item.slug}`}
                className="relative h-8 mt-[-1.00px] font-['Poppins'] font-light text-[#fffcfc] text-sm text-center tracking-[0] leading-[35px] whitespace-nowrap block px-3"
              >
                {item.title}
              </Link>
            </li>
          ))}
        </ul>

        <ul className="inline-flex items-center justify-center gap-[5px] px-1.5 py-0.5 relative">
          <li>
            <Link
              href="/qui-sommes-nous"
              className="relative w-[164px] h-[34px] mt-[-1.00px] font-['Poppins'] font-light text-[#fffcfc] text-sm text-center tracking-[0] leading-[35px] whitespace-nowrap block"
            >
              Qui sommes-nous
            </Link>
          </li>
          <li>
            <Link
              href="/aide-et-contact"
              className="relative w-[154px] h-[34px] mt-[-1.00px] font-['Poppins'] font-light text-[#fffcfc] text-sm text-center tracking-[0] leading-[35px] whitespace-nowrap block"
            >
              Aide et contact
            </Link>
          </li>
        </ul>
      </nav>

      {/* Main Content */}
      <main className="flex flex-col items-center">
        <h1 className="ml-2 h-[35px] w-[560px] self-center mt-11 font-['Poppins'] font-normal text-black text-[25px] text-center tracking-[0] leading-[35px]">
          Stage Récupération de Points à {city.charAt(0) + city.slice(1).toLowerCase()}
        </h1>

        <p className="h-[34px] w-[582px] self-center mt-[9px] font-['Poppins'] font-normal text-[#060505db] text-[15px] text-center tracking-[0] leading-7">
          Réservez votre stage agréé en quelques clics et récupérez 4 points en 2 jours
        </p>

        <Image
          src="/pourquoi-reserver.png"
          alt="Reassurance badges"
          width={973}
          height={61}
          className="ml-[274px] w-[973px] h-[61px] relative mt-[22px]"
        />

        <div className="flex ml-[458px] w-[557px] h-[37px] relative mt-[27px] items-center justify-center gap-[15px] px-[26px] py-[5px] bg-[#c4cce1] rounded-xl overflow-hidden">
          <div className="w-7 h-[19px] ml-[-7.00px] bg-blue-600 rounded flex items-center justify-center">
            <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
            </svg>
          </div>

          <p className="relative w-fit mr-[-7.00px] font-['Poppins'] font-normal text-[#2c2c2c] text-sm tracking-[0.98px] leading-[normal]">
            Stages Agréés par la Préfecture des Bouches-du-Rhône (13)
          </p>
        </div>

        {/* Filters Section */}
        <div className="flex ml-[293px] w-[722px] h-[71px] relative mt-[27px] items-center justify-center gap-[35px] px-[26px] py-[7px]">
          <div className="inline-flex min-w-[120px] items-center gap-2 pt-3 pr-4 pb-3 pl-4 relative ml-[-18.00px] bg-white rounded-lg overflow-hidden border border-solid border-[#d9d9d9]">
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Ville ou code postal"
              className="relative flex-1 mt-[-0.50px] font-['Poppins'] font-normal text-[#b3b3b3] text-sm bg-transparent border-none outline-none placeholder:text-[#b3b3b3]"
            />
            <svg className="relative w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>

          <div className="flex w-[286px] items-center justify-center gap-2 px-6 py-[11px] relative">
            <div className="relative flex items-center justify-center w-[76px] h-[35px] mt-[-1.00px] ml-[-17.50px] font-['Poppins'] font-normal text-black text-[13px] tracking-[0.91px] leading-[normal]">
              Trier par :
            </div>

            {(['date', 'prix', 'proximite'] as const).map((option) => (
              <button
                key={option}
                onClick={() => setSortBy(sortBy === option ? null : option)}
                className={`flex flex-col items-center justify-center gap-2.5 px-[30px] py-0.5 relative rounded-lg border border-solid border-[#7b7b7b] h-[35px] ${
                  sortBy === option ? 'bg-[#c4cce1]' : 'bg-white'
                }`}
              >
                <span className="w-fit text-[#030303f5] tracking-[0.84px] leading-[normal] font-['Poppins'] font-normal text-xs">
                  {option === 'date' ? 'Date' : option === 'prix' ? 'Prix' : 'Proximité'}
                </span>
              </button>
            ))}
          </div>

          <div className="relative">
            <button
              onClick={() => setShowCitiesDropdown(!showCitiesDropdown)}
              className="flex w-36 items-center justify-between px-[11px] py-0 h-[35px] rounded-[10px] overflow-hidden border border-solid border-black"
            >
              <span className="w-[52px] h-[33px] mt-[-1.00px] text-[#060505] tracking-[0] leading-[35px] whitespace-nowrap font-['Poppins'] font-normal text-xs">
                {selectedCity || 'Ville'}
              </span>
              <svg className="w-[25px] h-[25px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            {visibleStages.map((stage, index) => {
              const isFirst = index === 0
              const isCheapest = cheapestStage?.id === stage.id

              return (
                <article
                  key={stage.id}
                  className={`flex ml-[301px] w-[903px] h-[85px] relative ${index === 0 ? 'mt-0' : 'mt-[11px]'} items-center gap-[70px] px-[7px] py-0 ${
                    isCheapest ? 'bg-[#e4484814]' : 'bg-white'
                  } rounded-[10px] overflow-hidden border border-solid border-[#bbbbbb] shadow-[0px_4px_10px_#00000026]`}
                >
                  <div className="inline-flex flex-col h-[85px] items-start justify-center px-[5px] py-0 relative flex-[0_0_auto]">
                    <p className="relative w-[223px] h-[25px] font-['Poppins'] font-medium text-[#000000e3] text-[15px] tracking-[0] leading-[35px] whitespace-nowrap">
                      {formatDate(stage.date_start, stage.date_end)}
                    </p>

                    <button className="inline-flex items-center justify-center gap-[5px] px-0 py-[3px] relative flex-[0_0_auto] bg-transparent border-0 cursor-pointer">
                      <svg className="relative w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd"/>
                      </svg>
                      <span className="relative w-[120px] h-[34px] mt-[-1.00px] font-['Poppins'] font-normal text-[#596a93db] text-[13px] tracking-[0] leading-[35px] whitespace-nowrap">
                        Détails du stage
                      </span>
                    </button>
                  </div>

                  <div className="flex w-[200px] items-center px-0 py-[5px] relative overflow-hidden">
                    <div className="flex w-[38px] h-[38px] items-center justify-center gap-2.5 p-[15px] relative bg-[#e6e6e6] rounded-[150px] overflow-hidden">
                      <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clipRule="evenodd"/>
                      </svg>
                    </div>

                    <div className="flex flex-col w-[168px] h-[59px] items-start px-2.5 py-0 relative">
                      <div className="relative w-[138px] h-[34px] mt-[-1.00px] font-['Poppins'] font-normal text-[#000000fa] text-[15px] tracking-[0] leading-[35px] whitespace-nowrap">
                        {stage.site.ville}
                      </div>
                      <div className="relative self-stretch h-[31px] -mt-3 font-['Poppins'] font-normal text-[#0605058f] text-xs tracking-[0] leading-[35px] whitespace-nowrap">
                        {stage.site.adresse}
                      </div>
                    </div>
                  </div>

                  {isCheapest ? (
                    <div className="flex flex-col w-[173px] items-center px-0 py-2 relative">
                      <div className="relative self-stretch h-[31px] mt-[-1.00px] font-['Poppins'] font-normal text-[#060505db] text-xl text-center tracking-[0] leading-[35px] whitespace-nowrap">
                        {stage.prix}€
                      </div>
                      <p className="relative self-stretch font-['Poppins'] font-medium text-[#bc4747] text-xs text-center tracking-[0.84px] leading-[normal]">
                        Notre prix bas à {city.charAt(0) + city.slice(1).toLowerCase()}
                      </p>
                    </div>
                  ) : (
                    <div className="relative w-[121px] h-[31px] font-['Poppins'] font-normal text-[#060505db] text-xl text-center tracking-[0] leading-[35px] whitespace-nowrap">
                      {stage.prix}€
                    </div>
                  )}

                  <Link
                    href={`/stages-recuperation-points/${fullSlug}/${stage.id}`}
                    className="inline-flex items-center justify-center gap-5 px-[15px] py-[7px] relative flex-[0_0_auto] bg-[#40a333] rounded-xl overflow-hidden border-0 cursor-pointer hover:bg-[#368c2b] transition-colors"
                  >
                    <span className="relative w-fit mt-[-1.00px] font-['Poppins'] font-normal text-white text-[11px] tracking-[0.77px] leading-[normal]">
                      Sélectionner
                    </span>
                  </Link>
                </article>
              )
            })}

            {hasMore && (
              <button
                onClick={() => setVisibleCount(prev => prev + STAGES_PER_LOAD)}
                className="inline-flex ml-[664px] w-[171px] h-[37px] relative mt-[33px] items-center justify-center gap-2.5 px-[26px] py-[5px] bg-[#c4cce1] rounded-[15px] overflow-hidden cursor-pointer hover:bg-[#b3bdd4] transition-colors"
              >
                <span className="relative w-fit font-['Poppins'] font-normal text-[#2c2c2c] text-[11px] tracking-[0.77px] leading-[normal]">
                  Voir plus de stages
                </span>
              </button>
            )}
          </>
        )}

        {/* Pourquoi Réserver Section */}
        <section className="flex ml-[349px] w-[770px] h-[241px] relative mt-20 items-center gap-[33px] px-[42px] py-[17px] bg-[#e8e3e3] overflow-hidden">
          <div className="flex flex-col w-[323px] items-center gap-[5px] px-8 py-7 relative">
            <p className="relative flex items-center justify-center self-stretch h-[69.23px] mt-[-2.00px] ml-[-1.00px] font-['Poppins'] font-thin text-[#060505] text-xl text-center tracking-[0] leading-[35px]">
              Pourquoi réserver votre stage chez
            </p>
            <h2 className="relative flex items-center justify-center self-stretch h-[42.31px] ml-[-1.00px] font-['Poppins'] font-thin text-[#060505db] text-xl text-center tracking-[0] leading-[35px]">
              <span className="text-[#bc4747ba]">ProStagesPermis</span>
            </h2>
          </div>

          <ul className="flex flex-col w-[383px] items-start px-0 py-5 relative mt-[-2.79px] mb-[-2.79px] mr-[-42.00px]">
            {[
              'Paiement 100%  sécurisé',
              'Inscription en quelques clics',
              'Meilleur prix garanti',
              'Attestation de stage remise le 2ème jour'
            ].map((text, index) => (
              <li key={index} className="flex items-start gap-2.5 px-2.5 py-[3px] relative">
                <div className="relative w-[34.15px] h-[33.65px]">
                  <svg className="absolute w-[79.17%] h-[75.00%] top-[10.12%] left-[10.16%]" fill="#40a333" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd"/>
                  </svg>
                </div>
                <p className="font-['Poppins'] font-normal text-[#060505db] text-[15px] tracking-[0] leading-[35px]">
                  {text}
                </p>
              </li>
            ))}
          </ul>
        </section>

        {/* Customer Reviews Section */}
        <section className="flex ml-[194px] w-[1106px] h-[317px] relative mt-[109px] flex-col items-center gap-2.5 px-[18px] py-0">
          <div className="flex w-[337px] items-center justify-center gap-2.5 px-[152px] py-0 relative flex-[0_0_auto]">
            <div className="ml-[-103.00px] relative w-[53px] h-px bg-gray-400" />
            <h2 className="relative flex items-center justify-center w-fit ml-[-41.00px] font-['Poppins'] font-thin text-[#060505db] text-xl text-center tracking-[0] leading-[35px] whitespace-nowrap">
              Avis
            </h2>
            <div className="relative flex items-center justify-center w-[67px] h-11 mt-[-2.00px] mr-[-39.00px] font-['Poppins'] font-thin text-xl tracking-[0] leading-[35px]">
              <span className="text-[#c92727ba]">Clients</span>
            </div>
            <div className="mr-[-103.00px] relative w-[53px] h-px bg-gray-400" />
          </div>

          <div className="relative w-[1070px] h-[263px] bg-gray-100 rounded-lg flex items-center justify-center">
            <p className="text-gray-500 text-sm">Carousel Avis - Placeholder</p>
          </div>
        </section>

        {/* About Us Section */}
        <section className="inline-flex ml-[262px] w-[944px] h-[423px] relative mt-[88px] items-center">
          <div className="flex flex-col w-[605px] h-[423px] items-center justify-center gap-2.5 px-2.5 py-[21px] relative">
            <header className="flex h-[58px] items-center justify-center pl-[59px] pr-[38px] py-[5px] relative self-stretch w-full">
              <h2 className="relative flex items-center justify-center w-[85px] h-12 mt-[-2.00px] font-['Poppins'] font-thin text-[#060505db] text-xl text-center tracking-[0] leading-[35px]">
                Qui est
              </h2>
              <h2 className="flex items-center justify-center w-[167px] h-11 font-['Poppins'] font-extralight text-xl leading-[35px] relative text-center tracking-[0]">
                <span className="text-[#bc4747ba]">ProStagesPermis</span>
              </h2>
            </header>

            <div className="relative w-[552px] h-[309px] font-['Poppins'] font-normal text-[#060505f0] text-[15px] tracking-[0] leading-[25px]">
              <p className="mb-0">
                Depuis 2008, ProStagesPermis est le site n° 1 spécialisé dans les stages de récupération de points. Notre mission : vous aider à sauver votre permis dans les temps, avec un stage au meilleur prix proche de chez vous. Plus de 857 000 conducteurs nous ont déjà fait confiance.
              </p>
              <br />
              Près de 18 ans d'expérience dans les stages de récupération de points<br />
              Des dizaines de milliers de conducteurs accompagnés partout en France<br />
              Un réseau de centres de formation agréés partout en France<br />
              Note Google 4,8/5 avis vérifiés
            </div>
          </div>

          <div className="flex w-[35px] h-[286px] items-center justify-center gap-2.5 px-6 py-[26px] relative">
            <div className="relative w-px h-[249.0px] mt-[-7.50px] mb-[-7.50px] ml-[-6.50px] mr-[-7.50px] bg-gray-300" />
          </div>

          <aside className="flex flex-col w-[304px] items-center gap-2 px-0 py-[38px] relative">
            <div className="w-[109px] h-[67px] bg-blue-600 rounded flex items-center justify-center text-white font-bold">
              Europe 1
            </div>
            <p className="self-stretch h-14 font-['Poppins'] font-normal text-[#060505cc] text-[15px] leading-[25px] relative text-center tracking-[0]">
              ProStagesPermis cité comme site de confiance par Europe 1
            </p>
            <a href="#" className="relative self-stretch h-[22px] font-['Poppins'] font-normal text-[#770c0ccc] text-[15px] text-center tracking-[0] leading-[25px] whitespace-nowrap hover:underline">
              Écouter l'extrait
            </a>
          </aside>
        </section>

        {/* FAQ Section */}
        <section className="flex ml-[269px] w-[930px] h-[376px] relative mt-[88px] flex-col items-center justify-center gap-[25px] px-[5px] py-2.5">
          <header className="inline-flex items-center justify-center px-[103px] py-0 relative flex-[0_0_auto]">
            <h2 className="relative flex items-center justify-center w-[108px] h-[42px] mt-[-1.00px] ml-[-1.00px] font-['Poppins'] font-thin text-[#060505db] text-xl text-center tracking-[0] leading-[35px] whitespace-nowrap">
              Questions
            </h2>
            <span className="relative flex items-center justify-center w-[116px] h-11 mt-[-2.00px] -ml-0.5 font-['Poppins'] font-extralight text-xl text-center tracking-[0] leading-[35px]">
              <span className="text-[#bc4747ba]">Fréquentes</span>
            </span>
          </header>

          {faqData.map((faq, index) => (
            <article
              key={faq.id}
              className={`${index === 0 ? 'flex w-[914px]' : 'inline-flex'} items-center gap-[202px] px-[11px] py-1.5 relative flex-[0_0_auto] rounded-[5px] overflow-hidden border border-solid border-black`}
            >
              <button
                className="inline-flex items-center justify-center gap-2 px-0 py-[3px] relative flex-[0_0_auto] bg-transparent border-0 cursor-pointer text-left w-full"
                onClick={() => setOpenFaqIndex(openFaqIndex === index ? null : index)}
              >
                <svg className="relative w-[27px] h-[27px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p className="relative w-[626px] mt-[-1.00px] font-['Poppins'] font-normal text-[#060505] text-[15px] tracking-[0] leading-[35px]">
                  {faq.question}
                </p>
              </button>
              <svg className="relative w-[25px] h-[25px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
              </svg>
            </article>
          ))}
        </section>
      </main>

      {/* Footer */}
      <footer className="w-[1468px] h-[68px] relative mt-[599px] bg-[#343435]">
        <nav className="inline-flex items-center gap-[11px] px-[61px] py-[5px] absolute top-[7px] left-[327px]">
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
              className="relative h-[34px] mt-[-1.00px] font-['Poppins'] font-light text-white text-xs text-center tracking-[0] leading-[35px] whitespace-nowrap hover:underline"
            >
              {link.text}
            </Link>
          ))}
        </nav>

        <div className="absolute top-3 left-[calc(50.00%_-_707px)] w-[154px] font-['Poppins'] font-light text-white text-xs text-center tracking-[0] leading-[35px] whitespace-nowrap">
          2025©ProStagesPermis
        </div>
      </footer>
    </div>
  )
}
