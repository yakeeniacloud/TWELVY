'use client'

import { useState, useEffect, useRef } from 'react'
import { useParams } from 'next/navigation'
import Link from 'next/link'
import Image from 'next/image'
import StageDetailsModal from '@/components/stages/StageDetailsModal'
import { removeStreetNumber } from '@/lib/formatAddress'
import { useCities } from '@/hooks/useCities'
import { CITY_POSTAL_MAP } from '@/lib/city-postal-map'

// French department names map
const DEPARTMENT_NAMES: { [key: string]: string } = {
  '01': 'Ain', '02': 'Aisne', '03': 'Allier', '04': 'Alpes-de-Haute-Provence',
  '05': 'Hautes-Alpes', '06': 'Alpes-Maritimes', '07': 'Ardèche', '08': 'Ardennes',
  '09': 'Ariège', '10': 'Aube', '11': 'Aude', '12': 'Aveyron',
  '13': 'Bouches-du-Rhône', '14': 'Calvados', '15': 'Cantal', '16': 'Charente',
  '17': 'Charente-Maritime', '18': 'Cher', '19': 'Corrèze', '2A': 'Corse-du-Sud',
  '2B': 'Haute-Corse', '21': 'Côte-d\'Or', '22': 'Côtes-d\'Armor', '23': 'Creuse',
  '24': 'Dordogne', '25': 'Doubs', '26': 'Drôme', '27': 'Eure',
  '28': 'Eure-et-Loir', '29': 'Finistère', '30': 'Gard', '31': 'Haute-Garonne',
  '32': 'Gers', '33': 'Gironde', '34': 'Hérault', '35': 'Ille-et-Vilaine',
  '36': 'Indre', '37': 'Indre-et-Loire', '38': 'Isère', '39': 'Jura',
  '40': 'Landes', '41': 'Loir-et-Cher', '42': 'Loire', '43': 'Haute-Loire',
  '44': 'Loire-Atlantique', '45': 'Loiret', '46': 'Lot', '47': 'Lot-et-Garonne',
  '48': 'Lozère', '49': 'Maine-et-Loire', '50': 'Manche', '51': 'Marne',
  '52': 'Haute-Marne', '53': 'Mayenne', '54': 'Meurthe-et-Moselle', '55': 'Meuse',
  '56': 'Morbihan', '57': 'Moselle', '58': 'Nièvre', '59': 'Nord',
  '60': 'Oise', '61': 'Orne', '62': 'Pas-de-Calais', '63': 'Puy-de-Dôme',
  '64': 'Pyrénées-Atlantiques', '65': 'Hautes-Pyrénées', '66': 'Pyrénées-Orientales',
  '67': 'Bas-Rhin', '68': 'Haut-Rhin', '69': 'Rhône', '70': 'Haute-Saône',
  '71': 'Saône-et-Loire', '72': 'Sarthe', '73': 'Savoie', '74': 'Haute-Savoie',
  '75': 'Paris', '76': 'Seine-Maritime', '77': 'Seine-et-Marne', '78': 'Yvelines',
  '79': 'Deux-Sèvres', '80': 'Somme', '81': 'Tarn', '82': 'Tarn-et-Garonne',
  '83': 'Var', '84': 'Vaucluse', '85': 'Vendée', '86': 'Vienne',
  '87': 'Haute-Vienne', '88': 'Vosges', '89': 'Yonne', '90': 'Territoire de Belfort',
  '91': 'Essonne', '92': 'Hauts-de-Seine', '93': 'Seine-Saint-Denis',
  '94': 'Val-de-Marne', '95': 'Val-d\'Oise', '971': 'Guadeloupe', '972': 'Martinique',
  '973': 'Guyane', '974': 'La Réunion', '976': 'Mayotte'
}

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
  // Extract postal code from slug (e.g., "NICE-06000" -> "06000")
  const slugPostalCode = lastHyphenIndex > 0 ? fullSlug.substring(lastHyphenIndex + 1) : ''
  // Get department code from slug postal code (first 2 digits)
  const cityDeptCode = slugPostalCode.substring(0, 2) || '13'
  const cityDeptName = DEPARTMENT_NAMES[cityDeptCode] || 'France'

  const [stages, setStages] = useState<Stage[]>([])
  const [allStages, setAllStages] = useState<Stage[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [sortBy, setSortBy] = useState<'date' | 'prix' | 'proximite' | null>('date')
  const [nearbyCities, setNearbyCities] = useState<{ city: string; distance: number }[]>([])
  const [showCitiesDropdown, setShowCitiesDropdown] = useState(false)
  const [selectedCities, setSelectedCities] = useState<string[]>([])
  const [allCitiesSelected, setAllCitiesSelected] = useState(true)
  const [visibleCount, setVisibleCount] = useState(6)
  const [openFaqIndex, setOpenFaqIndex] = useState<number | null>(null)
  const [currentReviewIndex, setCurrentReviewIndex] = useState(0)
  const [selectedStage, setSelectedStage] = useState<Stage | null>(null)
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [showMobileMenu, setShowMobileMenu] = useState(false)
  const [isScrolled, setIsScrolled] = useState(false)
  const [isStagesVisible, setIsStagesVisible] = useState(false)
  const [showReassuranceModal, setShowReassuranceModal] = useState(false)

  // Desktop search bar state
  const [desktopSearchQuery, setDesktopSearchQuery] = useState('')
  const [showDesktopSuggestions, setShowDesktopSuggestions] = useState(false)
  const [desktopSelectedIndex, setDesktopSelectedIndex] = useState(-1)
  const { cities: allCities } = useCities()
  const desktopSearchRef = useRef<HTMLInputElement>(null)
  const desktopSuggestionsRef = useRef<HTMLDivElement>(null)

  // Mobile search bar state
  const [mobileSearchQuery, setMobileSearchQuery] = useState('')
  const [showMobileSuggestions, setShowMobileSuggestions] = useState(false)
  const [mobileSelectedIndex, setMobileSelectedIndex] = useState(-1)
  const mobileSearchRef = useRef<HTMLInputElement>(null)
  const mobileSuggestionsRef = useRef<HTMLDivElement>(null)

  const STAGES_PER_LOAD = 6

  const cityDropdownRef = useRef<HTMLDivElement>(null)
  const stagesSectionRef = useRef<HTMLDivElement>(null)

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

    if (!allCitiesSelected && selectedCities.length > 0) {
      filtered = filtered.filter(s => selectedCities.includes(s.site.ville))
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
  }, [sortBy, selectedCities, allCitiesSelected, allStages, nearbyCities])

  // Click-outside handler to close city dropdown
  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (cityDropdownRef.current && !cityDropdownRef.current.contains(event.target as Node)) {
        setShowCitiesDropdown(false)
      }
    }

    if (showCitiesDropdown) {
      document.addEventListener('mousedown', handleClickOutside)
      return () => {
        document.removeEventListener('mousedown', handleClickOutside)
      }
    }
  }, [showCitiesDropdown])

  // Scroll detection for sticky header behavior
  useEffect(() => {
    const handleScroll = () => {
      // Show compact header after scrolling 100px
      setIsScrolled(window.scrollY > 100)
    }

    window.addEventListener('scroll', handleScroll, { passive: true })
    return () => window.removeEventListener('scroll', handleScroll)
  }, [])

  // Close desktop search suggestions when clicking outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (
        desktopSuggestionsRef.current &&
        !desktopSuggestionsRef.current.contains(event.target as Node) &&
        desktopSearchRef.current &&
        !desktopSearchRef.current.contains(event.target as Node)
      ) {
        setShowDesktopSuggestions(false)
      }
    }

    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [])

  // Close mobile search suggestions when clicking outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (
        mobileSuggestionsRef.current &&
        !mobileSuggestionsRef.current.contains(event.target as Node) &&
        mobileSearchRef.current &&
        !mobileSearchRef.current.contains(event.target as Node)
      ) {
        setShowMobileSuggestions(false)
      }
    }

    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [])

  // Intersection Observer for stages visibility (for sticky footer)
  useEffect(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          setIsStagesVisible(entry.isIntersecting)
        })
      },
      { threshold: 0.1 }
    )

    if (stagesSectionRef.current) {
      observer.observe(stagesSectionRef.current)
    }

    return () => observer.disconnect()
  }, [])

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

  const formatCityName = (cityName: string) => {
    return cityName
      .split('-')
      .map(word => word.charAt(0) + word.slice(1).toLowerCase())
      .join(' ')
  }

  const handleCityToggle = (cityName: string) => {
    // If "all cities" is selected, deselect it and select only this city
    if (allCitiesSelected) {
      setAllCitiesSelected(false)
      setSelectedCities([cityName])
      return
    }

    if (selectedCities.includes(cityName)) {
      const newSelected = selectedCities.filter(c => c !== cityName)
      setSelectedCities(newSelected)
      if (newSelected.length === 0) {
        setAllCitiesSelected(true)
      }
    } else {
      setSelectedCities([...selectedCities, cityName])
    }
  }

  const handleAllCitiesToggle = () => {
    if (allCitiesSelected) {
      setAllCitiesSelected(false)
      setSelectedCities([])
    } else {
      setAllCitiesSelected(true)
      setSelectedCities([])
    }
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
      {/* Mobile Header - Only shown on mobile */}
      <header className="md:hidden bg-white border-b border-gray-200 sticky top-0 z-50">
        {/* Top row: Logo and Hamburger - hidden when scrolled */}
        {!isScrolled && (
          <div className="flex items-center justify-between px-4 pt-3 pb-[7px]">
            {/* Logo */}
            <Link href="/">
              <img
                src="/prostagespermis-logo.png"
                alt="ProStagesPermis"
                className="h-8 w-auto"
              />
            </Link>

            {/* Hamburger Menu */}
            <button
              onClick={() => setShowMobileMenu(!showMobileMenu)}
              className="flex flex-col gap-1 p-2"
              aria-label="Toggle menu"
            >
              <span className="w-6 h-0.5 bg-black"></span>
              <span className="w-6 h-0.5 bg-black"></span>
              <span className="w-6 h-0.5 bg-black"></span>
            </button>
          </div>
        )}

        {/* Search bar - full width rounded when scrolled - with autocomplete */}
        <div className={`px-4 ${isScrolled ? 'py-2' : 'pb-1'} relative`}>
          <div
            className="flex items-center gap-2 mx-auto"
            style={{
              width: isScrolled ? '100%' : 'auto',
              maxWidth: isScrolled ? '400px' : 'none',
              height: '40px',
              padding: '8px 16px',
              borderRadius: '20px',
              border: '1px solid #D9D9D9',
              background: '#F5F5F5'
            }}
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 16 16" fill="none" className="flex-shrink-0">
              <path d="M14 14L11.1 11.1M12.6667 7.33333C12.6667 10.2789 10.2789 12.6667 7.33333 12.6667C4.38781 12.6667 2 10.2789 2 7.33333C2 4.38781 4.38781 2 7.33333 2C10.2789 2 12.6667 4.38781 12.6667 7.33333Z" stroke="#808080" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
            <input
              ref={mobileSearchRef}
              type="text"
              value={mobileSearchQuery}
              onChange={(e) => {
                setMobileSearchQuery(e.target.value)
                setShowMobileSuggestions(true)
                setMobileSelectedIndex(-1)
              }}
              onFocus={() => setShowMobileSuggestions(true)}
              onKeyDown={(e) => {
                const filteredCities = mobileSearchQuery.length > 0
                  ? allCities.filter(c => c.toLowerCase().startsWith(mobileSearchQuery.toLowerCase())).slice(0, 8)
                  : []
                if (e.key === 'Enter') {
                  e.preventDefault()
                  const cityToSearch = mobileSelectedIndex >= 0 && mobileSelectedIndex < filteredCities.length
                    ? filteredCities[mobileSelectedIndex]
                    : mobileSearchQuery
                  if (cityToSearch.trim()) {
                    const cityUpper = cityToSearch.toUpperCase()
                    const postal = CITY_POSTAL_MAP[cityUpper] || '00000'
                    window.location.href = `/stages-recuperation-points/${cityUpper}-${postal}`
                  }
                } else if (e.key === 'ArrowDown') {
                  e.preventDefault()
                  setMobileSelectedIndex(prev => prev < filteredCities.length - 1 ? prev + 1 : prev)
                } else if (e.key === 'ArrowUp') {
                  e.preventDefault()
                  setMobileSelectedIndex(prev => prev > 0 ? prev - 1 : -1)
                } else if (e.key === 'Escape') {
                  setShowMobileSuggestions(false)
                  setMobileSelectedIndex(-1)
                }
              }}
              placeholder="Ville ou code postal"
              className="flex-1 bg-transparent border-none outline-none text-sm placeholder:text-gray-400"
              style={{ fontFamily: 'var(--font-poppins)' }}
            />
          </div>
          {/* Mobile Suggestions dropdown */}
          {showMobileSuggestions && mobileSearchQuery.length > 0 && (() => {
            const filteredCities = allCities
              .filter(c => c.toLowerCase().startsWith(mobileSearchQuery.toLowerCase()))
              .slice(0, 8)
            if (filteredCities.length === 0) return null
            return (
              <div
                ref={mobileSuggestionsRef}
                className="absolute left-4 right-4 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50"
                style={{ maxHeight: '250px', overflowY: 'auto' }}
              >
                {filteredCities.map((cityName, index) => {
                  const deptCode = CITY_POSTAL_MAP[cityName.toUpperCase()]?.substring(0, 2) || ''
                  const displayName = cityName.split('-').map((word, i) => {
                    const lower = word.toLowerCase()
                    if (i > 0 && ['en', 'de', 'du', 'la', 'le', 'les', 'sur', 'sous'].includes(lower)) return lower
                    return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
                  }).join('-')
                  return (
                    <button
                      key={cityName}
                      onClick={() => {
                        const cityUpper = cityName.toUpperCase()
                        const postal = CITY_POSTAL_MAP[cityUpper] || '00000'
                        window.location.href = `/stages-recuperation-points/${cityUpper}-${postal}`
                      }}
                      className={`w-full text-left px-4 py-3 text-sm transition-colors ${
                        index === mobileSelectedIndex ? 'bg-blue-100 text-blue-900' : 'text-gray-700 hover:bg-gray-100'
                      }`}
                      style={{ fontFamily: 'var(--font-poppins)' }}
                    >
                      {displayName}{deptCode ? ` (${deptCode})` : ''}
                    </button>
                  )
                })}
              </div>
            )
          })()}
        </div>
      </header>

      {/* Mobile Side Menu - Slides in from right */}
      {showMobileMenu && (
        <>
          {/* Overlay */}
          <div
            className="fixed inset-0 bg-black bg-opacity-50 z-50 md:hidden"
            onClick={() => setShowMobileMenu(false)}
          />

          {/* Side Menu */}
          <div className="fixed top-0 right-0 h-full w-64 bg-[#3d3d3d] z-50 md:hidden shadow-lg">
            {/* Close button */}
            <button
              onClick={() => setShowMobileMenu(false)}
              className="absolute top-4 right-4 text-white p-2"
              aria-label="Close menu"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>

            {/* Menu items */}
            <nav className="pt-16 px-6">
              <ul className="flex flex-col gap-4">
                <li>
                  <Link
                    href="/services"
                    className="text-white text-sm hover:text-gray-200 block py-2"
                    onClick={() => setShowMobileMenu(false)}
                  >
                    Services
                  </Link>
                </li>
                <li>
                  <Link
                    href="/retrait-de-points"
                    className="text-white text-sm hover:text-gray-200 block py-2"
                    onClick={() => setShowMobileMenu(false)}
                  >
                    Le retrait de points
                  </Link>
                </li>
                <li>
                  <Link
                    href="/stages"
                    className="text-white text-sm hover:text-gray-200 block py-2"
                    onClick={() => setShowMobileMenu(false)}
                  >
                    Les stages
                  </Link>
                </li>
                <li className="pt-4 border-t border-gray-600">
                  <Link
                    href="/espace-client"
                    className="text-white text-sm hover:text-gray-200 flex items-center gap-2 py-2"
                    onClick={() => setShowMobileMenu(false)}
                  >
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Espace Client
                  </Link>
                </li>
              </ul>
            </nav>
          </div>
        </>
      )}

      {/* Main Content */}
      <main className="max-w-5xl mx-auto px-4 md:px-4 pb-4 pt-[5px] md:py-8">
        <h1 className="text-lg md:text-2xl font-normal text-center" style={{ marginBottom: '4px' }}>
          Stage Récupération de Points à {city.charAt(0) + city.slice(1).toLowerCase()}
        </h1>

        {/* Mobile subtitle */}
        <div className="md:hidden flex flex-col items-center gap-4 mb-4 px-2">
          <p style={{
            width: '379px',
            height: '27px',
            flexShrink: 0,
            color: 'rgba(6, 6, 6, 0.86)',
            textAlign: 'center',
            fontFamily: 'var(--font-poppins)',
            fontSize: '15px',
            fontStyle: 'normal',
            fontWeight: 300,
            lineHeight: '22px'
          }}>
            Récupérez 4 points en 48h au meilleur prix
          </p>
          <p style={{
            width: '376px',
            height: '23px',
            color: '#000',
            textAlign: 'center',
            fontFamily: 'var(--font-poppins)',
            fontSize: '12px',
            fontStyle: 'italic',
            fontWeight: 400,
            lineHeight: 'normal',
            letterSpacing: '0.6px'
          }}>
            Plus de 857 000 conducteurs accompagnés depuis 2008
          </p>
        </div>

        {/* Desktop subtitle */}
        <p className="hidden md:block text-center text-base text-gray-700 mb-6 px-2">
          Réservez votre stage agréé en quelques clics et récupérez 4 points en 2 jours
        </p>

        {/* Reassurance Icons - REMOVED from desktop as per redesign */}

        {/* Prefecture Badge */}
        <div className="flex items-center justify-center mb-4 md:mb-6">
          {/* Mobile: Custom badge with flag and text */}
          <div className="md:hidden flex" style={{
            padding: '5px 14px',
            justifyContent: 'center',
            alignItems: 'center',
            gap: '5px',
            borderRadius: '12px',
            background: 'rgba(219, 206, 157, 0.69)'
          }}>
            {/* French Flag */}
            <img
              src="/flag.png"
              alt="Drapeau français"
              style={{
                width: '16px',
                height: '11px',
                flexShrink: 0,
                aspectRatio: '16/11',
                borderRadius: '10px',
                objectFit: 'cover'
              }}
            />
            {/* Text - Dynamic based on first stage's postal code */}
            <span style={{
              flexShrink: 0,
              color: '#2C2C2C',
              textAlign: 'center',
              fontFamily: 'var(--font-poppins)',
              fontSize: '14px',
              fontStyle: 'normal',
              fontWeight: 400,
              lineHeight: 'normal',
              letterSpacing: '0.7px'
            }}>
              Stages agréés Préfecture {stages[0]?.site.code_postal?.substring(0, 2) || '13'}
            </span>
          </div>

          {/* Desktop: Dynamic prefecture badge based on city's department from URL slug */}
          {(() => {
            // Use cityDeptCode/cityDeptName from URL slug (not stages[0] which changes with filtering)
            // French preposition: "d'" before vowels, "de " otherwise
            const getPreposition = (name: string) => {
              if (/^[AEIOUYH]/i.test(name)) return "d'"
              return 'de '
            }
            return (
              <div className="hidden md:flex items-center justify-center gap-4" style={{
                height: '35px',
                padding: '5px 20px',
                borderRadius: '12px',
                background: '#E6D9AB'
              }}>
                <img
                  src="/flag.png"
                  alt="Drapeau français"
                  style={{ width: '24px', height: '16px', borderRadius: '10px', objectFit: 'cover' }}
                />
                <span style={{
                  color: '#2C2C2C',
                  fontFamily: 'var(--font-poppins)',
                  fontSize: '16px',
                  fontWeight: 400,
                  letterSpacing: '0.8px',
                  whiteSpace: 'nowrap'
                }}>
                  Stages Agréés par la Préfecture {getPreposition(cityDeptName)}{cityDeptName} ({cityDeptCode})
                </span>
              </div>
            )
          })()}
        </div>

        {/* Tagline below orange bar - Desktop only */}
        <p className="hidden md:block text-center mb-6" style={{
          fontFamily: 'var(--font-poppins)',
          color: 'rgba(52, 52, 52, 0.86)',
          fontSize: '15px',
          fontStyle: 'italic',
          fontWeight: 400,
          lineHeight: '28px'
        }}>
          Depuis 2008, plus de 857 000 conducteurs ont récupéré leurs points avec ProStagesPermis
        </p>

        {/* Mobile Filters Section - Only visible on mobile */}
        <div className="flex flex-col gap-3 w-full mb-[9px] md:hidden">
          {/* Filter buttons row - centered on mobile */}
          <div className="flex items-center justify-center gap-2">
            <div className="flex flex-col justify-center flex-shrink-0" style={{
              width: '76px',
              height: '35px',
              fontFamily: 'var(--font-poppins)',
              color: '#000',
              fontSize: '13px',
              fontStyle: 'normal',
              fontWeight: '400',
              lineHeight: 'normal',
              letterSpacing: '0.91px'
            }}>
              Trier par :
            </div>

            <button
              onClick={() => setSortBy(sortBy === 'date' ? null : 'date')}
              className={`px-3 rounded-lg border border-gray-400 transition-colors flex-shrink-0 ${
                sortBy === 'date' ? 'bg-[#EBEBEB]' : 'bg-white'
              }`}
              style={{
                height: '35px',
                color: 'rgba(4, 4, 4, 0.96)',
                fontFamily: 'var(--font-poppins)',
                fontSize: '12px',
                fontStyle: 'normal',
                fontWeight: '400',
                lineHeight: 'normal',
                letterSpacing: '0.84px'
              }}
            >
              Date
            </button>
            <button
              onClick={() => setSortBy(sortBy === 'prix' ? null : 'prix')}
              className={`px-3 rounded-lg border border-gray-400 transition-colors flex-shrink-0 ${
                sortBy === 'prix' ? 'bg-[#EBEBEB]' : 'bg-white'
              }`}
              style={{
                height: '35px',
                color: 'rgba(4, 4, 4, 0.96)',
                fontFamily: 'var(--font-poppins)',
                fontSize: '12px',
                fontStyle: 'normal',
                fontWeight: '400',
                lineHeight: 'normal',
                letterSpacing: '0.84px'
              }}
            >
              Prix
            </button>
            <button
              onClick={() => setSortBy(sortBy === 'proximite' ? null : 'proximite')}
              className={`px-3 rounded-lg border border-gray-400 transition-colors flex-shrink-0 ${
                sortBy === 'proximite' ? 'bg-[#EBEBEB]' : 'bg-white'
              }`}
              style={{
                height: '35px',
                color: 'rgba(4, 4, 4, 0.96)',
                fontFamily: 'var(--font-poppins)',
                fontSize: '12px',
                fontStyle: 'normal',
                fontWeight: '400',
                lineHeight: 'normal',
                letterSpacing: '0.84px'
              }}
            >
              Proximité
            </button>
          </div>
        </div>

        {/* Desktop: Two-column flex layout - shifted right to align center of stages with center of yellow widget */}
        <div className="hidden md:flex" style={{ gap: '24px', marginLeft: '80px' }}>
          {/* Left column: Stages List - takes available space */}
          <div style={{ display: 'flex', flex: 1, padding: '0 2px', flexDirection: 'column' }}>
            {/* Desktop Filters - Search left, Trier par center, Ville dropdown right */}
            <div className="flex items-center w-full mb-4">
              {/* LEFT: Search bar with autocomplete */}
              <div className="relative flex-shrink-0">
                <div
                  className="flex items-center gap-2"
                  style={{
                    width: '180px',
                    height: '32px',
                    padding: '6px 10px',
                    borderRadius: '8px',
                    border: '1px solid #D9D9D9',
                    background: '#FFF'
                  }}
                >
                  <input
                    ref={desktopSearchRef}
                    type="text"
                    value={desktopSearchQuery}
                    onChange={(e) => {
                      setDesktopSearchQuery(e.target.value)
                      setShowDesktopSuggestions(true)
                      setDesktopSelectedIndex(-1)
                    }}
                    onFocus={() => setShowDesktopSuggestions(true)}
                    onKeyDown={(e) => {
                      const filteredCities = desktopSearchQuery.length > 0
                        ? allCities.filter(c => c.toLowerCase().startsWith(desktopSearchQuery.toLowerCase())).slice(0, 8)
                        : []
                      if (e.key === 'Enter') {
                        e.preventDefault()
                        const cityToSearch = desktopSelectedIndex >= 0 && desktopSelectedIndex < filteredCities.length
                          ? filteredCities[desktopSelectedIndex]
                          : desktopSearchQuery
                        if (cityToSearch.trim()) {
                          const cityUpper = cityToSearch.toUpperCase()
                          const postal = CITY_POSTAL_MAP[cityUpper] || '00000'
                          window.location.href = `/stages-recuperation-points/${cityUpper}-${postal}`
                        }
                      } else if (e.key === 'ArrowDown') {
                        e.preventDefault()
                        setDesktopSelectedIndex(prev => prev < filteredCities.length - 1 ? prev + 1 : prev)
                      } else if (e.key === 'ArrowUp') {
                        e.preventDefault()
                        setDesktopSelectedIndex(prev => prev > 0 ? prev - 1 : -1)
                      } else if (e.key === 'Escape') {
                        setShowDesktopSuggestions(false)
                        setDesktopSelectedIndex(-1)
                      }
                    }}
                    placeholder="Ville ou code postal"
                    className="flex-1 bg-transparent border-none outline-none text-xs placeholder:text-gray-400"
                    style={{ minWidth: '0', fontFamily: 'var(--font-poppins)' }}
                  />
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 16 16" fill="none" className="flex-shrink-0">
                    <path d="M14 14L11.1 11.1M12.6667 7.33333C12.6667 10.2789 10.2789 12.6667 7.33333 12.6667C4.38781 12.6667 2 10.2789 2 7.33333C2 4.38781 4.38781 2 7.33333 2C10.2789 2 12.6667 4.38781 12.6667 7.33333Z" stroke="#1E1E1E" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                  </svg>
                </div>
                {/* Suggestions dropdown */}
                {showDesktopSuggestions && desktopSearchQuery.length > 0 && (() => {
                  const filteredCities = allCities
                    .filter(c => c.toLowerCase().startsWith(desktopSearchQuery.toLowerCase()))
                    .slice(0, 8)
                  if (filteredCities.length === 0) return null
                  return (
                    <div
                      ref={desktopSuggestionsRef}
                      className="absolute top-full left-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50"
                      style={{ width: '180px', maxHeight: '200px', overflowY: 'auto' }}
                    >
                      {filteredCities.map((cityName, index) => {
                        const deptCode = CITY_POSTAL_MAP[cityName.toUpperCase()]?.substring(0, 2) || ''
                        const displayName = cityName.split('-').map((word, i) => {
                          const lower = word.toLowerCase()
                          if (i > 0 && ['en', 'de', 'du', 'la', 'le', 'les', 'sur', 'sous'].includes(lower)) return lower
                          return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
                        }).join('-')
                        return (
                          <button
                            key={cityName}
                            onClick={() => {
                              const cityUpper = cityName.toUpperCase()
                              const postal = CITY_POSTAL_MAP[cityUpper] || '00000'
                              window.location.href = `/stages-recuperation-points/${cityUpper}-${postal}`
                            }}
                            className={`w-full text-left px-3 py-2 text-xs transition-colors ${
                              index === desktopSelectedIndex ? 'bg-blue-100 text-blue-900' : 'text-gray-700 hover:bg-gray-100'
                            }`}
                            style={{ fontFamily: 'var(--font-poppins)' }}
                          >
                            {displayName}{deptCode ? ` (${deptCode})` : ''}
                          </button>
                        )
                      })}
                    </div>
                  )
                })()}
              </div>

              {/* CENTER: Trier par + Date/Prix/Proximité buttons */}
              <div className="flex items-center gap-2 flex-1 justify-center">
                <span className="flex-shrink-0 text-xs" style={{
                  fontFamily: 'var(--font-poppins)',
                  color: '#000',
                  letterSpacing: '0.5px'
                }}>
                  Trier par :
                </span>

                <button
                  onClick={() => setSortBy(sortBy === 'date' ? null : 'date')}
                  className={`px-2 rounded-lg border border-gray-400 transition-colors flex-shrink-0 ${
                    sortBy === 'date' ? 'bg-[#EBEBEB]' : 'bg-white'
                  }`}
                  style={{
                    height: '32px',
                    color: 'rgba(4, 4, 4, 0.96)',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '11px',
                    fontWeight: '400'
                  }}
                >
                  Date
                </button>
                <button
                  onClick={() => setSortBy(sortBy === 'prix' ? null : 'prix')}
                  className={`px-2 rounded-lg border border-gray-400 transition-colors flex-shrink-0 ${
                    sortBy === 'prix' ? 'bg-[#EBEBEB]' : 'bg-white'
                  }`}
                  style={{
                    height: '32px',
                    color: 'rgba(4, 4, 4, 0.96)',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '11px',
                    fontWeight: '400'
                  }}
                >
                  Prix
                </button>
                <button
                  onClick={() => setSortBy(sortBy === 'proximite' ? null : 'proximite')}
                  className={`px-2 rounded-lg border border-gray-400 transition-colors flex-shrink-0 ${
                    sortBy === 'proximite' ? 'bg-[#EBEBEB]' : 'bg-white'
                  }`}
                  style={{
                    height: '32px',
                    color: 'rgba(4, 4, 4, 0.96)',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '11px',
                    fontWeight: '400'
                  }}
                >
                  Proximité
                </button>
              </div>

              {/* RIGHT: Ville dropdown */}
              <div className="relative flex-shrink-0" ref={cityDropdownRef}>
                <button
                  onClick={() => setShowCitiesDropdown(!showCitiesDropdown)}
                  className="flex items-center rounded-lg border border-black"
                  style={{
                    display: 'flex',
                    width: '144px',
                    height: '32px',
                    padding: '0 11px',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                    flexShrink: 0,
                    fontFamily: 'var(--font-poppins)',
                    color: '#060606',
                    fontSize: '11px',
                    fontWeight: '400'
                  }}
                >
                    <span className="truncate flex-1 text-left">
                      {allCitiesSelected
                        ? 'Ville'
                        : selectedCities.length === 1
                          ? formatCityName(selectedCities[0])
                          : `${selectedCities.length} villes`
                      }
                    </span>
                    <svg className="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>

                  {showCitiesDropdown && (
                    <div className="absolute top-full mt-2 right-0 w-64 bg-white border border-gray-300 rounded shadow-lg z-10 max-h-96 overflow-y-auto">
                      <label className="flex items-center w-full px-4 py-2 hover:bg-gray-100 cursor-pointer">
                        <input
                          type="checkbox"
                          checked={allCitiesSelected}
                          onChange={handleAllCitiesToggle}
                          className="mr-3 w-4 h-4"
                        />
                        <span className="text-sm font-medium">Toutes les villes</span>
                      </label>
                      <label className="flex items-center w-full px-4 py-2 hover:bg-gray-100 cursor-pointer">
                        <input
                          type="checkbox"
                          checked={!allCitiesSelected && selectedCities.includes(city)}
                          onChange={() => handleCityToggle(city)}
                          className="mr-3 w-4 h-4"
                        />
                        <span className="text-sm">{formatCityName(city)}</span>
                      </label>
                      {nearbyCities.map((nearby) => (
                        <label key={nearby.city} className="flex items-center w-full px-4 py-2 hover:bg-gray-100 cursor-pointer">
                          <input
                            type="checkbox"
                            checked={!allCitiesSelected && selectedCities.includes(nearby.city)}
                            onChange={() => handleCityToggle(nearby.city)}
                            className="mr-3 w-4 h-4"
                          />
                          <span className="text-sm">{formatCityName(nearby.city)}</span>
                        </label>
                      ))}
                    </div>
                  )}
                </div>
            </div>
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
                    className="flex flex-row w-full h-[85px] p-[0_7px] items-center mb-3 rounded-[10px] border border-[#BBB] bg-white shadow-[0_4px_10px_0_rgba(0,0,0,0.15)]"
                  >
                    {/* Desktop Layout */}
                    <div className="flex items-center w-full">
                      {/* Left: Date and Details Link */}
                      <div className="flex flex-col flex-shrink-0 gap-0 ml-3">
                        <p className="w-[200px] text-[rgba(0,0,0,0.89)] text-[14px] font-medium leading-[14px]" style={{ fontFamily: 'var(--font-poppins)' }}>
                          {formatDate(stage.date_start, stage.date_end)}
                        </p>
                        <button
                          onClick={() => {
                            setSelectedStage(stage)
                            setIsModalOpen(true)
                          }}
                          className="flex items-center gap-[5px] text-[rgba(90,106,147,0.86)] text-[12px] font-normal leading-[12px] hover:underline text-left mt-2"
                          style={{ fontFamily: 'var(--font-poppins)' }}
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 15 15" fill="none" className="w-[12px] h-[12px] flex-shrink-0">
                            <path d="M7.46665 10.1334V7.46672M7.46665 4.80005H7.47332M14.1333 7.46672C14.1333 11.1486 11.1486 14.1334 7.46665 14.1334C3.78476 14.1334 0.799988 11.1486 0.799988 7.46672C0.799988 3.78482 3.78476 0.800049 7.46665 0.800049C11.1486 0.800049 14.1333 3.78482 14.1333 7.46672Z" stroke="#5A6A93" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                          </svg>
                          <span>Détails du stage</span>
                        </button>
                      </div>

                      {/* Center: Location Pin + City + Address */}
                      <div className="flex items-center gap-2 flex-1 mx-4">
                        <div className="flex w-[32px] h-[32px] p-[7px] justify-center items-center flex-shrink-0 rounded-full bg-gray-200">
                          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="none" className="w-[18px] h-[18px] flex-shrink-0">
                            <g clipPath="url(#clip0_desktop)">
                              <path d="M17.5 8.33337C17.5 14.1667 10 19.1667 10 19.1667C10 19.1667 2.5 14.1667 2.5 8.33337C2.5 6.34425 3.29018 4.4366 4.6967 3.03007C6.10322 1.62355 8.01088 0.833374 10 0.833374C11.9891 0.833374 13.8968 1.62355 15.3033 3.03007C16.7098 4.4366 17.5 6.34425 17.5 8.33337Z" stroke="#808080" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                              <path d="M10 10.8334C11.3807 10.8334 12.5 9.71409 12.5 8.33337C12.5 6.95266 11.3807 5.83337 10 5.83337C8.61929 5.83337 7.5 6.95266 7.5 8.33337C7.5 9.71409 8.61929 10.8334 10 10.8334Z" stroke="#808080" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                            </g>
                            <defs>
                              <clipPath id="clip0_desktop">
                                <rect width="20" height="20" fill="white"/>
                              </clipPath>
                            </defs>
                          </svg>
                        </div>
                        <div className="flex flex-col justify-center gap-0">
                          <p className="flex-shrink-0 text-[rgba(0,0,0,0.98)] text-[14px] font-normal leading-[14px]" style={{ fontFamily: 'var(--font-poppins)' }}>{formatCityName(stage.site.ville)}</p>
                          <p className="flex-shrink-0 text-[rgba(6,6,6,0.56)] text-[11px] font-normal leading-[11px] mt-2" style={{ fontFamily: 'var(--font-poppins)' }}>{removeStreetNumber(stage.site.adresse)}</p>
                        </div>
                      </div>

                      {/* Right: Price */}
                      <div className="w-[80px] flex-shrink-0">
                        <p className="text-[rgba(6,6,6,0.86)] text-center text-[18px] font-normal leading-[28px]" style={{ fontFamily: 'var(--font-poppins)' }}>{stage.prix}€</p>
                      </div>

                      {/* Right: Green Button */}
                      <Link
                        href={`/stages-recuperation-points/${fullSlug}/${stage.id}/inscription`}
                        className="flex px-[12px] py-[6px] justify-center items-center rounded-xl bg-[#41A334] text-white text-[10px] font-normal leading-normal tracking-[0.7px] hover:bg-[#389c2e] transition-colors whitespace-nowrap flex-shrink-0 mr-2"
                        style={{ fontFamily: 'var(--font-poppins)' }}
                      >
                        Sélectionner
                      </Link>
                    </div>
                  </article>
                ))}

                <div className="flex items-center justify-center gap-4 mt-6">
                  {visibleCount > STAGES_PER_LOAD && (
                    <button
                      onClick={() => setVisibleCount(STAGES_PER_LOAD)}
                      className="px-8 py-2 bg-white border-2 border-[#EBEBEB] text-gray-800 text-sm rounded-2xl hover:bg-gray-50 transition-colors"
                    >
                      Afficher moins de stages
                    </button>
                  )}

                  {hasMore && (
                    <button
                      onClick={() => setVisibleCount(prev => prev + STAGES_PER_LOAD)}
                      className="px-8 py-2 bg-[#EBEBEB] text-gray-800 text-sm rounded-2xl hover:bg-[#DEDEDE] transition-colors"
                    >
                      Voir plus de stages
                    </button>
                  )}
                </div>
              </>
            )}
          </div>

          {/* Right column: Sticky Guarantees Block */}
          <div style={{ width: '260px', flexShrink: 0 }}>
            <div className="sticky top-4" style={{
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'center',
              gap: '20px',
              padding: '15px',
              borderRadius: '15px',
              border: '1px solid #F1F1F1',
              background: '#FFF',
              boxShadow: '0 4px 12px 2px rgba(0, 0, 0, 0.15)'
            }}>
              {/* Header */}
              <div style={{
                display: 'flex',
                width: '100%',
                padding: '10px 15px',
                justifyContent: 'center',
                alignItems: 'center',
                borderRadius: '8px',
                background: '#EFEFEF'
              }}>
                <span style={{
                  width: '226px',
                  flexShrink: 0,
                  color: '#000',
                  textAlign: 'center',
                  fontFamily: 'var(--font-poppins)',
                  fontSize: '17px',
                  fontWeight: 400,
                  lineHeight: '25px'
                }}>
                  Vos Garanties ProStagesPermis
                </span>
              </div>

              {/* Benefit items */}
              <div style={{ display: 'flex', flexDirection: 'column', gap: '10px', width: '100%', padding: '0 5px' }}>
                {[
                  'Stages officiels agréés Préfecture',
                  '+4 points en 48h',
                  'Meilleur prix garanti',
                  'Inscriptions en quelques clics',
                  'Convocation envoyée immédiatement',
                  'Remboursement en cas d\'imprévu',
                  '98,7% de clients satisfaits'
                ].map((benefit, index) => (
                  <div key={index} style={{ display: 'flex', alignItems: 'flex-start', gap: '8px' }}>
                    {/* Yellow checkmark icon */}
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 25 25" fill="none" style={{ flexShrink: 0 }}>
                      <path d="M9.375 11.4583L12.5 14.5833L22.9167 4.16667M21.875 12.5V19.7917C21.875 20.3442 21.6555 20.8741 21.2648 21.2648C20.8741 21.6555 20.3442 21.875 19.7917 21.875H5.20833C4.6558 21.875 4.12589 21.6555 3.73519 21.2648C3.34449 20.8741 3.125 20.3442 3.125 19.7917V5.20833C3.125 4.6558 3.34449 4.12589 3.73519 3.73519C4.12589 3.34449 4.6558 3.125 5.20833 3.125H16.6667" stroke="#C4A226" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                    <span style={{
                      color: 'rgba(6, 6, 6, 0.86)',
                      fontFamily: 'var(--font-poppins)',
                      fontSize: '13px',
                      fontWeight: 400,
                      lineHeight: '18px'
                    }}>
                      {benefit}
                    </span>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>

        {/* Mobile: Stages List (full width, no guarantees block) */}
        <div className="md:hidden" data-stages-section ref={stagesSectionRef}>
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
                  className="flex flex-col w-full py-[10px] px-3 mb-[7px] rounded-[10px] border border-[#BBB] bg-white shadow-[0_4px_10px_0_rgba(0,0,0,0.15)] mx-auto"
                >
                  {/* Mobile Layout */}
                  <div className="flex w-full">
                    {/* Left side: Date, Location, Details link */}
                    <div className="flex flex-col flex-1">
                      {/* Date at top */}
                      <p className="text-[rgba(0,0,0,0.89)] text-[15px] font-medium leading-[15px] mb-2" style={{ fontFamily: 'var(--font-poppins)' }}>
                        {formatDate(stage.date_start, stage.date_end)}
                      </p>

                      {/* Location pin + City + Address + Details link */}
                      <div className="flex items-start gap-2">
                        <div className="flex w-[20px] h-[20px] justify-center items-center flex-shrink-0 rounded-full bg-gray-200 mt-0.5">
                          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 20 20" fill="none" className="w-3 h-3 flex-shrink-0">
                            <g clipPath="url(#clip0_mobile)">
                              <path d="M17.5 8.33337C17.5 14.1667 10 19.1667 10 19.1667C10 19.1667 2.5 14.1667 2.5 8.33337C2.5 6.34425 3.29018 4.4366 4.6967 3.03007C6.10322 1.62355 8.01088 0.833374 10 0.833374C11.9891 0.833374 13.8968 1.62355 15.3033 3.03007C16.7098 4.4366 17.5 6.34425 17.5 8.33337Z" stroke="#808080" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                              <path d="M10 10.8334C11.3807 10.8334 12.5 9.71409 12.5 8.33337C12.5 6.95266 11.3807 5.83337 10 5.83337C8.61929 5.83337 7.5 6.95266 7.5 8.33337C7.5 9.71409 8.61929 10.8334 10 10.8334Z" stroke="#808080" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                            </g>
                            <defs>
                              <clipPath id="clip0_mobile">
                                <rect width="20" height="20" fill="white"/>
                              </clipPath>
                            </defs>
                          </svg>
                        </div>
                        <div className="flex flex-col">
                          <p className="text-[rgba(0,0,0,0.98)] text-[14px] font-medium leading-[14px]" style={{ fontFamily: 'var(--font-poppins)' }}>{stage.site.ville.charAt(0).toUpperCase() + stage.site.ville.slice(1).toLowerCase()}</p>
                          <p className="text-[rgba(6,6,6,0.56)] text-[11px] font-normal leading-[11px] mt-1" style={{ fontFamily: 'var(--font-poppins)' }}>{removeStreetNumber(stage.site.adresse)}</p>
                          {/* Details link - aligned with city/address text */}
                          <button
                            onClick={() => {
                              setSelectedStage(stage)
                              setIsModalOpen(true)
                            }}
                            className="flex items-center text-[rgba(90,106,147,0.86)] text-[12px] font-normal leading-[12px] hover:underline text-left mt-1"
                            style={{ fontFamily: 'var(--font-poppins)' }}
                          >
                            Détails du stage
                          </button>
                        </div>
                      </div>
                    </div>

                    {/* Right side: Price and Button */}
                    <div className="flex flex-col items-center ml-4" style={{ gap: '6px', paddingTop: '2px' }}>
                      <p style={{
                        display: 'flex',
                        width: '63px',
                        height: '22px',
                        flexDirection: 'column',
                        justifyContent: 'center',
                        flexShrink: 0,
                        color: 'rgba(6, 6, 6, 0.86)',
                        textAlign: 'center',
                        fontFamily: 'var(--font-poppins)',
                        fontSize: '16px',
                        fontStyle: 'normal',
                        fontWeight: 500,
                        lineHeight: '28px',
                        marginRight: '2px'
                      }}>{stage.prix}€</p>
                      <Link
                        href={`/stages-recuperation-points/${fullSlug}/${stage.id}/inscription`}
                        style={{
                          display: 'flex',
                          width: '87px',
                          height: '32px',
                          padding: '6px 0',
                          justifyContent: 'center',
                          alignItems: 'center',
                          borderRadius: '10px',
                          background: '#41A334',
                          textDecoration: 'none'
                        }}
                        className="hover:bg-[#389c2e] transition-colors"
                      >
                        <span style={{
                          width: '85px',
                          flexShrink: 0,
                          color: '#FFF',
                          textAlign: 'center',
                          fontFamily: 'var(--font-poppins)',
                          fontSize: '12px',
                          fontStyle: 'normal',
                          fontWeight: 400,
                          lineHeight: 'normal',
                          letterSpacing: '0.24px'
                        }}>
                          Sélectionner
                        </span>
                      </Link>
                    </div>
                  </div>
                </article>
              ))}

              <div className="flex flex-col items-center justify-center gap-3 mt-6 px-4">
                {visibleCount > STAGES_PER_LOAD && (
                  <button
                    onClick={() => {
                      setVisibleCount(STAGES_PER_LOAD)
                      // Scroll to top of stages section
                      const stagesSection = document.querySelector('[data-stages-section]')
                      if (stagesSection) {
                        stagesSection.scrollIntoView({ behavior: 'smooth', block: 'start' })
                      }
                    }}
                    className="text-sm hover:opacity-70 transition-opacity"
                    style={{
                      fontFamily: 'var(--font-poppins)',
                      color: '#000',
                      fontWeight: 400,
                      textDecoration: 'underline',
                      background: 'none',
                      border: 'none',
                      cursor: 'pointer'
                    }}
                  >
                    Afficher moins de stages
                  </button>
                )}

                {hasMore && (
                  <button
                    onClick={() => setVisibleCount(prev => prev + STAGES_PER_LOAD)}
                    className="px-4 py-2 bg-[#EBEBEB] text-gray-800 text-sm rounded-2xl hover:bg-[#DEDEDE] transition-colors"
                  >
                    Voir plus de stages
                  </button>
                )}
              </div>
            </>
          )}
        </div>

        {/* Benefit Box Section - Mobile Only (Desktop uses sticky sidebar) */}
        <section className="my-8 flex justify-center px-4 md:hidden">
          {/* Mobile: New benefit widget */}
          <div style={{
            display: 'flex',
            width: '340px',
            height: '377px',
            padding: '10px 0',
            flexDirection: 'column',
            alignItems: 'center',
            gap: '25px',
            borderRadius: '20px',
            border: '1px solid #F1F1F1',
            background: '#FFF',
            boxShadow: '0 4px 12px 2px rgba(0, 0, 0, 0.20)'
          }}>
            {/* Top grey header */}
            <div style={{
              display: 'flex',
              width: '310px',
              height: '53px',
              padding: '8px 106px',
              justifyContent: 'center',
              alignItems: 'center',
              gap: '10px',
              flexShrink: 0,
              borderRadius: '8px',
              background: '#EFEFEF'
            }}>
              <span style={{
                width: '237px',
                flexShrink: 0,
                color: '#000',
                textAlign: 'center',
                fontFamily: 'var(--font-poppins)',
                fontSize: '17px',
                fontStyle: 'normal',
                fontWeight: 400,
                lineHeight: '25px'
              }}>
                Vos Garanties ProStagesPermis
              </span>
            </div>

            {/* Benefit items */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px', paddingLeft: '20px', paddingRight: '20px' }}>
              {[
                'Stage officiel agréé Préfecture',
                '+4 points en 48h',
                '98,7% de clients satisfaits',
                'Report ou remboursement en cas d\'imprévu',
                'Paiement 100% sécurisé',
                'Attestation de stage remise le 2ème jour'
              ].map((benefit, index) => (
                <div key={index} style={{ display: 'flex', alignItems: 'flex-start', gap: '10px' }}>
                  {/* Yellow checkmark icon */}
                  <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none" style={{ flexShrink: 0 }}>
                    <path d="M9.375 11.4583L12.5 14.5833L22.9167 4.16667M21.875 12.5V19.7917C21.875 20.3442 21.6555 20.8741 21.2648 21.2648C20.8741 21.6555 20.3442 21.875 19.7917 21.875H5.20833C4.6558 21.875 4.12589 21.6555 3.73519 21.2648C3.34449 20.8741 3.125 20.3442 3.125 19.7917V5.20833C3.125 4.6558 3.34449 4.12589 3.73519 3.73519C4.12589 3.34449 4.6558 3.125 5.20833 3.125H16.6667" stroke="#C4A226" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                  </svg>
                  <span style={{
                    color: 'rgba(6, 6, 6, 0.86)',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '15px',
                    fontStyle: 'normal',
                    fontWeight: 400,
                    lineHeight: '20px'
                  }}>
                    {benefit}
                  </span>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* Customer Reviews Section */}
        <section className="my-8 md:my-16 px-4">
          <div className="flex items-center justify-center gap-2 md:gap-4 mb-6 md:mb-8">
            <div className="h-px w-8 md:w-16 bg-gray-300" />
            <h2 className="text-center text-[16px] md:text-[20px] font-[250] leading-[25px] md:leading-[35px]" style={{
              fontFamily: 'var(--font-poppins)',
              color: 'rgba(6, 6, 6, 0.86)',
              WebkitTextStrokeWidth: '1px',
              WebkitTextStrokeColor: '#000'
            }}>
              Avis <span style={{
                WebkitTextStrokeColor: 'rgba(201, 39, 39, 0.73)'
              }}>Clients</span>
            </h2>
            <div className="h-px w-8 md:w-16 bg-gray-300" />
          </div>

          <div className="bg-gray-100 rounded-lg p-6 md:p-12 flex items-center justify-center">
            <p className="text-gray-500 text-sm md:text-base">Section Avis Clients - Placeholder</p>
          </div>
        </section>

        {/* About Us Section */}
        <section className="my-8 md:my-16 flex flex-col items-center px-4">
          {/* Top: Europe 1 Recommendation */}
          <div className="flex flex-row items-center justify-center gap-0 mb-12 md:mb-[120px]">
            {/* Left: Recommandé par Europe 1 with subtitle */}
            <div className="flex flex-col items-center justify-center">
              <h3 className="text-center md:hidden" style={{
                fontFamily: 'var(--font-poppins)',
                color: '#2C2C2C',
                fontSize: '14px',
                fontWeight: 500,
                lineHeight: '18px',
                letterSpacing: '0.8px',
                width: '120px'
              }}>
                Recommandé par Europe 1
              </h3>
              <h3 className="hidden md:block text-center" style={{
                fontFamily: 'var(--font-poppins)',
                color: '#2C2C2C',
                fontSize: '18px',
                fontWeight: 500,
                lineHeight: '22px',
                letterSpacing: '1.2px'
              }}>
                Recommandé par Europe 1
              </h3>
              <p className="text-center mt-2 md:hidden" style={{
                fontFamily: 'var(--font-poppins)',
                width: '200px',
                color: 'rgba(6, 6, 6, 0.80)',
                fontSize: '12px',
                fontWeight: 400,
                lineHeight: '18px'
              }}>
                ProStagesPermis cité comme site de confiance par Europe 1
              </p>
              <p className="hidden md:block text-center mt-2 px-4" style={{
                fontFamily: 'var(--font-poppins)',
                maxWidth: '333px',
                color: 'rgba(6, 6, 6, 0.80)',
                fontSize: '14px',
                fontWeight: 400,
                lineHeight: '22px'
              }}>
                ProStagesPermis cité comme site de confiance par Europe 1
              </p>
            </div>

            {/* Vertical Line - Mobile version */}
            <div className="md:hidden" style={{
              width: '1px',
              height: '120px',
              background: '#000',
              marginLeft: '15px',
              marginRight: '15px'
            }}></div>

            {/* Vertical Line - Desktop version */}
            <div className="hidden md:block" style={{
              width: '1px',
              height: '172.502px',
              background: '#000',
              marginLeft: '35px',
              marginRight: '35px'
            }}></div>

            {/* Right: Europe 1 Logo */}
            <div className="flex flex-col items-center justify-center md:gap-4 gap-2">
              <Image
                src="/europe1-logo.png"
                alt="Europe 1"
                width={200}
                height={100}
                className="w-auto h-12 md:h-20"
              />
              <a
                href="https://www.youtube.com/watch?v=z1AsmdcGTaw"
                target="_blank"
                rel="noopener noreferrer"
                className="text-red-700 hover:underline"
                style={{
                  fontFamily: 'var(--font-poppins)',
                  fontSize: '12px',
                  fontWeight: 400
                }}
              >
                Écouter l'extrait
              </a>
            </div>
          </div>

          {/* Bottom: Qui est ProStagesPermis */}
          <div className="flex justify-center items-center w-full md:w-[626px] p-6 md:p-8 md:border md:border-black">
            <div className="w-full">
              <h2 className="text-center mb-4 text-[18px] md:text-[20px] font-[250] leading-[30px] md:leading-[35px]" style={{
                fontFamily: 'var(--font-poppins)',
                color: 'rgba(6, 6, 6, 0.86)',
                WebkitTextStrokeWidth: '1px',
                WebkitTextStrokeColor: '#000'
              }}>
                Qui est <span style={{
                  WebkitTextStrokeColor: 'rgba(201, 39, 39, 0.73)'
                }}>ProStagesPermis</span>
              </h2>
              <div className="w-full" style={{
                fontFamily: 'var(--font-poppins)',
                color: 'rgba(6, 6, 6, 0.94)',
                fontSize: '14px',
                fontWeight: 400,
                lineHeight: '22px',
                textAlign: 'left'
              }}>
                <p>
                  Depuis 2008, ProStagesPermis est le site n° 1 spécialisé dans les stages de récupération de points. Notre mission : vous aider à sauver votre permis dans les temps, avec un stage au meilleur prix proche de chez vous. Plus de 857 000 conducteurs nous ont déjà fait confiance.
                </p>
                {/* Mobile: No bullet points */}
                {/* Desktop: Show bullet points */}
                <ul className="hidden md:block list-disc list-inside mt-4 space-y-1">
                  <li>Près de 18 ans d'expérience dans les stages de récupération de points</li>
                  <li>Des dizaines de milliers de conducteurs accompagnés partout en France</li>
                  <li>Un réseau de centres de formation agréés partout en France</li>
                  <li>Note Google 4,8/5 avis vérifiés</li>
                </ul>
              </div>
              {/* Mobile only: En savoir plus button */}
              <div className="md:hidden flex justify-center mt-4">
                <button
                  style={{
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '14px',
                    fontWeight: 400,
                    color: '#000',
                    textDecoration: 'underline',
                    background: 'none',
                    border: 'none',
                    cursor: 'default'
                  }}
                >
                  En savoir plus
                </button>
              </div>
            </div>
          </div>
        </section>

        {/* FAQ Section - Full width grey background on desktop */}
        <section className="my-8 md:my-16">
          {/* Desktop: Title - outside grey background */}
          <h2 className="hidden md:block text-center mb-6 md:mb-8 text-[18px] md:text-[20px] font-[250] leading-[30px] md:leading-[35px] px-4" style={{
            fontFamily: 'var(--font-poppins)',
            color: 'rgba(6, 6, 6, 0.86)',
            WebkitTextStrokeWidth: '1px',
            WebkitTextStrokeColor: '#000'
          }}>
            Questions <span style={{
              WebkitTextStrokeColor: 'rgba(201, 39, 39, 0.73)'
            }}>Fréquentes</span>
          </h2>

          {/* Mobile: Grey box with title, subtitle, and questions */}
          <div className="md:hidden flex flex-col items-center px-4">
            {/* Grey box container - dynamic height */}
            <div style={{
              display: 'flex',
              width: '382px',
              minHeight: '424px',
              flexDirection: 'column',
              alignItems: 'center',
              background: '#F6F6F6',
              padding: '20px 0'
            }}>
              {/* Title inside grey box */}
              <h2 className="text-center mb-3 text-[18px] font-[250] leading-[30px]" style={{
                fontFamily: 'var(--font-poppins)',
                color: 'rgba(6, 6, 6, 0.86)',
                WebkitTextStrokeWidth: '1px',
                WebkitTextStrokeColor: '#000'
              }}>
                Questions <span style={{
                  WebkitTextStrokeColor: 'rgba(201, 39, 39, 0.73)'
                }}>Fréquentes</span>
              </h2>

              {/* Subtitle inside grey box */}
              <p className="mb-4" style={{
                width: '339px',
                color: '#000',
                fontFamily: 'var(--font-poppins)',
                fontSize: '15px',
                fontStyle: 'normal',
                fontWeight: 400,
                lineHeight: '20px',
                textAlign: 'center'
              }}>
                Réponses aux questions que se posent le plus souvent les conducteurs
              </p>

              {/* FAQ questions */}
              {faqData.map((faq, index) => (
                <div key={faq.id} className="w-full">
                  <button
                    className="flex items-start justify-between p-3 w-full text-left"
                    onClick={() => setOpenFaqIndex(openFaqIndex === index ? null : index)}
                  >
                    <p className="text-gray-900 text-sm flex-1" style={{
                      fontFamily: 'var(--font-poppins)',
                      fontSize: '15px',
                      fontWeight: 400
                    }}>{faq.question}</p>
                    <svg
                      className={`w-5 h-5 text-gray-600 transition-transform flex-shrink-0 mt-0.5 ${openFaqIndex === index ? 'rotate-180' : ''}`}
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>

                  {openFaqIndex === index && (
                    <div className="px-3 pb-3 pt-2">
                      <p className="text-gray-700 leading-relaxed text-sm">
                        Ceci est un placeholder pour la réponse à la question. Le contenu sera ajouté ultérieurement.
                        Cette section peut contenir des informations détaillées sur la récupération de points,
                        les délais, les conditions et toutes les informations pertinentes pour répondre à la question posée.
                      </p>
                    </div>
                  )}

                  {/* Line separator (except after last question) */}
                  {index < faqData.length - 1 && (
                    <div className="flex justify-center">
                      <div style={{
                        width: '320px',
                        height: '1px',
                        background: '#D0D0D0'
                      }}></div>
                    </div>
                  )}
                </div>
              ))}

              {/* "Afficher plus de questions" button - INSIDE grey box at bottom */}
              <div className="flex justify-center mt-6">
                <button className="text-sm" style={{
                  fontFamily: 'var(--font-poppins)',
                  color: '#000',
                  fontWeight: 500,
                  letterSpacing: '1.05px',
                  textDecoration: 'underline',
                  textDecorationStyle: 'solid',
                  textDecorationSkipInk: 'auto',
                  background: 'none',
                  border: 'none',
                  cursor: 'pointer'
                }}>
                  Afficher plus de questions
                </button>
              </div>
            </div>
          </div>

          {/* Desktop: Full-width grey background - breaks out of container */}
          <div className="hidden md:block" style={{
            width: '100vw',
            marginLeft: 'calc(-50vw + 50%)',
            background: '#F6F6F6',
            padding: '30px 20px'
          }}>
            <div
              style={{
                display: 'flex',
                width: '692px',
                minHeight: '402px',
                margin: '0 auto',
                flexDirection: 'column',
                alignItems: 'center',
                gap: '0'
              }}
            >
              {/* Subtitle */}
              <div
                style={{
                  color: '#000',
                  textAlign: 'center',
                  fontFamily: 'var(--font-poppins)',
                  fontSize: '15px',
                  fontStyle: 'normal',
                  fontWeight: 400,
                  lineHeight: '20px',
                  marginBottom: '25px',
                  width: '100%'
                }}
              >
                Réponses aux questions que se posent le plus souvent les conducteurs
              </div>

              {/* Questions */}
              {faqData.map((faq, index) => (
                <div key={faq.id} style={{ width: '100%' }}>
                  <div
                    onClick={() => setOpenFaqIndex(openFaqIndex === index ? null : index)}
                    style={{
                      width: '100%',
                      display: 'flex',
                      justifyContent: 'space-between',
                      alignItems: 'center',
                      cursor: 'pointer',
                      gap: '10px'
                    }}
                  >
                    <div
                      style={{
                        flex: 1,
                        color: '#060606',
                        textAlign: 'left',
                        fontFamily: 'var(--font-poppins)',
                        fontSize: '15px',
                        fontStyle: 'normal',
                        fontWeight: 400,
                        lineHeight: '35px',
                        whiteSpace: 'nowrap',
                        overflow: 'hidden',
                        textOverflow: 'ellipsis'
                      }}
                    >
                      {faq.question}
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none" style={{ width: '25px', height: '25px', flexShrink: 0, transform: openFaqIndex === index ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform 0.2s' }}>
                      <path d="M6.25 9.375L12.5 15.625L18.75 9.375" stroke="#1E1E1E" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                  </div>
                  {openFaqIndex === index && (
                    <div style={{ padding: '15px 0', color: '#666', fontSize: '14px', lineHeight: '22px', textAlign: 'left' }}>
                      Ceci est un placeholder pour la réponse à la question. Le contenu sera ajouté ultérieurement.
                    </div>
                  )}

                  {/* Separator line */}
                  {index < faqData.length - 1 && (
                    <div style={{ width: '100%', height: '1px', background: '#D0D0D0', marginTop: '15px', marginBottom: '15px' }} />
                  )}
                </div>
              ))}

              {/* Last line with more margin */}
              <div style={{ width: '100%', height: '1px', background: '#D0D0D0', marginTop: '15px', marginBottom: '50px' }} />

              {/* Afficher plus de questions */}
              <div
                style={{
                  color: '#000',
                  fontFamily: 'var(--font-poppins)',
                  fontSize: '15px',
                  fontStyle: 'normal',
                  fontWeight: 500,
                  lineHeight: 'normal',
                  letterSpacing: '1.05px',
                  textDecoration: 'underline',
                  cursor: 'pointer'
                }}
              >
                Afficher plus de questions
              </div>
            </div>
          </div>
        </section>

        {/* Nearby Cities Section */}
        {nearbyCities.length > 0 && (
          <section className="my-8 md:my-16 px-4">
            <h2 className="text-center mb-2 md:mb-4 text-[18px] md:text-[20px] font-[250] leading-[30px] md:leading-[35px]" style={{
              fontFamily: 'var(--font-poppins)',
              color: 'rgba(6, 6, 6, 0.86)',
              WebkitTextStrokeWidth: '1px',
              WebkitTextStrokeColor: '#000'
            }}>
              Stages Récupération de Points <span style={{
                WebkitTextStrokeColor: 'rgba(201, 39, 39, 0.73)'
              }}>autour de {city.charAt(0) + city.slice(1).toLowerCase()}</span>
            </h2>
            {/* Subtitle - Desktop only */}
            <p className="hidden md:block text-center mb-6" style={{
              fontFamily: 'var(--font-poppins)',
              color: 'rgba(52, 52, 52, 0.80)',
              fontSize: '15px',
              fontWeight: 400,
              lineHeight: '24px'
            }}>
              Vous n'avez pas trouvé votre stage idéal ? Découvrez les stages disponibles autour de {city.charAt(0) + city.slice(1).toLowerCase()}
            </p>

            {/* 3x3 Grid of nearby cities (single column on mobile) */}
            <div className="flex flex-col md:flex-row justify-center gap-4 md:gap-[30px]">
              {/* Column 1 */}
              <div className="flex flex-col gap-[8px]">
                {nearbyCities.slice(0, 3).map((nearbyCity) => {
                  const formattedCity = nearbyCity.city
                    .split('-')
                    .map(word => word.charAt(0) + word.slice(1).toLowerCase())
                    .join(' ')
                  const cityStage = allStages.find(s => s.site.ville === nearbyCity.city)
                  const deptNumber = cityStage?.site.code_postal.substring(0, 2) || ''
                  const postalCode = cityStage?.site.code_postal || ''
                  const citySlug = `${nearbyCity.city}-${postalCode}`

                  return (
                    <Link
                      key={nearbyCity.city}
                      href={`/stages-recuperation-points/${citySlug}`}
                      className="flex items-center gap-2 h-[35px] flex-shrink-0 no-underline hover:underline"
                      style={{
                        fontFamily: 'var(--font-poppins)',
                        color: '#BC4747',
                        fontSize: '15px',
                        fontWeight: 400,
                        lineHeight: '35px',
                        whiteSpace: 'nowrap'
                      }}
                    >
                      <span style={{
                        width: '4px',
                        height: '4px',
                        borderRadius: '50%',
                        backgroundColor: '#BC4747',
                        flexShrink: 0
                      }}></span>
                      <span>Stage {formattedCity} ({deptNumber})</span>
                    </Link>
                  )
                })}
              </div>

              {/* Column 2 */}
              <div className="flex flex-col gap-[8px]">
                {nearbyCities.slice(3, 6).map((nearbyCity) => {
                  const formattedCity = nearbyCity.city
                    .split('-')
                    .map(word => word.charAt(0) + word.slice(1).toLowerCase())
                    .join(' ')
                  const cityStage = allStages.find(s => s.site.ville === nearbyCity.city)
                  const deptNumber = cityStage?.site.code_postal.substring(0, 2) || ''
                  const postalCode = cityStage?.site.code_postal || ''
                  const citySlug = `${nearbyCity.city}-${postalCode}`

                  return (
                    <Link
                      key={nearbyCity.city}
                      href={`/stages-recuperation-points/${citySlug}`}
                      className="flex items-center gap-2 h-[35px] flex-shrink-0 no-underline hover:underline"
                      style={{
                        fontFamily: 'var(--font-poppins)',
                        color: '#BC4747',
                        fontSize: '15px',
                        fontWeight: 400,
                        lineHeight: '35px',
                        whiteSpace: 'nowrap'
                      }}
                    >
                      <span style={{
                        width: '4px',
                        height: '4px',
                        borderRadius: '50%',
                        backgroundColor: '#BC4747',
                        flexShrink: 0
                      }}></span>
                      <span>Stage {formattedCity} ({deptNumber})</span>
                    </Link>
                  )
                })}
              </div>

              {/* Column 3 */}
              <div className="flex flex-col gap-[8px]">
                {nearbyCities.slice(6, 9).map((nearbyCity) => {
                  const formattedCity = nearbyCity.city
                    .split('-')
                    .map(word => word.charAt(0) + word.slice(1).toLowerCase())
                    .join(' ')
                  const cityStage = allStages.find(s => s.site.ville === nearbyCity.city)
                  const deptNumber = cityStage?.site.code_postal.substring(0, 2) || ''
                  const postalCode = cityStage?.site.code_postal || ''
                  const citySlug = `${nearbyCity.city}-${postalCode}`

                  return (
                    <Link
                      key={nearbyCity.city}
                      href={`/stages-recuperation-points/${citySlug}`}
                      className="flex items-center gap-2 h-[35px] flex-shrink-0 no-underline hover:underline"
                      style={{
                        fontFamily: 'var(--font-poppins)',
                        color: '#BC4747',
                        fontSize: '15px',
                        fontWeight: 400,
                        lineHeight: '35px',
                        whiteSpace: 'nowrap'
                      }}
                    >
                      <span style={{
                        width: '4px',
                        height: '4px',
                        borderRadius: '50%',
                        backgroundColor: '#BC4747',
                        flexShrink: 0
                      }}></span>
                      <span>Stage {formattedCity} ({deptNumber})</span>
                    </Link>
                  )
                })}
              </div>
            </div>
          </section>
        )}
      </main>

      {/* Footer */}
      <footer className="bg-[#343435] py-6 mt-16 md:mt-32">
        <div className="max-w-7xl mx-auto px-4">
          <div className="flex flex-col md:flex-row items-center justify-center gap-3 md:gap-6 mb-3">
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

      {/* Stage Details Modal */}
      {selectedStage && (
        <StageDetailsModal
          stage={selectedStage}
          isOpen={isModalOpen}
          onClose={() => {
            setIsModalOpen(false)
            setSelectedStage(null)
          }}
          city={city}
          slug={fullSlug}
        />
      )}

      {/* Mobile Sticky Footer - Only shown on mobile */}
      <div className="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-40 px-4 py-3">
        {/* 3 Reassurance items */}
        <div className="flex items-center justify-center gap-4 mb-2">
          {/* +4 Pts */}
          <div className="flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 25 25" fill="none">
              <path d="M9.375 11.4583L12.5 14.5833L22.9167 4.16667M21.875 12.5V19.7917C21.875 20.3442 21.6555 20.8741 21.2648 21.2648C20.8741 21.6555 20.3442 21.875 19.7917 21.875H5.20833C4.6558 21.875 4.12589 21.6555 3.73519 21.2648C3.34449 20.8741 3.125 20.3442 3.125 19.7917V5.20833C3.125 4.6558 3.34449 4.12589 3.73519 3.73519C4.12589 3.34449 4.6558 3.125 5.20833 3.125H16.6667" stroke="#C4A226" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
            <span style={{ fontFamily: 'var(--font-poppins)', fontSize: '12px', fontWeight: 400, color: '#000' }}>+4 Pts</span>
          </div>
          {/* Agréés */}
          <div className="flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 25 25" fill="none">
              <path d="M9.375 11.4583L12.5 14.5833L22.9167 4.16667M21.875 12.5V19.7917C21.875 20.3442 21.6555 20.8741 21.2648 21.2648C20.8741 21.6555 20.3442 21.875 19.7917 21.875H5.20833C4.6558 21.875 4.12589 21.6555 3.73519 21.2648C3.34449 20.8741 3.125 20.3442 3.125 19.7917V5.20833C3.125 4.6558 3.34449 4.12589 3.73519 3.73519C4.12589 3.34449 4.6558 3.125 5.20833 3.125H16.6667" stroke="#C4A226" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
            <span style={{ fontFamily: 'var(--font-poppins)', fontSize: '12px', fontWeight: 400, color: '#000' }}>Agréés</span>
          </div>
          {/* Satisfait-Remboursé */}
          <div className="flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 25 25" fill="none">
              <path d="M9.375 11.4583L12.5 14.5833L22.9167 4.16667M21.875 12.5V19.7917C21.875 20.3442 21.6555 20.8741 21.2648 21.2648C20.8741 21.6555 20.3442 21.875 19.7917 21.875H5.20833C4.6558 21.875 4.12589 21.6555 3.73519 21.2648C3.34449 20.8741 3.125 20.3442 3.125 19.7917V5.20833C3.125 4.6558 3.34449 4.12589 3.73519 3.73519C4.12589 3.34449 4.6558 3.125 5.20833 3.125H16.6667" stroke="#C4A226" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
            <span style={{ fontFamily: 'var(--font-poppins)', fontSize: '12px', fontWeight: 400, color: '#000' }}>Satisfait-Remboursé</span>
          </div>
        </div>

        {/* Conditional button/link based on stages visibility */}
        {isStagesVisible ? (
          // Sticky (1): "+ Plus d'infos" link when viewing stages
          <div className="flex justify-center">
            <button
              onClick={() => setShowReassuranceModal(true)}
              style={{
                fontFamily: 'var(--font-poppins)',
                fontSize: '13px',
                fontWeight: 400,
                color: '#2563EB',
                background: 'none',
                border: 'none',
                cursor: 'pointer'
              }}
            >
              + Plus d'infos
            </button>
          </div>
        ) : (
          // Sticky (2): "Voir les stages" button when NOT viewing stages
          <div className="flex justify-center">
            <button
              onClick={() => {
                const stagesSection = document.querySelector('[data-stages-section]')
                if (stagesSection) {
                  stagesSection.scrollIntoView({ behavior: 'smooth', block: 'start' })
                }
              }}
              style={{
                display: 'flex',
                width: '200px',
                height: '40px',
                padding: '8px 24px',
                justifyContent: 'center',
                alignItems: 'center',
                borderRadius: '20px',
                background: '#41A334',
                color: '#FFF',
                fontFamily: 'var(--font-poppins)',
                fontSize: '14px',
                fontWeight: 500,
                border: 'none',
                cursor: 'pointer'
              }}
            >
              Voir les stages
            </button>
          </div>
        )}
      </div>

      {/* Reassurance Modal - Mobile only */}
      {showReassuranceModal && (
        <div
          className="md:hidden fixed inset-0 z-50"
          style={{
            backgroundColor: 'rgba(0, 0, 0, 0.5)'
          }}
          onClick={() => setShowReassuranceModal(false)}
        >
          <div
            className="fixed bottom-0 left-0 right-0 bg-white overflow-y-auto"
            style={{
              borderTopLeftRadius: '20px',
              borderTopRightRadius: '20px',
              maxHeight: '80vh',
              animation: 'slideUp 0.3s ease-out'
            }}
            onClick={e => e.stopPropagation()}
            onTouchStart={(e) => {
              const touch = e.touches[0]
              e.currentTarget.dataset.touchStartY = String(touch.clientY)
            }}
            onTouchMove={(e) => {
              const touch = e.touches[0]
              const startY = Number(e.currentTarget.dataset.touchStartY || 0)
              const deltaY = touch.clientY - startY
              // If swiping down from near the top of the modal
              if (deltaY > 80 && startY < 150) {
                setShowReassuranceModal(false)
              }
            }}
          >
            {/* Drag indicator */}
            <div className="flex justify-center pt-3 pb-2">
              <div style={{
                width: '40px',
                height: '4px',
                backgroundColor: '#666',
                borderRadius: '2px'
              }} />
            </div>

            {/* Close button - X without circle */}
            <button
              onClick={() => setShowReassuranceModal(false)}
              className="absolute top-3 right-3 z-10"
              style={{
                width: '28px',
                height: '28px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                background: 'none',
                border: 'none',
                cursor: 'pointer',
                padding: 0
              }}
              aria-label="Fermer"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M18 6L6 18M6 6L18 18" stroke="#666" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
            </button>

            {/* Content */}
            <div className="px-6 pb-8">
              <h2 className="text-center mb-6" style={{
                fontFamily: 'var(--font-poppins)',
                fontSize: '18px',
                fontWeight: 500,
                color: '#000'
              }}>
                Vos Garanties ProStagesPermis
              </h2>

              {/* Separator line */}
              <div style={{ width: '100%', height: '1px', background: '#E0E0E0', marginBottom: '24px' }} />

              {/* Guarantees list */}
              <div className="space-y-4">
                {[
                  'Stage agréé tout type de stage (volontaire et obligatoire)',
                  '+4 points en 48h',
                  'Aucun examen',
                  'Attestation officielle remise le 2ème jour',
                  'Report ou remboursement en cas d\'imprévu',
                  'Convocation envoyée immédiatement par email après inscription'
                ].map((guarantee, index) => (
                  <div key={index} className="flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 25 25" fill="none" className="flex-shrink-0 mt-0.5">
                      <path d="M9.375 11.4583L12.5 14.5833L22.9167 4.16667M21.875 12.5V19.7917C21.875 20.3442 21.6555 20.8741 21.2648 21.2648C20.8741 21.6555 20.3442 21.875 19.7917 21.875H5.20833C4.6558 21.875 4.12589 21.6555 3.73519 21.2648C3.34449 20.8741 3.125 20.3442 3.125 19.7917V5.20833C3.125 4.6558 3.34449 4.12589 3.73519 3.73519C4.12589 3.34449 4.6558 3.125 5.20833 3.125H16.6667" stroke="#C4A226" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                    <span style={{
                      fontFamily: 'var(--font-poppins)',
                      fontSize: '15px',
                      fontWeight: 400,
                      color: '#000',
                      lineHeight: '22px'
                    }}>
                      {guarantee}
                    </span>
                  </div>
                ))}
              </div>

              {/* Fermer button */}
              <div className="flex justify-center mt-8">
                <button
                  onClick={() => setShowReassuranceModal(false)}
                  style={{
                    display: 'flex',
                    height: '44px',
                    padding: '10px 32px',
                    justifyContent: 'center',
                    alignItems: 'center',
                    borderRadius: '12px',
                    background: '#E5E5E5',
                    color: '#000',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '15px',
                    fontWeight: 400,
                    border: 'none',
                    cursor: 'pointer'
                  }}
                >
                  Fermer
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* CSS for slide up animation */}
      <style jsx>{`
        @keyframes slideUp {
          from {
            transform: translateY(100%);
          }
          to {
            transform: translateY(0);
          }
        }
      `}</style>
    </div>
  )
}
