#!/bin/bash

# Quick Setup for Shared Hosting
# Simplified deployment script for limited access environments

set -e

# Colors
GREEN='\\033[0;32m'
YELLOW='\\033[1;33m'
RED='\\033[0;31m'
NC='\\033[0m'

echo -e \"${GREEN}\"
echo \"===========================================\"
echo \"  Laravel E-commerce Quick Setup\"
echo \"  Shared Hosting Edition\"
echo \"===========================================\"
echo -e \"${NC}\"

echo \"This script will help you set up Laravel e-commerce on shared hosting.\"
echo \"Please ensure you have uploaded all files to your hosting account.\"
echo \"\"

# Step 1: Check PHP version
echo \"Step 1: Checking PHP version...\"
PHP_VERSION=$(php -r \"echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;\")
echo \"Current PHP version: $PHP_VERSION\"

if [[ \"$PHP_VERSION\" < \"8.2\" ]]; then
    echo -e \"${RED}Warning: PHP 8.2+ is recommended. Current: $PHP_VERSION${NC}\"
    echo \"Please contact your hosting provider to upgrade PHP.\"
else
    echo -e \"${GREEN}✓ PHP version is compatible${NC}\"
fi
echo \"\"

# Step 2: Environment setup
echo \"Step 2: Setting up environment...\"

if [ ! -f \".env\" ]; then
    if [ -f \".env.production\" ]; then
        cp \".env.production\" \".env\"
        echo -e \"${GREEN}✓ Environment file created from .env.production${NC}\"
    elif [ -f \".env.example\" ]; then
        cp \".env.example\" \".env\"
        echo -e \"${YELLOW}⚠ Environment file created from .env.example${NC}\"
        echo \"You need to configure the .env file manually.\"
    else
        echo -e \"${RED}Error: No environment template found${NC}\"
        exit 1
    fi
else
    echo -e \"${GREEN}✓ Environment file already exists${NC}\"
fi

# Generate app key if needed
if ! grep -q \"APP_KEY=base64:\" \".env\"; then
    echo \"Generating application key...\"
    php artisan key:generate --force
    echo -e \"${GREEN}✓ Application key generated${NC}\"
fi
echo \"\"

# Step 3: Install dependencies
echo \"Step 3: Installing dependencies...\"

if command -v composer &> /dev/null; then
    echo \"Installing Composer dependencies...\"
    composer install --no-dev --optimize-autoloader --no-interaction
    echo -e \"${GREEN}✓ Composer dependencies installed${NC}\"
else
    echo -e \"${YELLOW}⚠ Composer not found. Please install dependencies manually.${NC}\"
    echo \"Download vendor.zip from your development environment and extract it here.\"
fi
echo \"\"

# Step 4: Set up storage
echo \"Step 4: Setting up storage directories...\"

# Create storage directories
mkdir -p storage/app/public/uploads
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set basic permissions (what's possible on shared hosting)
chmod 755 storage
chmod 755 storage/app
chmod 755 storage/app/public
chmod 755 storage/app/public/uploads
chmod 755 storage/framework
chmod 755 storage/framework/cache
chmod 755 storage/framework/sessions
chmod 755 storage/framework/views
chmod 755 storage/logs
chmod 755 bootstrap/cache

echo -e \"${GREEN}✓ Storage directories created${NC}\"

# Create storage link
if [ ! -L \"public/storage\" ]; then
    if php artisan storage:link 2>/dev/null; then
        echo -e \"${GREEN}✓ Storage symlink created${NC}\"
    else
        echo -e \"${YELLOW}⚠ Could not create storage symlink automatically${NC}\"
        echo \"Please create it manually or contact your hosting provider.\"
    fi
fi
echo \"\"

# Step 5: Database setup
echo \"Step 5: Database configuration...\"
echo \"Please make sure you have configured your database settings in .env file:\"
echo \"- DB_HOST (usually localhost)\"
echo \"- DB_DATABASE (your database name)\"
echo \"- DB_USERNAME (your database user)\"
echo \"- DB_PASSWORD (your database password)\"
echo \"\"

read -p \"Have you configured your database settings? (y/N): \" -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo \"Testing database connection...\"
    if php artisan migrate:status &> /dev/null; then
        echo -e \"${GREEN}✓ Database connection successful${NC}\"
        
        echo \"Running database migrations...\"
        php artisan migrate --force
        echo -e \"${GREEN}✓ Database migrations completed${NC}\"
        
        read -p \"Do you want to seed essential data (units, attributes)? (y/N): \" -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            php artisan db:seed --class=UnitsSeeder
            php artisan db:seed --class=ProductAttributesSeeder
            echo -e \"${GREEN}✓ Essential data seeded${NC}\"
        fi
    else
        echo -e \"${RED}✗ Database connection failed${NC}\"
        echo \"Please check your database configuration in .env file.\"
    fi
else
    echo -e \"${YELLOW}⚠ Database setup skipped${NC}\"
    echo \"Please configure your database and run: php artisan migrate\"
fi
echo \"\"

# Step 6: Optimize for production
echo \"Step 6: Optimizing for production...\"

# Clear caches first
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo -e \"${GREEN}✓ Application optimized for production${NC}\"
echo \"\"

# Step 7: Security check
echo \"Step 7: Basic security check...\"

security_issues=0

# Check .env file
if [ -f \".env\" ]; then
    chmod 644 .env
    echo -e \"${GREEN}✓ .env file permissions set${NC}\"
fi

# Check debug mode
if grep -q \"APP_DEBUG=true\" .env 2>/dev/null; then
    echo -e \"${RED}⚠ APP_DEBUG is enabled - should be false in production${NC}\"
    security_issues=$((security_issues + 1))
fi

# Check app key
if ! grep -q \"APP_KEY=base64:\" .env 2>/dev/null; then
    echo -e \"${RED}⚠ APP_KEY is not properly set${NC}\"
    security_issues=$((security_issues + 1))
fi

if [ $security_issues -eq 0 ]; then
    echo -e \"${GREEN}✓ Basic security checks passed${NC}\"
else
    echo -e \"${YELLOW}⚠ Found $security_issues security issues to review${NC}\"
fi
echo \"\"

# Step 8: Final instructions
echo -e \"${GREEN}\"
echo \"===========================================\"
echo \"  🎉 SETUP COMPLETED!\"
echo \"===========================================\"
echo -e \"${NC}\"

echo \"Next steps:\"
echo \"1. Point your domain to the 'public' directory\"
echo \"2. Configure SSL certificate\"
echo \"3. Review and update .env file settings:\"
echo \"   - APP_URL (your domain)\"
echo \"   - MAIL_* settings\"
echo \"   - Any other custom settings\"
echo \"4. Test your application\"
echo \"5. Set up regular backups\"
echo \"\"
echo \"Important URLs:\"
echo \"- Homepage: /\"
echo \"- Admin Panel: /admin\"
echo \"- System Info: /admin/system/info\"
echo \"\"
echo \"Useful commands:\"
echo \"- Check status: php artisan migrate:status\"
echo \"- Clear cache: php artisan cache:clear\"
echo \"- View logs: tail storage/logs/laravel.log\"
echo \"\"

# Create a simple status check file
cat > \"status.php\" << 'EOF'
<?php
// Simple status check for shared hosting
echo \"<h2>Laravel E-commerce Status</h2>\";

try {
    // Check if Laravel is loaded
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    
    echo \"<p style='color: green;'>✓ Laravel application loaded successfully</p>\";
    
    // Check database connection
    $kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
    $kernel->bootstrap();
    
    $pdo = DB::connection()->getPdo();
    echo \"<p style='color: green;'>✓ Database connection successful</p>\";
    
    // Check storage permissions
    if (is_writable('storage')) {
        echo \"<p style='color: green;'>✓ Storage directory is writable</p>\";
    } else {
        echo \"<p style='color: orange;'>⚠ Storage directory permission issues</p>\";
    }
    
    echo \"<p><strong>Status: Application is working correctly!</strong></p>\";
    
} catch (Exception $e) {
    echo \"<p style='color: red;'>✗ Error: \" . htmlspecialchars($e->getMessage()) . \"</p>\";
    echo \"<p>Please check your configuration and try again.</p>\";
}

echo \"<hr>\";
echo \"<p><small>PHP Version: \" . PHP_VERSION . \"</small></p>\";
echo \"<p><small>Generated: \" . date('Y-m-d H:i:s') . \"</small></p>\";
echo \"<p><small>Delete this file after testing: status.php</small></p>\";
?>
EOF

echo \"Created status.php for testing. Visit http://yourdomain.com/status.php\"
echo \"Remember to delete status.php after testing!\"
echo \"\"
echo \"If you encounter any issues, check the deployment guide in docs/DEPLOYMENT_GUIDE.md\"

echo -e \"${GREEN}Setup completed successfully!${NC}\"