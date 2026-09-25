<?php
/*
|--------------------------------------------------------------------------
| Assistant d'installation
|--------------------------------------------------------------------------
| À lancer une seule fois après avoir déposé les fichiers sur le serveur.
| Étapes :
|   1. Vérifier les prérequis PHP
|   2. Configurer .env (identifiants base de données)
|   3. Importer le schéma SQL
|   4. Créer le premier administrateur
|   5. Personnaliser la boutique (nom, coordonnées)
|   6. Supprimer ce fichier (sécurité)
|
| Une fois terminé, l'assistant crée un fichier .installed pour se
| bloquer, puis propose de supprimer installation.php.
*/

session_start();

$racine = __DIR__;
$envPath = $racine . "/.env";
$installedFlag = $racine . "/.installed";
$schemaPath = $racine . "/database/schema.sql";


// -----------------------------------------------------------------
// 0) SÉCURITÉ : bloquer si déjà installé
// -----------------------------------------------------------------
if (file_exists($installedFlag) && !isset($_GET["force"])) {
    http_response_code(403);
    die(pageBloquee());
}


// -----------------------------------------------------------------
// Étape en cours
// -----------------------------------------------------------------
$etape = (int) ($_GET["etape"] ?? $_POST["etape"] ?? 1);
$erreur = "";
$succes = "";


