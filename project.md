# Full-Stack Laravel & Vue Skills Test

## Objective

Build a Task Management System using Laravel for the backend and Vue 3 (Composition API, Pinia, TailwindCSS) for the frontend. The system should follow modern development standards, be secure, responsive, and feature-complete, implementing core backend and frontend functionalities.

> ⚠️ **Security Note**: All developers must account for potential security breaches. Sanitize user inputs, validate all requests, guard against XSS/CSRF attacks, and ensure proper access controls. Follow OWASP guidelines wherever applicable.

## Backend (Laravel)

### 1. Project Setup & Best Practices

- Use the latest stable version of Laravel.
- Follow PSR-12 coding standards.
- Apply SOLID principles for maintainability.
- Implement a Service Layer and Repository Pattern to promote clean architecture and separation of concerns.

### 2. User Authentication

- Use Laravel Sanctum for SPA authentication.
- Implement:
  - User registration page: Allow new users to register.
  - Login/logout functionality.
- Serialize user data with API Resources.
- Use hashed passwords, and never expose sensitive user data.

### 3. Task Management API

- Create RESTful API endpoints for CRUD operations.
- A task must include:
  - `id`, `title`, `description`
  - `status`: pending, completed
  - `priority`: low, medium, high
  - `order`: integer (used for drag-and-drop sorting)
  - `user_id`: foreign key (relates to the user)
- Validate all incoming requests using Form Requests.
- Implement Eloquent scopes to filter by status and priority.
- Apply caching when fetching all tasks to improve performance.
- Allow task reordering with API support and save the order in the DB.

### 4. Admin Role Middleware

- Create middleware: `CheckAdmin`
  - Allow route access only if the user is an admin.
  - Redirect regular users to their personal task list.
- Apply this middleware to all admin-only routes.

### 5. Admin Dashboard

- Admins should access a dashboard that:
  - Displays all users and their tasks.
  - Shows task statistics per user (total, completed, pending).
  - Supports pagination for scalability.

### 6. Scheduled Task Cleanup

- Create a scheduled job (Cron) that:
  - Deletes tasks older than 30 days.
  - Logs all deletions.
  - Runs daily at midnight via Laravel Scheduler.

### 7. Testing

- Write unit tests for:
  - Task CRUD operations.
  - Task reordering logic.
- Use MySQL for testing (not SQLite).
  - Setup a separate test database.

### 8. API Documentation

- Document endpoints using Postman or Swagger.
- Include:
  - Request/response examples for all features (auth, tasks, admin).
- Add the exported collection or Swagger URL in README.md.

## Frontend (Vue 3)

### 1. Project Setup

- Use:
  - Vue 3 (Composition API)
  - Vue Router
  - Pinia (state management)
  - TailwindCSS (styling)

### 2. Task Management UI

- Display tasks in a responsive list view.
- Users should be able to:
  - Add a new task.
  - Mark tasks as completed.
  - Delete tasks (only if admin).
  - Drag and drop to reorder tasks (save order in backend).
- Use color-coded priorities (low, medium, high).
- Add smooth Vue transitions for status updates.

### 3. Filtering & Search

- Allow users to filter by:
  - **Status**: All, Pending, Completed
  - **Priority**: Low, Medium, High
- Implement a search bar for task title/description.

### 4. Responsive Design

- Ensure full responsiveness for:
  - Mobile
  - Tablet
  - Desktop
- Use Tailwind's utility classes for layout and spacing.

### 5. Authentication

- Build a registration and login page using Sanctum.
- Store user authentication state in Pinia.
- Handle route protection for authenticated vs guest users.

### 6. Required Features

- Drag-and-drop task reordering (with real-time backend updates).
- Display task statistics (e.g., completed vs pending).
- Implement WebSocket support (using Laravel Echo + Pusher or similar) for real-time task updates.
- Admin dashboard: View all users and their tasks.

## Expected Deliverables

A public GitHub repository containing:

```
/backend → Laravel backend code
/frontend → Vue 3 frontend code
README.md → Setup instructions + API documentation link/export
```
