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
| Vérifier l'identifiant de la commande
|--------------------------------------------------------------------------
*/

if (!isset($_GET["commande_id"]) || !is_numeric($_GET["commande_id"])) {
    header("Location: commandes.php");
    exit;
}

$commandeId = (int) $_GET["commande_id"];


/*
|--------------------------------------------------------------------------
| Récupérer la commande, le client et la facture
|--------------------------------------------------------------------------
*/

$requete = $pdo->prepare("
    SELECT
        commandes.id AS commande_id,
        commandes.utilisateur_id,
        utilisateurs.nom,
        utilisateurs.email,
        factures.id AS facture_id,
        factures.numero_facture,
        factures.montant_total,
        factures.montant_paye,
        factures.reste_a_payer
    FROM commandes
    INNER JOIN utilisateurs
        ON commandes.utilisateur_id = utilisateurs.id
    INNER JOIN factures
        ON factures.commande_id = commandes.id
    WHERE commandes.id = ?
    LIMIT 1
");

$requete->execute([$commandeId]);

$info = $requete->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Pas de facture : rien à payer ici
|--------------------------------------------------------------------------
*/

if (!$info) {
    header("Location: commande-details.php?id=" . $commandeId);
    exit;
}


/*
|--------------------------------------------------------------------------
| Dette déjà réglée
|--------------------------------------------------------------------------
*/

if ((float) $info["reste_a_payer"] <= 0) {

    $succes = "Cette facture est déjà entièrement payée.";

}


/*
|--------------------------------------------------------------------------
| Enregistrer le paiement
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verifier();


    $montant = $_POST["montant"] ?? "";
    $commentaire = trim($_POST["commentaire"] ?? "");

    $resteActuel = (float) $info["reste_a_payer"];


    /*
    |--------------------------------------------------------------------------
    | Vérifications
    |--------------------------------------------------------------------------
    */

    if ($resteActuel <= 0) {

        $erreur = "Cette facture est déjà entièrement payée.";

    } elseif ($montant === "") {

        $erreur = "Veuillez saisir le montant du paiement.";

    } elseif (!is_numeric($montant) || $montant <= 0) {

        $erreur = "Le montant du paiement est invalide.";

    } elseif ((float) $montant > $resteActuel) {

        $erreur = "Le paiement ne peut pas dépasser le reste à payer.";

    } else {

        $montant = (float) $montant;

        try {

            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | 1. Verrouiller et mettre à jour la facture
            |--------------------------------------------------------------------------
            */

            $requeteFactureLock = $pdo->prepare("
                SELECT id, montant_total, montant_paye
                FROM factures
                WHERE id = ?
                FOR UPDATE
            ");

            $requeteFactureLock->execute([$info["facture_id"]]);

            $factureActuelle = $requeteFactureLock->fetch(PDO::FETCH_ASSOC);

            $nouveauMontantPayeFacture =
                (float) $factureActuelle["montant_paye"] + $montant;

            if ($nouveauMontantPayeFacture > (float) $factureActuelle["montant_total"]) {
                $nouveauMontantPayeFacture = (float) $factureActuelle["montant_total"];
            }

            $nouveauResteFacture =
                (float) $factureActuelle["montant_total"] - $nouveauMontantPayeFacture;

            if ($nouveauResteFacture < 0) {
                $nouveauResteFacture = 0;
            }

            if ($nouveauResteFacture <= 0) {
                $statutFacture = "Payée";
            } elseif ($nouveauMontantPayeFacture > 0) {
                $statutFacture = "Partiellement payée";
            } else {
                $statutFacture = "Impayée";
            }

            $requeteUpdateFacture = $pdo->prepare("
                UPDATE factures
                SET
                    montant_paye = ?,
                    reste_a_payer = ?,
                    statut = ?
                WHERE id = ?
            ");

            $requeteUpdateFacture->execute([
                $nouveauMontantPayeFacture,
                $nouveauResteFacture,
                $statutFacture,
                $info["facture_id"]
            ]);


            /*
            |--------------------------------------------------------------------------
            | 2. Synchroniser un éventuel crédit client existant
            |--------------------------------------------------------------------------
            */

            $requeteCredit = $pdo->prepare("
                SELECT id, montant_total, montant_paye, reste_a_payer
                FROM credits_clients
                WHERE commande_id = ?
                ORDER BY id DESC
                LIMIT 1
                FOR UPDATE
            ");

            $requeteCredit->execute([$commandeId]);

            $credit = $requeteCredit->fetch(PDO::FETCH_ASSOC);

            if ($credit) {

                /*
                | Un crédit existe déjà pour cette commande : on le met à jour.
                */

                $nouveauMontantPayeCredit =
                    (float) $credit["montant_paye"] + $montant;

                if ($nouveauMontantPayeCredit > (float) $credit["montant_total"]) {
                    $nouveauMontantPayeCredit = (float) $credit["montant_total"];
                }

                $nouveauResteCredit =
                    (float) $credit["montant_total"] - $nouveauMontantPayeCredit;

                if ($nouveauResteCredit < 0) {
                    $nouveauResteCredit = 0;
                }

                $statutCredit = $nouveauResteCredit <= 0 ? "Réglé" : "En cours";

                $requeteUpdateCredit = $pdo->prepare("
                    UPDATE credits_clients
                    SET
                        montant_paye = ?,
                        reste_a_payer = ?,
                        statut = ?
                    WHERE id = ?
                ");

                $requeteUpdateCredit->execute([
                    $nouveauMontantPayeCredit,
                    $nouveauResteCredit,
                    $statutCredit,
                    $credit["id"]
                ]);

                $requetePaiement = $pdo->prepare("
                    INSERT INTO paiements_credits
                    (credit_id, montant, commentaire)
                    VALUES (?, ?, ?)
                ");

                $requetePaiement->execute([
                    $credit["id"],
                    $montant,
                    $commentaire !== "" ? $commentaire : "Paiement enregistré depuis la commande"
                ]);

            } elseif ($nouveauResteFacture > 0) {

                /*
                | Pas encore de crédit, mais il reste un solde après ce
                | paiement : on crée le crédit pour garder une trace du
                | solde restant, comme lors d'une vente en boutique.
                */

                $requeteNouveauCredit = $pdo->prepare("
                    INSERT INTO credits_clients
                    (utilisateur_id, commande_id, montant_total, montant_paye, reste_a_payer, statut)
                    VALUES (?, ?, ?, ?, ?, 'En cours')
                ");

                $requeteNouveauCredit->execute([
                    $info["utilisateur_id"],
                    $commandeId,
                    (float) $factureActuelle["montant_total"],
                    $nouveauMontantPayeFacture,
                    $nouveauResteFacture
                ]);

                $nouveauCreditId = $pdo->lastInsertId();

                $requetePaiement = $pdo->prepare("
                    INSERT INTO paiements_credits
                    (credit_id, montant, commentaire)
                    VALUES (?, ?, ?)
                ");

                $requetePaiement->execute([
                    $nouveauCreditId,
                    $montant,
                    $commentaire !== "" ? $commentaire : "Paiement enregistré depuis la commande"
                ]);
            }

            /*
            | Si le reste à payer tombe à 0 et qu'aucun crédit n'existait,
            | rien de plus à faire : la commande est simplement payée
            | intégralement, sans jamais avoir été une dette.
            */


            $pdo->commit();

            header("Location: commande-details.php?id=" . $commandeId . "&paiement=1");
            exit;

        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $erreur = "Une erreur est survenue lors de l'enregistrement du paiement.";
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

    <title>Enregistrer un paiement - NPM Admin</title>


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
                    Enregistrer un paiement
                </h1>

                <p class="text-muted mb-0">
                    Paiement immédiat pour la commande <?= $commandeId ?>.
                </p>

            </div>


            <a
                href="commande-details.php?id=<?= $commandeId ?>"
                class="btn btn-dark"
            >

                <i class="bi bi-arrow-left me-2"></i>

                Retour à la commande

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
                            <i class="bi bi-receipt me-2"></i>
                            Résumé de la facture
                        </h5>

                    </div>

                    <div class="card-body">

                        <div class="mb-4">

                            <small class="text-muted">Client</small>

                            <div class="fw-bold fs-5">
                                <?= htmlspecialchars($info["nom"]) ?>
                            </div>

                            <?php if (!empty($info["email"])): ?>
                                <small class="text-muted">
                                    <?= htmlspecialchars($info["email"]) ?>
                                </small>
                            <?php endif; ?>

                        </div>


                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Facture</span>
                            <strong><?= htmlspecialchars($info["numero_facture"]) ?></strong>
                        </div>


                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Commande</span>
                            <strong><?= $commandeId ?></strong>
                        </div>


                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Montant total</span>
                            <strong>
                                <?= number_format((float) $info["montant_total"], 0, ",", " ") ?> FCFA
                            </strong>
                        </div>


                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Déjà payé</span>
                            <strong class="text-success">
                                <?= number_format((float) $info["montant_paye"], 0, ",", " ") ?> FCFA
                            </strong>
                        </div>


                        <hr>


                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Reste à payer</span>
                            <strong class="text-danger fs-4">
                                <?= number_format((float) $info["reste_a_payer"], 0, ",", " ") ?> FCFA
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
                            <i class="bi bi-cash-coin me-2"></i>
                            Nouveau paiement
                        </h5>

                    </div>

                    <div class="card-body p-4">

                        <?php if ((float) $info["reste_a_payer"] > 0): ?>

                            <form method="POST">
                                <?= csrf_champ() ?>

                                <div class="mb-4">

                                    <label for="montant" class="form-label fw-semibold">
                                        Montant reçu maintenant
                                        <span class="text-danger">*</span>
                                    </label>

                                    <div class="input-group input-group-lg">

                                        <input
                                            type="number"
                                            name="montant"
                                            id="montant"
                                            class="form-control"
                                            min="1"
                                            max="<?= (float) $info["reste_a_payer"] ?>"
                                            step="1"
                                            required
                                        >

                                        <span class="input-group-text">FCFA</span>

                                    </div>

                                    <small class="text-muted">
                                        Maximum :
                                        <?= number_format((float) $info["reste_a_payer"], 0, ",", " ") ?> FCFA
                                    </small>

                                    <div class="mt-2">

                                        <button
                                            type="button"
                                            id="btnMontantTotal"
                                            class="btn btn-sm btn-outline-dark"
                                        >
                                            Client paie tout le reste
                                        </button>

                                    </div>

                                </div>


                                <div class="alert alert-warning">

                                    <div class="d-flex justify-content-between">
                                        <span>Reste après ce paiement</span>
                                        <strong id="nouveauReste">
                                            <?= number_format((float) $info["reste_a_payer"], 0, ",", " ") ?> FCFA
                                        </strong>
                                    </div>

                                </div>


                                <div class="mb-4">

                                    <label for="commentaire" class="form-label fw-semibold">
                                        Commentaire
                                    </label>

                                    <textarea
                                        name="commentaire"
                                        id="commentaire"
                                        class="form-control"
                                        rows="3"
                                        placeholder="Exemple : Paiement en espèces à la boutique..."
                                    ></textarea>

                                </div>


                                <div class="d-flex justify-content-end gap-2">

                                    <a
                                        href="commande-details.php?id=<?= $commandeId ?>"
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

                                <h4 class="fw-bold mt-3">Facture payée</h4>

                                <p class="text-muted">
                                    Cette facture ne possède plus de montant à payer.
                                </p>

                                <a
                                    href="commande-details.php?id=<?= $commandeId ?>"
                                    class="btn btn-dark"
                                >
                                    Voir la commande
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
const btnTotal = document.getElementById("btnMontantTotal");
const resteActuel = <?= (float) $info["reste_a_payer"] ?>;

function majReste() {

    const paiement = parseFloat(montant.value) || 0;

    let nouveauReste = resteActuel - paiement;

    if (nouveauReste < 0) {
        nouveauReste = 0;
    }

    document.getElementById("nouveauReste").textContent =
        nouveauReste.toLocaleString("fr-FR") + " FCFA";
}

if (montant) {

    montant.addEventListener("input", majReste);

    if (btnTotal) {

        btnTotal.addEventListener("click", function () {
            montant.value = resteActuel;
            majReste();
        });
    }
}

</script>


</body>

</html>
