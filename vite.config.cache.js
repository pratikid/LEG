import { defineConfig } from 'vite';

// Cache configuration for faster builds
export default defineConfig({
    // Optimize cache settings
    cacheDir: 'node_modules/.vite',
    
    // Optimize dependency pre-bundling
    optimizeDeps: {
        // Force pre-bundling of common dependencies
        force: true,
        // Include only necessary dependencies
        include: ['axios'],
        // Exclude problematic dependencies
        exclude: [],
    },
    
    // Optimize build cache
    build: {
        // Enable build cache for faster rebuilds
        cache: true,
        // Optimize chunk splitting
        rollupOptions: {
            cache: true,
        },
    },
    
    // Optimize CSS processing
    css: {
        // Enable CSS source maps only in development
        devSourcemap: process.env.NODE_ENV === 'development',
    },
}); 