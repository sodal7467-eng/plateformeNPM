<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<aside class="admin-sidebar">


<!-- Logo -->

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


<!-- Menu -->

<nav class="sidebar-menu">

    <p class="sidebar-title">
        MENU PRINCIPAL
    </p>


    <!-- Produits -->

    <a href="produits.php" class="sidebar-link">

        <span class="sidebar-icon">📦</span>

        <span>Produits</span>

    </a>


    <!-- Catégories -->

    <a href="categories.php" class="sidebar-link">

        <span class="sidebar-icon">🏷️</span>

        <span>Catégories</span>

    </a>


    <!-- Clients -->

    <a href="clients.php" class="sidebar-link">

        <span class="sidebar-icon">👥</span>

        <span>Clients</span>

    </a>


    <!-- Commandes -->

    <a href="commandes.php" class="sidebar-link">

        <span class="sidebar-icon">🛒</span>

        <span>Commandes</span>

    </a>


    <!-- Messages -->

    <a href="messages.php" class="sidebar-link">

        <span class="sidebar-icon">✉️</span>

        <span>Messages</span>

    </a>


    <div class="sidebar-separator"></div>


    <p class="sidebar-title">
        SITE
    </p>


    <!-- Voir le site -->

    <a href="../index.php" class="sidebar-link">

        <span class="sidebar-icon">🌐</span>

        <span>Voir le site</span>

    </a>


    <!-- Déconnexion -->

    <a href="../deconnexion.php" class="sidebar-link sidebar-logout">

        <span class="sidebar-icon">↪</span>

        <span>Déconnexion</span>

    </a>

</nav>


</aside>
