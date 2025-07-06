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

## Individual Management

### Get Individual Details
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

### Create Individual
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

### Update Individual
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

### Delete Individual
```http
DELETE /individuals/{id}
```

## Relationship Management

### Add Parent-Child Relationship
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

### Add Spouse Relationship
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

### Add Sibling Relationship
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

### Get Parents
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

### Get Children
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

### Get Spouses
```http
GET /relationships/{id}/spouses
```

**Response**
```json
{
    "data": [
        {
            "id": 4,
            "first_name": "Spouse",
            "last_name": "Doe",
            "birth_date": "1990-01-01",
            "death_date": null,
            "sex": "F"
        }
    ]
}
```

### Get Siblings
```http
GET /relationships/{id}/siblings
```

**Response**
```json
{
    "data": [
        {
            "id": 5,
            "first_name": "Sibling",
            "last_name": "Doe",
            "birth_date": "1992-01-01",
            "death_date": null,
            "sex": "M"
        }
    ]
}
```

### Remove Parent-Child Relationship
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

### Remove Spouse Relationship
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

### Remove Sibling Relationship
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

## Tree Management

### Get Tree Details
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

### Create Tree
```http
POST /trees
```

**Request Body**
```json
{
    "name": "Doe Family Tree",
    "description": "Family tree of the Doe family"
}
```

### Update Tree
```http
PUT /trees/{id}
```

**Request Body**
```json
{
    "name": "Updated Tree Name",
    "description": "Updated description"
}
```

### Delete Tree
```http
DELETE /trees/{id}
```

### Get Tree Visualization Data
```http
GET /trees/{id}/visualization
```

**Response**
```json
{
    "data": {
        "nodes": [
            {
                "id": 1,
                "label": "John Doe",
                "type": "Individual"
            }
        ],
        "edges": [
            {
                "from": 1,
                "to": 2,
                "type": "PARENT_OF"
            }
        ]
    }
}
```

### Import GEDCOM
```http
POST /trees/import
```

**Request Body**
```multipart/form-data
file: <gedcom_file>
tree_id: <tree_id> (optional)
import_method: standard|optimized (required)
```

**Request Parameters**
- `file` (required): GEDCOM file to import
- `tree_id` (optional): Target tree ID (creates new tree if not provided)
- `import_method` (required): Import method to use
  - `standard`: Multi-database import with ACID compliance
  - `optimized`: Parallel processing with memory optimization

**Response**
```json
{
    "message": "GEDCOM file uploaded successfully. Import is processing in the background. You will receive a notification when it completes.",
    "data": {
        "tree_id": 1,
        "import_method": "optimized",
        "file_name": "family_tree.ged"
    }
}
```

### Export GEDCOM
```http
GET /trees/{id}/export-gedcom
```

## Import Performance Metrics

### Get Import Metrics Summary
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

### Get Import Method Comparison
```http
GET /api/v1/import-metrics/comparison
```

**Response**
```json
{
    "success": true,
    "data": {
        "method_comparison": {
            "standard": {
                "total_imports": 150,
                "success_rate": 98.5,
                "avg_duration": 45.2,
                "avg_throughput": 22.1,
                "avg_memory": 125.5
            },
            "optimized": {
                "total_imports": 75,
                "success_rate": 99.2,
                "avg_duration": 33.7,
                "avg_throughput": 29.7,
                "avg_memory": 98.3
            }
        },
        "performance_gains": {
            "duration_improvement": -25.5,
            "throughput_improvement": 34.2,
            "memory_improvement": -21.7
        }
    }
}
```

### Get Recent Import Metrics
```http
GET /api/v1/import-metrics/recent
```

**Query Parameters**
- `limit` (optional): Number of recent imports to return (default: 10)
- `method` (optional): Filter by import method (standard/optimized)

**Response**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "import_method": "optimized",
            "tree_id": 1,
            "user_id": 1,
            "duration": 33.7,
            "memory_used_mb": 98.3,
            "file_size_bytes": 1048576,
            "records_processed": 1500,
            "success": true,
            "created_at": "2024-01-01T10:00:00Z"
        }
    ]
}
```

### Get Method-Specific Metrics
```http
GET /api/v1/import-metrics/method
```

**Query Parameters**
- `method` (required): Import method (standard/optimized)
- `timeframe` (optional): Time range (1h, 24h, 7d, 30d)

**Response**
```json
{
    "success": true,
    "data": {
        "method": "optimized",
        "timeframe": "24h",
        "total_imports": 25,
        "success_rate": 99.2,
        "avg_duration": 33.7,
        "avg_throughput": 29.7,
        "avg_memory": 98.3,
        "performance_trend": {
            "duration": [-2.1, -1.5, -0.8],
            "throughput": [1.2, 2.1, 1.8],
            "memory": [-5.2, -3.1, -2.8]
        }
    }
}
```

### Get Grafana-Compatible Time Series
```http
GET /api/v1/import-metrics/aggregated
```

**Query Parameters**
- `metric` (required): Metric type (duration, throughput, memory, success_rate)
- `method` (optional): Import method filter
- `from` (optional): Start timestamp (ISO 8601)
- `to` (optional): End timestamp (ISO 8601)

**Response**
```json
{
    "success": true,
    "data": {
        "metric": "duration",
        "method": "optimized",
        "time_series": [
            {
                "timestamp": "2024-01-01T10:00:00Z",
                "value": 33.7
            },
            {
                "timestamp": "2024-01-01T11:00:00Z",
                "value": 32.1
            }
        ]
    }
}
```

## Advanced Queries

### Get Ancestors
```http
GET /relationships/{id}/ancestors
```

**Query Parameters**
- `maxDepth` (optional): Maximum depth to traverse (default: 5)
- `limit` (optional): Maximum number of results (default: 20)

**Response**
```json
{
    "data": [
        {
            "id": 1,
            "first_name": "Ancestor",
            "last_name": "Doe",
            "birth_date": "1900-01-01",
            "death_date": "1980-01-01",
            "sex": "M",
            "depth": 1
        }
    ]
}
```

### Get Descendants
```http
GET /relationships/{id}/descendants
```

**Query Parameters**
- `maxDepth` (optional): Maximum depth to traverse (default: 5)
- `limit` (optional): Maximum number of results (default: 20)

### Get Shortest Path
```http
GET /relationships/{fromId}/shortest-path/{toId}
```

**Query Parameters**
- `maxDepth` (optional): Maximum depth to traverse (default: 10)

**Response**
```json
{
    "data": {
        "path": [
            {
                "id": 1,
                "first_name": "John",
                "last_name": "Doe"
            },
            {
                "id": 2,
                "first_name": "Jane",
                "last_name": "Doe"
            }
        ],
        "distance": 1
    }
}
```

## Error Responses

### Validation Error
```json
{
    "error": "Validation failed",
    "errors": {
        "first_name": ["The first name field is required."],
        "last_name": ["The last name field is required."]
    }
}
```

### Not Found Error
```json
{
    "error": "Individual not found"
}
```

### Relationship Error
```json
{
    "error": "Cannot create parent-child relationship: would create a cycle in the family tree"
}
```

### Authentication Error
```json
{
    "error": "Unauthenticated"
}
```

## Rate Limiting
- 60 requests per minute per IP address
- Rate limit headers included in response:
  - `X-RateLimit-Limit`
  - `X-RateLimit-Remaining`
  - `X-RateLimit-Reset`

## Versioning
- Current API version: v1
- Version included in URL: `/api/v1/...`
- Version deprecation notices will be provided 6 months in advance 