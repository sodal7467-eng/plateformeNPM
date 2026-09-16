<?php

session_start();

require_once("../config/database.php");

/*
|--------------------------------------------------------------------------
| Récupérer tous les messages
|--------------------------------------------------------------------------
*/

$sql = "SELECT * FROM messages ORDER BY date_message DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<?php require_once("includes/auth.php");?> 

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Messages - Administration NPM</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- CSS du projet -->
    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<?php require_once("includes/navbar.php"); ?>

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2">

            <?php require_once("includes/sidebar.php"); ?>

        </div>


        <!-- CONTENU PRINCIPAL -->
        <main class="col-md-9 col-lg-10 py-4">

            <!-- TITRE -->

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h1 class="fw-bold">
                        <i class="bi bi-envelope me-2"></i>
                        Messages
                    </h1>

                    <p class="text-muted mb-0">
                        Consultez les messages envoyés par les clients.
                    </p>

                </div>

            </div>


            <!-- CARTE -->

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <?php if (count($messages) > 0): ?>

                        <div class="table-responsive">

                            <table class="table table-hover align-middle">

                                <thead class="table-light">

                                    <tr>

                                        <th>#</th>

                                        <th>Client</th>

                                        <th>Email</th>

                                        <th>Sujet</th>

                                        <th>Date</th>

                                        <th>Statut</th>

                                        <th class="text-center">
                                            Action
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                <?php foreach ($messages as $message): ?>

                                    <tr>

                                        <!-- ID -->

                                        <td>
                                            <?= htmlspecialchars($message['id']) ?>
                                        </td>


                                        <!-- NOM -->

                                        <td>

                                            <strong>
                                                <?= htmlspecialchars($message['nom']) ?>
                                            </strong>

                                        </td>


                                        <!-- EMAIL -->

                                        <td>

                                            <?= htmlspecialchars($message['email']) ?>

                                        </td>


                                        <!-- SUJET -->

                                        <td>

                                            <?= htmlspecialchars($message['sujet']) ?>

                                        </td>


                                        <!-- DATE -->

                                        <td>

                                            <?= date(
                                                'd/m/Y H:i',
                                                strtotime($message['date_message'])
                                            ) ?>

                                        </td>


                                        <!-- STATUT -->

                                        <td>

                                            <?php if ($message['lu'] == 0): ?>

                                                <span class="badge bg-warning text-dark">
                                                    Nouveau
                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-secondary">
                                                    Lu
                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <!-- ACTION -->

                                        <td class="text-center">

                                            <a
                                                href="details-message.php?id=<?= $message['id'] ?>"
                                                class="btn btn-sm btn-primary"
                                            >

                                                <i class="bi bi-eye"></i>

                                                Voir

                                            </a>


                                            <a
                                                href="supprimer-message.php?id=<?= $message['id'] ?>"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Voulez-vous vraiment supprimer ce message ?');"
                                            >

                                                <i class="bi bi-trash"></i>

                                                Supprimer

                                            </a>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <!-- AUCUN MESSAGE -->

                        <div class="text-center py-5">

                            <i
                                class="bi bi-envelope-open display-1 text-muted"
                            ></i>

                            <h4 class="mt-3">
                                Aucun message
                            </h4>

                            <p class="text-muted">
                                Vous n'avez reçu aucun message pour le moment.
                            </p>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </main>

    </div>

</div>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>