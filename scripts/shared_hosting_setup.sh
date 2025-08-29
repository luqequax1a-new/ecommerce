#!/bin/bash

# Shared Hosting Optimization Script for Laravel E-commerce
# Specifically designed for cPanel and similar shared hosting environments

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
LOG_FILE="hosting_optimization.log"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

print_header() {
    echo -e "${BLUE}"
    echo "=================================================="
    echo "  Shared Hosting Optimization Script"
    echo "  cPanel & Shared Hosting Compatible"
    echo "=================================================="
    echo -e "${NC}"
}

create_htaccess_optimizations() {
    log "Creating optimized .htaccess files..."
    
    # Main .htaccess for Laravel (root directory)
    cat > .htaccess << 'EOF'
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
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Gzip Compression
<IfModule mod_deflate.c>
    <IfModule mod_setenvif.c>
        <IfModule mod_headers.c>
            SetEnvIfNoCase ^(Accept-EncodXng|X-cept-Encoding|X{15}|~{15}|-{15})$ ^((gzip|deflate)\s*,?\s*)+|[X~-]{4,13}$ HAVE_Accept-Encoding
            RequestHeader append Accept-Encoding "gzip,deflate" env=HAVE_Accept-Encoding
        </IfModule>
    </IfModule>
    
    <IfModule mod_filter.c>
        AddOutputFilterByType DEFLATE text/plain
        AddOutputFilterByType DEFLATE text/html
        AddOutputFilterByType DEFLATE text/xml
        AddOutputFilterByType DEFLATE text/css
        AddOutputFilterByType DEFLATE application/xml
        AddOutputFilterByType DEFLATE application/xhtml+xml
        AddOutputFilterByType DEFLATE application/rss+xml
        AddOutputFilterByType DEFLATE application/javascript
        AddOutputFilterByType DEFLATE application/x-javascript
        AddOutputFilterByType DEFLATE application/json
    </IfModule>
</IfModule>

# Browser Caching
<IfModule mod_expires.c>
    ExpiresActive on
    
    # Images
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/webp "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
    
    # CSS and JavaScript
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType application/x-javascript "access plus 1 month"
    
    # Fonts
    ExpiresByType font/woff2 "access plus 1 year"
    ExpiresByType application/font-woff2 "access plus 1 year"
    ExpiresByType font/woff "access plus 1 year"
    ExpiresByType application/font-woff "access plus 1 year"
    
    # Default
    ExpiresDefault "access plus 2 days"
</IfModule>

# File Security
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.json">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.lock">
    Order allow,deny
    Deny from all
</Files>

<Files "package.json">
    Order allow,deny
    Deny from all
</Files>

# Prevent access to vendor directory
RedirectMatch 403 /vendor/.*$
EOF

    # Public directory .htaccess
    cat > public/.htaccess << 'EOF'
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

# Additional optimizations for public directory
<IfModule mod_headers.c>
    # Cache static assets
    <FilesMatch "\.(jpg|jpeg|png|gif|webp|css|js|ico|svg|woff|woff2|ttf|eot)$">
        Header set Cache-Control "max-age=31536000, public, immutable"
    </FilesMatch>
</IfModule>
EOF

    echo -e "${GREEN}✓ .htaccess files optimized for shared hosting${NC}"
}

create_cpanel_symlinks() {
    log "Setting up cPanel compatible symlinks..."
    
    # Create public_html symlink if needed
    if [ ! -L "public_html" ] && [ ! -d "public_html" ]; then
        ln -sf public public_html
        echo -e "${GREEN}✓ public_html symlink created${NC}"
    fi
    
    # Create www symlink (some hosts use this)
    if [ ! -L "www" ] && [ ! -d "www" ]; then
        ln -sf public www
        echo -e "${GREEN}✓ www symlink created${NC}"
    fi
    
    # Create storage symlink in public if not exists
    if [ ! -L "public/storage" ]; then
        php artisan storage:link
        echo -e "${GREEN}✓ Storage symlink created${NC}"
    fi
}

optimize_php_settings() {
    log "Creating optimized PHP settings..."
    
    # Create php.ini for shared hosting
    cat > php.ini << 'EOF'
; PHP Configuration for Laravel E-commerce on Shared Hosting
; Place this file in your account's root directory

; Memory and Execution
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
max_input_vars = 3000

; File Uploads
upload_max_filesize = 10M
post_max_size = 12M
file_uploads = On

; Session Configuration
session.gc_maxlifetime = 7200
session.cookie_lifetime = 0
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1

; OPcache Configuration (if available)
opcache.enable = 1
opcache.enable_cli = 0
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 60
opcache.fast_shutdown = 1

; Security
expose_php = Off
display_errors = Off
log_errors = On
error_log = error.log

; Timezone
date.timezone = "Europe/Istanbul"

; Disable dangerous functions
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source
EOF

    # Create .user.ini for some shared hosts
    cat > .user.ini << 'EOF'
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 10M
post_max_size = 12M
date.timezone = "Europe/Istanbul"
EOF

    echo -e "${GREEN}✓ PHP settings optimized for shared hosting${NC}"
}

