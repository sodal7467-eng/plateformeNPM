<?php

session_start();

require_once "config/database.php";

$message = "";
$erreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom = trim($_POST["nom"] ?? "");
    $telephone = trim($_POST["telephone"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $motDePasse = $_POST["password"] ?? "";
    $confirmation = $_POST["confirmation"] ?? "";
    $conditions = isset($_POST["conditions"]);


    // Vérifier les champs (l'email est facultatif)
    if (
        empty($nom) ||
        empty($telephone) ||
        empty($motDePasse) ||
        empty($confirmation)
    ) {

        $erreur = "Veuillez remplir tous les champs obligatoires.";

    } elseif (!$conditions) {

        $erreur = "Vous devez accepter les conditions d'utilisation.";

    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $erreur = "Veuillez saisir une adresse email valide.";

    } elseif ($motDePasse !== $confirmation) {

        $erreur = "Les deux mots de passe ne correspondent pas.";

    } elseif (strlen($motDePasse) < 6) {

        $erreur = "Le mot de passe doit contenir au moins 6 caractères.";

    } else {

        // Vérifier si le numéro de téléphone existe déjà
        $verification = $pdo->prepare("
            SELECT id
            FROM utilisateurs
            WHERE telephone = ?
        ");

        $verification->execute([$telephone]);


        // Vérifier si l'email (s'il est renseigné) existe déjà
        $emailDejaUtilise = false;

        if (!empty($email)) {

            $verificationEmail = $pdo->prepare("
                SELECT id
                FROM utilisateurs
                WHERE email = ?
            ");

            $verificationEmail->execute([$email]);

            $emailDejaUtilise = (bool) $verificationEmail->fetch();
        }


        if ($verification->fetch()) {

            $erreur = "Ce numéro de téléphone est déjà utilisé.";

        } elseif ($emailDejaUtilise) {

            $erreur = "Cette adresse email est déjà utilisée.";

        } else {

            // CRYPTER LE MOT DE PASSE
            $motDePasseHash = password_hash(
                $motDePasse,
                PASSWORD_DEFAULT
            );


            // Insérer l'utilisateur (email facultatif)
            $requete = $pdo->prepare("
                INSERT INTO utilisateurs
                (nom, telephone, email, mot_de_passe)
                VALUES (?, ?, ?, ?)
            ");

            $requete->execute([
                $nom,
                $telephone,
                $email !== "" ? $email : null,
                $motDePasseHash
            ]);


            $message = "Votre compte a été créé avec succès.";

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
        content="width=device-width, initial-scale=1.0">

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

    <link
        rel="stylesheet"
        href="assets/css/style.css">

</head>


<body>


<?php include "includes/navbar.php"; ?>


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


                        <!-- Message succès -->

                        <?php if (!empty($message)): ?>

                            <div
                                class="alert alert-success"
                                role="alert">

                                <i class="bi bi-check-circle me-2"></i>

                                <?= htmlspecialchars($message) ?>

                                <div class="mt-3">

                                    <a
                                        href="connexion.php"
                                        class="btn btn-dark">

                                        Se connecter

                                    </a>

                                </div>

                            </div>

                        <?php endif; ?>


                        <!-- Message erreur -->

                        <?php if (!empty($erreur)): ?>

                            <div
                                class="alert alert-danger"
                                role="alert">

                                <i class="bi bi-exclamation-circle me-2"></i>

                                <?= htmlspecialchars($erreur) ?>

                            </div>

                        <?php endif; ?>


                        <?php if (empty($message)): ?>

                        <!-- Formulaire -->

                        <form
                            action="inscription.php"
                            method="POST">


                            <!-- Nom -->

                            <div class="mb-3">

                                <label
                                    for="nom"
                                    class="form-label fw-bold">

                                    Nom complet

                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">

                                        <i class="bi bi-person"></i>

                                    </span>

                                    <input
                                        type="text"
                                        name="nom"
                                        id="nom"
                                        class="form-control"
                                        placeholder="Votre nom complet"
                                        value="<?= htmlspecialchars($_POST["nom"] ?? "") ?>"
                                        required>

                                </div>

                            </div>


                            <!-- Téléphone -->

                            <div class="mb-3">

                                <label
                                    for="telephone"
                                    class="form-label fw-bold">

                                    Numéro de téléphone

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
                                        placeholder="Ex : 77 123 45 67"
                                        value="<?= htmlspecialchars($_POST["telephone"] ?? "") ?>"
                                        required>

                                </div>

                            </div>


                            <!-- Email (facultatif) -->

                            <div class="mb-3">

                                <label
                                    for="email"
                                    class="form-label fw-bold">

                                    Adresse email
                                    <span class="text-muted fw-normal">(facultatif)</span>

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
                                        value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">

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


                            <!-- Confirmation -->

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

                                <i class="bi bi-person-plus me-1"></i>

                                Créer mon compte

                            </button>


                        </form>

                        <?php endif; ?>


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


<?php include "includes/footer.php"; ?>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


<script>

const password = document.getElementById("password");
const voirPassword = document.getElementById("voirPassword");

if (password && voirPassword) {

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

}

</script>

</body>

</html>