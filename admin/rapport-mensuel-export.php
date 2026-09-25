<?php

/*
|--------------------------------------------------------------------------
| Export CSV / Excel du rapport mensuel
|--------------------------------------------------------------------------
| Genere un fichier CSV (compatible Excel, Google Sheets, LibreOffice)
| avec :
|   - Un resume des 12 derniers mois (ventes / depenses / benefice)
|   - Le detail de toutes les commandes du dernier mois disponible
|   - Le detail de toutes les depenses du dernier mois disponible
|
| Utilisation : le bouton "Exporter en Excel" sur rapport-mensuel.php.
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("includes/auth.php");
require_once("../config/database.php");
require_once("../includes/parametres.php");
require_once("../includes/log_admin.php");


// -----------------------------------------------------------------
// Preparation du fichier CSV
// -----------------------------------------------------------------
$nomBoutique = parametre($pdo, "nom_boutique", "Ma boutique");
$devise      = parametre($pdo, "devise", "FCFA");

$nomFichier = "rapport_" . preg_replace('/[^a-z0-9]+/i', '_', $nomBoutique)
            . "_" . date("Y-m-d") . ".csv";

header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=\"{$nomFichier}\"");
header("Cache-Control: no-store");

$out = fopen("php://output", "w");

// BOM UTF-8 pour qu'Excel ouvre correctement les caracteres accentues
fwrite($out, "\xEF\xBB\xBF");

// On utilise le point-virgule comme separateur (standard francais / Excel FR)
$sep = ";";

function ligne($out, array $cells, string $sep = ";") {
    fputcsv($out, $cells, $sep, '"', "\\");
}


// =================================================================
// EN-TETE DU DOCUMENT
// =================================================================
ligne($out, [$nomBoutique . " - Rapport financier"], $sep);
ligne($out, ["Genere le", date("d/m/Y H:i")], $sep);
ligne($out, ["Devise", $devise], $sep);
ligne($out, [], $sep);


// =================================================================
// 1) RESUME DES 12 DERNIERS MOIS
// =================================================================
ligne($out, ["RESUME DES 12 DERNIERS MOIS"], $sep);
ligne($out, ["Mois", "Nombre de commandes", "Ventes (" . $devise . ")", "Depenses (" . $devise . ")", "Benefice (" . $devise . ")"], $sep);

$mois = [];
for ($i = 11; $i >= 0; $i--) {
    $mois[] = date("Y-m", strtotime("-$i months"));
}

$requeteVentes = $pdo->prepare("
    SELECT
        DATE_FORMAT(date_commande, '%Y-%m') AS mois,
        SUM(total) AS total_ventes,
        COUNT(*) AS nombre_commandes
    FROM commandes
    WHERE statut != 'Annulée'
      AND date_commande >= ?
    GROUP BY mois
");
$requeteVentes->execute([$mois[0] . "-01"]);
$ventesParMois = [];
foreach ($requeteVentes->fetchAll(PDO::FETCH_ASSOC) as $l) {
    $ventesParMois[$l["mois"]] = $l;
}

$requeteDepenses = $pdo->prepare("
    SELECT
        DATE_FORMAT(date_depense, '%Y-%m') AS mois,
        SUM(montant) AS total_depenses
    FROM depenses
    WHERE date_depense >= ?
    GROUP BY mois
");
$requeteDepenses->execute([$mois[0] . "-01"]);
$depensesParMois = [];
foreach ($requeteDepenses->fetchAll(PDO::FETCH_ASSOC) as $l) {
    $depensesParMois[$l["mois"]] = (float) $l["total_depenses"];
}

$totalVentes = 0;
$totalDepenses = 0;
$totalCommandes = 0;

foreach ($mois as $m) {
    $v = isset($ventesParMois[$m]) ? (float) $ventesParMois[$m]["total_ventes"] : 0;
    $d = $depensesParMois[$m] ?? 0;
    $n = isset($ventesParMois[$m]) ? (int) $ventesParMois[$m]["nombre_commandes"] : 0;

    $totalVentes    += $v;
    $totalDepenses  += $d;
    $totalCommandes += $n;

    ligne($out, [
        $m,
        $n,
        number_format($v, 0, ",", " "),
        number_format($d, 0, ",", " "),
        number_format($v - $d, 0, ",", " "),
    ], $sep);
}

ligne($out, [
    "TOTAL",
    $totalCommandes,
    number_format($totalVentes, 0, ",", " "),
    number_format($totalDepenses, 0, ",", " "),
    number_format($totalVentes - $totalDepenses, 0, ",", " "),
], $sep);

ligne($out, [], $sep);


// =================================================================
// 2) DETAIL DES COMMANDES DES 12 DERNIERS MOIS
// =================================================================
ligne($out, ["DETAIL DES COMMANDES"], $sep);
ligne($out, ["Date", "N. commande", "Client", "Type de vente", "Statut", "Total (" . $devise . ")"], $sep);

$reqCommandes = $pdo->prepare("
    SELECT
        c.id,
        c.date_commande,
        c.total,
        c.statut,
        c.type_vente,
        u.nom AS client_nom
    FROM commandes c
    LEFT JOIN utilisateurs u ON u.id = c.utilisateur_id
    WHERE c.date_commande >= ?
      AND c.statut != 'Annulée'
    ORDER BY c.date_commande DESC
");
$reqCommandes->execute([$mois[0] . "-01"]);

foreach ($reqCommandes->fetchAll(PDO::FETCH_ASSOC) as $c) {
    ligne($out, [
        date("d/m/Y H:i", strtotime($c["date_commande"])),
        $c["id"],
        $c["client_nom"] ?? "-",
        $c["type_vente"] ?? "-",
        $c["statut"] ?? "-",
        number_format((float) $c["total"], 0, ",", " "),
    ], $sep);
}

ligne($out, [], $sep);


// =================================================================
// 3) DETAIL DES DEPENSES DES 12 DERNIERS MOIS
// =================================================================
ligne($out, ["DETAIL DES DEPENSES"], $sep);
ligne($out, ["Date", "Categorie", "Description", "Montant (" . $devise . ")"], $sep);

$reqDepenses = $pdo->prepare("
    SELECT date_depense, categorie, description, montant
    FROM depenses
    WHERE date_depense >= ?
    ORDER BY date_depense DESC
");
$reqDepenses->execute([$mois[0] . "-01"]);

foreach ($reqDepenses->fetchAll(PDO::FETCH_ASSOC) as $d) {
    ligne($out, [
        date("d/m/Y", strtotime($d["date_depense"])),
        $d["categorie"] ?? "-",
        $d["description"] ?? "-",
        number_format((float) $d["montant"], 0, ",", " "),
    ], $sep);
}

fclose($out);

logAdmin(
    $pdo,
    "rapport.export",
    "rapport",
    null,
    "Export CSV du rapport financier (12 derniers mois)."
);

exit;
