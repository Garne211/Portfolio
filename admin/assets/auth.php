<?php
/**
 * Système d'authentification
 */

// Déjà inclus dans helpers.php mais on peut ajouter des fonctions supplémentaires ici

/**
 * Vérifie si l'utilisateur est admin
 */
function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && 
           $_SESSION['admin_logged_in'] === true &&
           isset($_SESSION['admin_id']);
}

/**
 * Déconnexion
 */
function logout() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}