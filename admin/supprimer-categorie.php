<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("includes/auth.php");
require_once "../config/database.php";
require_once "../includes/log_admin.php";


/*
|--------------------------------------------------------------------------
| MÉTHODE POST OBLIGATOIRE
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: categories.php");
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
    header("Location: categories.php");
    exit;
}

$id = (int) $_POST["id"];


/*
|--------------------------------------------------------------------------
| VÉRIFIER QUE LA CATÉGORIE EXISTE
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare("
    SELECT id
    FROM categories
    WHERE id = ?
");

$requete->execute([$id]);

$categorie = $requete->fetch(PDO::FETCH_ASSOC);

if (!$categorie) {
    header("Location: categories.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| BLOQUER SI DES PRODUITS UTILISENT ENCORE CETTE CATÉGORIE
|--------------------------------------------------------------------------
*/

$requeteProduits = $pdo->prepare("
    SELECT COUNT(*) AS total
    FROM produits
    WHERE categorie = ?
");

$requeteProduits->execute([$id]);

$totalProduits = (int) $requeteProduits->fetch(PDO::FETCH_ASSOC)["total"];

if ($totalProduits > 0) {
    header("Location: categories.php?erreur=produits");
    exit;
}


/*
|--------------------------------------------------------------------------
| SUPPRIMER LA CATÉGORIE
|--------------------------------------------------------------------------
*/

$requeteSuppression = $pdo->prepare("
    DELETE FROM categories
    WHERE id = ?
");

$requeteSuppression->execute([$id]);


logAdmin($pdo, "categorie.supprimer", "categorie", $id);


header("Location: categories.php?supprime=1");
exit;
