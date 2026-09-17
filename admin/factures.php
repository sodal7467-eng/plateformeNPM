<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../config/database.php");

/*
|--------------------------------------------------------------------------
| Récupérer les factures
|--------------------------------------------------------------------------
*/

$requete = $pdo->query("
    SELECT
        f.id,
        f.commande_id,
        f.numero_facture,
        f.date_facture,
        f.montant_total,
        f.montant_paye,
        f.reste_a_payer,
        f.statut,
        u.nom AS client_nom,
        u.email AS client_email
    FROM factures f
    INNER JOIN commandes c
        ON f.commande_id = c.id
    INNER JOIN utilisateurs u
        ON c.utilisateur_id = u.id
    ORDER BY f.date_facture DESC
");

$factures = $requete->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Statistiques
|--------------------------------------------------------------------------
*/

$totalFactures = count($factures);

$totalFacture = 0;
$totalPaye = 0;
$totalReste = 0;

$facturesPayees = 0;
$facturesImpayees = 0;
$facturesPartielles = 0;

foreach ($factures as $facture) {

    $totalFacture += (float) $facture["montant_total"];
    $totalPaye += (float) $facture["montant_paye"];
    $totalReste += (float) $facture["reste_a_payer"];

    if ($facture["statut"] === "Payée") {
        $facturesPayees++;
    }

    if ($facture["statut"] === "Impayée") {
        $facturesImpayees++;
    }

    if ($facture["statut"] === "Partiellement payée") {
        $facturesPartielles++;
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

    <title>Factures - Administration NPM</title>

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

    <!-- CSS principal -->
    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body>

<?php require_once("includes/navbar.php"); ?>


<div class="admin-layout">

    <?php require_once("includes/sidebar.php"); ?>
    <?php require_once("includes/auth.php");?> 


    <main class="admin-content">

        <div class="container-fluid py-5 px-4">

        <!-- =====================================================
             EN-TÊTE
        ====================================================== -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1 class="fw-bold mb-1">
                    <i class="bi bi-receipt me-2"></i>
                    Factures
                </h1>

                <p class="text-muted mb-0">
                    Gérez les factures de vos clients.
                </p>

            </div>

            <div>

                <span class="badge bg-dark fs-6 px-3 py-2">

                    <?= $totalFactures ?>

                    facture<?= $totalFactures > 1 ? "s" : "" ?>

                </span>

            </div>

        </div>


        <!-- =====================================================
             STATISTIQUES
        ====================================================== -->

        <div class="row g-4 mb-4">

            <!-- Total factures -->

            <div class="col-xl-3 col-md-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between">

                            <div>

                                <p class="text-muted mb-1">
                                    Total factures
                                </p>

                                <h3 class="fw-bold mb-0">
                                    <?= $totalFactures ?>
                                </h3>

                            </div>

                            <div
                                class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center"
                                style="width:50px;height:50px;"
                            >

                                <i class="bi bi-receipt fs-4"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Montant total -->

            <div class="col-xl-3 col-md-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between">

                            <div>

                                <p class="text-muted mb-1">
                                    Montant facturé
                                </p>

                                <h3 class="fw-bold mb-0">

                                    <?= number_format(
                                        $totalFacture,
                                        0,
                                        ",",
                                        " "
                                    ) ?>

                                    <small>FCFA</small>

                                </h3>

                            </div>

                            <div
                                class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                style="width:50px;height:50px;"
                            >

                                <i class="bi bi-cash-stack fs-4"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Montant payé -->

            <div class="col-xl-3 col-md-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between">

                            <div>

                                <p class="text-muted mb-1">
                                    Montant payé
                                </p>

                                <h3 class="fw-bold mb-0">

                                    <?= number_format(
                                        $totalPaye,
                                        0,
                                        ",",
                                        " "
                                    ) ?>

                                    <small>FCFA</small>

                                </h3>

                            </div>

                            <div
                                class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                                style="width:50px;height:50px;"
                            >

                                <i class="bi bi-check-circle fs-4"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Reste à payer -->

            <div class="col-xl-3 col-md-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between">

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

                                    <small>FCFA</small>

                                </h3>

                            </div>

                            <div
                                class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center"
                                style="width:50px;height:50px;"
                            >

                                <i class="bi bi-exclamation-circle fs-4"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =====================================================
             RÉSUMÉ DES STATUTS
        ====================================================== -->

        <div class="row g-3 mb-4">

            <div class="col-md-4">

                <div class="card border-0 shadow-sm">

                    <div class="card-body">

                        <div class="d-flex justify-content-between">

                            <span>
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Factures payées
                            </span>

                            <strong>
                                <?= $facturesPayees ?>
                            </strong>

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card border-0 shadow-sm">

                    <div class="card-body">

                        <div class="d-flex justify-content-between">

                            <span>
                                <i class="bi bi-clock text-warning me-2"></i>
                                Paiements partiels
                            </span>

                            <strong>
                                <?= $facturesPartielles ?>
                            </strong>

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card border-0 shadow-sm">

                    <div class="card-body">

                        <div class="d-flex justify-content-between">

                            <span>
                                <i class="bi bi-x-circle text-danger me-2"></i>
                                Factures impayées
                            </span>

                            <strong>
                                <?= $facturesImpayees ?>
                            </strong>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =====================================================
             TABLEAU DES FACTURES
        ====================================================== -->

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white py-3">

                <div class="d-flex justify-content-between align-items-center">

                    <h5 class="fw-bold mb-0">

                        <i class="bi bi-list-ul me-2"></i>

                        Liste des factures

                    </h5>

                </div>

            </div>


            <div class="card-body p-0">

                <?php if (empty($factures)): ?>

                    <div class="text-center py-5">

                        <i
                            class="bi bi-receipt"
                            style="font-size:60px;"
                        ></i>

                        <h4 class="mt-3">
                            Aucune facture
                        </h4>

                        <p class="text-muted">
                            Aucune facture n'a encore été créée.
                        </p>

                    </div>

                <?php else: ?>

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-dark">

                                <tr>

                                    <th>
                                        N° Facture
                                    </th>

                                    <th>
                                        Client
                                    </th>

                                    <th>
                                        Commande
                                    </th>

                                    <th>
                                        Montant
                                    </th>

                                    <th>
                                        Payé
                                    </th>

                                    <th>
                                        Reste
                                    </th>

                                    <th class="text-center">
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

                            <?php foreach ($factures as $facture): ?>

                                <tr>

                                    <!-- NUMÉRO -->

                                    <td>

                                        <span class="fw-bold">

                                            <?= htmlspecialchars(
                                                $facture["numero_facture"]
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- CLIENT -->

                                    <td>

                                        <div class="fw-semibold">

                                            <?= htmlspecialchars(
                                                $facture["client_nom"]
                                            ) ?>

                                        </div>

                                        <small class="text-muted">

                                            <?= htmlspecialchars(
                                                $facture["client_email"]
                                            ) ?>

                                        </small>

                                    </td>


                                    <!-- COMMANDE -->

                                    <td>

                                        <span class="badge bg-secondary">

                                            #<?= (int) $facture["commande_id"] ?>

                                        </span>

                                    </td>


                                    <!-- MONTANT -->

                                    <td>

                                        <strong>

                                            <?= number_format(
                                                $facture["montant_total"],
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
                                                $facture["montant_paye"],
                                                0,
                                                ",",
                                                " "
                                            ) ?>

                                            FCFA

                                        </span>

                                    </td>


                                    <!-- RESTE -->

                                    <td>

                                        <?php if (
                                            (float) $facture["reste_a_payer"] > 0
                                        ): ?>

                                            <span class="text-danger fw-semibold">

                                                <?= number_format(
                                                    $facture["reste_a_payer"],
                                                    0,
                                                    ",",
                                                    " "
                                                ) ?>

                                                FCFA

                                            </span>

                                        <?php else: ?>

                                            <span class="text-success fw-semibold">

                                                0 FCFA

                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- STATUT -->

                                    <td class="text-center">

                                        <?php

                                        $statut = $facture["statut"];

                                        if ($statut === "Payée") {

                                            $classe = "bg-success";
                                            $icone = "bi-check-circle";

                                        } elseif (
                                            $statut === "Partiellement payée"
                                        ) {

                                            $classe = "bg-warning text-dark";
                                            $icone = "bi-clock";

                                        } elseif (
                                            $statut === "Impayée"
                                        ) {

                                            $classe = "bg-danger";
                                            $icone = "bi-x-circle";

                                        } else {

                                            $classe = "bg-secondary";
                                            $icone = "bi-info-circle";

                                        }

                                        ?>

                                        <span class="badge <?= $classe ?>">

                                            <i
                                                class="bi <?= $icone ?> me-1"
                                            ></i>

                                            <?= htmlspecialchars($statut) ?>

                                        </span>

                                    </td>


                                    <!-- DATE -->

                                    <td>

                                        <?= date(
                                            "d/m/Y",
                                            strtotime(
                                                $facture["date_facture"]
                                            )
                                        ) ?>

                                        <br>

                                        <small class="text-muted">

                                            <?= date(
                                                "H:i",
                                                strtotime(
                                                    $facture["date_facture"]
                                                )
                                            ) ?>

                                        </small>

                                    </td>


                                    <!-- ACTIONS -->

                                    <td class="text-center">

                                        <!-- Voir la facture -->

                                        <a
                                            href="facture.php?id=<?= (int) $facture["id"] ?>"
                                            class="btn btn-sm btn-outline-dark"
                                            title="Voir la facture"
                                        >

                                            <i class="bi bi-eye"></i>

                                        </a>


                                        <!-- Imprimer -->

                                        <a
                                            href="facture.php?id=<?= (int) $facture["id"] ?>&print=1"
                                            target="_blank"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Imprimer"
                                        >

                                            <i class="bi bi-printer"></i>

                                        </a>

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

</div>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>