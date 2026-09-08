<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../config/database.php");

$erreur = "";
$succes = "";


/*
|--------------------------------------------------------------------------
| Récupérer les clients
|--------------------------------------------------------------------------
*/

$requeteClients = $pdo->query("
    SELECT id, nom, email
    FROM utilisateurs
    ORDER BY nom ASC
");

$clients = $requeteClients->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Récupérer les commandes
|--------------------------------------------------------------------------
*/

$requeteCommandes = $pdo->query("
    SELECT 
        commandes.id,
        commandes.utilisateur_id,
        commandes.total,
        utilisateurs.nom
    FROM commandes
    INNER JOIN utilisateurs
        ON commandes.utilisateur_id = utilisateurs.id
    ORDER BY commandes.id DESC
");

$commandes = $requeteCommandes->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Ajouter un crédit
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $utilisateur_id = $_POST["utilisateur_id"] ?? "";
    $commande_id = $_POST["commande_id"] ?? "";
    $montant_total = $_POST["montant_total"] ?? "";
    $montant_paye = $_POST["montant_paye"] ?? "";
    $commentaire = trim($_POST["commentaire"] ?? "");


    /*
    |--------------------------------------------------------------------------
    | Vérifications
    |--------------------------------------------------------------------------
    */

    if (
        $utilisateur_id === "" ||
        $montant_total === "" ||
        $montant_paye === ""
    ) {

        $erreur = "Veuillez remplir tous les champs obligatoires.";

    } elseif (
        !is_numeric($montant_total) ||
        $montant_total <= 0
    ) {

        $erreur = "Le montant total est invalide.";

    } elseif (
        !is_numeric($montant_paye) ||
        $montant_paye < 0
    ) {

        $erreur = "Le montant payé est invalide.";

    } elseif ((float) $montant_paye > (float) $montant_total) {

        $erreur = "Le montant payé ne peut pas être supérieur au montant total.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Calcul du reste
        |--------------------------------------------------------------------------
        */

        $montant_total = (float) $montant_total;
        $montant_paye = (float) $montant_paye;

        $reste_a_payer = $montant_total - $montant_paye;


        /*
        |--------------------------------------------------------------------------
        | Déterminer le statut
        |--------------------------------------------------------------------------
        */

        if ($reste_a_payer <= 0) {

            $statut = "Réglé";

        } else {

            $statut = "En cours";
        }


        /*
        |--------------------------------------------------------------------------
        | Commande
        |--------------------------------------------------------------------------
        */

        $commande_id = $commande_id !== ""
            ? (int) $commande_id
            : null;


        /*
        |--------------------------------------------------------------------------
        | Enregistrer le crédit
        |--------------------------------------------------------------------------
        */

        $requete = $pdo->prepare("
            INSERT INTO credits_clients
            (
                utilisateur_id,
                commande_id,
                montant_total,
                montant_paye,
                reste_a_payer,
                statut
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $requete->execute([
            (int) $utilisateur_id,
            $commande_id,
            $montant_total,
            $montant_paye,
            $reste_a_payer,
            $statut
        ]);


        /*
        |--------------------------------------------------------------------------
        | Enregistrer le paiement initial
        |--------------------------------------------------------------------------
        */

        if ($montant_paye > 0) {

            $credit_id = $pdo->lastInsertId();

            $requetePaiement = $pdo->prepare("
                INSERT INTO paiements_credits
                (
                    credit_id,
                    montant,
                    commentaire
                )
                VALUES (?, ?, ?)
            ");

            $requetePaiement->execute([
                $credit_id,
                $montant_paye,
                $commentaire !== "" ? $commentaire : "Paiement initial"
            ]);
        }


        $succes = "La dette du client a été ajoutée avec succès.";

        /*
        |--------------------------------------------------------------------------
        | Réinitialiser le formulaire
        |--------------------------------------------------------------------------
        */

        $utilisateur_id = "";
        $commande_id = "";
        $montant_total = "";
        $montant_paye = "";
        $commentaire = "";
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

    <title>Ajouter une dette - NPM Admin</title>


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


    <!-- CSS du projet -->

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body class="bg-light">


<?php require_once("includes/navbar.php"); ?>

<?php require_once("includes/sidebar.php"); ?>


<main class="admin-content">

    <div class="container-fluid py-4">


        <!-- En-tête -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1 class="fw-bold mb-1">

                    Ajouter une dette

                </h1>

                <p class="text-muted mb-0">

                    Enregistrer un nouveau crédit client.

                </p>

            </div>


            <a
                href="credits.php"
                class="btn btn-dark"
            >

                <i class="bi bi-arrow-left me-2"></i>

                Retour aux crédits

            </a>

        </div>


        <!-- Messages -->

        <?php if ($erreur !== ""): ?>

            <div class="alert alert-danger">

                <i class="bi bi-exclamation-triangle me-2"></i>

                <?= htmlspecialchars($erreur) ?>

            </div>

        <?php endif; ?>


        <?php if ($succes !== ""): ?>

            <div class="alert alert-success">

                <i class="bi bi-check-circle me-2"></i>

                <?= htmlspecialchars($succes) ?>

            </div>

        <?php endif; ?>


        <!-- Formulaire -->

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0">

                    <i class="bi bi-credit-card me-2"></i>

                    Informations de la dette

                </h5>

            </div>


            <div class="card-body p-4">

                <form method="POST">


                    <div class="row g-4">


                        <!-- Client -->

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">

                                Client <span class="text-danger">*</span>

                            </label>


                            <select
                                name="utilisateur_id"
                                id="utilisateur_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Sélectionner un client
                                </option>


                                <?php foreach ($clients as $client): ?>

                                    <option
                                        value="<?= $client["id"] ?>"
                                        <?= isset($utilisateur_id) && $utilisateur_id == $client["id"] ? "selected" : "" ?>
                                    >

                                        <?= htmlspecialchars($client["nom"]) ?>

                                        —
                                        <?= htmlspecialchars($client["email"]) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- Commande -->

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">

                                Commande concernée

                            </label>


                            <select
                                name="commande_id"
                                class="form-select"
                            >

                                <option value="">
                                    Aucune commande
                                </option>


                                <?php foreach ($commandes as $commande): ?>

                                    <option
                                        value="<?= $commande["id"] ?>"
                                        <?= isset($commande_id) && $commande_id == $commande["id"] ? "selected" : "" ?>
                                    >

                                        Commande #<?= $commande["id"] ?>

                                        —
                                        <?= htmlspecialchars($commande["nom"]) ?>

                                        —
                                        <?= number_format(
                                            $commande["total"],
                                            0,
                                            ",",
                                            " "
                                        ) ?>
                                        FCFA

                                    </option>

                                <?php endforeach; ?>

                            </select>

                            <small class="text-muted">

                                Facultatif

                            </small>

                        </div>


                        <!-- Montant total -->

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">

                                Montant total

                                <span class="text-danger">*</span>

                            </label>


                            <div class="input-group">

                                <input
                                    type="number"
                                    name="montant_total"
                                    id="montant_total"
                                    class="form-control"
                                    min="1"
                                    step="1"
                                    value="<?= htmlspecialchars($montant_total ?? "") ?>"
                                    required
                                >

                                <span class="input-group-text">
                                    FCFA
                                </span>

                            </div>

                        </div>


                        <!-- Montant payé -->

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">

                                Montant déjà payé

                                <span class="text-danger">*</span>

                            </label>


                            <div class="input-group">

                                <input
                                    type="number"
                                    name="montant_paye"
                                    id="montant_paye"
                                    class="form-control"
                                    min="0"
                                    step="1"
                                    value="<?= htmlspecialchars($montant_paye ?? "0") ?>"
                                    required
                                >

                                <span class="input-group-text">
                                    FCFA
                                </span>

                            </div>

                        </div>


                        <!-- Reste -->

                        <div class="col-12">

                            <div class="alert alert-warning mb-0">

                                <div class="d-flex justify-content-between align-items-center">

                                    <span class="fw-semibold">

                                        <i class="bi bi-wallet2 me-2"></i>

                                        Reste à payer

                                    </span>


                                    <strong
                                        id="reste"
                                        class="fs-5"
                                    >

                                        0 FCFA

                                    </strong>

                                </div>

                            </div>

                        </div>


                        <!-- Commentaire -->

                        <div class="col-12">

                            <label class="form-label fw-semibold">

                                Commentaire

                            </label>


                            <textarea
                                name="commentaire"
                                class="form-control"
                                rows="4"
                                placeholder="Exemple : Paiement en espèces..."
                            ><?= htmlspecialchars($commentaire ?? "") ?></textarea>

                        </div>


                    </div>


                    <!-- Boutons -->

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">

                        <a
                            href="credits.php"
                            class="btn btn-outline-secondary"
                        >

                            Annuler

                        </a>


                        <button
                            type="submit"
                            class="btn btn-warning px-4"
                        >

                            <i class="bi bi-plus-lg me-2"></i>

                            Ajouter la dette

                        </button>

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


<!-- Calcul du reste -->

<script>

function calculerReste() {

    const total = parseFloat(
        document.getElementById("montant_total").value
    ) || 0;

    const paye = parseFloat(
        document.getElementById("montant_paye").value
    ) || 0;

    let reste = total - paye;

    if (reste < 0) {
        reste = 0;
    }

    document.getElementById("reste").textContent =
        reste.toLocaleString("fr-FR") + " FCFA";
}


document
    .getElementById("montant_total")
    .addEventListener("input", calculerReste);


document
    .getElementById("montant_paye")
    .addEventListener("input", calculerReste);


calculerReste();

</script>


</body>

</html>