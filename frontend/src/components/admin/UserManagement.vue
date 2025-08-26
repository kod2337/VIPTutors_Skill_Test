<template>
  <div class="user-management">
    <!-- Header with Search and Filters -->
    <div class="bg-white rounded-lg shadow mb-6">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
          <h2 class="text-lg font-medium text-gray-900 mb-4 sm:mb-0">User Management</h2>
          <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 sm:w-2/3">
            <!-- Search -->
            <div class="relative flex-[3] min-w-0">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
              </div>
              <input  
                v-model="searchTerm"
                @input="debouncedSearch"
                type="text"
                placeholder="Search users by name or email..."
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              />
            </div>
            
            <!-- Role Filter -->
            <select
              v-model="selectedRole"
              @change="applyFilters"
              class="block w-full sm:w-32 flex-shrink-0 px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
              <option value="all">All Users</option>
              <option value="admin">Admins</option>
              <option value="user">Regular Users</option>
            </select>
            
            <!-- Per Page -->
            <select
              v-model="perPage"
              @change="changePerPage"
              class="block w-full sm:w-28 flex-shrink-0 px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
              <option :value="5">5 per page</option>
              <option :value="10">10 per page</option>
              <option :value="15">15 per page</option>
              <option :value="25">25 per page</option>
              <option :value="50">50 per page</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="overflow-hidden">
        <div v-if="loading" class="p-6">
          <div class="animate-pulse space-y-4">
            <div v-for="i in 5" :key="i" class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
              <div class="flex-1 space-y-2">
                <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
              </div>
              <div class="h-8 bg-gray-200 rounded w-20"></div>
              <div class="h-8 bg-gray-200 rounded w-16"></div>
            </div>
          </div>
        </div>

        <div v-else-if="users.length === 0" class="p-6 text-center">
          <div class="text-gray-400 mb-2">
            <i class="fas fa-users text-4xl"></i>
          </div>
          <p class="text-gray-500">No users found</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  User
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Role
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tasks Overview
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Performance
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Recent Activity
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr 
                v-for="user in users" 
                :key="user.id"
                class="hover:bg-gray-50 transition-colors duration-150"
              >
                <!-- User Info -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-12 w-12">
                      <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                        <i class="fas fa-user text-gray-600 text-lg"></i>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-sm text-gray-500">{{ user.email }}</div>
                    </div>
                  </div>
                </td>

                <!-- Role -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      user.is_admin 
                        ? 'bg-purple-100 text-purple-800' 
                        : 'bg-blue-100 text-blue-800'
                    ]"
                  >
                    <i 
                      :class="[
                        user.is_admin ? 'fas fa-crown' : 'fas fa-user',
                        'mr-1'
                      ]"
                    ></i>
                    {{ user.is_admin ? 'Admin' : 'User' }}
                  </span>
                </td>

                <!-- Tasks Overview -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    <div class="font-medium">{{ user.tasks_count }} total</div>
                    <div class="text-xs text-gray-500">
                      {{ user.completed_tasks_count }} completed, {{ user.pending_tasks_count }} pending
                    </div>
                    <div v-if="user.high_priority_tasks_count > 0" class="text-xs text-red-600">
                      {{ user.high_priority_tasks_count }} high priority
                    </div>
                  </div>
                </td>

                <!-- Performance -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-1">
                      <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                        <span>{{ user.completion_rate }}%</span>
                      </div>
                      <div class="w-full bg-gray-200 rounded-full h-2">
                        <div 
                          :class="[
                            'h-2 rounded-full transition-all duration-500',
                            getCompletionRateColor(user.completion_rate)
                          ]"
                          :style="{ width: `${user.completion_rate}%` }"
                        ></div>
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Recent Activity -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    {{ user.recent_activity }} tasks
                  </div>
                  <div class="text-xs text-gray-500">this week</div>
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                  <button
                    @click="viewUserDetails(user)"
                    class="text-blue-600 hover:text-blue-900 transition-colors duration-150"
                    title="View Details"
                  >
                    <i class="fas fa-eye"></i>
                  </button>
                  
                  <button
                    @click="toggleUserRole(user)"
                    :disabled="user.id === currentUserId"
                    :class="[
                      'transition-colors duration-150',
                      user.id === currentUserId
                        ? 'text-gray-400 cursor-not-allowed'
                        : user.is_admin
                          ? 'text-orange-600 hover:text-orange-900'
                          : 'text-green-600 hover:text-green-900'
                    ]"
                    :title="user.id === currentUserId ? 'Cannot modify your own role' : user.is_admin ? 'Remove Admin' : 'Make Admin'"
                  >
                    <i :class="user.is_admin ? 'fas fa-user-minus' : 'fas fa-user-plus'"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="!loading && users.length > 0" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total }} users
          </div>
          
          <div class="flex items-center space-x-2">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page <= 1"
              class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
            >
              Previous
            </button>
            
            <span class="text-sm text-gray-700">
              Page {{ pagination.current_page }} of {{ pagination.last_page }}
            </span>
            
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page >= pagination.last_page"
              class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- User Details Modal -->
    <UserDetailsModal
      :show="showUserModal"
      :user="selectedUser"
      @close="closeUserModal"
      @role-updated="handleRoleUpdate"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAdminStore } from '@/stores/admin'
