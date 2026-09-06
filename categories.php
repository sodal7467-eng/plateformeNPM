<?php

require_once "config/database.php";


// Récupérer toutes les catégories
$requete = $pdo->query("
    SELECT *
    FROM categories
    ORDER BY id DESC
");

$categories = $requete->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Catégories - Quincaillerie NPM</title>


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
        href="assets/css/style.css">

</head>


<body>


    <!-- ================= NAVBAR ================= -->

    <?php require_once "includes/navbar.php"; ?>


    <!-- ================= TITRE ================= -->

    <section class="categories-header py-5">

        <div class="container">

            <div class="text-center">

                <span class="text-orange fw-bold">
                    NOS CATÉGORIES
                </span>

                <h1 class="fw-bold mt-2">
                    Découvrez nos catégories
                </h1>

                <p class="text-muted">
                    Retrouvez facilement les produits dont vous avez besoin
                    pour vos travaux.
                </p>

            </div>

        </div>

    </section>


    <!-- ================= CATÉGORIES ================= -->

    <section class="py-5">

        <div class="container">

            <?php if (empty($categories)): ?>

                <!-- AUCUNE CATÉGORIE -->

                <div class="text-center py-5">

                    <i class="bi bi-tags fs-1 text-muted"></i>

                    <h3 class="mt-3">
                        Aucune catégorie disponible
                    </h3>

                    <p class="text-muted">
                        Les catégories seront bientôt disponibles.
                    </p>

                </div>


            <?php else: ?>


                <div class="row g-4">


                    <?php foreach ($categories as $categorie): ?>


                        <!-- ================= CATÉGORIE ================= -->

                        <div class="col-md-6 col-lg-4">

                            <div class="category-large-card">


                                <!-- ICÔNE -->

                                <div class="category-large-icon">

                                    <i class="bi bi-tag"></i>

                                </div>


                                <div>


                                    <span class="text-orange">
                                        CATÉGORIE
                                    </span>


                                    <!-- NOM -->

                                    <h3 class="fw-bold mt-2">

                                        <?= htmlspecialchars(
                                            $categorie["nom"]
                                        ) ?>

                                    </h3>


                                    <!-- DESCRIPTION -->

                                    <p class="text-muted">

                                        <?= htmlspecialchars(
                                            $categorie["description"] ?? ""
                                        ) ?>

                                    </p>


                                    <!-- BOUTON -->

                                    <a
                                        href="produits.php?categorie=<?= $categorie["id"] ?>"
                                        class="btn btn-dark">

                                        Voir les produits

                                        <i class="bi bi-arrow-right"></i>

                                    </a>


                                </div>

                            </div>

                        </div>


                    <?php endforeach; ?>


                </div>


            <?php endif; ?>

        </div>

    </section>


    <!-- ================= FOOTER ================= -->

    <?php require_once "includes/footer.php"; ?>


    <!-- Bootstrap JS -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>


</body>

</html>