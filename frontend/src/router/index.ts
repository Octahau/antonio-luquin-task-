import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import LoginView from '../views/LoginView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      redirect: '/login'
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { requiresGuest: true, title: 'Iniciar Sesión - Task Manager' }
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue'),
      meta: { requiresGuest: true, title: 'Registrarse - Task Manager' }
    },
    {
      path: '/tasks',
      name: 'tasks',
      component: () => import('../views/TasksViews.vue'),
      meta: { requiresAuth: true, title: 'Mis Tareas - Task Manager' }
    },
    {
      path: '/admin',
      name: 'admin',
      component: () => import('../views/AdminDashboard.vue'),
      meta: { requiresAuth: true, requiresAdmin: true, title: 'Panel de Administración - Task Manager' },
      children: [
        {
          path: '',
          name: 'admin-dashboard',
          component: () => import('../components/admin/DashboardOverview.vue'),
          meta: { title: 'Dashboard - Panel de Administración' }
        },
        {
          path: 'users',
          name: 'admin-users',
          component: () => import('../components/admin/UsersView.vue'),
          meta: { title: 'Usuarios - Panel de Administración' }
        },
        {
          path: 'tasks',
          name: 'admin-tasks',
          component: () => import('../components/admin/AdminTasksView.vue'),
          meta: { title: 'Tareas - Panel de Administración' }
        }
      ]
    }
  ],
})

// Guard de navegación para autenticación
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  // Inicializar auth si no está inicializado
  if (!authStore.user && !authStore.token) {
    authStore.initializeAuth()
  }
  
  // Si está autenticado y trata de acceder a la página de login/register o a la raíz, redirigir según el rol
  if (authStore.isAuthenticated && (to.path === '/' || to.path === '/login' || to.path === '/register')) {
    if (authStore.isAdmin) {
      next('/admin')
    } else {
      next('/tasks')
    }
  }
  // Si no está autenticado y trata de acceder a una ruta protegida, redirigir a login
  else if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  }
  // Verificar si requiere admin y si el usuario no es admin
  else if (to.meta.requiresAdmin && !authStore.isAdmin) {
    next('/tasks') // Redirigir a tasks si no es admin
  }
  // Si está autenticado y trata de acceder a una ruta solo para invitados (excepto la raíz), redirigir según el rol
  else if (to.meta.requiresGuest && authStore.isAuthenticated && to.path !== '/') {
    if (authStore.isAdmin) {
      next('/admin')
    } else {
      next('/tasks')
    }
  } else {
    next()
  }
})

// Cambiar el título de la página dinámicamente
router.afterEach((to) => {
  // Usar el título de la meta si existe, sino usar el título por defecto
  const title = to.meta.title as string || 'Task Manager'
  document.title = title
})

export default router