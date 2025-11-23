<?php
session_start();
require_once(__DIR__ . '/app_config.php');

// Require client login and order completion data
if (!isset($_SESSION['client']) || !isset($_SESSION['order_completed'])) {
    header('Location: shop.php');
    exit();
}

$client = $_SESSION['client'];
$orderData = $_SESSION['order_completed'];

// Clear order completed data after displaying (prevent refresh duplicates)
$displayData = $orderData;
unset($_SESSION['order_completed']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0" />
    <title>Order Confirmation - <?= htmlspecialchars(platform_name()) ?></title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png" />
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css" />
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <style>
        .shop-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        .success-icon {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: scaleIn 0.5s ease-in-out;
        }
        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }
        .order-details-box {
            background: white;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <!-- Success Header -->
    <div class="shop-header text-center">
        <div class="container">
            <div class="success-icon">
                <i class="fas fa-check fa-3x text-success"></i>
            </div>
            <h2 class="mb-2">Order Placed Successfully!</h2>
            <p class="mb-0">Thank you for your purchase, <?= htmlspecialchars($client['nom']) ?>!</p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Order Information -->
                <div class="order-details-box">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="mb-1">Order Number</h5>
                            <h3 class="text-primary mb-0"><?= htmlspecialchars($displayData['num_com']) ?></h3>
                        </div>
                        <div>
                            <span class="status-badge status-pending">
                                <i class="fas fa-clock"></i> Pending
                            </span>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6><i class="fas fa-truck"></i> Delivery Address</h6>
                            <p class="text-muted"><?= nl2br(htmlspecialchars($displayData['delivery_address'])) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-credit-card"></i> Payment Method</h6>
                            <p class="text-muted">
                                <?php if ($displayData['payment_method'] === 'card'): ?>
                                    <i class="fas fa-credit-card"></i> Credit/Debit Card
                                    <?php if (isset($_SESSION['checkout_data']['card_last4'])): ?>
                                        (****<?= $_SESSION['checkout_data']['card_last4'] ?>)
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class="fas fa-money-bill-wave"></i> Cash on Delivery
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <h6 class="mb-3">Order Items</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($displayData['items'] as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['lib_pr']) ?></td>
                                        <td><?= format_price($item['prix_uni']) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td><?= format_price($item['prix_uni'] * $item['quantity']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong class="text-success" style="font-size: 20px;">
                                        <?= format_price($displayData['total']) ?>
                                    </strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- What's Next -->
                <div class="order-details-box">
                    <h5 class="mb-3"><i class="fas fa-info-circle"></i> What's Next?</h5>
                    <ul class="mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success"></i> 
                            You will receive an order confirmation email shortly.
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success"></i> 
                            We will prepare your order and notify you when it's shipped.
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success"></i> 
                            You can track your order status in your profile.
                        </li>
                        <?php if ($displayData['payment_method'] === 'on_arrival'): ?>
                            <li>
                                <i class="fas fa-check-circle text-success"></i> 
                                Please have the exact amount ready when the order arrives.
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="text-center mt-4">
                    <a href="client_profile.php" class="btn btn-primary btn-lg me-2">
                        <i class="fas fa-user"></i> View My Orders
                    </a>
                    <a href="shop.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
