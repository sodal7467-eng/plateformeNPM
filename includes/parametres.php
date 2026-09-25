<?php

/*
|--------------------------------------------------------------------------
| Helpers : paramètres modifiables de la boutique
|--------------------------------------------------------------------------
| Toutes les valeurs sont chargées une seule fois et mises en cache
| dans une variable statique.
*/

function parametresTous(PDO $pdo): array
{
    static $cache = null;

    if ($cache !== null) {
        return $cache;
    }

    try {
        $requete = $pdo->query("SELECT cle, valeur FROM parametres");
        $cache = [];
        foreach ($requete->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
            $cache[$ligne["cle"]] = $ligne["valeur"];
        }
    } catch (Throwable $e) {
        // Table absente ou DB down : ne pas casser le site
        $cache = [];
    }

    return $cache;
}


function parametre(PDO $pdo, string $cle, ?string $defaut = ""): string
{
    $valeurs = parametresTous($pdo);
    return $valeurs[$cle] ?? ($defaut ?? "");
}


/*
|--------------------------------------------------------------------------
| Mise à jour d'un paramètre (utilisée par la page admin)
|--------------------------------------------------------------------------
*/

function parametreDefinir(PDO $pdo, string $cle, string $valeur): void
{
    $requete = $pdo->prepare("
        INSERT INTO parametres (cle, valeur)
        VALUES (:cle, :valeur)
        ON DUPLICATE KEY UPDATE valeur = :valeur
    ");

    $requete->execute([
        "cle" => $cle,
        "valeur" => $valeur,
    ]);
}
