'use client'

import { useState, useEffect, useRef } from 'react'
import { useRouter } from 'next/navigation'

export default function Home() {
  const router = useRouter()
  const [searchQuery, setSearchQuery] = useState('')
  const [allCities, setAllCities] = useState<string[]>([])
  const [suggestions, setSuggestions] = useState<string[]>([])
  const [showSuggestions, setShowSuggestions] = useState(false)
  const [openFaqIndex, setOpenFaqIndex] = useState<number | null>(null)
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false)
  const [showStickySearch, setShowStickySearch] = useState(false)
  const searchRef = useRef<HTMLDivElement>(null)
  const heroSearchRef = useRef<HTMLDivElement>(null)

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
      if (heroSearchRef.current && !heroSearchRef.current.contains(event.target as Node)) {
        setShowSuggestions(false)
      }
    }
    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [])

  // Sticky search bar on scroll (mobile only)
  useEffect(() => {
    let ticking = false

    function handleScroll() {
      if (!ticking) {
        window.requestAnimationFrame(() => {
          if (heroSearchRef.current) {
            const rect = heroSearchRef.current.getBoundingClientRect()
            // Show sticky when the original search bar is completely above the viewport
            // Use a small threshold to account for rounding errors
            setShowStickySearch(rect.bottom < -10)
          }
          ticking = false
        })
        ticking = true
      }
    }

    // Run on mount and ONLY on scroll (not on resize)
    // Resize events from Chrome address bar hiding should NOT affect visibility
    handleScroll()
    window.addEventListener('scroll', handleScroll, { passive: true })
    return () => {
      window.removeEventListener('scroll', handleScroll)
    }
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

  const scrollToSearch = () => {
    heroSearchRef.current?.scrollIntoView({ behavior: 'smooth', block: 'center' })
  }

  return (
    <div className="bg-white min-h-screen">
      {/* DESKTOP VERSION - Hidden on mobile */}
      <div className="hidden md:block">
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

      {/* Spacing */}
      <div style={{ height: '60px' }}></div>

      {/* Nos Engagements Section */}
      <section style={{
        display: 'flex',
        width: '100%',
        height: '250px',
        padding: '10px',
        flexDirection: 'column',
        justifyContent: 'center',
        alignItems: 'center',
        backgroundColor: '#EFEFEF'
      }}>
        {/* Title: Nos Engagements */}
        <div className="flex items-center justify-center">
          <span style={{
            display: 'flex',
            width: '50px',
            height: '36px',
            flexDirection: 'column',
            justifyContent: 'center',
            flexShrink: 0,
            color: '#000',
            textAlign: 'center',
            fontFamily: 'var(--font-poppins)',
            fontSize: '23px',
            fontStyle: 'normal',
            fontWeight: 400,
            lineHeight: 'normal'
          }}>Nos</span>
          <span style={{
            display: 'flex',
            width: '168px',
            height: '35px',
            flexDirection: 'column',
            justifyContent: 'center',
            flexShrink: 0,
            color: '#BC4747',
            textAlign: 'center',
            fontFamily: 'var(--font-poppins)',
            fontSize: '23px',
            fontStyle: 'normal',
            fontWeight: 400,
            lineHeight: 'normal'
          }}>Engagements</span>
        </div>

        {/* Separator Line */}
        <div style={{
          display: 'flex',
          width: '973px',
          padding: '10px',
          justifyContent: 'center',
          alignItems: 'center',
          gap: '10px'
        }}>
          <div style={{ flex: 1, height: '1px', backgroundColor: '#B9B9B9' }}></div>
        </div>

        {/* 4 Benefits Row */}
        <div className="flex justify-center items-start gap-16 mt-4">
          {/* Benefit 1: Stages Agréés Préfecture */}
          <div className="flex flex-col items-center text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40" fill="none" style={{ flexShrink: 0 }}>
              <path d="M20 36.6667C20 36.6667 33.3333 30 33.3333 20V8.33337L20 3.33337L6.66666 8.33337V20C6.66666 30 20 36.6667 20 36.6667Z" stroke="#C4A226" strokeOpacity="0.96" strokeWidth="3.5" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
            <p style={{
              display: 'flex',
              height: '51px',
              flexDirection: 'column',
              justifyContent: 'center',
              flexShrink: 0,
              alignSelf: 'stretch',
              color: 'rgba(6, 6, 6, 0.84)',
              textAlign: 'center',
              fontFamily: 'var(--font-poppins)',
              fontSize: '18px',
              fontStyle: 'normal',
              fontWeight: 400,
              lineHeight: '25px'
            }}>
              Stages Agréés<br/>Préfecture
            </p>
          </div>

          {/* Benefit 2: + 4 points en 48h */}
          <div className="flex flex-col items-center text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40" fill="none" style={{ flexShrink: 0 }}>
              <path d="M20 10V20L26.6666 23.3334M36.6666 20C36.6666 29.2048 29.2047 36.6667 20 36.6667C10.7952 36.6667 3.33331 29.2048 3.33331 20C3.33331 10.7953 10.7952 3.33337 20 3.33337C29.2047 3.33337 36.6666 10.7953 36.6666 20Z" stroke="#C4A226" strokeWidth="3.5" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
            <p style={{
              display: 'flex',
              height: '35px',
              flexDirection: 'column',
              justifyContent: 'center',
              flexShrink: 0,
              alignSelf: 'stretch',
              color: 'rgba(6, 6, 6, 0.84)',
              textAlign: 'center',
              fontFamily: 'var(--font-poppins)',
              fontSize: '18px',
              fontStyle: 'normal',
              fontWeight: 400,
              lineHeight: '35px'
            }}>
              + 4 points en 48h
            </p>
          </div>

          {/* Benefit 3: Prix le plus bas garanti */}
          <div className="flex flex-col items-center text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40" fill="none" style={{ flexShrink: 0 }}>
              <g clipPath="url(#clip0_prix)">
                <path d="M38.3334 30L22.5 14.1667L14.1667 22.5L1.66669 10M38.3334 30H28.3334M38.3334 30V20" stroke="#C4A226" strokeWidth="4" strokeLinecap="round" strokeLinejoin="round"/>
              </g>
              <defs>
                <clipPath id="clip0_prix">
                  <rect width="40" height="40" fill="white"/>
                </clipPath>
              </defs>
            </svg>
            <p style={{
              display: 'flex',
              height: '51px',
              flexDirection: 'column',
              justifyContent: 'center',
              flexShrink: 0,
              alignSelf: 'stretch',
              color: 'rgba(6, 6, 6, 0.84)',
              textAlign: 'center',
              fontFamily: 'var(--font-poppins)',
              fontSize: '18px',
              fontStyle: 'normal',
              fontWeight: 400,
              lineHeight: '25px'
            }}>
              Prix le plus bas<br/>garanti
            </p>
          </div>

          {/* Benefit 4: Report ou remboursement */}
          <div className="flex flex-col items-center text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40" fill="none" style={{ flexShrink: 0 }}>
              <g clipPath="url(#clip0_report)">
                <path d="M1.66663 6.66662V16.6666M1.66663 16.6666H11.6666M1.66663 16.6666L9.39996 9.39996C11.7015 7.10232 14.6874 5.61491 17.9078 5.16182C21.1281 4.70873 24.4085 5.31451 27.2547 6.8879C30.1008 8.46128 32.3586 10.917 33.6877 13.8851C35.0168 16.8532 35.3453 20.1729 34.6237 23.3439C33.902 26.5149 32.1694 29.3655 29.6868 31.4662C27.2043 33.5669 24.1062 34.8039 20.8595 34.9907C17.6128 35.1776 14.3933 34.3042 11.6861 32.5022C8.97885 30.7003 6.93062 28.0673 5.84996 25" stroke="#C4A226" strokeWidth="4" strokeLinecap="round" strokeLinejoin="round"/>
              </g>
              <defs>
                <clipPath id="clip0_report">
                  <rect width="40" height="40" fill="white"/>
                </clipPath>
              </defs>
            </svg>
            <p style={{
              display: 'flex',
              height: '51px',
              flexDirection: 'column',
              justifyContent: 'center',
              flexShrink: 0,
              alignSelf: 'stretch',
              color: 'rgba(6, 6, 6, 0.84)',
              textAlign: 'center',
              fontFamily: 'var(--font-poppins)',
              fontSize: '18px',
              fontStyle: 'normal',
              fontWeight: 400,
              lineHeight: '25px'
            }}>
              Report ou<br/>remboursement
            </p>
          </div>
        </div>
      </section>

      {/* 50px spacing */}
      <div style={{ height: '50px' }}></div>

      {/* Prochains stages Section */}
      <section className="flex flex-col items-center">
        {/* Title: Prochains stages proches de chez vous */}
        <div className="flex items-center justify-center">
          <span style={{
            display: 'flex',
            width: '201px',
            height: '42px',
            flexDirection: 'column',
            justifyContent: 'center',
            color: 'rgba(6, 6, 6, 0.86)',
            textAlign: 'center',
            WebkitTextStrokeWidth: '1px',
            WebkitTextStrokeColor: '#000',
            fontFamily: 'var(--font-poppins)',
            fontSize: '23px',
            fontStyle: 'normal',
            fontWeight: 250,
            lineHeight: '35px'
          }}>Prochains stages</span>
          <span style={{
            width: '261px',
            color: 'rgba(6, 6, 6, 0.86)',
            textAlign: 'center',
            WebkitTextStrokeWidth: '1px',
            WebkitTextStrokeColor: 'rgba(188, 71, 71, 0.73)',
            fontFamily: 'var(--font-poppins)',
            fontSize: '23px',
            fontStyle: 'normal',
            fontWeight: 275,
            lineHeight: '35px'
          }}>proches de chez vous</span>
        </div>

        {/* 35px spacing */}
        <div style={{ height: '35px' }}></div>

        {/* 4 Widgets Row */}
        <div className="flex justify-center items-start" style={{ gap: '40px' }}>
          {/* Widget 1 - Marseille */}
          <div className="flex flex-col items-center">
            <div style={{
              width: '176px',
              height: '176px',
              flexShrink: 0,
              aspectRatio: '1/1',
              borderRadius: '15px',
              background: 'url(/widget.png) lightgray 50% / cover no-repeat'
            }}></div>
            <p style={{
              width: '199px',
              color: '#000',
              textAlign: 'center',
              fontFamily: 'var(--font-poppins)',
              fontSize: '17px',
              fontStyle: 'normal',
              fontWeight: 400,
              lineHeight: '35px',
              marginTop: '8px'
            }}>Vend 5 et sam 6 déc</p>
            <p style={{
              width: '202px',
              flexShrink: 0,
              color: '#6A6969',
              textAlign: 'center',
              fontFamily: 'var(--font-poppins)',
              fontSize: '16px',
              fontStyle: 'normal',
              fontWeight: 400,
              lineHeight: '20px'
            }}>Marseille</p>
            <p style={{
              width: '202px',
              flexShrink: 0,
              color: '#BC4747',
              textAlign: 'center',
              fontFamily: 'var(--font-poppins)',
              fontSize: '17px',
              fontStyle: 'normal',
              fontWeight: 400,
              lineHeight: '28px'
            }}>199 €</p>
            <button
              onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
              style={{
                display: 'flex',
                width: '103px',
                height: '31px',
                justifyContent: 'center',
                alignItems: 'center',
                gap: '10px',
                flexShrink: 0,
                borderRadius: '12px',
                background: '#41A334',
                border: 'none',
                cursor: 'pointer',
                marginTop: '8px'
              }}>
              <span style={{
                width: '83px',
                height: '20px',
                flexShrink: 0,
                color: '#FFF',
                fontFamily: 'var(--font-poppins)',
                fontSize: '11px',
                fontStyle: 'normal',
                fontWeight: 400,
                lineHeight: 'normal',
                letterSpacing: '0.77px'
              }}>Voir ce stage</span>
            </button>
          </div>

          {/* Widget 2 - Toulon */}
          <div className="flex flex-col items-center">
            <div style={{
              width: '176px',
              height: '176px',
              flexShrink: 0,
              aspectRatio: '1/1',
              borderRadius: '15px',
              background: 'url(/widget.png) lightgray 50% / cover no-repeat'
            }}></div>
            <p style={{
              width: '199px',
              color: '#000',
              textAlign: 'center',
              fontFamily: 'var(--font-poppins)',
              fontSize: '17px',
              fontStyle: 'normal',
              fontWeight: 400,
              lineHeight: '35px',
              marginTop: '8px'
            }}>Sam 7 et dim 8 déc</p>
            <p style={{
              width: '202px',
              flexShrink: 0,
              color: '#6A6969',
              textAlign: 'center',
              fontFamily: 'var(--font-poppins)',
              fontSize: '16px',
              fontStyle: 'normal',
              fontWeight: 400,
              lineHeight: '20px'
            }}>Toulon</p>
            <p style={{
              width: '202px',
              flexShrink: 0,
              color: '#BC4747',
              textAlign: 'center',
              fontFamily: 'var(--font-poppins)',
              fontSize: '17px',
              fontStyle: 'normal',
              fontWeight: 400,
              lineHeight: '28px'
            }}>219 €</p>
            <button
              onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
              style={{
                display: 'flex',
                width: '103px',
                height: '31px',
                justifyContent: 'center',
                alignItems: 'center',
                gap: '10px',
                flexShrink: 0,
                borderRadius: '12px',
                background: '#41A334',
                border: 'none',
                cursor: 'pointer',
                marginTop: '8px'
              }}>
              <span style={{
                width: '83px',
                height: '20px',
                flexShrink: 0,
                color: '#FFF',
                fontFamily: 'var(--font-poppins)',
                fontSize: '11px',
                fontStyle: 'normal',
                fontWeight: 400,
                lineHeight: 'normal',
                letterSpacing: '0.77px'
              }}>Voir ce stage</span>
            </button>
          </div>

          {/* Widget 3 - Nice */}
          <div className="flex flex-col items-center">
            <div style={{
              width: '176px',
              height: '176px',
              flexShrink: 0,
              aspectRatio: '1/1',
              borderRadius: '15px',
              background: 'url(/widget.png) lightgray 50% / cover no-repeat'
            }}></div>
            <p style={{
              width: '199px',
              color: '#000',
              textAlign: 'center',
              fontFamily: 'var(--font-poppins)',
              fontSize: '17px',
              fontStyle: 'normal',
              fontWeight: 400,
              lineHeight: '35px',
              marginTop: '8px'
            }}>Vend 12 et sam 13 déc</p>
            <p style={{
              width: '202px',
              flexShrink: 0,
              color: '#6A6969',
              textAlign: 'center',
              fontFamily: 'var(--font-poppins)',
              fontSize: '16px',
              fontStyle: 'normal',
              fontWeight: 400,
              lineHeight: '20px'
            }}>Nice</p>
            <p style={{
              width: '202px',
              flexShrink: 0,
              color: '#BC4747',
              textAlign: 'center',
              fontFamily: 'var(--font-poppins)',
              fontSize: '17px',
              fontStyle: 'normal',
              fontWeight: 400,
              lineHeight: '28px'
            }}>189 €</p>
            <button
              onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
              style={{
                display: 'flex',
                width: '103px',
                height: '31px',
                justifyContent: 'center',
                alignItems: 'center',
                gap: '10px',
                flexShrink: 0,
                borderRadius: '12px',
                background: '#41A334',
                border: 'none',
                cursor: 'pointer',
                marginTop: '8px'
              }}>
              <span style={{
                width: '83px',
                height: '20px',
                flexShrink: 0,
                color: '#FFF',
                fontFamily: 'var(--font-poppins)',
                fontSize: '11px',
                fontStyle: 'normal',
                fontWeight: 400,
                lineHeight: 'normal',
                letterSpacing: '0.77px'
              }}>Voir ce stage</span>
            </button>
          </div>

          {/* Widget 4 - Lyon */}
          <div className="flex flex-col items-center">
            <div style={{
              width: '176px',
              height: '176px',
              flexShrink: 0,
              aspectRatio: '1/1',
              borderRadius: '15px',
              background: 'url(/widget.png) lightgray 50% / cover no-repeat'
            }}></div>
            <p style={{
              width: '199px',
              color: '#000',
              textAlign: 'center',
              fontFamily: 'var(--font-poppins)',
              fontSize: '17px',
              fontStyle: 'normal',
              fontWeight: 400,
              lineHeight: '35px',
              marginTop: '8px'
            }}>Sam 14 et dim 15 déc</p>
            <p style={{
              width: '202px',
              flexShrink: 0,
              color: '#6A6969',
              textAlign: 'center',
              fontFamily: 'var(--font-poppins)',
              fontSize: '16px',
              fontStyle: 'normal',
              fontWeight: 400,
              lineHeight: '20px'
            }}>Lyon</p>
            <p style={{
              width: '202px',
              flexShrink: 0,
              color: '#BC4747',
              textAlign: 'center',
              fontFamily: 'var(--font-poppins)',
              fontSize: '17px',
              fontStyle: 'normal',
              fontWeight: 400,
              lineHeight: '28px'
            }}>209 €</p>
            <button
              onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
              style={{
                display: 'flex',
                width: '103px',
                height: '31px',
                justifyContent: 'center',
                alignItems: 'center',
                gap: '10px',
                flexShrink: 0,
                borderRadius: '12px',
                background: '#41A334',
                border: 'none',
                cursor: 'pointer',
                marginTop: '8px'
              }}>
              <span style={{
                width: '83px',
                height: '20px',
                flexShrink: 0,
                color: '#FFF',
                fontFamily: 'var(--font-poppins)',
                fontSize: '11px',
                fontStyle: 'normal',
                fontWeight: 400,
                lineHeight: 'normal',
                letterSpacing: '0.77px'
              }}>Voir ce stage</span>
            </button>
          </div>
        </div>
      </section>

      {/* 100px spacing */}
      <div style={{ height: '100px' }}></div>

      {/* Benefit Box 2 - Pourquoi réserver */}
      <section className="flex justify-center">
        <img
          src="/benefitboxv2.png"
          alt="Pourquoi réserver votre stage chez ProStagesPermis"
          style={{ maxWidth: '746px', height: 'auto' }}
        />
      </section>

      {/* 100px spacing */}
      <div style={{ height: '100px' }}></div>

      {/* Europe 1 Recommendation Section */}
      <section className="flex items-center justify-center">
        {/* Left: Recommandé par Europe 1 with subtitle */}
        <div className="flex flex-col items-center justify-center">
          <h3 className="text-center" style={{
            fontFamily: 'var(--font-poppins)',
            color: '#2C2C2C',
            fontSize: '20px',
            fontWeight: 500,
            lineHeight: '20px',
            letterSpacing: '1.4px',
            width: '176px',
            height: '77px',
            flexShrink: 0
          }}>
            Recommandé par Europe 1
          </h3>
          <p className="text-center mt-2" style={{
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
        </div>

        {/* Vertical Line */}
        <div style={{
          width: '1px',
          height: '172.502px',
          background: '#000',
          marginRight: '35px'
        }}></div>

        {/* Right: Europe 1 Logo */}
        <div className="flex flex-col items-center justify-center gap-4">
          <img
            src="/europe1-logo.png"
            alt="Europe 1"
            width={200}
            height={100}
            className="w-auto h-20"
          />
          <a href="https://www.youtube.com/watch?v=z1AsmdcGTaw" target="_blank" rel="noopener noreferrer"
             className="text-red-700 hover:underline" style={{
            fontFamily: 'var(--font-poppins)',
            fontSize: '15px',
            fontWeight: 400
          }}>
            Écouter l'extrait
          </a>
        </div>
      </section>

      {/* 100px spacing */}
      <div style={{ height: '100px' }}></div>

      {/* Dans quelle situation êtes-vous Section */}
      <section className="flex flex-col items-center">
        {/* Title: Dans quelle situation êtes-vous */}
        <div className="flex items-center justify-center">
          <span style={{
            display: 'flex',
            width: '241px',
            height: '42px',
            flexDirection: 'column',
            justifyContent: 'center',
            flexShrink: 0,
            color: 'rgba(6, 6, 6, 0.86)',
            textAlign: 'center',
            WebkitTextStrokeWidth: '1px',
            WebkitTextStrokeColor: '#000',
            fontFamily: 'var(--font-poppins)',
            fontSize: '23px',
            fontStyle: 'normal',
            fontWeight: 250,
            lineHeight: '35px'
          }}>Dans quelle situation</span>
          <span style={{ width: '8px' }}></span>
          <span style={{
            width: '120px',
            flexShrink: 0,
            color: 'rgba(6, 6, 6, 0.86)',
            textAlign: 'center',
            WebkitTextStrokeWidth: '1px',
            WebkitTextStrokeColor: 'rgba(188, 71, 71, 0.73)',
            fontFamily: 'var(--font-poppins)',
            fontSize: '23px',
            fontStyle: 'normal',
            fontWeight: 275,
            lineHeight: '35px'
          }}>êtes-vous</span>
        </div>

        {/* 25px spacing */}
        <div style={{ height: '25px' }}></div>

        {/* Widgets Grid */}
        <div className="flex flex-col" style={{ gap: '60px' }}>
          {/* First Row */}
          <div className="flex" style={{ gap: '30px' }}>
            {/* Widget 1 with line below */}
            <div className="flex flex-col">
              <div className="flex items-start" style={{ gap: '15px' }}>
                <div style={{
                  width: '140px',
                  height: '93px',
                  flexShrink: 0,
                  aspectRatio: '140/93',
                  borderRadius: '12px',
                  background: 'url(/widget2.png) lightgray 50% / cover no-repeat'
                }}></div>
                <div className="flex flex-col">
                  <p style={{
                    display: 'flex',
                    width: '196px',
                    height: '57px',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    flexShrink: 0,
                    color: '#000',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '16px',
                    fontStyle: 'normal',
                    fontWeight: 400,
                    lineHeight: '22px'
                  }}>Je viens de commettre une infraction</p>
                  <div style={{ height: '8px' }}></div>
                  <span style={{
                    display: 'flex',
                    width: '141px',
                    height: '24px',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    flexShrink: 0,
                    color: '#BC4747',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '14px',
                    fontStyle: 'normal',
                    fontWeight: 400,
                    lineHeight: '25px'
                  }}>Lire la suite</span>
                </div>
              </div>
              {/* Line below widget 1 */}
              <div style={{
                width: '358px',
                height: '1px',
                background: '#000',
                marginTop: '30px'
              }}></div>
            </div>

            {/* Widget 2 with line below */}
            <div className="flex flex-col">
              <div className="flex items-start" style={{ gap: '15px' }}>
                <div style={{
                  width: '140px',
                  height: '93px',
                  flexShrink: 0,
                  aspectRatio: '140/93',
                  borderRadius: '12px',
                  background: 'url(/widget2.png) lightgray 50% / cover no-repeat'
                }}></div>
                <div className="flex flex-col">
                  <p style={{
                    display: 'flex',
                    width: '196px',
                    height: '57px',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    flexShrink: 0,
                    color: '#000',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '16px',
                    fontStyle: 'normal',
                    fontWeight: 400,
                    lineHeight: '22px'
                  }}>Je dois vérifier mes points</p>
                  <div style={{ height: '8px' }}></div>
                  <span style={{
                    display: 'flex',
                    width: '141px',
                    height: '24px',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    flexShrink: 0,
                    color: '#BC4747',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '14px',
                    fontStyle: 'normal',
                    fontWeight: 400,
                    lineHeight: '25px'
                  }}>Lire la suite</span>
                </div>
              </div>
              {/* Line below widget 2 */}
              <div style={{
                width: '358px',
                height: '1px',
                background: '#000',
                marginTop: '30px'
              }}></div>
            </div>
          </div>

          {/* Second Row */}
          <div className="flex" style={{ gap: '30px' }}>
            {/* Widget 3 with line below */}
            <div className="flex flex-col">
              <div className="flex items-start" style={{ gap: '15px' }}>
                <div style={{
                  width: '140px',
                  height: '93px',
                  flexShrink: 0,
                  aspectRatio: '140/93',
                  borderRadius: '12px',
                  background: 'url(/widget2.png) lightgray 50% / cover no-repeat'
                }}></div>
                <div className="flex flex-col">
                  <p style={{
                    display: 'flex',
                    width: '196px',
                    height: '57px',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    flexShrink: 0,
                    color: '#000',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '16px',
                    fontStyle: 'normal',
                    fontWeight: 400,
                    lineHeight: '22px'
                  }}>J'ai reçu une lettre (48n, 48m)</p>
                  <div style={{ height: '8px' }}></div>
                  <span style={{
                    display: 'flex',
                    width: '141px',
                    height: '24px',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    flexShrink: 0,
                    color: '#BC4747',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '14px',
                    fontStyle: 'normal',
                    fontWeight: 400,
                    lineHeight: '25px'
                  }}>Lire la suite</span>
                </div>
              </div>
              {/* Line below widget 3 */}
              <div style={{
                width: '358px',
                height: '1px',
                background: '#000',
                marginTop: '30px'
              }}></div>
            </div>

            {/* Widget 4 with line below */}
            <div className="flex flex-col">
              <div className="flex items-start" style={{ gap: '15px' }}>
                <div style={{
                  width: '140px',
                  height: '93px',
                  flexShrink: 0,
                  aspectRatio: '140/93',
                  borderRadius: '12px',
                  background: 'url(/widget2.png) lightgray 50% / cover no-repeat'
                }}></div>
                <div className="flex flex-col">
                  <p style={{
                    display: 'flex',
                    width: '196px',
                    height: '57px',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    flexShrink: 0,
                    color: '#000',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '16px',
                    fontStyle: 'normal',
                    fontWeight: 400,
                    lineHeight: '22px'
                  }}>Je suis en permis probatoire</p>
                  <div style={{ height: '8px' }}></div>
                  <span style={{
                    display: 'flex',
                    width: '141px',
                    height: '24px',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    flexShrink: 0,
                    color: '#BC4747',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '14px',
                    fontStyle: 'normal',
                    fontWeight: 400,
                    lineHeight: '25px'
                  }}>Lire la suite</span>
                </div>
              </div>
              {/* Line below widget 4 */}
              <div style={{
                width: '358px',
                height: '1px',
                background: '#000',
                marginTop: '30px'
              }}></div>
            </div>
          </div>
        </div>
      </section>

      {/* 200px spacing */}
      <div style={{ height: '200px' }}></div>

      {/* Trouver un stage button */}
      <section className="flex justify-center">
        <button
          onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
          style={{
            display: 'flex',
            width: '460px',
            height: '69px',
            padding: '7px 15px',
            justifyContent: 'center',
            alignItems: 'center',
            gap: '20px',
            flexShrink: 0,
            borderRadius: '40px',
            background: '#41A334',
            border: 'none',
            cursor: 'pointer'
          }}
        >
          <span style={{
            color: '#FFF',
            fontFamily: 'var(--font-poppins)',
            fontSize: '20px',
            fontStyle: 'normal',
            fontWeight: 400,
            lineHeight: 'normal',
            letterSpacing: '1.4px'
          }}>Trouver un stage près de chez moi</span>
        </button>
      </section>

      {/* 100px spacing */}
      <div style={{ height: '100px' }}></div>

      {/* Google Reviews Placeholder */}
      <section className="flex justify-center">
        <div style={{
          width: '800px',
          padding: '40px',
          background: '#F6F6F6',
          borderRadius: '12px',
          textAlign: 'center'
        }}>
          <h3 style={{
            fontFamily: 'var(--font-poppins)',
            fontSize: '24px',
            fontWeight: 500,
            color: '#000',
            marginBottom: '20px'
          }}>Avis Google</h3>
          <p style={{
            fontFamily: 'var(--font-poppins)',
            fontSize: '16px',
            color: '#666',
            lineHeight: '24px'
          }}>Section en cours de construction - Les avis clients seront bientôt disponibles</p>
        </div>
      </section>

      {/* 100px spacing */}
      <div style={{ height: '100px' }}></div>

      {/* Questions fréquentes */}
      <section style={{ width: '100%', background: '#F6F6F6', padding: '30px 20px' }}>
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
          {/* Title: Questions Fréquentes - on one line */}
          <div style={{ display: 'flex', flexDirection: 'row', alignItems: 'center', gap: '8px', justifyContent: 'center' }}>
            {/* Questions */}
            <span
              style={{
                color: 'rgba(6, 6, 6, 0.86)',
                textAlign: 'center',
                WebkitTextStrokeWidth: '1px',
                WebkitTextStrokeColor: '#000',
                fontFamily: 'var(--font-poppins)',
                fontSize: '20px',
                fontStyle: 'normal',
                fontWeight: 250,
                lineHeight: '35px'
              }}
            >
              Questions
            </span>

            {/* Fréquentes */}
            <span
              style={{
                color: 'rgba(6, 6, 6, 0.86)',
                textAlign: 'center',
                WebkitTextStrokeWidth: '1px',
                WebkitTextStrokeColor: 'rgba(188, 71, 71, 0.73)',
                fontFamily: 'var(--font-poppins)',
                fontSize: '20px',
                fontStyle: 'normal',
                fontWeight: 275,
                lineHeight: '35px'
              }}
            >
              Fréquentes
            </span>
          </div>

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
              marginTop: '15px',
              marginBottom: '25px',
              width: '100%'
            }}
          >
            Réponses aux questions que se posent le plus souvent les conducteurs
          </div>

          {/* Question 1 with arrow */}
          <div style={{ width: '100%' }}>
            <div
              onClick={() => setOpenFaqIndex(openFaqIndex === 0 ? null : 0)}
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
                A quel moment mes 4 points sont il crédités sur mon permis après un stage
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none" style={{ width: '25px', height: '25px', flexShrink: 0, transform: openFaqIndex === 0 ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform 0.2s' }}>
                <path d="M6.25 9.375L12.5 15.625L18.75 9.375" stroke="#1E1E1E" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
            </div>
            {openFaqIndex === 0 && (
              <div style={{ padding: '15px 0', color: '#666', fontSize: '14px', lineHeight: '22px', textAlign: 'left' }}>
                Réponse à la question - Texte placeholder pour la réponse détaillée concernant le crédit des points sur le permis de conduire après avoir effectué un stage de récupération de points.
              </div>
            )}
          </div>

          {/* Line 1 */}
          <div style={{ width: '100%', height: '1px', background: '#D0D0D0', marginTop: '15px', marginBottom: '15px' }} />

          {/* Question 2 with arrow */}
          <div style={{ width: '100%' }}>
            <div
              onClick={() => setOpenFaqIndex(openFaqIndex === 1 ? null : 1)}
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
                A quel moment mes 4 points sont il crédités sur mon permis après un stage
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none" style={{ width: '25px', height: '25px', flexShrink: 0, transform: openFaqIndex === 1 ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform 0.2s' }}>
                <path d="M6.25 9.375L12.5 15.625L18.75 9.375" stroke="#1E1E1E" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
            </div>
            {openFaqIndex === 1 && (
              <div style={{ padding: '15px 0', color: '#666', fontSize: '14px', lineHeight: '22px', textAlign: 'left' }}>
                Réponse à la question - Texte placeholder pour la réponse détaillée concernant le crédit des points sur le permis de conduire après avoir effectué un stage de récupération de points.
              </div>
            )}
          </div>

          {/* Line 2 */}
          <div style={{ width: '100%', height: '1px', background: '#D0D0D0', marginTop: '15px', marginBottom: '15px' }} />

          {/* Question 3 with arrow */}
          <div style={{ width: '100%' }}>
            <div
              onClick={() => setOpenFaqIndex(openFaqIndex === 2 ? null : 2)}
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
                A quel moment mes 4 points sont il crédités sur mon permis après un stage
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none" style={{ width: '25px', height: '25px', flexShrink: 0, transform: openFaqIndex === 2 ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform 0.2s' }}>
                <path d="M6.25 9.375L12.5 15.625L18.75 9.375" stroke="#1E1E1E" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
            </div>
            {openFaqIndex === 2 && (
              <div style={{ padding: '15px 0', color: '#666', fontSize: '14px', lineHeight: '22px', textAlign: 'left' }}>
                Réponse à la question - Texte placeholder pour la réponse détaillée concernant le crédit des points sur le permis de conduire après avoir effectué un stage de récupération de points.
              </div>
            )}
          </div>

          {/* Line 3 */}
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
      </section>

      {/* 100px spacing */}
      <div style={{ height: '100px' }}></div>
      </div>
      {/* END DESKTOP VERSION */}

      {/* MOBILE VERSION - Only visible on mobile */}
      <div className="md:hidden">
        {/* Mobile Header */}
        <header style={{
          background: '#FFF',
          borderBottom: '1px solid #E0E0E0',
          padding: '12px 16px'
        }}>
          <div className="flex items-center justify-between">
            <img src="/prostagespermis-logo.png" alt="ProStagesPermis" style={{ height: '32px' }} />
            <button
              onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
              style={{
                width: '40px',
                height: '35px',
                background: 'transparent',
                border: 'none',
                cursor: 'pointer',
                padding: 0
              }}
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="40" height="35" viewBox="0 0 40 35" fill="none">
                <path d="M35 14.5833H5M35 8.75H5M35 20.4167H5M35 26.25H5" stroke="#1E1E1E" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
            </button>
          </div>
        </header>

        {/* Sticky Search Bar */}
        {showStickySearch && (
          <div style={{
            position: 'fixed',
            top: 0,
            left: 0,
            right: 0,
            zIndex: 9999,
            background: '#FFF',
            borderBottom: '1px solid #E0E0E0',
            padding: '8px 16px',
            display: 'flex',
            justifyContent: 'center',
            WebkitBackfaceVisibility: 'hidden',
            backfaceVisibility: 'hidden'
          }}>
            <div className="relative" style={{ width: '283px' }}>
              <div style={{
                display: 'flex',
                width: '283px',
                height: '36px',
                padding: '1px 20px',
                alignItems: 'center',
                gap: '15px',
                flexShrink: 0,
                borderRadius: '20px',
                border: '1px solid #989898',
                background: 'linear-gradient(0deg, rgba(176, 175, 175, 0.20) 0%, rgba(176, 175, 175, 0.20) 100%), #FFF'
              }}>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                  <path d="M15.75 15.75L12.4875 12.4875M14.25 8.25C14.25 11.5637 11.5637 14.25 8.25 14.25C4.93629 14.25 2.25 11.5637 2.25 8.25C2.25 4.93629 4.93629 2.25 8.25 2.25C11.5637 2.25 14.25 4.93629 14.25 8.25Z" stroke="#727171" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
                <input
                  type="text"
                  value={searchQuery}
                  onChange={(e) => handleInputChange(e.target.value)}
                  onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                  placeholder="Ville ou code postal"
                  style={{
                    flex: 1,
                    border: 'none',
                    outline: 'none',
                    background: 'transparent',
                    fontSize: '14px',
                    fontFamily: 'var(--font-poppins)',
                    color: searchQuery ? '#000' : '#949393'
                  }}
                />
              </div>
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
        )}

        {/* Mobile Menu Overlay */}
        {isMobileMenuOpen && (
          <div
            style={{
              position: 'fixed',
              top: 0,
              left: 0,
              right: 0,
              bottom: 0,
              background: 'rgba(0, 0, 0, 0.5)',
              zIndex: 999
            }}
            onClick={() => setIsMobileMenuOpen(false)}
          >
            <div
              style={{
                position: 'fixed',
                top: 0,
                right: 0,
                bottom: 0,
                width: '280px',
                background: '#FFF',
                boxShadow: '-2px 0 8px rgba(0, 0, 0, 0.15)',
                overflowY: 'auto',
                padding: '20px'
              }}
              onClick={(e) => e.stopPropagation()}
            >
              <div style={{ marginBottom: '24px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <h3 style={{ fontFamily: 'var(--font-poppins)', fontSize: '18px', fontWeight: 500 }}>Menu</h3>
                <button
                  onClick={() => setIsMobileMenuOpen(false)}
                  style={{
                    width: '32px',
                    height: '32px',
                    background: 'transparent',
                    border: 'none',
                    cursor: 'pointer',
                    fontSize: '24px'
                  }}
                >×</button>
              </div>
              <nav style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
                <a href="/qui-sommes-nous" style={{ fontFamily: 'var(--font-poppins)', fontSize: '15px', color: '#333', textDecoration: 'none', padding: '8px 0', borderBottom: '1px solid #E0E0E0' }}>Qui sommes-nous</a>
                <a href="/aide-et-contact" style={{ fontFamily: 'var(--font-poppins)', fontSize: '15px', color: '#333', textDecoration: 'none', padding: '8px 0', borderBottom: '1px solid #E0E0E0' }}>Aide et contact</a>
                <a href="/conditions-generales" style={{ fontFamily: 'var(--font-poppins)', fontSize: '15px', color: '#333', textDecoration: 'none', padding: '8px 0', borderBottom: '1px solid #E0E0E0' }}>Conditions générales de vente</a>
                <a href="/mentions-legales" style={{ fontFamily: 'var(--font-poppins)', fontSize: '15px', color: '#333', textDecoration: 'none', padding: '8px 0', borderBottom: '1px solid #E0E0E0' }}>Mentions légales</a>
                <a href="/espace-client" style={{ fontFamily: 'var(--font-poppins)', fontSize: '15px', color: '#333', textDecoration: 'none', padding: '8px 0' }}>Espace Client</a>
              </nav>
            </div>
          </div>
        )}

        {/* Mobile Hero Section */}
        <section className="px-4 pt-6 pb-8">

          {/* Title */}
          <h1 style={{
            fontFamily: 'var(--font-poppins)',
            fontSize: '20px',
            fontWeight: 400,
            lineHeight: '28px',
            textAlign: 'center',
            marginBottom: '12px'
          }}>
            Tous les Stages de Récupération de Points au Meilleur Prix
          </h1>

          {/* Subtitle */}
          <p style={{
            fontFamily: 'var(--font-poppins)',
            fontSize: '14px',
            fontWeight: 400,
            lineHeight: '20px',
            textAlign: 'center',
            color: 'rgba(0, 0, 0, 0.8)',
            marginBottom: '20px'
          }}>
            Trouvez rapidement un stage et récupérez 4 points
          </p>

          {/* Search Bar */}
          <div ref={heroSearchRef} className="relative mb-8 flex justify-center">
            <div ref={searchRef} style={{
              display: 'flex',
              width: '324px',
              height: '56px',
              padding: '1px 20px',
              alignItems: 'center',
              gap: '15px',
              flexShrink: 0,
              borderRadius: '20px',
              border: '1px solid #686868',
              background: '#FFF'
            }}>
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                <path d="M15.75 15.75L12.4875 12.4875M14.25 8.25C14.25 11.5637 11.5637 14.25 8.25 14.25C4.93629 14.25 2.25 11.5637 2.25 8.25C2.25 4.93629 4.93629 2.25 8.25 2.25C11.5637 2.25 14.25 4.93629 14.25 8.25Z" stroke="#727171" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => handleInputChange(e.target.value)}
                onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                placeholder="Ville ou code postal"
                style={{
                  flex: 1,
                  border: 'none',
                  outline: 'none',
                  background: 'transparent',
                  fontSize: '14px',
                  fontFamily: 'var(--font-poppins)',
                  color: searchQuery ? '#000' : '#949393'
                }}
              />
            </div>
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

          {/* 4 Benefits Box */}
          <div className="flex justify-center mb-8">
            <div style={{
              display: 'flex',
              width: '389px',
              maxWidth: '100%',
              height: '259px',
              padding: '10px',
              flexDirection: 'column',
              justifyContent: 'center',
              alignItems: 'center',
              flexShrink: 0,
              background: '#F0F0F0'
            }}>
              <div className="space-y-3 w-full px-4">
                {/* Benefit 1 */}
                <div className="flex items-center gap-3 pb-3 border-b border-gray-200">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 40 40" fill="none">
                    <path d="M20 36.6667C20 36.6667 33.3333 30 33.3333 20V8.33337L20 3.33337L6.66666 8.33337V20C6.66666 30 20 36.6667 20 36.6667Z" stroke="#C4A226" strokeOpacity="0.96" strokeWidth="3.5" strokeLinecap="round" strokeLinejoin="round"/>
                  </svg>
                  <span style={{ fontFamily: 'var(--font-poppins)', fontSize: '14px', fontWeight: 400 }}>
                    Stages Agréés Préfecture
                  </span>
                </div>

                {/* Benefit 2 */}
                <div className="flex items-center gap-3 pb-3 border-b border-gray-200">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 40 40" fill="none">
                    <path d="M20 10V20L26.6666 23.3334M36.6666 20C36.6666 29.2048 29.2047 36.6667 20 36.6667C10.7952 36.6667 3.33331 29.2048 3.33331 20C3.33331 10.7953 10.7952 3.33337 20 3.33337C29.2047 3.33337 36.6666 10.7953 36.6666 20Z" stroke="#C4A226" strokeWidth="3.5" strokeLinecap="round" strokeLinejoin="round"/>
                  </svg>
                  <span style={{ fontFamily: 'var(--font-poppins)', fontSize: '14px', fontWeight: 400 }}>
                    + 4 points en 48h
                  </span>
                </div>

                {/* Benefit 3 */}
                <div className="flex items-center gap-3 pb-3 border-b border-gray-200">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 40 40" fill="none">
                    <g clipPath="url(#clip0_prix_mobile)">
                      <path d="M38.3334 30L22.5 14.1667L14.1667 22.5L1.66669 10M38.3334 30H28.3334M38.3334 30V20" stroke="#C4A226" strokeWidth="4" strokeLinecap="round" strokeLinejoin="round"/>
                    </g>
                    <defs>
                      <clipPath id="clip0_prix_mobile">
                        <rect width="40" height="40" fill="white"/>
                      </clipPath>
                    </defs>
                  </svg>
                  <span style={{ fontFamily: 'var(--font-poppins)', fontSize: '14px', fontWeight: 400 }}>
                    Prix le plus bas garanti
                  </span>
                </div>

                {/* Benefit 4 */}
                <div className="flex items-center gap-3 pb-3">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 40 40" fill="none">
                    <g clipPath="url(#clip0_report_mobile)">
                      <path d="M1.66663 6.66662V16.6666M1.66663 16.6666H11.6666M1.66663 16.6666L9.39996 9.39996C11.7015 7.10232 14.6874 5.61491 17.9078 5.16182C21.1281 4.70873 24.4085 5.31451 27.2547 6.8879C30.1008 8.46128 32.3586 10.917 33.6877 13.8851C35.0168 16.8532 35.3453 20.1729 34.6237 23.3439C33.902 26.5149 32.1694 29.3655 29.6868 31.4662C27.2043 33.5669 24.1062 34.8039 20.8595 34.9907C17.6128 35.1776 14.3933 34.3042 11.6861 32.5022C8.97885 30.7003 6.93062 28.0673 5.84996 25" stroke="#C4A226" strokeWidth="4" strokeLinecap="round" strokeLinejoin="round"/>
                    </g>
                    <defs>
                      <clipPath id="clip0_report_mobile">
                        <rect width="40" height="40" fill="white"/>
                      </clipPath>
                    </defs>
                  </svg>
                  <span style={{ fontFamily: 'var(--font-poppins)', fontSize: '14px', fontWeight: 400 }}>
                    Report ou remboursement
                  </span>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* Prochains stages Section */}
        <section className="px-4 pb-8">
          <h2 className="text-center mb-4 flex flex-col items-center">
            <span style={{
              color: 'rgba(6, 6, 6, 0.86)',
              textAlign: 'center',
              WebkitTextStrokeWidth: '1px',
              WebkitTextStrokeColor: '#000',
              fontFamily: 'Poppins',
              fontSize: '20px',
              fontStyle: 'normal',
              fontWeight: 250,
              lineHeight: '35px',
              whiteSpace: 'nowrap'
            }}>
              Prochains stages
            </span>
            <span style={{
              color: 'rgba(6, 6, 6, 0.86)',
              WebkitTextStrokeWidth: '1px',
              WebkitTextStrokeColor: 'rgba(201, 39, 39, 0.73)',
              fontFamily: 'Poppins',
              fontSize: '20px',
              fontStyle: 'normal',
              fontWeight: 250,
              lineHeight: '35px',
              whiteSpace: 'nowrap'
            }}>
              proches de chez vous
            </span>
          </h2>

          {/* Horizontal Scrollable Cards */}
          <div className="flex gap-4 overflow-x-auto pb-4" style={{ scrollbarWidth: 'none', msOverflowStyle: 'none' }}>
            {/* Card 1 */}
            <div className="flex-shrink-0 w-[160px] flex flex-col items-center">
              <div style={{
                width: '160px',
                height: '160px',
                borderRadius: '12px',
                background: 'url(/widget.png) lightgray 50% / cover no-repeat',
                marginBottom: '8px'
              }}></div>
              <p style={{
                display: 'flex',
                width: '83px',
                height: '28px',
                flexDirection: 'column',
                justifyContent: 'center',
                flexShrink: 0,
                color: '#000',
                textAlign: 'center',
                fontFamily: 'Poppins',
                fontSize: '18px',
                fontStyle: 'normal',
                fontWeight: 400,
                lineHeight: '17px',
                marginBottom: '4px'
              }}>
                5-6 déc
              </p>
              <p style={{
                display: 'flex',
                width: '122px',
                height: '13px',
                flexDirection: 'column',
                justifyContent: 'center',
                flexShrink: 0,
                color: '#6A6969',
                textAlign: 'center',
                fontFamily: 'Poppins',
                fontSize: '16px',
                fontStyle: 'normal',
                fontWeight: 400,
                lineHeight: '35px',
                marginBottom: '4px'
              }}>
                Marseille
              </p>
              <p style={{
                width: '202px',
                flexShrink: 0,
                color: '#BC4747',
                textAlign: 'center',
                fontFamily: 'Poppins',
                fontSize: '17px',
                fontStyle: 'normal',
                fontWeight: 400,
                lineHeight: '35px',
                marginBottom: '8px'
              }}>
                210 €
              </p>
              <button onClick={scrollToSearch} style={{
                display: 'flex',
                width: '103px',
                height: '31px',
                justifyContent: 'center',
                alignItems: 'center',
                gap: '10px',
                flexShrink: 0,
                borderRadius: '12px',
                background: '#41A334',
                border: 'none',
                cursor: 'pointer'
              }}>
                <span style={{
                  width: '83px',
                  height: '20px',
                  flexShrink: 0,
                  color: '#FFF',
                  fontFamily: 'Poppins',
                  fontSize: '11px',
                  fontStyle: 'normal',
                  fontWeight: 400,
                  lineHeight: 'normal',
                  letterSpacing: '0.77px'
                }}>
                  Voir ce stage
                </span>
              </button>
            </div>

            {/* Card 2 */}
            <div className="flex-shrink-0 w-[160px] flex flex-col items-center">
              <div style={{
                width: '160px',
                height: '160px',
                borderRadius: '12px',
                background: 'url(/widget.png) lightgray 50% / cover no-repeat',
                marginBottom: '8px'
              }}></div>
              <p style={{
                display: 'flex',
                width: '83px',
                height: '28px',
                flexDirection: 'column',
                justifyContent: 'center',
                flexShrink: 0,
                color: '#000',
                textAlign: 'center',
                fontFamily: 'Poppins',
                fontSize: '18px',
                fontStyle: 'normal',
                fontWeight: 400,
                lineHeight: '17px',
                marginBottom: '4px'
              }}>
                6-6 déc
              </p>
              <p style={{
                display: 'flex',
                width: '122px',
                height: '13px',
                flexDirection: 'column',
                justifyContent: 'center',
                flexShrink: 0,
                color: '#6A6969',
                textAlign: 'center',
                fontFamily: 'Poppins',
                fontSize: '16px',
                fontStyle: 'normal',
                fontWeight: 400,
                lineHeight: '35px',
                marginBottom: '4px'
              }}>
                Marseille
              </p>
              <p style={{
                width: '202px',
                flexShrink: 0,
                color: '#BC4747',
                textAlign: 'center',
                fontFamily: 'Poppins',
                fontSize: '17px',
                fontStyle: 'normal',
                fontWeight: 400,
                lineHeight: '35px',
                marginBottom: '8px'
              }}>
                210 €
              </p>
              <button onClick={scrollToSearch} style={{
                display: 'flex',
                width: '103px',
                height: '31px',
                justifyContent: 'center',
                alignItems: 'center',
                gap: '10px',
                flexShrink: 0,
                borderRadius: '12px',
                background: '#41A334',
                border: 'none',
                cursor: 'pointer'
              }}>
                <span style={{
                  width: '83px',
                  height: '20px',
                  flexShrink: 0,
                  color: '#FFF',
                  fontFamily: 'Poppins',
                  fontSize: '11px',
                  fontStyle: 'normal',
                  fontWeight: 400,
                  lineHeight: 'normal',
                  letterSpacing: '0.77px'
                }}>
                  Voir ce stage
                </span>
              </button>
            </div>

            {/* Card 3 */}
            <div className="flex-shrink-0 w-[160px] flex flex-col items-center">
              <div style={{
                width: '160px',
                height: '160px',
                borderRadius: '12px',
                background: 'url(/widget.png) lightgray 50% / cover no-repeat',
                marginBottom: '8px'
              }}></div>
              <p style={{
                display: 'flex',
                width: '83px',
                height: '28px',
                flexDirection: 'column',
                justifyContent: 'center',
                flexShrink: 0,
                color: '#000',
                textAlign: 'center',
                fontFamily: 'Poppins',
                fontSize: '18px',
                fontStyle: 'normal',
                fontWeight: 400,
                lineHeight: '17px',
                marginBottom: '4px'
              }}>
                8-9 déc
              </p>
              <p style={{
                display: 'flex',
                width: '122px',
                height: '13px',
                flexDirection: 'column',
                justifyContent: 'center',
                flexShrink: 0,
                color: '#6A6969',
                textAlign: 'center',
                fontFamily: 'Poppins',
                fontSize: '16px',
                fontStyle: 'normal',
                fontWeight: 400,
                lineHeight: '35px',
                marginBottom: '4px'
              }}>
                Nice
              </p>
              <p style={{
                width: '202px',
                flexShrink: 0,
                color: '#BC4747',
                textAlign: 'center',
                fontFamily: 'Poppins',
                fontSize: '17px',
                fontStyle: 'normal',
                fontWeight: 400,
                lineHeight: '35px',
                marginBottom: '8px'
              }}>
                189 €
              </p>
              <button onClick={scrollToSearch} style={{
                display: 'flex',
                width: '103px',
                height: '31px',
                justifyContent: 'center',
                alignItems: 'center',
                gap: '10px',
                flexShrink: 0,
                borderRadius: '12px',
                background: '#41A334',
                border: 'none',
                cursor: 'pointer'
              }}>
                <span style={{
                  width: '83px',
                  height: '20px',
                  flexShrink: 0,
                  color: '#FFF',
                  fontFamily: 'Poppins',
                  fontSize: '11px',
                  fontStyle: 'normal',
                  fontWeight: 400,
                  lineHeight: 'normal',
                  letterSpacing: '0.77px'
                }}>
                  Voir ce stage
                </span>
              </button>
            </div>
          </div>
        </section>

        {/* Pourquoi réserver Section */}
        <section className="flex justify-center px-4 pb-8">
          <div style={{
            display: 'flex',
            width: '340px',
            height: '391px',
            padding: '2px 10px',
            flexDirection: 'column',
            justifyContent: 'center',
            alignItems: 'center',
            flexShrink: 0,
            borderRadius: '15px',
            background: '#F6F6F6',
            boxShadow: '0 4px 12px 2px rgba(0, 0, 0, 0.20)'
          }}>
            <div style={{
              display: 'flex',
              height: '59px',
              flexDirection: 'column',
              justifyContent: 'center',
              flexShrink: 0,
              alignSelf: 'stretch',
              color: '#060606',
              textAlign: 'center',
              WebkitTextStrokeWidth: '1px',
              WebkitTextStrokeColor: '#000',
              fontFamily: 'Poppins',
              fontSize: '20px',
              fontStyle: 'normal',
              fontWeight: 250,
              lineHeight: '25px'
            }}>
              Pourquoi réserver votre stage chez
            </div>
            <div style={{
              display: 'flex',
              height: '33px',
              flexDirection: 'column',
              justifyContent: 'center',
              flexShrink: 0,
              alignSelf: 'stretch',
              color: 'rgba(6, 6, 6, 0.86)',
              textAlign: 'center',
              WebkitTextStrokeWidth: '1px',
              WebkitTextStrokeColor: 'rgba(188, 71, 71, 0.73)',
              fontFamily: 'Poppins',
              fontSize: '20px',
              fontStyle: 'normal',
              fontWeight: 250,
              lineHeight: '35px'
            }}>
              ProstagePermis
            </div>

            <div style={{
              display: 'flex',
              width: '228px',
              padding: '7px 15px',
              flexDirection: 'column',
              justifyContent: 'center',
              alignItems: 'center',
              gap: '10px',
              borderBottom: '2px solid #BC4747',
              marginBottom: '16px'
            }}></div>

            <div className="space-y-3 w-full px-4">
              {[
                'Stage officiel agréé préfecture',
                '+4 points en 48h',
                'Report ou remboursement en quelques clics sur votre espace client',
                'Meilleur prix garanti',
                'Inscription en quelques clics',
                'Convocation envoyé immédiatement par email après l\'inscription'
              ].map((text, i) => (
                <div key={i} className="flex items-start gap-3">
                  <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none" style={{ flexShrink: 0 }}>
                    <path d="M8.25 10.0833L11 12.8333L20.1667 3.66667M19.25 11V17.4167C19.25 17.9029 19.0568 18.3692 18.713 18.713C18.3692 19.0568 17.9029 19.25 17.4167 19.25H4.58333C4.0971 19.25 3.63079 19.0568 3.28697 18.713C2.94315 18.3692 2.75 17.9029 2.75 17.4167V4.58333C2.75 4.0971 2.94315 3.63079 3.28697 3.28697C3.63079 2.94315 4.0971 2.75 4.58333 2.75H14.6667" stroke="#C4A226" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                  </svg>
                  <span style={{ fontFamily: 'var(--font-poppins)', fontSize: '13px', lineHeight: '18px' }}>
                    {text}
                  </span>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* Europe 1 Section */}
        <section className="px-4 pb-8">
          <div className="flex items-center justify-between" style={{ borderBottom: '1px solid #E0E0E0', paddingBottom: '16px' }}>
            <div className="flex-1">
              <h3 style={{
                fontFamily: 'var(--font-poppins)',
                fontSize: '16px',
                fontWeight: 500,
                marginBottom: '8px'
              }}>
                Recommandé par Europe 1
              </h3>
              <p style={{
                fontFamily: 'var(--font-poppins)',
                fontSize: '13px',
                color: 'rgba(0, 0, 0, 0.7)',
                lineHeight: '18px',
                fontStyle: 'italic'
              }}>
                ProStagesPermis cité comme site de confiance par Europe 1
              </p>
            </div>
            <div style={{ width: '1px', height: '80px', background: '#000', margin: '0 16px' }}></div>
            <div className="flex flex-col items-center gap-2">
              <img src="/europe1-logo.png" alt="Europe 1" className="w-20" />
              <a href="https://www.youtube.com/watch?v=z1AsmdcGTaw" target="_blank" rel="noopener noreferrer" style={{
                fontFamily: 'var(--font-poppins)',
                fontSize: '12px',
                color: '#BC4747',
                textDecoration: 'underline'
              }}>
                Écouter l'extrait
              </a>
            </div>
          </div>
        </section>

        {/* Dans quelle situation Section */}
        <section className="px-4 pb-8">
          <h2 className="text-center mb-6">
            <span style={{ fontFamily: 'var(--font-poppins)', fontSize: '18px', fontWeight: 400 }}>
              Dans quelle situation{' '}
            </span>
            <span style={{ fontFamily: 'var(--font-poppins)', fontSize: '18px', fontWeight: 400, color: '#BC4747' }}>
              êtes-vous
            </span>
          </h2>

          <div className="space-y-4">
            {[
              { title: 'Je viens de commettre une infraction', img: '/widget2.png' },
              { title: 'Je dois vérifier mes points', img: '/widget2.png' },
              { title: 'J\'ai reçu une lettre (48n, 48m)', img: '/widget2.png' },
              { title: 'Je suis en permis probatoire', img: '/widget2.png' }
            ].map((item, i) => (
              <div key={i} className="pb-4 border-b border-gray-300">
                <div className="flex items-center gap-3">
                  <div style={{
                    width: '80px',
                    height: '60px',
                    borderRadius: '8px',
                    background: `url(${item.img}) lightgray 50% / cover no-repeat`,
                    flexShrink: 0
                  }}></div>
                  <div>
                    <p style={{
                      fontFamily: 'var(--font-poppins)',
                      fontSize: '14px',
                      fontWeight: 400,
                      lineHeight: '20px',
                      marginBottom: '4px'
                    }}>
                      {item.title}
                    </p>
                    <a href="#" style={{
                      fontFamily: 'var(--font-poppins)',
                      fontSize: '12px',
                      color: '#BC4747'
                    }}>
                      Lire la suite
                    </a>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </section>

        {/* Trouver un stage Button */}
        <section className="px-4 pb-8 flex justify-center">
          <button
            onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
            style={{
              width: '100%',
              maxWidth: '300px',
              padding: '16px',
              borderRadius: '30px',
              background: '#41A334',
              border: 'none',
              color: '#FFF',
              fontFamily: 'var(--font-poppins)',
              fontSize: '15px',
              fontWeight: 400,
              letterSpacing: '0.5px'
            }}
          >
            Trouver un stage près de chez moi
          </button>
        </section>

        {/* Avis Clients Google Reviews Section */}
        <section className="px-4 pb-8">
          <div className="text-center mb-6">
            <h2 style={{
              fontFamily: 'var(--font-poppins)',
              fontSize: '18px',
              fontWeight: 400,
              marginBottom: '4px'
            }}>
              Avis <span style={{ color: '#BC4747' }}>Clients</span>
            </h2>
            <div style={{
              width: '120px',
              height: '2px',
              background: '#BC4747',
              margin: '8px auto 16px'
            }}></div>

            <div className="mb-4">
              <p style={{ fontFamily: 'var(--font-poppins)', fontSize: '20px', fontWeight: 600 }}>Excellent</p>
              <div className="flex justify-center gap-1 my-2">
                {[1,2,3,4].map(i => (
                  <svg key={i} width="24" height="24" viewBox="0 0 24 24" fill="#FFD700">
                    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                  </svg>
                ))}
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                  <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="#FFD700"/>
                  <path d="M12 2V17.77L18.18 21.02L17 14.14L22 9.27L15.09 8.26L12 2Z" fill="#E0E0E0"/>
                </svg>
              </div>
              <p style={{ fontFamily: 'var(--font-poppins)', fontSize: '14px', color: '#666' }}>
                4.7/5
              </p>
              <p style={{ fontFamily: 'var(--font-poppins)', fontSize: '12px', color: '#666' }}>
                Basé sur <strong>499 avis</strong>
              </p>
              <img src="/google-logo.png" alt="Google" className="mx-auto mt-2 h-6" style={{ filter: 'grayscale(0)' }} />
            </div>

            {/* Review Cards */}
            <div className="space-y-4 mb-6">
              {/* Review 1 */}
              <div style={{
                background: '#F9F9F9',
                borderRadius: '12px',
                padding: '16px',
                textAlign: 'left'
              }}>
                <div className="flex items-center gap-3 mb-2">
                  <div style={{
                    width: '40px',
                    height: '40px',
                    borderRadius: '50%',
                    background: '#4285F4',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    color: '#FFF',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '18px',
                    fontWeight: 600
                  }}>K</div>
                  <div>
                    <p style={{ fontFamily: 'var(--font-poppins)', fontSize: '14px', fontWeight: 500 }}>
                      Katia rbenrguig
                    </p>
                    <div className="flex gap-1">
                      {[1,2,3,4,5].map(i => (
                        <svg key={i} width="14" height="14" viewBox="0 0 24 24" fill="#FFD700">
                          <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                        </svg>
                      ))}
                    </div>
                  </div>
                </div>
                <p style={{
                  fontFamily: 'var(--font-poppins)',
                  fontSize: '12px',
                  lineHeight: '18px',
                  color: '#333'
                }}>
                  Bonjour, je vous recommande vivement ProStagesPermis, ont peux y trouver des stages rapidement, pas très loin de chez nous et pas très cher comparé à d'autre.
                </p>
              </div>

              {/* Review 2 */}
              <div style={{
                background: '#F9F9F9',
                borderRadius: '12px',
                padding: '16px',
                textAlign: 'left'
              }}>
                <div className="flex items-center gap-3 mb-2">
                  <div style={{
                    width: '40px',
                    height: '40px',
                    borderRadius: '50%',
                    background: '#0F9D58',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    color: '#FFF',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '18px',
                    fontWeight: 600
                  }}>J</div>
                  <div>
                    <p style={{ fontFamily: 'var(--font-poppins)', fontSize: '14px', fontWeight: 500 }}>
                      Joe Labaisse
                    </p>
                    <div className="flex gap-1">
                      {[1,2,3,4,5].map(i => (
                        <svg key={i} width="14" height="14" viewBox="0 0 24 24" fill="#FFD700">
                          <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                        </svg>
                      ))}
                    </div>
                  </div>
                </div>
                <p style={{
                  fontFamily: 'var(--font-poppins)',
                  fontSize: '12px',
                  lineHeight: '18px',
                  color: '#333'
                }}>
                  Merci à vous pour votre compré je recommande pro stage permi
                  <br/><br/>
                  Alex
                </p>
              </div>
            </div>

            <a href="#" style={{
              fontFamily: 'var(--font-poppins)',
              fontSize: '14px',
              color: '#BC4747',
              textDecoration: 'underline',
              fontWeight: 500
            }}>
              Lire les autres avis
            </a>
          </div>
        </section>

        {/* Questions Fréquentes Section */}
        <section style={{ background: '#F6F6F6', padding: '32px 16px' }}>
          <h2 className="text-center mb-2">
            <span style={{ fontFamily: 'var(--font-poppins)', fontSize: '18px', fontWeight: 400 }}>
              Questions{' '}
            </span>
            <span style={{ fontFamily: 'var(--font-poppins)', fontSize: '18px', fontWeight: 400, color: '#BC4747' }}>
              Fréquentes
            </span>
          </h2>
          <p style={{
            fontFamily: 'var(--font-poppins)',
            fontSize: '13px',
            textAlign: 'center',
            color: '#000',
            marginBottom: '24px'
          }}>
            Réponses aux questions que se posent le plus souvent les conducteurs
          </p>

          <div className="space-y-4 mb-6">
            {[0, 1, 2].map(i => (
              <div key={i}>
                <div
                  onClick={() => setOpenFaqIndex(openFaqIndex === i ? null : i)}
                  className="flex justify-between items-center cursor-pointer"
                >
                  <p style={{
                    flex: 1,
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '13px',
                    lineHeight: '20px'
                  }}>
                    A quel moment mes 4 points sont il crédités sur mon permis après un stage
                  </p>
                  <svg
                    width="20"
                    height="20"
                    viewBox="0 0 25 25"
                    fill="none"
                    style={{
                      flexShrink: 0,
                      marginLeft: '12px',
                      transform: openFaqIndex === i ? 'rotate(180deg)' : 'rotate(0deg)',
                      transition: 'transform 0.2s'
                    }}
                  >
                    <path d="M6.25 9.375L12.5 15.625L18.75 9.375" stroke="#1E1E1E" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                  </svg>
                </div>
                {openFaqIndex === i && (
                  <p style={{
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '12px',
                    color: '#666',
                    lineHeight: '18px',
                    marginTop: '12px'
                  }}>
                    Réponse à la question - Texte placeholder pour la réponse détaillée.
                  </p>
                )}
                {i < 2 && <div style={{ height: '1px', background: '#D0D0D0', margin: '16px 0' }} />}
              </div>
            ))}
          </div>

          <div className="text-center">
            <a href="#" style={{
              fontFamily: 'var(--font-poppins)',
              fontSize: '13px',
              fontWeight: 500,
              textDecoration: 'underline'
            }}>
              Afficher plus de questions
            </a>
          </div>
        </section>
      </div>
      {/* END MOBILE VERSION */}

      {/* Footer */}
      <footer className="bg-[#343435] py-6">
        <div className="max-w-7xl mx-auto px-4">
          <div className="flex items-center justify-center gap-6 mb-3">
            <a href="/qui-sommes-nous" className="text-white text-xs hover:underline">
              Qui sommes-nous
            </a>
            <a href="/aide-et-contact" className="text-white text-xs hover:underline">
              Aide et contact
            </a>
            <a href="/conditions-generales" className="text-white text-xs hover:underline">
              Conditions générales de vente
            </a>
            <a href="/mentions-legales" className="text-white text-xs hover:underline">
              Mentions légales
            </a>
            <a href="/espace-client" className="text-white text-xs hover:underline">
              Espace Client
            </a>
          </div>
          <p className="text-center text-white text-xs">2025©ProStagesPermis</p>
        </div>
      </footer>
    </div>
  )
}
