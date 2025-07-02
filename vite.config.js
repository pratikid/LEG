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
    
    // Aggressive build optimizations
    build: {
        // Disable source maps in production for faster builds
        sourcemap: false,
        
        // Use esbuild for faster minification
        minify: 'esbuild',
        
        // Optimize CSS
        cssMinify: 'esbuild',
        
        // Increase chunk size warning limit
        chunkSizeWarningLimit: 2000,
        
        // Enable build cache for faster rebuilds
        cache: true,
        
        // Optimize rollup options
        rollupOptions: {
            output: {
                // Optimize chunking
                manualChunks: {
                    vendor: ['axios'],
                },
                // Optimize asset naming
                assetFileNames: (assetInfo) => {
                    const info = assetInfo.name.split('.');
                    const ext = info[info.length - 1];
                    if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(ext)) {
                        return `assets/images/[name]-[hash][extname]`;
                    }
                    return `assets/[name]-[hash][extname]`;
                },
                chunkFileNames: 'assets/[name]-[hash].js',
                entryFileNames: 'assets/[name]-[hash].js',
            },
        },
        
        // Optimize target
        target: 'es2015',
        
        // Optimize CSS code splitting
        cssCodeSplit: true,
        
        // Optimize assets
        assetsInlineLimit: 4096,
    },
    
    // Optimize dependencies
    optimizeDeps: {
        include: ['axios'],
        exclude: ['d3'], // Exclude D3 as it's not used in main bundle
        // Force pre-bundling for faster builds
        force: true,
    },
    
    // CSS optimization
    css: {
        devSourcemap: false,
    },
    
    // Optimize esbuild
    esbuild: {
        target: 'es2015',
        // Optimize for speed
        minifyIdentifiers: false,
        minifySyntax: true,
        minifyWhitespace: true,
    },
    
    // Optimize worker
    worker: {
        format: 'es',
        plugins: () => [],
    },
    
    // Optimize resolve
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    
    // Optimize define
    define: {
        __VUE_OPTIONS_API__: false,
        __VUE_PROD_DEVTOOLS__: false,
    },
});
