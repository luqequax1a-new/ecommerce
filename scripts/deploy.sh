#!/bin/bash

# Laravel E-commerce Deployment Script for Shared Hosting
# Optimized for cPanel and similar shared hosting environments

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
DEPLOYMENT_LOG="deployment.log"
BACKUP_DIR="backups"
TEMP_DIR="temp_deployment"

# Functions
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$DEPLOYMENT_LOG"
}

print_header() {
    echo -e "${BLUE}"
    echo "=================================================="
    echo "  Laravel E-commerce Deployment Script"
    echo "  Shared Hosting Optimized"
    echo "=================================================="
    echo -e "${NC}"
}

check_requirements() {
    log "Checking deployment requirements..."
    
    # Check if we're in Laravel root
    if [ ! -f "artisan" ]; then
        echo -e "${RED}Error: Not in Laravel root directory${NC}"
        exit 1
    fi
    
    # Check PHP version
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    if [[ ! "$PHP_VERSION" =~ ^8\.[2-9] ]]; then
        echo -e "${YELLOW}Warning: PHP version $PHP_VERSION may not be optimal (8.2+ recommended)${NC}"
    fi
    
    # Check required extensions
    local required_extensions=("pdo" "mbstring" "openssl" "tokenizer" "xml" "curl")
    for ext in "${required_extensions[@]}"; do
        if ! php -m | grep -q "$ext"; then
            echo -e "${RED}Error: Required PHP extension '$ext' is missing${NC}"
            exit 1
        fi
    done
    
    echo -e "${GREEN}✓ Requirements check passed${NC}"
}

create_backup() {
    log "Creating backup before deployment..."
    
    # Create backup directory
    mkdir -p "$BACKUP_DIR"
    
    local backup_name="backup_$(date +%Y%m%d_%H%M%S)"
    local backup_path="$BACKUP_DIR/$backup_name"
    
    mkdir -p "$backup_path"
    
    # Backup database
    if [ -f ".env" ]; then
        source .env
        if [ "$DB_CONNECTION" = "mysql" ]; then
            log "Backing up MySQL database..."
            mysqldump -h "$DB_HOST" -P "${DB_PORT:-3306}" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > "$backup_path/database.sql"
        fi
    fi
    
    # Backup important files
    cp -r storage/app/public "$backup_path/uploads" 2>/dev/null || true
    cp .env "$backup_path/.env" 2>/dev/null || true
    
    echo -e "${GREEN}✓ Backup created at $backup_path${NC}"
    log "Backup completed: $backup_path"
}

install_dependencies() {
    log "Installing/updating dependencies..."
    
    # Update Composer dependencies for production
    composer install --no-dev --optimize-autoloader --no-interaction
    
    echo -e "${GREEN}✓ Dependencies installed${NC}"
}

optimize_application() {
    log "Optimizing application for production..."
    
    # Clear all caches first
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    
    # Optimize for production
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Optimize Composer autoloader
    composer dump-autoload --optimize
    
    echo -e "${GREEN}✓ Application optimized${NC}"
}

run_migrations() {
    log "Running database migrations..."
    
    # Check if migrations are needed
    if php artisan migrate:status | grep -q "Pending"; then
        read -p "Pending migrations found. Run migrations? (y/N): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            php artisan migrate --force
            echo -e "${GREEN}✓ Migrations completed${NC}"
        else
            echo -e "${YELLOW}⚠ Migrations skipped${NC}"
        fi
    else
        echo -e "${GREEN}✓ No pending migrations${NC}"
    fi
}

set_permissions() {
    log "Setting proper file permissions..."
    
    # Set directory permissions
    find . -type d -exec chmod 755 {} \;
    
    # Set file permissions
    find . -type f -exec chmod 644 {} \;
    
    # Make storage and bootstrap/cache writable
    chmod -R 775 storage
    chmod -R 775 bootstrap/cache
    
    # Make scripts executable
    find scripts -name "*.sh" -exec chmod +x {} \; 2>/dev/null || true
    
    echo -e "${GREEN}✓ Permissions set${NC}"
}

setup_environment() {
    log "Setting up production environment..."
    
    # Check if .env exists
    if [ ! -f ".env" ]; then
        if [ -f ".env.production" ]; then
            cp .env.production .env
            echo -e "${GREEN}✓ Production environment file copied${NC}"
        else
            echo -e "${RED}Error: No .env file found${NC}"
            exit 1
        fi
    fi
    
    # Generate app key if not set
    if ! grep -q "APP_KEY=base64:" .env; then
        php artisan key:generate --force
        echo -e "${GREEN}✓ Application key generated${NC}"
    fi
    
    # Ensure production settings
    sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env 2>/dev/null || true
    sed -i 's/APP_ENV=local/APP_ENV=production/' .env 2>/dev/null || true
    
    echo -e "${GREEN}✓ Environment configured for production${NC}"
}

