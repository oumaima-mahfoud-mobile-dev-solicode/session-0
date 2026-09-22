<?php
/**
 * CRUD Utilisateurs — Suppression
 * Suppression d'un utilisateur avec effet de CASCADE configuré en base de données :
 * La suppression d'un utilisateur entraîne automatiquement la suppression de ses comptes et transactions.
 */
require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setFlash('danger', "Identifiant d'utilisateur non valide.");
    redirect(getBaseUrl() . 'utilisateurs/list.php');
}

if (!isset($pdo)) {
    setFlash('danger', "Connexion à la base de données indisponible.");
    redirect(getBaseUrl() . 'utilisateurs/list.php');
}

try {
    // 1. Récupération des informations pour le message de confirmation
    $stmt = $pdo->prepare("SELECT nom, prenom FROM Utilisateur WHERE id_utilisateur = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) {
        setFlash('warning', "L'utilisateur demandé n'existe pas ou a déjà été supprimé.");
        redirect(getBaseUrl() . 'utilisateurs/list.php');
    }

    // 2. Suppression de l'utilisateur
    // Note d'intégrité : la contrainte FOREIGN KEY (fk_compte_utilisateur) avec ON DELETE CASCADE
    // supprime automatiquement tous les comptes bancaires de cet utilisateur,
    // qui à leur tour suppriment leurs transactions associées (fk_transaction_compte ON DELETE CASCADE).
    $deleteStmt = $pdo->prepare("DELETE FROM Utilisateur WHERE id_utilisateur = ?");
    $deleteStmt->execute([$id]);

    setFlash('success', "L'utilisateur <strong>" . escape($user['prenom'] . ' ' . $user['nom']) . "</strong> ainsi que ses comptes et transactions associés ont été supprimés avec succès.");
} catch (PDOException $e) {
    setFlash('danger', "Erreur lors de la suppression de l'utilisateur : " . $e->getMessage());
}

redirect(getBaseUrl() . 'utilisateurs/list.php');
