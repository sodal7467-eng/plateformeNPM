<?php

require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| RÉCUPÉRER LES MESSAGES
|--------------------------------------------------------------------------
*/

try {

    $requete = $pdo->query("
        SELECT
            id,
            nom,
            email,
            sujet,
            message
        FROM messages
        ORDER BY id DESC
    ");

    $messages = $requete->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $messages = [];

    $erreur = "Impossible de récupérer les messages.";

}


/*
|--------------------------------------------------------------------------
| STATISTIQUES
|--------------------------------------------------------------------------
*/

$totalMessages = count($messages);

?>

<!DOCTYPE html>

<html lang="fr">

<head>


<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

<title>Messages - NPM Admin</title>


<!-- Bootstrap -->

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">


<!-- Bootstrap Icons -->

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


<!-- CSS NPM -->

<link
    rel="stylesheet"
    href="../assets/css/style.css">


</head>

<body>


<!-- =========================
     NAVBAR ADMIN
========================== -->

<?php require_once("includes/navbar.php"); ?>


<!-- =========================
     SIDEBAR ADMIN
========================== -->

<?php require_once("includes/sidebar.php"); ?>


<!-- =========================
     CONTENU ADMIN
========================== -->

<main class="admin-content">

    <div class="container-fluid py-5 px-4">


        <!-- =========================
             EN-TÊTE
        ========================== -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1 class="fw-bold mb-2">

                    <i class="bi bi-envelope-fill me-2"></i>

                    Gestion des messages

                </h1>

                <p class="text-muted mb-0">

                    Consultez les messages envoyés par les visiteurs de la plateforme NPM.

                </p>

            </div>


            <!-- TOTAL -->

            <div class="bg-dark text-white rounded-3 px-4 py-3">

                <div class="d-flex align-items-center gap-3">

                    <i class="bi bi-envelope fs-3 text-warning"></i>

                    <div>

                        <small class="text-light">
                            Total messages
                        </small>

                        <h4 class="mb-0 fw-bold">

                            <?= $totalMessages ?>

                        </h4>

                    </div>

                </div>

            </div>

        </div>


        <!-- =========================
             ERREUR
        ========================== -->

        <?php if (isset($erreur)): ?>

            <div class="alert alert-danger">

                <i class="bi bi-exclamation-triangle-fill me-2"></i>

                <?= htmlspecialchars($erreur) ?>

            </div>

        <?php endif; ?>


        <!-- =========================
             LISTE DES MESSAGES
        ========================== -->

        <div class="card border-0 shadow-sm">


            <!-- HEADER -->

            <div class="card-header bg-white py-3">

                <div class="d-flex justify-content-between align-items-center">

                    <h5 class="fw-bold mb-0">

                        <i class="bi bi-envelope me-2"></i>

                        Liste des messages

                    </h5>


                    <span class="badge bg-warning text-dark">

                        <?= $totalMessages ?>

                        message<?= $totalMessages > 1 ? "s" : "" ?>

                    </span>

                </div>

            </div>


            <!-- CONTENU -->

            <div class="card-body p-0">


                <?php if (empty($messages)): ?>


                    <!-- =========================
                         AUCUN MESSAGE
                    ========================== -->

                    <div class="text-center py-5 px-3">

                        <i class="bi bi-envelope-open fs-1 text-muted"></i>

                        <h4 class="mt-3">

                            Aucun message

                        </h4>

                        <p class="text-muted mb-0">

                            Vous n'avez reçu aucun message pour le moment.

                        </p>

                    </div>


                <?php else: ?>


                    <!-- =========================
                         TABLEAU
                    ========================== -->

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-dark">

                                <tr>

                                    <th>
                                        ID
                                    </th>

                                    <th>
                                        Expéditeur
                                    </th>

                                    <th>
                                        Email
                                    </th>

                                    <th>
                                        Sujet
                                    </th>

                                    <th>
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php foreach ($messages as $m): ?>

                                    <tr>


                                        <!-- ID -->

                                        <td>

                                            <span class="fw-semibold">

                                                #<?= htmlspecialchars($m["id"]) ?>

                                            </span>

                                        </td>


                                        <!-- EXPÉDITEUR -->

                                        <td>

                                            <div class="d-flex align-items-center gap-3">

                                                <div
                                                    class="rounded-circle bg-dark text-warning d-flex align-items-center justify-content-center"
                                                    style="width: 42px; height: 42px;">

                                                    <i class="bi bi-person-fill"></i>

                                                </div>


                                                <div>

                                                    <strong>

                                                        <?= htmlspecialchars($m["nom"]) ?>

                                                    </strong>

                                                    <div class="text-muted small">

                                                        Visiteur NPM

                                                    </div>

                                                </div>

                                            </div>

                                        </td>


                                        <!-- EMAIL -->

                                        <td>

                                            <a
                                                href="mailto:<?= htmlspecialchars($m["email"]) ?>"
                                                class="text-decoration-none">

                                                <i class="bi bi-envelope me-1"></i>

                                                <?= htmlspecialchars($m["email"]) ?>

                                            </a>

                                        </td>


                                        <!-- SUJET -->

                                        <td>

                                            <span class="badge bg-warning text-dark">

                                                <?= htmlspecialchars($m["sujet"]) ?>

                                            </span>

                                        </td>


                                        <!-- ACTION -->

                                        <td>

                                            <button
                                                type="button"
                                                class="btn btn-dark btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#messageModal<?= $m["id"] ?>"
                                            >

                                                <i class="bi bi-eye me-1"></i>

                                                Voir

                                            </button>

                                        </td>


                                    </tr>


                                    <!-- =========================
                                         MODALE MESSAGE
                                    ========================== -->

                                    <div
                                        class="modal fade"
                                        id="messageModal<?= $m["id"] ?>"
                                        tabindex="-1"
                                        aria-hidden="true"
                                    >

                                        <div class="modal-dialog modal-lg modal-dialog-centered">

                                            <div class="modal-content">


                                                <!-- HEADER -->

                                                <div class="modal-header bg-dark text-white">

                                                    <h5 class="modal-title">

                                                        <i class="bi bi-envelope me-2"></i>

                                                        Message de
                                                        <?= htmlspecialchars($m["nom"]) ?>

                                                    </h5>

                                                    <button
                                                        type="button"
                                                        class="btn-close btn-close-white"
                                                        data-bs-dismiss="modal"
                                                    ></button>

                                                </div>


                                                <!-- BODY -->

                                                <div class="modal-body">

                                                    <!-- NOM -->

                                                    <div class="mb-3">

                                                        <strong>
                                                            Nom :
                                                        </strong>

                                                        <?= htmlspecialchars($m["nom"]) ?>

                                                    </div>


                                                    <!-- EMAIL -->

                                                    <div class="mb-3">

                                                        <strong>
                                                            Email :
                                                        </strong>

                                                        <a
                                                            href="mailto:<?= htmlspecialchars($m["email"]) ?>"
                                                            class="text-decoration-none"
                                                        >

                                                            <?= htmlspecialchars($m["email"]) ?>

                                                        </a>

                                                    </div>


                                                    <!-- SUJET -->

                                                    <div class="mb-3">

                                                        <strong>
                                                            Sujet :
                                                        </strong>

                                                        <span class="badge bg-warning text-dark">

                                                            <?= htmlspecialchars($m["sujet"]) ?>

                                                        </span>

                                                    </div>


                                                    <hr>


                                                    <!-- MESSAGE -->

                                                    <div>

                                                        <strong>
                                                            Message :
                                                        </strong>

                                                        <div class="bg-light rounded p-3 mt-2">

                                                            <?= nl2br(
                                                                htmlspecialchars($m["message"])
                                                            ) ?>

                                                        </div>

                                                    </div>

                                                </div>


                                                <!-- FOOTER -->

                          <div class="modal-footer">

    <!-- RÉPONDRE -->

    <a
        href="mailto:<?= htmlspecialchars($m["email"]) ?>?subject=Re%3A%20<?= urlencode($m["sujet"]) ?>"
        class="btn btn-warning"
    >

        <i class="bi bi-reply-fill me-1"></i>

        Répondre

    </a>


    <!-- FERMER -->

    <button
        type="button"
        class="btn btn-secondary"
        data-bs-dismiss="modal"
    >

        Fermer

    </button>

</div>

                                            </div>

                                        </div>

                                    </div>


                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>


                <?php endif; ?>


            </div>

        </div>


    </div>

</main>


<!-- =========================
     BOOTSTRAP JS
========================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>
