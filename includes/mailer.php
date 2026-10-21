<?php
/*
|--------------------------------------------------------------------------
| Envoi d'emails par SMTP (Gmail ou autre), sans librairie externe
|--------------------------------------------------------------------------
| Réglages dans le .env :
|   MAIL_HOST=smtp.gmail.com
|   MAIL_PORT=587
|   MAIL_USER=adresse@gmail.com
|   MAIL_PASSWORD=mot de passe d'application Google (16 lettres)
|   MAIL_FROM_NAME=NPM Quincaillerie
*/

require_once __DIR__ . "/../config/env.php";


function mailConfigure(): bool
{
    return env("MAIL_USER") && env("MAIL_PASSWORD");
}


/*
| Envoie un email HTML. Renvoie true si le serveur SMTP l'a accepté.
| Les erreurs sont écrites dans le log PHP, jamais affichées.
*/
function envoyerEmail(string $destinataire, string $sujet, string $html): bool
{
    if (!mailConfigure() || !filter_var($destinataire, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $hote     = (string) env("MAIL_HOST", "smtp.gmail.com");
    $port     = (int) env("MAIL_PORT", 587);
    $user     = (string) env("MAIL_USER");
    // Google affiche le mot de passe d'application par groupes de 4 lettres
    $password = str_replace(" ", "", (string) env("MAIL_PASSWORD"));
    $nomFrom  = (string) env("MAIL_FROM_NAME", "NPM Quincaillerie");

    try {
        $smtp = @stream_socket_client("tcp://$hote:$port", $errno, $errstr, 15);
        if (!$smtp) {
            throw new RuntimeException("Connexion SMTP impossible : $errstr");
        }
        stream_set_timeout($smtp, 15);

        smtpAttendre($smtp, 220);
        smtpCommande($smtp, "EHLO localhost", 250);
        smtpCommande($smtp, "STARTTLS", 220);

        if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT)) {
            throw new RuntimeException("Échec du chiffrement TLS");
        }

        smtpCommande($smtp, "EHLO localhost", 250);
        smtpCommande($smtp, "AUTH LOGIN", 334);
        smtpCommande($smtp, base64_encode($user), 334);
        smtpCommande($smtp, base64_encode($password), 235);
        smtpCommande($smtp, "MAIL FROM:<$user>", 250);
        smtpCommande($smtp, "RCPT TO:<$destinataire>", 250);
        smtpCommande($smtp, "DATA", 354);

        $entetes = [
            "Date: " . date("r"),
            "From: =?UTF-8?B?" . base64_encode($nomFrom) . "?= <$user>",
            "To: <$destinataire>",
            "Subject: =?UTF-8?B?" . base64_encode($sujet) . "?=",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "Content-Transfer-Encoding: base64",
        ];

        $corps = implode("\r\n", $entetes) . "\r\n\r\n"
               . chunk_split(base64_encode($html), 76, "\r\n");

        fwrite($smtp, $corps . "\r\n.\r\n");
        smtpAttendre($smtp, 250);

        fwrite($smtp, "QUIT\r\n");
        fclose($smtp);

        return true;

    } catch (Throwable $e) {
        error_log("Email non envoyé à $destinataire : " . $e->getMessage());
        if (isset($smtp) && is_resource($smtp)) {
            fclose($smtp);
        }
        return false;
    }
}


function smtpCommande($smtp, string $commande, int $codeAttendu): void
{
    fwrite($smtp, $commande . "\r\n");
    smtpAttendre($smtp, $codeAttendu);
}


// Lit la réponse (éventuellement sur plusieurs lignes) et vérifie le code
function smtpAttendre($smtp, int $codeAttendu): void
{
    $reponse = "";
    while (($ligne = fgets($smtp, 515)) !== false) {
        $reponse .= $ligne;
        if (isset($ligne[3]) && $ligne[3] === " ") {
            break;
        }
    }

    if ((int) substr($reponse, 0, 3) !== $codeAttendu) {
        throw new RuntimeException("Réponse SMTP inattendue : " . trim($reponse));
    }
}
