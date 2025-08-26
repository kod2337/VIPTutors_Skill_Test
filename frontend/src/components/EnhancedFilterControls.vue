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
            class="flex flex-col items-center justify-center p-4 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 min-h-[80px] cursor-pointer"
            :class="{ 
              'bg-blue-50 border-blue-500 text-blue-700 ring-2 ring-blue-500': isPresetActive(preset),
              'hover:bg-red-50 hover:border-red-400': isPresetActive(preset)
            }"
            :title="isPresetActive(preset) ? 'Click to remove filter' : 'Click to apply filter'"
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
const builtInPresets = ref([
  {
    id: 'all-tasks',
    label: 'All Tasks',
    icon: RectangleStackIcon,
    type: 'basic',
    count: 0,
    filters: {
      status: ['pending', 'completed'],
      priority: ['low', 'medium', 'high']
    }
  },
  {
    id: 'pending-tasks',
    label: 'Pending Tasks',
    icon: ClockIcon,
    type: 'basic',
    count: 0,
    filters: {
      status: ['pending']
    }
  },
  {
    id: 'completed-tasks',
    label: 'Completed',
    icon: CheckCircleIcon,
    type: 'basic',
    count: 0,
    filters: {
      status: ['completed']
    }
  },
  {
    id: 'high-priority',
    label: 'High Priority',
    icon: FireIcon,
    type: 'basic',
    count: 0,
    filters: {
      priority: ['high']
    }
  },
  {
    id: 'medium-priority',
    label: 'Medium Priority',
    icon: ExclamationTriangleIcon,
    type: 'basic',
    count: 0,
    filters: {
      priority: ['medium']
    }
  },
  {
    id: 'low-priority',
    label: 'Low Priority',
    icon: CheckIcon,
    type: 'basic',
    count: 0,
    filters: {
      priority: ['low']
    }
  },
  {
    id: 'urgent-pending',
    label: 'Urgent Pending',
    icon: BoltIcon,
    type: 'query',
    count: 0,
    filters: {
      status: ['pending'],
      priority: ['high']
    }
  },
  {
    id: 'oldest-pending',
    label: 'Oldest Pending',
    icon: ArchiveBoxIcon,
    type: 'query',
    count: 0,
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
  const currentFilters = taskStore.filters
  
  // Check if any filters are different from default state
  return currentFilters.status !== 'all' || 
         currentFilters.priority !== 'all' || 
         currentFilters.search !== '' || 
         currentFilters.sort_by !== 'order' || 
         currentFilters.sort_direction !== 'asc'
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
  // For basic filters (status/priority), only allow one at a time
  if (preset.type === 'basic') {
    // Check if preset is currently active, if so toggle it off
    if (isPresetActive(preset)) {
      // Clear filters to "toggle off" the preset
      clearAllFilters()
      return
    }
    
    // For basic filters, clear existing and apply only this one
    taskStore.clearFilters()
    
    // Apply this preset's filters
    if (preset.filters.status) {
      taskStore.setFilter('status', preset.filters.status.length === 2 ? 'all' : preset.filters.status[0])
    }
    
    if (preset.filters.priority) {
      taskStore.setFilter('priority', preset.filters.priority.length === 3 ? 'all' : preset.filters.priority[0])
    }
  } 
  // For query filters (compound filters), apply directly and override everything
  else if (preset.type === 'query') {
    // Check if preset is currently active, if so toggle it off
    if (isPresetActive(preset)) {
      // Clear all filters to "toggle off" the query
      clearAllFilters()
      return
    }
    
    // Clear existing filters first
    taskStore.clearFilters()
    
    // Apply all filters from this query preset
    if (preset.filters.status) {
      taskStore.setFilter('status', preset.filters.status.length === 2 ? 'all' : preset.filters.status[0])
    }
    
    if (preset.filters.priority) {
      taskStore.setFilter('priority', preset.filters.priority.length === 3 ? 'all' : preset.filters.priority[0])
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
  }
  
  // Save current filter state
  saveFiltersToStorage()
}

const isPresetActive = (preset) => {
  const currentFilters = taskStore.filters
  
  // If filters are in default state, only "All Tasks" preset should be active
  const isDefaultState = currentFilters.status === 'all' && 
                         currentFilters.priority === 'all' && 
                         currentFilters.search === '' && 
                         currentFilters.sort_by === 'order' && 
                         currentFilters.sort_direction === 'asc'
  
  if (isDefaultState) {
    return preset.id === 'all-tasks'
  }
  
  // For basic filters, check exact match AND ensure no other conditions are set
  if (preset.type === 'basic') {
    // Check if this is a status-only basic filter
    if (preset.filters.status && !preset.filters.priority) {
      // Must match status exactly, priority must be 'all', and no special sorting/dates
      return currentFilters.status === preset.filters.status[0] &&
             currentFilters.priority === 'all' &&
             currentFilters.sort_by === 'order' &&
             currentFilters.sort_direction === 'asc' &&
             !currentFilters.date_from &&
             !currentFilters.date_to
    }
    
    // Check if this is a priority-only basic filter
    if (preset.filters.priority && !preset.filters.status) {
      // Must match priority exactly, status must be 'all', and no special sorting/dates
      return currentFilters.priority === preset.filters.priority[0] &&
             currentFilters.status === 'all' &&
             currentFilters.sort_by === 'order' &&
             currentFilters.sort_direction === 'asc' &&
             !currentFilters.date_from &&
             !currentFilters.date_to
    }
    
    // Check if this is the "All Tasks" filter
    if (preset.filters.status && preset.filters.priority) {
      return currentFilters.status === 'all' &&
             currentFilters.priority === 'all' &&
             currentFilters.sort_by === 'order' &&
             currentFilters.sort_direction === 'asc' &&
             !currentFilters.date_from &&
             !currentFilters.date_to
    }
    
    return false
  }
  
  // For query filters, check if ALL filter conditions match exactly
  if (preset.type === 'query') {
    let matches = true
    
    // Check status filter
    if (preset.filters.status) {
      if (preset.filters.status.length === 1) {
        if (currentFilters.status !== preset.filters.status[0]) matches = false
      } else if (preset.filters.status.length === 2) {
        if (currentFilters.status !== 'all') matches = false
      }
    } else {
      // If preset doesn't specify status, current status should be 'all'
      if (currentFilters.status !== 'all') matches = false
    }
    
    // Check priority filter  
    if (preset.filters.priority) {
      if (preset.filters.priority.length === 1) {
        if (currentFilters.priority !== preset.filters.priority[0]) matches = false
      } else if (preset.filters.priority.length === 3) {
        if (currentFilters.priority !== 'all') matches = false
      }
    } else {
      // If preset doesn't specify priority, current priority should be 'all'
      if (currentFilters.priority !== 'all') matches = false
    }
    
    // Check sort options
    if (preset.filters.sort_by) {
      if (currentFilters.sort_by !== preset.filters.sort_by) matches = false
    } else {
      // If preset doesn't specify sort, should be default
      if (currentFilters.sort_by !== 'order') matches = false
    }
    
    if (preset.filters.sort_direction) {
      if (currentFilters.sort_direction !== preset.filters.sort_direction) matches = false
    } else {
      // If preset doesn't specify sort direction, should be default
      if (currentFilters.sort_direction !== 'asc') matches = false
    }
    
    // Check date filters
    if (preset.filters.created_from) {
      if (currentFilters.date_from !== preset.filters.created_from) matches = false
    } else {
      // If preset doesn't specify date_from, current should be empty
      if (currentFilters.date_from) matches = false
    }
    
    if (preset.filters.created_to) {
      if (currentFilters.date_to !== preset.filters.created_to) matches = false
    } else {
      // If preset doesn't specify date_to, current should be empty
      if (currentFilters.date_to) matches = false
    }
    
    return matches
  }
  
  return false
}

const clearAllFilters = () => {
  taskStore.clearFilters()
  
  // Force reactivity update by clearing all custom presets if needed
  // and ensuring the component re-evaluates all computed properties
  saveFiltersToStorage()
  
  // Ensure the component updates immediately
  updateFilterCounts()
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
  const tasks = taskStore.tasks || []
  const stats = taskStore.statistics || {}
  
  // Don't update if no tasks are loaded yet
  if (!Array.isArray(tasks)) {
    return
  }
  
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
      case 'oldest-pending':
        preset.count = tasks.filter(task => task.status === 'pending').length
        break
    }
  })
}

// Watch for changes in tasks and statistics to update counts
watch(() => taskStore.tasks, (newTasks, oldTasks) => {
  // Update counts whenever tasks change, including initial load
  updateFilterCounts()
}, { deep: true, immediate: true })

watch(() => taskStore.statistics, (newStats, oldStats) => {
  // Update counts when statistics change
  updateFilterCounts()
}, { deep: true, immediate: true })

watch(() => taskStore.filters, () => {
  // Force reactivity when filters change
  updateFilterCounts()
}, { deep: true })

// Lifecycle
onMounted(async () => {
  loadFiltersFromStorage()
  
  // Update filter counts with whatever data is already available
  updateFilterCounts()
  
  // If no tasks are loaded yet, the watchers will handle updating counts
  // when tasks become available from the parent component's taskStore.init()
})
</script>
