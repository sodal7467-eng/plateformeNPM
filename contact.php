<?php

session_start();

require_once "config/database.php";

$messageSucces = "";
$messageErreur = "";

$nom = "";
$email = "";
$telephone = "";
$sujet = "";
$message = "";


// =========================================================
// TRAITEMENT DU FORMULAIRE
// =========================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom = trim($_POST["nom"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telephone = trim($_POST["telephone"] ?? "");
    $sujet = trim($_POST["sujet"] ?? "");
    $message = trim($_POST["message"] ?? "");


    // Vérification des champs
    if (
        empty($nom) ||
        empty($email) ||
        empty($sujet) ||
        empty($message)
    ) {

        $messageErreur = "Veuillez remplir tous les champs.";

    }

    // Vérification de l'email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $messageErreur = "Veuillez entrer une adresse email valide.";

    }

    else {

        try {

            // Enregistrer le message dans la base de données
            $requete = $pdo->prepare("
                INSERT INTO messages
                (nom, email, telephone, sujet, message)
                VALUES (?, ?, ?, ?, ?)
            ");

            $requete->execute([
                $nom,
                $email,
                $telephone !== "" ? $telephone : null,
                $sujet,
                $message
            ]);


            // Message de confirmation
            $messageSucces =
                "Merci " . $nom .
                " ! Votre message a bien été envoyé.";

            // Vider les champs
            $nom = "";
            $email = "";
            $telephone = "";
            $sujet = "";
            $message = "";

        } catch (PDOException $e) {

            $messageErreur =
                "Une erreur est survenue lors de l'envoi du message.";
        }
    }
}

?>

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contact - NPM Quincaillerie</title>

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
            CONTACT
        </span>

        <h1 class="display-5 fw-bold mb-3">
            Contactez <span class="text-warning">NPM</span>
        </h1>

        <p class="lead text-light mx-auto" style="max-width: 700px;">
            Une question concernant nos produits ou votre commande ?
            Notre équipe est à votre écoute.
        </p>

    </div>

</section>


<!-- =========================================================
     CONTACT
========================================================= -->

