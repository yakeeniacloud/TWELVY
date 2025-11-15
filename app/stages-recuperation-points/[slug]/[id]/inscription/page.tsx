'use client'

import { useParams } from 'next/navigation'
import Link from 'next/link'

export default function InscriptionPage() {
  const params = useParams()

  // Extract city from slug: "MARSEILLE-13001" -> "MARSEILLE"
  const fullSlug = (params.slug as string) || ''
  const lastHyphenIndex = fullSlug ? fullSlug.lastIndexOf('-') : -1
  const city = lastHyphenIndex > 0 ? fullSlug.substring(0, lastHyphenIndex) : fullSlug

  const id = (params.id as string) || ''

  return (
    <div className="min-h-screen bg-white">
      {/* Back Button */}
      <div className="bg-gray-50 border-b border-gray-200 py-4">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Link
            href={`/stages-recuperation-points/${fullSlug}/${id}`}
            className="text-blue-600 hover:text-blue-800 font-semibold"
          >
            ← Retour aux détails
          </Link>
        </div>
      </div>

      <div className="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 py-12">
        <h1 className="text-4xl font-bold text-gray-900 mb-2">
          Formulaire d'inscription
        </h1>
        <p className="text-gray-600 mb-8">
          Stage ID: {id} | Ville: {city}
        </p>

        {/* Progress Indicator */}
        <div className="flex justify-between mb-8">
          {['Formulaire', 'Règlement', 'Personnalisation', 'Confirmation'].map((step, index) => (
            <div key={step} className="flex flex-col items-center">
              <div className={`w-10 h-10 rounded-full flex items-center justify-center font-bold ${
                index === 0
                  ? 'bg-blue-600 text-white'
                  : 'bg-gray-200 text-gray-600'
              }`}>
                {index + 1}
              </div>
              <p className="text-sm mt-2 text-gray-600">{step}</p>
            </div>
          ))}
        </div>

        {/* Placeholder */}
        <div className="bg-gray-50 p-8 rounded-lg border border-gray-200">
          <p className="text-gray-700 text-center mb-4">
            Le formulaire d'inscription sera implémenté demain.
          </p>
          <p className="text-sm text-gray-500 text-center">
            Les champs suivants seront disponibles: civilité, nom, prénom, date de naissance, email, téléphone, etc.
          </p>
        </div>
      </div>
    </div>
  )
}
