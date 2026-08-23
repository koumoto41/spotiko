import { render, screen } from '@testing-library/react'
import { MemoryRouter, Route, Routes } from 'react-router-dom'
import { useAuth } from './AuthContext'
import { RequireAuth } from './RequireAuth'

jest.mock('./AuthContext')

const mockedUseAuth = useAuth as jest.Mock

function renderWithRouter() {
  return render(
    <MemoryRouter
      initialEntries={['/protected']}
      future={{ v7_startTransition: true, v7_relativeSplatPath: true }}
    >
      <Routes>
        <Route path="/login" element={<p>login page</p>} />
        <Route
          path="/protected"
          element={
            <RequireAuth>
              <p>secret content</p>
            </RequireAuth>
          }
        />
      </Routes>
    </MemoryRouter>
  )
}

describe('RequireAuth（認証ガード）', () => {
  it('認証状態の確認中はローディング表示になる', () => {
    mockedUseAuth.mockReturnValue({ user: null, isLoading: true, login: jest.fn(), logout: jest.fn() })

    renderWithRouter()

    expect(screen.getByText('読み込み中...')).toBeInTheDocument()
  })

  it('認証済みユーザーが居ない場合は/loginにリダイレクトする', () => {
    mockedUseAuth.mockReturnValue({ user: null, isLoading: false, login: jest.fn(), logout: jest.fn() })

    renderWithRouter()

    expect(screen.getByText('login page')).toBeInTheDocument()
  })

  it('認証済みユーザーが居る場合は保護されたコンテンツを表示する', () => {
    mockedUseAuth.mockReturnValue({
      user: { id: 1, name: 'Test', email: 'test@example.com' },
      isLoading: false,
      login: jest.fn(),
      logout: jest.fn(),
    })

    renderWithRouter()

    expect(screen.getByText('secret content')).toBeInTheDocument()
  })
})
