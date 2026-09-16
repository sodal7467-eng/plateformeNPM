<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Questions fréquentes - Quincaillerie NPM</title>

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
            AIDE
        </span>

        <h1 class="display-5 fw-bold mb-3">
            Questions <span class="text-warning">fréquentes</span>
        </h1>

        <p class="lead text-light mx-auto" style="max-width: 700px;">
            Retrouvez les réponses aux questions les plus posées sur
            nos produits, nos commandes et nos livraisons.
        </p>

    </div>

</section>


<!-- =========================================================
     FAQ
========================================================= -->

<section class="py-5">

    <div class="container py-4">

        <div class="row justify-content-center">

            <div class="col-lg-9">

                <div class="accordion" id="accordionFAQ">


                    <!-- Commander -->

                    <div class="accordion-item mb-3 border-0 shadow-sm rounded-3 overflow-hidden">

                        <h2 class="accordion-header">

                            <button
                                class="accordion-button fw-bold"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#faq1"
                            >
                                <i class="bi bi-cart3 me-2 text-warning"></i>
                                Comment passer une commande ?
                            </button>

                        </h2>

                        <div
                            id="faq1"
                            class="accordion-collapse collapse show"
                            data-bs-parent="#accordionFAQ"
                        >
                            <div class="accordion-body text-muted">
                                Parcourez notre catalogue, ajoutez les produits
                                souhaités à votre panier, puis validez votre
                                commande en renseignant vos coordonnées de
                                livraison. Vous recevrez un numéro de commande
                                que vous pouvez suivre depuis votre profil.
                            </div>
                        </div>

                    </div>


                    <!-- Paiement -->

                    <div class="accordion-item mb-3 border-0 shadow-sm rounded-3 overflow-hidden">

                        <h2 class="accordion-header">

                            <button
                                class="accordion-button collapsed fw-bold"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#faq2"
                            >
                                <i class="bi bi-cash-coin me-2 text-warning"></i>
                                Quels sont les moyens de paiement acceptés ?
                            </button>

                        </h2>

                        <div
                            id="faq2"
                            class="accordion-collapse collapse"
                            data-bs-parent="#accordionFAQ"
                        >
                            <div class="accordion-body text-muted">
                                Vous pouvez payer à la livraison, ou en ligne via
                                Wave et Orange Money. Pour nos clients réguliers
                                de la boutique, un système de crédit peut
                                également être mis en place directement en
                                magasin.
                            </div>
                        </div>

                    </div>


                    <!-- Livraison -->

                    <div class="accordion-item mb-3 border-0 shadow-sm rounded-3 overflow-hidden">

                        <h2 class="accordion-header">

                            <button
                                class="accordion-button collapsed fw-bold"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#faq3"
                            >
                                <i class="bi bi-truck me-2 text-warning"></i>
                                Livrez-vous en dehors de Dakar ?
                            </button>

                        </h2>

                        <div
                            id="faq3"
                            class="accordion-collapse collapse"
                            data-bs-parent="#accordionFAQ"
                        >
                            <div class="accordion-body text-muted">
                                Nous livrons principalement dans Dakar et sa
                                banlieue. Pour une livraison en dehors de cette
                                zone, contactez-nous directement afin que nous
                                étudiions la meilleure solution avec vous.
                            </div>
                        </div>

                    </div>


                    <!-- Retrait en boutique -->

                    <div class="accordion-item mb-3 border-0 shadow-sm rounded-3 overflow-hidden">

                        <h2 class="accordion-header">

                            <button
                                class="accordion-button collapsed fw-bold"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#faq4"
                            >
                                <i class="bi bi-shop me-2 text-warning"></i>
                                Puis-je retirer ma commande directement en boutique ?
                            </button>

                        </h2>

                        <div
                            id="faq4"
                            class="accordion-collapse collapse"
                            data-bs-parent="#accordionFAQ"
                        >
                            <div class="accordion-body text-muted">
                                Oui. Vous pouvez passer commande sur le site puis
                                venir la récupérer directement à la boutique aux
                                horaires d'ouverture, ou tout simplement acheter
                                sur place sans passer par le site.
                            </div>
                        </div>

                    </div>


                    <!-- Retours -->

                    <div class="accordion-item mb-3 border-0 shadow-sm rounded-3 overflow-hidden">

                        <h2 class="accordion-header">

                            <button
                                class="accordion-button collapsed fw-bold"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#faq5"
                            >
                                <i class="bi bi-arrow-return-left me-2 text-warning"></i>
                                Puis-je retourner ou échanger un produit ?
                            </button>

                        </h2>

                        <div
                            id="faq5"
                            class="accordion-collapse collapse"
                            data-bs-parent="#accordionFAQ"
                        >
                            <div class="accordion-body text-muted">
                                Un produit défectueux ou non conforme à votre
                                commande peut être échangé sous 48 heures après
                                réception, sur présentation de votre numéro de
                                commande. Les outils électriques bénéficient en
                                plus de la garantie du fabricant.
                            </div>
                        </div>

                    </div>


                    <!-- Horaires -->

                    <div class="accordion-item border-0 shadow-sm rounded-3 overflow-hidden">

                        <h2 class="accordion-header">

                            <button
                                class="accordion-button collapsed fw-bold"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#faq6"
                            >
                                <i class="bi bi-clock me-2 text-warning"></i>
                                Quels sont vos horaires d'ouverture ?
                            </button>

                        </h2>

                        <div
                            id="faq6"
                            class="accordion-collapse collapse"
                            data-bs-parent="#accordionFAQ"
                        >
                            <div class="accordion-body text-muted">
                                Notre boutique est ouverte du lundi au samedi,
                                de 08h00 à 18h00. Le site reste quant à lui
                                accessible 24h/24 pour passer commande.
                            </div>
                        </div>

                    </div>


                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     CTA
========================================================= -->

<section class="py-5 bg-light">

    <div class="container text-center py-4">

        <h2 class="fw-bold mb-3">
            Vous n'avez pas trouvé votre réponse ?
        </h2>

        <p class="text-muted mb-4">
            Notre équipe reste disponible pour répondre à toutes vos questions.
        </p>

        <a
            href="contact.php"
            class="btn btn-dark btn-lg px-4"
        >

            <i class="bi bi-envelope me-2"></i>

            Contactez-nous

        </a>

    </div>

</section>


<?php require_once("includes/footer.php"); ?>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>
