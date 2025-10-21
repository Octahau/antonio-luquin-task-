<template>
  <div class="admin-dashboard">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ collapsed: isCollapsed }">
      <div class="sidebar-header">
        <h2 v-if="!isCollapsed">Task Manager</h2>
        <button @click="toggleSidebar" class="toggle-btn" :title="isCollapsed ? 'Expandir' : 'Contraer'">
          <i class="pi pi-bars toggle-icon"></i>
        </button>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li>
            <router-link 
              to="/admin" 
              class="nav-item"
              :class="{ active: $route.name === 'admin-dashboard' }"
              :title="isCollapsed ? 'Inicio / Dashboard' : ''"
            >
              <i class="pi pi-th-large nav-icon"></i>
              <span v-if="!isCollapsed">Inicio / Dashboard</span>
            </router-link>
          </li>
          <li>
            <router-link 
              to="/admin/users" 
              class="nav-item"
              :class="{ active: $route.name === 'admin-users' }"
              :title="isCollapsed ? 'Usuarios' : ''"
            >
              <i class="pi pi-users nav-icon"></i>
              <span v-if="!isCollapsed">Usuarios</span>
            </router-link>
          </li>
          <li>
            <router-link 
              to="/admin/tasks" 
              class="nav-item"
              :class="{ active: $route.name === 'admin-tasks' }"
              :title="isCollapsed ? 'Tareas' : ''"
            >
              <i class="pi pi-check-square nav-icon"></i>
              <span v-if="!isCollapsed">Tareas</span>
            </router-link>
          </li>
        </ul>
      </nav>
      <div class="sidebar-footer">
        <button 
          @click="logout" 
          class="logout-btn"
          :title="isCollapsed ? 'Cerrar Sesión' : ''"
        >
          <i class="pi pi-sign-out nav-icon"></i>
          <span v-if="!isCollapsed">Cerrar Sesión</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content" :class="{ 'main-content-collapsed': isCollapsed }">
      <!-- Header -->
      <header class="dashboard-header">
        <div class="header-content">
          <h1>{{ pageTitle }}</h1>
          <div class="user-info">
            <span class="welcome">Bienvenido, {{ authStore.user?.name }}</span>
            <div class="role-badge">
              <i class="pi pi-user"></i>
            </div>
          </div>
        </div>
      </header>

      <!-- Content Area -->
      <main class="content-area">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'

export default {
  name: 'AdminDashboard',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const authStore = useAuthStore()
    const isCollapsed = ref(false)

    // Computed property para el título dinámico
    const pageTitle = computed(() => {
      const routeName = route.name as string
      switch (routeName) {
        case 'admin-dashboard':
          return 'Panel de Administración'
        case 'admin-users':
          return 'Panel de Usuarios'
        case 'admin-tasks':
          return 'Panel de Tareas'
        default:
          return 'Panel de Administración'
      }
    })

    const toggleSidebar = () => {
      isCollapsed.value = !isCollapsed.value
    }

    const logout = async () => {
      await authStore.logout()
      router.push('/login')
    }

    // Verificar que el usuario sea admin al montar y redirigir si es necesario
    onMounted(() => {
      if (!authStore.isAdmin) {
        router.push('/tasks')
        return
      }
      
      // Si estamos en /admin sin subruta, redirigir al dashboard
      if (router.currentRoute.value.path === '/admin') {
        router.push('/admin/')
      }
    })

    return {
      authStore,
      isCollapsed,
      toggleSidebar,
      logout,
      pageTitle
    }
  }
}
</script>

<style scoped>
.admin-dashboard {
  display: flex;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Sidebar */
.sidebar {
  width: 280px;
  height: 100vh;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-right: 1px solid rgba(255, 255, 255, 0.2);
  display: flex;
  flex-direction: column;
  box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
  transition: width 0.3s ease;
  position: fixed;
  left: 0;
  top: 0;
  overflow: hidden;
}

.sidebar.collapsed {
  width: 80px;
}

.sidebar-header {
  padding: 2rem 1.5rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
}

.sidebar.collapsed .sidebar-header {
  padding: 2rem 1rem;
  justify-content: center;
}

.sidebar.collapsed .toggle-btn {
  position: absolute;
  right: 0.5rem;
}

.sidebar-header h2 {
  margin: 0;
  color: var(--gray-900);
  font-size: 1.5rem;
  font-weight: 700;
  transition: opacity 0.3s ease;
}

.toggle-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 0.5rem;
  color: var(--gray-600);
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
}

