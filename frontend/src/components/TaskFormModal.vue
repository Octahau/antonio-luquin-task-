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
        
        <div class="field">
          <label for="due_date">Fecha límite</label>
          <input 
            id="due_date"
            v-model="form.due_date" 
            type="date" 
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
    const isLoading = ref(false)
    const errorMessage = ref('')
    const errors = reactive({
      title: ''
    })

    const isEditing = computed(() => !!props.task)

    const form = reactive({
      title: '',
      description: '',
      status: 'pending',
      due_date: ''
    })

    const close = () => {
      emit('close')
    }

    const resetForm = () => {
      form.title = ''
      form.description = ''
      form.status = 'pending'
      form.due_date = ''
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
        
        emit('save', taskData)
      } catch (error) {
        errorMessage.value = 'Error al guardar la tarea'
      } finally {
        isLoading.value = false
      }
    }

    onMounted(() => {
      if (props.task) {
        form.title = props.task.title || ''
        form.description = props.task.description || ''
        form.status = props.task.status || 'pending'
        form.due_date = props.task.due_date || ''
      }
    })

    return {
      form,
      errors,
      errorMessage,
      isLoading,
      isEditing,
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
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: var(--white);
  border-radius: 1rem;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: var(--shadow-xl);
  border: 1px solid var(--gray-200);
  animation: modalSlideIn 0.3s ease-out;
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
  padding: 1.5rem;
  border-bottom: 1px solid var(--gray-200);
  background: linear-gradient(135deg, var(--gray-50) 0%, var(--white) 100%);
  border-radius: 1rem 1rem 0 0;
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
  font-size: 1.5rem;
  cursor: pointer;
  color: var(--gray-500);
  padding: 0.5rem;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.5rem;
  transition: all 0.2s ease;
}

.close-btn:hover {
  color: var(--gray-700);
  background-color: var(--gray-100);
}

.modal-body {
  padding: 1.5rem;
}

.field {
  margin-bottom: 1.5rem;
}

.field label {
  display: block;
  font-weight: 600;
  color: var(--gray-700);
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

input,
textarea,
select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--gray-300);
  border-radius: 0.5rem;
  font-size: 1rem;
  color: var(--gray-900);
  background-color: var(--white);
  box-sizing: border-box;
  transition: all 0.2s ease;
}

input:focus,
textarea:focus,
select:focus {
  outline: none;
  border-color: var(--primary-blue);
  box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
}

input:hover,
textarea:hover,
select:hover {
  border-color: var(--gray-400);
}

textarea {
  resize: vertical;
  min-height: 100px;
  font-family: inherit;
}

input::placeholder,
textarea::placeholder {
  color: var(--gray-400);
}

.help-text {
  color: var(--gray-500);
  font-size: 0.75rem;
  margin-top: 0.5rem;
  display: block;
}

.error {
  color: var(--error);
  font-size: 0.75rem;
  margin-top: 0.5rem;
  display: block;
}

.error-message {
  background-color: #fef2f2;
  color: #dc2626;
  padding: 0.75rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
  border: 1px solid #fecaca;
  font-size: 0.875rem;
}

.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid var(--gray-200);
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  background: var(--gray-50);
  border-radius: 0 0 1rem 1rem;
}

/* Los estilos de .btn-primary y .btn-secondary se heredan del App.vue */
</style>
