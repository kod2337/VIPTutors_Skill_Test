<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between">
          <div class="min-w-0 flex-1">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center">
              <RectangleStackIcon class="h-6 w-6 sm:h-7 sm:w-7 inline mr-2 text-blue-600 flex-shrink-0" />
              <span class="hidden sm:inline">Task Dashboard</span>
              <span class="sm:hidden">Tasks</span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-1 hidden sm:block">Manage your tasks efficiently</p>
          </div>
          <div class="flex items-center space-x-2 sm:space-x-4">
            <!-- User Info -->
            <div class="hidden md:flex items-center space-x-3">
              <div class="text-right">
                <p class="text-sm font-medium text-gray-900">{{ user?.name }}</p>
                <p class="text-xs text-gray-500">{{ user?.email }}</p>
              </div>
              <div class="h-8 w-8 bg-blue-600 rounded-full flex items-center justify-center">
                <span class="text-white text-sm font-medium">{{ userInitials }}</span>
              </div>
            </div>
            
            <!-- Mobile User Avatar -->
            <div class="md:hidden h-8 w-8 bg-blue-600 rounded-full flex items-center justify-center">
              <span class="text-white text-sm font-medium">{{ userInitials }}</span>
            </div>
            
            <span 
              v-if="user?.is_admin" 
              class="inline-flex items-center px-2 sm:px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
            >
              <ShieldCheckIcon class="h-3 w-3 mr-1" />
              <span class="hidden sm:inline">Admin</span>
            </span>
            
            <!-- Logout Button -->
            <button
              @click="handleLogout"
              :disabled="isLoading"
              class="inline-flex items-center px-2 sm:px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-xs sm:text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
              :class="{ 'opacity-50 cursor-not-allowed': isLoading }"
            >
              <ArrowRightOnRectangleIcon class="h-4 w-4 sm:mr-2" />
              <span class="hidden sm:inline">{{ isLoading ? 'Logging out...' : 'Logout' }}</span>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
      <!-- Action Bar -->
      <div class="flex flex-col space-y-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0 mb-6">
        <div class="min-w-0 flex-1">
          <h2 class="text-lg font-semibold text-gray-900">Task Management</h2>
          <p class="text-sm text-gray-500 hidden sm:block">Create, manage, and track your tasks</p>
        </div>
        <div class="flex-shrink-0">
          <button 
            @click="handleCreateTask"
            :disabled="taskStore.loading"
            class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed group"
          >
            <Transition
              enter-active-class="transition-all duration-200"
              enter-from-class="opacity-0 scale-95"
              enter-to-class="opacity-100 scale-100"
              leave-active-class="transition-all duration-200"
              leave-from-class="opacity-100 scale-100"
              leave-to-class="opacity-0 scale-95"
              mode="out-in"
            >
              <svg v-if="taskStore.loading" class="animate-spin h-4 w-4 mr-2 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <PlusIcon v-else class="h-4 w-4 mr-2 group-hover:scale-110 transition-transform duration-200" />
            </Transition>
            {{ taskStore.loading ? 'Loading...' : 'New Task' }}
          </button>
        </div>
      </div>

      <!-- Task List Component -->
      <DraggableTaskList @edit-task="handleEditTask" @tasks-reordered="handleTasksReordered" />

      <!-- Task Form Modal -->
      <TaskForm
        :task="selectedTask"
        :show="showTaskForm"
        @close="closeTaskForm"
        @saved="handleTaskSaved"
      />
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useTaskStore } from '@/stores/tasks'
import DraggableTaskList from '@/components/DraggableTaskList.vue'
import TaskForm from '@/components/TaskForm.vue'
import {
  RectangleStackIcon,
  ArrowRightOnRectangleIcon,
  ShieldCheckIcon,
  PlusIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()
const taskStore = useTaskStore()

const isLoading = ref(false)
const showTaskForm = ref(false)
const selectedTask = ref(null)

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

const handleEditTask = (task) => {
  selectedTask.value = task
  showTaskForm.value = true
}

const handleCreateTask = () => {
  selectedTask.value = null // Ensure we're in create mode
  showTaskForm.value = true
}

const closeTaskForm = () => {
  showTaskForm.value = false
  selectedTask.value = null
}

const handleTaskSaved = (savedTask) => {
  // Task saved successfully, refresh the task list and close form
  console.log('Task saved successfully:', savedTask)
  // The store will handle updating the task list and showing success notification
  // No need to manually refresh since the store already updates the reactive state
}

const handleTasksReordered = (reorderedTasks) => {
  // Handle tasks reordering
  console.log('Tasks reordered:', reorderedTasks)
}

// Initialize task store on mount
onMounted(() => {
  taskStore.init()
})
</script>
