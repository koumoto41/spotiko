import { Link, useSearchParams } from 'react-router-dom'

export function SpotifyCallbackPage() {
  const [searchParams] = useSearchParams()
  const status = searchParams.get('status')

  return (
    <div className="page page-narrow">
      <h1>Spotify連携</h1>
      {status === 'success' ? (
        <p>連携が完了しました。</p>
      ) : (
        <p className="error">連携に失敗しました。もう一度お試しください。</p>
      )}
      <Link to="/">トップへ戻る</Link>
    </div>
  )
}