// =================================================================
// Traitement POST
// =================================================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    switch ($etape) {

        // ---- Étape 2 : Enregistrer .env ----
        case 2:
            $donnees = [
                "DB_HOST"     => trim($_POST["db_host"] ?? ""),
                "DB_NAME"     => trim($_POST["db_name"] ?? ""),
                "DB_USER"     => trim($_POST["db_user"] ?? ""),
                "DB_PASSWORD" => (string) ($_POST["db_password"] ?? ""),
                "APP_URL"     => trim($_POST["app_url"] ?? ""),
                "APP_ENV"     => "production",
                "APP_DEBUG"   => "false",
                "LOGIN_MAX_ATTEMPTS"     => "5",
                "LOGIN_LOCKOUT_MINUTES"  => "15",
                "RESET_TOKEN_MINUTES"    => "30",
            ];

            if ($donnees["DB_HOST"] === "" || $donnees["DB_NAME"] === "" || $donnees["DB_USER"] === "") {
                $erreur = "Hôte, base et utilisateur sont obligatoires.";
                break;
            }

            // Tester la connexion
            try {
                $test = new PDO(
                    "mysql:host={$donnees['DB_HOST']};charset=utf8mb4",
                    $donnees["DB_USER"],
                    $donnees["DB_PASSWORD"]
                );
                $test->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Créer la base si absente
                $test->exec(
                    "CREATE DATABASE IF NOT EXISTS `{$donnees['DB_NAME']}` "
                    . "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
                );

            } catch (PDOException $e) {
                $erreur = "Connexion impossible : " . $e->getMessage();
                break;
            }

            // Écrire .env
            $contenu = "";
            foreach ($donnees as $cle => $val) {
                $contenu .= $cle . "=" . $val . "\n";
            }
            if (!@file_put_contents($envPath, $contenu)) {
                $erreur = "Impossible d'écrire le fichier .env (permissions ?).";
                break;
            }

            $succes = "Connexion réussie et fichier .env créé.";
            $etape = 3;
            break;


        // ---- Étape 3 : Importer le schéma ----
        case 3:
            require_once $racine . "/config/env.php";
            try {
                $pdo = new PDO(
                    "mysql:host=" . env("DB_HOST") . ";dbname=" . env("DB_NAME") . ";charset=utf8mb4",
                    env("DB_USER"),
                    env("DB_PASSWORD")
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                if (!file_exists($schemaPath)) {
                    $erreur = "Fichier database/schema.sql introuvable.";
                    break;
                }

                $sql = file_get_contents($schemaPath);
                $pdo->exec($sql);

                $succes = "Schéma de base importé avec succès.";
                $etape = 4;

            } catch (Throwable $e) {
                $erreur = "Erreur d'import : " . $e->getMessage();
            }
            break;


        // ---- Étape 4 : Créer le premier administrateur ----
        case 4:
            require_once $racine . "/config/env.php";
            $nom = trim($_POST["admin_nom"] ?? "");
            $email = trim($_POST["admin_email"] ?? "");
            $telephone = trim($_POST["admin_telephone"] ?? "");
            $mdp = $_POST["admin_password"] ?? "";
            $conf = $_POST["admin_confirm"] ?? "";

            if ($nom === "" || $mdp === "" || ($email === "" && $telephone === "")) {
                $erreur = "Nom, mot de passe et (email ou téléphone) sont obligatoires.";
                break;
            }
            if (strlen($mdp) < 8) {
                $erreur = "Le mot de passe doit contenir au moins 8 caractères.";
                break;
            }
            if ($mdp !== $conf) {
                $erreur = "Les mots de passe ne correspondent pas.";
                break;
            }

            try {
                $pdo = new PDO(
                    "mysql:host=" . env("DB_HOST") . ";dbname=" . env("DB_NAME") . ";charset=utf8mb4",
                    env("DB_USER"),
                    env("DB_PASSWORD")
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $stmt = $pdo->prepare("
                    INSERT INTO utilisateurs (nom, email, telephone, mot_de_passe, role)
                    VALUES (?, ?, ?, ?, 'admin')
                ");
                $stmt->execute([
                    $nom,
                    $email !== "" ? $email : null,
                    $telephone !== "" ? $telephone : null,
                    password_hash($mdp, PASSWORD_DEFAULT),
                ]);

                $succes = "Administrateur créé avec succès.";
                $etape = 5;

            } catch (Throwable $e) {
                $erreur = "Erreur : " . $e->getMessage();
            }
            break;


        // ---- Étape 5 : Personnaliser la boutique ----
        case 5:
            require_once $racine . "/config/env.php";
            require_once $racine . "/includes/parametres.php";

            try {
                $pdo = new PDO(
                    "mysql:host=" . env("DB_HOST") . ";dbname=" . env("DB_NAME") . ";charset=utf8mb4",
                    env("DB_USER"),
                    env("DB_PASSWORD")
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $champs = ["nom_boutique","slogan","adresse","telephone","email_contact","devise","logo_texte","logo_soustitre","footer_texte"];
                foreach ($champs as $c) {
                    if (isset($_POST[$c])) {
                        parametreDefinir($pdo, $c, trim($_POST[$c]));
                    }
                }

                // Créer le flag d'installation
                @file_put_contents($installedFlag, date("Y-m-d H:i:s"));

                $succes = "Installation terminée !";
                $etape = 6;

            } catch (Throwable $e) {
                $erreur = "Erreur : " . $e->getMessage();
            }
            break;


        // ---- Étape 6 : Auto-suppression ----
        case 6:
            if (isset($_POST["supprimer"])) {
                @unlink(__FILE__);
                header("Location: index.php");
                exit;
            }
            break;
    }
}


// =================================================================
// AFFICHAGE
// =================================================================

function pageBloquee(): string {
    return "<!DOCTYPE html><html lang='fr'><head><meta charset='utf-8'><title>Installation bloquée</title>"
        . "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'></head>"
        . "<body class='bg-light'><div class='container py-5'><div class='alert alert-warning'>"
        . "<h4>Installation déjà effectuée</h4>"
        . "<p>Ce site est déjà installé. Pour votre sécurité, l'assistant est désactivé.</p>"
        . "<p>Si vous voulez réinstaller, supprimez le fichier <code>.installed</code> à la racine.</p>"
        . "<a href='index.php' class='btn btn-dark'>Aller sur le site</a>"
        . "</div></div></body></html>";
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation — Étape <?= $etape ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f4f6fa; }
        .install-card { max-width: 720px; margin: 3rem auto; }
        .install-steps { display: flex; justify-content: space-between; margin-bottom: 2rem; }
        .install-steps .step {
            flex: 1; text-align: center; font-size: 12px; color: #999;
            padding: .5rem; border-bottom: 3px solid #dee2e6;
        }
        .install-steps .step.active { color: #ff8c00; border-color: #ff8c00; font-weight: bold; }
        .install-steps .step.done { color: #198754; border-color: #198754; }
    </style>
</head>
<body>

<div class="install-card">

    <div class="text-center mb-4">
        <h1 class="fw-bold"><i class="bi bi-gear-wide-connected me-2"></i>Installation</h1>
        <p class="text-muted">Assistant de mise en route de votre boutique.</p>
    </div>

    <div class="install-steps">
        <?php
        $labels = [1 => "Prérequis", 2 => "Base", 3 => "Schéma", 4 => "Admin", 5 => "Boutique", 6 => "Fini"];
        foreach ($labels as $n => $l):
            $cls = $etape === $n ? "active" : ($etape > $n ? "done" : "");
        ?>
            <div class="step <?= $cls ?>"><?= $n ?>. <?= $l ?></div>
        <?php endforeach; ?>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <?php if ($erreur): ?>
                <div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i><?= htmlspecialchars($erreur) ?></div>
            <?php endif; ?>
            <?php if ($succes): ?>
                <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($succes) ?></div>
            <?php endif; ?>


            <?php if ($etape === 1): // ------------ PRÉREQUIS ------------ ?>

                <h4>Vérification des prérequis</h4>
                <p class="text-muted">Ces éléments doivent être verts avant de continuer.</p>

                <?php
                $checks = [
                    "PHP ≥ 8.0"          => version_compare(PHP_VERSION, "8.0.0", ">="),
                    "Extension PDO"      => extension_loaded("pdo"),
                    "Extension pdo_mysql"=> extension_loaded("pdo_mysql"),
                    "Extension mbstring" => extension_loaded("mbstring"),
                    "Écriture racine"    => is_writable($racine),
                    "database/schema.sql présent" => file_exists($schemaPath),
                    "config/env.php présent"      => file_exists($racine . "/config/env.php"),
                ];
                $tousOK = true;
                foreach ($checks as $label => $ok) {
                    if (!$ok) $tousOK = false;
                    echo '<div class="d-flex justify-content-between border-bottom py-2">';
                    echo '<span>' . htmlspecialchars($label) . '</span>';
                    echo $ok
                        ? '<span class="badge bg-success"><i class="bi bi-check"></i> OK</span>'
                        : '<span class="badge bg-danger"><i class="bi bi-x"></i> Manquant</span>';
                    echo '</div>';
                }
                ?>

                <div class="d-flex justify-content-end mt-4">
                    <a href="?etape=2" class="btn btn-dark <?= $tousOK ? "" : "disabled" ?>">
                        Suivant <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>


            <?php elseif ($etape === 2): // ------------ BASE ------------ ?>

                <h4>Base de données</h4>
                <p class="text-muted">Renseignez les identifiants de connexion MySQL. La base sera créée si elle n'existe pas.</p>

                <form method="POST">
                    <input type="hidden" name="etape" value="2">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Hôte</label>
                            <input type="text" name="db_host" class="form-control" value="<?= htmlspecialchars($_POST["db_host"] ?? "localhost") ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nom de la base</label>
                            <input type="text" name="db_name" class="form-control" value="<?= htmlspecialchars($_POST["db_name"] ?? "quincaillerie") ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Utilisateur</label>
                            <input type="text" name="db_user" class="form-control" value="<?= htmlspecialchars($_POST["db_user"] ?? "root") ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mot de passe</label>
                            <input type="password" name="db_password" class="form-control" value="<?= htmlspecialchars($_POST["db_password"] ?? "") ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">URL publique du site</label>
                            <input type="url" name="app_url" class="form-control" value="<?= htmlspecialchars($_POST["app_url"] ?? "http://" . ($_SERVER["HTTP_HOST"] ?? "localhost") . rtrim(dirname($_SERVER["PHP_SELF"] ?? "/"), "/\\")) ?>" required>
                            <div class="form-text">Sans slash final. Ex : https://boutique.com</div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-dark">Tester et continuer <i class="bi bi-arrow-right ms-1"></i></button>
                    </div>
                </form>


            <?php elseif ($etape === 3): // ------------ SCHÉMA ------------ ?>

                <h4>Import du schéma</h4>
                <p class="text-muted">L'assistant va créer les 14 tables nécessaires au fonctionnement de l'application.</p>

                <form method="POST">
                    <input type="hidden" name="etape" value="3">
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-2"></i>
                        Cette étape ne supprime rien. Si les tables existent déjà, elles sont ignorées.
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-dark">Importer maintenant <i class="bi bi-arrow-right ms-1"></i></button>
                    </div>
                </form>


            <?php elseif ($etape === 4): // ------------ ADMIN ------------ ?>

                <h4>Premier administrateur</h4>
                <p class="text-muted">Ce compte pourra ensuite créer d'autres administrateurs ou employés.</p>

                <form method="POST">
                    <input type="hidden" name="etape" value="4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Nom complet</label>
                            <input type="text" name="admin_nom" class="form-control" value="<?= htmlspecialchars($_POST["admin_nom"] ?? "") ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email <span class="text-muted fw-normal">(recommandé)</span></label>
                            <input type="email" name="admin_email" class="form-control" value="<?= htmlspecialchars($_POST["admin_email"] ?? "") ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Téléphone</label>
                            <input type="tel" name="admin_telephone" class="form-control" value="<?= htmlspecialchars($_POST["admin_telephone"] ?? "") ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mot de passe</label>
                            <input type="password" name="admin_password" class="form-control" required>
                            <div class="form-text">Minimum 8 caractères.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Confirmer</label>
                            <input type="password" name="admin_confirm" class="form-control" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-dark">Créer et continuer <i class="bi bi-arrow-right ms-1"></i></button>
                    </div>
                </form>


            <?php elseif ($etape === 5): // ------------ BOUTIQUE ------------ ?>

                <h4>Personnalisation de la boutique</h4>
                <p class="text-muted">Ces informations apparaîtront dans le navbar, le footer, les factures. Tout est modifiable ensuite dans l'admin.</p>

                <form method="POST">
                    <input type="hidden" name="etape" value="5">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Nom de la boutique</label>
                            <input type="text" name="nom_boutique" class="form-control" value="<?= htmlspecialchars($_POST["nom_boutique"] ?? "Ma Quincaillerie") ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Devise</label>
                            <input type="text" name="devise" class="form-control" value="<?= htmlspecialchars($_POST["devise"] ?? "FCFA") ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Logo (texte principal)</label>
                            <input type="text" name="logo_texte" class="form-control" value="<?= htmlspecialchars($_POST["logo_texte"] ?? "MQ") ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Logo (sous-titre)</label>
                            <input type="text" name="logo_soustitre" class="form-control" value="<?= htmlspecialchars($_POST["logo_soustitre"] ?? "QUINCAILLERIE") ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Slogan</label>
                            <input type="text" name="slogan" class="form-control" value="<?= htmlspecialchars($_POST["slogan"] ?? "Tout pour reussir vos projets") ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Téléphone boutique</label>
                            <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($_POST["telephone"] ?? "") ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email boutique</label>
                            <input type="email" name="email_contact" class="form-control" value="<?= htmlspecialchars($_POST["email_contact"] ?? "") ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Adresse</label>
                            <input type="text" name="adresse" class="form-control" value="<?= htmlspecialchars($_POST["adresse"] ?? "") ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Texte du pied de page</label>
                            <textarea name="footer_texte" class="form-control" rows="2"><?= htmlspecialchars($_POST["footer_texte"] ?? "Votre partenaire pour vos besoins en outils et materiaux.") ?></textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-dark">Enregistrer et terminer <i class="bi bi-arrow-right ms-1"></i></button>
                    </div>
                </form>


            <?php elseif ($etape === 6): // ------------ FIN ------------ ?>

                <div class="text-center py-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    <h3 class="mt-3 fw-bold">Installation terminée</h3>
                    <p class="text-muted">Votre boutique est prête à fonctionner.</p>
                </div>

                <div class="alert alert-warning">
                    <strong><i class="bi bi-shield-exclamation me-2"></i>Important pour la sécurité :</strong>
                    Le fichier <code>installation.php</code> doit être supprimé. Cliquez sur le bouton ci-dessous pour le faire automatiquement.
                </div>

                <form method="POST" class="d-flex gap-2 justify-content-end">
                    <input type="hidden" name="etape" value="6">
                    <a href="index.php" class="btn btn-outline-dark">Garder pour l'instant</a>
                    <button type="submit" name="supprimer" value="1" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>
                        Supprimer et aller sur le site
                    </button>
                </form>

            <?php endif; ?>

        </div>
    </div>

    <p class="text-center text-muted small mt-3">
        Plateforme NPM — Assistant d'installation
    </p>

</div>

</body>
</html>
