<?php
session_start();
require_once(__DIR__ . '/app_config.php');
require_once(__DIR__ . "/../php/Class/Admin.php");
require_once(__DIR__ . "/../php/Class/Client.php");

// If already logged in as admin or client, send to respective home
if (isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit();
}
if (isset($_SESSION['client'])) {
    header('Location: shop.php');
    exit();
}

// Handle login
$errorCode = null;
if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $mdp = $_POST['mdp'] ?? '';

    // Attempt admin authentication first
    $adminAttempt = Admin::estAdmin($email, $mdp);
    if (is_array($adminAttempt) && isset($adminAttempt['email'])) {
        $_SESSION['admin'] = $adminAttempt;
        unset($_SESSION['client']);
        header('Location: index.php');
        exit();
    }

    if ($adminAttempt === FAUX_MDP) {
        $errorCode = 'password';
    } else {
        // Either admin email not found or constants not defined; proceed with client auth
        $clientAttempt = Client::estClient($email, $mdp);
        if (is_array($clientAttempt) && isset($clientAttempt['email'])) {
            $_SESSION['client'] = $clientAttempt;

            if (isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: $redirect");
            } else {
                header('Location: shop.php');
            }
            exit();
        }

        if ($clientAttempt === FAUX_MDP) {
            $errorCode = 'password';
        } elseif ($clientAttempt === FAUX_EMAIL && $adminAttempt === FAUX_EMAIL) {
            $errorCode = 'email';
        } else {
            // Fallback generic error
            $errorCode = $errorCode ?? 'email';
        }
    }
}

$loginMessage = $_GET['message'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Client Sign In - <?= htmlspecialchars(platform_name()) ?></title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="account-page">
    <div class="main-wrapper">
        <div class="account-content">
            <div class="login-wrapper">
                <div class="login-content">
                    <form class="login-userset" method="post" action="">
                        <div class="login-logo">
                            <img src="assets/img/logo.png" alt="img">
                        </div>
                        <div class="login-userheading">
                            <h3>Client Sign In</h3>
                            <h4>Welcome back! Please login to your account</h4>
                        </div>

                        <?php if ($loginMessage == 'login_required'): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Please sign in to add items to your cart.
                            </div>
                        <?php endif; ?>

                        <div class="form-login">
                            <label>Email</label>
                            <div class="form-addons">
                                <input type="email" placeholder="Enter your email address" name="email" required>
                                <img src="assets/img/icons/mail.svg" alt="img">
                                <?php if (isset($errorCode) && $errorCode === 'email'): ?>
                                    <p style="color:red; text-align: center">Invalid email</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-login">
                            <label>Password</label>
                            <div class="pass-group">
                                <input type="password" class="pass-input" placeholder="Enter your password" name="mdp" required>
                                <span class="fas toggle-password fa-eye-slash"></span>
                            </div>
                            <?php if (isset($errorCode) && $errorCode === 'password'): ?>
                                <p style="color:red; text-align: center">Incorrect password</p>
                            <?php endif; ?>
                        </div>

                        <div class="form-login">
                            <button class="btn btn-login" type="submit" name="login">Sign In</button>
                        </div>

                        <div class="signinform text-center">
                            <h4>Don't have an account? <a href="client_signup.php" class="hover-a">Sign Up</a></h4>
                        </div>

                        

                        <div class="text-center mt-3">
                            <a href="shop.php" class="text-muted">
                                <i class="fas fa-arrow-left"></i> Continue shopping as guest
                            </a>
                        </div>
                    </form>
                </div>
                <div class="login-img">
                    <img src="assets/img/login.jpg" alt="img">
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/feather.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
