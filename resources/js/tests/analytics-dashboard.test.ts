import { describe, expect, it } from 'vitest';
import AnalyticsDashboard from '../analytics-dashboard.js';

describe('AnalyticsDashboard formatting', () => {
  const dashboard = Object.create(AnalyticsDashboard.prototype) as AnalyticsDashboard;

  it('formats numbers using compact units', () => {
    expect(dashboard.formatNumber(999)).toBe('999');
    expect(dashboard.formatNumber(1_000)).toBe('1.0K');
    expect(dashboard.formatNumber(1_000_000)).toBe('1.0M');
  });

  it('formats object tooltips as key-value pairs', () => {
    expect(dashboard.formatTooltip({ count: 3, label: 'Sample' }))
      .toBe('count: 3<br>label: Sample');
  });

  it('formats primitive tooltips as strings', () => {
    expect(dashboard.formatTooltip(42)).toBe('42');
  });
});
