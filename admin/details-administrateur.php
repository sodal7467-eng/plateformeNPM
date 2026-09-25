<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("includes/auth.php");
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Vérifier l'ID de l'administrateur
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: administrateurs.php");
    exit;
}

$idAdministrateur = (int) $_GET["id"];


/*
|--------------------------------------------------------------------------
| Récupérer l'administrateur
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare("
    SELECT
        id,
        nom,
        email,
        date_creation
    FROM utilisateurs
    WHERE id = ?
        AND role = 'admin'
");

$requete->execute([$idAdministrateur]);

$administrateur = $requete->fetch(PDO::FETCH_ASSOC);

if (!$administrateur) {
    header("Location: administrateurs.php");
    exit;
}



?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($administrateur["nom"]) ?> - NPM Admin
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

    <!-- CSS NPM -->
    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body>


<?php require_once("includes/navbar.php"); ?>

<?php require_once("includes/sidebar.php"); ?>


<main class="admin-content">

    <div class="container-fluid py-5 px-4">


        <!-- TITRE -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1 class="fw-bold mb-1">
                    <i class="bi bi-shield-lock me-2"></i>
                    <?= htmlspecialchars($administrateur["nom"]) ?>

                    <?php if (
                        isset($_SESSION["id"])
                        && (int) $_SESSION["id"] === (int) $administrateur["id"]
                    ): ?>

                        <span class="badge bg-secondary align-middle">Vous</span>

                    <?php endif; ?>

                </h1>

                <p class="text-muted mb-0">
                    Profil de l'administrateur.
                </p>

            </div>

            <a href="administrateurs.php" class="btn btn-outline-dark">
                <i class="bi bi-arrow-left me-1"></i>
                Retour aux administrateurs
            </a>

        </div>


        <!-- INFORMATIONS ADMINISTRATEUR -->

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0">
                    <i class="bi bi-person-vcard me-2"></i>
                    Informations de l'administrateur
                </h5>

            </div>

            <div class="card-body p-4">

                <div class="row g-4">

                    <div class="col-md-4">

                        <small class="text-muted d-block">Nom</small>

                        <strong>
                            <?= htmlspecialchars($administrateur["nom"]) ?>
                        </strong>

                    </div>

                    <div class="col-md-4">

                        <small class="text-muted d-block">Email</small>

                        <a href="mailto:<?= htmlspecialchars($administrateur["email"]) ?>">
                            <?= htmlspecialchars($administrateur["email"]) ?>
                        </a>

                    </div>

                    <div class="col-md-4">

                        <small class="text-muted d-block">Administrateur depuis</small>

                        <?php if (!empty($administrateur["date_creation"])): ?>

                            <?= date("d/m/Y", strtotime($administrateur["date_creation"])) ?>

                        <?php else: ?>

                            —

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>


    </div>

</main>


<!-- Bootstrap JS -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>
