# Laravel E-commerce Deployment Checklist for Shared Hosting

## Pre-Deployment Checklist

### Development Environment
- [ ] All features tested locally
- [ ] Database migrations are ready
- [ ] No hardcoded URLs or paths
- [ ] Environment variables properly configured
- [ ] All dependencies are production-ready
- [ ] Code is optimized and commented
- [ ] Security vulnerabilities checked

### File Preparation
- [ ] Upload all Laravel files to hosting account
- [ ] Exclude development files (node_modules, tests, etc.)
- [ ] Include all vendor dependencies
- [ ] Prepare .env.production file
- [ ] Check file permissions (755 for directories, 644 for files)

## Hosting Environment Setup

### cPanel/Shared Hosting Configuration
- [ ] PHP version set to 8.2 or higher
- [ ] Required PHP extensions enabled:
  - [ ] PDO
  - [ ] Mbstring
  - [ ] OpenSSL
  - [ ] Tokenizer
  - [ ] XML
  - [ ] Curl
  - [ ] GD or ImageMagick
  - [ ] Zip
  - [ ] BCMath (optional)

### Database Setup
- [ ] MySQL database created
- [ ] Database user created with full privileges
- [ ] Database connection tested
- [ ] Character set set to utf8mb4_unicode_ci
- [ ] Database name, username, and password noted

### File Structure Setup
- [ ] Files uploaded to correct directory (usually public_html or similar)
- [ ] public_html symlink created pointing to Laravel's public folder
- [ ] Storage directory permissions set (755/775)
- [ ] Bootstrap/cache permissions set (755/775)

## Deployment Steps

### 1. File Upload
```bash
# Upload files via FTP/SFTP or File Manager
# Exclude: .git, node_modules, tests, .env, storage/logs/*
```

### 2. Environment Configuration
- [ ] Copy .env.production to .env
- [ ] Update database credentials in .env
- [ ] Update APP_URL to your domain
- [ ] Set APP_ENV=production
- [ ] Set APP_DEBUG=false
- [ ] Generate APP_KEY: `php artisan key:generate`

### 3. Run Deployment Scripts
```bash
# Make scripts executable
chmod +x scripts/deploy.sh
chmod +x scripts/shared_hosting_setup.sh

# Run shared hosting optimization
./scripts/shared_hosting_setup.sh

# Run deployment
./scripts/deploy.sh
```

### 4. Database Setup
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Seed initial data: `php artisan db:seed`
- [ ] Test database connection

### 5. Storage and Caching
- [ ] Create storage symlink: `php artisan storage:link`
- [ ] Clear all caches: `php artisan cache:clear`
- [ ] Cache configuration: `php artisan config:cache`
- [ ] Cache routes: `php artisan route:cache`
- [ ] Cache views: `php artisan view:cache`

### 6. File Permissions
```bash
# Set proper permissions
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## Post-Deployment Verification

### Functionality Tests
- [ ] Homepage loads correctly
- [ ] Admin panel accessible
- [ ] Product listing works
- [ ] Image uploads work
- [ ] Database operations work
- [ ] Contact forms send emails
- [ ] Error pages display correctly

### Performance Tests
- [ ] Page load times acceptable
- [ ] Images loading properly
- [ ] CSS/JS files loading
- [ ] Mobile responsiveness
- [ ] Memory usage within limits

### Security Tests
- [ ] .env file not accessible via web
- [ ] Admin panel properly protected
- [ ] File upload restrictions working
- [ ] SQL injection protection
- [ ] XSS protection enabled

## Maintenance Setup

### Automated Maintenance
- [ ] Set up cron job for maintenance:
```bash
# Add to cPanel cron jobs:
0 2 * * * /usr/bin/php /path/to/your/site/maintenance_cron.php?key=your_secret_key
```

### Backup Setup
- [ ] Set up automated backups:
```bash
# Weekly backup cron job:
0 3 * * 0 /usr/bin/php /path/to/your/site/backup_simple.php?key=your_backup_key
```

### Monitoring
- [ ] Set up uptime monitoring
- [ ] Configure error notifications
- [ ] Monitor disk space usage
- [ ] Track performance metrics

## Optimization Checklist

### Performance
- [ ] Gzip compression enabled
- [ ] Browser caching configured
- [ ] Image optimization enabled
- [ ] Database queries optimized
- [ ] Laravel caching properly configured

### SEO
- [ ] robots.txt properly configured
- [ ] sitemap.xml created and submitted
- [ ] Meta tags properly set
- [ ] URLs are SEO-friendly
- [ ] SSL certificate installed

### Security
- [ ] HTTPS enforced
- [ ] Security headers configured
- [ ] File access restricted
- [ ] Regular security updates planned
- [ ] Error logging enabled

## Troubleshooting Common Issues

### File Permission Issues
```bash
# If getting permission errors:
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Memory Limit Issues
- [ ] Increase PHP memory_limit in php.ini
- [ ] Optimize composer autoloader
- [ ] Use efficient caching strategies

### Database Connection Issues
- [ ] Verify database credentials
- [ ] Check database server accessibility
- [ ] Ensure proper character encoding

### .htaccess Issues
- [ ] Verify mod_rewrite is enabled
- [ ] Check for conflicting rules
- [ ] Test URL rewriting

## Post-Launch Tasks

### Immediate (First Week)
- [ ] Monitor error logs daily
- [ ] Check performance metrics
- [ ] Verify all functionality
- [ ] Address any reported issues
- [ ] Update documentation

### Ongoing (Monthly)
- [ ] Update Laravel and dependencies
- [ ] Review security logs
- [ ] Optimize database
- [ ] Clean up old files
- [ ] Review performance

### Regular Maintenance
- [ ] Database backups (weekly)
- [ ] Full site backups (monthly)
- [ ] Security updates (as needed)
- [ ] Performance optimization (quarterly)
- [ ] Content updates (ongoing)

## Contact Information

### Support Resources
- Laravel Documentation: https://laravel.com/docs
- Hosting Support: [Your hosting provider support]
- Developer Contact: [Your contact information]

### Emergency Contacts
- Technical Issues: [Emergency contact]
- Hosting Issues: [Hosting support number]
- Domain Issues: [Domain registrar support]

---

**Note**: Always test deployment procedures in a staging environment before applying to production. Keep this checklist updated as your application evolves.