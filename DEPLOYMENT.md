# 🚀 Shared Hosting Deployment Guide

This guide provides step-by-step instructions for deploying the Laravel E-Commerce platform to shared hosting environments.

## 📋 Prerequisites

### Hosting Requirements
- **PHP**: 8.2 or higher
- **MySQL**: 5.7 or higher  
- **Storage**: Minimum 500MB free space
- **Memory**: 128MB PHP memory limit (recommended)
- **Extensions**: zip, openssl, pdo, mbstring, tokenizer, xml, ctype, json, bcmath, gd

### Development Requirements  
- **Composer**: For dependency management
- **Node.js**: For asset compilation (development only)
- **Git**: For version control

## 🔧 Pre-Deployment Setup

### 1. Prepare Production Assets
```bash
# Install production dependencies
composer install --optimize-autoloader --no-dev

# Build optimized assets
npm run build

# Clear development caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 2. Environment Configuration
Create production `.env` file:
```env
APP_NAME="E-Commerce Store"
APP_ENV=production
APP_KEY=base64:your-32-character-key-here
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

# Mail Configuration (for shared hosting)
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="E-Commerce Store"

# Session & Cache (File-based for shared hosting)
SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=database

# File Storage
FILESYSTEM_DISK=public

# Shared Hosting Optimizations
OPTIMIZE_FOR_SHARED_HOSTING=true
```

### 3. Optimize for Production
```bash
# Generate optimized autoloader
composer dump-autoload --optimize

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize application
php artisan optimize
```

## 📁 File Structure for Upload

### Directory Structure
```
your_domain_folder/
├── public_html/              # Web-accessible directory
│   ├── index.php             # Laravel's public/index.php
│   ├── css/                  # Compiled CSS files
│   ├── js/                   # Compiled JS files
│   ├── images/               # Static images
│   ├── storage/              # Storage link
│   └── .htaccess             # URL rewriting rules
├── laravel_app/              # Laravel application (above web root)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   └── artisan
└── backups/                  # Database backups
```

## 🚀 Deployment Steps

### Step 1: Upload Files via FTP/cPanel
```bash
# Upload Laravel application to laravel_app/
# Upload public folder contents to public_html/
# Set proper file permissions (755 for directories, 644 for files)
```

### Step 2: Database Setup
```sql
-- Create database via cPanel or phpMyAdmin
CREATE DATABASE your_database_name;

-- Import structure and data
-- Run migrations via web interface or upload SQL dump
```

### Step 3: Configure Web Root
Update `public_html/index.php`:
```php
<?php
// Point to Laravel app directory above web root
$app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';

// Continue with Laravel bootstrap...
```

### Step 4: Storage Link Setup
Create symbolic link for storage:
```bash
# Via SSH (if available)
ln -s ../laravel_app/storage/app/public public_html/storage

# Or create via cPanel File Manager
# Link: public_html/storage -> ../laravel_app/storage/app/public
```

### Step 5: Database Migration
Run migrations via custom admin route or upload SQL:
```bash
# If SSH access available
php artisan migrate --force

# Or use web-based migration runner
# Visit: yourdomain.com/install (if implemented)
```

## 🔒 Security Configuration

### .htaccess for Laravel (public_html/.htaccess)
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options DENY
    Header always set X-Content-Type-Options nosniff
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Prevent access to sensitive files
<FilesMatch "\.(env|log|sql)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

### Additional Security (.htaccess for app directory)
```apache
# laravel_app/.htaccess
Order Allow,Deny
Deny from all
```

## 🗄️ Database Optimization

### MySQL Configuration
```sql
-- Optimize tables for shared hosting
OPTIMIZE TABLE products, categories, brands, product_images, product_variants;

-- Create essential indexes
ALTER TABLE products ADD INDEX idx_active_category (is_active, category_id);
ALTER TABLE categories ADD INDEX idx_active_menu (is_active, show_in_menu);
ALTER TABLE brands ADD INDEX idx_active_sort (is_active, sort_order);
```

### Backup Strategy
```bash
# Automated backup script (via cron if available)
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u username -p database_name > backup_$DATE.sql
tar -czf storage_backup_$DATE.tar.gz storage/
```

## ⚡ Performance Optimization

### PHP Configuration (php.ini)
```ini
# Memory optimization
memory_limit = 256M
max_execution_time = 300
max_input_vars = 3000

# File upload limits
upload_max_filesize = 10M
post_max_size = 12M

# OPcache (if available)
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 10000
```

### Application Optimizations
```php
// config/database.php - Optimize for shared hosting
'options' => [
    PDO::ATTR_PERSISTENT => true,
    PDO::ATTR_EMULATE_PREPARES => false,
],

// config/session.php - File-based sessions
'driver' => 'file',
'lifetime' => 120,
'expire_on_close' => false,
```

## 🔍 Troubleshooting

### Common Issues

#### 1. 500 Internal Server Error
```bash
# Check error logs
tail -f storage/logs/laravel.log

# Common fixes:
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chown -R www-data:www-data storage/
```

#### 2. Storage Link Issues
```bash
# Manual storage link creation
ln -sf ../laravel_app/storage/app/public public_html/storage

# Or via PHP script
symlink('../laravel_app/storage/app/public', 'public_html/storage');
```

#### 3. Database Connection Issues
```env
# Try different database configurations
DB_HOST=localhost
# or
DB_HOST=127.0.0.1
# or  
DB_HOST=your-server-name.com
```

#### 4. Asset Loading Issues
```bash
# Ensure correct asset URLs in production
APP_URL=https://yourdomain.com
ASSET_URL=https://yourdomain.com
```

### Performance Monitoring
```php
// Add to AppServiceProvider for shared hosting monitoring
public function boot()
{
    if (app()->environment('production')) {
        // Log slow queries
        DB::listen(function ($query) {
            if ($query->time > 1000) {
                Log::warning('Slow query detected', [
                    'sql' => $query->sql,
                    'time' => $query->time
                ]);
            }
        });
    }
}
```

## 📊 Monitoring & Maintenance

### Health Check Endpoint
```php
// routes/web.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'storage' => Storage::disk('public')->exists('test.txt') ? 'writable' : 'readonly',
        'cache' => Cache::has('health_check') ? 'working' : 'not_working'
    ]);
});
```

### Maintenance Mode
```bash
# Enable maintenance mode
php artisan down --message="Upgrading system" --retry=60

# Disable maintenance mode  
php artisan up
```

## 🔄 Update Process

### Safe Update Procedure
1. **Backup**: Database and files
2. **Test**: In staging environment
3. **Maintenance**: Enable maintenance mode
4. **Deploy**: Upload new files
5. **Migrate**: Run database updates
6. **Cache**: Clear and rebuild caches
7. **Test**: Verify functionality
8. **Live**: Disable maintenance mode

### Automated Deployment Script
```bash
#!/bin/bash
echo "Starting deployment..."

# Backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > backup_$(date +%Y%m%d_%H%M%S).sql

# Enable maintenance
php artisan down

# Update code
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader

# Build assets
npm run build

# Run migrations
php artisan migrate --force

# Clear caches
php artisan optimize

# Disable maintenance
php artisan up

echo "Deployment completed!"
```

---

**Need help?** Contact support or check the [troubleshooting section](README.md#troubleshooting) in the main documentation.