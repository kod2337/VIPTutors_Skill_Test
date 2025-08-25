<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center">
              <RectangleStackIcon class="h-7 w-7 inline mr-2 text-blue-600" />
              Task Dashboard
            </h1>
            <p class="text-sm text-gray-600 mt-1">Manage your tasks efficiently</p>
          </div>
          <div class="flex items-center space-x-4">
            <!-- User Info -->
            <div class="flex items-center space-x-3">
              <div class="text-right">
                <p class="text-sm font-medium text-gray-900">{{ user?.name }}</p>
                <p class="text-xs text-gray-500">{{ user?.email }}</p>
              </div>
              <div class="h-8 w-8 bg-blue-600 rounded-full flex items-center justify-center">
                <span class="text-white text-sm font-medium">{{ userInitials }}</span>
              </div>
            </div>
            
            <span 
              v-if="user?.is_admin" 
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
            >
              <ShieldCheckIcon class="h-3 w-3 mr-1" />
              Admin
            </span>
            
            <!-- Logout Button -->
            <button
              @click="handleLogout"
              :disabled="isLoading"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
              :class="{ 'opacity-50 cursor-not-allowed': isLoading }"
            >
              <ArrowRightOnRectangleIcon class="h-4 w-4 mr-2" />
              {{ isLoading ? 'Logging out...' : 'Logout' }}
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Quick Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                <ClipboardDocumentListIcon class="h-5 w-5 text-gray-600" />
              </div>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-500">Total Tasks</p>
              <p class="text-2xl font-bold text-gray-900">24</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <PlayIcon class="h-5 w-5 text-blue-600" />
              </div>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-500">In Progress</p>
              <p class="text-2xl font-bold text-blue-600">8</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <CheckCircleIcon class="h-5 w-5 text-green-600" />
              </div>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-500">Completed</p>
              <p class="text-2xl font-bold text-green-600">12</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                <ExclamationTriangleIcon class="h-5 w-5 text-red-600" />
              </div>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-500">Overdue</p>
              <p class="text-2xl font-bold text-red-600">4</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Recent Tasks</h2>
          <p class="text-sm text-gray-500">Keep track of your ongoing work</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
          <button class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
            <FunnelIcon class="h-4 w-4 mr-2" />
            Filter
          </button>
          <button class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
            <PlusIcon class="h-4 w-4 mr-2" />
            New Task
          </button>
        </div>
      </div>

      <!-- Sample Tasks List -->
      <div class="space-y-4">
        <!-- Task Item 1 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow duration-200 border-l-4 border-l-red-400">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
              <div>
                <h3 class="text-sm font-medium text-gray-900">Complete project proposal</h3>
                <p class="text-xs text-gray-500">Due: Today</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">High</span>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">In Progress</span>
              <button class="p-1 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 transition-colors duration-200">
                <EllipsisVerticalIcon class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>

        <!-- Task Item 2 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow duration-200 border-l-4 border-l-yellow-400">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
              <div>
                <h3 class="text-sm font-medium text-gray-900">Review team code submissions</h3>
                <p class="text-xs text-gray-500">Due: Tomorrow</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Medium</span>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">To Do</span>
              <button class="p-1 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 transition-colors duration-200">
                <EllipsisVerticalIcon class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>

        <!-- Task Item 3 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow duration-200 border-l-4 border-l-green-400">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <input type="checkbox" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
              <div>
                <h3 class="text-sm font-medium text-gray-900 line-through">Update documentation</h3>
                <p class="text-xs text-gray-500">Completed yesterday</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Low</span>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
              <button class="p-1 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 transition-colors duration-200">
                <EllipsisVerticalIcon class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>

        <!-- Task Item 4 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow duration-200 border-l-4 border-l-yellow-400">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
              <div>
                <h3 class="text-sm font-medium text-gray-900">Prepare client presentation</h3>
                <p class="text-xs text-gray-500">Due: Friday</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Medium</span>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">To Do</span>
              <button class="p-1 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 transition-colors duration-200">
                <EllipsisVerticalIcon class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  RectangleStackIcon,
  ArrowRightOnRectangleIcon,
  ShieldCheckIcon,
  ClipboardDocumentListIcon,
  PlayIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  FunnelIcon,
  PlusIcon,
  EllipsisVerticalIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()

const isLoading = ref(false)

const user = computed(() => authStore.user)

const userInitials = computed(() => {
  if (!user.value?.name) return 'U'
  return user.value.name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const handleLogout = async () => {
  isLoading.value = true
  
  try {
    await authStore.logout()
    router.push('/login')
  } catch (error) {
    console.error('Logout error:', error)
  } finally {
    isLoading.value = false
  }
}
</script>
