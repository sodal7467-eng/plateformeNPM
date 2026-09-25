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
    header("Location: credits.php");
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
| ID DU PAIEMENT
|--------------------------------------------------------------------------
*/

if (!isset($_POST["id"]) || !is_numeric($_POST["id"])) {
    header("Location: credits.php");
    exit;
}

$paiementId = (int) $_POST["id"];

// Redirection cible après suppression (par défaut : liste des crédits)
$retour = $_POST["retour"] ?? "credits.php";
if (!in_array(explode("?", $retour)[0], [
    "credits.php",
    "details-credit.php",
    "commande-details.php",
    "factures.php",
], true)) {
    $retour = "credits.php";
}


/*
|--------------------------------------------------------------------------
| ANNULATION ATOMIQUE
|--------------------------------------------------------------------------
| 1. Verrouiller le paiement, le crédit et la facture
| 2. Décrémenter les soldes payés + réajuster les statuts
| 3. Supprimer le paiement
| 4. Logger l'action
*/

try {

    $pdo->beginTransaction();

    // 1) Charger le paiement
    $stmt = $pdo->prepare("
        SELECT p.id, p.credit_id, p.montant, p.commentaire, p.date_paiement,
               c.commande_id, c.utilisateur_id
        FROM paiements_credits p
        INNER JOIN credits_clients c ON c.id = p.credit_id
        WHERE p.id = ?
        FOR UPDATE
    ");
    $stmt->execute([$paiementId]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$p) {
        $pdo->rollBack();
        header("Location: {$retour}&erreur=paiement_introuvable");
        exit;
    }

    $montant = (float) $p["montant"];
    $creditId = (int) $p["credit_id"];
    $commandeId = $p["commande_id"] ? (int) $p["commande_id"] : null;

    // 2) Réajuster le crédit
    $stmt = $pdo->prepare("
        SELECT montant_total, montant_paye
        FROM credits_clients
        WHERE id = ?
        FOR UPDATE
    ");
    $stmt->execute([$creditId]);
    $credit = $stmt->fetch(PDO::FETCH_ASSOC);

    $creditNouveauPaye = max(0, (float) $credit["montant_paye"] - $montant);
    $creditNouveauReste = max(0, (float) $credit["montant_total"] - $creditNouveauPaye);
    $creditStatut = $creditNouveauReste <= 0 ? "Réglé" : "En cours";

    $pdo->prepare("
        UPDATE credits_clients
        SET montant_paye = ?, reste_a_payer = ?, statut = ?
        WHERE id = ?
    ")->execute([$creditNouveauPaye, $creditNouveauReste, $creditStatut, $creditId]);


    // 3) Réajuster la facture (si commande liée)
    if ($commandeId !== null) {
        $stmt = $pdo->prepare("
            SELECT id, montant_total, montant_paye
            FROM factures
            WHERE commande_id = ?
            FOR UPDATE
        ");
        $stmt->execute([$commandeId]);
        $facture = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($facture) {
            $factNouveauPaye = max(0, (float) $facture["montant_paye"] - $montant);
            $factNouveauReste = max(0, (float) $facture["montant_total"] - $factNouveauPaye);

            if ($factNouveauReste <= 0) {
                $factStatut = "Payée";
            } elseif ($factNouveauPaye > 0) {
                $factStatut = "Partiellement payée";
            } else {
                $factStatut = "Impayée";
            }

            $pdo->prepare("
                UPDATE factures
                SET montant_paye = ?, reste_a_payer = ?, statut = ?
                WHERE id = ?
            ")->execute([$factNouveauPaye, $factNouveauReste, $factStatut, $facture["id"]]);
        }
    }


    // 4) Supprimer le paiement
    $pdo->prepare("DELETE FROM paiements_credits WHERE id = ?")->execute([$paiementId]);


    // 5) Journaliser
    $details = sprintf(
        "Annulation d'un paiement de %s FCFA (crédit %d%s). Commentaire d'origine : %s",
        number_format($montant, 0, ",", " "),
        $creditId,
        $commandeId ? ", commande {$commandeId}" : "",
        $p["commentaire"] !== null && $p["commentaire"] !== "" ? $p["commentaire"] : "(vide)"
    );

    logAdmin($pdo, "paiement.supprimer", "paiement_credit", $paiementId, $details);

    $pdo->commit();

    $sep = strpos($retour, "?") === false ? "?" : "&";
    header("Location: {$retour}{$sep}paiement_supprime=1");
    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("supprimer-paiement: " . $e->getMessage());
    $sep = strpos($retour, "?") === false ? "?" : "&";
    header("Location: {$retour}{$sep}erreur=paiement_suppr");
    exit;
}
