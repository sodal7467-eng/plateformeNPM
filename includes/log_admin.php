<?php

/*
|--------------------------------------------------------------------------
| Journal des actions administrateur
|--------------------------------------------------------------------------
| À appeler après chaque action sensible (suppression, modification,
| paiement enregistré, etc.).
*/

function logAdmin(
    PDO $pdo,
    string $action,
    ?string $cibleType = null,
    ?int $cibleId = null,
    ?string $details = null
): void {

    // Ne rien logger si personne n'est connecté
    if (empty($_SESSION["id"])) {
        return;
    }

    try {
        $requete = $pdo->prepare("
            INSERT INTO logs_admin
                (admin_id, admin_nom, action, cible_type, cible_id, details, ip)
            VALUES
                (:admin_id, :admin_nom, :action, :cible_type, :cible_id, :details, :ip)
        ");

        $requete->execute([
            "admin_id"   => (int) $_SESSION["id"],
            "admin_nom"  => $_SESSION["nom"] ?? null,
            "action"     => $action,
            "cible_type" => $cibleType,
            "cible_id"   => $cibleId,
            "details"    => $details,
            "ip"         => $_SERVER["REMOTE_ADDR"] ?? null,
        ]);
    } catch (Throwable $e) {
        // Le log ne doit jamais bloquer l'action métier.
        error_log("logAdmin: " . $e->getMessage());
    }
}
