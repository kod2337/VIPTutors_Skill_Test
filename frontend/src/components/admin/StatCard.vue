<template>
  <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow duration-200">
    <div class="flex items-center">
      <div class="flex-shrink-0">
        <div 
          :class="[
            'w-12 h-12 rounded-lg flex items-center justify-center',
            colorClasses
          ]"
        >
          <i :class="[icon, 'text-white text-xl']"></i>
        </div>
      </div>
      <div class="ml-4 flex-1">
        <div class="flex items-center justify-between">
          <p class="text-sm font-medium text-gray-600 truncate">{{ label }}</p>
          <div v-if="trend" class="flex items-center space-x-1">
            <i 
              :class="[
                trend.direction === 'up' ? 'fas fa-arrow-up text-green-500' : 'fas fa-arrow-down text-red-500',
                'text-xs'
              ]"
            ></i>
            <span 
              :class="[
                'text-xs font-medium',
                trend.direction === 'up' ? 'text-green-600' : 'text-red-600'
              ]"
            >
              {{ trend.percentage }}%
            </span>
          </div>
        </div>
        <div class="mt-1">
          <div v-if="loading" class="animate-pulse">
            <div class="h-8 bg-gray-200 rounded w-20"></div>
          </div>
          <div v-else class="flex items-baseline">
            <p class="text-2xl font-bold text-gray-900">
              {{ formattedValue }}
            </p>
            <p v-if="suffix" class="ml-1 text-sm font-medium text-gray-500">
              {{ suffix }}
            </p>
          </div>
        </div>
        <div v-if="description" class="mt-1">
          <p class="text-xs text-gray-500">{{ description }}</p>
        </div>
      </div>
    </div>
    
    <!-- Progress bar for percentage values -->
    <div v-if="showProgress" class="mt-4">
      <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
        <span>Progress</span>
        <span>{{ progressPercentage }}%</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-1.5">
        <div 
          :class="[
            'h-1.5 rounded-full transition-all duration-500',
            progressColorClasses
          ]"
          :style="{ width: `${progressPercentage}%` }"
        ></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  icon: {
    type: String,
    required: true
  },
  label: {
    type: String,
    required: true
  },
  value: {
    type: [Number, String],
    required: true
  },
  color: {
    type: String,
    default: 'blue',
    validator: (value) => ['blue', 'green', 'red', 'yellow', 'purple', 'indigo', 'pink', 'emerald'].includes(value)
  },
  loading: {
    type: Boolean,
    default: false
  },
  suffix: {
    type: String,
    default: ''
  },
  description: {
    type: String,
    default: ''
  },
  trend: {
    type: Object,
    default: null,
    validator: (value) => {
      if (!value) return true
      return value.direction && value.percentage && ['up', 'down'].includes(value.direction)
    }
  },
  maxValue: {
    type: Number,
    default: null
  },
  showProgress: {
    type: Boolean,
    default: false
  }
})

const colorClasses = computed(() => {
  const colors = {
    blue: 'bg-blue-500',
    green: 'bg-green-500',
    red: 'bg-red-500',
    yellow: 'bg-yellow-500',
    purple: 'bg-purple-500',
    indigo: 'bg-indigo-500',
    pink: 'bg-pink-500',
    emerald: 'bg-emerald-500'
  }
  return colors[props.color] || colors.blue
})

const progressColorClasses = computed(() => {
  const colors = {
    blue: 'bg-blue-500',
    green: 'bg-green-500',
    red: 'bg-red-500',
    yellow: 'bg-yellow-500',
    purple: 'bg-purple-500',
    indigo: 'bg-indigo-500',
    pink: 'bg-pink-500',
    emerald: 'bg-emerald-500'
  }
  return colors[props.color] || colors.blue
})

const formattedValue = computed(() => {
  if (props.loading) return '...'
  
  const value = typeof props.value === 'string' ? parseFloat(props.value) || props.value : props.value
  
  if (typeof value === 'number') {
    // Format large numbers with commas
    if (value >= 1000) {
      return value.toLocaleString()
    }
    return value.toString()
  }
  
  return value
})

const progressPercentage = computed(() => {
  if (!props.showProgress || !props.maxValue) return 0
  
  const value = typeof props.value === 'string' ? parseFloat(props.value) || 0 : props.value || 0
  return Math.min(Math.round((value / props.maxValue) * 100), 100)
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
