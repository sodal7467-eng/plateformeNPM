<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<nav class="admin-navbar">

    <!-- GAUCHE -->

    <div class="admin-navbar-left">

        <!-- MENU BURGER MOBILE -->

        <button
            type="button"
            class="admin-menu-btn"
            id="adminMenuBtn"
            aria-label="Ouvrir le menu"
        >

            <i class="bi bi-list"></i>

        </button>


        <!-- LOGO -->

        <a
            href="produits.php"
            class="admin-logo"
        >

            <span>NPM</span>

            <strong>ADMIN</strong>

        </a>

    </div>


    <!-- ACTIONS -->

    <div class="admin-navbar-actions">

        <a
            href="../index.php"
            class="admin-btn admin-btn-site"
        >

            <i class="bi bi-globe"></i>

            <span>Voir le site</span>

        </a>


        <a
            href="../deconnexion.php"
            class="admin-btn admin-btn-logout"
        >

            <i class="bi bi-box-arrow-right"></i>

            <span>Déconnexion</span>

        </a>

    </div>


    <!-- =====================================================
         MENU MOBILE
    ====================================================== -->

    <div
        class="admin-mobile-menu"
        id="adminMobileMenu"
    >

        <div class="admin-mobile-menu-header">

            <span>
                <i class="bi bi-grid me-2"></i>
                Menu principal
            </span>

            <button
                type="button"
                id="adminMobileClose"
                aria-label="Fermer"
            >

                <i class="bi bi-x-lg"></i>

            </button>

        </div>


        <!-- Produits -->

        <a href="produits.php">

            <i class="bi bi-box-seam"></i>

            <span>Produits</span>

        </a>


        <!-- Catégories -->

        <a href="categories.php">

            <i class="bi bi-tags"></i>

            <span>Catégories</span>

        </a>


        <!-- Clients -->

        <a href="clients.php">

            <i class="bi bi-people"></i>

            <span>Clients</span>

        </a>


        <!-- Commandes -->

        <a href="commandes.php">

            <i class="bi bi-cart3"></i>

            <span>Commandes</span>

        </a>


        <!-- Nouvelle vente -->

        <a href="nouvelle-vente.php">

            <i class="bi bi-cart-plus"></i>

            <span>Nouvelle vente</span>

        </a>


        <!-- Factures -->

        <a href="factures.php">

            <i class="bi bi-receipt"></i>

            <span>Factures</span>

        </a>


        <!-- Crédits -->

        <a href="credits.php">

            <i class="bi bi-cash-coin"></i>

            <span>Crédits clients</span>

        </a>


        <!-- Stocks -->

        <a href="stocks.php">

            <i class="bi bi-bar-chart"></i>

            <span>Stocks</span>

        </a>


        <!-- Messages -->

        <a href="messages.php">

            <i class="bi bi-envelope"></i>

            <span>Messages</span>

        </a>


        <hr>


        <!-- Voir le site -->

        <a
            href="../index.php"
            class="mobile-site-link"
        >

            <i class="bi bi-globe"></i>

            <span>Voir le site</span>

        </a>


        <!-- Déconnexion -->

        <a
            href="../deconnexion.php"
            class="mobile-logout-link"
        >

            <i class="bi bi-box-arrow-right"></i>

            <span>Déconnexion</span>

        </a>

    </div>

</nav>


<!-- =========================================================
     JAVASCRIPT MENU MOBILE
========================================================= -->

<script>

document.addEventListener("DOMContentLoaded", function () {

    const menuBtn = document.getElementById("adminMenuBtn");

    const mobileMenu = document.getElementById("adminMobileMenu");

    const closeBtn = document.getElementById("adminMobileClose");


    // OUVRIR LE MENU

    if (menuBtn && mobileMenu) {

        menuBtn.addEventListener("click", function () {

            mobileMenu.classList.add("active");

        });

    }


    // FERMER LE MENU

    if (closeBtn && mobileMenu) {

        closeBtn.addEventListener("click", function () {

            mobileMenu.classList.remove("active");

        });

    }


    // Fermer quand on clique sur un lien

    if (mobileMenu) {

        const liens = mobileMenu.querySelectorAll("a");

        liens.forEach(function (lien) {

            lien.addEventListener("click", function () {

                mobileMenu.classList.remove("active");

            });

        });

    }

});

</script>