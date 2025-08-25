<template>
  <div class="space-y-6">
    <!-- Task Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Tasks</p>
            <p class="text-2xl font-semibold text-gray-900">{{ statistics.total }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Pending</p>
            <p class="text-2xl font-semibold text-gray-900">{{ statistics.pending }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Completed</p>
            <p class="text-2xl font-semibold text-gray-900">{{ statistics.completed }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Progress</p>
            <p class="text-2xl font-semibold text-gray-900">{{ statistics.completion_rate }}%</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
      <div class="flex flex-col sm:flex-row gap-4">
        <!-- Search -->
        <div class="flex-1">
          <label for="search" class="sr-only">Search tasks</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <input
              id="search"
              v-model="searchQuery"
              @input="handleSearchChange"
              type="text"
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              placeholder="Search tasks..."
            />
          </div>
        </div>

        <!-- Status Filter -->
        <div class="sm:w-48">
          <label for="status-filter" class="sr-only">Filter by status</label>
          <select
            id="status-filter"
            v-model="statusFilter"
            @change="handleStatusChange"
            class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          >
            <option value="all">All Status</option>
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
          </select>
        </div>

        <!-- Priority Filter -->
        <div class="sm:w-48">
          <label for="priority-filter" class="sr-only">Filter by priority</label>
          <select
            id="priority-filter"
            v-model="priorityFilter"
            @change="handlePriorityChange"
            class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          >
            <option value="all">All Priority</option>
            <option value="low">Low Priority</option>
            <option value="medium">Medium Priority</option>
            <option value="high">High Priority</option>
          </select>
        </div>

        <!-- Clear Filters -->
        <button
          @click="clearAllFilters"
          class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          Clear
        </button>
      </div>
    </div>

    <!-- Task List -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-lg font-medium text-gray-900">
          Tasks 
          <span class="text-sm font-normal text-gray-500">
            ({{ filteredTasks.length }} of {{ tasks.length }})
          </span>
        </h3>
        
        <!-- Drag Mode Toggle -->
        <button
          @click="toggleDragMode"
          class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          :class="{ 'bg-blue-50 border-blue-300 text-blue-700': dragMode }"
        >
          <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
          </svg>
          {{ dragMode ? 'Exit Reorder' : 'Reorder Tasks' }}
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="p-6">
        <div class="animate-pulse space-y-4">
          <div v-for="i in 3" :key="i" class="flex space-x-4">
            <div class="rounded-full bg-gray-200 h-10 w-10"></div>
            <div class="flex-1 space-y-2 py-1">
              <div class="h-4 bg-gray-200 rounded w-3/4"></div>
              <div class="h-4 bg-gray-200 rounded w-1/2"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="p-6">
        <div class="rounded-md bg-red-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">Error loading tasks</h3>
              <div class="mt-2 text-sm text-red-700">
                <p>{{ error }}</p>
              </div>
              <div class="mt-4">
                <button
                  @click="retryFetch"
                  class="text-sm bg-red-100 text-red-800 hover:bg-red-200 px-3 py-1 rounded"
                >
                  Try Again
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredTasks.length === 0" class="p-6 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No tasks found</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ tasks.length === 0 ? 'Get started by creating a new task.' : 'Try adjusting your filters.' }}
        </p>
      </div>

      <!-- Draggable Task Items -->
      <div v-else>
        <!-- Drag Mode Notice -->
        <div v-if="dragMode" class="px-6 py-3 bg-blue-50 border-b border-blue-200">
          <div class="flex items-center">
            <svg class="h-5 w-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-sm text-blue-800">
              Drag and drop tasks to reorder them. Changes are saved automatically.
            </p>
          </div>
        </div>

        <Draggable
          v-model="localTasks"
          @start="onDragStart"
          @end="onDragEnd"
          item-key="id"
          :disabled="!dragMode"
          class="divide-y divide-gray-200"
          ghost-class="ghost-item"
          chosen-class="chosen-item"
          drag-class="drag-item"
        >
          <template #item="{ element: task }">
            <DraggableTaskItem
              :task="task"
              :drag-mode="dragMode"
              @toggle-status="handleToggleStatus"
              @edit="handleEditTask"
              @delete="handleDeleteTask"
            />
          </template>
        </Draggable>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useTaskStore } from '@/stores/tasks'
