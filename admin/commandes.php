<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Récupérer toutes les commandes
|--------------------------------------------------------------------------
| On relie commandes à utilisateurs pour récupérer le nom du client.
*/

$requete = $pdo->query("
    SELECT
        commandes.id,
        commandes.utilisateur_id,
        commandes.total,
        commandes.statut,
        commandes.type_vente,
        commandes.date_commande,
        utilisateurs.nom AS client_nom,
        utilisateurs.email AS client_email
    FROM commandes
    INNER JOIN utilisateurs
        ON commandes.utilisateur_id = utilisateurs.id
    ORDER BY commandes.id DESC
");

$commandes = $requete->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Statistiques
|--------------------------------------------------------------------------
*/

$totalCommandes = count($commandes);

$totalMontant = 0;
$commandesEnAttente = 0;
$commandesLivrees = 0;

foreach ($commandes as $commande) {

    $totalMontant += (float) $commande["total"];

    if ($commande["statut"] === "En attente") {
        $commandesEnAttente++;
    }

    if ($commande["statut"] === "Livrée") {
        $commandesLivrees++;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Commandes - NPM Admin</title>

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


<!-- Navbar admin -->
<?php require_once("includes/navbar.php"); ?>


<!-- Sidebar admin -->
<?php require_once("includes/sidebar.php"); ?>
<?php require_once("includes/auth.php");?> 


<main class="admin-content">

    <div class="container-fluid py-5 px-4">


        <!-- =========================================================
             EN-TÊTE
        ========================================================== -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1 class="fw-bold mb-2">
                    Gestion des commandes
                </h1>

                <p class="text-muted mb-0">
                    Consultez et gérez les commandes passées sur la plateforme NPM.
                </p>

            </div>


            <a href="nouvelle-vente.php" class="btn btn-warning">
                <i class="bi bi-cart-plus me-1"></i>
                Nouvelle vente en boutique
            </a>

        </div>



        <!-- =========================================================
             STATISTIQUES
        ========================================================== -->

        <div class="row g-4 mb-4">


            <!-- Total commandes -->

            <div class="col-md-6 col-xl-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <p class="text-muted mb-1">
                                    Total commandes
                                </p>

                                <h3 class="fw-bold mb-0">
                                    <?= $totalCommandes ?>
                                </h3>

                            </div>

                            <div
                                class="bg-dark text-warning rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 50px; height: 50px;"
                            >
                                <i class="bi bi-cart-check fs-4"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- En attente -->

            <div class="col-md-6 col-xl-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <p class="text-muted mb-1">
                                    En attente
                                </p>

                                <h3 class="fw-bold mb-0">
                                    <?= $commandesEnAttente ?>
                                </h3>

                            </div>

                            <div
                                class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 50px; height: 50px;"
                            >
                                <i class="bi bi-hourglass-split fs-4"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- Livrées -->

            <div class="col-md-6 col-xl-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <p class="text-muted mb-1">
                                    Livrées
                                </p>

                                <h3 class="fw-bold mb-0">
                                    <?= $commandesLivrees ?>
                                </h3>

                            </div>

                            <div
                                class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 50px; height: 50px;"
                            >
                                <i class="bi bi-check-circle fs-4"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- Chiffre d'affaires -->

            <div class="col-md-6 col-xl-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <p class="text-muted mb-1">
                                    Montant total
                                </p>

                                <h3 class="fw-bold mb-0">
                                    <?= number_format($totalMontant, 0, ",", " ") ?>
                                    FCFA
                                </h3>

                            </div>

                            <div
                                class="bg-dark text-warning rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 50px; height: 50px;"
                            >
                                <i class="bi bi-currency-dollar fs-4"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- =========================================================
             LISTE DES COMMANDES
        ========================================================== -->

        <div class="card border-0 shadow-sm">


            <!-- En-tête du tableau -->

            <div class="card-header bg-white py-3">

                <div class="d-flex justify-content-between align-items-center">

                    <h5 class="fw-bold mb-0">

                        <i class="bi bi-receipt me-2"></i>

                        Liste des commandes

                    </h5>

                    <span class="badge bg-warning text-dark">

                        <?= $totalCommandes ?> commande(s)

                    </span>

                </div>

            </div>



            <div class="card-body p-0">


                <?php if (empty($commandes)): ?>

                    <!-- Aucun commande -->

                    <div class="text-center py-5 px-3">

                        <i class="bi bi-cart-x fs-1 text-muted"></i>

                        <h4 class="mt-3">
                            Aucune commande
                        </h4>

                        <p class="text-muted mb-0">

                            Aucune commande n'a encore été passée
                            sur la plateforme.

                        </p>

                    </div>


                <?php else: ?>


                    <!-- Tableau -->

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">


                            <thead class="table-dark">

                                <tr>

                                    <th>
                                        Client
                                    </th>

                                    <th>
                                        Email
                                    </th>

                                    <th>
                                        Type
                                    </th>

                                    <th>
                                        Total
                                    </th>

                                    <th>
                                        Statut
                                    </th>

                                    <th>
                                        Date
                                    </th>

                                    <th class="text-center">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php foreach ($commandes as $commande): ?>

                                    <tr>


                                        <!-- Client -->

                                        <td>

                                            <div class="d-flex align-items-center gap-3">

                                                <div
                                                    class="rounded-circle bg-dark text-warning d-flex align-items-center justify-content-center"
                                                    style="width: 40px; height: 40px;"
                                                >

                                                    <i class="bi bi-person-fill"></i>

                                                </div>


                                                <div>

                                                    <strong>

                                                        <?= htmlspecialchars(
                                                            $commande["client_nom"]
                                                        ) ?>

                                                    </strong>

                                                </div>

                                            </div>

                                        </td>



                                        <!-- Email -->

                                        <td>

                                            <?php if (!empty($commande["client_email"])): ?>

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

                                            <?php else: ?>

                                                <span class="text-muted">—</span>

                                            <?php endif; ?>

                                        </td>



                                        <!-- Type -->

                                        <td>

                                            <?php if (($commande["type_vente"] ?? "en_ligne") === "boutique"): ?>

                                                <span class="badge bg-dark">
                                                    Boutique
                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-secondary">
                                                    En ligne
                                                </span>

                                            <?php endif; ?>

                                        </td>



                                        <!-- Total -->

                                        <td>

                                            <strong>

                                                <?= number_format(
                                                    (float) $commande["total"],
                                                    0,
                                                    ",",
                                                    " "
                                                ) ?>

                                                FCFA

                                            </strong>

                                        </td>



                                        <!-- Statut -->

                                        <td>


                                            <?php

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


                                            <span class="badge <?= $classeStatut ?>">

                                                <i class="bi <?= $iconeStatut ?> me-1"></i>

                                                <?= htmlspecialchars($statut) ?>

                                            </span>


                                        </td>



                                        <!-- Date -->

                                        <td>

                                            <?php if (!empty($commande["date_commande"])): ?>

                                                <span>

                                                    <i class="bi bi-calendar3 me-1"></i>

                                                    <?= date(
                                                        "d/m/Y H:i",
                                                        strtotime($commande["date_commande"])
                                                    ) ?>

                                                </span>

                                            <?php else: ?>

                                                —

                                            <?php endif; ?>

                                        </td>



<td class="text-center">

    <!-- Voir les détails -->
    <a
        href="commande-details.php?id=<?= $commande["id"] ?>"
        class="btn btn-sm btn-outline-dark me-1"
        title="Voir les détails"
    >
        <i class="bi bi-eye"></i>
    </a>

    <!-- Modifier le statut -->
    <a
        href="modifier-commande.php?id=<?= $commande["id"] ?>"
        class="btn btn-sm btn-warning"
        title="Modifier le statut"
    >
        <i class="bi bi-pencil"></i>
    </a>

</td>

                                    </tr>

                                <?php endforeach; ?>


                            </tbody>

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