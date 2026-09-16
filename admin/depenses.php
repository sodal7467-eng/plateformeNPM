<?php

require_once("includes/auth.php");

require_once("../config/database.php");


/*
|--------------------------------------------------------------------------
| Filtre par mois (facultatif)
|--------------------------------------------------------------------------
*/

$moisFiltre = $_GET["mois"] ?? "";

$moisValide = (bool) preg_match('/^\d{4}-\d{2}$/', $moisFiltre);

if ($moisValide) {

    $requeteDepenses = $pdo->prepare("
        SELECT id, description, categorie, montant, date_depense
        FROM depenses
        WHERE DATE_FORMAT(date_depense, '%Y-%m') = ?
        ORDER BY date_depense DESC, id DESC
    ");

    $requeteDepenses->execute([$moisFiltre]);

} else {

    $requeteDepenses = $pdo->query("
        SELECT id, description, categorie, montant, date_depense
        FROM depenses
        ORDER BY date_depense DESC, id DESC
    ");
}

$depenses = $requeteDepenses->fetchAll(PDO::FETCH_ASSOC);


$total = 0;

foreach ($depenses as $depense) {
    $total += (float) $depense["montant"];
}


/*
|--------------------------------------------------------------------------
| Liste des mois disponibles pour le filtre
|--------------------------------------------------------------------------
*/

$moisDisponibles = $pdo->query("
    SELECT DISTINCT DATE_FORMAT(date_depense, '%Y-%m') AS mois
    FROM depenses
    ORDER BY mois DESC
")->fetchAll(PDO::FETCH_COLUMN);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dépenses - NPM Admin</title>

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
                <h1 class="fw-bold mb-1">Dépenses</h1>
                <p class="text-muted mb-0">
                    Suivi des dépenses de la boutique.
                </p>
            </div>

            <div class="d-flex gap-2">

                <a href="rapport-mensuel.php" class="btn btn-outline-dark">
                    <i class="bi bi-bar-chart me-2"></i>
                    Rapport mensuel
                </a>

                <a href="ajouter-depense.php" class="btn btn-warning">
                    <i class="bi bi-plus-lg me-2"></i>
                    Nouvelle dépense
                </a>

            </div>

        </div>


        <?php if (isset($_GET["ajoute"])): ?>

            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                Dépense ajoutée avec succès.
            </div>

        <?php endif; ?>


        <?php if (isset($_GET["supprime"])): ?>

            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                Dépense supprimée.
            </div>

        <?php endif; ?>


        <!-- Filtre par mois -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-body py-3">

                <form method="GET" class="d-flex align-items-center gap-3 flex-wrap">

                    <label class="fw-semibold mb-0" for="mois">
                        Mois
                    </label>

                    <select
                        name="mois"
                        id="mois"
                        class="form-select"
                        style="max-width: 220px;"
                        onchange="this.form.submit()"
                    >
                        <option value="">Tous les mois</option>

                        <?php foreach ($moisDisponibles as $mois): ?>
                            <option
                                value="<?= htmlspecialchars($mois) ?>"
                                <?= $moisFiltre === $mois ? "selected" : "" ?>
                            >
                                <?= htmlspecialchars($mois) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>

                    <?php if ($moisValide): ?>
                        <a href="depenses.php" class="btn btn-sm btn-outline-secondary">
                            Réinitialiser
                        </a>
                    <?php endif; ?>

                    <div class="ms-auto text-end">
                        <small class="text-muted d-block">Total affiché</small>
                        <strong class="fs-5">
                            <?= number_format($total, 0, ",", " ") ?> FCFA
                        </strong>
                    </div>

                </form>

            </div>

        </div>


        <div class="card border-0 shadow-sm">

            <div class="card-body p-0">

                <?php if (empty($depenses)): ?>

                    <div class="text-center py-5">
                        <i class="bi bi-wallet2 fs-1 text-muted"></i>
                        <h5 class="mt-3">Aucune dépense</h5>
                        <p class="text-muted mb-0">
                            Aucune dépense enregistrée pour le moment.
                        </p>
                    </div>

                <?php else: ?>

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Catégorie</th>
                                    <th>Montant</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($depenses as $depense): ?>

                                    <tr>

                                        <td>
                                            <?= date("d/m/Y", strtotime($depense["date_depense"])) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($depense["description"]) ?>
                                        </td>

                                        <td>
                                            <?php if (!empty($depense["categorie"])): ?>
                                                <span class="badge bg-light text-dark border">
                                                    <?= htmlspecialchars($depense["categorie"]) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="fw-bold">
                                            <?= number_format((float) $depense["montant"], 0, ",", " ") ?> FCFA
                                        </td>

                                        <td class="text-end">

                                            <form
                                                action="supprimer-depense.php"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Voulez-vous vraiment supprimer cette dépense ?');"
                                            >

                                                <input type="hidden" name="id" value="<?= $depense["id"] ?>">

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>

                                            </form>

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
