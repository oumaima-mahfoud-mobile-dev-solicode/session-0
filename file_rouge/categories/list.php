<?php
/**
 * CRUD Catégories — Liste
 * Affichage des catégories de dépenses et revenus avec le nombre de transactions associées
 */
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Gestion des Catégories';
$activeNav = 'categories';

$categories = [];
if (isset($pdo)) {
    try {
        $sql = "SELECT c.id_categorie, c.nom_categorie, c.type, c.icone,
                       COUNT(t.id_transaction) AS nb_transactions
                FROM Categorie_depense c
                LEFT JOIN Transaction t ON c.id_categorie = t.id_categorie
                GROUP BY c.id_categorie, c.nom_categorie, c.type, c.icone
                ORDER BY c.type DESC, c.nom_categorie ASC";
        $stmt = $pdo->query($sql);
        $categories = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Erreur lors de la récupération des catégories : " . $e->getMessage();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">🏷️ Catégories de Dépenses & Revenus</h1>
        <p class="page-subtitle">Classification des flux financiers pour un suivi budgétaire précis</p>
    </div>
    <a href="<?= $baseUrl ?>categories/add.php" class="btn btn-primary">
        ➕ Nouvelle catégorie
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= escape($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Liste des catégories configurées (<?= count($categories) ?>)</h2>
    </div>

    <?php if (empty($categories)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🏷️</div>
            <h3 class="empty-state-title">Aucune catégorie définie</h3>
            <p>Créez des catégories (ex: Alimentation, Salaire, Transport) pour classer vos transactions.</p>
            <div style="margin-top: 1rem;">
                <a href="<?= $baseUrl ?>categories/add.php" class="btn btn-primary">Créer une catégorie</a>
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Icône</th>
                        <th>Nom de la catégorie</th>
                        <th>Type de flux</th>
                        <th>Transactions associées</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>#<?= (int)$cat['id_categorie'] ?></td>
                            <td style="font-size: 1.4rem;"><?= escape($cat['icone'] ?: '🏷️') ?></td>
                            <td>
                                <strong><?= escape($cat['nom_categorie']) ?></strong>
                            </td>
                            <td>
                                <?php if ($cat['type'] === 'revenu'): ?>
                                    <span class="badge badge-credit">📈 Revenu</span>
                                <?php else: ?>
                                    <span class="badge badge-debit">📉 Dépense</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $cat['nb_transactions'] > 0 ? 'badge-courant' : 'badge-autre' ?>">
                                    <?= (int)$cat['nb_transactions'] ?> transaction(s)
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div class="table-actions" style="justify-content: flex-end;">
                                    <a href="<?= $baseUrl ?>categories/edit.php?id=<?= (int)$cat['id_categorie'] ?>" class="btn btn-secondary btn-sm" title="Modifier">
                                        ✏️ Modifier
                                    </a>
                                    <a href="<?= $baseUrl ?>categories/delete.php?id=<?= (int)$cat['id_categorie'] ?>" 
                                       class="btn btn-danger btn-sm" 
                                       title="Supprimer"
                                       data-confirm="Êtes-vous sûr de vouloir supprimer la catégorie '<?= escape($cat['nom_categorie']) ?>' ? (La suppression sera bloquée si elle contient des transactions)">
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
     Commentaires de test CRUD — Catégories :
     - Pour tester l'Ajout : cliquer sur 'Nouvelle catégorie', choisir un nom, un type (dépense/revenu) et un emoji/icône.
     - Pour tester la Lecture : vérifier le regroupement par type et le comptage des transactions liées.
     - Pour tester la Modification : modifier l'intitulé ou l'icône, sauvegarder.
     - Pour tester la Règle d'intégrité RESTRICT : tenter de supprimer une catégorie qui a des transactions (ex: Alimentation).
       Résultat attendu : La contrainte FOREIGN KEY (RESTRICT) empêche la suppression et un message d'avertissement explicite s'affiche.
     - Tester la suppression d'une catégorie neuve (0 transaction) : la suppression réussit sans problème.
     ========================================================== -->

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
