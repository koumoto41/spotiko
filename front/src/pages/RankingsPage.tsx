import { useEffect, useState } from 'react'
import { fetchRankings } from '../api/rankings'
import type { Rankings } from '../api/types'

export function RankingsPage() {
  const [rankings, setRankings] = useState<Rankings | null>(null)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    fetchRankings()
      .then(setRankings)
      .catch(() => setError('ランキングの取得に失敗しました。'))
  }, [])

  if (error) {
    return <p className="error">{error}</p>
  }

  if (!rankings) {
    return <p>読み込み中...</p>
  }

  return (
    <div className="page">
      <h1>トップランキング</h1>

      <section>
        <h2>アーティスト（{rankings.artists.aggregate_date ?? '集計データなし'}）</h2>
        {rankings.artists.items.length === 0 ? (
          <p>まだ集計データがありません。</p>
        ) : (
          <ul>
            {rankings.artists.items.map((artist) => (
              <li key={artist.id}>
                <a href={artist.url} target="_blank" rel="noreferrer">
                  {artist.name}
                </a>{' '}
                (人気度: {artist.popularity})
              </li>
            ))}
          </ul>
        )}
      </section>

      <section>
        <h2>トラック（{rankings.tracks.aggregate_date ?? '集計データなし'}）</h2>
        {rankings.tracks.items.length === 0 ? (
          <p>まだ集計データがありません。</p>
        ) : (
          <ul>
            {rankings.tracks.items.map((track, index) => (
              <li key={index}>
                <a href={track.track_url} target="_blank" rel="noreferrer">
                  {track.track_name}
                </a>{' '}
                - {track.artist_name} ({track.album_name})
              </li>
            ))}
          </ul>
        )}
      </section>
    </div>
  )
}
