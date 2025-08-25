<template>
  <div class="relative">
    <!-- Search Input -->
    <div class="relative">
      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
      </div>
      <input
        v-model="searchQuery"
        @input="handleSearchInput"
        @focus="showSuggestions = true"
        @blur="handleBlur"
        @keydown.down.prevent="navigateDown"
        @keydown.up.prevent="navigateUp"
        @keydown.enter.prevent="selectSuggestion"
        @keydown.escape="clearSearch"
        type="text"
        placeholder="Search tasks by title or description..."
        class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
        :class="{ 'border-blue-500 ring-2 ring-blue-500': showSuggestions && suggestions.length > 0 }"
      />
      
      <!-- Clear Button -->
      <button
        v-if="searchQuery"
        @click="clearSearch"
        class="absolute inset-y-0 right-0 pr-3 flex items-center"
      >
        <XMarkIcon class="h-5 w-5 text-gray-400 hover:text-gray-600" />
      </button>
    </div>

    <!-- Search Suggestions Dropdown -->
    <div
      v-if="showSuggestions && suggestions.length > 0"
      class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto"
    >
      <div
        v-for="(suggestion, index) in suggestions"
        :key="index"
        @mousedown.prevent="selectSuggestionByIndex(index)"
        @mouseenter="selectedIndex = index"
        class="px-4 py-3 cursor-pointer hover:bg-gray-50 transition-colors duration-150"
        :class="{ 'bg-blue-50 border-l-4 border-blue-500': selectedIndex === index }"
      >
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <div class="text-sm font-medium text-gray-900" v-html="highlightMatch(suggestion.title)"></div>
            <div v-if="suggestion.description" class="text-xs text-gray-500 mt-1 truncate" v-html="highlightMatch(suggestion.description)"></div>
          </div>
          <div class="flex items-center space-x-2 ml-3">
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                  :class="getPriorityClass(suggestion.priority)">
              {{ suggestion.priority }}
            </span>
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                  :class="getStatusClass(suggestion.status)">
              {{ suggestion.status }}
            </span>
          </div>
        </div>
      </div>
      
      <!-- No results -->
      <div v-if="searchQuery && suggestions.length === 0 && !loadingSuggestions" class="px-4 py-3 text-sm text-gray-500 text-center">
        No matching tasks found
      </div>
      
      <!-- Loading -->
      <div v-if="loadingSuggestions" class="px-4 py-3 text-sm text-gray-500 text-center">
        <div class="flex items-center justify-center">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500 mr-2"></div>
          Searching...
        </div>
      </div>
    </div>

    <!-- Recent Searches -->
    <div
      v-if="showSuggestions && !searchQuery && recentSearches.length > 0"
      class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg"
    >
      <div class="px-4 py-2 bg-gray-50 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-gray-600 uppercase tracking-wide">Recent Searches</span>
          <button @click="clearRecentSearches" class="text-xs text-gray-400 hover:text-gray-600">
            Clear all
          </button>
        </div>
      </div>
      <div
        v-for="(recent, index) in recentSearches.slice(0, 5)"
        :key="`recent-${index}`"
        @mousedown.prevent="selectRecentSearch(recent)"
        class="px-4 py-2 cursor-pointer hover:bg-gray-50 transition-colors duration-150 flex items-center"
      >
        <ClockIcon class="h-4 w-4 text-gray-400 mr-3" />
        <span class="text-sm text-gray-700">{{ recent }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { MagnifyingGlassIcon, XMarkIcon, ClockIcon } from '@heroicons/vue/24/outline'
import { useTaskStore } from '@/stores/tasks'

// Props
const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Search tasks...'
  }
})

// Emits
const emit = defineEmits(['update:modelValue', 'search'])

// Store
const taskStore = useTaskStore()

// Reactive data
const searchQuery = ref(props.modelValue)
const suggestions = ref([])
const showSuggestions = ref(false)
const selectedIndex = ref(-1)
const loadingSuggestions = ref(false)
const searchTimeout = ref(null)
const recentSearches = ref([])

