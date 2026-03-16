'use client'

import { useState, useRef, useEffect } from 'react'
import { useRouter } from 'next/navigation'
import { useCities } from '@/hooks/useCities'
import { CITY_POSTAL_MAP } from '@/lib/city-postal-map'
import { DEPARTEMENTS } from '@/lib/departements'
import { REGIONS } from '@/lib/regions'

// Format city name: MARSEILLE -> Marseille, AIX-EN-PROVENCE -> Aix-en-Provence
const formatCityDisplay = (city: string) => {
  return city
    .split('-')
    .map((word, index) => {
      // Keep lowercase for common French prepositions (en, de, du, la, le, les, sur, sous)
      const lowerWord = word.toLowerCase()
      if (index > 0 && ['en', 'de', 'du', 'la', 'le', 'les', 'sur', 'sous'].includes(lowerWord)) {
        return lowerWord
      }
      return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
    })
    .join('-')
}

// Get department code from postal code
// Handles: standard (75001→75), Corse (20000-20190→2A, 20200-20290→2B), DOM-TOM (97100→971)
function getDeptFromPostal(postal: string): string {
  if (!postal) return ''
  const prefix2 = postal.substring(0, 2)
  const prefix3 = postal.substring(0, 3)

  // DOM-TOM: 971 (Guadeloupe), 972 (Martinique), 973 (Guyane), 974 (Réunion), 976 (Mayotte)
  if (prefix2 === '97') return prefix3

  // Corse: 20000-20190 → 2A (Corse-du-Sud), 20200-20290 → 2B (Haute-Corse)
  if (prefix2 === '20') {
    const num = parseInt(postal, 10)
    return num < 20200 ? '2A' : '2B'
  }

  return prefix2
}

// Normalize string for accent-insensitive matching
function normalizeForSearch(s: string): string {
  return s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
}

type Suggestion =
  | { type: 'city'; city: string }
  | { type: 'dept'; slug: string; name: string; code: string }
  | { type: 'region'; slug: string; name: string }

interface CitySearchBarProps {
  placeholder?: string
  variant?: 'large' | 'small' | 'sidebar' | 'filter'
  onCitySelect?: (city: string) => void
}

