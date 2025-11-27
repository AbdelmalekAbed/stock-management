# Security Migration Instructions

## Overview
This guide walks you through migrating your Stock Management System to include comprehensive security features including password hashing, CSRF protection, rate limiting, and more.

## Step 1: Backup Everything

```bash
# Backup database
mysqldump -u root -p gestion_des_stocks > backup_before_migration_$(date +%Y%m%d).sql

# Backup files
tar -czf backup_files_$(date +%Y%m%d).tar.gz gestion-stock-template/image/
```

## Step 2: Update Code

The new security features are already integrated into your code. Make sure you have:
- `config.php` - Configuration management
- `security.php` - Security utilities
- `.env.example` - Environment template
- `migrations/` directory with SQL and PHP migration scripts

## Step 3: Create .env File

```bash
# Copy the example
cp .env.example .env

# Edit with your actual values
nano .env
```

**Update these values in .env:**
```ini
DB_HOST=localhost
DB_NAME=gestion_des_stocks
DB_USER=root
DB_PASS=Abdou_pass0

# For development
APP_ENV=development
APP_DEBUG=true
FORCE_HTTPS=false

# For production (after deploying)
# APP_ENV=production
# APP_DEBUG=false
# FORCE_HTTPS=true
```

## Step 4: Run Database Migration

```bash
# Run the security migration SQL
mysql -u root -p gestion_des_stocks < migrations/001_security_migration.sql
```

**What this does:**
- Adds `is_super_admin` TINYINT column to `admin` table
- Changes password field to VARCHAR(255) to support hashed passwords
- Sets Abdelmalek as super admin
- Adds password field to `client` table
- Creates `login_attempts` table for tracking
- Adds performance indexes

## Step 5: Migrate Existing Passwords

```bash
# Run the password migration script
php migrations/migrate_passwords.php
```

**Expected output:**
```
=== Password Migration Script ===

✓ Connected to database

Migrating admin passwords...
  ✓ Updated password for admin: belcaida@email.com
  ✓ Updated password for admin: benhima@email.com
  ✓ Updated password for admin: abdelmalek.abed321@gmail.com

✓ Migrated 3 admin passwords

Migrating client passwords...
  ✓ Updated password for client: client1@email.com
  ...

✓ Migrated X client passwords

=== Migration Complete ===
```

**IMPORTANT:** After successfully running this script, delete it for security:
```bash
rm migrations/migrate_passwords.php
```

## Step 6: Test the Migration

### Test Admin Login
1. Go to http://localhost:8000/gestion-stock-template/client_signin.php
2. Login with:
   - Email: abdelmalek.abed321@gmail.com
   - Password: 0000 (your password is now securely hashed!)
3. Verify you can access the admin dashboard

### Test Client Login/Registration
1. Create a new client account
2. Verify password is required (minimum 8 characters now)
3. Upload a profile photo (now with enhanced validation)
4. Login with the new account

### Test Rate Limiting
1. Try logging in with wrong password 6 times
2. You should see a lockout message after 5 failed attempts
3. Wait 15 minutes or restart your session to try again

### Test CSRF Protection
1. Open browser dev tools (F12)
2. Inspect any form - you should see a hidden `csrf_token` field
3. Try submitting without the token (it will fail with 403)

## Step 7: Verify Security Features

### Check Database Changes
```sql
-- Verify super admin flag
SELECT id, email, is_super_admin FROM admin WHERE is_super_admin = 1;

-- Verify passwords are hashed (should be long strings starting with $2y$)
SELECT email, LENGTH(mdp) as password_length FROM admin;
-- Should show length 60 (bcrypt hashes are 60 characters)
```

### Check Logs
```bash
# Error logs should be created in logs/ directory
ls -la logs/
cat logs/error.log
```

## What's Changed?

### Authentication
- ✅ Passwords now hashed with bcrypt (cost 12)
- ✅ Rate limiting prevents brute force (5 attempts, 15 min lockout)
- ✅ Session regeneration on login
- ✅ Session timeout after 1 hour of inactivity
- ✅ Protection against session hijacking

