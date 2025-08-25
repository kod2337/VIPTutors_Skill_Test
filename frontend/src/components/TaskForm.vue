<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div 
        v-if="show"
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4" 
        @click="closeModal"
      >
        <Transition
          enter-active-class="transition-all duration-300 ease-out"
          enter-from-class="opacity-0 scale-95 translate-y-4"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-active-class="transition-all duration-200 ease-in"
          leave-from-class="opacity-100 scale-100 translate-y-0"
          leave-to-class="opacity-0 scale-95 translate-y-4"
        >
          <div 
            v-if="show"
            class="relative w-full max-w-lg mx-auto bg-white rounded-2xl shadow-2xl border border-gray-200" 
            @click.stop
          >
            <!-- Header with gradient background -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-5 rounded-t-2xl">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg v-if="isEditing" class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    <svg v-else class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-xl font-semibold text-white">
                      {{ isEditing ? 'Edit Task' : 'Create New Task' }}
                    </h3>
                    <p class="text-blue-100 text-sm">
                      {{ isEditing ? 'Update your task details' : 'Add a new task to your list' }}
                    </p>
                  </div>
                </div>
                <button
                  @click="closeModal"
                  class="w-10 h-10 bg-white/20 hover:bg-white/30 rounded-xl flex items-center justify-center transition-colors duration-200 group"
                >
                  <svg class="h-5 w-5 text-white group-hover:text-gray-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Form Content -->
            <div class="px-6 py-6">
              <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Title -->
                <div class="space-y-2">
                  <label for="title" class="block text-sm font-semibold text-gray-700">
                    Task Title <span class="text-red-500">*</span>
                  </label>
                  <div class="relative">
                    <input
                      id="title"
                      v-model="form.title"
                      type="text"
                      required
                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-sm"
                      :class="{ 
                        'border-red-300 focus:ring-red-500': errors.title,
                        'border-green-300 focus:ring-green-500': !errors.title && form.title.trim()
                      }"
                      placeholder="Enter a descriptive task title..."
                    />
                    <div v-if="!errors.title && form.title.trim()" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                      <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </div>
                  </div>
                  <p v-if="errors.title" class="text-sm text-red-600 flex items-center">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    {{ errors.title[0] }}
                  </p>
                </div>

                <!-- Description -->
                <div class="space-y-2">
                  <label for="description" class="block text-sm font-semibold text-gray-700">
                    Description
                  </label>
                  <textarea
                    id="description"
                    v-model="form.description"
                    rows="4"
                    class="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-sm resize-none"
                    :class="{ 
                      'border-red-300 focus:ring-red-500': errors.description,
                      'border-green-300 focus:ring-green-500': !errors.description && form.description.trim()
                    }"
                    placeholder="Add more details about your task (optional)..."
                  ></textarea>
                  <p v-if="errors.description" class="text-sm text-red-600 flex items-center">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    {{ errors.description[0] }}
                  </p>
                </div>

                <!-- Priority and Status Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <!-- Priority -->
                  <div class="space-y-2">
                    <label for="priority" class="block text-sm font-semibold text-gray-700">
                      Priority <span class="text-red-500">*</span>
                    </label>
                    <select
                      id="priority"
                      v-model="form.priority"
                      required
                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-sm"
                      :class="{ 'border-red-300 focus:ring-red-500': errors.priority }"
                    >
                      <option value="low">Low Priority</option>
                      <option value="medium">Medium Priority</option>
                      <option value="high">High Priority</option>
                    </select>
                    <p v-if="errors.priority" class="text-sm text-red-600 flex items-center">
                      <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                      </svg>
                      {{ errors.priority[0] }}
                    </p>
                  </div>

                  <!-- Status (only for editing) -->
                  <div v-if="isEditing" class="space-y-2">
                    <label for="status" class="block text-sm font-semibold text-gray-700">
                      Status
                    </label>
                    <select
                      id="status"
                      v-model="form.status"
                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-sm"
                      :class="{ 'border-red-300 focus:ring-red-500': errors.status }"
                    >
                      <option value="pending">Pending</option>
                      <option value="completed">Completed</option>
                    </select>
                    <p v-if="errors.status" class="text-sm text-red-600 flex items-center">
                      <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                      </svg>
                      {{ errors.status[0] }}
                    </p>
                  </div>
                </div>

                <!-- Error Message -->
                <Transition
                  enter-active-class="transition-all duration-300 ease-out"
                  enter-from-class="opacity-0 scale-95"
                  enter-to-class="opacity-100 scale-100"
                  leave-active-class="transition-all duration-200 ease-in"
                  leave-from-class="opacity-100 scale-100"
                  leave-to-class="opacity-0 scale-95"
                >
                  <div v-if="submitError" class="rounded-xl bg-red-50 border border-red-200 p-4">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                      </div>
                      <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Something went wrong</h3>
                        <div class="mt-1 text-sm text-red-700">
                          <p>{{ submitError }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </Transition>
              </form>
            </div>

            <!-- Footer Actions -->
            <div class="bg-gray-50 px-6 py-4 rounded-b-2xl border-t border-gray-200">
              <div class="flex flex-col-reverse sm:flex-row sm:justify-end space-y-reverse space-y-2 sm:space-y-0 sm:space-x-3">
                <button
                  type="button"
                  @click="closeModal"
                  class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 border border-gray-300 rounded-xl shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                  :disabled="loading"
                >
                  Cancel
                </button>
                <button
                  @click="handleSubmit"
                  :disabled="loading || !form.title.trim()"
                  class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
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
                    <span v-if="loading" class="flex items-center">
                      <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      {{ isEditing ? 'Updating...' : 'Creating...' }}
                    </span>
                    <span v-else class="flex items-center">
                      <svg v-if="isEditing" class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                      <svg v-else class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                      </svg>
                      {{ isEditing ? 'Update Task' : 'Create Task' }}
                    </span>
                  </Transition>
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useTaskStore } from '@/stores/tasks'

