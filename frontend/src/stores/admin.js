import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAdminStore = defineStore('admin', () => {
  // State
  const dashboardStats = ref(null)
  const taskStatistics = ref(null)
  const topPerformers = ref([])
  const users = ref([])
  const usersPagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })
  const selectedUser = ref(null)
  const selectedUserTasks = ref([])
  const selectedUserTasksPagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })
  const loading = ref(false)
  const error = ref(null)

  // Getters
  const isLoading = computed(() => loading.value)
  const hasError = computed(() => error.value !== null)
  const totalUsers = computed(() => dashboardStats.value?.total_users || 0)
  const totalTasks = computed(() => dashboardStats.value?.total_tasks || 0)
  const completionRate = computed(() => {
    const stats = dashboardStats.value
    if (!stats || stats.total_tasks === 0) return 0
    return Math.round((stats.completed_tasks / stats.total_tasks) * 100)
  })

  // Actions
  const setLoading = (isLoading) => {
    loading.value = isLoading
  }

  const setError = (errorMessage) => {
    error.value = errorMessage
  }

  const clearError = () => {
    error.value = null
  }

  const fetchDashboardStats = async () => {
    try {
      setLoading(true)
      clearError()
      
      const response = await api.get('/admin/dashboard-stats')
      dashboardStats.value = response.data.data
      
      return response.data.data
    } catch (err) {
      console.error('Error fetching dashboard stats:', err)
      setError('Failed to fetch dashboard statistics')
      throw err
    } finally {
      setLoading(false)
    }
  }

  const fetchTaskStatistics = async () => {
    try {
      const response = await api.get('/admin/task-statistics')
      taskStatistics.value = response.data.data
      
      return response.data.data
    } catch (err) {
      console.error('Error fetching task statistics:', err)
      setError('Failed to fetch task statistics')
      throw err
    }
  }

  const fetchTopPerformers = async () => {
    try {
      const response = await api.get('/admin/top-performers')
      topPerformers.value = response.data.data
      
      return response.data.data
    } catch (err) {
      console.error('Error fetching top performers:', err)
      setError('Failed to fetch top performers')
      throw err
    }
  }

  const fetchUsers = async (params = {}) => {
    try {
      setLoading(true)
      clearError()
      
      const response = await api.get('/admin/users', { params })
      users.value = response.data.data
      usersPagination.value = response.data.meta
      
      return response.data
    } catch (err) {
      console.error('Error fetching users:', err)
      setError('Failed to fetch users')
      throw err
    } finally {
      setLoading(false)
    }
  }

  const fetchUserDetails = async (userId, params = {}) => {
    try {
      setLoading(true)
      clearError()
      
      const response = await api.get(`/admin/users/${userId}`, { params })
      selectedUser.value = {
        ...response.data.data.user,
        statistics: response.data.data.statistics
      }
      selectedUserTasks.value = response.data.data.tasks
      selectedUserTasksPagination.value = response.data.data.meta
      
      return response.data.data
    } catch (err) {
      console.error('Error fetching user details:', err)
      setError('Failed to fetch user details')
      throw err
    } finally {
      setLoading(false)
    }
  }

  const updateUserRole = async (userId, isAdmin) => {
    try {
      setLoading(true)
      clearError()
      
      const response = await api.patch(`/admin/users/${userId}/role`, {
        is_admin: isAdmin
      })
      
      // Update the user in the users list
      const userIndex = users.value.findIndex(user => user.id === userId)
      if (userIndex !== -1) {
        users.value[userIndex] = response.data.data
      }
      
      // Update selected user if it's the same
      if (selectedUser.value?.id === userId) {
        selectedUser.value = response.data.data
      }
      
      return response.data.data
    } catch (err) {
      console.error('Error updating user role:', err)
      setError('Failed to update user role')
      throw err
    } finally {
      setLoading(false)
    }
  }

  const deleteTask = async (taskId) => {
    try {
      setLoading(true)
      clearError()
      
      const response = await api.delete(`/admin/tasks/${taskId}`)
      
      // Remove task from selected user tasks if applicable
      if (selectedUserTasks.value) {
        selectedUserTasks.value = selectedUserTasks.value.filter(task => task.id !== taskId)
      }
      
      // Refresh statistics
      await fetchDashboardStats()
      
      return response.data
    } catch (err) {
      console.error('Error deleting task:', err)
      setError('Failed to delete task')
      throw err
    } finally {
      setLoading(false)
    }
  }

  const searchUsers = async (searchTerm, filters = {}) => {
    return await fetchUsers({
      search: searchTerm,
      ...filters
    })
  }

  const filterUsersByRole = async (role) => {
    return await fetchUsers({
      role: role
    })
  }

  const changePage = async (page, type = 'users') => {
    if (type === 'users') {
      return await fetchUsers({
        page,
        per_page: usersPagination.value.per_page
      })
    } else if (type === 'userTasks' && selectedUser.value) {
      return await fetchUserDetails(selectedUser.value.id, {
        page,
        per_page: selectedUserTasksPagination.value.per_page
      })
    }
  }

  const changePerPage = async (perPage, type = 'users') => {
    if (type === 'users') {
      return await fetchUsers({
        per_page: perPage,
        page: 1
      })
    } else if (type === 'userTasks' && selectedUser.value) {
      return await fetchUserDetails(selectedUser.value.id, {
        per_page: perPage,
        page: 1
      })
    }
  }

  const clearSelectedUser = () => {
    selectedUser.value = null
    selectedUserTasks.value = []
    selectedUserTasksPagination.value = {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0
    }
  }

  const resetState = () => {
    dashboardStats.value = null
    taskStatistics.value = null
    topPerformers.value = []
    users.value = []
    usersPagination.value = {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0
    }
    clearSelectedUser()
    clearError()
    setLoading(false)
  }

  return {
    // State
    dashboardStats,
    taskStatistics,
    topPerformers,
    users,
    usersPagination,
    selectedUser,
    selectedUserTasks,
    selectedUserTasksPagination,
    loading,
    error,

    // Getters
    isLoading,
    hasError,
    totalUsers,
    totalTasks,
    completionRate,

    // Actions
    setLoading,
    setError,
    clearError,
    fetchDashboardStats,
    fetchTaskStatistics,
    fetchTopPerformers,
    fetchUsers,
    fetchUserDetails,
    updateUserRole,
    deleteTask,
    searchUsers,
    filterUsersByRole,
    changePage,
    changePerPage,
    clearSelectedUser,
    resetState
  }
})
