'use client'

import { useState } from 'react'

export default function TestPage() {
  const [postMessage, setPostMessage] = useState('')
  const [getMessage, setGetMessage] = useState('')
  const [loading, setLoading] = useState(false)

  async function handleSubmit() {
    setLoading(true)
    setPostMessage('Sending POST...')

    try {
      const response = await fetch('/api/test-booking', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          prenom: 'TestTWELVY',
          nom: 'User',
          email: 'test@twelvy.net',
          telephone: '0612345678',
          stage_id: '7cab4960-6fb6-4f92-9da7-8ed901014c39',
        }),
      })

      const data = await response.json()

      if (response.ok) {
        setPostMessage(`✅ Success! Booking ID: ${data.id} | Reference: ${data.booking_reference}`)
      } else {
        setPostMessage(`❌ Error: ${data.error || 'Unknown error'}`)
      }
    } catch (error) {
      setPostMessage(`❌ Error: ${error instanceof Error ? error.message : 'Unknown error'}`)
    } finally {
      setLoading(false)
    }
  }

  async function handleGetTest() {
    setLoading(true)
    setGetMessage('Testing GET...')

    try {
      const response = await fetch('/api/test-get', {
        method: 'GET',
      })

      const data = await response.json()

      if (response.ok && data.ok) {
        setGetMessage(`✅ GET Success! PHP is executing. Message: ${data.message}`)
      } else {
        setGetMessage(`❌ GET Error: ${data.message || data.error || 'Unknown error'}`)
      }
    } catch (error) {
      setGetMessage(`❌ Error: ${error instanceof Error ? error.message : 'Unknown error'}`)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="flex flex-col items-center justify-center min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
      <div className="bg-white p-12 rounded-xl shadow-2xl max-w-md w-full">
        <h1 className="text-3xl font-bold mb-2 text-center text-gray-900">TWELVY Test</h1>
        <p className="text-center text-gray-600 mb-8">Testing OVH API Connection</p>

        {/* POST Test Button */}
        <button
          onClick={handleSubmit}
          disabled={loading}
          className="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-bold py-4 px-6 rounded-lg transition-all duration-200 transform hover:scale-105 disabled:scale-100 mb-3"
        >
          {loading ? '⏳ Sending...' : '📤 Send Test Booking (POST)'}
        </button>

        {/* GET Test Button */}
        <button
          onClick={handleGetTest}
          disabled={loading}
          className="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-bold py-4 px-6 rounded-lg transition-all duration-200 transform hover:scale-105 disabled:scale-100"
        >
          {loading ? '⏳ Testing...' : '📥 Test GET Request'}
        </button>

        {/* POST Result Message */}
        {postMessage && (
          <div className="mt-6 p-4 rounded-lg bg-blue-50 border border-blue-200">
            <p className="text-center font-semibold text-sm text-blue-900">
              <span className="block text-xs text-blue-700 mb-1">POST Result:</span>
              {postMessage}
            </p>
          </div>
        )}

        {/* GET Result Message */}
        {getMessage && (
          <div className="mt-4 p-4 rounded-lg bg-green-50 border border-green-200">
            <p className="text-center font-semibold text-sm text-green-900">
              <span className="block text-xs text-green-700 mb-1">GET Result:</span>
              {getMessage}
            </p>
          </div>
        )}

        <div className="mt-8 pt-8 border-t border-gray-200">
          <h2 className="text-sm font-bold text-gray-700 mb-3">POST Test Data:</h2>
          <pre className="text-xs bg-gray-100 p-3 rounded text-gray-700 overflow-auto">
{JSON.stringify({
  prenom: 'TestTWELVY',
  nom: 'User',
  email: 'test@twelvy.net',
  telephone: '0612345678',
  stage_id: '7cab4960-6fb6-4f92-9da7-8ed901014c39'
}, null, 2)}
          </pre>
        </div>
      </div>
    </div>
  )
}
