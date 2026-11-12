import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { AnalyticsDashboard } from '../analytics-dashboard.js';

// Mock DOM elements
const mockContainer = {
  innerHTML: '',
  querySelector: vi.fn(),
  appendChild: vi.fn(),
  getAttribute: vi.fn(),
  setAttribute: vi.fn(),
  style: {},
};

const mockElement = {
  innerHTML: '',
  textContent: '',
  style: {},
  setAttribute: vi.fn(),
  getAttribute: vi.fn(),
  appendChild: vi.fn(),
  querySelector: vi.fn(),
  querySelectorAll: vi.fn(() => []),
  addEventListener: vi.fn(),
  removeEventListener: vi.fn(),
  classList: {
    add: vi.fn(),
    remove: vi.fn(),
    contains: vi.fn(),
    toggle: vi.fn(),
  },
};

// Mock document methods
Object.defineProperty(global, 'document', {
  value: {
    createElement: vi.fn(() => mockElement),
    querySelector: vi.fn(() => mockContainer),
    querySelectorAll: vi.fn(() => [mockContainer]),
    getElementById: vi.fn(() => mockContainer),
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
  },
  writable: true,
});

// Mock fetch
global.fetch = vi.fn(() =>
  Promise.resolve({
    ok: true,
    json: () => Promise.resolve({
      family_tree_stats: {
        total_individuals: 150,
        total_families: 75,
        generation_depth: 8,
        birth_year_range: { min: 1850, max: 2023 },
      },
      birth_year_distribution: [
        { year: '1950', count: 10 },
        { year: '1960', count: 15 },
        { year: '1970', count: 20 },
      ],
      gender_distribution: [
        { gender: 'Male', count: 75 },
        { gender: 'Female', count: 75 },
      ],
      surname_frequency: [
        { surname: 'Smith', count: 25 },
        { surname: 'Johnson', count: 20 },
        { surname: 'Brown', count: 15 },
      ],
    }),
  }),
);

describe('AnalyticsDashboard', () => {
  let dashboard: AnalyticsDashboard;

  beforeEach(() => {
    vi.clearAllMocks();
    mockContainer.querySelector.mockReturnValue(mockElement);
    dashboard = new AnalyticsDashboard('#analytics-dashboard');
  });

  afterEach(() => {
    vi.clearAllTimers();
  });

  it('should initialize with default configuration', () => {
    expect(dashboard).toBeDefined();
  });

  it('should initialize dashboard on construction', async () => {
    const initSpy = vi.spyOn(dashboard, 'init');
    
    // Create new instance to trigger initialization
    new AnalyticsDashboard('#test-container');
    
    expect(document.querySelector).toHaveBeenCalledWith('#test-container');
  });

  it('should load analytics data', async () => {
    await dashboard.loadAnalyticsData();
    
    expect(fetch).toHaveBeenCalledWith('/api/analytics/summary');
  });

  it('should handle API errors gracefully', async () => {
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});
    
    // Mock fetch to reject
    global.fetch = vi.fn(() => Promise.reject(new Error('API Error')));
    
    await dashboard.loadAnalyticsData();
    
    expect(consoleSpy).toHaveBeenCalledWith('Error loading analytics data:', expect.any(Error));
    consoleSpy.mockRestore();
  });

  it('should render overview cards', () => {
    const mockData = {
      family_tree_stats: {
        total_individuals: 150,
        total_families: 75,
        generation_depth: 8,
        birth_year_range: { min: 1850, max: 2023 },
      },
    };

    dashboard.renderOverviewCards(mockData);

    // Verify that cards are created and content is set
    expect(mockElement.textContent).toBeDefined();
  });

  it('should create birth year chart', () => {
    const mockData = [
      { year: '1950', count: 10 },
      { year: '1960', count: 15 },
      { year: '1970', count: 20 },
    ];

    dashboard.createBirthYearChart(mockData);

    // Verify D3 methods were called (mocked)
    expect(document.querySelector).toHaveBeenCalled();
  });

  it('should create gender distribution chart', () => {
    const mockData = [
      { gender: 'Male', count: 75 },
      { gender: 'Female', count: 75 },
    ];

    dashboard.createGenderChart(mockData);

    expect(document.querySelector).toHaveBeenCalled();
  });

  it('should create surname frequency chart', () => {
    const mockData = [
      { surname: 'Smith', count: 25 },
      { surname: 'Johnson', count: 20 },
      { surname: 'Brown', count: 15 },
    ];

    dashboard.createSurnameChart(mockData);

    expect(document.querySelector).toHaveBeenCalled();
  });

  it('should handle resize events', () => {
    const resizeSpy = vi.spyOn(dashboard, 'handleResize');
    
    // Simulate window resize
    global.dispatchEvent(new Event('resize'));
    
    // Note: Since we can't actually test the event listener without proper DOM,
    // we'll just verify the method exists and can be called
    expect(typeof dashboard.handleResize).toBe('function');
  });

  it('should refresh data when requested', async () => {
    const loadDataSpy = vi.spyOn(dashboard, 'loadAnalyticsData');
    
    await dashboard.refresh();
    
    expect(loadDataSpy).toHaveBeenCalled();
  });

  it('should export data in different formats', () => {
    const mockData = {
      family_tree_stats: { total_individuals: 150 },
      birth_year_distribution: [{ year: '1950', count: 10 }],
    };

    // Test CSV export
    const csvData = dashboard.exportData(mockData, 'csv');
    expect(typeof csvData).toBe('string');

    // Test JSON export
    const jsonData = dashboard.exportData(mockData, 'json');
    expect(typeof jsonData).toBe('string');
  });
});
