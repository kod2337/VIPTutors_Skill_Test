<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto pointer-events-none" @click.self="$emit('close')">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Modal panel -->
      <div class="relative inline-block align-bottom bg-white rounded-lg text-left shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full max-h-[90vh] overflow-y-auto border border-gray-300 pointer-events-auto">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <!-- Header -->
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-16 w-16">
                <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center">
                  <i class="fas fa-user text-gray-600 text-2xl"></i>
                </div>
              </div>
              <div class="ml-4">
                <h3 class="text-2xl font-bold text-gray-900">{{ user?.name }}</h3>
                <p class="text-gray-600">{{ user?.email }}</p>
                <div class="mt-2">
                  <span 
                    :class="[
                      'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                      user?.is_admin 
                        ? 'bg-purple-100 text-purple-800' 
                        : 'bg-blue-100 text-blue-800'
                    ]"
                  >
                    <i 
                      :class="[
                        user?.is_admin ? 'fas fa-crown' : 'fas fa-user',
                        'mr-2'
                      ]"
                    ></i>
                    {{ user?.is_admin ? 'Administrator' : 'Regular User' }}
                  </span>
                </div>
              </div>
            </div>
            <button
              @click="$emit('close')"
              class="text-gray-400 hover:text-gray-600 transition-colors duration-150"
            >
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>

          <!-- User Statistics -->
          <div class="mb-6">
            <h4 class="text-lg font-medium text-gray-900 mb-4">User Statistics</h4>
            
            <!-- Loading State -->
            <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div v-for="i in 4" :key="i" class="animate-pulse">
                <div class="bg-gray-200 rounded-lg p-4">
                  <div class="h-8 bg-gray-300 rounded mb-2"></div>
                  <div class="h-4 bg-gray-300 rounded w-20"></div>
                </div>
              </div>
            </div>
            
            <!-- Statistics Data -->
            <div v-else-if="userDetails && userDetails.statistics" class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="bg-blue-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-blue-600">{{ userDetails.statistics?.total_tasks || 0 }}</div>
                <div class="text-sm text-blue-800">Total Tasks</div>
              </div>
              <div class="bg-green-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-green-600">{{ userDetails.statistics?.completed_tasks || 0 }}</div>
                <div class="text-sm text-green-800">Completed</div>
              </div>
              <div class="bg-yellow-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-yellow-600">{{ userDetails.statistics?.pending_tasks || 0 }}</div>
                <div class="text-sm text-yellow-800">Pending</div>
              </div>
              <div class="bg-purple-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-purple-600">{{ userDetails.statistics?.completion_rate || 0 }}%</div>
                <div class="text-sm text-purple-800">Completion Rate</div>
              </div>
            </div>

            <!-- Priority Breakdown -->
            <div v-if="!loading && userDetails && userDetails.statistics" class="mt-4 grid grid-cols-3 gap-4">
              <div class="bg-red-50 rounded-lg p-3">
                <div class="text-lg font-bold text-red-600">{{ userDetails.statistics?.high_priority_tasks || 0 }}</div>
                <div class="text-xs text-red-800">High Priority</div>
              </div>
              <div class="bg-orange-50 rounded-lg p-3">
                <div class="text-lg font-bold text-orange-600">{{ userDetails.statistics?.medium_priority_tasks || 0 }}</div>
                <div class="text-xs text-orange-800">Medium Priority</div>
              </div>
              <div class="bg-blue-50 rounded-lg p-3">
                <div class="text-lg font-bold text-blue-600">{{ userDetails.statistics?.low_priority_tasks || 0 }}</div>
                <div class="text-xs text-blue-800">Low Priority</div>
              </div>
            </div>
            
            <!-- Loading State for Priority Breakdown -->
            <div v-else-if="loading" class="mt-4 grid grid-cols-3 gap-4">
              <div v-for="i in 3" :key="i" class="animate-pulse">
                <div class="bg-gray-200 rounded-lg p-3">
                  <div class="h-6 bg-gray-300 rounded mb-1"></div>
                  <div class="h-3 bg-gray-300 rounded w-16"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Task Filters -->
          <div class="mb-4 flex flex-wrap gap-4">
            <select
              v-model="statusFilter"
              @change="applyFilters"
              class="px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
            >
              <option value="all">All Status</option>
              <option value="pending">Pending</option>
              <option value="completed">Completed</option>
            </select>
            
            <select
              v-model="priorityFilter"
              @change="applyFilters"
              class="px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
            >
              <option value="all">All Priority</option>
              <option value="high">High Priority</option>
              <option value="medium">Medium Priority</option>
              <option value="low">Low Priority</option>
            </select>

            <select
              v-model="perPage"
              @change="changePerPage"
              class="px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
            >
              <option :value="5">5 per page</option>
              <option :value="10">10 per page</option>
              <option :value="15">15 per page</option>
            </select>
          </div>

          <!-- User Tasks -->
          <div class="mb-6">
            <h4 class="text-lg font-medium text-gray-900 mb-4">User Tasks</h4>
            
            <div v-if="loading" class="space-y-3">
              <div v-for="i in 3" :key="i" class="animate-pulse">
                <div class="h-20 bg-gray-200 rounded"></div>
              </div>
            </div>

            <div v-else-if="userTasks.length === 0" class="text-center py-8">
              <div class="text-gray-400 mb-2">
                <i class="fas fa-tasks text-4xl"></i>
              </div>
              <p class="text-gray-500">No tasks found</p>
            </div>

            <div v-else class="space-y-3 max-h-64 overflow-y-auto">
              <div 
                v-for="task in userTasks" 
                :key="task.id"
                class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors duration-150"
              >
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <h5 class="font-medium text-gray-900 mb-1">{{ task.title }}</h5>
                    <p v-if="task.description" class="text-sm text-gray-600 mb-2 line-clamp-2">
                      {{ task.description }}
                    </p>
                    <div class="flex items-center space-x-4 text-xs text-gray-500">
                      <span>Created: {{ formatDate(task.created_at) }}</span>
                      <span v-if="task.due_date">Due: {{ formatDate(task.due_date) }}</span>
                    </div>
                  </div>
                  <div class="flex items-center space-x-2 ml-4">
                    <!-- Priority Badge -->
                    <span 
                      :class="[
                        'px-2 py-1 rounded-full text-xs font-medium',
                        getPriorityColor(task.priority)
                      ]"
                    >
                      {{ task.priority }}
                    </span>
                    
                    <!-- Status Badge -->
                    <span 
                      :class="[
                        'px-2 py-1 rounded-full text-xs font-medium',
                        task.status === 'completed' 
                          ? 'bg-green-100 text-green-800' 
                          : 'bg-yellow-100 text-yellow-800'
                      ]"
                    >
                      {{ task.status }}
                    </span>
                    
                    <!-- Admin Delete Button -->
                    <button
                      @click="deleteTask(task)"
                      class="text-red-600 hover:text-red-800 transition-colors duration-150"
                      title="Delete Task (Admin)"
                    >
                      <i class="fas fa-trash text-sm"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tasks Pagination -->
            <div v-if="!loading && userTasks.length > 0" class="mt-4 flex items-center justify-between text-sm">
              <div class="text-gray-700">
                Showing {{ tasksPagination.from || 0 }} to {{ tasksPagination.to || 0 }} of {{ tasksPagination.total }} tasks
              </div>
              
              <div class="flex items-center space-x-2">
                <button
                  @click="changeTasksPage(tasksPagination.current_page - 1)"
                  :disabled="tasksPagination.current_page <= 1"
                  class="px-2 py-1 bg-white border border-gray-300 rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                  Previous
                </button>
                
                <span class="text-gray-700">
                  {{ tasksPagination.current_page }} / {{ tasksPagination.last_page }}
                </span>
                
                <button
                  @click="changeTasksPage(tasksPagination.current_page + 1)"
                  :disabled="tasksPagination.current_page >= tasksPagination.last_page"
                  class="px-2 py-1 bg-white border border-gray-300 rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                  Next
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button
            v-if="user && !user.is_admin"
            @click="promoteToAdmin"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
          >
            <i class="fas fa-user-plus mr-2"></i>
            Make Admin
          </button>
          
          <button
            v-if="user && user.is_admin && user.id !== currentUserId"
            @click="removeAdmin"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm"
          >
            <i class="fas fa-user-minus mr-2"></i>
            Remove Admin
          </button>
          
          <button
            @click="$emit('close')"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useAdminStore } from '@/stores/admin'
