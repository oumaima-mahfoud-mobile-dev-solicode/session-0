<?php
/**
 * CRUD Comptes Bancaires — Suppression
 * Suppression d'un compte avec effet CASCADE :
 * La suppression du compte entraîne automatiquement la suppression de ses transactions.
 */
require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setFlash('danger', "Identifiant de compte non valide.");
    redirect(getBaseUrl() . 'comptes/list.php');
}

if (!isset($pdo)) {
    setFlash('danger', "Connexion à la base de données indisponible.");
    redirect(getBaseUrl() . 'comptes/list.php');
}

try {
    // Récupération du compte pour le message
    $stmt = $pdo->prepare("SELECT nom_compte FROM Compte_bancaire WHERE id_compte = ?");
    $stmt->execute([$id]);
    $compte = $stmt->fetch();

    if (!$compte) {
        setFlash('warning', "Le compte demandé n'existe pas ou a déjà été supprimé.");
        redirect(getBaseUrl() . 'comptes/list.php');
    }

    // Suppression du compte (les transactions associées sont supprimées via ON DELETE CASCADE)
    $deleteStmt = $pdo->prepare("DELETE FROM Compte_bancaire WHERE id_compte = ?");
    $deleteStmt->execute([$id]);

    setFlash('success', "Le compte <strong>" . escape($compte['nom_compte']) . "</strong> et toutes ses transactions associées ont été supprimés avec succès.");
} catch (PDOException $e) {
    setFlash('danger', "Erreur lors de la suppression du compte : " . $e->getMessage());
}

redirect(getBaseUrl() . 'comptes/list.php');
