import { Metadata } from 'next'
import { notFound } from 'next/navigation'
import Link from 'next/link'
import CitySearchBar from '@/components/stages/CitySearchBar'

const WP_HEADERS = {
  'Accept': 'application/json',
  'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
}

interface WPPost {
  id: number
  slug: string
  title: { rendered: string }
  content: { rendered: string }
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

async function getPostBySlug(slug: string): Promise<WPPost | null> {
  try {
    const response = await fetch(
      `https://headless.twelvy.net/wp-json/wp/v2/posts?slug=${slug}&status=publish`,
      { headers: WP_HEADERS, next: { revalidate: 3600 } }
    )
    if (!response.ok) return null
    const posts = await response.json()
    if (!Array.isArray(posts) || posts.length === 0) return null
    return posts[0]
  } catch {
    return null
  }
}

export async function generateMetadata(
  { params }: { params: Promise<{ slug: string }> }
): Promise<Metadata> {
  const { slug } = await params
  const post = await getPostBySlug(slug)
  if (!post) return { title: 'Article non trouvé' }

  const title = decodeEntities(post.title.rendered)
  let description = stripHtml(post.excerpt.rendered)
  if (!description || description.length < 20) {
    description = stripHtml(post.content.rendered).substring(0, 160)
  }
  if (description.length > 160) description = description.substring(0, 157) + '...'

  return {
    title,
    description,
    alternates: {
      canonical: `https://www.twelvy.net/blog/${slug}`,
    },
    openGraph: {
      title,
      description,
      type: 'article',
      url: `https://www.twelvy.net/blog/${slug}`,
      siteName: 'Twelvy',
      locale: 'fr_FR',
    },
  }
}

export default async function BlogPostPage(
  { params }: { params: Promise<{ slug: string }> }
) {
  const { slug } = await params
  const post = await getPostBySlug(slug)
  if (!post) notFound()

  const title = decodeEntities(post.title.rendered)
  const year = new Date().getFullYear()

  const jsonLd = {
    '@context': 'https://schema.org',
    '@type': 'Article',
    headline: title,
    datePublished: post.date,
    url: `https://www.twelvy.net/blog/${slug}`,
    publisher: {
      '@type': 'Organization',
      name: 'Twelvy',
      url: 'https://www.twelvy.net',
    },
  }

  return (
    <>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }}
      />
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

        {/* Breadcrumb */}
        <div className="max-w-4xl mx-auto px-4 pt-6 sm:px-6 lg:px-8">
          <nav className="text-xs text-gray-400">
            <Link href="/" className="hover:underline">Accueil</Link>
            <span className="mx-2">›</span>
            <Link href="/blog" className="hover:underline">Blog</Link>
            <span className="mx-2">›</span>
            <span className="text-gray-600">{title}</span>
          </nav>
        </div>

        {/* Article */}
        <article className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
          <header className="mb-8">
            <p className="text-xs text-gray-400 mb-2">{formatDate(post.date)}</p>
            <h1 className="text-3xl font-bold text-gray-900 border-b-2 border-red-500 pb-4">
              {title}
            </h1>
          </header>
          <div
            className="wp-content max-w-none text-gray-700"
            dangerouslySetInnerHTML={{ __html: post.content.rendered }}
          />
          <div className="mt-10 pt-6 border-t border-gray-200">
            <Link href="/blog" className="text-sm text-[#2b85c9] hover:underline">
              ← Retour au blog
            </Link>
          </div>
        </article>

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
    </>
  )
}
