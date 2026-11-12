#!/bin/bash

# ===================================================================
# LEG Project Setup Script - Linux/macOS
# ===================================================================
# This script sets up the entire LEG development environment using
# ONLY Docker containers. No local PHP, Node.js, or Composer required!
# Prerequisites: Docker and Docker Compose
# ===================================================================

set -e  # Exit on any error

echo "🚀 Starting LEG project setup (100% Docker-based)..."
echo "All tools run in containers - no local installation required!"
echo ""

# Create necessary directories
echo "[1/8] Creating necessary directories..."
mkdir -p docker/nginx/conf.d

# Start Docker containers
echo "[2/8] Starting Docker containers (this may take a few minutes)..."
docker compose up -d

# Wait for containers to be ready
echo "[2.5/8] Waiting for containers to be healthy..."
sleep 10

# Install PHP dependencies
echo "[3/8] Installing PHP dependencies via Composer (in app container)..."
docker compose exec app composer install

# Generate application key
echo "[4/8] Generating Laravel application key..."
docker compose exec app php artisan key:generate

# Install Node.js dependencies
echo "[5/8] Installing Node.js dependencies via npm (in node container)..."
docker compose exec node npm install

# Run database migrations and seeding
echo "[6/8] Running database migrations and seeding..."
docker compose exec app php artisan migrate --seed

# Build frontend assets
echo "[7/8] Building frontend assets..."
docker compose exec node npm run build:fast

# Fix permissions
echo "[8/8] Final setup and permissions..."
docker compose exec app /usr/local/bin/fix-permissions.sh

echo ""
echo "==============================================================="
echo "✅ Setup complete! LEG development environment is ready."
echo ""
echo "🌐 Access points:"
echo "   - Application: http://localhost"
echo "   - Neo4j Browser: http://localhost:7474 (neo4j/password123)"
echo "   - Grafana Dashboard: http://localhost:3000 (admin/admin)"
echo ""
echo "🐳 All commands run through Docker - see WARP.md for examples:"
echo "   docker compose exec app php artisan migrate"
echo "   docker compose exec node npm run dev"
echo "   docker compose logs -f app"
echo "==============================================================="
