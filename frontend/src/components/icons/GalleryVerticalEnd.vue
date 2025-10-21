<template>
  <svg 
    :class="iconClasses"
    fill="none" 
    stroke="currentColor" 
    stroke-width="2" 
    stroke-linecap="round" 
    stroke-linejoin="round" 
    viewBox="0 0 24 24"
  >
    <path d="M7 2v20"/>
    <path d="M15 2v20"/>
    <path d="M3 12h18"/>
  </svg>
</template>

<script lang="ts" setup>
import { computed } from 'vue'

interface Props {
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  class: ''
})

const iconClasses = computed(() => {
  return `size-4 ${props.class}`
})
</script>

<style scoped>
.size-4 { 
  width: 1rem; 
  height: 1rem; 
}
</style>
