#!/bin/bash

# Create necessary directories
# mkdir -p docker/nginx/conf.d

# Start Docker containers
docker-compose up -d

# Create a new Laravel project
docker-compose exec app composer create-project laravel/laravel .

# Install Livewire
docker-compose exec app composer require livewire/livewire

# Install Alpine.js via npm
docker-compose exec node npm install alpinejs

# Install tailwindcss
docker-compose exec node npm install -D tailwindcss postcss autoprefixer
docker-compose exec node npx tailwindcss init -p

# Generate application key
docker-compose exec app php artisan key:generate

# Install Neo4j client
docker-compose exec app composer require laudis/neo4j-php-client

# Install MongoDB Laravel package
docker-compose exec app composer require mongodb/laravel-mongodb

# Install Redis Laravel package
docker-compose exec app composer require predis/predis

# Install dependencies for D3.js
docker-compose exec node npm install d3

# Give proper permissions
docker-compose exec app chmod -R 777 storage bootstrap/cache

echo "Setup complete! Your Laravel application with Livewire and Alpine.js is ready."
echo "Access your application at http://localhost" 