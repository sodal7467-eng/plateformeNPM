<?php

require_once("includes/auth.php");
require_once("../config/database.php");

// Annule précisément le paiement de test de 50 000 FCFA fait sur la
// commande #6 pour vérifier la nouvelle modale de paiement.

$commandeId = 6;
$montantTest = 50000;

$pdo->beginTransaction();

try {

    $facture = $pdo->prepare("SELECT id, montant_paye, reste_a_payer FROM factures WHERE commande_id = ? LIMIT 1");
    $facture->execute([$commandeId]);
    $f = $facture->fetch(PDO::FETCH_ASSOC);

    $credit = $pdo->prepare("SELECT id, montant_paye, reste_a_payer FROM credits_clients WHERE commande_id = ? ORDER BY id DESC LIMIT 1");
    $credit->execute([$commandeId]);
    $c = $credit->fetch(PDO::FETCH_ASSOC);

    $paiement = $pdo->prepare("SELECT id FROM paiements_credits WHERE credit_id = ? AND commentaire = 'Test modal inline - a annuler' ORDER BY id DESC LIMIT 1");
    $paiement->execute([$c["id"]]);
    $p = $paiement->fetch(PDO::FETCH_ASSOC);

    if (!$f || !$c || !$p) {
        throw new Exception("Données de test introuvables, rien annulé par sécurité.");
    }

    $pdo->prepare("UPDATE factures SET montant_paye = montant_paye - ?, reste_a_payer = reste_a_payer + ?, statut = 'Partiellement payée' WHERE id = ?")
        ->execute([$montantTest, $montantTest, $f["id"]]);

    $pdo->prepare("UPDATE credits_clients SET montant_paye = montant_paye - ?, reste_a_payer = reste_a_payer + ?, statut = 'En cours' WHERE id = ?")
        ->execute([$montantTest, $montantTest, $c["id"]]);

    $pdo->prepare("DELETE FROM paiements_credits WHERE id = ?")->execute([$p["id"]]);

    $pdo->commit();
    echo "OK - paiement de test annulé";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "ANNULÉ - " . $e->getMessage();
}
