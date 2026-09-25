<?php

session_start();

require_once "config/database.php";
require_once "config/env.php";
require_once "includes/parametres.php";


$erreur = "";
$succes = "";
$lienGenere = "";
$lienWhatsapp = "";
$telephoneCible = "";
$nomCible = "";


/*
|--------------------------------------------------------------------------
| Formater un numéro de téléphone pour WhatsApp (format international 221…)
|--------------------------------------------------------------------------
*/

function formaterWhatsapp(?string $numero): ?string
{
    if (empty($numero)) {
        return null;
    }
    $chiffres = preg_replace('/\D/', '', $numero);
    if ($chiffres === '') {
        return null;
    }
    // Numéro sénégalais à 9 chiffres → préfixer 221
    if (strlen($chiffres) === 9) {
        $chiffres = '221' . $chiffres;
    }
    // 0XXXXXXXXX → 221XXXXXXXXX
    if (strlen($chiffres) === 10 && $chiffres[0] === '0') {
        $chiffres = '221' . substr($chiffres, 1);
    }
    return $chiffres;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $identifiant = trim($_POST["identifiant"] ?? "");

    if ($identifiant === "") {

        $erreur = "Merci d'indiquer votre numéro de téléphone ou votre email.";

    } else {

        // Chercher l'utilisateur (téléphone ou email)
        $requete = $pdo->prepare("
            SELECT id, nom, telephone, email
            FROM utilisateurs
            WHERE telephone = :id OR email = :id
            LIMIT 1
        ");
        $requete->execute(["id" => $identifiant]);
        $utilisateur = $requete->fetch(PDO::FETCH_ASSOC);

        if ($utilisateur) {

            // Générer un token à usage unique (30 min)
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash("sha256", $token);
            $dureeMin = (int) env("RESET_TOKEN_MINUTES", 30);
            $expiration = (new DateTime())->modify("+{$dureeMin} minutes")->format("Y-m-d H:i:s");

            // Invalider les tokens précédents non utilisés
            $pdo->prepare("
                UPDATE resets_mot_de_passe
                SET utilise = 1
                WHERE utilisateur_id = ? AND utilise = 0
            ")->execute([$utilisateur["id"]]);

            // Créer le nouveau token
            $pdo->prepare("
                INSERT INTO resets_mot_de_passe (utilisateur_id, token_hash, date_expiration)
                VALUES (?, ?, ?)
            ")->execute([$utilisateur["id"], $tokenHash, $expiration]);

            // Construire le lien
            $baseUrl = rtrim(env("APP_URL", ""), "/");
            if ($baseUrl === "") {
                $scheme = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
                $baseUrl = $scheme . "://" . ($_SERVER["HTTP_HOST"] ?? "localhost")
                    . rtrim(dirname($_SERVER["PHP_SELF"] ?? ""), "/\\");
            }

            $lienGenere = $baseUrl . "/reinitialiser-mot-de-passe.php?token=" . $token;

            // Préparer le lien WhatsApp si un numéro est disponible
            $telephoneCible = $utilisateur["telephone"] ?? "";
            $nomCible = $utilisateur["nom"] ?? "";
            $telephoneWa = formaterWhatsapp($telephoneCible);

            if ($telephoneWa) {
                $messageWa = "Bonjour " . $nomCible . ",\n\n"
                    . "Voici votre lien pour reinitialiser votre mot de passe sur "
                    . parametre($pdo, "nom_boutique", "notre boutique") . " :\n\n"
                    . $lienGenere . "\n\n"
                    . "Ce lien est valable " . $dureeMin . " minutes.";
                $lienWhatsapp = "https://wa.me/" . $telephoneWa . "?text=" . rawurlencode($messageWa);
            }

            $succes = "Un lien de réinitialisation valide pendant {$dureeMin} minutes a été généré.";

        } else {

            // Ne pas révéler si le compte existe, mais afficher un message générique
            $succes = "Si un compte existe pour cet identifiant, un lien peut être généré. "
                    . "Contactez l'administration si vous ne recevez rien.";
        }
    }
}


$nomBoutique = parametre($pdo, "nom_boutique", "Quincaillerie NPM");

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - <?= htmlspecialchars($nomBoutique) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include "includes/navbar.php"; ?>

<main>
    <section class="connexion-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-5">
                    <div class="connexion-card">

                        <div class="connexion-icon">
                            <i class="bi bi-key"></i>
                        </div>

                        <div class="text-center mb-4">
                            <span class="text-orange fw-bold">MOT DE PASSE OUBLIÉ</span>
                            <h1 class="fw-bold mt-2">Réinitialiser</h1>
                            <p class="text-muted">
                                Indiquez votre numéro de téléphone ou votre email
                                pour obtenir un lien de réinitialisation.
                            </p>
                        </div>

                        <?php if (!empty($succes)): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="bi bi-check-circle me-2"></i>
                                <?= htmlspecialchars($succes) ?>
                            </div>

                            <?php if (!empty($lienGenere)): ?>

                                <?php if (!empty($lienWhatsapp)): ?>
                                    <div class="d-grid gap-2 mb-3">
                                        <a
                                            href="<?= htmlspecialchars($lienWhatsapp) ?>"
                                            class="btn btn-success btn-lg"
                                            target="_blank"
                                            rel="noopener"
                                        >
                                            <i class="bi bi-whatsapp me-2"></i>
                                            Envoyer le lien par WhatsApp
                                        </a>
                                    </div>
                                    <p class="small text-muted text-center mb-3">
                                        Le lien sera envoyé au <strong><?= htmlspecialchars($telephoneCible) ?></strong>.
                                    </p>
                                <?php endif; ?>

                                <div class="alert alert-light border small" role="alert">
                                    <strong>Ou copiez le lien :</strong>
                                    <div class="input-group input-group-sm mt-2">
                                        <input
                                            type="text"
                                            id="lienReset"
                                            class="form-control"
                                            value="<?= htmlspecialchars($lienGenere) ?>"
                                            readonly
                                            onclick="this.select();"
                                        >
                                        <button
                                            type="button"
                                            class="btn btn-outline-dark"
                                            onclick="navigator.clipboard.writeText(document.getElementById('lienReset').value); this.innerHTML='<i class=\'bi bi-check2\'></i> Copié'; setTimeout(()=>this.innerHTML='<i class=\'bi bi-clipboard\'></i> Copier', 1500);"
                                        >
                                            <i class="bi bi-clipboard"></i> Copier
                                        </button>
                                    </div>
                                    <div class="form-text mt-2">
                                        Valable <?= (int) env("RESET_TOKEN_MINUTES", 30) ?> minutes.
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (!empty($erreur)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                <?= htmlspecialchars($erreur) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="mot-de-passe-oublie.php">

                            <div class="mb-3">
                                <label for="identifiant" class="form-label fw-bold">
                                    Numéro de téléphone ou email
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input
                                        type="text"
                                        name="identifiant"
                                        id="identifiant"
                                        class="form-control"
                                        placeholder="Ex : 77 123 45 67 ou vous@exemple.com"
                                        value="<?= htmlspecialchars($_POST["identifiant"] ?? "") ?>"
                                        required
                                    >
                                </div>
                            </div>

                            <button type="submit" class="btn btn-dark btn-lg w-100">
                                <i class="bi bi-send me-1"></i>
                                Générer un lien
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <a href="connexion.php" class="text-orange fw-bold text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i>
                                Retour à la connexion
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include "includes/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
