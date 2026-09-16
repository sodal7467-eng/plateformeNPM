<?php

session_start();

require_once "../config/database.php";
require_once("includes/auth.php");

/*
|--------------------------------------------------------------------------
| Vérifier l'identifiant
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: messages.php");
    exit;
}

$id = (int) $_GET["id"];

/*
|--------------------------------------------------------------------------
| Vérifier que le message existe
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
| Supprimer le message
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare(
    "DELETE FROM messages WHERE id = ?"
);

$requete->execute([$id]);

/*
|--------------------------------------------------------------------------
| Retour à la liste des messages
|--------------------------------------------------------------------------
*/

header("Location: messages.php");
exit;