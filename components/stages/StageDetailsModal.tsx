'use client'

import { useState } from 'react'
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
  slug?: string  // Full slug like "MARSEILLE-13001"
}

export default function StageDetailsModal({
  stage,
  isOpen,
  onClose,
  city,
  slug,
}: StageDetailsModalProps) {
  const [mapType, setMapType] = useState<'map' | 'satellite'>('map')

  if (!isOpen) return null

  const { latitude, longitude } = stage.site
  const mapsUrl =
    latitude && longitude
      ? `https://www.google.com/maps/embed/v1/place?key=AIzaSyBFw0Qbyq9zTFTd-tUY6dZWTgaQzuU17R8&q=${latitude},${longitude}`
      : null

  const formatDate = (dateStart: string, dateEnd: string) => {
    const start = new Date(dateStart)
    const end = new Date(dateEnd)

    const dayStart = start.toLocaleDateString('fr-FR', { weekday: 'short' })
    const dayEnd = end.toLocaleDateString('fr-FR', { weekday: 'short' })
    const dateFormatStart = start.toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: 'long',
    })
    const dateFormatEnd = end.toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: 'long',
    })

    return `${dayStart} ${dateFormatStart} et ${dayEnd} ${dateFormatEnd}`
  }

  return (
    <div
      className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      onClick={onClose}
    >
      <div
        className="bg-white rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto flex flex-col lg:flex-row"
        onClick={e => e.stopPropagation()}
      >
        {/* Left: Google Maps */}
        <div className="w-full lg:w-1/2 bg-gray-100 min-h-96 lg:min-h-full relative">
          {mapsUrl ? (
            <iframe
              width="100%"
              height="100%"
              style={{ border: 0, minHeight: '400px' }}
              loading="lazy"
              allowFullScreen
              referrerPolicy="no-referrer-when-downgrade"
              src={mapsUrl}
            ></iframe>
          ) : (
            <div className="flex items-center justify-center h-full text-gray-500">
              Coordonnées non disponibles
            </div>
          )}

          {/* Close Button */}
          <button
            onClick={onClose}
            className="absolute top-4 right-4 bg-white rounded-full w-8 h-8 flex items-center justify-center text-2xl leading-none text-gray-600 hover:bg-gray-100 shadow-md"
          >
            ×
          </button>
        </div>

        {/* Right: Stage Details */}
        <div className="w-full lg:w-1/2 p-6 lg:p-8 flex flex-col justify-between bg-pink-50">
          <div>
            <h2 className="text-lg font-bold text-gray-900 mb-6">
              <span className="text-red-600">Détails du stage:</span>
            </h2>

            {/* Date */}
            <div className="mb-6">
              <p className="text-sm text-gray-700 font-semibold mb-1">
                {formatDate(stage.date_start, stage.date_end)}
              </p>
            </div>

            {/* Address */}
            <div className="mb-6">
              <h3 className="text-sm font-semibold text-gray-900 mb-2 flex items-center gap-2">
                <span>📍</span> Adresse exacte
              </h3>
              <p className="text-sm text-gray-700">
                {stage.site.adresse}
                <br />
                {stage.site.code_postal} {stage.site.ville}
              </p>
            </div>

            {/* Hours */}
            <div className="mb-6">
              <h3 className="text-sm font-semibold text-gray-900 mb-2 flex items-center gap-2">
                <span>⏰</span> Horaires du stage (2 jours)
              </h3>
              <p className="text-sm text-gray-700">
                Matin : de 08h45 à 12h30
                <br />
                Après-midi : de 13h30 à 17h00
              </p>
            </div>
          </div>

          {/* Price and Button */}
          <div>
            <div className="mb-6 text-right">
              <p className="text-4xl font-bold text-red-600">
                {stage.prix.toFixed(0)} €
              </p>
            </div>

            <Link
              href={slug ? `/stages-recuperation-points-${slug}/${stage.id}` : `/stages-recuperation-points-${city.toUpperCase()}-${stage.site.code_postal}/${stage.id}`}
              onClick={onClose}
              className="block w-full bg-gradient-to-b from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-3 rounded text-center transition-all"
            >
              Sélectionner
            </Link>
          </div>
        </div>
      </div>
    </div>
  )
}
