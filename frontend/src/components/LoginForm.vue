<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useToast } from '../composables/useToast'
import GoogleIcon from './icons/GoogleIcon.vue'

const router = useRouter()
const authStore = useAuthStore()
const { success, error } = useToast()

// Reactive data
const email = ref('')
const password = ref('')
const showPassword = ref(false)
const isLoading = ref(false)
const errors = ref<Record<string, string>>({})

// Define emits
const emit = defineEmits<{
  'github-login': []
}>()

// Handle form submission
const handleSubmit = async (e: Event) => {
  e.preventDefault()
  
  // Reset errors
  errors.value = {}
  isLoading.value = true

  try {
    // Basic validation
    if (!email.value.trim()) {
      errors.value.email = 'El correo es obligatorio'
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
      errors.value.email = 'Formato de correo inválido'
    }
    
    if (!password.value) {
      errors.value.password = 'La contraseña es obligatoria'
    }

    // If there are validation errors, don't proceed
    if (Object.keys(errors.value).length > 0) {
      isLoading.value = false
      return
    }

    // Call login API
    await authStore.login({
      email: email.value.trim(),
      password: password.value
    })

    // Show success message and redirect
    success('Bienvenido', 'Has iniciado sesión correctamente')
    setTimeout(() => {
      // Redirigir según el rol del usuario
      if (authStore.isAdmin) {
        router.push('/admin')
      } else {
        router.push('/tasks')
      }
    }, 500)
  } catch (error: unknown) {
    console.error('Login error:', error)
    
    // Handle API errors
    const apiError = error as { errors?: Record<string, string>; message?: string }
    const errorMessage = apiError.message || 'Error al iniciar sesión. Verifica tus credenciales.'
    
    if (apiError.errors) {
      errors.value = apiError.errors
    } else {
      error('Error de autenticación', errorMessage)
    }
  } finally {
    isLoading.value = false
  }
}

// Handle sign up click
const handleSignUp = () => {
  router.push('/register')
}

// Handle Google login click
const handleGoogleLogin = () => {
  emit('github-login') // Reutilizando el emit existente
}

// Toggle password visibility
const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}
</script>

<template>
  <div class="login-form-container">
    <!-- Título principal -->
    <div class="form-header">
      <h1 class="form-title">
        Task Manager
      </h1>
      <p class="form-subtitle">
        Inicie sesión para gestionar sus tareas
      </p>
    </div>

    <!-- Error general -->
    <div v-if="errors.general" class="error-message">
      {{ errors.general }}
    </div>

    <form @submit="handleSubmit" class="login-form">
      <!-- Campo de correo -->
      <div class="form-field">
        <label for="email" class="field-label">
          Correo electrónico
        </label>
        <input
          id="email"
          v-model="email"
          type="email"
          placeholder="usuario@ejemplo.com"
          required
          class="form-input"
          :class="{ 'input-error': errors.email }"
        />
        <div v-if="errors.email" class="error-text">{{ errors.email }}</div>
      </div>
      
      <!-- Campo de contraseña -->
      <div class="form-field">
        <label for="password" class="field-label">
          Contraseña
        </label>
        <div class="password-input-wrapper">
          <input
            id="password"
            v-model="password"
            :type="showPassword ? 'text' : 'password'"
            required
            placeholder="Ingrese su contraseña"
            class="form-input password-input"
            :class="{ 'input-error': errors.password }"
          />
          <button
            type="button"
            @click="togglePasswordVisibility"
            class="password-toggle-btn"
          >
            <svg v-if="!showPassword" class="password-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <svg v-else class="password-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
            </svg>
          </button>
        </div>
        <div v-if="errors.password" class="error-text">{{ errors.password }}</div>
      </div>
      
      <!-- Botón de login con Google -->
      <button
        type="button"
        @click="handleGoogleLogin"
        class="google-login-btn"
      >
        <GoogleIcon class="google-icon" />
        <span class="google-text">Continuar con Google</span>
      </button>
      
      <!-- Botón principal de inicio de sesión -->
      <button
        type="submit"
        class="primary-login-btn"
        :disabled="isLoading"
      >
        {{ isLoading ? 'Iniciando sesión...' : 'Iniciar sesión' }}
      </button>
    </form>
    
    <!-- Texto inferior -->
    <div class="register-link-container">
      <span class="register-text">¿No tiene una cuenta? </span>
      <button 
        type="button" 
        @click="handleSignUp" 
        class="register-link"
      >
        Registrarse
      </button>
    </div>
  </div>
