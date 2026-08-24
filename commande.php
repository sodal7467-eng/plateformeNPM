<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Passer la commande - Quincaillerie NPM</title>

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


    <!-- ================= COMMANDE ================= -->

    <main>


        <!-- Titre -->

        <section class="commande-header py-5">

            <div class="container">

                <div class="text-center">

                    <span class="text-orange fw-bold">
                        COMMANDE
                    </span>

                    <h1 class="fw-bold mt-2">
                        Finaliser ma commande
                    </h1>

                    <p class="text-muted">
                        Renseignez vos informations pour recevoir votre commande.
                    </p>

                </div>

            </div>

        </section>


        <!-- ================= FORMULAIRE ================= -->

        <section class="py-5">

            <div class="container">

                <div class="row g-4">


                    <!-- ================= INFORMATIONS CLIENT ================= -->

                    <div class="col-lg-7">

                        <div class="commande-card">

                            <h3 class="fw-bold mb-4">
                                Informations de livraison
                            </h3>


                            <form action="#" method="POST">


                                <!-- Nom -->

                                <div class="mb-3">

                                    <label
                                        for="nom"
                                        class="form-label fw-bold">

                                        Nom complet

                                    </label>

                                    <input
                                        type="text"
                                        name="nom"
                                        id="nom"
                                        class="form-control"
                                        placeholder="Votre nom complet"
                                        required>

                                </div>


                                <!-- Téléphone -->

                                <div class="mb-3">

                                    <label
                                        for="telephone"
                                        class="form-label fw-bold">

                                        Téléphone

                                    </label>

                                    <input
                                        type="tel"
                                        name="telephone"
                                        id="telephone"
                                        class="form-control"
                                        placeholder="Ex : 77 000 00 00"
                                        required>

                                </div>


                                <!-- Email -->

                                <div class="mb-3">

                                    <label
                                        for="email"
                                        class="form-label fw-bold">

                                        Adresse email

                                    </label>

                                    <input
                                        type="email"
                                        name="email"
                                        id="email"
                                        class="form-control"
                                        placeholder="exemple@email.com"
                                        required>

                                </div>


                                <!-- Adresse -->

                                <div class="mb-3">

                                    <label
                                        for="adresse"
                                        class="form-label fw-bold">

                                        Adresse de livraison

                                    </label>

                                    <textarea
                                        name="adresse"
                                        id="adresse"
                                        class="form-control"
                                        rows="3"
                                        placeholder="Votre adresse complète"
                                        required></textarea>

                                </div>


                                <!-- Ville -->

                                <div class="row">

                                    <div class="col-md-6 mb-3">

                                        <label
                                            for="ville"
                                            class="form-label fw-bold">

                                            Ville

                                        </label>

                                        <input
                                            type="text"
                                            name="ville"
                                            id="ville"
                                            class="form-control"
                                            placeholder="Dakar"
                                            required>

                                    </div>


                                    <!-- Quartier -->

                                    <div class="col-md-6 mb-3">

                                        <label
                                            for="quartier"
                                            class="form-label fw-bold">

                                            Quartier

                                        </label>

                                        <input
                                            type="text"
                                            name="quartier"
                                            id="quartier"
                                            class="form-control"
                                            placeholder="Votre quartier"
                                            required>

                                    </div>

                                </div>


                                <!-- Mode de paiement -->

                                <h4 class="fw-bold mt-4 mb-3">
                                    Mode de paiement
                                </h4>


                                <div class="payment-option">

                                    <div class="form-check">

                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            name="paiement"
                                            id="livraison"
                                            value="livraison"
                                            checked>

                                        <label
                                            class="form-check-label"
                                            for="livraison">

                                            <i class="bi bi-cash-coin"></i>

                                            Paiement à la livraison

                                        </label>

                                    </div>

                                </div>


                                <div class="payment-option">

                                    <div class="form-check">

                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            name="paiement"
                                            id="wave"
                                            value="wave">

                                        <label
                                            class="form-check-label"
                                            for="wave">

                                            <i class="bi bi-phone"></i>

                                            Wave

                                        </label>

                                    </div>

                                </div>


                                <div class="payment-option">

                                    <div class="form-check">

                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            name="paiement"
                                            id="om"
                                            value="orange_money">

                                        <label
                                            class="form-check-label"
                                            for="om">

                                            <i class="bi bi-phone"></i>

                                            Orange Money

                                        </label>

                                    </div>

                                </div>


                                <!-- Commentaire -->

                                <div class="mt-4 mb-4">

                                    <label
                                        for="commentaire"
                                        class="form-label fw-bold">

                                        Commentaire
                                        <span class="text-muted">
                                            (facultatif)
                                        </span>

                                    </label>

                                    <textarea
                                        name="commentaire"
                                        id="commentaire"
                                        class="form-control"
                                        rows="3"
                                        placeholder="Une précision concernant votre commande ?"></textarea>

                                </div>


                                <!-- Bouton -->

                                <button
                                    type="submit"
                                    class="btn btn-dark btn-lg w-100">

                                    <i class="bi bi-check-circle"></i>

                                    Confirmer ma commande

                                </button>


                            </form>

                        </div>

                    </div>


                    <!-- ================= RÉSUMÉ ================= -->

                    <div class="col-lg-5">

                        <div class="commande-summary">

                            <h3 class="fw-bold mb-4">
                                Résumé de votre commande
                            </h3>


                            <!-- Produit -->

                            <div class="commande-product">

                                <div class="commande-product-image">

                                    <i class="bi bi-image"></i>

                                </div>

                                <div class="flex-grow-1">

                                    <h6 class="fw-bold mb-1">
                                        Marteau professionnel
                                    </h6>

                                    <small class="text-muted">
                                        Quantité : 1
                                    </small>

                                </div>

                                <strong>
                                    8 000 FCFA
                                </strong>

                            </div>


                            <!-- Produit -->

                            <div class="commande-product">

                                <div class="commande-product-image">

                                    <i class="bi bi-image"></i>

                                </div>

                                <div class="flex-grow-1">

                                    <h6 class="fw-bold mb-1">
                                        Robinet moderne
                                    </h6>

                                    <small class="text-muted">
                                        Quantité : 1
                                    </small>

                                </div>

                                <strong>
                                    15 000 FCFA
                                </strong>

                            </div>


                            <hr>


                            <!-- Sous-total -->

                            <div class="d-flex justify-content-between mb-3">

                                <span>
                                    Sous-total
                                </span>

                                <strong>
                                    23 000 FCFA
                                </strong>

                            </div>


                            <!-- Livraison -->

                            <div class="d-flex justify-content-between mb-3">

                                <span>
                                    Livraison
                                </span>

                                <strong>
                                    À déterminer
                                </strong>

                            </div>


                            <hr>


                            <!-- Total -->

                            <div class="d-flex justify-content-between align-items-center">

                                <strong>
                                    Total
                                </strong>

                                <h3 class="text-orange fw-bold">
                                    23 000 FCFA
                                </h3>

                            </div>


                            <!-- Sécurité -->

                            <div class="mt-4 p-3 bg-light rounded">

                                <small class="text-muted">

                                    <i class="bi bi-shield-check text-success"></i>

                                    Vos informations sont protégées
                                    et utilisées uniquement pour traiter
                                    votre commande.

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