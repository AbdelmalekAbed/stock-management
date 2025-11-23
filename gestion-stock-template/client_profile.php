<?php
session_start();
require_once(__DIR__ . '/app_config.php');

// Require client login
if (!isset($_SESSION['client'])) {
    header('Location: signin_client.php');
    exit();
}

require_once(__DIR__ . "/../php/Class/Client.php");
require_once(__DIR__ . "/../php/Class/Dao.php");

$client = $_SESSION['client'];
$activeTab = $_GET['tab'] ?? 'profile';

// Handle profile update
if (isset($_POST['update_profile'])) {
    extract($_POST);
    
    $image = $client['image']; // Keep existing image by default
    
    // Handle image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $uploadDirectory = __DIR__ . '/image/client/';

            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0755, true);
            }

            $newFilename = 'client_' . $client['id'] . '_' . time() . '.' . $ext;
            $uploadPath = $uploadDirectory . $newFilename;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                $image = './image/client/' . $newFilename;
            }
        }
    }
    
    Client::updateProfile($client['id'], $nom, $prenom, $adr, $tele, $email, $image);
    
    // Refresh client data in session
    $_SESSION['client'] = Dao::affciherPersonne($client['id'], 'client');
    $client = $_SESSION['client'];
    $successMessage = "Profile updated successfully!";
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    if (!password_verify($current_password, $client['mdp'])) {
        $errors[] = "Current password is incorrect.";
    }
    if (strlen($new_password) < 6) {
        $errors[] = "New password must be at least 6 characters.";
    }
    if ($new_password !== $confirm_password) {
        $errors[] = "New passwords do not match.";
    }
    
    if (empty($errors)) {
        Client::changePassword($client['id'], $new_password);
        $successMessage = "Password changed successfully!";
    }
}

// Get order history
$orders = Client::getOrders($client['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0" />
    <title>My Profile - <?= htmlspecialchars(platform_name()) ?></title>

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
        .profile-card {
            background: white;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
        }
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #667eea;
            margin-bottom: 15px;
        }
        .order-card {
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .order-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-shipped { background: #cce5ff; color: #004085; }
        .status-delivered { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <!-- Shop Header -->
    <div class="shop-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><i class="fas fa-user"></i> My Profile</h3>
                </div>
                <div>
                    <a href="shop.php" class="btn btn-light btn-sm me-2">
                        <i class="fas fa-store"></i> Shop
                    </a>
                    <a href="cart.php" class="btn btn-light btn-sm me-2">
                        <i class="fas fa-shopping-cart"></i> Cart
                    </a>
                    <a href="client_logout.php" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?= $successMessage ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="profile-card text-center">
                    <img src="<?= htmlspecialchars($client['image']) ?>" 
                         alt="Profile" 
                         class="profile-avatar"
                         onerror="this.src='assets/img/profiles/avatar-default.jpg'">
                    <h5 class="mb-1"><?= htmlspecialchars($client['nom'] . ' ' . $client['prenom']) ?></h5>
                    <p class="text-muted small mb-3"><?= htmlspecialchars($client['email']) ?></p>
                    
                    <div class="list-group">
                        <a href="?tab=profile" class="list-group-item list-group-item-action <?= $activeTab === 'profile' ? 'active' : '' ?>">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                        <a href="?tab=orders" class="list-group-item list-group-item-action <?= $activeTab === 'orders' ? 'active' : '' ?>">
                            <i class="fas fa-shopping-bag"></i> My Orders
                        </a>
                        <a href="?tab=security" class="list-group-item list-group-item-action <?= $activeTab === 'security' ? 'active' : '' ?>">
                            <i class="fas fa-lock"></i> Security
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <?php if ($activeTab === 'profile'): ?>
                    <!-- Profile Tab -->
                    <div class="profile-card">
                        <h5 class="mb-4"><i class="fas fa-user-edit"></i> Edit Profile</h5>
                        
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">First Name *</label>
                                        <input type="text" class="form-control" name="nom" 
                                               value="<?= htmlspecialchars($client['nom']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Last Name *</label>
                                        <input type="text" class="form-control" name="prenom" 
                                               value="<?= htmlspecialchars($client['prenom']) ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?= htmlspecialchars($client['email']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="tele" 
                                       value="<?= htmlspecialchars($client['tele']) ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="adr" rows="3"><?= htmlspecialchars($client['adr']) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Profile Picture</label>
                                <input type="file" class="form-control" name="profile_image" accept="image/*">
                                <small class="text-muted">Accepted formats: JPG, PNG, GIF</small>
                            </div>

                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </form>
                    </div>

                <?php elseif ($activeTab === 'orders'): ?>
                    <!-- Orders Tab -->
                    <div class="profile-card">
                        <h5 class="mb-4"><i class="fas fa-shopping-bag"></i> Order History</h5>
                        
                        <?php if (empty($orders)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                                <h5>No orders yet</h5>
                                <p class="text-muted">Start shopping to see your orders here!</p>
                                <a href="shop.php" class="btn btn-primary">
                                    <i class="fas fa-store"></i> Browse Products
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <div class="order-card">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <strong>Order #<?= htmlspecialchars($order['num_com']) ?></strong>
                                            <p class="text-muted small mb-0"><?= date('M d, Y', strtotime($order['date_com'])) ?></p>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="status-badge status-<?= $order['statut'] ?? 'pending' ?>">
                                                <?= ucfirst($order['statut'] ?? 'pending') ?>
                                            </span>
                                        </div>
                                        <div class="col-md-3">
                                            <i class="fas fa-credit-card text-muted"></i>
                                            <?= $order['mode_paiement'] === 'card' ? 'Card' : 'Cash on Delivery' ?>
                                        </div>
                                        <div class="col-md-2">
                                            <strong class="text-success"><?= format_price($order['total']) ?></strong>
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <a href="order_details.php?num_com=<?= $order['num_com'] ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                <?php elseif ($activeTab === 'security'): ?>
                    <!-- Security Tab -->
                    <div class="profile-card">
                        <h5 class="mb-4"><i class="fas fa-lock"></i> Change Password</h5>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Current Password *</label>
                                <input type="password" class="form-control" name="current_password" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">New Password *</label>
                                <input type="password" class="form-control" name="new_password" 
                                       minlength="6" required>
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirm New Password *</label>
                                <input type="password" class="form-control" name="confirm_password" 
                                       minlength="6" required>
                            </div>

                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
