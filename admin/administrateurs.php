<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("includes/auth.php");
require_once("../config/database.php");

$erreur = "";
$succes = "";

$nom = "";
$email = "";


/*
|--------------------------------------------------------------------------
| AJOUTER UN ADMINISTRATEUR
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom = trim($_POST["nom"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $motDePasse = $_POST["mot_de_passe"] ?? "";
    $confirmation = $_POST["confirmation"] ?? "";


    if ($nom === "" || $email === "" || $motDePasse === "") {

        $erreur = "Veuillez remplir tous les champs obligatoires.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $erreur = "L'adresse email n'est pas valide.";

    } elseif (strlen($motDePasse) < 6) {

        $erreur = "Le mot de passe doit contenir au moins 6 caractères.";

    } elseif ($motDePasse !== $confirmation) {

        $erreur = "Les mots de passe ne correspondent pas.";

    } else {

        $verification = $pdo->prepare("
            SELECT id
            FROM utilisateurs
            WHERE email = ?
        ");

        $verification->execute([$email]);

        if ($verification->fetch()) {

            $erreur = "Cette adresse email est déjà utilisée.";

        } else {

            $requete = $pdo->prepare("
                INSERT INTO utilisateurs
                (nom, email, mot_de_passe, role)
                VALUES (?, ?, ?, 'admin')
            ");

            $requete->execute([
                $nom,
                $email,
                password_hash($motDePasse, PASSWORD_DEFAULT)
            ]);

            $succes = "L'administrateur « " . $nom . " » a été ajouté avec succès.";

            $nom = "";
            $email = "";
        }
    }
}


/*
|--------------------------------------------------------------------------
| RÉCUPÉRER LES ADMINISTRATEURS
|--------------------------------------------------------------------------
*/

