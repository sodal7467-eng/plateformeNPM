<?php

session_start();

require_once("../config/database.php");

$message = "";
$erreur = "";


// Variables du formulaire

$nom = "";
$description = "";
$prix = "";
$stock = "";
$categorie = "";


// =====================================================
// RÉCUPÉRER LES CATÉGORIES
// =====================================================

$requeteCategories = $pdo->query("
    SELECT id, nom
    FROM categories
    ORDER BY nom ASC
");

$categories = $requeteCategories->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// AJOUTER UN PRODUIT
// =====================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Récupérer les données

    $nom = trim($_POST["nom"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $prix = trim($_POST["prix"] ?? "");
    $stock = trim($_POST["stock"] ?? "");
    $categorie = trim($_POST["categorie"] ?? "");


    // =================================================
    // VALIDATION
    // =================================================

    if (empty($nom)) {

        $erreur = "Le nom du produit est obligatoire.";

    } elseif (empty($description)) {

        $erreur = "La description du produit est obligatoire.";

    } elseif ($prix === "" || !is_numeric($prix) || $prix < 0) {

        $erreur = "Veuillez saisir un prix valide.";

    } elseif ($stock === "" || !is_numeric($stock) || $stock < 0) {

        $erreur = "Veuillez saisir un stock valide.";

    } elseif (empty($categorie) || !is_numeric($categorie)) {

        $erreur = "Veuillez sélectionner une catégorie.";

    } else {


        // =================================================
        // VÉRIFIER QUE LA CATÉGORIE EXISTE
        // =================================================

        $verificationCategorie = $pdo->prepare("
            SELECT id
            FROM categories
            WHERE id = ?
        ");

        $verificationCategorie->execute([
            $categorie
        ]);

        $categorieExiste = $verificationCategorie->fetch();


        if (!$categorieExiste) {

            $erreur = "La catégorie sélectionnée n'existe pas.";

        } else {


            // =================================================
            // GESTION DE L'IMAGE
            // =================================================

            $nomImage = null;


            if (
                isset($_FILES["image"]) &&
                $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
            ) {

                // Vérifier les erreurs d'upload

                if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {

                    $erreur = "Une erreur est survenue lors de l'envoi de l'image.";

                } else {

                    $nomFichier = $_FILES["image"]["name"];

                    $tmpFichier = $_FILES["image"]["tmp_name"];

                    $extension = strtolower(
                        pathinfo($nomFichier, PATHINFO_EXTENSION)
                    );


                    // Extensions autorisées

                    $extensionsAutorisees = [
                        "jpg",
                        "jpeg",
                        "png",
                        "webp"
                    ];


                    if (!in_array($extension, $extensionsAutorisees)) {

                        $erreur = "Format d'image non autorisé. Utilisez JPG, JPEG, PNG ou WEBP.";

                    } else {


                        // Vérifier le type réel de l'image

                        $typesAutorises = [
                            "image/jpeg",
                            "image/png",
                            "image/webp"
                        ];

                        $typeImage = mime_content_type($tmpFichier);


                        if (!in_array($typeImage, $typesAutorises)) {

                            $erreur = "Le fichier envoyé n'est pas une image valide.";

                        } else {


                            // =================================================
                            // DOSSIER UPLOADS
                            // =================================================

                            $dossierUpload = "../uploads/";


                            if (!is_dir($dossierUpload)) {

                                mkdir(
                                    $dossierUpload,
                                    0777,
                                    true
                                );
                            }


                            // Nom unique

                            $nomImage = uniqid(
                                "produit_",
                                true
                            ) . "." . $extension;


                            $cheminImage = $dossierUpload . $nomImage;


                            // Déplacer l'image

                            if (!move_uploaded_file(
                                $tmpFichier,
                                $cheminImage
                            )) {

                                $erreur = "Impossible d'enregistrer l'image.";

                            }

                        }

                    }

                }

            }


            // =================================================
            // ENREGISTRER LE PRODUIT
            // =================================================

            if (empty($erreur)) {

                try {

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


                    $message = "Le produit a été ajouté avec succès.";


                    // Vider le formulaire

                    $nom = "";
                    $description = "";
                    $prix = "";
                    $stock = "";
                    $categorie = "";


                } catch (PDOException $e) {

                    // Si le produit n'a pas pu être enregistré
                    // et qu'une image a été créée, on la supprime.

                    if (
                        !empty($nomImage) &&
                        file_exists("../uploads/" . $nomImage)
                    ) {

                        unlink("../uploads/" . $nomImage);

                    }


                    $erreur = "Impossible d'ajouter le produit.";
                }

            }

        }

    }

}

?>


<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Ajouter un produit - NPM Admin</title>


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


<!-- =====================================================
     NAVBAR ADMIN
====================================================== -->

<?php require_once("includes/navbar.php"); ?>


<!-- =====================================================
     SIDEBAR ADMIN
====================================================== -->

<?php require_once("includes/sidebar.php"); ?>


<!-- =====================================================
     CONTENU ADMIN
====================================================== -->

<main class="admin-content">

    <div class="container py-5">


        <div class="row justify-content-center">

            <div class="col-lg-8">


                <!-- =================================================
                     TITRE
                ================================================== -->

                <div class="mb-4">

                    <h1 class="fw-bold">

                        Ajouter un produit

                    </h1>


                    <p class="text-muted">

                        Ajoutez un nouveau produit au catalogue NPM.

                    </p>

                </div>


                <!-- =================================================
                     MESSAGE SUCCÈS
                ================================================== -->

                <?php if (!empty($message)): ?>

                    <div
                        class="alert alert-success"
                        role="alert"
                    >

                        <i class="bi bi-check-circle me-2"></i>

                        <strong>Succès :</strong>

                        <?= htmlspecialchars($message) ?>

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     MESSAGE ERREUR
                ================================================== -->

                <?php if (!empty($erreur)): ?>

                    <div
                        class="alert alert-danger"
                        role="alert"
                    >

                        <i class="bi bi-exclamation-triangle me-2"></i>

                        <strong>Erreur :</strong>

                        <?= htmlspecialchars($erreur) ?>

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     FORMULAIRE
                ================================================== -->

                <div class="card border-0 shadow-sm">


                    <div class="card-header bg-dark text-white py-3">

                        <h5 class="mb-0">

                            <i class="bi bi-box-seam me-2"></i>

                            Informations du produit

                        </h5>

                    </div>


                    <div class="card-body p-4">


                        <form
                            method="POST"
                            enctype="multipart/form-data"
                        >


                            <!-- =================================================
                                 NOM
                            ================================================== -->

                            <div class="mb-3">

                                <label
                                    for="nom"
                                    class="form-label fw-semibold"
                                >

                                    Nom du produit
                                    <span class="text-danger">*</span>

                                </label>


                                <input
                                    type="text"
                                    id="nom"
                                    name="nom"
                                    class="form-control"
                                    value="<?= htmlspecialchars($nom) ?>"
                                    placeholder="Ex : Marteau professionnel"
                                    required
                                >

                            </div>


                            <!-- =================================================
                                 DESCRIPTION
                            ================================================== -->

                            <div class="mb-3">

                                <label
                                    for="description"
                                    class="form-label fw-semibold"
                                >

                                    Description
                                    <span class="text-danger">*</span>

                                </label>


                                <textarea
                                    id="description"
                                    name="description"
                                    class="form-control"
                                    rows="5"
                                    placeholder="Décrivez le produit..."
                                    required
                                ><?= htmlspecialchars($description) ?></textarea>

                            </div>


                            <!-- =================================================
                                 PRIX
                            ================================================== -->

                            <div class="mb-3">

                                <label
                                    for="prix"
                                    class="form-label fw-semibold"
                                >

                                    Prix (FCFA)
                                    <span class="text-danger">*</span>

                                </label>


                                <input
                                    type="number"
                                    id="prix"
                                    name="prix"
                                    class="form-control"
                                    value="<?= htmlspecialchars($prix) ?>"
                                    min="0"
                                    step="1"
                                    placeholder="Ex : 15000"
                                    required
                                >

                            </div>


                            <!-- =================================================
                                 STOCK
                            ================================================== -->

                            <div class="mb-3">

                                <label
                                    for="stock"
                                    class="form-label fw-semibold"
                                >

                                    Stock disponible
                                    <span class="text-danger">*</span>

                                </label>


                                <input
                                    type="number"
                                    id="stock"
                                    name="stock"
                                    class="form-control"
                                    value="<?= htmlspecialchars($stock) ?>"
                                    min="0"
                                    step="1"
                                    placeholder="Ex : 25"
                                    required
                                >

                            </div>


                            <!-- =================================================
                                 CATÉGORIE
                            ================================================== -->

                            <div class="mb-3">

                                <label
                                    for="categorie"
                                    class="form-label fw-semibold"
                                >

                                    Catégorie
                                    <span class="text-danger">*</span>

                                </label>


                                <select
                                    id="categorie"
                                    name="categorie"
                                    class="form-select"
                                    required
                                >

                                    <option value="">

                                        -- Choisir une catégorie --

                                    </option>


                                    <?php foreach ($categories as $cat): ?>

                                        <option
                                            value="<?= $cat["id"] ?>"
                                            <?= (
                                                $categorie == $cat["id"]
                                            ) ? "selected" : "" ?>
                                        >

                                            <?= htmlspecialchars(
                                                $cat["nom"]
                                            ) ?>

                                        </option>

                                    <?php endforeach; ?>


                                </select>


                                <?php if (empty($categories)): ?>

                                    <div class="form-text text-danger">

                                        Aucune catégorie disponible.
                                        Créez d'abord une catégorie.

                                    </div>

                                <?php else: ?>

                                    <div class="form-text">

                                        Sélectionnez la catégorie du produit.

                                    </div>

                                <?php endif; ?>

                            </div>


                            <!-- =================================================
                                 IMAGE
                            ================================================== -->

                            <div class="mb-4">

                                <label
                                    for="image"
                                    class="form-label fw-semibold"
                                >

                                    Image du produit

                                </label>


                                <input
                                    type="file"
                                    id="image"
                                    name="image"
                                    class="form-control"
                                    accept=".jpg,.jpeg,.png,.webp"
                                >


                                <small class="text-muted">

                                    Formats acceptés :
                                    JPG, JPEG, PNG et WEBP.

                                </small>

                            </div>


                            <!-- =================================================
                                 BOUTONS
                            ================================================== -->

                            <div class="d-flex gap-2">


                                <button
                                    type="submit"
                                    class="btn btn-warning px-4 fw-semibold"
                                >

                                    <i class="bi bi-plus-circle me-1"></i>

                                    Ajouter le produit

                                </button>


                                <a
                                    href="produits.php"
                                    class="btn btn-outline-dark"
                                >

                                    <i class="bi bi-arrow-left me-1"></i>

                                    Annuler

                                </a>


                            </div>


                        </form>

                    </div>

                </div>


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