<?php
session_start();
require_once(__DIR__ . '/app_config.php');

// Require client login
if (!isset($_SESSION['client'])) {
    header('Location: signin_client.php?message=login_required');
    exit();
}

// Check cart is not empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: shop.php');
    exit();
}

require_once(__DIR__ . "/../php/Class/Product.php");

$client = $_SESSION['client'];

// Calculate cart total
$cartTotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartTotal += $item['prix_uni'] * $item['quantity'];
}

// Handle checkout submission
if (isset($_POST['place_order'])) {
    $delivery_address = trim($_POST['delivery_address']);
    $payment_method = $_POST['payment_method'];
    
    $errors = [];
    
    if (empty($delivery_address)) {
        $errors[] = "Delivery address is required.";
    }
    
    if (!in_array($payment_method, ['card', 'on_arrival'])) {
        $errors[] = "Invalid payment method.";
    }
    
    if (empty($errors)) {
        // Store checkout data in session for next step
        $_SESSION['checkout_data'] = [
            'delivery_address' => $delivery_address,
            'payment_method' => $payment_method,
            'total' => $cartTotal
        ];
        
        // Redirect based on payment method
        if ($payment_method === 'card') {
            header('Location: payment_card.php');
        } else {
            header('Location: process_order.php');
        }
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0" />
    <title>Checkout - <?= htmlspecialchars(platform_name()) ?></title>

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
        .checkout-section {
            background: white;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
        }
        .payment-option {
            border: 2px solid #e5e5e5;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-option:hover {
            border-color: #667eea;
        }
        .payment-option.selected {
            border-color: #667eea;
            background-color: #f0f4ff;
        }
        .payment-option input[type="radio"] {
            margin-right: 10px;
        }
        .order-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            position: sticky;
            top: 20px;
        }
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            text-align: center;
            flex: 1;
            position: relative;
        }
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e5e5e5;
            color: #666;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .step.active .step-number {
            background: #667eea;
            color: white;
        }
        .step.completed .step-number {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Shop Header -->
    <div class="shop-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><i class="fas fa-shopping-bag"></i> Checkout</h3>
                </div>
                <div>
                    <a href="cart.php" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Cart
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="step completed">
                <div class="step-number">
                    <i class="fas fa-check"></i>
                </div>
                <div>Cart</div>
            </div>
            <div class="step active">
                <div class="step-number">2</div>
                <div>Checkout</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div>Payment</div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div>Confirmation</div>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Checkout Form -->
            <div class="col-lg-8">
                <form method="POST" action="" id="checkoutForm">
                    <!-- Delivery Information -->
                    <div class="checkout-section">
                        <h5 class="mb-3"><i class="fas fa-truck"></i> Delivery Information</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Customer Name</label>
                            <input type="text" class="form-control" 
                                   value="<?= htmlspecialchars($client['nom'] . ' ' . $client['prenom']) ?>" 
                                   readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" 
                                   value="<?= htmlspecialchars($client['email']) ?>" 
                                   readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" 
                                   value="<?= htmlspecialchars($client['tele']) ?>" 
                                   readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Delivery Address *</label>
                            <textarea class="form-control" name="delivery_address" rows="3" 
                                      placeholder="Enter your full delivery address" required><?= htmlspecialchars($client['adr'] ?? '') ?></textarea>
                            <small class="text-muted">Please provide a complete address including street, city, and postal code.</small>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="checkout-section">
                        <h5 class="mb-3"><i class="fas fa-credit-card"></i> Payment Method</h5>
                        
                        <div class="payment-option" onclick="selectPayment('card')">
                            <input type="radio" name="payment_method" value="card" id="payment_card" required>
                            <label for="payment_card" class="mb-0">
                                <strong><i class="fas fa-credit-card"></i> Credit/Debit Card</strong>
                                <p class="text-muted mb-0 mt-2">Pay securely with your credit or debit card</p>
                            </label>
                        </div>

                        <div class="payment-option" onclick="selectPayment('on_arrival')">
                            <input type="radio" name="payment_method" value="on_arrival" id="payment_arrival" required>
                            <label for="payment_arrival" class="mb-0">
                                <strong><i class="fas fa-money-bill-wave"></i> Cash on Delivery</strong>
                                <p class="text-muted mb-0 mt-2">Pay when you receive your order</p>
                            </label>
                        </div>
                    </div>

                    <button type="submit" name="place_order" class="btn btn-success btn-lg w-100">
                        <i class="fas fa-arrow-right"></i> Continue to Payment
                    </button>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="order-summary">
                    <h5 class="mb-3">Order Summary</h5>
                    
                    <div class="mb-3">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <small><?= htmlspecialchars($item['lib_pr']) ?></small>
                                    <br>
                                    <small class="text-muted">Qty: <?= $item['quantity'] ?> × <?= format_price($item['prix_uni']) ?></small>
                                </div>
                                <small class="fw-bold"><?= format_price($item['prix_uni'] * $item['quantity']) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span><?= format_price($cartTotal) ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Delivery:</span>
                        <span class="text-success">FREE</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong class="text-primary" style="font-size: 24px;"><?= format_price($cartTotal) ?></strong>
                    </div>
                    
                    <div class="text-center">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt"></i> Secure and encrypted transaction
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectPayment(method) {
            // Remove all selected classes
            document.querySelectorAll('.payment-option').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Select the radio button
            const radio = document.getElementById('payment_' + method);
            radio.checked = true;
            
            // Add selected class to parent
            radio.closest('.payment-option').classList.add('selected');
        }

        // Auto-select if clicked
        document.querySelectorAll('.payment-option input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                selectPayment(this.value);
            });
        });
    </script>
</body>
</html>
