<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Produit - Quincaillerie NPM</title>

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


    <!-- ================= PRODUIT ================= -->

    <main>

        <section class="py-5">

            <div class="container">

                <!-- Fil d'Ariane -->

                <div class="mb-4">

                    <a href="index.php"
                       class="text-decoration-none text-muted">
                        Accueil
                    </a>

                    <span class="text-muted">
                        /
                    </span>

                    <a href="produits.php"
                       class="text-decoration-none text-muted">
                        Produits
                    </a>

                    <span class="text-muted">
                        / Marteau professionnel
                    </span>

                </div>


                <div class="row g-5 align-items-center">


                    <!-- ================= IMAGE ================= -->

                    <div class="col-lg-6">

                        <div class="product-detail-image">

                            <i class="bi bi-image"></i>

                        </div>

                    </div>


                    <!-- ================= INFORMATIONS ================= -->

                    <div class="col-lg-6">

                        <span class="text-orange fw-bold">
                            OUTILS
                        </span>

                        <h1 class="fw-bold mt-2">
                            Marteau professionnel
                        </h1>

                        <!-- Étoiles -->

                        <div class="mb-3">

                            <span class="text-warning">
                                ★★★★★
                            </span>

                            <span class="text-muted ms-2">
                                (12 avis)
                            </span>

                        </div>


                        <!-- Prix -->

                        <h2 class="text-orange fw-bold mb-4">
                            8 000 FCFA
                        </h2>


                        <!-- Description -->

                        <p class="text-muted">

                            Marteau professionnel robuste et durable,
                            idéal pour les travaux de construction,
                            de rénovation et de bricolage.

                        </p>


                        <!-- Disponibilité -->

                        <div class="mb-4">

                            <span class="badge bg-success">
                                En stock
                            </span>

                        </div>


                        <!-- Quantité -->

                        <div class="mb-4">

                            <label
                                for="quantite"
                                class="form-label fw-bold">

                                Quantité

                            </label>

                            <div class="input-group quantity-box">

                                <button
                                    type="button"
                                    class="btn btn-outline-dark"
                                    id="moins">

                                    −

                                </button>

                                <input
                                    type="number"
                                    id="quantite"
                                    class="form-control text-center"
                                    value="1"
                                    min="1">

                                <button
                                    type="button"
                                    class="btn btn-outline-dark"
                                    id="plus">

                                    +

                                </button>

                            </div>

                        </div>


                        <!-- Boutons -->

                        <div class="d-flex gap-3 flex-wrap">

                            <button
                                type="button"
                                class="btn btn-dark btn-lg">

                                <i class="bi bi-cart-plus"></i>

                                Ajouter au panier

                            </button>


                            <a
                                href="panier.php"
                                class="btn btn-outline-dark btn-lg">

                                Voir le panier

                            </a>

                        </div>


                        <!-- Informations supplémentaires -->

                        <hr class="my-4">


                        <div class="row g-3">

                            <div class="col-md-6">

                                <div class="d-flex gap-3">

                                    <i class="bi bi-truck fs-3 text-orange"></i>

                                    <div>

                                        <strong>
                                            Livraison
                                        </strong>

                                        <p class="text-muted small mb-0">
                                            Livraison disponible
                                        </p>

                                    </div>

                                </div>

                            </div>


                            <div class="col-md-6">

                                <div class="d-flex gap-3">

                                    <i class="bi bi-shield-check fs-3 text-orange"></i>

                                    <div>

                                        <strong>
                                            Qualité garantie
                                        </strong>

                                        <p class="text-muted small mb-0">
                                            Produits sélectionnés
                                        </p>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- ================= DESCRIPTION ================= -->

        <section class="py-5 bg-light">

            <div class="container">

                <div class="row">

                    <div class="col-lg-8">

                        <h2 class="fw-bold mb-4">
                            Description du produit
                        </h2>

                        <p class="text-muted">

                            Ce marteau professionnel est conçu pour
                            accompagner les professionnels et les
                            particuliers dans leurs différents travaux.

                        </p>

                        <p class="text-muted">

                            Sa conception robuste permet une utilisation
                            régulière tout en offrant une bonne prise
                            en main et une grande résistance.

                        </p>

                    </div>

                </div>

            </div>

        </section>


        <!-- ================= PRODUITS SIMILAIRES ================= -->

        <section class="py-5">

            <div class="container">

                <div class="text-center mb-5">

                    <span class="text-orange fw-bold">
                        VOUS POURRIEZ AUSSI AIMER
                    </span>

                    <h2 class="fw-bold mt-2">
                        Produits similaires
                    </h2>

                </div>


                <div class="row g-4">


                    <!-- Produit similaire 1 -->

                    <div class="col-md-6 col-lg-3">

                        <div class="product-card">

                            <div class="product-image">

                                <i
                                    class="bi bi-image text-secondary"
                                    style="font-size: 50px;">
                                </i>

                            </div>

                            <div class="p-3">

                                <small class="text-muted">
                                    Outils
                                </small>

                                <h5 class="fw-bold mt-2">
                                    Tournevis professionnel
                                </h5>

                                <strong class="text-orange">
                                    5 000 FCFA
                                </strong>

                                <a
                                    href="produit.php"
                                    class="btn btn-dark w-100 mt-3">

                                    Voir le produit

                                </a>

                            </div>

                        </div>

                    </div>


                    <!-- Produit similaire 2 -->

                    <div class="col-md-6 col-lg-3">

                        <div class="product-card">

                            <div class="product-image">

                                <i
                                    class="bi bi-image text-secondary"
                                    style="font-size: 50px;">
                                </i>

                            </div>

                            <div class="p-3">

                                <small class="text-muted">
                                    Outils
                                </small>

                                <h5 class="fw-bold mt-2">
                                    Perceuse électrique
                                </h5>

                                <strong class="text-orange">
                                    45 000 FCFA
                                </strong>

                                <a
                                    href="produit.php"
                                    class="btn btn-dark w-100 mt-3">

                                    Voir le produit

                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>

    </main>


    <!-- ================= FOOTER ================= -->

    <?php include 'includes/footer.php'; ?>


    <!-- Bootstrap JS -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>


    <!-- Quantité -->

    <script>

        const moins = document.getElementById("moins");
        const plus = document.getElementById("plus");
        const quantite = document.getElementById("quantite");


        moins.addEventListener("click", function () {

            let valeur = parseInt(quantite.value);

            if (valeur > 1) {

                quantite.value = valeur - 1;

            }

        });


        plus.addEventListener("click", function () {

            let valeur = parseInt(quantite.value);

            quantite.value = valeur + 1;

        });

    </script>

</body>

</html>