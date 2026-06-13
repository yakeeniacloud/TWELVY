import { NextRequest, NextResponse } from 'next/server'
import { buildPaymentRedirectUrl } from '@/lib/paymentToken'

// Server-side proxy: Twelvy frontend → Vercel API route → bridge.php on OVH
// Why proxy: keeps the BRIDGE_API_KEY off the customer's browser (server-only env var).
// Bridge endpoint: ?action=create_or_update_prospect — INSERT/UPDATE the stagiaire row.
//
// Option B (PSP redirect): after the prospect is created we also return a SIGNED
// `redirect_url` to the PSP-copie payment page. The HMAC is computed here, server-side,
// with the shared secret (BRIDGE_API_KEY == OVH BRIDGE_SECRET_TOKEN). Folding it into
// this response — instead of a standalone "sign any id" endpoint — means the browser
// can only ever get a URL for the stagiaire it just created/looked up by email+stage.

const BRIDGE_URL = process.env.BRIDGE_URL || 'https://api.twelvy.net/bridge.php'
const BRIDGE_API_KEY = process.env.BRIDGE_API_KEY || ''
const PSP_COPIE_URL = process.env.PSP_COPIE_URL || 'https://psp-copie.twelvy.net'

export async function POST(request: NextRequest) {
  if (!BRIDGE_API_KEY) {
    console.error('BRIDGE_API_KEY env var not set')
    return NextResponse.json(
      { success: false, error: { code: 'config_error', message: 'Bridge API key not configured' } },
      { status: 500 }
    )
  }

  try {
    const body = await request.json()

    const response = await fetch(`${BRIDGE_URL}?action=create_or_update_prospect`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Api-Key': BRIDGE_API_KEY,
      },
      body: JSON.stringify(body),
    })

    const data = await response.json()

    // Build a FRESH response — never forward the upstream bridge object verbatim
    // (so a spoofed/compromised bridge response can't smuggle its own redirect_url
    // and turn window.location into an open redirect). We only ever emit a
    // redirect_url WE signed, for the integer stagiaire_id the bridge returned.
    if (!response.ok || !data?.success) {
      return NextResponse.json(
        { success: false, error: data?.error || { code: 'bridge_error', message: 'Erreur' } },
        { status: response.status }
      )
    }
    const stagiaireId = Number(data?.data?.stagiaire_id)
    if (!Number.isInteger(stagiaireId) || stagiaireId <= 0) {
      return NextResponse.json(
        { success: false, error: { code: 'bad_stagiaire_id', message: 'Réponse invalide' } },
        { status: 502 }
      )
    }
    return NextResponse.json(
      {
        success: true,
        data: {
          stagiaire_id: stagiaireId,
          booking_reference: data?.data?.booking_reference,
          redirect_url: buildPaymentRedirectUrl(PSP_COPIE_URL, stagiaireId, BRIDGE_API_KEY),
        },
      },
      { status: 200 }
    )
  } catch (error) {
    console.error('create-prospect proxy error:', error)
    return NextResponse.json(
      { success: false, error: { code: 'proxy_error', message: 'Bridge unreachable' } },
      { status: 502 }
    )
  }
}
