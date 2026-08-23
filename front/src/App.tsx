import { Route, Routes } from 'react-router-dom'
import { AuthProvider } from './auth/AuthContext'
import { RequireAuth } from './auth/RequireAuth'
import { Layout } from './components/Layout'
import { AggregatePage } from './pages/AggregatePage'
import { LoginPage } from './pages/LoginPage'
import { RankingsPage } from './pages/RankingsPage'
import { SpotifyCallbackPage } from './pages/SpotifyCallbackPage'
import { SpotifyConnectPage } from './pages/SpotifyConnectPage'
import { StockPage } from './pages/StockPage'

export function App() {
  return (
    <AuthProvider>
      <Routes>
        <Route path="/login" element={<LoginPage />} />
        <Route
          path="/*"
          element={
            <RequireAuth>
              <Layout>
                <Routes>
                  <Route path="/" element={<RankingsPage />} />
                  <Route path="/cms/stock" element={<StockPage />} />
                  <Route path="/spotify/connect" element={<SpotifyConnectPage />} />
                  <Route path="/spotify/callback" element={<SpotifyCallbackPage />} />
                  <Route path="/aggregate" element={<AggregatePage />} />
                </Routes>
              </Layout>
            </RequireAuth>
          }
        />
      </Routes>
    </AuthProvider>
  )
}
