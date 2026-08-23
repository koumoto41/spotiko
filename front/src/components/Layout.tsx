import type { ReactNode } from 'react'
import { Link, useNavigate } from 'react-router-dom'
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
        <nav>
          <Link to="/">ランキング</Link>
          <Link to="/cms/stock">ストック</Link>
          <Link to="/spotify/connect">Spotify連携</Link>
          <Link to="/aggregate">集計</Link>
        </nav>
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
