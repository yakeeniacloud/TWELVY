import CitySearchBar from '@/components/stages/CitySearchBar'

export const metadata = {
  title: 'TWELVY - Stage de Récupération de Points',
  description: 'Récupérez 4 points en 48h - Stages agréés par la Préfecture',
}

export default function Home() {
  return (
    <div>
      {/* Hero Section with Background */}
      <div className="relative bg-gradient-to-b from-gray-800 to-gray-900 min-h-[560px] flex items-center justify-center">
        {/* Dark Overlay */}
        <div className="absolute inset-0 bg-black/40"></div>

        {/* Hero Content */}
        <div className="relative z-10 mx-auto max-w-[880px] px-4 sm:px-6 lg:px-8 text-center">
          {/* Hero Title */}
          <div
            className="mb-8"
            style={{
              fontSize: '48px',
              fontWeight: 700,
              lineHeight: 1.1,
              color: '#ffffff',
              textShadow: '0 2px 8px rgba(0,0,0,0.45)',
            }}
          >
            <h1>Stage de Récupération de Points</h1>
            <p className="text-3xl mt-3">Récupérez 4 points en 48h</p>
          </div>

          {/* Search Bar */}
          <div className="max-w-[640px] mx-auto">
            <CitySearchBar placeholder="Saisir une ville pour trouver un stage" variant="large" />
          </div>
        </div>
      </div>

      {/* Content Section */}
      <div className="bg-white">
        <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
          <div className="prose prose-lg prose-indigo max-w-none">
            <h2>Trouvez votre stage de récupération de points</h2>
            <p>
              TWELVY vous propose une sélection de stages de récupération de points agréés par la Préfecture.
              Saisissez votre ville pour découvrir les stages disponibles près de chez vous.
            </p>
          </div>
        </div>
      </div>
    </div>
  )
}
