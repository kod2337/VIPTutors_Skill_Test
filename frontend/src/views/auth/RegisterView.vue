<template>
  <div class="min-h-screen flex">
    <!-- Left Side - Brand & Welcome -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-purple-600 via-purple-700 to-blue-800 relative overflow-hidden">
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
            <UserPlusIcon class="h-8 w-8 text-purple-600" />
          </div>
          <h1 class="text-4xl font-bold mb-4">
            Join<br />VIPTutors! 
          </h1>
        </div>
        
        <!-- Description -->
        <div class="mb-12">
          <p class="text-lg text-purple-100 leading-relaxed max-w-md">
            Create your account and join thousands of productive teams. Transform how you manage tasks and boost your productivity!
          </p>
        </div>
        
        <!-- Footer -->
        <div class="text-sm text-purple-200">
          © 2025 VIPTutors. All rights reserved.
        </div>
      </div>
    </div>

    <!-- Right Side - Registration Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center px-8 sm:px-12 lg:px-16 bg-gray-50">
      <div class="w-full max-w-md">
        <!-- Mobile Logo -->
        <div class="lg:hidden text-center mb-8">
          <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center mx-auto mb-4">
            <UserPlusIcon class="h-8 w-8 text-white" />
          </div>
          <h1 class="text-2xl font-bold text-gray-900">VIPTutors</h1>
        </div>

        <!-- Form Header -->
        <div class="mb-8">
          <h2 class="text-3xl font-bold text-gray-900 mb-2">Create Account</h2>
          <p class="text-gray-600">
            Already have an account? 
            <router-link 
              to="/login" 
              class="font-semibold text-purple-600 hover:text-purple-700 underline decoration-2 underline-offset-2"
            >
              Sign in here
            </router-link>
            , it's quick and easy!
          </p>
        </div>

        <!-- Registration Form -->
        <form @submit.prevent="handleRegister" class="space-y-6">
          <!-- Name Field -->
          <div>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-4 bg-white border-b-2 border-gray-300 focus:border-purple-600 focus:outline-none transition-colors duration-200 text-gray-900 placeholder-gray-500"
              :class="{ 'border-red-500': errors.name }"
              placeholder="Full Name"
            />
            <div v-if="errors.name" class="text-sm text-red-600 mt-2 flex items-center gap-2">
              <ExclamationCircleIcon class="h-4 w-4" />
              {{ errors.name }}
            </div>
          </div>

          <!-- Email Field -->
          <div>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="w-full px-4 py-4 bg-white border-b-2 border-gray-300 focus:border-purple-600 focus:outline-none transition-colors duration-200 text-gray-900 placeholder-gray-500"
              :class="{ 'border-red-500': errors.email }"
              placeholder="Email Address"
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
                class="w-full px-4 py-4 pr-12 bg-white border-b-2 border-gray-300 focus:border-purple-600 focus:outline-none transition-colors duration-200 text-gray-900 placeholder-gray-500"
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

          <!-- Confirm Password Field -->
          <div>
            <div class="relative">
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                required
                class="w-full px-4 py-4 pr-12 bg-white border-b-2 border-gray-300 focus:border-purple-600 focus:outline-none transition-colors duration-200 text-gray-900 placeholder-gray-500"
                :class="{ 'border-red-500': errors.password_confirmation }"
                placeholder="Confirm Password"
              />
              <button
                type="button"
                @click="showConfirmPassword = !showConfirmPassword"
                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors duration-200"
              >
                <EyeIcon v-if="!showConfirmPassword" class="h-5 w-5" />
                <EyeSlashIcon v-else class="h-5 w-5" />
              </button>
            </div>
            <div v-if="errors.password_confirmation" class="text-sm text-red-600 mt-2 flex items-center gap-2">
              <ExclamationCircleIcon class="h-4 w-4" />
              {{ errors.password_confirmation }}
            </div>
          </div>

          <!-- Terms and Conditions -->
          <div class="pt-4">
            <label class="flex items-start gap-3 cursor-pointer group">
              <input
                id="terms"
                v-model="form.terms"
                type="checkbox"
                class="mt-1 h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded transition-all duration-200"
                :class="{ 'border-red-400': errors.terms }"
              />
              <span class="text-sm text-gray-600 leading-relaxed group-hover:text-gray-800 transition-colors duration-200">
                I agree to the 
                <a href="#" class="font-semibold text-purple-600 hover:text-purple-700 transition-colors duration-200 underline">Terms of Service</a>
                and 
                <a href="#" class="font-semibold text-purple-600 hover:text-purple-700 transition-colors duration-200 underline">Privacy Policy</a>
              </span>
            </label>
            <div v-if="errors.terms" class="text-sm text-red-600 mt-2 flex items-center gap-2 ml-7">
              <ExclamationCircleIcon class="h-4 w-4" />
              {{ errors.terms }}
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
              <span>{{ isLoading ? 'Creating Account...' : 'Create Account' }}</span>
            </div>
          </button>

          <!-- Error Message -->
          <div v-if="authError" class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-start gap-3">
              <ExclamationTriangleIcon class="h-5 w-5 text-red-500 mt-0.5 flex-shrink-0" />
              <div>
                <h3 class="text-sm font-semibold text-red-800">
                  Registration failed
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
import type { RegisterData } from '@/types/auth'
import {
  UserPlusIcon,
  UserIcon,
  EnvelopeIcon,
  LockClosedIcon,
  EyeIcon,
  EyeSlashIcon,
  ExclamationCircleIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()

// Form data
const form = reactive<RegisterData & { terms?: boolean }>({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false
})

// Form validation errors
const errors = ref<Record<string, string>>({})
const authError = ref<string | null>(null)
const isLoading = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)

// Clear errors when form changes
const clearErrors = () => {
  errors.value = {}
  authError.value = null
}

// Validate form
const validateForm = (): boolean => {
  const newErrors: Record<string, string> = {}

  if (!form.name) {
    newErrors.name = 'Name is required'
  } else if (form.name.length < 2) {
    newErrors.name = 'Name must be at least 2 characters'
  }

  if (!form.email) {
    newErrors.email = 'Email is required'
  } else if (!/\S+@\S+\.\S+/.test(form.email)) {
    newErrors.email = 'Email is invalid'
  }

  if (!form.password) {
    newErrors.password = 'Password is required'
  } else if (form.password.length < 8) {
    newErrors.password = 'Password must be at least 8 characters'
  }

  if (!form.password_confirmation) {
    newErrors.password_confirmation = 'Password confirmation is required'
  } else if (form.password !== form.password_confirmation) {
    newErrors.password_confirmation = 'Passwords do not match'
  }

  if (!form.terms) {
    newErrors.terms = 'You must agree to the terms and conditions'
  }

  errors.value = newErrors
  return Object.keys(newErrors).length === 0
}

// Handle registration
const handleRegister = async () => {
  clearErrors()
  
  if (!validateForm()) {
    return
  }

  isLoading.value = true

  try {
    const result = await authStore.register({
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation
    })
    
    if (result.success) {
      // Redirect to dashboard
      router.push('/dashboard')
    } else {
      authError.value = result.error || 'Registration failed'
    }
  } catch (error) {
    authError.value = 'An unexpected error occurred'
  } finally {
    isLoading.value = false
  }
}
</script>
