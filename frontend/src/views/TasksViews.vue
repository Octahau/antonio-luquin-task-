<template>
    <div class="tasks-container">
      <header class="tasks-header">
        <h1>Mis Tareas</h1>
        <div class="header-actions">
          <select v-model="selectedStatus" @change="filterTasks" class="status-filter">
            <option value="">Todas las tareas</option>
            <option value="pending">Pendientes</option>
            <option value="in_progress">En Progreso</option>
            <option value="completed">Completadas</option>
          </select>
          <button @click="showCreateForm = true" class="btn-primary" v-if="canCreate">
            <i class="pi pi-plus"></i>
            <span>Nueva Tarea</span>
          </button>
          <button @click="logout" class="btn-secondary">
            <i class="pi pi-sign-out"></i>
            <span>Cerrar Sesión</span>
          </button>
        </div>
      </header>
  
      <div v-if="isLoading" class="loading">
        Cargando tareas...
      </div>
  
      <div v-else-if="tasks.length === 0" class="empty-state">
        <p>No hay tareas disponibles</p>
      </div>
  
      <div v-else class="tasks-grid">
        <TaskCard 
          v-for="task in tasks" 
          :key="task.id" 
          :task="task"
          :user="authStore.user"
          @view="viewTask"
          @edit="editTask"
          @delete="deleteTask"
        />
      </div>
  
      <!-- Modal para ver tarea -->
      <TaskModal 
        v-if="showModal && selectedTask"
        :task="selectedTask"
        @close="closeModal"
      />
  
      <!-- Modal para crear/editar -->
      <TaskFormModal 
        v-if="showCreateForm || editingTask"
        :task="editingTask || undefined"
        @close="closeFormModal"
        @save="handleSaveTask"
      />

      <!-- Modal de confirmación para eliminar -->
      <ConfirmModal
        v-if="showConfirmModal"
        title="Confirmar eliminación"
        message="¿Estás seguro de que quieres eliminar esta tarea?"
        :task-title="taskToDelete?.title"
        confirm-text="Eliminar"
        :is-loading="isDeleting"
        @confirm="confirmDelete"
        @cancel="cancelDelete"
      />
    </div>
  </template>
  
