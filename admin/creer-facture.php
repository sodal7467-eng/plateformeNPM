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
| Générer un numéro de facture
|--------------------------------------------------------------------------
*/

function genererNumeroFacture($pdo)
{
    $annee = date("Y");

    $requete = $pdo->query("
        SELECT id
        FROM factures
        ORDER BY id DESC
        LIMIT 1
    ");

    $derniereFacture = $requete->fetch(PDO::FETCH_ASSOC);

    if ($derniereFacture) {
        $numero = (int) $derniereFacture["id"] + 1;
    } else {
        $numero = 1;
    }

    return "FAC-" . $annee . "-" . str_pad(
        $numero,
        5,
        "0",
        STR_PAD_LEFT
    );
}


/*
|--------------------------------------------------------------------------
| Traitement du formulaire
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verifier();


    $commandeId = isset($_POST["commande_id"])
        ? (int) $_POST["commande_id"]
        : 0;


    /*
    |--------------------------------------------------------------------------
    | Vérifier la commande
    |--------------------------------------------------------------------------
    */

    if ($commandeId <= 0) {

        $erreur = "Veuillez sélectionner une commande.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Vérifier si une facture existe déjà
        |--------------------------------------------------------------------------
        */

        $requeteExistante = $pdo->prepare("
            SELECT id, numero_facture
            FROM factures
            WHERE commande_id = ?
            LIMIT 1
        ");

        $requeteExistante->execute([$commandeId]);

        $factureExistante = $requeteExistante->fetch(PDO::FETCH_ASSOC);


        if ($factureExistante) {

            $erreur =
                "Cette commande possède déjà une facture : "
                . $factureExistante["numero_facture"];


        } else {

            /*
            |--------------------------------------------------------------------------
            | Récupérer la commande
            |--------------------------------------------------------------------------
            */

            $requeteCommande = $pdo->prepare("
                SELECT
                    c.id,
                    c.utilisateur_id,
                    c.total,
                    c.statut,
                    c.date_commande,
                    u.nom AS client_nom,
                    u.email AS client_email

                FROM commandes c

                INNER JOIN utilisateurs u
                    ON c.utilisateur_id = u.id

                WHERE c.id = ?

                LIMIT 1
            ");

            $requeteCommande->execute([$commandeId]);

            $commande = $requeteCommande->fetch(PDO::FETCH_ASSOC);


            if (!$commande) {

                $erreur = "La commande sélectionnée n'existe pas.";

            } else {

                /*
                |--------------------------------------------------------------------------
                | Calcul des montants
                |--------------------------------------------------------------------------
                */

                $montantTotal = (float) $commande["total"];

                $montantPaye = 0;

                $resteAPayer = $montantTotal;

                $statutFacture = "Impayée";


                /*
                |--------------------------------------------------------------------------
                | Générer le numéro
                |--------------------------------------------------------------------------
                */

                $numeroFacture = genererNumeroFacture($pdo);


                /*
                |--------------------------------------------------------------------------
                | Créer la facture
                |--------------------------------------------------------------------------
                */

                try {

                    $pdo->beginTransaction();


                    $requeteInsertion = $pdo->prepare("
                        INSERT INTO factures (
                            commande_id,
                            numero_facture,
                            montant_total,
                            montant_paye,
                            reste_a_payer,
                            statut
                        )

                        VALUES (?, ?, ?, ?, ?, ?)
                    ");


                    $requeteInsertion->execute([
                        $commandeId,
                        $numeroFacture,
                        $montantTotal,
                        $montantPaye,
                        $resteAPayer,
                        $statutFacture
                    ]);


                    $factureId = $pdo->lastInsertId();


                    $pdo->commit();


                    /*
                    |--------------------------------------------------------------------------
                    | Redirection vers la facture
                    |--------------------------------------------------------------------------
                    */

                    header(
                        "Location: facture.php?id=" . $factureId
                    );

                    exit;


                } catch (Exception $e) {

                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }

                    $erreur =
                        "Une erreur est survenue lors de la création de la facture.";
                }
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| Récupérer les commandes qui n'ont pas encore de facture
|--------------------------------------------------------------------------
*/

$requeteCommandes = $pdo->query("
    SELECT
        c.id,
        c.total,
        c.statut,
        c.date_commande,
        u.nom AS client_nom,
        u.email AS client_email

    FROM commandes c

    INNER JOIN utilisateurs u
        ON c.utilisateur_id = u.id

    LEFT JOIN factures f
        ON f.commande_id = c.id

    WHERE f.id IS NULL

    ORDER BY c.date_commande DESC
");

$commandes = $requeteCommandes->fetchAll(PDO::FETCH_ASSOC);

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
        Créer une facture - Administration NPM
    </title>


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


    <!-- CSS principal -->

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body>


<?php require_once("includes/navbar.php"); ?>


<div class="admin-layout">


    <?php require_once("includes/sidebar.php"); ?>


    <main class="admin-content">


        <!-- =====================================================
             EN-TÊTE
        ====================================================== -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1 class="fw-bold mb-1">

                    <i class="bi bi-receipt me-2"></i>

                    Créer une facture

                </h1>

                <p class="text-muted mb-0">

                    Créez une facture à partir d'une commande.

                </p>

            </div>


            <a
                href="factures.php"
                class="btn btn-outline-dark"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Retour aux factures

            </a>

        </div>



        <!-- =====================================================
             MESSAGE D'ERREUR
        ====================================================== -->

        <?php if (!empty($erreur)): ?>

            <div
                class="alert alert-danger d-flex align-items-center"
                role="alert"
            >

                <i class="bi bi-exclamation-triangle me-2"></i>

                <?= htmlspecialchars($erreur) ?>

            </div>

        <?php endif; ?>



        <!-- =====================================================
             FORMULAIRE
        ====================================================== -->

        <div class="row justify-content-center">

            <div class="col-xl-8 col-lg-10">


                <div class="card border-0 shadow-sm">


                    <!-- CARD HEADER -->

                    <div class="card-header bg-dark text-white py-3">

                        <h5 class="mb-0">

                            <i class="bi bi-file-earmark-plus me-2"></i>

                            Nouvelle facture

                        </h5>

                    </div>


                    <!-- CARD BODY -->

                    <div class="card-body p-4">


                        <?php if (empty($commandes)): ?>


                            <!-- AUCUNE COMMANDE -->

                            <div class="text-center py-5">

                                <div class="mb-3">

                                    <i
                                        class="bi bi-receipt"
                                        style="font-size:60px;"
                                    ></i>

                                </div>


                                <h4 class="fw-bold">

                                    Aucune commande disponible

                                </h4>


                                <p class="text-muted">

                                    Toutes les commandes possèdent déjà
                                    une facture ou aucune commande n'a
                                    encore été créée.

                                </p>


                                <a
                                    href="commandes.php"
                                    class="btn btn-dark"
                                >

                                    <i class="bi bi-cart me-1"></i>

                                    Voir les commandes

                                </a>

                            </div>


                        <?php else: ?>


                            <!-- FORMULAIRE -->

                            <form
                                method="POST"
                                action=""
                            >
                                <?= csrf_champ() ?>


                                <div class="mb-4">


                                    <label
                                        for="commande_id"
                                        class="form-label fw-semibold"
                                    >

                                        Sélectionner une commande

                                    </label>


                                    <select
                                        name="commande_id"
                                        id="commande_id"
                                        class="form-select form-select-lg"
                                        required
                                    >

                                        <option value="">

                                            -- Choisir une commande --

                                        </option>


                                        <?php foreach ($commandes as $commande): ?>

                                            <option
                                                value="<?= (int) $commande["id"] ?>"
                                                <?= (int) ($_GET["commande_id"] ?? 0) === (int) $commande["id"] ? "selected" : "" ?>
                                            >

                                                Commande
                                                <?= (int) $commande["id"] ?>

                                                -
                                                <?= htmlspecialchars(
                                                    $commande["client_nom"]
                                                ) ?>

                                                -

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


                                    <div class="form-text">

                                        Seules les commandes qui ne possèdent
                                        pas encore de facture sont affichées.

                                    </div>

                                </div>



                                <!-- INFORMATIONS -->

                                <div class="alert alert-light border">


                                    <h6 class="fw-bold">

                                        <i class="bi bi-info-circle me-2"></i>

                                        Comment fonctionne la création ?

                                    </h6>


                                    <ul class="mb-0">

                                        <li>
                                            Sélectionnez une commande.
                                        </li>

                                        <li>
                                            Le client sera récupéré automatiquement.
                                        </li>

                                        <li>
                                            Le montant sera récupéré depuis la commande.
                                        </li>

                                        <li>
                                            Un numéro de facture unique sera généré.
                                        </li>

                                        <li>
                                            La facture sera créée avec le statut
                                            <strong>Impayée</strong>.
                                        </li>

                                    </ul>

                                </div>



                                <!-- BOUTONS -->

                                <div class="d-flex justify-content-end gap-2 mt-4">


                                    <a
                                        href="factures.php"
                                        class="btn btn-secondary"
                                    >

                                        Annuler

                                    </a>


                                    <button
                                        type="submit"
                                        class="btn btn-warning"
                                    >

                                        <i class="bi bi-file-earmark-plus me-1"></i>

                                        Créer la facture

                                    </button>

                                </div>


                            </form>


                        <?php endif; ?>


                    </div>

                </div>


            </div>

        </div>


    </main>

</div>



<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>
