<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("includes/auth.php");
require_once("../config/database.php");

$erreur = "";
$succes = "";

$nom = "";
$telephone = "";
$email = "";


/*
|--------------------------------------------------------------------------
| AJOUTER UN EMPLOYÉ
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom = trim($_POST["nom"] ?? "");
    $telephone = trim($_POST["telephone"] ?? "");
    $email = trim($_POST["email"] ?? "");


    if ($nom === "") {

        $erreur = "Le nom de l'employé est obligatoire.";

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

                $erreur = "Cette adresse email est déjà utilisée.";
            }
        }


        if ($erreur === "") {

            // L'employé n'a pas forcément besoin de se connecter :
            // on génère un mot de passe aléatoire par défaut.
            $motDePasseHash = password_hash(
                bin2hex(random_bytes(8)),
                PASSWORD_DEFAULT
            );

            $requete = $pdo->prepare("
                INSERT INTO utilisateurs
                (nom, email, telephone, mot_de_passe, role)
                VALUES (?, ?, ?, ?, 'employe')
            ");

            $requete->execute([
                $nom,
                $email !== "" ? $email : null,
                $telephone !== "" ? $telephone : null,
                $motDePasseHash
            ]);

            $succes = "L'employé « " . $nom . " » a été ajouté avec succès.";

            $nom = "";
            $telephone = "";
            $email = "";
        }
    }
}




/*
|--------------------------------------------------------------------------
| RÉCUPÉRER LES EMPLOYÉS
|--------------------------------------------------------------------------
*/

$requete = $pdo->query("
    SELECT id, nom, email, telephone, date_creation
    FROM utilisateurs
    WHERE role = 'employe'
    ORDER BY id DESC
");

$employes = $requete->fetchAll(PDO::FETCH_ASSOC);

$totalEmployes = count($employes);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Employés - NPM Admin</title>

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
                <i class="bi bi-person-badge me-2"></i>
                Employés
            </h1>

            <p class="text-muted mb-0">
                Gérez le personnel de la quincaillerie NPM.
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


        <!-- MODAL : AJOUTER UN EMPLOYÉ -->

        <div
            class="modal fade"
            id="modalAjouterEmploye"
            tabindex="-1"
            aria-labelledby="modalAjouterEmployeLabel"
            aria-hidden="true"
        >

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content">

                    <div class="modal-header bg-dark text-white">

                        <h5 class="modal-title" id="modalAjouterEmployeLabel">
                            <i class="bi bi-person-plus me-2"></i>
                            Ajouter un employé
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
                                    placeholder="Ex : Moussa Sarr"
                                    required
                                >

                            </div>


                            <!-- TÉLÉPHONE -->

                            <div class="mb-3">

                                <label class="form-label fw-semibold">
                                    Téléphone
                                </label>

                                <input
                                    type="tel"
                                    name="telephone"
                                    class="form-control"
                                    value="<?= htmlspecialchars($telephone) ?>"
                                    placeholder="Ex : 77 000 00 00"
                                >

                                <small class="text-muted">
                                    Facultatif.
                                </small>

                            </div>


                            <!-- EMAIL -->

                            <div class="mb-4">

                                <label class="form-label fw-semibold">
                                    Email
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    value="<?= htmlspecialchars($email) ?>"
                                    placeholder="Facultatif"
                                >

                                <small class="text-muted">
                                    Facultatif.
                                </small>

                            </div>


                            <!-- BOUTON -->

                            <button
                                type="submit"
                                class="btn btn-warning w-100 fw-semibold"
                            >

                                <i class="bi bi-plus-circle me-1"></i>

                                Ajouter l'employé

                            </button>


                        </form>

                    </div>

                </div>

            </div>

        </div>


        <!-- LISTE DES EMPLOYÉS -->

        <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white py-3">

                        <div class="d-flex justify-content-between align-items-center">

                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-people me-2"></i>
                                Liste des employés
                            </h5>

                            <div class="d-flex align-items-center gap-2">

                                <span class="badge bg-dark">
                                    <?= $totalEmployes ?> employé(s)
                                </span>

                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalAjouterEmploye"
                                >
                                    <i class="bi bi-person-plus me-1"></i>
                                    Ajouter un employé
                                </button>

                            </div>

                        </div>

                    </div>


                    <div class="card-body p-0">

                        <?php if (empty($employes)): ?>

                            <div class="text-center p-5">

                                <i class="bi bi-person-x fs-1 text-muted"></i>

                                <p class="text-muted mt-3">
                                    Aucun employé pour le moment.
                                </p>

                            </div>

                        <?php else: ?>

                            <div class="table-responsive">

                                <table class="table table-hover align-middle mb-0">

                                    <thead class="table-dark">

                                        <tr>
                                            <th>Nom</th>
                                            <th>Téléphone</th>
                                            <th>Email</th>
                                            <th>Date d'ajout</th>
                                            <th class="text-center">Actions</th>
                                        </tr>

                                    </thead>


                                    <tbody>

                                        <?php foreach ($employes as $employe): ?>

                                            <tr>

                                                <td>

                                                    <strong>
                                                        <?= htmlspecialchars($employe["nom"]) ?>
                                                    </strong>

                                                </td>

                                                <td>

                                                    <?php if (!empty($employe["telephone"])): ?>

                                                        <i class="bi bi-telephone me-1"></i>
                                                        <?= htmlspecialchars($employe["telephone"]) ?>

                                                    <?php else: ?>

                                                        <span class="text-muted">—</span>

                                                    <?php endif; ?>

                                                </td>

                                                <td>

                                                    <?php if (!empty($employe["email"])): ?>

                                                        <?= htmlspecialchars($employe["email"]) ?>

                                                    <?php else: ?>

                                                        <span class="text-muted">—</span>

                                                    <?php endif; ?>

                                                </td>

                                                <td>

                                                    <?php if (!empty($employe["date_creation"])): ?>

                                                        <?= date(
                                                            "d/m/Y",
                                                            strtotime($employe["date_creation"])
                                                        ) ?>

                                                    <?php else: ?>

                                                        —

                                                    <?php endif; ?>

                                                </td>


                                                <!-- ACTIONS -->

                                                <td class="text-center">

                                                    <a
                                                        href="details-employe.php?id=<?= $employe["id"] ?>"
                                                        class="btn btn-sm btn-outline-dark me-1"
                                                        title="Voir les détails"
                                                    >
                                                        <i class="bi bi-eye"></i>
                                                    </a>

                                                    <form
                                                        action="supprimer-employe.php"
                                                        method="POST"
                                                        class="d-inline"
                                                        onsubmit="return confirm('Supprimer cet employé ?');"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="csrf_token"
                                                            value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="id"
                                                            value="<?= $employe['id'] ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Supprimer"
                                                        >
                                                            <i class="bi bi-trash"></i>
                                                        </button>

                                                    </form>

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
    var modalEmploye = new bootstrap.Modal(
        document.getElementById("modalAjouterEmploye")
    );
    modalEmploye.show();
});
</script>
<?php endif; ?>


</body>

</html>
