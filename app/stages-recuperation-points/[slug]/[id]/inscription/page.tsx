'use client'

import { useState, useEffect, useRef } from 'react'
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

  // Form validation errors
  const [errors, setErrors] = useState<{
    civilite?: string
    nom?: string
    prenom?: string
    email?: string
    telephone?: string
    cgv?: string
  }>({})

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

  // Date change popup state
  const [isDatePopupOpen, setIsDatePopupOpen] = useState(false)
  const [availableStages, setAvailableStages] = useState<Stage[]>([])
  const [loadingStages, setLoadingStages] = useState(false)
  const [showDateChangedNotification, setShowDateChangedNotification] = useState(false)

  // Details popup state
  const [isDetailsModalOpen, setIsDetailsModalOpen] = useState(false)

  // Mobile-specific states
  const [formValidated, setFormValidated] = useState(false)
  const [paymentBlockVisible, setPaymentBlockVisible] = useState(false)
  const [isFormExpanded, setIsFormExpanded] = useState(true)
  const [isStageCardVisible, setIsStageCardVisible] = useState(true)
  const [isFormSectionVisible, setIsFormSectionVisible] = useState(true)
  const [isPaymentSectionVisible, setIsPaymentSectionVisible] = useState(false)
  const [isPayerButtonVisible, setIsPayerButtonVisible] = useState(false)
  const [isKeyboardOpen, setIsKeyboardOpen] = useState(false)
  const [isPayerButtonDisabled, setIsPayerButtonDisabled] = useState(false)
  const [showPhoneTooltip, setShowPhoneTooltip] = useState(false)

  // Desktop stepper state
  const [currentStep, setCurrentStep] = useState(1)

  // Garantie Sérénité accordion state
  const [isGarantieDetailOpen, setIsGarantieDetailOpen] = useState(false)

  // Refs for visibility detection
  const stageCardRef = useRef<HTMLDivElement>(null)
  const paymentSectionRef = useRef<HTMLDivElement>(null)
  const payerButtonRef = useRef<HTMLButtonElement>(null)

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

  // Keyboard detection for mobile
  useEffect(() => {
    if (typeof window === 'undefined') return

    const handleResize = () => {
      if (window.visualViewport) {
        const isOpen = window.visualViewport.height < window.innerHeight * 0.75
        setIsKeyboardOpen(isOpen)
      }
    }

    window.visualViewport?.addEventListener('resize', handleResize)
    return () => window.visualViewport?.removeEventListener('resize', handleResize)
  }, [])

  // Scroll detection for sticky behaviors
  useEffect(() => {
    if (typeof window === 'undefined') return

    const handleScroll = () => {
      // Check if stage card is visible
      const stageCard = document.getElementById('mobile-stage-card')
      const formSection = document.getElementById('mobile-form-section')
      const validateFormButton = document.getElementById('mobile-validate-form-button')
      const paymentSection = document.getElementById('mobile-payment-section')
      const payerButton = document.getElementById('mobile-payer-button')

      if (stageCard) {
        const rect = stageCard.getBoundingClientRect()
        setIsStageCardVisible(rect.top < window.innerHeight && rect.bottom > 0)
      }

      // Form section is visible if either the form section OR the validate button is on screen
      if (formSection || validateFormButton) {
        const formRect = formSection?.getBoundingClientRect()
        const buttonRect = validateFormButton?.getBoundingClientRect()

        const formVisible = formRect ? (formRect.top < window.innerHeight && formRect.bottom > 0) : false
        const buttonVisible = buttonRect ? (buttonRect.top < window.innerHeight && buttonRect.bottom > 0) : false

        setIsFormSectionVisible(formVisible || buttonVisible)
      }

      if (paymentSection) {
        const rect = paymentSection.getBoundingClientRect()
        setIsPaymentSectionVisible(rect.top < window.innerHeight && rect.bottom > 0)
      }

      if (payerButton) {
        const rect = payerButton.getBoundingClientRect()
        setIsPayerButtonVisible(rect.top < window.innerHeight && rect.bottom > 0)
      }
    }

    window.addEventListener('scroll', handleScroll)
    handleScroll() // Initial check

    return () => window.removeEventListener('scroll', handleScroll)
  }, [paymentBlockVisible])

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

  // Form validation function
  const validateForm = (): boolean => {
    const newErrors: typeof errors = {}
    let firstErrorId: string | null = null

    // Validate civilité
    if (!civilite) {
      newErrors.civilite = 'Veuillez sélectionner une civilité'
      if (!firstErrorId) firstErrorId = 'desktop-civilite'
    }

    // Validate nom
    if (!nom.trim()) {
      newErrors.nom = 'Veuillez entrer votre nom'
      if (!firstErrorId) firstErrorId = 'desktop-nom'
    }

    // Validate prénom
    if (!prenom.trim()) {
      newErrors.prenom = 'Veuillez entrer votre prénom'
      if (!firstErrorId) firstErrorId = 'desktop-prenom'
    }

    // Validate email
    if (!email.trim()) {
      newErrors.email = 'Veuillez entrer votre email'
      if (!firstErrorId) firstErrorId = 'desktop-email'
    }

    // Validate telephone
    if (!telephone.trim()) {
      newErrors.telephone = 'Veuillez entrer un numéro de téléphone'
      if (!firstErrorId) firstErrorId = 'desktop-telephone'
    }

    // Validate CGV
    if (!cgvAccepted) {
      newErrors.cgv = 'Veuillez accepter les conditions générales de vente'
      if (!firstErrorId) firstErrorId = 'desktop-cgv'
    }

    setErrors(newErrors)

    // If there are errors, scroll to the first one
    if (firstErrorId) {
      const element = document.getElementById(firstErrorId)
      if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'center' })
        element.focus()
      }
      return false
    }

    return true
  }

  const handleChangeDateClick = async (e: React.MouseEvent) => {
    e.preventDefault()
    setIsDatePopupOpen(true)

    // Fetch available stages for the same city
    if (!city) return

    setLoadingStages(true)
    try {
      const response = await fetch(`/api/stages/${city}`)
      if (!response.ok) throw new Error('Failed to fetch stages')

      const data = await response.json()
      let stages = data.stages || []

      // Filter future stages and sort by date
      const today = new Date()
      const todayStr = today.toISOString().split('T')[0]
      stages = stages.filter((s: Stage) => s.date_start >= todayStr)
      stages.sort((a: Stage, b: Stage) => a.date_start.localeCompare(b.date_start))

      // Limit to 20 stages
      setAvailableStages(stages.slice(0, 20))
    } catch (err) {
      console.error('Error fetching available stages:', err)
    } finally {
      setLoadingStages(false)
    }
  }

  const handleStageSelect = (selectedStage: Stage) => {
    // Update the current stage
    setStage(selectedStage)
    // Close the popup
    setIsDatePopupOpen(false)

    // Auto-scroll to top of page
    window.scrollTo({ top: 0, behavior: 'smooth' })

    // Show success notification
    setShowDateChangedNotification(true)

    // Hide notification after 5 seconds
    setTimeout(() => {
      setShowDateChangedNotification(false)
    }, 5000)

    // Update the URL without reloading the page
    const newUrl = `/stages-recuperation-points/${fullSlug}/${selectedStage.id}/inscription`
    window.history.pushState({}, '', newUrl)
  }

  const handleDetailsClick = () => {
    setIsDetailsModalOpen(true)
  }

  // Changer de date click in Details modal - close Details and open Date modal
  const handleChangeDateFromDetails = (e: React.MouseEvent) => {
    e.preventDefault()
    e.stopPropagation() // Prevent event bubbling

    // Close Details modal and open Date modal immediately
    setIsDetailsModalOpen(false)
    setIsDatePopupOpen(true)

    // Fetch available stages for the same city
    if (!city) return

    setLoadingStages(true)
    fetch(`/api/stages/${city}`)
      .then(response => {
        if (!response.ok) throw new Error('Failed to fetch stages')
        return response.json()
      })
      .then(data => {
        let stages = data.stages || []

        // Filter future stages and sort by date
        const today = new Date()
        const todayStr = today.toISOString().split('T')[0]
        stages = stages.filter((s: Stage) => s.date_start >= todayStr)
        stages.sort((a: Stage, b: Stage) => a.date_start.localeCompare(b.date_start))

        setAvailableStages(stages)
      })
      .catch(error => {
        console.error('Error fetching stages:', error)
      })
      .finally(() => {
        setLoadingStages(false)
      })
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

  // Mobile functions
  const handleValidateForm = () => {
    // Check if all required fields are filled
    if (!civilite || !nom || !prenom || !email || !telephone || !cgvAccepted) {
      // Find the first missing field and scroll to it (no popup)
      let firstMissingFieldId: string | null = null
      if (!civilite) firstMissingFieldId = 'mobile-civilite'
      else if (!nom) firstMissingFieldId = 'mobile-nom'
      else if (!prenom) firstMissingFieldId = 'mobile-prenom'
      else if (!email) firstMissingFieldId = 'mobile-email'
      else if (!telephone) firstMissingFieldId = 'mobile-telephone'
      else if (!cgvAccepted) firstMissingFieldId = 'mobile-cgv'

      // Scroll to the first missing field (without focusing/selecting it)
      if (firstMissingFieldId) {
        const element = document.getElementById(firstMissingFieldId)
        if (element) {
          element.scrollIntoView({ behavior: 'smooth', block: 'center' })
        }
      }

      // Keep payment block CLOSED
      return
    }

    // All fields are valid - reveal payment block and mark form as validated
    setPaymentBlockVisible(true)
    setFormValidated(true)
    setIsFormExpanded(false)

    // Scroll to payment section
    setTimeout(() => {
      const paymentSection = document.getElementById('mobile-payment-section')
      if (paymentSection) {
        paymentSection.scrollIntoView({ behavior: 'smooth', block: 'start' })
      }
    }, 100)
  }

  const handleModifierClick = () => {
    setIsFormExpanded(true)
    setIsPayerButtonDisabled(true)
  }

  const handleAnnulerClick = () => {
    setIsFormExpanded(false)
    setIsPayerButtonDisabled(false)
  }

  const handleReturnToPayment = () => {
    setIsFormExpanded(false)
    setIsPayerButtonDisabled(false)

    // Scroll to payment section
    setTimeout(() => {
      const paymentSection = document.getElementById('mobile-payment-section')
      if (paymentSection) {
        paymentSection.scrollIntoView({ behavior: 'smooth', block: 'start' })
      }
    }, 100)
  }

  const isFormComplete = civilite && nom && prenom && email && telephone && cgvAccepted
  const arePaymentFieldsFilled = nomCarte && numeroCarte && dateExpirationMois && dateExpirationAnnee && codeCVV

  if (loading) {
    return <div className="min-h-screen bg-white flex items-center justify-center">Chargement...</div>
  }

  if (!stage) {
    return <div className="min-h-screen bg-white flex items-center justify-center">Stage non trouvé</div>
  }

  // Calculate sticky states
  const totalPrice = garantieSerenite ? (stage?.prix || 0) + 57 : (stage?.prix || 0)

  // Determine which sticky to show (CAS logic)
  // STICKY 1a (MOBILE2 - no button): Form NOT validated, stage card NOT visible, but form section IS visible
  const showStickyType1a = !formValidated && !isStageCardVisible && isFormSectionVisible && !isKeyboardOpen

  // STICKY 1b (MOBILE1 - with button): Form NOT validated, stage card NOT visible, form section NOT visible
  const showStickyType1b = !formValidated && !isStageCardVisible && !isFormSectionVisible && !isKeyboardOpen

  // STICKY 2: CAS 2a1 - Form validated, payment fields NOT filled, Payer button not visible, IS on payment section
  const showStickyType2a1 = formValidated && paymentBlockVisible && !arePaymentFieldsFilled && !isPayerButtonVisible && isPaymentSectionVisible && !isKeyboardOpen

  // STICKY 3: CAS 2a3 - Form validated, payment fields NOT filled, Payer button not visible, NOT on payment section
  const showStickyType2a3 = formValidated && paymentBlockVisible && !arePaymentFieldsFilled && !isPayerButtonVisible && !isPaymentSectionVisible && !isKeyboardOpen

  // STICKY 4: CAS 2b1 - Form validated, payment fields FILLED, Payer button not visible
  const showStickyType2b1 = formValidated && paymentBlockVisible && arePaymentFieldsFilled && !isPayerButtonVisible && !isKeyboardOpen

  return (
    <div className="min-h-screen bg-white" style={{ fontFamily: 'var(--font-poppins)' }}>
      {/* MOBILE VERSION - Only visible on mobile */}
      <div className="md:hidden">
        {/* Mobile Header */}
        <div className="flex justify-between items-center px-3 py-2 bg-white border-b border-gray-200">
          <Image src="/prostagespermis-logo.png" alt="ProStagesPermis" width={120} height={30} priority className="h-6" />
          <div className="text-xs text-black">Aide et contact</div>
        </div>

        {/* Mobile Title */}
        <div className="px-3 py-3 bg-white">
          <h1 className="text-center font-normal leading-tight" style={{ fontFamily: 'Poppins', fontSize: '15px' }}>
            Stage Récupération de Points - av République, Marseille (13)
          </h1>
          <p className="text-center text-gray-600 mt-1" style={{ fontFamily: 'Poppins', fontSize: '15px' }}>
            + 4 points en 48h - Agréé Préfecture
          </p>
        </div>

        {/* Mobile Progress Steps */}
        <div className="flex justify-center items-center px-3 py-4 gap-2">
          {/* Step 1 - Coordonnées */}
          <div className="flex flex-col items-center">
            <div className={`w-7 h-7 rounded-full border-2 flex items-center justify-center mb-1 ${!formValidated ? 'border-black bg-white' : 'border-gray-300 bg-gray-100'}`}>
              <span className={`text-sm ${!formValidated ? 'font-normal text-black' : 'text-gray-400'}`}>1</span>
            </div>
            <p style={{ fontSize: '10px' }} className={`text-center ${!formValidated ? 'text-black' : 'text-gray-400'}`}>Coordonnées</p>
          </div>

          {/* Line */}
          <div className="flex-1 h-px bg-gray-300 mb-4" style={{ maxWidth: '60px' }} />

          {/* Step 2 - Paiement */}
          <div className="flex flex-col items-center">
            <div className={`w-7 h-7 rounded-full border-2 flex items-center justify-center mb-1 ${formValidated ? 'border-black bg-white' : 'border-gray-300 bg-gray-100'}`}>
              <span className={`text-sm ${formValidated ? 'font-normal text-black' : 'text-gray-400'}`}>2</span>
            </div>
            <p style={{ fontSize: '10px' }} className={`text-center ${formValidated ? 'text-black' : 'text-gray-400'}`}>Paiement</p>
          </div>

          {/* Line */}
          <div className="flex-1 h-px bg-gray-300 mb-4" style={{ maxWidth: '60px' }} />

          {/* Step 3 - Confirmation */}
          <div className="flex flex-col items-center">
            <div className="w-7 h-7 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center mb-1">
              <span className="text-sm text-gray-400">3</span>
            </div>
            <p style={{ fontSize: '10px' }} className="text-center text-gray-400">Confirmation</p>
          </div>
        </div>

        {/* Back Link */}
        <div className="px-3 py-1">
          <a href={`/stages-recuperation-points/${city.toLowerCase()}`} className="text-black" style={{ fontSize: '13px' }}>
            &lt; Retour aux stages à {formatCityName(city)}
          </a>
        </div>

        {/* Stage Card */}
        <div id="mobile-stage-card" className="mx-auto my-3" style={{ width: '363px', padding: '10px 8px', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '5px', background: '#FFF' }}>
          <div className="mb-1" style={{ display: 'flex', width: '337px', height: '38px', padding: '8px 20px', justifyContent: 'center', alignItems: 'center', gap: '10px', borderRadius: '8px', background: '#EFEFEF' }}>
            <p className="text-center font-medium" style={{ fontSize: '17px', whiteSpace: 'nowrap' }}>Stage du {stage && formatDate(stage.date_start, stage.date_end)}</p>
          </div>

          <p className="text-center font-normal" style={{ fontSize: '21px', marginBottom: '2px' }}>{stage?.prix}€ TTC</p>
          <p className="text-center text-green-700" style={{ fontSize: '14px', marginBottom: '8px' }}>Places disponibles</p>

          {/* Thin grey separator line - half width of widget */}
          <div style={{ width: '168px', height: '1px', background: '#D9D9D9', marginBottom: '8px' }} />

          <div className="flex items-center gap-2 mb-1.5 w-full">
            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 20 20" fill="none" className="flex-shrink-0">
              <g clipPath="url(#clip0_180_68)">
                <path d="M13.3333 1.66669V5.00002M6.66667 1.66669V5.00002M2.5 8.33335H17.5M4.16667 3.33335H15.8333C16.7538 3.33335 17.5 4.07955 17.5 5.00002V16.6667C17.5 17.5872 16.7538 18.3334 15.8333 18.3334H4.16667C3.24619 18.3334 2.5 17.5872 2.5 16.6667V5.00002C2.5 4.07955 3.24619 3.33335 4.16667 3.33335Z" stroke="#595656" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round"/>
              </g>
            </svg>
            <button onClick={handleChangeDateClick} className="text-blue-600" style={{ fontSize: '14px' }}>Changer de date</button>
          </div>

          <div className="flex gap-2 mb-1.5 text-gray-600 w-full">
            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 20 20" fill="none" className="flex-shrink-0">
              <path d="M17.5 8.33337C17.5 14.1667 10 19.1667 10 19.1667C10 19.1667 2.5 14.1667 2.5 8.33337C2.5 6.34425 3.29018 4.4366 4.6967 3.03007C6.10322 1.62355 8.01088 0.833374 10 0.833374C11.9891 0.833374 13.8968 1.62355 15.3033 3.03007C16.7098 4.4366 17.5 6.34425 17.5 8.33337Z" stroke="#595656" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              <path d="M10 10.8334C11.3807 10.8334 12.5 9.71409 12.5 8.33337C12.5 6.95266 11.3807 5.83337 10 5.83337C8.61929 5.83337 7.5 6.95266 7.5 8.33337C7.5 9.71409 8.61929 10.8334 10 10.8334Z" stroke="#595656" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
            <p style={{ fontSize: '14px', color: '#4A4A4A', fontFamily: 'Poppins', lineHeight: '22px' }}>{stage && `av de Saint Menet, 13001 ${formatCityName(stage.site.ville)}`}</p>
          </div>

          <div className="flex gap-2 mb-1.5 text-gray-600 w-full">
            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 20 20" fill="none" className="flex-shrink-0">
              <path d="M10 5V10L13.3333 11.6667M18.3333 10C18.3333 14.6024 14.6024 18.3333 10 18.3333C5.39763 18.3333 1.66667 14.6024 1.66667 10C1.66667 5.39763 5.39763 1.66667 10 1.66667C14.6024 1.66667 18.3333 5.39763 18.3333 10Z" stroke="#595656" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
            <p style={{ fontSize: '14px', color: '#4A4A4A', fontFamily: 'Poppins', lineHeight: '22px' }}>08h15-12h30 et 13h30-16h30</p>
          </div>

          <div className="flex gap-2 text-gray-600 w-full" style={{ marginBottom: '12px' }}>
            <Image
              src="/flag-france.png"
              alt="Drapeau français"
              width={24}
              height={16}
              className="flex-shrink-0 rounded-lg"
              style={{
                width: '24px',
                height: '16px',
                aspectRatio: '3/2',
                borderRadius: '10px',
                objectFit: 'cover'
              }}
            />
            <p style={{
              fontSize: '14px',
              width: '265px',
              height: '42px',
              color: '#4A4A4A',
              fontFamily: 'Poppins',
              fontStyle: 'normal',
              fontWeight: '400',
              lineHeight: '22px'
            }}>
              Agrément n° 25 R130060090064 par la Préfecture des Bouches-du-Rhône
            </p>
          </div>

          {/* Benefits with yellow checkmarks */}
          <div className="space-y-1.5" style={{ display: 'flex', width: '308px', padding: '12px 16px', flexDirection: 'column', justifyContent: 'center', alignItems: 'flex-start', gap: '-5px', borderRadius: '8px', border: '1px solid #9B9A9A' }}>
            {[
              'Stage officiel agréé Préfecture',
              '+4 points en 48h',
              'Report ou remboursement en cas d\'imprévu',
              'Attestation de stage remise le 2ème jour',
              'Paiement 100% sécurisé',
              '98,7% de clients satisfaits'
            ].map((benefit, index) => (
              <div key={index} className="flex gap-1.5 items-start">
                <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 25 25" fill="none" className="flex-shrink-0 mt-0.5">
                  <path d="M9.375 11.4583L12.5 14.5833L22.9167 4.16667M21.875 12.5V19.7917C21.875 20.3442 21.6555 20.8741 21.2648 21.2648C20.8741 21.6555 20.3442 21.875 19.7917 21.875H5.20833C4.6558 21.875 4.12589 21.6555 3.73519 21.2648C3.34449 20.8741 3.125 20.3442 3.125 19.7917V5.20833C3.125 4.6558 3.34449 4.12589 3.73519 3.73519C4.12589 3.34449 4.6558 3.125 5.20833 3.125H16.6667" stroke="#C4A226" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
                <p className="text-gray-800" style={{ fontSize: '13px', lineHeight: '1.4' }}>{benefit}</p>
              </div>
            ))}
          </div>
        </div>

        {/* Grey separator line below stage card */}
        <div className="mx-auto" style={{ width: '363px', height: '1px', background: '#D9D9D9', marginTop: '20px', marginBottom: '20px' }} />

        {/* Form Section */}
        <div id="mobile-form-section" className="px-3 py-0">
          {!formValidated ? (
            <>
              <h2 className="font-medium mb-1" style={{ fontSize: '14px' }}>Étape 1/2 : coordonnées personnelles</h2>
              <p className="italic text-gray-600 mb-3" style={{ fontSize: '11px' }}>• Tous les champs sont obligatoires</p>

              {/* Form fields */}
              <div className="space-y-3">
                <div>
                  <label className="block mb-1" style={{ fontSize: '12px' }}>Civilité *</label>
                  <select id="mobile-civilite" value={civilite} onChange={(e) => setCivilite(e.target.value)} className="w-full border border-black rounded-lg px-2 py-1.5" style={{ fontSize: '12px' }}>
                    <option value="">Sélectionner</option>
                    <option value="Monsieur">Monsieur</option>
                    <option value="Madame">Madame</option>
                  </select>
                </div>

                <div>
                  <label className="block mb-1" style={{ fontSize: '12px' }}>Nom *</label>
                  <input id="mobile-nom" type="text" value={nom} onChange={(e) => setNom(e.target.value)} placeholder="Nom" className="w-full border border-black rounded-lg px-2 py-1.5" style={{ fontSize: '12px' }} />
                </div>

                <div>
                  <label className="block mb-1" style={{ fontSize: '12px' }}>Prénom *</label>
                  <input id="mobile-prenom" type="text" value={prenom} onChange={(e) => setPrenom(e.target.value)} placeholder="Prénom" className="w-full border border-black rounded-lg px-2 py-1.5" style={{ fontSize: '12px' }} />
                </div>

                <div>
                  <label className="block mb-1" style={{ fontSize: '12px' }}>Email *</label>
                  <input id="mobile-email" type="email" value={email} onChange={(e) => setEmail(e.target.value)} placeholder="Email" className="w-full border border-black rounded-lg px-2 py-1.5" style={{ fontSize: '12px' }} />
                </div>

                <div>
                  <div className="flex items-center gap-1 mb-1">
                    <label style={{ fontSize: '12px' }}>Téléphone mobile *</label>
                    <div className="relative">
                      <button
                        type="button"
                        onClick={() => setShowPhoneTooltip(true)}
                        className="cursor-pointer"
                        aria-label="Information téléphone mobile"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none" style={{ width: '16px', height: '16px', flexShrink: 0 }}>
                          <g clipPath="url(#clip0_75_35)">
                            <path d="M7.99999 10.6667V8.00004M7.99999 5.33337H8.00666M14.6667 8.00004C14.6667 11.6819 11.6819 14.6667 7.99999 14.6667C4.3181 14.6667 1.33333 11.6819 1.33333 8.00004C1.33333 4.31814 4.3181 1.33337 7.99999 1.33337C11.6819 1.33337 14.6667 4.31814 14.6667 8.00004Z" stroke="#1E1E1E" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"/>
                          </g>
                          <defs>
                            <clipPath id="clip0_75_35">
                              <rect width="16" height="16" fill="white"/>
                            </clipPath>
                          </defs>
                        </svg>
                      </button>

                      {/* Phone Tooltip - positioned top-right of the i icon */}
                      {showPhoneTooltip && (
                        <>
                          {/* Backdrop for click outside detection */}
                          <div
                            className="fixed inset-0 z-40"
                            onClick={() => setShowPhoneTooltip(false)}
                          />
                          {/* Tooltip */}
                          <div
                            className="absolute z-50 bg-white border border-gray-300 rounded-lg shadow-lg p-2.5"
                            style={{
                              width: '220px',
                              bottom: '100%',
                              left: '50%',
                              transform: 'translateX(-30%)',
                              marginBottom: '8px'
                            }}
                          >
                            <div className="flex justify-between items-start gap-2">
                              <p className="italic" style={{ color: '#2E2E2E', fontFamily: 'Poppins', fontSize: '11px', fontStyle: 'italic', fontWeight: '400', lineHeight: '15px' }}>
                                Important : indiquez un numéro de mobile valide. Il servira au SMS de confirmation et aux informations essentielles liées à votre stage.
                              </p>
                              <button
                                type="button"
                                onClick={() => setShowPhoneTooltip(false)}
                                className="flex-shrink-0 w-4 h-4 flex items-center justify-center text-gray-600 hover:text-black"
                                aria-label="Fermer"
                              >
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 16 16" fill="none">
                                  <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                                </svg>
                              </button>
                            </div>
                          </div>
                        </>
                      )}
                    </div>
                  </div>

                  <input id="mobile-telephone" type="tel" value={telephone} onChange={(e) => setTelephone(e.target.value)} placeholder="Téléphone" className="w-full border border-black rounded-lg px-2 py-1.5" style={{ fontSize: '12px' }} />
                </div>

                {/* Garantie Sérénité */}
                <div className="bg-gray-100 rounded-lg p-2.5">
                  <div className="flex items-center gap-1.5 mb-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 25 25" fill="none">
                      <g clipPath="url(#clip0_122_55)">
                        <path d="M12.5 22.9166C12.5 22.9166 20.8334 18.75 20.8334 12.5V5.20831L12.5 2.08331L4.16669 5.20831V12.5C4.16669 18.75 12.5 22.9166 12.5 22.9166Z" fill="#EFEFEF" stroke="#696868" strokeOpacity="0.96" strokeWidth="3.5" strokeLinecap="round" strokeLinejoin="round"/>
                      </g>
                      <defs>
                        <clipPath id="clip0_122_55">
                          <rect width="25" height="25" fill="white"/>
                        </clipPath>
                      </defs>
                    </svg>
                    <span className="font-medium underline" style={{ fontSize: '12px' }}>Garantie Sérénité</span>
                  </div>
                  <label className="flex items-start gap-1.5 cursor-pointer mb-1.5">
                    <input type="checkbox" checked={garantieSerenite} onChange={(e) => setGarantieSerenite(e.target.checked)} className="mt-0.5" />
                    <span style={{ fontSize: '11px' }}>Je souscris à la Garantie Sérénité: +57€ TTC (supplement facturé en plus du stage)</span>
                  </label>
                  <div className="flex items-center justify-center gap-2 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="22" viewBox="0 0 25 22" fill="none" style={{ width: '25px', height: '25px' }}>
                      <path d="M6.25 9.375L12.5 15.625L18.75 9.375" stroke="#1E1E1E" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                    <div className="font-medium" style={{ fontSize: '11px' }}>Voir le détail de la garantie</div>
                  </div>
                </div>

                {/* CGV */}
                <label id="mobile-cgv" className="flex items-start gap-1.5 cursor-pointer">
                  <input type="checkbox" checked={cgvAccepted} onChange={(e) => setCgvAccepted(e.target.checked)} className="mt-0.5" />
                  <span style={{ fontSize: '11px' }}>J'accepte les <a href="#" className="text-blue-600 underline">conditions générales de vente</a></span>
                </label>

                {/* Submit Button */}
                <div className="flex justify-center">
                  <button
                    id="mobile-validate-form-button"
                    onClick={handleValidateForm}
                    className="text-white disabled:opacity-50"
                    style={{
                      display: 'flex',
                      width: '280px',
                      height: '62px',
                      padding: '10px',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '10px',
                      flexShrink: 0,
                      borderRadius: '30px',
                      background: '#41A334'
                    }}
                  >
                    <span style={{
                      color: '#FFF',
                      textAlign: 'center',
                      fontFamily: 'Poppins',
                      fontSize: '16px',
                      fontStyle: 'normal',
                      fontWeight: '400',
                      lineHeight: 'normal',
                      letterSpacing: '1.12px',
                      whiteSpace: 'nowrap'
                    }}>
                      Valider et passer au paiement
                    </span>
                  </button>
                </div>
              </div>
            </>
          ) : (
            <>
              {/* Form Summary */}
              {!isFormExpanded ? (
                <div className="p-2.5 mb-3">
                  <div className="flex items-center justify-between gap-1.5 mb-1.5">
                    <h3 className="font-medium" style={{ fontSize: '13px' }}>Étape 1/2 : coordonnées personnelles renseignées</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="none" className="flex-shrink-0">
                      <path d="M16.6667 5L7.50004 14.1667L3.33337 10" stroke="#41A334" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                  </div>
                  <div className="text-gray-700 space-y-0.5" style={{ fontSize: '11px' }}>
                    <p>{prenom} {nom}</p>
                    <p>Mail: {email}</p>
                    <p>Tel: {telephone}</p>
                  </div>
                  <div className="flex justify-center mt-2">
                    <button
                      onClick={handleModifierClick}
                      style={{
                        display: 'flex',
                        width: '196px',
                        height: '34px',
                        padding: '10px',
                        justifyContent: 'center',
                        alignItems: 'center',
                        gap: '10px',
                        flexShrink: 0,
                        borderRadius: '12px',
                        background: '#E1E1E1',
                        fontSize: '12px'
                      }}
                    >
                      Modifier
                    </button>
                  </div>
                  {/* Grey separator line below Modifier button */}
                  <div className="mx-auto mt-3" style={{ width: '363px', height: '1px', background: '#D9D9D9' }} />
                </div>
              ) : (
                <>
                  {/* Expanded Form */}
                  <h2 className="font-medium mb-3" style={{ fontSize: '14px' }}>Étape 1/2 : coordonnées personnelles pour l'inscription</h2>
                  <p className="italic mb-3" style={{ fontSize: '11px' }}>• Tous les champs sont obligatoires</p>
                  <div className="space-y-3">
                    <div>
                      <label className="block mb-1" style={{ fontSize: '12px' }}>Civilité *</label>
                      <select value={civilite} onChange={(e) => setCivilite(e.target.value)} className="w-full border border-black rounded-lg px-2 py-1.5" style={{ fontSize: '12px' }}>
                        <option value="Monsieur">Monsieur</option>
                        <option value="Madame">Madame</option>
                      </select>
                    </div>
                    <div>
                      <label className="block mb-1" style={{ fontSize: '12px' }}>Nom *</label>
                      <input type="text" value={nom} onChange={(e) => setNom(e.target.value)} placeholder="Nom" className="w-full border border-black rounded-lg px-2 py-1.5" style={{ fontSize: '12px' }} />
                    </div>
                    <div>
                      <label className="block mb-1" style={{ fontSize: '12px' }}>Prénom *</label>
                      <input type="text" value={prenom} onChange={(e) => setPrenom(e.target.value)} placeholder="Prénom" className="w-full border border-black rounded-lg px-2 py-1.5" style={{ fontSize: '12px' }} />
                    </div>
                    <div>
                      <label className="block mb-1" style={{ fontSize: '12px' }}>Email *</label>
                      <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} placeholder="Email" className="w-full border border-black rounded-lg px-2 py-1.5" style={{ fontSize: '12px' }} />
                    </div>
                    <div>
                      <label className="block mb-1" style={{ fontSize: '12px' }}>Téléphone mobile *</label>
                      <input type="tel" value={telephone} onChange={(e) => setTelephone(e.target.value)} placeholder="Téléphone" className="w-full border border-black rounded-lg px-2 py-1.5" style={{ fontSize: '12px' }} />
                    </div>

                    {/* Garantie Sérénité */}
                    <div className="bg-gray-100 rounded-lg p-2.5">
                      <div className="flex items-center gap-1.5 mb-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none" style={{ width: '25px', height: '25px' }}>
                          <path d="M12.5 22.9167C12.5 22.9167 20.8333 18.75 20.8333 12.5V5.20833L12.5 2.08333L4.16667 5.20833V12.5C4.16667 18.75 12.5 22.9167 12.5 22.9167Z" stroke="#1E1E1E" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                        </svg>
                        <span className="font-medium underline" style={{ fontSize: '12px' }}>Garantie Sérénité</span>
                      </div>
                      <label className="flex items-start gap-1.5 cursor-pointer mb-1.5">
                        <input type="checkbox" checked={garantieSerenite} onChange={(e) => setGarantieSerenite(e.target.checked)} className="mt-0.5" />
                        <span style={{ fontSize: '11px' }}>Je souscris à la Garantie Sérénité: +57€ TTC (supplement facturé en plus du stage)</span>
                      </label>
                      <div className="flex items-center justify-center gap-2 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="22" viewBox="0 0 25 22" fill="none" style={{ width: '25px', height: '25px' }}>
                          <path d="M6.25 9.375L12.5 15.625L18.75 9.375" stroke="#1E1E1E" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                        </svg>
                        <div className="font-medium" style={{ fontSize: '11px' }}>Voir le détail de la garantie</div>
                      </div>
                    </div>

                    {/* CGV - Pre-checked */}
                    <label className="flex items-start gap-1.5 cursor-pointer">
                      <input type="checkbox" checked={true} onChange={(e) => setCgvAccepted(e.target.checked)} className="mt-0.5" />
                      <span style={{ fontSize: '11px' }}>J'accepte <a href="#" className="text-blue-600 underline">les conditions générales de vente</a></span>
                    </label>

                    {/* Buttons - Stacked vertically */}
                    <div className="space-y-2">
                      <button onClick={handleAnnulerClick} className="w-full bg-gray-300 py-2 rounded-full" style={{ fontSize: '12px' }}>Annuler</button>
                      <button onClick={handleReturnToPayment} className="w-full bg-green-600 text-white py-2 rounded-full" style={{ fontSize: '12px' }}>Valider le formulaire et repasser au paiement</button>
                    </div>
                  </div>
                </>
              )}
            </>
          )}
        </div>

        {/* Payment Section */}
        {paymentBlockVisible && (
          <div id="mobile-payment-section" className="px-3 py-3">
            <h2 className="font-medium mb-3" style={{ fontSize: '14px' }}>Étape 2/2 : paiement sécurisé</h2>

            <p className="text-center font-medium mb-1" style={{ fontSize: '13px' }}>Paiement sécurisé par Crédit Agricole</p>
            <p className="text-center italic text-gray-600 mb-2" style={{ fontSize: '10px' }}>
              Vos données bancaires sont chiffrées par la solution Up2Pay-Crédit Agricole (cryptage SSL) et ne sont jamais stockées par ProStagesPermis
            </p>

            <div className="flex justify-center mb-3">
              <img src="/cards.png" alt="Cards" className="h-6" />
            </div>

            {/* Payment Fields */}
            <div className="space-y-3">
              <div>
                <label className="block mb-1" style={{ fontSize: '12px' }}>Nom sur la carte</label>
                <input
                  type="text"
                  value={nomCarte}
                  onChange={(e) => setNomCarte(e.target.value)}
                  placeholder="Nom"
                  className="w-full border border-black rounded-lg px-2 py-1.5"
                  style={{ fontSize: '12px' }}
                />
              </div>

              <div>
                <label className="block mb-1" style={{ fontSize: '12px' }}>Numéro de carte</label>
                <input
                  type="text"
                  value={numeroCarte}
                  onChange={(e) => setNumeroCarte(e.target.value)}
                  placeholder="Numéro de carte"
                  maxLength={16}
                  className="w-full border border-black rounded-lg px-2 py-1.5"
                  style={{ fontSize: '12px' }}
                />
              </div>

              <div>
                <label className="block mb-1" style={{ fontSize: '12px' }}>Date d&apos;expiration</label>
                <div className="flex gap-2">
                  <input
                    type="text"
                    value={dateExpirationMois}
                    onChange={(e) => setDateExpirationMois(e.target.value)}
                    placeholder="Mois"
                    maxLength={2}
                    className="w-16 border border-black rounded-lg px-2 py-1.5 text-center"
                    style={{ fontSize: '12px' }}
                  />
                  <input
                    type="text"
                    value={dateExpirationAnnee}
                    onChange={(e) => setDateExpirationAnnee(e.target.value)}
                    placeholder="Année"
                    maxLength={2}
                    className="w-16 border border-black rounded-lg px-2 py-1.5 text-center"
                    style={{ fontSize: '12px' }}
                  />
                </div>
              </div>

              <div>
                <label className="block mb-1" style={{ fontSize: '12px' }}>Code (cvv)</label>
                <input
                  type="text"
                  value={codeCVV}
                  onChange={(e) => setCodeCVV(e.target.value)}
                  placeholder="Code"
                  maxLength={3}
                  className="w-24 border border-black rounded-lg px-2 py-1.5"
                  style={{ fontSize: '12px' }}
                />
              </div>

              {/* Price Summary */}
              <div className="bg-gray-200 rounded-lg p-2.5 text-center">
                <p className="font-medium mb-1" style={{ fontSize: '12px' }}>Stage du {stage && formatDate(stage.date_start, stage.date_end)} à {stage && formatCityName(stage.site.ville)}</p>
                <p style={{ fontSize: '11px' }}>Prix du stage : {stage?.prix}€ TTC</p>
                {garantieSerenite && (
                  <p style={{ fontSize: '11px' }}>Garantie Sérénité : +57€ TTC</p>
                )}
                <p className="font-medium mt-1" style={{ fontSize: '13px' }}>Total à payer : {totalPrice}€ TTC</p>
              </div>

              {/* Payer Button */}
              <div className="flex justify-center">
                <button
                  id="mobile-payer-button"
                  onClick={handleSubmit}
                  disabled={isFormExpanded || isPayerButtonDisabled}
                  className="flex items-center disabled:bg-gray-400"
                  style={{
                    display: 'flex',
                    width: '232px',
                    height: '51px',
                    padding: '10px 15px',
                    justifyContent: 'center',
                    alignItems: 'center',
                    gap: '8px',
                    flexShrink: 0,
                    borderRadius: '30px',
                    background: (isFormExpanded || isPayerButtonDisabled) ? '#9CA3AF' : '#41A334'
                  }}
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none" className="flex-shrink-0">
                    <path d="M7.29167 11.4584V7.29171C7.29167 5.91037 7.8404 4.58561 8.81715 3.60886C9.7939 2.63211 11.1187 2.08337 12.5 2.08337C13.8813 2.08337 15.2061 2.63211 16.1828 3.60886C17.1596 4.58561 17.7083 5.91037 17.7083 7.29171V11.4584M5.20833 11.4584H19.7917C20.9423 11.4584 21.875 12.3911 21.875 13.5417V20.8334C21.875 21.984 20.9423 22.9167 19.7917 22.9167H5.20833C4.05774 22.9167 3.125 21.984 3.125 20.8334V13.5417C3.125 12.3911 4.05774 11.4584 5.20833 11.4584Z" stroke="white" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                  </svg>
                  <span style={{
                    color: '#FFF',
                    textAlign: 'center',
                    fontFamily: 'Poppins',
                    fontSize: '16px',
                    fontStyle: 'normal',
                    fontWeight: '400',
                    lineHeight: 'normal',
                    letterSpacing: '1.12px',
                    whiteSpace: 'nowrap'
                  }}>
                    Payer {totalPrice}€ TTC
                  </span>
                </button>
              </div>

              <p className="text-center italic mt-2 mb-6" style={{ fontSize: '10px' }}>
                Après avoir cliqué sur &quot;Payer&quot;, votre banque vous demandera une validation 3D secure. Une fois le paiement confirmé, vous recevez immédiatement par email votre convocation au stage.
              </p>
            </div>
          </div>
        )}

        {/* Informations pratiques section - shown by default */}
        <div className="px-3 py-4">
          <h3 className="font-medium mb-1.5" style={{
            height: '55px',
            fontSize: '20px',
            fontWeight: '500',
            lineHeight: '25px',
            display: 'flex',
            flexDirection: 'column',
            justifyContent: 'center',
            flexShrink: 0,
            alignSelf: 'stretch'
          }}>Informations pratiques sur votre stage</h3>
          <p className="text-gray-700 mb-2" style={{ fontSize: '11px' }}>
            Pour en savoir plus sur ce que comprends le prix de votre stage (programme, déroulement, agrément.
          </p>

          {/* Tabs */}
          <div className="border border-gray-300 rounded-lg bg-gray-200 flex p-0.5 mb-1.5">
            <button
              onClick={() => setActiveTab('prix')}
              className={`flex-1 py-1.5 px-1.5 rounded-lg font-medium ${activeTab === 'prix' ? 'bg-white' : 'bg-transparent'}`}
              style={{ fontSize: '10px' }}
            >
              Détails du stage
            </button>
            <button
              onClick={() => setActiveTab('agrement')}
              className={`flex-1 py-1.5 px-1.5 rounded-lg font-medium ${activeTab === 'agrement' ? 'bg-white' : 'bg-transparent'}`}
              style={{ fontSize: '10px' }}
            >
              Agrément
            </button>
            <button
              onClick={() => setActiveTab('programme')}
              className={`flex-1 py-1.5 px-1.5 rounded-lg font-medium ${activeTab === 'programme' ? 'bg-white' : 'bg-transparent'}`}
              style={{ fontSize: '10px' }}
            >
              Programme
            </button>
          </div>

          {/* Tab Content */}
          <div className="border border-gray-300 rounded-lg p-2.5 bg-white">
            {activeTab === 'prix' && (
              <ul className="space-y-1" style={{ fontSize: '11px' }}>
                <li>• 14 heures de formation</li>
                <li>• L&apos;attestation de stage remise le deuxième jour</li>
                <li>• La récupération automatique de 4 points</li>
                <li>• Le traitement de votre dossier administratif en préfecture</li>
                <li>• En cas d&apos;empêchement, le transfert sur un autre stage de notre réseau</li>
              </ul>
            )}
            {activeTab === 'agrement' && (
              <p style={{ fontSize: '11px' }}>Informations sur l&apos;agrément préfectoral</p>
            )}
            {activeTab === 'programme' && (
              <p style={{ fontSize: '11px' }}>Programme détaillé du stage de récupération de points</p>
            )}
          </div>
        </div>

        {/* Questions fréquentes */}
        <div className="px-3 py-4 bg-gray-100">
          <h3 className="mb-1.5" style={{
            color: 'rgba(6, 6, 6, 0.86)',
            WebkitTextStrokeWidth: '1px',
            WebkitTextStrokeColor: '#000',
            fontFamily: 'Poppins',
            fontSize: '20px',
            fontStyle: 'normal',
            fontWeight: '250',
            lineHeight: '35px'
          }}>
            Questions fréquentes
          </h3>
          <p className="mb-3" style={{ fontSize: '11px' }}>Vous vous posez encore des questions ?</p>

          {/* FAQ Items */}
          {[0, 1, 2].map((index) => (
            <div key={index} className="mb-2">
              <div
                onClick={() => setOpenFaqIndex(openFaqIndex === index ? null : index)}
                className="flex justify-between items-center cursor-pointer py-1.5"
              >
                <p className="flex-1" style={{ fontSize: '11px' }}>A quel moment mes 4 points sont il crédités sur mon permis après un stage</p>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="16"
                  height="16"
                  viewBox="0 0 25 25"
                  fill="none"
                  className="flex-shrink-0"
                  style={{ transform: openFaqIndex === index ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform 0.2s' }}
                >
                  <path d="M6.25 9.375L12.5 15.625L18.75 9.375" stroke="#1E1E1E" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
              </div>
              {openFaqIndex === index && (
                <p className="text-gray-600 py-1.5" style={{ fontSize: '10px' }}>Réponse à la question - Texte placeholder</p>
              )}
              {index < 2 && <div className="h-px bg-gray-300 mt-1.5" />}
            </div>
          ))}

          <button className="font-medium underline mt-3 block mx-auto" style={{ fontSize: '11px' }}>
            Afficher plus de questions
          </button>
        </div>

        {/* Bottom Spacing */}
        <div style={{ height: '100px' }} />

        {/* Sticky Bar - CAS Logic */}
        {!isKeyboardOpen && (
          <>
            {/* DATE CHANGED NOTIFICATION - Shows for 5 seconds, replaces other stickies */}
            {showDateChangedNotification ? (
              <div className="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-300 shadow-lg z-50 flex items-center justify-center gap-3" style={{ padding: '16px' }}>
                {/* Green checkmark icon */}
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                  <path d="M26.6667 8L12 22.6667L5.33337 16" stroke="#41A334" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
                {/* Text */}
                <p style={{
                  color: '#000',
                  fontFamily: 'Poppins',
                  fontSize: '18px',
                  fontStyle: 'normal',
                  fontWeight: '400',
                  lineHeight: '28px',
                  margin: 0
                }}>
                  Date de stage mise à jour
                </p>
              </div>
            ) : (
              <>
                {/* STICKY 1a (MOBILE2): Form NOT validated, at form level - NO button */}
                {showStickyType1a && (
                  <div className="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-300 shadow-lg z-50" style={{ padding: '12px 16px' }}>
                    <h3 className="text-center mb-2" style={{
                      width: '100%',
                      maxWidth: '391px',
                      margin: '0 auto 8px',
                      color: '#000',
                      fontFamily: 'Poppins',
                      fontSize: '18px',
                      fontStyle: 'normal',
                      fontWeight: '500',
                      lineHeight: '28px'
                    }}>
                      Stage du {stage && formatDate(stage.date_start, stage.date_end)} - {totalPrice}€
                    </h3>
                    <div className="flex justify-between items-center w-full">
                      <button onClick={handleDetailsClick} style={{
                        color: '#345FB0',
                        fontFamily: 'Poppins',
                        fontSize: '14px',
                        fontStyle: 'normal',
                        fontWeight: '400',
                        lineHeight: '23px'
                      }}>Détails du stage</button>
                      <button onClick={handleChangeDateClick} style={{
                        color: '#345FB0',
                        fontFamily: 'Poppins',
                        fontSize: '14px',
                        fontStyle: 'normal',
                        fontWeight: '400',
                        lineHeight: '23px'
                      }}>Changer de date</button>
                    </div>
                  </div>
                )}

                {/* STICKY 1b (MOBILE1): Form NOT validated, form NOT visible - WITH button */}
                {showStickyType1b && (
              <div className="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-300 shadow-lg z-50" style={{ padding: '12px 16px' }}>
                <h3 className="text-center mb-2" style={{
                  width: '100%',
                  maxWidth: '391px',
                  margin: '0 auto 8px',
                  color: '#000',
                  fontFamily: 'Poppins',
                  fontSize: '18px',
                  fontStyle: 'normal',
                  fontWeight: '500',
                  lineHeight: '28px'
                }}>
                  Stage du {stage && formatDate(stage.date_start, stage.date_end)} - {totalPrice}€
                </h3>
                <div className="flex justify-between items-center w-full mb-2">
                  <button onClick={handleDetailsClick} style={{
                    color: '#345FB0',
                    fontFamily: 'Poppins',
                    fontSize: '14px',
                    fontStyle: 'normal',
                    fontWeight: '400',
                    lineHeight: '23px'
                  }}>Détails du stage</button>
                  <button onClick={handleChangeDateClick} style={{
                    color: '#345FB0',
                    fontFamily: 'Poppins',
                    fontSize: '14px',
                    fontStyle: 'normal',
                    fontWeight: '400',
                    lineHeight: '23px'
                  }}>Changer de date</button>
                </div>
                <div className="flex justify-center">
                  <button
                    onClick={() => {
                      // Scroll to the top of the form section
                      const formSection = document.getElementById('mobile-form-section')
                      if (formSection) {
                        formSection.scrollIntoView({ behavior: 'smooth', block: 'start' })
                      }
                    }}
                    className="text-white"
                    style={{
                      display: 'flex',
                      width: '255px',
                      padding: '7px 15px',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '20px',
                      borderRadius: '12px',
                      background: '#41A334'
                    }}
                  >
                    S&apos;inscrire
                  </button>
                </div>
              </div>
            )}

            {/* STICKY 2: CAS 2a1 - Form validated, on payment section, button not visible */}
            {showStickyType2a1 && (
              <div className="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-300 shadow-lg z-50" style={{ padding: '12px 16px' }}>
                <h3 className="text-center mb-2" style={{
                  width: '100%',
                  maxWidth: '391px',
                  margin: '0 auto 8px',
                  color: '#000',
                  fontFamily: 'Poppins',
                  fontSize: '18px',
                  fontStyle: 'normal',
                  fontWeight: '500',
                  lineHeight: '28px'
                }}>
                  Stage du {stage && formatDate(stage.date_start, stage.date_end)} - {totalPrice}€
                </h3>
                <div className="text-center">
                  <button onClick={handleDetailsClick} style={{
                    color: '#345FB0',
                    fontFamily: 'Poppins',
                    fontSize: '14px',
                    fontStyle: 'normal',
                    fontWeight: '400',
                    lineHeight: '23px'
                  }}>Détails du stage</button>
                </div>
              </div>
            )}

            {/* STICKY 3: CAS 2a3 - Form validated, NOT on payment section, button not visible */}
            {showStickyType2a3 && (
              <div className="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-300 shadow-lg z-50" style={{ padding: '12px 16px' }}>
                <h3 className="text-center mb-2" style={{
                  width: '100%',
                  maxWidth: '391px',
                  margin: '0 auto 8px',
                  color: '#000',
                  fontFamily: 'Poppins',
                  fontSize: '18px',
                  fontStyle: 'normal',
                  fontWeight: '500',
                  lineHeight: '28px'
                }}>
                  Stage du {stage && formatDate(stage.date_start, stage.date_end)} - {totalPrice}€
                </h3>
                <div className="text-center mb-2">
                  <button onClick={handleDetailsClick} style={{
                    color: '#345FB0',
                    fontFamily: 'Poppins',
                    fontSize: '14px',
                    fontStyle: 'normal',
                    fontWeight: '400',
                    lineHeight: '23px'
                  }}>Détails du stage</button>
                </div>
                <div className="flex justify-center">
                  <button
                    onClick={() => {
                      const paymentSection = document.getElementById('mobile-payment-section')
                      paymentSection?.scrollIntoView({ behavior: 'smooth', block: 'start' })
                    }}
                    className="text-white"
                    style={{
                      display: 'flex',
                      width: '255px',
                      padding: '7px 15px',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '20px',
                      borderRadius: '12px',
                      background: '#41A334'
                    }}
                  >
                    Aller au paiement
                  </button>
                </div>
              </div>
            )}

            {/* STICKY 4: CAS 2b1 - Payment fields filled, Payer button not visible */}
            {showStickyType2b1 && (
              <div className="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-300 shadow-lg z-50" style={{ padding: '12px 16px' }}>
                <h3 className="text-center mb-2" style={{
                  width: '100%',
                  maxWidth: '391px',
                  margin: '0 auto 8px',
                  color: '#000',
                  fontFamily: 'Poppins',
                  fontSize: '18px',
                  fontStyle: 'normal',
                  fontWeight: '500',
                  lineHeight: '28px'
                }}>
                  Stage du {stage && formatDate(stage.date_start, stage.date_end)} - {totalPrice}€
                </h3>
                <div className="text-center mb-2">
                  <button onClick={handleDetailsClick} style={{
                    color: '#345FB0',
                    fontFamily: 'Poppins',
                    fontSize: '14px',
                    fontStyle: 'normal',
                    fontWeight: '400',
                    lineHeight: '23px'
                  }}>Détails du stage</button>
                </div>
                <div className="flex justify-center">
                  <button
                    onClick={handleSubmit}
                    disabled={isFormExpanded}
                    className="text-white disabled:bg-gray-400"
                    style={{
                      display: 'flex',
                      width: '255px',
                      padding: '7px 15px',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '20px',
                      borderRadius: '12px',
                      background: isFormExpanded ? '#9CA3AF' : '#41A334'
                    }}
                  >
                    Payer {totalPrice}€ TTC
                  </button>
                </div>
              </div>
            )}
              </>
            )}
          </>
        )}

        {/* Mobile Date Change Modal - Bottom Sheet */}
        {isDatePopupOpen && (
          <div
            className="fixed inset-0 z-50 flex items-end md:hidden"
            style={{ backgroundColor: 'rgba(0, 0, 0, 0.5)', overflow: 'hidden', touchAction: 'none' }}
            onClick={() => setIsDatePopupOpen(false)}
          >
            <div
              onClick={(e) => e.stopPropagation()}
              onTouchMove={(e) => e.stopPropagation()}
              className="w-full bg-white rounded-t-3xl"
              style={{
                height: '85vh',
                maxHeight: '85vh',
                minHeight: '85vh',
                display: 'flex',
                flexDirection: 'column',
                padding: '24px 16px',
                position: 'relative',
                overflow: 'hidden',
                touchAction: 'pan-y'
              }}
            >
              {/* Close X button - small X without circle (same as fiche ville) */}
              <button
                onClick={() => setIsDatePopupOpen(false)}
                className="absolute z-10"
                style={{
                  top: '4px',
                  right: '16px',
                  width: '28px',
                  height: '28px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  background: 'none',
                  border: 'none',
                  cursor: 'pointer',
                  padding: 0
                }}
                aria-label="Fermer"
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                  <path d="M18 6L6 18M6 6L18 18" stroke="#666" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
              </button>

              {/* Handle bar */}
              <div className="flex justify-center mb-4">
                <div style={{ width: '100px', height: '4px', backgroundColor: '#B0B0B0', borderRadius: '2px' }} />
              </div>

              {/* Header Content */}
              <div style={{ flexShrink: 0, marginBottom: '16px' }}>
                {/* Title */}
                <h2 style={{
                  display: 'flex',
                  width: '243px',
                  height: '39px',
                  flexDirection: 'column',
                  justifyContent: 'center',
                  color: '#333',
                  textAlign: 'center',
                  fontFamily: 'Poppins',
                  fontSize: '18px',
                  fontStyle: 'normal',
                  fontWeight: '500',
                  lineHeight: '35px',
                  margin: '0 auto 8px auto'
                }}>
                  Les stages à {formatCityName(city)}
                </h2>

                {/* Subtitle */}
                <p style={{
                  display: 'flex',
                  width: '368px',
                  height: '56px',
                  flexDirection: 'column',
                  justifyContent: 'center',
                  color: '#4E4E4E',
                  textAlign: 'center',
                  fontFamily: 'Poppins',
                  fontSize: '15px',
                  fontStyle: 'italic',
                  fontWeight: '400',
                  lineHeight: '22px',
                  margin: '0 auto 16px auto'
                }}>
                  Choisissez une autre date pour votre stage. Les informations déjà saisies sont conservées
                </p>

                {/* Current Stage Badge */}
                {stage && (
                  <div style={{
                    display: 'flex',
                    width: '323px',
                    height: '63px',
                    padding: '0 5px',
                    justifyContent: 'center',
                    alignItems: 'center',
                    flexShrink: 0,
                    borderRadius: '10px',
                    background: '#F5F5F5',
                    margin: '0 auto 16px auto'
                  }}>
                    <p style={{
                      display: 'flex',
                      width: '335px',
                      height: '49px',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      flexShrink: 0,
                      color: '#000',
                      textAlign: 'center',
                      fontFamily: 'Poppins',
                      fontSize: '17px',
                      fontStyle: 'normal',
                      fontWeight: '500',
                      lineHeight: '28px',
                      margin: 0
                    }}>
                      Stage actuel : {formatDate(stage.date_start, stage.date_end)} - {stage.prix}€
                    </p>
                  </div>
                )}

                {/* Liste des stages label */}
                <p style={{
                  display: 'flex',
                  width: '136px',
                  height: '22px',
                  flexDirection: 'column',
                  justifyContent: 'center',
                  color: '#4E4E4E',
                  textAlign: 'center',
                  fontFamily: 'Poppins',
                  fontSize: '15px',
                  fontStyle: 'normal',
                  fontWeight: '400',
                  lineHeight: '22px',
                  textDecorationLine: 'underline',
                  textDecorationStyle: 'solid',
                  textDecorationSkipInk: 'auto',
                  textDecorationThickness: 'auto',
                  textUnderlineOffset: 'auto',
                  textUnderlinePosition: 'from-font',
                  margin: '24px auto 8px auto'
                }}>
                  Liste des stages :
                </p>
              </div>

              {/* Scrollable stage list */}
              <div className="flex-1 overflow-y-auto" style={{ marginBottom: '16px' }}>
                {loadingStages ? (
                  <p style={{ textAlign: 'center', color: '#666', fontFamily: 'Poppins', fontSize: '13px' }}>Chargement...</p>
                ) : (
                  availableStages.map((stageItem) => {
                    const isCurrentStage = stage && stageItem.id === stage.id
                    return (
                      <article
                        key={stageItem.id}
                        style={{
                          width: '369px',
                          height: '106px',
                          padding: '6px 0',
                          flexShrink: 0,
                          borderRadius: '10px',
                          border: isCurrentStage ? '1px solid #BC4747' : '1px solid #BBB',
                          background: isCurrentStage ? '#F2DDDD' : '#FFF',
                          boxShadow: '0 4px 10px 0 rgba(0, 0, 0, 0.15)',
                          position: 'relative',
                          marginBottom: '12px',
                          marginLeft: 'auto',
                          marginRight: 'auto'
                        }}
                      >
                        {/* Left: Date, Time, Pin + Location */}
                        <div style={{ display: 'flex', flexDirection: 'column', gap: '0', position: 'absolute', left: '12px', top: '12px' }}>
                          {/* Date */}
                          <p style={{
                            color: 'rgba(0, 0, 0, 0.89)',
                            fontFamily: 'Poppins',
                            fontSize: '15px',
                            fontStyle: 'normal',
                            fontWeight: '600',
                            lineHeight: '18px',
                            margin: 0,
                            marginBottom: '2px'
                          }}>
                            {formatDate(stageItem.date_start, stageItem.date_end)}
                          </p>
                          {/* Time */}
                          <p style={{
                            color: 'rgba(66, 66, 66, 0.86)',
                            fontFamily: 'Poppins',
                            fontSize: '13px',
                            fontStyle: 'normal',
                            fontWeight: '400',
                            lineHeight: '16px',
                            margin: 0,
                            marginBottom: '6px'
                          }}>
                            8h15-12h30 / 13h30-16h30
                          </p>

                          {/* Pin + City + Address */}
                          <div style={{ display: 'flex', alignItems: 'flex-start', gap: '8px' }}>
                          {/* Map Pin Icon */}
                          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" style={{ width: '20px', height: '20px', flexShrink: 0, marginTop: '2px' }}>
                            <g clipPath="url(#clip0_3_103)">
                              <path d="M17.5 8.33333C17.5 14.1667 10 19.1667 10 19.1667C10 19.1667 2.5 14.1667 2.5 8.33333C2.5 6.3442 3.29018 4.43655 4.6967 3.03003C6.10322 1.6235 8.01088 0.833328 10 0.833328C11.9891 0.833328 13.8968 1.6235 15.3033 3.03003C16.7098 4.43655 17.5 6.3442 17.5 8.33333Z" stroke="#808080" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                              <path d="M10 10.8333C11.3807 10.8333 12.5 9.71404 12.5 8.33333C12.5 6.95262 11.3807 5.83333 10 5.83333C8.61929 5.83333 7.5 6.95262 7.5 8.33333C7.5 9.71404 8.61929 10.8333 10 10.8333Z" stroke="#808080" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                            </g>
                            <defs>
                              <clipPath id="clip0_3_103">
                                <rect width="20" height="20" fill="white"/>
                              </clipPath>
                            </defs>
                          </svg>

                          <div style={{ display: 'flex', flexDirection: 'column', gap: '2px' }}>
                            {/* City name */}
                            <p style={{
                              color: 'rgba(0, 0, 0, 0.98)',
                              fontFamily: 'Poppins',
                              fontSize: '15px',
                              fontStyle: 'normal',
                              fontWeight: '400',
                              lineHeight: '18px',
                              margin: 0
                            }}>
                              {stageItem.site.ville}
                            </p>
                            {/* Address */}
                            <p style={{
                              color: 'rgba(6, 6, 6, 0.56)',
                              fontFamily: 'Poppins',
                              fontSize: '12px',
                              fontStyle: 'normal',
                              fontWeight: '400',
                              lineHeight: '15px',
                              margin: 0
                            }}>
                              {removeStreetNumber(stageItem.site.adresse)}
                            </p>
                          </div>
                          </div>
                        </div>

                        {/* Right side container */}
                        <div style={{
                          position: 'absolute',
                          right: '12px',
                          top: '50%',
                          transform: 'translateY(-50%)',
                          display: 'flex',
                          flexDirection: 'column',
                          alignItems: 'center',
                          gap: '8px'
                        }}>
                          {/* "Stage sélectionné" badge for current stage */}
                          {isCurrentStage && (
                            <div style={{
                              color: '#336FF0',
                              textAlign: 'center',
                              fontFamily: 'Poppins',
                              fontSize: '13px',
                              fontStyle: 'normal',
                              fontWeight: 400,
                              lineHeight: '17px'
                            }}>
                              Stage sélectionné
                            </div>
                          )}

                          {/* Price */}
                          <div style={{
                            color: 'rgba(6, 6, 6, 0.86)',
                            textAlign: 'center',
                            fontFamily: 'Poppins',
                            fontSize: '20px',
                            fontStyle: 'normal',
                            fontWeight: 400,
                            lineHeight: '35px'
                          }}>
                            {stageItem.prix}€
                          </div>

                          {/* Green Button */}
                          {!isCurrentStage && (
                            <button
                              onClick={() => handleStageSelect(stageItem)}
                              style={{
                                display: 'flex',
                                width: '109px',
                                padding: '7px 0',
                                justifyContent: 'center',
                                alignItems: 'center',
                                borderRadius: '12px',
                                background: '#41A334',
                                border: 'none',
                                cursor: 'pointer'
                              }}
                            >
                              <span style={{
                                width: '110px',
                                flexShrink: 0,
                                color: '#FFF',
                                textAlign: 'center',
                                fontFamily: 'Poppins',
                                fontSize: '15px',
                                fontStyle: 'normal',
                                fontWeight: '400',
                                lineHeight: '18px',
                                letterSpacing: '0.3px'
                              }}>
                                Choisir cette date
                              </span>
                            </button>
                          )}
                        </div>
                      </article>
                    )
                  })
                )}
              </div>

              {/* Fermer button */}
              <div className="pt-4 flex justify-center">
                <button
                  onClick={() => setIsDatePopupOpen(false)}
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
                    fontWeight: '300',
                    lineHeight: 'normal',
                    letterSpacing: '1.05px',
                    border: 'none',
                    cursor: 'pointer'
                  }}
                >
                  Fermer
                </button>
              </div>
            </div>
          </div>
        )}

        {/* Mobile Details Modal - Bottom Sheet */}
        {isDetailsModalOpen && (
          <div className="fixed inset-0 z-50 flex items-end md:hidden" style={{ backgroundColor: 'rgba(0, 0, 0, 0.5)' }} onClick={() => setIsDetailsModalOpen(false)}>
            <div
              onClick={(e) => e.stopPropagation()}
              className="w-full bg-white rounded-t-3xl relative"
              style={{ maxHeight: '85vh', display: 'flex', flexDirection: 'column', padding: '24px 16px' }}
            >
              {/* Handle bar */}
              <div className="flex justify-center mb-4">
                <div style={{ width: '100px', height: '4px', backgroundColor: '#B0B0B0', borderRadius: '2px' }} />
              </div>

              {/* Close button - X without circle, top right (same as fiche ville) */}
              <button
                onClick={() => setIsDetailsModalOpen(false)}
                className="absolute z-10"
                style={{
                  top: '4px',
                  right: '16px',
                  width: '28px',
                  height: '28px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  background: 'none',
                  border: 'none',
                  cursor: 'pointer',
                  padding: 0
                }}
                aria-label="Fermer"
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                  <path d="M18 6L6 18M6 6L18 18" stroke="#666" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
              </button>

              {/* Scrollable content */}
              <div className="flex-1 overflow-y-auto">
                {/* Date header with grey background */}
                <div className="text-center mb-4" style={{
                  padding: '12px 16px',
                  backgroundColor: '#F5F5F5',
                  borderRadius: '12px'
                }}>
                  <h3 style={{
                    fontFamily: 'Poppins',
                    fontSize: '18px',
                    fontWeight: '500',
                    lineHeight: '28px',
                    color: '#000'
                  }}>
                    Stage {stage && formatDate(stage.date_start, stage.date_end)}
                  </h3>
                </div>

                {/* Places disponibles */}
                <div className="text-center mb-2">
                  <p style={{
                    color: 'rgba(38, 126, 28, 0.95)',
                    textAlign: 'center',
                    fontFamily: 'Poppins',
                    fontSize: '15px',
                    fontStyle: 'normal',
                    fontWeight: '400',
                    lineHeight: '25px'
                  }}>
                    Places disponibles
                  </p>
                </div>

                {/* Price */}
                <div className="text-center mb-4">
                  <p style={{
                    color: 'rgba(0, 0, 0, 0.86)',
                    textAlign: 'center',
                    fontFamily: 'Poppins',
                    fontSize: '26px',
                    fontStyle: 'normal',
                    fontWeight: '400',
                    lineHeight: '35px'
                  }}>
                    {stage?.prix}€ TTC
                  </p>
                </div>

                {/* Separator line */}
                <div style={{ height: '1px', backgroundColor: '#E0E0E0', margin: '16px 0' }} />

                {/* Changer de date */}
                <div className="flex items-center gap-3 mb-4" style={{ marginLeft: '40px' }}>
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" style={{ flexShrink: 0 }}>
                    <g clipPath="url(#clip0_1_23)">
                      <path d="M13.3333 1.66667V5.00001M6.66667 1.66667V5.00001M2.5 8.33334H17.5M4.16667 3.33334H15.8333C16.7538 3.33334 17.5 4.07953 17.5 5.00001V16.6667C17.5 17.5871 16.7538 18.3333 15.8333 18.3333H4.16667C3.24619 18.3333 2.5 17.5871 2.5 16.6667V5.00001C2.5 4.07953 3.24619 3.33334 4.16667 3.33334Z" stroke="#595656" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round"/>
                    </g>
                    <defs>
                      <clipPath id="clip0_1_23">
                        <rect width="20" height="20" fill="white"/>
                      </clipPath>
                    </defs>
                  </svg>
                  <button onClick={handleChangeDateFromDetails} style={{
                    fontFamily: 'Poppins',
                    fontSize: '14px',
                    fontWeight: '400',
                    lineHeight: '22px',
                    color: '#345FB0'
                  }}>
                    Changer de date
                  </button>
                </div>

                {/* Location */}
                <div className="flex items-start gap-3 mb-4" style={{ marginLeft: '40px' }}>
                  <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none" style={{ flexShrink: 0 }}>
                    <path d="M21.875 10.4167C21.875 17.7083 12.5 23.9583 12.5 23.9583C12.5 23.9583 3.125 17.7083 3.125 10.4167C3.125 7.93027 4.11272 5.5457 5.87087 3.78755C7.62903 2.02939 10.0136 1.04167 12.5 1.04167C14.9864 1.04167 17.371 2.02939 19.1291 3.78755C20.8873 5.5457 21.875 7.93027 21.875 10.4167Z" stroke="#595656" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                    <path d="M12.5 13.5417C14.2259 13.5417 15.625 12.1426 15.625 10.4167C15.625 8.69078 14.2259 7.29167 12.5 7.29167C10.7741 7.29167 9.375 8.69078 9.375 10.4167C9.375 12.1426 10.7741 13.5417 12.5 13.5417Z" stroke="#595656" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                  </svg>
                  <p style={{
                    fontFamily: 'Poppins',
                    fontSize: '14px',
                    fontWeight: '400',
                    lineHeight: '22px',
                    color: '#4A4A4A'
                  }}>
                    {stage && removeStreetNumber(stage.site.adresse)}, {stage?.site.code_postal} {formatCityName(stage?.site.ville || '')}
                  </p>
                </div>

                {/* Schedule */}
                <div className="flex items-center gap-3 mb-4" style={{ marginLeft: '40px' }}>
                  <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 28 27" fill="none" style={{ flexShrink: 0 }}>
                    <g filter="url(#filter0_d_1_31)">
                      <path d="M12.5 6.25001V12.5L16.6667 14.5833M22.9167 12.5C22.9167 18.253 18.253 22.9167 12.5 22.9167C6.74703 22.9167 2.08333 18.253 2.08333 12.5C2.08333 6.74704 6.74703 2.08334 12.5 2.08334C18.253 2.08334 22.9167 6.74704 22.9167 12.5Z" stroke="#595656" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                    </g>
                    <defs>
                      <filter id="filter0_d_1_31" x="-4" y="0" width="33" height="33" filterUnits="userSpaceOnUse" colorInterpolationFilters="sRGB">
                        <feFlood floodOpacity="0" result="BackgroundImageFix"/>
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                        <feOffset dy="4"/>
                        <feGaussianBlur stdDeviation="2"/>
                        <feComposite in2="hardAlpha" operator="out"/>
                        <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/>
                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_1_31"/>
                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_1_31" result="shape"/>
                      </filter>
                    </defs>
                  </svg>
                  <p style={{
                    fontFamily: 'Poppins',
                    fontSize: '14px',
                    fontWeight: '400',
                    lineHeight: '22px',
                    color: '#4A4A4A'
                  }}>
                    08h15-12h30 et 13h30-16h30
                  </p>
                </div>

                {/* Agrément */}
                <div className="flex items-start gap-3 mb-6" style={{ marginLeft: '40px' }}>
                  <img
                    src="/flag.png"
                    alt="Drapeau français"
                    style={{
                      height: '16.636px',
                      alignSelf: 'stretch',
                      aspectRatio: '25.00/16.64',
                      flexShrink: 0,
                      borderRadius: '10px',
                      objectFit: 'cover'
                    }}
                  />
                  <p style={{
                    fontFamily: 'Poppins',
                    fontSize: '14px',
                    fontWeight: '400',
                    lineHeight: '22px',
                    color: '#4A4A4A'
                  }}>
                    Agrément n° 25 R1300600090064<br/>
                    par la Préfecture des Bouches-du-Rhône
                  </p>
                </div>

                {/* Benefits box */}
                <div style={{
                  border: '1px solid #D0D0D0',
                  borderRadius: '12px',
                  padding: '16px',
                  marginBottom: '24px'
                }}>
                  {[
                    'Stage officiel agréé Prfecture',
                    '+4 points en 48h',
                    'Report ou remboursement en cas d\'imprévu',
                    'Attestation de stage remise le 2ème jour',
                    'Paiement 100% sécurisé',
                    '98,7% de clients satisfaits'
                  ].map((benefit, index) => (
                    <div key={index} className="flex items-start gap-3 mb-3 last:mb-0">
                      <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none" style={{ flexShrink: 0 }}>
                        <path d="M9.375 11.4583L12.5 14.5833L22.9167 4.16667M21.875 12.5V19.7917C21.875 20.3442 21.6555 20.8741 21.2648 21.2648C20.8741 21.6555 20.3442 21.875 19.7917 21.875H5.20833C4.6558 21.875 4.12589 21.6555 3.73519 21.2648C3.34449 20.8741 3.125 20.3442 3.125 19.7917V5.20833C3.125 4.6558 3.34449 4.12589 3.73519 3.73519C4.12589 3.34449 4.6558 3.125 5.20833 3.125H16.6667" stroke="#C4A226" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                      </svg>
                      <p style={{
                        fontFamily: 'Poppins',
                        fontSize: '14px',
                        fontWeight: '400',
                        lineHeight: '22px',
                        color: '#000'
                      }}>
                        {benefit}
                      </p>
                    </div>
                  ))}
                </div>
              </div>

              {/* Fermer button */}
              <div className="pt-4 flex justify-center">
                <button
                  onClick={() => setIsDetailsModalOpen(false)}
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
                    fontWeight: '300',
                    lineHeight: 'normal',
                    letterSpacing: '1.05px'
                  }}
                >
                  Fermer
                </button>
              </div>
            </div>
          </div>
        )}
      </div>

      {/* DESKTOP VERSION - Hidden on mobile */}
      <div className="hidden md:block">
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
          {/* Step 1 */}
          <div className="flex flex-col items-center" style={{ position: 'relative' }}>
            <div className="flex items-center justify-center" style={{ position: 'relative', width: '33px', height: '31px', marginBottom: '12px' }}>
              <svg xmlns="http://www.w3.org/2000/svg" width="33" height="31" viewBox="0 0 33 31" fill="none" style={{ position: 'absolute' }}>
                <path d="M16.5 0.5C25.3665 0.5 32.5 7.24472 32.5 15.5C32.5 23.7553 25.3665 30.5 16.5 30.5C7.63354 30.5 0.5 23.7553 0.5 15.5C0.5 7.24472 7.63354 0.5 16.5 0.5Z" fill={currentStep === 1 ? 'white' : '#F5F5F5'} stroke={currentStep === 1 ? '#030303' : '#D9D9D9'}/>
              </svg>
              <span
                style={{
                  position: 'relative',
                  zIndex: 1,
                  color: currentStep === 1 ? '#000' : '#828282',
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
                color: currentStep === 1 ? '#000' : '#828282',
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

          {/* Step 2 */}
          <div className="flex flex-col items-center" style={{ position: 'relative' }}>
            <div className="flex items-center justify-center" style={{ position: 'relative', width: '33px', height: '31px', marginBottom: '12px' }}>
              <svg xmlns="http://www.w3.org/2000/svg" width="33" height="31" viewBox="0 0 33 31" fill="none" style={{ position: 'absolute' }}>
                <path d="M16.5 0.5C25.3665 0.5 32.5 7.24472 32.5 15.5C32.5 23.7553 25.3665 30.5 16.5 30.5C7.63354 30.5 0.5 23.7553 0.5 15.5C0.5 7.24472 7.63354 0.5 16.5 0.5Z" fill={currentStep === 2 ? 'white' : '#F5F5F5'} stroke={currentStep === 2 ? '#030303' : '#D9D9D9'}/>
              </svg>
              <span
                style={{
                  position: 'relative',
                  zIndex: 1,
                  color: currentStep === 2 ? '#000' : '#828282',
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
                color: currentStep === 2 ? '#000' : '#828282',
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

          {/* Step 3 */}
          <div className="flex flex-col items-center" style={{ position: 'relative' }}>
            <div className="flex items-center justify-center" style={{ position: 'relative', width: '33px', height: '31px', marginBottom: '12px' }}>
              <svg xmlns="http://www.w3.org/2000/svg" width="33" height="31" viewBox="0 0 33 31" fill="none" style={{ position: 'absolute' }}>
                <path d="M16.5 0.5C25.3665 0.5 32.5 7.24472 32.5 15.5C32.5 23.7553 25.3665 30.5 16.5 30.5C7.63354 30.5 0.5 23.7553 0.5 15.5C0.5 7.24472 7.63354 0.5 16.5 0.5Z" fill={currentStep === 3 ? 'white' : '#F5F5F5'} stroke={currentStep === 3 ? '#030303' : '#D9D9D9'}/>
              </svg>
              <span
                style={{
                  position: 'relative',
                  zIndex: 1,
                  color: currentStep === 3 ? '#000' : '#828282',
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
                color: currentStep === 3 ? '#000' : '#828282',
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
            {/* Back Link */}
            <a
              href={`/stages-recuperation-points/${city.toLowerCase()}`}
              style={{
                display: 'inline-flex',
                alignItems: 'center',
                gap: '8px',
                color: '#000',
                fontFamily: 'Poppins',
                fontSize: '14px',
                fontWeight: 400,
                textDecoration: 'none',
                marginBottom: '16px'
              }}
            >
              <svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7 1L2 6L7 11" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
              Retour aux stages à {city.charAt(0) + city.slice(1).toLowerCase().replace(/-/g, ' ')}
            </a>

            {/* Show form OR summary based on currentStep */}
            {currentStep === 1 ? (
              <>
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
                    id="desktop-civilite"
                    value={civilite}
                    onChange={(e) => {
                      setCivilite(e.target.value)
                      if (errors.civilite) setErrors(prev => ({ ...prev, civilite: undefined }))
                    }}
                    required
                    className="border"
                    style={{
                      display: 'flex',
                      width: '413px',
                      height: '35px',
                      padding: '7px 15px',
                      alignItems: 'center',
                      gap: '10px',
                      flexShrink: 0,
                      borderRadius: '8px',
                      border: errors.civilite ? '2px solid #DC2626' : '1px solid #000'
                    }}
                  >
                    <option value="" disabled>Sélectionner</option>
                    <option value="Monsieur">Monsieur</option>
                    <option value="Madame">Madame</option>
                  </select>
                  {errors.civilite && (
                    <p style={{ color: '#DC2626', fontSize: '13px', marginTop: '4px', fontFamily: 'Poppins' }}>
                      {errors.civilite}
                    </p>
                  )}
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
                    id="desktop-nom"
                    type="text"
                    value={nom}
                    onChange={(e) => {
                      setNom(e.target.value)
                      if (errors.nom) setErrors(prev => ({ ...prev, nom: undefined }))
                    }}
                    required
                    placeholder="Nom"
                    className="border"
                    style={{
                      display: 'flex',
                      width: '413px',
                      height: '35px',
                      padding: '7px 15px',
                      alignItems: 'center',
                      gap: '10px',
                      flexShrink: 0,
                      borderRadius: '8px',
                      border: errors.nom ? '2px solid #DC2626' : '1px solid #000'
                    }}
                  />
                  {errors.nom && (
                    <p style={{ color: '#DC2626', fontSize: '13px', marginTop: '4px', fontFamily: 'Poppins' }}>
                      {errors.nom}
                    </p>
                  )}
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
                    id="desktop-prenom"
                    type="text"
                    value={prenom}
                    onChange={(e) => {
                      setPrenom(e.target.value)
                      if (errors.prenom) setErrors(prev => ({ ...prev, prenom: undefined }))
                    }}
                    required
                    placeholder="Prénom"
                    className="border"
                    style={{
                      display: 'flex',
                      width: '413px',
                      height: '35px',
                      padding: '7px 15px',
                      alignItems: 'center',
                      gap: '10px',
                      flexShrink: 0,
                      borderRadius: '8px',
                      border: errors.prenom ? '2px solid #DC2626' : '1px solid #000'
                    }}
                  />
                  {errors.prenom && (
                    <p style={{ color: '#DC2626', fontSize: '13px', marginTop: '4px', fontFamily: 'Poppins' }}>
                      {errors.prenom}
                    </p>
                  )}
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
                    id="desktop-email"
                    type="email"
                    value={email}
                    onChange={(e) => {
                      setEmail(e.target.value)
                      if (errors.email) setErrors(prev => ({ ...prev, email: undefined }))
                    }}
                    required
                    placeholder="Email"
                    className="border"
                    style={{
                      display: 'flex',
                      width: '413px',
                      height: '35px',
                      padding: '7px 15px',
                      alignItems: 'center',
                      gap: '10px',
                      flexShrink: 0,
                      borderRadius: '8px',
                      border: errors.email ? '2px solid #DC2626' : '1px solid #000'
                    }}
                  />
                  {errors.email && (
                    <p style={{ color: '#DC2626', fontSize: '13px', marginTop: '4px', fontFamily: 'Poppins' }}>
                      {errors.email}
                    </p>
                  )}
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
                      id="desktop-telephone"
                      type="tel"
                      value={telephone}
                      onChange={(e) => {
                        setTelephone(e.target.value)
                        if (errors.telephone) setErrors(prev => ({ ...prev, telephone: undefined }))
                      }}
                      required
                      placeholder="Téléphone"
                      className="border"
                      style={{
                        display: 'flex',
                        width: '413px',
                        height: '35px',
                        padding: '7px 15px',
                        alignItems: 'center',
                        gap: '10px',
                        flexShrink: 0,
                        borderRadius: '8px',
                        border: errors.telephone ? '2px solid #DC2626' : '1px solid #000'
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
                  {errors.telephone && (
                    <p style={{ color: '#DC2626', fontSize: '13px', marginTop: '4px', fontFamily: 'Poppins' }}>
                      {errors.telephone}
                    </p>
                  )}
                </div>
              </div>

              {/* Garantie Sérénité */}
              <div className="mb-6" style={{ marginLeft: '100px', marginTop: '50px' }}>
                <div
                  style={{
                    display: 'flex',
                    width: '482px',
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

                  {/* Voir le détail link - accordion toggle */}
                  <div
                    className="flex items-center gap-1 cursor-pointer"
                    style={{ marginLeft: '10px' }}
                    onClick={() => setIsGarantieDetailOpen(!isGarantieDetailOpen)}
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      viewBox="0 0 16 16"
                      fill="none"
                      style={{
                        transform: isGarantieDetailOpen ? 'rotate(180deg)' : 'rotate(0deg)',
                        transition: 'transform 0.2s'
                      }}
                    >
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
                      {isGarantieDetailOpen ? 'Masquer le détail' : 'Voir le détail de la garantie'}
                    </span>
                  </div>

                  {/* Accordion content */}
                  {isGarantieDetailOpen && (
                    <div
                      style={{
                        marginTop: '10px',
                        marginLeft: '10px',
                        padding: '15px',
                        background: '#fff',
                        borderRadius: '8px',
                        width: 'calc(100% - 20px)'
                      }}
                    >
                      <p
                        style={{
                          color: '#333',
                          fontFamily: 'Poppins',
                          fontSize: '13px',
                          fontWeight: 400,
                          lineHeight: '20px',
                          marginBottom: '10px'
                        }}
                      >
                        La Garantie Sérénité vous permet de bénéficier des avantages suivants :
                      </p>
                      <ul
                        style={{
                          color: '#333',
                          fontFamily: 'Poppins',
                          fontSize: '13px',
                          fontWeight: 400,
                          lineHeight: '22px',
                          paddingLeft: '20px',
                          listStyleType: 'disc'
                        }}
                      >
                        <li>Annulation sans frais jusqu&apos;à 24h avant le stage</li>
                        <li>Report gratuit et illimité de votre stage</li>
                        <li>Remboursement intégral en cas d&apos;empêchement justifié</li>
                        <li>Assistance téléphonique prioritaire</li>
                      </ul>
                    </div>
                  )}
                </div>
              </div>

              {/* CGV */}
              <div className="mb-6" style={{ marginLeft: '50px' }}>
                <label className="flex items-start gap-3 cursor-pointer">
                  <input
                    id="desktop-cgv"
                    type="checkbox"
                    checked={cgvAccepted}
                    onChange={(e) => {
                      setCgvAccepted(e.target.checked)
                      if (errors.cgv) setErrors(prev => ({ ...prev, cgv: undefined }))
                    }}
                    required
                    className="mt-1"
                    style={{ accentColor: errors.cgv ? '#DC2626' : undefined }}
                  />
                  <span className="text-sm" style={{ color: errors.cgv ? '#DC2626' : '#333' }}>
                    J'accepte les{' '}
                    <a href="#" className="text-blue-600 underline">
                      conditions générales de vente
                    </a>
                  </span>
                </label>
                {errors.cgv && (
                  <p style={{ color: '#DC2626', fontSize: '13px', marginTop: '4px', marginLeft: '28px', fontFamily: 'Poppins' }}>
                    {errors.cgv}
                  </p>
                )}
              </div>

              {/* Submit Button */}
              <div style={{ marginLeft: '110px' }}>
                <button
                  type="button"
                  onClick={() => {
                    if (validateForm()) {
                      setCurrentStep(2)
                      // Scroll to payment section
                      document.getElementById('payment-section')?.scrollIntoView({ behavior: 'smooth' })
                    }
                  }}
                  className="text-white font-medium"
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
                    cursor: 'pointer'
                  }}
                >
                  Valider le formulaire et passer au paiement
                </button>
              </div>
            </form>
              </>
            ) : (
              /* Summary section when form is validated (currentStep === 2) */
              <div style={{ marginBottom: '28px' }}>
                <div className="flex items-center justify-between" style={{ marginBottom: '20px' }}>
                  <h2
                    style={{
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      color: '#000',
                      fontFamily: 'Poppins',
                      fontSize: '18px',
                      fontWeight: 500,
                      lineHeight: '25px'
                    }}
                  >
                    Vos coordonnées
                  </h2>
                  <button
                    type="button"
                    onClick={() => setCurrentStep(1)}
                    style={{
                      display: 'flex',
                      alignItems: 'center',
                      gap: '6px',
                      padding: '8px 16px',
                      borderRadius: '20px',
                      border: '1px solid #000',
                      background: '#fff',
                      cursor: 'pointer',
                      fontFamily: 'Poppins',
                      fontSize: '14px',
                      fontWeight: 500
                    }}
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                      <path d="M11.334 2.00001C11.5091 1.82491 11.7169 1.68602 11.9457 1.59126C12.1745 1.4965 12.4197 1.44775 12.6673 1.44775C12.915 1.44775 13.1601 1.4965 13.389 1.59126C13.6178 1.68602 13.8256 1.82491 14.0007 2.00001C14.1758 2.17511 14.3147 2.38291 14.4094 2.61175C14.5042 2.84058 14.5529 3.08576 14.5529 3.33335C14.5529 3.58094 14.5042 3.82612 14.4094 4.05495C14.3147 4.28378 14.1758 4.49159 14.0007 4.66668L5.00065 13.6667L1.33398 14.6667L2.33398 11L11.334 2.00001Z" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                    Modifier
                  </button>
                </div>

                {/* Summary Grid */}
                <div
                  style={{
                    display: 'grid',
                    gridTemplateColumns: '150px 1fr',
                    gap: '12px 20px',
                    padding: '20px',
                    background: '#F9F9F9',
                    borderRadius: '10px',
                    fontFamily: 'Poppins',
                    fontSize: '14px'
                  }}
                >
                  <span style={{ color: '#666' }}>Civilité</span>
                  <span style={{ color: '#000', fontWeight: 500 }}>{civilite}</span>

                  <span style={{ color: '#666' }}>Nom</span>
                  <span style={{ color: '#000', fontWeight: 500 }}>{nom}</span>

                  <span style={{ color: '#666' }}>Prénom</span>
                  <span style={{ color: '#000', fontWeight: 500 }}>{prenom}</span>

                  <span style={{ color: '#666' }}>Email</span>
                  <span style={{ color: '#000', fontWeight: 500 }}>{email}</span>

                  <span style={{ color: '#666' }}>Téléphone</span>
                  <span style={{ color: '#000', fontWeight: 500 }}>{telephone}</span>

                  {garantieSerenite && (
                    <>
                      <span style={{ color: '#666' }}>Garantie Sérénité</span>
                      <span style={{ color: '#41A334', fontWeight: 500 }}>Oui (+57€)</span>
                    </>
                  )}
                </div>
              </div>
            )}

            {/* Separator Line and Payment Section - Only shown when currentStep === 2 */}
            {currentStep === 2 && (
              <>
                <div style={{ marginTop: '60px', marginBottom: '60px' }}>
                  <div style={{ width: '672px', height: '1px', background: '#D9D9D9' }} />
                </div>

                {/* Payment Section - Étape 2/2 */}
                <div id="payment-section">
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
                        padding: '0 15px',
                        marginLeft: '-5px'
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
                        padding: '0 15px',
                        marginRight: '-5px'
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
            <div style={{ marginTop: '40px' }}>
              <div
                style={{
                  display: 'flex',
                  width: '692px',
                  minHeight: '402px',
                  padding: '30px 20px',
                  flexDirection: 'column',
                  alignItems: 'center',
                  gap: '0',
                  background: '#F6F6F6'
                }}
              >
                {/* Title: Questions Fréquentes - on one line */}
                <div style={{ display: 'flex', flexDirection: 'row', alignItems: 'center', gap: '8px', justifyContent: 'center' }}>
                  {/* Questions */}
                  <span
                    style={{
                      color: 'rgba(6, 6, 6, 0.86)',
                      textAlign: 'center',
                      WebkitTextStrokeWidth: '1px',
                      WebkitTextStrokeColor: '#000',
                      fontFamily: 'Poppins',
                      fontSize: '20px',
                      fontStyle: 'normal',
                      fontWeight: 250,
                      lineHeight: '35px'
                    }}
                  >
                    Questions
                  </span>

                  {/* Fréquentes */}
                  <span
                    style={{
                      color: 'rgba(6, 6, 6, 0.86)',
                      textAlign: 'center',
                      WebkitTextStrokeWidth: '1px',
                      WebkitTextStrokeColor: 'rgba(188, 71, 71, 0.73)',
                      fontFamily: 'Poppins',
                      fontSize: '20px',
                      fontStyle: 'normal',
                      fontWeight: 275,
                      lineHeight: '35px'
                    }}
                  >
                    Fréquentes
                  </span>
                </div>

                {/* Subtitle */}
                <div
                  style={{
                    color: '#000',
                    textAlign: 'center',
                    fontFamily: 'Poppins',
                    fontSize: '15px',
                    fontStyle: 'normal',
                    fontWeight: 400,
                    lineHeight: '20px',
                    marginTop: '15px',
                    marginBottom: '25px',
                    width: '100%'
                  }}
                >
                  Réponses aux questions que se posent le plus souvent les conducteurs
                </div>

                {/* Question 1 with arrow */}
                <div style={{ width: '100%' }}>
                  <div
                    onClick={() => setOpenFaqIndex(openFaqIndex === 0 ? null : 0)}
                    style={{
                      width: '100%',
                      display: 'flex',
                      justifyContent: 'space-between',
                      alignItems: 'center',
                      cursor: 'pointer',
                      gap: '10px'
                    }}
                  >
                    <div
                      style={{
                        flex: 1,
                        color: '#060606',
                        textAlign: 'left',
                        fontFamily: 'Poppins',
                        fontSize: '15px',
                        fontStyle: 'normal',
                        fontWeight: 400,
                        lineHeight: '35px',
                        whiteSpace: 'nowrap',
                        overflow: 'hidden',
                        textOverflow: 'ellipsis'
                      }}
                    >
                      A quel moment mes 4 points sont il crédités sur mon permis après un stage
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none" style={{ width: '25px', height: '25px', flexShrink: 0, transform: openFaqIndex === 0 ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform 0.2s' }}>
                      <path d="M6.25 9.375L12.5 15.625L18.75 9.375" stroke="#1E1E1E" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                  </div>
                  {openFaqIndex === 0 && (
                    <div style={{ padding: '15px 0', color: '#666', fontSize: '14px', lineHeight: '22px', textAlign: 'left' }}>
                      Réponse à la question - Texte placeholder pour la réponse détaillée concernant le crédit des points sur le permis de conduire.
                    </div>
                  )}
                </div>

                {/* Line 1 */}
                <div style={{ width: '100%', height: '1px', background: '#D0D0D0', marginTop: '15px', marginBottom: '15px' }} />

                {/* Question 2 with arrow */}
                <div style={{ width: '100%' }}>
                  <div
                    onClick={() => setOpenFaqIndex(openFaqIndex === 1 ? null : 1)}
                    style={{
                      width: '100%',
                      display: 'flex',
                      justifyContent: 'space-between',
                      alignItems: 'center',
                      cursor: 'pointer',
                      gap: '10px'
                    }}
                  >
                    <div
                      style={{
                        flex: 1,
                        color: '#060606',
                        textAlign: 'left',
                        fontFamily: 'Poppins',
                        fontSize: '15px',
                        fontStyle: 'normal',
                        fontWeight: 400,
                        lineHeight: '35px',
                        whiteSpace: 'nowrap',
                        overflow: 'hidden',
                        textOverflow: 'ellipsis'
                      }}
                    >
                      A quel moment mes 4 points sont il crédités sur mon permis après un stage
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none" style={{ width: '25px', height: '25px', flexShrink: 0, transform: openFaqIndex === 1 ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform 0.2s' }}>
                      <path d="M6.25 9.375L12.5 15.625L18.75 9.375" stroke="#1E1E1E" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                  </div>
                  {openFaqIndex === 1 && (
                    <div style={{ padding: '15px 0', color: '#666', fontSize: '14px', lineHeight: '22px', textAlign: 'left' }}>
                      Réponse à la question - Texte placeholder pour la réponse détaillée concernant le crédit des points sur le permis de conduire.
                    </div>
                  )}
                </div>

                {/* Line 2 */}
                <div style={{ width: '100%', height: '1px', background: '#D0D0D0', marginTop: '15px', marginBottom: '15px' }} />

                {/* Question 3 with arrow */}
                <div style={{ width: '100%' }}>
                  <div
                    onClick={() => setOpenFaqIndex(openFaqIndex === 2 ? null : 2)}
                    style={{
                      width: '100%',
                      display: 'flex',
                      justifyContent: 'space-between',
                      alignItems: 'center',
                      cursor: 'pointer',
                      gap: '10px'
                    }}
                  >
                    <div
                      style={{
                        flex: 1,
                        color: '#060606',
                        textAlign: 'left',
                        fontFamily: 'Poppins',
                        fontSize: '15px',
                        fontStyle: 'normal',
                        fontWeight: 400,
                        lineHeight: '35px',
                        whiteSpace: 'nowrap',
                        overflow: 'hidden',
                        textOverflow: 'ellipsis'
                      }}
                    >
                      A quel moment mes 4 points sont il crédités sur mon permis après un stage
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none" style={{ width: '25px', height: '25px', flexShrink: 0, transform: openFaqIndex === 2 ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform 0.2s' }}>
                      <path d="M6.25 9.375L12.5 15.625L18.75 9.375" stroke="#1E1E1E" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                  </div>
                  {openFaqIndex === 2 && (
                    <div style={{ padding: '15px 0', color: '#666', fontSize: '14px', lineHeight: '22px', textAlign: 'left' }}>
                      Réponse à la question - Texte placeholder pour la réponse détaillée concernant le crédit des points sur le permis de conduire.
                    </div>
                  )}
                </div>

                {/* Line 3 */}
                <div style={{ width: '100%', height: '1px', background: '#D0D0D0', marginTop: '15px', marginBottom: '50px' }} />

                {/* Afficher plus de questions */}
                <div
                  style={{
                    color: '#000',
                    fontFamily: 'Poppins',
                    fontSize: '15px',
                    fontStyle: 'normal',
                    fontWeight: 500,
                    lineHeight: 'normal',
                    letterSpacing: '1.05px',
                    textDecoration: 'underline',
                    cursor: 'pointer'
                  }}
                >
                  Afficher plus de questions
                </div>
              </div>
            </div>
              </>
            )}
          </div>
          {/* End Left Column */}

          {/* Right Column - Stage Info */}
          <div style={{ position: 'sticky', top: '24px', alignSelf: 'flex-start' }}>
            {/* Success notification - Date changed */}
            {showDateChangedNotification && (
              <div
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  gap: '10px',
                  marginBottom: '16px'
                }}
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="33" viewBox="0 0 30 33" fill="none" style={{ width: '30px', height: '33px', flexShrink: 0 }}>
                  <path d="M25 8.25L11.25 23.375L5 16.5" stroke="#30B049" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
                <p
                  style={{
                    width: '235px',
                    flexShrink: 0,
                    color: '#30B049',
                    textAlign: 'center',
                    fontFamily: 'Poppins',
                    fontSize: '16px',
                    fontStyle: 'normal',
                    fontWeight: 400,
                    lineHeight: '24px',
                    margin: 0
                  }}
                >
                  Date de stage mise à jour
                </p>
              </div>
            )}

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

                {/* Price and Places disponibles - Price first, then Places disponibles */}
                <div className="mb-3 flex flex-col items-center">
                  <div
                    style={{
                      display: 'flex',
                      height: '37px',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      flexShrink: 0,
                      marginBottom: '2px'
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
                      {stage.prix}€ TTC
                    </p>
                  </div>
                  <div
                    style={{
                      display: 'flex',
                      height: '30px',
                      flexDirection: 'column',
                      justifyContent: 'flex-start',
                      flexShrink: 0
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
                </div>

                {/* Grey horizontal line */}
                <div style={{ width: '100%', height: '1px', background: '#D9D9D9', marginBottom: '12px' }} />

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
                  <a
                    href="#"
                    onClick={handleChangeDateClick}
                    className="text-blue-600 hover:underline cursor-pointer"
                    style={{ fontFamily: 'Poppins', fontSize: '14px' }}
                  >
                    Changer de date
                  </a>
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

      {/* Date Change Popup Modal */}
      {isDatePopupOpen && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center"
          style={{ backgroundColor: 'rgba(0, 0, 0, 0.5)' }}
          onClick={() => setIsDatePopupOpen(false)}
        >
          <div
            onClick={(e) => e.stopPropagation()}
            style={{
              width: '620px',
              height: '693px',
              borderRadius: '20px',
              background: '#FFF',
              position: 'relative',
              display: 'flex',
              flexDirection: 'column'
            }}
          >
            {/* Close Button - small X */}
            <button
              onClick={() => setIsDatePopupOpen(false)}
              style={{
                position: 'absolute',
                top: '15px',
                right: '15px',
                width: '24px',
                height: '24px',
                background: 'transparent',
                border: 'none',
                cursor: 'pointer',
                padding: 0
              }}
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M18 6L6 18M6 6L18 18" stroke="#A1A1A1" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
            </button>

            {/* Sticky Header Content */}
            <div style={{ padding: '30px 35px 20px 35px', flexShrink: 0 }}>
              {/* Title */}
              <h2
                style={{
                  display: 'flex',
                  width: '377px',
                  height: '39px',
                  flexDirection: 'column',
                  justifyContent: 'center',
                  color: '#333',
                  textAlign: 'center',
                  fontFamily: 'Poppins',
                  fontSize: '22px',
                  fontWeight: 500,
                  lineHeight: '35px',
                  margin: '0 auto'
                }}
              >
                Les stages à {formatCityName(city)}
              </h2>

              {/* Subtitle */}
              <p
                style={{
                  display: 'flex',
                  width: '549px',
                  height: '56px',
                  flexDirection: 'column',
                  justifyContent: 'center',
                  color: '#4E4E4E',
                  textAlign: 'center',
                  fontFamily: 'Poppins',
                  fontSize: '15px',
                  fontStyle: 'italic',
                  fontWeight: 400,
                  lineHeight: '22px',
                  margin: '15px auto 20px auto'
                }}
              >
                Choisissez une autre date pour votre stage. Les informations déjà saisies sont conservées
              </p>

              {/* Current Stage Badge */}
              {stage && (
                <div
                  style={{
                    display: 'flex',
                    width: '389px',
                    height: '39px',
                    padding: '0 5px',
                    justifyContent: 'center',
                    alignItems: 'center',
                    flexShrink: 0,
                    borderRadius: '10px',
                    background: '#F5F5F5',
                    margin: '0 auto 20px auto'
                  }}
                >
                  <p
                    style={{
                      color: '#000',
                      textAlign: 'center',
                      fontFamily: 'Poppins',
                      fontSize: '16px',
                      fontWeight: 400,
                      lineHeight: '28px',
                      margin: 0
                    }}
                  >
                    Stage actuel : {formatDate(stage.date_start, stage.date_end)} - {stage.prix}€
                  </p>
                </div>
              )}

              {/* Liste des stages label */}
              <p
                style={{
                  display: 'flex',
                  width: '223px',
                  height: '22px',
                  flexDirection: 'column',
                  justifyContent: 'center',
                  flexShrink: 0,
                  color: '#4E4E4E',
                  fontFamily: 'Poppins',
                  fontSize: '15px',
                  fontWeight: 400,
                  lineHeight: '22px',
                  textDecoration: 'underline',
                  margin: '40px 0 6px -28px'
                }}
              >
                Liste des stages :
              </p>
            </div>

            {/* Scrollable Stage Cards Container */}
            <div
              style={{
                flex: 1,
                overflowY: 'auto',
                paddingLeft: '5px',
                paddingRight: '5px',
                position: 'relative',
                maskImage: 'linear-gradient(to bottom, black calc(100% - 140px), transparent 100%)',
                WebkitMaskImage: 'linear-gradient(to bottom, black calc(100% - 140px), transparent 100%)'
              }}
            >
              {loadingStages ? (
                <p style={{ textAlign: 'center', color: '#666', fontFamily: 'Poppins' }}>Chargement...</p>
              ) : (
                availableStages.map((stageItem) => {
                  const isCurrentStage = stage && stageItem.id === stage.id
                  return (
                    <article
                      key={stageItem.id}
                      className="flex w-full mb-3 rounded-[10px] border bg-white shadow-[0_4px_10px_0_rgba(0,0,0,0.15)] relative"
                      style={{
                        borderColor: isCurrentStage ? '#BC4747' : '#BBB',
                        backgroundColor: isCurrentStage ? '#F8EBE1' : 'white',
                        width: '589px',
                        height: '85px',
                        paddingTop: '7px',
                        paddingLeft: '7px',
                        paddingRight: '7px'
                      }}
                    >
                      {/* Left: Date and Time */}
                      <div className="flex flex-col justify-center gap-0 ml-3" style={{ width: '220px' }}>
                        {/* Top Left: Date */}
                        <p className="text-[rgba(0,0,0,0.89)] text-[15px] font-medium leading-[15px]" style={{ fontFamily: 'Poppins', whiteSpace: 'nowrap', marginBottom: '3px' }}>
                          {formatDate(stageItem.date_start, stageItem.date_end)}
                        </p>
                        {/* Bottom Left: Time */}
                        <p
                          style={{
                            color: 'rgba(66, 66, 66, 0.86)',
                            fontFamily: 'Poppins',
                            fontSize: '13px',
                            fontWeight: 400,
                            lineHeight: '20px',
                            marginTop: '8px',
                            whiteSpace: 'nowrap'
                          }}
                        >
                          8h15-12h30 / 13h30-16h30
                        </p>
                      </div>

                      {/* Center: Location Pin + City + Address - Vertically centered */}
                      <div className="flex items-center gap-2.5" style={{ position: 'absolute', left: '240px', top: '50%', transform: 'translateY(-50%)' }}>
                        <div className="flex w-[38px] h-[38px] p-[9px] justify-center items-center gap-2.5 flex-shrink-0 rounded-full bg-gray-200">
                          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" className="w-5 h-5 flex-shrink-0">
                            <g clipPath="url(#clip0_2180_399)">
                              <path d="M17.5 8.33337C17.5 14.1667 10 19.1667 10 19.1667C10 19.1667 2.5 14.1667 2.5 8.33337C2.5 6.34425 3.29018 4.4366 4.6967 3.03007C6.10322 1.62355 8.01088 0.833374 10 0.833374C11.9891 0.833374 13.8968 1.62355 15.3033 3.03007C16.7098 4.4366 17.5 6.34425 17.5 8.33337Z" stroke="#808080" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                              <path d="M10 10.8334C11.3807 10.8334 12.5 9.71409 12.5 8.33337C12.5 6.95266 11.3807 5.83337 10 5.83337C8.61929 5.83337 7.5 6.95266 7.5 8.33337C7.5 9.71409 8.61929 10.8334 10 10.8334Z" stroke="#808080" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                            </g>
                            <defs>
                              <clipPath id="clip0_2180_399">
                                <rect width="20" height="20" fill="white"/>
                              </clipPath>
                            </defs>
                          </svg>
                        </div>
                        <div className="flex flex-col justify-center gap-0">
                          <p className="flex-shrink-0 text-[rgba(0,0,0,0.98)] text-[15px] font-normal leading-[15px]" style={{ fontFamily: 'Poppins' }}>{stageItem.site.ville}</p>
                          <p className="flex-shrink-0 text-[rgba(6,6,6,0.56)] text-[12px] font-normal leading-[12px] mt-3" style={{ fontFamily: 'Poppins' }}>{removeStreetNumber(stageItem.site.adresse)}</p>
                        </div>
                      </div>

                      {/* Right side container */}
                      <div style={{ position: 'absolute', right: '12px', top: isCurrentStage ? '5px' : '10px', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: isCurrentStage ? '2px' : '5px' }}>
                        {/* "Stage sélectionné" badge for current stage */}
                        {isCurrentStage && (
                          <div
                            style={{
                              color: '#336FF0',
                              fontFamily: 'Poppins',
                              fontSize: '11px',
                              fontWeight: 400,
                              textAlign: 'center',
                              marginBottom: '0px'
                            }}
                          >
                            Stage sélectionné
                          </div>
                        )}

                        {/* Price */}
                        <div
                          style={{
                            color: 'rgba(6,6,6,0.86)',
                            fontFamily: 'Poppins',
                            fontSize: '20px',
                            fontWeight: 400,
                            lineHeight: '1',
                            textAlign: 'center'
                          }}
                        >
                          {stageItem.prix}€
                        </div>

                        {/* Green Button - Only show for non-selected stages */}
                        {!isCurrentStage && (
                          <button
                            onClick={() => handleStageSelect(stageItem)}
                            style={{
                              display: 'flex',
                              width: '125px',
                              height: '31px',
                              padding: '7px',
                              flexDirection: 'column',
                              justifyContent: 'center',
                              alignItems: 'center',
                              gap: '20px',
                              borderRadius: '12px',
                              background: '#41A334',
                              border: 'none',
                              color: 'white',
                              fontFamily: 'Poppins',
                              fontSize: '11px',
                              fontWeight: 400,
                              cursor: 'pointer',
                              transition: 'background 0.2s',
                              marginTop: '5px'
                            }}
                            onMouseEnter={(e) => {
                              e.currentTarget.style.background = '#389c2e'
                            }}
                            onMouseLeave={(e) => {
                              e.currentTarget.style.background = '#41A334'
                            }}
                          >
                            Choisir cette date
                          </button>
                        )}
                      </div>
                    </article>
                  )
                })
              )}
            </div>

            {/* Fermer Button at Bottom */}
            <div style={{ padding: '20px 35px 30px 35px', display: 'flex', justifyContent: 'center', flexShrink: 0 }}>
              <button
                onClick={() => setIsDatePopupOpen(false)}
                style={{
                  display: 'inline-flex',
                  height: '44px',
                  padding: '7px 15px',
                  justifyContent: 'center',
                  alignItems: 'center',
                  gap: '20px',
                  borderRadius: '12px',
                  background: '#E0E0E0',
                  border: 'none',
                  color: '#000',
                  fontFamily: 'Poppins',
                  fontSize: '15px',
                  fontWeight: 300,
                  lineHeight: 'normal',
                  letterSpacing: '1.05px',
                  cursor: 'pointer'
                }}
              >
                Fermer
              </button>
            </div>
          </div>
        </div>
      )}
      </div>
      {/* End Desktop Version */}
    </div>
  )
}
