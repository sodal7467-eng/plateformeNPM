<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("includes/auth.php");
require_once("../config/database.php");
require_once("../includes/log_admin.php");


/*
|--------------------------------------------------------------------------
| MÉTHODE POST + CSRF
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: commandes.php");
    exit;
}

if (
    !isset($_POST["csrf_token"])
    || !hash_equals($_SESSION["csrf_token"] ?? "", $_POST["csrf_token"])
) {
    die("Erreur de sécurité : requête invalide.");
}


/*
|--------------------------------------------------------------------------
| ID DE LA COMMANDE
|--------------------------------------------------------------------------
*/

if (!isset($_POST["id"]) || !is_numeric($_POST["id"])) {
    header("Location: commandes.php");
    exit;
}

$commandeId = (int) $_POST["id"];
$restituerStock = isset($_POST["restituer_stock"]);


/*
|--------------------------------------------------------------------------
| ANNULATION ATOMIQUE
|--------------------------------------------------------------------------
| 1. Vérifier qu'aucun crédit "en cours" n'est lié
|    (sinon on efface une dette réelle — refus par sécurité)
| 2. Optionnellement restituer le stock des produits
| 3. Supprimer la commande (cascade : details, factures)
|    Les credits_clients passent en NULL (FK ON DELETE SET NULL)
| 4. Logger l'action
*/

try {

    $pdo->beginTransaction();

    // 1) Récupérer la commande + le client
    $stmt = $pdo->prepare("
        SELECT c.id, c.total, c.statut, c.type_vente, c.date_commande,
               u.nom AS client_nom
        FROM commandes c
        INNER JOIN utilisateurs u ON u.id = c.utilisateur_id
        WHERE c.id = ?
        FOR UPDATE
    ");
    $stmt->execute([$commandeId]);
    $commande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$commande) {
        $pdo->rollBack();
        header("Location: commandes.php?erreur=commande_introuvable");
        exit;
    }

    // 2a) Verrouillage comptable : une commande Livrée est definitive
    if (($commande["statut"] ?? "") === "Livrée") {
        $pdo->rollBack();
        header("Location: commande-details.php?id={$commandeId}&erreur=commande_verrouillee");
        exit;
    }

    // 2b) Bloquer si crédit avec reste à payer > 0
    $stmt = $pdo->prepare("
        SELECT id, reste_a_payer
        FROM credits_clients
        WHERE commande_id = ?
    ");
    $stmt->execute([$commandeId]);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
        if ((float) $c["reste_a_payer"] > 0) {
            $pdo->rollBack();
            header("Location: commande-details.php?id={$commandeId}&erreur=credit_actif");
            exit;
        }
    }

    // 3) Restituer le stock si demandé
    if ($restituerStock) {
        $lignes = $pdo->prepare("
            SELECT produit_id, quantite
            FROM commande_details
            WHERE commande_id = ?
        ");
        $lignes->execute([$commandeId]);

        $up = $pdo->prepare("UPDATE produits SET stock = stock + ? WHERE id = ?");
        foreach ($lignes->fetchAll(PDO::FETCH_ASSOC) as $l) {
            $up->execute([(int) $l["quantite"], (int) $l["produit_id"]]);
        }
    }

    // 4) Supprimer la commande
    //    - commande_details, factures : CASCADE
    //    - credits_clients : SET NULL (l'historique du crédit reste)
    //    - paiements_credits : reste attaché aux credits_clients
    $pdo->prepare("DELETE FROM commandes WHERE id = ?")->execute([$commandeId]);

    // 5) Journal
    $details = sprintf(
        "Suppression de la commande %d du client %s (total : %s FCFA, statut : %s, type : %s). Stock %s.",
        $commandeId,
        $commande["client_nom"],
        number_format((float) $commande["total"], 0, ",", " "),
        $commande["statut"],
        $commande["type_vente"],
        $restituerStock ? "restitué" : "non restitué"
    );

    logAdmin($pdo, "commande.supprimer", "commande", $commandeId, $details);

    $pdo->commit();

    header("Location: commandes.php?supprime=1");
    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("supprimer-commande: " . $e->getMessage());
    header("Location: commande-details.php?id={$commandeId}&erreur=commande_suppr");
    exit;
}
