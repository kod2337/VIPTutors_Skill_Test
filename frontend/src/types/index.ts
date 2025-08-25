export interface User {
  id: number
  name: string
  email: string
  email_verified_at?: string
  is_admin: boolean
  created_at: string
  updated_at: string
}

export interface Task {
  id: number
  title: string
  description: string
  status: 'pending' | 'completed'
  priority: 'low' | 'medium' | 'high'
  order: number
  user_id: number
  user?: User
  created_at: string
  updated_at: string
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export interface TaskFilters {
  status?: 'all' | 'pending' | 'completed'
  priority?: 'all' | 'low' | 'medium' | 'high'
  search?: string
}

export interface ApiResponse<T> {
  data: T
  message?: string
  status: string
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    per_page: number
    total: number
    last_page: number
  }
  links: {
    first: string
    last: string
    prev: string | null
    next: string | null
  }
}
