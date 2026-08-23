import type { ReactNode } from 'react'
import { Link, NavLink, useNavigate } from 'react-router-dom'
import { useAuth } from '../auth/AuthContext'

export function Layout({ children }: { children: ReactNode }) {
  const { user, logout } = useAuth()
  const navigate = useNavigate()

  async function handleLogout() {
    await logout()
    navigate('/login')
  }

  return (
    <div className="layout">
      <header className="layout-header">
        <div className="layout-brand-group">
          <Link to="/" className="layout-brand">
            Spotiko
          </Link>
          <nav>
            <NavLink to="/" end>
              ランキング
            </NavLink>
            <NavLink to="/cms/stock">ストック</NavLink>
            <NavLink to="/spotify/connect">Spotify連携</NavLink>
            <NavLink to="/aggregate">集計</NavLink>
          </nav>
        </div>
        {user && (
          <div className="layout-user">
            <span>{user.name}</span>
            <button onClick={handleLogout}>ログアウト</button>
          </div>
        )}
      </header>
      <main>{children}</main>
    </div>
  )
}
