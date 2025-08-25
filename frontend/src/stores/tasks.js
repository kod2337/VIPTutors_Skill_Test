import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiClient from '@/services/api'
import Swal from 'sweetalert2'

export const useTaskStore = defineStore('tasks', () => {
  // State
  const tasks = ref([])
  const loading = ref(false)
  const error = ref(null)
  const searchSuggestions = ref([])
  const searchCache = ref(new Map())
  const filters = ref({
    status: 'all',
    priority: 'all',
    search: '',
    sort_by: 'order',
    sort_direction: 'asc'
  })
  const pagination = ref({
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
    from: 0,
    to: 0
  })
  const usePagination = ref(false)
  const statistics = ref({
    total: 0,
    completed: 0,
    pending: 0,
    high_priority: 0,
    medium_priority: 0,
    low_priority: 0,
    completion_rate: 0
  })

  // Getters
  const filteredTasks = computed(() => {
    let filtered = [...tasks.value]

    // Filter by status
    if (filters.value.status !== 'all') {
      filtered = filtered.filter(task => task.status === filters.value.status)
    }

    // Filter by priority
    if (filters.value.priority !== 'all') {
      filtered = filtered.filter(task => task.priority === filters.value.priority)
    }

    // Filter by search
    if (filters.value.search) {
      const searchLower = filters.value.search.toLowerCase()
      filtered = filtered.filter(task => 
        task.title.toLowerCase().includes(searchLower) ||
        task.description.toLowerCase().includes(searchLower)
      )
    }

    // Sort by order
    return filtered.sort((a, b) => a.order - b.order)
  })

  const pendingTasks = computed(() => 
    tasks.value.filter(task => task.status === 'pending')
  )

  const completedTasks = computed(() => 
    tasks.value.filter(task => task.status === 'completed')
  )

  const priorityColors = {
    low: 'bg-green-100 text-green-800 border-green-200',
    medium: 'bg-yellow-100 text-yellow-800 border-yellow-200', 
    high: 'bg-red-100 text-red-800 border-red-200'
  }

  // Actions
  const fetchTasks = async () => {
    loading.value = true
    error.value = null
    
    try {
      const params = new URLSearchParams()
      
      // Add filters
      if (filters.value.status !== 'all') {
        params.append('status', filters.value.status)
      }
      if (filters.value.priority !== 'all') {
        params.append('priority', filters.value.priority)
      }
      if (filters.value.search) {
        params.append('search', filters.value.search)
      }
      if (filters.value.sort_by) {
        params.append('sort_by', filters.value.sort_by)
        params.append('sort_direction', filters.value.sort_direction)
      }
      
      // Add pagination if enabled
      if (usePagination.value) {
        params.append('page', pagination.value.current_page)
        params.append('per_page', pagination.value.per_page)
      }

      const response = await apiClient.get(`/tasks?${params.toString()}`)
      
      if (usePagination.value && response.data.meta) {
        // Handle paginated response
        tasks.value = response.data.data
        pagination.value = {
          current_page: response.data.meta.current_page,
          per_page: response.data.meta.per_page,
          total: response.data.meta.total,
          last_page: response.data.meta.last_page,
          from: response.data.meta.from,
          to: response.data.meta.to
        }
      } else {
        // Handle non-paginated response
        tasks.value = response.data.data
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch tasks'
      console.error('Error fetching tasks:', err)
    } finally {
      loading.value = false
    }
  }

  // Fetch search suggestions
  const fetchSearchSuggestions = async (query) => {
    if (!query.trim()) {
      searchSuggestions.value = []
      return
    }

    const cacheKey = `search_${query.trim().toLowerCase()}`
    
    // Check cache first
    if (searchCache.value.has(cacheKey)) {
      const cached = searchCache.value.get(cacheKey)
      // Use cache if it's less than 5 minutes old
      if (Date.now() - cached.timestamp < 300000) {
        searchSuggestions.value = cached.data
        return
      }
    }

    try {
      const response = await api.get('/tasks-search-suggestions', {
        params: { search: query.trim() }
      })
      const suggestions = response.data.data || []
      
      // Cache the results
      searchCache.value.set(cacheKey, {
        data: suggestions,
        timestamp: Date.now()
      })
      
      searchSuggestions.value = suggestions
    } catch (err) {
      console.error('Error fetching search suggestions:', err)
      searchSuggestions.value = []
    }
  }

  // Clear search cache
  const clearSearchCache = () => {
    searchCache.value.clear()
  }

  const createTask = async (taskData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiClient.post('/tasks', taskData)
      tasks.value.push(response.data.data)
      clearSearchCache() // Clear search cache since tasks have changed
      await fetchStatistics() // Update statistics
      
      // Show success notification
      Swal.fire({
        title: 'Success!',
        text: 'Task created successfully.',
        icon: 'success',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
      })
      
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create task'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateTask = async (taskId, taskData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiClient.put(`/tasks/${taskId}`, taskData)
      const index = tasks.value.findIndex(task => task.id === taskId)
      if (index !== -1) {
        tasks.value[index] = response.data.data
      }
      clearSearchCache() // Clear search cache since tasks have changed
      await fetchStatistics() // Update statistics
      
      // Show success notification
      Swal.fire({
        title: 'Updated!',
        text: 'Task updated successfully.',
        icon: 'success',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
      })
      
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update task'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteTask = async (taskId) => {
    // Get task details for confirmation
    const task = tasks.value.find(t => t.id === taskId)
    if (!task) {
      throw new Error('Task not found')
    }

    // Show SweetAlert2 confirmation dialog
    const result = await Swal.fire({
      title: 'Delete Task?',
      html: `Are you sure you want to delete <strong>"${task.title}"</strong>?<br><small class="text-gray-500">This action cannot be undone.</small>`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'Cancel',
      reverseButtons: true,
      customClass: {
        confirmButton: 'btn btn-danger',
        cancelButton: 'btn btn-secondary'
      }
    })

    if (!result.isConfirmed) {
      return // User cancelled
    }

    loading.value = true
    error.value = null

    try {
      await apiClient.delete(`/tasks/${taskId}`)
      tasks.value = tasks.value.filter(task => task.id !== taskId)
      clearSearchCache() // Clear search cache since tasks have changed
      await fetchStatistics() // Update statistics
      
      // Show success notification
      Swal.fire({
        title: 'Deleted!',
        text: 'Task has been deleted successfully.',
        icon: 'success',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
      })
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete task'
      
      // Show error notification
      Swal.fire({
        title: 'Error!',
        text: error.value,
        icon: 'error',
        confirmButtonColor: '#dc2626'
      })
      
      throw err
    } finally {
      loading.value = false
    }
  }

  const toggleTaskStatus = async (taskId) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiClient.patch(`/tasks/${taskId}/toggle-status`)
      const index = tasks.value.findIndex(task => task.id === taskId)
      if (index !== -1) {
        tasks.value[index] = response.data.data
      }
      await fetchStatistics() // Update statistics
      
      // Show success notification
      const task = response.data.data
      const statusText = task.status === 'completed' ? 'completed' : 'marked as pending'
      Swal.fire({
        title: 'Status Updated!',
        text: `Task "${task.title}" ${statusText}.`,
        icon: 'success',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
      })
      
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to toggle task status'
      throw err
    } finally {
      loading.value = false
    }
  }

  const reorderTasks = async (taskOrders) => {
    loading.value = true
    error.value = null

    console.log('Reordering tasks:', taskOrders)

    try {
      await apiClient.post('/tasks/reorder', { tasks: taskOrders })
      console.log('Backend reorder successful')
      
      // Update local task orders
      taskOrders.forEach((taskId, index) => {
        const task = tasks.value.find(t => t.id === taskId)
        if (task) {
          task.order = index + 1
        }
      })
      
      // Re-sort tasks
      tasks.value.sort((a, b) => a.order - b.order)
      console.log('Local tasks reordered')
      
      // Show success notification
      Swal.fire({
        title: 'Reordered!',
        text: 'Tasks have been reordered successfully.',
        icon: 'success',
        timer: 1500,
        timerProgressBar: true,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
      })
    } catch (err) {
      console.error('Reorder failed:', err)
      error.value = err.response?.data?.message || 'Failed to reorder tasks'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchStatistics = async () => {
    try {
      const response = await apiClient.get('/tasks-statistics')
      statistics.value = response.data.data
    } catch (err) {
      console.error('Error fetching statistics:', err)
    }
  }

  const setFilter = (filterType, value) => {
    filters.value[filterType] = value
    fetchTasks() // Re-fetch with new filters
  }

  const clearFilters = () => {
    filters.value = {
      status: 'all',
      priority: 'all',
      search: '',
      sort_by: 'order',
      sort_direction: 'asc'
    }
    pagination.value.current_page = 1
    fetchTasks()
  }

  const togglePagination = (enabled = null) => {
    usePagination.value = enabled !== null ? enabled : !usePagination.value
    pagination.value.current_page = 1
    fetchTasks()
  }

  const setPage = (page) => {
    pagination.value.current_page = page
    fetchTasks()
  }

  const setPerPage = (perPage) => {
    pagination.value.per_page = perPage
    pagination.value.current_page = 1
    fetchTasks()
  }

  const nextPage = () => {
    if (pagination.value.current_page < pagination.value.last_page) {
      setPage(pagination.value.current_page + 1)
    }
  }

  const prevPage = () => {
    if (pagination.value.current_page > 1) {
      setPage(pagination.value.current_page - 1)
    }
  }

  const clearError = () => {
    error.value = null
  }

  // Initialize
  const init = async () => {
    await fetchTasks()
    await fetchStatistics()
  }

  return {
    // State
    tasks,
    loading,
    error,
    searchSuggestions,
    filters,
    pagination,
    usePagination,
    statistics,
    
    // Getters
    filteredTasks,
    pendingTasks,
    completedTasks,
    priorityColors,
    
    // Actions
    fetchTasks,
    fetchSearchSuggestions,
    clearSearchCache,
    createTask,
    updateTask,
    deleteTask,
    toggleTaskStatus,
    reorderTasks,
    fetchStatistics,
    setFilter,
    clearFilters,
    togglePagination,
    setPage,
    setPerPage,
    nextPage,
    prevPage,
    clearError,
    init
  }
})
