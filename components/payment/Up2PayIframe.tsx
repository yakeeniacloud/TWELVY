'use client'

import { useEffect, useRef } from 'react'

export type PaymentData = {
  stagiaire_id: number
  paymentUrl: string
  paymentFields: Record<string, string>
  environment: 'test' | 'prod'
  reference: string
  amount_eur: number
}

type Props = {
  paymentData: PaymentData
  /** iframe height in px — defaults to 620 (works for both 3DS and non-3DS Paybox forms) */
  height?: number
}

/**
 * Up2PayIframe — embeds Verifone's MYframepagepaiement_ip.cgi inside an <iframe>
 * by auto-submitting a hidden POST form into it.
 *
 * Why a form (instead of just iframe src=URL?fields...): Up2Pay's MYchoix family
 * accepts both GET and POST, but POST is the documented + recommended path because
 * it doesn't expose the HMAC and customer email in browser history / referrer logs.
 */
export default function Up2PayIframe({ paymentData, height = 620 }: Props) {
  const formRef = useRef<HTMLFormElement>(null)
  // Track which paymentData reference we already submitted. Without this guard,
  // any re-render that re-runs the effect would re-submit the same payload, and
  // Paybox returns "Session invalide ou obsolète" on the second submission.
  const submittedForRef = useRef<PaymentData | null>(null)
  const iframeName = 'up2pay_iframe'

  useEffect(() => {
    if (!formRef.current) return
    // Skip if we already submitted THIS exact paymentData object (idempotent)
    if (submittedForRef.current === paymentData) return
    submittedForRef.current = paymentData
    formRef.current.submit()
  }, [paymentData])

  return (
    <div className="w-full">
      <iframe
        name={iframeName}
        title="Paiement sécurisé Up2Pay - Crédit Agricole"
        style={{ width: '100%', height: `${height}px`, border: 'none', display: 'block' }}
      />
      <form
        ref={formRef}
        method="POST"
        action={paymentData.paymentUrl}
        target={iframeName}
        style={{ display: 'none' }}
        aria-hidden="true"
      >
        {Object.entries(paymentData.paymentFields).map(([key, value]) => (
          <input key={key} type="hidden" name={key} value={value} />
        ))}
      </form>
      <p className="text-center text-xs text-gray-500 mt-2">
        Paiement sécurisé hébergé par Crédit Agricole — Up2Pay e-Transactions
        {paymentData.environment === 'test' && (
          <span className="ml-2 px-2 py-0.5 bg-amber-100 text-amber-800 rounded text-xs font-medium">
            MODE TEST
          </span>
        )}
      </p>
    </div>
  )
}
