import type { Metadata } from 'next'

// Funnel page — never indexed.
export const metadata: Metadata = {
  robots: { index: false, follow: false },
}

export default function PaiementLayout({ children }: { children: React.ReactNode }) {
  return <>{children}</>
}
