import { Metadata } from 'next'
import Link from 'next/link'
import CitySearchBar from '@/components/stages/CitySearchBar'

export const metadata: Metadata = {
  title: 'Blog — Conseils et actualités routières',
  description: 'Retrouvez nos articles et conseils sur la récupération de points, les infractions routières, la sécurité routière et tout ce qui concerne le code de la route.',
  alternates: {
    canonical: 'https://www.twelvy.net/blog',
  },
  openGraph: {
    title: 'Blog — Conseils et actualités routières',
    description: 'Retrouvez nos articles et conseils sur la récupération de points, les infractions routières et la sécurité routière.',
    type: 'website',
    url: 'https://www.twelvy.net/blog',
    siteName: 'Twelvy',
    locale: 'fr_FR',
  },
}

const WP_HEADERS = {
  'Accept': 'application/json',
  'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
}

interface WPPost {
  id: number
  slug: string
  title: { rendered: string }
  excerpt: { rendered: string }
  date: string
}

function formatDate(dateStr: string): string {
  const date = new Date(dateStr)
  const months = [
    'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
    'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',
  ]
  return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`
}

function decodeEntities(text: string): string {
  return text
    .replace(/&#(\d+);/g, (_, code) => String.fromCharCode(parseInt(code, 10)))
    .replace(/&#x([0-9a-fA-F]+);/g, (_, code) => String.fromCharCode(parseInt(code, 16)))
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .replace(/&rsquo;/g, "'")
    .replace(/&lsquo;/g, "'")
    .replace(/&nbsp;/g, ' ')
}

function stripHtml(html: string): string {
  return decodeEntities(html.replace(/<[^>]+>/g, '').replace(/\s+/g, ' ').trim())
}

async function getAllPosts(): Promise<WPPost[]> {
  try {
    const response = await fetch(
      'https://headless.twelvy.net/wp-json/wp/v2/posts?per_page=100&status=publish&orderby=date&order=desc&_fields=id,slug,title,excerpt,date',
      { headers: WP_HEADERS, next: { revalidate: 3600 } }
    )
    if (!response.ok) return []
    return await response.json()
  } catch {
    return []
  }
}

export default async function BlogPage() {
  const posts = await getAllPosts()
  const year = new Date().getFullYear()

  return (
    <div className="min-h-screen bg-white">
      {/* Search Banner */}
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

      {/* Main content */}
      <main className="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-2 border-b-2 border-red-500 pb-4">
          Blog — Conseils et actualités routières
        </h1>
        <p className="text-gray-400 text-sm mb-10">{posts.length} articles</p>

        <div className="space-y-8">
          {posts.map((post) => {
            const title = decodeEntities(post.title.rendered)
            const excerpt = stripHtml(post.excerpt.rendered).substring(0, 200)
            return (
              <article key={post.id} className="border-b border-gray-100 pb-8">
                <p className="text-xs text-gray-400 mb-1">{formatDate(post.date)}</p>
                <h2 className="text-xl font-semibold text-gray-900 mb-2">
                  <Link href={`/blog/${post.slug}`} className="hover:text-red-600 transition-colors">
                    {title}
                  </Link>
                </h2>
                <p className="text-gray-600 text-sm leading-relaxed mb-3">{excerpt}</p>
                <Link
                  href={`/blog/${post.slug}`}
                  className="text-sm font-medium text-[#2b85c9] hover:underline"
                >
                  Lire l&apos;article →
                </Link>
              </article>
            )
          })}
        </div>
      </main>

      {/* Footer */}
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
          <p className="text-center text-white text-xs">{year}©ProStagesPermis</p>
        </div>
      </footer>
    </div>
  )
}
