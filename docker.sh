#!/bin/bash

# Stop and remove existing containers
docker-compose down

# Build and start containers
docker-compose up -d --build

# Wait for PostgreSQL to be ready
echo "Waiting for PostgreSQL to be ready..."
sleep 2

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate:fresh

docker-compose exec app php artisan db:seed

# Set proper permissions
docker-compose exec app chown -R www-data:www-data /var/www/html
docker-compose exec app chmod -R 775 /var/www/html/storage
docker-compose exec app chmod 777 /var/www/html/vendor/bin/pint

# Run code style check (Laravel Pint)
docker-compose exec app vendor/bin/pint

# Run Enlightn security/code analysis
# docker-compose exec app php artisan enlightn

# Run tests
# docker-compose exec app php artisan test

echo "Setup complete! Your application should now be running with PostgreSQL." 