@echo off

REM Stop and remove existing containers
docker-compose down

REM Build and start containers
docker-compose up -d --build

REM Wait for PostgreSQL to be ready
echo Waiting for PostgreSQL to be ready...
timeout /t 2

REM Install and publish Telescope
docker-compose exec app php artisan telescope:install

REM Generate application key
docker-compose exec app php artisan key:generate

REM Run migrations
docker-compose exec app php artisan migrate:fresh

REM Seed the database
docker-compose exec app php artisan db:seed

REM Set proper permissions
docker-compose exec app chown -R www-data:www-data /var/www/html
docker-compose exec app chmod -R 775 /var/www/html/storage
docker-compose exec app chmod 777 /var/www/html/vendor/bin/pint

REM Run code style check (Laravel Pint)
docker-compose exec app vendor/bin/pint

REM Run Enlightn security/code analysis
REM docker-compose exec app php artisan enlightn

REM Run tests
REM docker-compose exec app php artisan test

echo Setup complete! Your application should now be running with PostgreSQL. 