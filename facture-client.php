<?php

session_start();

require_once "config/database.php";


// =========================================================
// VÉRIFIER LA CONNEXION
// =========================================================

if (!isset($_SESSION["id"])) {

    header("Location: connexion.php");
    exit;

}

$idClient = (int) $_SESSION["id"];


// =========================================================
// VÉRIFIER L'IDENTIFIANT DE LA COMMANDE
// =========================================================

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: mes-commende.php");
    exit;

}

$commandeId = (int) $_GET["id"];


/*
|--------------------------------------------------------------------------
| Récupérer la facture + commande, uniquement si la commande appartient
| bien au client connecté.
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare("
    SELECT
        f.id AS facture_id,
        f.numero_facture,
        f.commande_id,
        f.montant_total,
        f.montant_paye,
        f.reste_a_payer,
        f.statut AS statut_facture,

        c.date_commande,
        c.statut AS statut_commande,
        c.total AS total_commande,

        u.nom AS client_nom,
        u.email AS client_email

    FROM factures f

    INNER JOIN commandes c
        ON f.commande_id = c.id

    INNER JOIN utilisateurs u
        ON c.utilisateur_id = u.id

    WHERE f.commande_id = ?
        AND c.utilisateur_id = ?

    LIMIT 1
");

$requete->execute([$commandeId, $idClient]);

$facture = $requete->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Vérifier que la facture existe
|--------------------------------------------------------------------------
*/

