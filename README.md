# Task Management System

A full-stack task management application built with Laravel (backend) and Vue.js (frontend), featuring user authentication, real-time updates, drag-and-drop functionality, and admin dashboard.

## Features

- **User Authentication**: Registration, login/logout with Laravel Sanctum
- **Task Management**: Create, read, update, delete tasks with priority levels
- **Drag & Drop**: Reorder tasks with persistent state
- **Real-time Updates**: WebSocket support for live task synchronization
- **Admin Dashboard**: User management and task statistics
- **Responsive Design**: Mobile-first approach with TailwindCSS
- **Search & Filtering**: Advanced filtering by status, priority, and search
- **Scheduled Jobs**: Automatic cleanup of old tasks

## Tech Stack

### Backend
- **Laravel 12** (latest stable)
- **Laravel Sanctum** for SPA authentication
- **MySQL** database
- **PSR-12** coding standards
- **Service Layer & Repository Pattern**

### Frontend
- **Vue 3** with Composition API
- **TypeScript** for type safety
- **Pinia** for state management
- **Vue Router** for navigation
- **TailwindCSS** for styling
- **Axios** for API calls
- **Vite** for build tooling

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.2+** with required extensions
- **Composer** (latest version)
- **Node.js 18+** and **npm**
- **MySQL 8.0+**
- **Git**

### Required PHP Extensions
- BCMath
- Ctype
- cURL
- DOM
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PCRE
- PDO
- Tokenizer
- XML

## Installation & Setup

### 1. Clone the Repository

```bash
git clone <repository-url>
cd VIPTutors
```

### 2. Backend Setup (Laravel)

```bash
# Navigate to backend directory
cd backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your .env file with database credentials:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=task_management
# DB_USERNAME=root
# DB_PASSWORD=your_password

# Create databases
mysql -u root -p
CREATE DATABASE task_management;
CREATE DATABASE task_management_test;
exit;

# Run migrations
php artisan migrate

# Publish Sanctum configuration
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 3. Frontend Setup (Vue.js)

```bash
# Navigate to frontend directory
cd ../frontend

# Install Node.js dependencies
npm install

# Install additional dependencies
npm install axios @vueuse/core

# Install TailwindCSS
npm install -D tailwindcss postcss autoprefixer @tailwindcss/vite

# Initialize TailwindCSS
npx tailwindcss init -p
```

### 4. Environment Configuration

#### Backend (.env)
Update your `backend/.env` file:

```env
APP_NAME="Task Management System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=root
DB_PASSWORD=your_password

# Test Database
DB_TEST_CONNECTION=mysql
DB_TEST_HOST=127.0.0.1
DB_TEST_PORT=3306
DB_TEST_DATABASE=task_management_test
DB_TEST_USERNAME=root
DB_TEST_PASSWORD=your_password

SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
SESSION_DRIVER=database
```

## Running the Application

### Development Mode
```

#### Frontend (Environment)
Create `frontend/.env`:

```env
VITE_API_URL=http://localhost:8000/api
VITE_APP_URL=http://localhost:3000
```

## 🏃‍♂️ Running the Application

### Development Mode

#### Start Backend Server
```bash
cd backend
php artisan serve
# Server will run on http://localhost:8000
```

#### Start Frontend Development Server
```bash
cd frontend
npm run dev
# Server will run on http://localhost:3000
```

### Production Build

#### Backend
```bash
cd backend
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Frontend
```bash
cd frontend
npm run build
```

## Testing

### Backend Tests
```bash
cd backend

# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

### Frontend Tests
```bash
cd frontend

# Run unit tests
npm run test:unit

# Run e2e tests
npm run test:e2e

# Run tests in watch mode
npm run test:unit -- --watch
```

### Code Quality

#### Backend (PSR-12 Standards)
```bash
cd backend

# Check code style
./vendor/bin/pint --test

# Fix code style
./vendor/bin/pint
```

