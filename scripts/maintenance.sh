#!/bin/bash

# Laravel E-commerce Maintenance Script
# Regular system maintenance and optimization

set -e

# Colors
RED='\\033[0;31m'
GREEN='\\033[0;32m'
YELLOW='\\033[1;33m'
BLUE='\\033[0;34m'
NC='\\033[0m'

# Configuration
LOG_DIR=\"logs\"
MAINTENANCE_LOG=\"maintenance.log\"
MAX_LOG_SIZE=\"100M\"
LOG_RETENTION_DAYS=30
SESSION_CLEANUP_DAYS=7
CACHE_CLEANUP_DAYS=30

# Functions
log() {
    echo \"[$(date '+%Y-%m-%d %H:%M:%S')] $1\" | tee -a \"$LOG_DIR/$MAINTENANCE_LOG\"
}

print_header() {
    echo -e \"${BLUE}\"
    echo \"===========================================\"
    echo \"  Laravel E-commerce Maintenance\"
    echo \"  System Optimization & Cleanup\"
    echo \"===========================================\"
    echo -e \"${NC}\"
}

check_system_status() {
    log \"Checking system status...\"
    
    # Check if Laravel is working
    if php artisan --version &> /dev/null; then
        echo -e \"${GREEN}✓ Laravel application is functional${NC}\"
    else
        echo -e \"${RED}✗ Laravel application error${NC}\"
        return 1
    fi
    
    # Check database connection
    if php artisan migrate:status &> /dev/null; then
        echo -e \"${GREEN}✓ Database connection OK${NC}\"
    else
        echo -e \"${RED}✗ Database connection failed${NC}\"
        return 1
    fi
    
    # Check storage permissions
    if [ -w \"storage\" ] && [ -w \"bootstrap/cache\" ]; then
        echo -e \"${GREEN}✓ Storage permissions OK${NC}\"
    else
        echo -e \"${YELLOW}⚠ Storage permission issues detected${NC}\"
    fi
    
    log \"System status check completed\"
}

cleanup_logs() {
    log \"Cleaning up application logs...\"
    
    local cleaned_count=0
    
    # Clean old Laravel logs
    if [ -d \"storage/logs\" ]; then
        # Compress large log files
        find storage/logs -name \"*.log\" -size +\"$MAX_LOG_SIZE\" -exec gzip {} \\; 2>/dev/null || true
        
        # Remove old log files
        while IFS= read -r -d '' file; do
            rm \"$file\"
            cleaned_count=$((cleaned_count + 1))
            log \"Deleted old log: $(basename \"$file\")\"
        done < <(find storage/logs -name \"*.log\" -type f -mtime +$LOG_RETENTION_DAYS -print0 2>/dev/null)
        
        # Remove old compressed logs
        while IFS= read -r -d '' file; do
            rm \"$file\"
            cleaned_count=$((cleaned_count + 1))
            log \"Deleted old compressed log: $(basename \"$file\")\"
        done < <(find storage/logs -name \"*.log.gz\" -type f -mtime +$((LOG_RETENTION_DAYS * 2)) -print0 2>/dev/null)
    fi
    
    echo -e \"${GREEN}✓ Cleaned $cleaned_count old log files${NC}\"
}

cleanup_sessions() {
    log \"Cleaning up expired sessions...\"
    
    local cleaned_count=0
    
    if [ -d \"storage/framework/sessions\" ]; then
        # Remove old session files
        while IFS= read -r -d '' file; do
            rm \"$file\"
            cleaned_count=$((cleaned_count + 1))
        done < <(find storage/framework/sessions -type f -mtime +$SESSION_CLEANUP_DAYS -print0 2>/dev/null)
    fi
    
    echo -e \"${GREEN}✓ Cleaned $cleaned_count expired session files${NC}\"
}

cleanup_cache() {
    log \"Cleaning up cache files...\"
    
    # Clear application cache
    php artisan cache:clear
    
    # Clean old cache files
    local cleaned_count=0
    
    if [ -d \"storage/framework/cache\" ]; then
        while IFS= read -r -d '' file; do
            rm \"$file\"
            cleaned_count=$((cleaned_count + 1))
        done < <(find storage/framework/cache -type f -mtime +$CACHE_CLEANUP_DAYS -print0 2>/dev/null)
    fi
    
    echo -e \"${GREEN}✓ Cache cleared and $cleaned_count old cache files removed${NC}\"
}

cleanup_temp_files() {
    log \"Cleaning up temporary files...\"
    
    local cleaned_count=0
    
    # Remove temporary upload files (if any)
    if [ -d \"storage/app/temp\" ]; then
        while IFS= read -r -d '' file; do
            rm \"$file\"
            cleaned_count=$((cleaned_count + 1))
        done < <(find storage/app/temp -type f -mtime +1 -print0 2>/dev/null)
    fi
    
    # Remove old compiled views
    if [ -d \"storage/framework/views\" ]; then
        find storage/framework/views -name \"*.php\" -type f -mtime +7 -delete 2>/dev/null || true
    fi
    
    echo -e \"${GREEN}✓ Cleaned $cleaned_count temporary files${NC}\"
}

optimize_database() {
    log \"Optimizing database...\"
    
    # Load environment variables
    if [ -f \".env\" ]; then
        export $(grep -v '^#' .env | xargs)
    fi
    
    # Optimize database tables (if MySQL)
    if [ \"$DB_CONNECTION\" = \"mysql\" ]; then
        echo \"Optimizing MySQL tables...\"
        
        # Get list of tables and optimize them
        mysql -h \"$DB_HOST\" -P \"${DB_PORT:-3306}\" -u \"$DB_USERNAME\" -p\"$DB_PASSWORD\" \"$DB_DATABASE\" \\n            -e \"SELECT CONCAT('OPTIMIZE TABLE ', table_name, ';') AS query 
                FROM information_schema.tables 
                WHERE table_schema = '$DB_DATABASE';\" \\n            --skip-column-names --batch | \\n        mysql -h \"$DB_HOST\" -P \"${DB_PORT:-3306}\" -u \"$DB_USERNAME\" -p\"$DB_PASSWORD\" \"$DB_DATABASE\"
        
        echo -e \"${GREEN}✓ Database tables optimized${NC}\"
    else
        echo -e \"${YELLOW}⚠ Database optimization skipped (not MySQL)${NC}\"
    fi
}

check_disk_usage() {
    log \"Checking disk usage...\"
    
    # Check overall disk usage
    local disk_usage=$(df . | tail -1 | awk '{print $5}' | sed 's/%//')
    
    echo \"Current disk usage: ${disk_usage}%\"
    
    if [ \"$disk_usage\" -gt 90 ]; then
        echo -e \"${RED}⚠ Warning: Disk usage is above 90%${NC}\"
        log \"WARNING: High disk usage detected: ${disk_usage}%\"
    elif [ \"$disk_usage\" -gt 80 ]; then
        echo -e \"${YELLOW}⚠ Warning: Disk usage is above 80%${NC}\"
        log \"WARNING: Moderate disk usage: ${disk_usage}%\"
    else
        echo -e \"${GREEN}✓ Disk usage is normal${NC}\"
    fi
    
    # Check application directory sizes
    echo \"\"
    echo \"Directory sizes:\"
    du -sh storage/ 2>/dev/null || echo \"storage/: 0\"
    du -sh vendor/ 2>/dev/null || echo \"vendor/: 0\"
    du -sh public/uploads/ 2>/dev/null || echo \"public/uploads/: 0\"
    if [ -d \"database_backups\" ]; then
        du -sh database_backups/ 2>/dev/null || echo \"database_backups/: 0\"
    fi
}

check_security() {
    log \"Performing basic security checks...\"
    
    local security_issues=0
    
    # Check .env permissions
    if [ -f \".env\" ]; then
        local env_perms=$(stat -c \"%a\" .env 2>/dev/null || stat -f \"%A\" .env 2>/dev/null || echo \"000\")
        if [ \"$env_perms\" != \"644\" ] && [ \"$env_perms\" != \"600\" ]; then
            echo -e \"${YELLOW}⚠ .env file permissions: $env_perms (should be 644 or 600)${NC}\"
            security_issues=$((security_issues + 1))
        fi
    fi
    
    # Check if debug mode is disabled in production
    if grep -q \"APP_DEBUG=true\" .env 2>/dev/null; then
        echo -e \"${RED}⚠ APP_DEBUG is enabled (should be false in production)${NC}\"
        security_issues=$((security_issues + 1))
    fi
    
    # Check if APP_KEY is set
    if ! grep -q \"APP_KEY=base64:\" .env 2>/dev/null; then
        echo -e \"${RED}⚠ APP_KEY is not properly set${NC}\"
        security_issues=$((security_issues + 1))
    fi
    
    if [ $security_issues -eq 0 ]; then
        echo -e \"${GREEN}✓ Basic security checks passed${NC}\"
    else
        echo -e \"${YELLOW}⚠ Found $security_issues security issues${NC}\"
        log \"Security check found $security_issues issues\"
    fi
}

update_composer() {
    read -p \"Do you want to update Composer dependencies? (y/N): \" -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        log \"Updating Composer dependencies...\"
        
        # Update dependencies
        composer update --no-dev --optimize-autoloader --no-interaction
        
        echo -e \"${GREEN}✓ Composer dependencies updated${NC}\"
    fi
}

generate_report() {
    log \"Generating maintenance report...\"
    
    local report_file=\"maintenance_report_$(date +%Y%m%d_%H%M%S).txt\"
    
    {
        echo \"Laravel E-commerce Maintenance Report\"
        echo \"Generated: $(date)\"
        echo \"========================================\"
        echo \"\"
        
        echo \"System Information:\"
        echo \"- PHP Version: $(php -r 'echo PHP_VERSION;')\"
        echo \"- Laravel Version: $(php artisan --version | cut -d' ' -f3)\"
        echo \"- Environment: $(grep APP_ENV .env | cut -d'=' -f2)\"
        echo \"\"
        
        echo \"Database:\"
        echo \"- Connection: $(grep DB_CONNECTION .env | cut -d'=' -f2)\"
        echo \"- Host: $(grep DB_HOST .env | cut -d'=' -f2)\"
        echo \"\"
        
        echo \"Disk Usage:\"
        df -h . | tail -1
        echo \"\"
        
        echo \"Directory Sizes:\"
        du -sh storage/ vendor/ public/ 2>/dev/null || true
        echo \"\"
        
        echo \"Recent Logs (last 10 lines):\"
        tail -10 \"$LOG_DIR/$MAINTENANCE_LOG\" 2>/dev/null || echo \"No maintenance logs found\"
        
    } > \"$report_file\"
    
    echo -e \"${GREEN}✓ Maintenance report generated: $report_file${NC}\"
}

show_usage() {
    echo \"Laravel E-commerce Maintenance Script\"
    echo \"\"
    echo \"Usage: $0 [command]\"
    echo \"\"
    echo \"Commands:\"
    echo \"  full           Run complete maintenance (default)\"
    echo \"  cleanup        Run cleanup tasks only\"
    echo \"  optimize       Run optimization tasks only\"
    echo \"  security       Run security checks only\"
    echo \"  status         Check system status only\"
    echo \"  report         Generate maintenance report\"
    echo \"  help           Show this help message\"
    echo \"\"
    echo \"Examples:\"
    echo \"  $0              # Run full maintenance\"
    echo \"  $0 cleanup      # Run cleanup tasks only\"
    echo \"  $0 status       # Check system status\"
}

# Ensure log directory exists
mkdir -p \"$LOG_DIR\"

# Main execution
case \"${1:-full}\" in
    \"full\")
        print_header
        log \"Starting full maintenance routine...\"
        
        check_system_status
        cleanup_logs
        cleanup_sessions
        cleanup_cache
        cleanup_temp_files
        optimize_database
        check_disk_usage
        check_security
        update_composer
        generate_report
        
        echo -e \"${GREEN}\"
        echo \"===========================================\"
        echo \"  🎉 MAINTENANCE COMPLETED SUCCESSFULLY!\"
        echo \"===========================================\"
        echo -e \"${NC}\"
        log \"Full maintenance routine completed\"
        ;;
    \"cleanup\")
        print_header
        log \"Starting cleanup tasks...\"
        
        cleanup_logs
        cleanup_sessions
        cleanup_cache
        cleanup_temp_files
        
        echo -e \"${GREEN}✓ Cleanup tasks completed${NC}\"
        ;;
    \"optimize\")
        print_header
        log \"Starting optimization tasks...\"
        
        cleanup_cache
        optimize_database
        
        # Re-cache for production
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
        
        echo -e \"${GREEN}✓ Optimization tasks completed${NC}\"
        ;;
    \"security\")
        print_header
        check_security
        ;;
    \"status\")
        print_header
        check_system_status
        check_disk_usage
        ;;
    \"report\")
        generate_report
        ;;
    \"help\")
        show_usage
        ;;
    *)
        echo -e \"${RED}Error: Unknown command '$1'${NC}\"
        echo \"\"
        show_usage
        exit 1
        ;;
esac