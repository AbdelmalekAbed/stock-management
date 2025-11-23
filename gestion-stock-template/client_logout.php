<?php
session_start();
unset($_SESSION['client']);
unset($_SESSION['cart']);
session_destroy();
header("Location: shop.php?message=logged_out");
exit();
