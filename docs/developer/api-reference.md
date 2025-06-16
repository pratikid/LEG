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
        "gender": "male",
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
    "gender": "male",
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
    "gender": "male"
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
            "gender": "male"
        },
        {
            "id": 2,
            "first_name": "Mother",
            "last_name": "Doe",
            "birth_date": "1962-01-01",
            "death_date": null,
            "gender": "female"
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
            "gender": "male"
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
            "gender": "female"
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
            "gender": "male"
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
```

### Export GEDCOM
```http
GET /trees/{id}/export-gedcom
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
            "gender": "male",
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