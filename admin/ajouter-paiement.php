<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("includes/auth.php");
require_once("../config/database.php");

$erreur = "";
$succes = "";


/*
|--------------------------------------------------------------------------
| Vérifier l'ID du crédit
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: credits.php");
    exit;
}

$credit_id = (int) $_GET["id"];


/*
|--------------------------------------------------------------------------
| Récupérer le crédit
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare("
    SELECT
        credits_clients.*,
        utilisateurs.nom,
        utilisateurs.email
    FROM credits_clients
    INNER JOIN utilisateurs
        ON credits_clients.utilisateur_id = utilisateurs.id
    WHERE credits_clients.id = ?
");

$requete->execute([$credit_id]);

$credit = $requete->fetch(PDO::FETCH_ASSOC);


if (!$credit) {
    header("Location: credits.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Vérifier si la dette est déjà réglée
|--------------------------------------------------------------------------
*/

if ((float) $credit["reste_a_payer"] <= 0) {

    $succes = "Cette dette est déjà entièrement réglée.";

}


/*
|--------------------------------------------------------------------------
| Ajouter le paiement
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verifier();


    $montant = $_POST["montant"] ?? "";
    $commentaire = trim($_POST["commentaire"] ?? "");

    $resteActuel = (float) $credit["reste_a_payer"];


    /*
    |--------------------------------------------------------------------------
    | Vérifications
    |--------------------------------------------------------------------------
    */

    if ($resteActuel <= 0) {

        $erreur = "Cette dette est déjà réglée.";

    } elseif ($montant === "") {

        $erreur = "Veuillez saisir le montant du paiement.";

    } elseif (!is_numeric($montant) || $montant <= 0) {

        $erreur = "Le montant du paiement est invalide.";

    } elseif ((float) $montant > $resteActuel) {

        $erreur = "Le paiement ne peut pas dépasser le reste à payer.";

    } else {

        $montant = (float) $montant;


        /*
        |--------------------------------------------------------------------------
        | Nouveau montant payé
        |--------------------------------------------------------------------------
        */

        $nouveauMontantPaye =
            (float) $credit["montant_paye"] + $montant;


        /*
        |--------------------------------------------------------------------------
        | Nouveau reste
        |--------------------------------------------------------------------------
        */

        $nouveauReste =
            (float) $credit["montant_total"] - $nouveauMontantPaye;


        if ($nouveauReste < 0) {
            $nouveauReste = 0;
        }


        /*
        |--------------------------------------------------------------------------
        | Nouveau statut du crédit
        |--------------------------------------------------------------------------
        */

        if ($nouveauReste <= 0) {

            $statut = "Réglé";

        } else {

            $statut = "En cours";
        }


        /*
        |--------------------------------------------------------------------------
        | TRANSACTION
        |--------------------------------------------------------------------------
        */

        try {

            $pdo->beginTransaction();


            /*
            |------------------------------------------------------------------
            | 1. Enregistrer le paiement du crédit
            |------------------------------------------------------------------
            */

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
                $montant,
                $commentaire !== ""
                    ? $commentaire
                    : "Paiement de la dette"
            ]);


            /*
            |------------------------------------------------------------------
            | 2. Mettre à jour le crédit
            |------------------------------------------------------------------
            */

            $requeteCredit = $pdo->prepare("
                UPDATE credits_clients
                SET
                    montant_paye = ?,
                    reste_a_payer = ?,
                    statut = ?
                WHERE id = ?
            ");

            $requeteCredit->execute([
                $nouveauMontantPaye,
                $nouveauReste,
                $statut,
                $credit_id
            ]);


            /*
            |--------------------------------------------------------------------------
            | 3. METTRE À JOUR LA FACTURE
            |--------------------------------------------------------------------------
            |
            | Le crédit possède éventuellement une commande_id.
            | Si cette commande possède une facture, on la met à jour.
            |
            */

            if (
                !empty($credit["commande_id"]) &&
                is_numeric($credit["commande_id"])
            ) {

                $commandeId = (int) $credit["commande_id"];


                /*
                |------------------------------------------------------------------
                | Récupérer la facture liée à la commande
                |------------------------------------------------------------------
                */

                $requeteFacture = $pdo->prepare("
                    SELECT
                        id,
                        montant_total,
                        montant_paye
                    FROM factures
                    WHERE commande_id = ?
                    LIMIT 1
                    FOR UPDATE
                ");

                $requeteFacture->execute([
                    $commandeId
                ]);

                $facture = $requeteFacture->fetch(PDO::FETCH_ASSOC);


                /*
                |------------------------------------------------------------------
                | Si une facture existe
                |------------------------------------------------------------------
                */

                if ($facture) {

                    $factureTotal =
                        (float) $facture["montant_total"];

                    $factureMontantPaye =
                        (float) $facture["montant_paye"] + $montant;


                    /*
                    | Ne jamais dépasser le total
                    */

                    if ($factureMontantPaye > $factureTotal) {

                        $factureMontantPaye = $factureTotal;

                    }


                    $factureReste =
                        $factureTotal - $factureMontantPaye;


                    if ($factureReste < 0) {
                        $factureReste = 0;
                    }


                    /*
                    |------------------------------------------------------------------
                    | Statut de la facture
                    |------------------------------------------------------------------
                    */

                    if ($factureReste <= 0) {

                        $statutFacture = "Payée";

                    } elseif ($factureMontantPaye > 0) {

                        $statutFacture = "Partiellement payée";

                    } else {

                        $statutFacture = "Impayée";
                    }


                    /*
                    |------------------------------------------------------------------
                    | Mettre à jour la facture
                    |------------------------------------------------------------------
                    */

                    $requeteUpdateFacture = $pdo->prepare("
                        UPDATE factures
                        SET
                            montant_paye = ?,
                            reste_a_payer = ?,
                            statut = ?
                        WHERE id = ?
                    ");

                    $requeteUpdateFacture->execute([
                        $factureMontantPaye,
                        $factureReste,
                        $statutFacture,
                        $facture["id"]
                    ]);
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Valider la transaction
            |--------------------------------------------------------------------------
            */

            $pdo->commit();


            /*
            |--------------------------------------------------------------------------
            | Message de succès
            |--------------------------------------------------------------------------
            */

            $succes = "Le paiement a été enregistré avec succès.";


            if ($statut === "Réglé") {

                $succes .= " La dette est maintenant entièrement réglée.";

            }


            /*
            |--------------------------------------------------------------------------
            | Mettre à jour les informations affichées
            |--------------------------------------------------------------------------
            */

            $credit["montant_paye"] =
                $nouveauMontantPaye;

            $credit["reste_a_payer"] =
                $nouveauReste;

            $credit["statut"] =
                $statut;


        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $erreur =
                "Une erreur est survenue lors de l'enregistrement du paiement.";
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

    <title>Ajouter un paiement - NPM Admin</title>


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


<body class="bg-light">


<?php require_once("includes/navbar.php"); ?>

<?php require_once("includes/sidebar.php"); ?>



<main class="admin-content">

    <div class="container-fluid py-4">


        <!-- En-tête -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1 class="fw-bold mb-1">
                    Ajouter un paiement
                </h1>

                <p class="text-muted mb-0">
                    Enregistrer un paiement de dette.
                </p>

            </div>


            <a
                href="details-credit.php?id=<?= $credit_id ?>"
                class="btn btn-dark"
            >

                <i class="bi bi-arrow-left me-2"></i>

                Retour au crédit

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


        <div class="row g-4">


            <!-- Résumé -->

            <div class="col-lg-5">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white py-3">

                        <h5 class="fw-bold mb-0">

                            <i class="bi bi-credit-card me-2"></i>

                            Résumé de la dette

                        </h5>

                    </div>


                    <div class="card-body">


                        <!-- Client -->

                        <div class="mb-4">

                            <small class="text-muted">
                                Client
                            </small>

                            <div class="fw-bold fs-5">

                                <?= htmlspecialchars(
                                    $credit["nom"]
                                ) ?>

                            </div>

                            <small class="text-muted">

                                <?= htmlspecialchars(
                                    $credit["email"]
                                ) ?>

                            </small>

                        </div>


                        <!-- Crédit -->

                        <div class="d-flex justify-content-between mb-3">

                            <span class="text-muted">
                                Crédit
                            </span>

                            <strong>
                                <?= $credit["id"] ?>
                            </strong>

                        </div>


                        <!-- Commande -->

                        <?php if (!empty($credit["commande_id"])): ?>

                            <div class="d-flex justify-content-between mb-3">

                                <span class="text-muted">
                                    Commande
                                </span>

                                <strong>
                                    <?= $credit["commande_id"] ?>
                                </strong>

                            </div>

                        <?php endif; ?>


                        <!-- Total -->

                        <div class="d-flex justify-content-between mb-3">

                            <span class="text-muted">
                                Montant total
                            </span>

                            <strong>

                                <?= number_format(
                                    $credit["montant_total"],
                                    0,
                                    ",",
                                    " "
                                ) ?>

                                FCFA

                            </strong>

                        </div>


                        <!-- Payé -->

                        <div class="d-flex justify-content-between mb-3">

                            <span class="text-muted">
                                Déjà payé
                            </span>

                            <strong class="text-success">

                                <?= number_format(
                                    $credit["montant_paye"],
                                    0,
                                    ",",
                                    " "
                                ) ?>

                                FCFA

                            </strong>

                        </div>


                        <hr>


                        <!-- Reste -->

                        <div class="d-flex justify-content-between align-items-center">

                            <span class="fw-semibold">
                                Reste à payer
                            </span>

                            <strong class="text-danger fs-4">

                                <?= number_format(
                                    $credit["reste_a_payer"],
                                    0,
                                    ",",
                                    " "
                                ) ?>

                                FCFA

                            </strong>

                        </div>


                    </div>

                </div>

            </div>


            <!-- Formulaire -->

            <div class="col-lg-7">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white py-3">

                        <h5 class="fw-bold mb-0">

                            <i class="bi bi-wallet2 me-2"></i>

                            Nouveau paiement

                        </h5>

                    </div>


                    <div class="card-body p-4">


                        <?php if ((float) $credit["reste_a_payer"] > 0): ?>


                            <form method="POST">
                                <?= csrf_champ() ?>


                                <!-- Montant -->

                                <div class="mb-4">

                                    <label
                                        for="montant"
                                        class="form-label fw-semibold"
                                    >

                                        Montant du paiement

                                        <span class="text-danger">*</span>

                                    </label>


                                    <div class="input-group input-group-lg">

                                        <input
                                            type="number"
                                            name="montant"
                                            id="montant"
                                            class="form-control"
                                            min="1"
                                            max="<?= $credit["reste_a_payer"] ?>"
                                            step="1"
                                            required
                                        >

                                        <span class="input-group-text">
                                            FCFA
                                        </span>

                                    </div>


                                    <small class="text-muted">

                                        Maximum :

                                        <?= number_format(
                                            $credit["reste_a_payer"],
                                            0,
                                            ",",
                                            " "
                                        ) ?>

                                        FCFA

                                    </small>

                                </div>


                                <!-- Reste -->

                                <div class="alert alert-warning">

                                    <div class="d-flex justify-content-between">

                                        <span>
                                            Reste après paiement
                                        </span>

                                        <strong id="nouveauReste">

                                            <?= number_format(
                                                $credit["reste_a_payer"],
                                                0,
                                                ",",
                                                " "
                                            ) ?>

                                            FCFA

                                        </strong>

                                    </div>

                                </div>


                                <!-- Commentaire -->

                                <div class="mb-4">

                                    <label
                                        for="commentaire"
                                        class="form-label fw-semibold"
                                    >

                                        Commentaire

                                    </label>


                                    <textarea
                                        name="commentaire"
                                        id="commentaire"
                                        class="form-control"
                                        rows="4"
                                        placeholder="Exemple : Paiement en espèces..."
                                    ></textarea>

                                </div>


                                <!-- Boutons -->

                                <div class="d-flex justify-content-end gap-2">

                                    <a
                                        href="details-credit.php?id=<?= $credit_id ?>"
                                        class="btn btn-outline-secondary"
                                    >

                                        Annuler

                                    </a>


                                    <button
                                        type="submit"
                                        class="btn btn-warning px-4"
                                    >

                                        <i class="bi bi-check-lg me-2"></i>

                                        Enregistrer le paiement

                                    </button>

                                </div>


                            </form>


                        <?php else: ?>


                            <div class="text-center py-4">

                                <i class="bi bi-check-circle text-success fs-1"></i>

                                <h4 class="fw-bold mt-3">
                                    Dette réglée
                                </h4>

                                <p class="text-muted">

                                    Cette dette ne possède plus de montant à payer.

                                </p>


                                <a
                                    href="details-credit.php?id=<?= $credit_id ?>"
                                    class="btn btn-dark"
                                >

                                    Voir les détails

                                </a>

                            </div>


                        <?php endif; ?>


                    </div>

                </div>

            </div>


        </div>

    </div>

</main>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


<script>

const montant = document.getElementById("montant");

if (montant) {

    montant.addEventListener("input", function () {

        const resteActuel =
            <?= (float) $credit["reste_a_payer"] ?>;

        const paiement =
            parseFloat(this.value) || 0;

        let nouveauReste =
            resteActuel - paiement;

        if (nouveauReste < 0) {
            nouveauReste = 0;
        }

        document.getElementById("nouveauReste").textContent =
            nouveauReste.toLocaleString("fr-FR") + " FCFA";

    });

}

</script>


</body>

</html>
