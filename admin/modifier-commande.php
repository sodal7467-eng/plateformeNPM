<?php

require_once("includes/auth.php");
require_once "../config/database.php";


// Vérifier l'ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: commandes.php");
    exit;
}

$id = (int) $_GET["id"];


/*
|--------------------------------------------------------------------------
| Fonction : charger commande + facture + credit
|--------------------------------------------------------------------------
*/
function chargerContexteCommande(PDO $pdo, int $id): ?array
{
    $r = $pdo->prepare("
        SELECT
            commandes.*,
            utilisateurs.nom,
            utilisateurs.email
        FROM commandes
        INNER JOIN utilisateurs ON commandes.utilisateur_id = utilisateurs.id
        WHERE commandes.id = ?
    ");
    $r->execute([$id]);
    $commande = $r->fetch(PDO::FETCH_ASSOC);
    if (!$commande) return null;

    // Facture liee (si elle existe)
    $rf = $pdo->prepare("
        SELECT id, numero_facture, montant_total, montant_paye, reste_a_payer, statut
        FROM factures
        WHERE commande_id = ?
        LIMIT 1
    ");
    $rf->execute([$id]);
    $facture = $rf->fetch(PDO::FETCH_ASSOC) ?: null;

    // Credit lie (si il existe)
    $rc = $pdo->prepare("
        SELECT id, montant_total, montant_paye, reste_a_payer, statut
        FROM credits_clients
        WHERE commande_id = ?
        ORDER BY id DESC
        LIMIT 1
    ");
    $rc->execute([$id]);
    $credit = $rc->fetch(PDO::FETCH_ASSOC) ?: null;

    return ["commande" => $commande, "facture" => $facture, "credit" => $credit];
}


$ctx = chargerContexteCommande($pdo, $id);
if (!$ctx) {
    header("Location: commandes.php");
    exit;
}

$commande = $ctx["commande"];
$facture  = $ctx["facture"];
$credit   = $ctx["credit"];


/*
 * Verrouillage comptable :
 * une commande "Livree" est definitive.
 */
$commandeVerrouillee = ($commande["statut"] === "Livrée");


$erreur = "";
$message = "";


/*
|--------------------------------------------------------------------------
| Traitement du formulaire
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verifier();

    if ($commandeVerrouillee) {
        $erreur = "Cette commande est verrouillée (statut Livrée). Elle ne peut plus être modifiée.";
    } else {

        $statut = $_POST["statut"] ?? "";
        $montantPaiement = trim($_POST["montant_paiement"] ?? "");
        $reglerTout = isset($_POST["regler_tout"]);
        $commentairePaiement = trim($_POST["commentaire_paiement"] ?? "");

        // Statuts autorisés
        $statutsAutorises = ["En attente", "En préparation", "Expédiée", "Livrée", "Annulée"];

        if (!in_array($statut, $statutsAutorises)) {
            $erreur = "Statut invalide.";
        } else {

            try {
                $pdo->beginTransaction();

                // 1. UPDATE du statut de commande
                $up = $pdo->prepare("UPDATE commandes SET statut = ? WHERE id = ?");
                $up->execute([$statut, $id]);
                $commande["statut"] = $statut;

                // 2. Traitement du paiement (uniquement si facture existe et un montant est saisi)
                if ($facture) {

                    $resteActuel = (float) $facture["reste_a_payer"];

                    if ($reglerTout && $resteActuel > 0) {
                        $montantPaiement = $resteActuel;
                    } elseif ($montantPaiement === "") {
                        $montantPaiement = 0;
                    } else {
                        $montantPaiement = (float) $montantPaiement;
                    }

                    if ($montantPaiement < 0) {
                        throw new Exception("Le montant du paiement ne peut pas être négatif.");
                    }
                    if ($montantPaiement > $resteActuel + 0.001) {
                        throw new Exception("Le paiement ne peut pas dépasser le reste à payer.");
                    }

                    if ($montantPaiement > 0) {

                        // 2a) Mettre à jour la facture
                        $fLock = $pdo->prepare("SELECT id, montant_total, montant_paye FROM factures WHERE id = ? FOR UPDATE");
                        $fLock->execute([$facture["id"]]);
                        $f = $fLock->fetch(PDO::FETCH_ASSOC);

                        $nvPayeF = (float) $f["montant_paye"] + $montantPaiement;
                        if ($nvPayeF > (float) $f["montant_total"]) $nvPayeF = (float) $f["montant_total"];
                        $nvResteF = (float) $f["montant_total"] - $nvPayeF;
                        if ($nvResteF < 0) $nvResteF = 0;

                        if ($nvResteF <= 0)    $stF = "Payée";
                        elseif ($nvPayeF > 0)  $stF = "Partiellement payée";
                        else                   $stF = "Impayée";

                        $pdo->prepare("
                            UPDATE factures SET montant_paye = ?, reste_a_payer = ?, statut = ? WHERE id = ?
                        ")->execute([$nvPayeF, $nvResteF, $stF, $facture["id"]]);


                        // 2b) Mettre à jour ou créer le crédit
                        if ($credit) {
                            $nvPayeC = (float) $credit["montant_paye"] + $montantPaiement;
                            if ($nvPayeC > (float) $credit["montant_total"]) $nvPayeC = (float) $credit["montant_total"];
                            $nvResteC = (float) $credit["montant_total"] - $nvPayeC;
                            if ($nvResteC < 0) $nvResteC = 0;
                            $stC = $nvResteC <= 0 ? "Réglé" : "En cours";

                            $pdo->prepare("
                                UPDATE credits_clients SET montant_paye = ?, reste_a_payer = ?, statut = ? WHERE id = ?
                            ")->execute([$nvPayeC, $nvResteC, $stC, $credit["id"]]);

                            $pdo->prepare("
                                INSERT INTO paiements_credits (credit_id, montant, commentaire) VALUES (?, ?, ?)
                            ")->execute([
                                $credit["id"],
                                $montantPaiement,
                                $commentairePaiement !== "" ? $commentairePaiement : "Paiement enregistré depuis Modifier la commande"
                            ]);

                        } elseif ($nvResteF > 0) {
                            // Aucun crédit existant mais il reste un solde : on crée le crédit
                            $ins = $pdo->prepare("
                                INSERT INTO credits_clients
                                (utilisateur_id, commande_id, montant_total, montant_paye, reste_a_payer, statut)
                                VALUES (?, ?, ?, ?, ?, 'En cours')
                            ");
                            $ins->execute([
                                (int) $commande["utilisateur_id"],
                                $id,
                                (float) $f["montant_total"],
                                $nvPayeF,
                                $nvResteF
                            ]);

                            $nouveauCreditId = $pdo->lastInsertId();
                            $pdo->prepare("
                                INSERT INTO paiements_credits (credit_id, montant, commentaire) VALUES (?, ?, ?)
                            ")->execute([
                                $nouveauCreditId,
                                $montantPaiement,
                                $commentairePaiement !== "" ? $commentairePaiement : "Paiement initial enregistré depuis Modifier la commande"
                            ]);
                        }

                        $message = "Statut mis à jour et paiement de "
                                 . number_format($montantPaiement, 0, ",", " ")
                                 . " FCFA enregistré.";
                    } else {
                        $message = "Le statut de la commande a été modifié.";
                    }

                } else {
                    $message = "Le statut de la commande a été modifié.";
                }

                $pdo->commit();

                // Recharger le contexte (facture, credit)
                $ctx = chargerContexteCommande($pdo, $id);
                $commande = $ctx["commande"];
                $facture  = $ctx["facture"];
                $credit   = $ctx["credit"];
                $commandeVerrouillee = ($commande["statut"] === "Livrée");

            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $erreur = $e->getMessage();
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Modifier commande - NPM Admin
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body>


<?php include "includes/navbar.php"; ?>

<?php include "includes/sidebar.php"; ?>


<div class="admin-content">

    <div class="container-fluid p-4">


        <!-- En-tête -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1 class="fw-bold mb-1">
                    Modifier la commande <?= $commande["id"] ?>
                </h1>

                <p class="text-muted mb-0">
                    Modifier l'état de la commande.
                </p>

            </div>


            <a
                href="commande-details.php?id=<?= $commande["id"] ?>"
                class="btn btn-outline-dark"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Retour

            </a>

        </div>



        <!-- Messages -->

        <?php if (!empty($message)): ?>

            <div class="alert alert-success">

                <i class="bi bi-check-circle me-2"></i>

                <?= htmlspecialchars($message) ?>

            </div>

        <?php endif; ?>


        <?php if (!empty($erreur)): ?>

            <div class="alert alert-danger">

                <i class="bi bi-exclamation-circle me-2"></i>

                <?= htmlspecialchars($erreur) ?>

            </div>

        <?php endif; ?>




        <div class="row g-4">


            <!-- Informations commande -->

            <div class="col-lg-6">

                <div class="card border-0 shadow-sm">

                    <div class="card-body p-4">

                        <h4 class="fw-bold mb-4">

                            <i class="bi bi-receipt me-2"></i>

                            Informations de la commande

                        </h4>


                        <div class="mb-3">

                            <small class="text-muted">
                                Numéro
                            </small>

                            <div class="fw-bold">
                                <?= $commande["id"] ?>
                            </div>

                        </div>


                        <div class="mb-3">

                            <small class="text-muted">
                                Client
                            </small>

                            <div class="fw-bold">

                                <?= htmlspecialchars($commande["nom"]) ?>

                            </div>

                        </div>


                        <div class="mb-3">

                            <small class="text-muted">
                                Email
                            </small>

                            <div>

                                <?= htmlspecialchars($commande["email"]) ?>

                            </div>

                        </div>


                        <div class="mb-3">

                            <small class="text-muted">
                                Total
                            </small>

                            <div class="fw-bold text-orange">

                                <?= number_format(
                                    $commande["total"],
                                    0,
                                    ",",
                                    " "
                                ) ?>

                                FCFA

                            </div>

                        </div>


                        <div>

                            <small class="text-muted">
                                Date de commande
                            </small>

                            <div>

                                <?= date(
                                    "d/m/Y à H:i",
                                    strtotime($commande["date_commande"])
                                ) ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- Modification -->

            <div class="col-lg-6">

                <div class="card border-0 shadow-sm">

                    <div class="card-body p-4">


                        <h4 class="fw-bold mb-4">

                            <i class="bi bi-pencil-square me-2"></i>

                            État de la commande

                        </h4>


                        <?php if ($commandeVerrouillee): ?>
                            <div class="alert alert-success d-flex align-items-center" role="alert">
                                <i class="bi bi-shield-lock-fill me-2 fs-4"></i>
                                <div>
                                    <strong>Commande verrouillée</strong><br>
                                    <span class="small">
                                        Cette commande est marquée comme
                                        <strong>Livrée</strong> : elle est finalisée
                                        et ne peut plus être ni modifiée ni supprimée
                                        (verrouillage comptable).
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>


                        <form
                            action="modifier-commande.php?id=<?= $commande["id"] ?>"
                            method="POST"
                        >

                            <?= csrf_champ() ?>



                            <div class="mb-4">

                                <label
                                    for="statut"
                                    class="form-label fw-bold"
                                >

                                    Sélectionner le nouvel état

                                </label>


                                <select
                                    name="statut"
                                    id="statut"
                                    class="form-select form-select-lg"
                                    required
                                    <?= $commandeVerrouillee ? "disabled" : "" ?>
                                >

                                    <option
                                        value="En attente"
                                        <?= $commande["statut"] === "En attente" ? "selected" : "" ?>
                                    >

                                        🟡 En attente

                                    </option>


                                    <option
                                        value="En préparation"
                                        <?= $commande["statut"] === "En préparation" ? "selected" : "" ?>
                                    >

                                        🔵 En préparation

                                    </option>


                                    <option
                                        value="Expédiée"
                                        <?= $commande["statut"] === "Expédiée" ? "selected" : "" ?>
                                    >

                                        🚚 Expédiée

                                    </option>


                                    <option
                                        value="Livrée"
                                        <?= $commande["statut"] === "Livrée" ? "selected" : "" ?>
                                    >

                                        🟢 Livrée

                                    </option>


                                    <option
                                        value="Annulée"
                                        <?= $commande["statut"] === "Annulée" ? "selected" : "" ?>
                                    >

                                        🔴 Annulée

                                    </option>

                                </select>

                            </div>


                            <!-- ================================================
                                 BLOC : ETAT DU PAIEMENT
                                 (visible seulement si la commande a une facture)
                            ================================================= -->

                            <?php if ($facture): ?>

                                <?php
                                $totalFact = (float) $facture["montant_total"];
                                $payeFact  = (float) $facture["montant_paye"];
                                $resteFact = (float) $facture["reste_a_payer"];

                                $badgeClass = "bg-secondary";
                                if ($facture["statut"] === "Payée")                $badgeClass = "bg-success";
                                elseif ($facture["statut"] === "Partiellement payée") $badgeClass = "bg-warning text-dark";
                                elseif ($facture["statut"] === "Impayée")          $badgeClass = "bg-danger";
                                ?>

                                <div class="card border-0 shadow-sm mb-4 mt-4">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                        <strong>
                                            <i class="bi bi-cash-coin me-2"></i>
                                            État du paiement
                                        </strong>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= htmlspecialchars($facture["statut"]) ?>
                                        </span>
                                    </div>

                                    <div class="card-body">

                                        <!-- Recapitulatif -->
                                        <div class="row text-center g-3 mb-3">
                                            <div class="col-4">
                                                <small class="text-muted d-block">Montant total</small>
                                                <strong><?= number_format($totalFact, 0, ",", " ") ?> FCFA</strong>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">Déjà payé</small>
                                                <strong class="text-success"><?= number_format($payeFact, 0, ",", " ") ?> FCFA</strong>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">Reste à payer</small>
                                                <strong class="<?= $resteFact > 0 ? 'text-danger' : 'text-success' ?>">
                                                    <?= number_format($resteFact, 0, ",", " ") ?> FCFA
                                                </strong>
                                            </div>
                                        </div>

                                        <?php if ($resteFact > 0 && !$commandeVerrouillee): ?>

                                            <div class="alert alert-light border small mb-3">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Le client vous <strong>doit encore <?= number_format($resteFact, 0, ",", " ") ?> FCFA</strong>.
                                                Vous pouvez enregistrer un règlement (partiel ou total) ci-dessous.
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    Le client règle-t-il quelque chose maintenant ?
                                                </label>

                                                <div class="form-check mb-2">
                                                    <input
                                                        type="checkbox"
                                                        name="regler_tout"
                                                        id="reglerTout"
                                                        value="1"
                                                        class="form-check-input"
                                                    >
                                                    <label for="reglerTout" class="form-check-label fw-bold text-success">
                                                        Le client règle la totalité (<?= number_format($resteFact, 0, ",", " ") ?> FCFA)
                                                    </label>
                                                </div>

                                                <label class="form-label small text-muted">Ou saisir un montant partiel :</label>
                                                <div class="input-group">
                                                    <input
                                                        type="number"
                                                        name="montant_paiement"
                                                        class="form-control"
                                                        min="0"
                                                        max="<?= $resteFact ?>"
                                                        step="1"
                                                        placeholder="Ex : 5000"
                                                    >
                                                    <span class="input-group-text">FCFA</span>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label small text-muted">Commentaire (facultatif)</label>
                                                <input
                                                    type="text"
                                                    name="commentaire_paiement"
                                                    class="form-control"
                                                    placeholder="Ex : espèces / Wave / Orange Money…"
                                                >
                                            </div>

                                        <?php elseif ($resteFact <= 0): ?>
                                            <div class="alert alert-success mb-0">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Cette facture est entièrement <strong>réglée</strong>. Le client ne vous doit plus rien.
                                            </div>
                                        <?php endif; ?>

                                    </div>
                                </div>

                            <?php else: ?>

                                <div class="alert alert-warning mt-4 mb-4">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Aucune facture n'est encore associée à cette commande.
                                </div>

                            <?php endif; ?>


                            <button
                                type="submit"
                                class="btn btn-dark btn-lg w-100"
                                <?= $commandeVerrouillee ? "disabled" : "" ?>
                            >

                                <i class="bi <?= $commandeVerrouillee ? 'bi-lock' : 'bi-check-circle' ?> me-2"></i>

                                <?= $commandeVerrouillee ? "Commande verrouillée" : "Enregistrer les changements" ?>

                            </button>


                        </form>


                    </div>

                </div>

            </div>


        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>
