# AMITAM Store - Client E-Commerce Platform

## Overview
Complete client-facing e-commerce system with visitor browsing, account registration, shopping cart, checkout, and order management.

## Features Implemented

### 1. Public Product Browsing (No Login Required)
- **shop.php** - Main product catalog page
  - Product grid with images, prices, and stock status
  - Filter by category and brand
  - Search functionality
  - Responsive design matching admin template

- **product_detail.php** - Individual product page
  - Full product information
  - Add to cart functionality (requires login)
  - Stock availability display
  - Quantity selector

### 2. Client Authentication
- **client_signup.php** - New account registration
  - Required fields: name, email, password
  - Optional: phone, address
  - Email validation and password strength check
  - Auto-login after registration
  
- **client_signin.php** - Client login
  - Secure password verification (password_hash/password_verify)
  - Redirect to intended page after login
  - Guest browsing option

### 3. Shopping Cart System
- **cart.php** - Shopping cart management
  - View all cart items
  - Update quantities
  - Remove items
  - Cart total calculation
  - Proceed to checkout button

### 4. Checkout & Payment Flow
- **checkout.php** - Order checkout page
  - Delivery address input (prefilled from profile)
  - Payment method selection:
    - Credit/Debit Card
    - Cash on Delivery
  - Order summary with itemized costs

- **payment_card.php** - Card payment page (if card selected)
  - Card number, name, expiry, CVV input
  - Real-time card preview animation
  - Input validation and formatting
  - SSL encryption notice

- **process_order.php** - Backend order processing
  - Create order in database
  - Update product stock
  - Link products to order
  - Handle payment method logic

- **order_success.php** - Order confirmation
  - Order number display
  - Order summary
  - Delivery information
  - Next steps guide

### 5. Client Profile & Dashboard
- **client_profile.php** - Client account management
  - **Profile Tab:**
    - Edit personal information
    - Upload profile picture
    - Update contact details
  
  - **Orders Tab:**
    - View complete order history
    - Order status tracking
    - Order details with totals
  
  - **Security Tab:**
    - Change password
    - Current password verification

### 6. Database Enhancements
**Migration: migrations/add_client_auth.sql**
- Added `mdp` (password) column to `client` table
- Created `panier` (cart) table for persistent carts
- Extended `commande` table with:
  - `adr_livraison` (delivery address)
  - `mode_paiement` (payment method: card/on_arrival)
  - `statut` (order status: pending/confirmed/shipped/delivered/cancelled)
  - `total_amount` (order total)

### 7. Backend Methods Added to Dao.php
- `clientByEmail($email)` - Get client by email for authentication
- `registerClient(...)` - Register new client with hashed password
- `updateClientProfile(...)` - Update client information
- `updateClientPassword(...)` - Change client password
- `getClientOrders($id_client)` - Get order history
- `getOrderDetails($num_com, $id_client)` - Get specific order details
- `createClientOrder(...)` - Create new order with payment/delivery info
- Cart management: `addToDbCart`, `getCartItems`, `clearCart`, `removeFromCart`, `updateCartQuantity`

### 8. Backend Methods Added to Client.php
- `estClient($email, $mdp)` - Authenticate client
- `register(...)` - Register new client (checks email uniqueness)
- `updateProfile(...)` - Update profile information
- `changePassword(...)` - Update password with hash
- `getOrders($id_client)` - Get client order history
- `getOrderDetails(...)` - Get order details

## User Flows

### Visitor (Guest) Flow
1. Visit `shop.php` (no login required)
2. Browse products by category/brand/search
3. Click product to view `product_detail.php`
4. Click "Add to Cart" → redirected to `client_signin.php`

