<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contact - Quincaillerie NPM</title>

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


    <!-- ================= CONTENU ================= -->

    <main>


        <!-- ================= TITRE ================= -->

        <section class="contact-header py-5">

            <div class="container">

                <div class="text-center">

                    <span class="text-orange fw-bold">
                        CONTACTEZ-NOUS
                    </span>

                    <h1 class="fw-bold mt-2">
                        Nous sommes à votre écoute
                    </h1>

                    <p class="text-muted">
                        Une question concernant un produit ou une commande ?
                        Contactez l'équipe NPM.
                    </p>

                </div>

            </div>

        </section>


        <!-- ================= CONTACT ================= -->

        <section class="py-5">

            <div class="container">

                <div class="row g-5">


                    <!-- ================= INFORMATIONS ================= -->

                    <div class="col-lg-5">

                        <h2 class="fw-bold mb-4">
                            Parlons de votre projet
                        </h2>

                        <p class="text-muted mb-5">

                            Notre équipe est disponible pour répondre
                            à vos questions et vous accompagner dans
                            vos achats.

                        </p>


                        <!-- Téléphone -->

                        <div class="contact-info">

                            <div class="contact-icon">

                                <i class="bi bi-telephone"></i>

                            </div>

                            <div>

                                <h5 class="fw-bold mb-1">
                                    Téléphone
                                </h5>

                                <p class="text-muted mb-0">
                                    +221 77 640 58 26
                                </p>

                            </div>

                        </div>


                        <!-- Email -->

                        <div class="contact-info">

                            <div class="contact-icon">

                                <i class="bi bi-envelope"></i>

                            </div>

                            <div>

                                <h5 class="fw-bold mb-1">
                                    Email
                                </h5>

                                <p class="text-muted mb-0">
                                    contact@npm.com
                                </p>

                            </div>

                        </div>


                        <!-- Adresse -->

                        <div class="contact-info">

                            <div class="contact-icon">

                                <i class="bi bi-geo-alt"></i>

                            </div>

                            <div>

                                <h5 class="fw-bold mb-1">
                                    Adresse
                                </h5>

                                <p class="text-muted mb-0">
                                    Dakar, PAU 26 , Sénégal
                                </p>

                            </div>

                        </div>


                        <!-- Horaires -->

                        <div class="contact-info">

                            <div class="contact-icon">

                                <i class="bi bi-clock"></i>

                            </div>

                            <div>

                                <h5 class="fw-bold mb-1">
                                    Horaires
                                </h5>

                                <p class="text-muted mb-0">
                                    Lundi - Dimache : 08h00 - 19h00 <br>
                                    les Dimache : 09H00 - 14h00
                                </p>

                            </div>

                        </div>

                    </div>


                    <!-- ================= FORMULAIRE ================= -->

                    <div class="col-lg-7">

                        <div class="contact-card">

                            <h3 class="fw-bold mb-4">
                                Envoyez-nous un message
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
                                        placeholder="77 000 00 00">

                                </div>


                                <!-- Sujet -->

                                <div class="mb-3">

                                    <label
                                        for="sujet"
                                        class="form-label fw-bold">

                                        Sujet

                                    </label>

                                    <select
                                        name="sujet"
                                        id="sujet"
                                        class="form-select"
                                        required>

                                        <option value="" selected disabled>
                                            Choisissez un sujet
                                        </option>

                                        <option value="produit">
                                            Question sur un produit
                                        </option>

                                        <option value="commande">
                                            Question sur une commande
                                        </option>

                                        <option value="livraison">
                                            Livraison
                                        </option>

                                        <option value="autre">
                                            Autre
                                        </option>

                                    </select>

                                </div>


                                <!-- Message -->

                                <div class="mb-4">

                                    <label
                                        for="message"
                                        class="form-label fw-bold">

                                        Message

                                    </label>

                                    <textarea
                                        name="message"
                                        id="message"
                                        class="form-control"
                                        rows="6"
                                        placeholder="Écrivez votre message..."
                                        required></textarea>

                                </div>


                                <!-- Bouton -->

                                <button
                                    type="submit"
                                    class="btn btn-dark btn-lg w-100">

                                    <i class="bi bi-send"></i>

                                    Envoyer le message

                                </button>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- ================= FAQ ================= -->

        <section class="py-5 bg-light">

            <div class="container">

                <div class="text-center mb-5">

                    <span class="text-orange fw-bold">
                        QUESTIONS
                    </span>

                    <h2 class="fw-bold mt-2">
                        Questions fréquentes
                    </h2>

                </div>


                <div class="accordion" id="faq">


                    <!-- Question 1 -->

                    <div class="accordion-item">

                        <h2 class="accordion-header">

                            <button
                                class="accordion-button"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#question1">

                                Comment passer une commande ?

                            </button>

                        </h2>

                        <div
                            id="question1"
                            class="accordion-collapse collapse show"
                            data-bs-parent="#faq">

                            <div class="accordion-body">

                                Choisissez un produit, ajoutez-le à votre
                                panier puis cliquez sur « Passer la commande »
                                pour renseigner vos informations.

                            </div>

                        </div>

                    </div>


                    <!-- Question 2 -->

                    <div class="accordion-item">

                        <h2 class="accordion-header">

                            <button
                                class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#question2">

                                Quels sont les moyens de paiement ?

                            </button>

                        </h2>

                        <div
                            id="question2"
                            class="accordion-collapse collapse"
                            data-bs-parent="#faq">

                            <div class="accordion-body">

                                Nous prévoyons notamment le paiement
                                à la livraison, Wave et Orange Money.

                            </div>

                        </div>

                    </div>


                    <!-- Question 3 -->

                    <div class="accordion-item">

                        <h2 class="accordion-header">

                            <button
                                class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#question3">

                                Comment suivre ma commande ?

                            </button>

                        </h2>

                        <div
                            id="question3"
                            class="accordion-collapse collapse"
                            data-bs-parent="#faq">

                            <div class="accordion-body">

                                Les fonctionnalités de suivi seront
                                ajoutées lorsque nous mettrons en place
                                le système de commandes.

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