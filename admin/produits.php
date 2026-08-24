<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| RÉCUPÉRER LES PRODUITS
|--------------------------------------------------------------------------
*/

$requete = $pdo->query("
    SELECT *
    FROM produits
    ORDER BY id DESC
");

$produits = $requete->fetchAll(PDO::FETCH_ASSOC);

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


    <!-- CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/style.css">

</head>


<body>


<!-- HEADER -->

<?php include "includes/header.php"; ?>


<div class="container-fluid">

    <div class="row">


        <!-- SIDEBAR -->

        <div class="col-md-3 col-lg-2 p-0">

            <?php include "includes/sidebar.php"; ?>

        </div>


        <!-- CONTENU -->

        <main class="col-md-9 col-lg-10 p-4">


            <!-- TITRE + BOUTON -->

            <div class="d-flex
                        justify-content-between
                        align-items-center
                        mb-4">

                <div>

                    <h1 class="fw-bold">

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


            <!-- STATISTIQUE -->

            <div class="row g-3 mb-4">

                <div class="col-md-4">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body">

                            <div class="d-flex
                                        justify-content-between
                                        align-items-center">

                                <div>

                                    <p class="text-muted mb-1">

                                        Total produits

                                    </p>

                                    <h3 class="fw-bold mb-0">

                                        <?= count($produits) ?>

                                    </h3>

                                </div>

                                <i class="bi bi-box-seam fs-1 text-primary"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- LISTE DES PRODUITS -->

            <div class="card border-0 shadow-sm">


                <div class="card-header bg-white py-3">

                    <h5 class="fw-bold mb-0">

                        <i class="bi bi-box-seam me-2"></i>

                        Liste des produits

                    </h5>

                </div>


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

                                <i class="bi bi-plus-circle"></i>

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


                                    <?php foreach (
                                        $produits
                                        as $produit
                                    ): ?>


                                        <tr>


                                            <!-- ID -->

                                            <td>

                                                <?= $produit["id"] ?>

                                            </td>


                                            <!-- IMAGE -->

                                            <td>

                                                <?php if (
                                                    !empty($produit["image"])
                                                ): ?>

                                                    <img
                                                        src="../assets/images/<?= htmlspecialchars(
                                                            $produit["image"]
                                                        ) ?>"
                                                        alt="<?= htmlspecialchars(
                                                            $produit["nom"]
                                                        ) ?>"
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


                                            <!-- NOM -->

                                            <td>

                                                <strong>

                                                    <?= htmlspecialchars(
                                                        $produit["nom"]
                                                    ) ?>

                                                </strong>


                                                <?php if (
                                                    !empty(
                                                        $produit["description"]
                                                    )
                                                ): ?>

                                                    <br>

                                                    <small
                                                        class="text-muted">

                                                        <?= htmlspecialchars(
                                                            mb_strimwidth(
                                                                $produit[
                                                                    "description"
                                                                ],
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

                                                <span
                                                    class="badge
                                                           text-bg-secondary">

                                                    <?= htmlspecialchars(
                                                        $produit["categorie"]
                                                    ) ?>

                                                </span>

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

                                                <?php if (
                                                    $produit["stock"] <= 5
                                                ): ?>

                                                    <span
                                                        class="badge bg-danger">

                                                        <?= $produit["stock"] ?>

                                                        restant(s)

                                                    </span>

                                                <?php else: ?>

                                                    <span
                                                        class="badge bg-success">

                                                        <?= $produit["stock"] ?>

                                                    </span>

                                                <?php endif; ?>

                                            </td>


                                            <!-- DATE -->

                                            <td>

                                                <?= date(
                                                    "d/m/Y",
                                                    strtotime(
                                                        $produit[
                                                            "date_creation"
                                                        ]
                                                    )
                                                ) ?>

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

                                                        <i
                                                            class="bi bi-pencil">
                                                        </i>

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

                                                        <i
                                                            class="bi bi-trash">
                                                        </i>

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


        </main>

    </div>

</div>


<!-- FOOTER -->

<?php include "includes/footer.php"; ?>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>