create_maintenance_tools() {
    log "Creating maintenance tools for shared hosting..."
    
    # Create a simple maintenance script that can be run via cron
    cat > maintenance_cron.php << 'EOF'
<?php
/**
 * Maintenance Script for Shared Hosting
 * Can be run via cron job or manually through web browser
 * Usage: php maintenance_cron.php or visit: yourdomain.com/maintenance_cron.php
 */

// Prevent direct web access without key
if (isset($_GET['key']) && $_GET['key'] !== 'your_secret_maintenance_key') {
    die('Unauthorized');
}

// Set memory limit
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300);

// Change to Laravel directory
chdir(__DIR__);

// Include Laravel bootstrap
require_once 'vendor/autoload.php';

try {
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    
    echo "Laravel E-commerce Maintenance Started: " . date('Y-m-d H:i:s') . "\n\n";
    
    // Clear caches
    echo "Clearing caches...\n";
    $kernel->call('cache:clear');
    $kernel->call('config:clear');
    $kernel->call('route:clear');
    $kernel->call('view:clear');
    
    // Optimize for production
    echo "Optimizing for production...\n";
    $kernel->call('config:cache');
    $kernel->call('route:cache');
    $kernel->call('view:cache');
    
    // Clean up old logs (older than 30 days)
    echo "Cleaning old logs...\n";
    $logPath = storage_path('logs');
    if (is_dir($logPath)) {
        $files = glob($logPath . '/*.log');
        foreach ($files as $file) {
            if (filemtime($file) < strtotime('-30 days')) {
                unlink($file);
                echo "Deleted old log: " . basename($file) . "\n";
            }
        }
    }
    
    // Clean up old session files
    echo "Cleaning old sessions...\n";
    $sessionPath = storage_path('framework/sessions');
    if (is_dir($sessionPath)) {
        $files = glob($sessionPath . '/*');
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < strtotime('-7 days')) {
                unlink($file);
            }
        }
    }
    
    echo "\nMaintenance completed successfully: " . date('Y-m-d H:i:s') . "\n";
    
} catch (Exception $e) {
    echo "Error during maintenance: " . $e->getMessage() . "\n";
    error_log("Maintenance error: " . $e->getMessage());
}
EOF

    # Create a simple backup script
    cat > backup_simple.php << 'EOF'
<?php
/**
 * Simple Backup Script for Shared Hosting
 * Usage: php backup_simple.php or visit: yourdomain.com/backup_simple.php?key=your_backup_key
 */

// Prevent direct web access without key
if (isset($_GET['key']) && $_GET['key'] !== 'your_secret_backup_key') {
    die('Unauthorized');
}

ini_set('memory_limit', '512M');
ini_set('max_execution_time', 600);

chdir(__DIR__);

try {
    // Load environment
    if (file_exists('.env')) {
        $env = file_get_contents('.env');
        preg_match('/DB_DATABASE=(.+)/', $env, $matches);
        $database = trim($matches[1] ?? '');
        preg_match('/DB_USERNAME=(.+)/', $env, $matches);
        $username = trim($matches[1] ?? '');
        preg_match('/DB_PASSWORD=(.+)/', $env, $matches);
        $password = trim($matches[1] ?? '');
        preg_match('/DB_HOST=(.+)/', $env, $matches);
        $host = trim($matches[1] ?? 'localhost');
    }
    
    if (empty($database)) {
        die('Database configuration not found');
    }
    
    // Create backup directory
    $backupDir = 'backups';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d_H-i-s');
    $backupFile = "$backupDir/backup_$timestamp.sql";
    
    // Create database backup
    echo "Creating database backup...\n";
    $command = "mysqldump -h $host -u $username -p$password $database > $backupFile";
    $output = shell_exec($command);
    
    if (file_exists($backupFile) && filesize($backupFile) > 0) {
        echo "Database backup created: $backupFile\n";
        echo "Backup size: " . number_format(filesize($backupFile) / 1024, 2) . " KB\n";
    } else {
        echo "Backup failed or empty\n";
    }
    
    // Clean old backups (keep last 5)
    $backups = glob("$backupDir/backup_*.sql");
    rsort($backups);
    $toDelete = array_slice($backups, 5);
    
    foreach ($toDelete as $file) {
        unlink($file);
        echo "Deleted old backup: " . basename($file) . "\n";
    }
    
    echo "\nBackup process completed: " . date('Y-m-d H:i:s') . "\n";
    
} catch (Exception $e) {
    echo "Backup error: " . $e->getMessage() . "\n";
    error_log("Backup error: " . $e->getMessage());
}
EOF

    echo -e "${GREEN}✓ Maintenance tools created${NC}"
}

