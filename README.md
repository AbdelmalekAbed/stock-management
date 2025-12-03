# Stock Management System

🌐 **Live Demo**: [https://abdou-store.lovestoblog.com](https://abdou-store.lovestoblog.com)

A comprehensive web application for inventory and stock management built with PHP and MySQL, featuring both admin and customer interfaces with role-based access control.

## Features

### Admin Dashboard
- **Real-time KPIs** 
  - Total Purchases
  - Total Sales
  - Total Profit
  - Total Products in inventory
- **Top 4 Sales** tracking
- **Product Management** - Add, edit, delete, and track products with images
- **Purchase Management** - Record and manage supplier purchases
- **Sales Management** - Process customer orders with delete confirmations
- **Customer Management** - Maintain customer database with profiles
- **Supplier Management** - Track supplier information and purchases
- **Category Management** - Organize products by categories
- **Brand Management** - Manage product brands
- **Admin Management** - Super admin can add/delete administrators (restricted to one person only preferably)

### Client Storefront
- **Public Shop** - Browse and purchase products
- **User Registration** - Create account with profile photo upload
- **User Authentication** - Secure login system
- **Profile Management** - Edit personal information and profile picture
- **Product Details** - View detailed product information with enlarged quantity selector

### Security Features ✨ NEW
- **Password Hashing** - Bcrypt with cost factor 12
- **CSRF Protection** - Secure tokens on all forms
- **Rate Limiting** - 5 attempts, 15-minute lockout
- **Session Security** - Timeout, regeneration, hijacking prevention
- **Input Validation** - Email, phone, and data sanitization
- **File Upload Security** - Type validation, size limits, secure naming
- **SQL Injection Prevention** - Prepared statements throughout
- **Error Logging** - Comprehensive logging without exposing details
- **Role-based Access Control**
  - Super Admin (database-driven) - Full access including admin management
  - Regular Admins - Access to all features except admin management
  - Clients - Access to storefront and personal profile
- **Security Headers** - XSS, clickjacking, MIME sniffing protection
- **Environment Config** - Sensitive data in .env file

## Technology Stack

- **Backend**: PHP 8.x
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (jQuery)
- **UI Framework**: Bootstrap 5
- **Additional Libraries**:
  - DataTables - Interactive tables
  - SweetAlert - Beautiful alerts
  - Feather Icons - Modern iconography
  - ApexCharts - Data visualization
  - DOMPDF - PDF generation

## Architecture

- **Object-Oriented Programming (OOP)**
- **Model-View-Controller (MVC)** pattern
- **SOLID Principles**
- **Database Access Object (DAO)** pattern

## Installation

### Quick Start (Recommended)

For new installations with security features:

```bash
# Clone the repository
git clone <repository-url> stock-management-main
cd stock-management-main

# Run the automated migration script
./migrate.sh
```

The script will:
- Create `.env` configuration file
- Backup your database
- Run security migrations
- Hash existing passwords
- Set up logging

### Manual Installation

#### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher / MariaDB 10.4 or higher
- Web server (Apache/Nginx) or PHP built-in server

#### Step-by-Step

1. **Clone the repository**
   ```bash
   cd ~/Downloads
   git clone <repository-url> stock-management-main
   cd stock-management-main
   ```

2. **Configure environment**
   ```bash
   cp .env.example .env
   nano .env  # Update with your database credentials
   ```

3. **Import the database**
   ```bash
   mysql -u root -p < gestion_des_stocks.sql
   ```

4. **Run security migrations**
   ```bash
   # Add security features to database
   mysql -u root -p gestion_des_stocks < migrations/001_security_migration.sql
   
   # Migrate passwords to hashed format
   php migrations/migrate_passwords.php
   
   # Delete migration script for security
   rm migrations/migrate_passwords.php
   ```

5. **Set up file permissions**
   ```bash
   chmod 600 .env
   chmod -R 775 gestion-stock-template/image/
   chmod -R 775 gestion-stock-template/facture/
   chmod -R 775 logs/
   ```

6. **Start the server**
   ```bash
   php -S localhost:8000
   ```

7. **Access the application**
   - Landing Page: http://localhost:8000
   - Admin Dashboard: http://localhost:8000/gestion-stock-template/index.php
   - Client Sign In: http://localhost:8000/gestion-stock-template/client_signin.php

### 📚 Additional Documentation

- **[MIGRATION_GUIDE.md](MIGRATION_GUIDE.md)** - Step-by-step security migration
- **[SECURITY.md](SECURITY.md)** - Comprehensive security features overview
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Production deployment guide
- **[TODO.md](TODO.md)** - Remaining tasks and priorities


## Project Structure

```
stock-management-main/
├── gestion-stock-template/          # Main application directory
│   ├── index.php                    # Admin dashboard
│   ├── shop.php                     # Public storefront
│   ├── client_signin.php            # Unified authentication
│   ├── client_signup.php            # Customer registration
│   ├── sidebar.php                  # Admin navigation menu
│   ├── header.php                   # Common header
│   ├── addproduct.php               # Add new product
│   ├── editproduct.php              # Edit product
│   ├── productlist.php              # Product listing
│   ├── addpurchase.php              # Add purchase
│   ├── purchaselist.php             # Purchase listing
│   ├── addsupplier.php              # Add supplier
│   ├── supplierlist.php             # Supplier listing
│   ├── addcustomer.php              # Add customer
│   ├── customerlist.php             # Customer listing
│   ├── addcategory.php              # Add category
│   ├── categorylist.php             # Category listing
│   ├── addbrand.php                 # Add brand
│   ├── brandlist.php                # Brand listing
│   ├── newuser.php                  # Add admin (super admin only)
│   ├── userlists.php                # Admin list
│   ├── assets/                      # CSS, JS, images, plugins
│   ├── image/                       # Uploaded images
│   │   ├── admin/                   # Admin profile photos
│   │   ├── client/                  # Client profile photos
│   │   ├── product/                 # Product images
│   │   ├── brand/                   # Brand logos
│   │   ├── category/                # Category images
│   │   └── supplier/                # Supplier photos
│   ├── dompdf/                      # PDF generation library
│   └── facture/                     # Invoice generation
├── php/                             # Backend classes
│   └── Class/
│       ├── Dao.php                  # Database access layer
│       ├── Admin.php                # Admin management
│       ├── Client.php               # Client management
│       ├── Product.php              # Product operations
│       ├── Purchase.php             # Purchase operations
│       ├── Sale.php                 # Sales operations
│       ├── Supplier.php             # Supplier management
│       ├── Categorie.php            # Category management
│       ├── Marque.php               # Brand management
│       └── ...
├── index.php                        # Entry point (redirects to shop)
├── gestion_des_stocks.sql           # Database schema and sample data
└── README.md                        # This file
```


## Image Upload Requirements

- **Supported formats**: JPG, JPEG, PNG, GIF
- **Maximum file size**: 5MB (configurable in .env)
- **Security**: MIME type validation, secure filename generation
- **Storage locations**:
  - Client photos: `gestion-stock-template/image/client/`
  - Admin photos: `gestion-stock-template/image/admin/`
  - Product images: `gestion-stock-template/image/product/`
  - Category images: `gestion-stock-template/image/category/`
  - Brand logos: `gestion-stock-template/image/brand/`
  - Supplier photos: `gestion-stock-template/image/supplier/`

## Database Schema

### Main Tables
- `admin` - Administrator accounts
- `client` - Customer accounts
- `produit` - Product catalog
- `categorie` - Product categories
- `marque` - Product brands
- `fournisseur` - Supplier information
- `approvisionnement` - Purchase orders
- `commande` - Sales orders
- `contient_pr` - Sales order items
- `est_compose` - Purchase order items


## Browser Support

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Modern browsers with JavaScript enabled



## Support

For issues or questions, please contact abdelmalek.abed321@gmail.com

---

