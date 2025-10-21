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
      default: 4000
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
  top: 2rem;
  right: 2rem;
  z-index: 9999;
  max-width: 480px;
  min-width: 380px;
  pointer-events: auto;
}

.toast-content {
  display: flex;
  align-items: flex-start;
  gap: 1.25rem;
  padding: 1.5rem 1.75rem;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(20px);
  border-radius: 0.875rem;
  box-shadow: 
    0 20px 25px -5px rgba(0, 0, 0, 0.1),
    0 10px 10px -5px rgba(0, 0, 0, 0.04),
    0 0 0 1px rgba(255, 255, 255, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.toast-success {
  border-left: 4px solid #10b981;
}

.toast-success .toast-icon {
  color: #10b981;
}

.toast-error {
  border-left: 4px solid #ef4444;
}

.toast-error .toast-icon {
  color: #ef4444;
}

.toast-warning {
  border-left: 4px solid #f59e0b;
}

.toast-warning .toast-icon {
  color: #f59e0b;
}

.toast-info {
  border-left: 4px solid var(--primary-blue);
}

.toast-info .toast-icon {
  color: var(--primary-blue);
}

.toast-icon {
  flex-shrink: 0;
  width: 1.75rem;
  height: 1.75rem;
  margin-top: 0.125rem;
}

.toast-icon svg {
  width: 100%;
  height: 100%;
}

.toast-message {
  flex: 1;
  min-width: 0;
}

.toast-title {
  margin: 0 0 0.5rem 0;
  font-size: 1rem;
  font-weight: 700;
  color: var(--gray-900);
  line-height: 1.3;
}

.toast-description {
  margin: 0;
  font-size: 0.9375rem;
  color: var(--gray-600);
  line-height: 1.5;
}

.toast-close {
  position: relative;
  flex-shrink: 0;
  width: 2.25rem;
  height: 2.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: none;
  border-radius: 0.375rem;
  color: var(--gray-400);
  cursor: pointer;
  transition: all 0.2s ease;
  margin-top: -0.125rem;
}

.toast-close:hover {
  color: var(--gray-600);
  background-color: rgba(107, 114, 128, 0.1);
}

.toast-close svg {
  width: 1.125rem;
  height: 1.125rem;
}

/* Animaciones */
.toast-enter-active {
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.toast-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 1, 1);
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.toast-enter-to,
.toast-leave-from {
  opacity: 1;
  transform: translateX(0) scale(1);
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
    padding: 1.25rem 1.5rem;
    gap: 1rem;
  }
  
  .toast-title {
    font-size: 0.9375rem;
  }
  
  .toast-description {
    font-size: 0.875rem;
  }
  
  .toast-icon {
    width: 1.5rem;
    height: 1.5rem;
  }
}
</style>
