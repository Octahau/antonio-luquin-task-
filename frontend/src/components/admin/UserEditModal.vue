<template>
  <div class="modal-overlay" @click="close">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>Editar Usuario</h2>
        <button @click="close" class="close-btn">&times;</button>
      </div>
      
      <form @submit.prevent="handleSubmit" class="modal-body" v-if="user">
        <div class="form-grid">
          <div class="form-group">
            <label for="name">Nombre completo</label>
            <input
              id="name"
              v-model="formData.name"
              type="text"
              class="form-input"
              :class="{ error: errors.name }"
              required
            />
            <span v-if="errors.name" class="error-message">{{ errors.name }}</span>
          </div>

          <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input
              id="email"
              v-model="formData.email"
              type="email"
              class="form-input"
              :class="{ error: errors.email }"
              required
            />
            <span v-if="errors.email" class="error-message">{{ errors.email }}</span>
          </div>

          <div class="form-group">
            <label for="role">Rol</label>
            <select
              id="role"
              v-model="formData.role"
              class="form-select"
              :class="{ error: errors.role }"
              required
            >
              <option value="admin">Administrador</option>
              <option value="editor">Editor</option>
              <option value="viewer">Visualizador</option>
            </select>
            <span v-if="errors.role" class="error-message">{{ errors.role }}</span>
          </div>
        </div>
      </form>
      
      <div class="modal-footer">
        <button type="button" @click="close" class="btn-secondary">
          Cancelar
        </button>
        <button 
          type="submit" 
          @click="handleSubmit" 
          class="btn-primary"
          :disabled="isLoading"
        >
          <span v-if="isLoading">Guardando...</span>
          <span v-else>Actualizar Usuario</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { ref, reactive, watch } from 'vue'

export default {
  name: 'UserEditModal',
  props: {
    user: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'save'],
  setup(props, { emit }) {
    const isLoading = ref(false)
    const errors = ref({})

    const formData = reactive({
      name: props.user.name || '',
      email: props.user.email || '',
      role: props.user.roles?.[0] || 'viewer'
    })

    // Reset form when user prop changes
    watch(() => props.user, (newUser) => {
      if (newUser) {
        formData.name = newUser.name || ''
        formData.email = newUser.email || ''
        formData.role = newUser.roles?.[0] || 'viewer'
      }
    }, { immediate: true })

    const validateForm = () => {
      errors.value = {}

      if (!formData.name.trim()) {
        errors.value.name = 'El nombre es obligatorio'
      }

      if (!formData.email.trim()) {
        errors.value.email = 'El email es obligatorio'
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
        errors.value.email = 'El email no tiene un formato válido'
      }

      if (!formData.role) {
        errors.value.role = 'El rol es obligatorio'
      }

      return Object.keys(errors.value).length === 0
    }

    const handleSubmit = async () => {
      if (!validateForm()) return

      isLoading.value = true
      try {
        const userData = {
          name: formData.name.trim(),
          email: formData.email.trim(),
          role: formData.role
        }

        emit('save', userData)
      } catch (error) {
        console.error('Error updating user:', error)
      } finally {
        isLoading.value = false
      }
    }

    const close = () => {
      emit('close')
    }

    return {
      formData,
      errors,
      isLoading,
      handleSubmit,
      close
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

.form-grid {
  display: grid;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  color: var(--gray-700);
  font-weight: 600;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.form-input,
.form-select {
  padding: 0.75rem 1rem;
  border: 2px solid rgba(229, 231, 235, 0.5);
  border-radius: 0.75rem;
  font-size: 1rem;
  transition: all 0.2s ease;
  background: rgba(255, 255, 255, 0.8);
  color: var(--gray-900);
}

.form-input:focus,
.form-select:focus {
  outline: none;
  border-color: var(--primary-blue);
  background: rgba(255, 255, 255, 0.95);
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-input.error,
.form-select.error {
  border-color: #ef4444;
}

.error-message {
  color: #ef4444;
  font-size: 0.875rem;
  margin-top: 0.25rem;
  font-weight: 500;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1rem 2rem 2rem 2rem;
  border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.btn-secondary,
.btn-primary {
  padding: 0.75rem 2rem;
  border-radius: 0.75rem;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
  border: none;
}

.btn-secondary {
  background: var(--gray-100);
  color: var(--gray-700);
  border: 1px solid var(--gray-300);
}

.btn-secondary:hover {
  background: var(--gray-200);
  border-color: var(--gray-400);
}

.btn-primary {
  background: var(--primary-blue);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: var(--primary-blue-dark);
  transform: translateY(-1px);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
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
  
  .modal-footer {
    flex-direction: column-reverse;
  }
  
  .btn-secondary,
  .btn-primary {
    width: 100%;
  }
}
</style>
