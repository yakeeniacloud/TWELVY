'use client'

import { useSearchParams } from 'next/navigation'
import Link from 'next/link'

export default function MerciPage() {
  const searchParams = useSearchParams()
  const ref = searchParams.get('ref') || 'BK-XXXX-XXXXXX'

  return (
    <div className="min-h-screen bg-gradient-to-b from-green-50 to-white flex items-center justify-center px-4">
      <div className="max-w-2xl w-full text-center py-12">
        {/* Success Icon */}
        <div className="mb-8">
          <div className="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
            <svg className="w-10 h-10 text-green-600" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
            </svg>
          </div>
        </div>

        {/* Main Message */}
        <h1 className="text-4xl font-bold text-gray-900 mb-4">
          Merci pour votre inscription!
        </h1>
        <p className="text-xl text-gray-600 mb-8">
          Votre réservation a été confirmée avec succès.
        </p>

        {/* Booking Reference */}
        <div className="bg-white p-6 rounded-lg border-2 border-green-200 mb-8">
          <p className="text-sm text-gray-600 mb-2">Numéro de réservation</p>
          <p className="text-3xl font-bold text-green-600 font-mono">{ref}</p>
          <p className="text-sm text-gray-600 mt-2">
            Conservez ce numéro pour référence
          </p>
        </div>

        {/* Information */}
        <div className="bg-blue-50 p-6 rounded-lg border border-blue-200 mb-8 text-left">
          <h2 className="font-semibold text-gray-900 mb-4">Informations importantes</h2>
          <ul className="space-y-2 text-sm text-gray-700">
            <li>✓ Vous devez assister aux deux jours du stage</li>
            <li>✓ Veuillez apporter votre pièce d'identité et votre permis de conduire</li>
            <li>✓ Arrivez 15 minutes avant le début du stage</li>
            <li>✓ Vous pouvez annuler gratuitement jusqu'à 14 jours avant</li>
          </ul>
        </div>

        {/* Action Buttons */}
        <div className="flex flex-col gap-3">
          <Link
            href="/"
            className="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-all"
          >
            Retour à l'accueil
          </Link>
          <button
            onClick={() => window.print()}
            className="inline-block bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition-all"
          >
            Imprimer la confirmation
          </button>
        </div>

        {/* Footer */}
        <p className="text-xs text-gray-500 mt-8">
          Un email de confirmation a été envoyé à votre adresse email.
        </p>
      </div>
    </div>
  )
}
