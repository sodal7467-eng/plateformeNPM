<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inscription - Quincaillerie NPM</title>

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


    <!-- ================= INSCRIPTION ================= -->

    <main>

        <section class="inscription-section py-5">

            <div class="container">

                <div class="row justify-content-center">

                    <div class="col-md-9 col-lg-6">

                        <div class="inscription-card">


                            <!-- Icône -->

                            <div class="inscription-icon">

                                <i class="bi bi-person-plus"></i>

                            </div>


                            <!-- Titre -->

                            <div class="text-center mb-4">

                                <span class="text-orange fw-bold">
                                    ESPACE CLIENT
                                </span>

                                <h1 class="fw-bold mt-2">
                                    Créer un compte
                                </h1>

                                <p class="text-muted">
                                    Rejoignez NPM et profitez d'une expérience
                                    d'achat simple et rapide.
                                </p>

                            </div>


                            <!-- Formulaire -->

                            <form action="#" method="POST">


                                <!-- Nom et prénom -->

                                <div class="row">

                                    <div class="col-md-6 mb-3">

                                        <label
                                            for="prenom"
                                            class="form-label fw-bold">

                                            Prénom

                                        </label>

                                        <input
                                            type="text"
                                            name="prenom"
                                            id="prenom"
                                            class="form-control"
                                            placeholder="Votre prénom"
                                            required>

                                    </div>


                                    <div class="col-md-6 mb-3">

                                        <label
                                            for="nom"
                                            class="form-label fw-bold">

                                            Nom

                                        </label>

                                        <input
                                            type="text"
                                            name="nom"
                                            id="nom"
                                            class="form-control"
                                            placeholder="Votre nom"
                                            required>

                                    </div>

                                </div>


                                <!-- Téléphone -->

                                <div class="mb-3">

                                    <label
                                        for="telephone"
                                        class="form-label fw-bold">

                                        Téléphone

                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">

                                            <i class="bi bi-telephone"></i>

                                        </span>

                                        <input
                                            type="tel"
                                            name="telephone"
                                            id="telephone"
                                            class="form-control"
                                            placeholder="77 000 00 00"
                                            required>

                                    </div>

                                </div>


                                <!-- Email -->

                                <div class="mb-3">

                                    <label
                                        for="email"
                                        class="form-label fw-bold">

                                        Adresse email

                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">

                                            <i class="bi bi-envelope"></i>

                                        </span>

                                        <input
                                            type="email"
                                            name="email"
                                            id="email"
                                            class="form-control"
                                            placeholder="exemple@email.com"
                                            required>

                                    </div>

                                </div>


                                <!-- Mot de passe -->

                                <div class="mb-3">

                                    <label
                                        for="password"
                                        class="form-label fw-bold">

                                        Mot de passe

                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">

                                            <i class="bi bi-lock"></i>

                                        </span>

                                        <input
                                            type="password"
                                            name="password"
                                            id="password"
                                            class="form-control"
                                            placeholder="Créer un mot de passe"
                                            required>

                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary"
                                            id="voirPassword">

                                            <i class="bi bi-eye"></i>

                                        </button>

                                    </div>

                                </div>


                                <!-- Confirmation mot de passe -->

                                <div class="mb-3">

                                    <label
                                        for="confirmation"
                                        class="form-label fw-bold">

                                        Confirmer le mot de passe

                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">

                                            <i class="bi bi-lock-fill"></i>

                                        </span>

                                        <input
                                            type="password"
                                            name="confirmation"
                                            id="confirmation"
                                            class="form-control"
                                            placeholder="Confirmer votre mot de passe"
                                            required>

                                    </div>

                                </div>


                                <!-- Conditions -->

                                <div class="form-check mb-4">

                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        id="conditions"
                                        name="conditions"
                                        required>

                                    <label
                                        for="conditions"
                                        class="form-check-label">

                                        J'accepte les conditions
                                        d'utilisation de NPM.

                                    </label>

                                </div>


                                <!-- Bouton -->

                                <button
                                    type="submit"
                                    class="btn btn-dark btn-lg w-100">

                                    <i class="bi bi-person-plus"></i>

                                    Créer mon compte

                                </button>


                            </form>


                            <!-- Connexion -->

                            <div class="text-center mt-4">

                                <p class="text-muted mb-0">

                                    Vous avez déjà un compte ?

                                    <a
                                        href="connexion.php"
                                        class="text-orange fw-bold text-decoration-none">

                                        Se connecter

                                    </a>

                                </p>

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


    <!-- Afficher le mot de passe -->

    <script>

        const password = document.getElementById("password");
        const voirPassword = document.getElementById("voirPassword");

        voirPassword.addEventListener("click", function () {

            if (password.type === "password") {

                password.type = "text";

                voirPassword.innerHTML =
                    '<i class="bi bi-eye-slash"></i>';

            } else {

                password.type = "password";

                voirPassword.innerHTML =
                    '<i class="bi bi-eye"></i>';

            }

        });

    </script>

</body>

</html>