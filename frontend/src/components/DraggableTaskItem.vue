<template>
  <div 
    class="px-4 sm:px-6 py-4 hover:bg-gray-50 transition-colors duration-150 cursor-pointer"
    :class="{ 
      'opacity-75': task.status === 'completed',
      'cursor-move': dragMode,
      'bg-gray-50': dragMode && isDragging
    }"
  >
    <div class="flex items-start sm:items-center justify-between">
      <div class="flex items-start sm:items-center space-x-3 sm:space-x-4 flex-1 min-w-0">
        <!-- Drag Handle (visible only in drag mode) -->
        <div 
          v-if="dragMode" 
          class="flex-shrink-0 p-1 sm:p-2 text-gray-400 hover:text-gray-600 cursor-move"
          title="Drag to reorder"
        >
          <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
          </svg>
        </div>

        <!-- Status Toggle -->
        <button
          v-if="!dragMode"
          @click="$emit('toggle-status', task.id)"
          class="flex-shrink-0 p-1 rounded-full hover:bg-gray-100 transition-colors mt-1 sm:mt-0"
          :class="task.status === 'completed' ? 'text-green-600' : 'text-gray-400'"
          title="Toggle completion status"
        >
          <svg v-if="task.status === 'completed'" class="h-5 w-5 sm:h-6 sm:w-6" fill="currentColor" viewBox="0 0 24 24">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <svg v-else class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke-width="2"/>
          </svg>
        </button>

        <!-- Order Badge (visible only in drag mode) -->
        <div 
          v-if="dragMode" 
          class="flex-shrink-0 w-6 h-6 sm:w-8 sm:h-8 bg-blue-100 rounded-full flex items-center justify-center"
        >
          <span class="text-xs font-medium text-blue-800">{{ task.order }}</span>
        </div>

        <!-- Task Content -->
        <div class="flex-1 min-w-0">
          <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-3">
            <h4 
              class="text-sm font-medium text-gray-900 truncate"
              :class="{ 'line-through text-gray-500': task.status === 'completed' }"
            >
              {{ task.title }}
            </h4>
            
            <div class="flex flex-wrap gap-2">
              <!-- Priority Badge -->
              <span 
                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border"
                :class="getPriorityClasses(task.priority)"
              >
                <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="getPriorityDotClasses(task.priority)"></span>
                {{ task.priority.charAt(0).toUpperCase() + task.priority.slice(1) }}
              </span>

              <!-- Status Badge -->
              <span 
                v-if="task.status === 'completed'"
                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200"
              >
                ✓ Completed
              </span>
            </div>
          </div>
          
          <p 
            v-if="task.description" 
            class="mt-1 text-sm text-gray-600 line-clamp-2 sm:truncate"
            :class="{ 'line-through': task.status === 'completed' }"
          >
            {{ task.description }}
          </p>

          <!-- Task Metadata -->
          <div class="mt-2 flex flex-wrap items-center gap-2 sm:gap-4 text-xs text-gray-500">
            <span v-if="!dragMode">Order: {{ task.order }}</span>
            <span>Created: {{ formatDate(task.created_at) }}</span>
            <span v-if="task.updated_at !== task.created_at" class="hidden sm:inline">
              Updated: {{ formatDate(task.updated_at) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Action Buttons (hidden in drag mode) -->
      <div v-if="!dragMode" class="flex items-center space-x-1 sm:space-x-2 ml-2 sm:ml-4 flex-shrink-0">
        <!-- Edit Button -->
        <button
          @click="$emit('edit', task)"
          class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
          title="Edit task"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
          </svg>
        </button>

        <!-- Delete Button (show only for admins or task owners) -->
        <button
          v-if="canDelete"
          @click="$emit('delete', task.id)"
          class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
          title="Delete task"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
          </svg>
        </button>
      </div>

      <!-- Drag Mode Indicator -->
      <div v-if="dragMode" class="flex items-center space-x-2 ml-2 sm:ml-4 flex-shrink-0">
        <div class="flex flex-col space-y-1">
          <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
          <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
          <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
          <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
          <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
          <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
        </div>
        <div class="flex flex-col space-y-1">
          <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
          <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
          <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
          <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
          <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
          <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useTaskStore } from '@/stores/tasks'

// Props
const props = defineProps({
  task: {
    type: Object,
    required: true
  },
  dragMode: {
    type: Boolean,
    default: false
  },
  isDragging: {
    type: Boolean,
    default: false
  }
})

// Emits
defineEmits(['toggle-status', 'edit', 'delete'])

// Stores
const authStore = useAuthStore()
const taskStore = useTaskStore()

// Computed
const canDelete = computed(() => {
  return authStore.user?.is_admin || props.task.user_id === authStore.user?.id
})

// Methods
const getPriorityClasses = (priority) => {
  const classes = taskStore.priorityColors[priority] || 'bg-gray-100 text-gray-800 border-gray-200'
  return classes
}

const getPriorityDotClasses = (priority) => {
  switch (priority) {
    case 'high':
      return 'bg-red-400'
    case 'medium':
      return 'bg-yellow-400'
    case 'low':
      return 'bg-green-400'
    default:
      return 'bg-gray-400'
  }
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}
</script>

<style scoped>
/* Drag handle animation */
.drag-handle:hover {
  transform: scale(1.1);
}

/* Task item hover effects */
.task-item:hover .action-buttons {
  opacity: 1;
}

.action-buttons {
  opacity: 0.7;
  transition: opacity 0.2s ease;
}

/* Drag mode styling */
.drag-mode {
  border: 2px dashed #e5e7eb;
  background: #f9fafb;
}

/* Priority dot animation */
.priority-dot {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}
</style>
