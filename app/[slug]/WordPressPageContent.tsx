'use client'

import Link from 'next/link'
import CitySearchBar from '@/components/stages/CitySearchBar'

interface MenuItem {
  id: number
  title: string
  slug: string
  children: {
    id: number
    title: string
    slug: string
  }[]
}

interface PageContent {
  id: number
  title: string
  content: string
  slug: string
}

interface Props {
  content: PageContent
  menu: MenuItem[]
  slug: string
}

function SearchBanner() {
  return (
    <div className="py-6 border-b border-gray-200">
      <div className="mx-auto px-4 flex justify-center">
        <div style={{ width: '460px' }}>
          <CitySearchBar
            placeholder="Entrez votre ville ou code postal pour trouver un stage"
            variant="filter"
          />
        </div>
      </div>
    </div>
  )
}

function PageFooter() {
  return (
    <footer className="bg-[#343435] py-6 mt-12">
      <div className="max-w-7xl mx-auto px-4">
        <div className="flex flex-wrap items-center justify-center gap-6 mb-3">
          <a href="/qui-sommes-nous" className="text-white text-xs hover:underline">Qui sommes-nous</a>
          <a href="https://www.khapeo.com/wp/psp/aide-et-contact-prostagespermis/" className="text-white text-xs hover:underline" target="_blank" rel="noopener noreferrer">Aide et contact</a>
          <a href="https://www.prostagespermis.fr/CGV_PROSTAGESPERMIS-STAGIAIRES.pdf" className="text-white text-xs hover:underline" target="_blank" rel="noopener noreferrer">Conditions générales de vente</a>
          <a href="/mentions-legales" className="text-white text-xs hover:underline">Mentions légales</a>
          <a href="https://psp-copie.twelvy.net/es/" className="text-white text-xs hover:underline">Espace Client</a>
          <a href="https://psp-copie.twelvy.net/ep/" className="text-white text-xs hover:underline">Espace Partenaire</a>
        </div>
        <p className="text-center text-white text-xs">{new Date().getFullYear()}©ProStagesPermis</p>
      </div>
    </footer>
  )
}

export default function WordPressPageContent({ content, menu, slug }: Props) {
  // Find if this page is a parent page with children
  const parentPage = menu.find(item => item.slug === slug)
  const hasChildren = parentPage && parentPage.children.length > 0

  // Parent page with children - show list of child pages
  if (hasChildren && parentPage) {
    return (
      <div className="min-h-screen bg-white">
        <SearchBanner />
        <div className="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
          <h1 className="text-4xl font-bold text-gray-900 mb-8 border-b-2 border-red-500 pb-4">
            {parentPage.title}
          </h1>

          {content && content.content && (
            <div
              className="wp-content max-w-none text-gray-700 mb-12"
              dangerouslySetInnerHTML={{ __html: content.content }}
            />
          )}

          <div className="mt-12">
            <h2 className="text-2xl font-bold text-gray-900 mb-6">Articles dans cette catégorie</h2>
            <div className="grid gap-6 md:grid-cols-2">
              {parentPage.children.map((child) => (
                <Link
                  key={child.id}
                  href={`/${child.slug}`}
                  className="block bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-red-500 hover:shadow-lg transition-all group"
                >
                  <h3 className="text-lg font-semibold text-gray-900 group-hover:text-red-600 mb-2">
                    {child.title}
                  </h3>
                  <div className="flex items-center text-sm text-blue-600 group-hover:text-blue-800">
                    Lire l&apos;article
                    <svg className="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                    </svg>
                  </div>
                </Link>
              ))}
            </div>
          </div>
        </div>
        <PageFooter />
      </div>
    )
  }

  // Regular page (no children) - show normal content
  return (
    <div className="min-h-screen bg-white">
      <SearchBanner />
      <div className="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
        <h1 className="text-4xl font-bold text-gray-900 mb-8 border-b-2 border-red-500 pb-4">
          {content.title}
        </h1>

        <div
          className="wp-content max-w-none"
          dangerouslySetInnerHTML={{ __html: content.content }}
        />
      </div>
      <PageFooter />
    </div>
  )
}
