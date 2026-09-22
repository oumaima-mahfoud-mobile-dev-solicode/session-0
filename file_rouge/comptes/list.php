<?php
/**
 * CRUD Comptes Bancaires — Liste
 * Affichage des comptes avec jointure pour afficher le nom complet de l'utilisateur propriétaire
 */
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Gestion des Comptes Bancaires';
$activeNav = 'comptes';

$comptes = [];
if (isset($pdo)) {
    try {
        // Jointure explicite avec Utilisateur et comptage des transactions
        $sql = "SELECT c.id_compte, c.nom_compte, c.type_compte, c.solde, c.date_ouverture,
                       u.id_utilisateur, u.nom, u.prenom, u.email,
                       COUNT(t.id_transaction) AS nb_transactions
                FROM Compte_bancaire c
                INNER JOIN Utilisateur u ON c.id_utilisateur = u.id_utilisateur
                LEFT JOIN Transaction t ON c.id_compte = t.id_compte
                GROUP BY c.id_compte, c.nom_compte, c.type_compte, c.solde, c.date_ouverture,
                         u.id_utilisateur, u.nom, u.prenom, u.email
                ORDER BY c.solde DESC";
        $stmt = $pdo->query($sql);
        $comptes = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Erreur lors de la récupération des comptes : " . $e->getMessage();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">🏦 Comptes Bancaires</h1>
        <p class="page-subtitle">Gestion des comptes financiers et suivi des soldes</p>
    </div>
    <a href="<?= $baseUrl ?>comptes/add.php" class="btn btn-primary">
        ➕ Nouveau compte
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= escape($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Liste des comptes actifs (<?= count($comptes) ?>)</h2>
    </div>

    <?php if (empty($comptes)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">💳</div>
            <h3 class="empty-state-title">Aucun compte bancaire enregistré</h3>
            <p>Créez un compte pour commencer à y imputer des transactions de débit ou de crédit.</p>
            <div style="margin-top: 1rem;">
                <a href="<?= $baseUrl ?>comptes/add.php" class="btn btn-primary">Ajouter un compte</a>
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom du compte</th>
                        <th>Titulaire (Utilisateur)</th>
                        <th>Type</th>
                        <th>Solde actuel</th>
                        <th>Transactions</th>
                        <th>Date d'ouverture</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comptes as $compte): ?>
                        <tr>
                            <td>#<?= (int)$compte['id_compte'] ?></td>
                            <td>
                                <strong><?= escape($compte['nom_compte']) ?></strong>
                            </td>
                            <td>
                                👤 <a href="<?= $baseUrl ?>utilisateurs/edit.php?id=<?= (int)$compte['id_utilisateur'] ?>" style="color: var(--primary); text-decoration: none; font-weight: 500;">
                                    <?= escape($compte['prenom'] . ' ' . strtoupper($compte['nom'])) ?>
                                </a>
                            </td>
                            <td>
                                <span class="badge badge-<?= escape($compte['type_compte']) ?>">
                                    <?= ucfirst(escape($compte['type_compte'])) ?>
                                </span>
                            </td>
                            <td>
                                <strong class="<?= $compte['solde'] >= 0 ? 'amount-credit' : 'amount-debit' ?>">
                                    <?= formatMoney($compte['solde']) ?>
                                </strong>
                            </td>
                            <td>
                                <span class="badge badge-autre">
                                    <?= (int)$compte['nb_transactions'] ?> op.
                                </span>
                            </td>
                            <td><?= formatDate($compte['date_ouverture']) ?></td>
                            <td style="text-align: right;">
                                <div class="table-actions" style="justify-content: flex-end;">
                                    <a href="<?= $baseUrl ?>transactions/list.php?compte_id=<?= (int)$compte['id_compte'] ?>" class="btn btn-secondary btn-sm" title="Voir les transactions">
                                        👁️ Voir
                                    </a>
                                    <a href="<?= $baseUrl ?>comptes/edit.php?id=<?= (int)$compte['id_compte'] ?>" class="btn btn-secondary btn-sm" title="Modifier">
                                        ✏️ Modifier
                                    </a>
                                    <a href="<?= $baseUrl ?>comptes/delete.php?id=<?= (int)$compte['id_compte'] ?>" 
                                       class="btn btn-danger btn-sm" 
                                       title="Supprimer"
                                       data-confirm="⚠️ Attention : La suppression de ce compte supprimera également toutes ses transactions associées en cascade. Confirmer la suppression ?">
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
     Commentaires de test CRUD — Compte Bancaire :
     - Pour tester l'Ajout : cliquer sur 'Nouveau compte', sélectionner un utilisateur dans la liste déroulante, renseigner le nom, le type et le solde initial.
     - Pour tester la Lecture : vérifier la jointure avec le titulaire et l'affichage précis du solde en direct.
     - Pour tester la Modification : cliquer sur 'Modifier', changer le libellé ou réassigner à un autre titulaire.
     - Pour tester la Suppression : supprimer le compte et constater que toutes les transactions rattachées disparaissent en cascade.
     ========================================================== -->

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
