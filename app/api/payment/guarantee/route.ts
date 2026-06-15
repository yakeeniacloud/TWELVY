import { NextResponse } from 'next/server'

// GET /api/payment/guarantee → bridge.php get of PSP's Garantie Sérénité config (active + price).
//
// This is the SAME source bridge.php uses to compute the CHARGE (cancel_guarantee_params, admin-managed
// in simpligestion). The inscription form reads it for display, so the displayed +X€ and the debited
// amount can never disagree. The value is not sensitive (it's the public option price); the X-Api-Key
// stays server-side. Falls back to a sane default so the form still works if the bridge is unreachable.

const BRIDGE_URL = process.env.BRIDGE_URL || 'https://api.twelvy.net/bridge.php'
const BRIDGE_API_KEY = process.env.BRIDGE_API_KEY || ''

const FALLBACK = { active: true, price: 57 } // matches the current config; charge is always live-config

export async function GET() {
  if (!BRIDGE_API_KEY) {
    return NextResponse.json({ success: true, data: FALLBACK }, { headers: { 'Cache-Control': 'no-store' } })
  }
  try {
    const res = await fetch(`${BRIDGE_URL}?action=guarantee_params`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Api-Key': BRIDGE_API_KEY },
      next: { revalidate: 60 }, // price changes rarely
    })
    const data = await res.json()
    if (!res.ok || !data?.success) {
      return NextResponse.json({ success: true, data: FALLBACK })
    }
    return NextResponse.json({
      success: true,
      data: {
        active: !!data.data?.active,
        price: Number(data.data?.price) || 0,
      },
    })
  } catch {
    return NextResponse.json({ success: true, data: FALLBACK })
  }
}
