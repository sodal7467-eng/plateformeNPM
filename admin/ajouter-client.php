<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("includes/auth.php");
require_once("../config/database.php");

$erreur = "";
$succes = "";
$nouvelId = null;

$nom = "";
$telephone = "";
$email = "";


/*
|--------------------------------------------------------------------------
| Ajouter un client boutique (sans compte en ligne)
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verifier();


    $nom = trim($_POST["nom"] ?? "");
    $telephone = trim($_POST["telephone"] ?? "");
    $email = trim($_POST["email"] ?? "");


    if ($nom === "") {

        $erreur = "Le nom du client est obligatoire.";

    } elseif ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $erreur = "L'adresse email n'est pas valide.";

    } else {

        if ($email !== "") {

            $verification = $pdo->prepare("
                SELECT id
                FROM utilisateurs
                WHERE email = ?
            ");

            $verification->execute([$email]);

            if ($verification->fetch()) {

                $erreur = "Cette adresse email est déjà utilisée par un autre client.";
            }
        }


        if ($erreur === "") {

            // Ce client n'a pas de compte en ligne : on génère
            // un mot de passe aléatoire qu'il n'utilisera jamais.
            $motDePasseHash = password_hash(
                bin2hex(random_bytes(8)),
                PASSWORD_DEFAULT
            );

            $requete = $pdo->prepare("
                INSERT INTO utilisateurs
                (nom, email, telephone, mot_de_passe, role, type_client)
                VALUES (?, ?, ?, ?, 'client', 'boutique')
            ");

            $requete->execute([
                $nom,
                $email !== "" ? $email : null,
                $telephone !== "" ? $telephone : null,
                $motDePasseHash
            ]);

            $nouvelId = (int) $pdo->lastInsertId();

            $succes = "Le client « " . $nom . " » a été ajouté avec succès.";

            $nom = "";
            $telephone = "";
            $email = "";
        }
    }
}


?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ajouter un client - NPM Admin</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body class="bg-light">


<?php require_once("includes/navbar.php"); ?>

<?php require_once("includes/sidebar.php"); ?>


<main class="admin-content">

    <div class="container-fluid py-4">


        <!-- En-tête -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1 class="fw-bold mb-1">
                    Ajouter un client
                </h1>

                <p class="text-muted mb-0">
                    Pour un client de la boutique physique qui n'a pas
                    (encore) de compte sur le site.
                </p>

            </div>


            <a href="clients.php" class="btn btn-dark">
                <i class="bi bi-arrow-left me-2"></i>
                Retour aux clients
            </a>

        </div>


        <!-- Messages -->

        <?php if ($erreur !== ""): ?>

            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($erreur) ?>
            </div>

        <?php endif; ?>


        <?php if ($succes !== ""): ?>

            <div class="alert alert-success">

                <i class="bi bi-check-circle me-2"></i>

                <?= htmlspecialchars($succes) ?>

                <div class="mt-3 d-flex gap-2">

                    <a href="clients.php" class="btn btn-sm btn-dark">
                        Voir la liste des clients
                    </a>

                    <?php if ($nouvelId): ?>

                        <a
                            href="nouvelle-vente.php?utilisateur_id=<?= $nouvelId ?>"
                            class="btn btn-sm btn-warning"
                        >
                            <i class="bi bi-cart-plus me-1"></i>
                            Enregistrer une vente pour ce client
                        </a>

                    <?php endif; ?>

                </div>

            </div>

        <?php endif; ?>


        <!-- Formulaire -->

        <div class="row justify-content-center">

            <div class="col-xl-6 col-lg-8">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white py-3">

                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-person-plus me-2"></i>
                            Informations du client
                        </h5>

                    </div>


                    <div class="card-body p-4">

                        <form method="POST">
                            <?= csrf_champ() ?>


                            <!-- Nom -->

                            <div class="mb-3">

                                <label class="form-label fw-semibold">
                                    Nom complet
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="nom"
                                    class="form-control"
                                    placeholder="Ex : Awa Diop"
                                    value="<?= htmlspecialchars($nom) ?>"
                                    required
                                >

                            </div>


                            <!-- Téléphone -->

                            <div class="mb-3">

                                <label class="form-label fw-semibold">
                                    Téléphone
                                </label>

                                <input
                                    type="tel"
                                    name="telephone"
                                    class="form-control"
                                    placeholder="Ex : 77 000 00 00"
                                    value="<?= htmlspecialchars($telephone) ?>"
                                >

                                <small class="text-muted">
                                    Facultatif — laisse vide si le client ne le donne pas.
                                </small>

                            </div>


                            <!-- Email -->

                            <div class="mb-4">

                                <label class="form-label fw-semibold">
                                    Email
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="Facultatif"
                                    value="<?= htmlspecialchars($email) ?>"
                                >

                                <small class="text-muted">
                                    Facultatif pour un client de la boutique.
                                </small>

                            </div>


                            <!-- Boutons -->

                            <div class="d-flex justify-content-end gap-2 pt-3 border-top">

                                <a href="clients.php" class="btn btn-outline-secondary">
                                    Annuler
                                </a>

                                <button type="submit" class="btn btn-warning px-4">
                                    <i class="bi bi-plus-lg me-2"></i>
                                    Ajouter le client
                                </button>

                            </div>


                        </form>

                    </div>

                </div>

            </div>

        </div>


    </div>

</main>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>
