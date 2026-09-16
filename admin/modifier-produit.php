<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| VÉRIFIER L'ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: produits.php");
    exit;
}

$id = (int) $_GET["id"];


/*
|--------------------------------------------------------------------------
| RÉCUPÉRER LE PRODUIT
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare("
    SELECT *
    FROM produits
    WHERE id = ?
");

$requete->execute([$id]);

$produit = $requete->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| VÉRIFIER QUE LE PRODUIT EXISTE
|--------------------------------------------------------------------------
*/

if (!$produit) {
    header("Location: produits.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| RÉCUPÉRER LES CATÉGORIES
|--------------------------------------------------------------------------
*/

$requeteCategories = $pdo->query("
    SELECT id, nom
    FROM categories
    ORDER BY nom ASC
");

$categories = $requeteCategories->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$message = "";
$erreur = "";

$nom = $produit["nom"];
$description = $produit["description"];
$prix = $produit["prix"];
$stock = $produit["stock"];
$categorie = $produit["categorie"];


/*
|--------------------------------------------------------------------------
| TRAITEMENT DU FORMULAIRE
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom = trim($_POST["nom"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $prix = trim($_POST["prix"] ?? "");
    $stock = trim($_POST["stock"] ?? "");
    $categorie = trim($_POST["categorie"] ?? "");


    /*
    |----------------------------------------------------------------------
    | VALIDATION
    |----------------------------------------------------------------------
    */

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


        /*
        |------------------------------------------------------------------
        | VÉRIFIER LA CATÉGORIE
        |------------------------------------------------------------------
        */

        $verificationCategorie = $pdo->prepare("
            SELECT id
            FROM categories
            WHERE id = ?
        ");

        $verificationCategorie->execute([$categorie]);

        if (!$verificationCategorie->fetch()) {

            $erreur = "La catégorie sélectionnée n'existe pas.";

        } else {


            /*
            |--------------------------------------------------------------
            | CONSERVER L'ANCIENNE IMAGE
            |--------------------------------------------------------------
            */

            $image = $produit["image"];


            /*
            |--------------------------------------------------------------
            | NOUVELLE IMAGE
            |--------------------------------------------------------------
            */

            if (
                isset($_FILES["image"]) &&
                $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
            ) {

                if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {

                    $erreur = "Une erreur est survenue lors de l'envoi de l'image.";

                } else {

                    $nomFichier = $_FILES["image"]["name"];
                    $tmpFichier = $_FILES["image"]["tmp_name"];

                    $extension = strtolower(
                        pathinfo($nomFichier, PATHINFO_EXTENSION)
                    );

                    $extensionsAutorisees = [
                        "jpg",
                        "jpeg",
                        "png",
                        "webp"
                    ];


                    /*
                    | Vérifier l'extension
                    */

                    if (!in_array($extension, $extensionsAutorisees)) {

                        $erreur = "Format d'image non autorisé. Utilisez JPG, JPEG, PNG ou WEBP.";

                    } else {


                        /*
                        | Vérifier le type MIME
                        */

                        $typesAutorises = [
                            "image/jpeg",
                            "image/png",
                            "image/webp"
                        ];

                        $typeImage = mime_content_type($tmpFichier);

                        if (!in_array($typeImage, $typesAutorises)) {

                            $erreur = "Le fichier envoyé n'est pas une image valide.";

                        } else {


                            /*
                            | Vérifier le dossier uploads
                            */

                            $dossierUpload = "../uploads/";

                            if (!is_dir($dossierUpload)) {

                                mkdir($dossierUpload, 0777, true);
                            }


                            /*
                            | Nouveau nom
                            */

                            $nouveauNom = uniqid(
                                "produit_",
                                true
                            ) . "." . $extension;


                            $destination = $dossierUpload . $nouveauNom;


                            /*
                            | Déplacer l'image
                            */

                            if (
                                move_uploaded_file(
                                    $tmpFichier,
                                    $destination
                                )
                            ) {

                                /*
                                | Supprimer l'ancienne image
                                */

                                if (
                                    !empty($produit["image"]) &&
                                    file_exists(
                                        $dossierUpload . $produit["image"]
                                    )
                                ) {

                                    unlink(
                                        $dossierUpload . $produit["image"]
                                    );
                                }


                                $image = $nouveauNom;

                            } else {

                                $erreur = "Impossible d'enregistrer la nouvelle image.";

                            }
                        }
                    }
                }
            }


            /*
            |--------------------------------------------------------------
            | MODIFICATION EN BASE
            |--------------------------------------------------------------
            */

            if (empty($erreur)) {

                try {

                    $requete = $pdo->prepare("
                        UPDATE produits
                        SET
                            nom = :nom,
                            description = :description,
                            prix = :prix,
                            stock = :stock,
                            image = :image,
                            categorie = :categorie
                        WHERE id = :id
                    ");

                    $requete->execute([

                        ":nom" => $nom,

                        ":description" => $description,

                        ":prix" => $prix,

                        ":stock" => $stock,

                        ":image" => $image,

                        ":categorie" => $categorie,

                        ":id" => $id

                    ]);


                    $message = "Le produit a été modifié avec succès.";


                    /*
                    | Récupérer les nouvelles informations
                    */

                    $requete = $pdo->prepare("
                        SELECT *
                        FROM produits
                        WHERE id = ?
                    ");

                    $requete->execute([$id]);

                    $produit = $requete->fetch(PDO::FETCH_ASSOC);


                    $nom = $produit["nom"];
                    $description = $produit["description"];
                    $prix = $produit["prix"];
                    $stock = $produit["stock"];
                    $categorie = $produit["categorie"];

                } catch (PDOException $e) {

                    $erreur = "Impossible de modifier le produit.";
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

    <title>Modifier un produit - Administration NPM</title>


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

                    Modifier le produit

                </h1>

                <p class="text-muted mb-0">

                    Modifiez les informations du produit.

                </p>

            </div>


            <a
                href="produits.php"
                class="btn btn-outline-secondary"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Retour aux produits

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
        <?php require_once("includes/auth.php");?> 



        <!-- FORMULAIRE -->

        <div class="card border-0 shadow-sm">


            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0">

                    <i class="bi bi-pencil-square me-2"></i>

                    Informations du produit

                </h5>

            </div>



            <div class="card-body p-4">


                <form
                    method="POST"
                    enctype="multipart/form-data"
                >


                    <div class="row">


                        <!-- NOM -->

                        <div class="col-md-6 mb-3">

                            <label
                                for="nom"
                                class="form-label fw-bold"
                            >

                                Nom du produit *

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



                        <!-- CATÉGORIE -->

                        <div class="col-md-6 mb-3">

                            <label
                                for="categorie"
                                class="form-label fw-bold"
                            >

                                Catégorie *

                            </label>


                            <select
                                name="categorie"
                                id="categorie"
                                class="form-select"
                                required
                            >

                                <option value="">

                                    -- Choisir une catégorie --

                                </option>


                                <?php foreach ($categories as $cat): ?>

                                    <option
                                        value="<?= $cat["id"] ?>"
                                        <?= ($categorie == $cat["id"]) ? "selected" : "" ?>
                                    >

                                        <?= htmlspecialchars($cat["nom"]) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>



                        <!-- PRIX -->

                        <div class="col-md-6 mb-3">

                            <label
                                for="prix"
                                class="form-label fw-bold"
                            >

                                Prix (FCFA) *

                            </label>

                            <input
                                type="number"
                                name="prix"
                                id="prix"
                                class="form-control"
                                min="0"
                                value="<?= htmlspecialchars($prix) ?>"
                                required
                            >

                        </div>



                        <!-- STOCK -->

                        <div class="col-md-6 mb-3">

                            <label
                                for="stock"
                                class="form-label fw-bold"
                            >

                                Stock *

                            </label>

                            <input
                                type="number"
                                name="stock"
                                id="stock"
                                class="form-control"
                                min="0"
                                value="<?= htmlspecialchars($stock) ?>"
                                required
                            >

                        </div>



                        <!-- DESCRIPTION -->

                        <div class="col-12 mb-3">

                            <label
                                for="description"
                                class="form-label fw-bold"
                            >

                                Description *

                            </label>

                            <textarea
                                name="description"
                                id="description"
                                class="form-control"
                                rows="5"
                                required
                            ><?= htmlspecialchars($description) ?></textarea>

                        </div>



                        <!-- IMAGE -->

                        <div class="col-12 mb-4">

                            <label
                                for="image"
                                class="form-label fw-bold"
                            >

                                Image du produit

                            </label>


                            <?php if (!empty($produit["image"])): ?>

                                <div class="mb-3">

                                    <p class="text-muted mb-2">

                                        Image actuelle :

                                    </p>


                                    <img
                                        src="../uploads/<?= htmlspecialchars($produit["image"]) ?>"
                                        alt="<?= htmlspecialchars($produit["nom"]) ?>"
                                        width="150"
                                        height="150"
                                        style="
                                            object-fit: cover;
                                            border-radius: 10px;
                                            border: 1px solid #ddd;
                                        "
                                    >

                                </div>

                            <?php endif; ?>


                            <input
                                type="file"
                                name="image"
                                id="image"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp"
                            >


                            <small class="text-muted">

                                Laissez vide pour conserver l'image actuelle.

                            </small>

                        </div>


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
                            href="produits.php"
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