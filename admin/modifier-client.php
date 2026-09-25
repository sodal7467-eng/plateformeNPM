<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("includes/auth.php");
require_once("../config/database.php");
require_once("../includes/log_admin.php");


/*
|--------------------------------------------------------------------------
| VERIFIER L'ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: clients.php");
    exit;
}

$id = (int) $_GET["id"];


/*
|--------------------------------------------------------------------------
| CHARGER LE CLIENT
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare("
    SELECT id, nom, email, telephone, role, type_client
    FROM utilisateurs
    WHERE id = ? AND role = 'client'
    LIMIT 1
");
$requete->execute([$id]);
$client = $requete->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    header("Location: clients.php");
    exit;
}


$erreur = "";
$succes = "";


/*
|--------------------------------------------------------------------------
| TRAITEMENT DU FORMULAIRE
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["csrf_token"])
        || !hash_equals($_SESSION["csrf_token"] ?? "", $_POST["csrf_token"])
    ) {
        die("Erreur de securite : requete invalide.");
    }

    $nom       = trim($_POST["nom"] ?? "");
    $email     = trim($_POST["email"] ?? "");
    $telephone = trim($_POST["telephone"] ?? "");

    // Nettoyer telephone (garder seulement chiffres + le eventuel +)
    if ($telephone !== "") {
        $telephone = preg_replace('/[^0-9+]/', '', $telephone);
    }

    // Validation
    if ($nom === "") {
        $erreur = "Le nom est obligatoire.";
    } elseif ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "L'adresse email n'est pas valide.";
    } elseif ($email === "" && $telephone === "") {
        $erreur = "Merci de renseigner au moins un email ou un telephone.";
    } else {

        // Verifier unicite de l'email (s'il change)
        if ($email !== "" && $email !== ($client["email"] ?? "")) {
            $check = $pdo->prepare("
                SELECT id FROM utilisateurs
                WHERE email = ? AND id <> ?
                LIMIT 1
            ");
            $check->execute([$email, $id]);
            if ($check->fetch()) {
                $erreur = "Cet email est deja utilise par un autre compte.";
            }
        }

        // Verifier unicite du telephone (s'il change et n'est pas vide)
        if ($erreur === "" && $telephone !== "" && $telephone !== ($client["telephone"] ?? "")) {
            $check = $pdo->prepare("
                SELECT id FROM utilisateurs
                WHERE telephone = ? AND id <> ?
                LIMIT 1
            ");
            $check->execute([$telephone, $id]);
            if ($check->fetch()) {
                $erreur = "Ce numero de telephone est deja utilise par un autre compte.";
            }
        }

        // Enregistrer
        if ($erreur === "") {

            $req = $pdo->prepare("
                UPDATE utilisateurs
                SET nom = ?, email = ?, telephone = ?
                WHERE id = ?
            ");
            $req->execute([
                $nom,
                $email !== "" ? $email : null,
                $telephone !== "" ? $telephone : null,
                $id
            ]);

            // Detail des changements pour le journal
            $changements = [];
            if ($nom !== ($client["nom"] ?? "")) {
                $changements[] = "nom: '" . ($client["nom"] ?? "") . "' -> '{$nom}'";
            }
            if (($email ?: null) !== ($client["email"] ?? null)) {
                $changements[] = "email: '" . ($client["email"] ?? "") . "' -> '{$email}'";
            }
            if (($telephone ?: null) !== ($client["telephone"] ?? null)) {
                $changements[] = "telephone: '" . ($client["telephone"] ?? "") . "' -> '{$telephone}'";
            }

            logAdmin(
                $pdo,
                "client.modifier",
                "utilisateur",
                $id,
                "Modification du profil client. " . implode(" | ", $changements)
            );

            $succes = "Profil client mis a jour.";

            // Recharger les infos affichees
            $client["nom"] = $nom;
            $client["email"] = $email !== "" ? $email : null;
            $client["telephone"] = $telephone !== "" ? $telephone : null;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier client <?= htmlspecialchars($client["nom"]) ?> - NPM Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php require_once("includes/navbar.php"); ?>
<?php require_once("includes/sidebar.php"); ?>

<main class="admin-content">
    <div class="container-fluid py-5 px-4">

        <a href="details-client.php?id=<?= (int) $client["id"] ?>" class="btn btn-outline-secondary btn-sm mb-3">
            <i class="bi bi-arrow-left me-1"></i> Retour au client
        </a>

        <h1 class="fw-bold mb-2">
            <i class="bi bi-person-gear me-2"></i>
            Modifier le client
        </h1>
        <p class="text-muted mb-4">Mettez à jour les informations personnelles du client.</p>

        <?php if ($succes): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($succes) ?>
            </div>
        <?php endif; ?>

        <?php if ($erreur): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($erreur) ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Informations du client</strong>
            </div>
            <div class="card-body">

                <form method="POST" action="modifier-client.php?id=<?= (int) $client["id"] ?>">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "") ?>"
                    >

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nom complet</label>
                        <input
                            type="text"
                            name="nom"
                            class="form-control"
                            value="<?= htmlspecialchars($client["nom"] ?? "") ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Email
                            <span class="text-muted fw-normal">(facultatif si téléphone renseigné)</span>
                        </label>
                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="<?= htmlspecialchars($client["email"] ?? "") ?>"
                            placeholder="exemple@email.com"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Téléphone
                            <span class="text-muted fw-normal">(indispensable pour WhatsApp)</span>
                        </label>
                        <input
                            type="tel"
                            name="telephone"
                            class="form-control"
                            value="<?= htmlspecialchars($client["telephone"] ?? "") ?>"
                            placeholder="77 123 45 67"
                        >
                        <div class="form-text">
                            Formats acceptés : <code>77 123 45 67</code>, <code>+221 77 123 45 67</code>, <code>0771234567</code>.
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="details-client.php?id=<?= (int) $client["id"] ?>" class="btn btn-outline-secondary">
                            Annuler
                        </a>
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-save me-1"></i>
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