import { useAuthStore } from '@/stores/auth'
import { formatDate } from '@/utils/dateFormatter'
import Swal from 'sweetalert2'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  user: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'role-updated'])

const adminStore = useAdminStore()
const authStore = useAuthStore()

const statusFilter = ref('all')
const priorityFilter = ref('all')
const perPage = ref(15)

const userDetails = computed(() => adminStore.selectedUser)
const userTasks = computed(() => adminStore.selectedUserTasks)
const tasksPagination = computed(() => adminStore.selectedUserTasksPagination)
const loading = computed(() => adminStore.loading)
const currentUserId = computed(() => authStore.user?.id)

watch(
  () => props.show,
  (newShow) => {
    if (newShow && props.user) {
      fetchUserDetails()
    }
  }
)

const fetchUserDetails = async () => {
  if (!props.user) return
  
  try {
    await adminStore.fetchUserDetails(props.user.id, {
      status: statusFilter.value,
      priority: priorityFilter.value,
      per_page: perPage.value
    })
  } catch (error) {
    console.error('Error fetching user details:', error)
  }
}

const applyFilters = () => {
  fetchUserDetails()
}

const changePerPage = () => {
  fetchUserDetails()
}

const changeTasksPage = async (page) => {
  if (page < 1 || page > tasksPagination.value.last_page) return
  
  try {
    await adminStore.changePage(page, 'userTasks')
  } catch (error) {
    console.error('Error changing tasks page:', error)
  }
}

