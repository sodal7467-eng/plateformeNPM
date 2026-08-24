<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Accueil - Quincaillerie NPM</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Notre CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

    <!-- ================= NAVBAR ================= -->

    <?php include 'includes/navbar.php'; ?>


    <!-- ================= HERO ================= -->

    <section class="hero">

        <div class="container">

            <div class="row align-items-center min-vh-75">

                <!-- Texte -->

                <div class="col-lg-6">

                    <span class="badge bg-warning text-dark mb-3">
                        Bienvenue chez NPM
                    </span>

                    <h1 class="display-4 fw-bold">
                        Tout pour construire,
                        <span class="text-orange">
                            rénover et bricoler
                        </span>
                    </h1>

                    <p class="lead text-muted my-4">
                        Découvrez notre sélection de produits de
                        qualité pour vos travaux de construction,
                        rénovation et bricolage.
                    </p>

                    <div class="d-flex gap-3 flex-wrap">

                        <a href="produits.php"
                           class="btn btn-dark btn-lg">
                            Découvrir nos produits
                        </a>

                        <a href="categories.php"
                           class="btn btn-outline-dark btn-lg">
                            Voir les catégories
                        </a>

                    </div>

                </div>


                <!-- Image -->

                <div class="col-lg-6 text-center mt-5 mt-lg-0">

                    <div class="hero-image">

                        <img
                            src="assets/images/images-quincaillerie.png"
                            class="img-fluid">

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- ================= CATÉGORIES ================= -->

    <section class="py-5">

        <div class="container">

            <div class="text-center mb-5">

                <span class="text-orange fw-bold">
                    NOS CATÉGORIES
                </span>

                <h2 class="fw-bold mt-2">
                    Trouvez ce dont vous avez besoin
                </h2>

                <p class="text-muted">
                    Découvrez nos différentes catégories de produits.
                </p>

            </div>


            <div class="row g-4">

                <!-- Catégorie 1 -->

                <div class="col-md-6 col-lg-3">

                    <div class="category-card">

                        <div class="category-icon">
                            <i class="bi bi-tools"></i>
                        </div>

                        <h5 class="fw-bold">
                            Outils
                        </h5>

                        <p class="text-muted">
                            Marteaux, tournevis,
                            pinces et autres outils.
                        </p>

                        <a href="categories.php"
                           class="text-decoration-none">
                            Voir les produits →
                        </a>

                    </div>

                </div>


                <!-- Catégorie 2 -->

                <div class="col-md-6 col-lg-3">

                    <div class="category-card">

                        <div class="category-icon">
                            <i class="bi bi-droplet"></i>
                        </div>

                        <h5 class="fw-bold">
                            Plomberie
                        </h5>

                        <p class="text-muted">
                            Robinets, tuyaux et accessoires
                            de plomberie.
                        </p>

                        <a href="categories.php"
                           class="text-decoration-none">
                            Voir les produits →
                        </a>

                    </div>

                </div>


                <!-- Catégorie 3 -->

                <div class="col-md-6 col-lg-3">

                    <div class="category-card">

                        <div class="category-icon">
                            <i class="bi bi-lightbulb"></i>
                        </div>

                        <h5 class="fw-bold">
                            Électricité
                        </h5>

                        <p class="text-muted">
                            Câbles, ampoules, prises
                            et matériel électrique.
                        </p>

                        <a href="categories.php"
                           class="text-decoration-none">
                            Voir les produits →
                        </a>

                    </div>

                </div>


                <!-- Catégorie 4 -->

                <div class="col-md-6 col-lg-3">

                    <div class="category-card">

                        <div class="category-icon">
                            <i class="bi bi-brush"></i>
                        </div>

                        <h5 class="fw-bold">
                            Peinture
                        </h5>

                        <p class="text-muted">
                            Peintures, pinceaux et
                            accessoires.
                        </p>

                        <a href="categories.php"
                           class="text-decoration-none">
                            Voir les produits →
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- ================= PRODUITS POPULAIRES ================= -->

    <section class="py-5 bg-light">

        <div class="container">

            <div class="d-flex justify-content-between align-items-center mb-5">

                <div>

                    <span class="text-orange fw-bold">
                        NOS PRODUITS
                    </span>

                    <h2 class="fw-bold mt-2">
                        Produits populaires
                    </h2>

                </div>

                <a href="produits.php"
                   class="btn btn-outline-dark">
                    Voir tous les produits
                </a>

            </div>


            <div class="row g-4">

                <!-- Produit 1 -->

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

                            <h5 class="text-orange fw-bold">
                                8 000 FCFA
                            </h5>

                            <a href="produit.php"
                               class="btn btn-dark w-100 mt-2">
                                Voir le produit
                            </a>

                        </div>

                    </div>

                </div>


                <!-- Produit 2 -->

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

                            <h5 class="text-orange fw-bold">
                                45 000 FCFA
                            </h5>

                            <a href="produit.php"
                               class="btn btn-dark w-100 mt-2">
                                Voir le produit
                            </a>

                        </div>

                    </div>

                </div>


                <!-- Produit 3 -->

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

                            <h5 class="text-orange fw-bold">
                                15 000 FCFA
                            </h5>

                            <a href="produit.php"
                               class="btn btn-dark w-100 mt-2">
                                Voir le produit
                            </a>

                        </div>

                    </div>

                </div>


                <!-- Produit 4 -->

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

                            <h5 class="text-orange fw-bold">
                                12 000 FCFA
                            </h5>

                            <a href="produit.php"
                               class="btn btn-dark w-100 mt-2">
                                Voir le produit
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- ================= POURQUOI NPM ================= -->

    <section class="py-5">

        <div class="container">

            <div class="text-center mb-5">

                <span class="text-orange fw-bold">
                    POURQUOI NPM ?
                </span>

                <h2 class="fw-bold mt-2">
                    Pourquoi choisir NPM ?
                </h2>

            </div>


            <div class="row g-4">

                <div class="col-md-6 col-lg-3">

                    <div class="text-center">

                        <i class="bi bi-award feature-icon"></i>

                        <h5 class="fw-bold mt-3">
                            Produits de qualité
                        </h5>

                        <p class="text-muted">
                            Nous sélectionnons des produits
                            fiables et de qualité.
                        </p>

                    </div>

                </div>


                <div class="col-md-6 col-lg-3">

                    <div class="text-center">

                        <i class="bi bi-truck feature-icon"></i>

                        <h5 class="fw-bold mt-3">
                            Livraison
                        </h5>

                        <p class="text-muted">
                            Faites vos achats et recevez
                            vos produits facilement.
                        </p>

                    </div>

                </div>


                <div class="col-md-6 col-lg-3">

                    <div class="text-center">

                        <i class="bi bi-shield-check feature-icon"></i>

                        <h5 class="fw-bold mt-3">
                            Paiement sécurisé
                        </h5>

                        <p class="text-muted">
                            Vos commandes sont traitées
                            en toute sécurité.
                        </p>

                    </div>

                </div>


                <div class="col-md-6 col-lg-3">

                    <div class="text-center">

                        <i class="bi bi-headset feature-icon"></i>

                        <h5 class="fw-bold mt-3">
                            Assistance
                        </h5>

                        <p class="text-muted">
                            Notre équipe est disponible
                            pour vous accompagner.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </section>

