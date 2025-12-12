'use client'

import { useState, useEffect } from 'react'
import { useParams } from 'next/navigation'
import Image from 'next/image'
import { removeStreetNumber } from '@/lib/formatAddress'

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

  // Payment form state
  const [nomCarte, setNomCarte] = useState('')
  const [numeroCarte, setNumeroCarte] = useState('')
  const [dateExpirationMois, setDateExpirationMois] = useState('')
  const [dateExpirationAnnee, setDateExpirationAnnee] = useState('')
  const [codeCVV, setCodeCVV] = useState('')

  // Tabs state
  const [activeTab, setActiveTab] = useState('prix')

  // FAQ state
  const [openFaqIndex, setOpenFaqIndex] = useState<number | null>(null)

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
            Stage Récupération de points - {removeStreetNumber(stage.site.adresse)}, {stage.site.ville} ({stage.site.code_postal.substring(0, 2)})
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
          <div style={{ width: '349px' }}>
            <div className="bg-white rounded-lg border border-gray-200" style={{ padding: '20px' }}>
              {/* Stage sélectionné Header */}
              <div
                className="flex items-center justify-center mb-4"
                style={{
                  height: '38px',
                  padding: '8px 106px',
                  borderRadius: '8px',
                  background: '#EFEFEF',
                  alignSelf: 'stretch'
                }}
              >
                <p
                  style={{
                    color: '#000',
                    textAlign: 'center',
                    fontFamily: 'Poppins',
                    fontSize: '14px',
                    fontStyle: 'normal',
                    fontWeight: 400,
                    lineHeight: '20px',
                    whiteSpace: 'nowrap'
                  }}
                >
                  Stage sélectionné
                </p>
              </div>

              {/* Stage Date */}
              <div className="mb-4">
                <p
                  style={{
                    color: '#000',
                    textAlign: 'center',
                    fontFamily: 'Poppins',
                    fontSize: '14px',
                    fontStyle: 'normal',
                    fontWeight: 500,
                    lineHeight: '20px',
                    marginBottom: '12px',
                    whiteSpace: 'nowrap'
                  }}
                >
                  Stage du {formatDate(stage.date_start, stage.date_end)}
                </p>

                {/* Changer de date */}
                <div className="flex items-center gap-2 mb-3">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" style={{ width: '20px', height: '20px', flexShrink: 0 }}>
                    <g clipPath="url(#clip0_180_68)">
                      <path d="M13.3333 1.66669V5.00002M6.66667 1.66669V5.00002M2.5 8.33335H17.5M4.16667 3.33335H15.8333C16.7538 3.33335 17.5 4.07955 17.5 5.00002V16.6667C17.5 17.5872 16.7538 18.3334 15.8333 18.3334H4.16667C3.24619 18.3334 2.5 17.5872 2.5 16.6667V5.00002C2.5 4.07955 3.24619 3.33335 4.16667 3.33335Z" stroke="#595656" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round"/>
                    </g>
                    <defs>
                      <clipPath id="clip0_180_68">
                        <rect width="20" height="20" fill="white"/>
                      </clipPath>
                    </defs>
                  </svg>
                  <a href="#" className="text-blue-600" style={{ fontFamily: 'Poppins', fontSize: '14px' }}>Changer de date</a>
                </div>

                {/* Location */}
                <div className="flex gap-2 mb-3">
                  <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none" style={{ width: '25px', height: '25px' }}>
                    <path d="M21.875 10.4167C21.875 17.7084 12.5 23.9584 12.5 23.9584C12.5 23.9584 3.125 17.7084 3.125 10.4167C3.125 7.93028 4.11272 5.54571 5.87087 3.78756C7.62903 2.02941 10.0136 1.04169 12.5 1.04169C14.9864 1.04169 17.371 2.02941 19.1291 3.78756C20.8873 5.54571 21.875 7.93028 21.875 10.4167Z" stroke="#595656" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                    <path d="M12.5 13.5417C14.2259 13.5417 15.625 12.1426 15.625 10.4167C15.625 8.6908 14.2259 7.29169 12.5 7.29169C10.7741 7.29169 9.375 8.6908 9.375 10.4167C9.375 12.1426 10.7741 13.5417 12.5 13.5417Z" stroke="#595656" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                  </svg>
                  <p
                    style={{
                      width: '233px',
                      color: 'rgba(89, 86, 86, 0.86)',
                      fontFamily: 'Poppins',
                      fontSize: '14px',
                      fontWeight: 400,
                      lineHeight: '23px'
                    }}
                  >
                    {removeStreetNumber(stage.site.adresse)}, {stage.site.code_postal} {stage.site.ville}
                  </p>
                </div>

                {/* Schedule */}
                <div className="flex gap-2 mb-3">
                  <svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 23 23" fill="none" style={{ width: '20.833px', height: '20.833px' }}>
                    <path d="M11.4167 5.16667V11.4167L15.5833 13.5M21.8333 11.4167C21.8333 17.1696 17.1696 21.8333 11.4167 21.8333C5.6637 21.8333 1 17.1696 1 11.4167C1 5.6637 5.6637 1 11.4167 1C17.1696 1 21.8333 5.6637 21.8333 11.4167Z" stroke="#595656" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                  </svg>
                  <p
                    style={{
                      color: 'rgba(89, 86, 86, 0.86)',
                      fontFamily: 'Poppins',
                      fontSize: '14px',
                      fontWeight: 400,
                      lineHeight: '23px'
                    }}
                  >
                    08h15-12h30 et 13h30-16h30
                  </p>
                </div>

                {/* Agreement */}
                <div className="flex gap-2">
                  <Image
                    src="/flag-france.png"
                    alt="Drapeau français"
                    width={25}
                    height={17}
                    className="flex-shrink-0"
                    style={{
                      height: '16.636px',
                      alignSelf: 'stretch',
                      aspectRatio: '25.00/16.64'
                    }}
                  />
                  <p
                    style={{
                      color: 'rgba(89, 86, 86, 0.86)',
                      fontFamily: 'Poppins',
                      fontSize: '14px',
                      fontWeight: 400,
                      lineHeight: '23px'
                    }}
                  >
                    Agrément n° 25 R130060090064 par la Préfecture des Bouches-du-Rhône
                  </p>
                </div>
              </div>

              {/* Separator Line */}
              <div className="flex justify-center mb-6">
                <div
                  style={{
                    width: '264.125px',
                    height: '1px',
                    backgroundColor: '#B6B6B6'
                  }}
                />
              </div>

              {/* Price Box */}
              <div className="mb-4 flex flex-col items-center">
                <div
                  style={{
                    display: 'flex',
                    height: '30px',
                    flexDirection: 'column',
                    justifyContent: 'flex-start',
                    flexShrink: 0,
                    marginBottom: '4px'
                  }}
                >
                  <p
                    style={{
                      color: 'rgba(38, 126, 28, 0.95)',
                      textAlign: 'center',
                      fontFamily: 'Poppins',
                      fontSize: '15px',
                      fontStyle: 'normal',
                      fontWeight: 400,
                      lineHeight: '25px'
                    }}
                  >
                    Places disponibles
                  </p>
                </div>
                <div
                  style={{
                    display: 'flex',
                    height: '37px',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    flexShrink: 0
                  }}
                >
                  <p
                    style={{
                      color: 'rgba(0, 0, 0, 0.86)',
                      textAlign: 'center',
                      fontFamily: 'Poppins',
                      fontSize: '26px',
                      fontStyle: 'normal',
                      fontWeight: 400,
                      lineHeight: '35px'
                    }}
                  >
                    190€ TTC
                  </p>
                </div>
              </div>

              {/* Benefits List */}
              <div className="border border-gray-300 rounded-lg p-4">
                <div className="space-y-3">
                  {[
                    'Stage officiel agréé Préfecture',
                    '+4 points en 48h',
                    '98,7% de clients satisfaits',
                    'Report ou remboursement en cas d\'imprévu',
                    'Paiement 100% sécurisé',
                    'Attestation de stage remise le 2ème jour'
                  ].map((benefit, index) => (
                    <div key={index} className="flex gap-3 items-start">
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" className="flex-shrink-0" style={{ marginTop: '2px' }}>
                        <path d="M16.6667 5L7.50004 14.1667L3.33337 10" stroke="#C5A052" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                      </svg>
                      <p style={{ fontSize: '13px', color: '#000', fontFamily: 'Poppins', lineHeight: '20px' }}>{benefit}</p>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Payment Section - Étape 2/2 */}
        <div className="max-w-[1200px] mx-auto px-6 pb-12">
          <h2 className="font-semibold mb-6" style={{ fontSize: '18px', color: '#222' }}>
            Étape 2/2 - paiement sécurisé
          </h2>

          {/* Payment Method Header */}
          <div className="mb-6">
            <p className="text-sm mb-3" style={{ color: '#666' }}>
              Paiement sécurisé par Crédit Agricole
            </p>
            <div className="flex gap-2">
              {/* Payment logos */}
              <div className="px-3 py-1 border border-gray-300 rounded" style={{ height: '30px', display: 'flex', alignItems: 'center' }}>
                <span className="text-xs font-medium" style={{ color: '#333' }}>VISA</span>
              </div>
              <div className="px-3 py-1 border border-gray-300 rounded" style={{ height: '30px', display: 'flex', alignItems: 'center' }}>
                <span className="text-xs font-medium" style={{ color: '#333' }}>MC</span>
              </div>
              <div className="px-3 py-1 border border-gray-300 rounded" style={{ height: '30px', display: 'flex', alignItems: 'center' }}>
                <span className="text-xs font-medium" style={{ color: '#333' }}>CB</span>
              </div>
            </div>
          </div>

          {/* Payment Form */}
          <div className="grid grid-cols-2 gap-x-8 max-w-[600px]">
            {/* Nom sur la carte */}
            <div className="col-span-2 mb-4">
              <label className="block mb-2 text-sm font-medium" style={{ color: '#333' }}>
                Nom sur la carte
              </label>
              <input
                type="text"
                value={nomCarte}
                onChange={(e) => setNomCarte(e.target.value)}
                required
                placeholder="Nom"
                className="w-full px-4 py-2 border border-gray-300 rounded"
                style={{ height: '40px', fontSize: '14px' }}
              />
            </div>

            {/* Numéro de carte */}
            <div className="col-span-2 mb-4">
              <label className="block mb-2 text-sm font-medium" style={{ color: '#333' }}>
                Numéro de carte
              </label>
              <input
                type="text"
                value={numeroCarte}
                onChange={(e) => setNumeroCarte(e.target.value)}
                required
                placeholder="Numéro de carte"
                className="w-full px-4 py-2 border border-gray-300 rounded"
                style={{ height: '40px', fontSize: '14px' }}
                maxLength={16}
              />
            </div>

            {/* Date expiration */}
            <div className="mb-4">
              <label className="block mb-2 text-sm font-medium" style={{ color: '#333' }}>
                Date expiration
              </label>
              <div className="flex gap-2">
                <input
                  type="text"
                  value={dateExpirationMois}
                  onChange={(e) => setDateExpirationMois(e.target.value)}
                  required
                  placeholder="MM"
                  className="w-full px-4 py-2 border border-gray-300 rounded"
                  style={{ height: '40px', fontSize: '14px' }}
                  maxLength={2}
                />
                <input
                  type="text"
                  value={dateExpirationAnnee}
                  onChange={(e) => setDateExpirationAnnee(e.target.value)}
                  required
                  placeholder="Année"
                  className="w-full px-4 py-2 border border-gray-300 rounded"
                  style={{ height: '40px', fontSize: '14px' }}
                  maxLength={2}
                />
              </div>
            </div>

            {/* Code CVV */}
            <div className="mb-4">
              <label className="block mb-2 text-sm font-medium" style={{ color: '#333' }}>
                Code (cvv)
              </label>
              <input
                type="text"
                value={codeCVV}
                onChange={(e) => setCodeCVV(e.target.value)}
                required
                placeholder="Code"
                className="w-full px-4 py-2 border border-gray-300 rounded"
                style={{ height: '40px', fontSize: '14px' }}
                maxLength={3}
              />
            </div>
          </div>

          {/* Price Summary */}
          <div className="max-w-[600px] mt-8 mb-6">
            <div className="text-center mb-2">
              <p className="text-sm" style={{ color: '#666' }}>Stage du vend 5 et sam 6 déc 2025</p>
            </div>
            <div className="text-center mb-2">
              <p className="text-sm" style={{ color: '#666' }}>Prix du stage : 190€ TTC</p>
            </div>
            {garantieSerenite && (
              <div className="text-center mb-2">
                <p className="text-sm" style={{ color: '#666' }}>Garantie Sérénité : +25€ TTC</p>
              </div>
            )}
            <div className="text-center mb-6">
              <p className="font-semibold" style={{ fontSize: '16px', color: '#222' }}>
                Total à payer : {garantieSerenite ? stage?.prix + 25 : stage?.prix}€ TTC
              </p>
            </div>

            {/* Payment Button */}
            <button
              type="submit"
              className="w-full text-white font-medium rounded-full flex items-center justify-center gap-2"
              style={{
                background: '#41A334',
                height: '44px',
                fontSize: '15px'
              }}
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M12 5.33334L6.66667 10.6667L4 8.00001" stroke="white" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
              Payer {garantieSerenite ? stage?.prix + 25 : stage?.prix}€ TTC
            </button>

            {/* Payment Disclaimer */}
            <p className="text-xs text-center mt-4" style={{ color: '#666', lineHeight: '1.5' }}>
              Après avoir cliqué sur "Payer", votre banque vous demandera une validation 3D secure. Une fois le paiement confirmé, vous recevez l'attestation par email dans quelques minutes.
            </p>
          </div>
        </div>

        {/* Informations pratiques sur votre stage */}
        <div className="bg-gray-50 py-12">
          <div className="max-w-[1200px] mx-auto px-6">
            <h2 className="font-semibold mb-6" style={{ fontSize: '18px', color: '#222' }}>
              Informations pratiques sur votre stage
            </h2>

            {/* Tabs */}
            <div className="flex gap-4 mb-6 border-b border-gray-200">
              <button
                onClick={() => setActiveTab('prix')}
                className={`pb-3 px-4 text-sm font-medium transition-colors ${
                  activeTab === 'prix' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'
                }`}
              >
                Le prix du stage comprend
              </button>
              <button
                onClick={() => setActiveTab('programme')}
                className={`pb-3 px-4 text-sm font-medium transition-colors ${
                  activeTab === 'programme' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'
                }`}
              >
                Programme
              </button>
              <button
                onClick={() => setActiveTab('agrement')}
                className={`pb-3 px-4 text-sm font-medium transition-colors ${
                  activeTab === 'agrement' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'
                }`}
              >
                Agrément
              </button>
            </div>

            {/* Tab Content */}
            <div className="bg-white rounded-lg p-6">
              {activeTab === 'prix' && (
                <ul className="space-y-3">
                  <li className="flex gap-2 items-start">
                    <span className="text-sm" style={{ color: '#333' }}>• L'intégralité du stage visant la récupération de 4 points</span>
                  </li>
                  <li className="flex gap-2 items-start">
                    <span className="text-sm" style={{ color: '#333' }}>• L'attestation de stage remise le deuxième jour</span>
                  </li>
                  <li className="flex gap-2 items-start">
                    <span className="text-sm" style={{ color: '#333' }}>• La récupération automatique de 4 points</span>
                  </li>
                  <li className="flex gap-2 items-start">
                    <span className="text-sm" style={{ color: '#333' }}>• Le traitement de votre dossier administratif en préfecture</span>
                  </li>
                  <li className="flex gap-2 items-start">
                    <span className="text-sm" style={{ color: '#333' }}>• En cas d'empêchement, le transfert sur une autre stage à raison d'essence</span>
                  </li>
                </ul>
              )}
              {activeTab === 'programme' && (
                <div>
                  <p className="text-sm mb-3" style={{ color: '#333' }}>Programme détaillé du stage de récupération de points</p>
                </div>
              )}
              {activeTab === 'agrement' && (
                <div>
                  <p className="text-sm mb-3" style={{ color: '#333' }}>Informations sur l'agrément préfectoral</p>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Questions fréquentes */}
        <div className="max-w-[1200px] mx-auto px-6 py-12">
          <h2 className="font-semibold mb-6" style={{ fontSize: '18px', color: '#222' }}>
            Questions fréquentes
          </h2>

          <div className="space-y-4">
            {[
              'À quel moment mes 4 points sont-ils crédités sur mon permis après un stage ?',
              'À quel moment mes 4 points sont-ils crédités sur mon permis après un stage ?',
              'À quel moment mes 4 points sont-ils crédités sur mon permis après un stage ?'
            ].map((question, index) => (
              <div key={index} className="border border-gray-300 rounded-lg">
                <button
                  onClick={() => setOpenFaqIndex(openFaqIndex === index ? null : index)}
                  className="w-full flex items-center justify-between p-4 text-left"
                >
                  <div className="flex items-center gap-3">
                    <div className="flex-shrink-0 w-6 h-6 rounded-full border border-gray-400 flex items-center justify-center">
                      <span className="text-sm" style={{ color: '#666' }}>?</span>
                    </div>
                    <span className="text-sm font-medium" style={{ color: '#333' }}>{question}</span>
                  </div>
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    viewBox="0 0 16 16"
                    fill="none"
                    className={`flex-shrink-0 transition-transform ${openFaqIndex === index ? 'rotate-180' : ''}`}
                  >
                    <path d="M4 6L8 10L12 6" stroke="#666" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                  </svg>
                </button>
                {openFaqIndex === index && (
                  <div className="px-4 pb-4 pl-14">
                    <p className="text-sm" style={{ color: '#666' }}>
                      Réponse à la question fréquente...
                    </p>
                  </div>
                )}
              </div>
            ))}
          </div>

          {/* Afficher plus de questions */}
          <div className="text-center mt-6">
            <button className="text-sm font-medium" style={{ color: '#2b85c9' }}>
              Afficher plus de questions
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}
