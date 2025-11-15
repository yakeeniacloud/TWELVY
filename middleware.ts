import { NextResponse } from 'next/server'
import type { NextRequest } from 'next/server'

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl

  // Match: /stages-recuperation-points-CITY-POSTAL
  const stagesMatch = pathname.match(/^\/stages-recuperation-points-([A-Z0-9-]+)$/)
  if (stagesMatch) {
    const slug = stagesMatch[1] // e.g., "PARIS-75001"
    // Rewrite to: /stages-recuperation-points/PARIS-75001
    const url = request.nextUrl.clone()
    url.pathname = `/stages-recuperation-points/${slug}`
    return NextResponse.rewrite(url)
  }

  // Match: /stages-recuperation-points-CITY-POSTAL/123
  const stagesIdMatch = pathname.match(/^\/stages-recuperation-points-([A-Z0-9-]+)\/(\d+)$/)
  if (stagesIdMatch) {
    const slug = stagesIdMatch[1]
    const id = stagesIdMatch[2]
    const url = request.nextUrl.clone()
    url.pathname = `/stages-recuperation-points/${slug}/${id}`
    return NextResponse.rewrite(url)
  }

  // Match: /stages-recuperation-points-CITY-POSTAL/123/confirmation
  const stagesConfirmMatch = pathname.match(/^\/stages-recuperation-points-([A-Z0-9-]+)\/(\d+)\/confirmation$/)
  if (stagesConfirmMatch) {
    const slug = stagesConfirmMatch[1]
    const id = stagesConfirmMatch[2]
    const url = request.nextUrl.clone()
    url.pathname = `/stages-recuperation-points/${slug}/${id}/confirmation`
    return NextResponse.rewrite(url)
  }

  // Match: /stages-recuperation-points-CITY-POSTAL/123/inscription
  const stagesInscriptionMatch = pathname.match(/^\/stages-recuperation-points-([A-Z0-9-]+)\/(\d+)\/inscription$/)
  if (stagesInscriptionMatch) {
    const slug = stagesInscriptionMatch[1]
    const id = stagesInscriptionMatch[2]
    const url = request.nextUrl.clone()
    url.pathname = `/stages-recuperation-points/${slug}/${id}/inscription`
    return NextResponse.rewrite(url)
  }

  // Match: /stages-recuperation-points-CITY-POSTAL/123/merci
  const stagesMerciMatch = pathname.match(/^\/stages-recuperation-points-([A-Z0-9-]+)\/(\d+)\/merci$/)
  if (stagesMerciMatch) {
    const slug = stagesMerciMatch[1]
    const id = stagesMerciMatch[2]
    const url = request.nextUrl.clone()
    url.pathname = `/stages-recuperation-points/${slug}/${id}/merci`
    return NextResponse.rewrite(url)
  }

  return NextResponse.next()
}

export const config = {
  matcher: '/stages-recuperation-points-:path*',
}
