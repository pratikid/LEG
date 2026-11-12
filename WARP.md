# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

# LEG: Lineage Exploration & Genealogy Platform

LEG is a modern, open-source genealogy platform built with Laravel, featuring a multi-database architecture and advanced visualization capabilities. It combines PostgreSQL, MongoDB, Neo4j, and Redis to provide optimal performance for complex family tree data and relationships.

## 🚀 Quick-Reference Commands

> **Important**: All commands run inside Docker containers. Docker and Docker Compose are the only host prerequisites.

### Initial Setup
```bash
# Windows (automated setup via containers)
setup.bat

# Linux/Mac (automated setup via containers)
./setup.sh

# Manual setup with Docker
docker compose up -d                                          # Start all services
docker compose exec app composer install                      # Install PHP dependencies
docker compose exec app php artisan key:generate             # Generate app key
docker compose exec app php artisan migrate --seed           # Setup database with test data
docker compose exec node npm install                         # Install node dependencies
```

### Development Server
```bash
# Start all services (recommended for development)
docker compose up -d                                          # Start entire stack
# This starts: nginx (port 80) + app + queue worker + node build + all databases

# Start services individually:
docker compose up -d nginx app                                # Web server + Laravel app
docker compose exec node npm run dev                         # Vite dev server with HMR
docker compose up -d queue                                    # Background job processing
docker compose exec app php artisan pail --timeout=0        # Real-time log monitoring

# Monitor running services
docker compose logs -f app                                    # Follow app logs
docker compose logs -f --tail=50 nginx app queue             # Multiple service logs
```

### Testing & Quality Assurance
```bash
# Run all tests (in app container)
docker compose exec app composer test                        # Basic test suite
docker compose exec app composer test:coverage              # With coverage (min 80%)

# Run full CI suite locally (in app container)
docker compose exec app composer ci                         # lint + analyse + test:coverage

# Individual tools (all in app container)
docker compose exec app composer lint                       # Laravel Pint (PSR-12)
docker compose exec app composer analyse                    # PHPStan static analysis
docker compose exec app vendor/bin/phpunit                  # PHPUnit tests directly
docker compose exec app php artisan test                    # Laravel test runner
```

### Build & Assets
```bash
# Frontend builds (all in node container)
docker compose exec node npm run build                      # Production build
docker compose exec node npm run build:fast                # Fast production build
docker compose exec node npm run build:analyze             # With bundle analysis
docker compose exec node npm run dev                       # Development build with HMR
docker compose exec node npm run watch                     # Watch mode for development

# Package management (in node container)
docker compose exec node npm install                       # Install/update dependencies
docker compose exec node npm audit                         # Security audit
docker compose exec node npm outdated                      # Check for updates
```

### Database Operations
```bash
# Migrations (in app container)
docker compose exec app php artisan migrate                # Run migrations
docker compose exec app php artisan migrate:fresh --seed  # Fresh database with test data
docker compose exec app php artisan migrate:rollback      # Rollback last migration
docker compose exec app php artisan migrate:status        # Check migration status

# GEDCOM Import (background job)
# Use web interface at /trees/import or via API
# Background processing automatically handled by queue container

# Database connections (direct access to database containers)
docker compose exec postgres psql -U postgres -d leg          # PostgreSQL
docker compose exec mongodb mongosh -u leg -p password123     # MongoDB
docker compose exec neo4j cypher-shell -u neo4j -p password123 # Neo4j
docker compose exec redis redis-cli                           # Redis

# Database backups/restore (examples)
docker compose exec postgres pg_dump -U postgres leg > backup.sql
docker compose exec mongodb mongodump --username leg --password password123 --db leg --out /tmp/backup
```

### Docker Management
```bash
# Container lifecycle
docker compose up -d                                          # Start all services
docker compose down                                           # Stop all services
docker compose restart app                                   # Restart specific service
docker compose logs -f app                                   # Follow app logs

# Useful operations
docker compose exec app bash                                 # Shell into app container
docker compose build --no-cache                              # Rebuild all images
docker system prune -f                                       # Clean up Docker resources

# Advanced container management
docker compose exec app /usr/local/bin/fix-permissions.sh   # Fix file permissions
docker compose pull                                          # Pull latest images
docker compose down -v                                       # Stop and remove volumes (DATA LOSS!)
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
docker compose exec app chmod -R 777 storage bootstrap/cache
docker compose exec app /usr/local/bin/fix-permissions.sh

# Database connection issues
docker compose down && docker compose up -d     # Full restart
docker compose logs postgres                    # Check database logs

# Volume issues (corrupted data)
docker compose down -v                          # Remove volumes (DATA LOSS!)
docker volume prune                             # Clean up orphaned volumes

# Container health checks
docker compose ps                               # Check service status
docker compose exec app php artisan about      # Check Laravel environment
docker compose exec postgres pg_isready -U postgres  # Check PostgreSQL
```

## 🛠️ Development Workflow & Tooling

