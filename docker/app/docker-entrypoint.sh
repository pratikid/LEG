#!/bin/bash
set -e

# Ensure correct permissions for storage (fixes issues with bind mounts)
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

# Ensure correct permissions for logs directory and log file
mkdir -p /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage/logs
chmod -R 775 /var/www/html/storage/logs
touch /var/www/html/storage/logs/laravel.log
chown www-data:www-data /var/www/html/storage/logs/laravel.log
chmod 664 /var/www/html/storage/logs/laravel.log

# Handle composer dependencies
if [ ! -d "vendor" ]; then
    echo "Running composer install..."
    composer update --no-interaction --no-progress --prefer-dist --ignore-platform-reqs
fi

# Ensure storage directory is writable
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R g+s storage bootstrap/cache

# Set up environment file if it doesn't exist
if [ ! -f ".env" ]; then
    echo "Creating .env file..."
    cp .env.example .env
    php artisan key:generate
fi

# Run database migrations if needed
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

# Clear Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Start PHP-FPM
exec "$@" 