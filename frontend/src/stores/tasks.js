import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiClient from '@/services/api'
import Swal from 'sweetalert2'

export const useTaskStore = defineStore('tasks', () => {
  // State
  const tasks = ref([])
  const loading = ref(false)
  const error = ref(null)
  const filters = ref({
    status: 'all',
    priority: 'all',
    search: ''
  })
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
      if (filters.value.status !== 'all') {
        params.append('status', filters.value.status)
      }
      if (filters.value.priority !== 'all') {
        params.append('priority', filters.value.priority)
      }
      if (filters.value.search) {
        params.append('search', filters.value.search)
      }

      const response = await apiClient.get(`/tasks?${params.toString()}`)
      tasks.value = response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch tasks'
      console.error('Error fetching tasks:', err)
    } finally {
      loading.value = false
    }
  }

  const createTask = async (taskData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiClient.post('/tasks', taskData)
      tasks.value.push(response.data.data)
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
      search: ''
    }
    fetchTasks()
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
    filters,
    statistics,
    
    // Getters
    filteredTasks,
    pendingTasks,
    completedTasks,
    priorityColors,
    
    // Actions
    fetchTasks,
    createTask,
    updateTask,
    deleteTask,
    toggleTaskStatus,
    reorderTasks,
    fetchStatistics,
    setFilter,
    clearFilters,
    clearError,
    init
  }
})
