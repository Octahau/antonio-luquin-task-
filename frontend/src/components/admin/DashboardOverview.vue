<template>
  <div class="dashboard-overview">
    <div class="overview-header">
      <h2>Análisis de Tareas del Sistema</h2>
      <p>Resumen general del rendimiento y estadísticas</p>
    </div>

    <!-- Metrics Cards -->
    <div class="metrics-grid">
      <div class="metric-card">
        <div class="metric-icon pending">
          <i class="pi pi-clock"></i>
        </div>
        <div class="metric-content">
          <h3>{{ metrics.pending }}</h3>
          <p>Tareas Pendientes</p>
          <small>Este mes</small>
        </div>
      </div>

      <div class="metric-card">
        <div class="metric-icon in-progress">
          <i class="pi pi-refresh"></i>
        </div>
        <div class="metric-content">
          <h3>{{ metrics.in_progress }}</h3>
          <p>En Progreso</p>
          <small>Este mes</small>
        </div>
      </div>

      <div class="metric-card">
        <div class="metric-icon completed">
          <i class="pi pi-check"></i>
        </div>
        <div class="metric-content">
          <h3>{{ metrics.completed }}</h3>
          <p>Completadas</p>
          <small>Este mes</small>
        </div>
      </div>

      <div class="metric-card">
        <div class="metric-icon total">
          <i class="pi pi-chart-bar"></i>
        </div>
        <div class="metric-content">
          <h3>{{ completionPercentage }}%</h3>
          <p>Porcentaje de Avance</p>
          <small>General</small>
        </div>
      </div>
    </div>

    <!-- Chart Section -->
    <div class="chart-section">
      <div class="chart-header">
        <h3>Tareas Completadas por Mes</h3>
        <p>Análisis mensual del rendimiento</p>
      </div>
      <div class="chart-container">
        <canvas ref="chartCanvas" id="tasksChart"></canvas>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useTasksStore } from '../../stores/tasks'

