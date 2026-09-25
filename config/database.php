<?php

require_once __DIR__ . "/env.php";

$host = env("DB_HOST", "localhost");
$dbname = env("DB_NAME", "quincaillerie_npm");
$username = env("DB_USER", "root");
$password = env("DB_PASSWORD", "");

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {

    // Détails uniquement dans les logs serveur, jamais à l'écran.
    error_log("Erreur PDO : " . $e->getMessage());

    http_response_code(503);

    if (env("APP_DEBUG", false)) {
        die("Erreur DB (mode debug) : " . htmlspecialchars($e->getMessage()));
    }

    die("Le service est momentanément indisponible. Merci de réessayer plus tard.");

}
