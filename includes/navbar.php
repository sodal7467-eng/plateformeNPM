<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Charger les paramètres boutique si la DB est disponible
if (isset($pdo) && $pdo instanceof PDO) {
    require_once __DIR__ . "/parametres.php";
    $navbarLogo = parametre($pdo, "logo_texte", "NPM");
    $navbarSousLogo = parametre($pdo, "logo_soustitre", "QUINCAILLERIE");

    // Dernières factures du client connecté (menu déroulant)
    $navbarFactures = [];
    if (isset($_SESSION["id"])) {
        try {
            $reqFactures = $pdo->prepare("
                SELECT f.numero_facture, f.commande_id
                FROM factures f
                INNER JOIN commandes c ON f.commande_id = c.id
                WHERE c.utilisateur_id = ?
                ORDER BY f.id DESC
                LIMIT 5
            ");
            $reqFactures->execute([(int) $_SESSION["id"]]);
            $navbarFactures = $reqFactures->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $navbarFactures = [];
        }
    }
} else {
    $navbarLogo = "NPM";
    $navbarSousLogo = "QUINCAILLERIE";
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand fw-bold" href="index.php">
            <span class="text-warning"><?= htmlspecialchars($navbarLogo) ?></span>
            <small class="d-block text-light" style="font-size: 10px;">
                <?= htmlspecialchars($navbarSousLogo) ?>
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

                    <div class="dropdown">

                        <button
                            class="btn btn-warning dropdown-toggle d-flex align-items-center gap-1"
                            type="button"
                            id="menuCompteNPM"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            👤 <?= htmlspecialchars($_SESSION["nom"] ?? "Mon compte") ?>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuCompteNPM">

                            <li>
                                <a class="dropdown-item" href="profil.php">
                                    Mon compte
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="modifier-profil.php">
                                    Profil
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="mes-commende.php">
                                    Ma commande
                                </a>
                            </li>

                            <?php if (!empty($navbarFactures)): ?>

                                <li><hr class="dropdown-divider"></li>

                                <li><h6 class="dropdown-header">Télécharger ma facture</h6></li>

                                <?php foreach ($navbarFactures as $navFacture): ?>
                                    <li>
                                        <a class="dropdown-item" href="facture-client.php?id=<?= (int) $navFacture["commande_id"] ?>&amp;telecharger=1">
                                            📄 <?= htmlspecialchars($navFacture["numero_facture"]) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>

                            <?php endif; ?>

                            <?php if (($_SESSION["role"] ?? "") === "admin"): ?>

                                <li><hr class="dropdown-divider"></li>

                                <li>
                                    <a class="dropdown-item fw-bold text-warning" href="admin/index.php">
                                        ⚙️ Espace Administration
                                    </a>
                                </li>

                            <?php endif; ?>

                            <li><hr class="dropdown-divider"></li>

                            <li>
                                <a class="dropdown-item text-danger" href="deconnexion.php">
                                    Déconnexion
                                </a>
                            </li>

                        </ul>

                    </div>

                <?php else: ?>

                    <a href="connexion.php" class="btn btn-warning">
                        Connexion
                    </a>

                <?php endif; ?>

            </div>

        </div>
    </div>
</nav>
