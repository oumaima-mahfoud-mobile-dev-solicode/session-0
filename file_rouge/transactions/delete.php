<?php
/**
 * CRUD Transactions — Suppression
 * Suppression d'une transaction avec répercussion AUTOMATIQUE inverse sur le solde du compte bancaire :
 * - Un débit supprimé est recrédité au solde du compte (+montant)
 * - Un crédit supprimé est retiré du solde du compte (-montant)
 */
require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setFlash('danger', "Identifiant de transaction non valide.");
    redirect(getBaseUrl() . 'transactions/list.php');
}

if (!isset($pdo)) {
    setFlash('danger', "Connexion à la base de données indisponible.");
    redirect(getBaseUrl() . 'transactions/list.php');
}

try {
    // 1. Récupération de la transaction avant suppression
    $stmt = $pdo->prepare("SELECT * FROM Transaction WHERE id_transaction = ?");
    $stmt->execute([$id]);
    $tx = $stmt->fetch();

    if (!$tx) {
        setFlash('warning', "La transaction demandée n'existe pas ou a déjà été supprimée.");
        redirect(getBaseUrl() . 'transactions/list.php');
    }

    $compte_id = (int)$tx['id_compte'];
    $montant = (float)$tx['montant'];
    $type = $tx['type_transaction'];

    // 2. Début de la transaction SQL
    $pdo->beginTransaction();

    // Inversion de l'impact financier sur le solde du compte
    if ($type === 'credit') {
        // Le crédit supprimé doit être déduit du solde
        $revertStmt = $pdo->prepare("UPDATE Compte_bancaire SET solde = solde - ? WHERE id_compte = ?");
    } else {
        // Le débit supprimé doit être restitué au solde
        $revertStmt = $pdo->prepare("UPDATE Compte_bancaire SET solde = solde + ? WHERE id_compte = ?");
    }
    $revertStmt->execute([$montant, $compte_id]);

    // 3. Suppression de la transaction
    $deleteStmt = $pdo->prepare("DELETE FROM Transaction WHERE id_transaction = ?");
    $deleteStmt->execute([$id]);

    $pdo->commit();

    setFlash('success', "La transaction de <strong>" . formatMoney($montant) . "</strong> a été supprimée et le solde du compte a été réajusté avec succès.");
    redirect(getBaseUrl() . 'transactions/list.php?compte_id=' . $compte_id);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    setFlash('danger', "Erreur lors de la suppression de la transaction : " . $e->getMessage());
    redirect(getBaseUrl() . 'transactions/list.php');
}
