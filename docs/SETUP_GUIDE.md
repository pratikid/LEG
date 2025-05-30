# LEG Project Setup Guide

This guide will help you set up the LEG (Lineage Exploration and Genealogy) project with Laravel, Livewire, Alpine.js, and all the required technologies.

## Prerequisites

- Docker Desktop installed and running
- Git installed
- Basic knowledge of Laravel, Docker, and web development

## Setup Steps

### 1. Start Docker Desktop

Ensure Docker Desktop is running on your system before proceeding.

### 2. Clone the Repository

```bash
git clone https://github.com/pratikid/LEG.git
cd LEG
```

### 3. Create Required Directories

```bash
mkdir -p docker/nginx/conf.d
```

### 4. Start Docker Containers

```bash
docker compose up -d --build
```

### 5. Create a New Laravel Project

```bash
docker compose exec app composer create-project laravel/laravel .
```

### 6. Install Required PHP Packages

```bash
# Install Livewire
docker compose exec app composer require livewire/livewire

# Install Neo4j client
docker compose exec app composer require laudis/neo4j-php-client

# Install MongoDB Laravel package
docker compose exec app composer require mongodb/laravel-mongodb

# Install Redis Laravel package
docker compose exec app composer require predis/predis
```

### 7. Install Frontend Dependencies

```bash
# Install Alpine.js
docker compose exec node npm install alpinejs

# Install TailwindCSS
docker compose exec node npm install -D tailwindcss postcss autoprefixer
docker compose exec node npx tailwindcss init -p

# Install D3.js
docker compose exec node npm install d3
```

### 8. Configure Laravel

```bash
# Generate application key
docker compose exec app php artisan key:generate

# Give proper permissions to storage and bootstrap/cache directories
docker compose exec app chmod -R 777 storage bootstrap/cache
```

### 9. Configure Environment

Copy the `.env.example` file to `.env` and update the database and service connection settings:

```
# PostgreSQL Connection
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=leg
DB_USERNAME=leg
DB_PASSWORD=password123

# MongoDB Connection
MONGODB_HOST=mongodb
MONGODB_PORT=27017
MONGODB_DATABASE=leg
MONGODB_USERNAME=leg
MONGODB_PASSWORD=password123
MONGODB_AUTHENTICATION_DATABASE=admin

# Neo4j Connection
NEO4J_HOST=neo4j
NEO4J_PORT=7687
NEO4J_USERNAME=neo4j
NEO4J_PASSWORD=password123

# Redis Connection
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

### 10. Run Database Migrations

```bash
docker compose exec app php artisan migrate
```

## Accessing the Application

- **Web Application**: http://localhost
- **PostgreSQL**: localhost:5432 (Username: leg, Password: password123)
- **MongoDB**: localhost:27017 (Username: leg, Password: password123)
- **Neo4j Dashboard**: http://localhost:7474 (Username: neo4j, Password: password123)
- **Redis**: localhost:6379

## Troubleshooting

### Docker Issues

- Ensure Docker Desktop is running
- Try restarting Docker Desktop
- Check Docker logs for errors: `docker compose logs`

### Connection Issues

- Verify that all services are running: `docker compose ps`
- Check if the ports are properly exposed and not in use by other applications

### Permission Issues

- Run `docker compose exec app chmod -R 777 storage bootstrap/cache` if you encounter permission errors

## Development Workflow

1. Start the containers: `docker compose up -d`
2. Make changes to your code
3. Run tests: `docker compose exec app php artisan test`
4. Build assets: `docker compose exec node npm run dev`

## Technology Stack Reference

- **Laravel**: PHP framework for backend
- **Livewire**: Full-stack framework for dynamic interfaces
- **Alpine.js**: JavaScript framework for reactivity
- **D3.js**: Visualization library for family trees
- **Neo4j**: Graph database for family relationships
- **PostgreSQL**: Relational database
- **MongoDB**: Document database
- **Redis**: Cache and queue manager
- **TailwindCSS**: Utility-first CSS framework

## Additional Commands

### Composer Commands

```bash
docker compose exec app composer require [package-name]
docker compose exec app composer update
```

### Artisan Commands

```bash
docker compose exec app php artisan make:controller [ControllerName]
docker compose exec app php artisan make:model [ModelName]
docker compose exec app php artisan make:livewire [ComponentName]
```

### NPM Commands

```bash
docker compose exec node npm install [package-name]
docker compose exec node npm run dev
docker compose exec node npm run build
``` 