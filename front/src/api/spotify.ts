import { apiClient } from './client'

export async function fetchSpotifyAuthorizeUrl(): Promise<string> {
  const { data } = await apiClient.get<{ url: string }>('/api/spotify/authorize-url')
  return data.url
}
