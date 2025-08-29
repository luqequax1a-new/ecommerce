# Laravel E-commerce Deployment for Shared Hosting

A comprehensive deployment solution specifically designed for shared hosting environments (cPanel, Plesk, etc.) with optimization for limited resources and typical shared hosting constraints.

## 🚀 Quick Start

### Prerequisites
- PHP 8.2 or higher
- MySQL 5.7 or higher
- Composer installed
- Access to shared hosting account (cPanel recommended)

### 1. Download and Extract
Upload all files to your hosting account's root directory (usually `public_html` or similar).

### 2. Quick Setup
```bash
# Make scripts executable
chmod +x scripts/deploy.sh
chmod +x scripts/shared_hosting_setup.sh

# Run shared hosting optimization
./scripts/shared_hosting_setup.sh

# Run deployment
./scripts/deploy.sh
```

### 3. Configure Environment
```bash
# Copy and edit environment file
cp .env.production .env
# Edit .env with your database and domain details
```

## 📁 Project Structure

```
ecommerce/
├── app/                    # Laravel application code
├── database/               # Migrations, seeders, factories
├── public/                 # Web-accessible files
├── resources/              # Views, assets, lang files
├── scripts/                # Deployment and maintenance scripts
│   ├── deploy.sh          # Main deployment script
│   ├── shared_hosting_setup.sh # Shared hosting optimization
│   └── maintenance.sh     # Regular maintenance script
├── storage/               # Application storage
├── .env.production        # Production environment template
├── DEPLOYMENT_CHECKLIST.md # Step-by-step deployment guide
└── README.md             # This file
```

## 🔧 Features

### E-commerce Features
- **Product Management**: Simple and variable products with attributes
- **Stock Management**: Decimal stock support with unit inheritance
- **Category System**: Hierarchical categories with SEO optimization
- **Brand Management**: Brand-based product organization
- **Image Management**: Optimized image handling with multiple sizes
- **SEO Optimization**: Built-in SEO features for better search rankings

### Technical Features
- **Shared Hosting Optimized**: Resource-efficient code and caching
- **Laravel 12**: Latest Laravel framework with PHP 8.2+ support
- **Database Optimization**: Efficient queries and proper indexing
- **File Optimization**: Compressed assets and optimized file structure
- **Security**: Production-ready security configurations
- **Maintenance Tools**: Automated maintenance and cleanup scripts

## 🏗️ Architecture

### Product System
- **Simple Products**: Single variant with direct stock management
- **Variable Products**: Multiple variants with attribute combinations
- **Unit System**: Product-level unit definition inherited by all variants
- **Decimal Stock**: Support for fractional quantities (e.g., 78.3 meters)

### Database Design
- Optimized for shared hosting performance
- Proper indexing for fast queries
- Minimal resource usage
- Support for large product catalogs

## 🚀 Deployment Guide

### Shared Hosting Deployment

1. **File Upload**
   ```bash
   # Upload via FTP/SFTP or hosting file manager
   # Ensure all files are in the correct directory
   ```

2. **Environment Setup**
   ```bash
   # Copy production environment
   cp .env.production .env
   
   # Edit database credentials
   nano .env
   ```

3. **Database Setup**
   ```bash
   # Run migrations
   php artisan migrate --force
   
   # Seed initial data
   php artisan db:seed
   ```

4. **Optimization**
   ```bash
   # Run shared hosting setup
   ./scripts/shared_hosting_setup.sh
   
   # Complete deployment
   ./scripts/deploy.sh
   ```

### cPanel Specific Steps

1. **PHP Configuration**
   - Set PHP version to 8.2+
   - Enable required extensions
   - Upload custom php.ini (created by setup script)

2. **File Structure**
   - Upload files to account root (not public_html)
   - Script creates public_html symlink automatically

3. **Database Setup**
   - Create MySQL database via cPanel
   - Create database user with full privileges
   - Update .env with credentials

4. **Cron Jobs** (Optional)
   ```bash
   # Daily maintenance (2 AM)
   0 2 * * * /usr/bin/php /path/to/site/maintenance_cron.php?key=your_secret_key
   
   # Weekly backup (Sunday 3 AM)
   0 3 * * 0 /usr/bin/php /path/to/site/backup_simple.php?key=your_backup_key
   ```

## 🔧 Configuration

### Environment Variables

Key configuration options in `.env`:

```bash
# Application
APP_NAME="E-Ticaret"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Performance
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Image optimization
IMAGE_DRIVER=gd
IMAGE_QUALITY=85
```

### PHP Configuration

Recommended PHP settings (automatically configured):

```ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 10M
post_max_size = 12M
opcache.enable = 1
```

## 🛠️ Maintenance

### Automated Maintenance

The system includes automated maintenance scripts:

- **Daily Maintenance** (`maintenance_cron.php`):
  - Cache clearing and optimization
  - Log file cleanup
  - Session cleanup
  - Performance optimization

- **Weekly Backup** (`backup_simple.php`):
  - Database backup
  - File backup
  - Old backup cleanup

### Manual Maintenance

```bash
# Run full maintenance
./scripts/maintenance.sh

# Quick optimization
./scripts/maintenance.sh optimize

# Clear caches only
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 📊 Performance Optimization

### Shared Hosting Optimizations

1. **File Caching**: Aggressive file-based caching
2. **Database Optimization**: Efficient queries and indexing
3. **Image Optimization**: Automatic image compression and resizing
4. **Gzip Compression**: Enabled via .htaccess
5. **Browser Caching**: Long-term caching for static assets
6. **OPcache**: PHP opcode caching when available

### Resource Management

- Memory usage optimized for shared hosting limits
- Efficient database queries to reduce CPU usage
- Minimal external dependencies
- Optimized autoloader for faster loading

## 🔒 Security

### Production Security Features

- Environment file protection
- SQL injection prevention
- XSS protection
- CSRF protection
- Secure headers configuration
- File upload restrictions
- Directory browsing prevention

### Security Checklist

- [ ] .env file not web-accessible
- [ ] Admin panel properly protected
- [ ] HTTPS enforced
- [ ] Security headers configured
- [ ] Error pages customized
- [ ] File permissions properly set

## 🐛 Troubleshooting

### Common Issues

1. **File Permission Errors**
   ```bash
   chmod -R 775 storage
   chmod -R 775 bootstrap/cache
   ```

2. **Memory Limit Errors**
   - Increase PHP memory_limit
   - Optimize composer autoloader
   - Clear unnecessary caches

3. **Database Connection Issues**
   - Verify credentials in .env
   - Check database server status
   - Ensure proper character encoding

4. **Missing Extensions**
   - Contact hosting provider
   - Check PHP configuration
   - Update to compatible versions

### Error Monitoring

- Check `storage/logs/laravel.log` for application errors
- Monitor hosting account error logs
- Set up uptime monitoring
- Configure error notifications

## 📞 Support

### Documentation
- Laravel: https://laravel.com/docs
- Deployment Checklist: `DEPLOYMENT_CHECKLIST.md`
- Maintenance Scripts: `scripts/` directory

### Getting Help
1. Check Laravel documentation
2. Review error logs
3. Contact hosting provider for server issues
4. Check community forums for Laravel-specific issues

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

**Note**: This system is specifically optimized for shared hosting environments. For VPS or dedicated servers, additional optimizations may be available.