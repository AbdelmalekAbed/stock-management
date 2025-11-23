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
        $client = Dao::clientByEmail($email);
        if (!$client) {
            return FAUX_EMAIL;
        }
        if (!password_verify($mdp, $client['mdp'])) {
            return FAUX_MDP;
        }
        return $client;
    }

    // Register new client
    public static function register($nom, $prenom, $email, $mdp, $adr = '', $tele = '', $image = './image/client/default.png') {
        // Check if email already exists
        $existing = Dao::clientByEmail($email);
        if ($existing) {
            return false; // Email already registered
        }
        return Dao::registerClient($nom, $prenom, $email, $mdp, $adr, $tele, $image);
    }

    // Update profile
    public static function updateProfile($id, $nom, $prenom, $adr, $tele, $email, $image) {
        Dao::updateClientProfile($id, $nom, $prenom, $adr, $tele, $email, $image);
    }

    // Change password
    public static function changePassword($id, $newPassword) {
        Dao::updateClientPassword($id, $newPassword);
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