# GEDCOM Import Optimization

## Overview

This document describes the optimized GEDCOM import implementation that provides enhanced performance, memory management, and comprehensive monitoring capabilities for large genealogical data imports.

## Key Features

### 1. Dual Import Methods
- **Standard Import**: Traditional multi-database approach with ACID compliance
- **Optimized Import**: Parallel processing with memory optimization for large files

### 2. Performance Tracking
- Real-time metrics collection during import
- Memory usage monitoring
- Duration and throughput analysis
- Success rate tracking

### 3. Admin Dashboard
- Live performance metrics visualization
- Method comparison charts
- Recent import history
- Performance trend analysis

## Architecture

### Core Components

#### Import Services
```php
// Standard Import Service
GedcomMultiDatabaseService::class
- Multi-database architecture (PostgreSQL, MongoDB, Neo4j)
- ACID compliance for data integrity
- Sequential processing for reliability

// Optimized Import Service  
GedcomImportOptimizer::class
- Parallel processing capabilities
- Memory optimization for large files
- Enhanced throughput for bulk imports
```

#### Performance Tracking
```php
// Performance Metrics Service
ImportPerformanceTracker::class
- Tracks import duration, memory usage, throughput
- Stores metrics in Redis for real-time access
- Provides aggregated data for dashboards

// API Controller
ImportMetricsController::class
- RESTful endpoints for metrics data
- Grafana-compatible time series data
- Method comparison analytics
```

#### Job Processing
```php
// Enhanced Import Job
ImportGedcomJob::class
- Supports method selection (standard/optimized)
- Comprehensive error handling and logging
- Performance metrics collection
- User notification system
```

## Implementation Details

### 1. Import Method Selection

Users can choose their preferred import method through the enhanced import form:

```html
<select name="import_method" id="import_method" class="form-select">
    <option value="standard">Standard Import (Multi-Database)</option>
    <option value="optimized">Optimized Import (Parallel Processing)</option>
</select>
```

### 2. Job Processing Enhancement

The `ImportGedcomJob` now accepts an import method parameter:

```php
public function __construct(
    protected string $filePath,
    protected int $treeId,
    protected int $userId,
    protected ?string $originalFileName = null,
    protected string $importMethod = 'standard'
) {
    $this->onQueue('imports');
}
```

### 3. Service Routing

The job routes to the appropriate service based on the selected method:

```php
// Use the appropriate service based on import method
if ($this->importMethod === 'optimized') {
    $gedcomService = app(GedcomImportOptimizer::class);
    $importResults = $gedcomService->importGedcomData($content, $this->treeId);
} else {
    $gedcomService = app(GedcomMultiDatabaseService::class);
    $importResults = $gedcomService->importGedcomData($content, $this->treeId);
}
```

### 4. Performance Metrics Collection

Comprehensive metrics are collected during import:

```php
$startTime = microtime(true);
$initialMemory = memory_get_usage();

// ... import processing ...

$endTime = microtime(true);
$duration = round($endTime - $startTime, 2);
$memoryUsed = memory_get_usage() - $initialMemory;

// Track performance metrics
$performanceTracker = app(ImportPerformanceTracker::class);
$performanceTracker->trackImportMetrics(
    $this->importMethod,
    $this->treeId,
    $this->userId,
    array_merge($importResults, ['memory_used_mb' => round($memoryUsed / 1024 / 1024, 2)]),
    $duration,
    $fileSize,
    $totalRecords
);
```

## API Endpoints

### Import Metrics API

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/import-metrics/summary` | GET | Overall performance summary |
| `/api/v1/import-metrics/comparison` | GET | Method comparison data |
| `/api/v1/import-metrics/recent` | GET | Recent import metrics |
| `/api/v1/import-metrics/method` | GET | Method-specific metrics |
| `/api/v1/import-metrics/aggregated` | GET | Grafana-compatible time series |

### Example API Response

```json
{
  "success": true,
  "data": {
    "total_imports": {
      "standard": 150,
      "optimized": 75
    },
    "success_rates": {
      "standard": 98.5,
      "optimized": 99.2
    },
    "performance_improvements": {
      "duration": -25.5,
      "throughput": 34.2
    },
    "average_metrics": {
      "standard": {
        "duration": 45.2,
        "throughput": 22.1,
        "memory": 125.5
      },
      "optimized": {
        "duration": 33.7,
        "throughput": 29.7,
        "memory": 98.3
      }
    }
  }
}
```

## Admin Dashboard

### Access
Navigate to `/admin/import-metrics` to view the performance dashboard.

### Features
- **Real-time Metrics**: Live updates every 30 seconds
- **Method Comparison**: Side-by-side performance comparison
- **Recent Imports**: Table of recent import attempts
- **Performance Trends**: Visual representation of improvements

### Dashboard Components

1. **Summary Cards**: Key metrics at a glance
2. **Method Comparison**: Detailed metrics for each method
3. **Recent Imports Table**: Individual import results
4. **Performance Charts**: Visual trend analysis

## Monitoring Integration

### Neo4j Prometheus Metrics
The system now includes Neo4j Prometheus metrics endpoint:

```yaml
# docker-compose.yml
neo4j:
  ports:
    - "2004:2004" # Prometheus metrics
  environment:
    NEO4J_metrics_prometheus_enabled: "true"
    NEO4J_metrics_prometheus_endpoint: ":2004"
```

### Grafana Dashboard
Pre-configured dashboard (`import-performance-dashboard.json`) includes:

- Import duration comparison
- Throughput analysis
- Success rate tracking
- Total imports by method
- Performance improvement metrics

## Configuration

### Cache Settings
Performance metrics are cached with configurable TTL:

```php
private const METRICS_CACHE_TTL = 86400; // 24 hours
```

### Memory Limits
The optimized import method includes memory management:

```php
private const MAX_MEMORY_USAGE = 512 * 1024 * 1024; // 512MB
```

## Testing

### Test Coverage
The implementation includes comprehensive tests in `ImportMethodComparisonTest.php`:

- Method selection validation
- Import job routing
- Performance tracking functionality
- API endpoint accessibility
- Admin dashboard access

### Running Tests
```bash
php artisan test tests/Feature/ImportMethodComparisonTest.php
```

## Monitoring and Alerts

### Key Metrics to Monitor
1. **Success Rate**: Should remain above 95%
2. **Performance Improvement**: Track relative performance gains
3. **Memory Usage**: Monitor for memory leaks
4. **Queue Processing**: Ensure jobs are processed timely

### Alert Thresholds
- Success rate drops below 95%
- Memory usage exceeds 80% of limit
- Import duration increases by 50% from baseline
- Queue processing delay exceeds 5 minutes

## Best Practices

### For Standard Imports
- Use for critical data requiring ACID compliance
- Suitable for smaller files (< 10MB)
- Provides maximum data integrity

### For Optimized Imports
- Use for large files (> 10MB)
- Provides better performance and throughput
- Suitable for bulk data imports

### Performance Optimization
- Monitor memory usage during large imports
- Use appropriate queue workers for import jobs
- Implement proper error handling and retry logic
- Regular cleanup of temporary files and cache

## Troubleshooting

### Common Issues

1. **Import Method Not Available**
   - Ensure both services are properly registered
   - Check service provider configuration

2. **Performance Metrics Missing**
   - Verify Redis connection for metrics storage
   - Check ImportPerformanceTracker service registration

3. **Admin Dashboard Not Loading**
   - Ensure admin middleware is configured
   - Check route registration for import metrics

4. **Memory Issues During Import**
   - Monitor memory usage in logs
   - Consider reducing batch sizes for large files
   - Implement memory cleanup in import services 