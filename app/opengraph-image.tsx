import { ImageResponse } from 'next/og'

export const runtime = 'edge'
export const alt = 'Twelvy - Stage de récupération de points'
export const size = { width: 1200, height: 630 }
export const contentType = 'image/png'

export default async function Image() {
  return new ImageResponse(
    (
      <div
        style={{
          fontSize: 48,
          background: 'linear-gradient(135deg, #1e3a5f 0%, #2b85c9 100%)',
          width: '100%',
          height: '100%',
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          justifyContent: 'center',
          color: 'white',
          padding: '60px',
        }}
      >
        <div
          style={{
            fontSize: 72,
            fontWeight: 700,
            marginBottom: 20,
          }}
        >
          TWELVY
        </div>
        <div
          style={{
            fontSize: 36,
            opacity: 0.9,
            textAlign: 'center',
            maxWidth: '80%',
          }}
        >
          Stage de récupération de points
        </div>
        <div
          style={{
            fontSize: 24,
            opacity: 0.7,
            marginTop: 30,
            textAlign: 'center',
          }}
        >
          Récupérez 4 points en 48h - Meilleur prix garanti
        </div>
      </div>
    ),
    { ...size }
  )
}
