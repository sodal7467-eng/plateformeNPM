<?php
// Charger les paramètres si la DB est là
if (isset($pdo) && $pdo instanceof PDO) {
    require_once __DIR__ . "/parametres.php";
    $footerLogoTop = parametre($pdo, "logo_texte", "NPM");
    $footerNom     = parametre($pdo, "nom_boutique", "Quincaillerie NPM");
    $footerTexte   = parametre($pdo, "footer_texte", "Votre partenaire pour vos besoins.");
    $footerAdresse = parametre($pdo, "adresse", "");
    $footerTel     = parametre($pdo, "telephone", "");
    $footerEmail   = parametre($pdo, "email_contact", "");
} else {
    $footerLogoTop = "NPM";
    $footerNom     = "Quincaillerie NPM";
    $footerTexte   = "Votre partenaire pour vos besoins.";
    $footerAdresse = "";
    $footerTel     = "";
    $footerEmail   = "";
}
?>

<footer class="footer-npm">

    <div class="container">

        <div class="row gy-5">

            <!-- NPM -->
            <div class="col-lg-4 col-md-6">

                <h4 class="footer-logo">
                    <span><?= htmlspecialchars($footerLogoTop) ?></span> <?= htmlspecialchars(preg_replace('/^' . preg_quote($footerLogoTop, '/') . '\s*/i', '', $footerNom) ?: $footerNom) ?>
                </h4>

                <p>
                    <?= nl2br(htmlspecialchars($footerTexte)) ?>
                </p>

            </div>


            <!-- Navigation -->
            <div class="col-lg-2 col-md-6">

                <h5>
                    Navigation
                </h5>

                <ul class="footer-links">

                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="produits.php">Produits</a></li>
                    <li><a href="categories.php">Catégories</a></li>
                    <li><a href="a-propos.php">À propos</a></li>
                    <li><a href="contact.php">Contact</a></li>

                </ul>

            </div>


            <!-- Contact -->
            <div class="col-lg-3 col-md-6">

                <h5>
                    <?= htmlspecialchars($footerNom) ?>
                </h5>

                <ul class="footer-contact">

                    <?php if ($footerAdresse): ?>
                        <li>📍 <?= htmlspecialchars($footerAdresse) ?></li>
                    <?php endif; ?>

                    <?php if ($footerTel): ?>
                        <li>📞 <?= htmlspecialchars($footerTel) ?></li>
                    <?php endif; ?>

                    <?php if ($footerEmail): ?>
                        <li>✉️ <a href="mailto:<?= htmlspecialchars($footerEmail) ?>" class="text-light text-decoration-none"><?= htmlspecialchars($footerEmail) ?></a></li>
                    <?php endif; ?>

                    <?php if (!$footerAdresse && !$footerTel && !$footerEmail): ?>
                        <li class="text-muted small">Coordonnées à configurer dans l'admin.</li>
                    <?php endif; ?>

                </ul>

            </div>


            <!-- Développeuse -->
            <div class="col-lg-3 col-md-6">

                <h5>
                    Création du site
                </h5>

                <p class="developer-text">
                    Site conçu & développé avec passion par
                </p>

                <p class="developer-name">
                    Soda Lô
                </p>

                <p class="developer-job">
                    Développeuse Web
                </p>

                <a
                    href="mailto:sodal7467@gmail.com"
                    class="developer-mail"
                >
                    ✉️ sodal7467@gmail.com
                </a>

            </div>

        </div>


        <hr>


        <!-- Bas du footer -->

        <div class="footer-bottom">

            <p>
                © <?= date("Y") ?> <?= htmlspecialchars($footerNom) ?>.
                Tous droits réservés.
            </p>

            <p>
                Conçu & développé par
                <strong>Soda Lô — Développeuse Web</strong>
            </p>

        </div>

    </div>

</footer>


<!-- Widget d'accessibilite (malvoyants, daltoniens, seniors) -->
<link
    rel="stylesheet"
    href="<?= (strpos($_SERVER['PHP_SELF'] ?? '', '/admin/') !== false) ? '../assets/css/accessibility.css' : 'assets/css/accessibility.css' ?>"
>
<script
    src="<?= (strpos($_SERVER['PHP_SELF'] ?? '', '/admin/') !== false) ? '../assets/js/accessibility.js' : 'assets/js/accessibility.js' ?>"
    defer
></script>
