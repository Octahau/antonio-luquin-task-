<template>
  <div class="modal-overlay" @click="close">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>{{ task.title }}</h2>
        <button @click="close" class="close-btn">&times;</button>
      </div>
      
      <div class="modal-body">
        <div class="field">
          <label>Estado:</label>
          <span class="task-status" :class="statusClass">{{ statusText }}</span>
        </div>
        
        <div class="field">
          <label>Descripción:</label>
          <p class="description">{{ task.description || 'Sin descripción' }}</p>
        </div>
        
        <div class="field" v-if="task.due_date">
          <label>Fecha límite:</label>
          <p class="due-date"><i class="pi pi-calendar"></i> {{ formatDate(task.due_date) }}</p>
        </div>
        
        <div class="field">
          <label>Creado por:</label>
          <p class="user-info"><i class="pi pi-user"></i> {{ task.user?.name }} ({{ task.user?.email }})</p>
        </div>
        
        <div class="field">
          <label>Creado el:</label>
          <p class="created-date"><i class="pi pi-calendar"></i> {{ formatDate(task.created_at) }}</p>
        </div>
        
        <div v-if="task.updated_at !== task.created_at" class="field">
          <label>Última actualización:</label>
          <p class="updated-date"><i class="pi pi-refresh"></i> {{ formatDate(task.updated_at) }}</p>
        </div>
      </div>
      
      <div class="modal-footer">
        <button @click="close" class="btn-secondary">Cerrar</button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { computed } from 'vue'

export default {
  name: 'TaskModal',
  props: {
    task: {
      type: Object,
      required: true
    }
  },
  emits: ['close'],
  setup(props, { emit }) {
    const close = () => {
      emit('close')
    }
    
    const statusText = computed(() => {
      const statusMap: { [key: string]: string } = {
        pending: 'Pendiente',
        in_progress: 'En Progreso',
        completed: 'Completada'
      }
      return statusMap[props.task.status as string] || props.task.status
    })
    
    const statusClass = computed(() => {
      return `status-${props.task.status}`
    })
    
    const formatDate = (dateString: string) => {
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
      statusText,
      statusClass,
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
  background: rgba(107, 114, 128, 0.8);
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(-20px);
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
  padding: 2rem 1.5rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
  border-radius: 1.5rem 1.5rem 0 0;
  backdrop-filter: blur(10px);
}

.modal-header h2 {
  margin: 0;
  color: var(--gray-900);
  font-size: 1.75rem;
  font-weight: 700;
  background: linear-gradient(135deg, var(--primary-blue) 0%, var(--gradient-middle) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.close-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  font-size: 1.5rem;
  cursor: pointer;
  color: var(--gray-600);
  padding: 0.5rem;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.75rem;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.close-btn:hover {
  color: var(--gray-800);
  background-color: rgba(255, 255, 255, 0.2);
  border-color: rgba(255, 255, 255, 0.3);
  transform: scale(1.05);
}

.modal-body {
  padding: 2rem 1.5rem;
  background: rgba(255, 255, 255, 0.05);
}

.field {
  margin-bottom: 2rem;
}

.field label {
  display: block;
  font-weight: 600;
  color: var(--gray-800);
  margin-bottom: 0.75rem;
  font-size: 1rem;
}

.description {
  margin: 0;
  color: var(--gray-700);
  line-height: 1.7;
  background: rgba(255, 255, 255, 0.4);
  backdrop-filter: blur(10px);
  padding: 1.25rem;
  border-radius: 0.75rem;
  border-left: 4px solid var(--primary-blue);
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  font-size: 1rem;
}

.task-status {
  display: inline-block;
  padding: 0.5rem 1rem;
  border-radius: 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  text-align: center;
  min-width: 120px;
}

.status-pending {
  background-color: #fef3c7;
  color: #d97706;
  border: 1px solid #fbbf24;
}

.status-in_progress {
  background-color: #dbeafe;
  color: var(--primary-blue);
  border: 1px solid var(--primary-blue-light);
}

.status-completed {
  background-color: #d1fae5;
  color: var(--success);
  border: 1px solid #34d399;
}

.due-date,
.user-info,
.created-date,
.updated-date {
  margin: 0;
  color: var(--gray-700);
  padding: 0.75rem 1rem;
  background: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(5px);
  border-radius: 0.5rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.due-date i,
.user-info i,
.created-date i,
.updated-date i {
  color: var(--primary-blue);
  font-size: 1rem;
}

.modal-footer {
  padding: 1.5rem 1.5rem 2rem;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
  text-align: right;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
  border-radius: 0 0 1.5rem 1.5rem;
  backdrop-filter: blur(10px);
}

.modal-footer .btn-secondary {
  background: var(--gray-600);
  backdrop-filter: blur(10px);
  border: 2px solid var(--gray-700);
  color: var(--white);
  font-weight: 600;
  padding: 0.875rem 1.5rem;
  border-radius: 0.75rem;
  transition: all 0.3s ease;
  box-shadow: 
    0 4px 8px rgba(0, 0, 0, 0.2),
    0 2px 4px rgba(0, 0, 0, 0.1);
}

.modal-footer .btn-secondary:hover {
  background: var(--gray-700);
  border-color: var(--gray-800);
  transform: translateY(-2px);
  box-shadow: 
    0 6px 12px rgba(0, 0, 0, 0.25),
    0 4px 6px rgba(0, 0, 0, 0.15);
}
</style>
