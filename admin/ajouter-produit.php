<?php

require_once "../config/database.php";

$message = "";
$erreur = "";


/* =========================================
   TRAITEMENT DU FORMULAIRE
========================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom = trim($_POST["nom"]);
    $description = trim($_POST["description"]);
    $prix = $_POST["prix"];
    $stock = $_POST["stock"];
    $categorie = trim($_POST["categorie"]);


    /* Vérification */

    if (
        empty($nom) ||
        empty($prix) ||
        $stock === "" ||
        empty($categorie)
    ) {

        $erreur = "Veuillez remplir tous les champs obligatoires.";

    } elseif (!is_numeric($prix) || $prix < 0) {

        $erreur = "Le prix n'est pas valide.";

    } elseif (!is_numeric($stock) || $stock < 0) {

        $erreur = "Le stock n'est pas valide.";

    } else {


        /* =========================================
           GESTION DE L'IMAGE
        ========================================= */

        $nomImage = null;

        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] === UPLOAD_ERR_OK
        ) {

            $dossier = "../assets/images/";

            /* Créer le dossier s'il n'existe pas */

            if (!is_dir($dossier)) {

                mkdir($dossier, 0777, true);

            }


            $nomOriginal = $_FILES["image"]["name"];

            $extension = strtolower(
                pathinfo($nomOriginal, PATHINFO_EXTENSION)
            );


            /* Extensions autorisées */

            $extensionsAutorisees = [
                "jpg",
                "jpeg",
                "png",
                "webp"
            ];


            if (!in_array($extension, $extensionsAutorisees)) {

                $erreur = "Format d'image non autorisé.";

            } else {

                /* Nom unique */

                $nomImage = uniqid("produit_", true) . "." . $extension;

                $chemin = $dossier . $nomImage;


                if (!move_uploaded_file(
                    $_FILES["image"]["tmp_name"],
                    $chemin
                )) {

                    $erreur = "Impossible d'enregistrer l'image.";

                }

            }

        }


        /* =========================================
           ENREGISTREMENT DU PRODUIT
        ========================================= */

        if (empty($erreur)) {

            $requete = $pdo->prepare("
                INSERT INTO produits
                (
                    nom,
                    description,
                    prix,
                    stock,
                    image,
                    categorie
                )
                VALUES
                (
                    :nom,
                    :description,
                    :prix,
                    :stock,
                    :image,
                    :categorie
                )
            ");


            $requete->execute([

                ":nom" => $nom,

                ":description" => $description,

                ":prix" => $prix,

                ":stock" => $stock,

                ":image" => $nomImage,

                ":categorie" => $categorie

            ]);


            $message = "Produit ajouté avec succès !";


            /* Vider les champs */

            $nom = "";
            $description = "";
            $prix = "";
            $stock = "";
            $categorie = "";

        }

    }

}

?>


