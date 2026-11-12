#!/bin/bash

# Deployment script for Laravel Genealogy application
# This script handles dependency upgrades, testing, and deployment

set -e  # Exit on any error

echo "🚀 Starting Laravel Genealogy Deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if required tools are installed
check_dependencies() {
    print_status "Checking system dependencies..."
    
    if ! command -v php &> /dev/null; then
        print_error "PHP is not installed or not in PATH"
        exit 1
    fi
    
    if ! command -v composer &> /dev/null; then
        print_error "Composer is not installed or not in PATH"
        exit 1
    fi
    
    if ! command -v node &> /dev/null; then
        print_error "Node.js is not installed or not in PATH"
        exit 1
    fi
    
    if ! command -v npm &> /dev/null; then
        print_error "NPM is not installed or not in PATH"
        exit 1
    fi
    
    # Check versions
    PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
    NODE_VERSION=$(node -v | sed 's/v//' | cut -d "." -f 1)
    
    print_success "PHP version: $PHP_VERSION"
    print_success "Node.js version: $NODE_VERSION"
    
    if [[ $(echo "$PHP_VERSION >= 8.3" | bc -l 2>/dev/null || echo "0") -eq 1 ]]; then
        print_success "PHP version is compatible (8.3+)"
    else
        print_warning "PHP version might not be optimal. Recommended: 8.4+"
    fi
    
    if [[ $NODE_VERSION -ge 20 ]]; then
        print_success "Node.js version is compatible (20+)"
    else
        print_warning "Node.js version might not be optimal. Recommended: 22+"
    fi
}

# Backup current state
backup_current_state() {
    print_status "Creating backup of current state..."
    
    BACKUP_DIR="backups/$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$BACKUP_DIR"
    
    # Backup important files
    if [ -f ".env" ]; then
        cp .env "$BACKUP_DIR/.env.backup"
    fi
    
    if [ -f "composer.lock" ]; then
        cp composer.lock "$BACKUP_DIR/composer.lock.backup"
    fi
    
    if [ -f "package-lock.json" ]; then
        cp package-lock.json "$BACKUP_DIR/package-lock.json.backup"
    fi
    
    print_success "Backup created in $BACKUP_DIR"
}

# Update PHP dependencies
update_php_dependencies() {
    print_status "Updating PHP dependencies..."
    
    # Clear composer cache
    composer clear-cache
    
    # Update dependencies
    if composer update --no-dev --optimize-autoloader --no-interaction; then
        print_success "PHP dependencies updated successfully"
    else
        print_error "Failed to update PHP dependencies"
        exit 1
    fi
    
    # Install dev dependencies for testing
    if composer install --optimize-autoloader --no-interaction; then
        print_success "PHP dev dependencies installed successfully"
    else
        print_error "Failed to install PHP dev dependencies"
        exit 1
    fi
}

# Update Node.js dependencies
update_node_dependencies() {
    print_status "Updating Node.js dependencies..."
    
    # Clear npm cache
    npm cache clean --force
    
    # Install dependencies
    if npm ci; then
        print_success "Node.js dependencies installed successfully"
    else
        print_error "Failed to install Node.js dependencies"
        exit 1
    fi
    
    # Audit for vulnerabilities
    print_status "Auditing dependencies for security vulnerabilities..."
    npm audit --audit-level moderate || print_warning "Some vulnerabilities found, consider running 'npm audit fix'"
}

# Run tests
run_tests() {
    print_status "Running test suite..."
    
    # PHP tests
    print_status "Running PHP tests..."
    if composer test; then
        print_success "PHP tests passed"
    else
        print_error "PHP tests failed"
        exit 1
    fi
    
    # Frontend tests
    print_status "Running frontend tests..."
    if npm test; then
        print_success "Frontend tests passed"
    else
        print_error "Frontend tests failed"
        exit 1
    fi
    
    # Static analysis
    print_status "Running static analysis..."
    if composer analyse; then
        print_success "Static analysis passed"
    else
        print_warning "Static analysis found issues, but continuing..."
    fi
    
    # Linting
    print_status "Running code linting..."
    composer lint || print_warning "Code style issues found, but continuing..."
    npm run lint || print_warning "Frontend linting issues found, but continuing..."
}

