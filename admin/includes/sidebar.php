<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<aside class="admin-sidebar">

    <!-- LOGO -->
    <!-- <div class="sidebar-logo">

        <a href="produits.php">

            <span class="logo-npm">
                NPM
            </span>

            <span class="logo-admin">
                ADMIN
            </span>

        </a>

    </div> -->


    <!-- MENU -->
    <nav class="sidebar-menu">

        <p class="sidebar-title">
            MENU PRINCIPAL
        </p>


        <!-- PRODUITS -->
        <a href="produits.php" class="sidebar-link">

            <span class="sidebar-icon">
                📦
            </span>

            <span>
                Produits
            </span>

        </a>


        <!-- CATÉGORIES -->
        <a href="categories.php" class="sidebar-link">

            <span class="sidebar-icon">
                🏷️
            </span>

            <span>
                Catégories
            </span>

        </a>


        <!-- CLIENTS -->
        <a href="clients.php" class="sidebar-link">

            <span class="sidebar-icon">
                👥
            </span>

            <span>
                Clients
            </span>

        </a>


        <!-- COMMANDES -->
        <a href="commandes.php" class="sidebar-link">

            <span class="sidebar-icon">
                🛒
            </span>

            <span>
                Commandes
            </span>

        </a>


        <!-- NOUVELLE VENTE -->
        <a href="nouvelle-vente.php" class="sidebar-link">

            <span class="sidebar-icon">
                🧾
            </span>

            <span>
                Nouvelle vente
            </span>

        </a>


        <!-- FACTURES -->
        <a href="factures.php" class="sidebar-link">

            <span class="sidebar-icon">
                🧾
            </span>

            <span>
                Factures
            </span>

        </a>


        <!-- CRÉDITS CLIENTS -->
        <a href="credits.php" class="sidebar-link">

            <span class="sidebar-icon">
                💰
            </span>

            <span>
                Crédits clients
            </span>

        </a>


        <!-- DÉPENSES -->
        <a href="depenses.php" class="sidebar-link">

            <span class="sidebar-icon">
                🧮
            </span>

            <span>
                Dépenses
            </span>

        </a>


        <!-- RAPPORT MENSUEL -->
        <a href="rapport-mensuel.php" class="sidebar-link">

            <span class="sidebar-icon">
                📈
            </span>

            <span>
                Rapport mensuel
            </span>

        </a>


        <!-- STOCKS -->
        <a href="stocks.php" class="sidebar-link">

            <span class="sidebar-icon">
                📊
            </span>

            <span>
                Stocks
            </span>

        </a>


        <!-- MESSAGES -->
        <a href="messages.php" class="sidebar-link">

            <span class="sidebar-icon">
                ✉️
            </span>

            <span>
                Messages
            </span>

        </a>


        <!-- SÉPARATION -->
        <div class="sidebar-separator"></div>


        <p class="sidebar-title">
            SITE
        </p>


        <!-- VOIR LE SITE -->
        <a href="../index.php" class="sidebar-link">

            <span class="sidebar-icon">
                🌐
            </span>

            <span>
                Voir le site
            </span>

        </a>


        <!-- DÉCONNEXION -->
        <a
            href="../deconnexion.php"
            class="sidebar-link sidebar-logout"
        >

            <span class="sidebar-icon">
                ↪
            </span>

            <span>
                Déconnexion
            </span>

        </a>

    </nav>

</aside>