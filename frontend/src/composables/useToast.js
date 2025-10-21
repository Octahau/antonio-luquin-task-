import { ref, reactive } from 'vue'

// Estado global para las alertas
const toasts = ref([])
let toastId = 0

// Funciones globales que acceden al estado global
const removeToast = (id) => {
  const index = toasts.value.findIndex(toast => toast.id === id)
  if (index > -1) {
    toasts.value[index].show = false
    // Remover del array después de la animación
    setTimeout(() => {
      toasts.value.splice(index, 1)
    }, 300)
  }
}

const addToast = (options) => {
  const id = ++toastId
  const toast = reactive({
    id,
    show: true,
    type: options.type || 'success',
    title: options.title || '',
    description: options.description || '',
    duration: options.duration || 3500,
    autoClose: options.autoClose !== false
  })

  toasts.value.push(toast)

  // Auto-remove después de la duración
  if (toast.autoClose && toast.duration > 0) {
    setTimeout(() => {
      removeToast(id)
    }, toast.duration)
  }

  return id
}

export function useToast() {

  const success = (title, description = '', duration = 3500) => {
    return addToast({
      type: 'success',
      title,
      description,
      duration
    })
  }

  const error = (title, description = '', duration = 3500) => {
    return addToast({
      type: 'error',
      title,
      description,
      duration
    })
  }

  const warning = (title, description = '', duration = 3500) => {
    return addToast({
      type: 'warning',
      title,
      description,
      duration
    })
  }

  const info = (title, description = '', duration = 3500) => {
    return addToast({
      type: 'info',
      title,
      description,
      duration
    })
  }

  return {
    toasts,
    addToast,
    removeToast,
    success,
    error,
    warning,
    info
  }
}
