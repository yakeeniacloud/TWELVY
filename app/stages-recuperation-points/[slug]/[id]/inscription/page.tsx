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
    const month = start.toLocaleDateString('fr-FR', { month: 'short' }).replace('.', '')

    const capitalizedDayStart = dayStart.charAt(0).toUpperCase() + dayStart.slice(1)
    const capitalizedDayEnd = dayEnd.charAt(0).toUpperCase() + dayEnd.slice(1)

    return `${capitalizedDayStart} ${dayNumStart} et ${capitalizedDayEnd} ${dayNumEnd} ${month}`
  }

  const formatCityName = (cityName: string) => {
    // Remove arrondissement suffix (e.g., "-15eme", "-1er", "-2eme")
    let formatted = cityName.replace(/-\d+(er|eme|ème)$/i, '')

    // Convert to proper case (first letter uppercase, rest lowercase)
    formatted = formatted.charAt(0).toUpperCase() + formatted.slice(1).toLowerCase()

    return formatted
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()

    try {
      const response = await fetch('/api/stagiaire/create', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          stage_id: id,
          civilite,
          nom,
          prenom,
          email,
          telephone_mobile: telephone,
          guarantee_serenite: garantieSerenite,
          cgv_accepted: cgvAccepted
        }),
      })

      const data = await response.json()

      if (!response.ok) {
        alert('Erreur lors de l\'inscription: ' + (data.error || 'Erreur inconnue'))
        return
      }

      // Redirect to confirmation page
      window.location.href = `/stages-recuperation-points/${city.toLowerCase()}/${id}/merci?ref=${data.booking_reference || ''}`
    } catch (error) {
      console.error('Error submitting form:', error)
      alert('Une erreur est survenue lors de l\'inscription')
    }
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
      <div className="py-6" style={{ background: '#fff' }}>
        <div className="max-w-[1200px] mx-auto px-6">
          <h1
            className="text-center font-normal mb-2"
            style={{
              maxWidth: '829px',
              margin: '0 auto',
              color: '#000',
              fontFamily: 'Poppins',
              fontSize: '25px',
              fontStyle: 'normal',
              fontWeight: 400,
              lineHeight: '35px'
            }}
          >
            Stage Récupération de points - {removeStreetNumber(stage.site.adresse)}, {stage.site.ville} ({stage.site.code_postal.substring(0, 2)})
          </h1>
          <p
            className="text-center"
            style={{
              maxWidth: '582px',
              margin: '0 auto',
              color: 'rgba(6, 6, 6, 0.86)',
              fontFamily: 'Poppins',
              fontSize: '15px',
              fontStyle: 'normal',
              fontWeight: 400,
              lineHeight: '28px'
            }}
          >
            Stage agréé Préfecture - Récupération de 4 points en 48h
          </p>
        </div>
      </div>

      {/* Progress Steps */}
      <div className="max-w-[1200px] mx-auto px-6 py-12">
        <div className="flex justify-center items-center" style={{ width: '500px', margin: '0 auto', gap: '0' }}>
          {/* Step 1 - Active */}
          <div className="flex flex-col items-center" style={{ position: 'relative' }}>
            <div className="flex items-center justify-center" style={{ position: 'relative', width: '33px', height: '31px', marginBottom: '12px' }}>
              <svg xmlns="http://www.w3.org/2000/svg" width="33" height="31" viewBox="0 0 33 31" fill="none" style={{ position: 'absolute' }}>
                <path d="M16.5 0.5C25.3665 0.5 32.5 7.24472 32.5 15.5C32.5 23.7553 25.3665 30.5 16.5 30.5C7.63354 30.5 0.5 23.7553 0.5 15.5C0.5 7.24472 7.63354 0.5 16.5 0.5Z" fill="white" stroke="#030303"/>
              </svg>
              <span
                style={{
                  position: 'relative',
                  zIndex: 1,
                  color: '#000',
                  textAlign: 'center',
                  fontFamily: 'Poppins',
                  fontSize: '20px',
                  fontStyle: 'normal',
                  fontWeight: 400,
                  lineHeight: '28px'
                }}
              >
                1
              </span>
            </div>
            <p
              style={{
                color: '#000',
                textAlign: 'center',
                fontFamily: 'Poppins',
                fontSize: '16px',
                fontWeight: 400,
                lineHeight: '24px',
                whiteSpace: 'nowrap'
              }}
            >
              Coordonnées
            </p>
          </div>

          {/* Line 1 to 2 */}
          <div
            style={{
              flex: 1,
              height: '1px',
              background: '#D9D9D9',
              marginBottom: '42px',
              marginLeft: '-1px',
              marginRight: '-1px'
            }}
          />

          {/* Step 2 - Inactive */}
          <div className="flex flex-col items-center" style={{ position: 'relative' }}>
            <div className="flex items-center justify-center" style={{ position: 'relative', width: '33px', height: '31px', marginBottom: '12px' }}>
              <svg xmlns="http://www.w3.org/2000/svg" width="33" height="31" viewBox="0 0 33 31" fill="none" style={{ position: 'absolute' }}>
                <path d="M16.5 0.5C25.3665 0.5 32.5 7.24472 32.5 15.5C32.5 23.7553 25.3665 30.5 16.5 30.5C7.63354 30.5 0.5 23.7553 0.5 15.5C0.5 7.24472 7.63354 0.5 16.5 0.5Z" fill="#F5F5F5" stroke="#D9D9D9"/>
              </svg>
              <span
                style={{
                  position: 'relative',
                  zIndex: 1,
                  color: '#828282',
                  textAlign: 'center',
                  fontFamily: 'Poppins',
                  fontSize: '20px',
                  fontStyle: 'normal',
                  fontWeight: 400,
                  lineHeight: '28px'
                }}
              >
                2
              </span>
            </div>
            <p
              style={{
                color: '#828282',
                textAlign: 'center',
                fontFamily: 'Poppins',
                fontSize: '16px',
                fontWeight: 400,
                lineHeight: '24px',
                whiteSpace: 'nowrap'
              }}
            >
              Paiement sécurisé
            </p>
          </div>

          {/* Line 2 to 3 */}
          <div
            style={{
              flex: 1,
              height: '1px',
              background: '#D9D9D9',
              marginBottom: '42px',
              marginLeft: '-1px',
              marginRight: '-1px'
            }}
          />

          {/* Step 3 - Inactive */}
          <div className="flex flex-col items-center" style={{ position: 'relative' }}>
            <div className="flex items-center justify-center" style={{ position: 'relative', width: '33px', height: '31px', marginBottom: '12px' }}>
              <svg xmlns="http://www.w3.org/2000/svg" width="33" height="31" viewBox="0 0 33 31" fill="none" style={{ position: 'absolute' }}>
                <path d="M16.5 0.5C25.3665 0.5 32.5 7.24472 32.5 15.5C32.5 23.7553 25.3665 30.5 16.5 30.5C7.63354 30.5 0.5 23.7553 0.5 15.5C0.5 7.24472 7.63354 0.5 16.5 0.5Z" fill="#F5F5F5" stroke="#D9D9D9"/>
              </svg>
              <span
                style={{
                  position: 'relative',
                  zIndex: 1,
                  color: '#828282',
                  textAlign: 'center',
                  fontFamily: 'Poppins',
                  fontSize: '20px',
                  fontStyle: 'normal',
                  fontWeight: 400,
                  lineHeight: '28px'
                }}
              >
                3
              </span>
            </div>
            <p
              style={{
                color: '#828282',
                textAlign: 'center',
                fontFamily: 'Poppins',
                fontSize: '16px',
                fontWeight: 400,
                lineHeight: '24px',
                whiteSpace: 'nowrap'
              }}
            >
              Confirmation
            </p>
          </div>
        </div>
      </div>

      {/* Main Content - Two Columns */}
      <div className="max-w-[1200px] mx-auto px-6 pb-12">
        <div className="grid grid-cols-[1fr_380px] gap-8 items-start">
          {/* Left Column - Form */}
          <div style={{ marginLeft: '40px' }}>
            <div style={{ marginBottom: '28px' }}>
              <h2
                style={{
                  display: 'flex',
                  width: '673px',
                  height: '43px',
                  flexDirection: 'column',
                  justifyContent: 'center',
                  color: '#000',
                  fontFamily: 'Poppins',
                  fontSize: '18px',
                  fontStyle: 'normal',
                  fontWeight: 500,
                  lineHeight: '25px'
                }}
              >
                Étape 1/2 - vos coordonnées personnelles pour l'inscription
              </h2>
              <p
                style={{
                  marginTop: '28px',
                  width: '297px',
                  flexShrink: 0,
                  color: '#363636',
                  fontFamily: 'Poppins',
                  fontSize: '13px',
                  fontStyle: 'italic',
                  fontWeight: 400,
                  lineHeight: '25px'
                }}
              >
                • Tous les champs sont obligatoires
              </p>
            </div>

            <form onSubmit={handleSubmit}>
              {/* Civilité */}
              <div className="flex items-center mb-6" style={{ position: 'relative', marginLeft: '20px' }}>
                <label
                  style={{
                    position: 'absolute',
                    left: 0,
                    color: '#000',
                    fontFamily: 'Poppins',
                    fontSize: '15px',
                    fontStyle: 'normal',
                    fontWeight: 400,
                    lineHeight: '25px'
                  }}
                >
                  Civilité *
                </label>
                <div style={{ marginLeft: '164px' }}>
                  <select
                    value={civilite}
                    onChange={(e) => setCivilite(e.target.value)}
                    required
                    className="border border-black"
                    style={{
                      display: 'flex',
                      width: '413px',
                      height: '35px',
                      padding: '7px 15px',
                      alignItems: 'center',
                      gap: '10px',
                      flexShrink: 0,
                      borderRadius: '8px',
                      border: '1px solid #000'
                    }}
                  >
                    <option value="">Monsieur</option>
                    <option value="Monsieur">Monsieur</option>
                    <option value="Madame">Madame</option>
                  </select>
                </div>
              </div>

              {/* Nom */}
              <div className="flex items-center mb-6" style={{ position: 'relative', marginLeft: '20px' }}>
                <label
                  style={{
                    position: 'absolute',
                    left: 0,
                    color: '#000',
                    fontFamily: 'Poppins',
                    fontSize: '15px',
                    fontStyle: 'normal',
                    fontWeight: 400,
                    lineHeight: '25px'
                  }}
                >
                  Nom *
                </label>
                <div style={{ marginLeft: '164px' }}>
                  <input
                    type="text"
                    value={nom}
                    onChange={(e) => setNom(e.target.value)}
                    required
                    placeholder="Nom"
                    className="border border-black"
                    style={{
                      display: 'flex',
                      width: '413px',
                      height: '35px',
                      padding: '7px 15px',
                      alignItems: 'center',
                      gap: '10px',
                      flexShrink: 0,
                      borderRadius: '8px',
                      border: '1px solid #000'
                    }}
                  />
                </div>
              </div>

              {/* Prénom */}
              <div className="flex items-center mb-6" style={{ position: 'relative', marginLeft: '20px' }}>
                <label
                  style={{
                    position: 'absolute',
                    left: 0,
                    color: '#000',
                    fontFamily: 'Poppins',
                    fontSize: '15px',
                    fontStyle: 'normal',
                    fontWeight: 400,
                    lineHeight: '25px'
                  }}
                >
                  Prénom *
                </label>
                <div style={{ marginLeft: '164px' }}>
                  <input
                    type="text"
                    value={prenom}
                    onChange={(e) => setPrenom(e.target.value)}
                    required
                    placeholder="Prénom"
                    className="border border-black"
                    style={{
                      display: 'flex',
                      width: '413px',
                      height: '35px',
                      padding: '7px 15px',
                      alignItems: 'center',
                      gap: '10px',
                      flexShrink: 0,
                      borderRadius: '8px',
                      border: '1px solid #000'
                    }}
                  />
                </div>
              </div>

              {/* Email */}
              <div className="flex items-center mb-6" style={{ position: 'relative', marginLeft: '20px' }}>
                <label
                  style={{
                    position: 'absolute',
                    left: 0,
                    color: '#000',
                    fontFamily: 'Poppins',
                    fontSize: '15px',
                    fontStyle: 'normal',
                    fontWeight: 400,
                    lineHeight: '25px'
                  }}
                >
                  Email *
                </label>
                <div style={{ marginLeft: '164px' }}>
                  <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                    placeholder="Email"
                    className="border border-black"
                    style={{
                      display: 'flex',
                      width: '413px',
                      height: '35px',
                      padding: '7px 15px',
                      alignItems: 'center',
                      gap: '10px',
                      flexShrink: 0,
                      borderRadius: '8px',
                      border: '1px solid #000'
                    }}
                  />
                </div>
              </div>

              {/* Téléphone mobile */}
              <div className="flex items-start mb-2" style={{ position: 'relative', marginLeft: '20px' }}>
                <label
                  style={{
                    position: 'absolute',
                    left: 0,
                    color: '#000',
                    fontFamily: 'Poppins',
                    fontSize: '15px',
                    fontStyle: 'normal',
                    fontWeight: 400,
                    lineHeight: '25px'
                  }}
                >
                  Téléphone mobile *
                </label>
                <div style={{ marginLeft: '164px' }}>
                  <div className="flex items-center gap-2">
                    <input
                      type="tel"
                      value={telephone}
                      onChange={(e) => setTelephone(e.target.value)}
                      required
                      placeholder="Téléphone"
                      className="border border-black"
                      style={{
                        display: 'flex',
                        width: '413px',
                        height: '35px',
                        padding: '7px 15px',
                        alignItems: 'center',
                        gap: '10px',
                        flexShrink: 0,
                        borderRadius: '8px',
                        border: '1px solid #000'
                      }}
                    />
                    <div
                      style={{ width: '24px', height: '24px', flexShrink: 0, position: 'relative', cursor: 'pointer' }}
                      className="group"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <g clipPath="url(#clip0_156_42)">
                          <path d="M12 16V12M12 8H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="#1E1E1E" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"/>
                        </g>
                        <defs>
                          <clipPath id="clip0_156_42">
                            <rect width="24" height="24" fill="white"/>
                          </clipPath>
                        </defs>
                      </svg>
                      {/* Tooltip */}
                      <div
                        className="opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"
                        style={{
                          position: 'absolute',
                          top: '-80px',
                          left: '30px',
                          width: '300px',
                          padding: '12px 16px',
                          backgroundColor: '#2E2E2E',
                          color: '#FFFFFF',
                          borderRadius: '8px',
                          fontFamily: 'Poppins',
                          fontSize: '13px',
                          fontWeight: 400,
                          lineHeight: '18px',
                          zIndex: 1000,
                          boxShadow: '0 4px 6px rgba(0, 0, 0, 0.1)'
                        }}
                      >
                        Nous utilisons ce numéro uniquement pour le SMS de confirmation et les informations essentielles liées à votre stage. Aucun démarchage commercial.
                        {/* Arrow */}
                        <div
                          style={{
                            position: 'absolute',
                            bottom: '-6px',
                            left: '10px',
                            width: '12px',
                            height: '12px',
                            backgroundColor: '#2E2E2E',
                            transform: 'rotate(45deg)'
                          }}
                        />
                      </div>
                    </div>
                  </div>
                  <p
                    style={{
                      width: '424px',
                      flexShrink: 0,
                      color: '#2E2E2E',
                      fontFamily: 'Poppins',
                      fontSize: '13px',
                      fontStyle: 'italic',
                      fontWeight: 400,
                      lineHeight: '18px',
                      marginTop: '8px'
                    }}
                  >
                    Important : indiquez un numéro de mobile valide. Il servira au SMS de confirmation et aux infos essentielles sur votre stage.
                  </p>
                </div>
              </div>

              {/* Garantie Sérénité */}
              <div className="mb-6" style={{ marginLeft: '100px', marginTop: '50px' }}>
                <div
                  style={{
                    display: 'flex',
                    width: '482px',
                    height: '124px',
                    padding: '10px 15px',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    alignItems: 'flex-start',
                    gap: '8px',
                    flexShrink: 0,
                    borderRadius: '14px',
                    background: '#EFEFEF'
                  }}
                >
                  {/* Header with shield icon and title */}
                  <div className="flex items-center gap-2">
                    <div style={{ width: '25px', height: '25px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                      <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none">
                        <g clipPath="url(#clip0_122_55)">
                          <path d="M12.5 22.9166C12.5 22.9166 20.8334 18.75 20.8334 12.5V5.20831L12.5 2.08331L4.16669 5.20831V12.5C4.16669 18.75 12.5 22.9166 12.5 22.9166Z" fill="#EFEFEF" stroke="#696868" strokeOpacity="0.96" strokeWidth="3.5" strokeLinecap="round" strokeLinejoin="round"/>
                        </g>
                        <rect x="0.5" y="0.5" width="24" height="24" stroke="#EFEFEF"/>
                        <defs>
                          <clipPath id="clip0_122_55">
                            <rect width="25" height="25" fill="white"/>
                          </clipPath>
                        </defs>
                      </svg>
                    </div>
                    <div
                      style={{
                        display: 'flex',
                        width: '150px',
                        height: '25px',
                        flexDirection: 'column',
                        justifyContent: 'center',
                        fontSize: '14px'
                      }}
                    >
                      <span style={{ textDecoration: 'underline', fontWeight: 500 }}>Garantie Sérénité</span>
                    </div>
                  </div>

                  {/* Checkbox with text */}
                  <label className="flex items-start gap-2 cursor-pointer" style={{ marginLeft: '30px' }}>
                    <input
                      type="checkbox"
                      checked={garantieSerenite}
                      onChange={(e) => setGarantieSerenite(e.target.checked)}
                      className="mt-1 flex-shrink-0"
                      style={{ width: '20px', height: '20px' }}
                    />
                    <div
                      style={{
                        display: 'flex',
                        width: '411px',
                        height: '54px',
                        flexDirection: 'column',
                        justifyContent: 'center',
                        flexShrink: 0,
                        color: '#000',
                        fontFamily: 'Poppins',
                        fontSize: '14px',
                        fontStyle: 'normal',
                        fontWeight: 300,
                        lineHeight: '22px'
                      }}
                    >
                      Je souscris à la Garantie Sérénité: +57€ TTC (supplement facturé en plus du stage)
                    </div>
                  </label>

                  {/* Voir le détail link */}
                  <div className="flex items-center gap-1 cursor-pointer" style={{ marginLeft: '10px' }}>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                      <path d="M4 6L8 10L12 6" stroke="#0B0B0B" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                    <span
                      style={{
                        width: '226px',
                        color: '#0B0B0B',
                        fontFamily: 'Poppins',
                        fontSize: '14px',
                        fontStyle: 'normal',
                        fontWeight: 500,
                        lineHeight: '25px'
                      }}
                    >
                      Voir le détail de la garantie
                    </span>
                  </div>
                </div>
              </div>

              {/* CGV */}
              <div className="mb-6" style={{ marginLeft: '50px' }}>
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
              <div style={{ marginLeft: '110px' }}>
                <button
                  type="submit"
                  disabled={!cgvAccepted}
                  className="text-white font-medium disabled:opacity-50"
                  style={{
                    display: 'flex',
                    width: '432px',
                    height: '50px',
                    padding: '10px',
                    alignItems: 'center',
                    justifyContent: 'center',
                    gap: '10px',
                    borderRadius: '30px',
                    background: '#41A334',
                    border: 'none',
                    cursor: cgvAccepted ? 'pointer' : 'not-allowed'
                  }}
                >
                  Valider le formulaire et passer au paiement
                </button>
              </div>
            </form>

            {/* Separator Line */}
            <div style={{ marginTop: '60px', marginBottom: '60px' }}>
              <div style={{ width: '672px', height: '1px', background: '#D9D9D9' }} />
            </div>

            {/* Payment Section - Étape 2/2 */}
            <div>
              <h2
                style={{
                  display: 'flex',
                  width: '673px',
                  height: '43px',
                  flexDirection: 'column',
                  justifyContent: 'center',
                  color: '#000',
                  fontFamily: 'Poppins',
                  fontSize: '20px',
                  fontStyle: 'normal',
                  fontWeight: 500,
                  lineHeight: '25px'
                }}
              >
                Étape 2/2 - paiement sécurisé
              </h2>

              {/* Payment Method Header */}
              <div style={{ marginTop: '28px', display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
                <p
                  style={{
                    width: '464px',
                    color: '#000',
                    textAlign: 'center',
                    fontFamily: 'Poppins',
                    fontSize: '16px',
                    fontStyle: 'normal',
                    fontWeight: 500,
                    lineHeight: '25px',
                    marginBottom: '20px'
                  }}
                >
                  Paiement sécurisé par Crédit Agricole
                </p>

                {/* Security Disclaimer */}
                <p
                  style={{
                    alignSelf: 'stretch',
                    color: '#5C5C5C',
                    textAlign: 'center',
                    fontFamily: 'Poppins',
                    fontSize: '13px',
                    fontStyle: 'italic',
                    fontWeight: 500,
                    lineHeight: '22px'
                  }}
                >
                  Vos données bancaires sont chiffrées par la solution Up2Pay-Crédit Agricole (cryptage SSL) et ne sont jamais stockées par ProStagesPermis
                </p>

                {/* Payment Card Logos */}
                <div style={{ marginTop: '10px', display: 'flex', justifyContent: 'center' }}>
                  <img src="/cards.png" alt="Visa, Mastercard, Discover, American Express" style={{ width: '160px', height: '31px' }} />
                </div>
              </div>

              {/* Payment Form */}
              <div style={{ marginTop: '40px' }}>
                {/* Nom sur la carte */}
                <div className="flex items-center mb-6" style={{ position: 'relative' }}>
                  <label
                    style={{
                      position: 'absolute',
                      left: 0,
                      color: '#000',
                      fontFamily: 'Poppins',
                      fontSize: '15px',
                      fontStyle: 'normal',
                      fontWeight: 400,
                      lineHeight: '25px'
                    }}
                  >
                    Nom sur la carte
                  </label>
                  <div style={{ marginLeft: '170px' }}>
                    <input
                      type="text"
                      value={nomCarte}
                      onChange={(e) => setNomCarte(e.target.value)}
                      required
                      placeholder="Nom"
                      className="border border-black"
                      style={{
                        display: 'flex',
                        width: '369px',
                        height: '35px',
                        padding: '7px 15px',
                        alignItems: 'center',
                        gap: '10px',
                        borderRadius: '8px',
                        border: '1px solid #000'
                      }}
                    />
                  </div>
                </div>

                {/* Numéro de carte */}
                <div className="flex items-center mb-6" style={{ position: 'relative' }}>
                  <label
                    style={{
                      position: 'absolute',
                      left: 0,
                      color: '#000',
                      fontFamily: 'Poppins',
                      fontSize: '15px',
                      fontStyle: 'normal',
                      fontWeight: 400,
                      lineHeight: '25px'
                    }}
                  >
                    Numéro de carte
                  </label>
                  <div style={{ marginLeft: '170px' }}>
                    <input
                      type="text"
                      value={numeroCarte}
                      onChange={(e) => setNumeroCarte(e.target.value)}
                      required
                      placeholder="Numéro de carte"
                      className="border border-black"
                      style={{
                        display: 'flex',
                        width: '369px',
                        height: '35px',
                        padding: '7px 15px',
                        alignItems: 'center',
                        gap: '10px',
                        borderRadius: '8px',
                        border: '1px solid #000'
                      }}
                      maxLength={16}
                    />
                  </div>
                </div>

                {/* Date expiration */}
                <div className="flex items-center mb-6" style={{ position: 'relative' }}>
                  <label
                    style={{
                      position: 'absolute',
                      left: 0,
                      color: '#000',
                      fontFamily: 'Poppins',
                      fontSize: '15px',
                      fontStyle: 'normal',
                      fontWeight: 400,
                      lineHeight: '25px'
                    }}
                  >
                    Date d'expiration
                  </label>
                  <div style={{ marginLeft: '170px', display: 'flex', gap: '15px' }}>
                    <input
                      type="text"
                      value={dateExpirationMois}
                      onChange={(e) => setDateExpirationMois(e.target.value)}
                      required
                      placeholder="Mois"
                      className="border border-black"
                      style={{
                        display: 'flex',
                        width: '60px',
                        height: '35px',
                        padding: '7px 8px',
                        justifyContent: 'center',
                        alignItems: 'center',
                        gap: '10px',
                        flexShrink: 0,
                        borderRadius: '8px',
                        border: '1px solid #000',
                        fontSize: '13px',
                        fontFamily: 'Poppins',
                        textAlign: 'center'
                      }}
                      maxLength={2}
                    />
                    <input
                      type="text"
                      value={dateExpirationAnnee}
                      onChange={(e) => setDateExpirationAnnee(e.target.value)}
                      required
                      placeholder="Année"
                      className="border border-black"
                      style={{
                        display: 'flex',
                        width: '66px',
                        height: '35px',
                        padding: '7px 8px',
                        justifyContent: 'center',
                        alignItems: 'center',
                        gap: '10px',
                        flexShrink: 0,
                        borderRadius: '8px',
                        border: '1px solid #000',
                        fontSize: '13px',
                        fontFamily: 'Poppins',
                        textAlign: 'center'
                      }}
                      maxLength={2}
                    />
                  </div>
                </div>

                {/* Code CVV */}
                <div className="flex items-center mb-6" style={{ position: 'relative' }}>
                  <label
                    style={{
                      position: 'absolute',
                      left: 0,
                      color: '#000',
                      fontFamily: 'Poppins',
                      fontSize: '15px',
                      fontStyle: 'normal',
                      fontWeight: 400,
                      lineHeight: '25px'
                    }}
                  >
                    Code (cvv)
                  </label>
                  <div style={{ marginLeft: '170px' }}>
                    <input
                      type="text"
                      value={codeCVV}
                      onChange={(e) => setCodeCVV(e.target.value)}
                      required
                      placeholder="Code"
                      className="border border-black"
                      style={{
                        display: 'flex',
                        width: '137px',
                        height: '35px',
                        padding: '7px 15px',
                        alignItems: 'center',
                        gap: '10px',
                        flexShrink: 0,
                        borderRadius: '8px',
                        border: '1px solid #000'
                      }}
                      maxLength={3}
                    />
                  </div>
                </div>
              </div>

              {/* Price Summary - Grey Box */}
              <div style={{ marginTop: '40px', marginLeft: '170px' }}>
                <div
                  style={{
                    display: 'flex',
                    width: '331px',
                    height: '129px',
                    padding: '8px 0',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    alignItems: 'center',
                    flexShrink: 0,
                    borderRadius: '15px',
                    background: '#EFEFEF'
                  }}
                >
                  {/* Date */}
                  <div
                    style={{
                      display: 'flex',
                      height: '21px',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      flexShrink: 0,
                      alignSelf: 'stretch',
                      color: '#000',
                      textAlign: 'center',
                      fontFamily: 'Poppins',
                      fontSize: '15px',
                      fontStyle: 'normal',
                      fontWeight: 500,
                      lineHeight: '22px'
                    }}
                  >
                    Stage du {formatDate(stage.date_start, stage.date_end)} à {formatCityName(stage.site.ville)}
                  </div>

                  {/* Prix du stage */}
                  <div
                    style={{
                      display: 'flex',
                      width: '189px',
                      height: '30px',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      flexShrink: 0,
                      color: '#000',
                      textAlign: 'center',
                      fontFamily: 'Poppins',
                      fontSize: '14px',
                      fontStyle: 'normal',
                      fontWeight: 400,
                      lineHeight: '22px'
                    }}
                  >
                    Prix du stage : {stage?.prix}€ TTC
                  </div>

                  {/* Garantie Sérénité */}
                  <div
                    style={{
                      display: 'flex',
                      width: '226px',
                      height: '30px',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      flexShrink: 0,
                      color: '#000',
                      textAlign: 'center',
                      fontFamily: 'Poppins',
                      fontSize: '14px',
                      fontStyle: 'normal',
                      fontWeight: 400,
                      lineHeight: '22px'
                    }}
                  >
                    Garantie Sérénité : {garantieSerenite ? '+57€ TTC' : 'N/A'}
                  </div>

                  {/* Total à payer */}
                  <div
                    style={{
                      display: 'flex',
                      width: '227px',
                      height: '30px',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      flexShrink: 0,
                      color: '#000',
                      textAlign: 'center',
                      fontFamily: 'Poppins',
                      fontSize: '15px',
                      fontStyle: 'normal',
                      fontWeight: 500,
                      lineHeight: '22px'
                    }}
                  >
                    Total à payer : {garantieSerenite ? stage?.prix + 57 : stage?.prix}€ TTC
                  </div>
                </div>

                {/* Payment Button */}
                <button
                  type="submit"
                  style={{
                    display: 'flex',
                    width: '203px',
                    height: '40px',
                    padding: '10px 43px',
                    justifyContent: 'center',
                    alignItems: 'center',
                    gap: '5px',
                    flexShrink: 0,
                    borderRadius: '30px',
                    background: '#41A334',
                    border: 'none',
                    cursor: 'pointer',
                    marginTop: '20px',
                    marginLeft: '64px'
                  }}
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none" style={{ flexShrink: 0 }}>
                    <path d="M7.29167 11.4584V7.29171C7.29167 5.91037 7.8404 4.58561 8.81715 3.60886C9.7939 2.63211 11.1187 2.08337 12.5 2.08337C13.8813 2.08337 15.2061 2.63211 16.1828 3.60886C17.1596 4.58561 17.7083 5.91037 17.7083 7.29171V11.4584M5.20833 11.4584H19.7917C20.9423 11.4584 21.875 12.3911 21.875 13.5417V20.8334C21.875 21.984 20.9423 22.9167 19.7917 22.9167H5.20833C4.05774 22.9167 3.125 21.984 3.125 20.8334V13.5417C3.125 12.3911 4.05774 11.4584 5.20833 11.4584Z" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <div
                    style={{
                      display: 'flex',
                      width: '146px',
                      height: '21px',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      flexShrink: 0,
                      color: '#FFF',
                      textAlign: 'center',
                      fontFamily: 'Poppins',
                      fontSize: '16px',
                      fontStyle: 'normal',
                      fontWeight: 400,
                      lineHeight: 'normal',
                      letterSpacing: '1.12px'
                    }}
                  >
                    Payer {garantieSerenite ? stage?.prix + 57 : stage?.prix}€ TTC
                  </div>
                </button>

                {/* Payment Disclaimer */}
                <div
                  style={{
                    display: 'flex',
                    height: '65px',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    flexShrink: 0,
                    color: '#000',
                    textAlign: 'center',
                    fontFamily: 'Poppins',
                    fontSize: '14px',
                    fontStyle: 'italic',
                    fontWeight: 400,
                    lineHeight: '22px',
                    marginTop: '10px',
                    marginLeft: '-170px',
                    width: '650px'
                  }}
                >
                  Après avoir cliqué sur "Payer", votre banque vous demandera une validation 3D secure. Une fois le paiement confirmé, vous recevez immédtiatement par email votre convocation au stage.
                </div>

                {/* Spacing and separator line */}
                <div style={{ marginTop: '40px', marginLeft: '-170px' }}>
                  <div
                    style={{
                      width: '680px',
                      height: '1px',
                      background: '#DEDDDD'
                    }}
                  />
                </div>

                {/* Informations pratiques section */}
                <div style={{ marginTop: '40px', marginLeft: '-170px' }}>
                  {/* Title */}
                  <h2
                    style={{
                      color: '#000',
                      fontFamily: 'Poppins',
                      fontSize: '20px',
                      fontStyle: 'normal',
                      fontWeight: 500,
                      lineHeight: '25px',
                      marginBottom: '10px'
                    }}
                  >
                    Informations pratiques sur votre stage
                  </h2>

                  {/* Tab widget */}
                  <div
                    style={{
                      display: 'flex',
                      width: '638px',
                      height: '43px',
                      padding: '2px 5px',
                      alignItems: 'center',
                      gap: '11px',
                      borderRadius: '10px',
                      border: '1px solid #C5C5C5',
                      background: '#DEDDDD'
                    }}
                  >
                    {/* Tab 1: Le prix du stage comprend */}
                    <button
                      onClick={() => setActiveTab('prix')}
                      style={{
                        flex: 1,
                        height: '39px',
                        display: 'flex',
                        justifyContent: 'center',
                        alignItems: 'center',
                        borderRadius: '8px',
                        background: activeTab === 'prix' ? '#FFFFFF' : 'transparent',
                        border: 'none',
                        cursor: 'pointer',
                        color: '#000',
                        textAlign: 'center',
                        fontFamily: 'Poppins',
                        fontSize: '14px',
                        fontStyle: 'normal',
                        fontWeight: 500,
                        lineHeight: 'normal',
                        letterSpacing: '0.98px',
                        transition: 'background 0.2s',
                        whiteSpace: 'nowrap',
                        padding: '0 15px'
                      }}
                    >
                      Le prix du stage comprend
                    </button>

                    {/* Tab 2: Programme */}
                    <button
                      onClick={() => setActiveTab('programme')}
                      style={{
                        flex: 1,
                        height: '39px',
                        display: 'flex',
                        justifyContent: 'center',
                        alignItems: 'center',
                        borderRadius: '8px',
                        background: activeTab === 'programme' ? '#FFFFFF' : 'transparent',
                        border: 'none',
                        cursor: 'pointer',
                        color: '#000',
                        textAlign: 'center',
                        fontFamily: 'Poppins',
                        fontSize: '14px',
                        fontStyle: 'normal',
                        fontWeight: 500,
                        lineHeight: 'normal',
                        letterSpacing: '0.98px',
                        transition: 'background 0.2s',
                        whiteSpace: 'nowrap',
                        padding: '0 15px'
                      }}
                    >
                      Programme
                    </button>

                    {/* Tab 3: Agrément */}
                    <button
                      onClick={() => setActiveTab('agrement')}
                      style={{
                        flex: 1,
                        height: '39px',
                        display: 'flex',
                        justifyContent: 'center',
                        alignItems: 'center',
                        borderRadius: '8px',
                        background: activeTab === 'agrement' ? '#FFFFFF' : 'transparent',
                        border: 'none',
                        cursor: 'pointer',
                        color: '#000',
                        textAlign: 'center',
                        fontFamily: 'Poppins',
                        fontSize: '14px',
                        fontStyle: 'normal',
                        fontWeight: 500,
                        lineHeight: 'normal',
                        letterSpacing: '0.98px',
                        transition: 'background 0.2s',
                        whiteSpace: 'nowrap',
                        padding: '0 15px'
                      }}
                    >
                      Agrément
                    </button>
                  </div>

                  {/* Tab Content Box */}
                  <div
                    style={{
                      display: 'flex',
                      width: '638px',
                      height: '177px',
                      padding: '11px 24px 8px 24px',
                      justifyContent: 'center',
                      alignItems: 'center',
                      borderRadius: '20px',
                      border: '1px solid #B2B2B2',
                      background: '#FFF',
                      marginTop: '5px'
                    }}
                  >
                    <div
                      style={{
                        display: 'flex',
                        width: '590px',
                        height: '158px',
                        flexDirection: 'column',
                        justifyContent: 'center',
                        flexShrink: 0,
                        color: '#000',
                        fontFamily: 'Poppins',
                        fontSize: '14px',
                        fontStyle: 'normal',
                        fontWeight: 400,
                        lineHeight: '25px',
                        letterSpacing: '0.98px'
                      }}
                    >
                      {activeTab === 'prix' && (
                        <ul style={{ listStyle: 'none', padding: 0, margin: 0 }}>
                          <li>• 14 heures de formation</li>
                          <li>• L'attestation de stage remise le deuxième jour</li>
                          <li>• La récupération automatique de 4 points</li>
                          <li>• Le traitement de votre dossier administratif en préfecture</li>
                          <li>• En cas d'empêchement, le transfert sur un autre stage de notre réseau</li>
                        </ul>
                      )}
                      {activeTab === 'programme' && (
                        <div>
                          <p>Programme détaillé du stage de récupération de points</p>
                        </div>
                      )}
                      {activeTab === 'agrement' && (
                        <div>
                          <p>Informations sur l'agrément préfectoral</p>
                        </div>
                      )}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* Questions fréquentes */}
            <div className="py-12">
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
          {/* End Left Column */}

          {/* Right Column - Stage Info */}
          <div style={{ position: 'sticky', top: '24px', alignSelf: 'flex-start' }}>
            <div className="bg-white rounded-lg border border-gray-200" style={{ width: '349px', padding: '20px' }}>
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
              <div className="flex justify-center mb-3">
                <div
                  style={{
                    width: '264.125px',
                    height: '1px',
                    backgroundColor: '#B6B6B6'
                  }}
                />
              </div>

              {/* Price Box */}
              <div className="mb-2 flex flex-col items-center">
                <div
                  style={{
                    display: 'flex',
                    height: '30px',
                    flexDirection: 'column',
                    justifyContent: 'flex-start',
                    flexShrink: 0,
                    marginBottom: '2px'
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
                      <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none" className="flex-shrink-0" style={{ width: '25px', height: '25px' }}>
                        <path d="M9.375 11.4583L12.5 14.5833L22.9167 4.16667M21.875 12.5V19.7917C21.875 20.3442 21.6555 20.8741 21.2648 21.2648C20.8741 21.6555 20.3442 21.875 19.7917 21.875H5.20833C4.6558 21.875 4.12589 21.6555 3.73519 21.2648C3.34449 20.8741 3.125 20.3442 3.125 19.7917V5.20833C3.125 4.6558 3.34449 4.12589 3.73519 3.73519C4.12589 3.34449 4.6558 3.125 5.20833 3.125H16.6667" stroke="#C4A226" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                      </svg>
                      <div
                        style={{
                          display: 'flex',
                          width: '233px',
                          flexDirection: 'column',
                          justifyContent: 'center'
                        }}
                      >
                        <p
                          style={{
                            color: 'rgba(6, 6, 6, 0.86)',
                            fontFamily: 'Poppins',
                            fontSize: '15px',
                            fontStyle: 'normal',
                            fontWeight: 400,
                            lineHeight: '20px'
                          }}
                        >
                          {benefit}
                        </p>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
          {/* End Right Column - Stage Info */}
        </div>
        {/* End Grid */}
      </div>
      {/* End Max Width Container */}
    </div>
  )
}