export default function CitySearchBar({
  placeholder = 'Ville ou CP',
  variant = 'large',
  onCitySelect,
}: CitySearchBarProps) {
  const [query, setQuery] = useState('')
  const [showSuggestions, setShowSuggestions] = useState(false)
  const [selectedIndex, setSelectedIndex] = useState(-1)
  const inputRef = useRef<HTMLInputElement>(null)
  const suggestionsRef = useRef<HTMLDivElement>(null)
  const router = useRouter()
  const { cities, cityPostalMap } = useCities()

  // Get department code: try API postal map first, fall back to static map
  const getDeptCode = (city: string) => {
    const cityUpper = city.toUpperCase()
    const postal = cityPostalMap[cityUpper] || CITY_POSTAL_MAP[cityUpper]
    return postal ? getDeptFromPostal(postal) : ''
  }

  // Filter cities based on query (max 6)
  const filteredCities = query.length > 0
    ? cities.filter((city) =>
        city.toLowerCase().startsWith(query.toLowerCase())
      ).slice(0, 6)
    : []

  // Filter depts and regions (accent-insensitive, max 3 + 2)
  const normalizedQuery = query.length > 0 ? normalizeForSearch(query) : ''
  const filteredDepts = normalizedQuery
    ? DEPARTEMENTS.filter(d =>
        normalizeForSearch(d.name).startsWith(normalizedQuery) ||
        d.slug.startsWith(normalizedQuery) ||
        d.code.toLowerCase().startsWith(normalizedQuery)
      ).slice(0, 3)
    : []
  const filteredRegions = normalizedQuery
    ? REGIONS.filter(r =>
        normalizeForSearch(r.name).startsWith(normalizedQuery) ||
        r.slug.startsWith(normalizedQuery)
      ).slice(0, 2)
    : []

  // Combined suggestions for keyboard navigation
  const allSuggestions: Suggestion[] = [
    ...filteredCities.map(city => ({ type: 'city' as const, city })),
    ...filteredDepts.map(d => ({ type: 'dept' as const, slug: d.slug, name: d.name, code: d.code })),
    ...filteredRegions.map(r => ({ type: 'region' as const, slug: r.slug, name: r.name })),
  ]

  const handleSearch = async (selectedCity?: string) => {
    const cityToSearch = selectedCity || query
    if (!cityToSearch.trim()) return

    // Find exact match or use first filtered city
    const city = cities.find(
      (c) => c.toLowerCase() === cityToSearch.toLowerCase()
    ) || filteredCities[0]

    // If no city found in list, use typed value anyway (fallback)
    const cityToNavigate = city || cityToSearch

    if (cityToNavigate) {
      const cityUpper = cityToNavigate.toUpperCase()

      // TRY POSTAL MAP FIRST for instant navigation (API map covers all cities, static map as fallback)
      const postalFromMap = cityPostalMap[cityUpper] || CITY_POSTAL_MAP[cityUpper]

      if (postalFromMap) {
        // ⚡ INSTANT NAVIGATION - no API call needed!
        console.log(`⚡ Using postal map: ${cityUpper} -> ${postalFromMap}`)
        const newUrl = `/stages-recuperation-points/${cityUpper}-${postalFromMap}`

        // Prefetch data in background for instant display on results page
        fetch(`/api/stages/${cityUpper}`)
          .then(response => response.json())
          .then(data => {
            try {
              sessionStorage.setItem(
                `stages_cache_${cityUpper}`,
                JSON.stringify({
                  data: data,
                  timestamp: Date.now()
                })
              )
              console.log('✅ Cached stage data in background')
            } catch (e) {
              console.warn('⚠️ Failed to cache:', e)
            }
          })
          .catch(err => console.warn('⚠️ Background fetch failed:', err))

        if (onCitySelect) {
          onCitySelect(cityToNavigate)
        } else {
          router.push(newUrl)
        }

        setQuery('')
        setShowSuggestions(false)
        return
      }

      // FALLBACK: City not in map - fetch from API (slower but works for all cities)
      console.log(`📡 City not in postal map, fetching from API: ${cityUpper}`)
      fetch(`/api/stages/${cityUpper}`)
        .then(response => response.json())
        .then(data => {
          if (data.stages && data.stages.length > 0) {
            const postal = data.stages[0].site.code_postal
            const newUrl = `/stages-recuperation-points/${cityUpper}-${postal}`

            // CACHE the fetched data for instant display on results page
            try {
              sessionStorage.setItem(
                `stages_cache_${cityUpper}`,
                JSON.stringify({
                  data: data,
                  timestamp: Date.now()
                })
              )
              console.log('✅ Cached stage data for instant display')
            } catch (e) {
              console.warn('⚠️ Failed to cache stage data:', e)
            }

            if (onCitySelect) {
              onCitySelect(cityToNavigate)
            } else {
              router.push(newUrl)
            }
          } else {
            // Fallback if no stages found
            const newUrl = `/stages-recuperation-points/${cityUpper}-00000`
            if (onCitySelect) {
              onCitySelect(cityToNavigate)
            } else {
              router.push(newUrl)
            }
          }
        })
        .catch(() => {
          // Fallback on error
          const newUrl = `/stages-recuperation-points/${cityUpper}-00000`
          if (onCitySelect) {
            onCitySelect(cityToNavigate)
          } else {
            router.push(newUrl)
          }
        })

      setQuery('')
      setShowSuggestions(false)
    }
  }

  const handleSuggestionClick = (suggestion: Suggestion) => {
    if (suggestion.type === 'city') {
      handleSearch(suggestion.city)
    } else if (suggestion.type === 'dept') {
      router.push(`/stages-recuperation-points/departement/${suggestion.slug}`)
      setQuery('')
      setShowSuggestions(false)
    } else {
      router.push(`/stages-recuperation-points/region/${suggestion.slug}`)
      setQuery('')
      setShowSuggestions(false)
    }
  }

  const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter') {
      e.preventDefault()
      if (selectedIndex >= 0 && selectedIndex < allSuggestions.length) {
        handleSuggestionClick(allSuggestions[selectedIndex])
      } else {
        handleSearch()
      }
    } else if (e.key === 'ArrowDown') {
      e.preventDefault()
      setSelectedIndex((prev) =>
        prev < allSuggestions.length - 1 ? prev + 1 : prev
      )
    } else if (e.key === 'ArrowUp') {
      e.preventDefault()
      setSelectedIndex((prev) => (prev > 0 ? prev - 1 : -1))
    } else if (e.key === 'Escape') {
      setShowSuggestions(false)
      setSelectedIndex(-1)
    }
  }

  // Close suggestions when clicking outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (
        suggestionsRef.current &&
        !suggestionsRef.current.contains(event.target as Node) &&
        inputRef.current &&
        !inputRef.current.contains(event.target as Node)
      ) {
        setShowSuggestions(false)
      }
    }

    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [])

  const isLarge = variant === 'large'
  const isSidebar = variant === 'sidebar'
  const isFilter = variant === 'filter'

  return (
    <div className={`relative ${isFilter ? '' : 'w-full'} ${isLarge ? 'max-w-[640px] mx-auto' : ''}`}>
      <form
        role="search"
        aria-label="Rechercher un stage par ville"
        onSubmit={(e) => {
          e.preventDefault()
          handleSearch()
        }}
        className="relative"
      >
        {isFilter ? (
          // Filter variant - mobile header search bar specs (white background for desktop)
          <div
            className="flex items-center gap-3.5"
            style={{
              width: '100%',
              height: '44px',
              padding: '1px 20px',
              borderRadius: '20px',
              border: '1px solid #000',
              background: '#FFF',
            }}
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none" className="flex-shrink-0">
              <path d="M14 14L11.1 11.1M12.6667 7.33333C12.6667 10.2789 10.2789 12.6667 7.33333 12.6667C4.38781 12.6667 2 10.2789 2 7.33333C2 4.38781 4.38781 2 7.33333 2C10.2789 2 12.6667 4.38781 12.6667 7.33333Z" stroke="#1E1E1E" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
            <input
              ref={inputRef}
              type="text"
              value={query}
              onChange={(e) => {
                setQuery(e.target.value)
                setShowSuggestions(true)
                setSelectedIndex(-1)
              }}
              onFocus={() => setShowSuggestions(true)}
              onKeyDown={handleKeyDown}
              placeholder={placeholder}
              className="flex-1 bg-transparent border-none outline-none text-sm placeholder:text-gray-400"
              style={{ minWidth: '0' }}
            />
          </div>
        ) : isSidebar ? (
          // Sidebar variant - just input field, no button
          <input
            ref={inputRef}
            type="text"
            value={query}
            onChange={(e) => {
              setQuery(e.target.value)
              setShowSuggestions(true)
              setSelectedIndex(-1)
            }}
            onFocus={() => setShowSuggestions(true)}
            onKeyDown={handleKeyDown}
            placeholder={placeholder}
            className="w-full px-3 py-2 border border-gray-300 rounded text-sm"
          />
        ) : (
          <div className="flex gap-3">
            {/* Input */}
            <input
              ref={inputRef}
              type="text"
              value={query}
              onChange={(e) => {
                setQuery(e.target.value)
                setShowSuggestions(true)
                setSelectedIndex(-1)
              }}
              onFocus={() => setShowSuggestions(true)}
              onKeyDown={handleKeyDown}
              placeholder={placeholder}
              className={`flex-1 px-4 border-0 outline-none text-gray-900 placeholder-gray-500 transition-all ${
                isLarge
                  ? 'h-14 text-base rounded-lg'
                  : 'h-10 text-sm rounded'
              }`}
              style={{ background: '#ffffff' }}
            />

            {/* Search Button */}
            <button
              type="submit"
              className={`bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold transition-all rounded-lg flex items-center justify-center ${
                isLarge
                  ? 'px-8 h-14 text-base'
                  : 'px-4 h-10 text-sm'
              }`}
            >
              <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              {isLarge ? 'Rechercher' : 'OK'}
            </button>
          </div>
        )}

        {/* Suggestions Dropdown */}
        {showSuggestions && allSuggestions.length > 0 && (
          <div
            ref={suggestionsRef}
            className={`absolute top-full left-0 right-0 bg-white border border-gray-300 shadow-lg z-50 ${
              isSidebar ? 'border-t-0 rounded-b' : isFilter ? 'mt-1 rounded-lg' : 'mt-1 border rounded-lg'
            }`}
            style={{ maxHeight: '300px', overflowY: 'auto' }}
          >
            {allSuggestions.map((suggestion, index) => {
              const isSelected = index === selectedIndex
              const baseClass = `w-full text-left px-3 py-2 text-sm transition-colors flex items-center justify-between ${
                isSelected ? 'bg-blue-100 text-blue-900' : 'text-gray-700 hover:bg-gray-100'
              }`
              if (suggestion.type === 'city') {
                const deptCode = getDeptCode(suggestion.city)
                return (
                  <button key={`city-${suggestion.city}`} onClick={() => handleSuggestionClick(suggestion)} className={baseClass}>
                    <span>{formatCityDisplay(suggestion.city)}{deptCode ? ` (${deptCode})` : ''}</span>
                  </button>
                )
              } else if (suggestion.type === 'dept') {
                return (
                  <button key={`dept-${suggestion.slug}`} onClick={() => handleSuggestionClick(suggestion)} className={baseClass}>
                    <span>{suggestion.name} ({suggestion.code})</span>
                    <span className="text-xs text-gray-400 ml-2 flex-shrink-0">Département</span>
                  </button>
                )
              } else {
                return (
                  <button key={`region-${suggestion.slug}`} onClick={() => handleSuggestionClick(suggestion)} className={baseClass}>
                    <span>{suggestion.name}</span>
                    <span className="text-xs text-gray-400 ml-2 flex-shrink-0">Région</span>
                  </button>
                )
              }
            })}
          </div>
        )}
      </form>
    </div>
  )
}
