import { NextRequest, NextResponse } from 'next/server'

// Server-side proxy: Twelvy frontend → Vercel API route → bridge.php on OVH
// Bridge endpoint: ?action=prepare_payment — generates num_suivi + Up2Pay HMAC-signed
// payment fields, returns paymentUrl + paymentFields for iframe POST.

const BRIDGE_URL = process.env.BRIDGE_URL || 'https://api.twelvy.net/bridge.php'
const BRIDGE_API_KEY = process.env.BRIDGE_API_KEY || ''

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

    const response = await fetch(`${BRIDGE_URL}?action=prepare_payment`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Api-Key': BRIDGE_API_KEY,
      },
      body: JSON.stringify(body),
    })

    const data = await response.json()
    return NextResponse.json(data, { status: response.status })
  } catch (error) {
    console.error('prepare_payment proxy error:', error)
    return NextResponse.json(
      { success: false, error: { code: 'proxy_error', message: 'Bridge unreachable' } },
      { status: 502 }
    )
  }
}
