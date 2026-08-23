import { apiClient } from './client'
import type { RegisterResult, SpotifySearchAlbumItem, StockList } from './types'

export async function fetchStock(query: string, type: string, page: number): Promise<StockList> {
  const { data } = await apiClient.get<StockList>('/api/stock', { params: { query, type, page } })
  return data
}

export async function searchSpotifyAlbums(query: string, page: number): Promise<SpotifySearchAlbumItem[]> {
  const { data } = await apiClient.get<{ items: SpotifySearchAlbumItem[] }>('/api/stock/search', {
    params: { query, page },
  })
  return data.items
}

export async function registerAlbum(artistId: string, albumId: string): Promise<RegisterResult> {
  const { data } = await apiClient.post<RegisterResult>('/api/stock', {
    artist_id: artistId,
    album_id: albumId,
  })
  return data
}
