# API Reference

## Overview
LEG provides a RESTful API for managing genealogical data. All endpoints return JSON responses and follow standard HTTP status codes.

## Authentication
All API endpoints require authentication. Include the CSRF token in your requests:
```http
X-CSRF-TOKEN: <token>
```

## Rate Limiting
API endpoints are rate-limited to 60 requests per minute per IP address.

## Response Format
All responses are in JSON format with the following structure:
```json
{
    "data": {}, // Response data
    "message": "", // Success/error message
    "error": "" // Error details if any
}
```

## OpenAPI Specification
The API follows OpenAPI 3.0 specification. You can access the interactive documentation at `/api/docs` when the application is running.

## Core API Endpoints

### Individual Management

#### Get Individuals List
```http
GET /api/v1/individuals
```

**Query Parameters:**
- `tree_id` (integer): Filter by tree ID
- `sex` (string): Filter by sex (M, F, U)
- `search` (string): Search in names
- `per_page` (integer): Items per page (default: 15)

**Response**
```json
{
    "data": [
        {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "birth_date": "1990-01-01",
            "death_date": null,
            "sex": "M",
            "tree_id": 1
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 15,
        "total": 1
    }
}
```

#### Get Individual Details
```http
GET /api/v1/individuals/{id}
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
        "tree_id": 1,
        "tree": {
            "id": 1,
            "name": "Doe Family Tree"
        },
        "families_as_husband": [],
        "families_as_wife": [],
        "families_as_child": []
    }
}
```

#### Create Individual
```http
POST /api/v1/individuals
```

**Request Body**
```json
{
    "first_name": "John",
    "last_name": "Doe",
    "name_prefix": "Mr.",
    "name_suffix": "Jr.",
    "nickname": "Johnny",
    "birth_date": "1990-01-01",
    "death_date": null,
    "sex": "M",
    "birth_place": "New York, NY",
    "death_place": null,
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
PUT /api/v1/individuals/{id}
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
DELETE /api/v1/individuals/{id}
```

### Tree Management

#### Get Trees List
```http
GET /api/v1/trees
```

**Query Parameters:**
- `user_id` (integer): Filter by user ID
- `search` (string): Search in name and description
- `per_page` (integer): Items per page (default: 15)

#### Get Tree Details
```http
GET /api/v1/trees/{id}
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
        "updated_at": "2024-01-01T00:00:00Z",
        "user": {
            "id": 1,
            "name": "John Doe"
        },
        "individuals": []
    }
}
```

#### Create Tree
```http
POST /api/v1/trees
```

**Request Body**
```json
{
    "name": "My Family Tree",
    "description": "Family history research",
    "user_id": 1
}
```

### Family Management

#### Get Families List
```http
GET /api/v1/families
```

**Query Parameters:**
- `tree_id` (integer): Filter by tree ID
- `husband_id` (integer): Filter by husband ID
- `wife_id` (integer): Filter by wife ID
- `per_page` (integer): Items per page (default: 15)

#### Get Family Details
```http
GET /api/v1/families/{id}
```

**Response**
```json
{
    "data": {
        "id": 1,
        "tree_id": 1,
        "husband_id": 1,
        "wife_id": 2,
        "marriage_date": "1975-01-01",
        "marriage_place": "New York, NY",
        "divorce_date": null,
        "husband": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe"
        },
        "wife": {
            "id": 2,
            "first_name": "Jane",
            "last_name": "Doe"
        },
        "children": []
    }
}
```

### GEDCOM Operations

#### Import GEDCOM File
```http
POST /api/v1/gedcom/import
```

**Request Body** (multipart/form-data)
```
file: <gedcom_file>
tree_id: 1
import_method: "standard" | "optimized"
```

**Response**
```json
{
    "message": "GEDCOM imported successfully",
    "data": {
        "individuals_count": 150,
        "families_count": 75,
        "tree_id": 1
    }
}
```

#### Export GEDCOM File
```http
GET /api/v1/gedcom/export/{tree}
```

**Response**
```json
{
    "message": "GEDCOM exported successfully",
    "data": {
        "gedcom_content": "0 HEAD\n1 GEDC\n2 VERS 5.5.5\n...",
        "tree_id": 1
    }
}
```

#### Validate GEDCOM File
```http
POST /api/v1/gedcom/validate
```

**Request Body** (multipart/form-data)
```
file: <gedcom_file>
```

**Response**
```json
{
    "message": "GEDCOM validation completed",
    "data": {
        "is_valid": true,
        "individuals_count": 150,
        "families_count": 75,
        "sources_count": 25,
        "notes_count": 50
    }
}
```

### Search Functionality

#### Search Individuals
```http
GET /api/v1/search/individuals?q=John&tree_id=1
```

#### Search Families
```http
GET /api/v1/search/families?q=Doe&tree_id=1
```

#### Search Trees
```http
GET /api/v1/search/trees?q=Family&user_id=1
```

### Health Check

#### API Health Status
```http
GET /api/v1/health
```

**Response**
```json
{
    "status": "healthy",
    "timestamp": "2024-01-01T00:00:00Z",
    "version": "1.0.0"
}
```

## Error Handling

### Validation Errors (422)
```json
{
    "message": "Validation failed",
    "errors": {
        "first_name": ["The first name field is required."],
        "sex": ["The selected sex is invalid."]
    }
}
```

### Server Errors (500)
```json
{
    "message": "Import failed",
    "error": "Database connection failed"
}
```

## SDKs and Client Libraries

### JavaScript/TypeScript
```javascript
// Example using fetch
const response = await fetch('/api/v1/individuals', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        first_name: 'John',
        last_name: 'Doe',
        sex: 'M',
        tree_id: 1
    })
});

const data = await response.json();
```

### Python
```python
import requests

response = requests.post('/api/v1/individuals', json={
    'first_name': 'John',
    'last_name': 'Doe',
    'sex': 'M',
    'tree_id': 1
}, headers={
    'X-CSRF-TOKEN': csrf_token
})

data = response.json()
```

## Rate Limiting

The API implements rate limiting to prevent abuse:
- **Default**: 60 requests per minute per IP
- **Import endpoints**: 10 requests per minute per user
- **Export endpoints**: 30 requests per minute per user

Rate limit headers are included in responses:
```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
```

## Pagination

All list endpoints support pagination with the following parameters:
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15, max: 100)

Pagination metadata is included in responses:
```json
{
    "data": [...],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75
    }
}
``` 