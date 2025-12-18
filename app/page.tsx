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
        <div style={{
          display: 'flex',
          width: '746px',
          height: '308px',
          padding: '10px',
          justifyContent: 'center',
          alignItems: 'center',
          gap: '10px',
          flexShrink: 0,
          borderRadius: '16px',
          background: 'rgba(230, 230, 230, 0.20)'
        }}>
          <img
            src="/benefitbox2.png"
            alt="Pourquoi réserver votre stage chez ProStagesPermis"
            style={{ width: '100%', height: '100%', objectFit: 'contain' }}
          />
        </div>
      </section>
    </div>
  )
}
