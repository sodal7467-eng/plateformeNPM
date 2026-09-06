<?php

require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| VÉRIFIER L'ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: produits.php");
    exit;

}

$id = (int) $_GET["id"];


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

    $cheminImage = "../uploads/" . $produit["image"];

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