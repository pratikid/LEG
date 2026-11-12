@echo off
REM Deployment script for Laravel Genealogy application (Windows)
REM This script handles dependency upgrades, testing, and deployment

setlocal enabledelayedexpansion

echo 🚀 Starting Laravel Genealogy Deployment...

REM Parse command line arguments
set SKIP_TESTS=false
set SKIP_BUILD=false
set PRODUCTION_MODE=false

:parse_args
if "%~1"=="" goto start_deployment
if "%~1"=="--skip-tests" (
    set SKIP_TESTS=true
    shift
    goto parse_args
)
if "%~1"=="--skip-build" (
    set SKIP_BUILD=true
    shift
    goto parse_args
)
if "%~1"=="--production" (
    set PRODUCTION_MODE=true
    set APP_ENV=production
    shift
    goto parse_args
)
if "%~1"=="--help" (
    echo Usage: %0 [OPTIONS]
    echo.
    echo Options:
    echo   --skip-tests     Skip running tests
    echo   --skip-build     Skip building frontend assets
    echo   --production     Run in production mode
    echo   --help           Show this help message
    exit /b 0
)
echo Unknown option: %~1
exit /b 1

:start_deployment
echo 🎯 Laravel Genealogy Deployment Script
echo =======================================

if "%PRODUCTION_MODE%"=="true" (
    echo [WARNING] Running in PRODUCTION mode!
)

REM Check dependencies
echo [INFO] Checking system dependencies...

where php >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] PHP is not installed or not in PATH
    exit /b 1
)

where composer >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Composer is not installed or not in PATH
    exit /b 1
)

where node >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Node.js is not installed or not in PATH
    exit /b 1
)

where npm >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] NPM is not installed or not in PATH
    exit /b 1
)

echo [SUCCESS] All required tools are available

REM Create backup
echo [INFO] Creating backup of current state...
set BACKUP_DIR=backups\%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set BACKUP_DIR=%BACKUP_DIR: =0%
mkdir "%BACKUP_DIR%" 2>nul

if exist ".env" copy ".env" "%BACKUP_DIR%\.env.backup" >nul
if exist "composer.lock" copy "composer.lock" "%BACKUP_DIR%\composer.lock.backup" >nul
if exist "package-lock.json" copy "package-lock.json" "%BACKUP_DIR%\package-lock.json.backup" >nul

echo [SUCCESS] Backup created in %BACKUP_DIR%

REM Update PHP dependencies
echo [INFO] Updating PHP dependencies...
composer clear-cache
composer update --no-dev --optimize-autoloader --no-interaction
if %errorlevel% neq 0 (
    echo [ERROR] Failed to update PHP dependencies
    exit /b 1
)

composer install --optimize-autoloader --no-interaction
if %errorlevel% neq 0 (
    echo [ERROR] Failed to install PHP dev dependencies
    exit /b 1
)
echo [SUCCESS] PHP dependencies updated successfully

REM Update Node.js dependencies
echo [INFO] Updating Node.js dependencies...
npm cache clean --force
npm ci
if %errorlevel% neq 0 (
    echo [ERROR] Failed to install Node.js dependencies
    exit /b 1
)
echo [SUCCESS] Node.js dependencies installed successfully

REM Audit dependencies
echo [INFO] Auditing dependencies for security vulnerabilities...
npm audit --audit-level moderate
if %errorlevel% neq 0 (
    echo [WARNING] Some vulnerabilities found, consider running 'npm audit fix'
)

REM Run tests
if "%SKIP_TESTS%"=="false" (
    echo [INFO] Running test suite...
    
    echo [INFO] Running PHP tests...
    composer test
    if %errorlevel% neq 0 (
        echo [ERROR] PHP tests failed
        exit /b 1
    )
    echo [SUCCESS] PHP tests passed
    
    echo [INFO] Running frontend tests...
    npm test
    if %errorlevel% neq 0 (
        echo [ERROR] Frontend tests failed
        exit /b 1
    )
    echo [SUCCESS] Frontend tests passed
    
    echo [INFO] Running static analysis...
    composer analyse
    if %errorlevel% neq 0 (
        echo [WARNING] Static analysis found issues, but continuing...
    ) else (
        echo [SUCCESS] Static analysis passed
    )
    
    echo [INFO] Running code linting...
    composer lint
    if %errorlevel% neq 0 (
        echo [WARNING] Code style issues found, but continuing...
    )
    
    npm run lint
    if %errorlevel% neq 0 (
        echo [WARNING] Frontend linting issues found, but continuing...
    )
) else (
    echo [WARNING] Skipping tests as requested
)

REM Build assets
if "%SKIP_BUILD%"=="false" (
    echo [INFO] Building frontend assets...
    npm run build
    if %errorlevel% neq 0 (
        echo [ERROR] Failed to build assets
        exit /b 1
    )
    echo [SUCCESS] Assets built successfully
    
    echo [INFO] Analyzing bundle size...
    npm run build:analyze
    if %errorlevel% neq 0 (
        echo [WARNING] Bundle analysis failed, but continuing...
    )
) else (
    echo [WARNING] Skipping asset build as requested
)

REM Clear caches
echo [INFO] Clearing application caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo [SUCCESS] Caches cleared

REM Run migrations
echo [INFO] Running database migrations...
php artisan migrate --force
if %errorlevel% neq 0 (
    echo [ERROR] Database migrations failed
    exit /b 1
)
echo [SUCCESS] Database migrations completed

REM Optimize application
echo [INFO] Optimizing application...
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

if "%APP_ENV%"=="production" (
    php artisan optimize
    echo [SUCCESS] Production optimizations applied
)

REM Health check
echo [INFO] Running health checks...
php artisan --version >nul
if %errorlevel% neq 0 (
    echo [ERROR] Laravel application is not responding
    exit /b 1
)
echo [SUCCESS] Laravel application is responsive

php artisan migrate:status >nul
if %errorlevel% neq 0 (
    echo [ERROR] Database connection failed
    exit /b 1
)
echo [SUCCESS] Database connection is working

echo.
echo 🎉 Deployment completed successfully!
echo =======================================

REM Final summary
echo [SUCCESS] ✅ Dependencies updated
if "%SKIP_TESTS%"=="true" (
    echo [SUCCESS] ✅ Tests skipped
) else (
    echo [SUCCESS] ✅ Tests passed
)
if "%SKIP_BUILD%"=="true" (
    echo [SUCCESS] ✅ Assets skipped
) else (
    echo [SUCCESS] ✅ Assets built
)
echo [SUCCESS] ✅ Database migrated
echo [SUCCESS] ✅ Application optimized
echo [SUCCESS] ✅ Health checks passed

echo.
echo [INFO] Your Laravel Genealogy application is ready!

if "%PRODUCTION_MODE%"=="false" (
    echo.
    echo [INFO] To start the development server, run:
    echo php artisan serve
    echo.
    echo [INFO] To start the queue worker, run:
    echo php artisan queue:work
)

endlocal
pause
