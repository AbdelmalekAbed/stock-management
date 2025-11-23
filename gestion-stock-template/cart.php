<?php
session_start();
require_once(__DIR__ . '/app_config.php');

// Require client login
if (!isset($_SESSION['client'])) {
    header('Location: signin_client.php?message=login_required');
    exit();
}

require_once(__DIR__ . "/../php/Class/Product.php");

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $index => $qty) {
        $qty = intval($qty);
        if ($qty <= 0) {
            unset($_SESSION['cart'][$index]);
        } else {
            $_SESSION['cart'][$index]['quantity'] = $qty;
        }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
    $success = "Cart updated successfully!";
}

if (isset($_GET['remove'])) {
    $index = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
        $success = "Item removed from cart!";
    }
}

// Calculate totals
$cartTotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartTotal += $item['prix_uni'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0" />
    <title>Shopping Cart - <?= htmlspecialchars(platform_name()) ?></title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png" />
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css" />
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <style>
        .shop-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .cart-item {
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .quantity-input {
            width: 80px;
        }
        .cart-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            position: sticky;
            top: 20px;
        }
    </style>
</head>
<body>
    <!-- Shop Header -->
    <div class="shop-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><i class="fas fa-shopping-cart"></i> Shopping Cart</h3>
                </div>
                <div>
                    <a href="shop.php" class="btn btn-light btn-sm me-2">
                        <i class="fas fa-store"></i> Continue Shopping
                    </a>
                    <a href="client_profile.php" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-5x text-muted mb-3"></i>
                <h3>Your cart is empty</h3>
                <p class="text-muted">Add some products to your cart to get started!</p>
                <a href="shop.php" class="btn btn-primary">
                    <i class="fas fa-store"></i> Browse Products
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <h4 class="mb-3">Cart Items (<?= count($_SESSION['cart']) ?>)</h4>
                    
                    <form method="POST" action="">
                        <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                            <div class="cart-item">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <img src="<?= htmlspecialchars($item['pr_image']) ?>" 
                                             alt="<?= htmlspecialchars($item['lib_pr']) ?>"
                                             onerror="this.src='assets/img/product/noimage.png'">
                                    </div>
                                    <div class="col-md-4">
                                        <h6><?= htmlspecialchars($item['lib_pr']) ?></h6>
                                        <p class="text-muted mb-0"><?= format_price($item['prix_uni']) ?> each</p>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" class="form-control quantity-input" 
                                               name="quantity[<?= $index ?>]" 
                                               value="<?= $item['quantity'] ?>" 
                                               min="1">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Subtotal</label>
                                        <p class="fw-bold mb-0"><?= format_price($item['prix_uni'] * $item['quantity']) ?></p>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <a href="cart.php?remove=<?= $index ?>" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Remove
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="d-flex justify-content-between mt-3">
                            <a href="shop.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Continue Shopping
                            </a>
                            <button type="submit" name="update_cart" class="btn btn-primary">
                                <i class="fas fa-sync"></i> Update Cart
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Cart Summary -->
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h5 class="mb-3">Order Summary</h5>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span><?= format_price($cartTotal) ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span class="text-success">FREE</span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong class="text-primary" style="font-size: 24px;"><?= format_price($cartTotal) ?></strong>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-success w-100 btn-lg">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </a>
                        
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt"></i> Secure checkout
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
