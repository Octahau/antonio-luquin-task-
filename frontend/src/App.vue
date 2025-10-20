<script setup lang="ts">
import { RouterView } from 'vue-router'
</script>

<template>
  <RouterView />
</template>

<style>
/* Variables de color globales */
:root {
  /* Colores principales */
  --primary-blue: #1d4ed8;
  --primary-blue-dark: #1e40af;
  --primary-blue-light: #3b82f6;
  
  /* Colores del gradiente */
  --gradient-start: #334155;
  --gradient-middle: #1e3a8a;
  --gradient-end: #581c87;
  
  /* Colores neutros */
  --white: #ffffff;
  --gray-50: #f9fafb;
  --gray-100: #f3f4f6;
  --gray-200: #e5e7eb;
  --gray-300: #d1d5db;
  --gray-400: #9ca3af;
  --gray-500: #6b7280;
  --gray-600: #4b5563;
  --gray-700: #374151;
  --gray-800: #1f2937;
  --gray-900: #111827;
  
  /* Colores de estado */
  --success: #10b981;
  --warning: #f59e0b;
  --error: #ef4444;
  
  /* Sombras */
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

html, body {
  margin: 0 !important;
  padding: 0 !important;
  background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-middle) 50%, var(--gradient-end) 100%);
  background-attachment: fixed;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  color: var(--gray-900);
  line-height: 1.6;
  min-height: 100vh;
}

#app {
  margin: 0 !important;
  padding: 0 !important;
  background: transparent;
  min-height: 100vh;
}

/* Ocultar completamente las Vue DevTools */
#vue-inspector-container,
#__vue-devtools-container__,
#__vue-devtools-component-inspector__,
.vue-devtools__anchor,
.vue-devtools__panel,
.vue-devtools-frame {
  display: none !important;
  visibility: hidden !important;
  opacity: 0 !important;
  pointer-events: none !important;
}

* {
  box-sizing: border-box;
}

/* Estilos base para botones */
.btn-primary {
  background-color: var(--primary-blue);
  color: var(--white);
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: var(--shadow-sm);
}

.btn-primary:hover:not(:disabled) {
  background-color: var(--primary-blue-dark);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.btn-primary:disabled {
  background-color: var(--gray-400);
  cursor: not-allowed;
  transform: none;
}

.btn-secondary {
  background-color: var(--gray-500);
  color: var(--white);
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: var(--shadow-sm);
}

.btn-secondary:hover {
  background-color: var(--gray-600);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

/* Estilos base para inputs */
.form-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--gray-300);
  border-radius: 0.5rem;
  font-size: 1rem;
  color: var(--gray-900);
  background-color: var(--white);
  transition: all 0.2s ease;
}

.form-input:focus {
  outline: none;
  border-color: var(--primary-blue);
  box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
}

.form-input:hover {
  border-color: var(--gray-400);
}

.form-input::placeholder {
  color: var(--gray-400);
}
</style>
