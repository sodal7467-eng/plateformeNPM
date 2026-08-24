<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Produits - Quincaillerie NPM</title>

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

    <!-- NAVBAR -->

    <?php include 'includes/navbar.php'; ?>


    <!-- =========================
         TITRE DE LA PAGE
    ========================== -->

    <section class="py-5 bg-light">

        <div class="container">

            <div class="text-center">

                <span class="text-orange fw-bold">
                    NOTRE BOUTIQUE
                </span>

                <h1 class="fw-bold mt-2">
                    Tous nos produits
                </h1>

                <p class="text-muted">
                    Découvrez notre sélection de produits
                    pour vos travaux.
                </p>

            </div>

        </div>

    </section>


    <!-- =========================
         RECHERCHE ET FILTRE
    ========================== -->

    <section class="py-4">

        <div class="container">

            <div class="row g-3 align-items-center">

                <!-- Recherche -->

                <div class="col-lg-6">

                    <form>

                        <div class="input-group">

                            <input
                                type="text"
                                class="form-control"
                                placeholder="Rechercher un produit...">

                            <button
                                class="btn btn-dark"
                                type="submit">

                                <i class="bi bi-search"></i>

                                Rechercher

                            </button>

                        </div>

                    </form>

                </div>


                <!-- Catégorie -->

                <div class="col-lg-3">

                    <select class="form-select">

                        <option selected>
                            Toutes les catégories
                        </option>

                        <option>
                            Outils
                        </option>

                        <option>
                            Plomberie
                        </option>

                        <option>
                            Électricité
                        </option>

                        <option>
                            Peinture
                        </option>

                    </select>

                </div>


                <!-- Trier -->

                <div class="col-lg-3">

                    <select class="form-select">

                        <option selected>
                            Trier par
                        </option>

                        <option>
                            Prix croissant
                        </option>

                        <option>
                            Prix décroissant
                        </option>

                        <option>
                            Plus récent
                        </option>

                    </select>

                </div>

            </div>

        </div>

    </section>


    <!-- =========================
         PRODUITS
    ========================== -->

    <section class="py-5">

        <div class="container">

            <div class="row g-4">


                <!-- PRODUIT 1 -->

                <div class="col-md-6 col-lg-3">

                    <div class="product-card">

                        <div class="product-image">

                            <img
                                src="assets/images/marteau.jpg"
                                alt="Marteau"
                                class="img-fluid">

                        </div>

                        <div class="p-3">

                            <small class="text-muted">
                                Outils
                            </small>

                            <h5 class="fw-bold mt-2">
                                Marteau professionnel
                            </h5>

                            <p class="text-muted small">
                                Marteau solide pour vos travaux.
                            </p>

                            <div class="d-flex justify-content-between align-items-center">

                                <strong class="text-orange">
                                    8 000 FCFA
                                </strong>

                                <a
                                    href="produit.php"
                                    class="btn btn-dark btn-sm">

                                    Voir

                                </a>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- PRODUIT 2 -->

                <div class="col-md-6 col-lg-3">

                    <div class="product-card">

                        <div class="product-image">

                            <img
                                src="assets/images/perceuse.jpg"
                                alt="Perceuse"
                                class="img-fluid">

                        </div>

                        <div class="p-3">

                            <small class="text-muted">
                                Outils
                            </small>

                            <h5 class="fw-bold mt-2">
                                Perceuse électrique
                            </h5>

                            <p class="text-muted small">
                                Perceuse idéale pour vos travaux.
                            </p>

                            <div class="d-flex justify-content-between align-items-center">

                                <strong class="text-orange">
                                    45 000 FCFA
                                </strong>

                                <a
                                    href="produit.php"
                                    class="btn btn-dark btn-sm">

                                    Voir

                                </a>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- PRODUIT 3 -->

                <div class="col-md-6 col-lg-3">

                    <div class="product-card">

                        <div class="product-image">

                            <img
                                src="assets/images/robinet.jpg"
                                alt="Robinet"
                                class="img-fluid">

                        </div>

                        <div class="p-3">

                            <small class="text-muted">
                                Plomberie
                            </small>

                            <h5 class="fw-bold mt-2">
                                Robinet moderne
                            </h5>

                            <p class="text-muted small">
                                Robinet de qualité pour votre maison.
                            </p>

                            <div class="d-flex justify-content-between align-items-center">

                                <strong class="text-orange">
                                    15 000 FCFA
                                </strong>

                                <a
                                    href="produit.php"
                                    class="btn btn-dark btn-sm">

                                    Voir

                                </a>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- PRODUIT 4 -->

                <div class="col-md-6 col-lg-3">

                    <div class="product-card">

                        <div class="product-image">

                            <img
                                src="assets/images/peinture.jpg"
                                alt="Peinture"
                                class="img-fluid">

                        </div>

                        <div class="p-3">

                            <small class="text-muted">
                                Peinture
                            </small>

                            <h5 class="fw-bold mt-2">
                                Peinture intérieure
                            </h5>

                            <p class="text-muted small">
                                Peinture pour vos travaux intérieurs.
                            </p>

                            <div class="d-flex justify-content-between align-items-center">

                                <strong class="text-orange">
                                    12 000 FCFA
                                </strong>

                                <a
                                    href="produit.php"
                                    class="btn btn-dark btn-sm">

                                    Voir

                                </a>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- PRODUIT 5 -->

                <div class="col-md-6 col-lg-3">

                    <div class="product-card">

                        <div class="product-image">

                            <img
                                src="assets/images/tournevis.jpg"
                                alt="Tournevis"
                                class="img-fluid">

                        </div>

                        <div class="p-3">

                            <small class="text-muted">
                                Outils
                            </small>

                            <h5 class="fw-bold mt-2">
                                Tournevis professionnel
                            </h5>

                            <p class="text-muted small">
                                Tournevis résistant et pratique.
                            </p>

                            <div class="d-flex justify-content-between align-items-center">

                                <strong class="text-orange">
                                    5 000 FCFA
                                </strong>

                                <a
                                    href="produit.php"
                                    class="btn btn-dark btn-sm">

                                    Voir

                                </a>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- PRODUIT 6 -->

                <div class="col-md-6 col-lg-3">

                    <div class="product-card">

                        <div class="product-image">

                            <img
                                src="assets/images/ampoule.jpg"
                                alt="Ampoule"
                                class="img-fluid">

                        </div>

                        <div class="p-3">

                            <small class="text-muted">
                                Électricité
                            </small>

                            <h5 class="fw-bold mt-2">
                                Ampoule LED
                            </h5>

                            <p class="text-muted small">
                                Ampoule LED économique.
                            </p>

                            <div class="d-flex justify-content-between align-items-center">

                                <strong class="text-orange">
                                    2 500 FCFA
                                </strong>

                                <a
                                    href="produit.php"
                                    class="btn btn-dark btn-sm">

                                    Voir

                                </a>

                            </div>

                        </div>

                    </div>

                </div>


            </div>

        </div>

    </section>


    <!-- FOOTER -->

    <?php include 'includes/footer.php'; ?>


    <!-- Bootstrap JS -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>

</body>

</html>