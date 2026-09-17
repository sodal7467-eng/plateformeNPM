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


/*
|--------------------------------------------------------------------------
| VÉRIFIER LA MÉTHODE D'ENVOI
|--------------------------------------------------------------------------
| La suppression doit obligatoirement être faite avec POST.
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: employes.php");
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

    header("Location: employes.php");
    exit;

}

$id = (int) $_POST["id"];


/*
|--------------------------------------------------------------------------
| VÉRIFIER QUE L'EMPLOYÉ EXISTE
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare("
    SELECT id
    FROM utilisateurs
    WHERE id = ?
        AND role = 'employe'
");

$requete->execute([$id]);

$employe = $requete->fetch();

if (!$employe) {

    header("Location: employes.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| SUPPRIMER L'EMPLOYÉ
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare("
    DELETE FROM utilisateurs
    WHERE id = ?
        AND role = 'employe'
");

$requete->execute([$id]);


/*
|--------------------------------------------------------------------------
| RETOUR À LA LISTE
|--------------------------------------------------------------------------
*/

header("Location: employes.php?supprime=1");

exit;
