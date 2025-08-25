<template>
  <div class="overflow-hidden">
    <div v-if="loading" class="p-6">
      <div class="animate-pulse space-y-4">
        <div v-for="i in 5" :key="i" class="flex items-center space-x-4">
          <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-200 rounded w-1/4"></div>
            <div class="h-3 bg-gray-200 rounded w-1/3"></div>
          </div>
          <div class="h-4 bg-gray-200 rounded w-16"></div>
        </div>
      </div>
    </div>

    <div v-else-if="performers.length === 0" class="p-6 text-center">
      <div class="text-gray-400 mb-2">
        <i class="fas fa-users text-4xl"></i>
      </div>
      <p class="text-gray-500">No performance data available</p>
    </div>

    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Rank
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              User
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Total Tasks
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Completed
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Completion Rate
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr 
            v-for="(performer, index) in performers" 
            :key="performer.id"
            class="hover:bg-gray-50 transition-colors duration-150"
          >
            <!-- Rank -->
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div 
                  :class="[
                    'w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold',
                    getRankColorClass(index + 1)
                  ]"
                >
                  {{ index + 1 }}
                </div>
              </div>
            </td>

            <!-- User Info -->
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                    <i class="fas fa-user text-gray-600"></i>
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">{{ performer.name }}</div>
                  <div class="text-sm text-gray-500">{{ performer.email }}</div>
                </div>
              </div>
            </td>

            <!-- Total Tasks -->
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900 font-medium">{{ performer.tasks_count }}</div>
            </td>

            <!-- Completed Tasks -->
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900 font-medium">{{ performer.completed_tasks_count }}</div>
              <div class="text-xs text-gray-500">
                {{ performer.tasks_count - performer.completed_tasks_count }} pending
              </div>
            </td>

            <!-- Completion Rate -->
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-1 mr-3">
                  <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                    <span>{{ performer.completion_rate }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div 
                      :class="[
                        'h-2 rounded-full transition-all duration-500',
                        getCompletionRateColor(performer.completion_rate)
                      ]"
                      :style="{ width: `${performer.completion_rate}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </td>

            <!-- Status Badge -->
            <td class="px-6 py-4 whitespace-nowrap">
              <span 
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  performer.is_admin 
                    ? 'bg-purple-100 text-purple-800' 
                    : 'bg-blue-100 text-blue-800'
                ]"
              >
                <i 
                  :class="[
                    performer.is_admin ? 'fas fa-crown' : 'fas fa-user',
                    'mr-1'
                  ]"
                ></i>
                {{ performer.is_admin ? 'Admin' : 'User' }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Performance Insights -->
    <div v-if="!loading && performers.length > 0" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
      <div class="text-sm text-gray-600">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <span>
              <i class="fas fa-trophy text-yellow-500 mr-1"></i>
              Top performer: {{ topPerformer?.name }}
            </span>
            <span>
              <i class="fas fa-chart-line text-green-500 mr-1"></i>
              Average completion: {{ averageCompletion }}%
            </span>
          </div>
          <div class="text-xs text-gray-500">
            Based on completed tasks
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  performers: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const topPerformer = computed(() => {
  return props.performers.length > 0 ? props.performers[0] : null
})

const averageCompletion = computed(() => {
  if (props.performers.length === 0) return 0
  
  const total = props.performers.reduce((sum, performer) => sum + performer.completion_rate, 0)
  return Math.round(total / props.performers.length)
})

const getRankColorClass = (rank) => {
  switch (rank) {
    case 1:
      return 'bg-yellow-500 text-white' // Gold
    case 2:
      return 'bg-gray-400 text-white' // Silver
    case 3:
      return 'bg-yellow-600 text-white' // Bronze
    default:
      return 'bg-blue-500 text-white'
  }
}

const getCompletionRateColor = (rate) => {
  if (rate >= 90) return 'bg-green-500'
  if (rate >= 70) return 'bg-blue-500'
  if (rate >= 50) return 'bg-yellow-500'
  return 'bg-red-500'
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
