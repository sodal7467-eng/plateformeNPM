<?php

/*
|--------------------------------------------------------------------------
| Rate limiting du login
|--------------------------------------------------------------------------
| Limite les tentatives de connexion par IP sur une fenêtre glissante.
*/

require_once __DIR__ . "/../config/env.php";


function loginIp(): string
{
    return $_SERVER["REMOTE_ADDR"] ?? "0.0.0.0";
}


function loginEnregistrerTentative(PDO $pdo, string $identifiant, bool $succes): void
{
    try {
        $requete = $pdo->prepare("
            INSERT INTO tentatives_connexion (ip, identifiant, succes)
            VALUES (:ip, :identifiant, :succes)
        ");
        $requete->execute([
            "ip" => loginIp(),
            "identifiant" => mb_substr($identifiant, 0, 150),
            "succes" => $succes ? 1 : 0,
        ]);
    } catch (Throwable $e) {
        error_log("loginEnregistrerTentative: " . $e->getMessage());
    }
}


/*
|--------------------------------------------------------------------------
| Retourne le nombre de secondes restantes de blocage,
| ou 0 si l'IP peut retenter.
|--------------------------------------------------------------------------
*/

function loginBlocageRestant(PDO $pdo): int
{
    $maxTentatives = (int) env("LOGIN_MAX_ATTEMPTS", 5);
    $lockoutMin    = (int) env("LOGIN_LOCKOUT_MINUTES", 15);

    if ($maxTentatives <= 0) {
        return 0;
    }

    try {
        // Compte les échecs récents et calcule le temps restant côté MySQL
        // (évite les problèmes de timezone entre PHP et MySQL).
        // INTERVAL n'accepte pas une valeur bindée : on caste et on injecte.
        $lockoutMin = max(1, (int) $lockoutMin);

        $requete = $pdo->prepare("
            SELECT COUNT(*) AS nb,
                   TIMESTAMPDIFF(
                       SECOND,
                       NOW(),
                       DATE_ADD(MAX(date_tentative), INTERVAL {$lockoutMin} MINUTE)
                   ) AS secondes_restantes
            FROM tentatives_connexion
            WHERE ip = :ip
              AND succes = 0
              AND date_tentative >= (NOW() - INTERVAL {$lockoutMin} MINUTE)
        ");
        $requete->execute(["ip" => loginIp()]);

        $ligne = $requete->fetch(PDO::FETCH_ASSOC);

        if (!$ligne) {
            return 0;
        }

        $nb = (int) $ligne["nb"];

        if ($nb < $maxTentatives) {
            return 0;
        }

        return max(0, (int) $ligne["secondes_restantes"]);

    } catch (Throwable $e) {
        error_log("loginBlocageRestant: " . $e->getMessage());
        return 0;
    }
}


/*
|--------------------------------------------------------------------------
| Nettoyer les vieilles tentatives (à appeler périodiquement)
|--------------------------------------------------------------------------
*/

function loginNettoyerAnciennes(PDO $pdo): void
{
    try {
        $pdo->exec("
            DELETE FROM tentatives_connexion
            WHERE date_tentative < (NOW() - INTERVAL 7 DAY)
        ");
    } catch (Throwable $e) {
        error_log("loginNettoyerAnciennes: " . $e->getMessage());
    }
}
