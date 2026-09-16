<?php

/*
|--------------------------------------------------------------------------
| PROTECTION ADMIN
|--------------------------------------------------------------------------
*/

require_once("includes/auth.php");

require_once("../config/database.php");


/*
|--------------------------------------------------------------------------
| VÉRIFIER LA MÉTHODE D'ENVOI
|--------------------------------------------------------------------------
| La suppression doit obligatoirement être faite avec POST.
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: produits.php");
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

    header("Location: produits.php");
    exit;

}

$id = (int) $_POST["id"];


/*
|--------------------------------------------------------------------------
| RÉCUPÉRER LE PRODUIT
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare("
    SELECT image
    FROM produits
    WHERE id = ?
");

$requete->execute([$id]);

$produit = $requete->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| VÉRIFIER QUE LE PRODUIT EXISTE
|--------------------------------------------------------------------------
*/

if (!$produit) {

    header("Location: produits.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| SUPPRIMER LE PRODUIT
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare("
    DELETE FROM produits
    WHERE id = ?
");

$requete->execute([$id]);


/*
|--------------------------------------------------------------------------
| SUPPRIMER L'IMAGE
|--------------------------------------------------------------------------
*/

if (!empty($produit["image"])) {

    /*
    |----------------------------------------------------------------------
    | Sécuriser le nom du fichier
    |----------------------------------------------------------------------
    */

    $nomImage = basename($produit["image"]);

    $cheminImage = "../uploads/" . $nomImage;


    if (file_exists($cheminImage)) {

        unlink($cheminImage);

    }

}


/*
|--------------------------------------------------------------------------
| RETOUR À LA LISTE
|--------------------------------------------------------------------------
*/

header("Location: produits.php?supprime=1");

exit;