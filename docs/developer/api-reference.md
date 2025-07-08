# API Reference

## Authentication
All API endpoints require authentication. Include the CSRF token in your requests:
```http
X-CSRF-TOKEN: <token>
```

## Response Format
All responses are in JSON format with the following structure:
```json
{
    "data": {}, // Response data
    "message": "", // Success/error message
    "error": "" // Error details if any
}
```

## Core API Endpoints

### Individual Management

#### Get Individual Details
```http
GET /individuals/{id}
```

**Response**
```json
{
    "data": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "birth_date": "1990-01-01",
        "death_date": null,
        "sex": "M",
        "tree_id": 1
    }
}
```

#### Create Individual
```http
POST /individuals
```

**Request Body**
```json
{
    "first_name": "John",
    "last_name": "Doe",
    "birth_date": "1990-01-01",
    "death_date": null,
    "sex": "M",
    "tree_id": 1
}
```

**Response**
```json
{
    "message": "Individual created successfully",
    "data": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe"
    }
}
```

#### Update Individual
```http
PUT /individuals/{id}
```

**Request Body**
```json
{
    "first_name": "John",
    "last_name": "Doe",
    "birth_date": "1990-01-01",
    "death_date": null,
    "sex": "M"
}
```

#### Delete Individual
```http
DELETE /individuals/{id}
```

#### Get Individual Timeline
```http
GET /individuals/timeline
```

### Tree Management

#### Get Tree Details
```http
GET /trees/{id}
```

**Response**
```json
{
    "data": {
        "id": 1,
        "name": "Doe Family Tree",
        "description": "Family tree of the Doe family",
        "user_id": 1,
        "created_at": "2024-01-01T00:00:00Z",
        "updated_at": "2024-01-01T00:00:00Z"
    }
}
```

#### Create Tree
```http
POST /trees
```

**Request Body**
```json
{
    "name": "My Family Tree",
    "description": "Family history research"
}
```

#### Import GEDCOM File
```http
POST /trees/import
```

**Request Body** (multipart/form-data)
```
file: <gedcom_file>
import_method: "standard" | "optimized"
```

#### Export GEDCOM File
```http
GET /trees/{id}/export-gedcom
```

#### Tree Visualization
```http
GET /trees/{tree}/visualization
```

### Relationship Management

#### Add Parent-Child Relationship
```http
POST /relationships/parent-child
```

**Request Body**
```json
{
    "parent_id": 1,
    "child_id": 2
}
```

**Response**
```json
{
    "message": "Parent-child relationship added successfully!"
}
```

#### Add Spouse Relationship
```http
POST /relationships/spouse
```

**Request Body**
```json
{
    "spouse_a_id": 1,
    "spouse_b_id": 2
}
```

**Response**
```json
{
    "message": "Spouse relationship added successfully between John Doe and Jane Doe!"
}
```

#### Add Sibling Relationship
```http
POST /relationships/sibling
```

**Request Body**
```json
{
    "sibling_a_id": 1,
    "sibling_b_id": 2
}
```

**Response**
```json
{
    "message": "Sibling relationship added successfully!"
}
```

#### Get Parents
```http
GET /relationships/{id}/parents
```

**Response**
```json
{
    "data": [
        {
            "id": 1,
            "first_name": "Father",
            "last_name": "Doe",
            "birth_date": "1960-01-01",
            "death_date": null,
            "sex": "M"
        },
        {
            "id": 2,
            "first_name": "Mother",
            "last_name": "Doe",
            "birth_date": "1962-01-01",
            "death_date": null,
            "sex": "F"
        }
    ]
}
```

#### Get Children
```http
GET /relationships/{id}/children
```

**Response**
```json
{
    "data": [
        {
            "id": 3,
            "first_name": "Child",
            "last_name": "Doe",
            "birth_date": "1990-01-01",
            "death_date": null,
            "sex": "M"
        }
    ]
}
```

#### Get Spouses
```http
GET /relationships/{id}/spouses
```

#### Advanced Relationship Queries

##### Get Ancestors
```http
GET /relationships/{id}/ancestors
```

**Query Parameters**
- `generations`: Number of generations to retrieve (default: 5)

##### Get Descendants
```http
GET /relationships/{id}/descendants
```

**Query Parameters**
- `generations`: Number of generations to retrieve (default: 5)

##### Get Siblings
```http
GET /relationships/{id}/siblings
```

##### Get Shortest Path
```http
GET /relationships/{fromId}/shortest-path/{toId}
```

#### Remove Relationships

##### Remove Parent-Child
```http
DELETE /relationships/parent-child
```

**Request Body**
```json
{
    "parent_id": 1,
    "child_id": 2
}
```

##### Remove Spouse
```http
DELETE /relationships/spouse
```

**Request Body**
```json
{
    "spouse_a_id": 1,
    "spouse_b_id": 2
}
```

##### Remove Sibling
```http
DELETE /relationships/sibling
```

**Request Body**
```json
{
    "sibling_a_id": 1,
    "sibling_b_id": 2
}
```

### Timeline Events

#### Get Timeline Events
```http
GET /timeline
```

