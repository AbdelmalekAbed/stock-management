# Deployment Guide - Stock Management System

## Pre-Deployment Checklist

### 1. Database Migration
```bash
# Import initial database
mysql -u your_user -p < gestion_des_stocks.sql

# Run security migration
mysql -u your_user -p gestion_des_stocks < migrations/001_security_migration.sql

# Migrate passwords to hashed format
php migrations/migrate_passwords.php

# IMPORTANT: Delete migration script after running
rm migrations/migrate_passwords.php
```

### 2. Environment Configuration
```bash
# Copy .env.example to .env
cp .env.example .env

# Edit .env with production values
nano .env
```

**Required .env settings for production:**
```ini
DB_HOST=localhost
DB_NAME=gestion_des_stocks
DB_USER=your_db_user
DB_PASS=your_secure_db_password

SESSION_LIFETIME=3600
MAX_LOGIN_ATTEMPTS=5
LOGIN_LOCKOUT_TIME=900

MAX_FILE_SIZE=5242880
ALLOWED_IMAGE_TYPES=jpg,jpeg,png,gif

APP_ENV=production
APP_DEBUG=false
FORCE_HTTPS=true

LOG_ERRORS=true
LOG_FILE=logs/error.log
```

### 3. File Permissions
```bash
# Set proper permissions
chmod 755 gestion-stock-template/
chmod 755 php/

# Upload directories need write access
chmod 775 gestion-stock-template/image/
chmod 775 gestion-stock-template/image/admin/
chmod 775 gestion-stock-template/image/client/
chmod 775 gestion-stock-template/image/product/
chmod 775 gestion-stock-template/image/supplier/
chmod 775 gestion-stock-template/image/brand/
chmod 775 gestion-stock-template/image/category/

# Invoice directory
chmod 775 gestion-stock-template/facture/

# Logs directory
mkdir -p logs
chmod 775 logs

# Protect sensitive files
chmod 600 .env
chmod 600 config.php
```

### 4. Apache/Nginx Configuration

**Apache (using .htaccess - already provided):**
```bash
# Ensure mod_rewrite is enabled
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod expires
sudo a2enmod deflate

# Update .htaccess to force HTTPS (uncomment lines 8-10)
# Restart Apache
sudo systemctl restart apache2
```

**Nginx Configuration:**
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;

    root /var/www/stock-management;
    index index.php;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Root redirect
    location = / {
        return 301 /gestion-stock-template/shop.php;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ \.(log|env)$ {
        deny all;
    }
}
```

### 5. SSL Certificate (Let's Encrypt)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache  # For Apache
# OR
sudo apt install certbot python3-certbot-nginx   # For Nginx

# Obtain certificate
sudo certbot --apache -d yourdomain.com  # For Apache
# OR
sudo certbot --nginx -d yourdomain.com   # For Nginx

# Auto-renewal test
sudo certbot renew --dry-run
```

### 6. Database Security
```bash
# Create dedicated database user
mysql -u root -p

CREATE USER 'stock_admin'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON gestion_des_stocks.* TO 'stock_admin'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Update .env with new credentials
```

### 7. PHP Configuration
Edit `/etc/php/8.0/apache2/php.ini` (or appropriate path):
```ini
display_errors = Off
display_startup_errors = Off
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
log_errors = On
error_log = /var/www/stock-management/logs/php_errors.log

upload_max_filesize = 5M
post_max_size = 6M
max_execution_time = 30
memory_limit = 256M

session.cookie_httponly = 1
session.cookie_secure = 1
session.use_only_cookies = 1
session.cookie_samesite = Strict
```

### 8. Firewall Configuration
```bash
# UFW (Ubuntu/Debian)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable

# Check status
sudo ufw status
```

### 9. Backup Setup
```bash
# Create backup script
sudo nano /usr/local/bin/backup_stock_management.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/backups/stock-management"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u stock_admin -p'password' gestion_des_stocks > $BACKUP_DIR/db_$DATE.sql

# Backup files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/stock-management/gestion-stock-template/image/

# Keep only last 7 days of backups
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

```bash
# Make executable
sudo chmod +x /usr/local/bin/backup_stock_management.sh

# Add to crontab (daily at 2 AM)
sudo crontab -e
0 2 * * * /usr/local/bin/backup_stock_management.sh
```

### 10. Monitoring
```bash
# Install monitoring tool (optional)
sudo apt install logwatch

# Configure log monitoring
sudo nano /etc/logwatch/conf/logwatch.conf
```

## Post-Deployment Verification

### 1. Test HTTPS Redirect
```bash
curl -I http://yourdomain.com
# Should return 301 redirect to https://
```

### 2. Test Security Headers
```bash
curl -I https://yourdomain.com
# Should show security headers (X-Frame-Options, etc.)
```

### 3. Test Login Rate Limiting
- Attempt to login with wrong password 6 times
- Verify account lockout message appears

### 4. Test File Upload
- Create test client account
- Upload profile photo
- Verify file is stored securely with random name

### 5. Test CSRF Protection
- Submit form without CSRF token
- Verify 403 error

### 6. Check Error Logs
```bash
tail -f logs/error.log
tail -f logs/php_errors.log
```

### 7. Database Connection Test
- Login as admin
- Verify dashboard loads with correct data

## Security Hardening Checklist

- [ ] HTTPS enforced
- [ ] Database credentials secured in .env
- [ ] File permissions properly set
- [ ] Error display disabled
- [ ] Security headers configured
- [ ] CSRF protection active
- [ ] Password hashing enabled
- [ ] Rate limiting working
- [ ] Session security configured
- [ ] File upload validation active
- [ ] Backups scheduled
- [ ] Monitoring configured
- [ ] .env excluded from git
- [ ] Migration scripts deleted

## Maintenance

### Regular Tasks
1. **Daily**: Check error logs
2. **Weekly**: Verify backups
3. **Monthly**: Update dependencies
4. **Quarterly**: Security audit
5. **Yearly**: SSL certificate renewal (auto with certbot)

### Update Procedure
```bash
# Pull latest changes
git pull origin main

# Run any new migrations
php migrations/xxx_migration.php

# Clear PHP opcache if enabled
sudo systemctl reload php8.0-fpm
```

## Rollback Procedure
```bash
# Restore database
mysql -u stock_admin -p gestion_des_stocks < /backups/stock-management/db_YYYYMMDD_HHMMSS.sql

# Restore files
tar -xzf /backups/stock-management/files_YYYYMMDD_HHMMSS.tar.gz -C /

# Restart services
sudo systemctl restart apache2  # or nginx
```

## Support Contacts
- Server Administrator: [email]
- Database Administrator: [email]
- Developer: [email]

## Important URLs
- Production: https://yourdomain.com
- Admin Panel: https://yourdomain.com/gestion-stock-template/index.php
- PHPMyAdmin: https://yourdomain.com/phpmyadmin (if installed)
