# Task Management System

A full-stack Task Management System built with Laravel (backend) and Vue 3 (frontend) featuring modern development practices, security standards, and comprehensive functionality.

## Project Overview

This project implements a comprehensive task management system with the following key features:

- **Backend**: Laravel 12 with Sanctum authentication
- **Frontend**: Vue 3 with Composition API, Pinia, and TailwindCSS
- **Architecture**: Service Layer and Repository Pattern
- **Security**: OWASP guidelines compliance, XSS/CSRF protection
- **Real-time**: WebSocket support for live updates
- **Testing**: Comprehensive unit and feature tests

## Project Structure

```
├── backend/              # Laravel backend application
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   ├── Resources/     # API Resources
│   │   │   └── Requests/      # Form Request validation
│   │   ├── Models/
│   │   ├── Services/          # Business logic layer
│   │   └── Repositories/      # Data access layer
│   │       └── Interfaces/
│   ├── config/
│   ├── database/
│   └── tests/
├── frontend/             # Vue 3 frontend application
│   ├── src/
│   │   ├── components/
│   │   ├── views/
│   │   ├── stores/        # Pinia state management
│   │   ├── router/
│   │   └── services/      # API services
│   └── tests/
├── project-phases.md     # Development phases breakdown
└── project.md           # Original project requirements
```

## Technology Stack

### Backend
- **Framework**: Laravel 12.x
- **Authentication**: Laravel Sanctum (SPA)
- **Database**: MySQL
- **Architecture**: Service Layer + Repository Pattern
- **Code Standards**: PSR-12 (enforced by Laravel Pint)
- **Testing**: PHPUnit with MySQL test database

### Frontend
- **Framework**: Vue 3 (Composition API)
- **State Management**: Pinia
- **Routing**: Vue Router
- **Styling**: TailwindCSS
- **HTTP Client**: Axios
- **Build Tool**: Vite
- **Type Safety**: TypeScript
- **Testing**: Vitest + Playwright

## Development Status

### Phase 1: Project Setup & Foundation (COMPLETED)
- [x] Laravel project initialization with latest version
- [x] Laravel Sanctum installation and configuration
- [x] Vue 3 project setup with Vite
- [x] Service Layer and Repository Pattern architecture
- [x] PSR-12 coding standards configuration
- [x] MySQL database configuration
- [x] Test database setup
- [x] Project structure organization

### Current Phase: Phase 2 - User Authentication System
- [ ] User model and migration setup
- [ ] Authentication controllers
- [ ] API Resources for user serialization
- [ ] Admin role middleware
- [ ] Frontend authentication pages
- [ ] Pinia authentication store

## Setup Instructions

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- npm
- MySQL 8.0+
- XAMPP (or similar local server environment)

### Backend Setup

1. **Clone and navigate to backend**
   ```bash
   cd backend
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   - Create MySQL databases: `task_management` and `task_management_test`
   - Update `.env` file with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=task_management
   DB_USERNAME=root
   DB_PASSWORD=your_password
   
   # Test Database
   DB_TEST_DATABASE=task_management_test
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Publish Sanctum configuration**
   ```bash
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   ```

7. **Start development server**
   ```bash
   php artisan serve
   ```

### Frontend Setup

1. **Navigate to frontend**
   ```bash
   cd frontend
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Start development server**
   ```bash
   npm run dev
   ```

## Testing

### Backend Testing
```bash
cd backend
php artisan test
```

### Frontend Testing
```bash
cd frontend
npm run test:unit          # Unit tests with Vitest
npm run test:e2e          # E2E tests with Playwright
```

### Code Quality
```bash
# Backend (PSR-12 compliance)
cd backend
./vendor/bin/pint

# Frontend (ESLint + Prettier)
cd frontend
npm run lint
npm run format
```

## API Documentation

API documentation will be available via Postman collection or Swagger UI once the API endpoints are implemented.

## Security Features

- Input sanitization and validation
- XSS protection
- CSRF token implementation
- Secure password hashing
- SQL injection prevention
- API rate limiting
- OWASP guidelines compliance

## Key Features (Planned)

- User authentication and authorization
- Task CRUD operations with drag-and-drop reordering
- Priority and status management
- Real-time updates via WebSockets
- Advanced filtering and search
- Admin dashboard with user management
- Responsive design for all devices
- Automated task cleanup (scheduled jobs)

## Development Guidelines

- Follow PSR-12 coding standards for PHP
- Use ESLint and Prettier for JavaScript/TypeScript
- Write comprehensive tests for all features
- Follow SOLID principles
- Implement proper error handling
- Use semantic commit messages

## Development Timeline

**Total Estimated Time**: 24 Hrs

See [project-phases.md](project-phases.md) for detailed phase breakdown and progress tracking.

## Issues and Support

For issues and support, please refer to the project documentation or create an issue in the repository.

## License

This project is developed as a skill assessment and is for educational purposes.