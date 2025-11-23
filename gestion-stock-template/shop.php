<?php
// Public shop page - accessible without login
session_start();
require_once(__DIR__ . '/app_config.php');
require_once(__DIR__ . "/../php/Class/Product.php");
require_once(__DIR__ . "/../php/Class/Categorie.php");
require_once(__DIR__ . "/../php/Class/Marque.php");

// Get filter parameters
$searchTerm = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$brandFilter = $_GET['brand'] ?? '';

// Get all products
$products = Product::prJoinCatJoinMarque();
$categories = Categorie::afficher("categorie");
$brands = Marque::afficher("marque");

// Filter products
if ($searchTerm) {
    $products = array_filter($products, function($p) use ($searchTerm) {
        return stripos($p['lib_pr'], $searchTerm) !== false || 
               stripos($p['desc_pr'], $searchTerm) !== false;
    });
}
if ($categoryFilter) {
    $products = array_filter($products, function($p) use ($categoryFilter) {
        return $p['id_cat'] == $categoryFilter;
    });
}
if ($brandFilter) {
    $products = array_filter($products, function($p) use ($brandFilter) {
        return $p['id_marque'] == $brandFilter;
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0" />
    <meta name="description" content="<?= htmlspecialchars(platform_name()) ?> - Browse our products" />
    <title><?= htmlspecialchars(platform_name()) ?> - Shop</title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png" />
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/animate.css" />
    <link rel="stylesheet" href="assets/plugins/select2/css/select2.min.css" />
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css" />
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <style>
        .product-card {
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .product-img-wrapper {
            width: 100%;
            height: 200px;
            overflow: hidden;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .product-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .product-price {
            font-size: 24px;
            font-weight: bold;
            color: #ff9f43;
        }
        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
        }
        .in-stock {
            background-color: #28a745;
            color: white;
        }
        .low-stock {
            background-color: #ffc107;
            color: #333;
        }
        .out-of-stock {
            background-color: #dc3545;
            color: white;
        }
        .shop-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        .cart-icon {
            position: relative;
        }
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff9f43;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <!-- Shop Header -->
        <div class="shop-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1 class="mb-2"><?= htmlspecialchars(platform_name()) ?></h1>
                        <p class="mb-0">Discover our amazing products</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <?php if (isset($_SESSION['client'])): ?>
                            <a href="cart.php" class="btn btn-light me-2 cart-icon">
                                <i class="fas fa-shopping-cart"></i> Cart
                                <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                                    <span class="cart-count"><?= array_sum(array_column($_SESSION['cart'], 'quantity')) ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="client_profile.php" class="btn btn-light me-2">
                                <i class="fas fa-user"></i> Profile
                            </a>
                            <a href="client_logout.php" class="btn btn-outline-light">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        <?php else: ?>
                            <a href="signin_client.php" class="btn btn-light me-2">
                                <i class="fas fa-sign-in-alt"></i> Sign In
                            </a>
                            
                            <a href="client_signup.php" class="btn btn-outline-light">
                                <i class="fas fa-user-plus"></i> Sign Up
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container">
            <div class="row">
                <!-- Filters Sidebar -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Filters</h5>
                            <form method="GET" action="shop.php">
                                <!-- Search -->
                                <div class="mb-3">
                                    <label class="form-label">Search</label>
                                    <input type="text" class="form-control" name="search" 
                                           value="<?= htmlspecialchars($searchTerm) ?>" 
                                           placeholder="Search products...">
                                </div>

                                <!-- Category Filter -->
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" name="category">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['id_cat'] ?>" 
                                                    <?= $categoryFilter == $cat['id_cat'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat['lib_cat']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Brand Filter -->
                                <div class="mb-3">
                                    <label class="form-label">Brand</label>
                                    <select class="form-select" name="brand">
                                        <option value="">All Brands</option>
                                        <?php foreach ($brands as $brand): ?>
                                            <option value="<?= $brand['id_marque'] ?>" 
                                                    <?= $brandFilter == $brand['id_marque'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($brand['nom_marque']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                                <a href="shop.php" class="btn btn-secondary w-100 mt-2">Clear Filters</a>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="col-md-9">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4><?= count($products) ?> Products Found</h4>
                    </div>

                    <?php if (empty($products)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No products found matching your criteria.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($products as $product): ?>
                                <div class="col-md-4">
                                    <div class="product-card position-relative">
                                        <!-- Stock Badge -->
                                        <?php
                                        $stockClass = 'in-stock';
                                        $stockText = 'In Stock';
                                        if ($product['qte_stock'] == 0) {
                                            $stockClass = 'out-of-stock';
                                            $stockText = 'Out of Stock';
                                        } elseif ($product['qte_stock'] <= 10) {
                                            $stockClass = 'low-stock';
                                            $stockText = 'Low Stock';
                                        }
                                        ?>
                                        <span class="stock-badge <?= $stockClass ?>"><?= $stockText ?></span>

                                        <!-- Product Image -->
                                        <div class="product-img-wrapper">
                                            <img src="<?= htmlspecialchars($product['pr_image']) ?>" 
                                                 alt="<?= htmlspecialchars($product['lib_pr']) ?>"
                                                 onerror="this.src='assets/img/product/noimage.png'">
                                        </div>

                                        <!-- Product Info -->
                                        <h6 class="mb-2"><?= htmlspecialchars($product['lib_pr']) ?></h6>
                                        <p class="text-muted small mb-2">
                                            <?= htmlspecialchars($product['lib_cat']) ?> • 
                                            <?= htmlspecialchars($product['nom_marque']) ?>
                                        </p>
                                        <p class="text-muted small mb-3">
                                            <?= htmlspecialchars(substr($product['desc_pr'], 0, 60)) ?>...
                                        </p>

                                        <!-- Price and Actions -->
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="product-price"><?= format_price($product['prix_uni']) ?></span>
                                            <a href="product_detail.php?num_pr=<?= $product['num_pr'] ?>" 
                                               class="btn btn-sm btn-primary">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/feather.min.js"></script>
</body>
</html>
