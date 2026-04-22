import { NextRequest, NextResponse } from 'next/server'

// Server-side proxy: Twelvy frontend → Vercel API route → bridge.php on OVH
// Why proxy: keeps the BRIDGE_API_KEY off the customer's browser (server-only env var).
// Bridge endpoint: ?action=create_or_update_prospect — INSERT/UPDATE the stagiaire row.

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

    const response = await fetch(`${BRIDGE_URL}?action=create_or_update_prospect`, {
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
    console.error('create-prospect proxy error:', error)
    return NextResponse.json(
      { success: false, error: { code: 'proxy_error', message: 'Bridge unreachable' } },
      { status: 502 }
    )
  }
}
