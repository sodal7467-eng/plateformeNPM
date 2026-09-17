<?php

session_start();

require_once "config/database.php";


// =========================================================
// VÉRIFIER SI LE CLIENT EST CONNECTÉ
// =========================================================

if (!isset($_SESSION["id"])) {

    header("Location: connexion.php");
    exit;

}

$idClient = (int) $_SESSION["id"];


// =========================================================
// VÉRIFIER QUE LE CLIENT EXISTE
// =========================================================

$requete = $pdo->prepare("
    SELECT id, nom, email
    FROM utilisateurs
    WHERE id = ?
");

$requete->execute([$idClient]);

$utilisateur = $requete->fetch(PDO::FETCH_ASSOC);

if (!$utilisateur) {

    session_unset();
    session_destroy();

    header("Location: connexion.php");
    exit;

}


// =========================================================
// RÉCUPÉRER LES COMMANDES DU CLIENT
// =========================================================

$requeteCommandes = $pdo->prepare("
    SELECT
        commandes.id,
        commandes.total,
        commandes.statut,
        commandes.date_commande,

        factures.id AS facture_id,
        factures.numero_facture,
        factures.montant_paye,
        factures.reste_a_payer,
        factures.statut AS statut_paiement

    FROM commandes

    LEFT JOIN factures
        ON factures.commande_id = commandes.id

    WHERE commandes.utilisateur_id = ?

    ORDER BY commandes.date_commande DESC
");

$requeteCommandes->execute([$idClient]);

$commandes = $requeteCommandes->fetchAll(PDO::FETCH_ASSOC);

$nombreCommandes = count($commandes);

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
        Mes commandes - Quincaillerie NPM
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

<div class="text-center mb-5">

    <div
        class="rounded-circle bg-warning d-inline-flex
        align-items-center justify-content-center"
        style="width:80px;height:80px;"
    >

        <i class="bi bi-bag-check fs-1 text-dark"></i>

    </div>


    <h1 class="fw-bold mt-3">

        Mes commandes

    </h1>


    <p class="text-muted">

        L'historique de vos commandes NPM.

    </p>

</div>



<!-- =========================================================
     MES COMMANDES
========================================================= -->

<div class="card border-0 shadow-sm">

    <div class="card-body p-4 p-md-5">


        <div class="d-flex justify-content-between
                    align-items-center mb-4">

            <h3 class="fw-bold mb-0">

                <i class="bi bi-bag-check me-2"></i>

                Mes commandes

            </h3>


            <span class="badge bg-dark px-3 py-2">

                <?= $nombreCommandes ?>

                commande(s)

            </span>

        </div>



        <?php if (empty($commandes)): ?>


            <!-- Aucune commande -->

            <div class="text-center py-5">

                <i class="bi bi-cart-x fs-1 text-muted"></i>

                <h5 class="mt-3">

                    Aucune commande

                </h5>

                <p class="text-muted">

                    Vous n'avez pas encore passé de commande.

                </p>


                <a
                    href="produits.php"
                    class="btn btn-warning"
                >

                    <i class="bi bi-shop me-1"></i>

                    Voir les produits

                </a>

            </div>


        <?php else: ?>


            <!-- Tableau commandes -->

            <div class="table-responsive">

                <table class="table table-hover align-middle">


                    <thead class="table-dark">

                        <tr>

                            <th>
                                Date
                            </th>

                            <th>
                                Total
                            </th>

                            <th>
                                Payé
                            </th>

                            <th>
                                Reste
                            </th>

                            <th>
                                Paiement
                            </th>

                            <th>
                                Statut
                            </th>

                            <th class="text-center">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php foreach ($commandes as $commande): ?>


                        <?php

                        $statutPaiement =
                            $commande["statut_paiement"]
                            ?? "Impayée";


                        $statutCommande =
                            $commande["statut"];

                        ?>


                        <tr>


                            <!-- Date -->

                            <td>

                                <?= date(
                                    "d/m/Y",
                                    strtotime(
                                        $commande["date_commande"]
                                    )
                                ) ?>

                            </td>


                            <!-- Total -->

                            <td>

                                <strong>

                                    <?= number_format(
                                        $commande["total"],
                                        0,
                                        ",",
                                        " "
                                    ) ?>

                                    FCFA

                                </strong>

                            </td>


                            <!-- Payé -->

                            <td class="text-success">

                                <strong>

                                    <?= number_format(
                                        $commande["montant_paye"] ?? 0,
                                        0,
                                        ",",
                                        " "
                                    ) ?>

                                    FCFA

                                </strong>

                            </td>


                            <!-- Reste -->

                            <td class="text-danger">

                                <strong>

                                    <?= number_format(
                                        $commande["reste_a_payer"]
                                        ?? $commande["total"],
                                        0,
                                        ",",
                                        " "
                                    ) ?>

                                    FCFA

                                </strong>

                            </td>


                            <!-- Statut paiement -->

                            <td>


                                <?php if (
                                    $statutPaiement === "Payée"
                                ): ?>

                                    <span class="badge bg-success">

                                        <i class="bi bi-check-circle"></i>

                                        Payée

                                    </span>


                                <?php elseif (
                                    $statutPaiement === "Partiellement payée"
                                ): ?>

                                    <span class="badge bg-warning text-dark">

                                        <i class="bi bi-clock"></i>

                                        Partiellement payée

                                    </span>


                                <?php else: ?>

                                    <span class="badge bg-danger">

                                        <i class="bi bi-exclamation-circle"></i>

                                        Impayée

                                    </span>

                                <?php endif; ?>


                            </td>


                            <!-- Statut commande -->

                            <td>

                                <?php if (
                                    $statutCommande === "Livrée"
                                ): ?>

                                    <span class="badge bg-success">

                                        Livrée

                                    </span>


                                <?php elseif (
                                    $statutCommande === "Expédiée"
                                ): ?>

                                    <span class="badge bg-primary">

                                        Expédiée

                                    </span>


                                <?php elseif (
                                    $statutCommande === "Annulée"
                                ): ?>

                                    <span class="badge bg-danger">

                                        Annulée

                                    </span>


                                <?php elseif (
                                    $statutCommande === "En préparation"
                                ): ?>

                                    <span class="badge bg-warning text-dark">

                                        En préparation

                                    </span>


                                <?php else: ?>

                                    <span class="badge bg-secondary">

                                        <?= htmlspecialchars(
                                            $statutCommande
                                        ) ?>

                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- Actions -->

                            <td class="text-center">

                                <div class="d-flex justify-content-center gap-2">

                                    <a
                                        href="commande-detail.php?id=<?= $commande["id"] ?>"
                                        class="btn btn-sm btn-outline-dark"
                                        title="Voir le détail"
                                    >

                                        <i class="bi bi-eye"></i>

                                    </a>

                                    <?php if (!empty($commande["facture_id"])): ?>

                                        <a
                                            href="facture-client.php?id=<?= $commande["id"] ?>"
                                            class="btn btn-sm btn-outline-warning"
                                            title="Voir la facture"
                                        >

                                            <i class="bi bi-receipt"></i>

                                        </a>

                                    <?php else: ?>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-warning"
                                            disabled
                                            title="Aucune facture disponible"
                                        >

                                            <i class="bi bi-receipt"></i>

                                        </button>

                                    <?php endif; ?>

                                </div>

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

<div class="text-center mt-5">


    <a
        href="produits.php"
        class="btn btn-warning me-2"
    >

        <i class="bi bi-shop me-1"></i>

        Continuer mes achats

    </a>


    <a
        href="deconnexion.php"
        class="btn btn-danger"
    >

        <i class="bi bi-box-arrow-right me-1"></i>

        Déconnexion

    </a>

</div>


</div>

</section>

</main>



<?php include "includes/footer.php"; ?>



<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>
