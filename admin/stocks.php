<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../config/database.php");

/*
|--------------------------------------------------------------------------
| Récupérer les produits
|--------------------------------------------------------------------------
*/

$requete = $pdo->query("
    SELECT 
        id,
        nom,
        prix,
        stock,
        image,
        categorie
    FROM produits
    ORDER BY stock ASC
");

$produits = $requete->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Statistiques
|--------------------------------------------------------------------------
*/

$totalProduits = count($produits);
$rupture = 0;
$stockFaible = 0;
$stockNormal = 0;
$valeurStock = 0;

foreach ($produits as $produit) {

    $stock = (int) $produit["stock"];
    $prix = (float) $produit["prix"];

    $valeurStock += $stock * $prix;

    if ($stock == 0) {
        $rupture++;
    } elseif ($stock <= 5) {
        $stockFaible++;
    } else {
        $stockNormal++;
    }
}

?>
<?php require_once("includes/auth.php");?> 

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gestion des stocks - NPM Admin</title>

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
    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body class="bg-light">

<?php require_once("includes/navbar.php"); ?>

<?php require_once("includes/sidebar.php"); ?>


<main class="admin-content">

    <div class="container-fluid py-4">

        <!-- En-tête -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h1 class="fw-bold mb-1">
                    Gestion des stocks
                </h1>

                <p class="text-muted mb-0">
                    Suivez l'état du stock de vos produits.
                </p>
            </div>

            <a href="produits.php" class="btn btn-dark">
                <i class="bi bi-box-seam me-2"></i>
                Voir les produits
            </a>

        </div>


        <!-- STATISTIQUES -->

        <div class="row g-4 mb-4">

            <!-- Produits -->

            <div class="col-md-6 col-xl-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <p class="text-muted mb-1">
                                    Produits
                                </p>

                                <h3 class="fw-bold mb-0">
                                    <?= $totalProduits ?>
                                </h3>

                            </div>

                            <div class="fs-1 text-primary">
                                <i class="bi bi-box-seam"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Stock normal -->

            <div class="col-md-6 col-xl-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <p class="text-muted mb-1">
                                    Stock normal
                                </p>

                                <h3 class="fw-bold text-success mb-0">
                                    <?= $stockNormal ?>
                                </h3>

                            </div>

                            <div class="fs-1 text-success">
                                <i class="bi bi-check-circle"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Stock faible -->

            <div class="col-md-6 col-xl-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <p class="text-muted mb-1">
                                    Stock faible
                                </p>

                                <h3 class="fw-bold text-warning mb-0">
                                    <?= $stockFaible ?>
                                </h3>

                            </div>

                            <div class="fs-1 text-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Rupture -->

            <div class="col-md-6 col-xl-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <p class="text-muted mb-1">
                                    Rupture
                                </p>

                                <h3 class="fw-bold text-danger mb-0">
                                    <?= $rupture ?>
                                </h3>

                            </div>

                            <div class="fs-1 text-danger">
                                <i class="bi bi-x-circle"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- VALEUR DU STOCK -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-body">

                <div class="d-flex align-items-center">

                    <div class="fs-1 text-warning me-3">
                        <i class="bi bi-cash-stack"></i>
                    </div>

                    <div>

                        <p class="text-muted mb-1">
                            Valeur totale du stock
                        </p>

                        <h3 class="fw-bold mb-0">
                            <?= number_format($valeurStock, 0, ',', ' ') ?> FCFA
                        </h3>

                    </div>

                </div>

            </div>

        </div>


        <!-- TABLEAU DES STOCKS -->

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0">
                    <i class="bi bi-bar-chart me-2"></i>
                    État des stocks
                </h5>

            </div>


            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th>Produit</th>

                                <th>Catégorie</th>

                                <th>Prix</th>

                                <th class="text-center">
                                    Stock
                                </th>

                                <th class="text-center">
                                    État
                                </th>

                                <th class="text-center">
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if (empty($produits)): ?>

                            <tr>

                                <td colspan="6" class="text-center py-5">

                                    <i class="bi bi-box fs-1 text-muted"></i>

                                    <p class="text-muted mt-2 mb-0">
                                        Aucun produit enregistré.
                                    </p>

                                </td>

                            </tr>

                        <?php else: ?>


                            <?php foreach ($produits as $produit): ?>

                                <?php

                                $stock = (int) $produit["stock"];

                                if ($stock == 0) {

                                    $badge = "bg-danger";
                                    $texte = "Rupture";

                                } elseif ($stock <= 5) {

                                    $badge = "bg-warning text-dark";
                                    $texte = "Stock faible";

                                } else {

                                    $badge = "bg-success";
                                    $texte = "En stock";

                                }

                                ?>


                                <tr>

                                    <!-- Produit -->

                                    <td>

                                        <div class="d-flex align-items-center gap-3">

                                            <?php if (!empty($produit["image"])): ?>

                                                <img
                                                    src="../uploads/<?= htmlspecialchars($produit["image"]) ?>"
                                                    alt="<?= htmlspecialchars($produit["nom"]) ?>"
                                                    width="50"
                                                    height="50"
                                                    style="object-fit: cover;"
                                                    class="rounded"
                                                >

                                            <?php else: ?>

                                                <div
                                                    class="bg-light rounded d-flex align-items-center justify-content-center"
                                                    style="width:50px;height:50px;"
                                                >
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>

                                            <?php endif; ?>


                                            <strong>
                                                <?= htmlspecialchars($produit["nom"]) ?>
                                            </strong>

                                        </div>

                                    </td>


                                    <!-- Catégorie -->

                                    <td>

                                        <?= htmlspecialchars($produit["categorie"]) ?>

                                    </td>


                                    <!-- Prix -->

                                    <td>

                                        <?= number_format(
                                            (float) $produit["prix"],
                                            0,
                                            ',',
                                            ' '
                                        ) ?>
                                        FCFA

                                    </td>


                                    <!-- Stock -->

                                    <td class="text-center">

                                        <strong class="<?= $stock <= 5 ? 'text-danger' : 'text-success' ?>">

                                            <?= $stock ?>

                                        </strong>

                                    </td>


                                    <!-- Etat -->

                                    <td class="text-center">

                                        <span class="badge <?= $badge ?>">

                                            <?= $texte ?>

                                        </span>

                                    </td>


                                    <!-- Action -->

                                    <td class="text-center">

                                        <a
                                            href="modifier-produit.php?id=<?= $produit["id"] ?>"
                                            class="btn btn-sm btn-warning"
                                            title="Modifier le produit"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>


                        <?php endif; ?>

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

</body>

</html>