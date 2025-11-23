<?php
session_start();

// Require client login, cart, and checkout data
if (!isset($_SESSION['client']) || !isset($_SESSION['cart']) || 
    empty($_SESSION['cart']) || !isset($_SESSION['checkout_data'])) {
    header('Location: shop.php');
    exit();
}

require_once(__DIR__ . "/../php/Class/Dao.php");
require_once(__DIR__ . "/../php/Class/Product.php");
require_once(__DIR__ . "/../php/Class/Sale.php");

$client = $_SESSION['client'];
$checkoutData = $_SESSION['checkout_data'];
$cart = $_SESSION['cart'];

try {
    // Create order
    $num_com = Dao::createClientOrder(
        $client['id'],
        $checkoutData['delivery_address'],
        $checkoutData['payment_method'],
        $checkoutData['total']
    );

    // Add products to order and update stock
    foreach ($cart as $item) {
        // Add product to order
        Dao::prSale($item['num_pr'], $num_com, $item['quantity'], $item['prix_uni']);
        
        // Decrease stock
        Dao::deleteQty($item['num_pr'], $item['quantity']);
    }

    // Store order number in session for confirmation page
    $_SESSION['order_completed'] = [
        'num_com' => $num_com,
        'total' => $checkoutData['total'],
        'payment_method' => $checkoutData['payment_method'],
        'delivery_address' => $checkoutData['delivery_address'],
        'items' => $cart
    ];

    // Clear cart and checkout data
    unset($_SESSION['cart']);
    unset($_SESSION['checkout_data']);

    // Redirect to success page
    header('Location: order_success.php');
    exit();

} catch (Exception $e) {
    // Log error and redirect with error message
    $_SESSION['order_error'] = "An error occurred while processing your order. Please try again.";
    header('Location: checkout.php');
    exit();
}
