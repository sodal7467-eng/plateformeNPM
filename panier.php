<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mon panier - Quincaillerie NPM</title>

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


    <!-- ================= PANIER ================= -->

    <main>

        <!-- Titre -->

        <section class="cart-header py-5">

            <div class="container">

                <div class="text-center">

                    <span class="text-orange fw-bold">
                        MON ESPACE
                    </span>

                    <h1 class="fw-bold mt-2">
                        Mon panier
                    </h1>

                    <p class="text-muted">
                        Vérifiez vos produits avant de passer votre commande.
                    </p>

                </div>

            </div>

        </section>


        <!-- Contenu du panier -->

        <section class="py-5">

            <div class="container">

                <div class="row g-4">


                    <!-- ================= PRODUITS ================= -->

                    <div class="col-lg-8">

                        <div class="cart-container">


                            <!-- Produit 1 -->

                            <div class="cart-item">

                                <!-- Image -->

                                <div class="cart-product-image">

                                    <i class="bi bi-image"></i>

                                </div>


                                <!-- Informations -->

                                <div class="cart-product-info">

                                    <small class="text-muted">
                                        Outils
                                    </small>

                                    <h5 class="fw-bold">
                                        Marteau professionnel
                                    </h5>

                                    <p class="text-orange fw-bold mb-3">
                                        8 000 FCFA
                                    </p>


                                    <!-- Quantité -->

                                    <div class="quantity-box">

                                        <button
                                            type="button"
                                            class="btn btn-outline-dark">

                                            −

                                        </button>

                                        <input
                                            type="number"
                                            class="form-control text-center"
                                            value="1"
                                            min="1">

                                        <button
                                            type="button"
                                            class="btn btn-outline-dark">

                                            +

                                        </button>

                                    </div>

                                </div>


                                <!-- Prix total -->

                                <div class="cart-product-total">

                                    <strong>
                                        8 000 FCFA
                                    </strong>

                                    <button
                                        type="button"
                                        class="btn btn-link text-danger">

                                        <i class="bi bi-trash"></i>

                                    </button>

                                </div>

                            </div>


                            <!-- Produit 2 -->

                            <div class="cart-item">

                                <!-- Image -->

                                <div class="cart-product-image">

                                    <i class="bi bi-image"></i>

                                </div>


                                <!-- Informations -->

                                <div class="cart-product-info">

                                    <small class="text-muted">
                                        Plomberie
                                    </small>

                                    <h5 class="fw-bold">
                                        Robinet moderne
                                    </h5>

                                    <p class="text-orange fw-bold mb-3">
                                        15 000 FCFA
                                    </p>


                                    <!-- Quantité -->

                                    <div class="quantity-box">

                                        <button
                                            type="button"
                                            class="btn btn-outline-dark">

                                            −

                                        </button>

                                        <input
                                            type="number"
                                            class="form-control text-center"
                                            value="1"
                                            min="1">

                                        <button
                                            type="button"
                                            class="btn btn-outline-dark">

                                            +

                                        </button>

                                    </div>

                                </div>


                                <!-- Prix total -->

                                <div class="cart-product-total">

                                    <strong>
                                        15 000 FCFA
                                    </strong>

                                    <button
                                        type="button"
                                        class="btn btn-link text-danger">

                                        <i class="bi bi-trash"></i>

                                    </button>

                                </div>

                            </div>


                        </div>


                        <!-- Continuer les achats -->

                        <div class="mt-4">

                            <a
                                href="produits.php"
                                class="btn btn-outline-dark">

                                <i class="bi bi-arrow-left"></i>

                                Continuer mes achats

                            </a>

                        </div>

                    </div>


                    <!-- ================= RÉSUMÉ ================= -->

                    <div class="col-lg-4">

                        <div class="cart-summary">

                            <h4 class="fw-bold mb-4">
                                Résumé de la commande
                            </h4>


                            <div class="d-flex justify-content-between mb-3">

                                <span>
                                    Sous-total
                                </span>

                                <strong>
                                    23 000 FCFA
                                </strong>

                            </div>


                            <div class="d-flex justify-content-between mb-3">

                                <span>
                                    Livraison
                                </span>

                                <strong>
                                    À déterminer
                                </strong>

                            </div>


                            <hr>


                            <div class="d-flex justify-content-between mb-4">

                                <strong>
                                    Total
                                </strong>

                                <h4 class="text-orange fw-bold">
                                    23 000 FCFA
                                </h4>

                            </div>


                            <a
                                href="commande.php"
                                class="btn btn-dark btn-lg w-100">

                                Passer la commande

                                <i class="bi bi-arrow-right"></i>

                            </a>


                            <div class="mt-4 text-center">

                                <small class="text-muted">

                                    <i class="bi bi-shield-check"></i>

                                    Paiement sécurisé

                                </small>

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

</body>

</html>