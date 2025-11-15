'use client'

import { useState, useEffect, useRef } from 'react'
import { useParams, useRouter } from 'next/navigation'
import Link from 'next/link'

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

export default function StageDetailPage() {
  const params = useParams()
  const router = useRouter()
  const city = (params.city as string).toLowerCase()
  const id = params.id as string
  const [stage, setStage] = useState<Stage | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [activeTab, setActiveTab] = useState<'prix' | 'programme' | 'agrement' | 'acces' | 'paiement' | 'avis'>('prix')
  const [showForm, setShowForm] = useState(false)
  const formRef = useRef<HTMLDivElement>(null)

  // Form state
  const [formData, setFormData] = useState({
    civilite: 'Monsieur',
    nom: '',
    prenom: '',
    adresse: '',
    code_postal: '',
    ville: '',
    jour: '',
    mois: '',
    annee: '',
    email: '',
    email_confirmation: '',
    mobile: '',
    garantie_serenite: false,
    cgv_accepted: false
  })

  useEffect(() => {
    async function fetchStage() {
      try {
        setLoading(true)
        const response = await fetch(`/api/stages/${city.toUpperCase()}/${id}`)
        if (!response.ok) {
          throw new Error('Failed to fetch stage')
        }
        const data = (await response.json()) as { stage: Stage }
        setStage(data.stage)
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Unknown error')
      } finally {
        setLoading(false)
      }
    }

    fetchStage()
  }, [city, id])

  const handleValiderClick = () => {
    setShowForm(true)
    setTimeout(() => {
      formRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }, 100)
  }

  const handleFormSubmit = async (e: React.FormEvent) => {
    e.preventDefault()

    // Validation
    if (formData.email !== formData.email_confirmation) {
      alert('Les adresses email ne correspondent pas')
      return
    }

    if (!formData.cgv_accepted) {
      alert('Vous devez accepter les conditions générales de vente')
      return
    }

    // Build date_naissance
    const date_naissance = `${formData.annee}-${formData.mois.padStart(2, '0')}-${formData.jour.padStart(2, '0')}`

    const stagiaireData = {
      id_stage: stage?.id,
      civilite: formData.civilite,
      nom: formData.nom,
      prenom: formData.prenom,
      adresse: formData.adresse,
      code_postal: formData.code_postal,
      ville: formData.ville,
      date_naissance,
      email: formData.email,
      mobile: formData.mobile
    }

    try {
      const response = await fetch('/api/stagiaire/create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(stagiaireData)
      })

      if (!response.ok) {
        throw new Error('Erreur lors de l\'inscription')
      }

      // Store form data in sessionStorage for confirmation page
      sessionStorage.setItem('bookingFormData', JSON.stringify({
        ...stagiaireData,
        garantie_serenite: formData.garantie_serenite
      }))

      // Redirect to confirmation page
      router.push(`/stages-recuperation-points/${city}/${id}/confirmation`)
    } catch (error) {
      alert('Erreur: ' + (error instanceof Error ? error.message : 'Unknown error'))
    }
  }

  const formatDate = (dateString: string) => {
    const date = new Date(dateString)
    const day = date.getDate().toString().padStart(2, '0')
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
        <p className="text-gray-600">Chargement des détails du stage...</p>
      </div>
    )
  }

  if (error || !stage) {
    return (
      <div className="min-h-screen bg-white flex items-center justify-center">
        <p className="text-red-600">Erreur: {error || 'Stage non trouvé'}</p>
      </div>
    )
  }

  const mapUrl = stage.latitude && stage.longitude
    ? `https://www.google.com/maps/embed/v1/place?key=AIzaSyBFw0Qbyq9zTFTd-tUY6dZWTgaQzuU17R8&q=${stage.latitude},${stage.longitude}&zoom=14`
    : `https://www.google.com/maps/embed/v1/place?key=AIzaSyBFw0Qbyq9zTFTd-tUY6dZWTgaQzuU17R8&q=${encodeURIComponent(stage.adresse + ' ' + stage.ville)}`

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Progress Indicator */}
      <div className="bg-white border-b border-gray-200 py-6">
        <div className="mx-auto max-w-7xl px-4">
          <div className="flex items-center justify-between max-w-2xl mx-auto">
            {/* Step 1 */}
            <div className="flex flex-col items-center">
              <div className="w-10 h-10 rounded-full bg-red-500 text-white flex items-center justify-center font-bold mb-2">
                1
              </div>
              <span className="text-xs text-gray-600">Formulaire</span>
            </div>
            <div className="flex-1 h-0.5 bg-gray-300 mx-2"></div>
            {/* Step 2 */}
            <div className="flex flex-col items-center">
              <div className="w-10 h-10 rounded-full bg-gray-300 text-white flex items-center justify-center font-bold mb-2">
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
          {/* Left Column - Map & Details */}
          <div className="lg:col-span-8">
            {/* Stage Title Box */}
            <div className="bg-blue-600 text-white p-4 rounded-t mb-0">
              <h1 className="text-lg font-bold">
                STAGE DU LUNDI {formatDate(stage.date1).toUpperCase()} ET MARDI {formatDate(stage.date2).toUpperCase()}
              </h1>
              <div className="flex items-center gap-2 mt-2 text-sm">
                <span>⏰ de 10:15-12:30 et 13:30-16:30</span>
              </div>
              <div className="mt-2 text-sm">
                <p>{stage.site_nom}</p>
                <p>{stage.adresse}</p>
                <p>{stage.code_postal} {stage.ville}</p>
              </div>
              <div className="mt-2 bg-green-600 text-white px-3 py-1 inline-block rounded text-sm">
                Agrément N°ARS06004550
              </div>
            </div>

            {/* Map */}
            <div className="bg-white border border-gray-200 p-4">
              <div className="flex gap-2 mb-4">
                <button className="px-3 py-1 bg-gray-200 text-sm rounded">Map</button>
                <button className="px-3 py-1 bg-white border border-gray-300 text-sm rounded">Satellite</button>
              </div>
              <iframe
                width="100%"
                height="300"
                frameBorder="0"
                style={{ border: 0 }}
                src={mapUrl}
                allowFullScreen
              ></iframe>
            </div>

            {/* Details du stage */}
            <div className="bg-white border border-gray-200 mt-4 p-6">
              <h2 className="text-lg font-bold text-gray-900 mb-4">Détails du stage:</h2>
              <div className="space-y-2">
                <p><strong>Le Lundi {formatDate(stage.date1)}</strong></p>
                <p className="text-gray-700">Décembre 2025</p>
                <p className="mt-4"><strong>? Adresse exacte</strong></p>
                <p className="text-gray-700">{stage.site_nom} - {stage.adresse}</p>
                <p className="text-gray-700">{stage.code_postal} {stage.ville}</p>
                <p className="mt-4"><strong>? Horaires (2 jours)</strong></p>
                <p className="text-gray-700">Lundi - de 08h15 à 10h30</p>
                <p className="text-gray-700">Après-midi - de 13h30 à 16h30</p>
              </div>
            </div>

            {/* Tabs Section */}
            <div className="bg-white border border-gray-200 mt-4">
              {/* Tab Headers */}
              <div className="border-b border-gray-200 flex overflow-x-auto">
                <button
                  onClick={() => setActiveTab('prix')}
                  className={`px-4 py-3 text-sm whitespace-nowrap ${activeTab === 'prix' ? 'border-b-2 border-red-500 text-red-600 font-semibold' : 'text-gray-600'}`}
                >
                  Le prix du stage comprend
                </button>
                <button
                  onClick={() => setActiveTab('programme')}
                  className={`px-4 py-3 text-sm whitespace-nowrap ${activeTab === 'programme' ? 'border-b-2 border-red-500 text-red-600 font-semibold' : 'text-gray-600'}`}
                >
                  Programme
                </button>
                <button
                  onClick={() => setActiveTab('agrement')}
                  className={`px-4 py-3 text-sm whitespace-nowrap ${activeTab === 'agrement' ? 'border-b-2 border-red-500 text-red-600 font-semibold' : 'text-gray-600'}`}
                >
                  Agrément
                </button>
                <button
                  onClick={() => setActiveTab('acces')}
                  className={`px-4 py-3 text-sm whitespace-nowrap ${activeTab === 'acces' ? 'border-b-2 border-red-500 text-red-600 font-semibold' : 'text-gray-600'}`}
                >
                  Accès - Parking
                </button>
                <button
                  onClick={() => setActiveTab('paiement')}
                  className={`px-4 py-3 text-sm whitespace-nowrap ${activeTab === 'paiement' ? 'border-b-2 border-red-500 text-red-600 font-semibold' : 'text-gray-600'}`}
                >
                  Paiement et conditions
                </button>
                <button
                  onClick={() => setActiveTab('avis')}
                  className={`px-4 py-3 text-sm whitespace-nowrap ${activeTab === 'avis' ? 'border-b-2 border-red-500 text-red-600 font-semibold' : 'text-gray-600'}`}
                >
                  Avis
                </button>
              </div>

              {/* Tab Content */}
              <div className="p-6">
                {activeTab === 'prix' && (
                  <div className="space-y-3">
                    <div className="flex items-start gap-3">
                      <span className="text-green-600 text-xl">✓</span>
                      <span className="text-gray-700">14 heures de formation</span>
                    </div>
                    <div className="flex items-start gap-3">
                      <span className="text-green-600 text-xl">✓</span>
                      <span className="text-gray-700">L'utilisation de stage limitée à 48heures jour</span>
                    </div>
                    <div className="flex items-start gap-3">
                      <span className="text-green-600 text-xl">✓</span>
                      <span className="text-gray-700">La récupération automatique de 4 points</span>
                    </div>
                    <div className="flex items-start gap-3">
                      <span className="text-green-600 text-xl">✓</span>
                      <span className="text-gray-700">Un support de cours pour votre réveil mobilités</span>
                    </div>
                    <div className="flex items-start gap-3">
                      <span className="text-green-600 text-xl">✓</span>
                      <span className="text-gray-700">Une attestation de suivi pour envoi à votre permis</span>
                    </div>
                  </div>
                )}
                {activeTab === 'programme' && (
                  <div className="grid grid-cols-2 gap-6">
                    <div>
                      <h3 className="font-bold text-gray-900 mb-3">1er jour</h3>
                      <p className="text-sm text-gray-700">Matinée: Introduction et sensibilisation</p>
                      <p className="text-sm text-gray-700">Après-midi: Code de la route</p>
                    </div>
                    <div>
                      <h3 className="font-bold text-gray-900 mb-3">2ème jour</h3>
                      <p className="text-sm text-gray-700">Matinée: Sécurité routière</p>
                      <p className="text-sm text-gray-700">Après-midi: Évaluation</p>
                    </div>
                  </div>
                )}
                {activeTab === 'agrement' && (
                  <p className="text-gray-700">Agrément Préfecture N°: R2001300020</p>
                )}
                {activeTab === 'acces' && (
                  <div className="space-y-2">
                    <p className="text-gray-700"><strong>Adresse exacte:</strong> {stage.adresse}, {stage.code_postal} {stage.ville}</p>
                    <p className="text-gray-700"><strong>Parking:</strong> Disponible</p>
                  </div>
                )}
                {activeTab === 'paiement' && (
                  <p className="text-gray-700">Paiement par carte bancaire. Annulation gratuite jusqu'à 14 jours avant le stage.</p>
                )}
                {activeTab === 'avis' && (
                  <p className="text-gray-500 italic">Aucun avis pour le moment</p>
                )}
              </div>
            </div>

            {/* Valider Button */}
            <div className="mt-6">
              <button
                onClick={handleValiderClick}
                className="w-full bg-gradient-to-b from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-4 px-6 rounded text-lg transition-all"
              >
                Valider
              </button>
            </div>

            {/* Form Section (appears when Valider is clicked) */}
            {showForm && (
              <div ref={formRef} className="bg-white border border-gray-200 p-8 mt-8 rounded">
                <h2 className="text-2xl font-bold text-red-600 mb-6">Données personnelles</h2>

                <form onSubmit={handleFormSubmit} className="space-y-6">
                  {/* Civilité */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Civilité <span className="text-red-600">*</span>
                    </label>
                    <select
                      required
                      value={formData.civilite}
                      onChange={(e) => setFormData({ ...formData, civilite: e.target.value })}
                      className="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="Monsieur">Monsieur</option>
                      <option value="Madame">Madame</option>
                    </select>
                  </div>

                  {/* Nom */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Nom <span className="text-red-600">*</span>
                    </label>
                    <input
                      type="text"
                      required
                      value={formData.nom}
                      onChange={(e) => setFormData({ ...formData, nom: e.target.value })}
                      placeholder="Votre nom tel qu'il figure sur votre permis"
                      className="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  {/* Prénom */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Prénom <span className="text-red-600">*</span>
                    </label>
                    <input
                      type="text"
                      required
                      value={formData.prenom}
                      onChange={(e) => setFormData({ ...formData, prenom: e.target.value })}
                      placeholder="Votre prénom"
                      className="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  {/* Adresse */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Adresse <span className="text-red-600">*</span>
                    </label>
                    <input
                      type="text"
                      required
                      value={formData.adresse}
                      onChange={(e) => setFormData({ ...formData, adresse: e.target.value })}
                      placeholder="Votre adresse"
                      className="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  {/* Code Postal */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Code Postal <span className="text-red-600">*</span>
                    </label>
                    <input
                      type="text"
                      required
                      value={formData.code_postal}
                      onChange={(e) => setFormData({ ...formData, code_postal: e.target.value })}
                      placeholder="Votre code postal"
                      className="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  {/* Ville */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Ville <span className="text-red-600">*</span>
                    </label>
                    <input
                      type="text"
                      required
                      value={formData.ville}
                      onChange={(e) => setFormData({ ...formData, ville: e.target.value })}
                      placeholder="Votre ville"
                      className="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  {/* Date de naissance */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Date de naissance <span className="text-red-600">*</span>
                    </label>
                    <div className="grid grid-cols-3 gap-3">
                      <select
                        required
                        value={formData.jour}
                        onChange={(e) => setFormData({ ...formData, jour: e.target.value })}
                        className="px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                      >
                        <option value="">Jour</option>
                        {Array.from({ length: 31 }, (_, i) => i + 1).map(day => (
                          <option key={day} value={day}>{day}</option>
                        ))}
                      </select>
                      <select
                        required
                        value={formData.mois}
                        onChange={(e) => setFormData({ ...formData, mois: e.target.value })}
                        className="px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                      >
                        <option value="">Mois</option>
                        {Array.from({ length: 12 }, (_, i) => i + 1).map(month => (
                          <option key={month} value={month}>{month}</option>
                        ))}
                      </select>
                      <select
                        required
                        value={formData.annee}
                        onChange={(e) => setFormData({ ...formData, annee: e.target.value })}
                        className="px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                      >
                        <option value="">Année</option>
                        {Array.from({ length: 80 }, (_, i) => new Date().getFullYear() - i).map(year => (
                          <option key={year} value={year}>{year}</option>
                        ))}
                      </select>
                    </div>
                  </div>

                  {/* Email */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Adresse email <span className="text-red-600">*</span>
                    </label>
                    <input
                      type="email"
                      required
                      value={formData.email}
                      onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                      placeholder="Votre adresse email"
                      className="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  {/* Confirmation Email */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Confirmation email <span className="text-red-600">*</span>
                    </label>
                    <input
                      type="email"
                      required
                      value={formData.email_confirmation}
                      onChange={(e) => setFormData({ ...formData, email_confirmation: e.target.value })}
                      placeholder="Confirmez votre adresse email"
                      className="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  {/* Téléphone mobile */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Téléphone mobile <span className="text-red-600">*</span>
                    </label>
                    <input
                      type="tel"
                      required
                      value={formData.mobile}
                      onChange={(e) => setFormData({ ...formData, mobile: e.target.value })}
                      placeholder="Votre numéro de téléphone"
                      className="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  <p className="text-sm text-gray-600">
                    <span className="text-red-600">*</span> (Champs obligatoires)
                  </p>

                  {/* Garantie Sérénité */}
                  <div className="flex items-start gap-3">
                    <input
                      type="checkbox"
                      checked={formData.garantie_serenite}
                      onChange={(e) => setFormData({ ...formData, garantie_serenite: e.target.checked })}
                      className="mt-1"
                    />
                    <label className="text-sm text-gray-700">
                      Je souscris à la Garantie Sérénité : +268€ TTC (supplément facturé en plus du stage){' '}
                      <a href="#" className="text-blue-600 underline">En savoir plus</a>
                    </label>
                  </div>

                  {/* CGV */}
                  <div className="flex items-start gap-3">
                    <input
                      type="checkbox"
                      required
                      checked={formData.cgv_accepted}
                      onChange={(e) => setFormData({ ...formData, cgv_accepted: e.target.checked })}
                      className="mt-1"
                    />
                    <label className="text-sm text-gray-700">
                      J'accepte les{' '}
                      <a href="#" className="text-blue-600 underline">conditions générales de vente</a>.{' '}
                      <span className="text-red-600">*</span>
                    </label>
                  </div>

                  {/* Submit Button */}
                  <div className="pt-4">
                    <button
                      type="submit"
                      className="w-full bg-gradient-to-b from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-4 px-6 rounded text-lg transition-all"
                    >
                      Valider le formulaire
                      <br />
                      <span className="text-sm font-normal">et passer au paiement</span>
                    </button>
                  </div>
                </form>
              </div>
            )}
          </div>

          {/* Right Sidebar - Price & Engagements */}
          <div className="lg:col-span-4">
            {/* Price Box */}
            <div className="bg-red-500 text-white p-6 rounded mb-6 text-center">
              <p className="text-5xl font-bold mb-2">{stage.prix}€</p>
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
