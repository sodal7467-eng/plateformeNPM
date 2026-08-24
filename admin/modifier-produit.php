<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Vérifier qu'un ID est présent
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: produits.php");
    exit;
}

$id = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| Récupérer le produit
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
| Vérifier que le produit existe
|--------------------------------------------------------------------------
*/

if (!$produit) {
    header("Location: produits.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Modifier le produit
|--------------------------------------------------------------------------
*/

$message = "";
$erreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $prix = $_POST['prix'];
    $stock = $_POST['stock'];
    $categorie = trim($_POST['categorie']);


    /*
    | Vérification
    */

    if (
        empty($nom) ||
        $prix === "" ||
        $stock === "" ||
        empty($categorie)
    ) {

        $erreur = "Veuillez remplir tous les champs obligatoires.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Image
        |--------------------------------------------------------------------------
        */

        $image = $produit['image'];

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] === UPLOAD_ERR_OK
        ) {

            $nomImage = $_FILES['image']['name'];

            $extension = strtolower(
                pathinfo($nomImage, PATHINFO_EXTENSION)
            );

            $extensionsAutorisees = [
                "jpg",
                "jpeg",
                "png",
                "webp"
            ];

            if (!in_array($extension, $extensionsAutorisees)) {

                $erreur = "Format d'image non autorisé.";

            } else {

                /*
                | Nouveau nom pour l'image
                */

                $nouveauNom = uniqid("produit_") . "." . $extension;

                $destination = "../assets/images/" . $nouveauNom;


                if (move_uploaded_file(
                    $_FILES['image']['tmp_name'],
                    $destination
                )) {

                    $image = $nouveauNom;

                } else {

                    $erreur = "Erreur lors de l'envoi de l'image.";

                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Mise à jour
        |--------------------------------------------------------------------------
        */

        if (empty($erreur)) {

            $requete = $pdo->prepare("
                UPDATE produits
                SET
                    nom = ?,
                    description = ?,
                    prix = ?,
                    stock = ?,
                    image = ?,
                    categorie = ?
                WHERE id = ?
            ");

            $requete->execute([
                $nom,
                $description,
                $prix,
                $stock,
                $image,
                $categorie,
                $id
            ]);


            $message = "Produit modifié avec succès.";


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

    <title>Modifier un produit - NPM</title>


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


<?php include "includes/header.php"; ?>


<div class="container-fluid">

    <div class="row">


        <!-- SIDEBAR -->

        <div class="col-md-3 col-lg-2 p-0">

            <?php include "includes/sidebar.php"; ?>

        </div>


        <!-- CONTENU -->

        <main class="col-md-9 col-lg-10 p-4">


            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h1 class="fw-bold">

                        Modifier le produit

                    </h1>

                    <p class="text-muted">

                        Modifiez les informations de votre produit.

                    </p>

                </div>


                <a
                    href="produits.php"
                    class="btn btn-outline-secondary"
                >

                    <i class="bi bi-arrow-left"></i>

                    Retour aux produits

                </a>

            </div>


            <!-- MESSAGE DE SUCCÈS -->

            <?php if (!empty($message)): ?>

                <div
                    class="alert alert-success"
                    role="alert"
                >

                    <i class="bi bi-check-circle me-2"></i>

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>


            <!-- MESSAGE D'ERREUR -->

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

            <div class="card shadow-sm border-0">

                <div class="card-header bg-white">

                    <h5 class="mb-0 fw-bold">

                        <i class="bi bi-pencil-square me-2"></i>

                        Informations du produit

                    </h5>

                </div>


                <div class="card-body">


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
                                    value="<?= htmlspecialchars($produit['nom']) ?>"
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

                                <input
                                    type="text"
                                    name="categorie"
                                    id="categorie"
                                    class="form-control"
                                    value="<?= htmlspecialchars($produit['categorie']) ?>"
                                    required
                                >

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
                                    value="<?= htmlspecialchars($produit['prix']) ?>"
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
                                    value="<?= htmlspecialchars($produit['stock']) ?>"
                                    required
                                >

                            </div>


                            <!-- DESCRIPTION -->

                            <div class="col-12 mb-3">

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
                                ><?= htmlspecialchars($produit['description']) ?></textarea>

                            </div>


                            <!-- IMAGE -->

                            <div class="col-12 mb-4">

                                <label
                                    for="image"
                                    class="form-label fw-bold"
                                >

                                    Image du produit

                                </label>


                                <?php if (!empty($produit['image'])): ?>

                                    <div class="mb-3">

                                        <p class="text-muted mb-2">

                                            Image actuelle :

                                        </p>

                                        <img
                                            src="../assets/images/<?= htmlspecialchars($produit['image']) ?>"
                                            alt="<?= htmlspecialchars($produit['nom']) ?>"
                                            width="150"
                                            height="150"
                                            style="
                                                object-fit: cover;
                                                border-radius: 10px;
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

                                    Laissez vide si vous ne souhaitez pas
                                    changer l'image.

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


        </main>

    </div>

</div>


<?php include "includes/footer.php"; ?>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>