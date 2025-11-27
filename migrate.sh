#!/bin/bash

# Stock Management System - Security Migration Quick Start
# This script helps you migrate to the secure version

set -e  # Exit on error

echo "======================================"
echo "Stock Management Security Migration"
echo "======================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running from correct directory
if [ ! -f "config.php" ]; then
    echo -e "${RED}Error: Please run this script from the project root directory${NC}"
    exit 1
fi

echo -e "${YELLOW}Step 1: Checking prerequisites...${NC}"

# Check PHP
if ! command -v php &> /dev/null; then
    echo -e "${RED}PHP is not installed. Please install PHP 8.0 or higher.${NC}"
    exit 1
fi
echo -e "${GREEN}✓ PHP found: $(php -v | head -n 1)${NC}"

# Check MySQL
if ! command -v mysql &> /dev/null; then
    echo -e "${RED}MySQL is not installed. Please install MySQL/MariaDB.${NC}"
    exit 1
fi
echo -e "${GREEN}✓ MySQL found${NC}"

echo ""
echo -e "${YELLOW}Step 2: Creating .env file...${NC}"

if [ -f ".env" ]; then
    echo -e "${YELLOW}⚠ .env file already exists. Skipping...${NC}"
else
    cp .env.example .env
    echo -e "${GREEN}✓ Created .env file from template${NC}"
    echo -e "${YELLOW}⚠ Please edit .env and update your database credentials!${NC}"
    echo ""
    read -p "Press Enter to continue after updating .env..."
fi

echo ""
echo -e "${YELLOW}Step 3: Creating backup...${NC}"

# Create backups directory
mkdir -p backups

# Backup database
echo "Enter MySQL root password for backup:"
BACKUP_FILE="backups/backup_$(date +%Y%m%d_%H%M%S).sql"
mysqldump -u root -p gestion_des_stocks > "$BACKUP_FILE" 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Database backed up to $BACKUP_FILE${NC}"
else
    echo -e "${RED}✗ Database backup failed${NC}"
    exit 1
fi

# Backup files
tar -czf "backups/files_$(date +%Y%m%d_%H%M%S).tar.gz" gestion-stock-template/image/ 2>/dev/null
echo -e "${GREEN}✓ Files backed up${NC}"

echo ""
echo -e "${YELLOW}Step 4: Running database migration...${NC}"

# Check if migration file exists
if [ ! -f "migrations/001_security_migration.sql" ]; then
    echo -e "${RED}Error: Migration file not found${NC}"
    exit 1
fi

# Run migration
echo "Enter MySQL root password for migration:"
mysql -u root -p gestion_des_stocks < migrations/001_security_migration.sql 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Database schema updated${NC}"
else
    echo -e "${RED}✗ Database migration failed${NC}"
    echo "Restoring from backup..."
    mysql -u root -p gestion_des_stocks < "$BACKUP_FILE"
    exit 1
fi

echo ""
echo -e "${YELLOW}Step 5: Migrating passwords...${NC}"

# Check if password migration script exists
if [ ! -f "migrations/migrate_passwords.php" ]; then
    echo -e "${RED}Error: Password migration script not found${NC}"
    exit 1
fi

# Run password migration
php migrations/migrate_passwords.php
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Passwords migrated to secure hashes${NC}"
    echo ""
    echo -e "${YELLOW}Deleting migration script for security...${NC}"
    rm migrations/migrate_passwords.php
    echo -e "${GREEN}✓ Migration script deleted${NC}"
else
    echo -e "${RED}✗ Password migration failed${NC}"
    exit 1
fi

echo ""
echo -e "${YELLOW}Step 6: Creating logs directory...${NC}"
mkdir -p logs
chmod 775 logs
echo -e "${GREEN}✓ Logs directory created${NC}"

echo ""
echo -e "${YELLOW}Step 7: Setting file permissions...${NC}"
chmod 600 .env 2>/dev/null
chmod 775 gestion-stock-template/image/ 2>/dev/null
chmod 775 gestion-stock-template/image/*/ 2>/dev/null
chmod 775 gestion-stock-template/facture/ 2>/dev/null
echo -e "${GREEN}✓ File permissions set${NC}"

echo ""
echo "======================================"
echo -e "${GREEN}Migration Complete!${NC}"
echo "======================================"
echo ""
echo "Next steps:"
echo "1. Test admin login:"
echo "   URL: http://localhost:8000/gestion-stock-template/client_signin.php"
echo "   Email: abdelmalek.abed321@gmail.com"
echo "   Password: 0000"
echo ""
echo "2. Test client registration/login"
echo ""
echo "3. Check logs for any errors:"
echo "   tail -f logs/error.log"
echo ""
echo "4. Review MIGRATION_GUIDE.md for detailed information"
echo ""
echo "5. For production deployment, see DEPLOYMENT.md"
echo ""
echo -e "${YELLOW}⚠ Important: Update .env with APP_ENV=production before deploying!${NC}"
echo ""
