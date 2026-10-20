<?php
/*
|--------------------------------------------------------------------------
| FACTURE PUBLIQUE — accessible par token, sans connexion
|--------------------------------------------------------------------------
| Utilise pour envoyer une facture au client par WhatsApp / email.
| Le client ouvre le lien, voit la facture en lecture seule, peut
| l'imprimer ou l'enregistrer en PDF via le navigateur.
*/

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/includes/parametres.php";

// -----------------------------------------------------------------
// Validation du token
// -----------------------------------------------------------------
$token = trim($_GET["token"] ?? "");

if ($token === "" || !preg_match('/^[a-f0-9]{16,64}$/', $token)) {
    http_response_code(404);
    exit(pageErreur("Lien invalide ou expiré."));
}

// -----------------------------------------------------------------
// Recuperation de la facture, commande et client
// -----------------------------------------------------------------
$req = $pdo->prepare("
    SELECT
        f.id             AS facture_id,
        f.numero_facture,
        f.date_facture,
        f.montant_total,
        f.montant_paye,
        f.reste_a_payer,
        f.statut,
        c.id             AS commande_id,
        c.date_commande,
        c.type_vente,
        u.nom            AS client_nom,
        u.email          AS client_email,
        u.telephone      AS client_telephone
    FROM factures f
    INNER JOIN commandes c   ON c.id = f.commande_id
    INNER JOIN utilisateurs u ON u.id = c.utilisateur_id
    WHERE f.token_public = ?
    LIMIT 1
");
$req->execute([$token]);
$facture = $req->fetch(PDO::FETCH_ASSOC);

if (!$facture) {
    http_response_code(404);
    exit(pageErreur("Facture introuvable. Le lien est peut-être invalide."));
}

// Lignes de la commande
$reqLignes = $pdo->prepare("
    SELECT
        cd.quantite,
        cd.prix,
        p.nom AS produit_nom
    FROM commande_details cd
    INNER JOIN produits p ON p.id = cd.produit_id
    WHERE cd.commande_id = ?
    ORDER BY cd.id ASC
");
$reqLignes->execute([$facture["commande_id"]]);
$lignes = $reqLignes->fetchAll(PDO::FETCH_ASSOC);

// Parametres boutique
$params = parametresTous($pdo);
$nomBoutique     = $params["nom_boutique"]     ?? "Notre boutique";
$slogan          = $params["slogan"]           ?? "";
$adresse         = $params["adresse"]          ?? "";
$telephone       = $params["telephone"]        ?? "";
$emailContact    = $params["email_contact"]    ?? "";
$devise          = $params["devise"]           ?? "FCFA";
$logoTexte       = $params["logo_texte"]       ?? "NPM";
$logoSoustitre   = $params["logo_soustitre"]   ?? "QUINCAILLERIE";

// Utilitaire de format
function fmt(float $n, string $devise = "FCFA"): string {
    return number_format($n, 0, ",", " ") . " " . $devise;
}

// Badge de statut
$badgeCouleur = "#6c757d";
$badgeIcone = "";
switch ($facture["statut"]) {
    case "Payée":               $badgeCouleur = "#198754"; $badgeIcone = "&#10003;"; break;
    case "Partiellement payée": $badgeCouleur = "#fd7e14"; $badgeIcone = "&#8987;"; break;
    case "Impayée":             $badgeCouleur = "#dc3545"; $badgeIcone = "&#10007;"; break;
}


function pageErreur(string $message): string {
    return "<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'>"
         . "<title>Facture introuvable</title>"
         . "<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css'>"
         . "</head><body class='bg-light'><div class='container py-5'>"
         . "<div class='alert alert-warning'><h3>Facture introuvable</h3>"
         . "<p>" . htmlspecialchars($message) . "</p></div></div></body></html>";
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture <?= htmlspecialchars($facture["numero_facture"]) ?> — <?= htmlspecialchars($nomBoutique) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap"
    >

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 20px;
            background: #eef1f5;
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #0d1b2a;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Barre d'actions en haut (visible que a l'ecran) */
        .toolbar {
            max-width: 800px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
        .btn {
            background: #0d1b2a;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-primary { background: #ff8c00; }
        .btn:hover { opacity: 0.9; }

        /* Le "papier" A4 */
        .invoice {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 40px 50px;
            border-radius: 4px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #ff8c00;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .brand-name {
            font-size: 26px;
            font-weight: 700;
            color: #0d1b2a;
            margin: 0;
        }
        .brand-name .accent { color: #ff8c00; }
        .brand-subtitle {
            font-size: 11px;
            letter-spacing: 2px;
            color: #666;
            margin-top: 4px;
        }
        .brand-slogan {
            font-size: 12px;
            color: #666;
            margin-top: 8px;
            font-style: italic;
        }
        .invoice-title-box {
            text-align: right;
        }
        .invoice-title {
            font-size: 32px;
            font-weight: 700;
            color: #0d1b2a;
            margin: 0;
            letter-spacing: 2px;
        }
        .invoice-number {
            font-size: 13px;
            color: #666;
            margin-top: 4px;
        }

        .invoice-meta {
            display: flex;
            justify-content: space-between;
            gap: 30px;
            margin-bottom: 30px;
        }
        .meta-col { flex: 1; }
        .meta-label {
            font-size: 10px;
            letter-spacing: 1px;
            color: #999;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .meta-value {
            font-size: 14px;
            color: #0d1b2a;
        }
        .meta-value strong { font-weight: 700; }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* Table des lignes */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        table.items thead th {
            background: #0d1b2a;
            color: #fff;
            padding: 12px 10px;
            text-align: left;
            font-size: 12px;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        table.items thead th.right { text-align: right; }
        table.items tbody td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        table.items tbody td.right { text-align: right; }
        table.items tbody tr:last-child td { border-bottom: none; }
        table.items tbody tr:nth-child(even) { background: #fafbfc; }

        /* Totaux */
        .totals {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 25px;
        }
        .totals-table {
            min-width: 300px;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 8px 12px;
            font-size: 14px;
        }
        .totals-table td.label { color: #666; text-align: right; }
        .totals-table td.value { text-align: right; font-weight: 600; }
        .totals-table tr.total-final td {
            font-size: 18px;
            font-weight: 700;
            color: #ff8c00;
            border-top: 2px solid #ff8c00;
            padding-top: 12px;
        }

        .footer-note {
            border-top: 1px solid #eee;
            padding-top: 20px;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .footer-note .thanks {
            font-size: 14px;
            color: #0d1b2a;
            font-weight: 600;
            margin-bottom: 6px;
        }

        /* Empeche l'utilisateur de modifier quoi que ce soit */
        .invoice, .invoice * {
            user-select: text; /* permet la selection pour copier, mais pas modifier */
        }

        /* =========================
           IMPRESSION / PDF
        ========================= */
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none !important; }
            .invoice {
                box-shadow: none;
                padding: 20px;
                border-radius: 0;
                max-width: 100%;
                margin: 0;
            }
        }

        /* Mobile */
        @media (max-width: 640px) {
            body { padding: 10px; }
            .invoice { padding: 24px 20px; }
            .invoice-header { flex-direction: column; gap: 15px; }
            .invoice-title-box { text-align: left; }
            .invoice-meta { flex-direction: column; gap: 15px; }
            table.items thead th, table.items tbody td { font-size: 12px; padding: 8px 6px; }
        }
    </style>
</head>
<body>

<div class="toolbar">
    <button
        type="button"
        class="btn btn-primary"
        id="btnTelechargerFacture"
    >
        📥 Télécharger la facture (PDF)
    </button>
    <button
        type="button"
        class="btn"
        onclick="window.print()"
    >
        🖨️ Imprimer
    </button>
</div>

<article class="invoice" aria-label="Facture">

    <header class="invoice-header">
        <div>
            <h1 class="brand-name">
                <span class="accent"><?= htmlspecialchars($logoTexte) ?></span>
                <?= htmlspecialchars(preg_replace('/^' . preg_quote($logoTexte, '/') . '\s*/i', '', $nomBoutique) ?: $nomBoutique) ?>
            </h1>
            <div class="brand-subtitle"><?= htmlspecialchars($logoSoustitre) ?></div>
            <?php if ($slogan): ?>
                <div class="brand-slogan"><?= htmlspecialchars($slogan) ?></div>
            <?php endif; ?>
        </div>
        <div class="invoice-title-box">
            <div class="invoice-title">FACTURE</div>
            <div class="invoice-number"><?= htmlspecialchars($facture["numero_facture"]) ?></div>
        </div>
    </header>

    <section class="invoice-meta">
        <div class="meta-col">
            <div class="meta-label">Facturé à</div>
            <div class="meta-value"><strong><?= htmlspecialchars($facture["client_nom"]) ?></strong></div>
            <?php if ($facture["client_telephone"]): ?>
                <div class="meta-value">📞 <?= htmlspecialchars($facture["client_telephone"]) ?></div>
            <?php endif; ?>
            <?php if ($facture["client_email"]): ?>
                <div class="meta-value">✉️ <?= htmlspecialchars($facture["client_email"]) ?></div>
            <?php endif; ?>
        </div>
        <div class="meta-col" style="text-align:right;">
            <div class="meta-label">Informations</div>
            <div class="meta-value">Commande <?= (int) $facture["commande_id"] ?></div>
            <div class="meta-value">Date : <?= date("d/m/Y", strtotime($facture["date_commande"])) ?></div>
            <div style="margin-top:8px;">
                <span class="status-badge" style="background: <?= $badgeCouleur ?>;">
                    <?= $badgeIcone ?> <?= htmlspecialchars($facture["statut"]) ?>
                </span>
            </div>
        </div>
    </section>

    <?php if (count($lignes) > 0): ?>
        <table class="items">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th class="right">Quantité</th>
                    <th class="right">Prix unitaire</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lignes as $l): ?>
                    <?php $ligneTotal = (float) $l["prix"] * (int) $l["quantite"]; ?>
                    <tr>
                        <td><?= htmlspecialchars($l["produit_nom"]) ?></td>
                        <td class="right"><?= (int) $l["quantite"] ?></td>
                        <td class="right"><?= fmt((float) $l["prix"], $devise) ?></td>
                        <td class="right"><strong><?= fmt($ligneTotal, $devise) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align:center; color:#999; padding:20px 0;">
            <em>Détail des articles non disponible.</em>
        </p>
    <?php endif; ?>

    <div class="totals">
        <table class="totals-table">
            <tr>
                <td class="label">Montant total</td>
                <td class="value"><?= fmt((float) $facture["montant_total"], $devise) ?></td>
            </tr>
            <tr>
                <td class="label">Déjà payé</td>
                <td class="value" style="color:#198754;"><?= fmt((float) $facture["montant_paye"], $devise) ?></td>
            </tr>
            <tr class="total-final">
                <td class="label">Reste à payer</td>
                <td class="value"><?= fmt((float) $facture["reste_a_payer"], $devise) ?></td>
            </tr>
        </table>
    </div>

    <footer class="footer-note">
        <div class="thanks">Merci de votre confiance !</div>
        <?= htmlspecialchars($nomBoutique) ?>
        <?php if ($adresse): ?> &middot; <?= htmlspecialchars($adresse) ?><?php endif; ?>
        <?php if ($telephone): ?> &middot; <?= htmlspecialchars($telephone) ?><?php endif; ?>
        <?php if ($emailContact): ?> &middot; <?= htmlspecialchars($emailContact) ?><?php endif; ?>
        <div style="margin-top:10px; font-size:10px; color:#999;">
            Document généré le <?= date("d/m/Y à H:i") ?> &middot;
            Facture n° <?= htmlspecialchars($facture["numero_facture"]) ?>
        </div>
    </footer>

</article>

<!-- Téléchargement direct en PDF (fonctionne aussi sur téléphone) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function telechargerFacture() {
        const facture = document.querySelector(".invoice");

        if (!facture || typeof html2pdf === "undefined") {
            window.print();
            return;
        }

        html2pdf()
            .set({
                margin: 8,
                filename: <?= json_encode("facture-" . $facture["numero_facture"] . ".pdf") ?>,
                image: { type: "jpeg", quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: "mm", format: "a4", orientation: "portrait" }
            })
            .from(facture)
            .save();
    }

    document
        .getElementById("btnTelechargerFacture")
        .addEventListener("click", telechargerFacture);

    if (new URLSearchParams(window.location.search).get("telecharger") === "1") {
        window.addEventListener("load", telechargerFacture);
    }
</script>

</body>
</html>
