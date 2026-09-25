<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("includes/auth.php");
require_once("../config/database.php");


/*
|--------------------------------------------------------------------------
| Vérifier l'ID du client
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: clients.php");
    exit;
}

$idClient = (int) $_GET["id"];


/*
|--------------------------------------------------------------------------
| Récupérer le client
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare("
    SELECT
        id,
        nom,
        email,
        telephone,
        type_client,
        date_creation
    FROM utilisateurs
    WHERE id = ?
        AND role = 'client'
");

$requete->execute([$idClient]);

$client = $requete->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    header("Location: clients.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Récupérer les commandes du client
|--------------------------------------------------------------------------
*/

$requeteCommandes = $pdo->prepare("
    SELECT
        commandes.id,
        commandes.total,
        commandes.statut,
        commandes.date_commande,

        factures.id AS facture_id,
        factures.numero_facture,
        factures.montant_paye,
        factures.reste_a_payer,
        factures.statut AS statut_paiement

    FROM commandes

    LEFT JOIN factures
        ON factures.commande_id = commandes.id

    WHERE commandes.utilisateur_id = ?

    ORDER BY commandes.date_commande DESC
");

$requeteCommandes->execute([$idClient]);

$commandes = $requeteCommandes->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Statistiques
|--------------------------------------------------------------------------
*/

$nombreCommandes = count($commandes);

$totalCommandes = 0;
$totalPaye = 0;
$totalReste = 0;

foreach ($commandes as $commande) {

    $totalCommandes += (float) $commande["total"];
    $totalPaye += (float) ($commande["montant_paye"] ?? 0);

    $totalReste += (float) (
        $commande["reste_a_payer"] ?? $commande["total"]
    );
}



?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($client["nom"]) ?> - NPM Admin
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
                    <i class="bi bi-person-circle me-2"></i>
                    <?= htmlspecialchars($client["nom"]) ?>
                </h1>

                <p class="text-muted mb-0">
                    Profil du client et historique de ses commandes.
                </p>

            </div>

            <div class="d-flex gap-2">
                <a href="modifier-client.php?id=<?= (int) $client["id"] ?>" class="btn btn-warning">
                    <i class="bi bi-pencil-square me-1"></i>
                    Modifier
                </a>
                <a href="clients.php" class="btn btn-outline-dark">
                    <i class="bi bi-arrow-left me-1"></i>
                    Retour aux clients
                </a>
            </div>

        </div>


        <!-- STATISTIQUES -->

        <div class="row g-4 mb-4">

            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body text-center p-4">

                        <i class="bi bi-bag-check fs-1 text-warning"></i>

                        <h3 class="fw-bold mt-3">
                            <?= $nombreCommandes ?>
                        </h3>

                        <p class="text-muted mb-0">
                            Commande(s)
                        </p>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body text-center p-4">

                        <i class="bi bi-cash-stack fs-1 text-success"></i>

                        <h3 class="fw-bold mt-3">

                            <?= number_format($totalCommandes, 0, ",", " ") ?>

                            <small class="fs-6">FCFA</small>

                        </h3>

                        <p class="text-muted mb-0">
                            Total des commandes
                        </p>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body text-center p-4">

                        <i class="bi bi-wallet2 fs-1 text-danger"></i>

                        <h3 class="fw-bold mt-3">

                            <?= number_format($totalReste, 0, ",", " ") ?>

                            <small class="fs-6">FCFA</small>

                        </h3>

                        <p class="text-muted mb-0">
                            Reste à payer
                        </p>

                    </div>

                </div>

            </div>

        </div>


        <!-- INFORMATIONS CLIENT -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0">
                    <i class="bi bi-person-vcard me-2"></i>
                    Informations du client
                </h5>

            </div>

            <div class="card-body p-4">

                <div class="row g-4">

                    <div class="col-md-3">

                        <small class="text-muted d-block">Nom</small>

                        <strong>
                            <?= htmlspecialchars($client["nom"]) ?>
                        </strong>

                    </div>

                    <div class="col-md-3">

                        <small class="text-muted d-block">Email</small>

                        <?php if (!empty($client["email"])): ?>

                            <a href="mailto:<?= htmlspecialchars($client["email"]) ?>">
                                <?= htmlspecialchars($client["email"]) ?>
                            </a>

                        <?php else: ?>

                            <span class="text-muted">—</span>

                        <?php endif; ?>

                    </div>

                    <div class="col-md-3">

                        <small class="text-muted d-block">Téléphone</small>

                        <?php if (!empty($client["telephone"])): ?>

                            <?= htmlspecialchars($client["telephone"]) ?>

                        <?php else: ?>

                            <span class="text-muted">—</span>

                        <?php endif; ?>

                    </div>

                    <div class="col-md-3">

                        <small class="text-muted d-block">Client depuis</small>

                        <?php if (!empty($client["date_creation"])): ?>

                            <?= date("d/m/Y", strtotime($client["date_creation"])) ?>

                        <?php else: ?>

                            —

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>


        <!-- DÉTAIL DES COMMANDES -->

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white py-3">

                <div class="d-flex justify-content-between align-items-center">

                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-bag-check me-2"></i>
                        Détail des commandes
                    </h5>

                    <span class="badge bg-dark">
                        <?= $nombreCommandes ?> commande(s)
                    </span>

                </div>

            </div>

            <div class="card-body p-0">

                <?php if (empty($commandes)): ?>

                    <div class="text-center py-5">

                        <i class="bi bi-cart-x fs-1 text-muted"></i>

                        <h5 class="mt-3">Aucune commande</h5>

                        <p class="text-muted mb-0">
                            Ce client n'a pas encore passé de commande.
                        </p>

                    </div>

                <?php else: ?>

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-dark">

                                <tr>
                                    <th>Commande</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Payé</th>
                                    <th>Reste</th>
                                    <th>Paiement</th>
                                    <th>Statut</th>
                                    <th class="text-center">Facture</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($commandes as $commande): ?>

                                    <?php

                                    $statutPaiement = $commande["statut_paiement"] ?? "Impayée";
                                    $statutCommande = $commande["statut"];

                                    ?>

                                    <tr>

                                        <td>
                                            <strong><?= $commande["id"] ?></strong>
                                        </td>

                                        <td>
                                            <?= date("d/m/Y", strtotime($commande["date_commande"])) ?>
                                        </td>

                                        <td>
                                            <strong>
                                                <?= number_format($commande["total"], 0, ",", " ") ?> FCFA
                                            </strong>
                                        </td>

                                        <td class="text-success">
                                            <?= number_format($commande["montant_paye"] ?? 0, 0, ",", " ") ?> FCFA
                                        </td>

                                        <td class="text-danger">
                                            <?= number_format(
                                                $commande["reste_a_payer"] ?? $commande["total"],
                                                0,
                                                ",",
                                                " "
                                            ) ?> FCFA
                                        </td>

                                        <td>

                                            <?php if ($statutPaiement === "Payée"): ?>

                                                <span class="badge bg-success">Payée</span>

                                            <?php elseif ($statutPaiement === "Partiellement payée"): ?>

                                                <span class="badge bg-warning text-dark">Partielle</span>

                                            <?php else: ?>

                                                <span class="badge bg-danger">Impayée</span>

                                            <?php endif; ?>

                                        </td>

                                        <td>

                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars($statutCommande) ?>
                                            </span>

                                        </td>

                                        <td class="text-center">

                                            <?php if (!empty($commande["facture_id"])): ?>

                                                <a
                                                    href="facture.php?id=<?= $commande["facture_id"] ?>"
                                                    class="btn btn-sm btn-outline-dark"
                                                    title="Voir la facture"
                                                >

                                                    <i class="bi bi-receipt"></i>

                                                </a>

                                            <?php else: ?>

                                                <span class="text-muted">—</span>

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
