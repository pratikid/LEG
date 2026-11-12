/// <reference types="vitest" />
import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
  plugins: [vue()],
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./resources/js/tests/setup.ts'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html', 'lcov'],
      reportsDirectory: './coverage/frontend',
      exclude: [
        'node_modules/**',
        'public/**',
        'vendor/**',
        '**/*.d.ts',
        'vite.config.ts',
        'vitest.config.ts',
      ],
    },
    css: true,
    includeSource: ['resources/js/**/*.{js,ts,vue}'],
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
      '~': path.resolve(__dirname, 'resources'),
      vue: 'vue/dist/vue.esm-bundler.js',
    },
  },
  define: {
    __VUE_OPTIONS_API__: false,
  },
});
