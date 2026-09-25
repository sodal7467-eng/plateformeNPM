<?php

require_once("includes/auth.php");

require_once("../config/database.php");

$erreur = "";

$description = "";
$categorie = "";
$montant = "";
$date_depense = date("Y-m-d");


/*
|--------------------------------------------------------------------------
| Catégories courantes proposées (liste libre, pas de table dédiée)
|--------------------------------------------------------------------------
*/

$categoriesProposees = [
    "Achat de marchandise",
    "Loyer",
    "Électricité / eau",
    "Transport",
    "Salaires",
    "Autre"
];


/*
|--------------------------------------------------------------------------
| Enregistrer la dépense
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verifier();


    $description = trim($_POST["description"] ?? "");
    $categorie = trim($_POST["categorie"] ?? "");
    $montant = $_POST["montant"] ?? "";
    $date_depense = $_POST["date_depense"] ?? "";


    if ($description === "") {

        $erreur = "Veuillez décrire la dépense.";

    } elseif ($montant === "" || !is_numeric($montant) || (float) $montant <= 0) {

        $erreur = "Le montant est invalide.";

    } elseif (
        $date_depense === "" ||
        !DateTime::createFromFormat("Y-m-d", $date_depense)
    ) {

        $erreur = "La date est invalide.";

    } else {

        $requete = $pdo->prepare("
            INSERT INTO depenses
            (description, categorie, montant, date_depense)
            VALUES (?, ?, ?, ?)
        ");

        $requete->execute([
            $description,
            $categorie !== "" ? $categorie : null,
            (float) $montant,
            $date_depense
        ]);

        header("Location: depenses.php?ajoute=1");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ajouter une dépense - NPM Admin</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link rel="stylesheet" href="../assets/css/style.css">

</head>


<body class="bg-light">


<?php require_once("includes/navbar.php"); ?>

<?php require_once("includes/sidebar.php"); ?>


<main class="admin-content">

    <div class="container-fluid py-4">


        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h1 class="fw-bold mb-1">Ajouter une dépense</h1>
                <p class="text-muted mb-0">
                    Enregistrer une dépense de la boutique.
                </p>
            </div>

            <a href="depenses.php" class="btn btn-dark">
                <i class="bi bi-arrow-left me-2"></i>
                Retour aux dépenses
            </a>

        </div>


        <?php if ($erreur !== ""): ?>

            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($erreur) ?>
            </div>

        <?php endif; ?>


        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-wallet2 me-2"></i>
                    Informations de la dépense
                </h5>
            </div>

            <div class="card-body p-4">

                <form method="POST">
                    <?= csrf_champ() ?>

                    <div class="row g-4">

                        <div class="col-12">

                            <label class="form-label fw-semibold">
                                Description <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                name="description"
                                class="form-control"
                                placeholder="Exemple : Achat de vis et boulons"
                                value="<?= htmlspecialchars($description) ?>"
                                required
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label fw-semibold">
                                Catégorie
                            </label>

                            <input
                                type="text"
                                name="categorie"
                                list="categoriesListe"
                                class="form-control"
                                placeholder="Exemple : Achat de marchandise"
                                value="<?= htmlspecialchars($categorie) ?>"
                            >

                            <datalist id="categoriesListe">
                                <?php foreach ($categoriesProposees as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>">
                                <?php endforeach; ?>
                            </datalist>

                            <small class="text-muted">Facultatif</small>

                        </div>


                        <div class="col-md-3">

                            <label class="form-label fw-semibold">
                                Montant <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">

                                <input
                                    type="number"
                                    name="montant"
                                    class="form-control"
                                    min="1"
                                    step="1"
                                    value="<?= htmlspecialchars($montant) ?>"
                                    required
                                >

                                <span class="input-group-text">FCFA</span>

                            </div>

                        </div>


                        <div class="col-md-3">

                            <label class="form-label fw-semibold">
                                Date <span class="text-danger">*</span>
                            </label>

                            <input
                                type="date"
                                name="date_depense"
                                class="form-control"
                                value="<?= htmlspecialchars($date_depense) ?>"
                                required
                            >

                        </div>

                    </div>


                    <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">

                        <a href="depenses.php" class="btn btn-outline-secondary">
                            Annuler
                        </a>

                        <button type="submit" class="btn btn-warning px-4">
                            <i class="bi bi-plus-lg me-2"></i>
                            Ajouter la dépense
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</main>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>
