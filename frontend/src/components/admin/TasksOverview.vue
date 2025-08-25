<template>
  <div class="tasks-overview">
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">Tasks Overview</h2>
      
      <!-- Quick Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-blue-50 rounded-lg p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-500 text-white">
              <i class="fas fa-tasks text-xl"></i>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-blue-900">Total Tasks</h3>
              <p class="text-2xl font-bold text-blue-600">{{ taskStats?.overview?.total_tasks || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="bg-green-50 rounded-lg p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-500 text-white">
              <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-green-900">Completed</h3>
              <p class="text-2xl font-bold text-green-600">{{ taskStats?.overview?.completed_tasks || 0 }}</p>
              <p class="text-sm text-green-700">{{ completionPercentage }}% completion rate</p>
            </div>
          </div>
        </div>

        <div class="bg-yellow-50 rounded-lg p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-500 text-white">
              <i class="fas fa-clock text-xl"></i>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-yellow-900">Pending</h3>
              <p class="text-2xl font-bold text-yellow-600">{{ taskStats?.overview?.pending_tasks || 0 }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Priority Breakdown -->
      <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Priority Breakdown</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="font-medium text-red-900">High Priority</h4>
                <p class="text-2xl font-bold text-red-600">{{ taskStats?.by_priority?.high || 0 }}</p>
              </div>
              <i class="fas fa-exclamation-triangle text-2xl text-red-500"></i>
            </div>
          </div>

          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="font-medium text-yellow-900">Medium Priority</h4>
                <p class="text-2xl font-bold text-yellow-600">{{ taskStats?.by_priority?.medium || 0 }}</p>
              </div>
              <i class="fas fa-minus-circle text-2xl text-yellow-500"></i>
            </div>
          </div>

          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="font-medium text-blue-900">Low Priority</h4>
                <p class="text-2xl font-bold text-blue-600">{{ taskStats?.by_priority?.low || 0 }}</p>
              </div>
              <i class="fas fa-arrow-down text-2xl text-blue-500"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div>
              <p class="text-sm text-gray-600">Today</p>
              <p class="text-xl font-bold text-gray-900">{{ taskStats?.recent_activity?.today || 0 }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Yesterday</p>
              <p class="text-xl font-bold text-gray-900">{{ taskStats?.recent_activity?.yesterday || 0 }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">This Week</p>
              <p class="text-xl font-bold text-gray-900">{{ taskStats?.recent_activity?.this_week || 0 }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">This Month</p>
              <p class="text-xl font-bold text-gray-900">{{ taskStats?.recent_activity?.this_month || 0 }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Status and Priority Matrix -->
      <div v-if="taskStats?.by_status_and_priority">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Task Distribution Matrix</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full border border-gray-200 rounded-lg">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                  Status / Priority
                </th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                  High
                </th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                  Medium
                </th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                  Low
                </th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                  Total
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(statusData, status) in taskStats.by_status_and_priority" :key="status">
                <td class="px-4 py-3 text-sm font-medium text-gray-900 capitalize">
                  <span 
                    :class="[
                      'px-2 py-1 rounded-full text-xs',
                      status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                    ]"
                  >
                    {{ status }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-center text-gray-900">
                  {{ getCountByPriority(statusData, 'high') }}
                </td>
                <td class="px-4 py-3 text-sm text-center text-gray-900">
                  {{ getCountByPriority(statusData, 'medium') }}
                </td>
                <td class="px-4 py-3 text-sm text-center text-gray-900">
                  {{ getCountByPriority(statusData, 'low') }}
                </td>
                <td class="px-4 py-3 text-sm text-center font-medium text-gray-900">
                  {{ getTotalByStatus(statusData) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAdminStore } from '@/stores/admin'

const adminStore = useAdminStore()

const taskStats = computed(() => adminStore.taskStatistics)

const completionPercentage = computed(() => {
  const stats = taskStats.value?.overview
  if (!stats || stats.total_tasks === 0) return 0
  return Math.round((stats.completed_tasks / stats.total_tasks) * 100)
})

const getCountByPriority = (statusData, priority) => {
  if (!Array.isArray(statusData)) return 0
  const item = statusData.find(item => item.priority === priority)
  return item ? item.count : 0
}

const getTotalByStatus = (statusData) => {
  if (!Array.isArray(statusData)) return 0
  return statusData.reduce((total, item) => total + item.count, 0)
}
</script>
