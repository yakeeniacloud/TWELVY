'use client'

import { useState, useRef, useEffect } from 'react'
import { useRouter } from 'next/navigation'
import { useCities } from '@/hooks/useCities'

interface CitySearchBarProps {
  placeholder?: string
  variant?: 'large' | 'small' | 'sidebar'
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
  const { cities } = useCities()

  // Filter cities based on query
  const filteredCities = query.length > 0
    ? cities.filter((city) =>
        city.toLowerCase().startsWith(query.toLowerCase())
      )
    : []

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
      // Fetch stage data to get postal code AND cache for results page
      fetch(`/api/stages/${cityToNavigate.toUpperCase()}`)
        .then(response => response.json())
        .then(data => {
          if (data.stages && data.stages.length > 0) {
            const postal = data.stages[0].site.code_postal
            const newUrl = `/stages-recuperation-points/${cityToNavigate.toUpperCase()}-${postal}`

            // CACHE the fetched data for instant display on results page
            try {
              sessionStorage.setItem(
                `stages_cache_${cityToNavigate.toUpperCase()}`,
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
            const newUrl = `/stages-recuperation-points/${cityToNavigate.toUpperCase()}-00000`
            if (onCitySelect) {
              onCitySelect(cityToNavigate)
            } else {
              router.push(newUrl)
            }
          }
        })
        .catch(() => {
          // Fallback on error
          const newUrl = `/stages-recuperation-points/${cityToNavigate.toUpperCase()}-00000`
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

  const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter') {
      e.preventDefault()
      if (selectedIndex >= 0 && selectedIndex < filteredCities.length) {
        handleSearch(filteredCities[selectedIndex])
      } else {
        handleSearch()
      }
    } else if (e.key === 'ArrowDown') {
      e.preventDefault()
      setSelectedIndex((prev) =>
        prev < filteredCities.length - 1 ? prev + 1 : prev
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

  return (
    <div className={`relative w-full ${isLarge ? 'max-w-[640px] mx-auto' : ''}`}>
      <form
        role="search"
        aria-label="Rechercher un stage par ville"
        onSubmit={(e) => {
          e.preventDefault()
          handleSearch()
        }}
        className="relative"
      >
        {isSidebar ? (
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
        {showSuggestions && filteredCities.length > 0 && (
          <div
            ref={suggestionsRef}
            className={`absolute top-full left-0 right-0 bg-white border border-gray-300 shadow-lg z-50 ${
              isSidebar ? 'border-t-0 rounded-b' : 'mt-1 border rounded-lg'
            }`}
            style={{ maxHeight: '300px', overflowY: 'auto' }}
          >
            {filteredCities.map((city, index) => (
              <button
                key={city}
                onClick={() => handleSearch(city)}
                className={`w-full text-left px-3 py-2 text-sm transition-colors ${
                  index === selectedIndex
                    ? 'bg-blue-100 text-blue-900'
                    : 'text-gray-700 hover:bg-gray-100'
                }`}
              >
                {city}
              </button>
            ))}
          </div>
        )}
      </form>
    </div>
  )
}