# Build assets
build_assets() {
    print_status "Building frontend assets..."
    
    if npm run build; then
        print_success "Assets built successfully"
    else
        print_error "Failed to build assets"
        exit 1
    fi
    
    # Analyze bundle size
    print_status "Analyzing bundle size..."
    npm run build:analyze || print_warning "Bundle analysis failed, but continuing..."
}

# Optimize application
optimize_application() {
    print_status "Optimizing application..."
    
    # Laravel optimizations
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    
    # Only run in production
    if [ "$APP_ENV" = "production" ]; then
        php artisan optimize
        print_success "Production optimizations applied"
    fi
}

# Database migrations
run_migrations() {
    print_status "Running database migrations..."
    
    if php artisan migrate --force; then
        print_success "Database migrations completed"
    else
        print_error "Database migrations failed"
        exit 1
    fi
}

# Clear caches
clear_caches() {
    print_status "Clearing application caches..."
    
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    
    print_success "Caches cleared"
}

# Health check
health_check() {
    print_status "Running health checks..."
    
    # Check if artisan commands work
    if php artisan --version > /dev/null; then
        print_success "Laravel application is responsive"
    else
        print_error "Laravel application is not responding"
        exit 1
    fi
    
    # Check database connection
    if php artisan migrate:status > /dev/null; then
        print_success "Database connection is working"
    else
        print_error "Database connection failed"
        exit 1
    fi
}

# Main deployment process
main() {
    echo "🎯 Laravel Genealogy Deployment Script"
    echo "======================================="
    
    # Parse command line arguments
    SKIP_TESTS=false
    SKIP_BUILD=false
    PRODUCTION_MODE=false
    
    while [[ $# -gt 0 ]]; do
        case $1 in
            --skip-tests)
                SKIP_TESTS=true
                shift
                ;;
            --skip-build)
                SKIP_BUILD=true
                shift
                ;;
            --production)
                PRODUCTION_MODE=true
                export APP_ENV=production
                shift
                ;;
            --help)
                echo "Usage: $0 [OPTIONS]"
                echo ""
                echo "Options:"
                echo "  --skip-tests     Skip running tests"
                echo "  --skip-build     Skip building frontend assets"
                echo "  --production     Run in production mode"
                echo "  --help           Show this help message"
                exit 0
                ;;
            *)
                print_error "Unknown option $1"
                exit 1
                ;;
        esac
    done
    
    if [ "$PRODUCTION_MODE" = true ]; then
        print_warning "Running in PRODUCTION mode!"
    fi
    
    # Execute deployment steps
    check_dependencies
    backup_current_state
    update_php_dependencies
    update_node_dependencies
    
    if [ "$SKIP_TESTS" = false ]; then
        run_tests
    else
        print_warning "Skipping tests as requested"
    fi
    
    if [ "$SKIP_BUILD" = false ]; then
        build_assets
    else
        print_warning "Skipping asset build as requested"
    fi
    
    clear_caches
    run_migrations
    optimize_application
    health_check
    
    echo ""
    echo "🎉 Deployment completed successfully!"
    echo "======================================="
    
    # Final summary
    print_success "✅ Dependencies updated"
    print_success "✅ Tests $([ "$SKIP_TESTS" = true ] && echo "skipped" || echo "passed")"
    print_success "✅ Assets $([ "$SKIP_BUILD" = true ] && echo "skipped" || echo "built")"
    print_success "✅ Database migrated"
    print_success "✅ Application optimized"
    print_success "✅ Health checks passed"
    
    echo ""
    print_status "Your Laravel Genealogy application is ready!"
    
    if [ "$PRODUCTION_MODE" = false ]; then
        echo ""
        print_status "To start the development server, run:"
        echo "php artisan serve"
        echo ""
        print_status "To start the queue worker, run:"
        echo "php artisan queue:work"
        echo ""
        print_status "To start the scheduler, add this to your crontab:"
        echo "* * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1"
    fi
}

# Error handling
trap 'print_error "Deployment failed! Check the logs above for details."' ERR

# Run main function
main "$@"
