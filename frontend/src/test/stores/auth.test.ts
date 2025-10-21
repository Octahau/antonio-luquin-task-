import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '@/stores/auth'

// Mock the auth service
vi.mock('@/services/auth', () => ({
  login: vi.fn(),
  register: vi.fn(),
  logout: vi.fn(),
  getUserProfile: vi.fn(),
}))

describe('Auth Store', () => {
  let store: any
  let pinia: any

  beforeEach(() => {
    pinia = createPinia()
    setActivePinia(pinia)
    store = useAuthStore()
    vi.clearAllMocks()
  })

  describe('initial state', () => {
    it('should have correct initial state', () => {
      expect(store.user).toBeNull()
      expect(store.token).toBeNull()
      expect(store.isAuthenticated).toBe(false)
      expect(store.loading).toBe(false)
    })
  })

  describe('login', () => {
    it('should login successfully', async () => {
      const { login } = await import('@/services/auth')
      const mockResponse = {
        user: {
          id: 1,
          name: 'Test User',
          email: 'test@example.com',
          roles: ['admin']
        },
        token: 'mock-token'
      }

      vi.mocked(login).mockResolvedValue(mockResponse)

      await store.login({
        email: 'test@example.com',
        password: 'password123'
      })

      expect(login).toHaveBeenCalledWith({
        email: 'test@example.com',
        password: 'password123'
      })

      expect(store.user).toEqual(mockResponse.user)
      expect(store.token).toBe('mock-token')
      expect(store.isAuthenticated).toBe(true)
      expect(store.loading).toBe(false)
    })

    it('should handle login errors', async () => {
      const { login } = await import('@/services/auth')
      const mockError = new Error('Invalid credentials')
      vi.mocked(login).mockRejectedValue(mockError)

      await expect(store.login({
        email: 'test@example.com',
        password: 'wrongpassword'
      })).rejects.toThrow('Invalid credentials')

      expect(store.user).toBeNull()
      expect(store.token).toBeNull()
      expect(store.isAuthenticated).toBe(false)
      expect(store.loading).toBe(false)
    })

    it('should set loading state during login', async () => {
      const { login } = await import('@/services/auth')
      vi.mocked(login).mockImplementation(() => new Promise(resolve => setTimeout(resolve, 100)))

      const loginPromise = store.login({
        email: 'test@example.com',
        password: 'password123'
      })

      expect(store.loading).toBe(true)

      await loginPromise
      expect(store.loading).toBe(false)
    })
  })

  describe('register', () => {
    it('should register successfully', async () => {
      const { register } = await import('@/services/auth')
      const mockResponse = {
        user: {
          id: 1,
          name: 'New User',
          email: 'new@example.com',
          roles: ['viewer']
        },
        token: 'mock-token'
      }

      vi.mocked(register).mockResolvedValue(mockResponse)

      await store.register({
        name: 'New User',
        email: 'new@example.com',
        password: 'password123',
        password_confirmation: 'password123'
      })

      expect(register).toHaveBeenCalledWith({
        name: 'New User',
        email: 'new@example.com',
        password: 'password123',
        password_confirmation: 'password123'
      })

      expect(store.user).toEqual(mockResponse.user)
      expect(store.token).toBe('mock-token')
      expect(store.isAuthenticated).toBe(true)
    })

    it('should handle registration errors', async () => {
      const { register } = await import('@/services/auth')
      const mockError = new Error('Email already taken')
      vi.mocked(register).mockRejectedValue(mockError)

      await expect(store.register({
        name: 'New User',
        email: 'existing@example.com',
        password: 'password123',
        password_confirmation: 'password123'
      })).rejects.toThrow('Email already taken')

      expect(store.user).toBeNull()
      expect(store.token).toBeNull()
      expect(store.isAuthenticated).toBe(false)
    })
  })

  describe('logout', () => {
    it('should logout successfully', async () => {
      // First login
      store.user = { id: 1, name: 'Test User', email: 'test@example.com', roles: ['admin'] }
      store.token = 'mock-token'
      store.isAuthenticated = true

      const { logout } = await import('@/services/auth')
      vi.mocked(logout).mockResolvedValue({ message: 'Success' })

      await store.logout()

      expect(logout).toHaveBeenCalled()
      expect(store.user).toBeNull()
      expect(store.token).toBeNull()
      expect(store.isAuthenticated).toBe(false)
    })

    it('should handle logout errors gracefully', async () => {
      store.user = { id: 1, name: 'Test User', email: 'test@example.com', roles: ['admin'] }
      store.token = 'mock-token'
      store.isAuthenticated = true

      const { logout } = await import('@/services/auth')
      vi.mocked(logout).mockRejectedValue(new Error('Network Error'))

      await store.logout()

      // Should still clear the store even on error
      expect(store.user).toBeNull()
      expect(store.token).toBeNull()
      expect(store.isAuthenticated).toBe(false)
    })
  })

  describe('getUserProfile', () => {
    it('should get user profile successfully', async () => {
      const { getUserProfile } = await import('@/services/auth')
      const mockResponse = {
        user: {
          id: 1,
          name: 'Test User',
          email: 'test@example.com',
          roles: ['admin']
        }
      }

      vi.mocked(getUserProfile).mockResolvedValue(mockResponse)

      await store.getUserProfile()

      expect(getUserProfile).toHaveBeenCalled()
      expect(store.user).toEqual(mockResponse.user)
    })

    it('should handle profile fetch errors', async () => {
      const { getUserProfile } = await import('@/services/auth')
      vi.mocked(getUserProfile).mockRejectedValue(new Error('Unauthorized'))

      await expect(store.getUserProfile()).rejects.toThrow('Unauthorized')
    })
  })

  describe('setUser', () => {
    it('should set user and update authentication state', () => {
      const user = {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        roles: ['admin']
      }

      store.setUser(user)

      expect(store.user).toEqual(user)
      expect(store.isAuthenticated).toBe(true)
    })

    it('should set user to null and update authentication state', () => {
      store.setUser(null)

      expect(store.user).toBeNull()
      expect(store.isAuthenticated).toBe(false)
    })
  })

  describe('setToken', () => {
    it('should set token', () => {
      store.setToken('new-token')

      expect(store.token).toBe('new-token')
    })

    it('should set token to null', () => {
      store.setToken(null)

      expect(store.token).toBeNull()
    })
  })

  describe('clearAuth', () => {
    it('should clear all authentication data', () => {
      store.user = { id: 1, name: 'Test User', email: 'test@example.com', roles: ['admin'] }
      store.token = 'mock-token'
      store.isAuthenticated = true

      store.clearAuth()

      expect(store.user).toBeNull()
      expect(store.token).toBeNull()
      expect(store.isAuthenticated).toBe(false)
    })
  })

  describe('hasRole', () => {
    it('should return true if user has the specified role', () => {
      store.user = {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        roles: ['admin', 'editor']
      }

      expect(store.hasRole('admin')).toBe(true)
      expect(store.hasRole('editor')).toBe(true)
    })

    it('should return false if user does not have the specified role', () => {
      store.user = {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        roles: ['viewer']
      }

      expect(store.hasRole('admin')).toBe(false)
      expect(store.hasRole('editor')).toBe(false)
    })

    it('should return false if user is null', () => {
      store.user = null

      expect(store.hasRole('admin')).toBe(false)
    })
  })

  describe('hasAnyRole', () => {
    it('should return true if user has any of the specified roles', () => {
      store.user = {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        roles: ['editor']
      }

      expect(store.hasAnyRole(['admin', 'editor'])).toBe(true)
    })

    it('should return false if user has none of the specified roles', () => {
      store.user = {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        roles: ['viewer']
      }

      expect(store.hasAnyRole(['admin', 'editor'])).toBe(false)
    })

    it('should return false if user is null', () => {
      store.user = null

      expect(store.hasAnyRole(['admin', 'editor'])).toBe(false)
    })
  })

  describe('isAdmin', () => {
    it('should return true if user is admin', () => {
      store.user = {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        roles: ['admin']
      }

      expect(store.isAdmin).toBe(true)
    })

    it('should return false if user is not admin', () => {
      store.user = {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        roles: ['editor']
      }

      expect(store.isAdmin).toBe(false)
    })
  })

  describe('isEditor', () => {
    it('should return true if user is editor', () => {
      store.user = {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        roles: ['editor']
      }

      expect(store.isEditor).toBe(true)
    })

    it('should return false if user is not editor', () => {
      store.user = {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        roles: ['viewer']
      }

      expect(store.isEditor).toBe(false)
    })
  })
})
