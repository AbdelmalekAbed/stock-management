# InfinityFree Deployment Guide

## Prerequisites
- InfinityFree account (free)
- FileZilla or similar FTP client
- Your project files ready

## Step 1: Create InfinityFree Account

1. Go to https://infinityfree.net
2. Click "Sign Up Now"
3. Fill in the registration form
4. Verify your email address

## Step 2: Create a New Account/Website

1. Login to InfinityFree control panel
2. Click "Create Account"
3. Choose a subdomain (e.g., `yourusername.infinityfreeapp.com`)
   - Or use your own domain if you have one
4. Set a label (e.g., "Stock Management System")
5. Click "Create Account"

## Step 3: Get Your FTP Credentials

1. In the control panel, find your new account
2. Click "Manage" → "FTP Details"
3. Note down:
   - **FTP Hostname**: (e.g., `ftpupload.net`)
   - **FTP Username**: (e.g., `if0_12345678`)
   - **FTP Password**: (shown once, save it!)
   - **FTP Port**: 21

## Step 4: Get MySQL Database Credentials

1. In control panel, click "MySQL Databases"
2. Create a new database:
   - Database name will be auto-generated (e.g., `if0_12345678_stock`)
   - Username will match your FTP username
   - Set a strong password
3. Note down:
   - **Database Host**: (e.g., `sql123.infinityfree.com`)
   - **Database Name**: `if0_12345678_stock`
   - **Database Username**: `if0_12345678`
   - **Database Password**: Your chosen password

## Step 5: Prepare Your Project Files

### 5.1 Update .env File

Create/update `.env` with InfinityFree credentials:

```env
# Database Configuration
DB_HOST=sql123.infinityfree.com
DB_NAME=if0_12345678_stock
DB_USER=if0_12345678
DB_PASS=your_database_password

# Security
SESSION_LIFETIME=3600
CSRF_TOKEN_NAME=csrf_token
MAX_LOGIN_ATTEMPTS=5
LOGIN_LOCKOUT_TIME=900

# File Upload
MAX_FILE_SIZE=5242880
ALLOWED_IMAGE_TYPES=jpg,jpeg,png,gif

# Environment (IMPORTANT: Set to production)
APP_ENV=production
APP_DEBUG=false
FORCE_HTTPS=true

# Error Logging
LOG_ERRORS=true
LOG_FILE=logs/error.log
```

### 5.2 Create .htaccess for Root Directory

Create `/htaccess` (without the dot) with this content:

```apache
# Redirect to gestion-stock-template directory
RewriteEngine On
RewriteCond %{REQUEST_URI} !^/gestion-stock-template/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /gestion-stock-template/$1 [L]

# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Prevent directory browsing
Options -Indexes

# Protect sensitive files
<FilesMatch "^\.env$">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "\.log$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

## Step 6: Upload Files via FTP

### Using FileZilla:

1. **Install FileZilla Client**
   ```bash
   sudo apt install filezilla
   ```

2. **Connect to InfinityFree**
   - Host: `ftpupload.net`
   - Username: `if0_12345678`
   - Password: Your FTP password
   - Port: 21

3. **Upload Structure**
   ```
   Remote site: /htdocs/
   ├── .env (upload and rename htaccess to .htaccess)
   ├── .htaccess
   ├── config.php
   ├── security.php
   ├── index.php
   ├── gestion-stock-template/
   │   ├── (all PHP files)
   │   ├── assets/
   │   ├── image/
   │   └── ...
   ├── php/
   │   └── Class/
   ├── logs/ (create empty directory)
   └── migrations/
       └── 001_security_migration.sql
   ```

4. **Important**: Upload to `/htdocs/` directory (this is the web root)

5. **Set File Permissions**
   - Right-click on `logs/` → File Permissions → Set to `777` (or `775`)
   - Right-click on `image/` folders → Set to `775`
   - Right-click on `.env` → Set to `600`

## Step 7: Import Database

### Using phpMyAdmin:

1. In InfinityFree control panel, click "MySQL Databases"
2. Click "PhpMyAdmin" button
3. Login with your MySQL credentials
4. Select your database (e.g., `if0_12345678_stock`)
5. Click "Import" tab
6. Choose your SQL file: `gestion_des_stocks.sql`
7. Click "Go" to import
8. After import, run the security migration:
   - Go to "SQL" tab
   - Paste contents of `migrations/001_security_migration.sql`
   - Click "Go"

### Manual Password Hashing (Since you can't run PHP scripts):

1. In phpMyAdmin, select your database
2. Click on `admin` table
3. For each admin record, update the `mdp` field with hashed password:
   
   **To generate bcrypt hash for password "0000":**
   - Use online tool: https://bcrypt-generator.com/
   - Enter "0000"
   - Cost: 12
   - Copy the generated hash (starts with `$2y$12$`)
   
4. Update each admin:
   ```sql
   UPDATE admin 
   SET mdp = '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5lDhJ0F0i0XDm'
   WHERE email = 'abdelmalek.abed321@gmail.com';
   ```
   
   (Use the hash you generated from bcrypt-generator.com)

## Step 8: Test Your Deployment

1. Visit your site: `https://yourusername.infinityfreeapp.com`
2. Should redirect to gestion-stock-template
3. Test login with:
   - Email: `abdelmalek.abed321@gmail.com`
   - Password: `0000`

