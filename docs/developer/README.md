# Developer Documentation

Welcome to the LEG (Laravel Enhanced Genealogy) developer documentation. This guide provides comprehensive information for developers working on the LEG project.

## Quick Start

### Prerequisites
- **PHP 8.4+**: Latest PHP version with enhanced performance
- **Composer**: PHP dependency management
- **Node.js 18+**: For frontend asset compilation
- **Docker**: For containerized development environment
- **Git**: Version control

### Development Environment Setup

#### 1. Clone the Repository
```bash
git clone <repository-url>
cd leg
```

#### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

#### 3. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### 4. Database Setup
```bash
# Run database migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed
```

#### 5. Start Development Servers
```bash
# Start Laravel development server
php artisan serve

# Start Vite development server
npm run dev

# Start queue worker (in separate terminal)
php artisan queue:work
```

## Project Structure

### Core Directories
```
leg/
├── app/
│   ├── Console/Commands/     # Artisan commands
│   ├── Exceptions/           # Exception handlers
│   ├── Helpers/              # Helper functions
│   ├── Http/
│   │   ├── Controllers/      # Web controllers
│   │   ├── Middleware/       # Request middleware
│   │   └── Requests/         # Form request validation
│   ├── Jobs/                 # Background jobs
│   ├── Livewire/             # Livewire components
│   ├── Models/               # Eloquent models
│   ├── Notifications/        # Notification classes
│   ├── Policies/             # Authorization policies
│   ├── Providers/            # Service providers
│   └── Services/             # Business logic services
├── config/                   # Configuration files
├── database/
│   ├── factories/            # Model factories
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── docs/                     # Documentation
├── resources/
│   ├── css/                  # Stylesheets
│   ├── js/                   # JavaScript files
│   └── views/                # Blade templates
├── routes/                   # Route definitions
├── storage/                  # File storage
└── tests/                    # Test files
```

## Technology Stack

### Backend
- **Laravel 12.x**: Modern PHP framework
- **PHP 8.4+**: Latest PHP version
- **PostgreSQL**: Primary relational database
- **MongoDB**: Document storage
- **Neo4j**: Graph database for relationships
- **Redis**: Caching and sessions

### Frontend
- **Laravel Blade**: Server-side templating
- **Livewire 3.x**: Dynamic components
- **Tailwind CSS 4.x**: Utility-first CSS
- **D3.js**: Data visualizations
- **Vite**: Build tool

### Development Tools
- **Laravel Pint**: Code styling
- **PHPStan**: Static analysis
- **Rector**: Automated refactoring
- **PHPUnit**: Testing framework
- **Laravel Telescope**: Debugging

## Core Features

### ✅ Implemented Features

#### Authentication & Authorization
- **User Management**: Complete user registration and login
- **Role-based Access**: Admin and user roles
- **Password Reset**: Secure password reset functionality
- **Session Management**: Secure session handling

#### Tree Management
- **Tree Creation**: Create and manage family trees
- **GEDCOM Import**: Import genealogy data from GEDCOM files
- **GEDCOM Export**: Export trees to GEDCOM format
- **Tree Visualization**: D3.js-based tree visualization
- **Tree Sharing**: Share trees with other users

#### Individual Management
- **CRUD Operations**: Complete individual management
- **Relationship Management**: Parent-child, spouse, sibling relationships
- **Advanced Queries**: Neo4j-powered relationship queries
- **Timeline View**: Individual timeline visualization
- **Search & Filtering**: Advanced search capabilities

#### Timeline Events
- **Event Creation**: Create timeline events
- **Public Sharing**: Share events publicly
- **Event Types**: Various event type support
- **Report Generation**: Timeline reports and analytics

#### Import System
- **Dual Import Methods**: Standard and optimized import
- **Performance Tracking**: Real-time import metrics
- **Progress Monitoring**: Import progress tracking
- **Error Handling**: Comprehensive error handling

#### Admin Features
- **Activity Logs**: Comprehensive activity monitoring
- **Import Metrics**: Performance dashboard
- **User Management**: Admin user management
- **System Monitoring**: Health monitoring

### 🔄 In Progress Features

#### Advanced Visualizations
- **Enhanced D3.js**: Improved tree visualizations
- **Multiple Layouts**: Various tree layout options
- **Interactive Features**: Enhanced interactivity

#### Performance Optimization
- **Query Optimization**: Database query improvements
- **Caching Strategy**: Enhanced caching implementation
- **API Enhancement**: REST API improvements

### 📋 Planned Features

