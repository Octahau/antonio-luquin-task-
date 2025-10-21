import api from './api'
import axios from 'axios'

export const authService = {
  // Obtener CSRF cookie
  async getCsrfCookie() {
    try {
      // Usar axios directamente para evitar conflictos con la instancia api
      await axios.get('http://localhost:8000/sanctum/csrf-cookie', { 
        withCredentials: true,
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
    } catch (error) {
      console.warn('No se pudo obtener CSRF cookie:', error)
      // No lanzar error para no bloquear el flujo
    }
  },

  // Login
  async login(credentials) {
    try {
      const response = await api.post('/login', credentials)
      const { user, token } = response.data
      
      // Guardar token y datos del usuario en localStorage
      localStorage.setItem('auth_token', token)
      localStorage.setItem('user', JSON.stringify(user))
      
      return { user, token }
    } catch (error) {
      console.error('Error en login:', error.response || error)
      throw error.response?.data || error
    }
  },

  // Register
  async register(userData) {
    try {
      const response = await api.post('/register', userData)
      const { user, token } = response.data
      
      // Guardar token y datos del usuario en localStorage
      localStorage.setItem('auth_token', token)
      localStorage.setItem('user', JSON.stringify(user))
      
      return { user, token }
    } catch (error) {
      throw error.response?.data || error
    }
  },

  // Logout
  async logout() {
    try {
      await api.post('/logout')
    } catch (error) {
      console.error('Error during logout:', error)
    } finally {
      // Limpiar localStorage independientemente del resultado de la API
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
    }
  },

  // Obtener datos del usuario actual
  async getCurrentUser() {
    try {
      const response = await api.get('/user')
      return response.data.user
    } catch (error) {
      throw error.response?.data || error
    }
  },

  // Obtener lista de usuarios (solo para admin)
  async getUsers() {
    try {
      const response = await api.get('/users')
      return response.data
    } catch (error) {
      throw error.response?.data || error
    }
  },

  // Verificar si el usuario está autenticado
  isAuthenticated() {
    return !!localStorage.getItem('auth_token')
  },

  // Obtener token actual
  getToken() {
    return localStorage.getItem('auth_token')
  },

  // Obtener usuario actual desde localStorage
  getStoredUser() {
    const userStr = localStorage.getItem('user')
    return userStr ? JSON.parse(userStr) : null
  }
}
