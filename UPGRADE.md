# Laravel Genealogy Project Upgrade Guide

This document outlines the comprehensive upgrade from the legacy setup to a modern PHP 8.4, Laravel 13, and Node.js 22 development environment.

## 🚀 Upgrade Overview

### What's Been Upgraded

#### Backend (PHP/Laravel)
- **PHP**: Upgraded to 8.4 (from 8.3)
- **Laravel**: Upgraded to 13.x (from 11.x)  
- **Database**: Support for multiple databases (PostgreSQL, MySQL, MongoDB, Neo4j)
- **Testing**: PHPUnit 11.x with Pest 3.x
- **Code Quality**: PHPStan level 9, Laravel Pint, Rector

#### Frontend (JavaScript/Node.js)
- **Node.js**: Upgraded to 22.x (from 20.x)
- **Vue.js**: Upgraded to 3.5.x with Composition API
- **Vite**: Upgraded to 6.x with optimized build process
- **TypeScript**: Added with 5.7.x for type safety
- **Testing**: Vitest for unit testing, improved coverage
- **UI Framework**: TailwindCSS 3.4.x with enhanced utilities

### New Features Added

#### 🎯 Graph Analytics & Relationship Insights
- Neo4j integration for family relationship algorithms
- Shortest path calculations, common ancestors, relationship coefficients
- Advanced graph traversal and genealogical analysis
- Performance optimized with caching and batch processing

#### 🔒 Privacy & Consent Framework  
- GDPR-compliant data consent management
- Granular privacy settings per individual
- Data anonymization and access control
- Privacy-aware API endpoints with filtering

#### 💰 Premium Features & Monetization
- Subscription-based tiered access (Free, Premium, Professional)
- Usage metering and quota enforcement
- API licensing with rate limiting and domain restrictions
- Revenue tracking and analytics

#### 📊 Interactive D3.js Analytics Dashboard
- Rich data visualizations for family tree metrics
- Birth year distributions, gender analytics, surname frequency
- Interactive charts with tooltips and drill-down capabilities
- Export functionality for reports and analysis

#### 📄 Advanced Reporting & Export
- PDF generation for family tree charts and reports  
- Multi-format export (PDF, CSV, GEDCOM)
- Ancestor/descendant reports with privacy filtering
- Family history books with narrative generation

#### 🛡️ Enhanced Security & Performance
- Multi-database architecture with optimized queries
- Advanced caching strategies (Redis, file-based)
- Queue-based background processing
- Comprehensive audit logging

## 📦 Installation & Deployment

### Prerequisites

Ensure you have the following installed:
- **PHP 8.4+** with extensions: `mbstring, xml, ctype, iconv, intl, pdo_pgsql, redis, zip, mongodb`
- **Composer 2.7+** for PHP dependency management
- **Node.js 22.x+** with npm for frontend dependencies
- **Database**: PostgreSQL 16+, MySQL 8.0+, MongoDB 7+, Neo4j 5+
- **Redis 7+** for caching and queues

### Quick Start

#### Option 1: Using Deployment Script (Recommended)

```bash
# On Windows
./deploy.bat

# On Linux/macOS  
./deploy.sh

# With options
./deploy.bat --skip-tests --production
```

#### Option 2: Manual Installation

```bash
# 1. Install PHP dependencies
composer install --optimize-autoloader

# 2. Install Node.js dependencies  
npm install

# 3. Copy environment file
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Configure your database connections in .env

# 6. Run database migrations
php artisan migrate

# 7. Seed the database (optional)
php artisan db:seed

# 8. Build frontend assets
npm run build

# 9. Start the development server
php artisan serve
```

### Environment Configuration

Update your `.env` file with the following key configurations:

```env
# Application
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Primary Database (PostgreSQL recommended)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=genealogy
DB_USERNAME=your_username
DB_PASSWORD=your_password

# MongoDB (for analytics data)
MONGO_DB_HOST=127.0.0.1
MONGO_DB_PORT=27017
MONGO_DB_DATABASE=genealogy_analytics
MONGO_DB_USERNAME=mongo_user
MONGO_DB_PASSWORD=mongo_password

# Neo4j (for graph algorithms)
NEO4J_HOST=127.0.0.1
NEO4J_PORT=7687
NEO4J_USERNAME=neo4j
NEO4J_PASSWORD=neo4j_password

# Redis (caching and queues)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue Configuration
QUEUE_CONNECTION=redis

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
```

