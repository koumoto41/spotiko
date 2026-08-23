import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import * as authApi from '../api/auth'
import { AuthProvider, useAuth } from './AuthContext'

jest.mock('../api/auth')

const mockedAuthApi = authApi as jest.Mocked<typeof authApi>

const fakeUser = { id: 1, name: 'Test User', email: 'test@example.com' }

function TestConsumer() {
  const { user, isLoading, login, logout } = useAuth()

  if (isLoading) {
    return <p>loading</p>
  }

  return (
    <div>
      <p>{user ? `logged in as ${user.email}` : 'logged out'}</p>
      <button onClick={() => login('a@example.com', 'password')}>login</button>
      <button onClick={() => logout()}>logout</button>
    </div>
  )
}

describe('AuthProvider（認証コンテキスト）', () => {
  it('セッションが存在する場合、マウント時に現在のユーザーを取得する', async () => {
    mockedAuthApi.fetchCurrentUser.mockResolvedValue(fakeUser)

    render(
      <AuthProvider>
        <TestConsumer />
      </AuthProvider>
    )

    expect(screen.getByText('loading')).toBeInTheDocument()

    await waitFor(() => {
      expect(screen.getByText(`logged in as ${fakeUser.email}`)).toBeInTheDocument()
    })
  })

  it('セッションが無い場合は未ログイン状態になる', async () => {
    mockedAuthApi.fetchCurrentUser.mockRejectedValue(new Error('401'))

    render(
      <AuthProvider>
        <TestConsumer />
      </AuthProvider>
    )

    await waitFor(() => {
      expect(screen.getByText('logged out')).toBeInTheDocument()
    })
  })

  it('login()を呼ぶと現在のユーザーが更新される', async () => {
    mockedAuthApi.fetchCurrentUser.mockRejectedValue(new Error('401'))
    mockedAuthApi.login.mockResolvedValue(fakeUser)
    const user = userEvent.setup()

    render(
      <AuthProvider>
        <TestConsumer />
      </AuthProvider>
    )

    await waitFor(() => screen.getByText('logged out'))
    await user.click(screen.getByText('login'))

    await waitFor(() => {
      expect(screen.getByText(`logged in as ${fakeUser.email}`)).toBeInTheDocument()
    })
    expect(mockedAuthApi.login).toHaveBeenCalledWith('a@example.com', 'password')
  })

  it('logout()を呼ぶと現在のユーザーがクリアされる', async () => {
    mockedAuthApi.fetchCurrentUser.mockResolvedValue(fakeUser)
    mockedAuthApi.logout.mockResolvedValue(undefined)
    const user = userEvent.setup()

    render(
      <AuthProvider>
        <TestConsumer />
      </AuthProvider>
    )

    await waitFor(() => screen.getByText(`logged in as ${fakeUser.email}`))
    await user.click(screen.getByText('logout'))

    await waitFor(() => {
      expect(screen.getByText('logged out')).toBeInTheDocument()
    })
  })
})

describe('useAuth', () => {
  it('AuthProviderの外で使用すると例外を投げる', () => {
    // React logs its own error boundary noise for this; keep the test output clean.
    const spy = jest.spyOn(console, 'error').mockImplementation(() => {})

    expect(() => render(<TestConsumer />)).toThrow('useAuth must be used within an AuthProvider')

    spy.mockRestore()
  })
})