### Forms
- ✅ CSRF tokens on all forms
- ✅ Input sanitization
- ✅ Email validation
- ✅ Phone number validation (10 digits)

### File Uploads
- ✅ File type validation (JPG, PNG, GIF only)
- ✅ File size limits (5MB max)
- ✅ Secure filename generation
- ✅ MIME type checking
- ✅ Image dimension validation

### Database
- ✅ Super admin flag in database (not hardcoded)
- ✅ Prepared statements with PDO
- ✅ Error logging instead of displaying

### Configuration
- ✅ Environment variables (.env file)
- ✅ Separate dev/production modes
- ✅ Configurable security settings
- ✅ Secure session cookies

## Updating Other Pages

The core authentication files (signin, signup) are already updated. For other forms in your application, you need to:

### 1. Add CSRF Protection to Forms

**At the top of the PHP file:**
```php
<?php
require_once(__DIR__ . '/../security.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::checkCSRF();
    // ... your form processing
}
```

**In the HTML form:**
```php
<form method="post" action="">
    <?php echo Security::getCSRFField(); ?>
    <!-- rest of your form fields -->
</form>
```

### 2. Sanitize Inputs

```php
// Instead of:
$name = $_POST['name'];

// Use:
$name = Security::sanitizeInput($_POST['name'] ?? '');
```

### 3. Validate Emails and Phones

```php
if (!Security::validateEmail($email)) {
    $errors[] = "Invalid email address";
}

if (!empty($phone) && !Security::validatePhone($phone)) {
    $errors[] = "Invalid phone number";
}
```

### 4. Validate File Uploads

```php
if (isset($_FILES['image'])) {
    $validation = Security::validateImageUpload($_FILES['image']);
    
    if (!$validation['valid']) {
        $errors = array_merge($errors, $validation['errors']);
    } else {
        $filename = Security::generateSecureFilename('product', $validation['extension']);
        // ... process upload
    }
}
```

## Common Issues & Solutions

### Issue: "CSRF token validation failed"
**Solution:** Make sure you added `<?php echo Security::getCSRFField(); ?>` inside your form tags.

### Issue: Passwords too long error
**Solution:** The migration already updated the mdp column to VARCHAR(255). Verify with:
```sql
DESCRIBE admin;
```

### Issue: Can't login after migration
**Solution:** 
1. Check logs/error.log for details
2. Verify password migration ran successfully
3. Try resetting password in database if needed

### Issue: Session timeout too fast/slow
**Solution:** Adjust SESSION_LIFETIME in .env file (value in seconds)

### Issue: File uploads rejected
**Solution:** Check MAX_FILE_SIZE and ALLOWED_IMAGE_TYPES in .env

## Development vs Production

### Development Mode (.env)
```ini
APP_ENV=development
APP_DEBUG=true
FORCE_HTTPS=false
```
- Shows detailed errors
- No HTTPS requirement
- Verbose logging

### Production Mode (.env)
```ini
APP_ENV=production
APP_DEBUG=false
FORCE_HTTPS=true
```
- Hides errors from users
- Forces HTTPS
- Logs only to file

## Next Steps

1. ✅ Test all functionality thoroughly
2. ✅ Update remaining admin forms with CSRF tokens
3. ✅ Review all file upload locations
4. ✅ Set up automated backups
5. ✅ Configure SSL certificate for production
6. ✅ Review and adjust security settings as needed

## Support

If you encounter issues during migration:
1. Check `logs/error.log` for error details
2. Review this guide carefully
3. Verify all steps were completed
4. Test in development before production

## Security Best Practices

- Change default passwords immediately
- Use strong, unique passwords for database
- Keep .env file secret (never commit to git)
- Enable HTTPS in production
- Monitor error logs regularly
- Keep backups current
- Review user accounts periodically
