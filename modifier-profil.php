<?php

session_start();

require_once "config/database.php";


// =========================================================
// VÉRIFIER SI LE CLIENT EST CONNECTÉ
// =========================================================

if (!isset($_SESSION["id"])) {

    header("Location: connexion.php");
    exit;

}

$idClient = (int) $_SESSION["id"];


// =========================================================
// RÉCUPÉRER LES INFORMATIONS DU CLIENT
// =========================================================

$requete = $pdo->prepare("
    SELECT id, nom, email, date_creation
    FROM utilisateurs
    WHERE id = ?
");

$requete->execute([$idClient]);

$utilisateur = $requete->fetch(PDO::FETCH_ASSOC);


// Vérifier que le client existe

if (!$utilisateur) {

    session_unset();
    session_destroy();

    header("Location: connexion.php");
    exit;

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

    <title>
        Mon profil - Quincaillerie NPM
    </title>


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


    <!-- CSS du site -->

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>


<body>


<?php include "includes/navbar.php"; ?>


<main>

<section class="py-5">

<div class="container">


<!-- =========================================================
     EN-TÊTE
========================================================= -->

<div class="text-center mb-5">

    <div
        class="rounded-circle bg-warning d-inline-flex
        align-items-center justify-content-center"
        style="width:80px;height:80px;"
    >

        <i class="bi bi-person-fill fs-1 text-dark"></i>

    </div>


    <h1 class="fw-bold mt-3">

        Mon profil

    </h1>


    <p class="text-muted">

        Vos informations personnelles.

    </p>

</div>



<!-- =========================================================
     INFORMATIONS CLIENT
========================================================= -->

<div class="card border-0 shadow-sm mb-5">

    <div class="card-body p-4 p-md-5">


        <h3 class="fw-bold mb-4">

            <i class="bi bi-person-circle me-2"></i>

            <?= htmlspecialchars($utilisateur["nom"]) ?>

        </h3>


        <div class="row g-4">


            <!-- Nom -->

            <div class="col-md-6">

                <div class="border rounded p-3">

                    <small class="text-muted">
                        Nom
                    </small>

                    <h5 class="mb-0 mt-1">

                        <?= htmlspecialchars(
                            $utilisateur["nom"]
                        ) ?>

                    </h5>

                </div>

            </div>


            <!-- Email -->

            <div class="col-md-6">

                <div class="border rounded p-3">

                    <small class="text-muted">
                        Adresse email
                    </small>

                    <h5 class="mb-0 mt-1">

                        <?= htmlspecialchars(
                            $utilisateur["email"]
                        ) ?>

                    </h5>

                </div>

            </div>


            <!-- Date inscription -->

            <div class="col-md-6">

                <div class="border rounded p-3">

                    <small class="text-muted">
                        Client depuis
                    </small>

                    <h5 class="mb-0 mt-1">

                        <?= date(
                            "d/m/Y",
                            strtotime(
                                $utilisateur["date_creation"]
                            )
                        ) ?>

                    </h5>

                </div>

            </div>


            <!-- Numéro client -->

            <div class="col-md-6">

                <div class="border rounded p-3">

                    <small class="text-muted">
                        Numéro client
                    </small>

                    <h5 class="mb-0 mt-1">

                        #<?= htmlspecialchars(
                            $utilisateur["id"]
                        ) ?>

                    </h5>

                </div>

            </div>


        </div>

    </div>

</div>



<!-- =========================================================
     BOUTONS
========================================================= -->

<div class="text-center mt-5">


    <a
        href="profil.php"
        class="btn btn-outline-dark me-2"
    >

        <i class="bi bi-arrow-left me-1"></i>

        Retour à mon compte

    </a>


    <a
        href="deconnexion.php"
        class="btn btn-danger"
    >

        <i class="bi bi-box-arrow-right me-1"></i>

        Déconnexion

    </a>

</div>


</div>

</section>

</main>



<?php include "includes/footer.php"; ?>



<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>
