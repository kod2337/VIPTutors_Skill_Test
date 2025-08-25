<template>
  <div
    ref="containerRef"
    class="virtual-scrolling-container"
    :style="{ height: containerHeight + 'px', overflow: 'auto' }"
    @scroll="handleScroll"
  >
    <!-- Total height placeholder -->
    <div :style="{ height: totalHeight + 'px', position: 'relative' }">
      <!-- Visible items -->
      <div
        :style="{ 
          transform: `translateY(${offsetY}px)`,
          position: 'absolute',
          top: 0,
          left: 0,
          right: 0
        }"
      >
        <div
          v-for="item in visibleItems"
          :key="item.id"
          :style="{ height: itemHeight + 'px' }"
          class="virtual-item"
        >
          <slot :item="item" :index="item.index"></slot>
        </div>
      </div>
    </div>

    <!-- Loading skeleton for new items -->
    <div v-if="loading" class="p-4">
      <div class="animate-pulse space-y-4">
        <div v-for="i in 3" :key="i" class="flex space-x-4">
          <div class="rounded-full bg-gray-200 h-10 w-10"></div>
          <div class="flex-1 space-y-2 py-1">
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'

// Props
const props = defineProps({
  items: {
    type: Array,
    required: true
  },
  itemHeight: {
    type: Number,
    default: 80
  },
  containerHeight: {
    type: Number,
    default: 600
  },
  overscan: {
    type: Number,
    default: 5
  },
  loading: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['load-more'])

// Refs
const containerRef = ref(null)
const scrollTop = ref(0)

// Computed
const totalHeight = computed(() => props.items.length * props.itemHeight)

const visibleCount = computed(() => Math.ceil(props.containerHeight / props.itemHeight))

const startIndex = computed(() => {
  const index = Math.floor(scrollTop.value / props.itemHeight)
  return Math.max(0, index - props.overscan)
})

const endIndex = computed(() => {
  const index = startIndex.value + visibleCount.value + props.overscan * 2
  return Math.min(props.items.length - 1, index)
})

const visibleItems = computed(() => {
  const items = []
  for (let i = startIndex.value; i <= endIndex.value; i++) {
    if (props.items[i]) {
      items.push({
        ...props.items[i],
        index: i
      })
    }
  }
  return items
})

const offsetY = computed(() => startIndex.value * props.itemHeight)

// Methods
const handleScroll = (event) => {
  scrollTop.value = event.target.scrollTop
  
  // Check if we need to load more items
  const { scrollTop: currentScrollTop, scrollHeight, clientHeight } = event.target
  const scrolledPercentage = (currentScrollTop + clientHeight) / scrollHeight
  
  if (scrolledPercentage > 0.8 && !props.loading) {
    emit('load-more')
  }
}

const scrollToIndex = (index) => {
  if (containerRef.value) {
    const targetScrollTop = index * props.itemHeight
    containerRef.value.scrollTop = targetScrollTop
  }
}

const scrollToTop = () => {
  if (containerRef.value) {
    containerRef.value.scrollTop = 0
  }
}

// Watch for items changes and update scroll position if needed
watch(() => props.items.length, (newLength, oldLength) => {
  // If items were added, maintain scroll position
  if (newLength > oldLength && containerRef.value) {
    nextTick(() => {
      // Adjust scroll position to account for new items if they were prepended
      // This is mainly for real-time updates
    })
  }
})

// Expose methods to parent
defineExpose({
  scrollToIndex,
  scrollToTop
})

// Lifecycle
onMounted(() => {
  // Set initial scroll position if needed
  nextTick(() => {
    if (containerRef.value) {
      scrollTop.value = containerRef.value.scrollTop
    }
  })
})
</script>

<style scoped>
.virtual-scrolling-container {
  /* Enable hardware acceleration */
  will-change: transform;
  -webkit-overflow-scrolling: touch;
}

.virtual-item {
  /* Ensure proper positioning */
  box-sizing: border-box;
}

/* Smooth scrolling for better UX */
.virtual-scrolling-container {
  scroll-behavior: smooth;
}

/* Custom scrollbar styling */
.virtual-scrolling-container::-webkit-scrollbar {
  width: 6px;
}

.virtual-scrolling-container::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.virtual-scrolling-container::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.virtual-scrolling-container::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>
