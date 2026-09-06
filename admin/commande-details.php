<?php

require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Vérifier l'identifiant de la commande
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: commandes.php");
    exit;
}

$commandeId = (int) $_GET["id"];


/*
|--------------------------------------------------------------------------
| Récupérer les informations de la commande et du client
|--------------------------------------------------------------------------
*/

$requeteCommande = $pdo->prepare("
    SELECT
        commandes.id,
        commandes.utilisateur_id,
        commandes.total,
        commandes.statut,
        commandes.date_commande,
        utilisateurs.nom AS client_nom,
        utilisateurs.email AS client_email
    FROM commandes
    INNER JOIN utilisateurs
        ON commandes.utilisateur_id = utilisateurs.id
    WHERE commandes.id = ?
");

$requeteCommande->execute([$commandeId]);

$commande = $requeteCommande->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Vérifier que la commande existe
|--------------------------------------------------------------------------
*/

if (!$commande) {
    header("Location: commandes.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Récupérer les produits de la commande
|--------------------------------------------------------------------------
*/

$requeteDetails = $pdo->prepare("
    SELECT
        commande_details.id,
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


/*
|--------------------------------------------------------------------------
| Calculer le nombre total de produits
|--------------------------------------------------------------------------
*/

$totalArticles = 0;

foreach ($details as $detail) {
    $totalArticles += (int) $detail["quantite"];
}


/*
|--------------------------------------------------------------------------
| Déterminer la couleur du statut
|--------------------------------------------------------------------------
*/

$statut = $commande["statut"];

if ($statut === "En attente") {

    $classeStatut = "bg-warning text-dark";
    $iconeStatut = "bi-hourglass-split";

} elseif ($statut === "Confirmée") {

    $classeStatut = "bg-primary";
    $iconeStatut = "bi-check2-circle";

} elseif ($statut === "Expédiée") {

    $classeStatut = "bg-info text-dark";
    $iconeStatut = "bi-truck";

} elseif ($statut === "Livrée") {

    $classeStatut = "bg-success";
    $iconeStatut = "bi-check-circle";

} elseif ($statut === "Annulée") {

    $classeStatut = "bg-danger";
    $iconeStatut = "bi-x-circle";

} else {

    $classeStatut = "bg-secondary";
    $iconeStatut = "bi-question-circle";
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
        Commande #<?= $commande["id"] ?> - NPM Admin
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


    <!-- CSS du projet -->

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body>


<!-- =========================================================
     NAVBAR ADMIN
========================================================== -->

<?php require_once("includes/navbar.php"); ?>


<!-- =========================================================
     SIDEBAR ADMIN
========================================================== -->

<?php require_once("includes/sidebar.php"); ?>


<!-- =========================================================
     CONTENU
========================================================== -->

<main class="admin-content">

    <div class="container-fluid py-5 px-4">


        <!-- =====================================================
             EN-TÊTE
        ====================================================== -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <a
                    href="commandes.php"
                    class="btn btn-outline-secondary btn-sm mb-3"
                >
                    <i class="bi bi-arrow-left me-1"></i>
                    Retour aux commandes
                </a>

                <h1 class="fw-bold mb-2">

                    Commande #<?= $commande["id"] ?>

                </h1>

                <p class="text-muted mb-0">

                    Détails de la commande

                </p>

            </div>


            <!-- Statut -->

            <div>

                <span class="badge <?= $classeStatut ?> fs-6 px-3 py-2">

                    <i class="bi <?= $iconeStatut ?> me-1"></i>

                    <?= htmlspecialchars($statut) ?>

                </span>

            </div>

        </div>



        <!-- =====================================================
             INFORMATIONS COMMANDE + CLIENT
        ====================================================== -->

        <div class="row g-4 mb-4">


            <!-- Informations commande -->

            <div class="col-lg-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-header bg-white py-3">

                        <h5 class="fw-bold mb-0">

                            <i class="bi bi-receipt me-2"></i>

                            Informations de la commande

                        </h5>

                    </div>


                    <div class="card-body">

                        <div class="row g-3">


                            <div class="col-sm-6">

                                <small class="text-muted d-block">
                                    Numéro
                                </small>

                                <strong>
                                    #<?= $commande["id"] ?>
                                </strong>

                            </div>


                            <div class="col-sm-6">

                                <small class="text-muted d-block">
                                    Statut
                                </small>

                                <strong>
                                    <?= htmlspecialchars($statut) ?>
                                </strong>

                            </div>


                            <div class="col-sm-6">

                                <small class="text-muted d-block">
                                    Nombre d'articles
                                </small>

                                <strong>
                                    <?= $totalArticles ?>
                                </strong>

                            </div>


                            <div class="col-sm-6">

                                <small class="text-muted d-block">
                                    Date
                                </small>

                                <strong>

                                    <?php if (!empty($commande["date_commande"])): ?>

                                        <?= date(
                                            "d/m/Y H:i",
                                            strtotime($commande["date_commande"])
                                        ) ?>

                                    <?php else: ?>

                                        —

                                    <?php endif; ?>

                                </strong>

                            </div>


                            <div class="col-12">

                                <hr>

                                <small class="text-muted d-block">
                                    Total de la commande
                                </small>

                                <h3 class="fw-bold text-warning mb-0">

                                    <?= number_format(
                                        (float) $commande["total"],
                                        0,
                                        ",",
                                        " "
                                    ) ?>

                                    FCFA

                                </h3>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- Informations client -->

            <div class="col-lg-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-header bg-white py-3">

                        <h5 class="fw-bold mb-0">

                            <i class="bi bi-person me-2"></i>

                            Informations du client

                        </h5>

                    </div>


                    <div class="card-body">


                        <div class="d-flex align-items-center gap-3 mb-4">

                            <div
                                class="rounded-circle bg-dark text-warning d-flex align-items-center justify-content-center"
                                style="width: 60px; height: 60px;"
                            >

                                <i class="bi bi-person-fill fs-4"></i>

                            </div>


                            <div>

                                <h5 class="fw-bold mb-1">

                                    <?= htmlspecialchars(
                                        $commande["client_nom"]
                                    ) ?>

                                </h5>

                                <span class="text-muted">

                                    Client NPM

                                </span>

                            </div>

                        </div>


                        <div class="mb-3">

                            <small class="text-muted d-block">
                                Email
                            </small>

                            <a
                                href="mailto:<?= htmlspecialchars(
                                    $commande["client_email"]
                                ) ?>"
                                class="text-decoration-none"
                            >

                                <i class="bi bi-envelope me-1"></i>

                                <?= htmlspecialchars(
                                    $commande["client_email"]
                                ) ?>

                            </a>

                        </div>


                        <div>

                            <small class="text-muted d-block">
                                Identifiant client
                            </small>

                            <strong>
                                #<?= $commande["utilisateur_id"] ?>
                            </strong>

                        </div>


                    </div>

                </div>

            </div>

        </div>



        <!-- =====================================================
             PRODUITS COMMANDÉS
        ====================================================== -->

        <div class="card border-0 shadow-sm">


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

                    <!-- Aucun produit -->

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

                                    <th>
                                        Produit
                                    </th>

                                    <th>
                                        Prix unitaire
                                    </th>

                                    <th>
                                        Quantité
                                    </th>

                                    <th>
                                        Sous-total
                                    </th>

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


                                        <!-- Produit -->

                                        <td>

                                            <div class="d-flex align-items-center gap-3">


                                                <?php if (!empty($detail["produit_image"])): ?>

                                                    <img
                                                        src="../uploads/<?= htmlspecialchars(
                                                            $detail["produit_image"]
                                                        ) ?>"
                                                        alt="<?= htmlspecialchars(
                                                            $detail["produit_nom"]
                                                        ) ?>"
                                                        style="
                                                            width: 60px;
                                                            height: 60px;
                                                            object-fit: cover;
                                                            border-radius: 8px;
                                                        "
                                                    >

                                                <?php else: ?>

                                                    <div
                                                        class="bg-light rounded d-flex align-items-center justify-content-center"
                                                        style="
                                                            width: 60px;
                                                            height: 60px;
                                                        "
                                                    >

                                                        <i class="bi bi-image text-muted fs-4"></i>

                                                    </div>

                                                <?php endif; ?>


                                                <div>

                                                    <strong>

                                                        <?= htmlspecialchars(
                                                            $detail["produit_nom"]
                                                        ) ?>

                                                    </strong>

                                                    <div class="text-muted small">

                                                        Produit NPM

                                                    </div>

                                                </div>

                                            </div>

                                        </td>



                                        <!-- Prix -->

                                        <td>

                                            <?= number_format(
                                                $prix,
                                                0,
                                                ",",
                                                " "
                                            ) ?>

                                            FCFA

                                        </td>



                                        <!-- Quantité -->

                                        <td>

                                            <span class="badge bg-light text-dark border">

                                                <?= $quantite ?>

                                            </span>

                                        </td>



                                        <!-- Sous-total -->

                                        <td>

                                            <strong>

                                                <?= number_format(
                                                    $sousTotal,
                                                    0,
                                                    ",",
                                                    " "
                                                ) ?>

                                                FCFA

                                            </strong>

                                        </td>


                                    </tr>


                                <?php endforeach; ?>


                            </tbody>


                            <!-- TOTAL -->

                            <tfoot>

                                <tr>

                                    <td colspan="3" class="text-end fw-bold">

                                        TOTAL

                                    </td>

                                    <td>

                                        <h5 class="fw-bold text-warning mb-0">

                                            <?= number_format(
                                                (float) $commande["total"],
                                                0,
                                                ",",
                                                " "
                                            ) ?>

                                            FCFA

                                        </h5>

                                    </td>

                                </tr>

                            </tfoot>


                        </table>

                    </div>


                <?php endif; ?>


            </div>

        </div>


    </div>

</main>



<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>