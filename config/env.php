<?php

/*
|--------------------------------------------------------------------------
| Chargeur de fichier .env
|--------------------------------------------------------------------------
| Lit le fichier .env à la racine du projet et remplit $_ENV.
| Ne remplace pas les variables déjà définies par le serveur.
*/

function chargerEnv(string $chemin): void
{
    if (!is_readable($chemin)) {
        return;
    }

    $lignes = file($chemin, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lignes as $ligne) {

        $ligne = trim($ligne);

        // Ignorer les commentaires
        if ($ligne === "" || strpos($ligne, "#") === 0) {
            continue;
        }

        // Séparer clé = valeur (uniquement au premier =)
        $position = strpos($ligne, "=");

        if ($position === false) {
            continue;
        }

        $cle = trim(substr($ligne, 0, $position));
        $valeur = trim(substr($ligne, $position + 1));

        // Retirer d'éventuelles guillemets
        if (
            (strlen($valeur) >= 2)
            && (
                ($valeur[0] === '"' && substr($valeur, -1) === '"')
                || ($valeur[0] === "'" && substr($valeur, -1) === "'")
            )
        ) {
            $valeur = substr($valeur, 1, -1);
        }

        // Ne pas écraser une variable déjà définie côté serveur
        if (!array_key_exists($cle, $_ENV)) {
            $_ENV[$cle] = $valeur;
            putenv("$cle=$valeur");
        }
    }
}


/*
|--------------------------------------------------------------------------
| Lecture d'une variable d'environnement avec valeur par défaut
|--------------------------------------------------------------------------
*/

function env(string $cle, $defaut = null)
{
    if (array_key_exists($cle, $_ENV)) {
        $valeur = $_ENV[$cle];
    } else {
        $valeur = getenv($cle);
        if ($valeur === false) {
            return $defaut;
        }
    }

    // Conversions utiles
    switch (strtolower((string) $valeur)) {
        case "true":  return true;
        case "false": return false;
        case "null":  return null;
        case "":      return $defaut;
    }

    return $valeur;
}


// Charger automatiquement le .env à la racine du projet
chargerEnv(dirname(__DIR__) . "/.env");
