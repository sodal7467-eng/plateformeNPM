<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| PROTECTION ADMIN
|--------------------------------------------------------------------------
*/

require_once("includes/auth.php");

require_once "../config/database.php";
require_once "../includes/log_admin.php";


/*
|--------------------------------------------------------------------------
| VÉRIFIER LA MÉTHODE D'ENVOI
|--------------------------------------------------------------------------
| La suppression doit obligatoirement être faite avec POST.
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: administrateurs.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| VÉRIFIER LE TOKEN CSRF
|--------------------------------------------------------------------------
*/

if (
    !isset($_POST["csrf_token"]) ||
    !isset($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
) {

    die("Erreur de sécurité : requête invalide.");

}


/*
|--------------------------------------------------------------------------
| VÉRIFIER L'ID
|--------------------------------------------------------------------------
*/

if (!isset($_POST["id"]) || !is_numeric($_POST["id"])) {

    header("Location: administrateurs.php");
    exit;

}

$id = (int) $_POST["id"];


/*
|--------------------------------------------------------------------------
| EMPÊCHER LA SUPPRESSION DE SON PROPRE COMPTE
|--------------------------------------------------------------------------
*/

if (isset($_SESSION["id"]) && (int) $_SESSION["id"] === $id) {

    header("Location: administrateurs.php?erreur=soi-meme");
    exit;

}


/*
|--------------------------------------------------------------------------
| VÉRIFIER QUE L'ADMINISTRATEUR EXISTE
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare("
    SELECT id
    FROM utilisateurs
    WHERE id = ?
        AND role = 'admin'
");

$requete->execute([$id]);

$administrateur = $requete->fetch();

if (!$administrateur) {

    header("Location: administrateurs.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| SUPPRIMER L'ADMINISTRATEUR
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare("
    DELETE FROM utilisateurs
    WHERE id = ?
        AND role = 'admin'
");

$requete->execute([$id]);


logAdmin($pdo, "admin.supprimer", "utilisateur", $id, "Suppression d'un administrateur.");


/*
|--------------------------------------------------------------------------
| RETOUR À LA LISTE
|--------------------------------------------------------------------------
*/

header("Location: administrateurs.php?supprime=1");

exit;