#### Create Timeline Event
```http
POST /timeline
```

**Request Body**
```json
{
    "title": "Wedding Anniversary",
    "description": "Celebrating 25 years of marriage",
    "event_date": "2024-06-15",
    "event_type": "anniversary",
    "individual_id": 1
}
```

#### Update Timeline Event
```http
PUT /timeline/{id}
```

#### Delete Timeline Event
```http
DELETE /timeline/{id}
```

#### Public Timeline Event
```http
GET /timeline/{timelineEvent}
```

### Timeline Reports

#### Generate Timeline Report
```http
GET /timeline-reports/generate
```

#### Generate Event Report
```http
GET /timeline-reports/event/{timelineEvent}
```

#### Generate Type Report
```http
GET /timeline-reports/type/{type}
```

### Search

#### Search Individuals
```http
GET /search
```

**Query Parameters**
- `q`: Search query
- `type`: Search type (name, date, location)
- `tree_id`: Filter by tree

#### Get Search Suggestions
```http
GET /search/suggestions
```

### Import Progress

#### Get Import Progress
```http
GET /import-progress/{treeId}
```

#### Get All Import Progress
```http
GET /import-progress
```

### Notifications

#### Mark Notification as Read
```http
POST /notifications/{id}/mark-as-read
```

#### Mark All Notifications as Read
```http
POST /notifications/mark-all-as-read
```

#### Get Unread Count
```http
GET /notifications/unread-count
```

### Profile Management

#### Get Profile
```http
GET /profile
```

#### Update Profile
```http
PUT /profile
```

#### Update Password
```http
PUT /profile/password
```

#### Update Notifications
```http
POST /profile/notifications
```

#### Delete Profile
```http
DELETE /profile
```

### Timeline Preferences

#### Update Timeline Preferences
```http
PUT /timeline/preferences
```

## REST API (v1)

### Base URL
```
/api/v1
```

### Individual API
```http
GET /api/v1/individuals
GET /api/v1/individuals/{id}
POST /api/v1/individuals
PUT /api/v1/individuals/{id}
DELETE /api/v1/individuals/{id}
```

### Import Metrics API

#### Get Performance Summary
```http
GET /api/v1/import-metrics/summary
```

**Response**
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
        }
    }
}
```

#### Get Method Comparison
```http
GET /api/v1/import-metrics/comparison
```

#### Get Recent Metrics
```http
GET /api/v1/import-metrics/recent
```

#### Get Method-Specific Metrics
```http
GET /api/v1/import-metrics/method
```

#### Get Aggregated Metrics (Grafana Compatible)
```http
GET /api/v1/import-metrics/aggregated
```

## Admin API Endpoints

### Activity Logs

#### Get Activity Logs
```http
GET /admin/activity-logs
```

#### Get Activity Log Details
```http
GET /admin/activity-logs/{log}
```

#### Export Activity Logs
```http
GET /admin/activity-logs/export
```

### Admin Dashboard

#### Get Users
```http
GET /admin/users
```

#### Get System Logs
```http
GET /admin/logs
```

#### Get System Settings
```http
GET /admin/settings
```

#### Get Notifications
```http
GET /admin/notifications
```

#### Get Import Metrics
```http
GET /admin/import-metrics
```

## Error Handling

### Standard Error Response
```json
{
    "error": "Error message",
    "code": "ERROR_CODE",
    "details": {}
}
```

### Common Error Codes
- `VALIDATION_ERROR`: Input validation failed
- `NOT_FOUND`: Resource not found
- `UNAUTHORIZED`: Authentication required
- `FORBIDDEN`: Insufficient permissions
- `INTERNAL_ERROR`: Server error

### Example Error Response
```json
{
    "error": "Individual not found",
    "code": "NOT_FOUND",
    "details": {
        "id": 123
    }
}
```

## Rate Limiting

API endpoints are rate-limited to prevent abuse:
- **Standard endpoints**: 60 requests per minute
- **Import endpoints**: 10 requests per minute
- **Admin endpoints**: 30 requests per minute

Rate limit headers are included in responses:
```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1640995200
```

## Pagination

List endpoints support pagination:
```http
GET /individuals?page=1&per_page=20
```

**Response**
```json
{
    "data": [...],
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 150,
        "last_page": 8
    }
}
```

## Filtering and Sorting

### Filtering
```http
GET /individuals?tree_id=1&sex=M&birth_year=1990
```

### Sorting
```http
GET /individuals?sort=first_name&order=asc
```

## Webhooks (Planned)

Future implementation will include webhook support for:
- Individual creation/update
- Relationship changes
- Import completion
- Timeline event creation

## SDKs and Libraries

### PHP SDK (Planned)
```php
use Leg\Api\Client;

$client = new Client('your-api-key');
$individual = $client->individuals()->create([
    'first_name' => 'John',
    'last_name' => 'Doe'
]);
```

### JavaScript SDK (Planned)
```javascript
import { LegClient } from '@leg/api';

const client = new LegClient('your-api-key');
const individual = await client.individuals.create({
    firstName: 'John',
    lastName: 'Doe'
});
``` 