$requete = $pdo->query("
    SELECT id, nom, email, date_creation
    FROM utilisateurs
    WHERE role = 'admin'
    ORDER BY id DESC
");

$administrateurs = $requete->fetchAll(PDO::FETCH_ASSOC);

$totalAdministrateurs = count($administrateurs);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Administrateurs - NPM Admin</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- CSS NPM -->
    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body>


<?php require_once("includes/navbar.php"); ?>

<?php require_once("includes/sidebar.php"); ?>


<main class="admin-content">

    <div class="container-fluid py-5 px-4">


        <!-- TITRE -->

        <div class="mb-4">

            <h1 class="fw-bold">
                <i class="bi bi-shield-lock me-2"></i>
                Administrateurs
            </h1>

            <p class="text-muted mb-0">
                Gérez les comptes ayant accès à l'espace d'administration.
            </p>

        </div>


        <?php if ($erreur !== ""): ?>

            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($erreur) ?>
            </div>

        <?php endif; ?>


        <?php if ($succes !== ""): ?>

            <div class="alert alert-success" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?= htmlspecialchars($succes) ?>
            </div>

        <?php endif; ?>


        <!-- MODAL : AJOUTER UN ADMINISTRATEUR -->

        <div
            class="modal fade"
            id="modalAjouterAdministrateur"
            tabindex="-1"
            aria-labelledby="modalAjouterAdministrateurLabel"
            aria-hidden="true"
        >

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content">

                    <div class="modal-header bg-dark text-white">

                        <h5 class="modal-title" id="modalAjouterAdministrateurLabel">
                            <i class="bi bi-person-plus me-2"></i>
                            Ajouter un administrateur
                        </h5>

                        <button
                            type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>

                    </div>


                    <div class="modal-body p-4">

                        <form method="POST">


                            <!-- NOM -->

                            <div class="mb-3">

                                <label class="form-label fw-semibold">
                                    Nom complet
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="nom"
                                    class="form-control"
                                    value="<?= htmlspecialchars($nom) ?>"
                                    placeholder="Ex : Fatou Ndiaye"
                                    required
                                >

                            </div>


                            <!-- EMAIL -->

                            <div class="mb-3">

                                <label class="form-label fw-semibold">
                                    Adresse email
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    value="<?= htmlspecialchars($email) ?>"
                                    placeholder="admin@npm.com"
                                    required
                                >

                            </div>


                            <!-- MOT DE PASSE -->

                            <div class="mb-3">

                                <label class="form-label fw-semibold">
                                    Mot de passe
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="password"
                                    name="mot_de_passe"
                                    class="form-control"
                                    placeholder="6 caractères minimum"
                                    required
                                >

                            </div>


                            <!-- CONFIRMATION -->

                            <div class="mb-4">

                                <label class="form-label fw-semibold">
                                    Confirmer le mot de passe
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="password"
                                    name="confirmation"
                                    class="form-control"
                                    required
                                >

                            </div>


                            <!-- BOUTON -->

                            <button
                                type="submit"
                                class="btn btn-warning w-100 fw-semibold"
                            >

                                <i class="bi bi-plus-circle me-1"></i>

                                Ajouter l'administrateur

                            </button>


                        </form>

                    </div>

                </div>

            </div>

        </div>


        <!-- LISTE DES ADMINISTRATEURS -->

        <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white py-3">

                        <div class="d-flex justify-content-between align-items-center">

                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-people me-2"></i>
                                Liste des administrateurs
                            </h5>

                            <div class="d-flex align-items-center gap-2">

                                <span class="badge bg-dark">
                                    <?= $totalAdministrateurs ?> admin(s)
                                </span>

                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalAjouterAdministrateur"
                                >
                                    <i class="bi bi-person-plus me-1"></i>
                                    Ajouter un administrateur
                                </button>

                            </div>

                        </div>

                    </div>


                    <div class="card-body p-0">

                        <?php if (empty($administrateurs)): ?>

                            <div class="text-center p-5">

                                <i class="bi bi-shield-x fs-1 text-muted"></i>

                                <p class="text-muted mt-3">
                                    Aucun administrateur pour le moment.
                                </p>

                            </div>

                        <?php else: ?>

                            <div class="table-responsive">

                                <table class="table table-hover align-middle mb-0">

                                    <thead class="table-dark">

                                        <tr>
                                            <th>Nom</th>
                                            <th>Email</th>
                                            <th>Date d'ajout</th>
                                            <th class="text-center">Actions</th>
                                        </tr>

                                    </thead>


                                    <tbody>

                                        <?php foreach ($administrateurs as $administrateur): ?>

                                            <tr>

                                                <td>

                                                    <div class="d-flex align-items-center gap-2">

                                                        <i class="bi bi-shield-lock-fill text-warning"></i>

                                                        <strong>
                                                            <?= htmlspecialchars($administrateur["nom"]) ?>
                                                        </strong>

                                                        <?php if (
                                                            isset($_SESSION["id"])
                                                            && (int) $_SESSION["id"] === (int) $administrateur["id"]
                                                        ): ?>

                                                            <span class="badge bg-secondary">Vous</span>

                                                        <?php endif; ?>

                                                    </div>

                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($administrateur["email"]) ?>
                                                </td>

                                                <td>

                                                    <?php if (!empty($administrateur["date_creation"])): ?>

                                                        <?= date(
                                                            "d/m/Y",
                                                            strtotime($administrateur["date_creation"])
                                                        ) ?>

                                                    <?php else: ?>

                                                        —

                                                    <?php endif; ?>

                                                </td>


                                                <!-- ACTIONS -->

                                                <td class="text-center">

                                                    <a
                                                        href="details-administrateur.php?id=<?= $administrateur["id"] ?>"
                                                        class="btn btn-sm btn-outline-dark me-1"
                                                        title="Voir les détails"
                                                    >
                                                        <i class="bi bi-eye"></i>
                                                    </a>

                                                    <?php if (
                                                        !isset($_SESSION["id"])
                                                        || (int) $_SESSION["id"] !== (int) $administrateur["id"]
                                                    ): ?>

                                                        <form
                                                            action="supprimer-administrateur.php"
                                                            method="POST"
                                                            class="d-inline"
                                                            onsubmit="return confirm('Supprimer cet administrateur ?');"
                                                        >

                                                            <input
                                                                type="hidden"
                                                                name="csrf_token"
                                                                value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                                            >

                                                            <input
                                                                type="hidden"
                                                                name="id"
                                                                value="<?= $administrateur['id'] ?>"
                                                            >

                                                            <button
                                                                type="submit"
                                                                class="btn btn-sm btn-outline-danger"
                                                                title="Supprimer"
                                                            >
                                                                <i class="bi bi-trash"></i>
                                                            </button>

                                                        </form>

                                                    <?php endif; ?>

                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    </tbody>

                                </table>

                            </div>

                        <?php endif; ?>

                    </div>

        </div>


    </div>

</main>


<!-- Bootstrap JS -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<?php if ($erreur !== ""): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    var modalAdmin = new bootstrap.Modal(
        document.getElementById("modalAjouterAdministrateur")
    );
    modalAdmin.show();
});
</script>
<?php endif; ?>


</body>

</html>
