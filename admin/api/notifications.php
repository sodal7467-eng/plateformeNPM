<?php

/*
|--------------------------------------------------------------------------
| API : notifications admin
|--------------------------------------------------------------------------
| Retourne au format JSON :
| - le nombre de commandes "En attente"
| - les 10 dernieres pour affichage dans le dropdown
|
| Endpoint appele en AJAX toutes les 30 secondes par la navbar admin.
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

// Verifier l'authentification (sans redirection pour un endpoint AJAX)
if (
    empty($_SESSION["id"])
    || ($_SESSION["role"] ?? "") !== "admin"
) {
    http_response_code(401);
    echo json_encode(["erreur" => "Non autorise"]);
    exit;
}

require_once __DIR__ . "/../../config/database.php";

try {

    // Compte total
    $reqTotal = $pdo->prepare("
        SELECT COUNT(*) AS n
        FROM commandes
        WHERE statut = 'En attente'
    ");
    $reqTotal->execute();
    $total = (int) $reqTotal->fetch(PDO::FETCH_ASSOC)["n"];

    // Liste des 10 dernieres
    $req = $pdo->prepare("
        SELECT
            c.id,
            c.total,
            c.date_commande,
            u.nom AS client_nom,
            u.telephone AS client_telephone
        FROM commandes c
        LEFT JOIN utilisateurs u ON u.id = c.utilisateur_id
        WHERE c.statut = 'En attente'
        ORDER BY c.date_commande DESC
        LIMIT 10
    ");
    $req->execute();
    $items = [];

    foreach ($req->fetchAll(PDO::FETCH_ASSOC) as $c) {
        $items[] = [
            "id"          => (int) $c["id"],
            "client"      => $c["client_nom"] ?? "Client",
            "telephone"   => $c["client_telephone"] ?? "",
            "total"       => (float) $c["total"],
            "date"        => date("d/m/Y H:i", strtotime($c["date_commande"])),
            "date_iso"    => $c["date_commande"],
        ];
    }

    echo json_encode([
        "total" => $total,
        "items" => $items,
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur serveur"]);
    error_log("api/notifications: " . $e->getMessage());
}
