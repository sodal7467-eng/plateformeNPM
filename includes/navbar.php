<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand fw-bold" href="index.php">
            <span class="text-warning">NPM</span>
            <small class="d-block text-light" style="font-size: 10px;">
                QUINCAILLERIE
            </small>
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

            <!-- Partie droite -->
            <div class="d-flex align-items-center gap-2">

                <a href="panier.php" class="btn btn-outline-light position-relative">
                    🛒 Panier
                </a>

                <?php if (isset($_SESSION["id"])): ?>

                    <a href="profil.php" class="btn btn-warning">
                        Mon compte
                    </a>

                <?php else: ?>

                    <a href="connexion.php" class="btn btn-warning">
                        Connexion
                    </a>

                <?php endif; ?>

            </div>

        </div>
    </div>
</nav>