<section class="py-5">

    <div class="container py-4">

        <div class="row g-4 g-lg-5">


            <!-- =================================================
                 INFORMATIONS
            ================================================== -->

            <div class="col-lg-5">

                <span class="text-warning fw-bold">
                    PARLONS-NOUS
                </span>

                <h2 class="fw-bold mt-2 mb-4">
                    Nous sommes à votre écoute
                </h2>

                <p class="text-muted mb-5">
                    N'hésitez pas à nous contacter pour toute question
                    concernant nos produits, vos commandes ou nos services.
                </p>


                <!-- Adresse -->

                <div class="d-flex align-items-start mb-4">

                    <div
                        class="bg-dark text-warning rounded-circle d-flex
                        align-items-center justify-content-center me-3"
                        style="width: 50px; height: 50px; min-width: 50px;"
                    >

                        <i class="bi bi-geo-alt-fill fs-5"></i>

                    </div>

                    <div>

                        <h5 class="fw-bold mb-1">
                            Adresse
                        </h5>

                        <p class="text-muted mb-0">
                           parcelle unite 26 <br> en face marche unite 23
                        </p>

                    </div>

                </div>


                <!-- Téléphone -->

                <div class="d-flex align-items-start mb-4">

                    <div
                        class="bg-dark text-warning rounded-circle d-flex
                        align-items-center justify-content-center me-3"
                        style="width: 50px; height: 50px; min-width: 50px;"
                    >

                        <i class="bi bi-telephone-fill fs-5"></i>

                    </div>

                    <div>

                        <h5 class="fw-bold mb-1">
                            Téléphone
                        </h5>

                        <p class="text-muted mb-0">
                           77 640 58 26
                        </p>

                    </div>

                </div>


                <!-- Email -->

                <div class="d-flex align-items-start mb-4">

                    <div
                        class="bg-dark text-warning rounded-circle d-flex
                        align-items-center justify-content-center me-3"
                        style="width: 50px; height: 50px; min-width: 50px;"
                    >

                        <i class="bi bi-envelope-fill fs-5"></i>

                    </div>

                    <div>

                        <h5 class="fw-bold mb-1">
                            Email
                        </h5>

                        <p class="text-muted mb-0">
                            Email de la Quincaillerie NPM
                        </p>

                    </div>

                </div>


                <!-- Horaires -->

                <div class="d-flex align-items-start">

                    <div
                        class="bg-dark text-warning rounded-circle d-flex
                        align-items-center justify-content-center me-3"
                        style="width: 50px; height: 50px; min-width: 50px;"
                    >

                        <i class="bi bi-clock-fill fs-5"></i>

                    </div>

                    <div>

                        <h5 class="fw-bold mb-1">
                            Horaires
                        </h5>

                        <p class="text-muted mb-0">
                            Lundi - Samedi : 08h00 - 18h00
                        </p>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 FORMULAIRE
            ================================================== -->

            <div class="col-lg-7">

                <div class="card border-0 shadow-sm rounded-4">

                    <div class="card-body p-4 p-lg-5">

                        <h3 class="fw-bold mb-2">
                            Envoyez-nous un message
                        </h3>

                        <p class="text-muted mb-4">
                            Remplissez le formulaire ci-dessous et
                            nous vous répondrons dès que possible.
                        </p>


                        <!-- Message succès -->

                        <?php if (!empty($messageSucces)): ?>

                            <div class="alert alert-success">

                                <i class="bi bi-check-circle-fill me-2"></i>

                                <?= htmlspecialchars($messageSucces) ?>

                            </div>

                        <?php endif; ?>


                        <!-- Message erreur -->

                        <?php if (!empty($messageErreur)): ?>

                            <div class="alert alert-danger">

                                <i class="bi bi-exclamation-circle-fill me-2"></i>

                                <?= htmlspecialchars($messageErreur) ?>

                            </div>

                        <?php endif; ?>


                        <!-- Formulaire -->

                        <form action="contact.php" method="POST">


                            <!-- Nom -->

                            <div class="mb-3">

                                <label for="nom" class="form-label fw-semibold">
                                    Nom complet
                                </label>

                                <input
                                    type="text"
                                    class="form-control form-control-lg"
                                    id="nom"
                                    name="nom"
                                    value="<?= htmlspecialchars($nom ?? "") ?>"
                                    placeholder="Votre nom"
                                    required
                                >

                            </div>


                            <!-- Email -->

                            <div class="mb-3">

                                <label for="email" class="form-label fw-semibold">
                                    Adresse email
                                </label>

                                <input
                                    type="email"
                                    class="form-control form-control-lg"
                                    id="email"
                                    name="email"
                                    value="<?= htmlspecialchars($email ?? "") ?>"
                                    placeholder="exemple@email.com"
                                    required
                                >

                            </div>


                            <!-- Téléphone -->

                            <div class="mb-3">

                                <label for="telephone" class="form-label fw-semibold">
                                    Téléphone (WhatsApp)
                                </label>

                                <input
                                    type="tel"
                                    class="form-control form-control-lg"
                                    id="telephone"
                                    name="telephone"
                                    value="<?= htmlspecialchars($telephone ?? "") ?>"
                                    placeholder="Ex : 77 000 00 00"
                                >

                                <small class="text-muted">
                                    Facultatif — pour qu'on puisse te répondre plus vite sur WhatsApp.
                                </small>

                            </div>


                            <!-- Sujet -->

                            <div class="mb-3">

                                <label for="sujet" class="form-label fw-semibold">
                                    Sujet
                                </label>

                                <input
                                    type="text"
                                    class="form-control form-control-lg"
                                    id="sujet"
                                    name="sujet"
                                    value="<?= htmlspecialchars($sujet ?? "") ?>"
                                    placeholder="Sujet de votre message"
                                    required
                                >

                            </div>


                            <!-- Message -->

                            <div class="mb-4">

                                <label for="message" class="form-label fw-semibold">
                                    Message
                                </label>

                                <textarea
                                    class="form-control"
                                    id="message"
                                    name="message"
                                    rows="6"
                                    placeholder="Écrivez votre message..."
                                    required
                                ><?= htmlspecialchars($message ?? "") ?></textarea>

                            </div>


                            <!-- Bouton -->

                            <button
                                type="submit"
                                class="btn btn-warning btn-lg w-100"
                            >

                                <i class="bi bi-send-fill me-2"></i>

                                Envoyer le message

                            </button>

                        </form>

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
            Vous cherchez un produit ?
        </h2>

        <p class="text-muted mb-4">
            Découvrez notre catalogue de produits de quincaillerie.
        </p>

        <a
            href="produits.php"
            class="btn btn-dark btn-lg px-4"
        >

            <i class="bi bi-box-seam me-2"></i>

            Voir nos produits

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