import './assets/main.css'
import 'primeicons/primeicons.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/auth'

const app = createApp(App)

const pinia = createPinia()
app.use(pinia)
app.use(router)

// Inicializar autenticación
const authStore = useAuthStore()
authStore.initializeAuth().catch(error => {
  console.warn('Error inicializando autenticación:', error)
})

app.mount('#app')
