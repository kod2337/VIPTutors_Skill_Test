<template>
  <div class="priority-distribution-chart">
    <div v-if="loading" class="animate-pulse">
      <div class="h-64 bg-gray-200 rounded"></div>
    </div>
    
    <div v-else-if="!hasData" class="h-64 flex items-center justify-center text-gray-500">
      <div class="text-center">
        <i class="fas fa-chart-pie text-4xl mb-2"></i>
        <p>No priority data available</p>
      </div>
    </div>
    
    <div v-else class="h-64">
      <canvas ref="chartCanvas"></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  data: {
    type: Object,
    default: () => ({})
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const chartCanvas = ref(null)
let chartInstance = null

const hasData = computed(() => {
  const data = props.data || {}
  return Object.keys(data).length > 0 && Object.values(data).some(value => value > 0)
})

const chartData = computed(() => {
  if (!hasData.value) return { labels: [], data: [], colors: [] }
  
  const data = props.data || {}
  const labels = []
  const values = []
  const colors = []
  
  const priorityConfig = {
    high: { label: 'High Priority', color: 'rgb(239, 68, 68)' },
    medium: { label: 'Medium Priority', color: 'rgb(245, 158, 11)' },
    low: { label: 'Low Priority', color: 'rgb(59, 130, 246)' }
  }
  
  Object.entries(data).forEach(([priority, count]) => {
    if (count > 0 && priorityConfig[priority]) {
      labels.push(priorityConfig[priority].label)
      values.push(count)
      colors.push(priorityConfig[priority].color)
    }
  })
  
  return { labels, data: values, colors }
})

const createChart = async () => {
  if (!chartCanvas.value || !hasData.value) return

  await nextTick()

  const ctx = chartCanvas.value.getContext('2d')
  
  // Destroy existing chart
  if (chartInstance) {
    chartInstance.destroy()
  }

  const { labels, data, colors } = chartData.value

  chartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: colors,
        borderWidth: 2,
        borderColor: '#ffffff',
        hoverOffset: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            padding: 20,
            usePointStyle: true,
            pointStyle: 'circle'
          }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              const label = context.label || ''
              const value = context.parsed
              const total = context.dataset.data.reduce((a, b) => a + b, 0)
              const percentage = total > 0 ? Math.round((value / total) * 100) : 0
              return `${label}: ${value} (${percentage}%)`
            }
          }
        }
      },
      cutout: '50%'
    }
  })
}

watch(() => props.data, () => {
  if (!props.loading) {
    createChart()
  }
}, { deep: true })

watch(() => props.loading, (newLoading) => {
  if (!newLoading && hasData.value) {
    createChart()
  }
})

onMounted(() => {
  if (!props.loading && hasData.value) {
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
