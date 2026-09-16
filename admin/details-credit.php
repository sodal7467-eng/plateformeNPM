<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../config/database.php");


/*
|--------------------------------------------------------------------------
| Vérifier l'ID du crédit
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: credits.php");
    exit;
}

$id = (int) $_GET["id"];


/*
|--------------------------------------------------------------------------
| Récupérer le crédit
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare("
    SELECT
        credits_clients.*,
        utilisateurs.nom,
        utilisateurs.email
    FROM credits_clients
    INNER JOIN utilisateurs
        ON credits_clients.utilisateur_id = utilisateurs.id
    WHERE credits_clients.id = ?
");

$requete->execute([$id]);

$credit = $requete->fetch(PDO::FETCH_ASSOC);


if (!$credit) {
    header("Location: credits.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Récupérer l'historique des paiements
|--------------------------------------------------------------------------
*/

$requetePaiements = $pdo->prepare("
    SELECT
        id,
        montant,
        date_paiement,
        commentaire
    FROM paiements_credits
    WHERE credit_id = ?
    ORDER BY date_paiement DESC
");

$requetePaiements->execute([$id]);

$paiements = $requetePaiements->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Déterminer le statut
|--------------------------------------------------------------------------
*/

if ((float) $credit["reste_a_payer"] <= 0) {

    $statut = "Réglé";
    $badge = "bg-success";

} else {

    $statut = "En cours";
    $badge = "bg-warning text-dark";
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

    <title>Détails du crédit - NPM Admin</title>


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

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body class="bg-light">


<?php require_once("includes/navbar.php"); ?>

<?php require_once("includes/sidebar.php"); ?>

<?php require_once("includes/auth.php");?> 


<main class="admin-content">

    <div class="container-fluid py-4">


        <!-- En-tête -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1 class="fw-bold mb-1">

                    Détails du crédit

                </h1>

                <p class="text-muted mb-0">

                    Crédit #<?= $credit["id"] ?>

                </p>

            </div>


            <a
                href="credits.php"
                class="btn btn-dark"
            >

                <i class="bi bi-arrow-left me-2"></i>

                Retour aux crédits

            </a>

        </div>


        <!-- Informations client -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0">

                    <i class="bi bi-person me-2"></i>

                    Informations du client

                </h5>

            </div>


            <div class="card-body">

                <div class="row g-4">


                    <div class="col-md-6">

                        <small class="text-muted">
                            Nom du client
                        </small>

                        <div class="fw-semibold fs-5">

                            <?= htmlspecialchars($credit["nom"]) ?>

                        </div>

                    </div>


                    <div class="col-md-6">

                        <small class="text-muted">
                            Email
                        </small>

                        <div class="fw-semibold">

                            <?= htmlspecialchars($credit["email"]) ?>

                        </div>

                    </div>


                </div>

            </div>

        </div>


        <!-- Résumé financier -->

        <div class="row g-4 mb-4">


            <!-- Total -->

            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <small class="text-muted">
                            Montant total
                        </small>

                        <h3 class="fw-bold mt-2 mb-0">

                            <?= number_format(
                                $credit["montant_total"],
                                0,
                                ",",
                                " "
                            ) ?>

                            FCFA

                        </h3>

                    </div>

                </div>

            </div>


            <!-- Payé -->

            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <small class="text-muted">
                            Montant payé
                        </small>

                        <h3 class="fw-bold text-success mt-2 mb-0">

                            <?= number_format(
                                $credit["montant_paye"],
                                0,
                                ",",
                                " "
                            ) ?>

                            FCFA

                        </h3>

                    </div>

                </div>

            </div>


            <!-- Reste -->

            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <small class="text-muted">
                            Reste à payer
                        </small>

                        <h3 class="fw-bold text-danger mt-2 mb-0">

                            <?= number_format(
                                $credit["reste_a_payer"],
                                0,
                                ",",
                                " "
                            ) ?>

                            FCFA

                        </h3>

                    </div>

                </div>

            </div>


        </div>


        <!-- Informations crédit -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0">

                    <i class="bi bi-info-circle me-2"></i>

                    Informations du crédit

                </h5>

            </div>


            <div class="card-body">

                <div class="row g-4">


                    <div class="col-md-4">

                        <small class="text-muted">
                            Numéro du crédit
                        </small>

                        <div class="fw-semibold">

                            #<?= $credit["id"] ?>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <small class="text-muted">
                            Commande
                        </small>

                        <div class="fw-semibold">

                            <?php if (!empty($credit["commande_id"])): ?>

                                Commande #<?= $credit["commande_id"] ?>

                            <?php else: ?>

                                Aucune commande

                            <?php endif; ?>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <small class="text-muted">
                            Statut
                        </small>

                        <div>

                            <span class="badge <?= $badge ?>">

                                <?= $statut ?>

                            </span>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <small class="text-muted">
                            Date du crédit
                        </small>

                        <div class="fw-semibold">

                            <?= date(
                                "d/m/Y à H:i",
                                strtotime($credit["date_credit"])
                            ) ?>

                        </div>

                    </div>


                </div>

            </div>

        </div>


        <!-- Historique des paiements -->

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white py-3">

                <div class="d-flex justify-content-between align-items-center">

                    <h5 class="fw-bold mb-0">

                        <i class="bi bi-clock-history me-2"></i>

                        Historique des paiements

                    </h5>


                    <?php if ((float) $credit["reste_a_payer"] > 0): ?>

                        <a
                            href="ajouter-paiement.php?id=<?= $credit["id"] ?>"
                            class="btn btn-warning btn-sm"
                        >

                            <i class="bi bi-plus-lg me-1"></i>

                            Ajouter un paiement

                        </a>

                    <?php endif; ?>

                </div>

            </div>


            <div class="card-body p-0">


                <?php if (empty($paiements)): ?>

                    <div class="text-center py-5">

                        <i class="bi bi-wallet2 fs-1 text-muted"></i>

                        <p class="text-muted mt-2 mb-0">

                            Aucun paiement enregistré.

                        </p>

                    </div>


                <?php else: ?>


                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">


                            <thead class="table-light">

                                <tr>

                                    <th>#</th>

                                    <th>Montant</th>

                                    <th>Commentaire</th>

                                    <th>Date</th>

                                </tr>

                            </thead>


                            <tbody>


                            <?php foreach ($paiements as $paiement): ?>

                                <tr>

                                    <td>

                                        #<?= $paiement["id"] ?>

                                    </td>


                                    <td>

                                        <strong class="text-success">

                                            <?= number_format(
                                                $paiement["montant"],
                                                0,
                                                ",",
                                                " "
                                            ) ?>

                                            FCFA

                                        </strong>

                                    </td>


                                    <td>

                                        <?php if (!empty($paiement["commentaire"])): ?>

                                            <?= htmlspecialchars(
                                                $paiement["commentaire"]
                                            ) ?>

                                        <?php else: ?>

                                            <span class="text-muted">
                                                —
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <td>

                                        <?= date(
                                            "d/m/Y à H:i",
                                            strtotime($paiement["date_paiement"])
                                        ) ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>


                            </tbody>

                        </table>

                    </div>


                <?php endif; ?>


            </div>

        </div>


    </div>

</main>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>