<?php

require_once "../config/database.php";


// ===============================
// STATISTIQUES PRODUITS
// ===============================

// Nombre total de produits
$requeteProduits = $pdo->query(
    "SELECT COUNT(*) FROM produits"
);

$totalProduits = $requeteProduits->fetchColumn();


// Nombre de produits en stock faible
$requeteStock = $pdo->query(
    "SELECT COUNT(*) 
     FROM produits 
     WHERE stock <= 5"
);

$stockFaible = $requeteStock->fetchColumn();


// Valeur totale du stock
$requeteValeurStock = $pdo->query(
    "SELECT SUM(prix * stock) FROM produits"
);

$valeurStock = $requeteValeurStock->fetchColumn();


// Les 5 derniers produits
$requeteDerniersProduits = $pdo->query(
    "SELECT * FROM produits 
     ORDER BY id DESC 
     LIMIT 5"
);

$derniersProduits = $requeteDerniersProduits->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tableau de bord - NPM</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <!-- CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/style.css">

</head>

<body>


<!-- ================= HEADER ================= -->

<?php include "../includes/header.php"; ?>


<div class="container-fluid">

    <div class="row">


        <!-- ================= SIDEBAR ================= -->

        <div class="col-md-3 col-lg-2 p-0">

            <?php include "includes/sidebar.php"; ?>

        </div>


        <!-- ================= CONTENU ================= -->

        <main class="col-md-9 col-lg-10 p-4">


            <!-- Titre -->

            <div class="mb-4">

                <h1 class="fw-bold">

                    Tableau de bord

                </h1>

                <p class="text-muted">

                    Bienvenue dans l'espace d'administration de NPM.

                </p>

            </div>


            <!-- ================= STATISTIQUES ================= -->

            <div class="row g-4 mb-4">


                <!-- Produits -->

                <div class="col-md-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <p class="text-muted mb-1">
                                        Produits
                                    </p>

                                    <h2 class="fw-bold">
                                        <?= $totalProduits ?>
                                    </h2>

                                </div>

                                <div class="fs-1 text-primary">

                                    <i class="bi bi-box-seam"></i>

                                </div>

                            </div>

                            <a
                                href="produits.php"
                                class="text-decoration-none">

                                Voir les produits →

                            </a>

                        </div>

                    </div>

                </div>


                <!-- Commandes -->

                <div class="col-md-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <p class="text-muted mb-1">
                                        Commandes
                                    </p>

                                    <h2 class="fw-bold">
                                        0
                                    </h2>

                                </div>

                                <div class="fs-1 text-success">

                                    <i class="bi bi-cart-check"></i>

                                </div>

                            </div>

                            <a
                                href="commandes.php"
                                class="text-decoration-none">

                                Voir les commandes →

                            </a>

                        </div>

                    </div>

                </div>


                <!-- Clients -->

                <div class="col-md-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <p class="text-muted mb-1">
                                        Clients
                                    </p>

                                    <h2 class="fw-bold">
                                        0
                                    </h2>

                                </div>

                                <div class="fs-1 text-warning">

                                    <i class="bi bi-people"></i>

                                </div>

                            </div>

                            <a
                                href="clients.php"
                                class="text-decoration-none">

                                Voir les clients →

                            </a>

                        </div>

                    </div>

                </div>


                <!-- Valeur du stock -->

                <div class="col-md-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <p class="text-muted mb-1">
                                        Valeur du stock
                                    </p>

                                    <h4 class="fw-bold">

                                        <?= number_format(
                                            $valeurStock ?? 0,
                                            0,
                                            ",",
                                            " "
                                        ) ?>

                                        FCFA

                                    </h4>

                                </div>

                                <div class="fs-1 text-danger">

                                    <i class="bi bi-boxes"></i>

                                </div>

                            </div>

                            <a
                                href="stock.php"
                                class="text-decoration-none">

                                Gérer le stock →

                            </a>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ================= DEUX COLONNES ================= -->

            <div class="row g-4">


                <!-- Derniers produits -->

                <div class="col-lg-8">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white py-3">

                            <div class="d-flex
                                        justify-content-between
                                        align-items-center">

                                <h5 class="fw-bold mb-0">

                                    <i class="bi bi-box-seam"></i>

                                    Derniers produits

                                </h5>

                                <a
                                    href="ajouter-produit.php"
                                    class="btn btn-dark btn-sm">

                                    <i class="bi bi-plus"></i>

                                    Ajouter

                                </a>

                            </div>

                        </div>


                        <div class="card-body p-0">

                            <?php if (empty($derniersProduits)): ?>

                                <div class="text-center p-5">

                                    <i class="bi bi-box fs-1 text-muted"></i>

                                    <p class="text-muted mt-3">

                                        Aucun produit n'a encore été ajouté.

                                    </p>

                                    <a
                                        href="ajouter-produit.php"
                                        class="btn btn-dark">

                                        Ajouter mon premier produit

                                    </a>

                                </div>

                            <?php else: ?>

                                <div class="table-responsive">

                                    <table class="table table-hover mb-0">

                                        <thead>

                                            <tr>

                                                <th>Produit</th>

                                                <th>Catégorie</th>

                                                <th>Prix</th>

                                                <th>Stock</th>

                                            </tr>

                                        </thead>

                                        <tbody>

                                            <?php foreach (
                                                $derniersProduits
                                                as $produit
                                            ): ?>

                                                <tr>

                                                    <td>

                                                        <strong>

                                                            <?= htmlspecialchars(
                                                                $produit["nom"]
                                                            ) ?>

                                                        </strong>

                                                    </td>

                                                    <td>

                                                        <?= htmlspecialchars(
                                                            $produit["categorie"]
                                                        ) ?>

                                                    </td>

                                                    <td>

                                                        <?= number_format(
                                                            $produit["prix"],
                                                            0,
                                                            ",",
                                                            " "
                                                        ) ?>

                                                        FCFA

                                                    </td>

                                                    <td>

                                                        <?php if (
                                                            $produit["stock"] <= 5
                                                        ): ?>

                                                            <span
                                                                class="badge bg-danger">

                                                                <?= $produit["stock"] ?>

                                                            </span>

                                                        <?php else: ?>

                                                            <span
                                                                class="badge bg-success">

                                                                <?= $produit["stock"] ?>

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

                </div>


                <!-- Stock faible -->

                <div class="col-lg-4">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white py-3">

                            <h5 class="fw-bold mb-0">

                                <i class="bi bi-exclamation-triangle text-danger"></i>

                                Stock faible

                            </h5>

                        </div>


                        <div class="card-body">

                            <?php if ($stockFaible == 0): ?>

                                <div class="text-center py-4">

                                    <i
                                        class="bi bi-check-circle fs-1 text-success">
                                    </i>

                                    <p class="text-muted mt-3 mb-0">

                                        Aucun produit en stock faible.

                                    </p>

                                </div>

                            <?php else: ?>

                                <div class="text-center">

                                    <h2 class="fw-bold text-danger">

                                        <?= $stockFaible ?>

                                    </h2>

                                    <p class="text-muted">

                                        produit(s) avec un stock faible.

                                    </p>

                                    <a
                                        href="stock.php"
                                        class="btn btn-outline-danger">

                                        Voir le stock

                                    </a>

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>


        </main>

    </div>

</div>


<!-- ================= FOOTER ================= -->

<?php include "includes/footer.php"; ?>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>