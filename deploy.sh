#!/bin/bash

# Laravel E-commerce Production Deployment Script
# Optimized for Shared Hosting Environments
# Author: AI Assistant
# Version: 1.0

set -e  # Exit on any error

# Colors for output
RED='\\033[0;31m'
GREEN='\\033[0;32m'
YELLOW='\\033[1;33m'
BLUE='\\033[0;34m'
NC='\\033[0m' # No Color

# Configuration
APP_NAME=\"Laravel E-commerce\"
PHP_VERSION=\"8.2\"
REQUIRED_EXTENSIONS=(\"gd\" \"curl\" \"zip\" \"pdo_mysql\" \"mbstring\" \"xml\" \"tokenizer\")
BACKUP_DIR=\"backups\"
LOG_FILE=\"deployment.log\"

# Functions
log() {
    echo \"[$(date '+%Y-%m-%d %H:%M:%S')] $1\" | tee -a \"$LOG_FILE\"
}

print_header() {
    echo -e \"${BLUE}\"
    echo \"===========================================\"
    echo \"  $APP_NAME - Production Deployment\"
    echo \"  Shared Hosting Optimized\"
    echo \"===========================================\"
    echo -e \"${NC}\"
}

check_requirements() {
    log \"Checking system requirements...\"
    
    # Check PHP version
    if ! command -v php &> /dev/null; then
        echo -e \"${RED}Error: PHP is not installed${NC}\"
        exit 1
    fi
    
    PHP_CURRENT=$(php -r \"echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;\")
    if [[ \"$PHP_CURRENT\" < \"$PHP_VERSION\" ]]; then
        echo -e \"${RED}Error: PHP $PHP_VERSION or higher is required. Current: $PHP_CURRENT${NC}\"
        exit 1
    fi
    
    echo -e \"${GREEN}✓ PHP version: $PHP_CURRENT${NC}\"
    
    # Check PHP extensions
    for ext in \"${REQUIRED_EXTENSIONS[@]}\"; do
        if ! php -m | grep -i \"$ext\" &> /dev/null; then
            echo -e \"${RED}Error: PHP extension '$ext' is not installed${NC}\"
            exit 1
        fi
        echo -e \"${GREEN}✓ PHP extension: $ext${NC}\"
    done
    
    # Check Composer
    if ! command -v composer &> /dev/null; then
        echo -e \"${RED}Error: Composer is not installed${NC}\"
        exit 1
    fi
    
    echo -e \"${GREEN}✓ Composer is available${NC}\"
    
    # Check Node.js (optional for frontend assets)
    if command -v node &> /dev/null; then
        NODE_VERSION=$(node --version)
        echo -e \"${GREEN}✓ Node.js: $NODE_VERSION${NC}\"
    else
        echo -e \"${YELLOW}⚠ Node.js not found (optional for frontend builds)${NC}\"
    fi
}

create_backup() {
    if [ -d \"public\" ]; then
        log \"Creating backup...\"
        mkdir -p \"$BACKUP_DIR\"
        BACKUP_NAME=\"backup_$(date +%Y%m%d_%H%M%S).tar.gz\"
        
        # Create backup excluding unnecessary files
        tar -czf \"$BACKUP_DIR/$BACKUP_NAME\" \\n            --exclude=\"node_modules\" \\n            --exclude=\"vendor\" \\n            --exclude=\".git\" \\n            --exclude=\"storage/logs/*.log\" \\n            --exclude=\"storage/framework/cache/*\" \\n            --exclude=\"storage/framework/sessions/*\" \\n            --exclude=\"storage/framework/views/*\" \\n            .
            
        echo -e \"${GREEN}✓ Backup created: $BACKUP_DIR/$BACKUP_NAME${NC}\"
        
        # Keep only last 5 backups
        cd \"$BACKUP_DIR\"
        ls -t backup_*.tar.gz | tail -n +6 | xargs -r rm
        cd ..
    else
        echo -e \"${YELLOW}⚠ No existing installation found, skipping backup${NC}\"
    fi
}

install_dependencies() {
    log \"Installing PHP dependencies...\"
    
    # Install composer dependencies for production
    COMPOSER_MEMORY_LIMIT=-1 composer install \\n        --no-dev \\n        --optimize-autoloader \\n        --no-interaction \\n        --prefer-dist
        
    echo -e \"${GREEN}✓ Composer dependencies installed${NC}\"
    
    # Install/build frontend assets if package.json exists
    if [ -f \"package.json\" ]; then
        log \"Installing and building frontend assets...\"
        
        if command -v npm &> /dev/null; then
            npm ci --production
            npm run build
            echo -e \"${GREEN}✓ Frontend assets built${NC}\"
        else
            echo -e \"${YELLOW}⚠ npm not found, skipping frontend build${NC}\"
        fi
    fi
}

setup_environment() {
    log \"Setting up environment...\"
    
    # Create .env file if it doesn't exist
    if [ ! -f \".env\" ]; then
        if [ -f \".env.production\" ]; then
            cp \".env.production\" \".env\"
            echo -e \"${GREEN}✓ Copied .env.production to .env${NC}\"
        elif [ -f \".env.example\" ]; then
            cp \".env.example\" \".env\"
            echo -e \"${YELLOW}⚠ Copied .env.example to .env - Please configure manually${NC}\"
        else
            echo -e \"${RED}Error: No .env template found${NC}\"
            exit 1
        fi
    fi
    
    # Generate application key if not set
    if ! grep -q \"APP_KEY=base64:\" \".env\"; then
        php artisan key:generate --force
        echo -e \"${GREEN}✓ Application key generated${NC}\"
    fi
    
    # Set proper permissions
    chmod 644 .env
    echo -e \"${GREEN}✓ Environment file configured${NC}\"
}

setup_storage() {
    log \"Setting up storage and permissions...\"
    
    # Create storage directories
    mkdir -p storage/app/public/uploads
    mkdir -p storage/framework/cache
    mkdir -p storage/framework/sessions
    mkdir -p storage/framework/views
    mkdir -p storage/logs
    
    # Set permissions (compatible with shared hosting)
    find storage -type f -exec chmod 644 {} \\;
    find storage -type d -exec chmod 755 {} \\;
    
    # Bootstrap cache directory
    mkdir -p bootstrap/cache
    chmod 755 bootstrap/cache
    
    # Create symlink for public storage if it doesn't exist
    if [ ! -L \"public/storage\" ]; then
        php artisan storage:link
        echo -e \"${GREEN}✓ Storage symlink created${NC}\"
    fi
    
    echo -e \"${GREEN}✓ Storage setup completed${NC}\"
}

optimize_application() {
    log \"Optimizing application for production...\"
    
    # Clear all caches first
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan cache:clear
    
    # Cache configurations for production
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Optimize autoloader
    composer dump-autoload --optimize
    
    echo -e \"${GREEN}✓ Application optimized${NC}\"
}

run_migrations() {
    log \"Running database migrations...\"
    
    # Check database connection
    if php artisan migrate:status &> /dev/null; then
        # Run migrations
        php artisan migrate --force
        echo -e \"${GREEN}✓ Database migrations completed${NC}\"
    else
        echo -e \"${RED}Error: Cannot connect to database. Please check configuration.${NC}\"
        exit 1
    fi
}

seed_essential_data() {
    read -p \"Do you want to seed essential data (units, attributes)? (y/N): \" -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        log \"Seeding essential data...\"
        php artisan db:seed --class=UnitsSeeder
        php artisan db:seed --class=ProductAttributesSeeder
        echo -e \"${GREEN}✓ Essential data seeded${NC}\"
    fi
}

final_checks() {
    log \"Performing final checks...\"
    
    # Check critical files
    CRITICAL_FILES=(\".env\" \"public/index.php\" \"artisan\")
    for file in \"${CRITICAL_FILES[@]}\"; do
        if [ ! -f \"$file\" ]; then
            echo -e \"${RED}Error: Critical file missing: $file${NC}\"
            exit 1
        fi
    done
    
    # Check if application key is set
    if ! grep -q \"APP_KEY=base64:\" \".env\"; then
        echo -e \"${RED}Error: APP_KEY is not properly set${NC}\"
        exit 1
    fi
    
    # Test basic functionality
    if php artisan --version &> /dev/null; then
        echo -e \"${GREEN}✓ Laravel application is functional${NC}\"
    else
        echo -e \"${RED}Error: Laravel application test failed${NC}\"
        exit 1
    fi
    
    echo -e \"${GREEN}✓ All checks passed${NC}\"
}

print_completion() {
    echo -e \"${GREEN}\"
    echo \"===========================================\"
    echo \"  🎉 DEPLOYMENT COMPLETED SUCCESSFULLY!\"
    echo \"===========================================\"
    echo -e \"${NC}\"
    
    echo \"Next steps:\"
    echo \"1. Configure your web server to point to the 'public' directory\"
    echo \"2. Update .env file with your production settings\"
    echo \"3. Configure SSL certificate\"
    echo \"4. Set up regular backups\"
    echo \"5. Configure monitoring and logging\"
    echo \"\"
    echo \"Important URLs:\"
    echo \"- Admin Panel: /admin\"
    echo \"- System Info: /admin/system/info\"
    echo \"\"
    echo \"Log file: $LOG_FILE\"
}

# Main execution
main() {
    print_header
    
    log \"Starting deployment process...\"
    
    check_requirements
    create_backup
    install_dependencies
    setup_environment
    setup_storage
    run_migrations
    seed_essential_data
    optimize_application
    final_checks
    
    print_completion
    
    log \"Deployment completed successfully!\"
}

# Run main function
main \"$@\"