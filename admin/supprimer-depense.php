<?php

require_once("includes/auth.php");

require_once("../config/database.php");


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: depenses.php");
    exit;
}


if (
    !isset($_POST["csrf_token"]) ||
    !isset($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
) {
    die("Erreur de sécurité : requête invalide.");
}


if (!isset($_POST["id"]) || !is_numeric($_POST["id"])) {
    header("Location: depenses.php");
    exit;
}

$id = (int) $_POST["id"];

$requete = $pdo->prepare("DELETE FROM depenses WHERE id = ?");
$requete->execute([$id]);

header("Location: depenses.php?supprime=1");
exit;
