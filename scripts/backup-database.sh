#!/bin/bash

# Database Backup Script for Laravel E-commerce
# Optimized for Shared Hosting

set -e

# Colors
RED='\\033[0;31m'
GREEN='\\033[0;32m'
YELLOW='\\033[1;33m'
NC='\\033[0m'

# Configuration
BACKUP_DIR=\"database_backups\"
RETENTION_DAYS=30
COMPRESSION=true
LOG_FILE=\"backup.log\"

# Load environment variables
if [ -f \".env\" ]; then
    export $(grep -v '^#' .env | xargs)
else
    echo -e \"${RED}Error: .env file not found${NC}\"
    exit 1
fi

# Functions
log() {
    echo \"[$(date '+%Y-%m-%d %H:%M:%S')] $1\" | tee -a \"$LOG_FILE\"
}

create_backup_dir() {
    if [ ! -d \"$BACKUP_DIR\" ]; then
        mkdir -p \"$BACKUP_DIR\"
        log \"Created backup directory: $BACKUP_DIR\"
    fi
}

backup_database() {
    local timestamp=$(date +\"%Y%m%d_%H%M%S\")
    local backup_file=\"${BACKUP_DIR}/db_backup_${timestamp}.sql\"
    
    log \"Starting database backup...\"
    
    # Create mysqldump command
    local dump_cmd=\"mysqldump\"
    
    # Add host if specified
    if [ -n \"$DB_HOST\" ] && [ \"$DB_HOST\" != \"localhost\" ]; then
        dump_cmd=\"$dump_cmd -h $DB_HOST\"
    fi
    
    # Add port if specified
    if [ -n \"$DB_PORT\" ] && [ \"$DB_PORT\" != \"3306\" ]; then
        dump_cmd=\"$dump_cmd -P $DB_PORT\"
    fi
    
    # Add credentials and database
    dump_cmd=\"$dump_cmd -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE\"
    
    # Add options for better compatibility
    dump_cmd=\"$dump_cmd --single-transaction --routines --triggers --lock-tables=false\"
    
    # Execute backup
    if eval \"$dump_cmd > $backup_file\"; then
        log \"Database backup created: $backup_file\"
        
        # Compress if enabled
        if [ \"$COMPRESSION\" = true ]; then
            if command -v gzip &> /dev/null; then
                gzip \"$backup_file\"
                backup_file=\"${backup_file}.gz\"
                log \"Backup compressed: $backup_file\"
            fi
        fi
        
        # Get file size
        if [ -f \"$backup_file\" ]; then
            local size=$(ls -lh \"$backup_file\" | awk '{print $5}')
            log \"Backup size: $size\"
            echo -e \"${GREEN}✓ Database backup completed successfully${NC}\"
            echo \"File: $backup_file\"
            echo \"Size: $size\"
        fi
    else
        log \"ERROR: Database backup failed\"
        echo -e \"${RED}Error: Database backup failed${NC}\"
        exit 1
    fi
}

cleanup_old_backups() {
    log \"Cleaning up old backups (older than $RETENTION_DAYS days)...\"
    
    local deleted_count=0
    
    # Find and delete old backup files
    if [ -d \"$BACKUP_DIR\" ]; then
        while IFS= read -r -d '' file; do
            rm \"$file\"
            deleted_count=$((deleted_count + 1))
            log \"Deleted old backup: $(basename \"$file\")\"
        done < <(find \"$BACKUP_DIR\" -name \"db_backup_*.sql*\" -type f -mtime +$RETENTION_DAYS -print0 2>/dev/null)
    fi
    
    if [ $deleted_count -gt 0 ]; then
        log \"Deleted $deleted_count old backup file(s)\"
        echo -e \"${GREEN}✓ Cleaned up $deleted_count old backup(s)${NC}\"
    else
        log \"No old backups to clean up\"
        echo -e \"${YELLOW}No old backups found to clean up${NC}\"
    fi
}

list_backups() {
    echo -e \"${YELLOW}Available backups:${NC}\"
    
    if [ -d \"$BACKUP_DIR\" ] && [ \"$(ls -A $BACKUP_DIR 2>/dev/null)\" ]; then
        echo \"Directory: $BACKUP_DIR\"
        echo \"\"
        
        # List all backup files with details
        find \"$BACKUP_DIR\" -name \"db_backup_*.sql*\" -type f -exec ls -lh {} \\; | \\n            awk '{print $9 \"\\t\" $5 \"\\t\" $6 \" \" $7 \" \" $8}' | \\n            sort -r
    else
        echo \"No backups found in $BACKUP_DIR\"
    fi
}

restore_backup() {
    local backup_file=\"$1\"
    
    if [ -z \"$backup_file\" ]; then
        echo -e \"${RED}Error: Please specify backup file to restore${NC}\"
        echo \"Usage: $0 restore <backup_file>\"
        echo \"\"
        list_backups
        exit 1
    fi
    
    if [ ! -f \"$backup_file\" ]; then
        echo -e \"${RED}Error: Backup file not found: $backup_file${NC}\"
        exit 1
    fi
    
    echo -e \"${YELLOW}WARNING: This will replace all data in database '$DB_DATABASE'${NC}\"
    read -p \"Are you sure you want to continue? (type 'yes' to confirm): \" -r
    
    if [ \"$REPLY\" != \"yes\" ]; then
        echo \"Restore cancelled\"
        exit 0
    fi
    
    log \"Starting database restore from: $backup_file\"
    
    # Determine if file is compressed
    if [[ \"$backup_file\" == *.gz ]]; then
        local restore_cmd=\"gunzip -c $backup_file | mysql\"
    else
        local restore_cmd=\"mysql\"
    fi
    
    # Add connection parameters
    if [ -n \"$DB_HOST\" ] && [ \"$DB_HOST\" != \"localhost\" ]; then
        restore_cmd=\"$restore_cmd -h $DB_HOST\"
    fi
    
    if [ -n \"$DB_PORT\" ] && [ \"$DB_PORT\" != \"3306\" ]; then
        restore_cmd=\"$restore_cmd -P $DB_PORT\"
    fi
    
    restore_cmd=\"$restore_cmd -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE\"
    
    # Execute restore
    if [[ \"$backup_file\" == *.gz ]]; then
        if gunzip -c \"$backup_file\" | mysql -h \"$DB_HOST\" -P \"$DB_PORT\" -u \"$DB_USERNAME\" -p\"$DB_PASSWORD\" \"$DB_DATABASE\"; then
            log \"Database restore completed successfully\"
            echo -e \"${GREEN}✓ Database restored successfully${NC}\"
        else
            log \"ERROR: Database restore failed\"
            echo -e \"${RED}Error: Database restore failed${NC}\"
            exit 1
        fi
    else
        if mysql -h \"$DB_HOST\" -P \"$DB_PORT\" -u \"$DB_USERNAME\" -p\"$DB_PASSWORD\" \"$DB_DATABASE\" < \"$backup_file\"; then
            log \"Database restore completed successfully\"
            echo -e \"${GREEN}✓ Database restored successfully${NC}\"
        else
            log \"ERROR: Database restore failed\"
            echo -e \"${RED}Error: Database restore failed${NC}\"
            exit 1
        fi
    fi
}

show_usage() {
    echo \"Database Backup Script for Laravel E-commerce\"
    echo \"\"
    echo \"Usage: $0 [command] [options]\"
    echo \"\"
    echo \"Commands:\"
    echo \"  backup          Create a new database backup\"
    echo \"  list           List all available backups\"
    echo \"  cleanup        Remove old backups (older than $RETENTION_DAYS days)\"
    echo \"  restore <file> Restore database from backup file\"
    echo \"  help           Show this help message\"
    echo \"\"
    echo \"Examples:\"
    echo \"  $0 backup\"
    echo \"  $0 list\"
    echo \"  $0 restore database_backups/db_backup_20241201_120000.sql.gz\"
    echo \"  $0 cleanup\"
}

check_dependencies() {
    local missing_deps=()
    
    # Check mysql client
    if ! command -v mysql &> /dev/null; then
        missing_deps+=(\"mysql-client\")
    fi
    
    # Check mysqldump
    if ! command -v mysqldump &> /dev/null; then
        missing_deps+=(\"mysqldump\")
    fi
    
    if [ ${#missing_deps[@]} -gt 0 ]; then
        echo -e \"${RED}Error: Missing dependencies: ${missing_deps[*]}${NC}\"
        echo \"Please install the required MySQL client tools\"
        exit 1
    fi
}

# Main execution
case \"${1:-backup}\" in
    \"backup\")
        check_dependencies
        create_backup_dir
        backup_database
        ;;
    \"list\")
        list_backups
        ;;
    \"cleanup\")
        cleanup_old_backups
        ;;
    \"restore\")
        check_dependencies
        restore_backup \"$2\"
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