<!-- 
    ================= PROMOTION =================

    <section class="py-5">

        <div class="container">

            <div class="promotion">

                <div class="row align-items-center">

                    <div class="col-lg-8">

                        <span class="badge bg-light text-dark mb-3">
                            OFFRE SPÉCIALE
                        </span>

                        <h2 class="fw-bold">
                            Profitez de nos promotions !
                        </h2>

                        <p class="mb-4">
                            Découvrez nos offres et faites des économies
                            sur une sélection de produits.
                        </p>

                        <a href="produits.php"
                           class="btn btn-light">
                            Découvrir les offres
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </section> -->


    <!-- ================= TÉMOIGNAGES ================= -->

    <section class="py-5 bg-light">

        <div class="container">

            <div class="text-center mb-5">

                <span class="text-orange fw-bold">
                    AVIS CLIENTS
                </span>

                <h2 class="fw-bold mt-2">
                    Ce que nos clients pensent de nous
                </h2>

            </div>


            <div class="row g-4">

                <div class="col-md-4">

                    <div class="testimonial-card">

                        <div class="mb-3">
                            ⭐⭐⭐⭐⭐
                        </div>

                        <p>
                            "Très bon service et produits de qualité.
                            Je recommande NPM."
                        </p>

                        <strong>
                            Client NPM
                        </strong>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="testimonial-card">

                        <div class="mb-3">
                            ⭐⭐⭐⭐⭐
                        </div>

                        <p>
                            "J'ai trouvé rapidement les produits
                            dont j'avais besoin."
                        </p>

                        <strong>
                            Client NPM
                        </strong>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="testimonial-card">

                        <div class="mb-3">
                            ⭐⭐⭐⭐⭐
                        </div>

                        <p>
                            "Une plateforme simple et pratique
                            pour acheter mes matériaux."
                        </p>

                        <strong>
                            Client NPM
                        </strong>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- ================= NEWSLETTER ================= -->

    <section class="py-5">

        <div class="container">

            <div class="newsletter text-center">

                <h2 class="fw-bold">
                    Ne manquez aucune offre
                </h2>

                <p class="text-muted">
                    Inscrivez-vous à notre newsletter.
                </p>

                <form class="row justify-content-center g-2 mt-4">

                    <div class="col-md-6">

                        <input
                            type="email"
                            class="form-control form-control-lg"
                            placeholder="Votre adresse email"
                            required>

                    </div>

                    <div class="col-md-auto">

                        <button
                            type="submit"
                            class="btn btn-dark btn-lg">
                            S'inscrire
                        </button>

                    </div>

                </form>

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