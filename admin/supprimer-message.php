<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("includes/auth.php");
require_once "../config/database.php";
require_once "../includes/log_admin.php";


/*
|--------------------------------------------------------------------------
| VÉRIFIER LA MÉTHODE (POST obligatoire)
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: messages.php");
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
| VÉRIFIER L'IDENTIFIANT
|--------------------------------------------------------------------------
*/

if (!isset($_POST["id"]) || !is_numeric($_POST["id"])) {
    header("Location: messages.php");
    exit;
}

$id = (int) $_POST["id"];


/*
|--------------------------------------------------------------------------
| VÉRIFIER QUE LE MESSAGE EXISTE
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare(
    "SELECT id FROM messages WHERE id = ?"
);

$requete->execute([$id]);

$message = $requete->fetch();

if (!$message) {
    header("Location: messages.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| SUPPRIMER LE MESSAGE
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare(
    "DELETE FROM messages WHERE id = ?"
);

$requete->execute([$id]);


logAdmin($pdo, "message.supprimer", "message", $id);


header("Location: messages.php?supprime=1");
exit;
