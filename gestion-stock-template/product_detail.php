<?php
// Product detail page - accessible without login
session_start();
require_once(__DIR__ . '/app_config.php');
require_once(__DIR__ . "/../php/Class/Product.php");

if (!isset($_GET['num_pr'])) {
    header('Location: shop.php');
    exit();
}

$num_pr = $_GET['num_pr'];
$product = Product::displayPr($num_pr);

if (!$product) {
    header('Location: shop.php');
    exit();
}

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    // Check if user is logged in
    if (!isset($_SESSION['client'])) {
        $_SESSION['redirect_after_login'] = "product_detail.php?num_pr=" . $num_pr;
    header('Location: signin_client.php?message=login_required');
        exit();
    }

    $quantity = intval($_POST['quantity'] ?? 1);
    
    // Check stock availability
    if ($quantity > $product['qte_stock']) {
        $error = "Only {$product['qte_stock']} items available in stock.";
    } else {
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Add or update cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['num_pr'] == $num_pr) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $_SESSION['cart'][] = [
                'num_pr' => $num_pr,
                'lib_pr' => $product['lib_pr'],
                'prix_uni' => $product['prix_uni'],
                'pr_image' => $product['pr_image'],
                'quantity' => $quantity
            ];
        }

        $success = "Product added to cart successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0" />
    <title><?= htmlspecialchars($product['lib_pr']) ?> - <?= htmlspecialchars(platform_name()) ?></title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png" />
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/animate.css" />
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css" />
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <style>
        .product-image-main {
            width: 100%;
            height: 500px;
            object-fit: contain;
            border-radius: 10px;
            border: 1px solid #e5e5e5;
        }
        .price-tag {
            font-size: 32px;
            font-weight: bold;
            color: #ff9f43;
        }
        .stock-info {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .in-stock {
            background-color: #d4edda;
            color: #155724;
        }
        .low-stock {
            background-color: #fff3cd;
            color: #856404;
        }
        .out-of-stock {
            background-color: #f8d7da;
            color: #721c24;
        }
        .shop-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .quantity-input {
            width: 180px;
            border-radius: 50px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .quantity-input .btn {
            font-size: 1.25rem;
            padding: 0.65rem 1rem;
            border: none;
        }
        .quantity-input input {
            font-size: 1.3rem;
            height: 58px;
            border: none;
        }
        .quantity-input input:focus {
            box-shadow: none;
        }
    </style>
</head>
<body>
    <!-- Shop Header -->
    <div class="shop-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="shop.php" class="text-white text-decoration-none">
                        <i class="fas fa-arrow-left me-2"></i> Back to Shop
                    </a>
                </div>
                <div>
                    <h4 class="mb-0"><?= htmlspecialchars(platform_name()) ?></h4>
                </div>
                <div>
                    <?php if (isset($_SESSION['client'])): ?>
                        <a href="cart.php" class="btn btn-light btn-sm me-2">
                            <i class="fas fa-shopping-cart"></i> Cart
                            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                                <span class="badge bg-warning"><?= array_sum(array_column($_SESSION['cart'], 'quantity')) ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="client_profile.php" class="btn btn-light btn-sm">
                            <i class="fas fa-user"></i> Profile
                        </a>
                    <?php else: ?>
                        <a href="signin_client.php" class="btn btn-light btn-sm me-2">Sign In</a>
                        <a href="client_signup.php" class="btn btn-outline-light btn-sm">Sign Up</a>
                    <?php endif; ?>
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

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Product Image -->
            <div class="col-md-6 mb-4">
                <img src="<?= htmlspecialchars($product['pr_image']) ?>" 
                     alt="<?= htmlspecialchars($product['lib_pr']) ?>"
                     class="product-image-main"
                     onerror="this.src='assets/img/product/noimage.png'">
            </div>

            <!-- Product Info -->
            <div class="col-md-6">
                <h2 class="mb-3"><?= htmlspecialchars($product['lib_pr']) ?></h2>
                
                <div class="mb-3">
                    <span class="badge bg-primary me-2">
                        <i class="fas fa-tag"></i> <?= htmlspecialchars($product['lib_cat']) ?>
                    </span>
                    <span class="badge bg-secondary">
                        <i class="fas fa-industry"></i> <?= htmlspecialchars($product['nom_marque']) ?>
                    </span>
                </div>

                <!-- Price -->
                <div class="mb-4">
                    <span class="price-tag"><?= format_price($product['prix_uni']) ?></span>
                </div>

                <!-- Stock Status -->
                <div class="stock-info <?= $product['qte_stock'] == 0 ? 'out-of-stock' : ($product['qte_stock'] <= 10 ? 'low-stock' : 'in-stock') ?>">
                    <i class="fas fa-box"></i>
                    <?php if ($product['qte_stock'] == 0): ?>
                        <strong>Out of Stock</strong>
                    <?php elseif ($product['qte_stock'] <= 10): ?>
                        <strong>Only <?= $product['qte_stock'] ?> left in stock!</strong>
                    <?php else: ?>
                        <strong>In Stock</strong> (<?= $product['qte_stock'] ?> available)
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <h5>Description</h5>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($product['desc_pr'])) ?></p>
                </div>

                <!-- Add to Cart Form -->
                <?php if ($product['qte_stock'] > 0): ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <div class="input-group quantity-input">
                                <button class="btn btn-outline-secondary" type="button" id="decreaseQty">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="form-control text-center" name="quantity" 
                                       id="quantity" value="1" min="1" max="<?= $product['qte_stock'] ?>">
                                <button class="btn btn-outline-secondary" type="button" id="increaseQty">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg w-100" disabled>
                        <i class="fas fa-ban"></i> Out of Stock
                    </button>
                <?php endif; ?>

                <!-- Additional Info -->
                <div class="mt-4 p-3 bg-light rounded">
                    <div class="row text-center">
                        <div class="col-4">
                            <i class="fas fa-truck fa-2x text-primary mb-2"></i>
                            <p class="small mb-0">Free Delivery</p>
                        </div>
                        <div class="col-4">
                            <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                            <p class="small mb-0">Secure Payment</p>
                        </div>
                        <div class="col-4">
                            <i class="fas fa-undo fa-2x text-warning mb-2"></i>
                            <p class="small mb-0">Easy Returns</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Quantity controls
        document.getElementById('decreaseQty').addEventListener('click', function() {
            let qty = document.getElementById('quantity');
            if (qty.value > 1) {
                qty.value = parseInt(qty.value) - 1;
            }
        });

        document.getElementById('increaseQty').addEventListener('click', function() {
            let qty = document.getElementById('quantity');
            let max = parseInt(qty.getAttribute('max'));
            if (qty.value < max) {
                qty.value = parseInt(qty.value) + 1;
            }
        });
    </script>
</body>
</html>