## Step 9: Post-Deployment Checklist

- [ ] Login works
- [ ] Dashboard displays correctly
- [ ] Product images load
- [ ] CRUD operations work
- [ ] File uploads work
- [ ] Client registration works
- [ ] HTTPS is active (padlock in browser)
- [ ] No error messages displayed
- [ ] Check `logs/error.log` for any issues

## Common Issues & Solutions

### Issue 1: Database Connection Failed
**Solution**: 
- Double-check `.env` DB credentials
- Verify database exists in phpMyAdmin
- Check DB_HOST is correct (not localhost)

### Issue 2: 500 Internal Server Error
**Solution**:
- Check `.htaccess` syntax
- Set `APP_DEBUG=true` temporarily in `.env` to see errors
- Check file permissions (logs/ should be 777)

### Issue 3: Images Not Loading
**Solution**:
- Check `image/` folder permissions (775)
- Verify images uploaded via FTP
- Check paths in database

### Issue 4: CSRF Token Errors
**Solution**:
- Clear browser cookies
- Check session directory permissions
- Verify `session.save_path` in PHP settings

### Issue 5: Login Not Working
**Solution**:
- Verify passwords are hashed in database
- Check if `login_attempts` table exists
- Clear sessions and try again

## Performance Tips

1. **Enable Caching** in `.htaccess`:
   ```apache
   # Browser Caching
   <IfModule mod_expires.c>
       ExpiresActive On
       ExpiresByType image/jpg "access plus 1 year"
       ExpiresByType image/jpeg "access plus 1 year"
       ExpiresByType image/gif "access plus 1 year"
       ExpiresByType image/png "access plus 1 year"
       ExpiresByType text/css "access plus 1 month"
       ExpiresByType application/javascript "access plus 1 month"
   </IfModule>
   ```

2. **Compress Files**:
   ```apache
   # GZIP Compression
   <IfModule mod_deflate.c>
       AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css application/javascript
   </IfModule>
   ```

## Security Recommendations

1. **Change Default Passwords**: After first login, change all default passwords
2. **Regular Backups**: Use InfinityFree's backup feature weekly
3. **Monitor Logs**: Check `logs/error.log` regularly
4. **Update Super Admin**: Set `is_super_admin=1` for your account in database
5. **Disable Registration**: If not needed, comment out signup links

## Limitations of InfinityFree

- **512 MB disk space** (should be enough for your project)
- **10 GB bandwidth/month** (adequate for portfolio demo)
- **Ads on free plan** (removable with premium)
- **No SSH access** (use FTP only)
- **No email sending** (SMTP blocked, use external service)
- **Hit limit**: 50,000 hits/day

## Alternative: If InfinityFree Doesn't Work

If you encounter issues, try:
1. **000webhost** - Similar free hosting
2. **ByetHost** - Another PHP/MySQL option
3. **Heroku** - Free tier with PHP buildpack
4. **Railway.app** - Modern deployment platform

## Support

- InfinityFree Forum: https://forum.infinityfree.net
- InfinityFree Support: support@infinityfree.net
- Your project GitHub: https://github.com/AbdelmalekAbed/stock-management

---

**Note**: Free hosting is perfect for portfolio demonstration. For production use with real users, consider upgrading to paid hosting ($5-10/month).
