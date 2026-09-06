<?php

require_once "../config/database.php";


// Vérifier l'ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: commandes.php");
    exit;

}

$id = (int) $_GET["id"];


// Récupérer la commande
$requete = $pdo->prepare("
    SELECT
        commandes.*,
        utilisateurs.nom,
        utilisateurs.email
    FROM commandes
    INNER JOIN utilisateurs
        ON commandes.utilisateur_id = utilisateurs.id
    WHERE commandes.id = ?
");

$requete->execute([$id]);

$commande = $requete->fetch(PDO::FETCH_ASSOC);


// Vérifier que la commande existe
if (!$commande) {

    header("Location: commandes.php");
    exit;

}


$erreur = "";
$message = "";


// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $statut = $_POST["statut"] ?? "";


    // Statuts autorisés
    $statutsAutorises = [
        "En attente",
        "En préparation",
        "Expédiée",
        "Livrée",
        "Annulée"
    ];


    if (!in_array($statut, $statutsAutorises)) {

        $erreur = "Statut invalide.";

    } else {

        $requete = $pdo->prepare("
            UPDATE commandes
            SET statut = ?
            WHERE id = ?
        ");

        $requete->execute([
            $statut,
            $id
        ]);


        $message = "Le statut de la commande a été modifié avec succès.";


        // Mettre à jour les informations affichées
        $commande["statut"] = $statut;
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

    <title>
        Modifier commande - NPM Admin
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body>


<?php include "includes/navbar.php"; ?>

<?php include "includes/sidebar.php"; ?>


<div class="admin-content">

    <div class="container-fluid p-4">


        <!-- En-tête -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1 class="fw-bold mb-1">
                    Modifier la commande #<?= $commande["id"] ?>
                </h1>

                <p class="text-muted mb-0">
                    Modifier l'état de la commande.
                </p>

            </div>


            <a
                href="commande-details.php?id=<?= $commande["id"] ?>"
                class="btn btn-outline-dark"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Retour

            </a>

        </div>



        <!-- Messages -->

        <?php if (!empty($message)): ?>

            <div class="alert alert-success">

                <i class="bi bi-check-circle me-2"></i>

                <?= htmlspecialchars($message) ?>

            </div>

        <?php endif; ?>


        <?php if (!empty($erreur)): ?>

            <div class="alert alert-danger">

                <i class="bi bi-exclamation-circle me-2"></i>

                <?= htmlspecialchars($erreur) ?>

            </div>

        <?php endif; ?>



        <div class="row g-4">


            <!-- Informations commande -->

            <div class="col-lg-6">

                <div class="card border-0 shadow-sm">

                    <div class="card-body p-4">

                        <h4 class="fw-bold mb-4">

                            <i class="bi bi-receipt me-2"></i>

                            Informations de la commande

                        </h4>


                        <div class="mb-3">

                            <small class="text-muted">
                                Numéro
                            </small>

                            <div class="fw-bold">
                                #<?= $commande["id"] ?>
                            </div>

                        </div>


                        <div class="mb-3">

                            <small class="text-muted">
                                Client
                            </small>

                            <div class="fw-bold">

                                <?= htmlspecialchars($commande["nom"]) ?>

                            </div>

                        </div>


                        <div class="mb-3">

                            <small class="text-muted">
                                Email
                            </small>

                            <div>

                                <?= htmlspecialchars($commande["email"]) ?>

                            </div>

                        </div>


                        <div class="mb-3">

                            <small class="text-muted">
                                Total
                            </small>

                            <div class="fw-bold text-orange">

                                <?= number_format(
                                    $commande["total"],
                                    0,
                                    ",",
                                    " "
                                ) ?>

                                FCFA

                            </div>

                        </div>


                        <div>

                            <small class="text-muted">
                                Date de commande
                            </small>

                            <div>

                                <?= date(
                                    "d/m/Y à H:i",
                                    strtotime($commande["date_commande"])
                                ) ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- Modification -->

            <div class="col-lg-6">

                <div class="card border-0 shadow-sm">

                    <div class="card-body p-4">


                        <h4 class="fw-bold mb-4">

                            <i class="bi bi-pencil-square me-2"></i>

                            État de la commande

                        </h4>


                        <form
                            action="modifier-commande.php?id=<?= $commande["id"] ?>"
                            method="POST"
                        >


                            <div class="mb-4">

                                <label
                                    for="statut"
                                    class="form-label fw-bold"
                                >

                                    Sélectionner le nouvel état

                                </label>


                                <select
                                    name="statut"
                                    id="statut"
                                    class="form-select form-select-lg"
                                    required
                                >

                                    <option
                                        value="En attente"
                                        <?= $commande["statut"] === "En attente" ? "selected" : "" ?>
                                    >

                                        🟡 En attente

                                    </option>


                                    <option
                                        value="En préparation"
                                        <?= $commande["statut"] === "En préparation" ? "selected" : "" ?>
                                    >

                                        🔵 En préparation

                                    </option>


                                    <option
                                        value="Expédiée"
                                        <?= $commande["statut"] === "Expédiée" ? "selected" : "" ?>
                                    >

                                        🚚 Expédiée

                                    </option>


                                    <option
                                        value="Livrée"
                                        <?= $commande["statut"] === "Livrée" ? "selected" : "" ?>
                                    >

                                        🟢 Livrée

                                    </option>


                                    <option
                                        value="Annulée"
                                        <?= $commande["statut"] === "Annulée" ? "selected" : "" ?>
                                    >

                                        🔴 Annulée

                                    </option>

                                </select>

                            </div>


                            <button
                                type="submit"
                                class="btn btn-dark btn-lg w-100"
                            >

                                <i class="bi bi-check-circle me-2"></i>

                                Enregistrer le nouvel état

                            </button>


                        </form>


                    </div>

                </div>

            </div>


        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>