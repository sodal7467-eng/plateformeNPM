<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

    <!-- ================= NAVBAR ================= -->

    <?php include 'includes/navbar.php'; ?>


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

            <div class="row g-4">


                <!-- ================= OUTILS ================= -->

                <div class="col-md-6 col-lg-4">

                    <div class="category-large-card">

                        <div class="category-large-icon">

                            <i class="bi bi-tools"></i>

                        </div>

                        <div>

                            <span class="text-orange">
                                CATÉGORIE
                            </span>

                            <h3 class="fw-bold mt-2">
                                Outils
                            </h3>

                            <p class="text-muted">
                                Retrouvez tous les outils nécessaires
                                pour vos travaux de bricolage et de construction.
                            </p>

                            <a href="produits.php"
                               class="btn btn-dark">

                                Voir les produits
                                <i class="bi bi-arrow-right"></i>

                            </a>

                        </div>

                    </div>

                </div>


                <!-- ================= PLOMBERIE ================= -->

                <div class="col-md-6 col-lg-4">

                    <div class="category-large-card">

                        <div class="category-large-icon">

                            <i class="bi bi-droplet"></i>

                        </div>

                        <div>

                            <span class="text-orange">
                                CATÉGORIE
                            </span>

                            <h3 class="fw-bold mt-2">
                                Plomberie
                            </h3>

                            <p class="text-muted">
                                Tuyaux, robinets, raccords et accessoires
                                pour vos installations de plomberie.
                            </p>

                            <a href="produits.php"
                               class="btn btn-dark">

                                Voir les produits
                                <i class="bi bi-arrow-right"></i>

                            </a>

                        </div>

                    </div>

                </div>


                <!-- ================= ELECTRICITE ================= -->

                <div class="col-md-6 col-lg-4">

                    <div class="category-large-card">

                        <div class="category-large-icon">

                            <i class="bi bi-lightbulb"></i>

                        </div>

                        <div>

                            <span class="text-orange">
                                CATÉGORIE
                            </span>

                            <h3 class="fw-bold mt-2">
                                Électricité
                            </h3>

                            <p class="text-muted">
                                Câbles, prises, interrupteurs, ampoules
                                et matériel électrique.
                            </p>

                            <a href="produits.php"
                               class="btn btn-dark">

                                Voir les produits
                                <i class="bi bi-arrow-right"></i>

                            </a>

                        </div>

                    </div>

                </div>


                <!-- ================= PEINTURE ================= -->

                <div class="col-md-6 col-lg-4">

                    <div class="category-large-card">

                        <div class="category-large-icon">

                            <i class="bi bi-brush"></i>

                        </div>

                        <div>

                            <span class="text-orange">
                                CATÉGORIE
                            </span>

                            <h3 class="fw-bold mt-2">
                                Peinture
                            </h3>

                            <p class="text-muted">
                                Peintures, pinceaux, rouleaux et accessoires
                                pour donner vie à vos projets.
                            </p>

                            <a href="produits.php"
                               class="btn btn-dark">

                                Voir les produits
                                <i class="bi bi-arrow-right"></i>

                            </a>

                        </div>

                    </div>

                </div>


                <!-- ================= SERRURERIE ================= -->

                <div class="col-md-6 col-lg-4">

                    <div class="category-large-card">

                        <div class="category-large-icon">

                            <i class="bi bi-lock"></i>

                        </div>

                        <div>

                            <span class="text-orange">
                                CATÉGORIE
                            </span>

                            <h3 class="fw-bold mt-2">
                                Serrurerie
                            </h3>

                            <p class="text-muted">
                                Serrures, cadenas, poignées et accessoires
                                pour sécuriser vos espaces.
                            </p>

                            <a href="produits.php"
                               class="btn btn-dark">

                                Voir les produits
                                <i class="bi bi-arrow-right"></i>

                            </a>

                        </div>

                    </div>

                </div>


                <!-- ================= CONSTRUCTION ================= -->

                <div class="col-md-6 col-lg-4">

                    <div class="category-large-card">

                        <div class="category-large-icon">

                            <i class="bi bi-building"></i>

                        </div>

                        <div>

                            <span class="text-orange">
                                CATÉGORIE
                            </span>

                            <h3 class="fw-bold mt-2">
                                Construction
                            </h3>

                            <p class="text-muted">
                                Matériaux et accessoires indispensables
                                pour vos projets de construction.
                            </p>

                            <a href="produits.php"
                               class="btn btn-dark">

                                Voir les produits
                                <i class="bi bi-arrow-right"></i>

                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- ================= FOOTER ================= -->

    <?php include 'includes/footer.php'; ?>


    <!-- Bootstrap JS -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>

</body>

</html>