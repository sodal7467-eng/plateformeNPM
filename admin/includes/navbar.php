<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<nav class="navbar navbar-dark bg-dark shadow-sm admin-navbar">


<div class="container-fluid">

    <!-- LOGO -->

    <a
        class="navbar-brand fw-bold"
        href="produits.php"
    >

        <span class="text-warning">
            NPM
        </span>

        <small class="text-light ms-1">
            ADMIN
        </small>

    </a>


    <!-- ACTIONS -->

    <div class="d-flex align-items-center gap-2">

        <a
            href="../index.php"
            class="btn btn-warning"
        >

            <i class="bi bi-globe me-1"></i>

            Voir le site

        </a>


        <a
            href="../deconnexion.php"
            class="btn btn-danger"
        >

            <i class="bi bi-box-arrow-right me-1"></i>

            Déconnexion

        </a>

    </div>

</div>


</nav>
