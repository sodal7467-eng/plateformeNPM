<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Vérifier l'ID de l'employé
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: employes.php");
    exit;
}

$idEmploye = (int) $_GET["id"];


/*
|--------------------------------------------------------------------------
| Récupérer l'employé
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare("
    SELECT
        id,
        nom,
        email,
        telephone,
        date_creation
    FROM utilisateurs
    WHERE id = ?
        AND role = 'employe'
");

$requete->execute([$idEmploye]);

$employe = $requete->fetch(PDO::FETCH_ASSOC);

if (!$employe) {
    header("Location: employes.php");
    exit;
}


require_once("includes/auth.php");

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($employe["nom"]) ?> - NPM Admin
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
                    <i class="bi bi-person-badge me-2"></i>
                    <?= htmlspecialchars($employe["nom"]) ?>
                </h1>

                <p class="text-muted mb-0">
                    Profil de l'employé.
                </p>

            </div>

            <a href="employes.php" class="btn btn-outline-dark">
                <i class="bi bi-arrow-left me-1"></i>
                Retour aux employés
            </a>

        </div>


        <!-- INFORMATIONS EMPLOYÉ -->

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0">
                    <i class="bi bi-person-vcard me-2"></i>
                    Informations de l'employé
                </h5>

            </div>

            <div class="card-body p-4">

                <div class="row g-4">

                    <div class="col-md-4">

                        <small class="text-muted d-block">Nom</small>

                        <strong>
                            <?= htmlspecialchars($employe["nom"]) ?>
                        </strong>

                    </div>

                    <div class="col-md-4">

                        <small class="text-muted d-block">Téléphone</small>

                        <?php if (!empty($employe["telephone"])): ?>

                            <i class="bi bi-telephone me-1"></i>
                            <?= htmlspecialchars($employe["telephone"]) ?>

                        <?php else: ?>

                            <span class="text-muted">—</span>

                        <?php endif; ?>

                    </div>

                    <div class="col-md-4">

                        <small class="text-muted d-block">Email</small>

                        <?php if (!empty($employe["email"])): ?>

                            <a href="mailto:<?= htmlspecialchars($employe["email"]) ?>">
                                <?= htmlspecialchars($employe["email"]) ?>
                            </a>

                        <?php else: ?>

                            <span class="text-muted">—</span>

                        <?php endif; ?>

                    </div>

                    <div class="col-md-4">

                        <small class="text-muted d-block">Employé depuis</small>

                        <?php if (!empty($employe["date_creation"])): ?>

                            <?= date("d/m/Y", strtotime($employe["date_creation"])) ?>

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
