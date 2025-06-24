#!/bin/bash

# Optimized build script for faster Vite builds

echo "🚀 Starting optimized build process..."

# Set environment variables for faster builds
export NODE_ENV=production
export VITE_CACHE_DIR=/tmp/vite-cache
export NODE_OPTIONS="--max-old-space-size=4096"

# Clear previous build cache (optional)
echo "🧹 Clearing build cache..."
rm -rf public/build
rm -rf node_modules/.vite

# Install dependencies with cache
echo "📦 Installing dependencies..."
npm install --include=dev --cache .npm-cache

# Run optimized build
echo "🔨 Building assets..."
npm run build:fast

# Verify build output
echo "✅ Build completed!"
echo "📊 Build size:"
du -sh public/build/

echo "🎉 Optimized build process completed!" 