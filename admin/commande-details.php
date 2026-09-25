<?php

require_once("includes/auth.php");
require_once "../config/database.php";
require_once "../includes/parametres.php";


/*
|--------------------------------------------------------------------------
| Helpers WhatsApp
|--------------------------------------------------------------------------
*/

function whatsappNumero(?string $numero): ?string
{
    if (empty($numero)) return null;
    $chiffres = preg_replace('/\D/', '', $numero);
    if ($chiffres === '') return null;
    if (strlen($chiffres) === 9) $chiffres = '221' . $chiffres;
    if (strlen($chiffres) === 10 && $chiffres[0] === '0') {
        $chiffres = '221' . substr($chiffres, 1);
    }
    return $chiffres;
}

function whatsappLienFacture(
    ?string $telephoneClient,
    string $nomClient,
    array $facture,
    array $lignes,
    array $params,
    string $dateCommande
): ?string {
    $num = whatsappNumero($telephoneClient);
    if (!$num) return null;

    $nomBoutique = $params["nom_boutique"] ?? "Notre boutique";
    $devise      = $params["devise"] ?? "FCFA";

    // URL publique de la facture (avec token, non modifiable, imprimable en PDF)
    $scheme = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
    $host   = $_SERVER["HTTP_HOST"] ?? "localhost";
    // On remonte d'un dossier depuis /admin/
    $basePath = rtrim(dirname($_SERVER["PHP_SELF"] ?? "/"), "/\\");
    $basePath = preg_replace('#/admin/?$#', '', $basePath);
    $urlPubliqueFacture = $scheme . "://" . $host . $basePath . "/facture-publique.php?token="
                        . ($facture["token_public"] ?? "");

    /*
     * Message court, SANS aucun montant, pour eviter qu'un client
     * malveillant copie le message, modifie les chiffres et l'envoie
     * a un tiers en pretendant que ce sont les prix de la boutique.
     * Seul le lien officiel (servi par notre serveur) fait foi.
     */
    $texte  = "Bonjour " . $nomClient . ",\n\n";
    $texte .= "Votre facture officielle *" . $nomBoutique . "* "
           .  "(N. " . ($facture["numero_facture"] ?? "-") . ") est disponible ici :\n\n";
    $texte .= $urlPubliqueFacture . "\n\n";
    $texte .= "Cliquez sur le lien pour consulter les montants exacts "
           .  "et telecharger votre facture au format PDF.\n\n";
    $texte .= "Nous vous remercions pour votre confiance.\n\n";
    $texte .= "_" . $nomBoutique . "_";
    if (!empty($params["telephone"])) {
        $texte .= "\n" . $params["telephone"];
    }
    if (!empty($params["adresse"])) {
        $texte .= "\n" . $params["adresse"];
    }

    return "https://wa.me/" . $num . "?text=" . rawurlencode($texte);
}


/*
|--------------------------------------------------------------------------
| Vérifier l'identifiant de la commande
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: commandes.php");
    exit;
}

$commandeId = (int) $_GET["id"];


/*
|--------------------------------------------------------------------------
| Enregistrer un paiement directement depuis cette page (modale), sans
| passer par une autre page.
|--------------------------------------------------------------------------
*/

