import { useState } from 'react'
import { triggerArtistAggregation, triggerTrackAggregation } from '../api/aggregate'

export function AggregatePage() {
  const [artistMessage, setArtistMessage] = useState<string | null>(null)
  const [trackMessage, setTrackMessage] = useState<string | null>(null)
  const [isArtistRunning, setIsArtistRunning] = useState(false)
  const [isTrackRunning, setIsTrackRunning] = useState(false)

  async function runArtistAggregation() {
    setIsArtistRunning(true)
    try {
      setArtistMessage(await triggerArtistAggregation())
    } catch {
      setArtistMessage('集計に失敗しました。')
    } finally {
      setIsArtistRunning(false)
    }
  }

  async function runTrackAggregation() {
    setIsTrackRunning(true)
    try {
      setTrackMessage(await triggerTrackAggregation())
    } catch {
      setTrackMessage('集計に失敗しました。')
    } finally {
      setIsTrackRunning(false)
    }
  }

  return (
    <div className="page page-narrow">
      <h1>集計</h1>

      <section>
        <h2>アーティスト集計</h2>
        <button onClick={runArtistAggregation} disabled={isArtistRunning}>
          {isArtistRunning ? '実行中...' : '集計を実行'}
        </button>
        {artistMessage && <p>{artistMessage}</p>}
      </section>

      <section>
        <h2>曲目集計</h2>
        <button onClick={runTrackAggregation} disabled={isTrackRunning}>
          {isTrackRunning ? '実行中...' : '集計を実行'}
        </button>
        {trackMessage && <p>{trackMessage}</p>}
      </section>
    </div>
  )
}
