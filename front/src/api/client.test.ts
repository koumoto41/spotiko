import { apiClient, ensureCsrfCookie } from './client'

describe('apiClient', () => {
  it('Sanctumのcookieベース認証向けに設定されている', () => {
    expect(apiClient.defaults.withCredentials).toBe(true)
    expect(apiClient.defaults.withXSRFToken).toBe(true)
    expect(apiClient.defaults.xsrfCookieName).toBe('XSRF-TOKEN')
    expect(apiClient.defaults.xsrfHeaderName).toBe('X-XSRF-TOKEN')
    expect(apiClient.defaults.headers.common['Accept']).toContain('application/json')
  })
})

describe('ensureCsrfCookie', () => {
  it('SanctumのCSRF cookieエンドポイントにリクエストする', async () => {
    const getSpy = jest.spyOn(apiClient, 'get').mockResolvedValueOnce({ data: undefined })

    await ensureCsrfCookie()

    expect(getSpy).toHaveBeenCalledWith('/sanctum/csrf-cookie')
    getSpy.mockRestore()
  })
})
