<?php

session_start();

require_once "config/database.php";


// =========================================================
// VÉRIFIER LA CONNEXION
// =========================================================

if (!isset($_SESSION["id"])) {

    header("Location: connexion.php");
    exit;

}


// =========================================================
// VÉRIFIER LE PANIER
// =========================================================

if (
    !isset($_SESSION["panier"]) ||
    empty($_SESSION["panier"])
) {

    header("Location: panier.php");
    exit;

}


// =========================================================
// RÉCUPÉRER LES PRODUITS DU PANIER
// =========================================================

$produitsPanier = [];
$total = 0;

foreach ($_SESSION["panier"] as $produitId => $quantite) {

    $produitId = (int) $produitId;
    $quantite = (int) $quantite;

    if ($quantite <= 0) {
        continue;
    }


    $requete = $pdo->prepare("
        SELECT
            produits.*,
            categories.nom AS categorie_nom
        FROM produits
        LEFT JOIN categories
            ON produits.categorie = categories.id
        WHERE produits.id = ?
    ");

    $requete->execute([$produitId]);

    $produit = $requete->fetch(PDO::FETCH_ASSOC);


    if ($produit) {

        // Vérifier le stock
        if ($quantite > (int) $produit["stock"]) {

            $quantite = (int) $produit["stock"];
        }


        if ($quantite > 0) {

            $produit["quantite"] = $quantite;

            $produit["sous_total"] =
                $produit["prix"] * $quantite;

            $total += $produit["sous_total"];

            $produitsPanier[] = $produit;
        }
    }
}


// Si aucun produit valide
if (empty($produitsPanier)) {

    $_SESSION["panier"] = [];

    header("Location: panier.php");
    exit;
}


// =========================================================
// MESSAGE
// =========================================================

$erreur = "";


// =========================================================
// TRAITEMENT DE LA COMMANDE
// =========================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $paiement = $_POST["paiement"] ?? "livraison";


    try {

        // Démarrer une transaction
        $pdo->beginTransaction();


        // =================================================
        // VÉRIFIER UNE DERNIÈRE FOIS LE STOCK
        // =================================================

        foreach ($produitsPanier as $produit) {

            $requeteStock = $pdo->prepare("
                SELECT stock
                FROM produits
                WHERE id = ?
                FOR UPDATE
            ");

            $requeteStock->execute([
                $produit["id"]
            ]);

            $stockActuel = $requeteStock->fetchColumn();


            if (
                $stockActuel === false ||
                $stockActuel < $produit["quantite"]
            ) {

                throw new Exception(
                    "Le stock du produit « "
                    . $produit["nom"]
                    . " » est insuffisant."
                );
            }
        }


        // =================================================
        // CRÉER LA COMMANDE
        // =================================================

        $requeteCommande = $pdo->prepare("
            INSERT INTO commandes
            (
                utilisateur_id,
                total,
                statut
            )
            VALUES (?, ?, ?)
        ");

        $requeteCommande->execute([
            $_SESSION["id"],
            $total,
            "En attente"
        ]);


        // Récupérer l'ID de la commande
        $commandeId = $pdo->lastInsertId();


        // =================================================
        // AJOUTER LES DÉTAILS DE LA COMMANDE
        // =================================================

        $requeteDetail = $pdo->prepare("
            INSERT INTO commande_details
            (
                commande_id,
                produit_id,
                quantite,
                prix
            )
            VALUES (?, ?, ?, ?)
        ");


        // Requête pour diminuer le stock
        $requeteStock = $pdo->prepare("
            UPDATE produits
            SET stock = stock - ?
            WHERE id = ?
        ");


        foreach ($produitsPanier as $produit) {

            // Ajouter le produit dans commande_details
            $requeteDetail->execute([
                $commandeId,
                $produit["id"],
                $produit["quantite"],
                $produit["prix"]
            ]);


            // Diminuer le stock
            $requeteStock->execute([
                $produit["quantite"],
                $produit["id"]
            ]);
        }


        // =================================================
        // CRÉER AUTOMATIQUEMENT LA FACTURE
        // =================================================

        $annee = date("Y");


        // Récupérer la dernière facture
        $requeteDerniereFacture = $pdo->query("
            SELECT id
            FROM factures
            ORDER BY id DESC
            LIMIT 1
            FOR UPDATE
        ");

        $derniereFacture = $requeteDerniereFacture->fetch(
            PDO::FETCH_ASSOC
        );


        if ($derniereFacture) {

            $numero = (int) $derniereFacture["id"] + 1;

        } else {

            $numero = 1;

        }


        // Générer le numéro
        $numeroFacture = "FAC-"
            . $annee
            . "-"
            . str_pad(
                $numero,
                5,
                "0",
                STR_PAD_LEFT
            );


        // Montants de la facture
        $montantTotal = (float) $total;

        $montantPaye = 0;

        $resteAPayer = $montantTotal;

        $statutFacture = "Impayée";


        // Insérer la facture
        $requeteFacture = $pdo->prepare("
            INSERT INTO factures
            (
                commande_id,
                numero_facture,
                montant_total,
                montant_paye,
                reste_a_payer,
                statut
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");


        $requeteFacture->execute([
            $commandeId,
            $numeroFacture,
            $montantTotal,
            $montantPaye,
            $resteAPayer,
            $statutFacture
        ]);


        // =================================================
        // VALIDER LA TRANSACTION
        // =================================================

        $pdo->commit();


        // =================================================
        // VIDER LE PANIER
        // =================================================

        $_SESSION["panier"] = [];


        // =================================================
        // REDIRECTION VERS CONFIRMATION
        // =================================================

        header(
            "Location: commande.php?succes=1&id="
            . $commandeId
        );

        exit;


    } catch (Exception $e) {

        // Annuler les modifications
        if ($pdo->inTransaction()) {

            $pdo->rollBack();
        }


        $erreur = $e->getMessage();
    }
}


// =========================================================
// COMMANDE RÉUSSIE
// =========================================================

$commandeSucces = isset($_GET["succes"]);

$commandeId = $_GET["id"] ?? null;


// =========================================================
// RÉCUPÉRER LES INFORMATIONS DU CLIENT
// =========================================================

$requeteClient = $pdo->prepare("
    SELECT nom, email
    FROM utilisateurs
    WHERE id = ?
");

$requeteClient->execute([
    $_SESSION["id"]
]);

$client = $requeteClient->fetch(PDO::FETCH_ASSOC);

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
        Passer la commande - Quincaillerie NPM
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


    <!-- CSS -->

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>


<body>


<?php include "includes/navbar.php"; ?>


<main>


<?php if ($commandeSucces): ?>

    <!-- ================================================
         COMMANDE RÉUSSIE
    ================================================= -->

    <section class="py-5">

        <div class="container">

            <div class="row justify-content-center">

                <div class="col-lg-7">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body text-center p-5">


                            <div
                                class="mb-4"
                                style="font-size: 70px;"
                            >

                                <i class="bi bi-check-circle-fill text-success"></i>

                            </div>


                            <h1 class="fw-bold mb-3">

                                Commande confirmée !

                            </h1>


                            <p class="text-muted fs-5">

                                Merci pour votre commande auprès de
                                Quincaillerie NPM.

                            </p>


                            <div class="alert alert-light border mt-4">

                                <strong>
                                    Numéro de commande :
                                </strong>

                                #<?= htmlspecialchars($commandeId) ?>

                            </div>


                            <p class="text-muted">

                                Votre commande est actuellement
                                <strong>En attente</strong>.

                            </p>


                            <div class="d-flex justify-content-center gap-3 mt-4">

                                <a
                                    href="profil.php"
                                    class="btn btn-dark"
                                >

                                    <i class="bi bi-person me-1"></i>

                                    Mon profil

                                </a>


                                <a
                                    href="produits.php"
                                    class="btn btn-outline-dark"
                                >

                                    <i class="bi bi-shop me-1"></i>

                                    Continuer mes achats

                                </a>

                            </div>


                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>


<?php else: ?>


    <!-- ================================================
         TITRE
    ================================================= -->

    <section class="commande-header py-5">

        <div class="container">

            <div class="text-center">

                <span class="text-orange fw-bold">
                    COMMANDE
                </span>

                <h1 class="fw-bold mt-2">
                    Finaliser ma commande
                </h1>

                <p class="text-muted">
                    Renseignez vos informations pour recevoir votre commande.
                </p>

            </div>

        </div>

    </section>


    <!-- ================================================
         FORMULAIRE
    ================================================= -->

    <section class="py-5">

        <div class="container">

            <div class="row g-4">


                <!-- INFORMATIONS CLIENT -->

                <div class="col-lg-7">

                    <div class="commande-card">


                        <h3 class="fw-bold mb-4">
                            Informations de livraison
                        </h3>


                        <?php if (!empty($erreur)): ?>

                            <div class="alert alert-danger">

                                <i class="bi bi-exclamation-circle me-2"></i>

                                <?= htmlspecialchars($erreur) ?>

                            </div>

                        <?php endif; ?>


                        <form
                            action="commande.php"
                            method="POST"
                        >


                            <!-- Nom -->

                            <div class="mb-3">

                                <label
                                    for="nom"
                                    class="form-label fw-bold"
                                >

                                    Nom complet

                                </label>

                                <input
                                    type="text"
                                    name="nom"
                                    id="nom"
                                    class="form-control"
                                    value="<?= htmlspecialchars($client["nom"] ?? "") ?>"
                                    required
                                >

                            </div>


                            <!-- Téléphone -->

                            <div class="mb-3">

                                <label
                                    for="telephone"
                                    class="form-label fw-bold"
                                >

                                    Téléphone

                                </label>

                                <input
                                    type="tel"
                                    name="telephone"
                                    id="telephone"
                                    class="form-control"
                                    placeholder="Ex : 77 000 00 00"
                                    required
                                >

                            </div>


                            <!-- Email -->

                            <div class="mb-3">

                                <label
                                    for="email"
                                    class="form-label fw-bold"
                                >

                                    Adresse email

                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    class="form-control"
                                    value="<?= htmlspecialchars($client["email"] ?? "") ?>"
                                    required
                                >

                            </div>


                            <!-- Adresse -->

                            <div class="mb-3">

                                <label
                                    for="adresse"
                                    class="form-label fw-bold"
                                >

                                    Adresse de livraison

                                </label>

                                <textarea
                                    name="adresse"
                                    id="adresse"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Votre adresse complète"
                                    required
                                ></textarea>

                            </div>


                            <!-- Ville + quartier -->

                            <div class="row">

                                <div class="col-md-6 mb-3">

                                    <label
                                        for="ville"
                                        class="form-label fw-bold"
                                    >

                                        Ville

                                    </label>

                                    <input
                                        type="text"
                                        name="ville"
                                        id="ville"
                                        class="form-control"
                                        placeholder="Dakar"
                                        required
                                    >

                                </div>


                                <div class="col-md-6 mb-3">

                                    <label
                                        for="quartier"
                                        class="form-label fw-bold"
                                    >

                                        Quartier

                                    </label>

                                    <input
                                        type="text"
                                        name="quartier"
                                        id="quartier"
                                        class="form-control"
                                        placeholder="Votre quartier"
                                        required
                                    >

                                </div>

                            </div>


                            <!-- Paiement -->

                            <h4 class="fw-bold mt-4 mb-3">
                                Mode de paiement
                            </h4>


                            <div class="payment-option mb-2">

                                <div class="form-check">

                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="paiement"
                                        id="livraison"
                                        value="livraison"
                                        checked
                                    >

                                    <label
                                        class="form-check-label"
                                        for="livraison"
                                    >

                                        <i class="bi bi-cash-coin me-1"></i>

                                        Paiement à la livraison

                                    </label>

                                </div>

                            </div>


                            <div class="payment-option mb-2">

                                <div class="form-check">

                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="paiement"
                                        id="wave"
                                        value="wave"
                                    >

                                    <label
                                        class="form-check-label"
                                        for="wave"
                                    >

                                        <i class="bi bi-phone me-1"></i>

                                        Wave

                                    </label>

                                </div>

                            </div>


                            <div class="payment-option mb-3">

                                <div class="form-check">

                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="paiement"
                                        id="om"
                                        value="orange_money"
                                    >

                                    <label
                                        class="form-check-label"
                                        for="om"
                                    >

                                        <i class="bi bi-phone me-1"></i>

                                        Orange Money

                                    </label>

                                </div>

                            </div>


                            <!-- Commentaire -->

                            <div class="mt-4 mb-4">

                                <label
                                    for="commentaire"
                                    class="form-label fw-bold"
                                >

                                    Commentaire
                                    <span class="text-muted">
                                        (facultatif)
                                    </span>

                                </label>

                                <textarea
                                    name="commentaire"
                                    id="commentaire"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Une précision concernant votre commande ?"
                                ></textarea>

                            </div>


                            <!-- Bouton -->

                            <button
                                type="submit"
                                class="btn btn-dark btn-lg w-100"
                            >

                                <i class="bi bi-check-circle me-1"></i>

                                Confirmer ma commande

                            </button>


                        </form>

                    </div>

                </div>


                <!-- RÉSUMÉ -->

                <div class="col-lg-5">

                    <div class="commande-summary">


                        <h3 class="fw-bold mb-4">
                            Résumé de votre commande
                        </h3>


                        <?php foreach ($produitsPanier as $produit): ?>

                            <div class="commande-product">


                                <div class="commande-product-image">

                                    <?php if (!empty($produit["image"])): ?>

                                        <img
                                            src="uploads/<?= htmlspecialchars($produit["image"]) ?>"
                                            alt="<?= htmlspecialchars($produit["nom"]) ?>"
                                            style="
                                                width: 60px;
                                                height: 60px;
                                                object-fit: cover;
                                                border-radius: 8px;
                                            "
                                        >

                                    <?php else: ?>

                                        <i class="bi bi-image"></i>

                                    <?php endif; ?>

                                </div>


                                <div class="flex-grow-1">

                                    <h6 class="fw-bold mb-1">

                                        <?= htmlspecialchars($produit["nom"]) ?>

                                    </h6>


                                    <small class="text-muted">

                                        Quantité :
                                        <?= $produit["quantite"] ?>

                                    </small>

                                </div>


                                <strong>

                                    <?= number_format(
                                        $produit["sous_total"],
                                        0,
                                        ",",
                                        " "
                                    ) ?>

                                    FCFA

                                </strong>


                            </div>

                        <?php endforeach; ?>


                        <hr>


                        <div
                            class="d-flex justify-content-between mb-3"
                        >

                            <span>
                                Sous-total
                            </span>

                            <strong>

                                <?= number_format(
                                    $total,
                                    0,
                                    ",",
                                    " "
                                ) ?>

                                FCFA

                            </strong>

                        </div>


                        <div
                            class="d-flex justify-content-between mb-3"
                        >

                            <span>
                                Livraison
                            </span>

                            <strong>
                                À déterminer
                            </strong>

                        </div>


                        <hr>


                        <div
                            class="d-flex justify-content-between align-items-center"
                        >

                            <strong>
                                Total
                            </strong>

                            <h3 class="text-orange fw-bold mb-0">

                                <?= number_format(
                                    $total,
                                    0,
                                    ",",
                                    " "
                                ) ?>

                                FCFA

                            </h3>

                        </div>


                        <div class="mt-4 p-3 bg-light rounded">

                            <small class="text-muted">

                                <i class="bi bi-shield-check text-success"></i>

                                Vos informations sont protégées
                                et utilisées uniquement pour traiter
                                votre commande.

                            </small>

                        </div>


                    </div>

                </div>

            </div>

        </div>

    </section>


<?php endif; ?>


</main>


<?php include "includes/footer.php"; ?>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>