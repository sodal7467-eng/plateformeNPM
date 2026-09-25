<?php

/*
|--------------------------------------------------------------------------
| Helpers CSRF (protection contre Cross-Site Request Forgery)
|--------------------------------------------------------------------------
| Fichier commun a l'admin ET au site public. A inclure via
| require_once, apres session_start().
|
|   - csrf_champ()  : <input type="hidden"> a inserer dans les formulaires
|   - csrf_verifier() : a appeler AVANT tout traitement POST.
|                       Coupe l'execution proprement si jeton invalide.
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Genere le token si absent
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}


if (!function_exists("csrf_champ")) {
    function csrf_champ(): string
    {
        $token = $_SESSION["csrf_token"] ?? "";
        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars($token, ENT_QUOTES) . '">';
    }
}


if (!function_exists("csrf_verifier")) {
    function csrf_verifier(): void
    {
        if (
            !isset($_POST["csrf_token"])
            || !isset($_SESSION["csrf_token"])
            || !hash_equals($_SESSION["csrf_token"], (string) $_POST["csrf_token"])
        ) {
            http_response_code(403);
            die("Erreur de sécurité : requête invalide (jeton CSRF manquant ou incorrect).");
        }
    }
}
