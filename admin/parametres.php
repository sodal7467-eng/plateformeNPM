<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("includes/auth.php");
require_once("../config/database.php");
require_once("../includes/parametres.php");
require_once("../includes/log_admin.php");


$message = "";
$erreur = "";


/*
|--------------------------------------------------------------------------
| ENREGISTREMENT
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Vérification CSRF
    if (
        !isset($_POST["csrf_token"])
        || !hash_equals($_SESSION["csrf_token"] ?? "", $_POST["csrf_token"])
    ) {
        die("Erreur de sécurité : requête invalide.");
    }

    $champs = [
        "nom_boutique",
        "slogan",
        "adresse",
        "telephone",
        "email_contact",
        "devise",
        "logo_texte",
        "logo_soustitre",
        "footer_texte",
    ];

    try {

        $pdo->beginTransaction();

        foreach ($champs as $cle) {
            $valeur = trim($_POST[$cle] ?? "");
            parametreDefinir($pdo, $cle, $valeur);
        }

        $pdo->commit();

        logAdmin($pdo, "parametres.modifier", "parametres", null, "Mise à jour des paramètres boutique.");

        $message = "Paramètres enregistrés.";

    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log("Enregistrement paramètres : " . $e->getMessage());
        $erreur = "Impossible d'enregistrer les paramètres.";
    }
}


// Recharger les paramètres actuels (bypass le cache statique après modif)
$requete = $pdo->query("SELECT cle, valeur FROM parametres");
$valeurs = [];
foreach ($requete->fetchAll(PDO::FETCH_ASSOC) as $l) {
    $valeurs[$l["cle"]] = $l["valeur"];
}

$get = function (string $cle, string $defaut = "") use ($valeurs): string {
    return htmlspecialchars($valeurs[$cle] ?? $defaut);
};

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - NPM Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php require_once("includes/navbar.php"); ?>
<?php require_once("includes/sidebar.php"); ?>

<main class="admin-content">
    <div class="container-fluid py-5 px-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold">
                    <i class="bi bi-gear me-2"></i>
                    Paramètres de la boutique
                </h1>
                <p class="text-muted mb-0">
                    Ces informations sont utilisées partout dans le site et dans les factures.
                </p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($erreur): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <form method="POST" action="parametres.php">

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "") ?>">

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <strong>Identité de la boutique</strong>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Nom de la boutique</label>
                            <input type="text" name="nom_boutique" class="form-control" value="<?= $get("nom_boutique") ?>" required>
                            <div class="form-text">Le nom affiché dans le navbar, le footer et les factures.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Devise</label>
                            <input type="text" name="devise" class="form-control" value="<?= $get("devise", "FCFA") ?>" maxlength="10" required>
                            <div class="form-text">La monnaie (ex : FCFA, EUR, USD). 10 caractères max.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Logo (texte principal)</label>
                            <input type="text" name="logo_texte" class="form-control" value="<?= $get("logo_texte") ?>">
                            <div class="form-text">Grand texte en haut à gauche (ex : NPM).</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Logo (sous-titre)</label>
                            <input type="text" name="logo_soustitre" class="form-control" value="<?= $get("logo_soustitre") ?>">
                            <div class="form-text">Petit texte sous le logo (ex : QUINCAILLERIE).</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Slogan</label>
                            <input type="text" name="slogan" class="form-control" value="<?= $get("slogan") ?>">
                            <div class="form-text">Phrase courte qui décrit ton commerce (ex : "Votre satisfaction, notre priorité").</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <strong>Contact</strong>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Téléphone</label>
                            <input type="text" name="telephone" class="form-control" value="<?= $get("telephone") ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email de contact</label>
                            <input type="email" name="email_contact" class="form-control" value="<?= $get("email_contact") ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Adresse</label>
                            <input type="text" name="adresse" class="form-control" value="<?= $get("adresse") ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <strong>Pied de page</strong>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Texte du footer</label>
                            <textarea name="footer_texte" class="form-control" rows="3"><?= $get("footer_texte") ?></textarea>
                            <div class="form-text">Court paragraphe affiché dans le pied de page du site.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-dark btn-lg">
                    <i class="bi bi-save me-1"></i>
                    Enregistrer les paramètres
                </button>
            </div>
        </form>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
