<?php
/*
|--------------------------------------------------------------------------
| Ouvrir une notification : la marquer comme lue puis aller à son lien
|--------------------------------------------------------------------------
*/

session_start();

require_once "config/database.php";
require_once "includes/notifications.php";

if (!isset($_SESSION["id"])) {
    header("Location: connexion.php");
    exit;
}

$idClient = (int) $_SESSION["id"];

notificationsPreparerTable($pdo);


// Tout marquer comme lu
if (($_GET["action"] ?? "") === "tout-lu") {
    $pdo->prepare("UPDATE notifications_clients SET lu = 1 WHERE utilisateur_id = ?")
        ->execute([$idClient]);

    // Revenir sur la page d'origine, seulement si elle est sur ce site
    $retour = $_SERVER["HTTP_REFERER"] ?? "";
    if (parse_url($retour, PHP_URL_HOST) !== ($_SERVER["HTTP_HOST"] ?? "")) {
        $retour = "index.php";
    }

    header("Location: " . $retour);
    exit;
}


$id = (int) ($_GET["id"] ?? 0);

$requete = $pdo->prepare("
    SELECT lien FROM notifications_clients
    WHERE id = ? AND utilisateur_id = ?
");
$requete->execute([$id, $idClient]);
$notification = $requete->fetch(PDO::FETCH_ASSOC);

if (!$notification) {
    header("Location: index.php");
    exit;
}

$pdo->prepare("UPDATE notifications_clients SET lu = 1 WHERE id = ?")->execute([$id]);

// Liens internes uniquement (pas de redirection vers un autre site)
$lien = $notification["lien"] ?? "";
if ($lien === "" || preg_match('#^[a-z]+:|^//#i', $lien)) {
    $lien = "mes-commende.php";
}

header("Location: " . $lien);
exit;
