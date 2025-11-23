<?php
// Entry point for PHP built-in server. Redirect to public shop.
// Using 302 temporary redirect so browser updates location; change to 301 if permanent.
header('Location: gestion-stock-template/shop.php');
exit;