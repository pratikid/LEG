# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

# LEG: Lineage Exploration & Genealogy Platform

LEG is a modern, open-source genealogy platform built with Laravel, featuring a multi-database architecture and advanced visualization capabilities. It combines PostgreSQL, MongoDB, Neo4j, and Redis to provide optimal performance for complex family tree data and relationships.

## 🚀 Quick-Reference Commands

### Initial Setup
```bash
# Windows (use setup.bat)
setup.bat

# Linux/Mac (use setup.sh)
./setup.sh

# Manual setup with Docker
docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed
```

### Development Server
```bash
# Start all services with one command (recommended for development)
composer dev
# This runs: server + queue worker + logs + vite dev server concurrently

# Or start services individually:
php artisan serve                    # Laravel dev server (port 8000)
npm run dev                         # Vite dev server (port 5173)
php artisan queue:work              # Background job processing
php artisan pail --timeout=0       # Real-time log monitoring
```

### Testing & Quality Assurance
```bash
# Run all tests
composer test                       # Basic test suite
composer test:coverage             # With coverage (min 80%)

# Run full CI suite locally
composer ci                        # lint + analyse + test:coverage

# Individual tools
composer lint                       # Laravel Pint (PSR-12)
composer analyse                    # PHPStan static analysis
vendor/bin/phpunit                 # PHPUnit tests
```

### Build & Assets
```bash
# Frontend builds
npm run build                       # Production build
npm run build:fast                 # Fast production build
npm run build:analyze              # With bundle analysis
npm run dev                        # Development build with HMR
```

### Database Operations
```bash
# Migrations
php artisan migrate                 # Run migrations
php artisan migrate:fresh --seed   # Fresh database with test data
php artisan migrate:rollback       # Rollback last migration

# GEDCOM Import (background job)
# Use web interface at /trees/import or via API

# Database connections
docker-compose exec postgres psql -U postgres -d leg          # PostgreSQL
docker-compose exec mongodb mongosh -u leg -p password123     # MongoDB
docker-compose exec neo4j cypher-shell -u neo4j -p password123 # Neo4j
docker-compose exec redis redis-cli                           # Redis
```

### Docker Management
```bash
# Container lifecycle
docker-compose up -d               # Start all services
docker-compose down                # Stop all services
docker-compose restart app         # Restart specific service
docker-compose logs -f app         # Follow app logs

# Useful operations
docker-compose exec app bash       # Shell into app container
docker-compose build --no-cache    # Rebuild all images
docker system prune -f             # Clean up Docker resources
```

## 🏗️ Architecture at a Glance

LEG uses a sophisticated multi-database architecture optimized for genealogical data:

### Core Technology Stack
- **Backend**: Laravel 12.x + PHP 8.4+ with strict type declarations
- **Frontend**: Livewire 3.x + Tailwind CSS 4.x + D3.js for visualizations
- **Build**: Vite with aggressive optimizations for fast development

### Multi-Database Strategy
Each database serves a specific purpose:

- **PostgreSQL** (primary): Core genealogy data, users, trees, individuals, families, events
- **MongoDB**: Flexible document storage for extended individual details, notes, custom fields
- **Neo4j**: Graph relationships for advanced family tree queries and relationship analysis
- **Redis**: Session management, caching, queue backend

### Key Domain Models & Services

**Core Models:**
- `Individual` - Family members with relationships
- `Tree` - Family tree containers
- `Family` - Marriage/partnership units
- `Event` - Life events (birth, death, marriage, etc.)
- `TimelineEvent` - User-created timeline entries

**Essential Services:**
- `GedcomService` - Basic GEDCOM parsing and export
- `GedcomMultiDatabaseService` - ACID-compliant multi-DB imports
- `GedcomImportOptimizer` - High-performance parallel processing
- `Neo4jIndividualService` - Advanced relationship queries

### Import Processing Flow
1. GEDCOM file uploaded → Job queued (standard or optimized method)
2. Background processing via Laravel queue system
3. Data distributed across PostgreSQL, MongoDB, Neo4j
4. Real-time progress tracking with metrics collection
5. User notification upon completion

## 🐳 Docker & Multi-Database Stack

The application runs in a fully containerized environment with 13+ services:

### Core Services
- **nginx**: Web server (ports 80, 443, 8090)
- **app**: Laravel application (PHP 8.4)
- **node**: Frontend asset building (Vite)
- **queue**: Background job processing

### Database Services
- **postgres**: Port 5432 (primary DB)
- **mongodb**: Port 27017 (document storage)
- **neo4j**: Ports 7474 (HTTP), 7687 (Bolt) (graph DB)
- **redis**: Port 6379 (cache/sessions)

### Monitoring Stack
- **prometheus**: Metrics collection (port 9090)
- **grafana**: Visualization dashboard (port 3000, admin/admin)
- **cadvisor**: Container metrics (port 8080)
- Various exporters for database metrics

### Service Dependencies & Health Checks
The app service waits for all databases to be healthy before starting. Each service has built-in health checks with appropriate retry logic.