$erreurPaiement = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "enregistrer_paiement") {

    csrf_verifier();

    $montant = $_POST["montant"] ?? "";
    $commentaire = trim($_POST["commentaire"] ?? "");

    $requeteFactureActuelle = $pdo->prepare("
        SELECT
            factures.id AS facture_id,
            factures.montant_total,
            factures.montant_paye,
            factures.reste_a_payer,
            commandes.utilisateur_id
        FROM factures
        INNER JOIN commandes
            ON commandes.id = factures.commande_id
        WHERE factures.commande_id = ?
        LIMIT 1
    ");

    $requeteFactureActuelle->execute([$commandeId]);

    $infoPaiement = $requeteFactureActuelle->fetch(PDO::FETCH_ASSOC);

    if (!$infoPaiement) {

        $erreurPaiement = "Aucune facture n'existe pour cette commande.";

    } else {

        $resteActuel = (float) $infoPaiement["reste_a_payer"];

        if ($resteActuel <= 0) {

            $erreurPaiement = "Cette facture est déjà entièrement payée.";

        } elseif ($montant === "") {

            $erreurPaiement = "Veuillez saisir le montant du paiement.";

        } elseif (!is_numeric($montant) || $montant <= 0) {

            $erreurPaiement = "Le montant du paiement est invalide.";

        } elseif ((float) $montant > $resteActuel) {

            $erreurPaiement = "Le paiement ne peut pas dépasser le reste à payer.";

        } else {

            $montant = (float) $montant;

            try {

                $pdo->beginTransaction();


                // 1. Verrouiller et mettre à jour la facture

                $requeteFactureLock = $pdo->prepare("
                    SELECT id, montant_total, montant_paye
                    FROM factures
                    WHERE id = ?
                    FOR UPDATE
                ");

                $requeteFactureLock->execute([$infoPaiement["facture_id"]]);

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
                    $infoPaiement["facture_id"]
                ]);


                // 2. Synchroniser un éventuel crédit client existant

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

                    $requeteNouveauCredit = $pdo->prepare("
                        INSERT INTO credits_clients
                        (utilisateur_id, commande_id, montant_total, montant_paye, reste_a_payer, statut)
                        VALUES (?, ?, ?, ?, ?, 'En cours')
                    ");

                    $requeteNouveauCredit->execute([
                        $infoPaiement["utilisateur_id"],
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

                $pdo->commit();

                header("Location: commande-details.php?id=" . $commandeId . "&paiement=1");
                exit;

            } catch (Exception $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $erreurPaiement = "Une erreur est survenue lors de l'enregistrement du paiement.";
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| Récupérer les informations de la commande et du client
|--------------------------------------------------------------------------
*/

$requeteCommande = $pdo->prepare("
    SELECT
        commandes.id,
        commandes.utilisateur_id,
        commandes.total,
        commandes.statut,
        commandes.date_commande,
        utilisateurs.nom AS client_nom,
        utilisateurs.email AS client_email,
        utilisateurs.telephone AS client_telephone,
        utilisateurs.type_client AS client_type
    FROM commandes
    INNER JOIN utilisateurs
        ON commandes.utilisateur_id = utilisateurs.id
    WHERE commandes.id = ?
");

$requeteCommande->execute([$commandeId]);

$commande = $requeteCommande->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Vérifier que la commande existe
|--------------------------------------------------------------------------
*/

if (!$commande) {
    header("Location: commandes.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Récupérer l'état du paiement (facture) et un éventuel crédit
|--------------------------------------------------------------------------
*/

$requeteFacture = $pdo->prepare("
    SELECT
        id,
        numero_facture,
        token_public,
        montant_total,
        montant_paye,
        reste_a_payer,
        statut
    FROM factures
    WHERE commande_id = ?
    LIMIT 1
");

$requeteFacture->execute([$commandeId]);

$facture = $requeteFacture->fetch(PDO::FETCH_ASSOC);


$requeteCreditCommande = $pdo->prepare("
    SELECT id, statut
    FROM credits_clients
    WHERE commande_id = ?
    ORDER BY id DESC
    LIMIT 1
");

$requeteCreditCommande->execute([$commandeId]);

$creditCommande = $requeteCreditCommande->fetch(PDO::FETCH_ASSOC);


if ($facture) {

    $resteFacture = (float) $facture["reste_a_payer"];

    if ($resteFacture <= 0) {

        $statutPaiement = "Payée intégralement";
        $classePaiement = "bg-success";
        $iconePaiement = "bi-check-circle";

    } elseif ((float) $facture["montant_paye"] > 0) {

        $statutPaiement = "Paiement partiel";
        $classePaiement = "bg-warning text-dark";
        $iconePaiement = "bi-hourglass-split";

    } else {

        $statutPaiement = "Non payée";
        $classePaiement = "bg-danger";
        $iconePaiement = "bi-exclamation-circle";
    }
}


/*
|--------------------------------------------------------------------------
| Récupérer les produits de la commande
|--------------------------------------------------------------------------
*/

$requeteDetails = $pdo->prepare("
    SELECT
        commande_details.id,
        commande_details.quantite,
        commande_details.prix,
        produits.nom AS produit_nom,
        produits.image AS produit_image
    FROM commande_details
    INNER JOIN produits
        ON commande_details.produit_id = produits.id
    WHERE commande_details.commande_id = ?
    ORDER BY commande_details.id ASC
");

$requeteDetails->execute([$commandeId]);

$details = $requeteDetails->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Calculer le nombre total de produits
|--------------------------------------------------------------------------
*/

$totalArticles = 0;

foreach ($details as $detail) {
    $totalArticles += (int) $detail["quantite"];
}


/*
|--------------------------------------------------------------------------
| Déterminer la couleur du statut
|--------------------------------------------------------------------------
*/

$statut = $commande["statut"];

if ($statut === "En attente") {

    $classeStatut = "bg-warning text-dark";
    $iconeStatut = "bi-hourglass-split";

} elseif ($statut === "Confirmée") {

    $classeStatut = "bg-primary";
    $iconeStatut = "bi-check2-circle";

} elseif ($statut === "Expédiée") {

    $classeStatut = "bg-info text-dark";
    $iconeStatut = "bi-truck";

} elseif ($statut === "Livrée") {

    $classeStatut = "bg-success";
    $iconeStatut = "bi-check-circle";

} elseif ($statut === "Annulée") {

    $classeStatut = "bg-danger";
    $iconeStatut = "bi-x-circle";

} else {

    $classeStatut = "bg-secondary";
    $iconeStatut = "bi-question-circle";
}


/*
|--------------------------------------------------------------------------
| Preparer le lien WhatsApp pour envoyer la facture
|--------------------------------------------------------------------------
*/

$lienWhatsappFacture = null;

if ($facture) {
    $lienWhatsappFacture = whatsappLienFacture(
        $commande["client_telephone"] ?? null,
        $commande["client_nom"] ?? "client",
        $facture,
        $details,
        parametresTous($pdo),
        $commande["date_commande"] ?? date("Y-m-d")
    );
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
        Commande <?= $commande["id"] ?> - NPM Admin
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


    <!-- CSS du projet -->

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body>


<!-- =========================================================
     NAVBAR ADMIN
========================================================== -->

<?php require_once("includes/navbar.php"); ?>


<!-- =========================================================
     SIDEBAR ADMIN
========================================================== -->

<?php require_once("includes/sidebar.php"); ?>


<!-- =========================================================
     CONTENU
========================================================== -->

<main class="admin-content">

    <div class="container-fluid py-5 px-4">


        <!-- =====================================================
             EN-TÊTE
        ====================================================== -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <a
                    href="commandes.php"
                    class="btn btn-outline-secondary btn-sm mb-3"
                >
                    <i class="bi bi-arrow-left me-1"></i>
                    Retour aux commandes
                </a>

                <h1 class="fw-bold mb-2">

                    Commande <?= $commande["id"] ?>

                </h1>

                <p class="text-muted mb-0">

                    Détails de la commande

                </p>

            </div>


            <!-- Statut -->

            <div>

                <span class="badge <?= $classeStatut ?> fs-6 px-3 py-2">

                    <i class="bi <?= $iconeStatut ?> me-1"></i>

                    <?= htmlspecialchars($statut) ?>

                </span>

            </div>

        </div>


        <!-- Messages liés au paiement -->

        <?php if (($_GET["paiement"] ?? "") === "1"): ?>

            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                Le paiement a été enregistré avec succès.
            </div>

        <?php endif; ?>

        <?php if ($erreurPaiement !== ""): ?>

            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($erreurPaiement) ?>
            </div>

        <?php endif; ?>


        <?php if (($_GET["erreur"] ?? "") === "credit_actif"): ?>
            <div class="alert alert-warning">
                <i class="bi bi-shield-exclamation me-2"></i>
                Impossible de supprimer : cette commande a un crédit client encore
                en cours. Réglez le solde restant avant de supprimer.
            </div>
        <?php elseif (($_GET["erreur"] ?? "") === "commande_verrouillee"): ?>
            <div class="alert alert-success">
                <i class="bi bi-shield-lock-fill me-2"></i>
                Cette commande est <strong>Livrée</strong> et donc verrouillée :
                elle ne peut plus être ni modifiée ni supprimée.
            </div>
        <?php elseif (($_GET["erreur"] ?? "") === "commande_suppr"): ?>
            <div class="alert alert-danger">
                <i class="bi bi-x-circle me-2"></i>
                Une erreur est survenue lors de la suppression. L'action a été annulée.
            </div>
        <?php elseif (($_GET["paiement_supprime"] ?? "") === "1"): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Le paiement a été annulé et les soldes ont été recalculés.
            </div>
        <?php endif; ?>



        <!-- =====================================================
             INFORMATIONS COMMANDE + CLIENT
        ====================================================== -->

        <div class="row g-4 mb-4">


            <!-- Informations commande -->

            <div class="col-lg-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-header bg-white py-3">

                        <h5 class="fw-bold mb-0">

                            <i class="bi bi-receipt me-2"></i>

                            Informations de la commande

                        </h5>

                    </div>


                    <div class="card-body">

                        <div class="row g-3">


                            <div class="col-sm-6">

                                <small class="text-muted d-block">
                                    Numéro
                                </small>

                                <strong>
                                    #<?= $commande["id"] ?>
                                </strong>

                            </div>


                            <div class="col-sm-6">

                                <small class="text-muted d-block">
                                    Statut
                                </small>

                                <strong>
                                    <?= htmlspecialchars($statut) ?>
                                </strong>

                            </div>


                            <div class="col-sm-6">

                                <small class="text-muted d-block">
                                    Nombre d'articles
                                </small>

                                <strong>
                                    <?= $totalArticles ?>
                                </strong>

                            </div>


                            <div class="col-sm-6">

                                <small class="text-muted d-block">
                                    Date
                                </small>

                                <strong>

                                    <?php if (!empty($commande["date_commande"])): ?>

                                        <?= date(
                                            "d/m/Y H:i",
                                            strtotime($commande["date_commande"])
                                        ) ?>

                                    <?php else: ?>

                                        —

                                    <?php endif; ?>

                                </strong>

                            </div>


                            <div class="col-12">

                                <hr>

                                <small class="text-muted d-block">
                                    Total de la commande
                                </small>

                                <h3 class="fw-bold text-warning mb-0">

                                    <?= number_format(
                                        (float) $commande["total"],
                                        0,
                                        ",",
                                        " "
                                    ) ?>

                                    FCFA

                                </h3>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- Informations client -->

            <div class="col-lg-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-header bg-white py-3">

                        <h5 class="fw-bold mb-0">

                            <i class="bi bi-person me-2"></i>

                            Informations du client

                        </h5>

                    </div>


                    <div class="card-body">


                        <div class="d-flex align-items-center gap-3 mb-4">

                            <div
                                class="rounded-circle bg-dark text-warning d-flex align-items-center justify-content-center"
                                style="width: 60px; height: 60px;"
                            >

                                <i class="bi bi-person-fill fs-4"></i>

                            </div>


                            <div>

                                <h5 class="fw-bold mb-1">

                                    <?= htmlspecialchars(
                                        $commande["client_nom"]
                                    ) ?>

                                </h5>

                                <span class="text-muted">

                                    Client NPM

                                    <?php if (($commande["client_type"] ?? "") === "boutique"): ?>

                                        <span class="badge bg-dark ms-1">
                                            Boutique
                                        </span>

                                    <?php endif; ?>

                                </span>

                            </div>

                        </div>


                        <div class="mb-3">

                            <small class="text-muted d-block">
                                Email
                            </small>

                            <?php if (!empty($commande["client_email"])): ?>

                                <a
                                    href="mailto:<?= htmlspecialchars(
                                        $commande["client_email"]
                                    ) ?>"
                                    class="text-decoration-none"
                                >

                                    <i class="bi bi-envelope me-1"></i>

                                    <?= htmlspecialchars(
                                        $commande["client_email"]
                                    ) ?>

                                </a>

                            <?php else: ?>

                                <span class="text-muted">—</span>

                            <?php endif; ?>

                        </div>


                        <?php if (!empty($commande["client_telephone"])): ?>

                            <div class="mb-3">

                                <small class="text-muted d-block">
                                    Téléphone
                                </small>

                                <span>
                                    <i class="bi bi-telephone me-1"></i>
                                    <?= htmlspecialchars($commande["client_telephone"]) ?>
                                </span>

                            </div>

                        <?php endif; ?>


                        <div>

                            <small class="text-muted d-block">
                                Identifiant client
                            </small>

                            <strong>
                                #<?= $commande["utilisateur_id"] ?>
                            </strong>

                        </div>


                    </div>

                </div>

            </div>

        </div>



        <!-- =====================================================
             ÉTAT DU PAIEMENT
        ====================================================== -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0">
                    <i class="bi bi-cash-coin me-2"></i>
                    État du paiement
                </h5>

            </div>

            <div class="card-body">

                <?php if ($facture): ?>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">

                        <span class="badge <?= $classePaiement ?> fs-6 px-3 py-2">
                            <i class="bi <?= $iconePaiement ?> me-1"></i>
                            <?= htmlspecialchars($statutPaiement) ?>
                        </span>

                        <div class="d-flex gap-2">

                            <a
                                href="facture.php?id=<?= $facture["id"] ?>"
                                class="btn btn-sm btn-outline-dark"
                            >
                                <i class="bi bi-receipt me-1"></i>
                                Voir la facture
                            </a>

                            <?php if ($lienWhatsappFacture): ?>
                                <a
                                    href="<?= htmlspecialchars($lienWhatsappFacture) ?>"
                                    class="btn btn-sm btn-success"
                                    target="_blank"
                                    rel="noopener"
                                    title="Envoyer la facture au client sur WhatsApp"
                                >
                                    <i class="bi bi-whatsapp me-1"></i>
                                    Envoyer par WhatsApp
                                </a>
                            <?php else: ?>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    disabled
                                    title="Aucun numero de telephone enregistre pour ce client"
                                >
                                    <i class="bi bi-whatsapp me-1"></i>
                                    WhatsApp indisponible
                                </button>
                            <?php endif; ?>

                            <?php if ((float) $facture["reste_a_payer"] > 0): ?>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalPaiement"
                                >
                                    <i class="bi bi-cash-coin me-1"></i>
                                    Enregistrer un paiement
                                </button>

                            <?php endif; ?>

                            <?php if ($creditCommande): ?>

                                <a
                                    href="details-credit.php?id=<?= $creditCommande["id"] ?>"
                                    class="btn btn-sm btn-outline-dark"
                                >
                                    <i class="bi bi-eye me-1"></i>
                                    Voir le crédit
                                    <?php if ($creditCommande["statut"] === "Réglé"): ?>
                                        <span class="badge bg-success ms-1">Réglé</span>
                                    <?php endif; ?>
                                </a>

                            <?php endif; ?>

                        </div>

                    </div>


                    <div class="row g-3 text-center">

                        <div class="col-4">
                            <small class="text-muted d-block">Total</small>
                            <strong>
                                <?= number_format((float) $facture["montant_total"], 0, ",", " ") ?> FCFA
                            </strong>
                        </div>

                        <div class="col-4">
                            <small class="text-muted d-block">Payé</small>
                            <strong class="text-success">
                                <?= number_format((float) $facture["montant_paye"], 0, ",", " ") ?> FCFA
                            </strong>
                        </div>

                        <div class="col-4">
                            <small class="text-muted d-block">Reste à payer</small>
                            <strong class="<?= (float) $facture["reste_a_payer"] > 0 ? "text-danger" : "text-success" ?>">
                                <?= number_format((float) $facture["reste_a_payer"], 0, ",", " ") ?> FCFA
                            </strong>
                        </div>

                    </div>

                <?php else: ?>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

                        <span class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Aucune facture n'a encore été créée pour cette commande.
                        </span>

                        <a
                            href="creer-facture.php?commande_id=<?= $commande["id"] ?>"
                            class="btn btn-sm btn-dark"
                        >
                            <i class="bi bi-file-earmark-plus me-1"></i>
                            Créer une facture
                        </a>

                    </div>

                <?php endif; ?>

            </div>

        </div>



        <!-- =====================================================
             PRODUITS COMMANDÉS
        ====================================================== -->

        <div class="card border-0 shadow-sm">


            <div class="card-header bg-white py-3">

                <div class="d-flex justify-content-between align-items-center">

                    <h5 class="fw-bold mb-0">

                        <i class="bi bi-box-seam me-2"></i>

                        Produits commandés

                    </h5>


                    <span class="badge bg-dark">

                        <?= $totalArticles ?> article(s)

                    </span>

                </div>

            </div>


            <div class="card-body p-0">


                <?php if (empty($details)): ?>

                    <!-- Aucun produit -->

                    <div class="text-center py-5">

                        <i class="bi bi-box-seam fs-1 text-muted"></i>

                        <h5 class="mt-3">
                            Aucun produit
                        </h5>

                        <p class="text-muted mb-0">
                            Cette commande ne contient aucun produit.
                        </p>

                    </div>


                <?php else: ?>


                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">


                            <thead class="table-dark">

                                <tr>

                                    <th>
                                        Produit
                                    </th>

                                    <th>
                                        Prix unitaire
                                    </th>

                                    <th>
                                        Quantité
                                    </th>

                                    <th>
                                        Sous-total
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php foreach ($details as $detail): ?>


                                    <?php

                                    $prix = (float) $detail["prix"];

                                    $quantite = (int) $detail["quantite"];

                                    $sousTotal = $prix * $quantite;

                                    ?>


                                    <tr>


                                        <!-- Produit -->

                                        <td>

                                            <div class="d-flex align-items-center gap-3">


                                                <?php if (!empty($detail["produit_image"])): ?>

                                                    <img
                                                        src="../uploads/<?= htmlspecialchars(
                                                            $detail["produit_image"]
                                                        ) ?>"
                                                        alt="<?= htmlspecialchars(
                                                            $detail["produit_nom"]
                                                        ) ?>"
                                                        style="
                                                            width: 60px;
                                                            height: 60px;
                                                            object-fit: cover;
                                                            border-radius: 8px;
                                                        "
                                                    >

                                                <?php else: ?>

                                                    <div
                                                        class="bg-light rounded d-flex align-items-center justify-content-center"
                                                        style="
                                                            width: 60px;
                                                            height: 60px;
                                                        "
                                                    >

                                                        <i class="bi bi-image text-muted fs-4"></i>

                                                    </div>

                                                <?php endif; ?>


                                                <div>

                                                    <strong>

                                                        <?= htmlspecialchars(
                                                            $detail["produit_nom"]
                                                        ) ?>

                                                    </strong>

                                                    <div class="text-muted small">

                                                        Produit NPM

                                                    </div>

                                                </div>

                                            </div>

                                        </td>



                                        <!-- Prix -->

                                        <td>

                                            <?= number_format(
                                                $prix,
                                                0,
                                                ",",
                                                " "
                                            ) ?>

                                            FCFA

                                        </td>



                                        <!-- Quantité -->

                                        <td>

                                            <span class="badge bg-light text-dark border">

                                                <?= $quantite ?>

                                            </span>

                                        </td>



                                        <!-- Sous-total -->

                                        <td>

                                            <strong>

                                                <?= number_format(
                                                    $sousTotal,
                                                    0,
                                                    ",",
                                                    " "
                                                ) ?>

                                                FCFA

                                            </strong>

                                        </td>


                                    </tr>


                                <?php endforeach; ?>


                            </tbody>


                            <!-- TOTAL -->

                            <tfoot>

                                <tr>

                                    <td colspan="3" class="text-end fw-bold">

                                        TOTAL

                                    </td>

                                    <td>

                                        <h5 class="fw-bold text-warning mb-0">

                                            <?= number_format(
                                                (float) $commande["total"],
                                                0,
                                                ",",
                                                " "
                                            ) ?>

                                            FCFA

                                        </h5>

                                    </td>

                                </tr>

                            </tfoot>


                        </table>

                    </div>


                <?php endif; ?>


            </div>

        </div>



    </div>

</main>



<!-- =========================================================
     MODALE : ENREGISTRER UN PAIEMENT
     (reste sur cette même page, rien à quitter)
========================================================== -->

<?php if ($facture && (float) $facture["reste_a_payer"] > 0): ?>

    <div class="modal fade" id="modalPaiement" tabindex="-1" aria-labelledby="modalPaiementLabel" aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <form method="POST" action="commande-details.php?id=<?= $commande["id"] ?>">

                    <?= csrf_champ() ?>
                    <input type="hidden" name="action" value="enregistrer_paiement">

                    <div class="modal-header">

                        <h5 class="modal-title" id="modalPaiementLabel">
                            <i class="bi bi-cash-coin me-2"></i>
                            Enregistrer un paiement
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>

                    </div>

                    <div class="modal-body">

                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Reste à payer actuellement</span>
                            <strong class="text-danger">
                                <?= number_format((float) $facture["reste_a_payer"], 0, ",", " ") ?> FCFA
                            </strong>
                        </div>


                        <div class="mb-3">

                            <label for="montantPaiement" class="form-label fw-semibold">
                                Montant reçu maintenant
                                <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">

                                <input
                                    type="number"
                                    name="montant"
                                    id="montantPaiement"
                                    class="form-control"
                                    min="1"
                                    max="<?= (float) $facture["reste_a_payer"] ?>"
                                    step="1"
                                    required
                                >

                                <span class="input-group-text">FCFA</span>

                            </div>

                            <div class="mt-2">

                                <button
                                    type="button"
                                    id="btnMontantTotalModal"
                                    class="btn btn-sm btn-outline-dark"
                                >
                                    Client paie tout le reste
                                </button>

                            </div>

                        </div>


                        <div class="alert alert-warning mb-3">

                            <div class="d-flex justify-content-between">
                                <span>Reste après ce paiement</span>
                                <strong id="nouveauResteModal">
                                    <?= number_format((float) $facture["reste_a_payer"], 0, ",", " ") ?> FCFA
                                </strong>
                            </div>

                        </div>


                        <div class="mb-1">

                            <label for="commentairePaiement" class="form-label fw-semibold">
                                Commentaire
                            </label>

                            <textarea
                                name="commentaire"
                                id="commentairePaiement"
                                class="form-control"
                                rows="2"
                                placeholder="Exemple : Paiement en espèces à la boutique..."
                            ></textarea>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Annuler
                        </button>

                        <button type="submit" class="btn btn-warning px-4">
                            <i class="bi bi-check-lg me-2"></i>
                            Enregistrer le paiement
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

<?php endif; ?>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


<?php if ($facture && (float) $facture["reste_a_payer"] > 0): ?>

<script>

const montantModal = document.getElementById("montantPaiement");
const btnTotalModal = document.getElementById("btnMontantTotalModal");
const resteActuelModal = <?= (float) $facture["reste_a_payer"] ?>;

function majResteModal() {

    const paiement = parseFloat(montantModal.value) || 0;

    let nouveauReste = resteActuelModal - paiement;

    if (nouveauReste < 0) {
        nouveauReste = 0;
    }

    document.getElementById("nouveauResteModal").textContent =
        nouveauReste.toLocaleString("fr-FR") + " FCFA";
}

if (montantModal) {

    montantModal.addEventListener("input", majResteModal);

    if (btnTotalModal) {

        btnTotalModal.addEventListener("click", function () {
            montantModal.value = resteActuelModal;
            majResteModal();
        });
    }
}

<?php if ($erreurPaiement !== ""): ?>

// Une erreur de paiement vient de se produire : on rouvre la modale
// pour que l'administrateur puisse corriger sans tout ressaisir.
window.addEventListener("DOMContentLoaded", function () {
    const modalPaiementEl = document.getElementById("modalPaiement");
    if (modalPaiementEl) {
        new bootstrap.Modal(modalPaiementEl).show();
    }
});

<?php endif; ?>

</script>

<?php endif; ?>


</body>

</html>
