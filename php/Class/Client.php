<?php
require_once("Personne.php");
class Client extends Personne {
    public function __construct(
        $nom,
        $prenom,
        $adr,
        $tele,
        $email,
        $image
    ) {
        Personne::__construct($nom, $prenom, $adr, $tele, $email, $image);
    }

    // Authenticate client
    public static function estClient($email, $mdp) {
        if (!defined('FAUX_EMAIL')) {
            define('FAUX_EMAIL', "email n'existe pas");
        }
        if (!defined('FAUX_MDP')) {
            define('FAUX_MDP', "mdp est incorrect");
        }
        
        // Check rate limiting
        $rateLimitCheck = Security::checkLoginAttempts('client_' . $email);
        if (!$rateLimitCheck['allowed']) {
            Security::logError('Client login rate limit exceeded', ['email' => $email]);
            return $rateLimitCheck['message'];
        }
        
        $client = Dao::clientByEmail($email);
        if (!$client) {
            Security::recordFailedLogin('client_' . $email);
            Security::logError('Client login failed: email not found', ['email' => $email]);
            return FAUX_EMAIL;
        }
        
        // Verify password using Security class
        if (Security::verifyPassword($mdp, $client['mdp'])) {
            Security::resetLoginAttempts('client_' . $email);
            session_regenerate_id(true);
            Security::logError('Client login successful', ['email' => $email, 'client_id' => $client['id']]);
            return $client;
        } else {
            Security::recordFailedLogin('client_' . $email);
            Security::logError('Client login failed: incorrect password', ['email' => $email]);
            return FAUX_MDP;
        }
    }

    // Register new client
    public static function register($nom, $prenom, $email, $mdp, $adr = '', $tele = '', $image = './image/client/default.png') {
        // Validate input
        if (!Security::validateEmail($email)) {
            return false;
        }
        
        // Check if email already exists
        $existing = Dao::clientByEmail($email);
        if ($existing) {
            return false; // Email already registered
        }
        
        // Hash password before storing
        $hashedPassword = Security::hashPassword($mdp);
        return Dao::registerClient($nom, $prenom, $email, $hashedPassword, $adr, $tele, $image);
    }

    // Update profile
    public static function updateProfile($id, $nom, $prenom, $adr, $tele, $email, $image) {
        Dao::updateClientProfile($id, $nom, $prenom, $adr, $tele, $email, $image);
    }

    // Change password
    public static function changePassword($id, $newPassword) {
        $hashedPassword = Security::hashPassword($newPassword);
        Dao::updateClientPassword($id, $hashedPassword);
    }

    // Get client orders
    public static function getOrders($id_client) {
        return Dao::getClientOrders($id_client);
    }

    // Get order details
    public static function getOrderDetails($num_com, $id_client) {
        return Dao::getOrderDetails($num_com, $id_client);
    }
} 