### Volume Mounts & Persistence
- Application code: `./:/var/www/html` (delegated for performance)
- Database data: Named volumes (postgres-data, neo4j-data, mongodb-data, redis-data)
- Node modules: Cached volume for faster builds

### Resource Limits
Production-ready resource constraints are configured:
- App: 2GB RAM, 3 CPUs
- Neo4j: 2GB RAM with optimized JVM settings
- Databases: Appropriately sized for development workloads

### Common Docker Issues & Solutions
```bash
# Permission issues (especially on Linux)
docker-compose exec app chmod -R 777 storage bootstrap/cache
./docker/fix-permissions.sh

# Database connection issues
docker-compose down && docker-compose up -d  # Full restart
docker-compose logs postgres  # Check database logs

# Volume issues (corrupted data)
docker-compose down -v        # Remove volumes (DATA LOSS!)
docker volume prune           # Clean up orphaned volumes
```

## 🛠️ Development Workflow & Tooling

### Recommended Daily Workflow
1. **Start the stack**: `docker-compose up -d`
2. **Run dev environment**: `composer dev` (runs everything concurrently)
3. **Code with hot reload**: Frontend assets auto-rebuild on changes
4. **Monitor**: Use Laravel Telescope (http://localhost/telescope) for debugging
5. **Queue monitoring**: Laravel Horizon available at http://localhost/horizon
6. **Before commits**: Run `composer ci` to ensure code quality

### Code Quality Pipeline
The project enforces high code quality standards:

- **Laravel Pint**: PSR-12 compliant code formatting
- **PHPStan Level 8**: Strict static analysis with generics support
- **Rector**: Automated refactoring and modernization
- **PHPUnit**: Comprehensive test suite with 80% minimum coverage
- **Enlightn**: Security and performance auditing

### Frontend Development
- **Vite HMR**: Instant updates during development
- **Tailwind CSS 4.x**: Utility-first styling with JIT compilation
- **Livewire**: Reactive components without JavaScript complexity
- **D3.js**: Advanced family tree visualizations

### Testing Strategy
```bash
# Test categories
tests/Feature/          # Integration tests (controllers, auth, imports)
tests/Unit/            # Unit tests (models, services)

# Specific test files of interest:
tests/Feature/GedcomImportTest.php        # GEDCOM processing
tests/Feature/TreeControllerTest.php      # Tree management
tests/Feature/ImportMethodComparisonTest.php  # Performance comparisons
```

### CI/CD Pipeline
The GitHub Actions workflow runs:
1. **Test**: Full test suite with all database services
2. **Security**: Enlightn security checks, dependency scanning
3. **Performance**: Performance analysis and Lighthouse audits
4. **Docker**: Container build and composition tests
5. **Deploy**: Staging and production deployment (on respective branches)

### Monitoring & Debugging Tools
- **Laravel Telescope**: Request debugging, queries, jobs, mail
- **Laravel Horizon**: Queue monitoring and statistics
- **Prometheus + Grafana**: Infrastructure monitoring
- **Application logs**: `php artisan pail` for real-time log streaming

## 📚 Further Reading & Docs Index

### Project Documentation
- [`README.md`](README.md) - Project overview and quick start
- [`docs/developer/README.md`](docs/developer/README.md) - Comprehensive developer guide
- [`docs/developer/architecture.md`](docs/developer/architecture.md) - Detailed system architecture
- [`docs/developer/best-practices.md`](docs/developer/best-practices.md) - Development guidelines
- [`docs/developer/api-reference.md`](docs/developer/api-reference.md) - API documentation

### Specialized Guides
- [`docs/developer/gedcom-compliance.md`](docs/developer/gedcom-compliance.md) - GEDCOM format support
- [`docs/developer/security.md`](docs/developer/security.md) - Security guidelines
- [`docs/developer/clean-code.md`](docs/developer/clean-code.md) - Code quality standards
- [`docs/developer/commit-guidelines.md`](docs/developer/commit-guidelines.md) - Git workflow

### Key Implementation Details
- **GEDCOM Processing**: Three import methods (standard, multi-database, optimized)
- **Graph Queries**: Neo4j Cypher queries for complex relationship analysis  
- **Performance**: Import metrics tracking, query optimization strategies
- **Privacy**: Granular access controls and data protection compliance

### Technology Versions
- **PHP**: 8.4+ (latest features, enhanced performance)
- **Laravel**: 12.x (latest LTS when available)
- **Node.js**: 20+ for frontend tooling
- **Databases**: PostgreSQL 16, MongoDB 7, Neo4j 5 Enterprise, Redis Alpine

### Contributing Guidelines
- **Branch Strategy**: Feature branches from main, PR-based workflow
- **Code Review**: Automated CI checks + manual peer review required
- **Testing**: All new features must include comprehensive tests
- **Documentation**: Update relevant docs with code changes

---

**Quick Access URLs** (when stack is running):
- Application: http://localhost
- Neo4j Browser: http://localhost:7474
- Grafana: http://localhost:3000 (admin/admin)
- Prometheus: http://localhost:9090
- cAdvisor: http://localhost:8080

*This WARP.md was generated by analyzing the complete codebase structure, container configuration, and development toolchain.*
