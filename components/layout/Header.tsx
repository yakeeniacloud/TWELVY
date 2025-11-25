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
    <>
      {/* Top Bar - White background with Espace Client */}
      <div className="bg-white border-b border-gray-200">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="flex h-10 items-center justify-between">
            {/* Left: Logo */}
            <Link href="/" className="flex items-center gap-2">
              <span className="text-lg font-bold text-gray-900">TWELVY</span>
            </Link>

            {/* Center: Title */}
            <div className="text-xs text-gray-600 uppercase tracking-wide">
              STAGE DE RÉCUPÉRATION DE POINTS
            </div>

            {/* Right: Espace Client */}
            <Link
              href="/espace-client"
              className="flex items-center gap-1 text-sm text-gray-700 hover:text-gray-900"
            >
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              Espace Client
            </Link>
          </div>
        </div>
      </div>

      {/* Main Navigation - Dark background */}
      <nav className="bg-[#222222] text-white">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="flex h-14 items-center justify-between">
            {/* Left: Menu Items from WordPress */}
            <div className="flex items-center gap-6">
              {loading ? (
                <div className="text-xs text-gray-400">Chargement...</div>
              ) : (
                menu.map((item) => (
                  <div
                    key={item.id}
                    ref={(el) => {
                      menuRefs.current[item.id] = el
                    }}
                    className="relative"
                  >
                    {item.children.length > 0 ? (
                      // Menu with dropdown
                      <>
                        <button
                          onClick={() => toggleMenu(item.id)}
                          className={`text-xs font-medium uppercase tracking-wide hover:text-red-500 transition-colors flex items-center gap-1 ${
                            openMenuId === item.id ? 'text-red-500' : 'text-white'
                          }`}
                        >
                          {decodeHtmlEntities(item.title)}
                          <svg
                            className={`w-3 h-3 transition-transform ${
                              openMenuId === item.id ? 'rotate-180' : ''
                            }`}
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                          >
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                          </svg>
                        </button>

                        {/* Mega Menu Dropdown */}
                        {openMenuId === item.id && (
                          <div className="absolute top-full left-0 mt-0 bg-white shadow-lg border-t-2 border-red-500 z-50 min-w-[800px]">
                            <div className="grid grid-cols-4 gap-6 p-6">
                              {getMenuColumns(item.children).map((column, colIndex) => (
                                <div key={colIndex} className="space-y-3">
                                  {column.map((child) => (
                                    <Link
                                      key={child.id}
                                      href={`/${child.slug}`}
                                      onClick={() => setOpenMenuId(null)}
                                      className="block text-sm text-blue-600 hover:text-blue-800 hover:underline"
                                    >
                                      {decodeHtmlEntities(child.title)}
                                    </Link>
                                  ))}
                                </div>
                              ))}
                            </div>
                          </div>
                        )}
                      </>
                    ) : (
                      // Menu without dropdown
                      <Link
                        href={`/${item.slug}`}
                        className="text-xs font-medium uppercase tracking-wide text-white hover:text-red-500 transition-colors"
                      >
                        {decodeHtmlEntities(item.title)}
                      </Link>
                    )}
                  </div>
                ))
              )}
            </div>

            {/* Right: AIDE ET CONTACT Button */}
            <Link
              href="/aide-et-contact"
              className="bg-[#2b85c9] hover:bg-[#1e6aa8] text-white text-xs font-medium uppercase tracking-wide px-4 py-2 rounded transition-colors"
            >
              AIDE ET CONTACT
            </Link>
          </div>
        </div>
      </nav>
    </>
  )
}
