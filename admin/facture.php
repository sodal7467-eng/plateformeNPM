<?php

session_start();

require_once "../config/database.php";


// =========================================================
// VÉRIFIER L'ID DE LA FACTURE
// =========================================================

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: factures.php");
    exit;
}

$factureId = (int) $_GET["id"];


// =========================================================
// RÉCUPÉRER LA FACTURE
// =========================================================

$requete = $pdo->prepare("
    SELECT
        factures.*,

        commandes.id AS commande_id,
        commandes.statut AS commande_statut,
        commandes.date_commande,

        utilisateurs.nom AS client_nom,
        utilisateurs.email AS client_email

    FROM factures

    INNER JOIN commandes
        ON factures.commande_id = commandes.id

    INNER JOIN utilisateurs
        ON commandes.utilisateur_id = utilisateurs.id

    WHERE factures.id = ?
");

$requete->execute([$factureId]);

$facture = $requete->fetch(PDO::FETCH_ASSOC);


// =========================================================
// VÉRIFIER SI LA FACTURE EXISTE
// =========================================================

if (!$facture) {

    header("Location: factures.php");
    exit;
}


// =========================================================
// RÉCUPÉRER LES PRODUITS DE LA COMMANDE
// =========================================================

$requeteProduits = $pdo->prepare("
    SELECT
        commande_details.*,

        produits.nom AS produit_nom,
        produits.image AS produit_image

    FROM commande_details

    INNER JOIN produits
        ON commande_details.produit_id = produits.id

    WHERE commande_details.commande_id = ?

    ORDER BY commande_details.id ASC
");

$requeteProduits->execute([
    $facture["commande_id"]
]);

$produits = $requeteProduits->fetchAll(PDO::FETCH_ASSOC);


// =========================================================
// MONTANTS
// =========================================================

$montantTotal = (float) $facture["montant_total"];

$montantPaye = (float) $facture["montant_paye"];

$resteAPayer = (float) $facture["reste_a_payer"];


// =========================================================
// STATUT
// =========================================================

$statut = $facture["statut"];

if ($statut === "Payée") {

    $statutClasse = "success";

} elseif ($statut === "Partiellement payée") {

    $statutClasse = "warning";

} else {

    $statutClasse = "danger";
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
        <?= htmlspecialchars($facture["numero_facture"]) ?>
        - Quincaillerie NPM
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


    <!-- CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <style>

        body {
            background: #f5f7fa;
        }

        .invoice-container {
            max-width: 950px;
            margin: 40px auto;
        }

        .invoice-card {
            background: #ffffff;
            padding: 45px;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
        }

        .invoice-logo {
            font-size: 32px;
            font-weight: 800;
            color: #f28c28;
            letter-spacing: 1px;
        }

        .invoice-title {
            font-size: 34px;
            font-weight: 800;
            color: #101828;
        }

        .invoice-number {
            color: #667085;
            font-size: 15px;
        }

        .invoice-info {
            background: #f8fafc;
            border-radius: 10px;
            padding: 20px;
        }

        .invoice-table th {
            background: #101828;
            color: white;
            padding: 14px;
        }

        .invoice-table td {
            padding: 14px;
            vertical-align: middle;
        }

        .invoice-total {
            background: #f8fafc;
            border-radius: 10px;
            padding: 20px;
        }

        .grand-total {
            font-size: 25px;
            font-weight: 800;
            color: #f28c28;
        }

        .invoice-footer {
            border-top: 1px solid #e4e7ec;
            margin-top: 35px;
            padding-top: 20px;
            text-align: center;
            color: #667085;
        }


        /* =================================================
           IMPRESSION
        ================================================= */

        @media print {

            body {
                background: white !important;
            }

            .no-print {
                display: none !important;
            }

            .invoice-container {
                margin: 0;
                max-width: 100%;
            }

            .invoice-card {
                box-shadow: none;
                border-radius: 0;
                padding: 20px;
            }

        }


        /* =================================================
           MOBILE
        ================================================= */

        @media (max-width: 768px) {

            .invoice-card {
                padding: 25px 18px;
            }

            .invoice-title {
                font-size: 27px;
            }

            .invoice-logo {
                font-size: 27px;
            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     BOUTONS ADMIN
===================================================== -->

<div class="container mt-4 no-print">

    <div class="d-flex justify-content-between align-items-center">

        <a
            href="factures.php"
            class="btn btn-outline-dark"
        >

            <i class="bi bi-arrow-left me-1"></i>

            Retour aux factures

        </a>


        <button
            onclick="window.print()"
            class="btn btn-dark"
        >

            <i class="bi bi-printer me-1"></i>

            Imprimer la facture

        </button>

    </div>

</div>


<!-- =====================================================
     FACTURE
===================================================== -->

<div class="invoice-container">

    <div class="invoice-card">


        <!-- =================================================
             EN-TÊTE
        ================================================= -->

        <div class="row align-items-start mb-5">

            <div class="col-md-6">

                <div class="invoice-logo">
                    NPM
                </div>

                <h5 class="fw-bold mb-2">
                    Quincaillerie NPM
                </h5>

                <p class="text-muted mb-1">
                    Votre partenaire en quincaillerie
                </p>

                <p class="text-muted mb-0">
                    Outils • Matériaux • Équipements
                </p>

            </div>


            <div class="col-md-6 text-md-end mt-4 mt-md-0">

                <div class="invoice-title">
                    FACTURE
                </div>

                <div class="invoice-number">

                    <?= htmlspecialchars(
                        $facture["numero_facture"]
                    ) ?>

                </div>

                <div class="mt-2">

                    <strong>
                        Date :
                    </strong>

                    <?= date(
                        "d/m/Y",
                        strtotime($facture["date_facture"])
                    ) ?>

                </div>

            </div>

        </div>


        <!-- =================================================
             INFORMATIONS CLIENT
        ================================================= -->

        <div class="row g-4 mb-5">


            <div class="col-md-6">

                <div class="invoice-info">

                    <h6 class="fw-bold mb-3">

                        <i class="bi bi-person me-2"></i>

                        Facturé à

                    </h6>


                    <p class="mb-1">

                        <strong>
                            <?= htmlspecialchars(
                                $facture["client_nom"]
                            ) ?>
                        </strong>

                    </p>


                    <p class="mb-0 text-muted">

                        <i class="bi bi-envelope me-1"></i>

                        <?= htmlspecialchars(
                            $facture["client_email"]
                        ) ?>

                    </p>

                </div>

            </div>


            <div class="col-md-6">

                <div class="invoice-info">

                    <h6 class="fw-bold mb-3">

                        <i class="bi bi-receipt me-2"></i>

                        Informations commande

                    </h6>


                    <p class="mb-1">

                        <strong>
                            Commande :
                        </strong>

                        #<?= htmlspecialchars(
                            $facture["commande_id"]
                        ) ?>

                    </p>


                    <p class="mb-0">

                        <strong>
                            Statut :
                        </strong>

                        <?= htmlspecialchars(
                            $facture["commande_statut"]
                        ) ?>

                    </p>

                </div>

            </div>

        </div>


        <!-- =================================================
             PRODUITS
        ================================================= -->

        <div class="table-responsive">

            <table class="table invoice-table align-middle">

                <thead>

                    <tr>

                        <th>
                            Produit
                        </th>

                        <th class="text-center">
                            Quantité
                        </th>

                        <th class="text-end">
                            Prix unitaire
                        </th>

                        <th class="text-end">
                            Total
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php foreach ($produits as $produit): ?>

                    <?php

                    $prixUnitaire =
                        (float) $produit["prix"];

                    $quantite =
                        (int) $produit["quantite"];

                    $sousTotal =
                        $prixUnitaire * $quantite;

                    ?>

                    <tr>

                        <td>

                            <strong>

                                <?= htmlspecialchars(
                                    $produit["produit_nom"]
                                ) ?>

                            </strong>

                        </td>


                        <td class="text-center">

                            <?= $quantite ?>

                        </td>


                        <td class="text-end">

                            <?= number_format(
                                $prixUnitaire,
                                0,
                                ",",
                                " "
                            ) ?>

                            FCFA

                        </td>


                        <td class="text-end fw-bold">

                            <?= number_format(
                                $sousTotal,
                                0,
                                ",",
                                " "
                            ) ?>

                            FCFA

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>


        <!-- =================================================
             TOTAUX
        ================================================= -->

        <div class="row justify-content-end mt-4">

            <div class="col-md-5">

                <div class="invoice-total">


                    <div class="d-flex justify-content-between mb-3">

                        <span>
                            Total facture
                        </span>

                        <strong>

                            <?= number_format(
                                $montantTotal,
                                0,
                                ",",
                                " "
                            ) ?>

                            FCFA

                        </strong>

                    </div>


                    <div class="d-flex justify-content-between mb-3">

                        <span>
                            Montant payé
                        </span>

                        <strong class="text-success">

                            <?= number_format(
                                $montantPaye,
                                0,
                                ",",
                                " "
                            ) ?>

                            FCFA

                        </strong>

                    </div>


                    <hr>


                    <div class="d-flex justify-content-between align-items-center">

                        <span class="fw-bold">
                            Reste à payer
                        </span>

                        <span class="grand-total">

                            <?= number_format(
                                $resteAPayer,
                                0,
                                ",",
                                " "
                            ) ?>

                            FCFA

                        </span>

                    </div>


                    <div class="text-end mt-3">

                        <span
                            class="badge text-bg-<?= $statutClasse ?>"
                        >

                            <?= htmlspecialchars($statut) ?>

                        </span>

                    </div>

                </div>

            </div>

        </div>


        <!-- =================================================
             PIED DE FACTURE
        ================================================= -->

        <div class="invoice-footer">

            <p class="mb-1 fw-bold">
                Merci pour votre confiance !
            </p>

            <p class="mb-0">
                Quincaillerie NPM
            </p>

        </div>


    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>