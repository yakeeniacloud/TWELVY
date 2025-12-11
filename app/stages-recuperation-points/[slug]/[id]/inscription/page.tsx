'use client'

import { useState, useEffect } from 'react'
import { useParams } from 'next/navigation'
import Image from 'next/image'

interface Stage {
  id: number
  id_site: number
  date_start: string
  date_end: string
  prix: number
  nb_places: number
  site: {
    nom: string
    adresse: string
    code_postal: string
    ville: string
    latitude: string
    longitude: string
  }
}

export default function InscriptionPage() {
  const params = useParams()

  const fullSlug = (params.slug as string) || ''
  const lastHyphenIndex = fullSlug ? fullSlug.lastIndexOf('-') : -1
  const city = (lastHyphenIndex > 0 ? fullSlug.substring(0, lastHyphenIndex) : fullSlug).toUpperCase()
  const id = (params.id as string) || ''

  const [stage, setStage] = useState<Stage | null>(null)
  const [loading, setLoading] = useState(true)

  // Form state
  const [civilite, setCivilite] = useState('')
  const [nom, setNom] = useState('')
  const [prenom, setPrenom] = useState('')
  const [email, setEmail] = useState('')
  const [telephone, setTelephone] = useState('')
  const [garantieSerenite, setGarantieSerenite] = useState(false)
  const [cgvAccepted, setCgvAccepted] = useState(false)

  useEffect(() => {
    async function fetchStage() {
      try {
        setLoading(true)
        const response = await fetch(`/api/stages/${city}`)
        if (!response.ok) throw new Error('Failed to fetch stage')

        const data = await response.json()
        const foundStage = data.stages?.find((s: Stage) => s.id.toString() === id)

        if (foundStage) {
          setStage(foundStage)
        }
      } catch (err) {
        console.error('Error fetching stage:', err)
      } finally {
        setLoading(false)
      }
    }

    if (city && id) {
      fetchStage()
    }
  }, [city, id])

  const formatDate = (dateStart: string, dateEnd: string) => {
    const start = new Date(dateStart)
    const end = new Date(dateEnd)
    const dayStart = start.toLocaleDateString('fr-FR', { weekday: 'short' }).replace('.', '')
    const dayEnd = end.toLocaleDateString('fr-FR', { weekday: 'short' }).replace('.', '')
    const dayNumStart = start.getDate()
    const dayNumEnd = end.getDate()
    const month = start.toLocaleDateString('fr-FR', { month: 'long' })
    const year = start.getFullYear()

    const capitalizedDayStart = dayStart.charAt(0).toUpperCase() + dayStart.slice(1)
    const capitalizedDayEnd = dayEnd.charAt(0).toUpperCase() + dayEnd.slice(1)

    return `${capitalizedDayStart} ${dayNumStart} et ${capitalizedDayEnd} ${dayNumEnd} ${month} ${year}`
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    // TODO: Submit to database
    console.log('Form submitted:', { civilite, nom, prenom, email, telephone, garantieSerenite, cgvAccepted })
  }

  if (loading) {
    return <div className="min-h-screen bg-white flex items-center justify-center">Chargement...</div>
  }

  if (!stage) {
    return <div className="min-h-screen bg-white flex items-center justify-center">Stage non trouvé</div>
  }

  return (
    <div className="min-h-screen bg-white" style={{ fontFamily: 'var(--font-poppins)' }}>
      {/* Header with stage info */}
      <div className="border-b border-gray-200 py-4" style={{ background: '#fff' }}>
        <div className="max-w-[1200px] mx-auto px-6">
          <h1 className="text-center font-medium" style={{ fontSize: '18px', color: '#222', marginBottom: '4px' }}>
            Stage Récupération de points - {stage.site.adresse}, {stage.site.ville} ({stage.site.code_postal.substring(0, 2)})
          </h1>
          <p className="text-center" style={{ fontSize: '14px', color: '#666' }}>
            Stage agréé Préfecture - Récupération de 4 points en 48h
          </p>
        </div>
      </div>

      {/* Progress Steps */}
      <div className="max-w-[600px] mx-auto px-6 py-8">
        <div className="flex justify-center items-center gap-20">
          {/* Step 1 - Active */}
          <div className="flex flex-col items-center">
            <div className="flex items-center justify-center w-10 h-10 rounded-full bg-white border-2 border-gray-400 relative">
              <span className="text-gray-800 font-medium" style={{ fontSize: '16px' }}>1</span>
              <div className="absolute -right-20 top-1/2 -translate-y-1/2 w-16 h-0.5 bg-gray-300" />
            </div>
            <p className="mt-2 text-sm text-gray-700">Coordonnées</p>
          </div>

          {/* Step 2 */}
          <div className="flex flex-col items-center">
            <div className="flex items-center justify-center w-10 h-10 rounded-full bg-white border-2 border-gray-300 relative">
              <span className="text-gray-400 font-medium" style={{ fontSize: '16px' }}>2</span>
              <div className="absolute -right-20 top-1/2 -translate-y-1/2 w-16 h-0.5 bg-gray-300" />
            </div>
            <p className="mt-2 text-sm text-gray-400">Paiement sécurisé</p>
          </div>

          {/* Step 3 */}
          <div className="flex flex-col items-center">
            <div className="flex items-center justify-center w-10 h-10 rounded-full bg-white border-2 border-gray-300">
              <span className="text-gray-400 font-medium" style={{ fontSize: '16px' }}>3</span>
            </div>
            <p className="mt-2 text-sm text-gray-400">Confirmation</p>
          </div>
        </div>
      </div>

      {/* Main Content - Two Columns */}
      <div className="max-w-[1200px] mx-auto px-6 pb-12">
        <div className="grid grid-cols-[1fr_380px] gap-8">
          {/* Left Column - Form */}
          <div>
            <div className="mb-6">
              <h2 className="font-semibold mb-1" style={{ fontSize: '16px', color: '#222' }}>
                Étape 1/2 - vos coordonnées personnelles pour l'inscription
              </h2>
              <p className="text-sm" style={{ color: '#666' }}>
                * Tous les champs sont obligatoires
              </p>
            </div>

            <form onSubmit={handleSubmit}>
              {/* Civilité */}
              <div className="mb-4">
                <label className="block mb-2 text-sm font-medium" style={{ color: '#333' }}>
                  Civilité
                </label>
                <select
                  value={civilite}
                  onChange={(e) => setCivilite(e.target.value)}
                  required
                  className="w-full px-4 py-2 border border-gray-300 rounded"
                  style={{ height: '40px', fontSize: '14px' }}
                >
                  <option value="">Sélectionner</option>
                  <option value="Monsieur">Monsieur</option>
                  <option value="Madame">Madame</option>
                </select>
              </div>

              {/* Nom */}
              <div className="mb-4">
                <label className="block mb-2 text-sm font-medium" style={{ color: '#333' }}>
                  Nom
                </label>
                <input
                  type="text"
                  value={nom}
                  onChange={(e) => setNom(e.target.value)}
                  required
                  placeholder="Nom"
                  className="w-full px-4 py-2 border border-gray-300 rounded"
                  style={{ height: '40px', fontSize: '14px' }}
                />
              </div>

              {/* Prénom */}
              <div className="mb-4">
                <label className="block mb-2 text-sm font-medium" style={{ color: '#333' }}>
                  Prénom
                </label>
                <input
                  type="text"
                  value={prenom}
                  onChange={(e) => setPrenom(e.target.value)}
                  required
                  placeholder="Prénom"
                  className="w-full px-4 py-2 border border-gray-300 rounded"
                  style={{ height: '40px', fontSize: '14px' }}
                />
              </div>

              {/* Email */}
              <div className="mb-4">
                <label className="block mb-2 text-sm font-medium" style={{ color: '#333' }}>
                  Email
                </label>
                <input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  required
                  placeholder="Email"
                  className="w-full px-4 py-2 border border-gray-300 rounded"
                  style={{ height: '40px', fontSize: '14px' }}
                />
              </div>

              {/* Téléphone mobile */}
              <div className="mb-6">
                <label className="block mb-2 text-sm font-medium" style={{ color: '#333' }}>
                  Téléphone mobile
                </label>
                <input
                  type="tel"
                  value={telephone}
                  onChange={(e) => setTelephone(e.target.value)}
                  required
                  placeholder="Téléphone"
                  className="w-full px-4 py-2 border border-gray-300 rounded"
                  style={{ height: '40px', fontSize: '14px' }}
                />
              </div>

              {/* Garantie Sérénité */}
              <div className="mb-4">
                <label className="flex items-start gap-3 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={garantieSerenite}
                    onChange={(e) => setGarantieSerenite(e.target.checked)}
                    className="mt-1"
                  />
                  <div>
                    <span className="text-sm font-medium" style={{ color: '#333' }}>Garantie Sérénité</span>
                    <p className="text-xs" style={{ color: '#666', marginTop: '2px' }}>
                      Je souscris à la garantie Sérénité +25€ (TTC, à régler maintenant) qui me permettra de reporter ou annuler l'inscription jusqu'à 12 jours du stage
                    </p>
                  </div>
                </label>
              </div>

              {/* CGV */}
              <div className="mb-6">
                <label className="flex items-start gap-3 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={cgvAccepted}
                    onChange={(e) => setCgvAccepted(e.target.checked)}
                    required
                    className="mt-1"
                  />
                  <span className="text-sm" style={{ color: '#333' }}>
                    J'accepte les{' '}
                    <a href="#" className="text-blue-600 underline">
                      conditions générales de vente
                    </a>
                  </span>
                </label>
              </div>

              {/* Submit Button */}
              <button
                type="submit"
                disabled={!cgvAccepted}
                className="w-full text-white font-medium rounded-full disabled:opacity-50"
                style={{
                  background: '#41A334',
                  height: '44px',
                  fontSize: '15px'
                }}
              >
                Valider le formulaire et passer au paiement
              </button>
            </form>
          </div>

          {/* Right Column - Stage Info */}
          <div>
            <div className="bg-gray-50 rounded-lg p-6 border border-gray-200">
              <h3 className="font-semibold mb-4" style={{ fontSize: '16px', color: '#222' }}>
                Stage sélectionné
              </h3>

              <div className="mb-4">
                <p className="font-semibold mb-2" style={{ fontSize: '15px', color: '#222' }}>
                  Stage du {formatDate(stage.date_start, stage.date_end)}
                </p>

                {/* Location */}
                <div className="flex gap-2 mb-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none" className="flex-shrink-0 mt-1">
                    <path d="M14 6.66667C14 11.3333 8 15.3333 8 15.3333C8 15.3333 2 11.3333 2 6.66667C2 5.07536 2.63214 3.54925 3.75736 2.42404C4.88258 1.29882 6.40869 0.666672 8 0.666672C9.59131 0.666672 11.1174 1.29882 12.2426 2.42404C13.3679 3.54925 14 5.07536 14 6.66667Z" stroke="#666" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                    <path d="M8 8.66667C9.10457 8.66667 10 7.77124 10 6.66667C10 5.5621 9.10457 4.66667 8 4.66667C6.89543 4.66667 6 5.5621 6 6.66667C6 7.77124 6.89543 8.66667 8 8.66667Z" stroke="#666" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                  </svg>
                  <div>
                    <p className="text-sm" style={{ color: '#333' }}>Adresse : av de la République</p>
                    <p className="text-sm" style={{ color: '#333' }}>13001 Marseille</p>
                  </div>
                </div>

                {/* Schedule */}
                <div className="flex gap-2 mb-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none" className="flex-shrink-0 mt-1">
                    <path d="M8 4V8L10.6667 9.33333M14.6667 8C14.6667 11.6819 11.6819 14.6667 8 14.6667C4.3181 14.6667 1.33333 11.6819 1.33333 8C1.33333 4.3181 4.3181 1.33333 8 1.33333C11.6819 1.33333 14.6667 4.3181 14.6667 8Z" stroke="#666" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                  </svg>
                  <div>
                    <p className="text-sm" style={{ color: '#333' }}>Horaires: 08h30-12h30 et</p>
                    <p className="text-sm" style={{ color: '#333' }}>13h30-16h30</p>
                  </div>
                </div>

                {/* Agreement */}
                <div className="flex gap-2">
                  <Image
                    src="/flag-france.png"
                    alt="Drapeau français"
                    width={16}
                    height={11}
                    className="flex-shrink-0 mt-1 rounded"
                    style={{ width: '20px', height: '14px' }}
                  />
                  <p className="text-sm" style={{ color: '#333' }}>
                    Agrément n° 311300000006A
                    <br />
                    par la Préfecture de Marseille
                  </p>
                </div>
              </div>

              {/* Price Box */}
              <div className="bg-white rounded p-4 mb-4 text-center border border-gray-200">
                <p className="text-xs mb-1" style={{ color: '#666' }}>Places disponibles</p>
                <p className="text-3xl font-bold" style={{ color: '#222' }}>
                  {stage.prix.toFixed(0)}€ <span className="text-base font-normal">TTC</span>
                </p>
              </div>

              {/* Benefits List */}
              <div className="space-y-2">
                {[
                  'Stage officiel agréé Préfecture',
                  '+4 points en 48h',
                  'Aucun examen, aucun contrôle',
                  'Report ou remboursement en quelques clics',
                  'Paiement 100% sécurisé',
                  'Attestation de stage remise le 2ème jour'
                ].map((benefit, index) => (
                  <div key={index} className="flex gap-2 items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none" className="flex-shrink-0 mt-0.5">
                      <path d="M13.3333 4L6 11.3333L2.66666 8" stroke="#41A334" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                    <p className="text-sm" style={{ color: '#333' }}>{benefit}</p>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
