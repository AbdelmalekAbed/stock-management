# Stock Management System

A comprehensive web application for inventory and stock management built with PHP and MySQL, featuring both admin and customer interfaces with role-based access control.

## Features

### Admin Dashboard
- **Real-time KPIs** with currency conversion (MAD → TND)
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
- **Admin Management** - Super admin can add/delete administrators (restricted to Abdelmalek only)

### Client Storefront
- **Public Shop** - Browse and purchase products
- **User Registration** - Create account with profile photo upload
- **User Authentication** - Secure login system
- **Profile Management** - Edit personal information and profile picture
- **Product Details** - View detailed product information with enlarged quantity selector

### Security Features
- **Role-based Access Control**
  - Super Admin (abdelmalek.abed321@gmail.com) - Full access including admin management
  - Regular Admins - Access to all features except admin management
  - Clients - Access to storefront and personal profile
- **Session Management** - Secure PHP sessions
- **Delete Confirmations** - JavaScript confirmations prevent accidental deletions

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

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher / MariaDB 10.4 or higher
- Web server (Apache/Nginx) or PHP built-in server

### Setup Instructions

1. **Clone the repository**
   ```bash
   cd ~/Downloads
   git clone <repository-url> stock-management-main
   cd stock-management-main
   ```

2. **Import the database**
   ```bash
   mysql -u root -p < gestion_des_stocks.sql
   ```
   Or use phpMyAdmin to import `gestion_des_stocks.sql`

3. **Configure database connection**
   Edit the database credentials in `/php/Class/Dao.php`:
   ```php
   private $servername = "localhost";
   private $username = "root";
   private $password = "your_password";
   private $dbname = "gestion_des_stocks";
   ```

4. **Set up file permissions**
   ```bash
   chmod -R 755 gestion-stock-template/image/
   chmod -R 755 gestion-stock-template/facture/
   ```

5. **Start the server**
   ```bash
   php -S localhost:8000
   ```

6. **Access the application**
   - Landing Page: http://localhost:8000
   - Admin Dashboard: http://localhost:8000/gestion-stock-template/index.php
   - Client Sign In: http://localhost:8000/gestion-stock-template/client_signin.php

## Default Admin Credentials

- **Super Admin**:
  - Email: abdelmalek.abed321@gmail.com
  - Password: 0000

- **Regular Admin** (Haitam):
  - Email: belcaida@email.com
  - Password: 0000

- **Regular Admin** (Mohamed-Amine):
  - Email: benhima@email.com
  - Password: 0000

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

## Currency Conversion

The system uses MAD (Moroccan Dirham) as the base currency and displays values in TND (Tunisian Dinar) with a conversion rate of 0.32. Conversion functions are defined in `app_config.php`.

## Image Upload Requirements

- **Supported formats**: JPG, JPEG, PNG, GIF
- **Client photos**: Uploaded to `gestion-stock-template/image/client/`
- **Admin photos**: Uploaded to `gestion-stock-template/image/admin/`
- **Product images**: Uploaded to `gestion-stock-template/image/product/`
- **Maximum file size**: Default PHP upload limits apply

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

## Development Notes

- Passwords are currently stored in plain text (0000 for all default accounts)
- Consider implementing password hashing for production
- The system includes delete confirmations via `data-confirm-delete` attributes
- All delete operations check for dependencies before removal
- Super admin privileges are hardcoded to abdelmalek.abed321@gmail.com

## Browser Support

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Modern browsers with JavaScript enabled

## Contributing

This project follows OOP, MVC, and SOLID principles. When contributing:
1. Maintain the existing architecture
2. Add proper error handling
3. Include delete confirmations for destructive actions
4. Test with both super admin and regular admin accounts
5. Ensure responsive design compatibility

## License

[Add your license information here]

## Support

For issues or questions, please contact the development team.

---

**Version**: 1.0  
**Last Updated**: November 23, 2025
