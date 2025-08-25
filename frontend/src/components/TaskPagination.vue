<template>
  <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
    <!-- Mobile Pagination -->
    <div class="flex-1 flex justify-between sm:hidden">
      <button
        @click="$emit('prevPage')"
        :disabled="pagination.current_page <= 1"
        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Previous
      </button>
      <button
        @click="$emit('nextPage')"
        :disabled="pagination.current_page >= pagination.last_page"
        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Next
      </button>
    </div>

    <!-- Desktop Pagination -->
    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
      <div class="flex items-center space-x-4">
        <!-- Results Info -->
        <p class="text-sm text-gray-700">
          Showing
          <span class="font-medium">{{ pagination.from }}</span>
          to
          <span class="font-medium">{{ pagination.to }}</span>
          of
          <span class="font-medium">{{ pagination.total }}</span>
          results
        </p>

        <!-- Per Page Selector -->
        <div class="flex items-center space-x-2">
          <label for="per-page" class="text-sm text-gray-700">Show:</label>
          <select
            id="per-page"
            :value="pagination.per_page"
            @change="$emit('changePerPage', parseInt($event.target.value))"
            class="border border-gray-300 rounded-md text-sm px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="5">5</option>
            <option value="10">10</option>
          </select>
          <span class="text-sm text-gray-700">per page</span>
        </div>
      </div>

      <!-- Page Navigation -->
      <div class="flex items-center space-x-1">
        <!-- Previous Button -->
        <button
          @click="$emit('prevPage')"
          :disabled="pagination.current_page <= 1"
          class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span class="sr-only">Previous</span>
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <!-- Page Numbers -->
        <template v-for="page in pageNumbers" :key="page">
          <button
            v-if="page !== '...'"
            @click="$emit('goToPage', page)"
            :class="[
              'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
              page === pagination.current_page
                ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
            ]"
          >
            {{ page }}
          </button>
          <span
            v-else
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
          >
            ...
          </span>
        </template>

        <!-- Next Button -->
        <button
          @click="$emit('nextPage')"
          :disabled="pagination.current_page >= pagination.last_page"
          class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span class="sr-only">Next</span>
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  pagination: {
    type: Object,
    required: true
  }
})

defineEmits(['prevPage', 'nextPage', 'goToPage', 'changePerPage'])

const pageNumbers = computed(() => {
  const { current_page, last_page } = props.pagination
  const pages = []
  
  if (last_page <= 7) {
    // Show all pages if there are 7 or fewer
    for (let i = 1; i <= last_page; i++) {
      pages.push(i)
    }
  } else {
    // Show smart pagination with ellipsis
    if (current_page <= 4) {
      // Near the beginning
      for (let i = 1; i <= 5; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(last_page)
    } else if (current_page >= last_page - 3) {
      // Near the end
      pages.push(1)
      pages.push('...')
      for (let i = last_page - 4; i <= last_page; i++) {
        pages.push(i)
      }
    } else {
      // In the middle
      pages.push(1)
      pages.push('...')
      for (let i = current_page - 1; i <= current_page + 1; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(last_page)
    }
  }
  
  return pages
})
</script>

<style scoped>
/* Custom focus styles for better accessibility */
button:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

select:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}
</style>
