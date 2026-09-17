<?php

session_start();

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Récupérer les crédits clients
|--------------------------------------------------------------------------
*/

$requete = $pdo->query(
    "SELECT 
        c.id,
        c.utilisateur_id,
        c.commande_id,
        c.montant_total,
        c.montant_paye,
        c.reste_a_payer,
        c.statut,
        c.date_credit,
        u.nom,
        u.email
     FROM credits_clients c
     INNER JOIN utilisateurs u
        ON c.utilisateur_id = u.id
     ORDER BY c.id DESC"
);

$credits = $requete->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Statistiques
|--------------------------------------------------------------------------
*/

$totalCredits = count($credits);

$totalDette = 0;
$totalPaye = 0;
$totalReste = 0;
$creditsEnCours = 0;
$creditsRegles = 0;

foreach ($credits as $credit) {

    $totalDette += $credit["montant_total"];
    $totalPaye += $credit["montant_paye"];
    $totalReste += $credit["reste_a_payer"];

    if ($credit["statut"] === "En cours") {
        $creditsEnCours++;
    }

    if ($credit["statut"] === "Réglé") {
        $creditsRegles++;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crédits clients - Administration NPM</title>

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

<body>


<?php require_once "includes/navbar.php"; ?>

<?php require_once "includes/sidebar.php"; ?>

<?php require_once("includes/auth.php");?> 


<!--
|--------------------------------------------------------------------------
| CONTENU ADMIN
|--------------------------------------------------------------------------
-->

<main class="admin-content">

    <div class="container-fluid py-4">


        <!-- EN-TÊTE -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1 class="fw-bold mb-1">
                    Crédits clients
                </h1>

                <p class="text-muted mb-0">
                    Gérez les sommes dues par vos clients.
                </p>

            </div>


            <!-- BOUTON AJOUTER -->

            <a
                href="ajouter-credit.php"
                class="btn btn-warning"
            >

                <i class="bi bi-plus-circle me-1"></i>

                Ajouter un crédit

            </a>

        </div>


        <!--
        |--------------------------------------------------------------------------
        | STATISTIQUES
        |--------------------------------------------------------------------------
        -->

        <div class="row g-4 mb-4">


            <!-- TOTAL DES CRÉDITS -->

            <div class="col-xl-3 col-md-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <p class="text-muted mb-1">
                                    Total des crédits
                                </p>

                                <h3 class="fw-bold mb-0">
                                    <?= $totalCredits ?>
                                </h3>

                            </div>

                            <div
                                class="rounded-circle bg-warning bg-opacity-10 p-3"
                            >

                                <i
                                    class="bi bi-journal-text fs-4 text-warning"
                                ></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- MONTANT TOTAL -->

            <div class="col-xl-3 col-md-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <p class="text-muted mb-1">
                                    Montant total
                                </p>

                                <h3 class="fw-bold mb-0">

                                    <?= number_format(
                                        $totalDette,
                                        0,
                                        ",",
                                        " "
                                    ) ?>

                                    <small class="fs-6">
                                        FCFA
                                    </small>

                                </h3>

                            </div>

                            <div
                                class="rounded-circle bg-dark bg-opacity-10 p-3"
                            >

                                <i
                                    class="bi bi-cash-stack fs-4"
                                ></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- TOTAL PAYÉ -->

            <div class="col-xl-3 col-md-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <p class="text-muted mb-1">
                                    Total payé
                                </p>

                                <h3 class="fw-bold mb-0">

                                    <?= number_format(
                                        $totalPaye,
                                        0,
                                        ",",
                                        " "
                                    ) ?>

                                    <small class="fs-6">
                                        FCFA
                                    </small>

                                </h3>

                            </div>

                            <div
                                class="rounded-circle bg-success bg-opacity-10 p-3"
                            >

                                <i
                                    class="bi bi-check-circle fs-4 text-success"
                                ></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- RESTE À PAYER -->

            <div class="col-xl-3 col-md-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <p class="text-muted mb-1">
                                    Reste à payer
                                </p>

                                <h3 class="fw-bold mb-0">

                                    <?= number_format(
                                        $totalReste,
                                        0,
                                        ",",
                                        " "
                                    ) ?>

                                    <small class="fs-6">
                                        FCFA
                                    </small>

                                </h3>

                            </div>

                            <div
                                class="rounded-circle bg-danger bg-opacity-10 p-3"
                            >

                                <i
                                    class="bi bi-exclamation-circle fs-4 text-danger"
                                ></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!--
        |--------------------------------------------------------------------------
        | INFORMATIONS RAPIDES
        |--------------------------------------------------------------------------
        -->

        <div class="row g-4 mb-4">


            <div class="col-md-6">

                <div class="alert alert-warning border-0 shadow-sm mb-0">

                    <div class="d-flex align-items-center">

                        <i class="bi bi-clock-history fs-4 me-3"></i>

                        <div>

                            <strong>
                                <?= $creditsEnCours ?>
                            </strong>

                            crédit(s) encore en cours.

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-md-6">

                <div class="alert alert-success border-0 shadow-sm mb-0">

                    <div class="d-flex align-items-center">

                        <i class="bi bi-check-circle fs-4 me-3"></i>

                        <div>

                            <strong>
                                <?= $creditsRegles ?>
                            </strong>

                            crédit(s) entièrement réglé(s).

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!--
        |--------------------------------------------------------------------------
        | TABLE DES CRÉDITS
        |--------------------------------------------------------------------------
        -->

        <div class="card border-0 shadow-sm">


            <!-- HEADER -->

            <div class="card-header bg-white border-0 py-3">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h5 class="fw-bold mb-1">

                            Liste des crédits

                        </h5>

                        <small class="text-muted">

                            Suivi des sommes dues par les clients

                        </small>

                    </div>


                    <span class="badge bg-dark">

                        <?= $totalCredits ?> crédit(s)

                    </span>

                </div>

            </div>


            <div class="card-body p-0">


                <?php if (empty($credits)): ?>


                    <!-- AUCUN CRÉDIT -->

                    <div class="text-center py-5">

                        <i
                            class="bi bi-wallet2 fs-1 text-muted"
                        ></i>

                        <h5 class="mt-3">
                            Aucun crédit
                        </h5>

                        <p class="text-muted">
                            Aucun client ne possède actuellement de crédit.
                        </p>


                        <a
                            href="ajouter-credit.php"
                            class="btn btn-warning"
                        >

                            <i class="bi bi-plus-circle me-1"></i>

                            Ajouter un crédit

                        </a>

                    </div>


                <?php else: ?>


                    <!-- TABLE -->

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">


                            <thead class="table-dark">

                                <tr>

                                    <th class="px-4">
                                        Client
                                    </th>

                                    <th>
                                        Commande
                                    </th>

                                    <th>
                                        Montant total
                                    </th>

                                    <th>
                                        Payé
                                    </th>

                                    <th>
                                        Reste
                                    </th>

                                    <th>
                                        Statut
                                    </th>

                                    <th>
                                        Date
                                    </th>

                                    <th class="text-center">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                            <?php foreach ($credits as $credit): ?>

                                <tr>


                                    <!-- CLIENT -->

                                    <td class="px-4">

                                        <div class="d-flex align-items-center gap-2">

                                            <div
                                                class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center"
                                                style="width:40px;height:40px;"
                                            >

                                                <?= strtoupper(
                                                    substr($credit["nom"], 0, 1)
                                                ) ?>

                                            </div>


                                            <div>

                                                <strong>

                                                    <?= htmlspecialchars(
                                                        $credit["nom"]
                                                    ) ?>

                                                </strong>

                                                <br>

                                                <small class="text-muted">

                                                    <?= htmlspecialchars(
                                                        $credit["email"]
                                                    ) ?>

                                                </small>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- COMMANDE -->

                                    <td>

                                        <?php if ($credit["commande_id"] !== null): ?>

                                            <span class="fw-semibold">

                                                #<?= $credit["commande_id"] ?>

                                            </span>

                                        <?php else: ?>

                                            <span class="text-muted">
                                                Aucune
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- MONTANT TOTAL -->

                                    <td>

                                        <strong>

                                            <?= number_format(
                                                $credit["montant_total"],
                                                0,
                                                ",",
                                                " "
                                            ) ?>

                                            FCFA

                                        </strong>

                                    </td>


                                    <!-- PAYÉ -->

                                    <td>

                                        <span class="text-success fw-semibold">

                                            <?= number_format(
                                                $credit["montant_paye"],
                                                0,
                                                ",",
                                                " "
                                            ) ?>

                                            FCFA

                                        </span>

                                    </td>


                                    <!-- RESTE -->

                                    <td>

                                        <?php if ($credit["reste_a_payer"] > 0): ?>

                                            <span class="text-danger fw-bold">

                                                <?= number_format(
                                                    $credit["reste_a_payer"],
                                                    0,
                                                    ",",
                                                    " "
                                                ) ?>

                                                FCFA

                                            </span>

                                        <?php else: ?>

                                            <span class="text-success fw-bold">

                                                0 FCFA

                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- STATUT -->

                                    <td>

                                        <?php if ($credit["statut"] === "En cours"): ?>

                                            <span class="badge bg-warning text-dark">

                                                <i class="bi bi-clock me-1"></i>

                                                En cours

                                            </span>


                                        <?php elseif ($credit["statut"] === "Réglé"): ?>

                                            <span class="badge bg-success">

                                                <i class="bi bi-check-circle me-1"></i>

                                                Réglé

                                            </span>


                                        <?php else: ?>

                                            <span class="badge bg-secondary">

                                                <?= htmlspecialchars(
                                                    $credit["statut"]
                                                ) ?>

                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- DATE -->

                                    <td>

                                        <small class="text-muted">

                                            <?= date(
                                                "d/m/Y H:i",
                                                strtotime($credit["date_credit"])
                                            ) ?>

                                        </small>

                                    </td>


                                    <!-- ACTIONS -->

                                    <td class="text-center">

                                        <!-- VOIR -->

                                        <a
                                            href="details-credit.php?id=<?= $credit["id"] ?>"
                                            class="btn btn-sm btn-outline-dark"
                                            title="Voir les détails"
                                        >

                                            <i class="bi bi-eye"></i>

                                        </a>


                                        <!-- PAIEMENT -->

                                        <?php if ($credit["reste_a_payer"] > 0): ?>

                                            <a
                                                href="ajouter-paiement.php?id=<?= $credit["id"] ?>"
                                                class="btn btn-sm btn-outline-success"
                                                title="Enregistrer un paiement"
                                            >

                                                <i class="bi bi-cash"></i>

                                            </a>

                                        <?php endif; ?>


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


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>