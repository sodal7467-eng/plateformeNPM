<?php

session_start();

require_once("config/database.php");

/* Vérifier que l'id existe */
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: produits.php");
    exit;
}

$id = (int) $_GET["id"];

/* Récupérer le produit */
$requete = $pdo->prepare("
    SELECT 
        produits.*,
        categories.nom AS categorie_nom
    FROM produits
    LEFT JOIN categories 
        ON produits.categorie = categories.id
    WHERE produits.id = ?
");

$requete->execute([$id]);

$produit = $requete->fetch(PDO::FETCH_ASSOC);

/* Produit introuvable */
if (!$produit) {
    header("Location: produits.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($produit["nom"]) ?> - Quincaillerie NPM
    </title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- CSS NPM -->
    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<?php require_once("includes/navbar.php"); ?>


<!-- =========================
     DETAIL PRODUIT
========================= -->

<section class="product-detail-section">

    <div class="container">

        <!-- Retour -->
        <div class="mb-4">

            <a href="produits.php" class="btn btn-outline-dark">

                ← Retour aux produits

            </a>

        </div>


        <div class="row g-4 g-lg-5 align-items-center">


            <!-- =========================
                 IMAGE
            ========================== -->

            <div class="col-lg-6">

                <div class="product-detail-image">

                    <?php if (!empty($produit["image"])): ?>

                        <img
                            src="uploads/<?= htmlspecialchars($produit["image"]) ?>"
                            alt="<?= htmlspecialchars($produit["nom"]) ?>"
                        >

                    <?php else: ?>

                        <div class="no-product-image">

                            <span>📦</span>

                            <p>Image non disponible</p>

                        </div>

                    <?php endif; ?>

                </div>

            </div>



            <!-- =========================
                 INFORMATIONS
            ========================== -->

            <div class="col-lg-6">

                <div class="product-detail-content">


                    <!-- Catégorie -->

                    <?php if (!empty($produit["categorie_nom"])): ?>

                        <span class="product-category">

                            <?= htmlspecialchars($produit["categorie_nom"]) ?>

                        </span>

                    <?php endif; ?>


                    <!-- Nom -->

                    <h1 class="product-detail-title">

                        <?= htmlspecialchars($produit["nom"]) ?>

                    </h1>


                    <!-- Prix -->

                    <div class="product-detail-price mb-4">

                        <?= number_format($produit["prix"], 0, ",", " ") ?> FCFA

                    </div>


                    <!-- =========================
                         ACHAT
                    ========================== -->

                    <?php if (!isset($produit["stock"]) || $produit["stock"] > 0): ?>

                        <form
                            action="panier.php"
                            method="GET"
                            class="product-detail-form mb-4"
                        >

                            <input
                                type="hidden"
                                name="action"
                                value="ajouter"
                            >

                            <input
                                type="hidden"
                                name="id"
                                value="<?= $produit["id"] ?>"
                            >


                            <div class="quantity-box">

                                <label for="quantite">

                                    Quantité

                                </label>

                                <input
                                    type="number"
                                    id="quantite"
                                    name="quantite"
                                    value="1"
                                    min="1"
                                    <?php if (isset($produit["stock"])): ?>
                                        max="<?= (int) $produit["stock"] ?>"
                                    <?php endif; ?>
                                    class="form-control"
                                >

                            </div>


                            <button
                                type="submit"
                                class="btn btn-warning btn-lg w-100"
                            >

                                🛒 Ajouter au panier

                            </button>

                        </form>

                    <?php else: ?>

                        <button
                            class="btn btn-secondary btn-lg w-100 mb-4"
                            disabled
                        >

                            Produit indisponible

                        </button>

                    <?php endif; ?>


                    <!-- Description -->

                    <div class="product-detail-description">

                        <h4>Description</h4>

                        <p>

                            <?= nl2br(htmlspecialchars($produit["description"])) ?>

                        </p>

                    </div>


                </div>

            </div>

        </div>

    </div>

</section>


<?php require_once("includes/footer.php"); ?>


<!-- Bootstrap JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
