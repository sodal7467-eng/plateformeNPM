<?php

require_once("includes/auth.php");
require_once("../config/database.php");

/*
|--------------------------------------------------------------------------
| STATISTIQUES
|--------------------------------------------------------------------------
*/

// Nombre de produits
$requete = $pdo->query("SELECT COUNT(*) FROM produits");
$totalProduits = $requete->fetchColumn();

// Nombre de clients
$requete = $pdo->query("
    SELECT COUNT(*)
    FROM utilisateurs
    WHERE role = 'client'
");
$totalClients = $requete->fetchColumn();

// Nombre de commandes
$requete = $pdo->query("SELECT COUNT(*) FROM commandes");
$totalCommandes = $requete->fetchColumn();

// Chiffre d'affaires
$requete = $pdo->query("
    SELECT COALESCE(SUM(total), 0)
    FROM commandes
");
$chiffreAffaires = $requete->fetchColumn();

// Produits avec stock faible
$requete = $pdo->query("
    SELECT COUNT(*)
    FROM produits
    WHERE stock <= 5
");
$stockFaible = $requete->fetchColumn();


/*
|--------------------------------------------------------------------------
| DERNIÈRES COMMANDES
|--------------------------------------------------------------------------
*/

$requete = $pdo->query("
    SELECT *
    FROM commandes
    ORDER BY id DESC
    LIMIT 5
");

$dernieresCommandes = $requete->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| PRODUITS EN STOCK FAIBLE
|--------------------------------------------------------------------------
*/

$requete = $pdo->query("
    SELECT *
    FROM produits
    WHERE stock <= 5
    ORDER BY stock ASC
    LIMIT 5
");

$produitsStockFaible = $requete->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Quincaillerie NPM</title>

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

<?php require_once("includes/navbar.php"); ?>

<?php require_once("includes/sidebar.php"); ?>

<main class="admin-content">

    <div class="container-fluid py-5 px-4">

            <!-- EN-TÊTE -->

            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">

                <div>

                    <h1 class="fw-bold">
                        Tableau de bord
                    </h1>

                    <p class="text-muted mb-0">
                        Bienvenue dans l'espace d'administration de Quincaillerie NPM.
                    </p>

                </div>


                <!-- ACTIONS RAPIDES -->

                <div class="d-flex flex-wrap gap-2">

                    <a
                        href="ajouter-produit.php"
                        class="btn btn-dark btn-sm"
                    >
                        <i class="bi bi-plus-circle me-1"></i>
                        Ajouter un produit
                    </a>

                    <a
                        href="categories.php"
                        class="btn btn-outline-dark btn-sm"
                    >
                        <i class="bi bi-tags me-1"></i>
                        Catégories
                    </a>

                    <a
                        href="commandes.php"
                        class="btn btn-outline-dark btn-sm"
                    >
                        <i class="bi bi-cart-check me-1"></i>
                        Commandes
                    </a>

                    <a
                        href="messages.php"
                        class="btn btn-outline-dark btn-sm"
                    >
                        <i class="bi bi-envelope me-1"></i>
                        Messages
                    </a>

                </div>

            </div>


            <!-- CARTES STATISTIQUES -->

            <div class="row g-4 mb-4">


                <!-- PRODUITS -->

                <div class="col-md-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <p class="text-muted mb-1">
                                        Produits
                                    </p>

                                    <h2 class="fw-bold mb-0">
                                        <?= $totalProduits ?>
                                    </h2>

                                </div>

                                <div class="fs-1 text-secondary">
                                    <i class="bi bi-box-seam"></i>
                                </div>

                            </div>

                            <a
                                href="produits.php"
                                class="small text-decoration-none"
                            >
                                Voir les produits →
                            </a>

                        </div>

                    </div>

                </div>


                <!-- CLIENTS -->

                <div class="col-md-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <p class="text-muted mb-1">
                                        Clients
                                    </p>

                                    <h2 class="fw-bold mb-0">
                                        <?= $totalClients ?>
                                    </h2>

                                </div>

                                <div class="fs-1 text-secondary">
                                    <i class="bi bi-people"></i>
                                </div>

                            </div>

                            <a
                                href="clients.php"
                                class="small text-decoration-none"
                            >
                                Voir les clients →
                            </a>

                        </div>

                    </div>

                </div>


                <!-- COMMANDES -->

                <div class="col-md-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <p class="text-muted mb-1">
                                        Commandes
                                    </p>

                                    <h2 class="fw-bold mb-0">
                                        <?= $totalCommandes ?>
                                    </h2>

                                </div>

                                <div class="fs-1 text-secondary">
                                    <i class="bi bi-cart-check"></i>
                                </div>

                            </div>

                            <a
                                href="commandes.php"
                                class="small text-decoration-none"
                            >
                                Voir les commandes →
                            </a>

                        </div>

                    </div>

                </div>


                <!-- CHIFFRE D'AFFAIRES -->

                <div class="col-md-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div class="flex-grow-1" style="min-width: 0;">

                                    <p class="text-muted mb-1">
                                        Chiffre d'affaires
                                    </p>

                                    <h4 class="fw-bold mb-0" style="word-break: break-word;">

                                        <?= number_format(
                                            $chiffreAffaires,
                                            0,
                                            ',',
                                            ' '
                                        ) ?>

                                        <small class="fw-normal text-muted">FCFA</small>

                                    </h4>

                                </div>

                                <div class="fs-2 text-secondary ms-2">
                                    <i class="bi bi-cash-stack"></i>
                                </div>

                            </div>

                            <span class="small text-muted">
                                Total des commandes
                            </span>

                        </div>

                    </div>

                </div>

            </div>


            <!-- DEUXIÈME SECTION -->

            <div class="row g-4">


                <!-- DERNIÈRES COMMANDES -->

                <div class="col-lg-8">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white py-3">

                            <div class="d-flex justify-content-between align-items-center">

                                <h5 class="fw-bold mb-0">
                                    Dernières commandes
                                </h5>

                                <a
                                    href="commandes.php"
                                    class="btn btn-sm btn-outline-dark"
                                >
                                    Toutes les commandes
                                </a>

                            </div>

                        </div>


                        <div class="card-body p-0">

                            <div class="table-responsive">

                                <table class="table table-hover align-middle mb-0">

                                    <thead class="table-dark">

                                        <tr>

                                            <th>Client</th>

                                            <th>Total</th>

                                            <th>Statut</th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                    <?php if (empty($dernieresCommandes)): ?>

                                        <tr>

                                            <td
                                                colspan="3"
                                                class="text-center text-muted py-4"
                                            >
                                                Aucune commande pour le moment.
                                            </td>

                                        </tr>

                                    <?php else: ?>

                                        <?php foreach ($dernieresCommandes as $commande): ?>

                                            <tr>

                                                <td>
                                                    <?= htmlspecialchars(
                                                        $commande["nom"] ?? "Client"
                                                    ) ?>
                                                </td>

                                                <td class="fw-semibold">

                                                    <?= number_format(
                                                        $commande["total"] ?? 0,
                                                        0,
                                                        ',',
                                                        ' '
                                                    ) ?>

                                                    FCFA

                                                </td>

                                                <td>

                                                    <span class="badge bg-secondary">

                                                        <?= htmlspecialchars(
                                                            $commande["statut"] ?? "En attente"
                                                        ) ?>

                                                    </span>

                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    <?php endif; ?>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- STOCK FAIBLE -->

                <div class="col-lg-4">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white py-3">

                            <h5 class="fw-bold mb-0">
                                Stock faible
                            </h5>

                        </div>


                        <div class="card-body">

                            <?php if ($stockFaible == 0): ?>

                                <div class="text-center py-4">

                                    <i class="bi bi-check-circle fs-1 text-success"></i>

                                    <p class="mt-3 mb-0 text-muted">
                                        Tous les produits ont un stock suffisant.
                                    </p>

                                </div>

                            <?php else: ?>

                                <?php foreach ($produitsStockFaible as $produit): ?>

                                    <div class="d-flex justify-content-between align-items-center border-bottom py-3">

                                        <div>

                                            <div class="fw-semibold">

                                                <?= htmlspecialchars(
                                                    $produit["nom"]
                                                ) ?>

                                            </div>

                                            <small class="text-muted">
                                                Stock restant
                                            </small>

                                        </div>


                                        <span class="badge bg-danger">

                                            <?= $produit["stock"] ?>

                                        </span>

                                    </div>

                                <?php endforeach; ?>


                                <div class="mt-3">

                                    <a
                                        href="stocks.php"
                                        class="btn btn-outline-dark btn-sm w-100"
                                    >
                                        Gérer les stocks
                                    </a>

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>

    </div>

</main>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>