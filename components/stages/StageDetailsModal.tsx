'use client'

import { useState, useEffect, useRef } from 'react'
import Link from 'next/link'
import Image from 'next/image'
import { removeStreetNumber } from '@/lib/formatAddress'

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

interface StageDetailsModalProps {
  stage: Stage
  isOpen: boolean
  onClose: () => void
  city: string
  slug?: string
}

export default function StageDetailsModal({
  stage,
  isOpen,
  onClose,
  city,
  slug,
}: StageDetailsModalProps) {
  const [isAnimating, setIsAnimating] = useState(false)
  const [touchStart, setTouchStart] = useState(0)
  const [touchEnd, setTouchEnd] = useState(0)
  const sheetRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    if (isOpen) {
      setIsAnimating(true)
      // Prevent body scroll when modal is open
      document.body.style.overflow = 'hidden'
    } else {
      document.body.style.overflow = ''
    }

    return () => {
      document.body.style.overflow = ''
    }
  }, [isOpen])

  if (!isOpen && !isAnimating) return null

  const formatDate = (dateStart: string, dateEnd: string) => {
    const start = new Date(dateStart)
    const end = new Date(dateEnd)

    const dayStart = start.toLocaleDateString('fr-FR', { weekday: 'short' }).replace('.', '')
    const dayEnd = end.toLocaleDateString('fr-FR', { weekday: 'short' }).replace('.', '')
    const dayNumStart = start.getDate()
    const dayNumEnd = end.getDate()
    const month = start.toLocaleDateString('fr-FR', { month: 'short' })
    const year = start.getFullYear()

    // Capitalize first letter of day names
    const capitalizedDayStart = dayStart.charAt(0).toUpperCase() + dayStart.slice(1)
    const capitalizedDayEnd = dayEnd.charAt(0).toUpperCase() + dayEnd.slice(1)

    return `du ${capitalizedDayStart} ${dayNumStart} et ${capitalizedDayEnd} ${dayNumEnd} ${month} ${year}`
  }

  // Handle escape key
  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === 'Escape') {
      handleClose()
    }
  }

  // Handle close with animation
  const handleClose = () => {
    setIsAnimating(false)
    setTimeout(() => {
      onClose()
    }, 300) // Match animation duration
  }

  // Handle touch events for swipe down
  const handleTouchStart = (e: React.TouchEvent) => {
    setTouchStart(e.targetTouches[0].clientY)
  }

  const handleTouchMove = (e: React.TouchEvent) => {
    setTouchEnd(e.targetTouches[0].clientY)
  }

  const handleTouchEnd = () => {
    if (touchStart - touchEnd < -50) {
      // Swiped down more than 50px
      handleClose()
    }
  }

  return (
    <>
      {/* Desktop Modal */}
      <div
        className="hidden md:flex fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 p-4"
        onClick={handleClose}
        onKeyDown={handleKeyDown}
        role="dialog"
        aria-modal="true"
      >
        <div
          className="bg-white rounded-2xl shadow-2xl relative"
          style={{
            width: '780px',
            maxHeight: '90vh',
            overflowY: 'auto',
            overflowX: 'hidden'
          }}
          onClick={e => e.stopPropagation()}
        >
        {/* Close button - positioned inside the popup, smaller */}
        <button
          onClick={onClose}
          className="absolute flex items-center justify-center z-10 hover:opacity-80 transition-opacity"
          style={{
            top: '12px',
            right: '12px',
            width: '28px',
            height: '28px',
            background: 'transparent',
            border: 'none',
            cursor: 'pointer'
          }}
          aria-label="Fermer"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M18 6L6 18M6 6L18 18" stroke="#666" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
          </svg>
        </button>

        {/* Content */}
        <div className="p-6">
          {/* Title */}
          <h2
            className="text-center mb-2"
            style={{
              width: '499px',
              height: '34px',
              flexShrink: 0,
              color: 'rgba(34, 34, 34, 0.86)',
              fontFamily: 'var(--font-poppins)',
              fontSize: '22px',
              fontStyle: 'normal',
              fontWeight: '500',
              lineHeight: '35px',
              margin: '0 auto'
            }}
          >
            Stage {formatDate(stage.date_start, stage.date_end)}
          </h2>

          {/* Price - in black */}
          <p
            className="text-center mb-1"
            style={{
              width: '235px',
              flexShrink: 0,
              color: '#000',
              textAlign: 'center',
              fontFamily: 'var(--font-poppins)',
              fontSize: '24px',
              fontStyle: 'normal',
              fontWeight: '500',
              lineHeight: '35px',
              margin: '0 auto'
            }}
          >
            {stage.prix.toFixed(0)}€ TTC
          </p>

          {/* Places disponibles - in green */}
          <p
            className="text-center mb-4"
            style={{
              color: '#267E1C',
              textAlign: 'center',
              fontFamily: 'var(--font-poppins)',
              fontSize: '14px',
              fontStyle: 'normal',
              fontWeight: '400',
              lineHeight: '20px',
              margin: '0 auto 16px'
            }}
          >
            Places disponibles
          </p>

          {/* Two column layout */}
          <div className="grid grid-cols-2 gap-6 mb-5">
            {/* Left column */}
            <div className="space-y-3" style={{ marginTop: '8px' }}>
              {/* Address */}
              <div className="flex gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none" className="flex-shrink-0 mt-1">
                  <g clipPath="url(#clip0_1_70)">
                    <path d="M21.875 10.4167C21.875 17.7083 12.5 23.9583 12.5 23.9583C12.5 23.9583 3.125 17.7083 3.125 10.4167C3.125 7.93026 4.11272 5.54569 5.87087 3.78754C7.62903 2.02938 10.0136 1.04166 12.5 1.04166C14.9864 1.04166 17.371 2.02938 19.1291 3.78754C20.8873 5.54569 21.875 7.93026 21.875 10.4167Z" stroke="#595656" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                    <path d="M12.5 13.5417C14.2259 13.5417 15.625 12.1426 15.625 10.4167C15.625 8.69077 14.2259 7.29166 12.5 7.29166C10.7741 7.29166 9.375 8.69077 9.375 10.4167C9.375 12.1426 10.7741 13.5417 12.5 13.5417Z" stroke="#595656" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                  </g>
                  <defs>
                    <clipPath id="clip0_1_70">
                      <rect width="25" height="25" fill="white"/>
                    </clipPath>
                  </defs>
                </svg>
                <div style={{ width: '280px' }}>
                  <p style={{
                    color: 'rgba(89, 86, 86, 0.86)',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '16px',
                    fontStyle: 'normal',
                    fontWeight: '500',
                    lineHeight: '23px'
                  }}>
                    Adresse: {removeStreetNumber(stage.site.adresse)}, {stage.site.code_postal}
                  </p>
                  <p style={{
                    color: 'rgba(89, 86, 86, 0.86)',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '16px',
                    fontStyle: 'normal',
                    fontWeight: '500',
                    lineHeight: '23px'
                  }}>
                    {stage.site.ville}
                  </p>
                </div>
              </div>

              {/* Separator line after address */}
              <div style={{
                width: '266px',
                height: '1px',
                background: '#C4C1C1'
              }} />

              {/* Hours */}
              <div className="flex gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none" className="flex-shrink-0 mt-1">
                  <path d="M12.5 6.24999V12.5L16.6667 14.5833M22.9167 12.5C22.9167 18.253 18.253 22.9167 12.5 22.9167C6.74704 22.9167 2.08334 18.253 2.08334 12.5C2.08334 6.74703 6.74704 2.08333 12.5 2.08333C18.253 2.08333 22.9167 6.74703 22.9167 12.5Z" stroke="#595656" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
                <div style={{ width: '280px' }}>
                  <p style={{
                    color: 'rgba(89, 86, 86, 0.86)',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '16px',
                    fontStyle: 'normal',
                    fontWeight: '500',
                    lineHeight: '23px'
                  }}>
                    Horaires: 08h15-12h30
                  </p>
                  <p style={{
                    color: 'rgba(89, 86, 86, 0.86)',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '16px',
                    fontStyle: 'normal',
                    fontWeight: '500',
                    lineHeight: '23px'
                  }}>
                    et 13h30-16h30
                  </p>
                </div>
              </div>

              {/* Separator line after hours */}
              <div style={{
                width: '266px',
                height: '1px',
                background: '#C4C1C1'
              }} />

              {/* Prefecture agreement */}
              <div className="flex gap-3">
                <Image
                  src="/flag-france.png"
                  alt="Drapeau français"
                  width={32}
                  height={21}
                  className="flex-shrink-0 mt-1 rounded-lg"
                  style={{
                    width: '32px',
                    height: '21px',
                    borderRadius: '10px'
                  }}
                />
                <div style={{ width: '280px' }}>
                  <p style={{
                    color: 'rgba(89, 86, 86, 0.86)',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '16px',
                    fontStyle: 'normal',
                    fontWeight: '500',
                    lineHeight: '23px'
                  }}>
                    Agrement n°: 25
                  </p>
                  <p style={{
                    color: 'rgba(89, 86, 86, 0.86)',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '16px',
                    fontStyle: 'normal',
                    fontWeight: '500',
                    lineHeight: '23px'
                  }}>
                    R130060009006 par la Préfecture des Bouches du Rhône
                  </p>
                </div>
              </div>
            </div>

            {/* Right column - Benefits */}
            <div style={{
              display: 'flex',
              width: '373px',
              height: '284px',
              padding: '15px 10px',
              flexDirection: 'column',
              justifyContent: 'flex-start',
              alignItems: 'flex-start',
              gap: '3px',
              flexShrink: 0,
              background: '#F5F5F5',
              borderRadius: '0px',
              marginLeft: '-10px',
              marginTop: '8px'
            }}>
              <div className="flex gap-3 items-start">
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="26" viewBox="0 0 25 26" fill="none" className="flex-shrink-0" style={{ width: '24.513px', height: '25.252px' }}>
                  <path d="M9.19219 11.5737L12.2563 14.7302L22.4698 4.20863M21.4485 12.6259V19.991C21.4485 20.5491 21.2332 21.0843 20.8502 21.479C20.4671 21.8736 19.9475 22.0953 19.4057 22.0953H5.10677C4.56501 22.0953 4.04544 21.8736 3.66235 21.479C3.27927 21.0843 3.06406 20.5491 3.06406 19.991V5.26079C3.06406 4.70269 3.27927 4.16745 3.66235 3.77281C4.04544 3.37818 4.56501 3.15647 5.10677 3.15647H16.3417" stroke="#C4A226" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
                <p style={{
                  width: '305px',
                  height: '44px',
                  color: 'rgba(6, 6, 6, 0.86)',
                  fontFamily: 'var(--font-poppins)',
                  fontSize: '14px',
                  fontStyle: 'normal',
                  fontWeight: '400',
                  lineHeight: '22px'
                }}>
                  Stage agréé tout type de stage (volontaire et obligatoire)
                </p>
              </div>

              <div className="flex gap-3 items-start">
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="26" viewBox="0 0 25 26" fill="none" className="flex-shrink-0" style={{ width: '24.513px', height: '25.252px' }}>
                  <path d="M9.19219 11.5737L12.2563 14.7302L22.4698 4.20863M21.4485 12.6259V19.991C21.4485 20.5491 21.2332 21.0843 20.8502 21.479C20.4671 21.8736 19.9475 22.0953 19.4057 22.0953H5.10677C4.56501 22.0953 4.04544 21.8736 3.66235 21.479C3.27927 21.0843 3.06406 20.5491 3.06406 19.991V5.26079C3.06406 4.70269 3.27927 4.16745 3.66235 3.77281C4.04544 3.37818 4.56501 3.15647 5.10677 3.15647H16.3417" stroke="#C4A226" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
                <p style={{
                  width: '305px',
                  color: 'rgba(6, 6, 6, 0.86)',
                  fontFamily: 'var(--font-poppins)',
                  fontSize: '14px',
                  fontStyle: 'normal',
                  fontWeight: '400',
                  lineHeight: '22px'
                }}>
                  +4 points en 48h
                </p>
              </div>

              <div className="flex gap-3 items-start">
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="26" viewBox="0 0 25 26" fill="none" className="flex-shrink-0" style={{ width: '24.513px', height: '25.252px' }}>
                  <path d="M9.19219 11.5737L12.2563 14.7302L22.4698 4.20863M21.4485 12.6259V19.991C21.4485 20.5491 21.2332 21.0843 20.8502 21.479C20.4671 21.8736 19.9475 22.0953 19.4057 22.0953H5.10677C4.56501 22.0953 4.04544 21.8736 3.66235 21.479C3.27927 21.0843 3.06406 20.5491 3.06406 19.991V5.26079C3.06406 4.70269 3.27927 4.16745 3.66235 3.77281C4.04544 3.37818 4.56501 3.15647 5.10677 3.15647H16.3417" stroke="#C4A226" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
                <p style={{
                  width: '305px',
                  color: 'rgba(6, 6, 6, 0.86)',
                  fontFamily: 'var(--font-poppins)',
                  fontSize: '14px',
                  fontStyle: 'normal',
                  fontWeight: '400',
                  lineHeight: '22px'
                }}>
                  Aucun examen
                </p>
              </div>

              <div className="flex gap-3 items-start">
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="26" viewBox="0 0 25 26" fill="none" className="flex-shrink-0" style={{ width: '24.513px', height: '25.252px' }}>
                  <path d="M9.19219 11.5737L12.2563 14.7302L22.4698 4.20863M21.4485 12.6259V19.991C21.4485 20.5491 21.2332 21.0843 20.8502 21.479C20.4671 21.8736 19.9475 22.0953 19.4057 22.0953H5.10677C4.56501 22.0953 4.04544 21.8736 3.66235 21.479C3.27927 21.0843 3.06406 20.5491 3.06406 19.991V5.26079C3.06406 4.70269 3.27927 4.16745 3.66235 3.77281C4.04544 3.37818 4.56501 3.15647 5.10677 3.15647H16.3417" stroke="#C4A226" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
                <p style={{
                  width: '305px',
                  height: '44px',
                  color: 'rgba(6, 6, 6, 0.86)',
                  fontFamily: 'var(--font-poppins)',
                  fontSize: '14px',
                  fontStyle: 'normal',
                  fontWeight: '400',
                  lineHeight: '22px'
                }}>
                  Attestation officielle remise le 2ème jour
                </p>
              </div>

              <div className="flex gap-3 items-start">
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="26" viewBox="0 0 25 26" fill="none" className="flex-shrink-0" style={{ width: '24.513px', height: '25.252px' }}>
                  <path d="M9.19219 11.5737L12.2563 14.7302L22.4698 4.20863M21.4485 12.6259V19.991C21.4485 20.5491 21.2332 21.0843 20.8502 21.479C20.4671 21.8736 19.9475 22.0953 19.4057 22.0953H5.10677C4.56501 22.0953 4.04544 21.8736 3.66235 21.479C3.27927 21.0843 3.06406 20.5491 3.06406 19.991V5.26079C3.06406 4.70269 3.27927 4.16745 3.66235 3.77281C4.04544 3.37818 4.56501 3.15647 5.10677 3.15647H16.3417" stroke="#C4A226" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
                <p style={{
                  width: '305px',
                  height: '44px',
                  color: 'rgba(6, 6, 6, 0.86)',
                  fontFamily: 'var(--font-poppins)',
                  fontSize: '14px',
                  fontStyle: 'normal',
                  fontWeight: '400',
                  lineHeight: '22px'
                }}>
                  Report ou remboursement en quelques clics sur votre Espace Client
                </p>
              </div>

              <div className="flex gap-3 items-start">
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="26" viewBox="0 0 25 26" fill="none" className="flex-shrink-0" style={{ width: '24.513px', height: '25.252px' }}>
                  <path d="M9.19219 11.5737L12.2563 14.7302L22.4698 4.20863M21.4485 12.6259V19.991C21.4485 20.5491 21.2332 21.0843 20.8502 21.479C20.4671 21.8736 19.9475 22.0953 19.4057 22.0953H5.10677C4.56501 22.0953 4.04544 21.8736 3.66235 21.479C3.27927 21.0843 3.06406 20.5491 3.06406 19.991V5.26079C3.06406 4.70269 3.27927 4.16745 3.66235 3.77281C4.04544 3.37818 4.56501 3.15647 5.10677 3.15647H16.3417" stroke="#C4A226" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
                <p style={{
                  width: '305px',
                  height: '44px',
                  color: 'rgba(6, 6, 6, 0.86)',
                  fontFamily: 'var(--font-poppins)',
                  fontSize: '14px',
                  fontStyle: 'normal',
                  fontWeight: '400',
                  lineHeight: '22px'
                }}>
                  Convocation envoyée immédiatement par email après inscription
                </p>
              </div>
            </div>
          </div>

          {/* Buttons */}
          <div className="flex gap-4 justify-center">
            <button
              onClick={onClose}
              style={{
                display: 'flex',
                height: '40px',
                width: '80px',
                padding: '7px 15px',
                justifyContent: 'center',
                alignItems: 'center',
                gap: '20px',
                borderRadius: '12px',
                background: '#E0E0E0',
                color: '#000',
                fontFamily: 'var(--font-poppins)',
                fontSize: '13px',
                fontStyle: 'normal',
                fontWeight: 300,
                lineHeight: 'normal',
                letterSpacing: '1.05px',
                border: 'none',
                cursor: 'pointer'
              }}
            >
              Fermer
            </button>
            <Link
              href={slug ? `/stages-recuperation-points/${slug}/${stage.id}/inscription` : `/stages-recuperation-points/${city.toUpperCase()}-${stage.site.code_postal}/${stage.id}/inscription`}
              onClick={onClose}
              style={{
                display: 'flex',
                width: '180px',
                height: '40px',
                padding: '7px 15px',
                justifyContent: 'center',
                alignItems: 'center',
                flexShrink: 0,
                borderRadius: '12px',
                background: '#41A334',
                color: '#FFF',
                textAlign: 'center',
                fontFamily: 'var(--font-poppins)',
                fontSize: '13px',
                fontStyle: 'normal',
                fontWeight: 400,
                lineHeight: 'normal',
                letterSpacing: '1.05px',
                textDecoration: 'none',
                whiteSpace: 'nowrap'
              }}
            >
              Sélectionner ce stage
            </Link>
          </div>
        </div>
      </div>
      </div>

      {/* Mobile Bottom Sheet */}
      <div
        className="md:hidden fixed inset-0 z-50"
        style={{
          backgroundColor: isAnimating ? 'rgba(0, 0, 0, 0.5)' : 'transparent',
          transition: 'background-color 0.3s ease-in-out',
          pointerEvents: isAnimating ? 'auto' : 'none'
        }}
        onClick={handleClose}
        onKeyDown={handleKeyDown}
        role="dialog"
        aria-modal="true"
      >
        <div
          ref={sheetRef}
          className="fixed bottom-0 left-0 right-0 bg-white overflow-y-auto"
          style={{
            borderTopLeftRadius: '20px',
            borderTopRightRadius: '20px',
            maxHeight: '90vh',
            transform: isAnimating ? 'translateY(0)' : 'translateY(100%)',
            transition: 'transform 0.3s ease-out'
          }}
          onClick={e => e.stopPropagation()}
          onTouchStart={handleTouchStart}
          onTouchMove={handleTouchMove}
          onTouchEnd={handleTouchEnd}
        >
          {/* Drag indicator */}
          <div className="flex justify-center pt-3 pb-2">
            <div style={{
              width: '40px',
              height: '4px',
              backgroundColor: '#D9D9D9',
              borderRadius: '2px'
            }} />
          </div>

          {/* Content */}
          <div className="px-4 pb-6 flex flex-col items-center">
            {/* Title - on grey background */}
            <div
              className="w-full mb-3 py-2 px-4"
              style={{
                background: '#F5F5F5',
                borderRadius: '8px'
              }}
            >
              <h2
                className="text-center"
                style={{
                  color: 'rgba(34, 34, 34, 0.86)',
                  fontFamily: 'var(--font-poppins)',
                  fontSize: '16px',
                  fontStyle: 'normal',
                  fontWeight: '500',
                  lineHeight: '24px'
                }}
              >
                Stage {formatDate(stage.date_start, stage.date_end)}
              </h2>
            </div>

            {/* Price - in black */}
            <p
              className="text-center mb-1"
              style={{
                color: '#000',
                textAlign: 'center',
                fontFamily: 'var(--font-poppins)',
                fontSize: '18px',
                fontStyle: 'normal',
                fontWeight: '500',
                lineHeight: '24px'
              }}
            >
              {stage.prix.toFixed(0)}€ TTC
            </p>

            {/* Places disponibles - in green */}
            <p
              className="text-center mb-2"
              style={{
                color: '#267E1C',
                fontFamily: 'var(--font-poppins)',
                fontSize: '13px',
                fontStyle: 'normal',
                fontWeight: '400',
                lineHeight: '18px'
              }}
            >
              Places disponibles
            </p>

            {/* Black separator line */}
            <div style={{
              width: '100%',
              height: '1px',
              background: '#000',
              marginBottom: '16px'
            }} />

            {/* Main Widget Container - no shadow */}
            <div style={{
              display: 'flex',
              width: '366px',
              padding: '10px 8px',
              flexDirection: 'column',
              alignItems: 'center',
              gap: '10px',
              flexShrink: 0,
              borderRadius: '20px',
              border: '1px solid #EAEAEA',
              background: '#FFF'
            }}>
              {/* Address */}
              <div className="flex gap-2 mb-3">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 25 25" fill="none" className="flex-shrink-0 mt-0.5">
                <g clipPath="url(#clip0_1_70)">
                  <path d="M21.875 10.4167C21.875 17.7083 12.5 23.9583 12.5 23.9583C12.5 23.9583 3.125 17.7083 3.125 10.4167C3.125 7.93026 4.11272 5.54569 5.87087 3.78754C7.62903 2.02938 10.0136 1.04166 12.5 1.04166C14.9864 1.04166 17.371 2.02938 19.1291 3.78754C20.8873 5.54569 21.875 7.93026 21.875 10.4167Z" stroke="#595656" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                  <path d="M12.5 13.5417C14.2259 13.5417 15.625 12.1426 15.625 10.4167C15.625 8.69077 14.2259 7.29166 12.5 7.29166C10.7741 7.29166 9.375 8.69077 9.375 10.4167C9.375 12.1426 10.7741 13.5417 12.5 13.5417Z" stroke="#595656" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                </g>
                <defs>
                  <clipPath id="clip0_1_70">
                    <rect width="25" height="25" fill="white"/>
                  </clipPath>
                </defs>
              </svg>
              <div>
                <p style={{
                  color: 'rgba(89, 86, 86, 0.86)',
                  fontFamily: 'var(--font-poppins)',
                  fontSize: '13px',
                  fontStyle: 'normal',
                  fontWeight: '400',
                  lineHeight: '18px'
                }}>
                  {removeStreetNumber(stage.site.adresse)}, {stage.site.code_postal} {stage.site.ville}
                </p>
              </div>
            </div>

            {/* Hours */}
            <div className="flex gap-2 mb-3">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 25 25" fill="none" className="flex-shrink-0 mt-0.5">
                <path d="M12.5 6.24999V12.5L16.6667 14.5833M22.9167 12.5C22.9167 18.253 18.253 22.9167 12.5 22.9167C6.74704 22.9167 2.08334 18.253 2.08334 12.5C2.08334 6.74703 6.74704 2.08333 12.5 2.08333C18.253 2.08333 22.9167 6.74703 22.9167 12.5Z" stroke="#595656" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
              <div>
                <p style={{
                  color: 'rgba(89, 86, 86, 0.86)',
                  fontFamily: 'var(--font-poppins)',
                  fontSize: '13px',
                  fontStyle: 'normal',
                  fontWeight: '400',
                  lineHeight: '18px'
                }}>
                  08h15-12h30 et 13h30-16h30
                </p>
              </div>
            </div>

            {/* Prefecture agreement */}
            <div className="flex gap-2 mb-4">
              <Image
                src="/flag-france.png"
                alt="Drapeau français"
                width={24}
                height={16}
                className="flex-shrink-0 mt-0.5 rounded-lg"
                style={{
                  width: '24px',
                  height: '16px',
                  borderRadius: '6px'
                }}
              />
              <div>
                <p style={{
                  color: 'rgba(89, 86, 86, 0.86)',
                  fontFamily: 'var(--font-poppins)',
                  fontSize: '13px',
                  fontStyle: 'normal',
                  fontWeight: '400',
                  lineHeight: '18px'
                }}>
                  Agrement n°: 25 R130060009006 par la Préfecture des Bouches du Rhône
                </p>
              </div>
            </div>

              {/* Nested Benefits Widget */}
              <div style={{
                display: 'flex',
                width: '330px',
                height: '250px',
                padding: '0 16px',
                flexDirection: 'column',
                justifyContent: 'center',
                alignItems: 'flex-start',
                flexShrink: 0,
                borderRadius: '8px',
                border: '1px solid #9B9A9A'
              }}>
                {/* Benefits list */}
                <div className="space-y-2">
                  {[
                    'Stage agréé Préfecture tout type de stage (volontaire et obligatoire)',
                    '+4 points en 48h',
                    'Aucun examen',
                    'Attestation officielle remise le 2ème jour',
                    'Report ou remboursement en quelques clics sur votre Espace Client',
                    'Convocation envoyée immédiatement par email après inscription'
                  ].map((benefit, index) => (
                    <div key={index} className="flex gap-2 items-start">
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 25 26" fill="none" className="flex-shrink-0 mt-0.5">
                        <path d="M9.19219 11.5737L12.2563 14.7302L22.4698 4.20863M21.4485 12.6259V19.991C21.4485 20.5491 21.2332 21.0843 20.8502 21.479C20.4671 21.8736 19.9475 22.0953 19.4057 22.0953H5.10677C4.56501 22.0953 4.04544 21.8736 3.66235 21.479C3.27927 21.0843 3.06406 20.5491 3.06406 19.991V5.26079C3.06406 4.70269 3.27927 4.16745 3.66235 3.77281C4.04544 3.37818 4.56501 3.15647 5.10677 3.15647H16.3417" stroke="#C4A226" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                      </svg>
                      <p style={{
                        color: 'rgba(6, 6, 6, 0.86)',
                        fontFamily: 'var(--font-poppins)',
                        fontSize: '12px',
                        fontStyle: 'normal',
                        fontWeight: '400',
                        lineHeight: '18px'
                      }}>
                        {benefit}
                      </p>
                    </div>
                  ))}
                </div>
              </div>
            </div>

            {/* Buttons - Outside main widget, stacked vertically */}
            <div className="flex flex-col gap-2 mt-4 items-center">
              <button
                onClick={handleClose}
                style={{
                  display: 'flex',
                  height: '44px',
                  padding: '7px 15px',
                  justifyContent: 'center',
                  alignItems: 'center',
                  gap: '20px',
                  flexShrink: 0,
                  borderRadius: '12px',
                  background: '#E0E0E0',
                  color: '#000',
                  fontFamily: 'Poppins',
                  fontSize: '15px',
                  fontStyle: 'normal',
                  fontWeight: 300,
                  lineHeight: 'normal',
                  letterSpacing: '1.05px',
                  border: 'none',
                  cursor: 'pointer'
                }}
              >
                Fermer
              </button>
              <Link
                href={slug ? `/stages-recuperation-points/${slug}/${stage.id}/inscription` : `/stages-recuperation-points/${city.toUpperCase()}-${stage.site.code_postal}/${stage.id}/inscription`}
                onClick={handleClose}
                style={{
                  display: 'flex',
                  width: '197px',
                  padding: '7px 15px',
                  justifyContent: 'center',
                  alignItems: 'center',
                  gap: '20px',
                  borderRadius: '12px',
                  background: '#41A334',
                  color: '#FFF',
                  textAlign: 'center',
                  fontFamily: 'Poppins',
                  fontSize: '15px',
                  fontStyle: 'normal',
                  fontWeight: 400,
                  lineHeight: 'normal',
                  letterSpacing: '1.05px',
                  textDecoration: 'none',
                  whiteSpace: 'nowrap'
                }}
              >
                Sélectionner ce stage
              </Link>
            </div>
          </div>
        </div>
      </div>
    </>
  )
}