#### Advanced Features
- **DNA Integration**: DNA result linking
- **Advanced Privacy**: Granular privacy controls
- **Multimedia Support**: Enhanced media integration
- **Geographic Mapping**: Migration pattern visualization

#### Internationalization
- **Multi-language Support**: Internationalization
- **Localization**: Regional adaptations
- **Accessibility**: Comprehensive accessibility

## Development Guidelines

### Code Standards
- **PSR-12**: PHP coding standards
- **Laravel Conventions**: Laravel best practices
- **Type Declarations**: Strict typing throughout
- **Documentation**: Comprehensive code documentation

### Testing Strategy
- **Unit Tests**: Model and service testing
- **Feature Tests**: Controller and integration testing
- **Browser Tests**: End-to-end testing
- **Performance Tests**: Import and query performance

### Security Practices
- **Input Validation**: Comprehensive request validation
- **SQL Injection Prevention**: Parameterized queries
- **XSS Protection**: Output sanitization
- **CSRF Protection**: Cross-site request forgery protection

## API Development

### REST API
- **Individual API**: Complete CRUD operations
- **Import Metrics API**: Performance tracking
- **Authentication**: Token-based authentication
- **Rate Limiting**: API rate limiting

### API Documentation
- **OpenAPI/Swagger**: API documentation
- **Postman Collections**: API testing collections
- **Example Requests**: Comprehensive examples

## Database Management

### Multi-Database Strategy
- **PostgreSQL**: Core genealogy data
- **MongoDB**: Flexible document storage
- **Neo4j**: Graph relationships
- **Redis**: Caching and sessions

### Migration Strategy
- **Version Control**: Database schema versioning
- **Rollback Support**: Migration rollback capabilities
- **Data Integrity**: ACID compliance

## Performance Optimization

### Caching Strategy
- **Redis Caching**: Session and data caching
- **Query Caching**: Database query results
- **Page Caching**: Static page content

### Database Optimization
- **Indexing**: Strategic database indexing
- **Query Optimization**: Optimized database queries
- **Connection Pooling**: Database connection management

## Monitoring & Debugging

### Development Tools
- **Laravel Telescope**: Debugging and monitoring
- **Laravel Horizon**: Queue monitoring
- **Log Management**: Comprehensive logging

### Performance Monitoring
- **Import Metrics**: Real-time import tracking
- **Query Performance**: Database query monitoring
- **Memory Usage**: Memory consumption tracking

## Deployment

### Containerization
- **Docker**: Containerized application
- **Docker Compose**: Multi-service orchestration
- **Environment Configuration**: Environment-specific configs

### CI/CD Pipeline
- **Automated Testing**: Continuous integration
- **Code Quality**: Automated code quality checks
- **Deployment**: Automated deployment pipeline

## Contributing

### Development Workflow
1. **Feature Branch**: Create feature branch from main
2. **Development**: Implement feature with tests
3. **Code Review**: Submit pull request for review
4. **Testing**: Ensure all tests pass
5. **Documentation**: Update relevant documentation
6. **Merge**: Merge after approval

### Code Review Process
- **Automated Checks**: CI/CD pipeline validation
- **Manual Review**: Peer code review
- **Testing**: Comprehensive testing requirements
- **Documentation**: Documentation updates

## Troubleshooting

### Common Issues

#### Import Problems
- **Memory Issues**: Use optimized import method
- **File Size**: Split large files
- **Format Issues**: Validate GEDCOM format

#### Performance Issues
- **Slow Queries**: Check database indexing
- **Memory Usage**: Monitor memory consumption
- **Cache Issues**: Clear application cache

#### Development Issues
- **Dependency Issues**: Clear composer cache
- **Asset Issues**: Rebuild frontend assets
- **Database Issues**: Reset database migrations

### Debug Information
- **Log Files**: Check Laravel logs
- **Telescope**: Use Laravel Telescope for debugging
- **Database Logs**: Check database logs
- **Queue Logs**: Monitor queue processing

## Resources

### Documentation
- [API Reference](api-reference.md): Complete API documentation
- [Architecture Overview](architecture.md): System architecture
- [Best Practices](best-practices.md): Development guidelines
- [Clean Code](clean-code.md): Code quality standards

### External Resources
- [Laravel Documentation](https://laravel.com/docs)
- [Neo4j Documentation](https://neo4j.com/docs/)
- [D3.js Documentation](https://d3js.org/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)

### Community
- [GitHub Issues](https://github.com/leg/issues): Bug reports and feature requests
- [Discussions](https://github.com/leg/discussions): Community discussions
- [Contributing Guide](CONTRIBUTING.md): How to contribute

---

*This documentation is regularly updated. Last updated: January 2025* 