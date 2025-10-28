import { NextResponse } from 'next/server'

export async function GET() {
  try {
    console.log('📨 Testing GET request to OVH phpinfo.php...')

    const ovhApiUrl = process.env.OVH_API_URL || 'https://api.twelvy.net'

    const response = await fetch(`${ovhApiUrl}/phpinfo.php`, {
      method: 'GET',
      headers: {
        'Accept': 'text/html',
      },
    })

    const text = await response.text()

    // Check if we got HTML (phpinfo) or raw PHP code
    if (text.includes('PHP Version') || text.includes('phpinfo')) {
      return NextResponse.json(
        { ok: true, message: 'PHP is executing correctly on OVH!', preview: text.substring(0, 200) },
        { status: 200 }
      )
    } else if (text.includes('<?php')) {
      return NextResponse.json(
        { ok: false, message: 'Got raw PHP code - PHP not executing properly' },
        { status: 500 }
      )
    } else {
      return NextResponse.json(
        { ok: true, message: 'Got response from OVH', preview: text.substring(0, 200) },
        { status: 200 }
      )
    }
  } catch (error) {
    console.error('❌ Error:', error)
    return NextResponse.json(
      {
        ok: false,
        error: 'Failed to reach OVH API',
        message: error instanceof Error ? error.message : 'Unknown error'
      },
      { status: 500 }
    )
  }
}
