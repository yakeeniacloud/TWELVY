'use client'

import { Suspense, useEffect, useRef, useState } from 'react'
import { useSearchParams } from 'next/navigation'
import Image from 'next/image'
import Link from 'next/link'

// /paiement/confirmation?id=<id>&status=<ok|refuse|annule>&t=<sig>&te=<exp>
// PSP's twelvy_validate.php redirects here. The `status` query is only a HINT — we NEVER
// render an authoritative success/refused state from it. The real state comes ONLY from a
// token-verified poll of /api/payment/status (which reads the DB row written server-side).

// Espace Client login (real prostagespermis.fr — migration target; Twelvy does not own the spaces).
const ESPACE_LOGIN = 'https://www.prostagespermis.fr/es/loginv2.php'

type StatusData = {
  status?: 'paye' | 'refuse' | 'en_attente'
  prenom?: string
  nom?: string
  email?: string
  mobile?: string
  identifiant?: string      // Espace Client login (= stagiaire id) — present only on 'paye'
  mot_de_passe?: string     // Espace Client password (derived) — present only on 'paye'
  facture_num?: number
  errorMessage?: string
  montant_total?: number    // base + Garantie Sérénité (what was actually paid)
  total_guarantee?: number
  stage?: {
    date_debut?: string
    date_fin?: string
    prix?: number
    horaires?: string
    lieu_nom?: string
    lieu_adresse?: string
    lieu_ville?: string
    lieu_cp?: string
  }
}

const POLL_INTERVAL_MS = 2500
const MAX_PENDING_MS = 90_000

const FR_DAYS = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam']
const FR_MONTHS = ['janv', 'févr', 'mars', 'avr', 'mai', 'juin', 'juil', 'août', 'sept', 'oct', 'nov', 'déc']

// Short FR date range with year, matching the funnel sticky: "Ven 5 et Sam 6 déc 2026".
function frDateShort(d1?: string, d2?: string): string {
  if (!d1 || d1 === '0000-00-00') return ''
  const a = new Date(d1 + 'T00:00:00')
  if (isNaN(a.getTime())) return ''
  const b = d2 && d2 !== '0000-00-00' ? new Date(d2 + 'T00:00:00') : new Date(a.getTime() + 86400000)
  return `${FR_DAYS[a.getDay()]} ${a.getDate()} et ${FR_DAYS[b.getDay()]} ${b.getDate()} ${FR_MONTHS[a.getMonth()]} ${a.getFullYear()}`
}

