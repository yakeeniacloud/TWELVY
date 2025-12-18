'use client'

import { useState, useEffect, useRef } from 'react'
import { useRouter } from 'next/navigation'

export default function Home() {
  const router = useRouter()
  const [searchQuery, setSearchQuery] = useState('')
  const [allCities, setAllCities] = useState<string[]>([])
  const [suggestions, setSuggestions] = useState<string[]>([])
  const [showSuggestions, setShowSuggestions] = useState(false)
  const searchRef = useRef<HTMLDivElement>(null)

  // Fetch all cities on mount
  useEffect(() => {
    async function fetchCities() {
      try {
        const response = await fetch('/api/cities')
        const data = await response.json()
        setAllCities(data.cities || [])
      } catch {
        setAllCities([])
      }
    }
    fetchCities()
  }, [])

  // Click outside to close suggestions
  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (searchRef.current && !searchRef.current.contains(event.target as Node)) {
        setShowSuggestions(false)
      }
    }
    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [])

  // Filter cities based on input
  const handleInputChange = (value: string) => {
    setSearchQuery(value)
    if (value.length >= 2) {
      const filtered = allCities
        .filter(city => city.toLowerCase().includes(value.toLowerCase()))
        .slice(0, 8)
      setSuggestions(filtered)
      setShowSuggestions(filtered.length > 0)
    } else {
      setSuggestions([])
      setShowSuggestions(false)
    }
  }

  const handleCitySelect = (city: string) => {
    setSearchQuery(city)
    setShowSuggestions(false)
    // Navigate to city page
    const slug = city.toUpperCase().replace(/ /g, '-')
    router.push(`/stages-recuperation-points/${slug}`)
  }

  const handleSearch = () => {
    if (searchQuery.trim()) {
      const slug = searchQuery.toUpperCase().replace(/ /g, '-')
      router.push(`/stages-recuperation-points/${slug}`)
    }
  }

  return (
    <div className="bg-white min-h-screen">
      {/* Hero Section */}
      <section className="pt-12 pb-8">
        <div className="max-w-4xl mx-auto px-4 text-center">
          {/* Main Title */}
          <h1 style={{
            fontFamily: 'var(--font-poppins)',
            color: '#000',
            textAlign: 'center',
            fontSize: '25px',
            fontStyle: 'normal',
            fontWeight: 400,
            lineHeight: '35px'
          }}>
            Tous les Stages de Récupération de Points au Meilleur Prix
          </h1>

          {/* Subtitle */}
          <p style={{
            fontFamily: 'var(--font-poppins)',
            width: '672px',
            height: '34px',
            flexShrink: 0,
            color: 'rgba(6, 6, 6, 0.86)',
            textAlign: 'center',
            fontSize: '17px',
            fontStyle: 'normal',
            fontWeight: 400,
            lineHeight: '28px',
            margin: '0 auto',
            marginTop: '8px'
          }}>
            Trouvez rapidement un stage près de chez vous et récupérez 4 points en 48h
          </p>

          {/* Stats line */}
          <p style={{
            fontFamily: 'var(--font-poppins)',
            width: '576px',
            height: '34px',
            color: 'rgba(52, 52, 52, 0.86)',
            textAlign: 'center',
            fontSize: '16px',
            fontStyle: 'italic',
            fontWeight: 400,
            lineHeight: '28px',
            margin: '0 auto',
            marginTop: '8px'
          }}>
            Plus de 857 000 conducteurs accompagnés depuis 2008
          </p>

          {/* Search Bar */}
          <div className="mt-8 flex justify-center">
            <div
              ref={searchRef}
              className="relative"
              style={{
                display: 'flex',
                width: '672px',
                height: '61px',
                padding: '1px 20px',
                alignItems: 'center',
                gap: '15px',
                flexShrink: 0,
                borderRadius: '20px',
                border: '1px solid #000',
                background: 'linear-gradient(0deg, rgba(255, 255, 255, 0.20) 0%, rgba(255, 255, 255, 0.20) 100%), #FFF'
              }}
            >
              {/* Search Icon */}
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" style={{ flexShrink: 0 }}>
                <path d="M15.75 15.75L12.4875 12.4875M14.25 8.25C14.25 11.5637 11.5637 14.25 8.25 14.25C4.93629 14.25 2.25 11.5637 2.25 8.25C2.25 4.93629 4.93629 2.25 8.25 2.25C11.5637 2.25 14.25 4.93629 14.25 8.25Z" stroke="#727171" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>

              {/* Input */}
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => handleInputChange(e.target.value)}
                onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                placeholder="Entrez votre ville ou code postal"
                style={{
                  flex: 1,
                  border: 'none',
                  outline: 'none',
                  background: 'transparent',
                  fontFamily: 'Inter, sans-serif',
                  color: searchQuery ? '#000' : '#949393',
                  fontSize: '17px',
                  fontStyle: 'normal',
                  fontWeight: 400,
                  lineHeight: '100%'
                }}
              />

              {/* Suggestions Dropdown */}
              {showSuggestions && suggestions.length > 0 && (
                <div className="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-300 rounded-lg shadow-lg z-50 max-h-60 overflow-y-auto">
                  {suggestions.map((city, index) => (
                    <button
                      key={index}
                      onClick={() => handleCitySelect(city)}
                      className="w-full px-4 py-3 text-left hover:bg-gray-100 text-sm"
                      style={{ fontFamily: 'var(--font-poppins)' }}
                    >
                      {city}
                    </button>
                  ))}
                </div>
              )}
            </div>
          </div>
        </div>
      </section>

      {/* 120px spacing */}
      <div style={{ height: '120px' }}></div>

      {/* Nos Engagements Section */}
      <section style={{ backgroundColor: '#EFEFEF', width: '100%', padding: '40px 0' }}>
        <div className="max-w-5xl mx-auto px-4">
          {/* Title with lines */}
          <div className="flex items-center justify-center gap-4 mb-10">
            <div style={{ flex: 1, height: '1px', backgroundColor: '#C4A574' }}></div>
            <h2 style={{ fontFamily: 'var(--font-poppins)', fontSize: '20px', fontWeight: 500 }}>
              <span style={{ color: '#000' }}>Nos </span>
              <span style={{ color: '#B22222' }}>Engagements</span>
            </h2>
            <div style={{ flex: 1, height: '1px', backgroundColor: '#C4A574' }}></div>
          </div>

          {/* 4 Benefits */}
          <div className="flex justify-center items-start gap-16">
            {/* Benefit 1: Stages Agréés Préfecture */}
            <div className="flex flex-col items-center text-center">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#C4A574" strokeWidth="1.5">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
              <p style={{ fontFamily: 'var(--font-poppins)', fontSize: '14px', color: '#333', marginTop: '12px', lineHeight: '1.4' }}>
                Stages Agréés<br/>Préfecture
              </p>
            </div>

            {/* Benefit 2: + 4 points en 48h */}
            <div className="flex flex-col items-center text-center">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#C4A574" strokeWidth="1.5">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 6v6l4 2" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
              <p style={{ fontFamily: 'var(--font-poppins)', fontSize: '14px', color: '#333', marginTop: '12px', lineHeight: '1.4' }}>
                + 4 points en 48h
              </p>
            </div>

            {/* Benefit 3: Prix le plus bas garanti */}
            <div className="flex flex-col items-center text-center">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#C4A574" strokeWidth="1.5">
                <path d="M23 6l-9.5 9.5-5-5L1 18" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M17 6h6v6" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
              <p style={{ fontFamily: 'var(--font-poppins)', fontSize: '14px', color: '#333', marginTop: '12px', lineHeight: '1.4' }}>
                Prix le plus bas<br/>garanti
              </p>
            </div>

            {/* Benefit 4: Report ou remboursement */}
            <div className="flex flex-col items-center text-center">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#C4A574" strokeWidth="1.5">
                <path d="M1 4v6h6" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
              <p style={{ fontFamily: 'var(--font-poppins)', fontSize: '14px', color: '#333', marginTop: '12px', lineHeight: '1.4' }}>
                Report ou<br/>remboursement
              </p>
            </div>
          </div>
        </div>
      </section>
    </div>
  )
}
