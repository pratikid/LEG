# LEG API SDK & Integration Guide

Welcome to the LEG (Lineage Exploration & Genealogy) API SDK documentation. This guide provides comprehensive information for developers integrating with the LEG platform.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Authentication](#authentication)
3. [SDK Installation](#sdk-installation)
4. [Core Concepts](#core-concepts)
5. [API Endpoints](#api-endpoints)
6. [Code Examples](#code-examples)
7. [Error Handling](#error-handling)
8. [Rate Limiting](#rate-limiting)
9. [Webhooks](#webhooks)
10. [Best Practices](#best-practices)
11. [Troubleshooting](#troubleshooting)

## Getting Started

The LEG API provides programmatic access to genealogical data, analytics, and advanced family tree operations. Our RESTful API supports JSON data interchange and follows OpenAPI 3.0 specifications.

### Base URL
```
Production: https://api.leg-genealogy.com/v1
Staging: https://staging-api.leg-genealogy.com/v1
```

### API Versioning
We use URL-based versioning. The current stable version is `v1`. Version-specific changes are documented in our [changelog](./changelog.md).

## Authentication

### API Keys
LEG uses API key authentication for programmatic access. Obtain your API key from the [developer dashboard](https://app.leg-genealogy.com/developers).

```http
Authorization: Bearer your-api-key-here
Content-Type: application/json
```

### OAuth 2.0 (Coming Soon)
For applications requiring user-level permissions, OAuth 2.0 will be available.

### Rate Limiting
- **Free tier**: 100 requests/hour
- **Basic tier**: 1,000 requests/hour  
- **Premium tier**: 10,000 requests/hour
- **Professional tier**: 100,000 requests/hour

## SDK Installation

### PHP SDK
```bash
composer require leg/genealogy-sdk
```

### JavaScript/Node.js SDK
```bash
npm install @leg/genealogy-sdk
```

### Python SDK
```bash
pip install leg-genealogy-sdk
```

### PHP Example
```php
<?php

use Leg\GenealogySDK\Client;

$client = new Client('your-api-key');

// Get family tree information
$tree = $client->trees()->get(123);

// Find relationship between individuals
$relationship = $client->relationships()->find(456, 789);

// Generate family tree statistics
$stats = $client->analytics()->treeStatistics(123);
```

### JavaScript Example
```javascript
import { LegClient } from '@leg/genealogy-sdk';

const client = new LegClient('your-api-key');

// Async/await syntax
async function example() {
    // Get family tree
    const tree = await client.trees.get(123);
    
    // Find common ancestors
    const ancestors = await client.graph.commonAncestors(456, 789);
    
    // Generate reports
    const report = await client.reports.generate('family-tree', {
        treeId: 123,
        format: 'pdf'
    });
}
```

### Python Example
```python
from leg_genealogy_sdk import LegClient

client = LegClient('your-api-key')

# Get family tree
tree = client.trees.get(123)

# Calculate relationship statistics
stats = client.analytics.tree_statistics(123)

# Export anonymized data for research
dataset = client.research.export_anonymized([123, 456], {
    'privacy_level': 'k-anonymity-5',
    'format': 'json'
})
```

## Core Concepts

### Trees
Family trees are the primary organizational unit in LEG. Each tree contains individuals, families, and relationships.

### Individuals
People in your genealogical research, with biographical data, events, and relationships.

### Relationships
Connections between individuals including parent-child, spouse, and sibling relationships.

### Events
Life events such as birth, death, marriage, and other significant occurrences.

### Privacy Settings
Granular controls for data visibility and sharing, including living person protection.

## API Endpoints

### Trees API

#### List Trees
```http
GET /trees
```

#### Get Tree Details
```http
GET /trees/{id}
```

#### Create Tree
```http
POST /trees
Content-Type: application/json

{
    "name": "Smith Family Tree",
    "description": "Research on the Smith family lineage"
}
```

#### Get Tree Statistics
```http
GET /trees/{id}/statistics
```

#### Export Tree (GEDCOM)
```http
GET /trees/{id}/export?format=gedcom
```

### Individuals API

#### List Individuals
```http
GET /individuals?tree_id={tree_id}
```

#### Get Individual Details
```http
GET /individuals/{id}
```

#### Get Individual Relationships
```http
GET /individuals/{id}/relationships
```

#### Get Individual Ancestors
```http
GET /individuals/{id}/ancestors?generations=5
```

#### Get Individual Descendants
```http
GET /individuals/{id}/descendants?generations=5
```

### Graph Analytics API

#### Find Relationship Path
```http
POST /graph/relationship-path
Content-Type: application/json

{
    "individual_a": 123,
    "individual_b": 456
}
```

#### Find Common Ancestors
```http
POST /graph/common-ancestors
Content-Type: application/json

{
    "individual_a": 123,
    "individual_b": 456
}
```

#### Calculate Coefficient of Relationship
```http
POST /graph/coefficient-relationship
Content-Type: application/json

{
    "individual_a": 123,
    "individual_b": 456
}
```

### Analytics API

#### Get Dashboard Metrics
```http
GET /analytics/dashboard
```

#### Get Import Metrics
```http
GET /analytics/import-metrics?days=30
```

#### Get Tree Insights
```http
GET /analytics/tree/{id}/insights
```

### Reporting API

#### Generate Family Tree Report
```http
POST /reports/family-tree-chart
Content-Type: application/json

{
    "tree_id": 123,
    "format": "pdf",
    "orientation": "landscape",
    "include_photos": true,
    "generations": 4
}
```

#### Generate Ancestor Report
```http
POST /reports/ancestor-report
Content-Type: application/json

{
    "individual_id": 456,
    "generations": 5,
    "format": "pdf",
    "include_siblings": true
}
```

#### Get Report Status
```http
GET /reports/{report_id}
```

#### Download Report
```http
GET /reports/{report_id}/download
```

### Research Data API

#### Request Anonymized Dataset
```http
POST /research/dataset/request
Content-Type: application/json

{
    "tree_ids": [123, 456],
    "privacy_level": "k-anonymity-5",
    "format": "json",
    "temporal_aggregation": "5-year",
    "geographic_generalization": "country"
}
```

## Error Handling

### Error Response Format
```json
{
    "error": {
        "code": "RESOURCE_NOT_FOUND",
        "message": "The requested tree was not found",
        "details": {
            "tree_id": 999,
            "suggestion": "Check that the tree ID is correct and you have access permissions"
        },
        "documentation_url": "https://docs.leg-genealogy.com/errors#resource-not-found"
    },
    "request_id": "req_1234567890"
}
```

### Common Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `AUTHENTICATION_REQUIRED` | 401 | API key missing or invalid |
| `PERMISSION_DENIED` | 403 | Insufficient permissions |
| `RESOURCE_NOT_FOUND` | 404 | Requested resource doesn't exist |
| `VALIDATION_ERROR` | 422 | Request validation failed |
| `RATE_LIMIT_EXCEEDED` | 429 | API rate limit exceeded |
| `INTERNAL_ERROR` | 500 | Server error occurred |

### PHP Error Handling
```php
<?php

try {
    $tree = $client->trees()->get(999);
} catch (Leg\GenealogySDK\Exceptions\ResourceNotFoundException $e) {
    echo "Tree not found: " . $e->getMessage();
} catch (Leg\GenealogySDK\Exceptions\RateLimitException $e) {
    echo "Rate limit exceeded. Retry after: " . $e->getRetryAfter();
} catch (Leg\GenealogySDK\Exceptions\ApiException $e) {
    echo "API error: " . $e->getCode() . " - " . $e->getMessage();
}
```

### JavaScript Error Handling
```javascript
try {
    const tree = await client.trees.get(999);
} catch (error) {
    if (error.code === 'RESOURCE_NOT_FOUND') {
        console.log('Tree not found:', error.message);
    } else if (error.code === 'RATE_LIMIT_EXCEEDED') {
        console.log('Rate limited. Retry after:', error.retryAfter);
    } else {
        console.log('API error:', error.message);
    }
}
```

## Rate Limiting

### Headers
Rate limit information is included in response headers:

```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1609459200
X-RateLimit-Window: 3600
```

### Handling Rate Limits
```php
<?php

// Check rate limit headers
$response = $client->getLastResponse();
$remaining = $response->getHeader('X-RateLimit-Remaining');
$reset = $response->getHeader('X-RateLimit-Reset');

if ($remaining < 10) {
    $waitTime = $reset - time();
    echo "Rate limit low. Reset in {$waitTime} seconds";
}
```

## Webhooks

### Webhook Events
LEG can send webhooks for various events:

- `tree.created` - New tree created
- `tree.updated` - Tree information updated  
- `import.completed` - GEDCOM import finished
- `import.failed` - GEDCOM import failed
- `report.generated` - Report generation completed

### Webhook Payload Example
```json
{
    "event": "import.completed",
    "timestamp": "2023-12-01T10:30:00Z",
    "data": {
        "import_id": "imp_1234567890",
        "tree_id": 123,
        "status": "completed",
        "records_imported": 1542,
        "duration": 45.2,
        "import_method": "optimized"
    },
    "api_version": "v1"
}
```

### Webhook Verification
```php
<?php

function verifyWebhook($payload, $signature, $secret) {
    $expectedSignature = hash_hmac('sha256', $payload, $secret);
    return hash_equals($signature, 'sha256=' . $expectedSignature);
}

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_LEG_SIGNATURE'];

if (verifyWebhook($payload, $signature, $webhookSecret)) {
    $event = json_decode($payload, true);
    // Process webhook event
}
```

## Best Practices

### Authentication
- Store API keys securely (environment variables, secret managers)
- Rotate API keys regularly
- Use different keys for different environments

### Performance
- Use appropriate pagination for large datasets
- Implement client-side caching for frequently accessed data
- Use bulk operations when available
- Monitor API usage and optimize requests

### Error Handling
- Always handle rate limiting gracefully
- Implement exponential backoff for retries
- Log API errors for debugging
- Provide meaningful error messages to users

### Data Privacy
- Respect privacy settings in API responses
- Use anonymization features for research data
- Implement proper access controls in your application
- Follow GDPR and other privacy regulations

### Code Examples

#### Bulk Individual Processing
```php
<?php

// Process individuals in batches to avoid rate limits
$individuals = $client->trees()->individuals(123);

foreach (array_chunk($individuals, 50) as $batch) {
    foreach ($batch as $individual) {
        // Process individual
        $relationships = $client->individuals()->relationships($individual['id']);
        
        // Add delay to respect rate limits
        usleep(100000); // 100ms delay
    }
}
```

#### Relationship Analysis
```javascript
async function analyzeFamily(treeId) {
    try {
        // Get tree statistics
        const stats = await client.analytics.treeStatistics(treeId);
        
        // Find most connected individual
        const individuals = await client.trees.individuals(treeId);
        let mostConnected = null;
        let maxConnections = 0;
        
        for (const individual of individuals) {
            const relationships = await client.individuals.relationships(individual.id);
            if (relationships.length > maxConnections) {
                maxConnections = relationships.length;
                mostConnected = individual;
            }
            
            // Respect rate limits
            await new Promise(resolve => setTimeout(resolve, 100));
        }
        
        console.log('Family Analysis Results:', {
            totalIndividuals: stats.total_individuals,
            generations: stats.generations,
            mostConnectedPerson: mostConnected.name,
            connectionCount: maxConnections
        });
        
    } catch (error) {
        console.error('Analysis failed:', error.message);
    }
}
```

#### Research Data Export
```python
import time
from leg_genealogy_sdk import LegClient

def export_research_data(client, tree_ids, privacy_level='k-anonymity-5'):
    """Export anonymized research data with proper error handling."""
    
    try:
        # Request anonymized dataset
        request = client.research.request_dataset(tree_ids, {
            'privacy_level': privacy_level,
            'format': 'json',
            'include_relationships': True,
            'temporal_aggregation': '5-year'
        })
        
        # Poll for completion
        while True:
            status = client.research.get_dataset_status(request['dataset_id'])
            
            if status['status'] == 'completed':
                # Download the dataset
                dataset = client.research.download_dataset(request['dataset_id'])
                return dataset
            elif status['status'] == 'failed':
                raise Exception(f"Dataset generation failed: {status['error']}")
            
            # Wait before polling again
            time.sleep(30)
            
    except Exception as e:
        print(f"Research data export failed: {e}")
        return None
```

## Troubleshooting

### Common Issues

#### 1. Authentication Problems
**Issue**: Getting 401 Unauthorized errors
**Solutions**:
- Verify API key is correct
- Check that API key hasn't expired
- Ensure proper Authorization header format
- Verify account has necessary permissions

#### 2. Rate Limiting
**Issue**: Getting 429 Too Many Requests
**Solutions**:
- Implement exponential backoff
- Add delays between requests
- Upgrade to higher tier plan
- Use bulk operations where available

#### 3. Large Data Processing
**Issue**: Timeouts or memory issues with large datasets
**Solutions**:
- Use pagination for large queries
- Process data in smaller batches
- Implement streaming for large exports
- Use background processing for long operations

#### 4. Privacy and Access Issues
**Issue**: Missing data or incomplete responses
**Solutions**:
- Check privacy settings on trees/individuals
- Verify user permissions for private data
- Review living person protection settings
- Ensure proper data sharing permissions

### Debug Information

#### Enable Debug Mode (PHP)
```php
<?php

$client = new Client('your-api-key', [
    'debug' => true,
    'log_requests' => true,
    'log_responses' => true
]);
```

#### Request/Response Logging (JavaScript)
```javascript
const client = new LegClient('your-api-key', {
    debug: true,
    requestLogger: (request) => console.log('Request:', request),
    responseLogger: (response) => console.log('Response:', response)
});
```

### Support Resources

- **Documentation**: [https://docs.leg-genealogy.com](https://docs.leg-genealogy.com)
- **API Status**: [https://status.leg-genealogy.com](https://status.leg-genealogy.com)
- **Developer Support**: [developers@leg-genealogy.com](mailto:developers@leg-genealogy.com)
- **Community Forum**: [https://community.leg-genealogy.com](https://community.leg-genealogy.com)
- **GitHub Issues**: [https://github.com/leg-genealogy/sdk-issues](https://github.com/leg-genealogy/sdk-issues)

### Performance Tips

1. **Batch Requests**: Group multiple operations when possible
2. **Use Caching**: Cache frequently accessed data locally
3. **Pagination**: Always use pagination for large datasets
4. **Filtering**: Apply filters to reduce response sizes
5. **Compression**: Enable gzip compression for responses
6. **Connection Pooling**: Reuse HTTP connections when possible

### Security Checklist

- [ ] API keys stored securely (not in source code)
- [ ] HTTPS used for all API communications
- [ ] Input validation implemented
- [ ] Error messages don't leak sensitive information
- [ ] Rate limiting handled gracefully
- [ ] Webhook signatures verified
- [ ] Access logs monitored
- [ ] Privacy settings respected

---

## Changelog

### v1.0.0 (Current)
- Initial API release
- Core genealogy operations
- Graph analytics
- Reporting system
- Research data export

### Coming Soon
- OAuth 2.0 authentication
- Real-time subscriptions
- Enhanced webhook events
- Mobile SDK support
- GraphQL API (beta)

---

*Last updated: December 2023*
*API Version: v1.0.0*
