# Developer Documentation

## Overview
This project is a family tree management system built with Laravel and Neo4j. It provides functionality for managing individuals, their relationships, and family trees.

## Technology Stack
- **Backend**: Laravel (PHP)
- **Database**: 
  - MySQL (for user data and basic information)
  - Neo4j (for relationship management and graph queries)
- **Frontend**: Blade templates with Tailwind CSS
- **Authentication**: Laravel's built-in authentication system

## Project Structure

### Key Directories
```
├── app/
│   ├── Http/
│   │   ├── Controllers/    # Application controllers
│   │   └── Middleware/     # Custom middleware
│   ├── Models/             # Eloquent models
│   ├── Services/           # Business logic services
│   └── Traits/             # Reusable traits
├── config/                 # Configuration files
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/           # Database seeders
├── resources/
│   └── views/             # Blade templates
└── routes/
    └── web.php            # Web routes
```

### Key Components

#### Models
- `Individual`: Represents a person in the family tree
- `Tree`: Represents a family tree
- `User`: Represents system users

#### Services
- `Neo4jIndividualService`: Handles all Neo4j graph operations
  - Relationship management (parent-child, spouse, sibling)
  - Graph queries (ancestors, descendants, shortest path)
  - Tree visualization data

#### Controllers
- `IndividualController`: Manages individual CRUD operations
- `Neo4jRelationshipController`: Handles relationship operations
- `TreeController`: Manages family trees

## Database Schema

### MySQL Tables
- `individuals`: Stores basic individual information
- `trees`: Stores family tree information
- `users`: Stores user accounts
- `activity_logs`: Tracks system activities

### Neo4j Graph Structure
- **Nodes**:
  - `Individual`: Represents a person
  - `Tree`: Represents a family tree
- **Relationships**:
  - `PARENT_OF`: Parent-child relationship
  - `SPOUSE_OF`: Spouse relationship
  - `SIBLING_OF`: Sibling relationship
  - `BELONGS_TO`: Individual's membership in a tree

## Key Features

### Relationship Management
- Add/remove parent-child relationships
- Add/remove spouse relationships
- Add/remove sibling relationships
- View relationship statistics

### Tree Management
- Create and manage family trees
- Import/export GEDCOM files
- Tree visualization
- Tree statistics

### Individual Management
- Create and edit individual profiles
- View relationship networks
- Track life events
- Manage media attachments

## Development Setup

### Prerequisites
- PHP 8.1 or higher
- Composer
- MySQL 8.0 or higher
- Neo4j 4.4 or higher
- Node.js and NPM

### Installation
1. Clone the repository
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Install JavaScript dependencies:
   ```bash
   npm install
   ```
4. Copy `.env.example` to `.env` and configure:
   - Database credentials
   - Neo4j connection details
   - Application settings
5. Generate application key:
   ```bash
   php artisan key:generate
   ```
6. Run migrations:
   ```bash
   php artisan migrate
   ```
7. Start the development server:
   ```bash
   php artisan serve
   ```

### Docker Setup
Alternatively, use Docker:
```bash
docker-compose up -d
```

## API Documentation

### Relationship Endpoints

#### Add Relationships
- `POST /relationships/parent-child`: Add parent-child relationship
- `POST /relationships/spouse`: Add spouse relationship
- `POST /relationships/sibling`: Add sibling relationship

#### Get Relationships
- `GET /relationships/{id}/parents`: Get individual's parents
- `GET /relationships/{id}/children`: Get individual's children
- `GET /relationships/{id}/spouses`: Get individual's spouses
- `GET /relationships/{id}/siblings`: Get individual's siblings

#### Remove Relationships
- `DELETE /relationships/parent-child`: Remove parent-child relationship
- `DELETE /relationships/spouse`: Remove spouse relationship
- `DELETE /relationships/sibling`: Remove sibling relationship

### Tree Endpoints
- `GET /trees/{id}/visualization`: Get tree visualization data
- `POST /trees/import`: Import GEDCOM file
- `GET /trees/{id}/export-gedcom`: Export tree as GEDCOM

## Testing
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
```

## Deployment
1. Set up production environment variables
2. Run migrations
3. Optimize for production:
   ```bash
   php artisan optimize
   php artisan view:cache
   php artisan config:cache
   ```
4. Set up web server (Nginx/Apache)
5. Configure SSL certificates
6. Set up monitoring and logging

## Contributing
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Write/update tests
5. Submit a pull request

## Security
- All user input is validated
- CSRF protection enabled
- Authentication required for all operations
- Role-based access control
- Secure password hashing
- Input sanitization

## Performance Considerations
- Neo4j queries are optimized for graph traversal
- Caching implemented for frequently accessed data
- Lazy loading for relationship data
- Pagination for large datasets

## Troubleshooting

### Common Issues
1. Neo4j Connection Issues
   - Check connection settings in `.env`
   - Verify Neo4j server is running
   - Check network connectivity

2. Relationship Loading Failures
   - Verify individual IDs exist
   - Check Neo4j query logs
   - Validate relationship constraints

3. Tree Visualization Issues
   - Check browser console for errors
   - Verify data format
   - Check Neo4j query results

### Logging
- Application logs: `storage/logs/laravel.log`
- Neo4j logs: Check Neo4j server logs
- Error tracking: Configure error reporting service

## Support
For technical support:
1. Check existing documentation
2. Review issue tracker
3. Contact development team
4. Submit bug reports with detailed information

## License
This project is licensed under the MIT License - see the LICENSE file for details. 