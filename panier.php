<?php

session_start();

require_once "config/database.php";


/*
|--------------------------------------------------------------------------
| Initialiser le panier
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["panier"])) {
    $_SESSION["panier"] = [];
}


/*
|--------------------------------------------------------------------------
| Ajouter un produit
|--------------------------------------------------------------------------
*/

if (
    isset($_GET["action"])
    && $_GET["action"] === "ajouter"
    && isset($_GET["id"])
    && is_numeric($_GET["id"])
) {

    $produitId = (int) $_GET["id"];

    $quantite = 1;

    if (isset($_GET["quantite"]) && is_numeric($_GET["quantite"])) {
        $quantite = max(1, (int) $_GET["quantite"]);
    }


    /*
    | Vérifier que le produit existe
    */

    $requete = $pdo->prepare("
        SELECT *
        FROM produits
        WHERE id = ?
    ");

    $requete->execute([$produitId]);

    $produit = $requete->fetch(PDO::FETCH_ASSOC);


    if ($produit) {

        /*
        | Vérifier le stock
        */

        if ((int) $produit["stock"] > 0) {

            if (isset($_SESSION["panier"][$produitId])) {

                $_SESSION["panier"][$produitId] += $quantite;

            } else {

                $_SESSION["panier"][$produitId] = $quantite;
            }


            /*
            | Ne pas dépasser le stock
            */

            if (
                $_SESSION["panier"][$produitId]
                > (int) $produit["stock"]
            ) {

                $_SESSION["panier"][$produitId]
                    = (int) $produit["stock"];
            }
        }
    }


    header("Location: panier.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Supprimer un produit
|--------------------------------------------------------------------------
*/

if (
    isset($_GET["action"])
    && $_GET["action"] === "supprimer"
    && isset($_GET["id"])
    && is_numeric($_GET["id"])
) {

    $produitId = (int) $_GET["id"];

    unset($_SESSION["panier"][$produitId]);

    header("Location: panier.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Vider le panier
|--------------------------------------------------------------------------
*/

if (
    isset($_GET["action"])
    && $_GET["action"] === "vider"
) {

    $_SESSION["panier"] = [];

    header("Location: panier.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Modifier les quantités
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["quantites"]) && is_array($_POST["quantites"])) {

        foreach ($_POST["quantites"] as $produitId => $quantite) {

            if (!is_numeric($produitId) || !is_numeric($quantite)) {
                continue;
            }

            $produitId = (int) $produitId;
            $quantite = (int) $quantite;


            /*
            | Si quantité = 0, supprimer
            */

            if ($quantite <= 0) {

                unset($_SESSION["panier"][$produitId]);

                continue;
            }


            /*
            | Vérifier le stock
            */

            $requete = $pdo->prepare("
                SELECT stock
                FROM produits
                WHERE id = ?
            ");

            $requete->execute([$produitId]);

            $produit = $requete->fetch(PDO::FETCH_ASSOC);


            if ($produit) {

                $stock = (int) $produit["stock"];

                if ($stock > 0) {

                    $_SESSION["panier"][$produitId]
                        = min($quantite, $stock);

                } else {

                    unset($_SESSION["panier"][$produitId]);
                }
            }
        }
    }


    header("Location: panier.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Récupérer les produits du panier
|--------------------------------------------------------------------------
*/

$produitsPanier = [];

$totalPanier = 0;
$totalArticles = 0;


if (!empty($_SESSION["panier"])) {

    $ids = array_keys($_SESSION["panier"]);

    $placeholders = implode(
        ",",
        array_fill(0, count($ids), "?")
    );


    $requete = $pdo->prepare("
        SELECT
            produits.*,
            categories.nom AS categorie_nom
        FROM produits
        LEFT JOIN categories
            ON produits.categorie = categories.id
        WHERE produits.id IN ($placeholders)
    ");

    $requete->execute($ids);

    $produits = $requete->fetchAll(PDO::FETCH_ASSOC);


    foreach ($produits as $produit) {

        $id = (int) $produit["id"];

        $quantite = (int) $_SESSION["panier"][$id];

        $prix = (float) $produit["prix"];

        $sousTotal = $prix * $quantite;


        $produit["quantite"] = $quantite;

        $produit["sous_total"] = $sousTotal;


        $produitsPanier[] = $produit;


        $totalPanier += $sousTotal;

        $totalArticles += $quantite;
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

    <title>
        Mon panier - Quincaillerie NPM
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

</head>


<body>


<!-- =========================================================
     NAVBAR
========================================================== -->

<?php include "includes/navbar.php"; ?>



<main>


    <!-- =====================================================
         EN-TÊTE
    ====================================================== -->

    <section class="cart-header py-5">

        <div class="container">

            <div class="text-center">

                <span class="text-orange fw-bold">
                    MON ESPACE
                </span>

                <h1 class="fw-bold mt-2">
                    Mon panier
                </h1>

                <p class="text-muted">
                    Vérifiez vos produits avant de passer votre commande.
                </p>

            </div>

        </div>

    </section>



    <!-- =====================================================
         PANIER
    ====================================================== -->

    <section class="py-5">

        <div class="container">


            <?php if (empty($produitsPanier)): ?>


                <!-- =================================================
                     PANIER VIDE
                ================================================== -->

                <div class="text-center py-5">

                    <i class="bi bi-cart-x display-1 text-muted"></i>

                    <h2 class="fw-bold mt-4">
                        Votre panier est vide
                    </h2>

                    <p class="text-muted">
                        Vous n'avez encore ajouté aucun produit.
                    </p>

                    <a
                        href="produits.php"
                        class="btn btn-dark mt-3"
                    >

                        <i class="bi bi-shop me-2"></i>

                        Découvrir nos produits

                    </a>

                </div>


            <?php else: ?>


                <form method="POST" action="panier.php">


                    <div class="row g-4">


                        <!-- =================================================
                             PRODUITS
                        ================================================== -->

                        <div class="col-lg-8">


                            <div class="card border-0 shadow-sm">


                                <div class="card-header bg-white py-3">

                                    <h5 class="fw-bold mb-0">

                                        <i class="bi bi-cart3 me-2"></i>

                                        Produits dans le panier

                                    </h5>

                                </div>


                                <div class="card-body p-0">

                                    <div class="table-responsive">

                                        <table class="table table-hover align-middle mb-0">

                                            <thead class="table-dark">

                                                <tr>

                                                    <th>Produit</th>

                                                    <th>Prix unitaire</th>

                                                    <th class="text-center">Quantité</th>

                                                    <th>Sous-total</th>

                                                    <th class="text-center">Actions</th>

                                                </tr>

                                            </thead>


                                            <tbody>

                                                <?php foreach ($produitsPanier as $produit): ?>

                                                    <tr>


                                                        <!-- Produit -->

                                                        <td>

                                                            <div class="d-flex align-items-center gap-3">

                                                                <div
                                                                    class="cart-product-image"
                                                                    style="width:60px;height:60px;flex-shrink:0;"
                                                                >

                                                                    <?php if (!empty($produit["image"])): ?>

                                                                        <img
                                                                            src="uploads/<?= htmlspecialchars($produit["image"]) ?>"
                                                                            alt="<?= htmlspecialchars($produit["nom"]) ?>"
                                                                            style="
                                                                                width:100%;
                                                                                height:100%;
                                                                                object-fit:cover;
                                                                                border-radius:10px;
                                                                            "
                                                                        >

                                                                    <?php else: ?>

                                                                        <div
                                                                            class="d-flex align-items-center justify-content-center bg-light rounded"
                                                                            style="width:100%;height:100%;"
                                                                        >

                                                                            <i class="bi bi-image text-muted"></i>

                                                                        </div>

                                                                    <?php endif; ?>

                                                                </div>


                                                                <div>

                                                                    <small class="text-muted d-block">

                                                                        <?= htmlspecialchars(
                                                                            $produit["categorie_nom"] ?? "Produit"
                                                                        ) ?>

                                                                    </small>

                                                                    <strong>

                                                                        <?= htmlspecialchars(
                                                                            $produit["nom"]
                                                                        ) ?>

                                                                    </strong>

                                                                </div>

                                                            </div>

                                                        </td>


                                                        <!-- Prix unitaire -->

                                                        <td>

                                                            <?= number_format(
                                                                (float) $produit["prix"],
                                                                0,
                                                                ",",
                                                                " "
                                                            ) ?>

                                                            FCFA

                                                        </td>


                                                        <!-- Quantité -->

                                                        <td class="text-center">

                                                            <input
                                                                type="number"
                                                                name="quantites[<?= $produit["id"] ?>]"
                                                                class="form-control text-center mx-auto"
                                                                style="width:85px;"
                                                                value="<?= $produit["quantite"] ?>"
                                                                min="1"
                                                                max="<?= $produit["stock"] ?>"
                                                            >

                                                            <small class="text-muted d-block mt-1">

                                                                Stock :
                                                                <?= $produit["stock"] ?>

                                                            </small>

                                                        </td>


                                                        <!-- Sous-total -->

                                                        <td>

                                                            <strong>

                                                                <?= number_format(
                                                                    $produit["sous_total"],
                                                                    0,
                                                                    ",",
                                                                    " "
                                                                ) ?>

                                                                FCFA

                                                            </strong>

                                                        </td>


                                                        <!-- Actions -->

                                                        <td class="text-center">

                                                            <a
                                                                href="panier.php?action=supprimer&id=<?= $produit["id"] ?>"
                                                                class="btn btn-sm btn-outline-danger"
                                                                title="Supprimer"
                                                                onclick="return confirm('Voulez-vous supprimer ce produit du panier ?');"
                                                            >

                                                                <i class="bi bi-trash"></i>

                                                            </a>

                                                        </td>


                                                    </tr>

                                                <?php endforeach; ?>

                                            </tbody>

                                        </table>

                                    </div>

                                </div>


                            </div>



                            <!-- Actions panier -->

                            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">


                                <a
                                    href="produits.php"
                                    class="btn btn-outline-dark"
                                >

                                    <i class="bi bi-arrow-left me-1"></i>

                                    Continuer mes achats

                                </a>


                                <div class="d-flex gap-2">


                                    <button
                                        type="submit"
                                        class="btn btn-outline-dark"
                                    >

                                        <i class="bi bi-arrow-repeat me-1"></i>

                                        Mettre à jour

                                    </button>


                                    <a
                                        href="panier.php?action=vider"
                                        class="btn btn-outline-danger"
                                        onclick="return confirm('Voulez-vous vraiment vider votre panier ?');"
                                    >

                                        <i class="bi bi-trash me-1"></i>

                                        Vider le panier

                                    </a>


                                </div>


                            </div>


                        </div>



                        <!-- =================================================
                             RÉSUMÉ
                        ================================================== -->

                        <div class="col-lg-4">


                            <div class="cart-summary">


                                <h4 class="fw-bold mb-4">

                                    Résumé de la commande

                                </h4>


                                <div class="d-flex justify-content-between mb-3">

                                    <span>
                                        Articles
                                    </span>

                                    <strong>
                                        <?= $totalArticles ?>
                                    </strong>

                                </div>


                                <div class="d-flex justify-content-between mb-3">

                                    <span>
                                        Sous-total
                                    </span>

                                    <strong>

                                        <?= number_format(
                                            $totalPanier,
                                            0,
                                            ",",
                                            " "
                                        ) ?>

                                        FCFA

                                    </strong>

                                </div>


                                <div class="d-flex justify-content-between mb-3">

                                    <span>
                                        Livraison
                                    </span>

                                    <strong>
                                        À déterminer
                                    </strong>

                                </div>


                                <hr>


                                <div class="d-flex justify-content-between mb-4">

                                    <strong>
                                        Total
                                    </strong>

                                    <h4 class="text-orange fw-bold">

                                        <?= number_format(
                                            $totalPanier,
                                            0,
                                            ",",
                                            " "
                                        ) ?>

                                        FCFA

                                    </h4>

                                </div>



                                <!-- Passer commande -->

                                <a
                                    href="commande.php"
                                    class="btn btn-dark btn-lg w-100"
                                >

                                    Passer la commande

                                    <i class="bi bi-arrow-right ms-1"></i>

                                </a>



                                <div class="mt-4 text-center">

                                    <small class="text-muted">

                                        <i class="bi bi-shield-check me-1"></i>

                                        Paiement sécurisé

                                    </small>

                                </div>


                            </div>


                        </div>


                    </div>


                </form>


            <?php endif; ?>


        </div>

    </section>


</main>



<!-- =========================================================
     FOOTER
========================================================== -->

<?php include "includes/footer.php"; ?>



<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>