<template>
  <div class="task-card">
    <div class="task-card-header">
      <h3 class="task-title">{{ task.title }}</h3>
      <span class="task-status" :class="statusClass">{{ statusText }}</span>
    </div>
    
    <div class="task-card-body">
      <p class="task-description" v-if="task.description">
        {{ task.description.length > 120 ? task.description.substring(0, 120) + '...' : task.description }}
      </p>
      <p v-else class="no-description">Sin descripción</p>
      
      <div class="task-meta">
        <div v-if="task.due_date" class="due-date">
          <span class="meta-icon">📅</span>
          <span>{{ formatDate(task.due_date) }}</span>
        </div>
        <div class="created-info">
          <span class="meta-icon">👤</span>
          <span>{{ task.user?.name || 'Usuario' }}</span>
        </div>
      </div>
    </div>
    
    <div class="task-card-footer">
      <button @click="$emit('view', task)" class="btn-view">
        Ver
      </button>
      <button @click="$emit('edit', task)" class="btn-edit">
        Editar
      </button>
      <button @click="$emit('delete', task)" class="btn-delete">
        Eliminar
      </button>
    </div>
  </div>
</template>

<script lang="ts">
import { computed } from 'vue'

export default {
  name: 'TaskCard',
  props: {
    task: {
      type: Object,
      required: true
    }
  },
  emits: ['view', 'edit', 'delete'],
  setup(props) {
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
        day: 'numeric',
        month: 'short',
        year: date.getFullYear() !== new Date().getFullYear() ? 'numeric' : undefined
      })
    }
    
    return {
      statusText,
      statusClass,
      formatDate
    }
  }
}
</script>

<style scoped>
.task-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.25),
    0 10px 25px -5px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.task-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
  border-color: var(--primary-blue-light);
}

.task-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  gap: 1rem;
}

.task-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--gray-900);
  line-height: 1.4;
  flex: 1;
}

.task-status {
  display: inline-block;
  padding: 0.375rem 0.75rem;
  border-radius: 0.75rem;
  font-size: 0.75rem;
  font-weight: 500;
  text-align: center;
  white-space: nowrap;
  min-width: 80px;
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

.task-card-body {
  flex: 1;
  margin-bottom: 1rem;
}

.task-description {
  color: var(--gray-600);
  line-height: 1.5;
  margin: 0 0 1rem 0;
  font-size: 1rem;
}

.no-description {
  color: var(--gray-400);
  font-style: italic;
  margin: 0 0 1rem 0;
  font-size: 1rem;
}

.task-meta {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.due-date,
.created-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: var(--gray-500);
}

.meta-icon {
  font-size: 0.875rem;
}

.task-card-footer {
  display: flex;
  gap: 0.5rem;
  margin-top: auto;
}

.btn-view,
.btn-edit,
.btn-delete {
  flex: 1;
  padding: 0.5rem 0.75rem;
  border: none;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-view {
  background-color: var(--primary-blue);
  color: var(--white);
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
  color: var(--error);
  border: 1px solid #fecaca;
}

.btn-delete:hover {
  background-color: #fee2e2;
  border-color: #fca5a5;
}

/* Responsive */
@media (max-width: 640px) {
  .task-card {
    padding: 1rem;
  }
  
  .task-card-header {
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
  }
  
  .task-status {
    align-self: flex-start;
  }
  
  .task-card-footer {
    flex-direction: column;
  }
  
  .btn-view,
  .btn-edit,
  .btn-delete {
    flex: none;
    padding: 0.75rem;
    font-size: 0.875rem;
  }
}
</style>
