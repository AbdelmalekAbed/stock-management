<?php
session_start();
require_once(__DIR__ . '/app_config.php');

// Require client login and checkout data
if (!isset($_SESSION['client']) || !isset($_SESSION['checkout_data'])) {
    header('Location: checkout.php');
    exit();
}

$client = $_SESSION['client'];
$checkoutData = $_SESSION['checkout_data'];

// Handle payment submission
if (isset($_POST['process_payment'])) {
    $card_number = preg_replace('/\s+/', '', $_POST['card_number']);
    $card_name = trim($_POST['card_name']);
    $expiry_date = trim($_POST['expiry_date']);
    $cvv = trim($_POST['cvv']);
    
    $errors = [];
    
    // Validate card (basic validation - in production use a payment gateway)
    if (strlen($card_number) != 16 || !ctype_digit($card_number)) {
        $errors[] = "Invalid card number.";
    }
    if (empty($card_name)) {
        $errors[] = "Cardholder name is required.";
    }
    if (!preg_match('/^\d{2}\/\d{2}$/', $expiry_date)) {
        $errors[] = "Invalid expiry date format (MM/YY).";
    }
    if (strlen($cvv) < 3 || strlen($cvv) > 4 || !ctype_digit($cvv)) {
        $errors[] = "Invalid CVV.";
    }
    
    if (empty($errors)) {
        // Store payment info in session (in production, send to payment gateway)
        $_SESSION['checkout_data']['card_last4'] = substr($card_number, -4);
        $_SESSION['checkout_data']['payment_status'] = 'completed';
        
        // Redirect to process order
        header('Location: process_order.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0" />
    <title>Card Payment - <?= htmlspecialchars(platform_name()) ?></title>

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
        .payment-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .card-chip {
            width: 50px;
            height: 40px;
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .card-number {
            font-size: 24px;
            letter-spacing: 4px;
            margin-bottom: 20px;
            font-family: 'Courier New', monospace;
        }
        .card-details {
            display: flex;
            justify-content: space-between;
        }
        .payment-form {
            background: white;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 30px;
        }
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            text-align: center;
            flex: 1;
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
        .step.completed .step-number {
            background: #28a745;
            color: white;
        }
        .step.active .step-number {
            background: #667eea;
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
                    <h3 class="mb-0"><i class="fas fa-credit-card"></i> Card Payment</h3>
                </div>
                <div>
                    <a href="checkout.php" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Checkout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="step completed">
                <div class="step-number"><i class="fas fa-check"></i></div>
                <div>Cart</div>
            </div>
            <div class="step completed">
                <div class="step-number"><i class="fas fa-check"></i></div>
                <div>Checkout</div>
            </div>
            <div class="step active">
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
            <div class="col-lg-7">
                <!-- Card Preview -->
                <div class="payment-card">
                    <div class="card-chip"></div>
                    <div class="card-number" id="cardPreview">•••• •••• •••• ••••</div>
                    <div class="card-details">
                        <div>
                            <small class="text-white-50">CARDHOLDER NAME</small>
                            <div id="namePreview">YOUR NAME</div>
                        </div>
                        <div>
                            <small class="text-white-50">EXPIRES</small>
                            <div id="expiryPreview">MM/YY</div>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <div class="payment-form">
                    <h5 class="mb-4"><i class="fas fa-lock"></i> Enter Card Details</h5>
                    
                    <form method="POST" action="" id="paymentForm">
                        <div class="mb-3">
                            <label class="form-label">Card Number *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-credit-card"></i></span>
                                <input type="text" class="form-control" name="card_number" id="cardNumber"
                                       placeholder="1234 5678 9012 3456" maxlength="19" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cardholder Name *</label>
                            <input type="text" class="form-control" name="card_name" id="cardName"
                                   placeholder="John Doe" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Expiry Date *</label>
                                    <input type="text" class="form-control" name="expiry_date" id="expiryDate"
                                           placeholder="MM/YY" maxlength="5" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">CVV *</label>
                                    <input type="text" class="form-control" name="cvv" id="cvv"
                                           placeholder="123" maxlength="4" required>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Your payment information is encrypted and secure.
                        </div>

                        <button type="submit" name="process_payment" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-lock"></i> Pay <?= format_price($checkoutData['total']) ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Order Summary</h5>
                        
                        <div class="mb-3">
                            <strong>Delivery Address:</strong>
                            <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($checkoutData['delivery_address'])) ?></p>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <?php foreach ($_SESSION['cart'] as $item): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <small><?= htmlspecialchars($item['lib_pr']) ?> (×<?= $item['quantity'] ?>)</small>
                                    <small class="fw-bold"><?= format_price($item['prix_uni'] * $item['quantity']) ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total Amount:</strong>
                            <strong class="text-success" style="font-size: 24px;">
                                <?= format_price($checkoutData['total']) ?>
                            </strong>
                        </div>

                        <div class="text-center">
                            <i class="fas fa-shield-alt text-success"></i>
                            <small class="text-muted d-block">256-bit SSL Encryption</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Card number formatting and preview
        document.getElementById('cardNumber').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
            
            // Update preview
            if (value.length > 0) {
                let preview = value.replace(/\d(?=\d{4})/g, '•');
                preview = preview.match(/.{1,4}/g)?.join(' ') || preview;
                document.getElementById('cardPreview').textContent = preview;
            } else {
                document.getElementById('cardPreview').textContent = '•••• •••• •••• ••••';
            }
        });

        // Cardholder name preview
        document.getElementById('cardName').addEventListener('input', function(e) {
            document.getElementById('namePreview').textContent = e.target.value.toUpperCase() || 'YOUR NAME';
        });

        // Expiry date formatting and preview
        document.getElementById('expiryDate').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            e.target.value = value;
            document.getElementById('expiryPreview').textContent = value || 'MM/YY';
        });

        // CVV input - only digits
        document.getElementById('cvv').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    </script>
</body>
</html>
