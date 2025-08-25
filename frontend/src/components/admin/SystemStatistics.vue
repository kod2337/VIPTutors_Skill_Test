<template>
  <div class="system-statistics">
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">System Statistics</h2>
      
      <div v-if="loading" class="space-y-6">
        <div class="animate-pulse">
          <div class="h-32 bg-gray-200 rounded mb-4"></div>
          <div class="h-64 bg-gray-200 rounded mb-4"></div>
          <div class="h-48 bg-gray-200 rounded"></div>
        </div>
      </div>

      <div v-else-if="!statistics" class="text-center py-12">
        <div class="text-gray-400 mb-4">
          <i class="fas fa-chart-bar text-6xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Statistics Available</h3>
        <p class="text-gray-500">Unable to load system statistics at this time.</p>
      </div>

      <div v-else class="space-y-8">
        <!-- System Overview -->
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-4">System Overview</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-500 text-white">
                  <i class="fas fa-tasks text-xl"></i>
                </div>
                <div class="ml-4">
                  <h4 class="text-sm font-medium text-blue-900">Total Tasks</h4>
                  <p class="text-2xl font-bold text-blue-600">{{ statistics.overview?.total_tasks || 0 }}</p>
                </div>
              </div>
            </div>

            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-500 text-white">
                  <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div class="ml-4">
                  <h4 class="text-sm font-medium text-green-900">Completed</h4>
                  <p class="text-2xl font-bold text-green-600">{{ statistics.overview?.completed_tasks || 0 }}</p>
                  <p class="text-xs text-green-700">{{ systemCompletionRate }}% completion</p>
                </div>
              </div>
            </div>

            <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-lg p-6 border border-yellow-200">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-500 text-white">
                  <i class="fas fa-clock text-xl"></i>
                </div>
                <div class="ml-4">
                  <h4 class="text-sm font-medium text-yellow-900">Pending</h4>
                  <p class="text-2xl font-bold text-yellow-600">{{ statistics.overview?.pending_tasks || 0 }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Priority Analysis -->
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Priority Analysis</h3>
          <div class="bg-gray-50 rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="text-center">
                <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-3">
                  <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                </div>
                <h4 class="font-medium text-gray-900">High Priority</h4>
                <p class="text-2xl font-bold text-red-600">{{ statistics.by_priority?.high || 0 }}</p>
                <p class="text-sm text-gray-600">{{ getPriorityPercentage('high') }}% of total</p>
              </div>

              <div class="text-center">
                <div class="mx-auto w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mb-3">
                  <i class="fas fa-minus-circle text-2xl text-yellow-600"></i>
                </div>
                <h4 class="font-medium text-gray-900">Medium Priority</h4>
                <p class="text-2xl font-bold text-yellow-600">{{ statistics.by_priority?.medium || 0 }}</p>
                <p class="text-sm text-gray-600">{{ getPriorityPercentage('medium') }}% of total</p>
              </div>

              <div class="text-center">
                <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-3">
                  <i class="fas fa-arrow-down text-2xl text-blue-600"></i>
                </div>
                <h4 class="font-medium text-gray-900">Low Priority</h4>
                <p class="text-2xl font-bold text-blue-600">{{ statistics.by_priority?.low || 0 }}</p>
                <p class="text-sm text-gray-600">{{ getPriorityPercentage('low') }}% of total</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Activity Timeline -->
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Activity Timeline</h3>
          <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
              <h4 class="text-sm font-medium text-gray-900 mb-1">Today</h4>
              <p class="text-xl font-bold text-blue-600">{{ statistics.recent_activity?.today || 0 }}</p>
              <p class="text-xs text-gray-500">tasks created</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
              <h4 class="text-sm font-medium text-gray-900 mb-1">Yesterday</h4>
              <p class="text-xl font-bold text-gray-600">{{ statistics.recent_activity?.yesterday || 0 }}</p>
              <p class="text-xs text-gray-500">tasks created</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
              <h4 class="text-sm font-medium text-gray-900 mb-1">This Week</h4>
              <p class="text-xl font-bold text-green-600">{{ statistics.recent_activity?.this_week || 0 }}</p>
              <p class="text-xs text-gray-500">tasks created</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
              <h4 class="text-sm font-medium text-gray-900 mb-1">Last Week</h4>
              <p class="text-xl font-bold text-gray-600">{{ statistics.recent_activity?.last_week || 0 }}</p>
              <p class="text-xs text-gray-500">tasks created</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
              <h4 class="text-sm font-medium text-gray-900 mb-1">This Month</h4>
              <p class="text-xl font-bold text-purple-600">{{ statistics.recent_activity?.this_month || 0 }}</p>
              <p class="text-xs text-gray-500">tasks created</p>
            </div>
          </div>
        </div>

        <!-- Completion Trends Chart -->
        <div v-if="statistics.completion_trends && statistics.completion_trends.length > 0">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">30-Day Completion Trends</h3>
          <div class="bg-white border border-gray-200 rounded-lg p-4">
            <CompletionTrendsChart :data="statistics.completion_trends" />
          </div>
        </div>

        <!-- Status & Priority Matrix -->
        <div v-if="statistics.by_status_and_priority">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Task Distribution Matrix</h3>
          <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Status
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                      High Priority
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Medium Priority
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Low Priority
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Total
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="(statusData, status) in statistics.by_status_and_priority" :key="status">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span 
                        :class="[
                          'px-3 py-1 rounded-full text-sm font-medium capitalize',
                          status === 'completed' 
                            ? 'bg-green-100 text-green-800' 
                            : 'bg-yellow-100 text-yellow-800'
                        ]"
                      >
                        {{ status }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <span class="text-sm font-medium text-gray-900">
                        {{ getMatrixCount(statusData, 'high') }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <span class="text-sm font-medium text-gray-900">
                        {{ getMatrixCount(statusData, 'medium') }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <span class="text-sm font-medium text-gray-900">
                        {{ getMatrixCount(statusData, 'low') }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <span class="text-sm font-bold text-gray-900">
                        {{ getMatrixTotal(statusData) }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- System Health Indicators -->
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-4">System Health</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
              <div class="flex items-center justify-between mb-2">
                <h4 class="font-medium text-gray-900">Task Completion Rate</h4>
                <span :class="getHealthColor(systemCompletionRate)">
                  {{ systemCompletionRate }}%
                </span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div 
                  :class="getHealthBarColor(systemCompletionRate)"
                  class="h-2 rounded-full transition-all duration-500"
                  :style="{ width: `${systemCompletionRate}%` }"
                ></div>
              </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
              <div class="flex items-center justify-between mb-2">
                <h4 class="font-medium text-gray-900">High Priority Ratio</h4>
                <span :class="getHealthColor(100 - highPriorityRatio, true)">
                  {{ highPriorityRatio }}%
                </span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div 
                  :class="getHealthBarColor(100 - highPriorityRatio, true)"
                  class="h-2 rounded-full transition-all duration-500"
                  :style="{ width: `${highPriorityRatio}%` }"
                ></div>
              </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
              <div class="flex items-center justify-between mb-2">
                <h4 class="font-medium text-gray-900">Activity Trend</h4>
                <span :class="getActivityTrendColor()">
                  {{ getActivityTrend() }}
                </span>
              </div>
              <p class="text-sm text-gray-600">
                {{ getActivityTrendDescription() }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import CompletionTrendsChart from './CompletionTrendsChart.vue'

const props = defineProps({
  statistics: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const systemCompletionRate = computed(() => {
  const overview = props.statistics?.overview
  if (!overview || overview.total_tasks === 0) return 0
  return Math.round((overview.completed_tasks / overview.total_tasks) * 100)
})

const highPriorityRatio = computed(() => {
  const overview = props.statistics?.overview
  const priorities = props.statistics?.by_priority
  if (!overview || !priorities || overview.total_tasks === 0) return 0
  return Math.round((priorities.high / overview.total_tasks) * 100)
})

const getPriorityPercentage = (priority) => {
  const overview = props.statistics?.overview
  const priorities = props.statistics?.by_priority
  if (!overview || !priorities || overview.total_tasks === 0) return 0
  return Math.round((priorities[priority] / overview.total_tasks) * 100)
}

const getMatrixCount = (statusData, priority) => {
  if (!Array.isArray(statusData)) return 0
  const item = statusData.find(item => item.priority === priority)
  return item ? item.count : 0
}

const getMatrixTotal = (statusData) => {
  if (!Array.isArray(statusData)) return 0
  return statusData.reduce((total, item) => total + item.count, 0)
}

const getHealthColor = (value, inverted = false) => {
  const threshold = inverted ? 30 : 70
  if (inverted) {
    if (value <= 20) return 'text-green-600 font-medium'
    if (value <= threshold) return 'text-yellow-600 font-medium'
    return 'text-red-600 font-medium'
  } else {
    if (value >= 80) return 'text-green-600 font-medium'
    if (value >= threshold) return 'text-yellow-600 font-medium'
    return 'text-red-600 font-medium'
  }
}

const getHealthBarColor = (value, inverted = false) => {
  const threshold = inverted ? 30 : 70
  if (inverted) {
    if (value <= 20) return 'bg-green-500'
    if (value <= threshold) return 'bg-yellow-500'
    return 'bg-red-500'
  } else {
    if (value >= 80) return 'bg-green-500'
    if (value >= threshold) return 'bg-yellow-500'
    return 'bg-red-500'
  }
}

const getActivityTrend = () => {
  const activity = props.statistics?.recent_activity
  if (!activity) return 'No Data'
  
  const thisWeek = activity.this_week || 0
  const lastWeek = activity.last_week || 0
  
  if (thisWeek > lastWeek) return 'Increasing'
  if (thisWeek < lastWeek) return 'Decreasing'
  return 'Stable'
}

const getActivityTrendColor = () => {
  const trend = getActivityTrend()
  switch (trend) {
    case 'Increasing':
      return 'text-green-600 font-medium'
    case 'Decreasing':
      return 'text-red-600 font-medium'
    case 'Stable':
      return 'text-blue-600 font-medium'
    default:
      return 'text-gray-600 font-medium'
  }
}

const getActivityTrendDescription = () => {
  const activity = props.statistics?.recent_activity
  if (!activity) return 'No activity data available'
  
  const thisWeek = activity.this_week || 0
  const lastWeek = activity.last_week || 0
  const diff = Math.abs(thisWeek - lastWeek)
  
  if (thisWeek > lastWeek) {
    return `${diff} more tasks than last week`
  } else if (thisWeek < lastWeek) {
    return `${diff} fewer tasks than last week`
  } else {
    return 'Same as last week'
  }
}
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
