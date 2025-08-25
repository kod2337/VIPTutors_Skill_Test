<template>
  <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-medium text-gray-900">Advanced Filters</h3>
      <div class="flex items-center space-x-2">
        <button
          @click="clearAllFilters"
          class="text-sm text-gray-500 hover:text-gray-700 transition-colors duration-200"
        >
          Clear All
        </button>
        <button
          @click="toggleCollapsed"
          class="p-1 rounded-md hover:bg-gray-100 transition-colors duration-200"
        >
          <ChevronUpIcon v-if="!collapsed" class="h-5 w-5 text-gray-500" />
          <ChevronDownIcon v-else class="h-5 w-5 text-gray-500" />
        </button>
      </div>
    </div>

    <!-- Filter Content -->
    <div v-show="!collapsed" class="space-y-6">
      <!-- Quick Filter Presets -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-3">Quick Filters</label>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
          <button
            v-for="preset in filterPresets"
            :key="preset.id"
            @click="applyPreset(preset)"
            class="flex flex-col items-center justify-center p-4 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 min-h-[80px]"
            :class="{ 'bg-blue-50 border-blue-500 text-blue-700 ring-2 ring-blue-500': isPresetActive(preset) }"
          >
            <component :is="preset.icon" class="h-6 w-6 mb-2 flex-shrink-0" />
            <span class="text-center leading-tight">{{ preset.label }}</span>
            <span v-if="preset.count !== undefined" class="mt-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
              {{ preset.count }}
            </span>
          </button>
        </div>
      </div>

      <!-- Active Filters Summary -->
      <div v-if="hasActiveFilters" class="pt-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <span class="text-sm font-medium text-gray-700">Active Filters:</span>
          <button
            @click="clearAllFilters"
            class="text-sm text-red-600 hover:text-red-700 transition-colors duration-200"
          >
            Clear All
          </button>
        </div>
        <div class="mt-2 flex flex-wrap gap-2">
          <span
            v-for="filter in activeFilters"
            :key="filter.key"
            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
          >
            {{ filter.label }}
            <button
              @click="removeFilter(filter.key)"
              class="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full text-blue-400 hover:text-blue-600 hover:bg-blue-200"
            >
              <XMarkIcon class="h-3 w-3" />
            </button>
          </span>
        </div>
      </div>

      <!-- Save Filter Preset -->
      <div v-if="hasCustomFilters" class="pt-4 border-t border-gray-200">
        <div class="flex items-center space-x-3">
          <input
            v-model="newPresetName"
            @keydown.enter="saveFilterPreset"
            type="text"
            placeholder="Save current filters as preset..."
            class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          />
          <button
            @click="saveFilterPreset"
            :disabled="!newPresetName.trim()"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors duration-200"
          >
            Save Preset
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { 
  ChevronUpIcon, 
  ChevronDownIcon, 
  XMarkIcon,
  FireIcon,
  ClockIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  CalendarIcon,
  CheckIcon,
  BoltIcon,
  ArchiveBoxIcon,
  RectangleStackIcon
} from '@heroicons/vue/24/outline'
import { useTaskStore } from '@/stores/tasks'

// Store
const taskStore = useTaskStore()

// Local state
const collapsed = ref(false)
const newPresetName = ref('')
const customPresets = ref([])

// Status and Priority options with counts (based on database schema)
const statusOptions = ref([
  { value: 'pending', label: 'Pending', count: 0 },
  { value: 'completed', label: 'Completed', count: 0 }
])

const priorityOptions = ref([
  { value: 'high', label: 'High Priority', count: 0, colorClass: 'bg-red-500' },
  { value: 'medium', label: 'Medium Priority', count: 0, colorClass: 'bg-yellow-500' },
  { value: 'low', label: 'Low Priority', count: 0, colorClass: 'bg-green-500' }
])

