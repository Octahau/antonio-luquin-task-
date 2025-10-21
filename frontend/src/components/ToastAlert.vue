<template>
  <transition name="toast" appear>
    <div v-if="show" class="toast-container" :class="toastClass">
      <div class="toast-content">
        <div class="toast-icon">
          <svg v-if="type === 'success'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <svg v-else-if="type === 'error'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <svg v-else-if="type === 'warning'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
          </svg>
          <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div class="toast-message">
          <h4 class="toast-title">{{ title }}</h4>
          <p class="toast-description" v-if="description">{{ description }}</p>
        </div>
        <button @click="close" class="toast-close">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>
  </transition>
</template>

<script lang="ts">
import { ref, computed, onMounted, watch } from 'vue'

export default {
  name: 'ToastAlert',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    type: {
      type: String,
      default: 'success',
      validator: (value: string) => ['success', 'error', 'warning', 'info'].includes(value)
    },
    title: {
      type: String,
      required: true
    },
    description: {
      type: String,
      default: ''
    },
    duration: {
      type: Number,
      default: 3500
    },
    autoClose: {
      type: Boolean,
      default: true
    }
  },
  emits: ['close'],
  setup(props, { emit }) {
    const show = ref(props.show)
    let timeoutId: number | null = null

    const toastClass = computed(() => `toast-${props.type}`)

    const close = () => {
      show.value = false
      if (timeoutId) {
        clearTimeout(timeoutId)
        timeoutId = null
      }
      emit('close')
    }

    const setupAutoClose = () => {
      if (props.autoClose && props.duration > 0) {
        timeoutId = setTimeout(() => {
          close()
        }, props.duration)
      }
    }

    watch(() => props.show, (newValue) => {
      show.value = newValue
      if (newValue) {
        setupAutoClose()
      } else {
        if (timeoutId) {
          clearTimeout(timeoutId)
          timeoutId = null
        }
      }
    })

    onMounted(() => {
      if (props.show && props.autoClose) {
        setupAutoClose()
      }
    })

    return {
      show,
      toastClass,
      close
    }
  }
}
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 1.5rem;
  right: 1.5rem;
  z-index: 9999;
  max-width: 360px;
  min-width: 320px;
  pointer-events: auto;
}

.toast-content {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  background: #ffffff;
  border-radius: 0.75rem;
  box-shadow: 
    0 10px 15px -3px rgba(0, 0, 0, 0.1),
    0 4px 6px -2px rgba(0, 0, 0, 0.05),
    0 0 0 1px rgba(0, 0, 0, 0.05);
  border-left: 3px solid;
  transition: transform 0.2s ease;
}

.toast-content:hover {
  transform: translateY(-2px);
}

.toast-success {
  border-left-color: #10b981;
}

.toast-success .toast-icon {
  background: #d1fae5;
  color: #059669;
}

.toast-error {
  border-left-color: #ef4444;
}

.toast-error .toast-icon {
  background: #fee2e2;
  color: #dc2626;
}

.toast-warning {
  border-left-color: #f59e0b;
}

.toast-warning .toast-icon {
  background: #fef3c7;
  color: #d97706;
}

.toast-info {
  border-left-color: #3b82f6;
}

.toast-info .toast-icon {
  background: #dbeafe;
  color: #2563eb;
}

.toast-icon {
  flex-shrink: 0;
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.5rem;
  transition: transform 0.3s ease;
}

.toast-content:hover .toast-icon {
  transform: scale(1.1);
}

.toast-icon svg {
  width: 1.25rem;
  height: 1.25rem;
  stroke-width: 2.5;
}

.toast-message {
  flex: 1;
  min-width: 0;
}

.toast-title {
  margin: 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: #111827;
  line-height: 1.4;
}

.toast-description {
  margin: 0.25rem 0 0 0;
  font-size: 0.8125rem;
  color: #6b7280;
  line-height: 1.4;
}

.toast-close {
  flex-shrink: 0;
  width: 1.75rem;
  height: 1.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: none;
  border-radius: 0.375rem;
  color: #9ca3af;
  cursor: pointer;
  transition: all 0.15s ease;
}

.toast-close:hover {
  color: #4b5563;
  background-color: #f3f4f6;
  transform: rotate(90deg);
}

.toast-close:active {
  transform: rotate(90deg) scale(0.9);
}

.toast-close svg {
  width: 1rem;
  height: 1rem;
  stroke-width: 2;
}

/* Animaciones mejoradas */
.toast-enter-active {
  animation: slideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.toast-leave-active {
  animation: slideOut 0.2s cubic-bezier(0.4, 0, 1, 1);
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(100%) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translateX(0) scale(1);
  }
}

@keyframes slideOut {
  from {
    opacity: 1;
    transform: translateX(0) scale(1);
  }
  to {
    opacity: 0;
    transform: translateX(100%) scale(0.9);
  }
}

/* Responsive */
@media (max-width: 640px) {
  .toast-container {
    top: 1rem;
    right: 1rem;
    left: 1rem;
    max-width: none;
    min-width: auto;
  }
  
  .toast-content {
    padding: 0.75rem 0.875rem;
    gap: 0.625rem;
  }
  
  .toast-title {
    font-size: 0.8125rem;
  }
  
  .toast-description {
    font-size: 0.75rem;
  }
  
  .toast-icon {
    width: 1.75rem;
    height: 1.75rem;
  }
  
  .toast-icon svg {
    width: 1rem;
    height: 1rem;
  }
}
</style>
