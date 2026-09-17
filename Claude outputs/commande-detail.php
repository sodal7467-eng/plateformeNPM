<?php

session_start();

require_once "config/database.php";


// =========================================================
// VÉRIFIER LA CONNEXION
// =========================================================

if (!isset($_SESSION["id"])) {

    header("Location: connexion.php");
    exit;

}

$idClient = (int) $_SESSION["id"];


// =========================================================
// VÉRIFIER L'IDENTIFIANT DE LA COMMANDE
// =========================================================

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: mes-commende.php");
    exit;

}

$commandeId = (int) $_GET["id"];


// =========================================================
// RÉCUPÉRER LA COMMANDE (UNIQUEMENT SI ELLE APPARTIENT AU CLIENT)
// =========================================================

$requeteCommande = $pdo->prepare("
    SELECT
        commandes.id,
        commandes.total,
        commandes.statut,
        commandes.date_commande
    FROM commandes
    WHERE commandes.id = ?
        AND commandes.utilisateur_id = ?
");

$requeteCommande->execute([$commandeId, $idClient]);

$commande = $requeteCommande->fetch(PDO::FETCH_ASSOC);

if (!$commande) {

    header("Location: mes-commende.php");
    exit;

}


// =========================================================
// RÉCUPÉRER LA FACTURE LIÉE (S'IL Y EN A UNE)
// =========================================================

$requeteFacture = $pdo->prepare("
    SELECT
        id,
        numero_facture,
        montant_total,
        montant_paye,
        reste_a_payer,
        statut
    FROM factures
    WHERE commande_id = ?
    LIMIT 1
");

$requeteFacture->execute([$commandeId]);

$facture = $requeteFacture->fetch(PDO::FETCH_ASSOC);


// =========================================================
// RÉCUPÉRER LES PRODUITS DE LA COMMANDE
// =========================================================

$requeteDetails = $pdo->prepare("
    SELECT
        commande_details.quantite,
        commande_details.prix,
        produits.nom AS produit_nom,
        produits.image AS produit_image
    FROM commande_details
    INNER JOIN produits
        ON commande_details.produit_id = produits.id
    WHERE commande_details.commande_id = ?
    ORDER BY commande_details.id ASC
");

$requeteDetails->execute([$commandeId]);

$details = $requeteDetails->fetchAll(PDO::FETCH_ASSOC);

$totalArticles = 0;

foreach ($details as $detail) {
    $totalArticles += (int) $detail["quantite"];
}


// =========================================================
// STATUT DU PAIEMENT
// =========================================================

$statutPaiement = $facture["statut"] ?? "Impayée";

$montantPaye = (float) ($facture["montant_paye"] ?? 0);

$resteAPayer = (float) ($facture["reste_a_payer"] ?? $commande["total"]);


// =========================================================
// STATUT DE LA COMMANDE (COULEUR DU BADGE)
// =========================================================

$statutCommande = $commande["statut"];

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
        Commande #<?= $commande["id"] ?> - Quincaillerie NPM
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <!-- CSS du site -->

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>


<body>


<?php include "includes/navbar.php"; ?>


<main>

<section class="py-5">

<div class="container">


<!-- =========================================================
     EN-TÊTE
========================================================= -->

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

    <div>

        <h1 class="fw-bold mb-1">

            <i class="bi bi-bag-check me-2"></i>

            Commande #<?= $commande["id"] ?>

        </h1>

        <p class="text-muted mb-0">

            Passée le
            <?= date("d/m/Y à H:i", strtotime($commande["date_commande"])) ?>

        </p>

    </div>

    <a
        href="mes-commende.php"
        class="btn btn-outline-dark"
    >

        <i class="bi bi-arrow-left me-1"></i>

        Retour à mes commandes

    </a>

</div>



<!-- =========================================================
     RÉSUMÉ
========================================================= -->

<div class="row g-4 mb-4">

    <div class="col-md-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body text-center p-4">

                <i class="bi bi-receipt fs-1 text-warning"></i>

                <h4 class="fw-bold mt-3">

                    <?= number_format($commande["total"], 0, ",", " ") ?>

                    <small class="fs-6">FCFA</small>

                </h4>

                <p class="text-muted mb-0">
                    Total de la commande
                </p>

            </div>

        </div>

    </div>


    <div class="col-md-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body text-center p-4">

                <i class="bi bi-truck fs-1 text-primary"></i>

                <h4 class="fw-bold mt-3">

                    <?php if ($statutCommande === "Livrée"): ?>

                        <span class="badge bg-success fs-6">Livrée</span>

                    <?php elseif ($statutCommande === "Expédiée"): ?>

                        <span class="badge bg-primary fs-6">Expédiée</span>

                    <?php elseif ($statutCommande === "Annulée"): ?>

                        <span class="badge bg-danger fs-6">Annulée</span>

                    <?php elseif ($statutCommande === "En préparation"): ?>

                        <span class="badge bg-warning text-dark fs-6">En préparation</span>

                    <?php else: ?>

                        <span class="badge bg-secondary fs-6">
                            <?= htmlspecialchars($statutCommande) ?>
                        </span>

                    <?php endif; ?>

                </h4>

                <p class="text-muted mb-0">
                    Statut de la commande
                </p>

            </div>

        </div>

    </div>


    <div class="col-md-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body text-center p-4">

                <i class="bi bi-cash-stack fs-1 text-danger"></i>

                <h4 class="fw-bold mt-3">

                    <?= number_format($resteAPayer, 0, ",", " ") ?>

                    <small class="fs-6">FCFA</small>

                </h4>

                <p class="text-muted mb-0">
                    Reste à payer
                </p>

            </div>

        </div>

    </div>

</div>



<!-- =========================================================
     PRODUITS COMMANDÉS
========================================================= -->

<div class="card border-0 shadow-sm mb-4">

    <div class="card-header bg-white py-3">

        <div class="d-flex justify-content-between align-items-center">

            <h5 class="fw-bold mb-0">

                <i class="bi bi-box-seam me-2"></i>

                Produits commandés

            </h5>

            <span class="badge bg-dark">

                <?= $totalArticles ?> article(s)

            </span>

        </div>

    </div>


    <div class="card-body p-0">

        <?php if (empty($details)): ?>

            <div class="text-center py-5">

                <i class="bi bi-box-seam fs-1 text-muted"></i>

                <h5 class="mt-3">
                    Aucun produit
                </h5>

                <p class="text-muted mb-0">
                    Cette commande ne contient aucun produit.
                </p>

            </div>

        <?php else: ?>

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-dark">

                        <tr>

                            <th>Produit</th>

                            <th class="text-center">Prix unitaire</th>

                            <th class="text-center">Quantité</th>

                            <th class="text-end">Sous-total</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($details as $detail): ?>

                            <?php

                            $prix = (float) $detail["prix"];
                            $quantite = (int) $detail["quantite"];
                            $sousTotal = $prix * $quantite;

                            ?>

                            <tr>

                                <td>

                                    <div class="d-flex align-items-center gap-3">

                                        <div
                                            style="width:50px;height:50px;flex-shrink:0;"
                                        >

                                            <?php if (!empty($detail["produit_image"])): ?>

                                                <img
                                                    src="uploads/<?= htmlspecialchars($detail["produit_image"]) ?>"
                                                    alt="<?= htmlspecialchars($detail["produit_nom"]) ?>"
                                                    style="width:100%;height:100%;object-fit:cover;border-radius:8px;"
                                                >

                                            <?php else: ?>

                                                <div
                                                    class="d-flex align-items-center justify-content-center bg-light rounded"
                                                    style="width:100%;height:100%;"
                                                >

                                                    <i class="bi bi-image text-muted"></i>

                                                </div>

                                            <?php endif; ?>

                                        </div>

                                        <strong>

                                            <?= htmlspecialchars($detail["produit_nom"]) ?>

                                        </strong>

                                    </div>

                                </td>

                                <td class="text-center">

                                    <?= number_format($prix, 0, ",", " ") ?> FCFA

                                </td>

                                <td class="text-center">

                                    <?= $quantite ?>

                                </td>

                                <td class="text-end">

                                    <strong>

                                        <?= number_format($sousTotal, 0, ",", " ") ?> FCFA

                                    </strong>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </div>

</div>



<!-- =========================================================
     BOUTONS
========================================================= -->

<div class="text-center">

    <?php if ($facture): ?>

        <a
            href="facture-client.php?id=<?= $commande["id"] ?>"
            class="btn btn-warning me-2"
        >

            <i class="bi bi-receipt me-1"></i>

            Voir la facture

        </a>

    <?php else: ?>

        <button
            type="button"
            class="btn btn-warning me-2"
            disabled
            title="Aucune facture disponible pour cette commande"
        >

            <i class="bi bi-receipt me-1"></i>

            Voir la facture

        </button>

    <?php endif; ?>

    <a
        href="mes-commende.php"
        class="btn btn-outline-dark"
    >

        <i class="bi bi-arrow-left me-1"></i>

        Retour à mes commandes

    </a>

</div>


</div>

</section>

</main>


<?php include "includes/footer.php"; ?>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>
