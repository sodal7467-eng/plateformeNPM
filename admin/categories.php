<?php

require_once "../config/database.php";

$message = "";
$erreur = "";


/*
|--------------------------------------------------------------------------
| AJOUTER UNE CATÉGORIE
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom = trim($_POST["nom"]);
    $description = trim($_POST["description"]);


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
        content="width=device-width, initial-scale=1.0"
    >

    <title>Catégories - Administration NPM</title>


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


    <!-- CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body>


<!-- HEADER -->

<?php include "includes/header.php"; ?>


<div class="container-fluid">

    <div class="row">


        <!-- SIDEBAR -->

        <div class="col-md-3 col-lg-2 p-0">

            <?php include "includes/sidebar.php"; ?>

        </div>


        <!-- CONTENU -->

        <main class="col-md-9 col-lg-10 p-4">


            <!-- TITRE -->

            <div class="mb-4">

                <h1 class="fw-bold">

                    Catégories

                </h1>

                <p class="text-muted">

                    Gérez les catégories de votre quincaillerie.

                </p>

            </div>


            <!-- MESSAGE SUCCÈS -->

            <?php if (!empty($message)): ?>

                <div
                    class="alert alert-success"
                    role="alert"
                >

                    <i class="bi bi-check-circle me-2"></i>

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>


            <!-- MESSAGE ERREUR -->

            <?php if (!empty($erreur)): ?>

                <div
                    class="alert alert-danger"
                    role="alert"
                >

                    <i class="bi bi-exclamation-triangle me-2"></i>

                    <?= htmlspecialchars($erreur) ?>

                </div>

            <?php endif; ?>


            <div class="row g-4">


                <!-- FORMULAIRE -->

                <div class="col-lg-4">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-dark text-white">

                            <h5 class="mb-0">

                                <i class="bi bi-plus-circle me-2"></i>

                                Ajouter une catégorie

                            </h5>

                        </div>


                        <div class="card-body">


                            <form method="POST">


                                <!-- NOM -->

                                <div class="mb-3">

                                    <label
                                        for="nom"
                                        class="form-label fw-semibold"
                                    >

                                        Nom de la catégorie
                                        <span class="text-danger">*</span>

                                    </label>

                                    <input
                                        type="text"
                                        name="nom"
                                        id="nom"
                                        class="form-control"
                                        placeholder="Ex : Outillage"
                                        required
                                    >

                                </div>


                                <!-- DESCRIPTION -->

                                <div class="mb-3">

                                    <label
                                        for="description"
                                        class="form-label fw-semibold"
                                    >

                                        Description

                                    </label>

                                    <textarea
                                        name="description"
                                        id="description"
                                        class="form-control"
                                        rows="4"
                                        placeholder="Description de la catégorie..."
                                    ></textarea>

                                </div>


                                <button
                                    type="submit"
                                    class="btn btn-dark w-100"
                                >

                                    <i class="bi bi-plus-circle me-1"></i>

                                    Ajouter la catégorie

                                </button>


                            </form>

                        </div>

                    </div>

                </div>


                <!-- LISTE DES CATÉGORIES -->

                <div class="col-lg-8">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white">

                            <h5 class="fw-bold mb-0">

                                <i class="bi bi-tags me-2"></i>

                                Liste des catégories

                            </h5>

                        </div>


                        <div class="card-body p-0">


                            <?php if (empty($categories)): ?>

                                <div class="text-center p-5">

                                    <i
                                        class="bi bi-tags fs-1 text-muted"
                                    ></i>

                                    <p class="text-muted mt-3">

                                        Aucune catégorie n'a encore été créée.

                                    </p>

                                </div>

                            <?php else: ?>


                                <div class="table-responsive">

                                    <table
                                        class="table table-hover
                                               align-middle mb-0"
                                    >

                                        <thead class="table-dark">

                                            <tr>

                                                <th>ID</th>

                                                <th>Nom</th>

                                                <th>Description</th>

                                                <th>Date</th>

                                            </tr>

                                        </thead>


                                        <tbody>

                                            <?php foreach (
                                                $categories
                                                as $categorie
                                            ): ?>

                                                <tr>

                                                    <td>

                                                        <?= $categorie["id"] ?>

                                                    </td>


                                                    <td>

                                                        <strong>

                                                            <?= htmlspecialchars(
                                                                $categorie["nom"]
                                                            ) ?>

                                                        </strong>

                                                    </td>


                                                    <td>

                                                        <?= htmlspecialchars(
                                                            $categorie[
                                                                "description"
                                                            ] ?? ""
                                                        ) ?>

                                                    </td>


                                                    <td>

                                                        <?= date(
                                                            "d/m/Y",
                                                            strtotime(
                                                                $categorie[
                                                                    "date_creation"
                                                                ]
                                                            )
                                                        ) ?>

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


            </div>


        </main>

    </div>

</div>


<!-- FOOTER -->

<?php include "includes/footer.php"; ?>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>