import Draggable from 'vuedraggable'
import DraggableTaskItem from './DraggableTaskItem.vue'

// Store
const taskStore = useTaskStore()

// Local reactive data
const searchQuery = ref('')
const statusFilter = ref('all')
const priorityFilter = ref('all')
const dragMode = ref(false)
const isDragging = ref(false)
const localTasks = ref([])

// Computed properties
const tasks = computed(() => taskStore.tasks)
const filteredTasks = computed(() => taskStore.filteredTasks)
const loading = computed(() => taskStore.loading)
const error = computed(() => taskStore.error)
const statistics = computed(() => taskStore.statistics)

// Watch for changes in filteredTasks and update localTasks
watch(filteredTasks, (newTasks) => {
  localTasks.value = [...newTasks]
}, { immediate: true })

// Methods
const handleSearchChange = () => {
  taskStore.setFilter('search', searchQuery.value)
}

const handleStatusChange = () => {
  taskStore.setFilter('status', statusFilter.value)
}

const handlePriorityChange = () => {
  taskStore.setFilter('priority', priorityFilter.value)
}

const clearAllFilters = () => {
  searchQuery.value = ''
  statusFilter.value = 'all'
  priorityFilter.value = 'all'
  taskStore.clearFilters()
}

const toggleDragMode = () => {
  dragMode.value = !dragMode.value
  // Don't refresh tasks when exiting drag mode - let the reordering persist
}

const onDragStart = () => {
  isDragging.value = true
}

const onDragEnd = async (event) => {
  isDragging.value = false
  
  console.log('Drag ended:', event.oldIndex, '->', event.newIndex)
  
  // Only proceed if the item was actually moved
  if (event.oldIndex === event.newIndex) {
    console.log('No movement detected, skipping reorder')
    return
  }

  try {
    // Get the new order of task IDs from the locally dragged tasks
    const taskOrders = localTasks.value.map(task => task.id)
    console.log('New task order:', taskOrders)
    
    // Update the backend
    await taskStore.reorderTasks(taskOrders)
    console.log('Tasks reordered successfully')
    
    // Show success feedback
    console.log('Tasks reordered successfully')
  } catch (error) {
    console.error('Failed to reorder tasks:', error)
    // Revert the order by fetching fresh data
    await taskStore.fetchTasks()
  }
}

const handleToggleStatus = async (taskId) => {
  try {
    await taskStore.toggleTaskStatus(taskId)
  } catch (error) {
    console.error('Failed to toggle task status:', error)
  }
}

const handleEditTask = (task) => {
  // Emit event to parent component to handle editing
  emit('edit-task', task)
}

const handleDeleteTask = async (taskId) => {
  try {
    await taskStore.deleteTask(taskId)
  } catch (error) {
    console.error('Failed to delete task:', error)
    // Error handling is now done in the store with SweetAlert2
  }
}

const retryFetch = () => {
  taskStore.fetchTasks()
}

// Emits
const emit = defineEmits(['edit-task'])

// Note: init() is called from DashboardView to avoid duplicate calls
</script>

<style scoped>
/* Drag and Drop Styles */
.ghost-item {
  opacity: 0.5;
  background: #e3f2fd;
  transform: rotate(2deg);
}

.chosen-item {
  background: #f3e5f5;
}

.drag-item {
  transform: rotate(2deg);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Vue Transitions */
.task-list-enter-active,
.task-list-leave-active {
  transition: all 0.3s ease;
}

.task-list-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.task-list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

.task-list-move {
  transition: transform 0.3s ease;
}

/* Drag mode animation */
.drag-mode-enter-active,
.drag-mode-leave-active {
  transition: all 0.3s ease;
}

.drag-mode-enter-from,
.drag-mode-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
