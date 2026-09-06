
<?php
session_start();

require_once("config/database.php");

/* =========================================
   RECHERCHE
========================================= */

$recherche = $_GET["recherche"] ?? "";

/* =========================================
   FILTRE CATÉGORIE
========================================= */

$categorie = $_GET["categorie"] ?? "";


/* =========================================
   RÉCUPÉRER LES CATÉGORIES
========================================= */

$requeteCategories = $pdo->query(
    "SELECT * FROM categories ORDER BY nom ASC"
);

$categories = $requeteCategories->fetchAll(PDO::FETCH_ASSOC);


/* =========================================
   RÉCUPÉRER LES PRODUITS
========================================= */

$sql = "
    SELECT 
        produits.*,
        categories.nom AS categorie_nom
    FROM produits
    LEFT JOIN categories
        ON produits.categorie = categories.id
    WHERE 1
";

$parametres = [];


/* Recherche */

if (!empty($recherche)) {

    $sql .= "
        AND (
            produits.nom LIKE :recherche
            OR produits.description LIKE :recherche
        )
    ";

    $parametres["recherche"] = "%" . $recherche . "%";
}


/* Catégorie */

if (!empty($categorie)) {

    $sql .= "
        AND produits.categorie = :categorie
    ";

    $parametres["categorie"] = $categorie;
}


/* Ordre */

$sql .= "
    ORDER BY produits.id DESC
";


$requeteProduits = $pdo->prepare($sql);

$requeteProduits->execute($parametres);

$produits = $requeteProduits->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Nos produits | Quincaillerie NPM</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- CSS NPM -->

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>


<body>


<?php require_once("includes/navbar.php"); ?>


<!-- =========================================
     EN-TÊTE
========================================= -->

<section class="products-header">

    <div class="container">

        <div class="text-center">

            <span>
                QUINCAILLERIE NPM
            </span>

            <h1>
                Nos produits
            </h1>

            <p>
                Découvrez notre catalogue de produits
                pour tous vos projets.
            </p>

        </div>

    </div>

</section>



<!-- =========================================
     CATALOGUE
========================================= -->

<section class="py-5">

    <div class="container">


        <!-- RECHERCHE -->

        <div class="product-tools mb-5">

            <form
                method="GET"
                action="produits.php"
                class="row g-3"
            >

                <!-- Recherche -->

                <div class="col-lg-6">

                    <div class="search-box">

                        <input
                            type="text"
                            name="recherche"
                            class="form-control"
                            placeholder="Rechercher un produit..."
                            value="<?= htmlspecialchars($recherche) ?>"
                        >

                    </div>

                </div>


                <!-- Catégorie -->

                <div class="col-lg-4">

                    <select
                        name="categorie"
                        class="form-select"
                    >

                        <option value="">
                            Toutes les catégories
                        </option>


                        <?php foreach ($categories as $cat): ?>

                            <option
                                value="<?= $cat["id"] ?>"
                                <?= ($categorie == $cat["id"]) ? "selected" : "" ?>
                            >

                                <?= htmlspecialchars($cat["nom"]) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- Bouton -->

                <div class="col-lg-2">

                    <button
                        type="submit"
                        class="btn btn-warning w-100"
                    >
                        Rechercher
                    </button>

                </div>

            </form>

        </div>



        <!-- =================================
             RÉSULTATS
        ================================= -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h2 class="catalogue-title">
                    Catalogue
                </h2>

                <p class="text-muted mb-0">

                    <?= count($produits) ?>

                    produit(s) trouvé(s)

                </p>

            </div>


            <?php if (!empty($recherche) || !empty($categorie)): ?>

                <a
                    href="produits.php"
                    class="btn btn-outline-dark"
                >
                    Réinitialiser
                </a>

            <?php endif; ?>

        </div>



        <!-- =================================
             PRODUITS
        ================================= -->

        <div class="row g-4">


            <?php if (!empty($produits)): ?>


                <?php foreach ($produits as $produit): ?>


                    <div class="col-xl-3 col-lg-4 col-md-6">


                        <div class="product-card">


                            <!-- IMAGE -->

                            <div class="product-image">


                                <?php if (!empty($produit["image"])): ?>


                                    <img
                                        src="uploads/<?= htmlspecialchars($produit["image"]) ?>"
                                        alt="<?= htmlspecialchars($produit["nom"]) ?>"
                                    >


                                <?php else: ?>


                                    <div class="no-image">
                                        🛠️
                                    </div>


                                <?php endif; ?>


                            </div>



                            <!-- INFORMATIONS -->

                            <div class="product-content">


                                <small>

                                    <?= htmlspecialchars(
                                        $produit["categorie_nom"] ?? "Produit"
                                    ) ?>

                                </small>


                                <h3>

                                    <?= htmlspecialchars(
                                        $produit["nom"]
                                    ) ?>

                                </h3>



                                <?php if (!empty($produit["description"])): ?>

                                    <p class="product-description">

                                        <?= htmlspecialchars(
                                            mb_substr(
                                                $produit["description"],
                                                0,
                                                80
                                            )
                                        ) ?>

                                        <?php if (strlen($produit["description"]) > 80): ?>
                                            ...
                                        <?php endif; ?>

                                    </p>

                                <?php endif; ?>



                                <!-- PRIX -->

                                <p class="price">

                                    <?= number_format(
                                        $produit["prix"],
                                        0,
                                        ",",
                                        " "
                                    ) ?>

                                    FCFA

                                </p>



                                <!-- STOCK -->

                                <?php if (isset($produit["stock"])): ?>


                                    <?php if ($produit["stock"] > 0): ?>

                                        <span class="stock-available">

                                            ✓
                                            <?= $produit["stock"] ?>
                                            disponible(s)

                                        </span>


                                    <?php else: ?>

                                        <span class="stock-empty">

                                            Rupture de stock

                                        </span>

                                    <?php endif; ?>


                                <?php endif; ?>



                                <!-- ACTIONS -->

                                <div class="product-actions mt-3">


                                    <a
                                        href="details-produit.php?id=<?= $produit["id"] ?>"
                                        class="btn btn-dark w-100 mb-2"
                                    >

                                        Voir le produit

                                    </a>


                                    <?php if (
                                        !isset($produit["stock"])
                                        || $produit["stock"] > 0
                                    ): ?>


                                        <a
                                            href="panier.php?action=ajouter&id=<?= $produit["id"] ?>"
                                            class="btn btn-warning w-100"
                                        >

                                            🛒 Ajouter au panier

                                        </a>


                                    <?php else: ?>


                                        <button
                                            type="button"
                                            class="btn btn-secondary w-100"
                                            disabled
                                        >

                                            Rupture de stock

                                        </button>


                                    <?php endif; ?>


                                </div>


                            </div>

                        </div>


                    </div>


                <?php endforeach; ?>


            <?php else: ?>


                <!-- AUCUN PRODUIT -->

                <div class="col-12">

                    <div class="empty-products text-center">

                        <div class="empty-icon">
                            🔎
                        </div>

                        <h3>
                            Aucun produit trouvé
                        </h3>

                        <p>
                            Aucun produit ne correspond
                            à votre recherche.
                        </p>

                        <a
                            href="produits.php"
                            class="btn btn-warning"
                        >
                            Voir tous les produits
                        </a>

                    </div>

                </div>


            <?php endif; ?>


        </div>

    </div>

</section>



<?php require_once("includes/footer.php"); ?>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>