function removeStreetNumber(a?: string): string {
  return (a || '').replace(/^\s*\d+[\s,\-]+/, '').trim()
}
function titleCity(v?: string): string {
  return (v || '').toLowerCase().replace(/(^|[\s\-’'])(\p{L})/gu, (_m, sep, ch) => sep + ch.toUpperCase())
}
function formatLieu(s?: StatusData['stage']): string {
  if (!s) return ''
  const street = removeStreetNumber(s.lieu_adresse)
  const cityPart = [s.lieu_cp, titleCity(s.lieu_ville)].filter(Boolean).join(' ')
  return [street, cityPart].filter(Boolean).join(', ')
}

const PinIcon = (
  <svg width="18" height="18" viewBox="0 0 25 25" fill="none"><path d="M21.875 10.4167C21.875 17.7084 12.5 23.9584 12.5 23.9584C12.5 23.9584 3.125 17.7084 3.125 10.4167C3.125 7.93028 4.11272 5.54571 5.87087 3.78756C7.62903 2.02941 10.0136 1.04169 12.5 1.04169C14.9864 1.04169 17.371 2.02941 19.1291 3.78756C20.8873 5.54571 21.875 7.93028 21.875 10.4167Z" stroke="#595656" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" /><path d="M12.5 13.5417C14.2259 13.5417 15.625 12.1426 15.625 10.4167C15.625 8.6908 14.2259 7.29169 12.5 7.29169C10.7741 7.29169 9.375 8.6908 9.375 10.4167C9.375 12.1426 10.7741 13.5417 12.5 13.5417Z" stroke="#595656" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" /></svg>
)
const ClockIcon = (
  <svg width="17" height="17" viewBox="0 0 23 23" fill="none"><path d="M11.4167 5.16667V11.4167L15.5833 13.5M21.8333 11.4167C21.8333 17.1696 17.1696 21.8333 11.4167 21.8333C5.6637 21.8333 1 17.1696 1 11.4167C1 5.6637 5.6637 1 11.4167 1C17.1696 1 21.8333 5.6637 21.8333 11.4167Z" stroke="#595656" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" /></svg>
)
const MailIcon = (
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2" stroke="#111827" strokeWidth="1.6" /><path d="M4 7l8 6 8-6" stroke="#111827" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" /></svg>
)
const CheckIcon = (
  <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M4 12.5l5 5L20 6.5" stroke="#41A334" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" /></svg>
)

// Funnel progress, confirmation state: Coordonnées ✓ · Paiement ✓ · Confirmation (active).
function FunnelStepper() {
  const DoneCircle = (
    <span style={{ position: 'relative', width: 33, height: 31, display: 'inline-flex', alignItems: 'center', justifyContent: 'center', marginBottom: 10 }}>
      <svg width="33" height="31" viewBox="0 0 33 31" fill="none" style={{ position: 'absolute' }}><path d="M16.5 0.5C25.3665 0.5 32.5 7.24472 32.5 15.5C32.5 23.7553 25.3665 30.5 16.5 30.5C7.63354 30.5 0.5 23.7553 0.5 15.5C0.5 7.24472 7.63354 0.5 16.5 0.5Z" fill="#41A334" stroke="#41A334" /></svg>
      <svg width="17" height="17" viewBox="0 0 20 20" fill="none" style={{ position: 'absolute', zIndex: 2 }}><path d="M16.6667 5.5L8 14.1667L3.83337 10" stroke="#fff" strokeWidth="2.6" strokeLinecap="round" strokeLinejoin="round" /></svg>
    </span>
  )
  const line = <span style={{ flex: 1, height: 1, background: '#D9D9D9', marginBottom: 40, marginLeft: -1, marginRight: -1 }} />
  return (
    <div style={{ width: 500, maxWidth: '100%', margin: '0 auto', display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
      <span style={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>{DoneCircle}<p className="conf-step-lbl">Coordonnées</p></span>
      {line}
      <span style={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>{DoneCircle}<p className="conf-step-lbl"><span className="conf-lbl-d">Paiement sécurisé</span><span className="conf-lbl-m">Paiement</span></p></span>
      {line}
      <span style={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
        <span style={{ position: 'relative', width: 33, height: 31, display: 'inline-flex', alignItems: 'center', justifyContent: 'center', marginBottom: 10 }}>
          <svg width="33" height="31" viewBox="0 0 33 31" fill="none" style={{ position: 'absolute' }}><path d="M16.5 0.5C25.3665 0.5 32.5 7.24472 32.5 15.5C32.5 23.7553 25.3665 30.5 16.5 30.5C7.63354 30.5 0.5 23.7553 0.5 15.5C0.5 7.24472 7.63354 0.5 16.5 0.5Z" fill="white" stroke="#030303" /></svg>
          <span style={{ position: 'relative', zIndex: 1, fontSize: 20, color: '#000', lineHeight: '28px' }}>3</span>
        </span>
        <p className="conf-step-lbl conf-step-grey">Confirmation</p>
      </span>
    </div>
  )
}

const CONF_CSS = `
  .conf-page{ background:#fff; min-height:100vh; font-family:'Poppins',system-ui,Arial,sans-serif; color:#1f2937; }
  .conf-topbar{ display:flex; align-items:center; padding:12px 32px; border-bottom:1px solid #e5e7eb; }
  .conf-nav{ background:#333333; padding:9px 32px; display:flex; justify-content:flex-end; }
  .conf-nav a{ color:#fff; font-size:13px; text-decoration:none; }
  .conf-nav a:hover{ color:#e5e7eb; }
  .conf-stepper{ padding:44px 24px 8px; }
  .conf-step-lbl{ margin:0; font-size:15px; color:#000; white-space:nowrap; text-align:center; }
  .conf-step-grey{ color:#828282; }
  .conf-lbl-m{ display:none; }
  .conf-content{ max-width:680px; margin:0 auto; padding:8px 24px 70px; }
  .conf-title{ display:flex; align-items:center; justify-content:center; gap:12px; margin:18px 0 26px; }
  .conf-title h1{ margin:0; font-size:26px; font-weight:700; color:#111827; line-height:1.2; }
  .conf-msg{ text-align:center; margin:0 auto 10px; font-size:16px; line-height:1.5; color:#1f2937; }
  .conf-msg.spam{ display:flex; align-items:center; justify-content:center; gap:8px; margin-top:8px; }
  .conf-msg.spam b{ font-weight:600; }
  .conf-section{ margin-top:42px; }
  .conf-section h2{ margin:0; font-size:18px; font-weight:600; color:#111827; }
  .conf-rule{ height:1px; background:#D9D9D9; margin:12px 0 16px; }
  .conf-section .body{ padding-left:6px; }
  .conf-section .body p{ margin:0 0 6px; font-size:15px; line-height:1.55; color:#374151; }
  .conf-row{ display:flex; align-items:center; gap:8px; margin-bottom:8px; color:#374151; font-size:15px; }
  .conf-row svg{ flex-shrink:0; }
  .conf-strong{ font-weight:600; color:#111827; }
  .conf-link{ color:#2563EB; text-decoration:underline; }
  .conf-ids{ list-style:disc; padding-left:34px; margin:6px 0 0; }
  .conf-ids li{ font-size:15px; color:#374151; margin-bottom:4px; }
  .conf-btnwrap{ display:flex; justify-content:center; margin-top:46px; }
  .conf-btn{ display:inline-block; background:#41A334; color:#fff; font-size:17px; font-weight:500; padding:15px 42px; border-radius:30px; text-decoration:none; }
  .conf-btn:hover{ background:#388a2c; }
  @media (max-width:768px){
    .conf-topbar{ padding:10px 16px; }
    .conf-nav{ padding:8px 16px; }
    .conf-stepper{ padding:26px 12px 4px; }
    .conf-step-lbl{ font-size:11px; }
    .conf-lbl-d{ display:none; }
    .conf-lbl-m{ display:inline; }
    .conf-content{ padding:6px 18px 52px; }
    .conf-title h1{ font-size:21px; }
    .conf-msg{ font-size:15px; }
    .conf-section{ margin-top:34px; }
    .conf-section h2{ font-size:17px; }
    .conf-btn{ width:100%; max-width:340px; text-align:center; padding:14px 20px; }
  }
`

function ConfirmationInner() {
  const params = useSearchParams()
  const id = params.get('id') || ''
  const sig = params.get('t') || ''
  const exp = params.get('te') || ''
  // Cosmetic hint set by twelvy_payment.php when the booking was ALREADY paid on a prior visit.
  const already = params.get('already') === '1'

  const [data, setData] = useState<StatusData | null>(null)
  const [phase, setPhase] = useState<'loading' | 'paye' | 'refuse' | 'pending_timeout' | 'error'>('loading')
  const startedAt = useRef<number>(Date.now())
  const timer = useRef<ReturnType<typeof setTimeout> | null>(null)

  useEffect(() => {
    let cancelled = false

    // Without a verifiable token we CANNOT assert any outcome — show neutral state.
    if (!id || !sig || !exp) {
      setPhase('error')
      return
    }

    const poll = async () => {
      try {
        const qs = `id=${encodeURIComponent(id)}&t=${encodeURIComponent(sig)}&te=${encodeURIComponent(exp)}`
        const res = await fetch(`/api/payment/status?${qs}`, { cache: 'no-store' })
        const json = await res.json()
        if (cancelled) return

        if (!res.ok || !json?.success) {
          setPhase('error')
          return
        }
        const d: StatusData = json.data || {}
        setData(d)

        if (d.status === 'paye') { setPhase('paye'); return }
        if (d.status === 'refuse') { setPhase('refuse'); return }
        if (Date.now() - startedAt.current > MAX_PENDING_MS) { setPhase('pending_timeout'); return }
        timer.current = setTimeout(poll, POLL_INTERVAL_MS)
      } catch {
        if (cancelled) return
        if (Date.now() - startedAt.current > MAX_PENDING_MS) { setPhase('pending_timeout'); return }
        timer.current = setTimeout(poll, POLL_INTERVAL_MS)
      }
    }

    poll()
    return () => { cancelled = true; if (timer.current) clearTimeout(timer.current) }
  }, [id, sig, exp])

  // ---- SUCCESS: full confirmation design (desktop + mobile via responsive CSS) ----
  if (phase === 'paye') {
    const s = data?.stage
    const montant = data?.montant_total ?? s?.prix
    const fullName = [data?.prenom, data?.nom].filter(Boolean).join(' ').trim()
    const lieu = formatLieu(s)
    return (
      <main className="conf-page">
        <style>{CONF_CSS}</style>

        <header>
          <div className="conf-topbar">
            <Link href="/"><Image src="/prostagespermis-logo.png" alt="ProStagesPermis" width={130} height={32} priority style={{ height: 32, width: 'auto' }} /></Link>
          </div>
          <nav className="conf-nav"><Link href="/aide-et-contact">Aide et contact</Link></nav>
        </header>

        <div className="conf-stepper"><FunnelStepper /></div>

        <div className="conf-content">
          <div className="conf-title">
            <h1>Inscription confirmée</h1>
            {CheckIcon}
          </div>

          <p className="conf-msg">
            {already ? 'Votre inscription à ce stage est déjà confirmée — vous n’avez pas été débité de nouveau.' : 'Votre paiement a bien été accepté.'}
          </p>
          <p className="conf-msg">Un email de confirmation vient de vous être envoyé.</p>
          <p className="conf-msg spam">{MailIcon}<span><b>Vous ne retrouvez pas l’email ?</b> Pensez à vérifier vos courriers indésirables.</span></p>

          <section className="conf-section">
            <h2>Stage sélectionné</h2>
            <div className="conf-rule" />
            <div className="body">
              {s?.date_debut ? <p>Date: {frDateShort(s.date_debut, s.date_fin)}</p> : null}
              {montant ? <p className="conf-strong">Montant payé: {montant}€ TTC</p> : null}
              {lieu ? <div className="conf-row">{PinIcon}<span>{lieu}</span></div> : null}
              {s?.horaires ? <div className="conf-row">{ClockIcon}<span>{s.horaires}</span></div> : null}
            </div>
          </section>

          <section className="conf-section">
            <h2>Vos coordonnées</h2>
            <div className="conf-rule" />
            <div className="body">
              {fullName ? <p>{fullName}</p> : null}
              {data?.email ? <p>Mail: {data.email}</p> : null}
              {data?.mobile ? <p>Tel: {data.mobile}</p> : null}
            </div>
          </section>

          <section className="conf-section">
            <h2>Vos prochaines étapes</h2>
            <div className="conf-rule" />
            <div className="body">
              <p>1- Connectez-vous à votre <a className="conf-link" href={ESPACE_LOGIN}>Espace Client</a></p>
              <p>2- Compléter votre dossier</p>
              <p>3- Retrouver toutes les informations pour préparer votre RDV</p>
              {data?.identifiant && data?.mot_de_passe ? (
                <>
                  <p style={{ marginTop: 14 }}>Voici les identifiants pour vous connecter&nbsp;:</p>
                  <ul className="conf-ids">
                    <li>Identifiant&nbsp;: {data.identifiant}</li>
                    <li>Mot de passe&nbsp;: {data.mot_de_passe}</li>
                  </ul>
                </>
              ) : null}
            </div>
          </section>

          <div className="conf-btnwrap">
            <a className="conf-btn" href={ESPACE_LOGIN}>Accéder à mon Espace</a>
          </div>
        </div>
      </main>
    )
  }

  // ---- Non-success states keep the minimal centered card ----
  return (
    <main className="min-h-screen bg-gray-50 flex flex-col items-center px-4 py-12">
      <div className="w-full max-w-xl bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
        {phase === 'loading' && (
          <>
            <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600" />
            <h1 className="mt-5 text-xl font-semibold text-gray-800">Vérification de votre paiement…</h1>
            <p className="mt-2 text-sm text-gray-500">Merci de patienter, ne fermez pas cette page.</p>
          </>
        )}

        {phase === 'refuse' && (
          <>
            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
              <svg className="h-9 w-9 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </div>
            <h1 className="mt-5 text-2xl font-bold text-gray-900">Paiement non abouti</h1>
            <p className="mt-3 text-gray-600">
              {data?.errorMessage || "Votre paiement n'a pas pu être finalisé. Aucun montant n'a été débité."}
            </p>
            <p className="mt-2 text-sm text-gray-500">Vous pouvez réessayer ou utiliser une autre carte.</p>
            <Link href="/" className="mt-6 inline-block rounded-full bg-blue-600 px-6 py-3 text-white font-medium hover:bg-blue-700">
              Reprendre ma réservation
            </Link>
          </>
        )}

        {phase === 'pending_timeout' && (
          <>
            <div className="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-amber-500" />
            <h1 className="mt-5 text-xl font-semibold text-gray-800">Paiement en cours de traitement</h1>
            <p className="mt-3 text-gray-600">
              La confirmation prend un peu plus de temps que prévu. Vous recevrez un email dès qu&apos;elle est validée.
              Inutile de relancer le paiement.
            </p>
            <Link href="/" className="mt-6 inline-block rounded-full bg-gray-700 px-6 py-3 text-white font-medium hover:bg-gray-800">
              Retour à l&apos;accueil
            </Link>
          </>
        )}

        {phase === 'error' && (
          <>
            <h1 className="mt-2 text-xl font-semibold text-gray-800">Confirmation indisponible</h1>
            <p className="mt-3 text-gray-600">
              Nous ne pouvons pas afficher le statut de votre paiement ici. Si votre carte a été débitée,
              vous recevrez votre convocation par email.
            </p>
            <Link href="/" className="mt-6 inline-block rounded-full bg-gray-700 px-6 py-3 text-white font-medium hover:bg-gray-800">
              Retour à l&apos;accueil
            </Link>
          </>
        )}
      </div>
    </main>
  )
}

export default function ConfirmationPage() {
  return (
    <Suspense fallback={<main className="min-h-screen bg-gray-50" />}>
      <ConfirmationInner />
    </Suspense>
  )
}
