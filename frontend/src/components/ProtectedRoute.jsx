import { Navigate } from 'react-router-dom'
import { useAuth } from '../services/AuthContext'

export default function ProtectedRoute({ children, roles }) {
  const { user, loading } = useAuth()

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-brand-cream">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-dark mx-auto mb-4"></div>
          <p className="text-sm text-stone-500">Memuat...</p>
        </div>
      </div>
    )
  }

  if (!user) return <Navigate to="/login" replace />
  if (roles && !roles.includes(user.role)) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-brand-cream">
        <div className="text-center">
          <p className="text-4xl mb-4">403</p>
          <p className="text-sm text-stone-500">Anda tidak memiliki akses ke halaman ini.</p>
        </div>
      </div>
    )
  }

  return children
}
