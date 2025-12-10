'use client'

import Link from 'next/link'

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
        {/* Close button */}
        <button
          onClick={onClose}
          className="absolute top-6 right-6 w-12 h-12 rounded-full bg-gray-400 flex items-center justify-center text-white text-3xl hover:bg-gray-500 transition-colors z-10"
          aria-label="Fermer"
        >
          ×
        </button>

        {/* Content */}
        <div className="p-12">
          {/* Title */}
          <h2 className="text-3xl font-normal text-center mb-4 text-black">
            {formatDate(stage.date_start, stage.date_end)}
          </h2>

          {/* Price */}
          <p className="text-4xl font-normal text-center mb-8 text-red-500">
            {stage.prix.toFixed(0)}€ TTC
          </p>

          {/* Two column layout */}
          <div className="grid grid-cols-2 gap-8 mb-8">
            {/* Left column */}
            <div className="space-y-6">
              {/* Address */}
              <div className="flex gap-3">
                <svg className="w-6 h-6 flex-shrink-0 mt-1" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <div>
                  <p className="text-base text-gray-700">
                    <strong>Adresse:</strong> {stage.site.adresse}, {stage.site.code_postal}
                  </p>
                  <p className="text-base text-gray-700">{stage.site.ville}</p>
                </div>
              </div>

              {/* Hours */}
              <div className="flex gap-3">
                <svg className="w-6 h-6 flex-shrink-0 mt-1" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <circle cx="12" cy="12" r="10" strokeWidth={2} />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6l4 2" />
                </svg>
                <div>
                  <p className="text-base text-gray-700">
                    <strong>Horaires:</strong> 08h15-12h30
                  </p>
                  <p className="text-base text-gray-700">et 13h30-16h30</p>
                </div>
              </div>

              {/* Prefecture agreement */}
              <div className="flex gap-3">
                <svg className="w-6 h-6 flex-shrink-0 mt-1" viewBox="0 0 24 24" fill="none">
                  <rect x="4" y="4" width="16" height="16" rx="2" fill="#0055A4" />
                  <rect x="12" y="4" width="8" height="16" fill="#EF4135" />
                </svg>
                <div>
                  <p className="text-base text-gray-700">
                    <strong>Agrement n°: 25</strong>
                  </p>
                  <p className="text-base text-gray-700">
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
              className="px-8 py-3 bg-gray-300 text-gray-800 rounded-full font-medium hover:bg-gray-400 transition-colors"
            >
              Fermer
            </button>
            <Link
              href={slug ? `/stages-recuperation-points/${slug}/${stage.id}/inscription` : `/stages-recuperation-points/${city.toUpperCase()}-${stage.site.code_postal}/${stage.id}/inscription`}
              onClick={onClose}
              className="px-8 py-3 bg-green-600 text-white rounded-full font-medium hover:bg-green-700 transition-colors"
            >
              Sélectionner ce stage
            </Link>
          </div>
        </div>
      </div>
    </div>
  )
}
