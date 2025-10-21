import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { authService } from '../services/auth'

export const useAuthStore = defineStore('auth', () => {
  // Estado
  const user = ref(null)
  const token = ref(null)
  const isLoading = ref(false)

  // Getters
  const isAuthenticated = computed(() => !!token.value)
  const userRoles = computed(() => user.value?.roles || [])
  const isAdmin = computed(() => userRoles.value.includes('admin'))
  const isEditor = computed(() => userRoles.value.includes('editor'))
  const isViewer = computed(() => userRoles.value.includes('viewer'))

  // Actions
  async function login(credentials) {
    isLoading.value = true
    try {
      const { user: userData, token: tokenData } = await authService.login(credentials)
      user.value = userData
      token.value = tokenData
      return { user: userData, token: tokenData }
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  async function register(userData) {
    isLoading.value = true
    try {
      const { user: userResponse, token: tokenData } = await authService.register(userData)
      user.value = userResponse
      token.value = tokenData
      return { user: userResponse, token: tokenData }
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  async function logout() {
    isLoading.value = true
    try {
      await authService.logout()
    } finally {
      user.value = null
      token.value = null
      isLoading.value = false
    }
  }

  async function fetchUser() {
    try {
      const userData = await authService.getCurrentUser()
      user.value = userData
      return userData
    } catch (error) {
      throw error
    }
  }

  async function initializeAuth() {
    const storedToken = authService.getToken()
    const storedUser = authService.getStoredUser()
    
    if (storedToken && storedUser) {
      token.value = storedToken
      user.value = storedUser
    }
  }

  function canEdit(taskUserId) {
    return isAdmin.value || (isEditor.value && user.value?.id === taskUserId)
  }

  function canDelete(taskUserId) {
    return isAdmin.value || (isEditor.value && user.value?.id === taskUserId)
  }

  return {
    // Estado
    user,
    token,
    isLoading,
    
    // Getters
    isAuthenticated,
    userRoles,
    isAdmin,
    isEditor,
    isViewer,
    
    // Actions
    login,
    register,
    logout,
    fetchUser,
    initializeAuth,
    canEdit,
    canDelete
  }
})