// Built-in filter presets based on our database schema
const builtInPresets = computed(() => [
  {
    id: 'all-tasks',
    label: 'All Tasks',
    icon: RectangleStackIcon,
    filters: {
      status: ['pending', 'completed'],
      priority: ['low', 'medium', 'high']
    }
  },
  {
    id: 'pending-tasks',
    label: 'Pending Tasks',
    icon: ClockIcon,
    filters: {
      status: ['pending']
    }
  },
  {
    id: 'completed-tasks',
    label: 'Completed',
    icon: CheckCircleIcon,
    filters: {
      status: ['completed']
    }
  },
  {
    id: 'high-priority',
    label: 'High Priority',
    icon: FireIcon,
    filters: {
      priority: ['high']
    }
  },
  {
    id: 'medium-priority',
    label: 'Medium Priority',
    icon: ExclamationTriangleIcon,
    filters: {
      priority: ['medium']
    }
  },
  {
    id: 'low-priority',
    label: 'Low Priority',
    icon: CheckIcon,
    filters: {
      priority: ['low']
    }
  },
  {
    id: 'urgent-pending',
    label: 'Urgent Pending',
    icon: BoltIcon,
    filters: {
      status: ['pending'],
      priority: ['high']
    }
  },
  {
    id: 'recent-tasks',
    label: 'Recent Tasks',
    icon: CalendarIcon,
    filters: {
      created_from: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0], // Last 7 days
      sort_by: 'created_at',
      sort_direction: 'desc'
    }
  },
  {
    id: 'oldest-pending',
    label: 'Oldest Pending',
    icon: ArchiveBoxIcon,
    filters: {
      status: ['pending'],
      sort_by: 'created_at',
      sort_direction: 'asc'
    }
  }
])

const filterPresets = computed(() => [...builtInPresets.value, ...customPresets.value])

// Computed properties
const hasActiveFilters = computed(() => {
  // Check if any preset is currently active (meaning filters are applied)
  return isAnyPresetActive.value
})

const hasCustomFilters = computed(() => {
  return hasActiveFilters.value && !isAnyPresetActive.value
})

const isAnyPresetActive = computed(() => {
  return filterPresets.value.some(preset => isPresetActive(preset))
})

const activeFilters = computed(() => {
  const filters = []
  
  // Get currently applied filters from the task store
  const currentFilters = taskStore.filters
  
  if (currentFilters.status !== 'all') {
    filters.push({
      key: 'status',
      label: `Status: ${currentFilters.status}`
    })
  }
  
  if (currentFilters.priority !== 'all') {
    filters.push({
      key: 'priority',
      label: `Priority: ${currentFilters.priority}`
    })
  }
  
  if (currentFilters.search) {
    filters.push({
      key: 'search',
      label: `Search: "${currentFilters.search}"`
    })
  }
  
  if (currentFilters.sort_by !== 'order') {
    filters.push({
      key: 'sort',
      label: `Sort: ${currentFilters.sort_by} (${currentFilters.sort_direction})`
    })
  }
  
  return filters
})

// Methods
const toggleCollapsed = () => {
  collapsed.value = !collapsed.value
  localStorage.setItem('filtersCollapsed', JSON.stringify(collapsed.value))
}

const applyPreset = (preset) => {
  // Apply preset filters to the task store
  if (preset.filters.status) {
    taskStore.setFilter('status', preset.filters.status.length === 2 ? 'all' : preset.filters.status[0])
  } else {
    taskStore.setFilter('status', 'all')
  }
  
  if (preset.filters.priority) {
    taskStore.setFilter('priority', preset.filters.priority.length === 3 ? 'all' : preset.filters.priority.join(','))
  } else {
    taskStore.setFilter('priority', 'all')
  }
  
  if (preset.filters.created_from) {
    taskStore.setFilter('date_from', preset.filters.created_from)
  }
  
  if (preset.filters.created_to) {
    taskStore.setFilter('date_to', preset.filters.created_to)
  }
  
  if (preset.filters.sort_by) {
    taskStore.setFilter('sort_by', preset.filters.sort_by)
  }
  
  if (preset.filters.sort_direction) {
    taskStore.setFilter('sort_direction', preset.filters.sort_direction)
  }
  
  // Save current filter state
  saveFiltersToStorage()
}

const isPresetActive = (preset) => {
  // Check if current task store filters match the preset
  const currentFilters = taskStore.filters
  
  // Check status filter
  if (preset.filters.status) {
    const expectedStatus = preset.filters.status.length === 2 ? 'all' : preset.filters.status[0]
    if (currentFilters.status !== expectedStatus) return false
  }
  
  // Check priority filter  
  if (preset.filters.priority) {
    const expectedPriority = preset.filters.priority.length === 3 ? 'all' : preset.filters.priority.join(',')
    if (currentFilters.priority !== expectedPriority) return false
  }
  
  // Check sort options
  if (preset.filters.sort_by && currentFilters.sort_by !== preset.filters.sort_by) return false
  if (preset.filters.sort_direction && currentFilters.sort_direction !== preset.filters.sort_direction) return false
  
  return true
}

const clearAllFilters = () => {
  taskStore.clearFilters()
  saveFiltersToStorage()
}