#### Frontend (ESLint + Prettier)
```bash
cd frontend

# Check linting
npm run lint

# Fix linting issues
npm run lint -- --fix

# Format code
npm run format
```

## Database Schema

### Users Table
- `id` - Primary key
- `name` - User's full name
- `email` - Email address (unique)
- `password` - Hashed password
- `is_admin` - Boolean flag for admin role
- `email_verified_at` - Email verification timestamp
- `created_at`, `updated_at` - Timestamps

### Tasks Table
- `id` - Primary key
- `title` - Task title
- `description` - Task description
- `status` - Enum: 'pending', 'completed'
- `priority` - Enum: 'low', 'medium', 'high'
- `order` - Integer for drag-and-drop ordering
- `user_id` - Foreign key to users table
- `created_at`, `updated_at` - Timestamps

## Security Features

- **Input Sanitization**: All inputs validated and sanitized
- **CSRF Protection**: Enabled for all forms
- **XSS Prevention**: Output escaping and content security policies
- **SQL Injection Protection**: Eloquent ORM and prepared statements
- **Authentication**: Secure password hashing with bcrypt
- **Rate Limiting**: API endpoint protection
- **OWASP Compliance**: Following security best practices

## API Endpoints

### Authentication
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `GET /api/user` - Get authenticated user

### Tasks
- `GET /api/tasks` - List user's tasks
- `POST /api/tasks` - Create new task
- `PUT /api/tasks/{id}` - Update task
- `DELETE /api/tasks/{id}` - Delete task
- `POST /api/tasks/reorder` - Reorder tasks

### Admin (Admin only)
- `GET /api/admin/users` - List all users
- `GET /api/admin/tasks` - List all tasks
- `GET /api/admin/statistics` - Get task statistics

## Scheduled Jobs

The application includes automated cleanup jobs:

- **Task Cleanup**: Deletes tasks older than 30 days
- **Schedule**: Runs daily at midnight
- **Logging**: All deletions are logged

To run the scheduler:
```bash
# Add to crontab
* * * * * cd /path-to-your-project/backend && php artisan schedule:run >> /dev/null 2>&1
```

## Deployment

### Production Checklist

1. **Environment Setup**
   - Set `APP_ENV=production`
   - Set `APP_DEBUG=false`
   - Configure production database
   - Set up proper domain in `SANCTUM_STATEFUL_DOMAINS`

2. **Security**
   - Generate new `APP_KEY`
   - Configure HTTPS
   - Set up proper CORS policies
   - Enable rate limiting

3. **Performance**
   - Run `php artisan optimize`
   - Enable OPcache
   - Configure Redis for caching
   - Set up CDN for static assets

4. **Monitoring**
   - Set up error logging
   - Configure monitoring tools
   - Set up backup procedures

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Standards

- Follow PSR-12 for PHP code
- Use ESLint + Prettier for JavaScript/TypeScript
- Write tests for new features
- Update documentation as needed
- Follow conventional commit messages

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Troubleshooting

### Common Issues

#### Backend Issues
- **Database connection error**: Check MySQL credentials in `.env`
- **Sanctum not working**: Ensure `SANCTUM_STATEFUL_DOMAINS` includes your frontend URL
- **Migration errors**: Check database exists and permissions are correct

#### Frontend Issues
- **API calls failing**: Check `VITE_API_URL` in frontend `.env`
- **CORS errors**: Verify backend CORS configuration
- **Build failures**: Clear `node_modules` and reinstall dependencies

#### General Issues
- **Authentication not working**: Check session configuration and Sanctum setup
- **Real-time features not working**: Verify WebSocket configuration
- **Tests failing**: Check test database configuration

### Getting Help

If you encounter issues:

1. Check the troubleshooting section above
2. Review Laravel and Vue.js documentation
3. Search existing GitHub issues
4. Create a new issue with detailed information

## Support

For support and questions:

- Create an issue on GitHub
- Check the [Wiki](../../wiki) for additional documentation
- Review the API documentation (coming soon)

---

**Happy coding!**
