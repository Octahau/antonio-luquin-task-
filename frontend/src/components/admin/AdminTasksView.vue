<template>
  <div class="admin-tasks-view">
    <div class="tasks-header">
      <div class="header-content">
        <h2>Gestión de Tareas</h2>
        <p>Administra todas las tareas del sistema</p>
      </div>
      <div class="header-actions">
        <div class="filters">
          <select v-model="selectedStatus" @change="filterTasks" class="status-filter">
            <option value="">Todos los estados</option>
            <option value="pending">Pendientes</option>
            <option value="in_progress">En Progreso</option>
            <option value="completed">Completadas</option>
          </select>
          
          <select v-model="selectedUser" @change="filterTasks" class="user-filter">
            <option value="">Todos los usuarios</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>
          
          <input
            v-model="selectedMonth"
            type="month"
            @change="filterTasks"
            class="month-filter"
          />
        </div>
        <button @click="showCreateForm = true" class="btn-primary">
          Nueva Tarea
        </button>
      </div>
    </div>

    <div v-if="isLoading" class="loading">
      Cargando tareas...
    </div>

    <div v-else-if="filteredTasks.length === 0" class="empty-state">
      <p>No hay tareas disponibles con los filtros seleccionados</p>
    </div>

    <div v-else class="tasks-grid">
      <TaskCard 
        v-for="task in filteredTasks" 
        :key="task.id" 
        :task="task"
        :user="authStore.user"
        @view="viewTask"
        @edit="editTask"
        @delete="deleteTask"
      />
    </div>

    <!-- Modals -->
    <TaskModal 
      v-if="showModal"
      :task="selectedTask"
      @close="closeModal"
    />

    <TaskFormModal 
      v-if="showCreateForm || editingTask"
      :task="editingTask"
      @close="closeFormModal"
      @save="handleSaveTask"
    />

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
import { ref, onMounted, computed } from 'vue'
import { useTasksStore } from '../../stores/tasks'
import { useAuthStore } from '../../stores/auth'
import { userService } from '../../services/userService'
import { useToast } from '../../composables/useToast'
import TaskCard from '../TaskCard.vue'
import TaskModal from '../TaskModal.vue'
import TaskFormModal from '../TaskFormModal.vue'
import ConfirmModal from '../ConfirmModal.vue'

export default {
  name: 'AdminTasksView',
  components: {
    TaskCard,
    TaskModal,
    TaskFormModal,
    ConfirmModal
  },
  setup() {
    const tasksStore = useTasksStore()
    const authStore = useAuthStore()
    const { success, error } = useToast()

    const users = ref([])
    const selectedStatus = ref('')
    const selectedUser = ref('')
    const selectedMonth = ref('')
    
    // Modals
    const showModal = ref(false)
    const showCreateForm = ref(false)
    const showConfirmModal = ref(false)
    const selectedTask = ref(null)
    const editingTask = ref(null)
    const taskToDelete = ref(null)
    const isDeleting = ref(false)

    const isLoading = computed(() => tasksStore.isLoading)
    const allTasks = computed(() => tasksStore.tasks)

    const filteredTasks = computed(() => {
      let tasks = allTasks.value

      // Filter by status
      if (selectedStatus.value) {
        tasks = tasks.filter(task => task.status === selectedStatus.value)
      }

      // Filter by user
      if (selectedUser.value) {
        tasks = tasks.filter(task => task.user_id === parseInt(selectedUser.value))
      }

      // Filter by month
      if (selectedMonth.value) {
        const [year, month] = selectedMonth.value.split('-')
        tasks = tasks.filter(task => {
          const taskDate = new Date(task.created_at)
          return taskDate.getFullYear() === parseInt(year) && 
                 taskDate.getMonth() === (parseInt(month) - 1)
        })
      }

      return tasks
    })

    const loadTasks = async () => {
      try {
        await tasksStore.fetchTasks()
      } catch (err) {
        console.error('Error loading tasks:', err)
        error('Error', 'No se pudieron cargar las tareas')
      }
    }

    const loadUsers = async () => {
      try {
        users.value = await userService.getUsers()
      } catch (err) {
        console.error('Error loading users:', err)
      }
    }

    const filterTasks = async () => {
      await loadTasks()
    }

    const viewTask = (task) => {
      selectedTask.value = task
      showModal.value = true
    }

    const closeModal = () => {
      showModal.value = false
      selectedTask.value = null
    }

    const editTask = (task) => {
      editingTask.value = task
    }

    const closeFormModal = () => {
      showCreateForm.value = false
      editingTask.value = null
    }

    const deleteTask = (task) => {
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
        await filterTasks()
      } catch (err) {
        console.error('Error al eliminar tarea:', err)
        
        if (err.response?.status === 403 || err.status === 403) {
          error('Sin permisos', err.response?.data?.message || 'No tienes permisos para eliminar esta tarea.')
        } else {
          error('Error al eliminar', err.response?.data?.message || 'No se pudo eliminar la tarea. Inténtalo de nuevo.')
        }
      } finally {
        isDeleting.value = false
      }
    }

    const cancelDelete = () => {
      showConfirmModal.value = false
      taskToDelete.value = null
    }

    const handleSaveTask = async (taskData) => {
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
      } catch (err) {
        console.error('Error al guardar tarea:', err)
        
        if (err.response?.status === 403 || err.status === 403) {
          error('Sin permisos', err.response?.data?.message || 'No tienes permisos para esta acción.')
        } else {
          error('Error al guardar', err.response?.data?.message || 'No se pudo guardar la tarea. Inténtalo de nuevo.')
        }
      }
    }

    onMounted(() => {
      loadTasks()
      loadUsers()
    })

    return {
      authStore,
      users,
      selectedStatus,
      selectedUser,
      selectedMonth,
      isLoading,
      filteredTasks,
      showModal,
      showCreateForm,
      showConfirmModal,
      selectedTask,
      editingTask,
      taskToDelete,
      isDeleting,
      filterTasks,
      viewTask,
      closeModal,
      editTask,
      closeFormModal,
      deleteTask,
      confirmDelete,
      cancelDelete,
      handleSaveTask
    }
  }
}
</script>

<style scoped>
.admin-tasks-view {
  max-width: 1200px;
  margin: 0 auto;
  animation: fadeIn 0.5s ease-out;
}

.tasks-header {
  margin-bottom: 2rem;
}

.header-content {
  margin-bottom: 2rem;
  text-align: center;
}

.header-content h2 {
  color: white;
  font-size: 2rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
}

.header-content p {
  color: rgba(255, 255, 255, 0.8);
  font-size: 1.125rem;
  margin: 0;
}

.header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.filters {
  display: flex;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
}

.status-filter,
.user-filter,
.month-filter {
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 0.75rem;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  font-size: 0.875rem;
  color: var(--gray-900);
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.status-filter:focus,
.user-filter:focus,
.month-filter:focus {
  outline: none;
  box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
  border-color: var(--primary-blue-light);
}

.user-filter,
.month-filter {
  min-width: 150px;
}

.btn-primary {
  background: var(--primary-blue);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-primary:hover {
  background: var(--primary-blue-dark);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.loading, .empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: white;
  font-size: 1.125rem;
}

.tasks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 2rem;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive */
@media (max-width: 768px) {
  .header-actions {
    flex-direction: column;
    align-items: stretch;
  }
  
  .filters {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .status-filter,
  .user-filter,
  .month-filter {
    width: 100%;
  }
  
  .tasks-grid {
    grid-template-columns: 1fr;
  }
}
</style>