const getPriorityColor = (priority) => {
  switch (priority) {
    case 'high':
      return 'bg-red-100 text-red-800'
    case 'medium':
      return 'bg-yellow-100 text-yellow-800'
    case 'low':
      return 'bg-blue-100 text-blue-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const deleteTask = async (task) => {
  const result = await Swal.fire({
    title: 'Delete Task?',
    text: `Are you sure you want to delete the task "${task.title}"? This action cannot be undone.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel',
    reverseButtons: true
  })

  if (result.isConfirmed) {
    try {
      await adminStore.deleteTask(task.id)
      // Refresh user details to update task count
      await fetchUserDetails()
      
      Swal.fire({
        title: 'Deleted!',
        text: `Task "${task.title}" has been deleted successfully.`,
        icon: 'success',
        confirmButtonColor: '#10b981',
        timer: 3000,
        timerProgressBar: true
      })
    } catch (error) {
      console.error('Error deleting task:', error)
      
      Swal.fire({
        title: 'Error!',
        text: 'Failed to delete task. Please try again.',
        icon: 'error',
        confirmButtonColor: '#ef4444'
      })
    }
  }
}

const promoteToAdmin = async () => {
  const result = await Swal.fire({
    title: 'Promote to Admin?',
    text: `Are you sure you want to promote ${props.user.name} to administrator? This will give them full admin privileges.`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, promote!',
    cancelButtonText: 'Cancel',
    reverseButtons: true
  })

  if (result.isConfirmed) {
    try {
      await emit('role-updated', props.user.id, true)
      
      Swal.fire({
        title: 'Success!',
        text: `${props.user.name} has been promoted to administrator.`,
        icon: 'success',
        confirmButtonColor: '#10b981',
        timer: 3000,
        timerProgressBar: true
      })
    } catch (error) {
      console.error('Error promoting user:', error)
      
      Swal.fire({
        title: 'Error!',
        text: 'Failed to promote user. Please try again.',
        icon: 'error',
        confirmButtonColor: '#ef4444'
      })
    }
  }
}

const removeAdmin = async () => {
  const result = await Swal.fire({
    title: 'Remove Admin Privileges?',
    text: `Are you sure you want to remove admin privileges from ${props.user.name}? They will become a regular user.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#f59e0b',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, remove admin!',
    cancelButtonText: 'Cancel',
    reverseButtons: true
  })

  if (result.isConfirmed) {
    try {
      await emit('role-updated', props.user.id, false)
      
      Swal.fire({
        title: 'Success!',
        text: `Admin privileges have been removed from ${props.user.name}.`,
        icon: 'success',
        confirmButtonColor: '#10b981',
        timer: 3000,
        timerProgressBar: true
      })
    } catch (error) {
      console.error('Error removing admin:', error)
      
      Swal.fire({
        title: 'Error!',
        text: 'Failed to remove admin privileges. Please try again.',
        icon: 'error',
        confirmButtonColor: '#ef4444'
      })
    }
  }
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

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
