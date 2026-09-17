<?php

// =========================================================
// DÉCONNEXION DU CLIENT / ADMIN
// =========================================================

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


// Rediriger vers l'accueil

header("Location: index.php");
exit;
