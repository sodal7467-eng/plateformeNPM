<?php

session_start();

require_once "config/database.php";


// Message d'erreur
$erreur = "";


// Si le formulaire est envoyé
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $motDePasse = $_POST["password"] ?? "";


    // Vérification des champs
    if (empty($email) || empty($motDePasse)) {

        $erreur = "Veuillez remplir tous les champs.";

    } else {

        // Rechercher l'utilisateur
        $requete = $pdo->prepare("
            SELECT *
            FROM utilisateurs
            WHERE email = ?
        ");

        $requete->execute([$email]);

        $utilisateur = $requete->fetch(PDO::FETCH_ASSOC);


        // Vérifier l'utilisateur et le mot de passe
        if (
            $utilisateur
            && password_verify(
                $motDePasse,
                $utilisateur["mot_de_passe"]
            )
        ) {

            // Connexion réussie
            $_SESSION["id"] = $utilisateur["id"];
            $_SESSION["nom"] = $utilisateur["nom"];
            $_SESSION["email"] = $utilisateur["email"];


            // Redirection
            header("Location: index.php");
            exit;

        } else {

            $erreur = "Email ou mot de passe incorrect.";

        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Connexion - Quincaillerie NPM</title>


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


    <!-- CSS -->

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>


<body>


<!-- =========================================================
     NAVBAR
========================================================== -->

<?php include "includes/navbar.php"; ?>



<!-- =========================================================
     CONNEXION
========================================================== -->

<main>

    <section class="connexion-section py-5">

        <div class="container">

            <div class="row justify-content-center">

                <div class="col-md-8 col-lg-5">


                    <div class="connexion-card">


                        <!-- Icône -->

                        <div class="connexion-icon">

                            <i class="bi bi-person"></i>

                        </div>



                        <!-- Titre -->

                        <div class="text-center mb-4">

                            <span class="text-orange fw-bold">

                                ESPACE CLIENT

                            </span>


                            <h1 class="fw-bold mt-2">

                                Connexion

                            </h1>


                            <p class="text-muted">

                                Connectez-vous à votre compte NPM.

                            </p>

                        </div>



                        <!-- Message erreur -->

                        <?php if (!empty($erreur)): ?>

                            <div
                                class="alert alert-danger d-flex align-items-center"
                                role="alert"
                            >

                                <i class="bi bi-exclamation-circle me-2"></i>

                                <?= htmlspecialchars($erreur) ?>

                            </div>

                        <?php endif; ?>



                        <!-- Formulaire -->

                        <form
                            action="connexion.php"
                            method="POST"
                        >


                            <!-- Email -->

                            <div class="mb-3">

                                <label
                                    for="email"
                                    class="form-label fw-bold"
                                >

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
                                        value="<?= htmlspecialchars($_POST["email"] ?? "") ?>"
                                        required
                                    >

                                </div>

                            </div>



                            <!-- Mot de passe -->

                            <div class="mb-3">

                                <label
                                    for="password"
                                    class="form-label fw-bold"
                                >

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
                                        placeholder="Votre mot de passe"
                                        required
                                    >


                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        id="voirPassword"
                                    >

                                        <i class="bi bi-eye"></i>

                                    </button>

                                </div>

                            </div>



                            <!-- Se souvenir -->

                            <div
                                class="d-flex justify-content-between align-items-center mb-4"
                            >

                                <div class="form-check">

                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        id="remember"
                                        name="remember"
                                    >


                                    <label
                                        for="remember"
                                        class="form-check-label"
                                    >

                                        Se souvenir de moi

                                    </label>

                                </div>


                                <a
                                    href="#"
                                    class="text-orange text-decoration-none"
                                >

                                    Mot de passe oublié ?

                                </a>

                            </div>



                            <!-- Connexion -->

                            <button
                                type="submit"
                                class="btn btn-dark btn-lg w-100"
                            >

                                <i class="bi bi-box-arrow-in-right me-1"></i>

                                Se connecter

                            </button>


                        </form>



                        <!-- Inscription -->

                        <div class="text-center mt-4">

                            <p class="text-muted mb-0">

                                Vous n'avez pas encore de compte ?

                                <a
                                    href="inscription.php"
                                    class="text-orange fw-bold text-decoration-none"
                                >

                                    Créer un compte

                                </a>

                            </p>

                        </div>


                    </div>

                </div>

            </div>

        </div>

    </section>

</main>



<!-- =========================================================
     FOOTER
========================================================== -->

<?php include "includes/footer.php"; ?>



<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>



<!-- Afficher / cacher le mot de passe -->

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