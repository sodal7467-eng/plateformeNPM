<?php

require_once "../config/database.php";


// Vérifier que l'ID existe
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: categories.php");
    exit;
}

$id = (int) $_GET["id"];


// Vérifier que la catégorie existe
$requete = $pdo->prepare("
    SELECT *
    FROM categories
    WHERE id = ?
");

$requete->execute([$id]);

$categorie = $requete->fetch(PDO::FETCH_ASSOC);


// Si la catégorie n'existe pas
if (!$categorie) {
    header("Location: categories.php");
    exit;
}


// Vérifier si des produits utilisent cette catégorie
$requeteProduits = $pdo->prepare("
    SELECT COUNT(*) AS total
    FROM produits
    WHERE categorie = ?
");

$requeteProduits->execute([$id]);

$resultat = $requeteProduits->fetch(PDO::FETCH_ASSOC);

$totalProduits = (int) $resultat["total"];


// Si des produits utilisent encore cette catégorie
if ($totalProduits > 0) {

    header("Location: categories.php?erreur=produits");
    exit;
}


// Supprimer la catégorie
$requeteSuppression = $pdo->prepare("
    DELETE FROM categories
    WHERE id = ?
");

$requeteSuppression->execute([$id]);


// Retourner vers la liste
header("Location: categories.php?supprime=1");
exit;