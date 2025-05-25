# Simple Blog Application - Setup Guide

This guide will help you set up and run the Laravel Blog Application locally using Docker.

## Prerequisites

- **Docker** (version 20.0 or higher)
- **Docker Compose** (version 2.0 or higher)
- **Git**

## Quick Start

### 1. Clone the Repository

```bash
git clone <repository-url>
cd simple-blog-application
```

### 2. Environment Configuration

Copy the example environment file:
```bash
cp .env.example .env
```

The default `.env` file is already configured for Docker. Key settings:
```env
DB_HOST=mysql
DB_DATABASE=simple_blog_application
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_password
```

### 3. Build and Start the Application

Build and start all containers:
```bash
docker-compose up -d --build
```

This will start:
- **app**: PHP-FPM application container
- **nginx**: Web server (accessible on port 8080)
- **mysql**: MySQL database (accessible on port 3342)

This creates:
- 4 demo users with known credentials
- ~40 blog posts with realistic content
- ~150-200 comments (mix of user and guest comments)

## Access the Application

- **API Base URL**: `http://localhost:8080/api`
- **Database**: `localhost:3342` (from host machine)

## Demo User Accounts

| Email | Password | Role |
|-------|----------|------|
| john@example.com | password | Regular User |
| jane@example.com | password | Regular User |
| admin@example.com | password | Admin User |
| demo@example.com | password | Demo User |

## API Endpoints

### Authentication
- `POST /api/register` - Register a new user
- `POST /api/login` - Login and get access token
- `POST /api/logout` - Logout (revoke token)

### Posts (Public)
- `GET /api/posts` - List all posts (with pagination)
- `GET /api/posts/{id}` - Get a specific post with comments

### Posts (Authenticated)
- `POST /api/posts` - Create a new post
- `PUT /api/posts/{id}` - Update a post (only by author)
- `DELETE /api/posts/{id}` - Delete a post (only by author)

### Comments
- `POST /api/posts/{id}/comments` - Add a comment (supports both authenticated users and guests)
- `DELETE /api/comments/{id}` - Delete a comment (by comment author or post author)

## Testing the API

### 1. Register a new user:
```bash
curl -X POST http://localhost:8080/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password",
    "password_confirmation": "password"
  }'
```

### 2. Login to get a token:
```bash
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password"
  }'
```

### 3. Get all posts:
```bash
curl http://localhost:8080/api/posts
```

### 4. Create a post (replace YOUR_TOKEN):
```bash
curl -X POST http://localhost:8080/api/posts \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "My Docker Post",
    "content": "This post was created via Docker setup!"
  }'
```

### 5. Add a comment as guest:
```bash
curl -X POST http://localhost:8080/api/posts/1/comments \
  -H "Content-Type: application/json" \
  -d '{
    "guest_name": "Docker User",
    "comment": "Great post! Docker setup works perfectly."
  }'
```

## Running Tests

Run the complete test suite:
```bash
docker-compose exec app php artisan test
```

Run specific test types:
```bash
# Feature tests only
docker-compose exec app php artisan test --testsuite=Feature

# Unit tests only
docker-compose exec app php artisan test --testsuite=Unit
```

## Troubleshooting

### Common Issues

1. **Port conflicts:**
   ```bash
   # Check if ports are in use
   netstat -an | grep :8080
   netstat -an | grep :3342
   
   # Change ports in docker-compose.yml if needed
   ```

2. **Permission issues:**
   ```bash
   # Fix storage permissions
   docker-compose exec app chmod -R 775 storage bootstrap/cache
   ```

3. **Database connection issues:**
   ```bash
   # Check if MySQL is ready
   docker-compose exec mysql mysqladmin ping -h localhost
   
   # Restart database
   docker-compose restart mysql
   ```

4. **Container not starting:**
   ```bash
   # Check logs for errors
   docker-compose logs app
   
   # Rebuild containers
   docker-compose down
   docker-compose up -d --build
   ```

### Reset Everything
```bash
# Stop and remove all containers, networks, and volumes
docker-compose down -v

# Remove images (optional)
docker-compose down -v --rmi all

# Start fresh
docker-compose up -d
```

## Development Workflow

1. **Make code changes** in your local files
2. **Changes are automatically reflected** (volume mounting)
3. **Run tests** to ensure everything works:
   ```bash
   docker-compose exec app php artisan test
   ```
4. **Check logs** if needed:
   ```bash
   docker-compose logs app
   ```

## Project Structure

The Docker setup includes:
- **PHP 8.4** with all required extensions
- **Nginx** web server with optimized configuration
- **MySQL 8.0** database
- **Volume mounting** for live code reloading
- **Network isolation** for security

Your local code is mounted into the containers, so any changes you make are immediately reflected without rebuilding.

## Support

If you encounter issues:
1. Check container logs: `docker-compose logs [service-name]`
2. Verify all containers are running: `docker-compose ps`
3. Ensure ports 8080 and 3342 are available
4. Try rebuilding: `docker-compose up -d --build`