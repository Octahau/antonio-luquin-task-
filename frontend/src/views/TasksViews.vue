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
            Nueva Tarea
          </button>
          <button @click="logout" class="btn-secondary">
            Cerrar Sesión
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
          @view="viewTask"
          @edit="editTask"
          @delete="deleteTask"
        />
      </div>
  
      <!-- Modal para ver tarea -->
      <TaskModal 
        v-if="showModal"
        :task="selectedTask"
        @close="closeModal"
      />
  
      <!-- Modal para crear/editar -->
      <TaskFormModal 
        v-if="showCreateForm || editingTask"
        :task="editingTask"
        @close="closeFormModal"
        @save="handleSaveTask"
      />
    </div>
  </template>
  
<script lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useTasksStore } from '../stores/tasks'
import { useAuthStore } from '../stores/auth'
  import TaskCard from '../components/TaskCard.vue'
  import TaskModal from '../components/TaskModal.vue'
  import TaskFormModal from '../components/TaskFormModal.vue'
  
  export default {
    name: 'TasksView',
    components: {
      TaskCard,
      TaskModal,
      TaskFormModal
    },
    setup() {
      const tasksStore = useTasksStore()
      const authStore = useAuthStore()
      
      const selectedStatus = ref('')
      const showModal = ref(false)
      const showCreateForm = ref(false)
      const selectedTask = ref(null)
      const editingTask = ref(null)
  
      const tasks = computed(() => tasksStore.tasks)
      const isLoading = computed(() => tasksStore.isLoading)
      const canCreate = computed(() => authStore.isAdmin || authStore.isEditor)
  
      const filterTasks = async () => {
        await tasksStore.fetchTasks(selectedStatus.value || null)
      }
  
      const viewTask = (task) => {
        selectedTask.value = task
        showModal.value = true
      }
  
      const editTask = (task) => {
        editingTask.value = task
      }
  
      const deleteTask = async (task) => {
        if (confirm('¿Estás seguro de que quieres eliminar esta tarea?')) {
          try {
            await tasksStore.deleteTask(task.id)
          } catch (error) {
            alert('Error al eliminar la tarea')
          }
        }
      }
  
      const closeModal = () => {
        showModal.value = false
        selectedTask.value = null
      }
  
      const closeFormModal = () => {
        showCreateForm.value = false
        editingTask.value = null
      }
  
      const handleSaveTask = async (taskData) => {
        try {
          if (editingTask.value) {
            await tasksStore.updateTask(editingTask.value.id, taskData)
          } else {
            await tasksStore.createTask(taskData)
          }
          closeFormModal()
          await filterTasks()
        } catch (error) {
          alert('Error al guardar la tarea')
        }
      }
  
      const logout = async () => {
        await authStore.logout()
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
        canCreate,
        filterTasks,
        viewTask,
        editTask,
        deleteTask,
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
    gap: 1rem;
    align-items: center;
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
    background: var(--white);
    color: var(--gray-800);
    font-weight: 500;
    padding: 0.5rem;
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
    font-weight: bold;
    font-size: 1rem;
  }
  
  /* Responsive */
  @media (max-width: 768px) {
    .tasks-container {
      padding: 1rem;
    }
    
    .tasks-header {
      flex-direction: column;
      gap: 1rem;
      align-items: stretch;
    }
    
    .header-actions {
      justify-content: center;
    }
    
    .tasks-grid {
      grid-template-columns: 1fr;
      gap: 1rem;
    }
  }
  </style>