<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Conditions d'utilisation - Quincaillerie NPM</title>

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
            Conditions <span class="text-warning">d'utilisation</span>
        </h1>

        <p class="lead text-light mx-auto" style="max-width: 700px;">
            Les règles applicables à l'utilisation du site et à vos
            achats auprès de Quincaillerie NPM.
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
                            1. Objet
                        </h4>

                        <p class="text-muted">
                            Les présentes conditions régissent l'utilisation
                            du site de Quincaillerie NPM ainsi que les
                            commandes de produits de quincaillerie, matériaux
                            et outillage effectuées en ligne ou en boutique.
                            En passant commande, vous acceptez sans réserve
                            l'ensemble de ces conditions.
                        </p>


                        <h4 class="fw-bold mt-4">
                            2. Compte client
                        </h4>

                        <p class="text-muted">
                            La création d'un compte permet de suivre vos
                            commandes et de faciliter vos prochains achats.
                            Vous êtes responsable de la confidentialité de
                            votre mot de passe et de toute activité effectuée
                            depuis votre compte.
                        </p>


                        <h4 class="fw-bold mt-4">
                            3. Commandes et prix
                        </h4>

                        <p class="text-muted">
                            Les prix affichés sont exprimés en Francs CFA
                            (FCFA) et incluent les taxes applicables. Ils
                            peuvent être modifiés à tout moment, sans effet
                            sur les commandes déjà validées. Une commande
                            n'est confirmée qu'après vérification de la
                            disponibilité des produits.
                        </p>


                        <h4 class="fw-bold mt-4">
                            4. Paiement
                        </h4>

                        <p class="text-muted">
                            Le paiement peut s'effectuer à la livraison, par
                            Wave, par Orange Money, ou directement en
                            boutique. Pour certains clients réguliers, un
                            règlement à crédit peut être accordé selon les
                            conditions convenues avec notre équipe.
                        </p>


                        <h4 class="fw-bold mt-4">
                            5. Livraison
                        </h4>

                        <p class="text-muted">
                            Les délais de livraison sont communiqués à titre
                            indicatif et peuvent varier selon votre zone et
                            la disponibilité des produits. Un retrait
                            gratuit en boutique est également possible.
                        </p>


                        <h4 class="fw-bold mt-4">
                            6. Retours et échanges
                        </h4>

                        <p class="text-muted">
                            Tout produit non conforme ou défectueux peut être
                            signalé dans les 48 heures suivant sa réception,
                            accompagné du numéro de commande, en vue d'un
                            échange ou d'un remboursement.
                        </p>


                        <h4 class="fw-bold mt-4">
                            7. Responsabilité
                        </h4>

                        <p class="text-muted">
                            Quincaillerie NPM met tout en œuvre pour assurer
                            l'exactitude des informations présentées sur le
                            site, mais ne saurait être tenue responsable
                            d'une utilisation inappropriée des produits
                            vendus, notamment des outils et matériaux
                            nécessitant des précautions d'usage.
                        </p>


                        <h4 class="fw-bold mt-4">
                            8. Modification des conditions
                        </h4>

                        <p class="text-muted">
                            Ces conditions peuvent être mises à jour à tout
                            moment. La version en vigueur est celle publiée
                            sur cette page au moment de votre commande.
                        </p>


                        <h4 class="fw-bold mt-4">
                            9. Contact
                        </h4>

                        <p class="text-muted mb-0">
                            Pour toute question relative à ces conditions,
                            vous pouvez nous contacter via notre
                            <a href="contact.php" class="text-decoration-none">page de contact</a>.
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