</template>

<style scoped>
.login-form-container {
  width: 100%;
  animation: fadeIn 0.6s ease-out;
}

/* Header */
.form-header {
  text-align: center;
  margin-bottom: 2rem;
}

.form-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: var(--gray-900);
  margin: 0 0 0.5rem 0;
  letter-spacing: -0.025em;
  line-height: 1.2;
}

.form-subtitle {
  color: var(--gray-600);
  font-size: 0.875rem;
  margin: 0;
  line-height: 1.5;
}

/* Form */
.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Form fields */
.form-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.field-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  line-height: 1.5;
}

.form-input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid var(--gray-300);
  border-radius: 0.5rem;
  font-size: 1rem;
  line-height: 1.5;
  color: var(--gray-900);
  background-color: var(--white);
  transition: all 0.2s ease;
  box-sizing: border-box;
}

.form-input:focus {
  outline: none;
  border-color: var(--primary-blue);
  box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
}

.form-input:hover {
  border-color: #9ca3af;
}

.form-input::placeholder {
  color: #9ca3af;
}

.input-error {
  border-color: var(--error) !important;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
}

/* Error messages */
.error-text {
  color: var(--error);
  font-size: 0.75rem;
  margin-top: 0.25rem;
}

.error-message {
  background-color: #fef2f2;
  color: var(--error);
  padding: 0.75rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
  border: 1px solid #fecaca;
  font-size: 0.875rem;
}

/* Password input wrapper */
.password-input-wrapper {
  position: relative;
}

.password-input {
  padding-right: 3rem;
}

.password-toggle-btn {
  position: absolute;
  right: 0;
  top: 50%;
  transform: translateY(-50%);
  padding: 0.75rem;
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s ease;
}

.password-toggle-btn:hover {
  color: #6b7280;
}

.password-toggle-btn:focus {
  outline: none;
  color: #2563eb;
}

.password-icon {
  width: 1.25rem;
  height: 1.25rem;
}

/* Google login button */
.google-login-btn {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  background-color: #ffffff;
  color: #374151;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.google-login-btn:hover {
  background-color: #f9fafb;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.google-login-btn:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
  border-color: #2563eb;
}

.google-icon {
  margin-right: 0.75rem;
  width: 1.25rem;
  height: 1.25rem;
}

.google-text {
  font-weight: 500;
}

/* Primary login button */
.primary-login-btn {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 0.5rem;
  background-color: #1d4ed8;
  color: #ffffff;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.primary-login-btn:hover:not(:disabled) {
  background-color: #1e40af;
  transform: translateY(-1px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.primary-login-btn:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.primary-login-btn:disabled {
  background-color: var(--gray-400);
  cursor: not-allowed;
  transform: none;
}

.primary-login-btn:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* Register link */
.register-link-container {
  margin-top: 2rem;
  text-align: center;
  font-size: 0.875rem;
}

.register-text {
  color: #6b7280;
}

.register-link {
  background: none;
  border: none;
  color: #1d4ed8;
  font-weight: 500;
  text-decoration: underline;
  cursor: pointer;
  transition: color 0.2s ease;
  font-size: inherit;
  padding: 0;
  margin-left: 0.25rem;
}

.register-link:hover {
  color: #1e40af;
}

.register-link:focus {
  outline: none;
  color: #1e40af;
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive */
@media (max-width: 640px) {
  .form-title {
    font-size: 1.5rem;
  }
  
  .form-header {
    margin-bottom: 1.5rem;
  }
  
  .login-form {
    gap: 1.25rem;
  }
}
</style>