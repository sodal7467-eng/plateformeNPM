<?php
/*
|--------------------------------------------------------------------------
| Lien public d'une facture (envoi par WhatsApp / email)
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/../config/env.php";


/*
| Renvoie le token public de la facture, en le créant s'il n'existe pas
| encore (les factures n'en reçoivent pas à leur création).
*/
function factureTokenPublic(PDO $pdo, int $factureId, ?string $tokenActuel): string
{
    if (!empty($tokenActuel)) {
        return $tokenActuel;
    }

    $token = bin2hex(random_bytes(24));

    // "token_public IS NULL" : ne pas écraser un token créé entre-temps
    $requete = $pdo->prepare("
        UPDATE factures
        SET token_public = ?
        WHERE id = ? AND (token_public IS NULL OR token_public = '')
    ");
    $requete->execute([$token, $factureId]);

    if ($requete->rowCount() === 0) {
        $relire = $pdo->prepare("SELECT token_public FROM factures WHERE id = ?");
        $relire->execute([$factureId]);
        $token = (string) $relire->fetchColumn();
    }

    return $token;
}


/*
| URL complète de la facture publique.
| Utilise APP_URL (.env) : le client doit pouvoir ouvrir le lien depuis
| son téléphone, ce qui est impossible avec "localhost".
*/
function factureUrlPublique(string $token): string
{
    $base = rtrim((string) env("APP_URL", ""), "/");

    if ($base === "") {
        $scheme = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
        $host   = $_SERVER["HTTP_HOST"] ?? "localhost";
        $chemin = rtrim(dirname($_SERVER["PHP_SELF"] ?? "/"), "/\\");
        $chemin = preg_replace('#/admin/?$#', '', $chemin);
        $base   = $scheme . "://" . $host . $chemin;
    }

    return $base . "/facture-publique.php?token=" . rawurlencode($token);
}
