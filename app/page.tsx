'use client'

import CitySearchBar from '@/components/stages/CitySearchBar'
import { useWordPressContent } from '@/lib/useWordPressContent'

export default function Home() {
  const { content, loading } = useWordPressContent('homepage')

  // Split content at [SEARCH_BAR] delimiter
  const contentAbove = content?.content.split('[SEARCH_BAR]')[0] || ''
  const contentBelow = content?.content.split('[SEARCH_BAR]')[1] || ''

  return (
    <div>
      {/* Hero Section with Background */}
      <div className="relative bg-gradient-to-b from-gray-800 to-gray-900 min-h-[560px] flex items-center justify-center">
        {/* Dark Overlay */}
        <div className="absolute inset-0 bg-black/40"></div>

        {/* Hero Content */}
        <div className="relative z-10 mx-auto max-w-[880px] px-4 sm:px-6 lg:px-8 text-center">
          {/* WordPress Content ABOVE Search Bar */}
          {contentAbove && (
            <div
              className="mb-8 text-white"
              style={{
                fontSize: '48px',
                fontWeight: 700,
                lineHeight: 1.1,
                textShadow: '0 2px 8px rgba(0,0,0,0.45)',
              }}
              dangerouslySetInnerHTML={{ __html: contentAbove }}
            />
          )}

          {/* Fallback if no WordPress content */}
          {!loading && !contentAbove && (
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
          )}

          {/* Search Bar */}
          <div className="max-w-[640px] mx-auto">
            <CitySearchBar placeholder="Saisir une ville pour trouver un stage" variant="large" />
          </div>
        </div>
      </div>

      {/* WordPress Content Section BELOW Search Bar */}
      {contentBelow && (
        <div className="bg-white border-b border-gray-200">
          <div className="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
            <div
              className="prose prose-sm prose-indigo max-w-none text-gray-700"
              dangerouslySetInnerHTML={{ __html: contentBelow }}
            />
          </div>
        </div>
      )}
    </div>
  )
}
