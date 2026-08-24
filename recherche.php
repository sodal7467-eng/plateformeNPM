<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Accueil - NPM Quincaillerie</title>

    <!-- Bootstrap -->
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
        rel="stylesheet"
    >

    <!-- Ton CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

    <?php include 'includes/navbar.php'; ?>
    <?php
// includes/navbar.php
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand fw-bold" href="index.php">
            <span class="text-warning">NPM</span> Quincaillerie
        </a>

        <!-- Bouton mobile -->
        <button 
            class="navbar-toggler" 
            type="button" 
            data-bs-toggle="collapse" 
            data-bs-target="#navbarNPM"
            aria-controls="navbarNPM"
            aria-expanded="false"
            aria-label="Menu"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu -->
        <div class="collapse navbar-collapse" id="navbarNPM">

            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        Accueil
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="produits.php">
                        Produits
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="categories.php">
                        Catégories
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="a-propos.php">
                        À propos
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="contact.php">
                        Contact
                    </a>
                </li>

            </ul>

            <!-- Recherche -->
            <form class="d-flex me-3" action="recherche.php" method="GET">
                <input 
                    class="form-control form-control-sm" 
                    type="search" 
                    name="q"
                    placeholder="Rechercher..."
                    aria-label="Rechercher"
                >

                <button class="btn btn-warning btn-sm ms-1" type="submit">
                    🔍
                </button>
            </form>

            <!-- Panier -->
            <a href="panier.php" class="btn btn-outline-warning me-2">
                🛒 Panier
            </a>

            <!-- Compte -->
            <a href="connexion.php" class="btn btn-warning">
                👤 Compte
            </a>

        </div>
    </div>
</nav>


   

    <main>

        <h1 class="text-center mt-5">
            Bienvenue chez NPM Quincaillerie
        </h1>

    </main>


    <?php include 'includes/footer.php'; ?>
      <?php
// includes/footer.php
?>

<footer class="bg-dark text-white mt-5">

    <div class="container py-5">

        <div class="row g-4">

            <!-- À propos -->
            <div class="col-lg-4 col-md-6">

                <h4 class="fw-bold">
                    <span class="text-warning">NPM</span> Quincaillerie
                </h4>

                <p class="text-secondary">
                    Votre partenaire pour tous vos besoins en outils,
                    matériaux de construction, plomberie, électricité
                    et équipements.
                </p>

                <p class="mb-1">
                    📍 Dakar, Sénégal
                </p>

                <p class="mb-1">
                    📞 +221 77 000 00 00
                </p>

                <p>
                    ✉️ contact@npm-quincaillerie.com
                </p>

            </div>


            <!-- Liens rapides -->
            <div class="col-lg-2 col-md-6">

                <h5 class="fw-bold text-warning">
                    Liens rapides
                </h5>

                <ul class="list-unstyled">

                    <li class="mb-2">
                        <a href="index.php" class="text-secondary text-decoration-none">
                            Accueil
                        </a>
                    </li>

                    <li class="mb-2">
                        <a href="produits.php" class="text-secondary text-decoration-none">
                            Produits
                        </a>
                    </li>

                    <li class="mb-2">
                        <a href="categories.php" class="text-secondary text-decoration-none">
                            Catégories
                        </a>
                    </li>

                    <li class="mb-2">
                        <a href="a-propos.php" class="text-secondary text-decoration-none">
                            À propos
                        </a>
                    </li>

                    <li>
                        <a href="contact.php" class="text-secondary text-decoration-none">
                            Contact
                        </a>
                    </li>

                </ul>

            </div>


            <!-- Service client -->
            <div class="col-lg-3 col-md-6">

                <h5 class="fw-bold text-warning">
                    Service client
                </h5>

                <ul class="list-unstyled">

                    <li class="mb-2">
                        <a href="mes-commandes.php" class="text-secondary text-decoration-none">
                            Mes commandes
                        </a>
                    </li>

                    <li class="mb-2">
                        <a href="faq.php" class="text-secondary text-decoration-none">
                            FAQ
                        </a>
                    </li>

                    <li class="mb-2">
                        <a href="conditions.php" class="text-secondary text-decoration-none">
                            Conditions générales
                        </a>
                    </li>

                    <li>
                        <a href="confidentialite.php" class="text-secondary text-decoration-none">
                            Confidentialité
                        </a>
                    </li>

                </ul>

            </div>


            <!-- Réseaux sociaux -->
            <div class="col-lg-3 col-md-6">

                <h5 class="fw-bold text-warning">
                    Suivez-nous
                </h5>

                <p class="text-secondary">
                    Retrouvez-nous sur les réseaux sociaux.
                </p>

                <div class="d-flex gap-2">

                    <a href="#" class="btn btn-outline-light">
                        Facebook
                    </a>

                    <a href="#" class="btn btn-outline-light">
                        Instagram
                    </a>

                    <a href="#" class="btn btn-outline-light">
                        WhatsApp
                    </a>

                </div>

            </div>

        </div>

    </div>


    <!-- Copyright -->
    <div class="border-top border-secondary">

        <div class="container py-3">

            <div class="row">

                <div class="col-md-6 text-center text-md-start">

                    <small class="text-secondary">
                        © 2026 NPM Quincaillerie. Tous droits réservés.
                    </small>

                </div>

                <div class="col-md-6 text-center text-md-end">

                    <small class="text-secondary">
                        Développé avec ❤️ au Sénégal
                    </small>

                </div>

            </div>

        </div>

    </div>

</footer>

   
    <script 
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>

</body>

</html>