// Computed
const debouncedSearch = computed(() => searchQuery.value)

// Watch for search input changes
watch(searchQuery, (newValue) => {
  emit('update:modelValue', newValue)
  
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
  
  if (newValue.trim()) {
    searchTimeout.value = setTimeout(() => {
      fetchSuggestions(newValue)
    }, 300) // 300ms debounce
  } else {
    suggestions.value = []
    emit('search', '')
  }
})

// Methods
const fetchSuggestions = async (query) => {
  if (!query.trim()) return
  
  loadingSuggestions.value = true
  try {
    await taskStore.fetchSearchSuggestions(query)
    suggestions.value = taskStore.searchSuggestions || []
  } catch (error) {
    console.error('Failed to fetch search suggestions:', error)
    suggestions.value = []
  } finally {
    loadingSuggestions.value = false
  }
}

const handleSearchInput = () => {
  selectedIndex.value = -1
}

const handleBlur = () => {
  // Delay hiding suggestions to allow clicks
  setTimeout(() => {
    showSuggestions.value = false
  }, 200)
}

const navigateDown = () => {
  if (selectedIndex.value < suggestions.value.length - 1) {
    selectedIndex.value++
  }
}

const navigateUp = () => {
  if (selectedIndex.value > 0) {
    selectedIndex.value--
  }
}

const selectSuggestion = () => {
  if (selectedIndex.value >= 0 && suggestions.value[selectedIndex.value]) {
    const suggestion = suggestions.value[selectedIndex.value]
    searchQuery.value = suggestion.title
    addToRecentSearches(suggestion.title)
    performSearch(suggestion.title)
    showSuggestions.value = false
  } else if (searchQuery.value.trim()) {
    addToRecentSearches(searchQuery.value)
    performSearch(searchQuery.value)
    showSuggestions.value = false
  }
}

const selectSuggestionByIndex = (index) => {
  selectedIndex.value = index
  selectSuggestion()
}

const selectRecentSearch = (search) => {
  searchQuery.value = search
  performSearch(search)
  showSuggestions.value = false
}

const performSearch = (query) => {
  emit('search', query.trim())
}

const clearSearch = () => {
  searchQuery.value = ''
  suggestions.value = []
  showSuggestions.value = false
  emit('search', '')
}

const addToRecentSearches = (search) => {
  if (!search.trim()) return
  
  // Remove if already exists
  const filtered = recentSearches.value.filter(item => item !== search)
  // Add to beginning
  recentSearches.value = [search, ...filtered].slice(0, 10)
  // Save to localStorage
  localStorage.setItem('taskSearchHistory', JSON.stringify(recentSearches.value))
}

const clearRecentSearches = () => {
  recentSearches.value = []
  localStorage.removeItem('taskSearchHistory')
}

const loadRecentSearches = () => {
  try {
    const saved = localStorage.getItem('taskSearchHistory')
    if (saved) {
      recentSearches.value = JSON.parse(saved)
    }
  } catch (error) {
    console.error('Failed to load recent searches:', error)
  }
}

const highlightMatch = (text) => {
  if (!searchQuery.value.trim() || !text) return text
  
  const regex = new RegExp(`(${searchQuery.value.trim()})`, 'gi')
  return text.replace(regex, '<mark class="bg-yellow-200 rounded px-1">$1</mark>')
}

const getPriorityClass = (priority) => {
  const classes = {
    low: 'bg-green-100 text-green-800',
    medium: 'bg-yellow-100 text-yellow-800',
    high: 'bg-red-100 text-red-800'
  }
  return classes[priority] || 'bg-gray-100 text-gray-800'
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

// Lifecycle
onMounted(() => {
  loadRecentSearches()
})
</script>

<style scoped>
mark {
  background-color: #fef08a;
  padding: 0 2px;
  border-radius: 2px;
}
</style>
