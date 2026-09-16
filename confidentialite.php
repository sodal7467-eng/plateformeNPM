<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Politique de confidentialité - Quincaillerie NPM</title>

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

    <div class="container py-5 text-center">

        <span class="badge bg-warning text-dark px-3 py-2 mb-3">
            INFORMATIONS LÉGALES
        </span>

        <h1 class="display-5 fw-bold mb-3">
            Politique de <span class="text-warning">confidentialité</span>
        </h1>

        <p class="lead text-light mx-auto" style="max-width: 700px;">
            Comment Quincaillerie NPM collecte, utilise et protège vos
            informations personnelles.
        </p>

    </div>

</section>


<!-- =========================================================
     CONTENU
========================================================= -->

<section class="py-5">

    <div class="container py-4">

        <div class="row justify-content-center">

            <div class="col-lg-9">

                <div class="card border-0 shadow-sm rounded-4">

                    <div class="card-body p-4 p-lg-5">


                        <p class="text-muted">
                            Dernière mise à jour : <?= date("d/m/Y") ?>
                        </p>


                        <h4 class="fw-bold mt-4">
                            1. Données collectées
                        </h4>

                        <p class="text-muted">
                            Lorsque vous créez un compte, passez une commande
                            ou nous contactez, nous collectons certaines
                            informations : nom, adresse email, numéro de
                            téléphone, adresse de livraison, et l'historique
                            de vos commandes. Pour les clients de notre
                            boutique physique, seules les informations
                            nécessaires au suivi de leurs achats (nom,
                            téléphone) sont enregistrées.
                        </p>


                        <h4 class="fw-bold mt-4">
                            2. Utilisation des données
                        </h4>

                        <p class="text-muted">
                            Vos données sont utilisées uniquement pour traiter
                            vos commandes, assurer le suivi de vos achats et
                            de vos éventuels crédits, vous contacter en cas de
                            besoin, et améliorer nos services. Elles ne sont
                            jamais utilisées à des fins publicitaires sans
                            votre accord.
                        </p>


                        <h4 class="fw-bold mt-4">
                            3. Partage des données
                        </h4>

                        <p class="text-muted">
                            Quincaillerie NPM ne vend ni ne loue vos données
                            personnelles à des tiers. Elles peuvent
                            uniquement être partagées avec nos prestataires
                            de livraison, dans la stricte mesure nécessaire
                            à l'acheminement de votre commande.
                        </p>


                        <h4 class="fw-bold mt-4">
                            4. Sécurité
                        </h4>

                        <p class="text-muted">
                            Vos mots de passe sont stockés de façon
                            chiffrée et ne sont jamais visibles, y compris
                            par notre équipe. Nous mettons en œuvre des
                            mesures raisonnables pour protéger vos
                            informations contre tout accès non autorisé.
                        </p>


                        <h4 class="fw-bold mt-4">
                            5. Conservation des données
                        </h4>

                        <p class="text-muted">
                            Vos données sont conservées le temps nécessaire à
                            la gestion de votre compte et de vos commandes,
                            ainsi qu'au respect de nos obligations légales et
                            comptables.
                        </p>


                        <h4 class="fw-bold mt-4">
                            6. Vos droits
                        </h4>

                        <p class="text-muted">
                            Vous pouvez à tout moment demander à consulter,
                            corriger ou supprimer les informations vous
                            concernant en nous contactant via notre
                            <a href="contact.php" class="text-decoration-none">page de contact</a>.
                        </p>


                        <h4 class="fw-bold mt-4">
                            7. Cookies
                        </h4>

                        <p class="text-muted mb-0">
                            Le site utilise des cookies techniques
                            indispensables à son fonctionnement, notamment
                            pour garder votre session ouverte et retenir le
                            contenu de votre panier. Aucun cookie publicitaire
                            n'est utilisé.
                        </p>


                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<?php require_once("includes/footer.php"); ?>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>
