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

  // Find related articles: sibling pages under the same parent category
  const relatedArticles: { title: string; slug: string }[] = []
  for (const parent of menu) {
    const isChild = parent.children.some(c => c.slug === slug)
    if (isChild) {
      for (const child of parent.children) {
        if (child.slug !== slug && relatedArticles.length < 4) {
          relatedArticles.push({ title: child.title, slug: child.slug })
        }
      }
    }
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

        {relatedArticles.length > 0 && (
          <div className="mt-16 pt-8 border-t border-gray-200">
            <h2 className="text-2xl font-bold text-gray-900 mb-6">Articles similaires</h2>
            <div className="grid gap-4 md:grid-cols-2">
              {relatedArticles.map((article) => (
                <Link
                  key={article.slug}
                  href={`/${article.slug}`}
                  className="block bg-gray-50 border border-gray-200 rounded-lg p-4 hover:border-red-500 hover:bg-white hover:shadow transition-all group"
                >
                  <h3 className="text-base font-medium text-gray-900 group-hover:text-red-600">
                    {article.title}
                  </h3>
                  <span className="text-sm text-blue-600 mt-1 inline-flex items-center">
                    Lire l&apos;article
                    <svg className="w-3 h-3 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                    </svg>
                  </span>
                </Link>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
