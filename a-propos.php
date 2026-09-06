<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>À propos - NPM Quincaillerie</title>

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
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<?php require_once("includes/navbar.php"); ?>


<!-- =========================================================
     HERO
========================================================= -->

<section class="py-5 bg-dark text-white">
    <div class="container py-5">

        <div class="row align-items-center g-5">

            <div class="col-lg-7">

                <span class="badge bg-warning text-dark px-3 py-2 mb-3">
                    À PROPOS DE NPM
                </span>

                <h1 class="display-4 fw-bold mb-4">
                    Votre partenaire pour
                    <span class="text-warning">vos projets</span>
                </h1>

                <p class="lead text-light mb-0">
                    Bienvenue chez NPM Quincaillerie, votre partenaire
                    pour trouver des outils, matériaux et équipements
                    adaptés à vos besoins.
                </p>

            </div>

            <div class="col-lg-5">

                <div class="bg-white text-dark rounded-4 p-4 shadow-lg">

                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-warning rounded-circle p-3 me-3">
                            <i class="bi bi-tools fs-3"></i>
                        </div>

                        <div>
                            <h4 class="fw-bold mb-1">
                                NPM Quincaillerie
                            </h4>

                            <small class="text-muted">
                                Qualité & confiance
                            </small>
                        </div>
                    </div>

                    <p class="text-muted mb-0">
                        Des produits sélectionnés pour accompagner
                        les professionnels et les particuliers dans
                        leurs différents projets.
                    </p>

                </div>

            </div>

        </div>

    </div>
</section>


<!-- =========================================================
     PRESENTATION
========================================================= -->

<section class="py-5">

    <div class="container py-4">

        <div class="row align-items-center g-5">

            <div class="col-lg-6">

                <span class="text-warning fw-bold">
                    QUI SOMMES-NOUS ?
                </span>

                <h2 class="display-6 fw-bold mt-2 mb-4">
                    Une quincaillerie pensée pour vos besoins
                </h2>

                <p class="text-muted">
                    NPM Quincaillerie est une plateforme dédiée à la
                    vente de produits et équipements de quincaillerie.
                    Notre objectif est de permettre à chaque client
                    de trouver facilement les produits dont il a besoin.
                </p>

                <p class="text-muted">
                    Que vous soyez professionnel, artisan ou particulier,
                    nous souhaitons vous proposer une expérience simple,
                    rapide et fiable pour vos achats.
                </p>

                <p class="text-muted mb-0">
                    Grâce à notre plateforme en ligne, vous pouvez
                    consulter nos produits, découvrir nos catégories
                    et passer vos commandes facilement.
                </p>

            </div>


            <div class="col-lg-6">

                <div class="row g-4">

                    <div class="col-6">

                        <div class="card border-0 shadow-sm h-100 text-center p-4">

                            <div class="mb-3">
                                <i class="bi bi-award-fill text-warning fs-1"></i>
                            </div>

                            <h5 class="fw-bold">
                                Qualité
                            </h5>

                            <p class="text-muted small mb-0">
                                Des produits sélectionnés
                                avec attention.
                            </p>

                        </div>

                    </div>


                    <div class="col-6">

                        <div class="card border-0 shadow-sm h-100 text-center p-4">

                            <div class="mb-3">
                                <i class="bi bi-shield-check text-warning fs-1"></i>
                            </div>

                            <h5 class="fw-bold">
                                Confiance
                            </h5>

                            <p class="text-muted small mb-0">
                                Une expérience d'achat
                                simple et fiable.
                            </p>

                        </div>

                    </div>


                    <div class="col-6">

                        <div class="card border-0 shadow-sm h-100 text-center p-4">

                            <div class="mb-3">
                                <i class="bi bi-lightning-charge-fill text-warning fs-1"></i>
                            </div>

                            <h5 class="fw-bold">
                                Rapidité
                            </h5>

                            <p class="text-muted small mb-0">
                                Trouvez rapidement
                                les produits recherchés.
                            </p>

                        </div>

                    </div>


                    <div class="col-6">

                        <div class="card border-0 shadow-sm h-100 text-center p-4">

                            <div class="mb-3">
                                <i class="bi bi-people-fill text-warning fs-1"></i>
                            </div>

                            <h5 class="fw-bold">
                                Service
                            </h5>

                            <p class="text-muted small mb-0">
                                Une plateforme pensée
                                pour nos clients.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     NOS VALEURS
========================================================= -->

