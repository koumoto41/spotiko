import type { ReactElement } from 'react'
import { Navigate } from 'react-router-dom'
import { useAuth } from './AuthContext'

export function RequireAuth({ children }: { children: ReactElement }): ReactElement {
  const { user, isLoading } = useAuth()

  if (isLoading) {
    return <p>読み込み中...</p>
  }

  if (!user) {
    return <Navigate to="/login" replace />
  }

  return children
}
