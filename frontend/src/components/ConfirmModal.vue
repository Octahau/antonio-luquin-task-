<template>
  <div class="modal-overlay" @click="handleOverlayClick">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <div class="header-content">
          <div class="icon-container">
            <div class="warning-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4"></path>
                <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"></path>
                <path d="M12 16h.01"></path>
              </svg>
            </div>
          </div>
          <div class="header-text">
            <h2 class="dialog-title">{{ title }}</h2>
            <p v-if="taskTitle" class="task-subtitle">{{ taskTitle }}</p>
          </div>
        </div>
      </div>
      
      <div class="modal-body">
        <p class="dialog-description">{{ message }}</p>
      </div>
      
      <div class="modal-footer">
        <button type="button" @click="handleCancel" class="btn-secondary">Cancelar</button>
        <button 
          type="button" 
          @click="handleConfirm" 
          :disabled="isLoading"
          class="btn-danger"
        >
          {{ isLoading ? 'Eliminando...' : confirmText }}
        </button>
      </div>
      
      <button @click="handleCancel" class="close-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M18 6 6 18"></path>
          <path d="m6 6 12 12"></path>
        </svg>
        <span class="sr-only">Cerrar</span>
      </button>
    </div>
  </div>
</template>

<script lang="ts">
export default {
  name: 'ConfirmModal',
  props: {
    title: {
      type: String,
      default: 'Confirmar eliminación'
    },
    message: {
      type: String,
      default: '¿Estás seguro de que quieres eliminar esta tarea?'
    },
    taskTitle: {
      type: String,
      default: ''
    },
    confirmText: {
      type: String,
      default: 'Eliminar'
    },
    isLoading: {
      type: Boolean,
      default: false
    },
    closeOnOverlay: {
      type: Boolean,
      default: false
    }
  },
  emits: ['confirm', 'cancel'],
  setup(props, { emit }) {
    const handleConfirm = () => {
      emit('confirm')
    }

    const handleCancel = () => {
      emit('cancel')
    }

    const handleOverlayClick = () => {
      if (props.closeOnOverlay) {
        handleCancel()
      }
    }

    return {
      handleConfirm,
      handleCancel,
      handleOverlayClick
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
  z-index: 1000;
  backdrop-filter: blur(8px);
  animation: modalFadeIn 0.2s ease-out;
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
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: #ffffff;
  border-radius: 0.5rem;
  width: calc(100% - 2rem);
  max-width: 28rem; /* sm:max-w-md */
  max-height: 90vh;
  overflow: hidden;
  border: 1px solid rgba(0, 0, 0, 0.1);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  animation: modalSlideIn 0.2s ease-out;
  z-index: 1001;
  display: grid;
  gap: 1rem;
  padding: 1.5rem;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translate(-50%, -50%) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
  }
}

.modal-header {
  padding: 0;
  border-bottom: none;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.icon-container {
  flex-shrink: 0;
}

.warning-icon {
  width: 2.5rem;
  height: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fef2f2;
  border-radius: 50%;
  color: #dc2626;
}

.warning-icon svg {
  width: 1.25rem;
  height: 1.25rem;
}

.header-text {
  flex: 1;
}

.dialog-title {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
  line-height: 1.25;
  color: #111827;
  text-align: left;
}

.task-subtitle {
  margin: 0.25rem 0 0 0;
  font-size: 0.875rem;
  color: #6b7280;
}

.modal-body {
  padding: 0;
}

.dialog-description {
  margin: 0;
  color: #6b7280;
  font-size: 0.875rem;
  text-align: left;
  line-height: 1.5;
}

.close-btn {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  background: transparent;
  border: none;
  cursor: pointer;
  color: #6b7280;
  padding: 0.5rem;
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.25rem;
  transition: all 0.2s ease;
  opacity: 0.7;
}

.close-btn:hover {
  opacity: 1;
  background-color: #f3f4f6;
}

.close-btn svg {
  width: 1rem;
  height: 1rem;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

.modal-footer {
  padding: 0;
  display: flex;
  gap: 0.5rem;
  flex-direction: column-reverse;
}

.modal-footer .btn-secondary,
.modal-footer .btn-danger {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  white-space: nowrap;
  border-radius: 0.375rem;
  transition: all 0.2s ease;
  outline: none;
  font-size: 0.875rem;
  font-weight: 500;
  height: 2.25rem;
  padding: 0.5rem 1rem;
  border: 1px solid transparent;
}

.modal-footer .btn-secondary {
  background: #ffffff;
  border-color: #d1d5db;
  color: #374151;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.modal-footer .btn-secondary:hover {
  background: #f9fafb;
}

.modal-footer .btn-danger {
  background: #dc2626;
  border-color: #dc2626;
  color: #ffffff;
}

.modal-footer .btn-danger:hover:not(:disabled) {
  background: #b91c1c;
}

.modal-footer .btn-danger:disabled {
  background: #9ca3af;
  border-color: #9ca3af;
  cursor: not-allowed;
  opacity: 0.5;
}

.modal-footer .btn-secondary:disabled,
.modal-footer .btn-danger:disabled {
  pointer-events: none;
}

/* Responsive */
@media (min-width: 640px) {
  .modal-footer {
    flex-direction: row;
    justify-content: flex-end;
    gap: 0.5rem;
  }
}
</style>