<script lang="ts">
/* eslint-disable @typescript-eslint/no-explicit-any */
/* eslint-disable @typescript-eslint/ban-ts-comment */
// @ts-nocheck
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useTasksStore } from '../stores/tasks'
import { useAuthStore } from '../stores/auth'
import { useToast } from '../composables/useToast'
  import TaskCard from '../components/TaskCard.vue'
  import TaskModal from '../components/TaskModal.vue'
  import TaskFormModal from '../components/TaskFormModal.vue'
  import ConfirmModal from '../components/ConfirmModal.vue'

  interface Task {
    id: number
    title: string
    description?: string
    status: string
    due_date?: string
    user_id?: number
    [key: string]: any
  }
  
  export default {
    name: 'TasksView',
    components: {
      TaskCard,
      TaskModal,
      TaskFormModal,
      ConfirmModal
    },
    setup() {
      const router = useRouter()
      const tasksStore = useTasksStore() as any
      const authStore = useAuthStore()
      const { success, error } = useToast() as any
      
      const selectedStatus = ref('')
      const showModal = ref(false)
      const showCreateForm = ref(false)
      const selectedTask = ref<Task | null>(null)
      const editingTask = ref<Task | null>(null)
      const showConfirmModal = ref(false)
      const taskToDelete = ref<Task | null>(null)
      const isDeleting = ref(false)
  
      const tasks = computed(() => tasksStore.tasks)
      const isLoading = computed(() => tasksStore.isLoading)
      const canCreate = computed(() => authStore.isAdmin || authStore.isEditor)
  
      const filterTasks = async () => {
        await tasksStore.fetchTasks(selectedStatus.value || null)
      }
  
      const viewTask = (task: Task) => {
        selectedTask.value = task
        showModal.value = true
      }
  
      const editTask = (task: Task) => {
        editingTask.value = task
      }
  
      const deleteTask = (task: Task) => {
        taskToDelete.value = task
        showConfirmModal.value = true
      }

      const confirmDelete = async () => {
        if (!taskToDelete.value) return
        
        isDeleting.value = true
        try {
          await tasksStore.deleteTask(taskToDelete.value.id)
          success('Tarea eliminada', 'La tarea se ha eliminado correctamente')
          showConfirmModal.value = false
          taskToDelete.value = null
        } catch (err: any) {
          console.error('Error al eliminar tarea:', err)
          
          // Manejar errores específicos de permisos
          if (err.response?.status === 403 || err.status === 403) {
            const backendMessage = err.response?.data?.message || err.message
            if (authStore.isViewer) {
              error('Sin permisos', backendMessage || 'Como usuario viewer, no tienes permisos para eliminar tareas. Solo puedes verlas.')
            } else {
              error('Sin permisos', backendMessage || 'No tienes permisos para eliminar esta tarea. Solo puedes eliminar tus propias tareas.')
            }
          } else if (err.response?.data?.message || err.message) {
            error('Error al eliminar', err.response?.data?.message || err.message)
          } else {
            error('Error al eliminar', 'No se pudo eliminar la tarea. Inténtalo de nuevo.')
          }
        } finally {
          isDeleting.value = false
        }
      }

      const cancelDelete = () => {
        showConfirmModal.value = false
        taskToDelete.value = null
      }
  
      const closeModal = () => {
        showModal.value = false
        selectedTask.value = null
      }
  
      const closeFormModal = () => {
        showCreateForm.value = false
        editingTask.value = null
      }
  
      const handleSaveTask = async (taskData: any) => {
        try {
          if (editingTask.value) {
            await tasksStore.updateTask(editingTask.value.id, taskData)
            success('Tarea actualizada', 'La tarea se ha actualizado correctamente')
          } else {
            await tasksStore.createTask(taskData)
            success('Tarea creada', 'La nueva tarea se ha creado exitosamente')
          }
          
          await filterTasks()
          closeFormModal()
        } catch (err: any) {
          console.error('Error al guardar tarea:', err)
          
          // Manejar errores específicos de permisos
          if (err.response?.status === 403 || err.status === 403) {
            const backendMessage = err.response?.data?.message || err.message
            if (authStore.isViewer) {
              if (editingTask.value) {
                error('Sin permisos', backendMessage || 'Como usuario viewer, no tienes permisos para editar tareas. Solo puedes verlas.')
              } else {
                error('Sin permisos', backendMessage || 'Como usuario viewer, no tienes permisos para crear tareas. Solo puedes verlas.')
              }
            } else {
              if (editingTask.value) {
                error('Sin permisos', backendMessage || 'No tienes permisos para editar esta tarea. Solo puedes editar tus propias tareas.')
              } else {
                error('Sin permisos', backendMessage || 'No tienes permisos para crear tareas. Contacta al administrador.')
              }
            }
          } else if (err.response?.data?.message || err.message) {
            error('Error al guardar', err.response?.data?.message || err.message)
          } else {
            error('Error al guardar', 'No se pudo guardar la tarea. Inténtalo de nuevo.')
          }
        }
      }
  
      const logout = async () => {
        await authStore.logout()
        router.push('/login')
      }
  
      onMounted(async () => {
        await filterTasks()
      })
  
      return {
        tasks,
        isLoading,
        selectedStatus,
        showModal,
        showCreateForm,
        selectedTask,
        editingTask,
        showConfirmModal,
        taskToDelete,
        isDeleting,
        canCreate,
        authStore,
        filterTasks,
        viewTask,
        editTask,
        deleteTask,
        confirmDelete,
        cancelDelete,
        closeModal,
        closeFormModal,
        handleSaveTask,
        logout
      }
    }
  }
  </script>
  
  <style scoped>
  .tasks-container {
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
    min-height: 100vh;
    background: transparent;
    width: 100%;
  }
  
  .tasks-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 1rem;
    box-shadow: 
      0 25px 50px -12px rgba(0, 0, 0, 0.25),
      0 10px 25px -5px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
  }
  
  .tasks-header h1 {
    color: var(--gray-900);
    font-size: 2.25rem;
    font-weight: 700;
    margin: 0;
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--gradient-middle) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
  
  .header-actions {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    flex-wrap: wrap;
  }
  
  .status-filter {
    padding: 0.875rem 1.125rem;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 0.75rem;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    color: var(--gray-800);
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 
      0 4px 6px -1px rgba(0, 0, 0, 0.1),
      0 2px 4px -1px rgba(0, 0, 0, 0.06);
    min-width: 180px;
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%234b5563' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1rem;
    padding-right: 2.5rem;
  }
  
  .status-filter:focus {
    outline: none;
    border-color: var(--primary-blue);
    box-shadow: 
      0 0 0 3px rgba(29, 78, 216, 0.15),
      0 8px 15px -3px rgba(0, 0, 0, 0.1),
      0 4px 6px -2px rgba(0, 0, 0, 0.05);
    transform: translateY(-1px);
  }
  
  .status-filter:hover {
    border-color: var(--primary-blue-light);
    box-shadow: 
      0 6px 10px -1px rgba(0, 0, 0, 0.15),
      0 4px 6px -1px rgba(0, 0, 0, 0.08);
    transform: translateY(-1px);
  }

  .status-filter option {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    color: var(--gray-800);
    font-weight: 500;
    padding: 0.875rem;
    border-radius: 0.5rem;
    border: none;
    margin: 0.25rem 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .status-filter option:hover,
  .status-filter option:focus,
  .status-filter option:checked {
    background: rgba(29, 78, 216, 0.1);
    color: var(--primary-blue-dark);
    font-weight: 600;
  }

  .status-filter option:checked {
    background: var(--primary-blue);
    color: white;
  }
  
  .tasks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
  }
  
  .loading, .empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--gray-700);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 1rem;
    box-shadow: 
      0 25px 50px -12px rgba(0, 0, 0, 0.25),
      0 10px 25px -5px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
    margin-top: 2rem;
  }
  
  .loading {
    font-size: 1.25rem;
    font-weight: 500;
  }
  
  .empty-state p {
    font-size: 1.25rem;
    margin: 0;
  }

  /* Estilos específicos para botones con font-weight bold */
  .header-actions .btn-primary,
  .header-actions .btn-secondary {
    font-weight: 600;
    font-size: 0.9375rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    white-space: nowrap;
  }
  
  .header-actions .btn-primary i,
  .header-actions .btn-secondary i {
    font-size: 1rem;
  }
  
  /* Responsive */
  @media (max-width: 1024px) {
    .tasks-header h1 {
      font-size: 1.875rem;
    }
    
    .header-actions {
      gap: 0.625rem;
    }
    
    .status-filter {
      min-width: 150px;
      font-size: 0.875rem;
      padding: 0.75rem 1rem;
    }
    
    .header-actions .btn-primary,
    .header-actions .btn-secondary {
      font-size: 0.875rem;
      padding: 0.75rem 1rem;
    }
  }
  
  @media (max-width: 768px) {
    .tasks-container {
      padding: 0.75rem;
    }
    
    .tasks-header {
      flex-direction: column;
      gap: 1.25rem;
      align-items: stretch;
      padding: 1.25rem;
    }
    
    .tasks-header h1 {
      font-size: 1.5rem;
      text-align: center;
    }
    
    .header-actions {
      flex-direction: column;
      gap: 0.75rem;
      width: 100%;
    }
    
    .status-filter {
      width: 100%;
      min-width: auto;
      padding: 0.75rem 1rem;
      font-size: 0.875rem;
    }
    
    .header-actions .btn-primary,
    .header-actions .btn-secondary {
      width: 100%;
      justify-content: center;
      padding: 0.875rem 1rem;
      font-size: 0.9375rem;
    }
    
    .header-actions .btn-primary i,
    .header-actions .btn-secondary i {
      font-size: 1.125rem;
    }
    
    .tasks-grid {
      grid-template-columns: 1fr;
      gap: 1rem;
    }
  }
  
  @media (max-width: 480px) {
    .tasks-container {
      padding: 0.5rem;
    }
    
    .tasks-header {
      padding: 1rem;
      gap: 1rem;
    }
    
    .tasks-header h1 {
      font-size: 1.25rem;
    }
    
    .status-filter {
      padding: 0.625rem 0.875rem;
      font-size: 0.8125rem;
    }
    
    .header-actions .btn-primary,
    .header-actions .btn-secondary {
      padding: 0.625rem 0.875rem;
      font-size: 0.875rem;
    }
  }
  </style>