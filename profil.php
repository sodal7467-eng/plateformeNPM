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
// RÉCUPÉRER LES INFORMATIONS DU CLIENT
// =========================================================

$requete = $pdo->prepare("
    SELECT id, nom, email, date_creation
    FROM utilisateurs
    WHERE id = ?
");

$requete->execute([$idClient]);

$utilisateur = $requete->fetch(PDO::FETCH_ASSOC);


// Vérifier que le client existe

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


// =========================================================
// CALCULER QUELQUES STATISTIQUES
// =========================================================

$nombreCommandes = count($commandes);

$totalCommandes = 0;
$totalPaye = 0;
$totalReste = 0;

foreach ($commandes as $commande) {

    $totalCommandes += (float) $commande["total"];

    $totalPaye += (float) ($commande["montant_paye"] ?? 0);

    $totalReste += (float) (
        $commande["reste_a_payer"] ?? $commande["total"]
    );
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
        <?= htmlspecialchars($utilisateur["nom"]) ?> - Quincaillerie NPM
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
     BIENVENUE
========================================================= -->

<div class="text-center mb-5">

    <div
        class="rounded-circle bg-warning d-inline-flex
        align-items-center justify-content-center"
        style="width:80px;height:80px;"
    >

        <i class="bi bi-person-fill fs-1 text-dark"></i>

    </div>


    <h1 class="fw-bold mt-3">

        Bienvenue,
        <?= htmlspecialchars($utilisateur["nom"]) ?> 👋

    </h1>


    <p class="text-muted">

        Voici votre espace client NPM.

    </p>

</div>



<!-- =========================================================
     STATISTIQUES
========================================================= -->

<div class="row g-4 mb-5">


    <!-- Nombre commandes -->

    <div class="col-md-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body text-center p-4">

                <i class="bi bi-bag-check fs-1 text-warning"></i>

                <h3 class="fw-bold mt-3">

                    <?= $nombreCommandes ?>

                </h3>

                <p class="text-muted mb-0">

                    Commande(s)

                </p>

            </div>

        </div>

    </div>



    <!-- Total commandes -->

    <div class="col-md-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body text-center p-4">

                <i class="bi bi-cash-stack fs-1 text-success"></i>

                <h3 class="fw-bold mt-3">

                    <?= number_format(
                        $totalCommandes,
                        0,
                        ",",
                        " "
                    ) ?>

                    <small class="fs-6">FCFA</small>

                </h3>

                <p class="text-muted mb-0">

                    Total des commandes

                </p>

            </div>

        </div>

    </div>



    <!-- Reste à payer -->

    <div class="col-md-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body text-center p-4">

                <i class="bi bi-wallet2 fs-1 text-danger"></i>

                <h3 class="fw-bold mt-3">

                    <?= number_format(
                        $totalReste,
                        0,
                        ",",
                        " "
                    ) ?>

                    <small class="fs-6">FCFA</small>

                </h3>

                <p class="text-muted mb-0">

                    Reste à payer

                </p>

            </div>

        </div>

    </div>

</div>



<!-- =========================================================
     INFORMATIONS CLIENT
========================================================= -->

<div class="card border-0 shadow-sm mb-5">

    <div class="card-body p-4 p-md-5">


        <h3 class="fw-bold mb-4">

            <i class="bi bi-person-circle me-2"></i>

            <?= htmlspecialchars($utilisateur["nom"]) ?>

        </h3>


        <div class="row g-4">


            <!-- Nom -->

            <div class="col-md-6">

                <div class="border rounded p-3">

                    <small class="text-muted">
                        Nom
                    </small>

                    <h5 class="mb-0 mt-1">

                        <?= htmlspecialchars(
                            $utilisateur["nom"]
                        ) ?>

                    </h5>

                </div>

            </div>


            <!-- Email -->

            <div class="col-md-6">

                <div class="border rounded p-3">

                    <small class="text-muted">
                        Adresse email
                    </small>

                    <h5 class="mb-0 mt-1">

                        <?= htmlspecialchars(
                            $utilisateur["email"]
                        ) ?>

                    </h5>

                </div>

            </div>


            <!-- Date inscription -->

            <div class="col-md-6">

                <div class="border rounded p-3">

                    <small class="text-muted">
                        Client depuis
                    </small>

                    <h5 class="mb-0 mt-1">

                        <?= date(
                            "d/m/Y",
                            strtotime(
                                $utilisateur["date_creation"]
                            )
                        ) ?>

                    </h5>

                </div>

            </div>


            <!-- Numéro client -->

            <div class="col-md-6">

                <div class="border rounded p-3">

                    <small class="text-muted">
                        Numéro client
                    </small>

                    <h5 class="mb-0 mt-1">

                        #<?= htmlspecialchars(
                            $utilisateur["id"]
                        ) ?>

                    </h5>

                </div>

            </div>


        </div>

    </div>

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
                                Commande
                            </th>

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
                                Commande
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


                            <!-- Numéro -->

                            <td>

                                <strong>

                                    #<?= $commande["id"] ?>

                                </strong>

                            </td>


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