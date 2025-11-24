'use client'

import { useParams } from 'next/navigation'
import Link from 'next/link'
import { useWordPressContent } from '@/lib/useWordPressContent'
import { useWordPressMenu } from '@/lib/useWordPressMenu'

export default function WordPressPage() {
  const params = useParams()
  const slug = params.slug as string

  const { content, loading } = useWordPressContent(slug)
  const { menu } = useWordPressMenu()

  // Find if this page is a parent page with children
  const parentPage = menu.find(item => item.slug === slug)
  const hasChildren = parentPage && parentPage.children.length > 0

  if (loading) {
    return (
      <div className="min-h-screen bg-white">
        <div className="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
          <div className="text-center text-gray-500">
            Chargement de la page...
          </div>
        </div>
      </div>
    )
  }

  if (!content) {
    return (
      <div className="min-h-screen bg-white">
        <div className="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
          <div className="text-center">
            <h1 className="text-4xl font-bold text-gray-900 mb-4">Page non trouvée</h1>
            <p className="text-gray-600">
              La page que vous recherchez n'existe pas ou a été déplacée.
            </p>
          </div>
        </div>
      </div>
    )
  }

  // Parent page with children - show list of child pages
  if (hasChildren && parentPage) {
    return (
      <div className="min-h-screen bg-white">
        <div className="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
          {/* Page Title */}
          <h1 className="text-4xl font-bold text-gray-900 mb-8 border-b-2 border-red-500 pb-4">
            {parentPage.title}
          </h1>

          {/* Parent Page Content (if any) */}
          {content && content.content && (
            <div
              className="prose prose-lg max-w-none text-gray-700 mb-12"
              dangerouslySetInnerHTML={{ __html: content.content }}
            />
          )}

          {/* Children List */}
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
                    Lire l'article
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
        {/* Page Title */}
        <h1 className="text-4xl font-bold text-gray-900 mb-8 border-b-2 border-red-500 pb-4">
          {content.title}
        </h1>

        {/* Page Content from WordPress */}
        <div
          className="prose prose-lg max-w-none text-gray-700
            prose-headings:text-gray-900
            prose-h2:text-2xl prose-h2:font-bold prose-h2:mt-8 prose-h2:mb-4
            prose-h3:text-xl prose-h3:font-semibold prose-h3:mt-6 prose-h3:mb-3
            prose-p:mb-4
            prose-a:text-blue-600 prose-a:hover:text-blue-800 prose-a:underline
            prose-ul:list-disc prose-ul:ml-6 prose-ul:mb-4
            prose-ol:list-decimal prose-ol:ml-6 prose-ol:mb-4
            prose-li:mb-2
            prose-strong:font-semibold prose-strong:text-gray-900"
          dangerouslySetInnerHTML={{ __html: content.content }}
        />
      </div>
    </div>
  )
}
