<?php
/**
 * CRUD Utilisateurs — Liste
 * Affichage des utilisateurs avec jointure pour compter les comptes associés
 */
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Gestion des Utilisateurs';
$activeNav = 'utilisateurs';

// Récupération des utilisateurs avec le nombre de comptes bancaires associés (LEFT JOIN)
$users = [];
if (isset($pdo)) {
    try {
        $sql = "SELECT u.id_utilisateur, u.nom, u.prenom, u.email, u.date_creation,
                       COUNT(c.id_compte) AS nb_comptes
                FROM Utilisateur u
                LEFT JOIN Compte_bancaire c ON u.id_utilisateur = c.id_utilisateur
                GROUP BY u.id_utilisateur, u.nom, u.prenom, u.email, u.date_creation
                ORDER BY u.date_creation DESC";
        $stmt = $pdo->query($sql);
        $users = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Erreur lors de la récupération des utilisateurs : " . $e->getMessage();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">👥 Utilisateurs</h1>
        <p class="page-subtitle">Gestion des comptes utilisateurs de l'application</p>
    </div>
    <a href="<?= $baseUrl ?>utilisateurs/add.php" class="btn btn-primary">
        ➕ Nouvel utilisateur
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= escape($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Liste des utilisateurs inscrits (<?= count($users) ?>)</h2>
    </div>

    <?php if (empty($users)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">👤</div>
            <h3 class="empty-state-title">Aucun utilisateur trouvé</h3>
            <p>Commencez par ajouter votre premier utilisateur pour lui associer des comptes bancaires.</p>
            <div style="margin-top: 1rem;">
                <a href="<?= $baseUrl ?>utilisateurs/add.php" class="btn btn-primary">Créer un utilisateur</a>
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Comptes associés</th>
                        <th>Date d'inscription</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>#<?= (int)$user['id_utilisateur'] ?></td>
                            <td>
                                <strong><?= escape($user['prenom']) . ' ' . strtoupper(escape($user['nom'])) ?></strong>
                            </td>
                            <td><?= escape($user['email']) ?></td>
                            <td>
                                <span class="badge <?= $user['nb_comptes'] > 0 ? 'badge-courant' : 'badge-autre' ?>">
                                    🏦 <?= (int)$user['nb_comptes'] ?> compte(s)
                                </span>
                            </td>
                            <td><?= formatDate($user['date_creation']) ?></td>
                            <td style="text-align: right;">
                                <div class="table-actions" style="justify-content: flex-end;">
                                    <a href="<?= $baseUrl ?>utilisateurs/edit.php?id=<?= (int)$user['id_utilisateur'] ?>" class="btn btn-secondary btn-sm" title="Modifier">
                                        ✏️ Modifier
                                    </a>
                                    <a href="<?= $baseUrl ?>utilisateurs/delete.php?id=<?= (int)$user['id_utilisateur'] ?>" 
                                       class="btn btn-danger btn-sm" 
                                       title="Supprimer"
                                       data-confirm="⚠️ Attention : Supprimer cet utilisateur supprimera automatiquement en cascade tous ses comptes bancaires et l'historique de ses transactions. Confirmer la suppression ?">
                                        🗑️ Supprimer
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- ==========================================================
     Commentaires de test CRUD — Utilisateur :
     - Pour tester l'Ajout : cliquer sur 'Nouvel utilisateur', remplir le formulaire, vérifier l'insertion en BDD avec mot de passe haché.
     - Pour tester la Lecture : vérifier l'affichage des utilisateurs et le compteur exact de comptes associés.
     - Pour tester la Modification : cliquer sur 'Modifier', modifier le nom/email, vérifier la mise à jour sans altérer le mot de passe s'il n'est pas changé.
     - Pour tester la Suppression : cliquer sur 'Supprimer', constater la confirmation JS, puis vérifier la suppression en cascade du compte et de ses transactions.
     ========================================================== -->

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
