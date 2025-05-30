#!/bin/bash

# Stop and remove existing containers
docker-compose down

# Build and start containers
docker-compose up -d --build

# Wait for PostgreSQL to be ready
echo "Waiting for PostgreSQL to be ready..."
sleep 10

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate:fresh

# Set proper permissions
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/storage

echo "Setup complete! Your application should now be running with PostgreSQL." 