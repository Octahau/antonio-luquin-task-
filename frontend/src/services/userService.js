import api from './api'

export const userService = {
  // Obtener todos los usuarios
  async getUsers() {
    try {
      const response = await api.get('/users')
      return response.data
    } catch (error) {
      throw error
    }
  },

  // Obtener un usuario específico
  async getUser(id) {
    try {
      const response = await api.get(`/users/${id}`)
      return response.data
    } catch (error) {
      throw error
    }
  },

  // Actualizar un usuario
  async updateUser(id, userData) {
    try {
      const response = await api.put(`/users/${id}`, userData)
      return response.data
    } catch (error) {
      throw error
    }
  },

  // Eliminar un usuario
  async deleteUser(id) {
    try {
      const response = await api.delete(`/users/${id}`)
      return response.data
    } catch (error) {
      throw error
    }
  }
}
