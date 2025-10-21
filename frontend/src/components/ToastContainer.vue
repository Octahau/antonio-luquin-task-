<template>
  <div class="toast-wrapper">
    <ToastAlert
      v-for="toast in toasts"
      :key="toast.id"
      :show="toast.show"
      :type="toast.type"
      :title="toast.title"
      :description="toast.description"
      :duration="toast.duration"
      :auto-close="toast.autoClose"
      @close="removeToast(toast.id)"
    />
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
</style>
