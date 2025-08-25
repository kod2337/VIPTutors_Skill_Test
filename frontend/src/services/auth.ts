import type { LoginCredentials, RegisterData, User, AuthResponse } from '@/types/auth'
import api from './api'

export class AuthService {
  /**
   * Login user
   */
  static async login(credentials: LoginCredentials): Promise<AuthResponse> {
    try {
      const response = await api.post('/login', credentials)
      return {
        success: true,
        user: response.data.user,
        token: response.data.token,
        message: response.data.message
      }
    } catch (error: any) {
      return {
        success: false,
        error: error.response?.data?.message || 'Login failed'
      }
    }
  }

  /**
   * Register new user
   */
  static async register(userData: RegisterData): Promise<AuthResponse> {
    try {
      const response = await api.post('/register', userData)
      return {
        success: true,
        user: response.data.user,
        token: response.data.token,
        message: response.data.message
      }
    } catch (error: any) {
      return {
        success: false,
        error: error.response?.data?.message || 'Registration failed'
      }
    }
  }

  /**
   * Logout user
   */
  static async logout(): Promise<void> {
    try {
      await api.post('/logout')
    } catch (error) {
      // Even if logout fails on server, we clear local storage
      console.error('Logout error:', error)
    }
  }

  /**
   * Get current user
   */
  static async getCurrentUser(): Promise<User> {
    const response = await api.get('/user')
    return response.data.user
  }

  /**
   * Check if user is authenticated
   */
  static isAuthenticated(): boolean {
    const token = localStorage.getItem('token')
    const user = localStorage.getItem('user')
    return !!(token && user)
  }

  /**
   * Get stored token
   */
  static getToken(): string | null {
    return localStorage.getItem('token')
  }

  /**
   * Get stored user
   */
  static getStoredUser(): User | null {
    const userStr = localStorage.getItem('user')
    if (userStr) {
      try {
        return JSON.parse(userStr)
      } catch {
        return null
      }
    }
    return null
  }

  /**
   * Clear authentication data
   */
  static clearAuth(): void {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }
}

export default AuthService
