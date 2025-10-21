<template>
  <div class="toast-wrapper">
    <transition-group name="toast-list">
      <ToastAlert
        v-for="(toast, index) in toasts"
        :key="toast.id"
        :show="toast.show"
        :type="toast.type"
        :title="toast.title"
        :description="toast.description"
        :duration="toast.duration"
        :auto-close="toast.autoClose"
        :style="{ top: `${index * 4.5}rem` }"
        @close="removeToast(toast.id)"
      />
    </transition-group>
  </div>
</template>

<script lang="ts">
import { useToast } from '../composables/useToast'
import ToastAlert from './ToastAlert.vue'

export default {
  name: 'ToastContainer',
  components: {
    ToastAlert
  },
  setup() {
    const { toasts, removeToast } = useToast()

    return {
      toasts,
      removeToast
    }
  }
}
</script>

<style scoped>
.toast-wrapper {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
  z-index: 9999;
}

/* Animaciones para lista de toasts */
.toast-list-move {
  transition: all 0.3s ease;
}
</style>