### New Customer Flow
1. Click "Sign Up" → `client_signup.php`
2. Fill registration form (name, email, password, optional phone/address)
3. Auto-login and redirect to shop
4. Browse and add products to cart
5. Go to `cart.php` → review items
6. Click "Proceed to Checkout" → `checkout.php`
7. Enter delivery address and select payment method
8. **If Card:** Fill card details on `payment_card.php`
9. **If Cash on Delivery:** Skip to order processing
10. Order processed via `process_order.php`
11. Confirmation shown on `order_success.php`
12. View orders in `client_profile.php?tab=orders`

### Returning Customer Flow
1. Sign in via `client_signin.php`
2. Browse products, add to cart
3. Complete checkout
4. View past orders in profile
5. Edit profile information
6. Change password if needed

## Security Features
- Password hashing using PHP's `password_hash()` with `PASSWORD_DEFAULT`
- Password verification with `password_verify()`
- Session-based authentication
- Input validation and sanitization
- SQL injection protection (prepared statements)
- CSRF protection recommended for production (add tokens to forms)

## File Structure
```
gestion-stock-template/
├── shop.php                    # Public product catalog
├── product_detail.php          # Single product view
├── client_signin.php           # Client login
├── client_signup.php           # Client registration
├── client_logout.php           # Logout handler
├── cart.php                    # Shopping cart
├── checkout.php                # Checkout page
├── payment_card.php            # Card payment form
├── process_order.php           # Order processing backend
├── order_success.php           # Order confirmation
├── client_profile.php          # Client dashboard & profile
└── order_details.php           # (To be created - order detail view)

php/Class/
├── Dao.php                     # Extended with client/cart/order methods
├── Client.php                  # Extended with auth & profile methods
└── (other existing classes)

migrations/
└── add_client_auth.sql         # Database migration script

image/client/                   # Client profile pictures directory
```

## Design Consistency
- All pages use the same Bootstrap-based design as admin area
- Color scheme: Purple gradient header (#667eea to #764ba2)
- Responsive layout (mobile-friendly)
- Font Awesome icons throughout
- Consistent button styles and form elements

## Testing the System

### 1. Database Migration
```bash
mysql -u root -p'Abdou_pass0' gestion_des_stocks < migrations/add_client_auth.sql
```

### 2. Start PHP Development Server
```bash
cd /home/abdou/Downloads/stock-management-main/gestion-stock-template
php -S localhost:8000
```

### 3. Test Flow
1. Visit: http://localhost:8000/shop.php
2. Browse products (no login needed)
3. Click "Sign Up" and create an account
4. Add products to cart
5. Complete checkout with card or cash on delivery
6. View confirmation and order in profile

### 4. Test Credentials
Create a test client account through the signup page or use existing admins.

## Production Recommendations

### Security Enhancements
1. **Add CSRF Protection:**
   - Generate tokens for forms
   - Validate tokens on submission

2. **HTTPS Only:**
   - Force HTTPS in production
   - Use secure cookies

3. **Payment Gateway Integration:**
   - Replace simulated card processing with real gateway (Stripe, PayPal, etc.)
   - Never store full card numbers
   - Comply with PCI-DSS

4. **Rate Limiting:**
   - Limit login attempts
   - Prevent brute force attacks

5. **Email Verification:**
   - Send verification email on registration
   - Confirm email before allowing orders

### Feature Enhancements
1. **Order Tracking:**
   - Real-time status updates
   - Email notifications

2. **Reviews & Ratings:**
   - Allow clients to review purchased products

3. **Wishlist:**
   - Save products for later

4. **Coupons & Discounts:**
   - Apply promo codes at checkout

5. **Multiple Addresses:**
   - Save multiple delivery addresses

6. **Invoice Generation:**
   - PDF invoices for orders

## Current Limitations
- Card payment is simulated (not connected to real gateway)
- No email notifications
- No order tracking updates
- No admin panel for order management (uses existing admin system)
- Image uploads use local storage (recommend cloud storage for production)

## Next Steps
If you need any of these features:
- Order detail view page (`order_details.php`)
- Admin order management interface
- Email notification system
- Payment gateway integration
- Additional features from Production Recommendations

Let me know and I'll implement them!
