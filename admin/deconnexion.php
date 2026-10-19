<?php

// =========================================================
// DÉCONNEXION DE L'ADMIN
// (session NPM_ADMIN, voir admin/.htaccess — la session
//  client de la boutique n'est pas touchée)
// =========================================================

session_name("NPM_ADMIN");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Vider toutes les variables de session

$_SESSION = [];


// Supprimer le cookie de session

if (ini_get("session.use_cookies")) {

    $parametres = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        time() - 42000,
        $parametres["path"],
        $parametres["domain"],
        $parametres["secure"],
        $parametres["httponly"]
    );

}


// Détruire la session côté serveur

session_destroy();


// Rediriger vers la page de connexion

header("Location: ../connexion.php");
exit;
