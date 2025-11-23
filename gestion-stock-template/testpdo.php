<?php
// Le dossier `php/Class/` est au niveau parent de `gestion-stock-template`
require_once __DIR__ . '/../php/Class/Dao.php'; // chemin corrigé

// Tentative 1: utiliser Dao::getPDO() (comportement existant)
// Note: Dao::getPDO() appelle exit() en cas d'erreur, ce qui stoppe le script.
// Pour obtenir des messages d'erreur plus détaillés, on tente aussi une connexion PDO directe.
try {
    // Essayer une connexion PDO directe avec l'utilisateur root et le mot de passe que vous avez défini.
    $user = 'root';
    $pass = 'Abdou_pass0'; // utilisé seulement pour debug local
    $dsn = 'mysql:host=localhost;dbname=gestion_des_stocks;charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "DB OK (direct PDO, localhost/socket): " . $pdo->query('SELECT VERSION()')->fetchColumn() . PHP_EOL;
    } catch (Throwable $eSocket) {
        // Si échec socket (No such file or directory), retenter en forçant TCP (127.0.0.1)
        echo "Direct PDO (localhost) failed: " . $eSocket->getMessage() . PHP_EOL;
        $dsnTcp = 'mysql:host=127.0.0.1;port=3306;dbname=gestion_des_stocks;charset=utf8mb4';
        $pdo = new PDO($dsnTcp, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "DB OK (direct PDO, TCP): " . $pdo->query('SELECT VERSION()')->fetchColumn() . PHP_EOL;
    }
} catch (Throwable $e) {
    echo "PDO ERROR (direct): " . $e->getMessage() . PHP_EOL;
}

// Enfin, montrer ce que renvoie Dao::getPDO() si cela fonctionne (peut appeler exit() en cas d'erreur)
if (class_exists('Dao')) {
    try {
        $pdo2 = Dao::getPDO();
        echo "DB OK (Dao): " . $pdo2->query('SELECT VERSION()')->fetchColumn() . PHP_EOL;
    } catch (Throwable $e) {
        echo "PDO ERROR (Dao): " . $e->getMessage() . PHP_EOL;
    }
}