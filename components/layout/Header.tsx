'use client'

import { useState, useRef, useEffect } from 'react'
import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { useWordPressMenu } from '@/lib/useWordPressMenu'

// Decode HTML entities (like &#8217; to ')
const decodeHtmlEntities = (text: string): string => {
  if (typeof window === 'undefined') return text
  const textarea = document.createElement('textarea')
  textarea.innerHTML = text
  return textarea.value
}

// Format menu item: capitalize first letter only (like "Les stages permis à points")
const formatMenuItem = (text: string): string => {
  const decoded = decodeHtmlEntities(text)
  const lower = decoded.toLowerCase()
  return lower.charAt(0).toUpperCase() + lower.slice(1)
}

// Nav items that are anchors within other articles (not standalone pages)
// These link directly to a section in the target article instead of a placeholder page
const NAV_ANCHOR_LINKS: Record<string, string> = {
  'comment-obtenir-mes-acces-telepoints': '/consulter-ses-points#comment-obtenir-ses-codes-d-acces-a-telepoints',
  'obtenir-mes-acces-telepoints': '/consulter-ses-points#comment-obtenir-ses-codes-d-acces-a-telepoints',
  'comment-contester-un-retrait': '/suspension-de-permis-et-retrait-de-permis#comment-contester-une-suspension-de-permis',
  'recuperer-des-points-sur-mon-permis-probatoire': '/recuperer-ses-points#comment-recuperer-des-points-sur-mon-permis-probatoire',
  'combien-de-temps-pour-recuperer-ses-points': '/stage-de-sensibilisation-a-la-securite-routiere#comment-recuperer-les-points-apres-le-stage-de-sensibilisation',
  'dans-quels-cas-faire-un-stage': '/retrait-de-permis#dans-quels-cas-faut-il-faire-un-stage-de-recuperation-de-points',
  'comment-contester-mon-amende': '/payer-son-amende#comment-contester-un-pv',
  'comment-consulter-mon-solde-de-points': '/consulter-ses-points',
}

// Get the correct href for a nav child item (anchor link or normal page link)
const getChildHref = (slug: string): string => {
  return NAV_ANCHOR_LINKS[slug] || `/${slug}`
}

export default function Header() {
  const { menu, loading } = useWordPressMenu()
  const [openMenuId, setOpenMenuId] = useState<number | null>(null)
  const menuRefs = useRef<{ [key: number]: HTMLDivElement | null }>({})
  const pathname = usePathname()

  // Hide WordPress menu items on inscription (formulaire) page - only show "Aide et contact"
  const isInscriptionPage = pathname?.includes('/inscription')

  // Close dropdown when clicking outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (openMenuId !== null) {
        const menuElement = menuRefs.current[openMenuId]
        if (menuElement && !menuElement.contains(event.target as Node)) {
          setOpenMenuId(null)
        }
      }
    }

    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [openMenuId])

  // Close on escape key
  useEffect(() => {
    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        setOpenMenuId(null)
      }
    }

    document.addEventListener('keydown', handleKeyDown)
    return () => document.removeEventListener('keydown', handleKeyDown)
  }, [])

  const toggleMenu = (menuId: number) => {
    setOpenMenuId(openMenuId === menuId ? null : menuId)
  }

  return (
    <header className="hidden md:block bg-white border-b border-gray-200">
      {/* Top Bar - White background with Logo */}
      <div className="flex items-center justify-between px-8 py-3">
        <Link href="/">
          <img
            src="/prostagespermis-logo.png"
            alt="ProStagesPermis"
            className="h-8 w-auto"
          />
        </Link>
        <a
          href="https://psp-copie.twelvy.net/es/"
          className="flex items-center gap-1.5 no-underline"
          style={{ textDecoration: 'none' }}
        >
          <svg className="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <span style={{ fontSize: '13px', color: '#666', textDecoration: 'underline' }}>Espace Client</span>
        </a>
      </div>

      {/* Main Navigation - Dark background (reduced height by 20%) */}
      <nav className="bg-[#222222] px-8 py-1.5">
        <div className="flex items-center justify-between max-w-7xl mx-auto">
          {/* WordPress Menu Items - Hidden on inscription page */}
          <div className="flex items-center gap-6">
            {!isInscriptionPage && !loading && menu.map((item) => (
              <div
                key={item.id}
                ref={(el) => { menuRefs.current[item.id] = el }}
                className="relative"
              >
                {item.children.length > 0 ? (
                  // Parent with children - clickable dropdown
                  <>
                    <button
                      onClick={() => toggleMenu(item.id)}
                      className="flex items-center gap-1 text-white hover:text-gray-200 transition-colors"
                      style={{ fontSize: '11px' }}
                    >
                      <span>{formatMenuItem(item.title)}</span>
                      <svg
                        className={`w-3 h-3 transition-transform ${openMenuId === item.id ? 'rotate-180' : ''}`}
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                      </svg>
                    </button>

                    {/* Dropdown Menu */}
                    {openMenuId === item.id && (
                      <div className="absolute top-full left-0 mt-2 bg-white border border-gray-200 shadow-lg z-50 rounded" style={{ minWidth: '520px' }}>
                        <div className="border-t-2 border-red-500" />
                        <div className="p-5 grid grid-cols-3 gap-x-6 gap-y-1">
                          {item.children.map((child) => (
                            <Link
                              key={child.id}
                              href={getChildHref(child.slug)}
                              className="block py-1.5 text-sm text-gray-700 hover:text-red-600 transition-colors"
                              onClick={() => setOpenMenuId(null)}
                            >
                              {decodeHtmlEntities(child.title)}
                            </Link>
                          ))}
                        </div>
                      </div>
                    )}
                  </>
                ) : (
                  // No children - simple link
                  <Link
                    href={`/${item.slug}`}
                    className="text-white hover:text-gray-200 transition-colors"
                    style={{ fontSize: '11px' }}
                  >
                    {formatMenuItem(item.title)}
                  </Link>
                )}
              </div>
            ))}
          </div>

          {/* Right side - Qui sommes-nous + Aide et contact */}
          <div className="flex items-center gap-4">
            <Link href="/qui-sommes-nous" className="text-white hover:text-gray-200 transition-colors" style={{ fontSize: '11px' }}>
              Qui sommes-nous
            </Link>
            <Link href="/aide-et-contact" className="text-white hover:text-gray-200 transition-colors" style={{ fontSize: '11px' }}>
              Aide et contact
            </Link>
          </div>
        </div>
      </nav>
    </header>
  )
}
