<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useToast } from '../composables/useToast'
import { authService } from '../services/auth'

const router = useRouter()
const authStore = useAuthStore()
const { success, error: showError } = useToast()

// Reactive data
const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
const isLoading = ref(false)
const isGoogleButtonReady = ref(false)
const errors = ref<Record<string, string>>({})

// Handle form submission
const handleSubmit = async (e: Event) => {
  e.preventDefault()
  
  // Reset errors
  errors.value = {}
  isLoading.value = true

  try {
    // Basic validation
    if (!name.value.trim()) {
      errors.value.name = 'El nombre es obligatorio'
    }
    
    if (!email.value.trim()) {
      errors.value.email = 'El correo es obligatorio'
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
      errors.value.email = 'Formato de correo inválido'
    }
    
    if (!password.value) {
      errors.value.password = 'La contraseña es obligatoria'
    } else if (password.value.length < 8) {
      errors.value.password = 'La contraseña debe tener al menos 8 caracteres'
    }
    
    if (!passwordConfirmation.value) {
      errors.value.passwordConfirmation = 'Confirma tu contraseña'
    } else if (password.value !== passwordConfirmation.value) {
      errors.value.passwordConfirmation = 'Las contraseñas no coinciden'
    }

    // If there are validation errors, don't proceed
    if (Object.keys(errors.value).length > 0) {
      isLoading.value = false
      return
    }

    // Call register API
    await authStore.register({
      name: name.value.trim(),
      email: email.value.trim(),
      password: password.value,
      password_confirmation: passwordConfirmation.value
    })

    // Show success message and redirect
    success('¡Registro exitoso!', 'Te has registrado correctamente. Bienvenido a Task Manager')
    setTimeout(() => {
      // Los nuevos usuarios se registran como 'viewer' por defecto, así que van a /tasks
      router.push('/tasks')
    }, 1000)
  } catch (error: unknown) {
    console.error('Registration error:', error)
    
    // Handle API errors
    const apiError = error as { errors?: Record<string, string>; message?: string }
    const errorMessage = apiError.message || 'Error al registrarse. Inténtalo de nuevo.'
    
    if (apiError.errors) {
      errors.value = apiError.errors
    } else {
      showError('Error de registro', errorMessage)
    }
  } finally {
    isLoading.value = false
  }
}

// Handle sign in click (go back to login)
const handleSignIn = () => {
  router.push('/login')
}

// Toggle password visibility
const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}

const togglePasswordConfirmationVisibility = () => {
  showPasswordConfirmation.value = !showPasswordConfirmation.value
}

// Initialize Google Auth on component mount
onMounted(async () => {
  // Load Google Auth library
  if (!(window as any).google && import.meta.env.VITE_GOOGLE_CLIENT_ID) {
    try {
      await new Promise<void>((resolve) => {
        const script = document.createElement('script')
        script.src = 'https://accounts.google.com/gsi/client'
        script.onload = () => resolve()
        document.head.appendChild(script)
      })
      
      // Esperar un poco para que Google se cargue completamente
      setTimeout(() => {
        if ((window as any).google && (window as any).google.accounts && (window as any).google.accounts.id) {
          // Inicializar Google Auth
          (window as any).google.accounts.id.initialize({
            client_id: import.meta.env.VITE_GOOGLE_CLIENT_ID,
            callback: async (response: any) => {
              try {
                const result = await authService.loginWithGoogle(response.credential)
                
                // Actualizar el store con los datos del usuario
                authStore.user = result.user
                authStore.token = result.token
                
                success('Bienvenido', 'Te has registrado correctamente con Google')
                
                setTimeout(() => {
                  if (authStore.isAdmin) {
                    router.push('/admin')
                  } else {
                    router.push('/tasks')
                  }
                }, 500)
              } catch (err: any) {
                console.error('Error en Google login:', err)
                showError('Error de autenticación', err.message || 'Error al registrarse con Google')
              }
            },
            auto_select: false,
            cancel_on_tap_outside: true
          })

          // Renderizar el botón de Google
          try {
            (window as any).google.accounts.id.renderButton(
              document.getElementById('google-signin-button-register'),
              {
                theme: 'outline',
                size: 'large',
                text: 'continue_with',
                shape: 'rectangular',
                logo_alignment: 'left'
              }
            )
            isGoogleButtonReady.value = true
          } catch (renderError) {
            console.error('Error rendering Google button:', renderError)
          }
        }
      }, 100)
    } catch (err: any) {
      console.error('Error loading Google Auth:', err)
    }
  }
})
</script>

