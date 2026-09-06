<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| RÉCUPÉRER LES PRODUITS
|--------------------------------------------------------------------------
*/

$requete = $pdo->query("
    SELECT 
        produits.*,
        categories.nom AS categorie_nom
    FROM produits
    LEFT JOIN categories
        ON produits.categorie = categories.id
    ORDER BY produits.id DESC
");

$produits = $requete->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| STATISTIQUES
|--------------------------------------------------------------------------
*/

$totalProduits = count($produits);

$stockTotal = 0;
$rupture = 0;
$stockFaible = 0;

foreach ($produits as $produit) {

    $stock = (int) $produit["stock"];

    $stockTotal += $stock;

    if ($stock === 0) {
        $rupture++;
    } elseif ($stock <= 10) {
        $stockFaible++;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Produits - Administration NPM</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- CSS NPM -->
    <link
        rel="stylesheet"
        href="../assets/css/style.css">

</head>


<body>


<!-- NAVBAR ADMIN -->

<?php require_once("includes/navbar.php"); ?>


<!-- SIDEBAR ADMIN -->

<?php require_once("includes/sidebar.php"); ?>


<!-- CONTENU ADMIN -->

<main class="admin-content">

    <div class="container-fluid p-4">


        <!-- EN-TÊTE -->

        <div class="d-flex
                    justify-content-between
                    align-items-center
                    flex-wrap
                    gap-3
                    mb-4">

            <div>

                <h1 class="fw-bold mb-1">
                    Produits
                </h1>

                <p class="text-muted mb-0">
                    Gérez les produits de votre quincaillerie.
                </p>

            </div>


            <a
                href="ajouter-produit.php"
                class="btn btn-dark">

                <i class="bi bi-plus-circle me-1"></i>

                Ajouter un produit

            </a>

        </div>



        <!-- STATISTIQUES -->

        <div class="row g-3 mb-4">


            <!-- TOTAL PRODUITS -->

            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex
                                    justify-content-between
                                    align-items-center">

                            <div>

                                <p class="text-muted mb-1">
                                    Total produits
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



            <!-- STOCK TOTAL -->

            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex
                                    justify-content-between
                                    align-items-center">

                            <div>

                                <p class="text-muted mb-1">
                                    Stock total
                                </p>

                                <h3 class="fw-bold mb-0">
                                    <?= $stockTotal ?>
                                </h3>

                            </div>

                            <div class="fs-1 text-success">

                                <i class="bi bi-boxes"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- STOCK FAIBLE / RUPTURE -->

            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex
                                    justify-content-between
                                    align-items-center">

                            <div>

                                <p class="text-muted mb-1">
                                    Stock à surveiller
                                </p>

                                <h3 class="fw-bold mb-0">

                                    <?= $stockFaible + $rupture ?>

                                </h3>

                                <small class="text-muted">

                                    <?= $stockFaible ?> faible /
                                    <?= $rupture ?> rupture

                                </small>

                            </div>

                            <div class="fs-1 text-warning">

                                <i class="bi bi-exclamation-triangle"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- LISTE DES PRODUITS -->

        <div class="card border-0 shadow-sm">


            <!-- HEADER -->

            <div class="card-header bg-white py-3">

                <div class="d-flex
                            justify-content-between
                            align-items-center">

                    <h5 class="fw-bold mb-0">

                        <i class="bi bi-box-seam me-2"></i>

                        Liste des produits

                    </h5>

                    <span class="badge bg-dark">

                        <?= $totalProduits ?> produit(s)

                    </span>

                </div>

            </div>



            <!-- CONTENU -->

            <div class="card-body p-0">


                <?php if (empty($produits)): ?>


                    <!-- AUCUN PRODUIT -->

                    <div class="text-center p-5">

                        <i
                            class="bi bi-box
                                   fs-1
                                   text-muted">
                        </i>

                        <h5 class="mt-3">
                            Aucun produit
                        </h5>

                        <p class="text-muted">
                            Vous n'avez encore ajouté aucun produit.
                        </p>

                        <a
                            href="ajouter-produit.php"
                            class="btn btn-dark">

                            <i class="bi bi-plus-circle me-1"></i>

                            Ajouter le premier produit

                        </a>

                    </div>


                <?php else: ?>


                    <!-- TABLEAU -->

                    <div class="table-responsive">

                        <table
                            class="table
                                   table-hover
                                   align-middle
                                   mb-0">


                            <thead class="table-dark">

                                <tr>

                                    <th>ID</th>

                                    <th>Image</th>

                                    <th>Produit</th>

                                    <th>Catégorie</th>

                                    <th>Prix</th>

                                    <th>Stock</th>

                                    <th>Date</th>

                                    <th class="text-center">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php foreach ($produits as $produit): ?>

                                    <?php

                                    $stock = (int) $produit["stock"];

                                    ?>


                                    <tr>


                                        <!-- ID -->

                                        <td>

                                            <span class="fw-bold">

                                                #<?= $produit["id"] ?>

                                            </span>

                                        </td>



                                        <!-- IMAGE -->

                                        <td>

                                            <?php if (!empty($produit["image"])): ?>

                                                <img
                                                    src="../uploads/<?= htmlspecialchars($produit["image"]) ?>"
                                                    alt="<?= htmlspecialchars($produit["nom"]) ?>"
                                                    width="60"
                                                    height="60"
                                                    style="
                                                        object-fit: cover;
                                                        border-radius: 8px;
                                                    ">

                                            <?php else: ?>

                                                <div
                                                    class="d-flex
                                                           align-items-center
                                                           justify-content-center
                                                           bg-light
                                                           border"
                                                    style="
                                                        width:60px;
                                                        height:60px;
                                                        border-radius:8px;
                                                    ">

                                                    <i
                                                        class="bi bi-image
                                                               text-muted
                                                               fs-4">
                                                    </i>

                                                </div>

                                            <?php endif; ?>

                                        </td>



                                        <!-- PRODUIT -->

                                        <td>

                                            <strong>

                                                <?= htmlspecialchars($produit["nom"]) ?>

                                            </strong>


                                            <?php if (!empty($produit["description"])): ?>

                                                <br>

                                                <small class="text-muted">

                                                    <?= htmlspecialchars(
                                                        mb_strimwidth(
                                                            $produit["description"],
                                                            0,
                                                            60,
                                                            "..."
                                                        )
                                                    ) ?>

                                                </small>

                                            <?php endif; ?>

                                        </td>



                                        <!-- CATÉGORIE -->

                                        <td>

                                            <?php if (!empty($produit["categorie_nom"])): ?>

                                                <span class="badge text-bg-secondary">

                                                    <?= htmlspecialchars(
                                                        $produit["categorie_nom"]
                                                    ) ?>

                                                </span>

                                            <?php else: ?>

                                                <span class="text-muted">
                                                    Sans catégorie
                                                </span>

                                            <?php endif; ?>

                                        </td>



                                        <!-- PRIX -->

                                        <td>

                                            <strong>

                                                <?= number_format(
                                                    $produit["prix"],
                                                    0,
                                                    ",",
                                                    " "
                                                ) ?>

                                                FCFA

                                            </strong>

                                        </td>



                                        <!-- STOCK -->

                                        <td>


                                            <?php if ($stock === 0): ?>

                                                <span class="badge bg-danger">

                                                    <i class="bi bi-x-circle me-1"></i>

                                                    Rupture

                                                </span>


                                            <?php elseif ($stock <= 10): ?>

                                                <span class="badge bg-warning text-dark">

                                                    <i class="bi bi-exclamation-triangle me-1"></i>

                                                    <?= $stock ?> restant(s)

                                                </span>


                                            <?php else: ?>

                                                <span class="badge bg-success">

                                                    <i class="bi bi-check-circle me-1"></i>

                                                    <?= $stock ?> disponible(s)

                                                </span>

                                            <?php endif; ?>


                                        </td>



                                        <!-- DATE -->

                                        <td>

                                            <?php if (
                                                isset($produit["date_creation"])
                                                && !empty($produit["date_creation"])
                                            ): ?>

                                                <?= date(
                                                    "d/m/Y",
                                                    strtotime(
                                                        $produit["date_creation"]
                                                    )
                                                ) ?>

                                            <?php else: ?>

                                                <span class="text-muted">
                                                    —
                                                </span>

                                            <?php endif; ?>

                                        </td>



                                        <!-- ACTIONS -->

                                        <td class="text-center">

                                            <div
                                                class="d-flex
                                                       justify-content-center
                                                       gap-1">


                                                <!-- MODIFIER -->

                                                <a
                                                    href="modifier-produit.php?id=<?= $produit["id"] ?>"
                                                    class="btn
                                                           btn-sm
                                                           btn-outline-primary"
                                                    title="Modifier">

                                                    <i class="bi bi-pencil"></i>

                                                </a>



                                                <!-- SUPPRIMER -->

                                                <a
                                                    href="supprimer-produit.php?id=<?= $produit["id"] ?>"
                                                    class="btn
                                                           btn-sm
                                                           btn-outline-danger"
                                                    title="Supprimer"
                                                    onclick="
                                                        return confirm(
                                                            'Voulez-vous vraiment supprimer ce produit ?'
                                                        );
                                                    ">

                                                    <i class="bi bi-trash"></i>

                                                </a>


                                            </div>

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
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>