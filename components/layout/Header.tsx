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
      {/* Top Bar - White background with Logo */}
      <div className="flex items-center justify-between px-8 py-3">
        <Link href="/">
          <img
            src="/prostagespermis-logo.png"
            alt="ProStagesPermis"
            className="h-8 w-auto"
          />
        </Link>
      </div>

      {/* Main Navigation - Dark background - reduced height by 20% */}
      <nav className="bg-[#222222] px-8 py-2">
        <div className="flex items-center justify-end max-w-7xl mx-auto">
          <Link href="/aide-et-contact" className="text-white text-xs hover:text-gray-200 transition-colors lowercase">
            aide et contact
          </Link>
        </div>
      </nav>
    </header>
  )
}
