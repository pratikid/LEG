@echo off

REM Stop and remove existing containers
docker-compose down

REM Build and start containers
docker-compose up -d --build

REM Wait for PostgreSQL to be ready
echo Waiting for PostgreSQL to be ready...
timeout /t 2

REM Generate application key
docker-compose exec app php artisan key:generate

REM Run migrations
docker-compose exec app php artisan migrate:fresh

docker-compose exec app php artisan db:seed

REM Set proper permissions
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/storage

echo Setup complete! Your application should now be running with PostgreSQL. 