setup_error_handling() {
    log "Setting up error handling for shared hosting..."
    
    # Create custom error pages
    mkdir -p public/errors
    
    # 404 Error Page
    cat > public/errors/404.html << 'EOF'
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sayfa Bulunamadı - E-Ticaret</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 50px auto; text-align: center; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #e74c3c; font-size: 72px; margin: 0; }
        h2 { color: #2c3e50; margin: 20px 0; }
        p { color: #7f8c8d; line-height: 1.6; }
        .btn { display: inline-block; background: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .btn:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class="container">
        <h1>404</h1>
        <h2>Sayfa Bulunamadı</h2>
        <p>Aradığınız sayfa bulunamadı. URL'yi kontrol edin veya ana sayfaya dönün.</p>
        <a href="/" class="btn">Ana Sayfaya Dön</a>
    </div>
</body>
</html>
EOF

    # 500 Error Page
    cat > public/errors/500.html << 'EOF'
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunucu Hatası - E-Ticaret</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 50px auto; text-align: center; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #e74c3c; font-size: 72px; margin: 0; }
        h2 { color: #2c3e50; margin: 20px 0; }
        p { color: #7f8c8d; line-height: 1.6; }
        .btn { display: inline-block; background: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .btn:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class="container">
        <h1>500</h1>
        <h2>Sunucu Hatası</h2>
        <p>Geçici bir teknik sorun yaşanıyor. Lütfen birkaç dakika sonra tekrar deneyin.</p>
        <a href="/" class="btn">Ana Sayfaya Dön</a>
    </div>
</body>
</html>
EOF

    # Add error page directives to .htaccess
    echo "" >> .htaccess
    echo "# Custom Error Pages" >> .htaccess
    echo "ErrorDocument 404 /errors/404.html" >> .htaccess
    echo "ErrorDocument 500 /errors/500.html" >> .htaccess

    echo -e "${GREEN}✓ Error handling configured${NC}"
}

create_robots_and_sitemap() {
    log "Creating SEO files..."
    
    # Create robots.txt
    cat > public/robots.txt << 'EOF'
User-agent: *
Allow: /

# Disallow admin and sensitive areas
Disallow: /admin/
Disallow: /vendor/
Disallow: /storage/
Disallow: /.env
Disallow: /composer.json
Disallow: /composer.lock

# Allow images
Allow: /storage/uploads/
Allow: /images/

# Sitemap
Sitemap: https://your-domain.com/sitemap.xml
EOF

    # Create basic sitemap.xml
    cat > public/sitemap.xml << 'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>https://your-domain.com/</loc>
        <lastmod>2024-01-01</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <!-- Add more URLs as needed -->
</urlset>
EOF

    echo -e "${GREEN}✓ SEO files created${NC}"
}

optimize_storage_structure() {
    log "Optimizing storage structure for shared hosting..."
    
    # Create necessary storage directories
    mkdir -p storage/app/public/uploads/products
    mkdir -p storage/app/public/uploads/categories
    mkdir -p storage/app/public/uploads/brands
    mkdir -p storage/framework/cache/data
    mkdir -p storage/framework/sessions
    mkdir -p storage/framework/views
    mkdir -p storage/logs
    
    # Set proper permissions
    chmod -R 755 storage
    chmod -R 755 bootstrap/cache
    
    # Create index.html files to prevent directory browsing
    for dir in storage storage/app storage/app/public storage/framework storage/logs; do
        echo '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><h1>Directory access is forbidden.</h1></body></html>' > "$dir/index.html"
    done
    
    echo -e "${GREEN}✓ Storage structure optimized${NC}"
}

show_usage() {
    echo "Shared Hosting Optimization Script"
    echo ""
    echo "Usage: $0 [command]"
    echo ""
    echo "Commands:"
    echo "  full           Run complete optimization (default)"
    echo "  htaccess       Create optimized .htaccess files only"
    echo "  symlinks       Create cPanel symlinks only"
    echo "  php            Optimize PHP settings only"
    echo "  maintenance    Create maintenance tools only"
    echo "  errors         Setup error handling only"
    echo "  seo            Create SEO files only"
    echo "  storage        Optimize storage structure only"
    echo "  help           Show this help message"
}

# Main execution
case "${1:-full}" in
    "full")
        print_header
        log "Starting complete shared hosting optimization..."
        
        create_htaccess_optimizations
        create_cpanel_symlinks
        optimize_php_settings
        create_maintenance_tools
        setup_error_handling
        create_robots_and_sitemap
        optimize_storage_structure
        
        echo -e "${GREEN}"
        echo "=================================================="
        echo "  🎉 SHARED HOSTING OPTIMIZATION COMPLETED!"
        echo "=================================================="
        echo -e "${NC}"
        echo ""
        echo "Next steps:"
        echo "1. Update .env.production with your actual values"
        echo "2. Update robots.txt and sitemap.xml with your domain"
        echo "3. Change the secret keys in maintenance_cron.php and backup_simple.php"
        echo "4. Set up cron jobs for maintenance and backups"
        echo ""
        log "Shared hosting optimization completed successfully"
        ;;
    "htaccess")
        create_htaccess_optimizations
        ;;
    "symlinks")
        create_cpanel_symlinks
        ;;
    "php")
        optimize_php_settings
        ;;
    "maintenance")
        create_maintenance_tools
        ;;
    "errors")
        setup_error_handling
        ;;
    "seo")
        create_robots_and_sitemap
        ;;
    "storage")
        optimize_storage_structure
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