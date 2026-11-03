import { BrowserRouter, Routes, Route } from 'react-router-dom'
import { AuthProvider } from './services/AuthContext'
import Welcome from './pages/Welcome'
import Login from './pages/Login'
import Register from './pages/Register'
import Dashboard from './pages/Dashboard'
import Payment from './pages/Payment'
import Nota from './pages/Nota'
import NotaAdmin from './pages/NotaAdmin'
import UserManagement from './pages/UserManagement'
import ChatInbox from './pages/ChatInbox'
import ProtectedRoute from './components/ProtectedRoute'

export default function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <Routes>
          {/* Public */}
          <Route path="/" element={<Welcome />} />
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          <Route path="/payment/:id" element={<Payment />} />
          <Route path="/nota/:id" element={<Nota />} />

          {/* Auth Required */}
          <Route path="/dashboard" element={<ProtectedRoute><Dashboard /></ProtectedRoute>} />
          <Route path="/admin/nota/:id" element={<ProtectedRoute><NotaAdmin /></ProtectedRoute>} />
          <Route path="/admin/users" element={<ProtectedRoute roles={['super_admin']}><UserManagement /></ProtectedRoute>} />
          <Route path="/admin/chat" element={<ProtectedRoute roles={['admin','super_admin']}><ChatInbox /></ProtectedRoute>} />
        </Routes>
      </BrowserRouter>
    </AuthProvider>
  )
}
