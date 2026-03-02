import { Metadata } from 'next'

function formatCityDisplay(city: string): string {
  return city
    .split('-')
    .map((word, index) => {
      const lowerWord = word.toLowerCase()
      if (index > 0 && ['en', 'de', 'du', 'la', 'le', 'les', 'sur', 'sous'].includes(lowerWord)) {
        return lowerWord
      }
      return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
    })
    .join('-')
}

export async function generateMetadata(
  { params }: { params: Promise<{ slug: string }> }
): Promise<Metadata> {
  const { slug } = await params
  const cityName = formatCityDisplay(slug)

  const title = `Stage récupération de points ${cityName}`
  const description = `Trouvez un stage de récupération de points à ${cityName}. Comparez les prix, les dates et réservez votre stage de sensibilisation à la sécurité routière au meilleur prix.`

  return {
    title,
    description,
    alternates: {
      canonical: `https://www.twelvy.net/stages-recuperation-points/${slug}`,
    },
    openGraph: {
      title: `${title} - Twelvy`,
      description,
      type: 'website',
      url: `https://www.twelvy.net/stages-recuperation-points/${slug}`,
      siteName: 'Twelvy',
    },
  }
}

export default function StagesLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return children
}
