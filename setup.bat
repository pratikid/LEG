@echo off
REM ===================================================================
REM LEG Project Setup Script - Windows
REM ===================================================================
REM This script sets up the entire LEG development environment using
REM ONLY Docker containers. No local PHP, Node.js, or Composer required!
REM Prerequisites: Docker Desktop for Windows
REM ===================================================================

echo Starting LEG project setup (100%% Docker-based)...
echo All tools run in containers - no local installation required!
echo.

echo [1/8] Creating necessary directories...
mkdir docker\nginx\conf.d 2>nul

echo [2/8] Starting Docker containers (this may take a few minutes)...
docker compose up -d

echo [3/8] Installing PHP dependencies via Composer (in app container)...
docker compose exec app composer install

echo [4/8] Generating Laravel application key...
docker compose exec app php artisan key:generate

echo [5/8] Installing Node.js dependencies via npm (in node container)...
docker compose exec node npm install

echo [6/8] Running database migrations and seeding...
docker compose exec app php artisan migrate --seed

echo [7/8] Building frontend assets...
docker compose exec node npm run build:fast

echo [8/8] Final setup and permissions...
docker compose exec app /usr/local/bin/fix-permissions.sh

echo.
echo ===============================================================
echo Setup complete! LEG development environment is ready.
echo.
echo Access points:
echo - Application: http://localhost
echo - Neo4j Browser: http://localhost:7474 (neo4j/password123)
echo - Grafana Dashboard: http://localhost:3000 (admin/admin)
echo.
echo All commands run through Docker - see WARP.md for examples:
echo   docker compose exec app php artisan migrate
echo   docker compose exec node npm run dev
echo   docker compose logs -f app
echo ===============================================================
