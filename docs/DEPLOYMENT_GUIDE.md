# Laravel E-commerce Deployment Guide

Comprehensive guide for deploying Laravel e-commerce application to shared hosting environments.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Pre-deployment Checklist](#pre-deployment-checklist)
3. [Deployment Process](#deployment-process)
4. [Post-deployment Configuration](#post-deployment-configuration)
5. [Maintenance & Monitoring](#maintenance--monitoring)
6. [Troubleshooting](#troubleshooting)
7. [Security Considerations](#security-considerations)
8. [Performance Optimization](#performance-optimization)

## Prerequisites

### Server Requirements

- **PHP**: 8.2 or higher
- **Database**: MySQL 8.0+ or MariaDB 10.3+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: Minimum 256MB, Recommended 512MB+
- **Disk Space**: Minimum 1GB, Recommended 5GB+

### Required PHP Extensions

```bash
# Core Extensions
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML

# Additional Extensions
- GD or Imagick (for image processing)
- cURL
- Zip
- MySQL/MySQLi
```

### Tools Required

- Composer (for dependency management)
- Git (for version control)
- SSH access (preferred) or FTP/SFTP
- MySQL client tools

## Pre-deployment Checklist

### 1. Code Preparation

```bash
# Ensure all changes are committed
git status
git add .
git commit -m \"Prepare for production deployment\"

# Tag the release
git tag -a v1.0.0 -m \"Production release v1.0.0\"
git push origin v1.0.0
```

### 2. Environment Configuration

- [ ] Copy `.env.production` to `.env`
- [ ] Configure database credentials
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure mail settings
- [ ] Set proper `APP_URL`
- [ ] Generate application key

### 3. Security Setup

- [ ] SSL certificate installed
- [ ] Firewall configured
- [ ] Database user with minimal privileges
- [ ] Secure file permissions planned

## Deployment Process

### Method 1: Automated Deployment (Recommended)

```bash
# Make deployment script executable
chmod +x deploy.sh

# Run deployment
./deploy.sh
```

The automated script will:
1. Check system requirements
2. Create backup of existing installation
3. Install dependencies
4. Configure environment
5. Set up storage and permissions
6. Run database migrations
7. Optimize for production
8. Perform final checks

### Method 2: Manual Deployment

#### Step 1: Upload Files

```bash
# Option A: Using Git (if available)
git clone https://github.com/yourusername/your-repo.git .

# Option B: Upload via FTP/SFTP
# Upload all files except:
# - .git/
# - node_modules/
# - .env.local
# - storage/logs/*.log
```

#### Step 2: Install Dependencies

```bash
# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# If Node.js is available
npm ci --production
npm run build
```

#### Step 3: Environment Setup

```bash
# Copy environment file
cp .env.production .env

# Generate application key
php artisan key:generate

# Set proper file permissions
chmod 644 .env
```

#### Step 4: Storage Setup

```bash
# Create necessary directories
mkdir -p storage/app/public/uploads
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set permissions
find storage -type f -exec chmod 644 {} \\;
find storage -type d -exec chmod 755 {} \\;
chmod 755 bootstrap/cache

# Create storage symlink
php artisan storage:link
```

#### Step 5: Database Setup

```bash
# Run migrations
php artisan migrate --force

# Seed essential data
php artisan db:seed --class=UnitsSeeder
php artisan db:seed --class=ProductAttributesSeeder
```

#### Step 6: Production Optimization

```bash
# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

## Post-deployment Configuration

### 1. Web Server Configuration

#### Apache (.htaccess)

Create or update `public/.htaccess`:

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
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection \"1; mode=block\"
    Header always set Strict-Transport-Security \"max-age=31536000; includeSubDomains\"
    Header always set Referrer-Policy \"strict-origin-when-cross-origin\"
</IfModule>

# Cache Static Assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css \"access plus 1 year\"
    ExpiresByType application/javascript \"access plus 1 year\"
    ExpiresByType image/png \"access plus 1 year\"
    ExpiresByType image/jpg \"access plus 1 year\"
    ExpiresByType image/jpeg \"access plus 1 year\"
    ExpiresByType image/gif \"access plus 1 year\"
    ExpiresByType image/webp \"access plus 1 year\"
</IfModule>
```

#### Nginx Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /path/to/your/project/public;

    add_header X-Frame-Options \"SAMEORIGIN\";
    add_header X-Content-Type-Options \"nosniff\";
    add_header X-XSS-Protection \"1; mode=block\";
    add_header Strict-Transport-Security \"max-age=31536000; includeSubDomains\";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \\.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\\.(?!well-known).* {
        deny all;
    }

    # SSL Configuration
    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/private.key;
}
```

### 2. Database Optimization

```sql
-- MySQL configuration recommendations
SET GLOBAL innodb_buffer_pool_size = 128M;
SET GLOBAL max_connections = 50;
SET GLOBAL query_cache_size = 32M;
SET GLOBAL tmp_table_size = 32M;
SET GLOBAL max_heap_table_size = 32M;
```

### 3. Cron Jobs Setup

```bash
# Edit crontab
crontab -e

# Add Laravel scheduler
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1

# Add maintenance tasks
0 2 * * * cd /path/to/your/project && ./scripts/maintenance.sh cleanup >> /dev/null 2>&1
0 1 * * 0 cd /path/to/your/project && ./scripts/backup-database.sh backup >> /dev/null 2>&1
*/15 * * * * cd /path/to/your/project && ./scripts/health-check.sh --quick >> /dev/null 2>&1
```

## Maintenance & Monitoring

### Regular Maintenance Tasks

```bash
# Daily maintenance (automated)
./scripts/maintenance.sh cleanup

# Weekly maintenance
./scripts/maintenance.sh full

# Database backup
./scripts/backup-database.sh backup

# Health check
./scripts/health-check.sh
```

### Monitoring Setup

1. **Log Monitoring**
   ```bash
   # Monitor error logs
   tail -f storage/logs/laravel.log
   
   # Monitor web server logs
   tail -f /var/log/apache2/error.log
   ```

2. **Performance Monitoring**
   - Set up log rotation
   - Monitor disk usage
   - Track database performance
   - Monitor memory usage

3. **Uptime Monitoring**
   - Configure external uptime monitoring
   - Set up health check endpoints
   - Configure alert notifications

## Troubleshooting

### Common Issues

#### 1. 500 Internal Server Error

```bash
# Check Laravel logs
tail -50 storage/logs/laravel.log

# Check web server logs
tail -50 /var/log/apache2/error.log

# Common causes:
# - Missing .env file
# - Incorrect file permissions
# - PHP extension missing
# - Database connection issues
```

#### 2. File Permission Issues

```bash
# Fix storage permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Fix ownership (if needed)
chown -R www-data:www-data storage bootstrap/cache
```

#### 3. Database Connection Issues

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check configuration
php artisan config:show database
```

#### 4. Memory Limit Issues

```bash
# Increase PHP memory limit
# In .htaccess:
php_value memory_limit 512M

# Or in php.ini:
memory_limit = 512M
```

### Debugging Commands

```bash
# Clear all caches
php artisan optimize:clear

# Rebuild caches
php artisan optimize

# Check routes
php artisan route:list

# Check configuration
php artisan config:show

# Test database
php artisan migrate:status
```

## Security Considerations

### 1. File Security

```bash
# Hide sensitive files
echo \"deny from all\" > .env.htaccess
echo \"deny from all\" > storage/.htaccess

# Secure file permissions
chmod 644 .env
chmod 644 config/*.php
chmod -R 644 app/
```

### 2. Database Security

- Use dedicated database user with minimal privileges
- Enable SSL for database connections
- Regular security updates
- Monitor for unauthorized access

### 3. Application Security

- Keep Laravel and dependencies updated
- Use HTTPS only
- Implement rate limiting
- Monitor for vulnerabilities
- Regular security audits

## Performance Optimization

### 1. PHP Optimization

```ini
# php.ini optimizations
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=12
opcache.max_accelerated_files=4000
opcache.validate_timestamps=0
```

### 2. Database Optimization

```sql
-- Add indexes for better performance
CREATE INDEX idx_products_active ON products(is_active);
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_variants_product ON product_variants(product_id);
```

### 3. Caching Strategy

```bash
# Enable all production caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Configure application cache
# Use Redis or Memcached if available
```

### 4. Asset Optimization

```bash
# Minify and compress assets
npm run build

# Enable gzip compression in web server
# Configure CDN for static assets
```

## Conclusion

This deployment guide provides a comprehensive approach to deploying Laravel e-commerce applications on shared hosting environments. Following these steps will ensure a secure, optimized, and maintainable production deployment.

For additional support or custom configurations, refer to the Laravel documentation or consult with your hosting provider.

---

**Important Notes:**

- Always test deployments in a staging environment first
- Keep regular backups of both code and database
- Monitor application performance and logs regularly
- Update dependencies and security patches promptly
- Document any custom configurations for your specific environment