import { useAuthStore } from '@/stores/auth'
import UserDetailsModal from './UserDetailsModal.vue'
import Swal from 'sweetalert2'

// Simple debounce function
const debounce = (func, wait) => {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

const adminStore = useAdminStore()
const authStore = useAuthStore()

const searchTerm = ref('')
const selectedRole = ref('all')
const perPage = ref(15)
const showUserModal = ref(false)
const selectedUser = ref(null)

const users = computed(() => adminStore.users)
const pagination = computed(() => adminStore.usersPagination)
const loading = computed(() => adminStore.loading)
const currentUserId = computed(() => authStore.user?.id)

const debouncedSearch = debounce(() => {
  applyFilters()
}, 300)

const applyFilters = async () => {
  try {
    await adminStore.fetchUsers({
      search: searchTerm.value || undefined,
      role: selectedRole.value,
      per_page: perPage.value,
      page: 1
    })
  } catch (error) {
    console.error('Error applying filters:', error)
  }
}

const changePerPage = async () => {
  try {
    await adminStore.changePerPage(perPage.value, 'users')
  } catch (error) {
    console.error('Error changing per page:', error)
  }
}

const changePage = async (page) => {
  if (page < 1 || page > pagination.value.last_page) return
  
  try {
    await adminStore.changePage(page, 'users')
  } catch (error) {
    console.error('Error changing page:', error)
  }
}

const getCompletionRateColor = (rate) => {
  if (rate >= 90) return 'bg-green-500'
  if (rate >= 70) return 'bg-blue-500'
  if (rate >= 50) return 'bg-yellow-500'
  return 'bg-red-500'
}

const viewUserDetails = (user) => {
  selectedUser.value = user
  showUserModal.value = true
}

const closeUserModal = () => {
  showUserModal.value = false
  selectedUser.value = null
}

const toggleUserRole = async (user) => {
  if (user.id === currentUserId.value) return
  
  const newRole = !user.is_admin
  const actionText = newRole ? 'promote to admin' : 'remove admin privileges'
  const title = newRole ? 'Promote to Admin?' : 'Remove Admin Privileges?'
  const text = newRole 
    ? `Are you sure you want to promote ${user.name} to administrator? This will give them full admin privileges.`
    : `Are you sure you want to remove admin privileges from ${user.name}? They will become a regular user.`
  
  const result = await Swal.fire({
    title: title,
    text: text,
    icon: newRole ? 'question' : 'warning',
    showCancelButton: true,
    confirmButtonColor: newRole ? '#3085d6' : '#f59e0b',
    cancelButtonColor: '#6b7280',
    confirmButtonText: newRole ? 'Yes, promote!' : 'Yes, remove admin!',
    cancelButtonText: 'Cancel',
    reverseButtons: true
  })

  if (result.isConfirmed) {
    try {
      await adminStore.updateUserRole(user.id, newRole)
      
      Swal.fire({
        title: 'Success!',
        text: newRole 
          ? `${user.name} has been promoted to administrator.`
          : `Admin privileges have been removed from ${user.name}.`,
        icon: 'success',
        confirmButtonColor: '#10b981',
        timer: 3000,
        timerProgressBar: true
      })
    } catch (error) {
      console.error('Error updating user role:', error)
      
      Swal.fire({
        title: 'Error!',
        text: 'Failed to update user role. Please try again.',
        icon: 'error',
        confirmButtonColor: '#ef4444'
      })
    }
  }
}

const handleRoleUpdate = async (userId, isAdmin) => {
  try {
    await adminStore.updateUserRole(userId, isAdmin)
    closeUserModal()
  } catch (error) {
    console.error('Error updating user role:', error)
  }
}

onMounted(() => {
  adminStore.fetchUsers()
})
</script>

<style scoped>
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
