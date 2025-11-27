<?php
require_once("Personne.php");
class Admin extends Personne {
    public function __construct(
        $nom,
        $prenom,
        $adr,
        $tele,
        $email,
        $image,
        private string $mdp
    ) {
        Personne::__construct($nom, $prenom, $adr, $tele, $email, $image);
        $this->mdp;
    }


    // getter 
    public function __get(string $property) {
        Personne::__get($property); // call the getter of parent
        switch ($property) {
            case 'mdp':
                return $this->mdp;
            default:
                exit("<b>ERROR :<b> {$property} property is invalid!!!!");
        }
    }

    // ajouter un nouveau admin
    public function AjouterAdmin($nom_de_class, $isSuperAdmin = false) {
        // Hash password before storing
        $hashedPassword = Security::hashPassword($this->mdp);
        Dao::ajouterAdmin($this->nom, $this->prenom, $this->adr, $this->tele, $this->email, $hashedPassword, $this->image, $nom_de_class, $isSuperAdmin);
    }

    // Modifier les donnes sauf l'image d'admin
    public static function modifierAdmin($id, $nom, $prenom, $adr, $tele, $email, $mdp, $nom_de_class) {
        // Hash password if it's being changed
        $hashedPassword = (!empty($mdp) && strlen($mdp) < 60) ? Security::hashPassword($mdp) : $mdp;
        Dao::modifierAdmin($id, $nom, $prenom, $adr, $tele, $email, $hashedPassword, $nom_de_class);
    }

    // Modifier l'image d'admin
    public static function modifierImageAdmin($id, $image) {
        Dao::modifierImageAdmin($id, $image);
    }

    // Supprimer un compte d'un admin
    public static function supprimer($id, $nom_de_class) {
        Dao::supprimerPersonne($id, $nom_de_class);
    }

    // pour tester si email exitse et mdp correcte
    public static function estAdmin($email, $mdp) {
        // pour declarer les constantes en cas d'echec
        if (!defined('FAUX_EMAIL')) define("FAUX_EMAIL", "email n'existe pas");
        if (!defined('FAUX_MDP')) define("FAUX_MDP", "mdp est incorrect");
        
        // Check rate limiting
        $rateLimitCheck = Security::checkLoginAttempts($email);
        if (!$rateLimitCheck['allowed']) {
            Security::logError('Login rate limit exceeded', ['email' => $email]);
            return $rateLimitCheck['message'];
        }
        
        /*
        poour tester si un admin AVEC cet email existe
        si oui la methode returne un tableau associative contient toutes les donnes de cet admin
        sinon va retourner false 
        */
        $admin = Dao::adminExiste($email);
        if ($admin === false) {
            Security::recordFailedLogin($email);
            Security::logError('Admin login failed: email not found', ['email' => $email]);
            return FAUX_EMAIL;
        }
        /*
        pour tester si mdp correcte ou pas on cas de succes return la ligne d'admin
        sinon return FAUX_MDP
        */

        // Use password_verify for hashed passwords
        if (Security::verifyPassword($mdp, $admin['mdp'])) {
            // Reset login attempts on successful login
            Security::resetLoginAttempts($email);
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            Security::logError('Admin login successful', ['email' => $email, 'admin_id' => $admin['id']]);
            return $admin;
        } else {
            Security::recordFailedLogin($email);
            Security::logError('Admin login failed: incorrect password', ['email' => $email]);
            return FAUX_MDP;
        }
    }
}

// $admin = new Admin("root", "", "", "", "root@gmail.com", "root");
// $admin->AjouterAdmin("Admin");

// Admin::supprimer(1, "Admin");
// echo ("<pre>");
// print_r($admin->Afficher("admin"));