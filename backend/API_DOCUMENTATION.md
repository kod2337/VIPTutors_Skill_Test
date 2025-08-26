# VIP Tutors Task Management API Documentation

## Overview

The VIP Tutors Task Management API is a comprehensive RESTful API built with Laravel that provides user authentication, task management, and administrative capabilities. The API uses Laravel Sanctum for authentication and provides extensive task management features including CRUD operations, reordering, filtering, and search capabilities.

## Base URL

```
http://localhost:8000/api
```

## Authentication

The API uses Bearer token authentication (Laravel Sanctum). Include the token in the Authorization header:

```
Authorization: Bearer {your-token}
```

### Getting a Token

1. **Register**: `POST /register` - Create a new user account and receive a token
2. **Login**: `POST /login` - Authenticate with existing credentials and receive a token

## API Endpoints

### Authentication Endpoints

| Method | Endpoint | Description | Authentication |
|--------|----------|-------------|----------------|
| POST | `/register` | Register a new user | None |
| POST | `/login` | Login user | None |
| POST | `/logout` | Logout user | Required |
| GET | `/user` | Get current user info | Required |

### Task Management Endpoints

| Method | Endpoint | Description | Authentication |
|--------|----------|-------------|----------------|
| GET | `/tasks` | Get user's tasks with filtering/pagination | Required |
| POST | `/tasks` | Create a new task | Required |
| GET | `/tasks/{id}` | Get specific task | Required |
| PUT | `/tasks/{id}` | Update specific task | Required |
| DELETE | `/tasks/{id}` | Delete specific task | Required |
| POST | `/tasks/reorder` | Reorder user's tasks | Required |
| PATCH | `/tasks/{id}/toggle-status` | Toggle task status | Required |
| GET | `/tasks-statistics` | Get user's task statistics | Required |

### Admin Endpoints

| Method | Endpoint | Description | Authentication |
|--------|----------|-------------|----------------|
| GET | `/admin/dashboard-stats` | Get admin dashboard statistics | Admin Required |
| GET | `/admin/task-statistics` | Get system-wide task statistics | Admin Required |
| GET | `/admin/top-performers` | Get top performing users | Admin Required |
| GET | `/admin/users` | Get all users | Admin Required |
| GET | `/admin/users/{id}` | Get specific user details | Admin Required |
| PATCH | `/admin/users/{id}/role` | Update user role | Admin Required |
| DELETE | `/admin/tasks/{id}` | Delete any task (admin) | Admin Required |

## Request/Response Examples

### Authentication

#### Register User
```http
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response (201):**
```json
{
    "message": "User registered successfully",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "is_admin": false,
        "email_verified_at": null,
        "created_at": "2024-01-15T10:30:00.000000Z",
        "updated_at": "2024-01-15T10:30:00.000000Z"
    },
    "token": "1|abc123def456..."
}
```

#### Login User
```http
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response (200):**
```json
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "is_admin": false,
        "email_verified_at": null,
        "created_at": "2024-01-15T10:30:00.000000Z",
        "updated_at": "2024-01-15T10:30:00.000000Z"
    },
    "token": "2|xyz789abc456..."
}
```

### Task Management

#### Create Task
```http
POST /api/tasks
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Complete project documentation",
    "description": "Write comprehensive documentation for the project",
    "priority": "medium",
    "status": "pending"
}
```

**Response (201):**
```json
{
    "data": {
        "id": 1,
        "title": "Complete project documentation",
        "description": "Write comprehensive documentation for the project",
        "status": "pending",
        "priority": "medium",
        "order": 1,
        "user_id": 1,
        "created_at": "2024-01-15T10:30:00.000000Z",
        "updated_at": "2024-01-15T10:30:00.000000Z"
    }
}
```

#### Get Tasks with Filtering
```http
GET /api/tasks?status=pending&priority=high,medium&search=project&sort_by=created_at&sort_direction=desc&per_page=10&page=1
Authorization: Bearer {token}
```

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "title": "Complete project documentation",
            "description": "Write comprehensive documentation for the project",
            "status": "pending",
            "priority": "medium",
            "order": 1,
            "user_id": 1,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T10:30:00.000000Z"
        }
    ],
    "links": {
        "first": "http://localhost:8000/api/tasks?page=1",
        "last": "http://localhost:8000/api/tasks?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "path": "http://localhost:8000/api/tasks",
        "per_page": 10,
        "to": 1,
        "total": 1
    }
}
```

#### Reorder Tasks
```http
POST /api/tasks/reorder
Authorization: Bearer {token}
Content-Type: application/json

{
    "tasks": [3, 1, 2]
}
```

**Response (200):**
```json
{
    "message": "Tasks reordered successfully"
}
```

## Query Parameters for Task Filtering

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `status` | string | Filter by status | `pending`, `completed` |
| `priority` | string | Filter by priority (comma-separated) | `high,medium` |
| `search` | string | Search in title/description | `project` |
| `date_from` | date | Tasks created from date | `2024-01-01` |
| `date_to` | date | Tasks created until date | `2024-12-31` |
| `sort_by` | string | Sort by field | `created_at`, `title`, `priority` |
| `sort_direction` | string | Sort direction | `asc`, `desc` |
| `per_page` | integer | Items per page (5-10) | `10` |
| `page` | integer | Page number | `1` |

## Error Responses

### Validation Error (422)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

### Unauthorized (401)
```json
{
    "message": "Unauthenticated"
}
```

### Forbidden (403)
```json
{
    "message": "Unauthorized to access this resource"
}
```

### Not Found (404)
```json
{
    "message": "Resource not found"
}
```

## Data Models

### User Model
- `id`: integer (Primary key)
- `name`: string (User's full name)
- `email`: string (Unique email address)
- `is_admin`: boolean (Admin role flag)
- `email_verified_at`: datetime (Email verification timestamp)
- `created_at`: datetime (Creation timestamp)
- `updated_at`: datetime (Last update timestamp)

### Task Model
- `id`: integer (Primary key)
- `title`: string (Task title)
- `description`: text (Task description)
- `status`: enum (pending, completed)
- `priority`: enum (low, medium, high)
- `order`: integer (Sort order)
- `user_id`: integer (Foreign key to users table)
- `created_at`: datetime (Creation timestamp)
- `updated_at`: datetime (Last update timestamp)

## Rate Limiting

The API implements standard Laravel rate limiting:
- Authentication endpoints: 60 requests per minute
- General API endpoints: 60 requests per minute per user

## Interactive Documentation

For interactive API testing, visit the Swagger UI at:
```
http://localhost:8000/api/documentation
```

This provides a comprehensive interface for testing all endpoints with authentication and request/response examples.

## Security Features

1. **Authentication**: Laravel Sanctum token-based authentication
2. **Authorization**: User-specific task access and admin role checking
3. **Validation**: Comprehensive input validation on all endpoints
4. **CSRF Protection**: Built-in Laravel CSRF protection
5. **Rate Limiting**: Request throttling to prevent abuse
6. **SQL Injection Prevention**: Eloquent ORM protection

## Testing

The API includes comprehensive test suites:
- Authentication tests
- Task management tests
- Task reordering tests
- Integration tests
- Admin functionality tests

Run tests with:
```bash
php artisan test
```

## Support

For support and questions about the API, contact the development team at admin@viptutors.com.
