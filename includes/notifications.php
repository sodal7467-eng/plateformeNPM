<?php
/*
|--------------------------------------------------------------------------
| Notifications client (changement d'état de commande)
|--------------------------------------------------------------------------
| Site  : cloche dans le menu du compte client (table notifications_clients).
| Email : actif dès que MAIL_USER / MAIL_PASSWORD sont dans le .env.
| SMS   : emplacement prêt (envoyerSms), à brancher sur un fournisseur.
*/

require_once __DIR__ . "/mailer.php";
require_once __DIR__ . "/parametres.php";


// Crée la table au premier usage (bases installées avant cette fonctionnalité)
function notificationsPreparerTable(PDO $pdo): void
{
    static $prete = false;
    if ($prete) return;

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notifications_clients (
            id               INT PRIMARY KEY AUTO_INCREMENT,
            utilisateur_id   INT NOT NULL,
            message          VARCHAR(255) NOT NULL,
            lien             VARCHAR(255) DEFAULT NULL,
            lu               TINYINT(1) NOT NULL DEFAULT 0,
            date_creation    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_utilisateur_lu (utilisateur_id, lu),
            CONSTRAINT fk_notification_utilisateur FOREIGN KEY (utilisateur_id)
                REFERENCES utilisateurs (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $prete = true;
}


function notificationAjouter(PDO $pdo, int $utilisateurId, string $message, ?string $lien = null): void
{
    notificationsPreparerTable($pdo);

    $pdo->prepare("
        INSERT INTO notifications_clients (utilisateur_id, message, lien)
        VALUES (?, ?, ?)
    ")->execute([$utilisateurId, mb_substr($message, 0, 255), $lien]);
}


// Dernières notifications + nombre de non lues (pour la cloche)
function notificationsClient(PDO $pdo, int $utilisateurId, int $limite = 8): array
{
    notificationsPreparerTable($pdo);

    $liste = $pdo->prepare("
        SELECT id, message, lien, lu, date_creation
        FROM notifications_clients
        WHERE utilisateur_id = ?
        ORDER BY id DESC
        LIMIT " . (int) $limite
    );
    $liste->execute([$utilisateurId]);

    $nonLues = $pdo->prepare("
        SELECT COUNT(*) FROM notifications_clients
        WHERE utilisateur_id = ? AND lu = 0
    ");
    $nonLues->execute([$utilisateurId]);

    return [
        "liste"   => $liste->fetchAll(PDO::FETCH_ASSOC),
        "nonLues" => (int) $nonLues->fetchColumn(),
    ];
}


/*
| Prévient le client que sa commande a changé d'état.
| Renvoie les canaux utilisés, ex. ["email"] (vide si rien n'est parti).
*/
function notifierChangementStatutCommande(PDO $pdo, int $commandeId, string $nouveauStatut): array
{
    $requete = $pdo->prepare("
        SELECT c.id, c.total, c.utilisateur_id, u.nom, u.email, u.telephone
        FROM commandes c
        INNER JOIN utilisateurs u ON u.id = c.utilisateur_id
        WHERE c.id = ?
    ");
    $requete->execute([$commandeId]);
    $commande = $requete->fetch(PDO::FETCH_ASSOC);

    if (!$commande) {
        return [];
    }

    $nomBoutique = parametre($pdo, "nom_boutique", "NPM Quincaillerie");
    $texteStatut = messageStatutCommande($nouveauStatut);
    $lien = rtrim((string) env("APP_URL", ""), "/") . "/commande-detail.php?id=" . $commandeId;

    $canaux = [];

    // --- Site (cloche) ---
    try {
        notificationAjouter(
            $pdo,
            (int) $commande["utilisateur_id"],
            "Commande " . $commandeId . " : " . $nouveauStatut . ". " . $texteStatut,
            "commande-detail.php?id=" . $commandeId
        );
        $canaux[] = "site";
    } catch (PDOException $e) {
        error_log("Notification site non enregistrée : " . $e->getMessage());
    }

    // --- Email ---
    if (!empty($commande["email"])) {
        $sujet = "Commande " . $commandeId . " : " . $nouveauStatut;
        $html  = emailStatutCommande($commande, $nouveauStatut, $texteStatut, $lien, $nomBoutique);

        if (envoyerEmail($commande["email"], $sujet, $html)) {
            $canaux[] = "email";
        }
    }

    // --- SMS (à brancher) ---
    if (!empty($commande["telephone"])) {
        $sms = $nomBoutique . " : votre commande " . $commandeId
             . " est maintenant \"" . $nouveauStatut . "\". " . $texteStatut;

        if (envoyerSms($commande["telephone"], $sms)) {
            $canaux[] = "sms";
        }
    }

    return $canaux;
}


function messageStatutCommande(string $statut): string
{
    switch ($statut) {
        case "En attente":     return "Nous avons bien reçu votre commande.";
        case "En préparation": return "Nous préparons votre commande.";
        case "Expédiée":       return "Votre commande est en route.";
        case "Livrée":         return "Votre commande a été livrée. Merci pour votre confiance !";
        case "Annulée":        return "Votre commande a été annulée. Contactez-nous pour toute question.";
        default:               return "";
    }
}


function emailStatutCommande(array $commande, string $statut, string $texte, string $lien, string $nomBoutique): string
{
    $e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, "UTF-8");

    return '<!DOCTYPE html><html lang="fr"><body style="margin:0;background:#f4f4f4;font-family:Arial,sans-serif;">'
         . '<div style="max-width:520px;margin:20px auto;background:#fff;border-radius:8px;overflow:hidden;">'
         . '<div style="background:#212529;color:#ffc107;padding:18px 24px;font-size:20px;font-weight:bold;">' . $e($nomBoutique) . '</div>'
         . '<div style="padding:24px;color:#333;font-size:15px;line-height:1.5;">'
         . '<p>Bonjour ' . $e($commande["nom"]) . ',</p>'
         . '<p>Votre commande <strong>' . $e($commande["id"]) . '</strong> est maintenant :</p>'
         . '<p style="font-size:18px;font-weight:bold;color:#ff8c00;">' . $e($statut) . '</p>'
         . '<p>' . $e($texte) . '</p>'
         . '<p style="margin:28px 0;"><a href="' . $e($lien) . '" style="background:#ffc107;color:#212529;padding:12px 20px;border-radius:6px;text-decoration:none;font-weight:bold;">Voir ma commande</a></p>'
         . '<p style="color:#888;font-size:13px;">Merci pour votre confiance.<br>' . $e($nomBoutique) . '</p>'
         . '</div></div></body></html>';
}


/*
| SMS : pas encore de fournisseur. Brancher ici l'API choisie
| (ex. Orange SMS API Sénégal) et renvoyer true si le SMS est parti.
*/
function envoyerSms(string $telephone, string $message): bool
{
    return false;
}
