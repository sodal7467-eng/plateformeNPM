<?php

require_once("includes/auth.php");

require_once("../config/database.php");


/*
|--------------------------------------------------------------------------
| Construire les 12 derniers mois (du plus ancien au plus récent)
|--------------------------------------------------------------------------
*/

$mois = [];

for ($i = 11; $i >= 0; $i--) {

    $mois[] = date("Y-m", strtotime("-$i months"));
}


/*
|--------------------------------------------------------------------------
| Ventes par mois (commandes non annulées)
|--------------------------------------------------------------------------
*/

$requeteVentes = $pdo->prepare("
    SELECT
        DATE_FORMAT(date_commande, '%Y-%m') AS mois,
        SUM(total) AS total_ventes,
        COUNT(*) AS nombre_commandes
    FROM commandes
    WHERE statut != 'Annulée'
      AND date_commande >= ?
    GROUP BY mois
");

$requeteVentes->execute([$mois[0] . "-01"]);

$ventesParMois = [];

foreach ($requeteVentes->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
    $ventesParMois[$ligne["mois"]] = $ligne;
}


/*
|--------------------------------------------------------------------------
| Dépenses par mois
|--------------------------------------------------------------------------
*/

$requeteDepenses = $pdo->prepare("
    SELECT
        DATE_FORMAT(date_depense, '%Y-%m') AS mois,
        SUM(montant) AS total_depenses
    FROM depenses
    WHERE date_depense >= ?
    GROUP BY mois
");

$requeteDepenses->execute([$mois[0] . "-01"]);

$depensesParMois = [];

foreach ($requeteDepenses->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
    $depensesParMois[$ligne["mois"]] = (float) $ligne["total_depenses"];
}


/*
|--------------------------------------------------------------------------
| Assembler les données finales
|--------------------------------------------------------------------------
*/

$labels = [];
$ventes = [];
$depenses = [];
$solde = [];

$totalVentesPeriode = 0;
$totalDepensesPeriode = 0;

foreach ($mois as $m) {

    $labels[] = $m;

    $v = isset($ventesParMois[$m]) ? (float) $ventesParMois[$m]["total_ventes"] : 0;
    $d = $depensesParMois[$m] ?? 0;

    $ventes[] = $v;
    $depenses[] = $d;
    $solde[] = $v - $d;

    $totalVentesPeriode += $v;
    $totalDepensesPeriode += $d;
}

$moisEnCours = end($mois);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Rapport mensuel - NPM Admin</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link rel="stylesheet" href="../assets/css/style.css">

</head>


<body class="bg-light">


<?php require_once("includes/navbar.php"); ?>

<?php require_once("includes/sidebar.php"); ?>


<main class="admin-content">

    <div class="container-fluid py-4">


        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

            <div>
                <h1 class="fw-bold mb-1">Rapport mensuel</h1>
                <p class="text-muted mb-0">
                    Ventes et dépenses sur les 12 derniers mois.
                </p>
            </div>

            <a href="depenses.php" class="btn btn-outline-dark">
                <i class="bi bi-wallet2 me-2"></i>
                Gérer les dépenses
            </a>

        </div>


        <!-- Cartes résumé -->

        <div class="row g-4 mb-4">

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">
                            Ventes (12 derniers mois)
                        </small>
                        <h3 class="fw-bold text-success mb-0">
                            <?= number_format($totalVentesPeriode, 0, ",", " ") ?> FCFA
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">
                            Dépenses (12 derniers mois)
                        </small>
                        <h3 class="fw-bold text-danger mb-0">
                            <?= number_format($totalDepensesPeriode, 0, ",", " ") ?> FCFA
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">
                            Solde (12 derniers mois)
                        </small>
                        <h3 class="fw-bold <?= ($totalVentesPeriode - $totalDepensesPeriode) >= 0 ? 'text-success' : 'text-danger' ?> mb-0">
                            <?= number_format($totalVentesPeriode - $totalDepensesPeriode, 0, ",", " ") ?> FCFA
                        </h3>
                    </div>
                </div>
            </div>

        </div>


        <!-- Graphique -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-bar-chart me-2"></i>
                    Ventes vs dépenses par mois
                </h5>
            </div>

            <div class="card-body">
                <canvas id="graphiqueMensuel" height="100"></canvas>
            </div>

        </div>


        <!-- Tableau détaillé -->

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-table me-2"></i>
                    Détail par mois
                </h5>
            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-dark">
                            <tr>
                                <th>Mois</th>
                                <th>Ventes</th>
                                <th>Dépenses</th>
                                <th>Solde</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php for ($i = count($mois) - 1; $i >= 0; $i--): ?>

                                <tr <?= $mois[$i] === $moisEnCours ? 'class="table-warning"' : "" ?>>

                                    <td class="fw-semibold">
                                        <?= htmlspecialchars($mois[$i]) ?>
                                        <?= $mois[$i] === $moisEnCours ? ' <span class="badge bg-dark">En cours</span>' : "" ?>
                                    </td>

                                    <td class="text-success">
                                        <?= number_format($ventes[$i], 0, ",", " ") ?> FCFA
                                    </td>

                                    <td class="text-danger">
                                        <?= number_format($depenses[$i], 0, ",", " ") ?> FCFA
                                    </td>

                                    <td class="fw-bold <?= $solde[$i] >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= number_format($solde[$i], 0, ",", " ") ?> FCFA
                                    </td>

                                </tr>

                            <?php endfor; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</main>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

<script
    src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js">
</script>


<script>

const labels = <?= json_encode($labels) ?>;
const ventes = <?= json_encode($ventes) ?>;
const depenses = <?= json_encode($depenses) ?>;

const ctx = document.getElementById("graphiqueMensuel");

new Chart(ctx, {
    type: "bar",
    data: {
        labels: labels,
        datasets: [
            {
                label: "Ventes",
                data: ventes,
                backgroundColor: "rgba(25, 135, 84, 0.75)"
            },
            {
                label: "Dépenses",
                data: depenses,
                backgroundColor: "rgba(220, 53, 69, 0.75)"
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function (value) {
                        return value.toLocaleString("fr-FR") + " FCFA";
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function (context) {
                        return context.dataset.label + " : " +
                            context.parsed.y.toLocaleString("fr-FR") + " FCFA";
                    }
                }
            }
        }
    }
});

</script>


</body>

</html>
