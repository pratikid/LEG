#!/bin/sh

# Docker entrypoint script for Node.js build process

set -e

echo "🚀 Starting Node.js build process..."

# Set environment variables
export NODE_ENV=production
export NODE_OPTIONS="--max-old-space-size=4096"
export VITE_CACHE_DIR=/tmp/vite-cache

# Create cache directories
mkdir -p /tmp/vite-cache
mkdir -p .npm-cache

# Install npm globally if needed
if ! command -v npm &> /dev/null; then
    echo "📦 Installing npm globally..."
    npm install -g npm@11.4.2
fi

# Install dependencies
echo "📦 Installing dependencies..."
npm install --include=dev --cache .npm-cache

# Run the build
echo "🔨 Building assets..."
npm run build:fast

# Verify build output
echo "✅ Build completed!"
echo "📊 Build size:"
du -sh public/build/ 2>/dev/null || echo "Build directory not found"

echo "🎉 Node.js build process completed!" 