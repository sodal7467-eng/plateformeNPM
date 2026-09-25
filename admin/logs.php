<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("includes/auth.php");
require_once("../config/database.php");


// Filtres simples
$filtreAction = trim($_GET["action"] ?? "");
$filtreAdmin  = trim($_GET["admin"] ?? "");

$sql = "
    SELECT l.*, u.nom AS admin_nom_reel
    FROM logs_admin l
    LEFT JOIN utilisateurs u ON u.id = l.admin_id
    WHERE 1=1
";
$params = [];

if ($filtreAction !== "") {
    $sql .= " AND l.action LIKE :action";
    $params["action"] = "%" . $filtreAction . "%";
}
if ($filtreAdmin !== "") {
    $sql .= " AND (u.nom LIKE :admin OR l.admin_nom LIKE :admin)";
    $params["admin"] = "%" . $filtreAdmin . "%";
}

$sql .= " ORDER BY l.date_action DESC LIMIT 500";

$requete = $pdo->prepare($sql);
$requete->execute($params);
$logs = $requete->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal des actions - NPM Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php require_once("includes/navbar.php"); ?>
<?php require_once("includes/sidebar.php"); ?>

<main class="admin-content">
    <div class="container-fluid py-5 px-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold">
                    <i class="bi bi-journal-text me-2"></i>
                    Journal des actions
                </h1>
                <p class="text-muted mb-0">
                    500 dernières actions sensibles effectuées par les administrateurs.
                </p>
            </div>
        </div>

        <form method="GET" class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Action</label>
                        <input type="text" name="action" class="form-control" placeholder="ex : supprimer, paiement…" value="<?= htmlspecialchars($filtreAction) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Administrateur</label>
                        <input type="text" name="admin" class="form-control" placeholder="Nom" value="<?= htmlspecialchars($filtreAdmin) ?>">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-dark"><i class="bi bi-search me-1"></i>Filtrer</button>
                        <a href="logs.php" class="btn btn-outline-secondary">Réinitialiser</a>
                    </div>
                </div>
            </div>
        </form>

        <div class="card shadow-sm border-0">
            <div class="card-body">

                <?php if (count($logs) === 0): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-journal display-1 text-muted"></i>
                        <h4 class="mt-3">Aucune action enregistrée</h4>
                        <p class="text-muted">Les actions sensibles apparaîtront ici à mesure qu'elles sont effectuées.</p>
                    </div>
                <?php else: ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Administrateur</th>
                                <th>Action</th>
                                <th>Cible</th>
                                <th>Détails</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($logs as $l): ?>
                            <tr>
                                <td class="text-nowrap">
                                    <?= htmlspecialchars(date("d/m/Y H:i", strtotime($l["date_action"]))) ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($l["admin_nom_reel"] ?? $l["admin_nom"] ?? "?") ?></strong>
                                    <span class="text-muted small"> <?= (int) $l["admin_id"] ?></span>
                                </td>
                                <td>
                                    <code class="text-dark"><?= htmlspecialchars($l["action"]) ?></code>
                                </td>
                                <td>
                                    <?php if ($l["cible_type"]): ?>
                                        <?= htmlspecialchars($l["cible_type"]) ?>
                                        <?php if ($l["cible_id"]): ?>
                                            <span class="text-muted"><?= (int) $l["cible_id"] ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted">
                                    <?= htmlspecialchars($l["details"] ?? "") ?>
                                </td>
                                <td class="small text-muted"><?= htmlspecialchars($l["ip"] ?? "") ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php endif; ?>

            </div>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