// Props
const props = defineProps({
  task: {
    type: Object,
    default: null
  },
  show: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['close', 'saved'])

// Store
const taskStore = useTaskStore()

// Form state
const form = ref({
  title: '',
  description: '',
  priority: 'medium',
  status: 'pending'
})

const errors = ref({})
const submitError = ref('')
const loading = ref(false)

// Computed
const isEditing = computed(() => !!props.task)

// Methods (defined before watchers to avoid reference errors)
const resetForm = () => {
  form.value = {
    title: '',
    description: '',
    priority: 'medium',
    status: 'pending'
  }
  errors.value = {}
  submitError.value = ''
}

const closeModal = () => {
  resetForm()
  emit('close')
}

// Watchers
watch(() => props.task, (newTask) => {
  if (newTask) {
    // Editing mode - populate form with task data
    form.value = {
      title: newTask.title || '',
      description: newTask.description || '',
      priority: newTask.priority || 'medium',
      status: newTask.status || 'pending'
    }
  } else {
    // Create mode - reset form
    resetForm()
  }
}, { immediate: true })

const handleSubmit = async () => {
  loading.value = true
  errors.value = {}
  submitError.value = ''

  try {
    const taskData = {
      title: form.value.title.trim(),
      description: form.value.description.trim(),
      priority: form.value.priority,
      ...(isEditing.value && { status: form.value.status })
    }

    let result
    if (isEditing.value) {
      result = await taskStore.updateTask(props.task.id, taskData)
    } else {
      result = await taskStore.createTask(taskData)
    }

    emit('saved', result)
    closeModal()
  } catch (error) {
    if (error.response?.status === 422) {
      // Validation errors
      errors.value = error.response.data.errors || {}
    } else {
      submitError.value = error.response?.data?.message || 'An error occurred while saving the task'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* Custom scrollbar for the modal */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 6px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 6px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Form input focus ring enhancement */
input:focus,
textarea:focus,
select:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Gradient animation for the submit button */
.bg-gradient-to-r {
  background-size: 200% 200%;
  animation: gradient-shift 3s ease infinite;
}

@keyframes gradient-shift {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

/* Backdrop blur support */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
}

/* Enhanced focus states */
button:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Smooth state transitions */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
</style>
