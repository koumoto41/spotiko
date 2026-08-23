import { type FormEvent, useEffect, useState } from 'react'
import { fetchStock, registerAlbum, searchSpotifyAlbums } from '../api/stock'
import type { MyAlbumItem, SpotifySearchAlbumItem } from '../api/types'

export function StockPage() {
  const [items, setItems] = useState<MyAlbumItem[]>([])
  const [count, setCount] = useState(0)
  const [listQuery, setListQuery] = useState('')
  const [listType, setListType] = useState<'album' | 'artist'>('album')

  const [searchQuery, setSearchQuery] = useState('')
  const [searchResults, setSearchResults] = useState<SpotifySearchAlbumItem[]>([])
  const [isSearching, setIsSearching] = useState(false)
  const [registeringId, setRegisteringId] = useState<string | null>(null)
  const [message, setMessage] = useState<string | null>(null)

  const loadStock = () => {
    fetchStock(listQuery, listType, 1).then((result) => {
      setItems(result.items)
      setCount(result.count)
    })
  }

  useEffect(loadStock, [listQuery, listType])

  async function handleSearch(event: FormEvent) {
    event.preventDefault()
    if (!searchQuery) {
      return
    }
    setIsSearching(true)
    try {
      setSearchResults(await searchSpotifyAlbums(searchQuery, 1))
    } finally {
      setIsSearching(false)
    }
  }

  async function handleRegister(item: SpotifySearchAlbumItem) {
    setRegisteringId(item.album_spotify_id)
    setMessage(null)
    try {
      const result = await registerAlbum(item.artist_spotify_id, item.album_spotify_id)
      setMessage(result.message)
      setSearchResults((prev) =>
        prev.map((row) =>
          row.album_spotify_id === item.album_spotify_id ? { ...row, is_register: true } : row
        )
      )
      loadStock()
    } finally {
      setRegisteringId(null)
    }
  }

  return (
    <div className="page">
      <h1>ストック</h1>

      <section>
        <h2>Spotifyから検索して登録</h2>
        <form onSubmit={handleSearch}>
          <input
            type="text"
            placeholder="アルバムを検索"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />
          <button type="submit" disabled={isSearching}>
            {isSearching ? '検索中...' : '検索'}
          </button>
        </form>
        {message && <p>{message}</p>}
        <ul>
          {searchResults.map((item) => (
            <li key={item.album_spotify_id}>
              {item.image && <img src={item.image} alt={item.album_name} width={48} />}
              {item.album_name} - {item.artist_name} ({item.release_date})
              {item.is_register ? (
                <span> 登録済</span>
              ) : (
                <button
                  onClick={() => handleRegister(item)}
                  disabled={registeringId === item.album_spotify_id}
                >
                  {registeringId === item.album_spotify_id ? '登録中...' : '登録'}
                </button>
              )}
            </li>
          ))}
        </ul>
      </section>

      <section>
        <h2>登録済みアルバム一覧（{count}件）</h2>
        <div>
          <input
            type="text"
            placeholder="絞り込み"
            value={listQuery}
            onChange={(e) => setListQuery(e.target.value)}
          />
          <select value={listType} onChange={(e) => setListType(e.target.value as 'album' | 'artist')}>
            <option value="album">アルバム名</option>
            <option value="artist">アーティスト名</option>
          </select>
        </div>
        <ul>
          {items.map((item, index) => (
            <li key={index}>
              {item.img_64 && <img src={item.img_64} alt={item.album_name} width={48} />}
              {item.album_name} - {item.artist_name}
            </li>
          ))}
        </ul>
      </section>
    </div>
  )
}
