<?php

require_once("includes/auth.php");
require_once("../config/database.php");

// Supprime uniquement le client de test créé pour vérifier que le
// téléphone est bien facultatif, à condition qu'il n'ait aucune commande.
$check = $pdo->prepare("SELECT COUNT(*) FROM commandes WHERE utilisateur_id = 4");
$check->execute();

if ((int) $check->fetchColumn() === 0) {
    $pdo->prepare("DELETE FROM utilisateurs WHERE id = 4 AND nom = 'Client Test Sans Telephone'")->execute();
    echo "OK - client de test supprimé";
} else {
    echo "ANNULÉ - ce client a des commandes, rien supprimé";
}
