<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand fw-bold" href="index.php">
            <span class="logo-npm">NPM</span>
            <small class="d-block text-muted">Quincaillerie</small>
        </a>

        <!-- Bouton mobile -->
        <button 
            class="navbar-toggler" 
            type="button" 
            data-bs-toggle="collapse"
            data-bs-target="#navbarNPM"
            aria-controls="navbarNPM"
            aria-expanded="false"
            aria-label="Menu">
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
                    <a class="nav-link" href="contact.php">
                        Contact
                    </a>
                </li>

            </ul>

            <!-- Actions -->
            <div class="d-flex align-items-center gap-2">

                <a href="connexion.php" 
                   class="btn btn-outline-dark">
                    Connexion
                </a>

                <a href="panier.php" 
                   class="btn btn-dark position-relative">
                    🛒 Panier
                    <span class="badge bg-danger rounded-pill">
                        0
                    </span>
                </a>

            </div>

        </div>

    </div>
</nav>