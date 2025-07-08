# Installation Guide

This guide will help you set up the LEG (Laravel Enhanced Genealogy) application on your local development environment.

## Prerequisites

Before installing LEG, ensure you have the following software installed:

### Required Software
- **PHP 8.4+**: Latest PHP version with enhanced performance
- **Composer 2.0+**: PHP dependency management
- **Node.js 18+**: For frontend asset compilation
- **Git**: Version control system

### Database Requirements
- **PostgreSQL 14+**: Primary relational database
- **MongoDB 6+**: Document storage database
- **Neo4j 5+**: Graph database for relationships
- **Redis 7+**: Caching and session management

### Optional Software
- **Docker & Docker Compose**: For containerized development
- **Laravel Sail**: Laravel's Docker development environment

## Installation Methods

### Method 1: Manual Installation

#### Step 1: Clone the Repository
```bash
git clone <repository-url>
cd leg
```

#### Step 2: Install PHP Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

#### Step 3: Install Node.js Dependencies
```bash
npm install
```

#### Step 4: Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### Step 5: Configure Environment Variables
Edit the `.env` file with your database credentials:

```env
# Database Configuration
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=leg_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

# MongoDB Configuration
MONGODB_HOST=127.0.0.1
MONGODB_PORT=27017
MONGODB_DATABASE=leg_documents
MONGODB_USERNAME=your_username
MONGODB_PASSWORD=your_password

# Neo4j Configuration
NEO4J_HOST=127.0.0.1
NEO4J_PORT=7687
NEO4J_USERNAME=neo4j
NEO4J_PASSWORD=your_password

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Application Configuration
APP_NAME="LEG - Laravel Enhanced Genealogy"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Queue Configuration
QUEUE_CONNECTION=redis
```

#### Step 6: Database Setup
```bash
# Run database migrations
php artisan migrate

# Seed the database with initial data
php artisan db:seed
```

#### Step 7: Build Frontend Assets
```bash
# Build production assets
npm run build
```

#### Step 8: Set Permissions
```bash
# Set storage permissions
chmod -R 775 storage bootstrap/cache

# Set ownership (if using web server)
chown -R www-data:www-data storage bootstrap/cache
```

### Method 2: Docker Installation

#### Step 1: Clone the Repository
```bash
git clone <repository-url>
cd leg
```

#### Step 2: Configure Docker Environment
```bash
# Copy Docker environment file
cp .env.docker.example .env
```

#### Step 3: Start Docker Services
```bash
# Start all services
docker-compose up -d

# Or use the provided script
./docker.sh
```

#### Step 4: Install Dependencies
```bash
# Install PHP dependencies
docker-compose exec app composer install

# Install Node.js dependencies
docker-compose exec app npm install
```

#### Step 5: Setup Application
```bash
# Generate application key
docker-compose exec app php artisan key:generate

# Run database migrations
docker-compose exec app php artisan migrate

# Seed the database
docker-compose exec app php artisan db:seed

# Build frontend assets
docker-compose exec app npm run build
```

## Database Setup

### PostgreSQL Setup

#### Create Database
```sql
CREATE DATABASE leg_db;
CREATE USER leg_user WITH PASSWORD 'your_password';
GRANT ALL PRIVILEGES ON DATABASE leg_db TO leg_user;
```

#### Configure Connection
Update your `.env` file with PostgreSQL credentials:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=leg_db
DB_USERNAME=leg_user
DB_PASSWORD=your_password
```

### MongoDB Setup

#### Install MongoDB
```bash
# Ubuntu/Debian
sudo apt-get install mongodb

# macOS
brew install mongodb-community

# Start MongoDB service
sudo systemctl start mongod
```

#### Create Database
```bash
# Connect to MongoDB
mongo

# Create database and user
use leg_documents
db.createUser({
  user: "leg_user",
  pwd: "your_password",
  roles: ["readWrite"]
})
```

#### Configure Connection
Update your `.env` file with MongoDB credentials:
```env
MONGODB_HOST=127.0.0.1
MONGODB_PORT=27017
MONGODB_DATABASE=leg_documents
MONGODB_USERNAME=leg_user
MONGODB_PASSWORD=your_password
```

### Neo4j Setup

#### Install Neo4j
```bash
# Download Neo4j Desktop or Community Edition
# https://neo4j.com/download/

