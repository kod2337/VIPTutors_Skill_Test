import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/auth'
import './assets/style.css'
import './assets/sweetalert2-theme.css'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// Initialize authentication
const authStore = useAuthStore()
authStore.initializeAuth()

app.mount('#app')
