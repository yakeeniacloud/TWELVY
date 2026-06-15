'use client'

import { Suspense, useEffect, useRef, useState } from 'react'
import { useSearchParams } from 'next/navigation'
import Link from 'next/link'

// /paiement/confirmation?id=<id>&status=<ok|refuse|annule>&t=<sig>&te=<exp>
// PSP's twelvy_validate.php redirects here. The `status` query is only a HINT — we NEVER
// render an authoritative success/refused state from it. The real state comes ONLY from a
// token-verified poll of /api/payment/status (which reads the DB row written server-side).

type StatusData = {
  status?: 'paye' | 'refuse' | 'en_attente'
  prenom?: string
  facture_num?: number
  errorMessage?: string
  montant_total?: number   // base + Garantie Sérénité (what was actually paid)
  total_guarantee?: number
  stage?: {
    date_debut?: string
    date_fin?: string
    prix?: number
    lieu_nom?: string
    lieu_ville?: string
    lieu_cp?: string
  }
}

const POLL_INTERVAL_MS = 2500
const MAX_PENDING_MS = 90_000

function frDate(d?: string): string {
  if (!d || d === '0000-00-00') return ''
  const dt = new Date(d + 'T00:00:00')
  if (isNaN(dt.getTime())) return d
  return dt.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
}

function ConfirmationInner() {
  const params = useSearchParams()
  const id = params.get('id') || ''
  const sig = params.get('t') || ''
  const exp = params.get('te') || ''
  // Cosmetic hint set by twelvy_payment.php when the booking was ALREADY paid on a prior visit
  // (the already-paid double-charge guard) — so we frame it as "déjà réservé" instead of a fresh
  // "merci, paiement confirmé". The real paid/refuse state still comes only from the verified poll.
  const already = params.get('already') === '1'

  const [data, setData] = useState<StatusData | null>(null)
  const [phase, setPhase] = useState<'loading' | 'paye' | 'refuse' | 'pending_timeout' | 'error'>('loading')
  const startedAt = useRef<number>(Date.now())
  const timer = useRef<ReturnType<typeof setTimeout> | null>(null)

  useEffect(() => {
    let cancelled = false

    // Without a verifiable token we CANNOT assert any outcome — show neutral state.
    // (Never trust the unsigned ?status= hint to render a "paid" screen.)
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

  const stage = data?.stage
  const prenom = (data?.prenom || '').trim()
  const ref = data?.facture_num

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

        {phase === 'paye' && (
          <>
            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
              <svg className="h-9 w-9 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h1 className="mt-5 text-2xl font-bold text-gray-900">
              {already ? 'Vous avez déjà réservé ce stage' : `Merci${prenom ? ` ${prenom}` : ''} !`}
            </h1>
            <p className="mt-2 text-gray-600">
              {already
                ? 'Votre inscription à ce stage est déjà confirmée — vous n’avez pas été débité de nouveau.'
                : 'Votre paiement a bien été confirmé.'}
            </p>
            {ref ? (
              <p className="mt-1 text-sm text-gray-500">
                Référence de réservation : <span className="font-semibold text-gray-700">{ref}</span>
              </p>
            ) : null}
            {stage && (stage.date_debut || stage.lieu_ville) && (
              <div className="mt-6 rounded-xl bg-gray-50 p-5 text-left text-sm text-gray-700">
                {stage.date_debut && (
                  <p><span className="font-medium">Stage : </span>{frDate(stage.date_debut)}
                    {stage.date_fin && stage.date_fin !== stage.date_debut ? ` au ${frDate(stage.date_fin)}` : ''}</p>
                )}
                {(stage.lieu_nom || stage.lieu_ville) && (
                  <p className="mt-1"><span className="font-medium">Lieu : </span>
                    {[stage.lieu_nom, stage.lieu_cp, stage.lieu_ville].filter(Boolean).join(', ')}</p>
                )}
                {(data?.montant_total || stage.prix) ? (
                  <p className="mt-1"><span className="font-medium">Montant : </span>{data?.montant_total ?? stage.prix}€ TTC
                    {data?.total_guarantee && data.total_guarantee > 0 ? (
                      <span className="text-gray-500"> (dont Garantie Sérénité : {data.total_guarantee}€)</span>
                    ) : null}
                  </p>
                ) : null}
              </div>
            )}
            <p className="mt-6 text-sm text-gray-600">
              Vous recevez par email votre convocation et les détails pratiques de votre stage.
            </p>
            <Link href="/" className="mt-6 inline-block rounded-full bg-green-600 px-6 py-3 text-white font-medium hover:bg-green-700">
              Retour à l&apos;accueil
            </Link>
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
