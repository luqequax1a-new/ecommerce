#!/bin/bash

# Laravel E-commerce Health Check Script
# Monitor system health and send alerts

set -e

# Colors
RED='\\033[0;31m'
GREEN='\\033[0;32m'
YELLOW='\\033[1;33m'
BLUE='\\033[0;34m'
NC='\\033[0m'

# Configuration
HEALTH_LOG=\"health_check.log\"
ALERT_EMAIL=\"\"
MAX_RESPONSE_TIME=5
MAX_MEMORY_USAGE=80
MAX_DISK_USAGE=85
MAX_DB_CONNECTIONS=10

# Load environment if available
if [ -f \".env\" ]; then
    export $(grep -v '^#' .env | xargs 2>/dev/null || true)
fi

# Functions
log() {
    echo \"[$(date '+%Y-%m-%d %H:%M:%S')] $1\" | tee -a \"$HEALTH_LOG\"
}

print_status() {
    local status=\"$1\"
    local message=\"$2\"
    
    case \"$status\" in
        \"OK\")
            echo -e \"${GREEN}✓ $message${NC}\"
            ;;
        \"WARNING\")
            echo -e \"${YELLOW}⚠ $message${NC}\"
            ;;
        \"ERROR\")
            echo -e \"${RED}✗ $message${NC}\"
            ;;
        \"INFO\")
            echo -e \"${BLUE}ℹ $message${NC}\"
            ;;
    esac
}

check_laravel_status() {
    log \"Checking Laravel application status...\"
    
    # Check if artisan is working
    if timeout 10 php artisan --version &> /dev/null; then
        local version=$(php artisan --version 2>/dev/null | awk '{print $3}')
        print_status \"OK\" \"Laravel application is running (v$version)\"
        return 0
    else
        print_status \"ERROR\" \"Laravel application is not responding\"
        log \"ERROR: Laravel application health check failed\"
        return 1
    fi
}

check_database_connection() {
    log \"Checking database connection...\"
    
    # Test database connection
    if timeout 10 php artisan migrate:status &> /dev/null; then
        print_status \"OK\" \"Database connection is working\"
        
        # Check database responsiveness
        local start_time=$(date +%s%N)
        php -r \"
            try {
                \\$pdo = new PDO('mysql:host=$DB_HOST;port=${DB_PORT:-3306};dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD');
                \\$pdo->query('SELECT 1');
                echo 'OK';
            } catch (Exception \\$e) {
                echo 'ERROR';
            }
        \" &> /dev/null
        local end_time=$(date +%s%N)
        local response_time=$((($end_time - $start_time) / 1000000))
        
        if [ $response_time -gt $((MAX_RESPONSE_TIME * 1000)) ]; then
            print_status \"WARNING\" \"Database response time is slow: ${response_time}ms\"
        else
            print_status \"OK\" \"Database response time: ${response_time}ms\"
        fi
        
        return 0
    else
        print_status \"ERROR\" \"Database connection failed\"
        log \"ERROR: Database connection health check failed\"
        return 1
    fi
}

check_storage_permissions() {
    log \"Checking storage permissions...\"
    
    local permission_errors=0
    
    # Check critical directories
    local directories=(\"storage/app\" \"storage/logs\" \"storage/framework/cache\" \"storage/framework/sessions\" \"storage/framework/views\" \"bootstrap/cache\")
    
    for dir in \"${directories[@]}\"; do
        if [ -d \"$dir\" ]; then
            if [ -w \"$dir\" ]; then
                print_status \"OK\" \"$dir is writable\"
            else
                print_status \"ERROR\" \"$dir is not writable\"
                permission_errors=$((permission_errors + 1))
            fi
        else
            print_status \"WARNING\" \"$dir does not exist\"
            permission_errors=$((permission_errors + 1))
        fi
    done
    
    if [ $permission_errors -eq 0 ]; then
        return 0
    else
        log \"ERROR: $permission_errors storage permission issues found\"
        return 1
    fi
}

check_disk_usage() {
    log \"Checking disk usage...\"
    
    local disk_usage=$(df . | tail -1 | awk '{print $5}' | sed 's/%//')
    
    if [ \"$disk_usage\" -gt $MAX_DISK_USAGE ]; then
        print_status \"ERROR\" \"Disk usage is critical: ${disk_usage}%\"
        log \"ERROR: Critical disk usage: ${disk_usage}%\"
        return 1
    elif [ \"$disk_usage\" -gt $((MAX_DISK_USAGE - 10)) ]; then
        print_status \"WARNING\" \"Disk usage is high: ${disk_usage}%\"
        return 0
    else
        print_status \"OK\" \"Disk usage is normal: ${disk_usage}%\"
        return 0
    fi
}

check_memory_usage() {
    log \"Checking memory usage...\"
    
    # Get memory usage percentage
    local memory_usage=$(free | awk 'NR==2{printf \"%.0f\", $3*100/$2}')
    
    if [ \"$memory_usage\" -gt $MAX_MEMORY_USAGE ]; then
        print_status \"WARNING\" \"Memory usage is high: ${memory_usage}%\"
        return 0
    else
        print_status \"OK\" \"Memory usage is normal: ${memory_usage}%\"
        return 0
    fi
}

check_log_errors() {
    log \"Checking for recent errors in logs...\"
    
    local error_count=0
    
    # Check Laravel logs for recent errors
    if [ -d \"storage/logs\" ]; then
        # Look for errors in the last 24 hours
        local recent_errors=$(find storage/logs -name \"*.log\" -mtime -1 -exec grep -l \"ERROR\\|CRITICAL\\|EMERGENCY\" {} \\; 2>/dev/null | wc -l)
        
        if [ \"$recent_errors\" -gt 0 ]; then
            # Count actual error lines
            error_count=$(find storage/logs -name \"*.log\" -mtime -1 -exec grep -c \"ERROR\\|CRITICAL\\|EMERGENCY\" {} \\; 2>/dev/null | awk '{sum+=$1} END {print sum}')
            
            if [ \"$error_count\" -gt 10 ]; then
                print_status \"ERROR\" \"High number of recent errors: $error_count\"
                return 1
            elif [ \"$error_count\" -gt 0 ]; then
                print_status \"WARNING\" \"Recent errors found: $error_count\"
                return 0
            fi
        fi
    fi
    
    print_status \"OK\" \"No recent critical errors found\"
    return 0
}

check_cache_status() {
    log \"Checking cache status...\"
    
    # Check if caches are properly set for production
    if [ \"$APP_ENV\" = \"production\" ]; then
        local cache_issues=0
        
        # Check config cache
        if [ ! -f \"bootstrap/cache/config.php\" ]; then
            print_status \"WARNING\" \"Configuration cache is not enabled\"
            cache_issues=$((cache_issues + 1))
        fi
        
        # Check route cache
        if [ ! -f \"bootstrap/cache/routes-v7.php\" ]; then
            print_status \"WARNING\" \"Route cache is not enabled\"
            cache_issues=$((cache_issues + 1))
        fi
        
        if [ $cache_issues -eq 0 ]; then
            print_status \"OK\" \"Production caches are properly configured\"
        fi
    else
        print_status \"INFO\" \"Cache check skipped (not production environment)\"
    fi
    
    return 0
}

check_queue_status() {
    log \"Checking queue status...\"
    
    # Check if there are failed jobs
    local failed_jobs=$(php artisan queue:failed --format=json 2>/dev/null | jq length 2>/dev/null || echo \"0\")
    
    if [ \"$failed_jobs\" -gt 0 ]; then
        print_status \"WARNING\" \"$failed_jobs failed queue jobs found\"
    else
        print_status \"OK\" \"No failed queue jobs\"
    fi
    
    return 0
}

check_ssl_certificate() {
    if [ -n \"$APP_URL\" ] && [[ \"$APP_URL\" == https://* ]]; then
        log \"Checking SSL certificate...\"
        
        local domain=$(echo \"$APP_URL\" | sed 's|https://||' | sed 's|/.*||')
        
        # Check SSL certificate expiration
        local ssl_info=$(echo | timeout 10 openssl s_client -servername \"$domain\" -connect \"$domain:443\" 2>/dev/null | openssl x509 -noout -dates 2>/dev/null || echo \"\")
        
        if [ -n \"$ssl_info\" ]; then
            local expiry_date=$(echo \"$ssl_info\" | grep notAfter | cut -d= -f2)
            local expiry_timestamp=$(date -d \"$expiry_date\" +%s 2>/dev/null || echo \"0\")
            local current_timestamp=$(date +%s)
            local days_until_expiry=$(( (expiry_timestamp - current_timestamp) / 86400 ))
            
            if [ \"$days_until_expiry\" -lt 7 ]; then
                print_status \"ERROR\" \"SSL certificate expires in $days_until_expiry days\"
                return 1
            elif [ \"$days_until_expiry\" -lt 30 ]; then
                print_status \"WARNING\" \"SSL certificate expires in $days_until_expiry days\"
                return 0
            else
                print_status \"OK\" \"SSL certificate is valid ($days_until_expiry days remaining)\"
                return 0
            fi
        else
            print_status \"WARNING\" \"Could not check SSL certificate\"
            return 0
        fi
    else
        print_status \"INFO\" \"SSL check skipped (HTTP or no URL configured)\"
        return 0
    fi
}

generate_health_report() {
    local report_file=\"health_report_$(date +%Y%m%d_%H%M%S).json\"
    
    log \"Generating health report...\"
    
    # Create JSON health report
    cat > \"$report_file\" << EOF
{
  \"timestamp\": \"$(date -Iseconds)\",
  \"status\": \"$1\",
  \"checks\": {
    \"laravel\": $([ \"$2\" -eq 0 ] && echo \"true\" || echo \"false\"),
    \"database\": $([ \"$3\" -eq 0 ] && echo \"true\" || echo \"false\"),
    \"storage\": $([ \"$4\" -eq 0 ] && echo \"true\" || echo \"false\"),
    \"disk_usage\": $([ \"$5\" -eq 0 ] && echo \"true\" || echo \"false\"),
    \"memory\": $([ \"$6\" -eq 0 ] && echo \"true\" || echo \"false\")
  },
  \"metrics\": {
    \"disk_usage\": \"$(df . | tail -1 | awk '{print $5}')\",
    \"memory_usage\": \"$(free | awk 'NR==2{printf \"%.0f%%\", $3*100/$2}' 2>/dev/null || echo 'N/A')\",
    \"php_version\": \"$(php -r 'echo PHP_VERSION;')\",
    \"laravel_version\": \"$(php artisan --version 2>/dev/null | awk '{print $3}' || echo 'Unknown')\"
  }
}
EOF
    
    print_status \"OK\" \"Health report generated: $report_file\"
}

send_alert() {
    local status=\"$1\"
    local message=\"$2\"
    
    if [ -n \"$ALERT_EMAIL\" ]; then
        log \"Sending alert email to $ALERT_EMAIL\"
        
        # Send email alert (requires mail command)
        if command -v mail &> /dev/null; then
            echo \"Health Check Alert: $status\" | mail -s \"Laravel E-commerce Health Check - $status\" \"$ALERT_EMAIL\"
        else
            log \"WARNING: mail command not available for alerts\"
        fi
    fi
}

show_usage() {
    echo \"Laravel E-commerce Health Check Script\"
    echo \"\"
    echo \"Usage: $0 [options]\"
    echo \"\"
    echo \"Options:\"
    echo \"  --quick        Run quick health check (basic tests only)\"
    echo \"  --json         Output results in JSON format\"
    echo \"  --alert-email  Email address for alerts\"
    echo \"  --help         Show this help message\"
    echo \"\"
    echo \"Examples:\"
    echo \"  $0                                    # Full health check\"
    echo \"  $0 --quick                           # Quick check\"
    echo \"  $0 --alert-email admin@domain.com    # Send alerts via email\"
}

# Parse command line arguments
QUICK_MODE=false
JSON_OUTPUT=false

while [[ $# -gt 0 ]]; do
    case $1 in
        --quick)
            QUICK_MODE=true
            shift
            ;;
        --json)
            JSON_OUTPUT=true
            shift
            ;;
        --alert-email)
            ALERT_EMAIL=\"$2\"
            shift 2
            ;;
        --help)
            show_usage
            exit 0
            ;;
        *)
            echo \"Unknown option: $1\"
            show_usage
            exit 1
            ;;
    esac
done

# Main health check execution
log \"Starting health check...\"

if [ \"$JSON_OUTPUT\" = false ]; then
    echo -e \"${BLUE}\"
    echo \"===========================================\"
    echo \"  Laravel E-commerce Health Check\"
    echo \"  $(date)\"
    echo \"===========================================\"
    echo -e \"${NC}\"
fi

# Run health checks
laravel_status=0
db_status=0
storage_status=0
disk_status=0
memory_status=0
overall_status=\"HEALTHY\"

check_laravel_status || laravel_status=1
check_database_connection || db_status=1
check_storage_permissions || storage_status=1
check_disk_usage || disk_status=1
check_memory_usage || memory_status=1

if [ \"$QUICK_MODE\" = false ]; then
    check_log_errors
    check_cache_status
    check_queue_status
    check_ssl_certificate
fi

# Determine overall status
if [ $laravel_status -ne 0 ] || [ $db_status -ne 0 ] || [ $storage_status -ne 0 ]; then
    overall_status=\"CRITICAL\"
elif [ $disk_status -ne 0 ] || [ $memory_status -ne 0 ]; then
    overall_status=\"WARNING\"
fi

# Output results
if [ \"$JSON_OUTPUT\" = true ]; then
    generate_health_report \"$overall_status\" $laravel_status $db_status $storage_status $disk_status $memory_status
else
    echo \"\"
    echo -e \"${BLUE}Overall Status: \"
    case \"$overall_status\" in
        \"HEALTHY\")
            echo -e \"${GREEN}$overall_status${NC}\"
            ;;
        \"WARNING\")
            echo -e \"${YELLOW}$overall_status${NC}\"
            ;;
        \"CRITICAL\")
            echo -e \"${RED}$overall_status${NC}\"
            ;;
    esac
fi

# Send alerts if needed
if [ \"$overall_status\" = \"CRITICAL\" ]; then
    send_alert \"CRITICAL\" \"Health check failed - immediate attention required\"
    exit 2
elif [ \"$overall_status\" = \"WARNING\" ]; then
    send_alert \"WARNING\" \"Health check warnings detected\"
    exit 1
fi

log \"Health check completed successfully\"
exit 0