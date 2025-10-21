<template>
  <div class="users-view">
    <div class="users-header">
      <div class="header-content">
        <h2>Gestión de Usuarios</h2>
        <div class="header-actions">
          <div class="search-container">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Buscar por nombre o email..."
              class="search-input"
            />
            <i class="pi pi-search search-icon"></i>
          </div>
        </div>
      </div>
    </div>

    <div v-if="isLoading" class="loading">
      Cargando usuarios...
    </div>

    <div v-else-if="filteredUsers.length === 0" class="empty-state">
      <p v-if="searchQuery">No se encontraron usuarios que coincidan con "{{ searchQuery }}"</p>
      <p v-else>No hay usuarios registrados</p>
    </div>

    <div v-else class="users-grid">
      <div 
        v-for="user in filteredUsers" 
        :key="user.id" 
        class="user-card"
      >
        <div class="user-card-header">
          <div class="user-avatar">
            {{ user.name.charAt(0).toUpperCase() }}
          </div>
          <div class="user-info">
            <h3>{{ user.name }}</h3>
            <p>{{ user.email }}</p>
          </div>
          <div class="user-role">
            <span class="role-badge" :class="getRoleClass(user.roles[0])">
              {{ getRoleLabel(user.roles[0]) }}
            </span>
          </div>
        </div>

        <div class="user-card-body">
          <div class="user-meta">
            <div class="meta-item">
              <span class="meta-label">ID:</span>
              <span class="meta-value">#{{ user.id }}</span>
            </div>
            <div class="meta-item" v-if="user.created_at">
              <span class="meta-label">Registrado:</span>
              <span class="meta-value">{{ formatDate(user.created_at) }}</span>
            </div>
          </div>
        </div>

        <div class="user-card-footer">
          <button @click="viewUser(user)" class="btn-view">
            Ver
          </button>
          <button @click="editUser(user)" class="btn-edit">
            Editar
          </button>
          <button 
            @click="deleteUser(user)" 
            class="btn-delete"
            :disabled="user.id === authStore.user?.id"
          >
            Eliminar
          </button>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <UserDetailModal 
      v-if="showUserModal"
      :user="selectedUser"
      @close="closeUserModal"
    />

    <UserEditModal
      v-if="showEditModal"
      :user="editingUser"
      @close="closeEditModal"
      @save="handleSaveUser"
    />

    <ConfirmModal
      v-if="showConfirmModal"
      title="Confirmar eliminación"
      :message="`¿Estás seguro de que quieres eliminar al usuario ${userToDelete?.name}?`"
      confirm-text="Eliminar"
      :is-loading="isDeleting"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />
  </div>
</template>

<script lang="ts">
import { ref, onMounted, computed } from 'vue'
import { userService } from '../../services/userService'
import { useAuthStore } from '../../stores/auth'
import { useToast } from '../../composables/useToast'
import UserDetailModal from './UserDetailModal.vue'
import UserEditModal from './UserEditModal.vue'
import ConfirmModal from '../ConfirmModal.vue'