<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Ajouter un produit - NPM</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <!-- CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/style.css">

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

            <div class="d-flex
                        justify-content-between
                        align-items-center
                        mb-4">

                <div>

                    <h1 class="fw-bold">

                        Ajouter un produit

                    </h1>

                    <p class="text-muted">

                        Ajoutez un nouveau produit dans votre
                        quincaillerie.

                    </p>

                </div>


                <a
                    href="produits.php"
                    class="btn btn-outline-dark">

                    <i class="bi bi-arrow-left"></i>

                    Retour aux produits

                </a>

            </div>


            <!-- MESSAGE DE SUCCÈS -->

            <?php if (!empty($message)): ?>

                <div
                    class="alert alert-success
                           alert-dismissible fade show">

                    <i class="bi bi-check-circle"></i>

                    <?= htmlspecialchars($message) ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                    </button>

                </div>

            <?php endif; ?>


            <!-- MESSAGE D'ERREUR -->

            <?php if (!empty($erreur)): ?>

                <div
                    class="alert alert-danger
                           alert-dismissible fade show">

                    <i class="bi bi-exclamation-triangle"></i>

                    <?= htmlspecialchars($erreur) ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                    </button>

                </div>

            <?php endif; ?>


            <!-- FORMULAIRE -->

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-dark text-white">

                    <h5 class="mb-0">

                        <i class="bi bi-box-seam"></i>

                        Informations du produit

                    </h5>

                </div>


                <div class="card-body p-4">


                    <form
                        action=""
                        method="POST"
                        enctype="multipart/form-data">


                        <div class="row g-4">


                            <!-- NOM -->

                            <div class="col-md-6">

                                <label
                                    for="nom"
                                    class="form-label fw-semibold">

                                    Nom du produit
                                    <span class="text-danger">*</span>

                                </label>

                                <input
                                    type="text"
                                    name="nom"
                                    id="nom"
                                    class="form-control"
                                    placeholder="Ex : Marteau"
                                    value="<?= htmlspecialchars($nom ?? "") ?>"
                                    required>

                            </div>


                            <!-- CATÉGORIE -->

                            <div class="col-md-6">

                                <label
                                    for="categorie"
                                    class="form-label fw-semibold">

                                    Catégorie
                                    <span class="text-danger">*</span>

                                </label>

                                <input
                                    type="text"
                                    name="categorie"
                                    id="categorie"
                                    class="form-control"
                                    placeholder="Ex : Outillage"
                                    value="<?= htmlspecialchars($categorie ?? "") ?>"
                                    required>

                            </div>


                            <!-- PRIX -->

                            <div class="col-md-6">

                                <label
                                    for="prix"
                                    class="form-label fw-semibold">

                                    Prix (FCFA)
                                    <span class="text-danger">*</span>

                                </label>

                                <input
                                    type="number"
                                    name="prix"
                                    id="prix"
                                    class="form-control"
                                    placeholder="Ex : 5000"
                                    min="0"
                                    step="1"
                                    value="<?= htmlspecialchars($prix ?? "") ?>"
                                    required>

                            </div>


                            <!-- STOCK -->

                            <div class="col-md-6">

                                <label
                                    for="stock"
                                    class="form-label fw-semibold">

                                    Quantité en stock
                                    <span class="text-danger">*</span>

                                </label>

                                <input
                                    type="number"
                                    name="stock"
                                    id="stock"
                                    class="form-control"
                                    placeholder="Ex : 20"
                                    min="0"
                                    step="1"
                                    value="<?= htmlspecialchars($stock ?? "") ?>"
                                    required>

                            </div>


                            <!-- DESCRIPTION -->

                            <div class="col-12">

                                <label
                                    for="description"
                                    class="form-label fw-semibold">

                                    Description

                                </label>

                                <textarea
                                    name="description"
                                    id="description"
                                    class="form-control"
                                    rows="5"
                                    placeholder="Décrivez le produit..."><?= htmlspecialchars($description ?? "") ?></textarea>

                            </div>


                            <!-- IMAGE -->

                            <div class="col-12">

                                <label
                                    for="image"
                                    class="form-label fw-semibold">

                                    Image du produit

                                </label>

                                <input
                                    type="file"
                                    name="image"
                                    id="image"
                                    class="form-control"
                                    accept=".jpg,.jpeg,.png,.webp">


                                <div class="form-text">

                                    Formats acceptés :
                                    JPG, JPEG, PNG et WEBP.

                                </div>

                            </div>


                            <!-- BOUTONS -->

                            <div class="col-12">

                                <hr>

                                <div class="d-flex gap-2">

                                    <button
                                        type="submit"
                                        class="btn btn-dark">

                                        <i class="bi bi-plus-circle"></i>

                                        Ajouter le produit

                                    </button>


                                    <button
                                        type="reset"
                                        class="btn btn-outline-secondary">

                                        Réinitialiser

                                    </button>

                                </div>

                            </div>


                        </div>


                    </form>

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