.toggle-btn:hover {
  background: rgba(102, 126, 234, 0.1);
  color: var(--primary-blue);
}

.toggle-icon {
  font-size: 1.25rem;
  font-weight: 600;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.sidebar-nav {
  flex: 1;
  padding: 1rem 0;
  overflow-y: auto;
  overflow-x: hidden;
}

/* Estilos para el scrollbar del sidebar */
.sidebar-nav::-webkit-scrollbar {
  width: 4px;
}

.sidebar-nav::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-nav::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 2px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.3);
}

.sidebar-nav ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

.sidebar-nav li {
  margin-bottom: 0.5rem;
}

.nav-item {
  display: flex;
  align-items: center;
  padding: 1rem 1.5rem;
  color: var(--gray-600);
  text-decoration: none;
  transition: all 0.3s ease;
  border-radius: 0;
  position: relative;
}

.sidebar.collapsed .nav-item {
  padding: 1rem;
  justify-content: center;
}

.nav-item:hover {
  background: rgba(102, 126, 234, 0.1);
  color: var(--primary-blue);
}

.nav-item.active {
  background: rgba(102, 126, 234, 0.15);
  color: var(--primary-blue);
  font-weight: 600;
}

.nav-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: var(--primary-blue);
}

.nav-icon {
  font-size: 1.25rem;
  margin-right: 0.75rem;
  width: 24px;
  text-align: center;
  transition: margin 0.3s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

/* Estilos específicos para iconos PrimeIcons */
.nav-icon.pi {
  display: inline-flex !important;
  align-items: center;
  justify-content: center;
}

.sidebar.collapsed .nav-icon {
  margin-right: 0;
}

/* Asegurar que los iconos PrimeIcons se vean bien */
.pi {
  font-style: normal;
  font-variant: normal;
  text-rendering: auto;
  line-height: 1;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.sidebar-footer {
  padding: 1.5rem;
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  flex-shrink: 0;
  margin-top: auto;
}

.sidebar.collapsed .sidebar-footer {
  padding: 1.5rem 1rem;
}

.logout-btn {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 1rem 1.5rem;
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 1rem;
  font-weight: 500;
}

.sidebar.collapsed .logout-btn {
  padding: 1rem;
  justify-content: center;
}

.logout-btn:hover {
  background: rgba(239, 68, 68, 0.2);
  transform: translateY(-1px);
}

/* Main Content */
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: transparent;
  margin-left: 280px;
  transition: margin-left 0.3s ease;
}

.main-content.main-content-collapsed {
  margin-left: 80px;
}

.dashboard-header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  padding: 1rem 2rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto;
}

.dashboard-header h1 {
  margin: 0;
  color: var(--gray-900);
  font-size: 1.5rem;
  font-weight: 700;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.welcome {
  color: var(--gray-600);
  font-size: 0.9rem;
}

.role-badge {
  background: var(--primary-blue);
  color: white;
  padding: 0.5rem;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.875rem;
  font-weight: 600;
}

.role-badge i {
  font-size: 1rem;
}

.content-area {
  flex: 1;
  padding: 2rem;
  overflow-y: auto;
}

/* Tooltips for collapsed state */
[title] {
  position: relative;
}

/* Responsive */
@media (max-width: 768px) {
  .admin-dashboard {
    flex-direction: column;
  }
  
  .sidebar {
    width: 100%;
    height: auto;
    order: 2;
    position: relative;
    left: auto;
    top: auto;
  }
  
  .main-content {
    order: 1;
    margin-left: 0 !important;
  }
  
  .sidebar-nav {
    display: flex;
    overflow-x: auto;
  }
  
  .sidebar-nav ul {
    display: flex;
    gap: 1rem;
    padding: 0 1rem;
  }
  
  .sidebar-nav li {
    margin-bottom: 0;
    flex-shrink: 0;
  }
  
  .nav-item {
    padding: 0.75rem 1rem;
    white-space: nowrap;
  }
  
  .header-content {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .content-area {
    padding: 1rem;
  }
}
</style>