export default {
  name: 'UsersView',
  components: {
    UserDetailModal,
    UserEditModal,
    ConfirmModal
  },
  setup() {
    const authStore = useAuthStore()
    const { success, error } = useToast()

    const users = ref([])
    const isLoading = ref(false)
    const searchQuery = ref('')

    // Modals
    const showUserModal = ref(false)
    const showEditModal = ref(false)
    const showConfirmModal = ref(false)
    const selectedUser = ref(null)
    const editingUser = ref(null)
    const userToDelete = ref(null)
    const isDeleting = ref(false)

    const filteredUsers = computed(() => {
      if (!searchQuery.value) return users.value
      
      const query = searchQuery.value.toLowerCase()
      return users.value.filter(user => 
        user.name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query)
      )
    })

    const loadUsers = async () => {
      isLoading.value = true
      try {
        users.value = await userService.getUsers()
      } catch (err) {
        console.error('Error loading users:', err)
        error('Error', 'No se pudieron cargar los usuarios')
      } finally {
        isLoading.value = false
      }
    }

    const viewUser = (user) => {
      selectedUser.value = user
      showUserModal.value = true
    }

    const closeUserModal = () => {
      showUserModal.value = false
      selectedUser.value = null
    }

    const editUser = (user) => {
      editingUser.value = { ...user }
      showEditModal.value = true
    }

    const closeEditModal = () => {
      showEditModal.value = false
      editingUser.value = null
    }

    const handleSaveUser = async (userData) => {
      try {
        await userService.updateUser(editingUser.value.id, userData)
        success('Usuario actualizado', 'Los datos del usuario se han actualizado correctamente')
        await loadUsers() // Recargar la lista
        closeEditModal()
      } catch (err) {
        console.error('Error updating user:', err)
        error('Error', 'No se pudo actualizar el usuario')
      }
    }

    const deleteUser = (user) => {
      if (user.id === authStore.user?.id) {
        error('Error', 'No puedes eliminar tu propia cuenta')
        return
      }
      userToDelete.value = user
      showConfirmModal.value = true
    }

    const confirmDelete = async () => {
      if (!userToDelete.value) return
      
      isDeleting.value = true
      try {
        await userService.deleteUser(userToDelete.value.id)
        success('Usuario eliminado', 'El usuario ha sido eliminado correctamente')
        await loadUsers() // Recargar la lista
        showConfirmModal.value = false
        userToDelete.value = null
      } catch (err) {
        console.error('Error deleting user:', err)
        
        if (err.response?.status === 403 || err.status === 403) {
          error('Sin permisos', err.response?.data?.message || 'No tienes permisos para eliminar usuarios')
        } else {
          error('Error', 'No se pudo eliminar el usuario')
        }
      } finally {
        isDeleting.value = false
      }
    }

    const cancelDelete = () => {
      showConfirmModal.value = false
      userToDelete.value = null
    }

    const getRoleLabel = (role) => {
      const labels = {
        admin: 'Administrador',
        editor: 'Editor',
        viewer: 'Visualizador'
      }
      return labels[role] || role
    }

    const getRoleClass = (role) => {
      return `role-${role}`
    }

    const formatDate = (dateString) => {
      if (!dateString) return 'N/A'
      const date = new Date(dateString)
      return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }

    onMounted(() => {
      loadUsers()
    })

    return {
      authStore,
      users,
      isLoading,
      searchQuery,
      filteredUsers,
      showUserModal,
      showEditModal,
      showConfirmModal,
      selectedUser,
      editingUser,
      userToDelete,
      isDeleting,
      loadUsers,
      viewUser,
      closeUserModal,
      editUser,
      closeEditModal,
      handleSaveUser,
      deleteUser,
      confirmDelete,
      cancelDelete,
      getRoleLabel,
      getRoleClass,
      formatDate
    }
  }
}
</script>

<style scoped>
.users-view {
  max-width: 1200px;
  margin: 0 auto;
  animation: fadeIn 0.5s ease-out;
}

.users-header {
  margin-bottom: 2rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.users-header h2 {
  color: white;
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.search-container {
  position: relative;
}

.search-input {
  padding: 0.75rem 1rem 0.75rem 3rem;
  border: none;
  border-radius: 0.75rem;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  width: 300px;
  font-size: 1rem;
  color: var(--gray-900);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.search-input:focus {
  outline: none;
  box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--gray-500);
  font-size: 1rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.loading, .empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: white;
  font-size: 1.125rem;
}

.users-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 2rem;
}

.user-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
}

.user-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 35px 70px -12px rgba(0, 0, 0, 0.3);
}

.user-card-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.user-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea, #764ba2);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.25rem;
  font-weight: 700;
}

.user-info {
  flex: 1;
}

.user-info h3 {
  margin: 0 0 0.25rem 0;
  color: var(--gray-900);
  font-size: 1.125rem;
  font-weight: 600;
}

.user-info p {
  margin: 0;
  color: var(--gray-600);
  font-size: 0.875rem;
}

.user-role {
  flex-shrink: 0;
}

.role-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.role-badge.role-admin {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
  border: 1px solid rgba(239, 68, 68, 0.2);
}

.role-badge.role-editor {
  background: rgba(34, 197, 94, 0.1);
  color: #16a34a;
  border: 1px solid rgba(34, 197, 94, 0.2);
}

.role-badge.role-viewer {
  background: rgba(59, 130, 246, 0.1);
  color: #2563eb;
  border: 1px solid rgba(59, 130, 246, 0.2);
}

.user-card-body {
  margin-bottom: 1.5rem;
}

.user-meta {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.meta-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.meta-label {
  color: var(--gray-600);
  font-size: 0.875rem;
  font-weight: 500;
}

.meta-value {
  color: var(--gray-900);
  font-size: 0.875rem;
  font-weight: 600;
}

.user-card-footer {
  display: flex;
  gap: 0.5rem;
}

.user-card-footer button {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-view {
  background-color: var(--primary-blue);
  color: white;
}

.btn-view:hover {
  background-color: var(--primary-blue-dark);
}

.btn-edit {
  background-color: var(--gray-100);
  color: var(--gray-700);
  border: 1px solid var(--gray-300);
}

.btn-edit:hover {
  background-color: var(--gray-200);
  border-color: var(--gray-400);
}

.btn-delete {
  background-color: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.btn-delete:hover:not(:disabled) {
  background-color: #fee2e2;
  border-color: #fca5a5;
}

.btn-delete:disabled {
  opacity: 0.5;
  cursor: not-allowed;
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
  .header-content {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-input {
    width: 100%;
  }
  
  .users-grid {
    grid-template-columns: 1fr;
  }
  
  .user-card-footer {
    flex-direction: column;
  }
}
</style>
