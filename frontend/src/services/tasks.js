import api from './api'

export const taskService = {
  // Obtener todas las tareas
  async getTasks(status = null) {
    try {
      const params = status ? { status } : {}
      const response = await api.get('/tasks', { params })
      return response.data
    } catch (error) {
      throw error.response?.data || error
    }
  },

  // Obtener una tarea específica
  async getTask(id) {
    try {
      const response = await api.get(`/tasks/${id}`)
      return response.data
    } catch (error) {
      throw error.response?.data || error
    }
  },

  // Crear nueva tarea
  async createTask(taskData) {
    try {
      const response = await api.post('/tasks', taskData)
      return response.data
    } catch (error) {
      throw error.response?.data || error
    }
  },

  // Actualizar tarea
  async updateTask(id, taskData) {
    try {
      const response = await api.put(`/tasks/${id}`, taskData)
      return response.data
    } catch (error) {
      throw error.response?.data || error
    }
  },

  // Eliminar tarea
  async deleteTask(id) {
    try {
      const response = await api.delete(`/tasks/${id}`)
      return response.data
    } catch (error) {
      throw error.response?.data || error
    }
  }
}
