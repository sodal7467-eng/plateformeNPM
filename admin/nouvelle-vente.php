<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("includes/auth.php");
require_once("../config/database.php");

$erreur = "";


/*
|--------------------------------------------------------------------------
| Récupérer les clients et les produits disponibles
|--------------------------------------------------------------------------
*/

$clients = $pdo->query("
    SELECT id, nom, email, telephone, type_client
    FROM utilisateurs
    WHERE role = 'client'
    ORDER BY nom ASC
")->fetchAll(PDO::FETCH_ASSOC);

$produits = $pdo->query("
    SELECT id, nom, prix, stock
    FROM produits
    WHERE stock > 0
    ORDER BY nom ASC
")->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Valeurs par défaut du formulaire (pré-remplissage)
|--------------------------------------------------------------------------
*/

$modeClient = "existant";
$utilisateurIdSelectionne = isset($_GET["utilisateur_id"])
    ? (int) $_GET["utilisateur_id"]
    : 0;

$nouveauNom = "";
$nouveauTelephone = "";
$nouveauEmail = "";

$quantitesSaisies = [];
$montantPayeSaisi = "";
$paiementPartiel = false;
$commentaire = "";


/*
|--------------------------------------------------------------------------
| Traitement de la vente
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verifier();


    $modeClient = $_POST["mode_client"] ?? "existant";

    $utilisateurIdSelectionne = (int) ($_POST["utilisateur_id"] ?? 0);

    $nouveauNom = trim($_POST["nouveau_nom"] ?? "");
    $nouveauTelephone = trim($_POST["nouveau_telephone"] ?? "");
    $nouveauEmail = trim($_POST["nouveau_email"] ?? "");

    $quantitesSaisies = $_POST["quantite"] ?? [];

    $montantPayeSaisi = $_POST["montant_paye"] ?? "";

    $paiementPartiel = isset($_POST["paiement_partiel"]);

    $commentaire = trim($_POST["commentaire"] ?? "");


    /*
    |----------------------------------------------------------------
    | 1) Vérifier le client
    |----------------------------------------------------------------
    */

    if ($modeClient === "nouveau") {

        if ($nouveauNom === "") {

            $erreur = "Le nom du nouveau client est obligatoire.";

        } elseif (
            $nouveauEmail !== "" &&
            !filter_var($nouveauEmail, FILTER_VALIDATE_EMAIL)
        ) {

            $erreur = "L'adresse email du nouveau client n'est pas valide.";

        } elseif ($nouveauEmail !== "") {

            $verification = $pdo->prepare("
                SELECT id FROM utilisateurs WHERE email = ?
            ");

            $verification->execute([$nouveauEmail]);

            if ($verification->fetch()) {
                $erreur = "Cette adresse email est déjà utilisée par un autre client.";
            }
        }

    } else {

        if ($utilisateurIdSelectionne <= 0) {
            $erreur = "Veuillez sélectionner un client.";
        }
    }


    /*
    |----------------------------------------------------------------
    | 2) Vérifier les produits sélectionnés
    |----------------------------------------------------------------
    */

    $lignesVente = [];

    if ($erreur === "") {

        foreach ($quantitesSaisies as $produitId => $quantite) {

            $quantite = (int) $quantite;

            if ($quantite > 0) {
                $lignesVente[(int) $produitId] = $quantite;
            }
        }

        if (empty($lignesVente)) {
            $erreur = "Sélectionnez au moins un produit avec une quantité.";
        }
    }


    /*
    |----------------------------------------------------------------
    | 3) Vérifier le montant payé
    |----------------------------------------------------------------
    */

    if ($erreur === "") {

        if (
            $montantPayeSaisi === "" ||
            !is_numeric($montantPayeSaisi) ||
            (float) $montantPayeSaisi < 0
        ) {

            $erreur = "Le montant payé est invalide.";
        }
    }


    /*
    |----------------------------------------------------------------
    | 4) Enregistrer la vente
    |----------------------------------------------------------------
    */

    if ($erreur === "") {

        try {

            $pdo->beginTransaction();


            // ---- Client ----

            $utilisateurId = $utilisateurIdSelectionne;

            if ($modeClient === "nouveau") {

                $motDePasseHash = password_hash(
                    bin2hex(random_bytes(8)),
                    PASSWORD_DEFAULT
                );

                $requeteClient = $pdo->prepare("
                    INSERT INTO utilisateurs
                    (nom, email, telephone, mot_de_passe, role, type_client)
                    VALUES (?, ?, ?, ?, 'client', 'boutique')
                ");

                $requeteClient->execute([
                    $nouveauNom,
                    $nouveauEmail !== "" ? $nouveauEmail : null,
                    $nouveauTelephone !== "" ? $nouveauTelephone : null,
                    $motDePasseHash
                ]);

                $utilisateurId = (int) $pdo->lastInsertId();
            }

            $verifClient = $pdo->prepare("
                SELECT id FROM utilisateurs WHERE id = ?
            ");

            $verifClient->execute([$utilisateurId]);

            if (!$verifClient->fetch()) {
                throw new Exception("Le client sélectionné est introuvable.");
            }


            // ---- Produits + stock ----

            $total = 0;
            $produitsVendus = [];

            foreach ($lignesVente as $produitId => $quantite) {

                $requeteProduit = $pdo->prepare("
                    SELECT id, nom, prix, stock
                    FROM produits
                    WHERE id = ?
                    FOR UPDATE
                ");

                $requeteProduit->execute([$produitId]);

                $produit = $requeteProduit->fetch(PDO::FETCH_ASSOC);

                if (!$produit) {
                    throw new Exception("Un des produits sélectionnés n'existe plus.");
                }

                if ($quantite > (int) $produit["stock"]) {

                    throw new Exception(
                        "Le stock du produit « " . $produit["nom"] . " » est insuffisant."
                    );
                }

                $sousTotal = (float) $produit["prix"] * $quantite;

                $total += $sousTotal;

                $produitsVendus[] = [
                    "id" => $produit["id"],
                    "prix" => $produit["prix"],
                    "quantite" => $quantite
                ];
            }


            // ---- Paiement ----

            $montantPaye = (float) $montantPayeSaisi;

            if ($montantPaye > $total) {
                throw new Exception("Le montant payé ne peut pas dépasser le total de la vente.");
            }


            // ---- Commande ----

            $requeteCommande = $pdo->prepare("
                INSERT INTO commandes
                (utilisateur_id, total, statut, type_vente)
                VALUES (?, ?, 'Livrée', 'boutique')
            ");

            $requeteCommande->execute([
                $utilisateurId,
                $total
            ]);

            $commandeId = $pdo->lastInsertId();


            // ---- Détails de la commande + stock ----

            $requeteDetail = $pdo->prepare("
                INSERT INTO commande_details
                (commande_id, produit_id, quantite, prix)
                VALUES (?, ?, ?, ?)
            ");

            $requeteStock = $pdo->prepare("
                UPDATE produits SET stock = stock - ? WHERE id = ?
            ");

            foreach ($produitsVendus as $ligne) {

                $requeteDetail->execute([
                    $commandeId,
                    $ligne["id"],
                    $ligne["quantite"],
                    $ligne["prix"]
                ]);

                $requeteStock->execute([
                    $ligne["quantite"],
                    $ligne["id"]
                ]);
            }


            // ---- Facture ----

            $resteAPayer = $total - $montantPaye;

            if ($resteAPayer <= 0) {

                $statutFacture = "Payée";

            } elseif ($montantPaye > 0) {

                $statutFacture = "Partiellement payée";

            } else {

                $statutFacture = "Impayée";
            }

            $annee = date("Y");

            $derniereFacture = $pdo
                ->query("SELECT id FROM factures ORDER BY id DESC LIMIT 1 FOR UPDATE")
                ->fetch(PDO::FETCH_ASSOC);

            $numero = $derniereFacture
                ? ((int) $derniereFacture["id"] + 1)
                : 1;

            $numeroFacture = "FAC-" . $annee . "-" . str_pad(
                $numero,
                5,
                "0",
                STR_PAD_LEFT
            );

            $requeteFacture = $pdo->prepare("
                INSERT INTO factures
                (commande_id, numero_facture, montant_total, montant_paye, reste_a_payer, statut)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $requeteFacture->execute([
                $commandeId,
                $numeroFacture,
                $total,
                $montantPaye,
                $resteAPayer,
                $statutFacture
            ]);


            // ---- Crédit client si reste à payer ----

            if ($resteAPayer > 0) {

                $requeteCredit = $pdo->prepare("
                    INSERT INTO credits_clients
                    (utilisateur_id, commande_id, montant_total, montant_paye, reste_a_payer, statut)
                    VALUES (?, ?, ?, ?, ?, 'En cours')
                ");

                $requeteCredit->execute([
                    $utilisateurId,
                    $commandeId,
                    $total,
                    $montantPaye,
                    $resteAPayer
                ]);

                if ($montantPaye > 0) {

                    $creditId = $pdo->lastInsertId();

                    $requetePaiement = $pdo->prepare("
                        INSERT INTO paiements_credits
                        (credit_id, montant, commentaire)
                        VALUES (?, ?, ?)
                    ");

                    $requetePaiement->execute([
                        $creditId,
                        $montantPaye,
                        $commentaire !== "" ? $commentaire : "Paiement initial (vente boutique)"
                    ]);
                }
            }


            $pdo->commit();


            header("Location: commande-details.php?id=" . $commandeId . "&vente=1");
            exit;


        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $erreur = $e->getMessage();
        }
    }
}


?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nouvelle vente - NPM Admin</title>

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


<body class="bg-light">


<?php require_once("includes/navbar.php"); ?>

<?php require_once("includes/sidebar.php"); ?>


<main class="admin-content">

    <div class="container-fluid py-4">


        <!-- En-tête -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1 class="fw-bold mb-1">
                    <i class="bi bi-cart-plus me-2"></i>
                    Nouvelle vente en boutique
                </h1>

                <p class="text-muted mb-0">
                    Pour un client qui achète directement au magasin.
                </p>

            </div>

            <a href="commandes.php" class="btn btn-outline-dark">
                <i class="bi bi-arrow-left me-2"></i>
                Retour aux commandes
            </a>

        </div>


        <!-- Erreur -->

        <?php if ($erreur !== ""): ?>

            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($erreur) ?>
            </div>

        <?php endif; ?>


        <?php if (empty($produits)): ?>

            <div class="alert alert-warning">
                <i class="bi bi-exclamation-circle me-2"></i>
                Aucun produit en stock. Ajoute du stock avant d'enregistrer une vente.
            </div>

        <?php else: ?>


        <form method="POST" id="formVente">
            <?= csrf_champ() ?>

            <div class="row g-4">


                <!-- ================================================
                     CLIENT
                ================================================= -->

                <div class="col-lg-5">

                    <div class="card border-0 shadow-sm mb-4">

                        <div class="card-header bg-white py-3">

                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-person me-2"></i>
                                Client
                            </h5>

                        </div>

                        <div class="card-body">


                            <div class="btn-group w-100 mb-3" role="group">

                                <input
                                    type="radio"
                                    class="btn-check"
                                    name="mode_client"
                                    id="modeExistant"
                                    value="existant"
                                    <?= $modeClient !== "nouveau" ? "checked" : "" ?>
                                >

                                <label class="btn btn-outline-dark" for="modeExistant">
                                    Client existant
                                </label>


                                <input
                                    type="radio"
                                    class="btn-check"
                                    name="mode_client"
                                    id="modeNouveau"
                                    value="nouveau"
                                    <?= $modeClient === "nouveau" ? "checked" : "" ?>
                                >

                                <label class="btn btn-outline-dark" for="modeNouveau">
                                    Nouveau client
                                </label>

                            </div>


                            <!-- Client existant -->

                            <div id="blocClientExistant">

                                <label class="form-label fw-semibold">
                                    Sélectionner un client
                                </label>

                                <select name="utilisateur_id" class="form-select">

                                    <option value="">
                                        -- Choisir un client --
                                    </option>

                                    <?php foreach ($clients as $client): ?>

                                        <option
                                            value="<?= $client["id"] ?>"
                                            <?= $utilisateurIdSelectionne == $client["id"] ? "selected" : "" ?>
                                        >

                                            <?= htmlspecialchars($client["nom"]) ?>

                                            <?php if (!empty($client["telephone"])): ?>
                                                — <?= htmlspecialchars($client["telephone"]) ?>
                                            <?php elseif (!empty($client["email"])): ?>
                                                — <?= htmlspecialchars($client["email"]) ?>
                                            <?php endif; ?>

                                            <?= $client["type_client"] === "boutique" ? " (boutique)" : "" ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- Nouveau client -->

                            <div id="blocClientNouveau" class="d-none">

                                <div class="mb-3">

                                    <label class="form-label fw-semibold">
                                        Nom complet
                                    </label>

                                    <input
                                        type="text"
                                        name="nouveau_nom"
                                        class="form-control"
                                        value="<?= htmlspecialchars($nouveauNom) ?>"
                                    >

                                </div>

                                <div class="mb-3">

                                    <label class="form-label fw-semibold">
                                        Téléphone
                                    </label>

                                    <input
                                        type="tel"
                                        name="nouveau_telephone"
                                        class="form-control"
                                        placeholder="Ex : 77 000 00 00"
                                        value="<?= htmlspecialchars($nouveauTelephone) ?>"
                                    >

                                    <small class="text-muted">
                                        Facultatif — laisse vide si le client ne le donne pas.
                                    </small>

                                </div>

                                <div class="mb-1">

                                    <label class="form-label fw-semibold">
                                        Email
                                    </label>

                                    <input
                                        type="email"
                                        name="nouveau_email"
                                        class="form-control"
                                        placeholder="Facultatif"
                                        value="<?= htmlspecialchars($nouveauEmail) ?>"
                                    >

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- ============================================
                         PAIEMENT
                    ============================================= -->

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white py-3">

                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-cash-coin me-2"></i>
                                Paiement
                            </h5>

                        </div>

                        <div class="card-body">


                            <div class="alert alert-light border d-flex justify-content-between align-items-center">

                                <span class="fw-semibold">Total de la vente</span>

                                <strong id="totalVente" class="fs-5">
                                    0 FCFA
                                </strong>

                            </div>


                            <div class="form-check form-switch mb-3">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="paiement_partiel"
                                    id="paiementPartiel"
                                    <?= $paiementPartiel ? "checked" : "" ?>
                                >

                                <label class="form-check-label" for="paiementPartiel">
                                    Le client ne paie pas la totalité aujourd'hui (crédit)
                                </label>

                            </div>


                            <label class="form-label fw-semibold">
                                Montant payé maintenant
                            </label>

                            <div class="input-group mb-2">

                                <input
                                    type="number"
                                    name="montant_paye"
                                    id="montantPaye"
                                    class="form-control"
                                    min="0"
                                    step="1"
                                    value="<?= htmlspecialchars($montantPayeSaisi) ?>"
                                    required
                                >

                                <span class="input-group-text">FCFA</span>

                            </div>

                            <div class="form-text mb-3" id="resteInfo">
                                Reste à payer : 0 FCFA
                            </div>


                            <div class="mb-1">

                                <label class="form-label fw-semibold">
                                    Commentaire
                                    <span class="text-muted">(facultatif)</span>
                                </label>

                                <textarea
                                    name="commentaire"
                                    class="form-control"
                                    rows="2"
                                ><?= htmlspecialchars($commentaire) ?></textarea>

                            </div>


                        </div>

                    </div>

                </div>



                <!-- ================================================
                     PRODUITS
                ================================================= -->

                <div class="col-lg-7">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white py-3">

                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-box-seam me-2"></i>
                                Produits achetés
                            </h5>

                        </div>

                        <div class="card-body p-0">

                            <div class="table-responsive">

                                <table class="table table-hover align-middle mb-0" id="tableProduits">

                                    <thead class="table-dark">

                                        <tr>
                                            <th>Produit</th>
                                            <th>Prix</th>
                                            <th>Stock dispo</th>
                                            <th style="width:120px;">Quantité</th>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        <?php foreach ($produits as $produit): ?>

                                            <tr>

                                                <td>
                                                    <strong>
                                                        <?= htmlspecialchars($produit["nom"]) ?>
                                                    </strong>
                                                </td>

                                                <td
                                                    class="prix-produit"
                                                    data-prix="<?= (float) $produit["prix"] ?>"
                                                >
                                                    <?= number_format($produit["prix"], 0, ",", " ") ?>
                                                    FCFA
                                                </td>

                                                <td>
                                                    <?= (int) $produit["stock"] ?>
                                                </td>

                                                <td>

                                                    <input
                                                        type="number"
                                                        name="quantite[<?= $produit["id"] ?>]"
                                                        class="form-control input-quantite"
                                                        min="0"
                                                        max="<?= (int) $produit["stock"] ?>"
                                                        value="<?= isset($quantitesSaisies[$produit["id"]]) ? (int) $quantitesSaisies[$produit["id"]] : 0 ?>"
                                                    >

                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>


                    <div class="d-flex justify-content-end gap-2 mt-4">

                        <a href="commandes.php" class="btn btn-outline-secondary">
                            Annuler
                        </a>

                        <button type="submit" class="btn btn-warning px-4">
                            <i class="bi bi-check-circle me-2"></i>
                            Enregistrer la vente
                        </button>

                    </div>

                </div>


            </div>

        </form>


        <?php endif; ?>


    </div>

</main>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<!-- Logique du formulaire -->

<script>

const blocClientExistant = document.getElementById("blocClientExistant");
const blocClientNouveau = document.getElementById("blocClientNouveau");

const radiosClient = document.querySelectorAll('input[name="mode_client"]');

function majModeClient() {

    const modeNouveau = document.getElementById("modeNouveau").checked;

    blocClientExistant.classList.toggle("d-none", modeNouveau);
    blocClientNouveau.classList.toggle("d-none", !modeNouveau);
}

radiosClient.forEach(function (radio) {
    radio.addEventListener("change", majModeClient);
});

majModeClient();


const inputsQuantite = document.querySelectorAll(".input-quantite");
const totalVenteEl = document.getElementById("totalVente");
const montantPayeEl = document.getElementById("montantPaye");
const resteInfoEl = document.getElementById("resteInfo");
const paiementPartielEl = document.getElementById("paiementPartiel");

let totalActuel = 0;

function calculerTotal() {

    let total = 0;

    inputsQuantite.forEach(function (input) {

        const ligne = input.closest("tr");
        const prix = parseFloat(
            ligne.querySelector(".prix-produit").dataset.prix
        ) || 0;

        const quantite = parseInt(input.value) || 0;

        total += prix * quantite;
    });

    totalActuel = total;

    totalVenteEl.textContent = total.toLocaleString("fr-FR") + " FCFA";

    if (!paiementPartielEl.checked) {
        montantPayeEl.value = total;
    }

    calculerReste();
}

function calculerReste() {

    const paye = parseFloat(montantPayeEl.value) || 0;

    let reste = totalActuel - paye;

    if (reste < 0) {
        reste = 0;
    }

    resteInfoEl.textContent = "Reste à payer : " + reste.toLocaleString("fr-FR") + " FCFA";
}

function majPaiementPartiel() {

    if (paiementPartielEl.checked) {

        montantPayeEl.readOnly = false;
        montantPayeEl.value = 0;

    } else {

        montantPayeEl.readOnly = true;
        montantPayeEl.value = totalActuel;
    }

    calculerReste();
}

inputsQuantite.forEach(function (input) {
    input.addEventListener("input", calculerTotal);
});

montantPayeEl.addEventListener("input", calculerReste);

paiementPartielEl.addEventListener("change", majPaiementPartiel);


// Initialisation
calculerTotal();
majPaiementPartiel();

</script>


</body>

</html>