<section class="py-5 bg-light">

    <div class="container py-4">

        <div class="text-center mb-5">

            <span class="text-warning fw-bold">
                NOS ENGAGEMENTS
            </span>

            <h2 class="fw-bold mt-2">
                Ce qui nous guide
            </h2>

            <p class="text-muted">
                Des valeurs simples pour vous offrir une meilleure
                expérience.
            </p>

        </div>


        <div class="row g-4">

            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100 p-4">

                    <i class="bi bi-check-circle-fill text-warning fs-1 mb-3"></i>

                    <h4 class="fw-bold">
                        Simplicité
                    </h4>

                    <p class="text-muted mb-0">
                        Nous souhaitons rendre la recherche et l'achat
                        de vos produits aussi simples que possible.
                    </p>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100 p-4">

                    <i class="bi bi-heart-fill text-warning fs-1 mb-3"></i>

                    <h4 class="fw-bold">
                        Satisfaction
                    </h4>

                    <p class="text-muted mb-0">
                        La satisfaction de nos clients est au cœur
                        de notre démarche.
                    </p>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100 p-4">

                    <i class="bi bi-graph-up-arrow text-warning fs-1 mb-3"></i>

                    <h4 class="fw-bold">
                        Évolution
                    </h4>

                    <p class="text-muted mb-0">
                        Nous améliorons progressivement notre plateforme
                        afin de répondre aux besoins de nos clients.
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     POURQUOI NPM ?
========================================================= -->

<section class="py-5">

    <div class="container py-4">

        <div class="row align-items-center g-5">

            <div class="col-lg-5">

                <span class="text-warning fw-bold">
                    POURQUOI NPM ?
                </span>

                <h2 class="fw-bold mt-2 mb-4">
                    Une expérience simple et professionnelle
                </h2>

                <p class="text-muted">
                    Notre plateforme vous permet de parcourir
                    facilement nos produits et de gérer vos commandes
                    depuis votre espace personnel.
                </p>

            </div>


            <div class="col-lg-7">

                <div class="row g-3">

                    <div class="col-md-6">

                        <div class="d-flex gap-3">

                            <div>
                                <i class="bi bi-box-seam-fill text-warning fs-3"></i>
                            </div>

                            <div>
                                <h5 class="fw-bold">
                                    Large choix
                                </h5>

                                <p class="text-muted small">
                                    Des produits organisés par catégories
                                    pour faciliter votre recherche.
                                </p>
                            </div>

                        </div>

                    </div>


                    <div class="col-md-6">

                        <div class="d-flex gap-3">

                            <div>
                                <i class="bi bi-cart-check-fill text-warning fs-3"></i>
                            </div>

                            <div>
                                <h5 class="fw-bold">
                                    Commande en ligne
                                </h5>

                                <p class="text-muted small">
                                    Ajoutez vos produits au panier
                                    et passez votre commande facilement.
                                </p>
                            </div>

                        </div>

                    </div>


                    <div class="col-md-6">

                        <div class="d-flex gap-3">

                            <div>
                                <i class="bi bi-person-check-fill text-warning fs-3"></i>
                            </div>

                            <div>
                                <h5 class="fw-bold">
                                    Espace client
                                </h5>

                                <p class="text-muted small">
                                    Gérez votre compte et retrouvez
                                    vos informations personnelles.
                                </p>
                            </div>

                        </div>

                    </div>


                    <div class="col-md-6">

                        <div class="d-flex gap-3">

                            <div>
                                <i class="bi bi-headset text-warning fs-3"></i>
                            </div>

                            <div>
                                <h5 class="fw-bold">
                                    À votre écoute
                                </h5>

                                <p class="text-muted small">
                                    Une page contact est disponible
                                    pour nous faire parvenir vos demandes.
                                </p>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     CALL TO ACTION
========================================================= -->

<section class="py-5 bg-dark text-white">

    <div class="container text-center py-4">

        <h2 class="fw-bold mb-3">
            Prêt à découvrir nos produits ?
        </h2>

        <p class="text-light mb-4">
            Explorez notre catalogue et trouvez les équipements
            adaptés à vos projets.
        </p>

        <a href="produits.php" class="btn btn-warning btn-lg px-4">
            <i class="bi bi-box-seam me-2"></i>
            Découvrir nos produits
        </a>

        <a href="contact.php" class="btn btn-outline-light btn-lg px-4 ms-2">
            <i class="bi bi-envelope me-2"></i>
            Nous contacter
        </a>

    </div>

</section>


<?php require_once("includes/footer.php"); ?>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>