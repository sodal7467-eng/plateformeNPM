<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| VÉRIFIER L'ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: categories.php");
    exit;
}

$id = (int) $_GET["id"];


/*
|--------------------------------------------------------------------------
| RÉCUPÉRER LA CATÉGORIE
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare("
    SELECT *
    FROM categories
    WHERE id = ?
");

$requete->execute([$id]);

$categorie = $requete->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| VÉRIFIER QUE LA CATÉGORIE EXISTE
|--------------------------------------------------------------------------
*/

if (!$categorie) {
    header("Location: categories.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$message = "";
$erreur = "";

$nom = $categorie["nom"];
$description = $categorie["description"];


/*
|--------------------------------------------------------------------------
| MODIFICATION
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom = trim($_POST["nom"] ?? "");
    $description = trim($_POST["description"] ?? "");


    /*
    |----------------------------------------------------------------------
    | VALIDATION
    |----------------------------------------------------------------------
    */

    if (empty($nom)) {

        $erreur = "Le nom de la catégorie est obligatoire.";

    } else {

        /*
        | Vérifier si une autre catégorie possède déjà ce nom
        */

        $verification = $pdo->prepare("
            SELECT id
            FROM categories
            WHERE nom = ?
            AND id != ?
        ");

        $verification->execute([
            $nom,
            $id
        ]);


        if ($verification->fetch()) {

            $erreur = "Une autre catégorie possède déjà ce nom.";

        } else {

            /*
            |------------------------------------------------------------------
            | MISE À JOUR
            |------------------------------------------------------------------
            */

            $requete = $pdo->prepare("
                UPDATE categories
                SET
                    nom = ?,
                    description = ?
                WHERE id = ?
            ");

            $requete->execute([
                $nom,
                $description,
                $id
            ]);


            $message = "La catégorie a été modifiée avec succès.";


            /*
            | Récupérer les nouvelles informations
            */

            $requete = $pdo->prepare("
                SELECT *
                FROM categories
                WHERE id = ?
            ");

            $requete->execute([$id]);

            $categorie = $requete->fetch(PDO::FETCH_ASSOC);

            $nom = $categorie["nom"];
            $description = $categorie["description"];
        }
    }
}

?>
<?php require_once("includes/auth.php");?> 

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Modifier une catégorie - Administration NPM</title>


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


<!-- NAVBAR ADMIN -->

<?php require_once("includes/navbar.php"); ?>


<!-- SIDEBAR ADMIN -->

<?php require_once("includes/sidebar.php"); ?>


<!-- CONTENU -->

<main class="admin-content">

    <div class="container-fluid p-4">


        <!-- EN-TÊTE -->

        <div class="d-flex
                    justify-content-between
                    align-items-center
                    flex-wrap
                    gap-3
                    mb-4">

            <div>

                <h1 class="fw-bold mb-1">

                    Modifier la catégorie

                </h1>

                <p class="text-muted mb-0">

                    Modifiez les informations de cette catégorie.

                </p>

            </div>


            <a
                href="categories.php"
                class="btn btn-outline-secondary"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Retour aux catégories

            </a>

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



        <!-- FORMULAIRE -->

        <div class="card border-0 shadow-sm">


            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0">

                    <i class="bi bi-pencil-square me-2"></i>

                    Informations de la catégorie

                </h5>

            </div>


            <div class="card-body p-4">


                <form method="POST">


                    <!-- NOM -->

                    <div class="mb-4">

                        <label
                            for="nom"
                            class="form-label fw-bold"
                        >

                            Nom de la catégorie *

                        </label>

                        <input
                            type="text"
                            name="nom"
                            id="nom"
                            class="form-control"
                            value="<?= htmlspecialchars($nom) ?>"
                            required
                        >

                    </div>



                    <!-- DESCRIPTION -->

                    <div class="mb-4">

                        <label
                            for="description"
                            class="form-label fw-bold"
                        >

                            Description

                        </label>

                        <textarea
                            name="description"
                            id="description"
                            class="form-control"
                            rows="5"
                        ><?= htmlspecialchars($description) ?></textarea>

                    </div>



                    <!-- BOUTONS -->

                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-dark"
                        >

                            <i class="bi bi-check-circle me-1"></i>

                            Enregistrer les modifications

                        </button>


                        <a
                            href="categories.php"
                            class="btn btn-outline-secondary"
                        >

                            Annuler

                        </a>

                    </div>


                </form>

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