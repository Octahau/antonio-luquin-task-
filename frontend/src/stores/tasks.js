import { ref } from 'vue'
import { defineStore } from 'pinia'
import { taskService } from '../services/tasks'

export const useTasksStore = defineStore('tasks', () => {
  // Estado
  const tasks = ref([])
  const isLoading = ref(false)
  const selectedStatus = ref(null)

  // Actions
  async function fetchTasks(status = null) {
    isLoading.value = true
    try {
      const data = await taskService.getTasks(status)
      tasks.value = data
      selectedStatus.value = status
      return data
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  async function createTask(taskData) {
    try {
      const newTask = await taskService.createTask(taskData)
      tasks.value.push(newTask)
      return newTask
    } catch (error) {
      throw error
    }
  }

  async function updateTask(id, taskData) {
    try {
      const updatedTask = await taskService.updateTask(id, taskData)
      const index = tasks.value.findIndex(task => task.id === id)
      if (index !== -1) {
        tasks.value[index] = updatedTask
      }
      return updatedTask
    } catch (error) {
      throw error
    }
  }

  async function deleteTask(id) {
    try {
      await taskService.deleteTask(id)
      tasks.value = tasks.value.filter(task => task.id !== id)
      return true
    } catch (error) {
      throw error
    }
  }

  function getTasksByStatus(status) {
    return tasks.value.filter(task => task.status === status)
  }

  return {
    // Estado
    tasks,
    isLoading,
    selectedStatus,
    
    // Actions
    fetchTasks,
    createTask,
    updateTask,
    deleteTask,
    getTasksByStatus
  }
})