if (!$facture) {
    header("Location: mes-commende.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Récupérer les produits de la commande
|--------------------------------------------------------------------------
*/

$requeteProduits = $pdo->prepare("
    SELECT
        cd.quantite,
        cd.prix,

        p.nom AS produit_nom

    FROM commande_details cd

    INNER JOIN produits p
        ON cd.produit_id = p.id

    WHERE cd.commande_id = ?

    ORDER BY cd.id ASC
");

$requeteProduits->execute([
    $facture["commande_id"]
]);

$produits = $requeteProduits->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Recalcul des valeurs d'affichage
|--------------------------------------------------------------------------
*/

$montantTotal = (float) $facture["montant_total"];

$montantPaye = (float) $facture["montant_paye"];

$resteAPayer = (float) $facture["reste_a_payer"];


if ($montantPaye < 0) {
    $montantPaye = 0;
}

if ($montantPaye > $montantTotal) {
    $montantPaye = $montantTotal;
}

$resteAPayer = $montantTotal - $montantPaye;

if ($resteAPayer < 0) {
    $resteAPayer = 0;
}


/*
|--------------------------------------------------------------------------
| Déterminer le statut automatiquement
|--------------------------------------------------------------------------
*/

if ($resteAPayer <= 0) {

    $statutFacture = "Payée";
    $classeStatut = "statut-payee";
    $iconeStatut = "bi-check-circle";

} elseif ($montantPaye > 0) {

    $statutFacture = "Partiellement payée";
    $classeStatut = "statut-partielle";
    $iconeStatut = "bi-clock";

} else {

    $statutFacture = "Impayée";
    $classeStatut = "statut-impayee";
    $iconeStatut = "bi-exclamation-circle";

}


$dateCommande = date(
    "d/m/Y à H:i",
    strtotime($facture["date_commande"])
);

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
        - NPM
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
        href="assets/css/style.css"
    >


    <style>

        .facture-wrapper {
            max-width: 1100px;
            margin: 0 auto;
            padding: 35px;
        }


        .facture-card {
            background: #ffffff;
            border: 1px solid #eaecf0;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(16, 24, 40, 0.06);
            overflow: hidden;
        }


        .facture-header {
            padding: 35px;
            border-bottom: 1px solid #eaecf0;

            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 30px;
        }


        .facture-logo {
            font-size: 30px;
            font-weight: 800;
            color: #101828;
        }


        .facture-logo span {
            color: #f28c28;
        }


        .facture-title {
            text-align: right;
        }


        .facture-title h1 {
            margin: 0;
            font-size: 30px;
            font-weight: 700;
            color: #101828;
        }


        .facture-number {
            color: #667085;
            margin-top: 5px;
        }


        .facture-informations {
            padding: 30px 35px;

            display: grid;
            grid-template-columns: 1fr 1fr;

            gap: 30px;
        }


        .info-box h6 {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #667085;
            margin-bottom: 10px;
            font-weight: 700;
        }


        .info-box strong {
            color: #101828;
            font-size: 16px;
        }


        .info-box p {
            margin: 3px 0;
            color: #475467;
        }


        .facture-status-box {
            margin: 0 35px 30px;
            padding: 18px 20px;

            background: #f9fafb;

            border: 1px solid #eaecf0;
            border-radius: 10px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }


        .facture-status-label {
            color: #667085;
            font-weight: 600;
        }


        .facture-status {
            display: inline-flex;
            align-items: center;
            gap: 7px;

            padding: 8px 14px;

            border-radius: 20px;

            font-size: 13px;
            font-weight: 700;
        }


        .statut-payee {
            background: #ecfdf3;
            color: #027a48;
        }


        .statut-partielle {
            background: #fffaeb;
            color: #b54708;
        }


        .statut-impayee {
            background: #fef3f2;
            color: #b42318;
        }


        .facture-produits {
            padding: 0 35px 30px;
        }


        .facture-produits table {
            width: 100%;
            border-collapse: collapse;
        }


        .facture-produits thead th {
            background: #f9fafb;

            padding: 14px 15px;

            color: #475467;

            font-size: 13px;
            font-weight: 700;

            border-bottom: 1px solid #eaecf0;
        }


        .facture-produits tbody td {
            padding: 16px 15px;

            border-bottom: 1px solid #f2f4f7;

            color: #344054;
        }


        .facture-produits tbody tr:last-child td {
            border-bottom: none;
        }


        .facture-totaux {
            width: 380px;
            max-width: 100%;

            margin-left: auto;

            padding: 0 35px 35px;
        }


        .total-ligne {
            display: flex;
            justify-content: space-between;
            align-items: center;

            padding: 10px 0;

            color: #475467;
        }


        .total-ligne strong {
            color: #101828;
        }


        .total-final {
            border-top: 2px solid #101828;

            margin-top: 10px;
            padding-top: 15px;

            font-size: 20px;
            font-weight: 700;
        }


        .total-paye strong {
            color: #027a48;
        }


        .total-reste strong {
            color: #b42318;
        }


        .facture-footer {
            padding: 25px 35px;

            background: #f9fafb;

            border-top: 1px solid #eaecf0;

            text-align: center;

            color: #667085;

            font-size: 13px;
        }


        .facture-actions {
            max-width: 1100px;

            margin: 25px auto;

            display: flex;
            justify-content: space-between;
            align-items: center;

            gap: 10px;
        }


        .facture-actions-right {
            display: flex;
            gap: 10px;
        }


        @media print {

            body {
                background: #ffffff !important;
            }


            .site-navbar,
            .site-footer,
            nav,
            footer,
            .facture-actions {
                display: none !important;
            }


            .facture-wrapper {
                padding: 0;
                max-width: none;
            }


            .facture-card {
                border: none;
                box-shadow: none;
            }


            .facture-header {
                padding-top: 10px;
            }

        }


        @media (max-width: 767px) {

            .facture-wrapper {
                padding: 20px 15px;
            }


            .facture-header {
                flex-direction: column;
                padding: 25px;
            }


            .facture-title {
                text-align: left;
            }


            .facture-informations {
                grid-template-columns: 1fr;
                padding: 25px;
            }


            .facture-status-box {
                margin: 0 25px 25px;
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }


            .facture-produits {
                padding: 0 15px 25px;
                overflow-x: auto;
            }


            .facture-produits table {
                min-width: 650px;
            }


            .facture-totaux {
                padding: 0 25px 25px;
            }


            .facture-actions {
                padding: 0 15px;
                flex-direction: column;
                align-items: stretch;
            }


            .facture-actions-right {
                width: 100%;
            }


            .facture-actions-right .btn {
                flex: 1;
            }

        }

    </style>

</head>


<body class="bg-light">


<?php include "includes/navbar.php"; ?>


<main>


    <!-- =========================================================
         BOUTONS
    ========================================================== -->

    <div class="facture-actions">


        <a
            href="commande-detail.php?id=<?= (int) $facture["commande_id"] ?>"
            class="btn btn-outline-secondary"
        >

            <i class="bi bi-arrow-left me-2"></i>

            Retour à la commande

        </a>


        <div class="facture-actions-right">

            <button
                type="button"
                id="btnTelechargerFacture"
                class="btn btn-warning"
            >

                <i class="bi bi-download me-2"></i>

                Télécharger PDF

            </button>

            <button
                type="button"
                onclick="window.print()"
                class="btn btn-dark"
            >

                <i class="bi bi-printer me-2"></i>

                Imprimer

            </button>

        </div>


    </div>



    <!-- =========================================================
         FACTURE
    ========================================================== -->

    <div class="facture-wrapper">


        <div class="facture-card">


            <!-- EN-TÊTE -->

            <div class="facture-header">


                <div>

                    <div class="facture-logo">

                        <span>NPM</span>

                        Quincaillerie

                    </div>

                    <p class="text-muted mb-0 mt-2">

                        Matériaux, outillage et équipements

                    </p>

                </div>


                <div class="facture-title">

                    <h1>

                        FACTURE

                    </h1>

                    <div class="facture-number">

                        <?= htmlspecialchars(
                            $facture["numero_facture"]
                        ) ?>

                    </div>

                </div>


            </div>



            <!-- INFORMATIONS -->

            <div class="facture-informations">


                <!-- CLIENT -->

                <div class="info-box">

                    <h6>

                        Facturé à

                    </h6>


                    <strong>

                        <?= htmlspecialchars(
                            $facture["client_nom"]
                        ) ?>

                    </strong>


                    <p>

                        <?= htmlspecialchars(
                            $facture["client_email"]
                        ) ?>

                    </p>

                </div>



                <!-- COMMANDE -->

                <div class="info-box">

                    <h6>

                        Informations

                    </h6>


                    <p>

                        <strong>Commande :</strong>

                        #<?= (int) $facture["commande_id"] ?>

                    </p>


                    <p>

                        <strong>Date :</strong>

                        <?= $dateCommande ?>

                    </p>


                </div>


            </div>



            <!-- STATUT -->

            <div class="facture-status-box">


                <span class="facture-status-label">

                    État du paiement

                </span>


                <span class="facture-status <?= $classeStatut ?>">

                    <i class="bi <?= $iconeStatut ?>"></i>

                    <?= htmlspecialchars($statutFacture) ?>

                </span>


            </div>



            <!-- PRODUITS -->

            <div class="facture-produits">


                <table>


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


                    <?php if (!empty($produits)): ?>


                        <?php foreach ($produits as $produit): ?>


                            <?php

                            $prix =
                                (float) $produit["prix"];

                            $quantite =
                                (int) $produit["quantite"];

                            $totalProduit =
                                $prix * $quantite;

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
                                        $prix,
                                        0,
                                        ",",
                                        " "
                                    ) ?>

                                    FCFA

                                </td>


                                <td class="text-end">

                                    <strong>

                                        <?= number_format(
                                            $totalProduit,
                                            0,
                                            ",",
                                            " "
                                        ) ?>

                                        FCFA

                                    </strong>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="4"
                                class="text-center text-muted py-4"
                            >

                                Aucun produit trouvé pour cette commande.

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>

                </table>


            </div>



            <!-- TOTAUX -->

            <div class="facture-totaux">


                <!-- TOTAL -->

                <div class="total-ligne">

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



                <!-- PAYÉ -->

                <div class="total-ligne total-paye">

                    <span>

                        Montant payé

                    </span>

                    <strong>

                        <?= number_format(
                            $montantPaye,
                            0,
                            ",",
                            " "
                        ) ?>

                        FCFA

                    </strong>

                </div>



                <!-- RESTE -->

                <div class="total-ligne total-reste total-final">

                    <span>

                        Reste à payer

                    </span>

                    <strong>

                        <?= number_format(
                            $resteAPayer,
                            0,
                            ",",
                            " "
                        ) ?>

                        FCFA

                    </strong>

                </div>


            </div>



            <!-- FOOTER -->

            <div class="facture-footer">

                <strong>NPM Quincaillerie</strong>

                <br>

                Merci pour votre confiance.

            </div>


        </div>


    </div>


</main>



<?php include "includes/footer.php"; ?>



<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


<!-- Génération PDF de la facture -->

<script
    src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js">
</script>

<script>
    function telechargerFacture() {
        const facture = document.querySelector(".facture-card");

        if (!facture || typeof html2pdf === "undefined") {
            window.print();
            return;
        }

        html2pdf()
            .set({
                margin: 10,
                filename: <?= json_encode("facture-" . $facture["numero_facture"] . ".pdf") ?>,
                image: { type: "jpeg", quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: "mm", format: "a4", orientation: "portrait" }
            })
            .from(facture)
            .save();
    }

    document
        .getElementById("btnTelechargerFacture")
        .addEventListener("click", telechargerFacture);

    // Téléchargement automatique depuis le menu du compte client
    if (new URLSearchParams(window.location.search).get("telecharger") === "1") {
        window.addEventListener("load", telechargerFacture);
    }
</script>


</body>

</html>