### Recommended Daily Workflow
1. **Start the stack**: `docker compose up -d`
2. **Build frontend assets**: `docker compose exec node npm run dev` (for development with HMR)
3. **Code with hot reload**: Frontend assets auto-rebuild on changes via node container
4. **Monitor**: Use Laravel Telescope (http://localhost/telescope) for debugging
5. **Queue monitoring**: Laravel Horizon available at http://localhost/horizon
6. **Before commits**: Run `docker compose exec app composer ci` to ensure code quality

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
- **Application logs**: `docker compose exec app php artisan pail` for real-time log streaming
- **Container logs**: `docker compose logs -f app queue nginx` for service monitoring
- **Health checks**: `docker compose ps` and `docker compose exec app php artisan about`

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
> All tooling is pre-installed in Docker containers - no local installation required.

- **PHP**: 8.4+ (pre-installed in app container with all extensions)
- **Laravel**: 12.x (latest LTS when available, via Composer in app container)
- **Node.js**: 24+ Alpine (pre-installed in node container for frontend tooling)
- **Databases**: PostgreSQL 16, MongoDB 7, Neo4j 5 Enterprise, Redis Alpine (all containerized)
- **Development Tools**: Composer, npm, Vite, PHPUnit, Laravel Pint, PHPStan (all containerized)

### Contributing Guidelines
- **Branch Strategy**: Feature branches from main, PR-based workflow
- **Code Review**: Automated CI checks + manual peer review required
- **Testing**: All new features must include comprehensive tests
- **Documentation**: Update relevant docs with code changes

## 🐚 Container Command Cheatsheet

### Essential One-Liners

```bash
# Laravel/PHP Commands (app container)
docker compose exec app php artisan migrate         # Run migrations
docker compose exec app php artisan tinker          # Laravel REPL
docker compose exec app php artisan queue:work      # Process jobs
docker compose exec app composer install            # Install dependencies
docker compose exec app composer test               # Run tests
docker compose exec app composer ci                 # Full CI suite

# Frontend/Node Commands (node container)
docker compose exec node npm install                # Install dependencies
docker compose exec node npm run dev                # Development build (HMR)
docker compose exec node npm run build              # Production build
docker compose exec node npm audit                  # Security audit

# Database Access (direct to database containers)
docker compose exec postgres psql -U postgres -d leg            # PostgreSQL shell
docker compose exec mongodb mongosh -u leg -p password123       # MongoDB shell
docker compose exec neo4j cypher-shell -u neo4j -p password123  # Neo4j shell
docker compose exec redis redis-cli                             # Redis shell

# Container Management
docker compose up -d                    # Start all services
docker compose down                     # Stop all services
docker compose logs -f app              # Follow app logs
docker compose ps                       # Check service status
docker compose exec app bash            # Shell into app container
docker compose restart app              # Restart specific service
```

### Productivity Aliases

Add these to your `~/.bashrc`, `~/.zshrc`, or PowerShell profile for faster development:

```bash
# Bash/Zsh aliases
alias dc='docker compose'
alias dcu='docker compose up -d'
alias dcd='docker compose down'
alias dcl='docker compose logs -f'
alias dce='docker compose exec'
alias dcea='docker compose exec app'
alias dcen='docker compose exec node'

# Common Laravel commands
alias art='docker compose exec app php artisan'
alias tinker='docker compose exec app php artisan tinker'
alias migrate='docker compose exec app php artisan migrate'
alias test='docker compose exec app composer test'
alias ci='docker compose exec app composer ci'

# Frontend commands
alias npm='docker compose exec node npm'
alias build='docker compose exec node npm run build'
alias dev='docker compose exec node npm run dev'
```

### PowerShell Functions (Windows)

```powershell
# Add to your PowerShell profile
function dc { docker compose $args }
function dcu { docker compose up -d }
function dcd { docker compose down }
function art { docker compose exec app php artisan $args }
function npm { docker compose exec node npm $args }
function composer { docker compose exec app composer $args }
```

### Common Workflow Examples

```bash
# Fresh start
dcu && art migrate:fresh --seed && npm run dev

# Run full test suite before commit
art test && composer ci

# Check everything is working
dc ps && art about && npm audit

# Database operations
art migrate && art db:seed --class=DemoDataSeeder

# Quick development loop
npm run dev &    # Start in background
art pail         # Watch logs in foreground
```

### Tips

- **Performance**: Use `docker compose exec` instead of `docker exec` for better performance
- **Logs**: Combine `docker compose logs -f service1 service2` to watch multiple services
- **Debugging**: Use `docker compose exec app bash` to get a shell inside the container
- **Database**: All database tools are available inside their respective containers
- **File permissions**: Run `docker compose exec app /usr/local/bin/fix-permissions.sh` if needed

---

**Quick Access URLs** (when stack is running):
- Application: http://localhost
- Neo4j Browser: http://localhost:7474
- Grafana: http://localhost:3000 (admin/admin)
- Prometheus: http://localhost:9090
- cAdvisor: http://localhost:8080

*This WARP.md was generated by analyzing the complete codebase structure, container configuration, and development toolchain.*
