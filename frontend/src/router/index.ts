import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: { requiresGuest: true }
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { requiresGuest: true }
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue'),
      meta: { requiresGuest: true }
    },
    {
      path: '/tasks',
      name: 'tasks',
      component: () => import('../views/TasksViews.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('../views/AboutView.vue'),
    },
  ],
})

// Guard de navegación para autenticación
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  // Inicializar auth si no está inicializado
  if (!authStore.user && !authStore.token) {
    authStore.initializeAuth()
  }
  
  // Si está autenticado y trata de acceder a la página de login/register o a la raíz, redirigir a tasks
  if (authStore.isAuthenticated && (to.path === '/' || to.path === '/login' || to.path === '/register')) {
    next('/tasks')
  }
  // Si no está autenticado y trata de acceder a una ruta protegida, redirigir a login
  else if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  }
  // Si está autenticado y trata de acceder a una ruta solo para invitados (excepto la raíz), redirigir a tasks
  else if (to.meta.requiresGuest && authStore.isAuthenticated && to.path !== '/') {
    next('/tasks')
  } else {
    next()
  }
})

export default router