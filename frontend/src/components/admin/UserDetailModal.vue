<template>
  <div class="modal-overlay" @click="close">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>Detalles del Usuario</h2>
        <button @click="close" class="close-btn">&times;</button>
      </div>
      
      <div class="modal-body" v-if="user">
        <div class="user-profile">
          <div class="user-avatar-large">
            {{ user.name.charAt(0).toUpperCase() }}
          </div>
          <div class="user-details">
            <h3>{{ user.name }}</h3>
            <p class="user-email">{{ user.email }}</p>
            <span class="role-badge" :class="getRoleClass(user.roles[0])">
              {{ getRoleLabel(user.roles[0]) }}
            </span>
          </div>
        </div>

        <div class="user-info-grid">
          <div class="info-item">
            <label>ID de Usuario:</label>
            <span>#{{ user.id }}</span>
          </div>
          
          <div class="info-item">
            <label>Rol:</label>
            <span>{{ getRoleLabel(user.roles[0]) }}</span>
          </div>
          
          <div class="info-item">
            <label>Email:</label>
            <span>{{ user.email }}</span>
          </div>
          
          <div class="info-item" v-if="user.created_at">
            <label>Fecha de Registro:</label>
            <span>{{ formatDate(user.created_at) }}</span>
          </div>
          
          <div class="info-item" v-if="user.updated_at">
            <label>Última Actualización:</label>
            <span>{{ formatDate(user.updated_at) }}</span>
          </div>
        </div>
      </div>
      
      <div class="modal-footer">
        <button @click="close" class="btn-secondary">Cerrar</button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
export default {
  name: 'UserDetailModal',
  props: {
    user: {
      type: Object,
      required: true
    }
  },
  emits: ['close'],
  setup(props, { emit }) {
    const close = () => {
      emit('close')
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
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
    
    return {
      close,
      getRoleLabel,
      getRoleClass,
      formatDate
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(8px);
  animation: modalFadeIn 0.3s ease-out;
}

@keyframes modalFadeIn {
  from {
    opacity: 0;
    backdrop-filter: blur(0px);
  }
  to {
    opacity: 1;
    backdrop-filter: blur(8px);
  }
}

.modal-content {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 1.5rem;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.4),
    0 15px 35px -5px rgba(0, 0, 0, 0.2),
    0 0 0 1px rgba(255, 255, 255, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.2);
  animation: modalSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  
  /* Scrollbar personalizado */
  scrollbar-width: thin;
  scrollbar-color: rgba(156, 163, 175, 0.6) transparent;
}

.modal-content::-webkit-scrollbar {
  width: 8px;
}

.modal-content::-webkit-scrollbar-track {
  background: transparent;
  border-radius: 4px;
  margin: 0.5rem 0;
}

.modal-content::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.6);
  border-radius: 4px;
  border: none;
}

.modal-content::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.8);
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: scale(0.9) translateY(-20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 2rem 2rem 1rem 2rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.modal-header h2 {
  margin: 0;
  color: var(--gray-900);
  font-size: 1.5rem;
  font-weight: 700;
}

.close-btn {
  background: none;
  border: none;
  font-size: 2rem;
  color: var(--gray-500);
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: rgba(0, 0, 0, 0.1);
  color: var(--gray-700);
}

.modal-body {
  padding: 2rem;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.user-avatar-large {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea, #764ba2);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 2rem;
  font-weight: 700;
}

.user-details h3 {
  margin: 0 0 0.5rem 0;
  color: var(--gray-900);
  font-size: 1.5rem;
  font-weight: 700;
}

.user-email {
  margin: 0 0 1rem 0;
  color: var(--gray-600);
  font-size: 1rem;
}

.role-badge {
  padding: 0.5rem 1rem;
  border-radius: 1rem;
  font-size: 0.875rem;
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

.user-info-grid {
  display: grid;
  gap: 1rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: rgba(243, 244, 246, 0.5);
  border-radius: 0.75rem;
  border: 1px solid rgba(229, 231, 235, 0.5);
}

.info-item label {
  color: var(--gray-700);
  font-weight: 600;
  font-size: 0.875rem;
}

.info-item span {
  color: var(--gray-900);
  font-weight: 500;
  font-size: 0.875rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  padding: 1rem 2rem 2rem 2rem;
  border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.btn-secondary {
  background: var(--gray-100);
  color: var(--gray-700);
  border: 1px solid var(--gray-300);
  padding: 0.75rem 2rem;
  border-radius: 0.75rem;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-secondary:hover {
  background: var(--gray-200);
  border-color: var(--gray-400);
  transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 640px) {
  .modal-content {
    width: 95%;
    margin: 1rem;
  }
  
  .modal-header,
  .modal-body,
  .modal-footer {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }
  
  .user-profile {
    flex-direction: column;
    text-align: center;
  }
  
  .info-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
}
</style>