const removeFilter = (filterKey) => {
  switch (filterKey) {
    case 'status':
      taskStore.setFilter('status', 'all')
      break
    case 'priority':
      taskStore.setFilter('priority', 'all')
      break
    case 'search':
      taskStore.setFilter('search', '')
      break
    case 'sort':
      taskStore.setFilter('sort_by', 'order')
      taskStore.setFilter('sort_direction', 'asc')
      break
  }
  saveFiltersToStorage()
}

const saveFilterPreset = () => {
  if (!newPresetName.value.trim()) return
  
  const currentFilters = taskStore.filters
  const newPreset = {
    id: `custom_${Date.now()}`,
    label: newPresetName.value.trim(),
    icon: ClockIcon,
    filters: {
      status: currentFilters.status === 'all' ? ['pending', 'completed'] : [currentFilters.status],
      priority: currentFilters.priority === 'all' ? ['low', 'medium', 'high'] : currentFilters.priority.split(','),
      sort_by: currentFilters.sort_by,
      sort_direction: currentFilters.sort_direction
    }
  }
  
  customPresets.value.push(newPreset)
  localStorage.setItem('customFilterPresets', JSON.stringify(customPresets.value))
  newPresetName.value = ''
}

const saveFiltersToStorage = () => {
  const filtersState = {
    taskStoreFilters: taskStore.filters
  }
  localStorage.setItem('taskFiltersState', JSON.stringify(filtersState))
}

const loadFiltersFromStorage = () => {
  try {
    // Load collapsed state
    const collapsedState = localStorage.getItem('filtersCollapsed')
    if (collapsedState) {
      collapsed.value = JSON.parse(collapsedState)
    }
    
    // Load custom presets
    const savedPresets = localStorage.getItem('customFilterPresets')
    if (savedPresets) {
      customPresets.value = JSON.parse(savedPresets)
    }
  } catch (error) {
    console.error('Failed to load filters from storage:', error)
  }
}

const updateFilterCounts = () => {
  // Update counts based on current tasks and statistics from the store
  const tasks = taskStore.tasks
  const stats = taskStore.statistics
  
  // Update status counts from store statistics
  statusOptions.value.forEach(option => {
    if (option.value === 'pending') {
      option.count = stats.pending || tasks.filter(task => task.status === 'pending').length
    } else if (option.value === 'completed') {
      option.count = stats.completed || tasks.filter(task => task.status === 'completed').length
    }
  })
  
  // Update priority counts from store statistics
  priorityOptions.value.forEach(option => {
    if (option.value === 'high') {
      option.count = stats.high_priority || tasks.filter(task => task.priority === 'high').length
    } else if (option.value === 'medium') {
      option.count = stats.medium_priority || tasks.filter(task => task.priority === 'medium').length
    } else if (option.value === 'low') {
      option.count = stats.low_priority || tasks.filter(task => task.priority === 'low').length
    }
  })
  
  // Update preset counts based on actual data
  builtInPresets.value.forEach(preset => {
    switch (preset.id) {
      case 'all-tasks':
        preset.count = stats.total || tasks.length
        break
      case 'pending-tasks':
        preset.count = stats.pending || tasks.filter(task => task.status === 'pending').length
        break
      case 'completed-tasks':
        preset.count = stats.completed || tasks.filter(task => task.status === 'completed').length
        break
      case 'high-priority':
        preset.count = stats.high_priority || tasks.filter(task => task.priority === 'high').length
        break
      case 'medium-priority':
        preset.count = stats.medium_priority || tasks.filter(task => task.priority === 'medium').length
        break
      case 'low-priority':
        preset.count = stats.low_priority || tasks.filter(task => task.priority === 'low').length
        break
      case 'urgent-pending':
        preset.count = tasks.filter(task => task.status === 'pending' && task.priority === 'high').length
        break
      case 'recent-tasks':
        const sevenDaysAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000)
        preset.count = tasks.filter(task => new Date(task.created_at) >= sevenDaysAgo).length
        break
      case 'oldest-pending':
        preset.count = tasks.filter(task => task.status === 'pending').length
        break
    }
  })
}

// Watch for changes in tasks and statistics to update counts
watch(() => taskStore.tasks, updateFilterCounts, { deep: true })
watch(() => taskStore.statistics, updateFilterCounts, { deep: true })

// Lifecycle
onMounted(() => {
  loadFiltersFromStorage()
  
  // Ensure statistics are loaded
  if (taskStore.statistics.total === 0) {
    taskStore.fetchStatistics()
  }
  
  updateFilterCounts()
})
</script>
