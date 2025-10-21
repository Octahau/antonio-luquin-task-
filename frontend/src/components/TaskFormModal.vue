<template>
  <div class="modal-overlay" @click="close">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>{{ isEditing ? 'Editar Tarea' : 'Nueva Tarea' }}</h2>
        <button @click="close" class="close-btn">&times;</button>
      </div>
      
      <form @submit.prevent="handleSubmit" class="modal-body">
        <div class="field">
          <label for="title">Título *</label>
          <input 
            id="title"
            v-model="form.title" 
            type="text" 
            required 
            maxlength="255"
            placeholder="Ingresa el título de la tarea"
          />
          <div v-if="errors.title" class="error">{{ errors.title }}</div>
        </div>
        
        <div class="field">
          <label for="description">Descripción</label>
          <textarea 
            id="description"
            v-model="form.description" 
            rows="4"
            placeholder="Describe la tarea"
          ></textarea>
        </div>
        
        <div class="field">
          <label for="status">Estado</label>
          <select id="status" v-model="form.status">
            <option value="pending">Pendiente</option>
            <option value="in_progress">En Progreso</option>
            <option value="completed">Completada</option>
          </select>
        </div>
        
        <div class="field" v-if="isAdmin">
          <label for="assigned_user">Asignar a usuario</label>
          <select id="assigned_user" v-model="form.user_id">
            <option :value="null">Seleccionar usuario...</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }} ({{ user.email }}) - {{ user.roles.join(', ') }}
            </option>
          </select>
          <small class="help-text">Solo administradores pueden asignar tareas a otros usuarios</small>
        </div>
        
        <div class="field">
          <label for="due_date">Fecha límite</label>
          <input 
            id="due_date"
            v-model="form.due_date" 
            type="date" 
            class="date-input"
          />
          <small class="help-text">Opcional - Solo fechas futuras</small>
        </div>
        
        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>
      </form>
      
      <div class="modal-footer">
        <button type="button" @click="close" class="btn-secondary">Cancelar</button>
        <button 
          type="submit" 
          @click="handleSubmit" 
          :disabled="isLoading"
          class="btn-primary"
        >
          {{ isLoading ? 'Guardando...' : (isEditing ? 'Actualizar' : 'Crear') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { authService } from '../services/auth'

export default {
  name: 'TaskFormModal',
  props: {
    task: {
      type: Object,
      default: null
    }
  },
  emits: ['close', 'save'],
  setup(props, { emit }) {
    const authStore = useAuthStore()
    const isLoading = ref(false)
    const errorMessage = ref('')
    const users = ref([])
    const errors = reactive({
      title: ''
    })

    const isEditing = computed(() => !!props.task)
    const isAdmin = computed(() => authStore.isAdmin)

    const form = reactive({
      title: '',
      description: '',
      status: 'pending',
      due_date: '',
      user_id: null
    })

    const close = () => {
      emit('close')
    }

    const loadUsers = async () => {
      if (isAdmin.value) {
        try {
          users.value = await authService.getUsers()
        } catch (error) {
          console.error('Error loading users:', error)
          users.value = []
        }
      }
    }

    const resetForm = () => {
      form.title = ''
      form.description = ''
      form.status = 'pending'
      form.due_date = ''
      form.user_id = null
      errorMessage.value = ''
      errors.title = ''
    }

    const validateForm = () => {
      errors.title = ''
      
      if (!form.title.trim()) {
        errors.title = 'El título es obligatorio'
        return false
      }
      
      if (form.title.length > 255) {
        errors.title = 'El título no puede exceder 255 caracteres'
        return false
      }

      return true
    }

    const handleSubmit = async () => {
      if (!validateForm()) {
        return
      }

      isLoading.value = true
      errorMessage.value = ''

      try {
        const taskData = {
          title: form.title.trim(),
          description: form.description.trim() || null,
          status: form.status,
          due_date: form.due_date || null
        }
        
        // Solo incluir user_id si es admin y se ha seleccionado un usuario
        if (isAdmin.value && form.user_id) {
          taskData.user_id = form.user_id
        }
        
        emit('save', taskData)
      } catch (error) {
        errorMessage.value = 'Error al guardar la tarea'
      } finally {
        isLoading.value = false
      }
    }

    onMounted(async () => {
      await loadUsers()
      
      if (props.task) {
        form.title = props.task.title || ''
        form.description = props.task.description || ''
        form.status = props.task.status || 'pending'
        form.due_date = props.task.due_date || ''
        
        // Solo establecer user_id si es admin y la tarea tiene un usuario asignado
        if (isAdmin.value && props.task.user_id) {
          form.user_id = props.task.user_id
        }
      }
    })

    return {
      form,
      errors,
      errorMessage,
      isLoading,
      isEditing,
      isAdmin,
      users,
      close,
      handleSubmit
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
  max-width: 500px;
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
  background: rgba(0, 0, 0, 0.02);
  backdrop-filter: blur(5px);
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

input,
textarea,
select {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 1px solid rgba(255, 255, 255, 0.4);
  border-radius: 0.75rem;
  font-size: 1rem;
  color: var(--gray-900);
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(10px);
  box-sizing: border-box;
  transition: all 0.3s ease;
  box-shadow: 
    0 4px 6px -1px rgba(0, 0, 0, 0.1),
    0 2px 4px -1px rgba(0, 0, 0, 0.06),
    inset 0 1px 0 rgba(255, 255, 255, 0.6);
}

input:focus,
textarea:focus,
select:focus {
  outline: none;
  border-color: var(--primary-blue);
  box-shadow: 
    0 0 0 3px rgba(29, 78, 216, 0.2),
    0 8px 15px -3px rgba(0, 0, 0, 0.15),
    0 4px 6px -2px rgba(0, 0, 0, 0.08),
    inset 0 1px 0 rgba(255, 255, 255, 0.8);
  background: rgba(255, 255, 255, 0.95);
  transform: translateY(-2px);
}

input:hover,
textarea:hover,
select:hover {
  border-color: var(--primary-blue-light);
  background: rgba(255, 255, 255, 0.9);
  box-shadow: 
    0 6px 10px -1px rgba(0, 0, 0, 0.15),
    0 4px 6px -1px rgba(0, 0, 0, 0.08),
    inset 0 1px 0 rgba(255, 255, 255, 0.7);
  transform: translateY(-1px);
}

textarea {
  resize: vertical;
  min-height: 120px;
  font-family: inherit;
  line-height: 1.5;
}

/* Estilo especial para el select */
select {
  appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%234b5563' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 1rem;
  padding-right: 2.5rem;
}

/* Estilos para las opciones del select */
select option {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  color: var(--gray-800);
  font-weight: 500;
  padding: 1rem;
  border: none;
  border-radius: 0.5rem;
  margin: 0.25rem 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

select option:hover,
select option:focus,
select option:checked {
  background: rgba(29, 78, 216, 0.1);
  color: var(--primary-blue-dark);
  font-weight: 600;
}

select option:checked {
  background: var(--primary-blue);
  color: white;
}

/* Estilo específico para el select de usuarios con texto más pequeño */
#assigned_user option {
  font-size: 0.875rem;
  line-height: 1.4;
  padding: 0.75rem;
}

input::placeholder,
textarea::placeholder {
  color: var(--gray-400);
}

.help-text {
  color: var(--gray-700);
  font-size: 0.875rem;
  margin-top: 0.75rem;
  display: block;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.4);
  backdrop-filter: blur(10px);
  border-radius: 0.5rem;
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
}

.error {
  color: var(--error);
  font-size: 0.875rem;
  margin-top: 0.75rem;
  display: block;
  padding: 0.5rem 0.75rem;
  background: rgba(239, 68, 68, 0.1);
  border-radius: 0.5rem;
  border: 1px solid rgba(239, 68, 68, 0.2);
}

.error-message {
  background: rgba(239, 68, 68, 0.1);
  backdrop-filter: blur(10px);
  color: var(--error);
  padding: 1rem;
  border-radius: 0.75rem;
  margin-bottom: 1.5rem;
  border: 1px solid rgba(239, 68, 68, 0.2);
  font-size: 0.875rem;
}

.modal-footer {
  padding: 1.5rem 1.5rem 2rem;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
  border-radius: 0 0 1.5rem 1.5rem;
  backdrop-filter: blur(10px);
}

.modal-footer .btn-secondary {
  order: 1;
}

/* Estilos específicos para los botones del footer */
.modal-footer .btn-primary {
  order: 2;
  margin-left: auto;
  background: var(--primary-blue);
  backdrop-filter: blur(10px);
  border: 2px solid var(--primary-blue-dark);
  color: var(--white);
  font-weight: 600;
  padding: 0.875rem 1.5rem;
  border-radius: 0.75rem;
  transition: all 0.3s ease;
  box-shadow: 
    0 4px 8px rgba(29, 78, 216, 0.3),
    0 2px 4px rgba(0, 0, 0, 0.1);
}

.modal-footer .btn-primary:hover:not(:disabled) {
  background: var(--primary-blue-dark);
  border-color: #1e3a8a;
  transform: translateY(-1px);
  box-shadow: 
    0 6px 12px rgba(29, 78, 216, 0.4),
    0 4px 6px rgba(0, 0, 0, 0.15);
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
  transform: translateY(-1px);
  box-shadow: 
    0 6px 12px rgba(0, 0, 0, 0.25),
    0 4px 6px rgba(0, 0, 0, 0.15);
}

/* Estilos específicos para el input de fecha (calendario) en el modal */
.date-input {
  position: relative;
  /* Los estilos principales están en App.vue para consistencia global */
}
</style>
