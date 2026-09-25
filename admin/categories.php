<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("includes/auth.php");
require_once "../config/database.php";

$message = "";
$erreur = "";


/*
|--------------------------------------------------------------------------
| AJOUTER UNE CATÉGORIE
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom = trim($_POST["nom"] ?? "");
    $description = trim($_POST["description"] ?? "");


    if (empty($nom)) {

        $erreur = "Le nom de la catégorie est obligatoire.";

    } else {

        // Vérifier si la catégorie existe déjà

        $verification = $pdo->prepare("
            SELECT id
            FROM categories
            WHERE nom = ?
        ");

        $verification->execute([$nom]);


        if ($verification->fetch()) {

            $erreur = "Cette catégorie existe déjà.";

        } else {

            // Ajouter la catégorie

            $requete = $pdo->prepare("
                INSERT INTO categories (nom, description)
                VALUES (?, ?)
            ");

            $requete->execute([
                $nom,
                $description
            ]);

            $message = "Catégorie ajoutée avec succès.";

            // Vider les champs après ajout
            $nom = "";
            $description = "";
        }
    }
}


/*
|--------------------------------------------------------------------------
| RÉCUPÉRER LES CATÉGORIES
|--------------------------------------------------------------------------
*/

$requete = $pdo->query("
    SELECT *
    FROM categories
    ORDER BY id DESC
");

$categories = $requete->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Catégories - NPM Admin</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <!-- CSS NPM -->

    <link
        rel="stylesheet"
        href="../assets/css/style.css">

</head>


<body>

    <!-- NAVBAR ADMIN -->

    <?php require_once("includes/navbar.php"); ?>


    <!-- SIDEBAR ADMIN -->

    <?php require_once("includes/sidebar.php"); ?>


    <!-- CONTENU ADMIN -->

    <main class="admin-content">

        <div class="container-fluid py-5 px-4">


            <!-- TITRE -->

            <div class="mb-4">

                <h1 class="fw-bold">
                    Gestion des catégories
                </h1>

                <p class="text-muted">
                    Gérez les catégories de votre quincaillerie NPM.
                </p>

            </div>


            <!-- MESSAGE SUCCÈS -->

            <?php if (!empty($message)): ?>

                <div
                    class="alert alert-success"
                    role="alert">

                    <i class="bi bi-check-circle me-2"></i>

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>


            <!-- MESSAGE ERREUR -->

            <?php if (!empty($erreur)): ?>

                <div
                    class="alert alert-danger"
                    role="alert">

                    <i class="bi bi-exclamation-triangle me-2"></i>

                    <?= htmlspecialchars($erreur) ?>

                </div>

            <?php endif; ?>


            <!-- MODALE : AJOUTER UNE CATÉGORIE -->

            <div
                class="modal fade"
                id="modalAjouterCategorie"
                tabindex="-1"
                aria-labelledby="modalAjouterCategorieLabel"
                aria-hidden="true">

                <div class="modal-dialog">

                    <div class="modal-content">

                        <div class="modal-header bg-dark text-white">

                            <h5 class="modal-title" id="modalAjouterCategorieLabel">

                                <i class="bi bi-plus-circle me-2"></i>

                                Ajouter une catégorie

                            </h5>

                            <button
                                type="button"
                                class="btn-close btn-close-white"
                                data-bs-dismiss="modal"
                                aria-label="Fermer">
                            </button>

                        </div>


                        <form method="POST">

                            <div class="modal-body p-4">


                                <!-- NOM -->

                                <div class="mb-3">

                                    <label
                                        for="nom"
                                        class="form-label fw-semibold">

                                        Nom de la catégorie

                                        <span class="text-danger">*</span>

                                    </label>


                                    <input
                                        type="text"
                                        name="nom"
                                        id="nom"
                                        class="form-control"
                                        value="<?= htmlspecialchars($nom ?? "") ?>"
                                        placeholder="Ex : Outillage"
                                        required>

                                </div>


                                <!-- DESCRIPTION -->

                                <div class="mb-0">

                                    <label
                                        for="description"
                                        class="form-label fw-semibold">

                                        Description

                                    </label>


                                    <textarea
                                        name="description"
                                        id="description"
                                        class="form-control"
                                        rows="4"
                                        placeholder="Description de la catégorie..."><?= htmlspecialchars($description ?? "") ?></textarea>

                                </div>


                            </div>


                            <div class="modal-footer">

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">

                                    Annuler

                                </button>

                                <button
                                    type="submit"
                                    class="btn btn-warning fw-semibold">

                                    <i class="bi bi-plus-circle me-1"></i>

                                    Ajouter la catégorie

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>


            <!-- LISTE DES CATÉGORIES -->

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                        <h5 class="fw-bold mb-0">

                            <i class="bi bi-tags me-2"></i>

                            Liste des catégories

                        </h5>

                        <button
                            type="button"
                            class="btn btn-warning fw-semibold"
                            data-bs-toggle="modal"
                            data-bs-target="#modalAjouterCategorie">

                            <i class="bi bi-plus-circle me-1"></i>

                            Ajouter une catégorie

                        </button>

                    </div>

                </div>


                        <div class="card-body p-0">


                            <?php if (empty($categories)): ?>

                                <div class="text-center p-5">

                                    <i
                                        class="bi bi-tags fs-1 text-muted"></i>

                                    <p class="text-muted mt-3">

                                        Aucune catégorie n'a encore été créée.

                                    </p>

                                </div>


                            <?php else: ?>


                                <div class="table-responsive">

                                    <table
                                        class="table table-hover align-middle mb-0">

                                        <thead class="table-dark">

                                            <tr>

                                                <th>Nom</th>

                                                <th>Description</th>

                                                <th>Date</th>

                                                <th class="text-center">
                                                    Actions
                                                </th>

                                            </tr>

                                        </thead>


                                        <tbody>

                                            <?php foreach ($categories as $categorie): ?>

                                                <tr>

                                                    <!-- NOM -->

                                                    <td>

                                                        <strong>

                                                            <?= htmlspecialchars(
                                                                $categorie["nom"]
                                                            ) ?>

                                                        </strong>

                                                    </td>


                                                    <!-- DESCRIPTION -->

                                                    <td style="max-width: 260px;">

                                                        <?php

                                                        $description = trim($categorie["description"] ?? "");

                                                        ?>

                                                        <?php if ($description === ""): ?>

                                                            <span class="text-muted">—</span>

                                                        <?php else: ?>

                                                            <?php

                                                            $mots = preg_split('/\s+/', $description);
                                                            $apercu = implode(" ", array_slice($mots, 0, 3));
                                                            $estTronque = count($mots) > 3;

                                                            ?>

                                                            <?php if ($estTronque): ?>

                                                                <details>

                                                                    <summary
                                                                        style="cursor: pointer;"
                                                                        class="text-dark"
                                                                    >

                                                                        <?= htmlspecialchars($apercu) ?>…

                                                                    </summary>

                                                                    <div class="mt-2 text-muted">

                                                                        <?= htmlspecialchars($description) ?>

                                                                    </div>

                                                                </details>

                                                            <?php else: ?>

                                                                <?= htmlspecialchars($description) ?>

                                                            <?php endif; ?>

                                                        <?php endif; ?>

                                                    </td>


                                                    <!-- DATE -->

                                                    <td>

                                                        <?php if (
                                                            isset($categorie["date_creation"])
                                                            && !empty($categorie["date_creation"])
                                                        ): ?>

                                                            <?= date(
                                                                "d/m/Y",
                                                                strtotime(
                                                                    $categorie["date_creation"]
                                                                )
                                                            ) ?>

                                                        <?php else: ?>

                                                            —

                                                        <?php endif; ?>

                                                    </td>


                                                    <!-- ACTIONS -->

                                                    <td class="text-center">

                                                        <div class="d-flex justify-content-center gap-2">


                                                            <!-- MODIFIER -->

                                                            <a
                                                                href="modifier-categorie.php?id=<?= $categorie["id"] ?>"
                                                                class="btn btn-sm btn-outline-primary"
                                                                title="Modifier">

                                                                <i class="bi bi-pencil"></i>

                                                            </a>


                                                            <!-- SUPPRIMER -->

                                                            <form
                                                                method="POST"
                                                                action="supprimer-categorie.php"
                                                                class="d-inline"
                                                                onsubmit="return confirm('Voulez-vous vraiment supprimer cette catégorie ?');">

                                                                <input
                                                                    type="hidden"
                                                                    name="csrf_token"
                                                                    value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                                                                <input
                                                                    type="hidden"
                                                                    name="id"
                                                                    value="<?= (int) $categorie["id"] ?>">

                                                                <button
                                                                    type="submit"
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    title="Supprimer">

                                                                    <i class="bi bi-trash"></i>

                                                                </button>

                                                            </form>

                                                        </div>

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
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>


</body>

</html>