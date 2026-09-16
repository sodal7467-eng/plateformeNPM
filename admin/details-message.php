<?php

session_start();

require_once("../config/database.php");

/*
|--------------------------------------------------------------------------
| Vérifier l'identifiant du message
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: messages.php");
    exit;
}

$id = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| Récupérer le message
|--------------------------------------------------------------------------
*/

$sql = "SELECT * FROM messages WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

$message = $stmt->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Vérifier si le message existe
|--------------------------------------------------------------------------
*/

if (!$message) {
    header("Location: messages.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Marquer le message comme lu
|--------------------------------------------------------------------------
*/

if ($message['lu'] == 0) {

    $sqlLu = "UPDATE messages SET lu = 1 WHERE id = ?";
    $stmtLu = $pdo->prepare($sqlLu);
    $stmtLu->execute([$id]);

    // Mettre également la valeur à jour dans la page
    $message['lu'] = 1;
}

?>
<?php require_once("includes/auth.php");?> 

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Détails du message - Administration NPM</title>


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

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

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

                        <i class="bi bi-envelope-open me-2"></i>

                        Détails du message

                    </h1>

                    <p class="text-muted mb-0">

                        Consultez le message envoyé par le client.

                    </p>

                </div>


                <a
                    href="messages.php"
                    class="btn btn-outline-secondary"
                >

                    <i class="bi bi-arrow-left me-1"></i>

                    Retour aux messages

                </a>

            </div>



            <!-- MESSAGE -->

            <div class="card shadow-sm border-0">

                <div class="card-body p-4">


                    <!-- INFORMATIONS CLIENT -->

                    <div class="row mb-4">


                        <!-- NOM -->

                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-bold">

                                <i class="bi bi-person me-1"></i>

                                Nom du client

                            </label>

                            <div class="form-control bg-light">

                                <?= htmlspecialchars($message['nom']) ?>

                            </div>

                        </div>



                        <!-- EMAIL -->

                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-bold">

                                <i class="bi bi-envelope me-1"></i>

                                Adresse email

                            </label>

                            <div class="form-control bg-light">

                                <?= htmlspecialchars($message['email']) ?>

                            </div>

                        </div>


                    </div>



                    <!-- SUJET -->

                    <div class="mb-4">

                        <label class="form-label fw-bold">

                            <i class="bi bi-chat-left-text me-1"></i>

                            Sujet

                        </label>

                        <div class="form-control bg-light">

                            <?= htmlspecialchars($message['sujet']) ?>

                        </div>

                    </div>



                    <!-- DATE -->

                    <div class="mb-4">

                        <label class="form-label fw-bold">

                            <i class="bi bi-calendar3 me-1"></i>

                            Date du message

                        </label>

                        <div class="form-control bg-light">

                            <?= date(
                                'd/m/Y à H:i',
                                strtotime($message['date_message'])
                            ) ?>

                        </div>

                    </div>



                    <!-- STATUT -->

                    <div class="mb-4">

                        <label class="form-label fw-bold">

                            <i class="bi bi-check-circle me-1"></i>

                            Statut

                        </label>

                        <div>

                            <span class="badge bg-secondary">

                                Lu

                            </span>

                        </div>

                    </div>



                    <!-- MESSAGE DU CLIENT -->

                    <div class="mb-4">

                        <label class="form-label fw-bold">

                            <i class="bi bi-chat-square-text me-1"></i>

                            Message du client

                        </label>

                        <div
                            class="border rounded p-4 bg-light"
                            style="min-height: 180px; white-space: pre-line;"
                        >

                            <?= nl2br(
                                htmlspecialchars($message['message'])
                            ) ?>

                        </div>

                    </div>



                    <!-- ACTIONS -->

                    <div class="d-flex gap-2">

                        <a
                            href="mailto:<?= htmlspecialchars($message['email']) ?>"
                            class="btn btn-primary"
                        >

                            <i class="bi bi-reply me-1"></i>

                            Répondre par email

                        </a>


                        <a
                            href="supprimer-message.php?id=<?= $message['id'] ?>"
                            class="btn btn-danger"
                            onclick="return confirm('Voulez-vous vraiment supprimer ce message ?');"
                        >

                            <i class="bi bi-trash me-1"></i>

                            Supprimer

                        </a>


                        <a
                            href="messages.php"
                            class="btn btn-outline-secondary"
                        >

                            Fermer

                        </a>

                    </div>


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