setup_symbolic_links() {
    log "Setting up symbolic links for shared hosting..."
    
    # Create public_html symlink if it doesn't exist (for cPanel)
    if [ ! -L "public_html" ] && [ ! -d "public_html" ]; then
        ln -s public public_html
        echo -e "${GREEN}✓ public_html symlink created${NC}"
    fi
    
    # Storage link
    php artisan storage:link
    
    echo -e "${GREEN}✓ Symbolic links configured${NC}"
}

cleanup_deployment() {
    log "Cleaning up deployment files..."
    
    # Remove development files
    rm -rf node_modules
    rm -rf tests
    rm -f phpunit.xml
    rm -f webpack.mix.js
    rm -f package.json
    rm -f package-lock.json
    
    # Clean up temporary files
    rm -rf "$TEMP_DIR"
    
    echo -e "${GREEN}✓ Cleanup completed${NC}"
}

verify_deployment() {
    log "Verifying deployment..."
    
    # Check if application responds
    if php artisan --version &> /dev/null; then
        echo -e "${GREEN}✓ Laravel application is functional${NC}"
    else
        echo -e "${RED}✗ Laravel application error${NC}"
        return 1
    fi
    
    # Check database connection
    if php artisan migrate:status &> /dev/null; then
        echo -e "${GREEN}✓ Database connection OK${NC}"
    else
        echo -e "${RED}✗ Database connection failed${NC}"
        return 1
    fi
    
    # Check storage permissions
    if [ -w "storage" ]; then
        echo -e "${GREEN}✓ Storage is writable${NC}"
    else
        echo -e "${RED}✗ Storage permission issues${NC}"
        return 1
    fi
    
    log "Deployment verification completed successfully"
    return 0
}

generate_deployment_report() {
    log "Generating deployment report..."
    
    local report_file="deployment_report_$(date +%Y%m%d_%H%M%S).txt"
    
    {
        echo "Laravel E-commerce Deployment Report"
        echo "Deployment Date: $(date)"
        echo "========================================"
        echo ""
        
        echo "System Information:"
        echo "- PHP Version: $(php -r 'echo PHP_VERSION;')"
        echo "- Laravel Version: $(php artisan --version | cut -d' ' -f3)"
        echo "- Environment: $(grep APP_ENV .env | cut -d'=' -f2)"
        echo ""
        
        echo "Database:"
        echo "- Connection: $(grep DB_CONNECTION .env | cut -d'=' -f2)"
        echo "- Migration Status:"
        php artisan migrate:status | head -10
        echo ""
        
        echo "Cache Status:"
        echo "- Config Cached: $([ -f bootstrap/cache/config.php ] && echo 'Yes' || echo 'No')"
        echo "- Routes Cached: $([ -f bootstrap/cache/routes-v7.php ] && echo 'Yes' || echo 'No')"
        echo "- Views Cached: $(find storage/framework/views -name '*.php' | wc -l) compiled views"
        echo ""
        
        echo "File Permissions:"
        echo "- Storage: $(ls -ld storage | awk '{print $1}')"
        echo "- Bootstrap Cache: $(ls -ld bootstrap/cache | awk '{print $1}')"
        echo ""
        
        echo "Disk Usage:"
        du -sh . | awk '{print "- Total Size: " $1}'
        du -sh storage | awk '{print "- Storage: " $1}'
        du -sh vendor | awk '{print "- Vendor: " $1}'
        
    } > "$report_file"
    
    echo -e "${GREEN}✓ Deployment report generated: $report_file${NC}"
}

show_usage() {
    echo "Laravel E-commerce Deployment Script"
    echo ""
    echo "Usage: $0 [command]"
    echo ""
    echo "Commands:"
    echo "  full           Run complete deployment (default)"
    echo "  quick          Quick deployment (no backup, no migrations)"
    echo "  rollback       Rollback to previous version"
    echo "  backup         Create backup only"
    echo "  optimize       Optimize application only"
    echo "  verify         Verify deployment only"
    echo "  help           Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0              # Run full deployment"
    echo "  $0 quick        # Quick deployment"
    echo "  $0 backup       # Create backup only"
}

# Main execution
case "${1:-full}" in
    "full")
        print_header
        log "Starting full deployment..."
        
        check_requirements
        create_backup
        install_dependencies
        setup_environment
        run_migrations
        optimize_application
        set_permissions
        setup_symbolic_links
        cleanup_deployment
        verify_deployment
        generate_deployment_report
        
        echo -e "${GREEN}"
        echo "=================================================="
        echo "  🎉 DEPLOYMENT COMPLETED SUCCESSFULLY!"
        echo "=================================================="
        echo -e "${NC}"
        log "Full deployment completed successfully"
        ;;
    "quick")
        print_header
        log "Starting quick deployment..."
        
        check_requirements
        install_dependencies
        setup_environment
        optimize_application
        set_permissions
        verify_deployment
        
        echo -e "${GREEN}✓ Quick deployment completed${NC}"
        ;;
    "backup")
        print_header
        create_backup
        ;;
    "optimize")
        print_header
        optimize_application
        ;;
    "verify")
        print_header
        verify_deployment
        ;;
    "help")
        show_usage
        ;;
    *)
        echo -e "${RED}Error: Unknown command '$1'${NC}"
        echo ""
        show_usage
        exit 1
        ;;
esac