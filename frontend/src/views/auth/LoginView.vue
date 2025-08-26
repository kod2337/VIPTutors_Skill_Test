<template>
  <div class="min-h-screen flex">
    <!-- Left Side - Brand & Welcome -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 relative overflow-hidden">
      <!-- Professional Background Pattern -->
      <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-indigo-900/40"></div>
      <div class="absolute inset-0 bg-blue-600/10 bg-[radial-gradient(circle_at_20%_50%,rgba(120,119,198,0.3),transparent_50%)] opacity-70"></div>
      <div class="absolute inset-0 bg-indigo-500/5 bg-[radial-gradient(circle_at_80%_20%,rgba(59,130,246,0.3),transparent_50%)]"></div>
      
      <!-- Geometric Pattern Background -->
      <div class="absolute inset-0 opacity-10">
        <div class="absolute top-20 left-20 w-32 h-32 border-2 border-white transform rotate-45"></div>
        <div class="absolute top-40 right-32 w-24 h-24 border-2 border-white transform rotate-12"></div>
        <div class="absolute bottom-32 left-32 w-40 h-40 border border-white transform -rotate-12"></div>
        <div class="absolute bottom-20 right-20 w-28 h-28 border-2 border-white transform rotate-45"></div>
      </div>
      
      <!-- Content -->
      <div class="relative z-10 flex flex-col justify-center px-12 text-white">
        <!-- Logo/Icon -->
        <div class="mb-8">
          <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center mb-6">
            <CheckCircleIcon class="h-8 w-8 text-blue-600" />
          </div>
          <h1 class="text-4xl font-bold mb-4">
            Welcome Back to<br />Kayser's Task Management
          </h1>
        </div>
        
        <!-- Description -->
        <div class="mb-12">
          <p class="text-lg text-blue-100 leading-relaxed max-w-md">
            Streamline your task management workflow. Get highly productive through smart organization and collaboration tools designed for modern teams.
          </p>
        </div>
        
        <!-- Footer -->
        <div class="text-sm text-blue-200">
          © 2025 Kayser Bompat. All rights reserved.
        </div>
      </div>
    </div>

    <!-- Right Side - Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center px-8 sm:px-12 lg:px-16 bg-gray-50">
      <div class="w-full max-w-md">
        <!-- Mobile Logo -->
        <div class="lg:hidden text-center mb-8">
          <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mx-auto mb-4">
            <CheckCircleIcon class="h-8 w-8 text-white" />
          </div>
          <h1 class="text-2xl font-bold text-gray-900">Kayser's Task Management</h1>
        </div>

        <!-- Form Header -->
        <div class="mb-8">
          <h2 class="text-3xl font-bold text-gray-900 mb-2">Welcome Back!</h2>
          <p class="text-gray-600">
            Don't have an account? 
            <router-link 
              to="/register" 
              class="font-semibold text-blue-600 hover:text-blue-700 underline decoration-2 underline-offset-2"
            >
              Create a new account now
            </router-link>
            , it's FREE! Takes less than a minute.
          </p>
        </div>

        <!-- Login Form -->
        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- Email Field -->
          <div>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="w-full px-4 py-4 bg-white border-b-2 border-gray-300 focus:border-blue-600 focus:outline-none transition-colors duration-200 text-gray-900 placeholder-gray-500"
              :class="{ 'border-red-500': errors.email }"
              placeholder="Enter your email address"
            />
            <div v-if="errors.email" class="text-sm text-red-600 mt-2 flex items-center gap-2">
              <ExclamationCircleIcon class="h-4 w-4" />
              {{ errors.email }}
            </div>
          </div>

          <!-- Password Field -->
          <div>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                class="w-full px-4 py-4 pr-12 bg-white border-b-2 border-gray-300 focus:border-blue-600 focus:outline-none transition-colors duration-200 text-gray-900 placeholder-gray-500"
                :class="{ 'border-red-500': errors.password }"
                placeholder="Password"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors duration-200"
              >
                <EyeIcon v-if="!showPassword" class="h-5 w-5" />
                <EyeSlashIcon v-else class="h-5 w-5" />
              </button>
            </div>
            <div v-if="errors.password" class="text-sm text-red-600 mt-2 flex items-center gap-2">
              <ExclamationCircleIcon class="h-4 w-4" />
              {{ errors.password }}
            </div>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full bg-gray-900 hover:bg-black text-white font-semibold py-4 px-6 rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <div class="flex items-center justify-center gap-3">
              <div v-if="isLoading" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
              <span>{{ isLoading ? 'Signing in...' : 'Login Now' }}</span>
            </div>
          </button>


          <!-- Error Message -->
          <div v-if="authError" class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-start gap-3">
              <ExclamationTriangleIcon class="h-5 w-5 text-red-500 mt-0.5 flex-shrink-0" />
              <div>
                <h3 class="text-sm font-semibold text-red-800">
                  Authentication failed
                </h3>
                <p class="mt-1 text-sm text-red-700">
                  {{ authError }}
                </p>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import type { LoginCredentials } from '@/types/auth'
import {
  CheckCircleIcon,
  EnvelopeIcon,
  LockClosedIcon,
  EyeIcon,
  EyeSlashIcon,
  ExclamationCircleIcon,
  ExclamationTriangleIcon,
  ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()

// Form data
const form = reactive<LoginCredentials & { remember?: boolean }>({
  email: '',
  password: '',
  remember: false
})

// Form validation errors
const errors = ref<Record<string, string>>({})
const authError = ref<string | null>(null)
const isLoading = ref(false)
const showPassword = ref(false)

// Clear errors when form changes
const clearErrors = () => {
  errors.value = {}
  authError.value = null
}

// Validate form
const validateForm = (): boolean => {
  const newErrors: Record<string, string> = {}

  if (!form.email) {
    newErrors.email = 'Email is required'
  } else if (!/\S+@\S+\.\S+/.test(form.email)) {
    newErrors.email = 'Email is invalid'
  }

  if (!form.password) {
    newErrors.password = 'Password is required'
  }

  errors.value = newErrors
  return Object.keys(newErrors).length === 0
}

// Handle login
const handleLogin = async () => {
  clearErrors()
  
  if (!validateForm()) {
    return
  }

  isLoading.value = true

  try {
    const result = await authStore.login({
      email: form.email,
      password: form.password
    })
    
    if (result.success) {
      // Redirect to dashboard or intended route
      const redirect = router.currentRoute.value.query.redirect as string
      router.push(redirect || '/dashboard')
    } else {
      authError.value = result.error || 'Login failed'
    }
  } catch (error) {
    authError.value = 'An unexpected error occurred'
  } finally {
    isLoading.value = false
  }
}
</script>
