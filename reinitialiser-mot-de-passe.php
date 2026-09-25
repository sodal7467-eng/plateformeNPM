<?php

session_start();

require_once "config/database.php";
require_once "includes/parametres.php";


$erreur = "";
$succes = "";
$tokenValide = false;
$utilisateurId = null;


// Récupérer et valider le token (GET pour affichage, POST à la soumission)
$token = $_GET["token"] ?? $_POST["token"] ?? "";
$token = trim($token);

if ($token === "") {

    $erreur = "Lien invalide ou expiré.";

} else {

    $tokenHash = hash("sha256", $token);

    $requete = $pdo->prepare("
        SELECT id, utilisateur_id, date_expiration, utilise
        FROM resets_mot_de_passe
        WHERE token_hash = ?
        LIMIT 1
    ");
    $requete->execute([$tokenHash]);
    $reset = $requete->fetch(PDO::FETCH_ASSOC);

    if (
        !$reset
        || (int) $reset["utilise"] === 1
        || strtotime($reset["date_expiration"]) < time()
    ) {
        $erreur = "Ce lien est invalide ou expiré. Veuillez recommencer la procédure.";
    } else {
        $tokenValide = true;
        $utilisateurId = (int) $reset["utilisateur_id"];
        $resetId = (int) $reset["id"];
    }
}


// Soumission du nouveau mot de passe
if ($tokenValide && $_SERVER["REQUEST_METHOD"] === "POST") {

    $motDePasse = $_POST["password"] ?? "";
    $confirmation = $_POST["confirmation"] ?? "";

    if (strlen($motDePasse) < 6) {

        $erreur = "Le mot de passe doit contenir au moins 6 caractères.";

    } elseif ($motDePasse !== $confirmation) {

        $erreur = "Les deux mots de passe ne correspondent pas.";

    } else {

        $pdo->beginTransaction();

        try {

            $hash = password_hash($motDePasse, PASSWORD_DEFAULT);

            $pdo->prepare("
                UPDATE utilisateurs
                SET mot_de_passe = ?
                WHERE id = ?
            ")->execute([$hash, $utilisateurId]);

            $pdo->prepare("
                UPDATE resets_mot_de_passe
                SET utilise = 1
                WHERE id = ?
            ")->execute([$resetId]);

            $pdo->commit();

            $succes = "Mot de passe mis à jour avec succès. Vous pouvez maintenant vous connecter.";
            $tokenValide = false;

        } catch (Throwable $e) {
            $pdo->rollBack();
            error_log("Reset mdp : " . $e->getMessage());
            $erreur = "Une erreur est survenue. Veuillez réessayer.";
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
    <title>Nouveau mot de passe - <?= htmlspecialchars($nomBoutique) ?></title>
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
                            <i class="bi bi-shield-lock"></i>
                        </div>

                        <div class="text-center mb-4">
                            <span class="text-orange fw-bold">SÉCURITÉ</span>
                            <h1 class="fw-bold mt-2">Nouveau mot de passe</h1>
                        </div>

                        <?php if (!empty($succes)): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="bi bi-check-circle me-2"></i>
                                <?= htmlspecialchars($succes) ?>
                            </div>
                            <div class="text-center">
                                <a href="connexion.php" class="btn btn-dark">Se connecter</a>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($erreur)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                <?= htmlspecialchars($erreur) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($tokenValide): ?>
                        <form method="POST" action="reinitialiser-mot-de-passe.php">

                            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold">
                                    Nouveau mot de passe
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        class="form-control"
                                        placeholder="Au moins 6 caractères"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="confirmation" class="form-label fw-bold">
                                    Confirmer le mot de passe
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input
                                        type="password"
                                        name="confirmation"
                                        id="confirmation"
                                        class="form-control"
                                        placeholder="Ressaisir le mot de passe"
                                        required
                                    >
                                </div>
                            </div>

                            <button type="submit" class="btn btn-dark btn-lg w-100">
                                <i class="bi bi-check2 me-1"></i>
                                Enregistrer
                            </button>
                        </form>
                        <?php elseif (empty($succes)): ?>
                            <div class="text-center">
                                <a href="mot-de-passe-oublie.php" class="btn btn-outline-dark">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Demander un nouveau lien
                                </a>
                            </div>
                        <?php endif; ?>

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