## 🧪 Testing

### Running Tests

```bash
# Run all tests
composer test

# Run with coverage
composer test:coverage

# Run specific test suites
vendor/bin/phpunit tests/Unit
vendor/bin/phpunit tests/Feature

# Frontend tests
npm test

# Frontend tests with coverage
npm run test:coverage
```

### Code Quality

```bash
# Static analysis
composer analyse

# Code formatting
composer lint

# Frontend linting  
npm run lint

# Type checking
npm run type-check
```

## 📈 Performance Monitoring

### Application Optimization

```bash
# Production optimizations
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Clear caches during development
php artisan optimize:clear
```

### Queue Workers

```bash
# Start queue worker
php artisan queue:work

# With memory limits and timeouts
php artisan queue:work --memory=512 --timeout=300

# Horizon dashboard (if using)
php artisan horizon
```

### Scheduled Tasks

Add to your crontab for automated tasks:
```cron
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

## 🚀 Deployment

### Production Checklist

- [ ] Environment configured for production (`APP_ENV=production`)
- [ ] Debug mode disabled (`APP_DEBUG=false`)
- [ ] Database connections secured
- [ ] SSL certificates configured
- [ ] Redis/MongoDB/Neo4j secured
- [ ] File permissions set correctly
- [ ] Queue workers configured as services
- [ ] Scheduled tasks configured in crontab
- [ ] Log rotation configured
- [ ] Monitoring and alerting configured

### CI/CD Pipeline

The project includes a comprehensive GitHub Actions workflow that:
- Runs on PHP 8.4 and Node.js 22
- Tests with PostgreSQL, Redis, MongoDB, and Neo4j
- Performs security scans and dependency audits  
- Builds and analyzes frontend assets
- Provides deployment automation

## 🔧 Development Workflow

### Starting Development

```bash
# Start all services
php artisan serve
php artisan queue:work
npm run dev

# Watch for frontend changes
npm run dev
```

### Git Hooks (Optional)

Install pre-commit hooks for code quality:
```bash
# Install husky
npm install --save-dev husky

# Add pre-commit hook
npx husky add .husky/pre-commit "composer lint && npm run lint"
```

## 📊 Features Overview

### Core Genealogy Features
- Individual and family management
- GEDCOM import/export
- Multi-generational tree visualization
- Photo and document attachment
- Timeline and life events

### Advanced Analytics
- Graph-based relationship analysis
- Statistical reporting with D3.js visualizations  
- Surname distribution and migration patterns
- Birth/death date analysis
- Family size and structure metrics

### Privacy & Security
- Granular privacy controls per individual
- GDPR-compliant consent management
- Data anonymization for research
- Audit logging and access tracking

### Premium Features
- Advanced reporting and PDF exports
- Extended API access with higher limits
- Priority support and feature requests
- Advanced graph algorithms and insights

## 🆘 Troubleshooting

### Common Issues

#### Database Connection Issues
```bash
# Test database connections
php artisan migrate:status
php artisan tinker
> DB::connection()->getPdo();
```

#### Frontend Build Issues
```bash
# Clear node_modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Check Node.js version compatibility
node --version  # Should be 22.x+
```

#### Permission Issues (Linux/macOS)
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### Performance Issues

#### Database Optimization
- Ensure proper indexing on frequently queried columns
- Use database connection pooling
- Consider read replicas for heavy read workloads

#### Caching Optimization  
- Configure Redis for session and cache storage
- Enable OPcache for PHP performance
- Use CDN for static asset delivery

### Getting Help

- Check the [Laravel Documentation](https://laravel.com/docs)
- Review the [Vue.js Guide](https://vuejs.org/guide/)
- Search existing GitHub Issues
- Create new issues with detailed reproduction steps

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes and add tests
4. Run the test suite (`composer test && npm test`)
5. Commit your changes (`git commit -m 'Add some amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

---

**Happy coding! 🎉**

For additional support, please check the project documentation or create an issue on GitHub.
