import { apiClient } from './client'
import type { Rankings } from './types'

export async function fetchRankings(): Promise<Rankings> {
  const { data } = await apiClient.get<Rankings>('/api/rankings')
  return data
}
