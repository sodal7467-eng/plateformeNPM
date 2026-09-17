<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../config/database.php");


/*
|--------------------------------------------------------------------------
| Vérifier si une colonne existe déjà
|--------------------------------------------------------------------------
*/

function colonneExiste(PDO $pdo, string $table, string $colonne): bool
{
    $requete = $pdo->prepare("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
    ");

    $requete->execute([$table, $colonne]);

    return (int) $requete->fetchColumn() > 0;
}


$actions = [];


/*
|--------------------------------------------------------------------------
| 1) utilisateurs.telephone
|--------------------------------------------------------------------------
*/

try {

    if (!colonneExiste($pdo, "utilisateurs", "telephone")) {

        $pdo->exec("
            ALTER TABLE utilisateurs
            ADD COLUMN telephone VARCHAR(30) NULL AFTER email
        ");

        $actions[] = [
            "ok" => true,
            "texte" => "Colonne « telephone » ajoutée à la table utilisateurs."
        ];

    } else {

        $actions[] = [
            "ok" => true,
            "texte" => "Colonne « telephone » déjà présente (rien à faire)."
        ];
    }

} catch (Exception $e) {

    $actions[] = [
        "ok" => false,
        "texte" => "Erreur sur « telephone » : " . $e->getMessage()
    ];
}


/*
|--------------------------------------------------------------------------
| 2) utilisateurs.type_client
|--------------------------------------------------------------------------
*/

try {

    if (!colonneExiste($pdo, "utilisateurs", "type_client")) {

        $pdo->exec("
            ALTER TABLE utilisateurs
            ADD COLUMN type_client ENUM('en_ligne', 'boutique')
            NOT NULL DEFAULT 'en_ligne' AFTER role
        ");

        $actions[] = [
            "ok" => true,
            "texte" => "Colonne « type_client » ajoutée à la table utilisateurs."
        ];

    } else {

        $actions[] = [
            "ok" => true,
            "texte" => "Colonne « type_client » déjà présente (rien à faire)."
        ];
    }

} catch (Exception $e) {

    $actions[] = [
        "ok" => false,
        "texte" => "Erreur sur « type_client » : " . $e->getMessage()
    ];
}


/*
|--------------------------------------------------------------------------
| 3) utilisateurs.email doit accepter NULL
|--------------------------------------------------------------------------
| Les clients ajoutés depuis la boutique n'ont pas forcément d'email.
| On garde le même type de colonne (VARCHAR(x)) et on la rend juste
| optionnelle.
*/

try {

    $colonne = $pdo
        ->query("SHOW COLUMNS FROM utilisateurs LIKE 'email'")
        ->fetch(PDO::FETCH_ASSOC);

    if ($colonne && stripos($colonne["Null"], "YES") === false) {

        $type = $colonne["Type"];

        $pdo->exec("
            ALTER TABLE utilisateurs
            MODIFY COLUMN email $type NULL
        ");

        $actions[] = [
            "ok" => true,
            "texte" => "Colonne « email » rendue optionnelle."
        ];

    } else {

        $actions[] = [
            "ok" => true,
            "texte" => "Colonne « email » déjà optionnelle (rien à faire)."
        ];
    }

} catch (Exception $e) {

    $actions[] = [
        "ok" => false,
        "texte" => "Erreur sur « email » : " . $e->getMessage()
    ];
}


/*
|--------------------------------------------------------------------------
| 4) commandes.type_vente
|--------------------------------------------------------------------------
*/

try {

    if (!colonneExiste($pdo, "commandes", "type_vente")) {

        $pdo->exec("
            ALTER TABLE commandes
            ADD COLUMN type_vente ENUM('en_ligne', 'boutique')
            NOT NULL DEFAULT 'en_ligne' AFTER statut
        ");

        $actions[] = [
            "ok" => true,
            "texte" => "Colonne « type_vente » ajoutée à la table commandes."
        ];

    } else {

        $actions[] = [
            "ok" => true,
            "texte" => "Colonne « type_vente » déjà présente (rien à faire)."
        ];
    }

} catch (Exception $e) {

    $actions[] = [
        "ok" => false,
        "texte" => "Erreur sur « type_vente » : " . $e->getMessage()
    ];
}


/*
|--------------------------------------------------------------------------
| 5) Table depenses
|--------------------------------------------------------------------------
*/

try {

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS depenses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            description VARCHAR(255) NOT NULL,
            categorie VARCHAR(100) NULL,
            montant DECIMAL(12,2) NOT NULL,
            date_depense DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $actions[] = [
        "ok" => true,
        "texte" => "Table « depenses » prête."
    ];

} catch (Exception $e) {

    $actions[] = [
        "ok" => false,
        "texte" => "Erreur sur « depenses » : " . $e->getMessage()
    ];
}


/*
|--------------------------------------------------------------------------
| 6) utilisateurs.role doit accepter 'employe'
|--------------------------------------------------------------------------
*/

try {

    $colonne = $pdo
        ->query("SHOW COLUMNS FROM utilisateurs LIKE 'role'")
        ->fetch(PDO::FETCH_ASSOC);

    if (
        $colonne
        && stripos($colonne["Type"], "enum") === 0
        && stripos($colonne["Type"], "'employe'") === false
    ) {

        $pdo->exec("
            ALTER TABLE utilisateurs
            MODIFY COLUMN role ENUM('client', 'admin', 'employe')
            NOT NULL DEFAULT 'client'
        ");

        $actions[] = [
            "ok" => true,
            "texte" => "Colonne « role » mise à jour pour accepter « employe »."
        ];

    } else {

        $actions[] = [
            "ok" => true,
            "texte" => "Colonne « role » accepte déjà « employe » (rien à faire)."
        ];
    }

} catch (Exception $e) {

    $actions[] = [
        "ok" => false,
        "texte" => "Erreur sur « role » : " . $e->getMessage()
    ];
}


/*
|--------------------------------------------------------------------------
| 7) messages.telephone
|--------------------------------------------------------------------------
*/

try {

    if (!colonneExiste($pdo, "messages", "telephone")) {

        $pdo->exec("
            ALTER TABLE messages
            ADD COLUMN telephone VARCHAR(30) NULL AFTER email
        ");

        $actions[] = [
            "ok" => true,
            "texte" => "Colonne « telephone » ajoutée à la table messages."
        ];

    } else {

        $actions[] = [
            "ok" => true,
            "texte" => "Colonne « telephone » déjà présente sur messages (rien à faire)."
        ];
    }

} catch (Exception $e) {

    $actions[] = [
        "ok" => false,
        "texte" => "Erreur sur « messages.telephone » : " . $e->getMessage()
    ];
}


$toutOk = true;

foreach ($actions as $action) {

    if (!$action["ok"]) {
        $toutOk = false;
    }
}

?>
<?php require_once("includes/auth.php"); ?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mise à jour de la base - NPM Admin</title>

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


<body>


<?php require_once("includes/navbar.php"); ?>


<div class="admin-layout">

    <?php require_once("includes/sidebar.php"); ?>


    <main class="admin-content">

        <div class="container-fluid py-5 px-4">


            <div class="mb-4">

                <h1 class="fw-bold mb-2">
                    Mise à jour de la base de données
                </h1>

                <p class="text-muted mb-0">
                    Cette page prépare la base pour la gestion des clients
                    boutique et des ventes en magasin. Tu peux la relancer
                    autant de fois que nécessaire, elle ne modifie que ce
                    qui manque.
                </p>

            </div>


            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">

                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-database-gear me-2"></i>
                        Résultat de la mise à jour
                    </h5>

                </div>

                <div class="card-body">

                    <ul class="list-group list-group-flush">

                        <?php foreach ($actions as $action): ?>

                            <li class="list-group-item d-flex align-items-center">

                                <?php if ($action["ok"]): ?>

                                    <i class="bi bi-check-circle text-success me-2"></i>

                                <?php else: ?>

                                    <i class="bi bi-x-circle text-danger me-2"></i>

                                <?php endif; ?>

                                <?= htmlspecialchars($action["texte"]) ?>

                            </li>

                        <?php endforeach; ?>

                    </ul>


                    <?php if ($toutOk): ?>

                        <div class="alert alert-success mt-4 mb-0">

                            <i class="bi bi-check-circle me-2"></i>

                            La base est prête. Tu peux maintenant ajouter
                            des clients boutique et enregistrer des ventes.

                        </div>

                    <?php else: ?>

                        <div class="alert alert-danger mt-4 mb-0">

                            <i class="bi bi-exclamation-triangle me-2"></i>

                            Certaines étapes ont échoué. Regarde les
                            messages ci-dessus.

                        </div>

                    <?php endif; ?>


                    <div class="d-flex gap-2 mt-4">

                        <a href="clients.php" class="btn btn-dark">
                            <i class="bi bi-people me-1"></i>
                            Aller aux clients
                        </a>

                        <a href="nouvelle-vente.php" class="btn btn-warning">
                            <i class="bi bi-cart-plus me-1"></i>
                            Faire une nouvelle vente
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </main>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>
