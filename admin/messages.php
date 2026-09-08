<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../config/database.php");

/*
|--------------------------------------------------------------------------
| Marquer un message comme lu
|--------------------------------------------------------------------------
*/
if (isset($_GET["lire"]) && is_numeric($_GET["lire"])) {

    $messageId = (int) $_GET["lire"];

    $requeteLu = $pdo->prepare("
        UPDATE messages
        SET lu = 1
        WHERE id = ?
    ");

    $requeteLu->execute([$messageId]);

    // Si la requête vient de JavaScript
    if (
        isset($_SERVER["HTTP_X_REQUESTED_WITH"]) &&
        strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest"
    ) {
        header("Content-Type: application/json; charset=utf-8");

        echo json_encode([
            "success" => true
        ]);

        exit;
    }

    header("Location: messages.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Récupérer les messages
|--------------------------------------------------------------------------
*/
$requete = $pdo->query("
    SELECT
        id,
        nom,
        email,
        sujet,
        message,
        date_message,
        lu
    FROM messages
    ORDER BY date_message DESC
");

$messages = $requete->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Nombre total de messages
|--------------------------------------------------------------------------
*/
$totalMessages = count($messages);


/*
|--------------------------------------------------------------------------
| Nombre de messages non lus
|--------------------------------------------------------------------------
*/
$nonLus = 0;

foreach ($messages as $message) {
    if ((int) $message["lu"] === 0) {
        $nonLus++;
    }
}

?>

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

    <!-- CSS principal -->
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

        <!-- =========================================================
             EN-TÊTE
        ========================================================== -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1 class="fw-bold mb-1">
                    Messages
                </h1>

                <p class="text-muted mb-0">
                    Consultez les messages reçus par Quincaillerie NPM.
                </p>

            </div>

            <div class="d-flex gap-2">

                <span class="badge bg-dark fs-6 px-3 py-2">
                    <?= $totalMessages ?>
                    message<?= $totalMessages > 1 ? "s" : "" ?>
                </span>

                <?php if ($nonLus > 0): ?>

                    <span
                        id="badgeNonLus"
                        class="badge bg-danger fs-6 px-3 py-2"
                    >
                        <?= $nonLus ?> nouveau<?= $nonLus > 1 ? "x" : "" ?>
                    </span>

                <?php endif; ?>

            </div>

        </div>


        <!-- =========================================================
             TABLEAU DES MESSAGES
        ========================================================== -->

        <div class="card border-0 shadow-sm">

            <div class="card-body p-0">

                <?php if (empty($messages)): ?>

                    <!-- Aucun message -->

                    <div class="text-center py-5">

                        <i
                            class="bi bi-envelope-open"
                            style="font-size: 50px;"
                        ></i>

                        <h4 class="mt-3">
                            Aucun message
                        </h4>

                        <p class="text-muted">
                            Vous n'avez reçu aucun message pour le moment.
                        </p>

                    </div>

                <?php else: ?>

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-dark">

                                <tr>

                                    <th>
                                        #
                                    </th>

                                    <th>
                                        Expéditeur
                                    </th>

                                    <th>
                                        Sujet
                                    </th>

                                    <th>
                                        Date
                                    </th>

                                    <th class="text-center">
                                        État
                                    </th>

                                    <th class="text-center">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                            <?php foreach ($messages as $message): ?>

                                <?php

                                $messageId = (int) $message["id"];

                                $estNonLu = (int) $message["lu"] === 0;

                                ?>

                                <tr
                                    id="message-<?= $messageId ?>"
                                    data-message-id="<?= $messageId ?>"
                                    class="<?= $estNonLu ? "table-warning" : "" ?>"
                                >

                                    <!-- ID -->

                                    <td>
                                        <strong>
                                            #<?= $messageId ?>
                                        </strong>
                                    </td>


                                    <!-- EXPÉDITEUR -->

                                    <td>

                                        <div class="fw-bold">

                                            <?= htmlspecialchars(
                                                $message["nom"]
                                            ) ?>

                                            <?php if ($estNonLu): ?>

                                                <span
                                                    class="badge bg-danger ms-1 badge-nouveau"
                                                >
                                                    Nouveau
                                                </span>

                                            <?php endif; ?>

                                        </div>

                                        <small class="text-muted">

                                            <?= htmlspecialchars(
                                                $message["email"]
                                            ) ?>

                                        </small>

                                    </td>


                                    <!-- SUJET -->

                                    <td>

                                        <span class="fw-semibold">

                                            <?= htmlspecialchars(
                                                $message["sujet"]
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- DATE -->

                                    <td>

                                        <?= date(
                                            "d/m/Y H:i",
                                            strtotime($message["date_message"])
                                        ) ?>

                                    </td>


                                    <!-- ÉTAT -->

                                    <td class="text-center">

                                        <?php if ($estNonLu): ?>

                                            <span
                                                class="badge bg-danger"
                                                id="etat-<?= $messageId ?>"
                                            >
                                                <i class="bi bi-envelope"></i>
                                                Non lu
                                            </span>

                                        <?php else: ?>

                                            <span
                                                class="badge bg-success"
                                                id="etat-<?= $messageId ?>"
                                            >
                                                <i class="bi bi-envelope-open"></i>
                                                Lu
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- ACTIONS -->

                                    <td class="text-center">

                                        <!-- Voir -->

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-dark"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalMessage<?= $messageId ?>"
                                            onclick="marquerCommeLu(<?= $messageId ?>)"
                                            title="Voir le message"
                                        >

                                            <i class="bi bi-eye"></i>

                                        </button>


                                        <!-- Répondre -->

                                        <a
                                            href="mailto:<?= htmlspecialchars(
                                                $message["email"]
                                            ) ?>?subject=Re: <?= rawurlencode(
                                                $message["sujet"]
                                            ) ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Répondre"
                                        >

                                            <i class="bi bi-reply"></i>

                                        </a>


                                        <!-- Supprimer -->

                                        <a
                                            href="supprimer-message.php?id=<?= $messageId ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Supprimer"
                                            onclick="return confirm('Voulez-vous vraiment supprimer ce message ?');"
                                        >

                                            <i class="bi bi-trash"></i>

                                        </a>

                                    </td>

                                </tr>


                                <!-- =================================================
                                     MODAL DU MESSAGE
                                ================================================== -->

                                <div
                                    class="modal fade"
                                    id="modalMessage<?= $messageId ?>"
                                    tabindex="-1"
                                    aria-labelledby="titreMessage<?= $messageId ?>"
                                    aria-hidden="true"
                                >

                                    <div class="modal-dialog modal-lg modal-dialog-centered">

                                        <div class="modal-content">


                                            <!-- HEADER -->

                                            <div class="modal-header">

                                                <h5
                                                    class="modal-title fw-bold"
                                                    id="titreMessage<?= $messageId ?>"
                                                >

                                                    <i class="bi bi-envelope me-2"></i>

                                                    <?= htmlspecialchars(
                                                        $message["sujet"]
                                                    ) ?>

                                                </h5>


                                                <button
                                                    type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal"
                                                    aria-label="Fermer"
                                                ></button>

                                            </div>


                                            <!-- BODY -->

                                            <div class="modal-body">


                                                <!-- Expéditeur -->

                                                <div class="mb-4">

                                                    <div class="d-flex align-items-center gap-3">

                                                        <div
                                                            class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center"
                                                            style="width:50px;height:50px;"
                                                        >

                                                            <i class="bi bi-person fs-4"></i>

                                                        </div>


                                                        <div>

                                                            <div class="fw-bold">

                                                                <?= htmlspecialchars(
                                                                    $message["nom"]
                                                                ) ?>

                                                            </div>

                                                            <div class="text-muted">

                                                                <?= htmlspecialchars(
                                                                    $message["email"]
                                                                ) ?>

                                                            </div>

                                                        </div>

                                                    </div>

                                                </div>


                                                <!-- Date -->

                                                <div class="mb-3">

                                                    <small class="text-muted">

                                                        <i class="bi bi-calendar3 me-1"></i>

                                                        Reçu le

                                                        <?= date(
                                                            "d/m/Y à H:i",
                                                            strtotime(
                                                                $message["date_message"]
                                                            )
                                                        ) ?>

                                                    </small>

                                                </div>


                                                <!-- Sujet -->

                                                <div class="mb-3">

                                                    <strong>
                                                        Sujet :
                                                    </strong>

                                                    <span>
                                                        <?= htmlspecialchars(
                                                            $message["sujet"]
                                                        ) ?>
                                                    </span>

                                                </div>


                                                <!-- Message -->

                                                <div class="border rounded p-4 bg-light">

                                                    <p
                                                        class="mb-0"
                                                        style="white-space: pre-line;"
                                                    >

                                                        <?= htmlspecialchars(
                                                            $message["message"]
                                                        ) ?>

                                                    </p>

                                                </div>

                                            </div>


                                            <!-- FOOTER -->

                                            <div class="modal-footer">

                                                <a
                                                    href="mailto:<?= htmlspecialchars(
                                                        $message["email"]
                                                    ) ?>?subject=Re: <?= rawurlencode(
                                                        $message["sujet"]
                                                    ) ?>"
                                                    class="btn btn-primary"
                                                >

                                                    <i class="bi bi-reply me-1"></i>

                                                    Répondre

                                                </a>


                                                <a
                                                    href="supprimer-message.php?id=<?= $messageId ?>"
                                                    class="btn btn-danger"
                                                    onclick="return confirm('Voulez-vous vraiment supprimer ce message ?');"
                                                >

                                                    <i class="bi bi-trash me-1"></i>

                                                    Supprimer

                                                </a>


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

    </main>

</div>


<!-- =============================================================
     BOOTSTRAP JS
============================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<!-- =============================================================
     JAVASCRIPT : MARQUER COMME LU
============================================================== -->

<script>

function marquerCommeLu(messageId) {

    fetch("messages.php?lire=" + messageId, {

        method: "GET",

        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }

    })

    .then(response => response.json())

    .then(data => {

        if (data.success) {

            // Récupérer la ligne
            const ligne = document.getElementById(
                "message-" + messageId
            );


            if (ligne) {

                // Enlever le fond jaune
                ligne.classList.remove("table-warning");


                // Supprimer les badges "Nouveau"
                const badgesNouveau = ligne.querySelectorAll(
                    ".badge-nouveau"
                );

                badgesNouveau.forEach(badge => {
                    badge.remove();
                });


                // Modifier l'état
                const etat = document.getElementById(
                    "etat-" + messageId
                );

                if (etat) {

                    etat.className = "badge bg-success";

                    etat.innerHTML =
                        '<i class="bi bi-envelope-open"></i> Lu';

                }

            }


            // Mettre à jour le nombre de nouveaux messages
            const badgeNonLus = document.getElementById(
                "badgeNonLus"
            );


            if (badgeNonLus) {

                let nombre = parseInt(
                    badgeNonLus.textContent
                );

                nombre = nombre - 1;


                if (nombre <= 0) {

                    badgeNonLus.remove();

                } else {

                    badgeNonLus.textContent =
                        nombre +
                        (nombre > 1
                            ? " nouveaux"
                            : " nouveau");

                }

            }

        }

    })

    .catch(error => {

        console.error(
            "Erreur lors du marquage du message :",
            error
        );

    });

}

</script>

</body>

</html>