import axios from 'axios'

export const apiClient = axios.create({
  baseURL: '/',
  withCredentials: true,
  withXSRFToken: true,
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',
  headers: {
    Accept: 'application/json',
  },
})

export async function ensureCsrfCookie(): Promise<void> {
  await apiClient.get('/sanctum/csrf-cookie')
}
