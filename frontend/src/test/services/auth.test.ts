import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { login, register, logout, getUserProfile } from '@/services/auth'

// Mock axios
const mockAxios = {
  post: vi.fn(),
  get: vi.fn(),
  defaults: {
    headers: {
      common: {}
    }
  }
}

vi.mock('axios', () => ({
  default: mockAxios,
  create: () => mockAxios
}))

describe('Auth Service', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    localStorage.clear()
  })

  afterEach(() => {
    vi.restoreAllMocks()
  })

  describe('login', () => {
    it('should login successfully with valid credentials', async () => {
      const mockResponse = {
        data: {
          user: {
            id: 1,
            name: 'Test User',
            email: 'test@example.com',
            roles: ['admin']
          },
          token: 'mock-token'
        }
      }

      mockAxios.post.mockResolvedValue(mockResponse)

      const result = await login({
        email: 'test@example.com',
        password: 'password123'
      })

      expect(mockAxios.post).toHaveBeenCalledWith('/api/login', {
        email: 'test@example.com',
        password: 'password123'
      })

      expect(result).toEqual(mockResponse.data)
      expect(localStorage.getItem('token')).toBe('mock-token')
      expect(mockAxios.defaults.headers.common['Authorization']).toBe('Bearer mock-token')
    })

    it('should handle login errors', async () => {
      const mockError = new Error('Invalid credentials')
      mockAxios.post.mockRejectedValue(mockError)

      await expect(login({
        email: 'test@example.com',
        password: 'wrongpassword'
      })).rejects.toThrow('Invalid credentials')
    })

    it('should handle network errors', async () => {
      mockAxios.post.mockRejectedValue(new Error('Network Error'))

      await expect(login({
        email: 'test@example.com',
        password: 'password123'
      })).rejects.toThrow('Network Error')
    })
  })

  describe('register', () => {
    it('should register successfully with valid data', async () => {
      const mockResponse = {
        data: {
          user: {
            id: 1,
            name: 'New User',
            email: 'new@example.com',
            roles: ['viewer']
          },
          token: 'mock-token'
        }
      }

      mockAxios.post.mockResolvedValue(mockResponse)

      const result = await register({
        name: 'New User',
        email: 'new@example.com',
        password: 'password123',
        password_confirmation: 'password123'
      })

      expect(mockAxios.post).toHaveBeenCalledWith('/api/register', {
        name: 'New User',
        email: 'new@example.com',
        password: 'password123',
        password_confirmation: 'password123'
      })

      expect(result).toEqual(mockResponse.data)
      expect(localStorage.getItem('token')).toBe('mock-token')
    })

    it('should handle registration validation errors', async () => {
      const mockError = {
        response: {
          status: 422,
          data: {
            errors: {
              email: ['The email has already been taken.']
            }
          }
        }
      }

      mockAxios.post.mockRejectedValue(mockError)

      await expect(register({
        name: 'New User',
        email: 'existing@example.com',
        password: 'password123',
        password_confirmation: 'password123'
      })).rejects.toThrow()
    })
  })

  describe('logout', () => {
    it('should logout successfully', async () => {
      localStorage.setItem('token', 'mock-token')
      mockAxios.defaults.headers.common['Authorization'] = 'Bearer mock-token'

      const mockResponse = {
        data: {
          message: 'Sesión cerrada correctamente'
        }
      }

      mockAxios.post.mockResolvedValue(mockResponse)

      const result = await logout()

      expect(mockAxios.post).toHaveBeenCalledWith('/api/logout')
      expect(result).toEqual(mockResponse.data)
      expect(localStorage.getItem('token')).toBeNull()
      expect(mockAxios.defaults.headers.common['Authorization']).toBeUndefined()
    })

    it('should handle logout errors gracefully', async () => {
      localStorage.setItem('token', 'mock-token')
      mockAxios.post.mockRejectedValue(new Error('Network Error'))

      await expect(logout()).rejects.toThrow('Network Error')
      
      // Should still clear local storage even on error
      expect(localStorage.getItem('token')).toBeNull()
    })
  })

  describe('getUserProfile', () => {
    it('should get user profile successfully', async () => {
      const mockResponse = {
        data: {
          user: {
            id: 1,
            name: 'Test User',
            email: 'test@example.com',
            roles: ['admin']
          }
        }
      }

      mockAxios.get.mockResolvedValue(mockResponse)

      const result = await getUserProfile()

      expect(mockAxios.get).toHaveBeenCalledWith('/api/user')
      expect(result).toEqual(mockResponse.data)
    })

    it('should handle profile fetch errors', async () => {
      mockAxios.get.mockRejectedValue(new Error('Unauthorized'))

      await expect(getUserProfile()).rejects.toThrow('Unauthorized')
    })
  })

  describe('token management', () => {
    it('should set token in localStorage and axios headers on successful login', async () => {
      const mockResponse = {
        data: {
          user: { id: 1, name: 'Test', email: 'test@example.com', roles: [] },
          token: 'new-token'
        }
      }

      mockAxios.post.mockResolvedValue(mockResponse)

      await login({ email: 'test@example.com', password: 'password' })

      expect(localStorage.getItem('token')).toBe('new-token')
      expect(mockAxios.defaults.headers.common['Authorization']).toBe('Bearer new-token')
    })

    it('should clear token from localStorage and axios headers on logout', async () => {
      localStorage.setItem('token', 'existing-token')
      mockAxios.defaults.headers.common['Authorization'] = 'Bearer existing-token'

      const mockResponse = { data: { message: 'Success' } }
      mockAxios.post.mockResolvedValue(mockResponse)

      await logout()

      expect(localStorage.getItem('token')).toBeNull()
      expect(mockAxios.defaults.headers.common['Authorization']).toBeUndefined()
    })
  })

  describe('error handling', () => {
    it('should handle 401 unauthorized errors', async () => {
      const mockError = {
        response: {
          status: 401,
          data: { message: 'Unauthorized' }
        }
      }

      mockAxios.post.mockRejectedValue(mockError)

      await expect(login({
        email: 'test@example.com',
        password: 'wrongpassword'
      })).rejects.toThrow()
    })

    it('should handle 422 validation errors', async () => {
      const mockError = {
        response: {
          status: 422,
          data: {
            errors: {
              email: ['The email field is required.'],
              password: ['The password field is required.']
            }
          }
        }
      }

      mockAxios.post.mockRejectedValue(mockError)

      await expect(login({
        email: '',
        password: ''
      })).rejects.toThrow()
    })

    it('should handle 500 server errors', async () => {
      const mockError = {
        response: {
          status: 500,
          data: { message: 'Internal Server Error' }
        }
      }

      mockAxios.post.mockRejectedValue(mockError)

      await expect(login({
        email: 'test@example.com',
        password: 'password123'
      })).rejects.toThrow()
    })
  })
})
