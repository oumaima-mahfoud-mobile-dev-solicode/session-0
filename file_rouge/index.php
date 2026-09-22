<?php
/**
 * Page d'accueil — Tableau de bord (Dashboard)
 * Présentation des statistiques clés, des raccourcis rapides et des dernières transactions
 */
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Tableau de Bord';
$activeNav = 'dashboard';

// Initialisation des indicateurs
$totalSolde = 0.0;
$nbUsers = 0;
$nbComptes = 0;
$nbTransactions = 0;
$recentTransactions = [];
$comptesList = [];

if (isset($pdo)) {
    try {
        // 1. Somme totale des soldes de tous les comptes bancaires
        $stmt = $pdo->query("SELECT COALESCE(SUM(solde), 0) AS total_solde FROM Compte_bancaire");
        $totalSolde = (float)$stmt->fetchColumn();

        // 2. Nombre d'utilisateurs
        $stmt = $pdo->query("SELECT COUNT(*) FROM Utilisateur");
        $nbUsers = (int)$stmt->fetchColumn();

        // 3. Nombre de comptes
        $stmt = $pdo->query("SELECT COUNT(*) FROM Compte_bancaire");
        $nbComptes = (int)$stmt->fetchColumn();

        // 4. Nombre de transactions
        $stmt = $pdo->query("SELECT COUNT(*) FROM Transaction");
        $nbTransactions = (int)$stmt->fetchColumn();

        // 5. Comptes bancaires avec propriétaires
        $stmt = $pdo->query("
            SELECT c.id_compte, c.nom_compte, c.type_compte, c.solde, u.nom, u.prenom
            FROM Compte_bancaire c
            INNER JOIN Utilisateur u ON c.id_utilisateur = u.id_utilisateur
            ORDER BY c.solde DESC
        ");
        $comptesList = $stmt->fetchAll();

        // 6. Les 6 dernières transactions
        $stmt = $pdo->query("
            SELECT t.id_transaction, t.montant, t.type_transaction, t.description, t.date_transaction,
                   c.nom_compte, u.prenom, u.nom, cat.nom_categorie, cat.icone
            FROM Transaction t
            INNER JOIN Compte_bancaire c ON t.id_compte = c.id_compte
            INNER JOIN Utilisateur u ON c.id_utilisateur = u.id_utilisateur
            INNER JOIN Categorie_depense cat ON t.id_categorie = cat.id_categorie
            ORDER BY t.date_transaction DESC, t.id_transaction DESC
            LIMIT 6
        ");
        $recentTransactions = $stmt->fetchAll();
    } catch (PDOException $e) {
        $dbFetchError = $e->getMessage();
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">📊 Tableau de Bord</h1>
        <p class="page-subtitle">Vue d'ensemble de vos finances personnelles, soldes et opérations récentes</p>
    </div>
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="<?= $baseUrl ?>transactions/add.php" class="btn btn-primary">
            ➕ Nouvelle transaction
        </a>
        <a href="<?= $baseUrl ?>comptes/add.php" class="btn btn-secondary">
            🏦 Nouveau compte
        </a>
    </div>
</div>

<?php if (!empty($dbFetchError)): ?>
    <div class="alert alert-danger">
        <strong>Erreur SQL :</strong> <?= escape($dbFetchError) ?>
    </div>
<?php endif; ?>

<!-- Grille des statistiques clés -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">💰</div>
        <div class="stat-details">
            <div class="stat-label">Solde Total Global</div>
            <div class="stat-value <?= $totalSolde >= 0 ? 'amount-credit' : 'amount-debit' ?>">
                <?= formatMoney($totalSolde) ?>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">🏦</div>
        <div class="stat-details">
            <div class="stat-label">Comptes Bancaires</div>
            <div class="stat-value"><?= $nbComptes ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">👥</div>
        <div class="stat-details">
            <div class="stat-label">Utilisateurs</div>
            <div class="stat-value"><?= $nbUsers ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">💸</div>
        <div class="stat-details">
            <div class="stat-label">Transactions</div>
            <div class="stat-value"><?= $nbTransactions ?></div>
        </div>
    </div>
</div>

<!-- Navigation rapide vers les 4 modules -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <a href="<?= $baseUrl ?>utilisateurs/list.php" class="card" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 1rem; margin-bottom: 0; transition: transform 0.15s ease;">
        <span style="font-size: 2rem;">👥</span>
        <div>
            <h3 style="font-size: 1.05rem; font-weight: 600;">Utilisateurs</h3>
            <p style="font-size: 0.82rem; color: var(--text-muted);">Gérer les profils et accès</p>
        </div>
    </a>

    <a href="<?= $baseUrl ?>comptes/list.php" class="card" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 1rem; margin-bottom: 0; transition: transform 0.15s ease;">
        <span style="font-size: 2rem;">🏦</span>
        <div>
            <h3 style="font-size: 1.05rem; font-weight: 600;">Comptes</h3>
            <p style="font-size: 0.82rem; color: var(--text-muted);">Consulter et créer des comptes</p>
        </div>
    </a>

    <a href="<?= $baseUrl ?>categories/list.php" class="card" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 1rem; margin-bottom: 0; transition: transform 0.15s ease;">
        <span style="font-size: 2rem;">🏷️</span>
        <div>
            <h3 style="font-size: 1.05rem; font-weight: 600;">Catégories</h3>
            <p style="font-size: 0.82rem; color: var(--text-muted);">Dépenses et revenus</p>
        </div>
    </a>

    <a href="<?= $baseUrl ?>transactions/list.php" class="card" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 1rem; margin-bottom: 0; transition: transform 0.15s ease;">
        <span style="font-size: 2rem;">💸</span>
        <div>
            <h3 style="font-size: 1.05rem; font-weight: 600;">Transactions</h3>
            <p style="font-size: 0.82rem; color: var(--text-muted);">Historique et flux de fonds</p>
        </div>
    </a>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Synthèse des comptes -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h2 class="card-title">🏦 Aperçu des comptes</h2>
            <a href="<?= $baseUrl ?>comptes/list.php" class="btn btn-secondary btn-sm">Tous les comptes</a>
        </div>
        <?php if (empty($comptesList)): ?>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Aucun compte enregistré.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                <?php foreach (array_slice($comptesList, 0, 5) as $c): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.65rem 0.8rem; background: var(--bg); border-radius: var(--radius-sm); border: 1px solid var(--border);">
                        <div>
                            <div style="font-weight: 600; font-size: 0.9rem;"><?= escape($c['nom_compte']) ?></div>
                            <small style="color: var(--text-muted); font-size: 0.78rem;">
                                <?= escape($c['prenom'] . ' ' . $c['nom']) ?> &bull; <?= ucfirst(escape($c['type_compte'])) ?>
                            </small>
                        </div>
                        <strong class="<?= $c['solde'] >= 0 ? 'amount-credit' : 'amount-debit' ?>" style="font-size: 0.95rem;">
                            <?= formatMoney($c['solde']) ?>
                        </strong>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Dernières transactions -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h2 class="card-title">🕒 Transactions récentes</h2>
            <a href="<?= $baseUrl ?>transactions/list.php" class="btn btn-secondary btn-sm">Historique complet</a>
        </div>
        <?php if (empty($recentTransactions)): ?>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Aucune transaction effectuée.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Compte</th>
                            <th>Catégorie</th>
                            <th style="text-align: right;">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTransactions as $tx): ?>
                            <tr>
                                <td><?= formatDate($tx['date_transaction']) ?></td>
                                <td><?= escape($tx['description'] ?: 'Opération') ?></td>
                                <td><?= escape($tx['nom_compte']) ?></td>
                                <td><?= escape($tx['icone'] . ' ' . $tx['nom_categorie']) ?></td>
                                <td style="text-align: right;">
                                    <strong class="<?= $tx['type_transaction'] === 'credit' ? 'amount-credit' : 'amount-debit' ?>">
                                        <?= $tx['type_transaction'] === 'credit' ? '+' : '-' ?> <?= formatMoney($tx['montant']) ?>
                                    </strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Boîte informative pour les tests -->
<div class="card" style="background-color: #f1f5f9; border-color: #cbd5e1;">
    <h3 style="font-size: 1.05rem; font-weight: 600; margin-bottom: 0.5rem;">🧪 Guide de test rapide de l'application</h3>
    <p style="font-size: 0.9rem; color: #334155; margin-bottom: 0.75rem;">
        Pour tester la conformité complète des exigences :
    </p>
    <ul style="margin-left: 1.5rem; font-size: 0.88rem; color: #475569; display: flex; flex-direction: column; gap: 0.35rem;">
        <li><strong>Installation :</strong> Exécutez le script SQL <code>schema.sql</code> puis <code>seed.sql</code> dans MySQL Workbench ou en ligne de commande.</li>
        <li><strong>Synchronisation du solde :</strong> Allez dans <a href="<?= $baseUrl ?>transactions/add.php">Transactions ➔ Ajouter</a>. Ajoutez un débit de 50 €. Le solde du compte sélectionné diminue immédiatement de 50 €. Supprimez cette transaction : le compte est recrédité de 50 €.</li>
        <li><strong>Test de contrainte RESTRICT (Catégories) :</strong> Essayez de supprimer la catégorie "Alimentation" dans <a href="<?= $baseUrl ?>categories/list.php">Catégories</a>. Le système bloque la suppression car des transactions y sont associées.</li>
        <li><strong>Test de cascade ON DELETE CASCADE (Utilisateur) :</strong> Créez un utilisateur temporaire, ouvrez-lui un compte et une transaction. Supprimez l'utilisateur : son compte et sa transaction sont automatiquement supprimés en cascade.</li>
    </ul>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
