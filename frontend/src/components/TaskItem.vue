<template>
  <div 
    class="px-6 py-4 hover:bg-gray-50 transition-colors duration-150"
    :class="{ 'opacity-75': task.status === 'completed' }"
  >
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-4 flex-1">
        <!-- Status Toggle -->
        <button
          @click="$emit('toggle-status', task.id)"
          class="flex-shrink-0 p-1 rounded-full hover:bg-gray-100 transition-colors"
          :class="task.status === 'completed' ? 'text-green-600' : 'text-gray-400'"
        >
          <svg v-if="task.status === 'completed'" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke-width="2"/>
          </svg>
        </button>

        <!-- Task Content -->
        <div class="flex-1 min-w-0">
          <div class="flex items-center space-x-3">
            <h4 
              class="text-sm font-medium text-gray-900 truncate"
              :class="{ 'line-through text-gray-500': task.status === 'completed' }"
            >
              {{ task.title }}
            </h4>
            
            <!-- Priority Badge -->
            <span 
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
              :class="getPriorityClasses(task.priority)"
            >
              {{ task.priority.charAt(0).toUpperCase() + task.priority.slice(1) }}
            </span>

            <!-- Status Badge -->
            <span 
              v-if="task.status === 'completed'"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200"
            >
              ✓ Completed
            </span>
          </div>
          
          <p 
            v-if="task.description" 
            class="mt-1 text-sm text-gray-600 truncate"
            :class="{ 'line-through': task.status === 'completed' }"
          >
            {{ task.description }}
          </p>

          <!-- Task Metadata -->
          <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
            <span>Order: {{ task.order }}</span>
            <span>Created: {{ formatDate(task.created_at) }}</span>
            <span v-if="task.updated_at !== task.created_at">
              Updated: {{ formatDate(task.updated_at) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center space-x-2 ml-4">
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

        <!-- Drag Handle (for reordering) -->
        <div class="drag-handle p-2 text-gray-400 hover:text-gray-600 cursor-move">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
          </svg>
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
  return taskStore.priorityColors[priority] || 'bg-gray-100 text-gray-800 border-gray-200'
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
.drag-handle {
  cursor: grab;
}

.drag-handle:active {
  cursor: grabbing;
}

/* Transition for status changes */
.task-item {
  transition: all 0.3s ease;
}
</style>
