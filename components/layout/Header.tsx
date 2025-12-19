'use client'

import { useState, useRef, useEffect } from 'react'
import Link from 'next/link'
import { useWordPressMenu } from '@/lib/useWordPressMenu'

// Decode HTML entities (like &#8217; to ')
const decodeHtmlEntities = (text: string): string => {
  if (typeof window === 'undefined') return text
  const textarea = document.createElement('textarea')
  textarea.innerHTML = text
  return textarea.value
}

export default function Header() {
  const { menu, loading } = useWordPressMenu()
  const [openMenuId, setOpenMenuId] = useState<number | null>(null)
  const menuRefs = useRef<{ [key: number]: HTMLDivElement | null }>({})

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

  // Split children into columns (4 columns like screenshot)
  const getMenuColumns = (children: any[]) => {
    if (children.length === 0) return []

    const columns: any[][] = [[], [], [], []]
    children.forEach((child, index) => {
      columns[index % 4].push(child)
    })

    return columns.filter(col => col.length > 0)
  }

  return (
    <header className="hidden md:block bg-white border-b border-gray-200">
      {/* Top Bar - White background with Logo and Espace Client */}
      <div className="flex items-center justify-between px-8 py-3">
        <Link href="/">
          <img
            src="/prostagespermis-logo.png"
            alt="ProStagesPermis"
            className="h-8 w-auto"
          />
        </Link>

        <Link
          href="/espace-client"
          className="flex items-center gap-2 text-gray-700 hover:text-gray-900 transition-colors"
        >
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <span className="text-sm">Espace Client</span>
        </Link>
      </div>

      {/* Main Navigation - Dark background */}
      <nav className="bg-[#3d3d3d] px-8 py-3">
        <div className="flex items-center justify-between max-w-7xl mx-auto">
          <ul className="flex items-center gap-6">
            {loading ? (
              <li className="text-xs text-gray-400">Chargement...</li>
            ) : (
              menu.slice(0, 3).map((item) => (
                <li key={item.id}>
                  <Link
                    href={`/${item.slug}`}
                    className="text-white text-sm hover:text-gray-200 transition-colors"
                  >
                    {decodeHtmlEntities(item.title)}
                  </Link>
                </li>
              ))
            )}
          </ul>

          <ul className="flex items-center gap-6">
            <li>
              <Link href="/qui-sommes-nous" className="text-white text-sm hover:text-gray-200 transition-colors">
                Qui sommes-nous
              </Link>
            </li>
            <li>
              <Link href="/aide-et-contact" className="text-white text-sm hover:text-gray-200 transition-colors">
                Aide et contact
              </Link>
            </li>
          </ul>
        </div>
      </nav>
    </header>
  )
}
