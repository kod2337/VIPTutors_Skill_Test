<template>
  <div class="admin-dashboard">
    <!-- Header Section -->
    <div class="admin-header bg-white shadow-sm mb-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
            <p class="text-gray-600 mt-1">Manage users, tasks, and system statistics</p>
          </div>
          <div class="flex items-center space-x-4">
            <router-link
              to="/dashboard"
              class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
            >
              <i class="fas fa-arrow-left mr-2"></i>
              Back to Tasks
            </router-link>
            <span class="text-sm text-gray-500">Last updated: {{ formatDate(new Date()) }}</span>
            <button
              @click="refreshData"
              :disabled="loading"
              class="btn btn-primary btn-sm"
            >
              <i class="fas fa-sync-alt" :class="{ 'animate-spin': loading }"></i>
              Refresh
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === tab.id
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            <i :class="tab.icon"></i>
            {{ tab.name }}
          </button>
        </nav>
      </div>
    </div>

    <!-- Content Area -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Dashboard Overview Tab -->
      <div v-if="activeTab === 'dashboard'" class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <StatCard
            v-for="stat in dashboardStats"
            :key="stat.label"
            :icon="stat.icon"
            :label="stat.label"
            :value="stat.value"
            :color="stat.color"
            :loading="loading"
          />
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Task Completion Trends</h3>
            <div v-if="loading" class="flex items-center justify-center h-64">
              <div class="animate-pulse text-gray-500">Loading chart...</div>
            </div>
            <TaskCompletionChart 
              v-else-if="taskStatistics?.completion_trends"
              :data="taskStatistics.completion_trends" 
              :key="'completion-' + Date.now()"
            />
            <div v-else class="flex items-center justify-center h-64 text-gray-500">
              No data available
            </div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Priority Distribution</h3>
            <div v-if="loading" class="flex items-center justify-center h-64">
              <div class="animate-pulse text-gray-500">Loading chart...</div>
            </div>
            <PriorityDistributionChart 
              v-else-if="taskStatistics?.by_priority"
              :data="taskStatistics.by_priority" 
              :key="'priority-' + Date.now()"
            />
            <div v-else class="flex items-center justify-center h-64 text-gray-500">
              No data available
            </div>
          </div>
        </div>

        <!-- Top Performers -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Top Performers</h3>
          </div>
          <TopPerformersTable :performers="topPerformers" :loading="loading" />
        </div>
      </div>

      <!-- Users Management Tab -->
      <div v-if="activeTab === 'users'">
        <UserManagement />
      </div>

      <!-- Tasks Overview Tab -->
      <div v-if="activeTab === 'tasks'">
        <TasksOverview />
      </div>

      <!-- System Stats Tab -->
      <div v-if="activeTab === 'stats'">
        <SystemStatistics :statistics="taskStatistics" :loading="loading" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAdminStore } from '@/stores/admin'
import { formatDate } from '@/utils/dateFormatter'
import StatCard from '@/components/admin/StatCard.vue'
import TaskCompletionChart from '@/components/admin/TaskCompletionChart.vue'
import PriorityDistributionChart from '@/components/admin/PriorityDistributionChart.vue'
import TopPerformersTable from '@/components/admin/TopPerformersTable.vue'
import UserManagement from '@/components/admin/UserManagement.vue'
import TasksOverview from '@/components/admin/TasksOverview.vue'
import SystemStatistics from '@/components/admin/SystemStatistics.vue'

const adminStore = useAdminStore()
const loading = ref(true) // Start with loading true
const activeTab = ref('dashboard')

const tabs = [
  { id: 'dashboard', name: 'Dashboard', icon: 'fas fa-tachometer-alt' },
  { id: 'users', name: 'Users', icon: 'fas fa-users' },
  { id: 'tasks', name: 'Tasks', icon: 'fas fa-tasks' },
  { id: 'stats', name: 'Statistics', icon: 'fas fa-chart-bar' }
]

const dashboardStats = computed(() => {
  const stats = adminStore.dashboardStats
  if (!stats) return []
  
  return [
    {
      label: 'Total Users',
      value: stats.total_users,
      icon: 'fas fa-users',
      color: 'blue'
    },
    {
      label: 'Total Tasks',
      value: stats.total_tasks,
      icon: 'fas fa-list-ul',
      color: 'green'
    },
    {
      label: 'Completed Tasks',
      value: stats.completed_tasks,
      icon: 'fas fa-check-circle',
      color: 'emerald'
    },
    {
      label: 'High Priority Tasks',
      value: stats.high_priority_tasks,
      icon: 'fas fa-exclamation-circle',
      color: 'red'
    }
  ]
})

const taskStatistics = computed(() => adminStore.taskStatistics)
const topPerformers = computed(() => adminStore.topPerformers)

const refreshData = async () => {
  loading.value = true
  try {
    // Clear existing data to force re-render
    await Promise.all([
      adminStore.fetchDashboardStats(),
      adminStore.fetchTaskStatistics(),
      adminStore.fetchTopPerformers()
    ])
    
    // Small delay to ensure data is fully loaded before charts render
    await new Promise(resolve => setTimeout(resolve, 100))
  } catch (error) {
    console.error('Error refreshing data:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  // Automatically refresh data when component mounts
  refreshData()
})
</script>

<style scoped>
.admin-dashboard {
  min-height: 100vh;
  background-color: #f9fafb;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
