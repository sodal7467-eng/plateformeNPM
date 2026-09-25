<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("includes/auth.php");
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| RÉCUPÉRER LES UTILISATEURS
|--------------------------------------------------------------------------
*/

$requete = $pdo->query("
    SELECT
        id,
        nom,
        email,
        telephone,
        type_client,
        date_creation
    FROM utilisateurs
    WHERE role = 'client'
    ORDER BY id DESC
");

$utilisateurs = $requete->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| STATISTIQUES
|--------------------------------------------------------------------------
*/

$totalUtilisateurs = count($utilisateurs);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Clients - NPM Admin</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <!-- CSS NPM -->

    <link
        rel="stylesheet"
        href="../assets/css/style.css">

</head>


<body>


    <!-- =========================
         NAVBAR ADMIN
    ========================== -->

    <?php require_once("includes/navbar.php"); ?>


    <!-- =========================
         SIDEBAR ADMIN
    ========================== -->

    <?php require_once("includes/sidebar.php"); ?>


    <!-- =========================
         CONTENU ADMIN
    ========================== -->

    <main class="admin-content">

        <div class="container-fluid py-5 px-4">


            <!-- =========================
                 EN-TÊTE
            ========================== -->

            <div class="mb-4">

                <h1 class="fw-bold mb-2">
                    Gestion des clients
                </h1>

                <p class="text-muted mb-3">
                    Consultez les clients inscrits en ligne et ceux ajoutés depuis la boutique.
                </p>


                <div class="d-flex flex-wrap align-items-center gap-3">

                    <!-- TOTAL CLIENTS -->

                    <div class="bg-dark text-white rounded-3 px-4 py-3">

                        <div class="d-flex align-items-center gap-3">

                            <i class="bi bi-people fs-3 text-warning"></i>

                            <div>

                                <small class="text-light">
                                    Total clients
                                </small>

                                <h4 class="mb-0 fw-bold">
                                    <?= $totalUtilisateurs ?>
                                </h4>

                            </div>

                        </div>

                    </div>


                    <!-- ACTIONS -->

                    <div class="d-flex flex-wrap gap-2 ms-auto">

                        <a href="ajouter-client.php" class="btn btn-dark">
                            <i class="bi bi-person-plus me-1"></i>
                            Ajouter un client
                        </a>

                        <a href="nouvelle-vente.php" class="btn btn-warning">
                            <i class="bi bi-cart-plus me-1"></i>
                            Nouvelle vente
                        </a>

                    </div>

                </div>

            </div>


            <!-- =========================
                 LISTE DES CLIENTS
            ========================== -->

            <div class="card border-0 shadow-sm">


                <!-- HEADER -->

                <div class="card-header bg-white py-3">

                    <div class="d-flex justify-content-between align-items-center">

                        <h5 class="fw-bold mb-0">

                            <i class="bi bi-people me-2"></i>

                            Liste des clients

                        </h5>

                        <span class="badge bg-warning text-dark">

                            <?= $totalUtilisateurs ?> client(s)

                        </span>

                    </div>

                </div>


                <!-- CONTENU -->

                <div class="card-body p-0">


                    <?php if (empty($utilisateurs)): ?>


                        <!-- AUCUN CLIENT -->

                        <div class="text-center py-5 px-3">

                            <i
                                class="bi bi-person-x fs-1 text-muted">
                            </i>

                            <h4 class="mt-3">
                                Aucun client
                            </h4>

                            <p class="text-muted mb-0">

                                Aucun utilisateur ne s'est encore inscrit
                                sur la plateforme.

                            </p>

                        </div>


                    <?php else: ?>


                        <!-- TABLEAU -->

                        <div class="table-responsive">

                            <table class="table table-hover align-middle mb-0">


                                <!-- EN-TÊTE -->

                                <thead class="table-dark">

                                    <tr>

                                        <th>
                                            Client
                                        </th>

                                        <th>
                                            Email
                                        </th>

                                        <th>
                                            Téléphone
                                        </th>

                                        <th>
                                            Type
                                        </th>

                                        <th>
                                            Date d'inscription
                                        </th>

                                        <th class="text-center">
                                            Actions
                                        </th>

                                    </tr>

                                </thead>


                                <!-- CLIENTS -->

                                <tbody>

                                    <?php foreach ($utilisateurs as $utilisateur): ?>

                                        <tr>


                                            <!-- NOM -->

                                            <td>

                                                <div class="d-flex align-items-center gap-3">

                                                    <div
                                                        class="rounded-circle bg-dark text-warning d-flex align-items-center justify-content-center"
                                                        style="width: 42px; height: 42px;">

                                                        <i class="bi bi-person-fill"></i>

                                                    </div>


                                                    <div>

                                                        <strong>

                                                            <?= htmlspecialchars(
                                                                $utilisateur["nom"]
                                                            ) ?>

                                                        </strong>

                                                        <div class="text-muted small">

                                                            Client NPM

                                                        </div>

                                                    </div>

                                                </div>

                                            </td>


                                            <!-- EMAIL -->

                                            <td>

                                                <?php if (!empty($utilisateur["email"])): ?>

                                                    <a
                                                        href="mailto:<?= htmlspecialchars($utilisateur["email"]) ?>"
                                                        class="text-decoration-none">

                                                        <i class="bi bi-envelope me-1"></i>

                                                        <?= htmlspecialchars(
                                                            $utilisateur["email"]
                                                        ) ?>

                                                    </a>

                                                <?php else: ?>

                                                    <span class="text-muted">—</span>

                                                <?php endif; ?>

                                            </td>


                                            <!-- TÉLÉPHONE -->

                                            <td>

                                                <?php if (!empty($utilisateur["telephone"])): ?>

                                                    <i class="bi bi-telephone me-1"></i>

                                                    <?= htmlspecialchars($utilisateur["telephone"]) ?>

                                                <?php else: ?>

                                                    <span class="text-muted">—</span>

                                                <?php endif; ?>

                                            </td>


                                            <!-- TYPE -->

                                            <td>

                                                <?php if (($utilisateur["type_client"] ?? "en_ligne") === "boutique"): ?>

                                                    <span class="badge bg-dark">
                                                        Boutique
                                                    </span>

                                                <?php else: ?>

                                                    <span class="badge bg-secondary">
                                                        En ligne
                                                    </span>

                                                <?php endif; ?>

                                            </td>


                                            <!-- DATE -->

                                            <td>

                                                <?php if (!empty($utilisateur["date_creation"])): ?>

                                                    <span>

                                                        <i class="bi bi-calendar3 me-1"></i>

                                                        <?= date(
                                                            "d/m/Y",
                                                            strtotime(
                                                                $utilisateur["date_creation"]
                                                            )
                                                        ) ?>

                                                    </span>

                                                <?php else: ?>

                                                    —

                                                <?php endif; ?>

                                            </td>


                                            <!-- ACTIONS -->

                                            <td class="text-center">

                                                <div class="d-inline-flex gap-2">

                                                    <a
                                                        href="details-client.php?id=<?= $utilisateur["id"] ?>"
                                                        class="btn btn-sm btn-outline-dark"
                                                        title="Voir le profil"
                                                    >
                                                        <i class="bi bi-eye"></i>
                                                    </a>

                                                    <a
                                                        href="modifier-client.php?id=<?= $utilisateur["id"] ?>"
                                                        class="btn btn-sm btn-outline-warning"
                                                        title="Modifier le client"
                                                    >
                                                        <i class="bi bi-pencil"></i>
                                                    </a>

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


        </div>

    </main>


    <!-- =========================
         BOOTSTRAP JS
    ========================== -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>


</body>

</html>