export default {
  name: 'DashboardOverview',
  setup() {
    const tasksStore = useTasksStore()
    const chartCanvas = ref(null)
    
    const metrics = ref({
      pending: 0,
      in_progress: 0,
      completed: 0,
      total: 0
    })

    const completionPercentage = computed(() => {
      if (metrics.value.total === 0) return 0
      return Math.round((metrics.value.completed / metrics.value.total) * 100)
    })

    const loadMetrics = async () => {
      try {
        await tasksStore.fetchTasks()
        
        const currentMonth = new Date().getMonth()
        const currentYear = new Date().getFullYear()
        
        const monthlyTasks = tasksStore.tasks.filter(task => {
          const taskDate = new Date(task.created_at)
          return taskDate.getMonth() === currentMonth && taskDate.getFullYear() === currentYear
        })

        metrics.value = {
          pending: monthlyTasks.filter(task => task.status === 'pending').length,
          in_progress: monthlyTasks.filter(task => task.status === 'in_progress').length,
          completed: monthlyTasks.filter(task => task.status === 'completed').length,
          total: monthlyTasks.length
        }

        // Cargar datos para el gráfico
        loadChartData()
      } catch (error) {
        console.error('Error loading metrics:', error)
      }
    }

    const loadChartData = async () => {
      try {
        // Obtener tareas de los últimos 12 meses
        const allTasks = tasksStore.tasks
        
        // Agrupar por mes
        const monthlyData = {}
        const currentDate = new Date()
        
        // Inicializar últimos 12 meses
        for (let i = 11; i >= 0; i--) {
          const date = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1)
          const key = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`
          monthlyData[key] = 0
        }

        // Contar tareas completadas por mes
        allTasks.forEach(task => {
          if (task.status === 'completed') {
            const taskDate = new Date(task.created_at)
            const key = `${taskDate.getFullYear()}-${String(taskDate.getMonth() + 1).padStart(2, '0')}`
            if (monthlyData.hasOwnProperty(key)) {
              monthlyData[key]++
            }
          }
        })

        // Crear el gráfico
        createChart(monthlyData)
      } catch (error) {
        console.error('Error loading chart data:', error)
      }
    }

    const createChart = (data) => {
      if (!chartCanvas.value) return

      // Esperar un frame para asegurar que el DOM esté actualizado
      requestAnimationFrame(() => {
        const ctx = chartCanvas.value.getContext('2d')
        drawChart(ctx, data)
      })
    }

    const drawChart = (ctx, data) => {
      if (!chartCanvas.value) return
      
      // Datos del gráfico
      const months = Object.keys(data).map(key => {
        const [year, month] = key.split('-')
        const date = new Date(year, month - 1)
        // Formato más corto para móviles
        const isMobile = window.innerWidth <= 768
        return date.toLocaleDateString('es-ES', { 
          month: isMobile ? 'short' : 'short', 
          year: '2-digit' 
        })
      })
      
      const values = Object.values(data)

      // Crear gráfico simple usando canvas
      const canvas = chartCanvas.value
      const rect = canvas.getBoundingClientRect()
      
      // Usar el tamaño real del contenedor
      canvas.width = rect.width
      canvas.height = rect.height
      
      // Configuración responsiva
      const isMobile = rect.width < 600
      const barSpacing = isMobile ? 0.05 : 0.1
      const labelAreaHeight = isMobile ? 40 : 60
      const valueLabelOffset = isMobile ? 15 : 30
      const fontSize = isMobile ? '10px' : '12px'
      const monthFontSize = isMobile ? '8px' : '10px'
      
      const maxValue = Math.max(...values, 1)
      const availableWidth = rect.width - (valueLabelOffset * 2)
      const barWidth = availableWidth / months.length
      const maxHeight = rect.height - labelAreaHeight
      const padding = barWidth * barSpacing

      // Limpiar canvas
      ctx.clearRect(0, 0, rect.width, rect.height)

      // Dibujar barras
      months.forEach((month, index) => {
        if (values[index] === 0) return
        
        const barHeight = Math.max((values[index] / maxValue) * maxHeight, 2) // Mínimo 2px de altura
        const x = valueLabelOffset + index * barWidth + padding
        const y = rect.height - barHeight - (labelAreaHeight / 2)
        const actualBarWidth = barWidth - (padding * 2)

        // Color de la barra
        ctx.fillStyle = '#667eea'
        ctx.fillRect(x, y, actualBarWidth, barHeight)

        // Etiqueta del valor (solo si hay espacio suficiente)
        if (!isMobile || actualBarWidth > 20) {
          ctx.fillStyle = '#374151'
          ctx.font = `${fontSize} Inter, sans-serif`
          ctx.textAlign = 'center'
          ctx.fillText(
            values[index].toString(), 
            x + actualBarWidth / 2, 
            Math.max(y - 5, 15)
          )
        }

        // Etiqueta del mes
        ctx.fillStyle = '#6b7280'
        ctx.font = `${monthFontSize} Inter, sans-serif`
        ctx.textAlign = 'center'
        
        // Rotar texto del mes en móviles si es necesario
        if (isMobile && month.length > 8) {
          ctx.save()
          ctx.translate(x + actualBarWidth / 2, rect.height - 5)
          ctx.rotate(-Math.PI / 4)
          ctx.fillText(month.substring(0, 6) + '...', 0, 0)
          ctx.restore()
        } else {
          ctx.fillText(month, x + actualBarWidth / 2, rect.height - 10)
        }
      })
    }

    // Listener para redibujar el gráfico en resize
    let resizeTimeout
    const resizeHandler = () => {
      if (chartCanvas.value) {
        // Debounce para evitar demasiadas redraws
        clearTimeout(resizeTimeout)
        resizeTimeout = setTimeout(() => {
          loadChartData()
        }, 150)
      }
    }

    onMounted(() => {
      loadMetrics()
      window.addEventListener('resize', resizeHandler)
    })

    onUnmounted(() => {
      window.removeEventListener('resize', resizeHandler)
    })

    return {
      chartCanvas,
      metrics,
      completionPercentage
    }
  }
}
</script>

<style scoped>
.dashboard-overview {
  max-width: 1200px;
  margin: 0 auto;
  animation: fadeIn 0.5s ease-out;
}

.overview-header {
  text-align: center;
  margin-bottom: 3rem;
}

.overview-header h2 {
  color: white;
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
}

.overview-header p {
  color: rgba(255, 255, 255, 0.8);
  font-size: 1.125rem;
  margin: 0;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.metric-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 1rem;
  padding: 2rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
}

.metric-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 35px 70px -12px rgba(0, 0, 0, 0.3);
}

.metric-icon {
  width: 60px;
  height: 60px;
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.metric-icon i {
  color: white;
  font-size: 1.5rem;
}

.metric-icon.pending {
  background: linear-gradient(135deg, #fbbf24, #f59e0b);
}

.metric-icon.in-progress {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.metric-icon.completed {
  background: linear-gradient(135deg, #10b981, #059669);
}

.metric-icon.total {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
}

.metric-content h3 {
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--gray-900);
  margin: 0 0 0.25rem 0;
  line-height: 1;
}

.metric-content p {
  color: var(--gray-600);
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 0.25rem 0;
}

.metric-content small {
  color: var(--gray-500);
  font-size: 0.875rem;
}

.chart-section {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 1.5rem;
  padding: 2rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.chart-header {
  text-align: center;
  margin-bottom: 2rem;
}

.chart-header h3 {
  color: var(--gray-900);
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
}

.chart-header p {
  color: var(--gray-600);
  margin: 0;
}

.chart-container {
  height: 400px;
  position: relative;
  background: rgba(255, 255, 255, 0.5);
  border-radius: 1rem;
  padding: 1rem;
}

#tasksChart {
  width: 100%;
  height: 100%;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive */
@media (max-width: 1024px) {
  .metrics-grid {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
  }
  
  .metric-card {
    padding: 1.5rem;
    flex-direction: row;
    align-items: center;
  }
  
  .metric-icon {
    width: 50px;
    height: 50px;
  }
  
  .chart-container {
    height: 350px;
  }
}

@media (max-width: 768px) {
  .overview-header h2 {
    font-size: 2rem;
  }
  
  .overview-header p {
    font-size: 1rem;
  }
  
  .metrics-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .metric-card {
    padding: 1.5rem;
    flex-direction: row;
    align-items: center;
  }
  
  .metric-content h3 {
    font-size: 2rem;
  }
  
  .metric-content p {
    font-size: 0.9rem;
  }
  
  .metric-content small {
    font-size: 0.8rem;
  }
  
  .chart-section {
    padding: 1.5rem;
  }
  
  .chart-header h3 {
    font-size: 1.25rem;
  }
  
  .chart-container {
    height: 300px;
    padding: 0.75rem;
  }
}

@media (max-width: 480px) {
  .overview-header {
    margin-bottom: 2rem;
  }
  
  .overview-header h2 {
    font-size: 1.75rem;
  }
  
  .overview-header p {
    font-size: 0.9rem;
  }
  
  .metrics-grid {
    gap: 1rem;
  }
  
  .metric-card {
    padding: 1rem;
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
  
  .metric-icon {
    width: 45px;
    height: 45px;
  }
  
  .metric-icon i {
    font-size: 1.25rem;
  }
  
  .metric-content h3 {
    font-size: 1.75rem;
  }
  
  .chart-section {
    padding: 1rem;
  }
  
  .chart-header h3 {
    font-size: 1.125rem;
  }
  
  .chart-container {
    height: 250px;
    padding: 0.5rem;
  }
}
</style>
