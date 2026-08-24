<?php

require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Vérifier l'ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header("Location: produits.php");
    exit;

}

$id = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| Récupérer le produit avant suppression
|--------------------------------------------------------------------------
| Cela nous permet de récupérer son image.
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
| Vérifier que le produit existe
|--------------------------------------------------------------------------
*/

if (!$produit) {

    header("Location: produits.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| Supprimer le produit
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare("
    DELETE FROM produits
    WHERE id = ?
");

$requete->execute([$id]);


/*
|--------------------------------------------------------------------------
| Supprimer également l'image
|--------------------------------------------------------------------------
*/

if (!empty($produit['image'])) {

    $cheminImage = "../assets/images/" . $produit['image'];

    if (file_exists($cheminImage)) {

        unlink($cheminImage);

    }

}


/*
|--------------------------------------------------------------------------
| Retour à la liste des produits
|--------------------------------------------------------------------------
*/

header("Location: produits.php?supprime=1");

exit;