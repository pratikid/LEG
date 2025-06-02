#!/bin/sh
set -e

# Detect user based on process (php-fpm or nginx)
if echo "$1" | grep -q "php-fpm"; then
  USER=www-data
elif echo "$1" | grep -q "nginx"; then
  USER=nginx
else
  USER=www-data  # Fallback
fi

# Ensure writable directories exist
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/bootstrap/cache

# Set permissions for Laravel writable directories
chown -R $USER:$USER /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# For development on Windows, you may need to relax permissions:
# chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

exec "$@" 