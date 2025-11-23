<?php
session_start();
require_once(__DIR__ . '/app_config.php');
require_once(__DIR__ . "/../php/Class/Client.php");

// Handle registration
if (isset($_POST['register'])) {
    extract($_POST);

    // Validate inputs
    $errors = [];

    if (empty($nom) || empty($prenom)) {
        $errors[] = "Name and surname are required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }
    if (strlen($mdp) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($mdp !== $confirm_mdp) {
        $errors[] = "Passwords do not match.";
    }

    $imagePath = './image/client/default.png';

    // Handle optional profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExtensions, true)) {
                $errors[] = "Profile photo must be a JPG, JPEG, PNG or GIF file.";
            } else {
                $newFilename = 'client_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
                $uploadDirectory = __DIR__ . '/image/client/';

                if (!is_dir($uploadDirectory)) {
                    if (!mkdir($uploadDirectory, 0755, true)) {
                        $errors[] = "Unable to prepare upload directory.";
                    }
                }

                if (empty($errors)) {
                    $uploadPath = $uploadDirectory . $newFilename;

                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                        $imagePath = './image/client/' . $newFilename;
                    } else {
                        $errors[] = "Failed to upload profile photo. Please try again.";
                    }
                }
            }
        } else {
            $errors[] = "Unexpected error while uploading profile photo.";
        }
    }

    if (empty($errors)) {
        $result = Client::register($nom, $prenom, $email, $mdp, $adr ?? '', $tele ?? '', $imagePath);

        if ($result === false) {
            $errors[] = "Email already registered. Please sign in instead.";
        } else {
            // Auto-login after registration
            $client = Client::estClient($email, $mdp);
            if (isset($client['email'])) {
                $_SESSION['client'] = $client;
                header("Location: shop.php?message=welcome");
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Client Sign Up - <?= htmlspecialchars(platform_name()) ?></title>

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
                    <form class="login-userset" method="post" action="" enctype="multipart/form-data">
                        <div class="login-logo">
                            <img src="assets/img/logo.png" alt="img">
                        </div>
                        <div class="login-userheading">
                            <h3>Create Account</h3>
                            <h4>Join <?= htmlspecialchars(platform_name()) ?> today</h4>
                        </div>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $error): ?>
                                    <div><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-login">
                                    <label>First Name *</label>
                                    <input type="text" name="nom" placeholder="Enter your first name" 
                                           value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-login">
                                    <label>Last Name *</label>
                                    <input type="text" name="prenom" placeholder="Enter your last name" 
                                           value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-login">
                            <label>Email *</label>
                            <div class="form-addons">
                                <input type="email" name="email" placeholder="Enter your email address" 
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                <img src="assets/img/icons/mail.svg" alt="img">
                            </div>
                        </div>

                        <div class="form-login">
                            <label>Phone</label>
                            <div class="form-addons">
                                <input type="text" name="tele" placeholder="Enter your phone number" 
                                       value="<?= htmlspecialchars($_POST['tele'] ?? '') ?>">
                                <img src="assets/img/icons/phone.svg" alt="img">
                            </div>
                        </div>

                        <div class="form-login">
                            <label>Address</label>
                            <textarea name="adr" class="form-control" rows="2" 
                                      placeholder="Enter your address (optional)"><?= htmlspecialchars($_POST['adr'] ?? '') ?></textarea>
                        </div>

                        <div class="form-login">
                            <label>Profile Photo</label>
                            <input type="file" name="profile_image" accept="image/*">
                        </div>

                        <div class="form-login">
                            <label>Password *</label>
                            <div class="pass-group">
                                <input type="password" class="pass-input" name="mdp" 
                                       placeholder="Enter your password (min 6 characters)" required>
                                <span class="fas toggle-password fa-eye-slash"></span>
                            </div>
                        </div>

                        <div class="form-login">
                            <label>Confirm Password *</label>
                            <div class="pass-group">
                                <input type="password" class="pass-input" name="confirm_mdp" 
                                       placeholder="Confirm your password" required>
                                <span class="fas toggle-password fa-eye-slash"></span>
                            </div>
                        </div>

                        <div class="form-login">
                            <button class="btn btn-login" type="submit" name="register">Create Account</button>
                        </div>

                        <div class="signinform text-center">
                            <h4>Already have an account? <a href="signin_client.php" class="hover-a">Sign In</a></h4>
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
