'use client'

import { useParams } from 'next/navigation'
import { useWordPressContent } from '@/lib/useWordPressContent'

export default function WordPressPage() {
  const params = useParams()
  const slug = params.slug as string

  const { content, loading } = useWordPressContent(slug)

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
