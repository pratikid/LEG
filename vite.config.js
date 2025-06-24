import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
        cors: true,
        https: false,
        hmr: {
            host: 'localhost',
            protocol: 'ws',
            port: 5173,
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            buildDirectory: 'build',
        }),
        tailwindcss(),
    ],
    base: process.env.NODE_ENV === 'production' ? '/' : 'http://localhost:5173/',
    
    // Build optimizations
    build: {
        // Enable source maps for debugging (disable for production)
        sourcemap: process.env.NODE_ENV !== 'production',
        
        // Optimize chunk size
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['axios', 'alpinejs'],
                    d3: ['d3'],
                },
            },
        },
        
        // Enable minification
        minify: 'terser',
        
        // Optimize CSS
        cssMinify: true,
        
        // Set chunk size warning limit
        chunkSizeWarningLimit: 1000,
        
        // Enable build cache
        cache: true,
    },
    
    // Optimize dependencies
    optimizeDeps: {
        include: ['axios', 'alpinejs', 'd3'],
        exclude: [],
    },
    
    // CSS optimization
    css: {
        devSourcemap: process.env.NODE_ENV !== 'production',
    },
    
    // Worker configuration for parallel processing
    worker: {
        format: 'es',
    },
    
    // Enable esbuild for faster builds
    esbuild: {
        target: 'es2020',
    },
});
