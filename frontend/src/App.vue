<script setup lang="ts">
import { RouterView } from 'vue-router'
import ToastContainer from './components/ToastContainer.vue'
</script>

<template>
  <RouterView />
  <ToastContainer />
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

/* Estilos globales para opciones de select */
select option {
  background: rgba(255, 255, 255, 0.98);
  color: var(--gray-800);
  font-weight: 500;
  padding: 0.75rem 1rem;
  border: none;
  font-size: 0.95rem;
}

select option:hover {
  background: rgba(29, 78, 216, 0.1);
  color: var(--primary-blue-dark);
}

select option:checked,
select option[selected] {
  background: var(--primary-blue);
  color: white;
  font-weight: 600;
}

/* Estilos globales para el calendario nativo del navegador */
input[type="date"]::-webkit-calendar-picker-indicator {
  cursor: pointer;
  border-radius: 6px;
  margin-right: 8px;
  padding: 10px;
  background-color: var(--primary-blue);
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3crect x='3' y='4' width='18' height='18' rx='2' ry='2'%3e%3c/rect%3e%3cline x1='16' y1='2' x2='16' y2='6'%3e%3c/line%3e%3cline x1='8' y1='2' x2='8' y2='6'%3e%3c/line%3e%3cline x1='3' y1='10' x2='21' y2='10'%3e%3c/line%3e%3c/svg%3e");
  background-size: 20px 20px;
  background-repeat: no-repeat;
  background-position: center;
  transition: all 0.3s ease;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

input[type="date"]::-webkit-calendar-picker-indicator:hover {
  background-color: var(--primary-blue-dark);
  transform: scale(1.1);
  filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
}

/* Estilos para el contenido del input date */
input[type="date"]::-webkit-datetime-edit {
  padding: 10px 14px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.1);
  font-weight: 600;
  font-size: 1rem;
}

input[type="date"]::-webkit-datetime-edit-fields-wrapper {
  background: transparent;
}

input[type="date"]::-webkit-datetime-edit-text {
  color: var(--gray-600);
  padding: 0 6px;
  font-weight: 500;
}

input[type="date"]::-webkit-datetime-edit-month-field,
input[type="date"]::-webkit-datetime-edit-day-field,
input[type="date"]::-webkit-datetime-edit-year-field {
  color: var(--gray-800);
  background: rgba(255, 255, 255, 0.3);
  border-radius: 6px;
  padding: 4px 8px;
  margin: 0 3px;
  font-weight: 700;
  font-size: 1rem;
  transition: all 0.2s ease;
}

input[type="date"]::-webkit-datetime-edit-month-field:hover,
input[type="date"]::-webkit-datetime-edit-day-field:hover,
input[type="date"]::-webkit-datetime-edit-year-field:hover {
  background: rgba(29, 78, 216, 0.1);
  color: var(--primary-blue-dark);
}

/* Estilos para el popup del calendario (solo Chrome/Safari) */
input[type="date"]::-webkit-calendar-picker-indicator:active {
  background-color: var(--primary-blue-dark);
  transform: scale(0.95);
}

/* Mejoras para el display del calendario */
input[type="date"] {
  position: relative;
  /* Asegurar que el calendario tenga un tamaño consistente */
  min-width: 150px;
}

input[type="date"]:focus {
  background: rgba(255, 255, 255, 0.95);
}

/* Mejorar la apariencia cuando está deshabilitado */
input[type="date"]:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

input[type="date"]:disabled::-webkit-calendar-picker-indicator {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Estilos para diferentes estados del calendario */
input[type="date"]:valid {
  border-color: rgba(16, 185, 129, 0.3);
}

input[type="date"]:invalid {
  border-color: rgba(239, 68, 68, 0.3);
}
</style>
