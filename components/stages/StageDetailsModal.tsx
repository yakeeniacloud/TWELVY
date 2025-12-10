'use client'

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
  if (!isOpen) return null

  const formatDate = (dateStart: string, dateEnd: string) => {
    const start = new Date(dateStart)
    const end = new Date(dateEnd)

    const dayStart = start.toLocaleDateString('fr-FR', { weekday: 'short' }).replace('.', '')
    const dayEnd = end.toLocaleDateString('fr-FR', { weekday: 'short' }).replace('.', '')
    const dayNumStart = start.getDate()
    const dayNumEnd = end.getDate()
    const month = start.toLocaleDateString('fr-FR', { month: 'long' })
    const year = start.getFullYear()

    return `stage du ${dayStart} ${dayNumStart} et ${dayEnd} ${dayNumEnd} ${month} ${year}`
  }

  // Handle escape key
  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === 'Escape') {
      onClose()
    }
  }

  return (
    <div
      className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      onClick={onClose}
      onKeyDown={handleKeyDown}
      role="dialog"
      aria-modal="true"
    >
      <div
        className="bg-white rounded-2xl shadow-2xl max-w-4xl w-full relative"
        onClick={e => e.stopPropagation()}
      >
        {/* Close button - positioned slightly outside the popup */}
        <button
          onClick={onClose}
          className="absolute flex items-center justify-center z-10 hover:opacity-80 transition-opacity"
          style={{
            top: '-10px',
            right: '-10px',
            width: '40px',
            height: '40px',
            background: 'transparent',
            border: 'none',
            cursor: 'pointer'
          }}
          aria-label="Fermer"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 44 44" fill="none">
            <path d="M28 16L16 28M16 16L28 28M42 22C42 33.0457 33.0457 42 22 42C10.9543 42 2 33.0457 2 22C2 10.9543 10.9543 2 22 2C33.0457 2 42 10.9543 42 22Z" stroke="#A1A1A1" strokeWidth="4" strokeLinecap="round" strokeLinejoin="round"/>
          </svg>
        </button>

        {/* Content */}
        <div className="p-12">
          {/* Title */}
          <h2
            className="text-center mb-4"
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
            {formatDate(stage.date_start, stage.date_end)}
          </h2>

          {/* Price */}
          <p
            className="text-center mb-8"
            style={{
              width: '235px',
              height: '30px',
              flexShrink: 0,
              color: 'rgba(188, 71, 71, 0.86)',
              textAlign: 'center',
              fontFamily: 'var(--font-poppins)',
              fontSize: '24px',
              fontStyle: 'normal',
              fontWeight: '400',
              lineHeight: '35px',
              margin: '0 auto 32px'
            }}
          >
            {stage.prix.toFixed(0)}€ TTC
          </p>

          {/* Two column layout */}
          <div className="grid grid-cols-2 gap-8 mb-8">
            {/* Left column */}
            <div className="space-y-6">
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
                <div style={{ width: '173px' }}>
                  <p style={{
                    color: 'rgba(89, 86, 86, 0.86)',
                    fontFamily: 'var(--font-poppins)',
                    fontSize: '16px',
                    fontStyle: 'normal',
                    fontWeight: '500',
                    lineHeight: '23px'
                  }}>
                    Adresse: {stage.site.adresse}, {stage.site.code_postal}
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

              {/* Hours */}
              <div className="flex gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none" className="flex-shrink-0 mt-1">
                  <path d="M12.5 6.24999V12.5L16.6667 14.5833M22.9167 12.5C22.9167 18.253 18.253 22.9167 12.5 22.9167C6.74704 22.9167 2.08334 18.253 2.08334 12.5C2.08334 6.74703 6.74704 2.08333 12.5 2.08333C18.253 2.08333 22.9167 6.74703 22.9167 12.5Z" stroke="#595656" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
                <div style={{ width: '173px' }}>
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
                <div style={{ width: '173px' }}>
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
            <div className="bg-gray-100 rounded-lg p-6 space-y-4">
              <div className="flex gap-3 items-start">
                <svg className="w-6 h-6 flex-shrink-0 text-yellow-500" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                </svg>
                <p className="text-sm text-gray-800">
                  Stage agréé tout type de stage (volontaire et obligatoire)
                </p>
              </div>

              <div className="flex gap-3 items-start">
                <svg className="w-6 h-6 flex-shrink-0 text-yellow-500" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                </svg>
                <p className="text-sm text-gray-800">+4 points en 48h</p>
              </div>

              <div className="flex gap-3 items-start">
                <svg className="w-6 h-6 flex-shrink-0 text-yellow-500" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                </svg>
                <p className="text-sm text-gray-800">Aucun examen</p>
              </div>

              <div className="flex gap-3 items-start">
                <svg className="w-6 h-6 flex-shrink-0 text-yellow-500" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                </svg>
                <p className="text-sm text-gray-800">
                  Attestation officielle remise le 2ème jour
                </p>
              </div>

              <div className="flex gap-3 items-start">
                <svg className="w-6 h-6 flex-shrink-0 text-yellow-500" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                </svg>
                <p className="text-sm text-gray-800">
                  Report ou remboursement en quelques clics sur votre Espace Client
                </p>
              </div>

              <div className="flex gap-3 items-start">
                <svg className="w-6 h-6 flex-shrink-0 text-yellow-500" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                </svg>
                <p className="text-sm text-gray-800">
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
                height: '44px',
                width: '88px',
                padding: '7px 15px',
                justifyContent: 'center',
                alignItems: 'center',
                gap: '20px',
                borderRadius: '12px',
                background: '#E0E0E0',
                color: '#000',
                fontFamily: 'var(--font-poppins)',
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
              onClick={onClose}
              style={{
                display: 'flex',
                width: '197px',
                height: '44px',
                padding: '7px 15px',
                justifyContent: 'center',
                alignItems: 'center',
                flexShrink: 0,
                borderRadius: '12px',
                background: '#41A334',
                color: '#FFF',
                textAlign: 'center',
                fontFamily: 'var(--font-poppins)',
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
  )
}
