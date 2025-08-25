<template>
  <div id="app" class="min-h-screen bg-gray-50 antialiased">
    <router-view class="fade-in" />
  </div>
</template>

<script setup lang="ts">
// Task Management System - Main App Component
</script>

<style>
#app {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
}

/* Smooth transitions for all elements */
* {
  transition: all 150ms ease-in-out;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Focus visible for accessibility */
.focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}
</style>
