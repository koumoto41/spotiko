import { apiClient } from './client'

export async function triggerArtistAggregation(): Promise<string> {
  const { data } = await apiClient.post<{ message: string }>('/api/aggregate/artist')
  return data.message
}

export async function triggerTrackAggregation(): Promise<string> {
  const { data } = await apiClient.post<{ message: string }>('/api/aggregate/track')
  return data.message
}
