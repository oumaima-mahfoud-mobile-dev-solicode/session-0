<?php
/**
 * CRUD Catégories — Suppression
 * Règle d'intégrité référentielle :
 * La contrainte FOREIGN KEY (fk_transaction_categorie) est configurée en ON DELETE RESTRICT.
 * Si la catégorie est encore utilisée par des transactions, MySQL bloque l'opération.
 * Ce script intercepte cette contrainte pour afficher un message explicatif et convivial à l'utilisateur.
 */
require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setFlash('danger', "Identifiant de catégorie non valide.");
    redirect(getBaseUrl() . 'categories/list.php');
}

if (!isset($pdo)) {
    setFlash('danger', "Connexion à la base de données indisponible.");
    redirect(getBaseUrl() . 'categories/list.php');
}

try {
    // 1. Récupération de la catégorie
    $stmt = $pdo->prepare("SELECT nom_categorie, icone FROM Categorie_depense WHERE id_categorie = ?");
    $stmt->execute([$id]);
    $cat = $stmt->fetch();

    if (!$cat) {
        setFlash('warning', "La catégorie demandée n'existe pas ou a déjà été supprimée.");
        redirect(getBaseUrl() . 'categories/list.php');
    }

    // 2. Vérification proactive du nombre de transactions rattachées
    $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM Transaction WHERE id_categorie = ?");
    $countStmt->execute([$id]);
    $nbTransactions = (int)$countStmt->fetchColumn();

    if ($nbTransactions > 0) {
        // Règle d'intégrité : blocage explicite
        setFlash('warning', "⚠️ <strong>Suppression impossible :</strong> La catégorie <em>'" . escape($cat['nom_categorie']) . "'</em> est actuellement associée à <strong>{$nbTransactions} transaction(s)</strong>. Pour garantir l'intégrité comptable de vos données, veuillez d'abord réassigner ou supprimer ces transactions.");
        redirect(getBaseUrl() . 'categories/list.php');
    }

    // 3. Suppression effective si aucune transaction n'y fait référence
    $deleteStmt = $pdo->prepare("DELETE FROM Categorie_depense WHERE id_categorie = ?");
    $deleteStmt->execute([$id]);

    setFlash('success', "La catégorie <strong>" . escape($cat['icone'] . ' ' . $cat['nom_categorie']) . "</strong> a été supprimée avec succès.");
} catch (PDOException $e) {
    // Interception au cas où une contrainte SQLSTATE 23000 se déclenche
    if ($e->getCode() == '23000' || str_contains($e->getMessage(), 'foreign key constraint fails')) {
        setFlash('warning', "⚠️ Impossible de supprimer cette catégorie car elle est utilisée par des transactions (Contrainte de clé étrangère RESTRICT).");
    } else {
        setFlash('danger', "Erreur lors de la suppression : " . $e->getMessage());
    }
}

redirect(getBaseUrl() . 'categories/list.php');
