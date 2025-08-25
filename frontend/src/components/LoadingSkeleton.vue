<template>
  <div class="loading-skeleton" :class="containerClass">
    <!-- Task List Skeleton -->
    <div v-if="type === 'task-list'" class="space-y-4">
      <div
        v-for="n in count"
        :key="n"
        class="animate-pulse bg-white border border-gray-200 rounded-lg p-4"
      >
        <div class="flex items-start space-x-4">
          <!-- Drag handle skeleton -->
          <div class="w-6 h-6 bg-gray-200 rounded"></div>
          
          <!-- Priority indicator skeleton -->
          <div class="w-3 h-3 bg-gray-200 rounded-full mt-2"></div>
          
          <!-- Content skeleton -->
          <div class="flex-1 space-y-3">
            <!-- Title skeleton -->
            <div class="h-4 bg-gray-200 rounded" :style="{ width: getRandomWidth() }"></div>
            
            <!-- Description skeleton -->
            <div class="space-y-2">
              <div class="h-3 bg-gray-200 rounded" :style="{ width: getRandomWidth() }"></div>
              <div class="h-3 bg-gray-200 rounded" :style="{ width: getRandomWidth(0.6, 0.8) }"></div>
            </div>
            
            <!-- Meta info skeleton -->
            <div class="flex items-center space-x-4">
              <div class="h-3 bg-gray-200 rounded w-16"></div>
              <div class="h-3 bg-gray-200 rounded w-20"></div>
              <div class="h-3 bg-gray-200 rounded w-12"></div>
            </div>
          </div>
          
          <!-- Actions skeleton -->
          <div class="flex space-x-2">
            <div class="w-8 h-8 bg-gray-200 rounded"></div>
            <div class="w-8 h-8 bg-gray-200 rounded"></div>
            <div class="w-8 h-8 bg-gray-200 rounded"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Search Results Skeleton -->
    <div v-else-if="type === 'search-results'" class="space-y-3">
      <div
        v-for="n in count"
        :key="n"
        class="animate-pulse bg-white border border-gray-200 rounded-lg p-3"
      >
        <div class="flex items-center justify-between">
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-200 rounded" :style="{ width: getRandomWidth() }"></div>
            <div class="h-3 bg-gray-200 rounded" :style="{ width: getRandomWidth(0.4, 0.7) }"></div>
          </div>
          <div class="flex space-x-2 ml-4">
            <div class="w-12 h-5 bg-gray-200 rounded-full"></div>
            <div class="w-16 h-5 bg-gray-200 rounded-full"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Options Skeleton -->
    <div v-else-if="type === 'filter-options'" class="space-y-4">
      <div class="animate-pulse">
        <!-- Header skeleton -->
        <div class="flex items-center justify-between mb-4">
          <div class="h-6 bg-gray-200 rounded w-32"></div>
          <div class="h-5 bg-gray-200 rounded w-20"></div>
        </div>
        
        <!-- Quick filters skeleton -->
        <div class="flex flex-wrap gap-2 mb-6">
          <div v-for="n in 4" :key="n" class="h-8 bg-gray-200 rounded-md" :style="{ width: getRandomWidth(80, 120) + 'px' }"></div>
        </div>
        
        <!-- Filter sections skeleton -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div v-for="section in 2" :key="section" class="space-y-3">
            <div class="h-4 bg-gray-200 rounded w-24"></div>
            <div class="space-y-2">
              <div v-for="n in 3" :key="n" class="flex items-center space-x-3">
                <div class="w-4 h-4 bg-gray-200 rounded"></div>
                <div class="h-3 bg-gray-200 rounded flex-1"></div>
                <div class="h-3 bg-gray-200 rounded w-6"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Cards Skeleton -->
    <div v-else-if="type === 'stats-cards'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div
        v-for="n in count"
        :key="n"
        class="animate-pulse bg-white border border-gray-200 rounded-lg p-6"
      >
        <div class="flex items-center">
          <div class="w-12 h-12 bg-gray-200 rounded-lg"></div>
          <div class="ml-4 flex-1">
            <div class="h-4 bg-gray-200 rounded w-20 mb-2"></div>
            <div class="h-6 bg-gray-200 rounded w-16"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination Skeleton -->
    <div v-else-if="type === 'pagination'" class="animate-pulse">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <div class="h-4 bg-gray-200 rounded w-12"></div>
          <div class="h-8 bg-gray-200 rounded w-16"></div>
          <div class="h-4 bg-gray-200 rounded w-16"></div>
        </div>
        <div class="flex items-center space-x-1">
          <div class="w-8 h-8 bg-gray-200 rounded"></div>
          <div class="w-8 h-8 bg-gray-200 rounded"></div>
          <div class="w-8 h-8 bg-gray-200 rounded"></div>
          <div class="w-8 h-8 bg-gray-200 rounded"></div>
          <div class="w-8 h-8 bg-gray-200 rounded"></div>
        </div>
        <div class="h-4 bg-gray-200 rounded w-32"></div>
      </div>
    </div>

    <!-- Generic Content Skeleton -->
    <div v-else class="animate-pulse space-y-4">
      <div
        v-for="n in count"
        :key="n"
        class="space-y-3"
      >
        <div class="h-4 bg-gray-200 rounded" :style="{ width: getRandomWidth() }"></div>
        <div class="h-4 bg-gray-200 rounded" :style="{ width: getRandomWidth(0.6, 0.9) }"></div>
        <div class="h-4 bg-gray-200 rounded" :style="{ width: getRandomWidth(0.4, 0.7) }"></div>
      </div>
    </div>

    <!-- Shimmer effect overlay -->
    <div v-if="shimmer" class="shimmer-overlay"></div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

// Props
const props = defineProps({
  type: {
    type: String,
    default: 'generic',
    validator: (value) => [
      'task-list',
      'search-results', 
      'filter-options',
      'stats-cards',
      'pagination',
      'generic'
    ].includes(value)
  },
  count: {
    type: Number,
    default: 3
  },
  shimmer: {
    type: Boolean,
    default: true
  },
  containerClass: {
    type: String,
    default: ''
  }
})

// Methods
const getRandomWidth = (min = 0.7, max = 1) => {
  const percentage = Math.random() * (max - min) + min
  return `${Math.round(percentage * 100)}%`
}
</script>

<style scoped>
.loading-skeleton {
  position: relative;
  overflow: hidden;
}

.shimmer-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(
    90deg,
    transparent 0%,
    rgba(255, 255, 255, 0.4) 50%,
    transparent 100%
  );
  animation: shimmer 2s infinite;
  pointer-events: none;
}

@keyframes shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}

/* Optimize animations for performance */
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

/* Reduce animation for users who prefer reduced motion */
@media (prefers-reduced-motion: reduce) {
  .animate-pulse,
  .shimmer-overlay {
    animation: none;
  }
  
  .animate-pulse {
    opacity: 0.7;
  }
}
</style>
