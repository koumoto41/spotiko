import { useState } from 'react'
import { fetchSpotifyAuthorizeUrl } from '../api/spotify'

export function SpotifyConnectPage() {
  const [isRedirecting, setIsRedirecting] = useState(false)
  const [error, setError] = useState<string | null>(null)

  async function handleConnect() {
    setIsRedirecting(true)
    setError(null)
    try {
      const url = await fetchSpotifyAuthorizeUrl()
      window.location.href = url
    } catch {
      setError('認可URLの取得に失敗しました。')
      setIsRedirecting(false)
    }
  }

  return (
    <div className="page page-narrow">
      <h1>Spotify連携</h1>
      <p>Spotifyアカウントと連携してトークンを取得します。</p>
      {error && <p className="error">{error}</p>}
      <button onClick={handleConnect} disabled={isRedirecting}>
        {isRedirecting ? '接続中...' : 'Spotifyと連携する'}
      </button>
    </div>
  )
}
