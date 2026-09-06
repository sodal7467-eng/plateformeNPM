
<?php
session_start();

require_once("config/database.php");
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Quincaillerie NPM</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Notre CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<?php
require_once("includes/navbar.php");
?>


<!-- =====================================
     HERO
===================================== -->

<section class="hero-npm">

    <div class="container">

        <div class="row align-items-center">

            <!-- Texte -->
            <div class="col-lg-6">

                <span class="hero-badge">
                    QUINCAILLERIE NPM
                </span>

                <h1>
                    Tout pour réussir
                    <span>vos projets.</span>
                </h1>

                <p>
                    Retrouvez chez NPM des outils, matériaux et
                    équipements de qualité pour vos travaux de
                    construction, rénovation et bricolage.
                </p>

                <div class="mt-4">

                    <a href="produits.php" class="btn btn-warning btn-lg me-2">
                        Découvrir les produits
                    </a>

                    <a href="contact.php" class="btn btn-outline-light btn-lg">
                        Nous contacter
                    </a>

                </div>

            </div>


            <!-- Bloc visuel -->
            <div class="col-lg-6">

                <div class="hero-box">

                    <div class="hero-box-icon">
                        <img src="assets/images/images-quincaillerie.png" alt="">
                    </div>

                    <h2>
                        NPM
                    </h2>

                    <p>
                        Qualité • Fiabilité • Proximité
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>



<!-- =====================================
     CATÉGORIES
===================================== -->

<section class="py-5">

    <div class="container">

        <div class="section-title text-center mb-5">

            <span>
                NOS CATÉGORIES
            </span>

            <h2>
                Trouvez ce qu'il vous faut
            </h2>

            <p>
                Découvrez nos différentes catégories de produits.
            </p>

        </div>


        <div class="row g-4">

            <?php

            $requete = $pdo->query(
                "SELECT * FROM categories ORDER BY id DESC LIMIT 8"
            );

            $categories = $requete->fetchAll(PDO::FETCH_ASSOC);

            ?>


            <?php if (!empty($categories)): ?>

                <?php foreach ($categories as $categorie): ?>

                    <div class="col-lg-3 col-md-6">

                        <a
                            href="produits.php?categorie=<?= $categorie['id'] ?>"
                            class="category-card"
                        >

                            <div class="category-icon">
                                🛠️
                            </div>

                            <h3>
                                <?= htmlspecialchars($categorie['nom']) ?>
                            </h3>

                            <span>
                                Voir les produits →
                            </span>

                        </a>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="col-12 text-center">

                    <p>
                        Aucune catégorie disponible.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

</section>



<!-- =====================================
     POURQUOI NPM
===================================== -->

<section class="why-npm py-5">

    <div class="container">

        <div class="text-center section-title mb-5">

            <span>
                POURQUOI NOUS CHOISIR ?
            </span>

            <h2>
                Pourquoi choisir NPM ?
            </h2>

        </div>


        <div class="row g-4">

            <div class="col-lg-3 col-md-6">

                <div class="feature-card">

                    <div class="feature-icon">
                        ✓
                    </div>

                    <h3>
                        Qualité
                    </h3>

                    <p>
                        Des produits sélectionnés pour répondre
                        à vos besoins.
                    </p>

                </div>

            </div>


            <div class="col-lg-3 col-md-6">

                <div class="feature-card">

                    <div class="feature-icon">
                        💰
                    </div>

                    <h3>
                        Bons prix
                    </h3>

                    <p>
                        Des solutions accessibles pour vos différents
                        projets.
                    </p>

                </div>

            </div>


            <div class="col-lg-3 col-md-6">

                <div class="feature-card">

                    <div class="feature-icon">
                        🚚
                    </div>

                    <h3>
                        Service
                    </h3>

                    <p>
                        Une expérience simple et pratique pour
                        trouver vos produits.
                    </p>

                </div>

            </div>


            <div class="col-lg-3 col-md-6">

                <div class="feature-card">

                    <div class="feature-icon">
                        🤝
                    </div>

                    <h3>
                        Confiance
                    </h3>

                    <p>
                        Nous plaçons la satisfaction de nos clients
                        au cœur de nos priorités.
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>



<!-- =====================================
     PRODUITS
===================================== -->

<section class="py-5">

    <div class="container">

        <div class="section-title text-center mb-5">

            <span>
                NOS PRODUITS
            </span>

            <h2>
                Produits populaires
            </h2>

            <p>
                Découvrez quelques-uns de nos produits.
            </p>

        </div>


        <?php

        $requete = $pdo->query(
            "SELECT produits.*, categories.nom AS categorie_nom
             FROM produits
             LEFT JOIN categories
             ON produits.categorie = categories.id
             ORDER BY produits.id DESC
             LIMIT 8"
        );

        $produits = $requete->fetchAll(PDO::FETCH_ASSOC);

        ?>


        <div class="row g-4">

            <?php if (!empty($produits)): ?>

                <?php foreach ($produits as $produit): ?>

                    <div class="col-lg-3 col-md-6">

                        <div class="product-card">

                            <!-- Image -->
                            <div class="product-image">

                                <?php if (!empty($produit['image'])): ?>

                                    <img
                                        src="uploads/<?= htmlspecialchars($produit['image']) ?>"
                                        alt="<?= htmlspecialchars($produit['nom']) ?>"
                                    >

                                <?php else: ?>

                                    <div class="no-image">
                                        🛠️
                                    </div>

                                <?php endif; ?>

                            </div>


                            <!-- Informations -->
                            <div class="product-content">

                                <small>
                                    <?= htmlspecialchars(
                                        $produit['categorie_nom'] ?? 'Produit'
                                    ) ?>
                                </small>

                                <h3>
                                    <?= htmlspecialchars($produit['nom']) ?>
                                </h3>

                                <p class="price">
                                    <?= number_format(
                                        $produit['prix'],
                                        0,
                                        ',',
                                        ' '
                                    ) ?>
                                    FCFA
                                </p>

                                <a
                                    href="details-produit.php?id=<?= $produit['id'] ?>"
                                    class="btn btn-dark w-100"
                                >
                                    Voir le produit
                                </a>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="col-12 text-center">

                    <p>
                        Aucun produit disponible pour le moment.
                    </p>

                </div>

            <?php endif; ?>

        </div>


        <div class="text-center mt-5">

            <a href="produits.php" class="btn btn-warning btn-lg">
                Voir tous les produits
            </a>

        </div>

    </div>

</section>



<!-- =====================================
     APPEL À L'ACTION
===================================== -->

<section class="cta-npm">

    <div class="container text-center">

        <h2>
            Un projet en tête ?
        </h2>

        <p>
            Découvrez notre catalogue ou contactez-nous
            pour obtenir plus d'informations.
        </p>

        <a
            href="produits.php"
            class="btn btn-warning btn-lg me-2"
        >
            Voir nos produits
        </a>

        <a
            href="contact.php"
            class="btn btn-outline-light btn-lg"
        >
            Nous contacter
        </a>

    </div>

</section>



<?php
require_once("includes/footer.php");
?>


<!-- Bootstrap JS -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>
</html>

