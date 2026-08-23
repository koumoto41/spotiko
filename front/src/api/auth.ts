import { apiClient, ensureCsrfCookie } from './client'
import type { User } from './types'

export async function login(email: string, password: string): Promise<User> {
  await ensureCsrfCookie()
  const { data } = await apiClient.post<{ data: User }>('/api/login', { email, password })
  return data.data
}

export async function logout(): Promise<void> {
  await apiClient.post('/api/logout')
}

export async function fetchCurrentUser(): Promise<User> {
  const { data } = await apiClient.get<{ data: User }>('/api/user')
  return data.data
}
