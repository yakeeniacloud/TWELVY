'use client'

import { useEffect, useState } from 'react'
import { useParams, useSearchParams } from 'next/navigation'

interface Stage {
  id: number
  id_site: number
  date1: string
  date2: string
  prix: number
  nb_places_allouees: number
  nb_inscrits: number
  site_nom: string
  ville: string
  adresse: string
  code_postal: string
  latitude?: number
  longitude?: number
}

interface FormData {
  civilite: string
  nom: string
  prenom: string
  adresse: string
  code_postal: string
  ville: string
  date_naissance: string
  email: string
  mobile: string
  garantie_serenite: boolean
}

export default function ConfirmationPage() {
  const params = useParams()
  const searchParams = useSearchParams()

  // Extract city from slug: "MARSEILLE-13001" -> "MARSEILLE"
  const fullSlug = (params.slug as string) || ''
  const lastHyphenIndex = fullSlug ? fullSlug.lastIndexOf('-') : -1
  const city = lastHyphenIndex > 0 ? fullSlug.substring(0, lastHyphenIndex) : fullSlug

  const id = (params.id as string) || ''

  const [stage, setStage] = useState<Stage | null>(null)
  const [formData, setFormData] = useState<FormData | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    async function fetchStage() {
      try {
        const response = await fetch(`/api/stages/${city.toUpperCase()}/${id}`)
        if (response.ok) {
          const data = await response.json()
          setStage(data.stage)
        }
      } catch (error) {
        console.error('Error fetching stage:', error)
      } finally {
        setLoading(false)
      }
    }

    fetchStage()

    // Get form data from URL params
    const storedData = sessionStorage.getItem('bookingFormData')
    if (storedData) {
      setFormData(JSON.parse(storedData))
      // Clear after reading
      sessionStorage.removeItem('bookingFormData')
    }
  }, [city, id])

  const formatDate = (dateString: string) => {
    const date = new Date(dateString)
    const day = date.getDate()
    const monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre']
    const month = monthNames[date.getMonth()]
    const year = date.getFullYear()
    return `${day} ${month} ${year}`
  }

  const formatDateWithDay = (dateString: string) => {
    const date = new Date(dateString)
    const dayNames = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi']
    const dayName = dayNames[date.getDay()]
    return `${dayName} ${formatDate(dateString)}`
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-white flex items-center justify-center">
        <p className="text-gray-600">Chargement...</p>
      </div>
    )
  }

  if (!stage) {
    return (
      <div className="min-h-screen bg-white flex items-center justify-center">
        <p className="text-red-600">Stage non trouvé</p>
      </div>
    )
  }

  const totalPrice = stage.prix + (formData?.garantie_serenite ? 268 : 0)

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Progress Indicator */}
      <div className="bg-white border-b border-gray-200 py-6">
        <div className="mx-auto max-w-7xl px-4">
          <div className="flex items-center justify-between max-w-2xl mx-auto">
            {/* Step 1 - Completed */}
            <div className="flex flex-col items-center">
              <div className="w-10 h-10 rounded-full bg-blue-400 text-white flex items-center justify-center font-bold mb-2">
                1
              </div>
              <span className="text-xs text-gray-600">Formulaire</span>
            </div>
            <div className="flex-1 h-0.5 bg-gray-300 mx-2"></div>
            {/* Step 2 - Active */}
            <div className="flex flex-col items-center">
              <div className="w-10 h-10 rounded-full bg-red-500 text-white flex items-center justify-center font-bold mb-2">
                2
              </div>
              <span className="text-xs text-gray-600">Règlement</span>
            </div>
            <div className="flex-1 h-0.5 bg-gray-300 mx-2"></div>
            {/* Step 3 */}
            <div className="flex flex-col items-center">
              <div className="w-10 h-10 rounded-full bg-gray-300 text-white flex items-center justify-center font-bold mb-2">
                3
              </div>
              <span className="text-xs text-gray-600">Personnalisation</span>
            </div>
            <div className="flex-1 h-0.5 bg-gray-300 mx-2"></div>
            {/* Step 4 */}
            <div className="flex flex-col items-center">
              <div className="w-10 h-10 rounded-full bg-gray-300 text-white flex items-center justify-center font-bold mb-2">
                4
              </div>
              <span className="text-xs text-gray-600">Confirmation</span>
            </div>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="mx-auto max-w-7xl px-4 py-8">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
          {/* Left Column - Order Summary */}
          <div className="lg:col-span-8">
            <h1 className="text-2xl font-bold text-red-600 mb-6">Récapitulatif de votre commande</h1>

            {/* Stage Details Box */}
            <div className="bg-pink-50 border border-pink-200 rounded p-6 mb-6">
              <h2 className="text-lg font-bold text-gray-900 mb-4">
                Stage du {formatDateWithDay(stage.date1)} et {formatDateWithDay(stage.date2)}
              </h2>

              <div className="space-y-2 text-sm text-gray-700 mb-4">
                <p>155 rue Jean Jaurès {stage.site_nom}</p>
                <p>{stage.adresse}</p>
                <p>{stage.code_postal} {stage.ville.toUpperCase()}</p>
              </div>

              <div className="flex items-center gap-2 mb-3">
                <div className="w-6 h-4 bg-blue-600 relative overflow-hidden rounded-sm">
                  <div className="absolute left-0 top-0 bottom-0 w-1/3 bg-blue-700"></div>
                  <div className="absolute right-0 top-0 bottom-0 w-1/3 bg-red-600"></div>
                </div>
                <span className="text-sm font-semibold">Agrément R2006300010</span>
              </div>

              <p className="text-sm text-gray-600 mb-4">Valeur: 850€$</p>

              <div className="border-t pt-4">
                <h3 className="font-semibold text-gray-900 mb-3">Ce tarif comprend :</h3>
                <div className="space-y-2">
                  <div className="flex items-start gap-2">
                    <span className="text-green-600 text-sm">✓</span>
                    <span className="text-sm text-gray-700">14 heures de formation</span>
                  </div>
                  <div className="flex items-start gap-2">
                    <span className="text-green-600 text-sm">✓</span>
                    <span className="text-sm text-gray-700">L'utilisation de stage limitée à 48heures jour</span>
                  </div>
                  <div className="flex items-start gap-2">
                    <span className="text-green-600 text-sm">✓</span>
                    <span className="text-sm text-gray-700">La récupération automatique de 4 points</span>
                  </div>
                  <div className="flex items-start gap-2">
                    <span className="text-green-600 text-sm">✓</span>
                    <span className="text-sm text-gray-700">Un support de cours pour mieux anticiper mobilités (circulation préventive)</span>
                  </div>
                  <div className="flex items-start gap-2">
                    <span className="text-green-600 text-sm">✓</span>
                    <span className="text-sm text-gray-700">Un cas d'endommagement, le transfert sur un autre stage</span>
                  </div>
                  <div className="flex items-start gap-2">
                    <span className="text-green-600 text-sm">✓</span>
                    <span className="text-sm text-gray-700">Une attestation de suivi à envoyer permis (par la Préfecture)</span>
                  </div>
                </div>
              </div>
            </div>

            {/* Payment Button */}
            <button
              onClick={() => alert('Paiement à implémenter')}
              className="w-full bg-gradient-to-b from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-4 px-6 rounded text-lg transition-all"
            >
              Paiement
            </button>
          </div>

          {/* Right Sidebar - Price & Engagements */}
          <div className="lg:col-span-4">
            {/* Price Box */}
            <div className="bg-pink-50 border border-pink-200 rounded p-6 mb-6">
              <p className="text-sm text-gray-700 mb-2">Total à payer</p>
              <p className="text-5xl font-bold text-red-600">{totalPrice} €</p>
            </div>

            {/* Engagements */}
            <div className="bg-white border border-gray-200 p-6 rounded">
              <h2 className="text-lg font-bold text-gray-900 mb-4">
                NOS <span className="text-red-600">ENGAGEMENTS</span>
              </h2>
              <div className="space-y-4">
                {/* Engagement 1 */}
                <div className="flex gap-3">
                  <div className="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span className="text-amber-900 font-bold text-lg">+4</span>
                  </div>
                  <div>
                    <p className="text-sm font-semibold text-gray-900">+4 Points en 48h</p>
                    <button className="text-xs text-blue-700 hover:underline">
                      ▼ plus d'infos
                    </button>
                  </div>
                </div>

                {/* Engagement 2 */}
                <div className="flex gap-3">
                  <div className="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span className="text-amber-900 text-lg">✓</span>
                  </div>
                  <div>
                    <p className="text-sm font-semibold text-gray-900">Paiement Sécurisé</p>
                    <button className="text-xs text-blue-700 hover:underline">
                      ▼ plus d'infos
                    </button>
                  </div>
                </div>

                {/* Engagement 3 */}
                <div className="flex gap-3">
                  <div className="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span className="text-amber-900 text-lg">€</span>
                  </div>
                  <div>
                    <p className="text-sm font-semibold text-gray-900">Prix le Plus Bas Garanti</p>
                    <button className="text-xs text-blue-700 hover:underline">
                      ▼ plus d'infos
                    </button>
                  </div>
                </div>

                {/* Engagement 4 */}
                <div className="flex gap-3">
                  <div className="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span className="text-amber-900 text-lg">★</span>
                  </div>
                  <div>
                    <p className="text-sm font-semibold text-gray-900">Stages Agréés</p>
                    <button className="text-xs text-blue-700 hover:underline">
                      ▼ plus d'infos
                    </button>
                  </div>
                </div>

                {/* Engagement 5 */}
                <div className="flex gap-3">
                  <div className="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span className="text-amber-900 text-lg">↺</span>
                  </div>
                  <div>
                    <p className="text-sm font-semibold text-gray-900">14 Jours pour Changer d'Avis</p>
                    <button className="text-xs text-blue-700 hover:underline">
                      ▼ plus d'infos
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
