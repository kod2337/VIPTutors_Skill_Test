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
      <!-- Action Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Task Management</h2>
          <p class="text-sm text-gray-500">Create, manage, and track your tasks</p>
        </div>
        <div class="mt-4 sm:mt-0">
          <button 
            @click="showTaskForm = true"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            New Task
          </button>
        </div>
      </div>

      <!-- Task List Component -->
      <DraggableTaskList @edit-task="handleEditTask" @tasks-reordered="handleTasksReordered" />

      <!-- Task Form Modal -->
      <TaskForm
        v-if="showTaskForm"
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

const closeTaskForm = () => {
  showTaskForm.value = false
  selectedTask.value = null
}

const handleTaskSaved = () => {
  // Task saved successfully, form will close automatically
  console.log('Task saved successfully')
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