# Or use Docker
docker run -p 7474:7474 -p 7687:7687 neo4j:latest
```

#### Configure Neo4j
1. Open Neo4j Browser at `http://localhost:7474`
2. Set initial password
3. Create database user (optional)

#### Configure Connection
Update your `.env` file with Neo4j credentials:
```env
NEO4J_HOST=127.0.0.1
NEO4J_PORT=7687
NEO4J_USERNAME=neo4j
NEO4J_PASSWORD=your_password
```

### Redis Setup

#### Install Redis
```bash
# Ubuntu/Debian
sudo apt-get install redis-server

# macOS
brew install redis

# Start Redis service
sudo systemctl start redis
```

#### Configure Connection
Update your `.env` file with Redis credentials:
```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Web Server Configuration

### Apache Configuration

Create a virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName leg.local
    DocumentRoot /path/to/leg/public
    
    <Directory /path/to/leg/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/leg_error.log
    CustomLog ${APACHE_LOG_DIR}/leg_access.log combined
</VirtualHost>
```

### Nginx Configuration

Create a server block configuration:

```nginx
server {
    listen 80;
    server_name leg.local;
    root /path/to/leg/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Queue Configuration

### Redis Queue Setup

Configure Redis for queue processing:

```bash
# Install Redis PHP extension
sudo apt-get install php-redis

# Or using PECL
pecl install redis
```

### Queue Worker Setup

Start the queue worker for background job processing:

```bash
# Start queue worker
php artisan queue:work

# Or use Laravel Horizon
php artisan horizon
```

## Monitoring Setup

### Laravel Telescope

Enable Laravel Telescope for debugging and monitoring:

```bash
# Publish Telescope assets
php artisan telescope:install

# Run migrations
php artisan migrate
```

### Performance Monitoring

Configure performance monitoring:

```bash
# Enable monitoring
php artisan monitoring:enable

# Start monitoring service
php artisan monitoring:start
```

## Testing Setup

### Install Testing Dependencies
```bash
# Install testing dependencies
composer install --dev
```

### Configure Testing Environment
```bash
# Copy testing environment
cp .env.testing.example .env.testing
```

### Run Tests
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
```

## Development Tools

### Code Quality Tools

#### Laravel Pint
```bash
# Check code style
./vendor/bin/pint --test

# Fix code style
./vendor/bin/pint
```

#### PHPStan
```bash
# Run static analysis
./vendor/bin/phpstan analyse
```

#### Rector
```bash
# Run automated refactoring
./vendor/bin/rector process
```

### Development Scripts

Use the provided development scripts:

```bash
# Start development environment
composer run dev

# Run tests
composer run test

# Lint code
composer run lint
```

## Troubleshooting

### Common Issues

#### Permission Issues
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### Database Connection Issues
```bash
# Test database connection
php artisan tinker
DB::connection()->getPdo();
```

#### Queue Issues
```bash
# Clear queue
php artisan queue:clear

# Restart queue worker
php artisan queue:restart
```

#### Cache Issues
```bash
# Clear application cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Debug Information

#### Check Application Status
```bash
# Check application status
php artisan about

# Check environment
php artisan env
```

#### Check Database Status
```bash
# Check database connection
php artisan db:show

# Check migration status
php artisan migrate:status
```

#### Check Queue Status
```bash
# Check queue status
php artisan queue:work --once

# Check failed jobs
php artisan queue:failed
```

## Production Deployment

### Environment Configuration
```bash
# Set production environment
APP_ENV=production
APP_DEBUG=false

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Security Configuration
```bash
# Generate secure application key
php artisan key:generate

# Set secure session configuration
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
```

### Performance Optimization
```bash
# Optimize autoloader
composer install --no-dev --optimize-autoloader

# Build production assets
npm run build
```

## Next Steps

After successful installation:

1. **Create Admin User**: Set up your first admin account
2. **Import Sample Data**: Import sample GEDCOM files
3. **Configure Monitoring**: Set up monitoring and alerting
4. **Review Documentation**: Read the comprehensive documentation
5. **Join Community**: Participate in the LEG community

## Support

If you encounter issues during installation:

1. **Check Logs**: Review Laravel and system logs
2. **Verify Requirements**: Ensure all prerequisites are met
3. **Community Support**: Ask questions in the community
4. **Issue Reporting**: Report bugs through GitHub issues

---

*This installation guide is regularly updated. Last updated: January 2025* 