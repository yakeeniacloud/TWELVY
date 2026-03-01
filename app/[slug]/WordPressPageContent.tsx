'use client'

import Link from 'next/link'

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

export default function WordPressPageContent({ content, menu, slug }: Props) {
  // Find if this page is a parent page with children
  const parentPage = menu.find(item => item.slug === slug)
  const hasChildren = parentPage && parentPage.children.length > 0

  // Parent page with children - show list of child pages
  if (hasChildren && parentPage) {
    return (
      <div className="min-h-screen bg-white">
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
      </div>
    )
  }

  // Regular page (no children) - show normal content
  return (
    <div className="min-h-screen bg-white">
      <div className="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
        <h1 className="text-4xl font-bold text-gray-900 mb-8 border-b-2 border-red-500 pb-4">
          {content.title}
        </h1>

        <div
          className="wp-content max-w-none"
          dangerouslySetInnerHTML={{ __html: content.content }}
        />
      </div>
    </div>
  )
}
