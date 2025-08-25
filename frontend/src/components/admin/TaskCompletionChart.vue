<template>
  <div class="task-completion-chart">
    <div v-if="loading" class="animate-pulse">
      <div class="h-64 bg-gray-200 rounded"></div>
    </div>
    
    <div v-else-if="chartData.length === 0" class="h-64 flex items-center justify-center text-gray-500">
      <div class="text-center">
        <i class="fas fa-chart-line text-4xl mb-2"></i>
        <p>No completion data available</p>
      </div>
    </div>
    
    <div v-else class="h-64">
      <canvas ref="chartCanvas"></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const chartCanvas = ref(null)
let chartInstance = null

const chartData = ref([])

const createChart = async () => {
  if (!chartCanvas.value || chartData.value.length === 0) return

  await nextTick()

  const ctx = chartCanvas.value.getContext('2d')
  
  // Destroy existing chart
  if (chartInstance) {
    chartInstance.destroy()
  }

  const labels = chartData.value.map(item => {
    const date = new Date(item.date)
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
  })

  const totalTasks = chartData.value.map(item => item.total_tasks)
  const completedTasks = chartData.value.map(item => item.completed_tasks)
  const completionRates = chartData.value.map(item => {
    return item.total_tasks > 0 ? (item.completed_tasks / item.total_tasks) * 100 : 0
  })

  chartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Total Tasks',
          data: totalTasks,
          borderColor: 'rgb(59, 130, 246)',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          borderWidth: 2,
          fill: false,
          tension: 0.1,
          yAxisID: 'y'
        },
        {
          label: 'Completed Tasks',
          data: completedTasks,
          borderColor: 'rgb(34, 197, 94)',
          backgroundColor: 'rgba(34, 197, 94, 0.1)',
          borderWidth: 2,
          fill: false,
          tension: 0.1,
          yAxisID: 'y'
        },
        {
          label: 'Completion Rate (%)',
          data: completionRates,
          borderColor: 'rgb(168, 85, 247)',
          backgroundColor: 'rgba(168, 85, 247, 0.1)',
          borderWidth: 2,
          fill: false,
          tension: 0.1,
          yAxisID: 'y1'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: 'index',
        intersect: false,
      },
      plugins: {
        legend: {
          position: 'top',
        },
        tooltip: {
          callbacks: {
            afterLabel: function(context) {
              if (context.datasetIndex === 2) {
                return '%'
              }
              return ''
            }
          }
        }
      },
      scales: {
        x: {
          display: true,
          title: {
            display: true,
            text: 'Date'
          }
        },
        y: {
          type: 'linear',
          display: true,
          position: 'left',
          title: {
            display: true,
            text: 'Number of Tasks'
          },
          beginAtZero: true,
        },
        y1: {
          type: 'linear',
          display: true,
          position: 'right',
          title: {
            display: true,
            text: 'Completion Rate (%)'
          },
          beginAtZero: true,
          max: 100,
          grid: {
            drawOnChartArea: false,
          },
        }
      }
    }
  })
}

watch(() => props.data, (newData) => {
  chartData.value = newData || []
  if (!props.loading) {
    createChart()
  }
}, { immediate: true })

watch(() => props.loading, (newLoading) => {
  if (!newLoading && chartData.value.length > 0) {
    createChart()
  }
})

onMounted(() => {
  if (!props.loading && chartData.value.length > 0) {
    createChart()
  }
})
</script>

<style scoped>
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}
</style>