<template>
  <div class="register-form-container">
    <!-- Título principal -->
    <div class="form-header">
      <div class="logo-container">
        <i class="pi pi-check-circle logo-icon"></i>
      </div>
      <h1 class="form-title">
        Task Manager
      </h1>
      <p class="form-subtitle">
        Crea tu cuenta para gestionar tus tareas
      </p>
    </div>

    <!-- Error general -->
    <div v-if="errors.general" class="error-message">
      {{ errors.general }}
    </div>

    <form @submit="handleSubmit" class="register-form">
      <!-- Campo de nombre -->
      <div class="form-field">
        <label for="name" class="field-label">
          Nombre completo
        </label>
        <input
          id="name"
          v-model="name"
          type="text"
          placeholder="Ingresa tu nombre completo"
          required
          class="form-input"
          :class="{ 'input-error': errors.name }"
        />
        <div v-if="errors.name" class="error-text">{{ errors.name }}</div>
      </div>

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
            placeholder="Mínimo 8 caracteres"
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

      <!-- Campo de confirmación de contraseña -->
      <div class="form-field">
        <label for="passwordConfirmation" class="field-label">
          Confirmar contraseña
        </label>
        <div class="password-input-wrapper">
          <input
            id="passwordConfirmation"
            v-model="passwordConfirmation"
            :type="showPasswordConfirmation ? 'text' : 'password'"
            required
            placeholder="Repite tu contraseña"
            class="form-input password-input"
            :class="{ 'input-error': errors.passwordConfirmation }"
          />
          <button
            type="button"
            @click="togglePasswordConfirmationVisibility"
            class="password-toggle-btn"
          >
            <svg v-if="!showPasswordConfirmation" class="password-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <svg v-else class="password-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
            </svg>
          </button>
        </div>
        <div v-if="errors.passwordConfirmation" class="error-text">{{ errors.passwordConfirmation }}</div>
      </div>
      
      <!-- Botón de registro con Google -->
      <div class="google-auth-container">
        <div 
          id="google-signin-button-register"
          class="google-signin-wrapper"
        ></div>
      </div>
      
      <!-- Botón principal de registro -->
      <button
        type="submit"
        class="primary-login-btn"
        :disabled="isLoading"
      >
        {{ isLoading ? 'Creando cuenta...' : 'Crear cuenta' }}
      </button>
    </form>
    
    <!-- Texto inferior -->
    <div class="register-link-container">
      <span class="register-text">¿Ya tienes una cuenta? </span>
      <button 
        type="button" 
        @click="handleSignIn" 
        class="register-link"
      >
        Iniciar sesión
      </button>
    </div>
  </div>
</template>

<style scoped>
.register-form-container {
  width: 100%;
  animation: fadeIn 0.6s ease-out;
}

/* Header */
.form-header {
  text-align: center;
  margin-bottom: 2rem;
}

.logo-container {
  display: flex;
  justify-content: center;
  margin-bottom: 1rem;
}

.logo-icon {
  font-size: 3.5rem;
  color: #1d4ed8;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.05);
    opacity: 0.9;
  }
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
.register-form {
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
  border-color: var(--gray-400);
}

.form-input::placeholder {
  color: var(--gray-400);
}

.input-error {
  border-color: var(--error) !important;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
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
  color: var(--gray-400);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s ease;
}

.password-toggle-btn:hover {
  color: var(--gray-600);
}

.password-toggle-btn:focus {
  outline: none;
  color: var(--primary-blue);
}

.password-icon {
  width: 1.25rem;
  height: 1.25rem;
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

/* Google login button */
.google-login-btn {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 1rem;
  border: 1px solid var(--gray-300);
  border-radius: 0.5rem;
  background-color: var(--white);
  color: var(--gray-700);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: var(--shadow-sm);
}

.google-login-btn:hover {
  background-color: var(--gray-50);
  box-shadow: var(--shadow-md);
}

.google-login-btn:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
  border-color: var(--primary-blue);
}

.google-icon {
  margin-right: 0.75rem;
  width: 1.25rem;
  height: 1.25rem;
}

.google-text {
  font-weight: 500;
}

/* Primary register button */
.primary-login-btn {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 0.5rem;
  background-color: var(--primary-blue);
  color: var(--white);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: var(--shadow-sm);
}

.primary-login-btn:hover:not(:disabled) {
  background-color: var(--primary-blue-dark);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.primary-login-btn:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: var(--shadow-sm);
}

.primary-login-btn:disabled {
  background-color: var(--gray-400);
  cursor: not-allowed;
  transform: none;
}

.primary-login-btn:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
}

/* Register link */
.register-link-container {
  margin-top: 2rem;
  text-align: center;
  font-size: 0.875rem;
}

.register-text {
  color: var(--gray-600);
}

.register-link {
  background: none;
  border: none;
  color: var(--primary-blue);
  font-weight: 500;
  text-decoration: underline;
  cursor: pointer;
  transition: color 0.2s ease;
  font-size: inherit;
  padding: 0;
  margin-left: 0.25rem;
}

.register-link:hover {
  color: var(--primary-blue-dark);
}

.register-link:focus {
  outline: none;
  color: var(--primary-blue-dark);
}

/* Google Auth Container */
.google-auth-container {
  margin-bottom: 1.5rem;
}

.google-signin-wrapper {
  display: flex;
  justify-content: center;
}

.google-signin-wrapper iframe {
  border-radius: 0.75rem;
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
  
  .register-form {
    gap: 1.25rem;
  }
}
</style>
