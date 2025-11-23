<?php
require_once __DIR__ . '/../php/Class/Sale.php';
try {
    $s = Sale::displayAllSales();
    echo 'OK: '.count($s)." rows\n";
} catch (Throwable $e) {
    echo 'ERR: '.get_class($e).': '.$e->getMessage()."\n";
}
