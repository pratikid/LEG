import { vi } from 'vitest';
import { config } from '@vue/test-utils';

// Mock global objects
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: vi.fn().mockImplementation(query => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: vi.fn(), // deprecated
    removeListener: vi.fn(), // deprecated
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
    dispatchEvent: vi.fn(),
  })),
});

// Mock ResizeObserver
global.ResizeObserver = vi.fn().mockImplementation(() => ({
  observe: vi.fn(),
  unobserve: vi.fn(),
  disconnect: vi.fn(),
}));

// Mock IntersectionObserver
global.IntersectionObserver = vi.fn().mockImplementation(() => ({
  observe: vi.fn(),
  unobserve: vi.fn(),
  disconnect: vi.fn(),
}));

// Setup global test configuration
config.global.stubs = {
  transition: false,
  'transition-group': false,
};

// Mock Axios
vi.mock('axios', () => ({
  default: {
    get: vi.fn(() => Promise.resolve({ data: {} })),
    post: vi.fn(() => Promise.resolve({ data: {} })),
    put: vi.fn(() => Promise.resolve({ data: {} })),
    delete: vi.fn(() => Promise.resolve({ data: {} })),
    patch: vi.fn(() => Promise.resolve({ data: {} })),
    create: vi.fn(() => ({
      get: vi.fn(() => Promise.resolve({ data: {} })),
      post: vi.fn(() => Promise.resolve({ data: {} })),
      put: vi.fn(() => Promise.resolve({ data: {} })),
      delete: vi.fn(() => Promise.resolve({ data: {} })),
      patch: vi.fn(() => Promise.resolve({ data: {} })),
      interceptors: {
        request: { use: vi.fn() },
        response: { use: vi.fn() },
      },
    })),
  },
}));

// Mock D3.js
vi.mock('d3', () => ({
  select: vi.fn(() => ({
    append: vi.fn(() => ({ attr: vi.fn(), style: vi.fn() })),
    attr: vi.fn(),
    style: vi.fn(),
    selectAll: vi.fn(() => ({ data: vi.fn(), enter: vi.fn(), append: vi.fn() })),
  })),
  scaleLinear: vi.fn(() => ({
    domain: vi.fn(() => ({ range: vi.fn() })),
    range: vi.fn(),
  })),
  scaleBand: vi.fn(() => ({
    domain: vi.fn(() => ({ range: vi.fn() })),
    range: vi.fn(),
    bandwidth: vi.fn(() => 20),
    paddingInner: vi.fn(),
  })),
  axisBottom: vi.fn(),
  axisLeft: vi.fn(),
  max: vi.fn(),
  min: vi.fn(),
}));
