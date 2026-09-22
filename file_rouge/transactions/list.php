<?php
/**
 * CRUD Transactions — Liste
 * Affichage des transactions avec jointures (Compte, Titulaire, Catégorie)
 * Affiche le solde actuel du compte et l'historique complet
 */
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Gestion des Transactions';
$activeNav = 'transactions';

$compteFilter = isset($_GET['compte_id']) ? (int)$_GET['compte_id'] : 0;
$categorieFilter = isset($_GET['categorie_id']) ? (int)$_GET['categorie_id'] : 0;

$transactions = [];
$comptes = [];
$categories = [];

if (isset($pdo)) {
    try {
        // Chargement des filtres
        $comptes = $pdo->query("SELECT id_compte, nom_compte FROM Compte_bancaire ORDER BY nom_compte")->fetchAll();
        $categories = $pdo->query("SELECT id_categorie, nom_categorie, icone FROM Categorie_depense ORDER BY nom_categorie")->fetchAll();

        // Requête principale avec triples jointures
        $sql = "SELECT t.id_transaction, t.montant, t.type_transaction, t.description, t.date_transaction,
                       c.id_compte, c.nom_compte, c.solde AS solde_compte,
                       u.id_utilisateur, u.nom AS nom_user, u.prenom AS prenom_user,
                       cat.id_categorie, cat.nom_categorie, cat.type AS type_cat, cat.icone AS icone_cat
                FROM Transaction t
                INNER JOIN Compte_bancaire c ON t.id_compte = c.id_compte
                INNER JOIN Utilisateur u ON c.id_utilisateur = u.id_utilisateur
                INNER JOIN Categorie_depense cat ON t.id_categorie = cat.id_categorie
                WHERE 1=1 ";

        $params = [];
        if ($compteFilter > 0) {
            $sql .= " AND t.id_compte = ? ";
            $params[] = $compteFilter;
        }
        if ($categorieFilter > 0) {
            $sql .= " AND t.id_categorie = ? ";
            $params[] = $categorieFilter;
        }

        $sql .= " ORDER BY t.date_transaction DESC, t.id_transaction DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Erreur lors de la récupération des transactions : " . $e->getMessage();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">💸 Transactions Financières</h1>
        <p class="page-subtitle">Suivi des débits, crédits et impact direct sur les soldes bancaires</p>
    </div>
    <a href="<?= $baseUrl ?>transactions/add.php" class="btn btn-primary">
        ➕ Nouvelle transaction
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= escape($error) ?></div>
<?php endif; ?>

<!-- Barre de filtres rapides -->
<div class="card" style="padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
    <form method="GET" action="" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <label style="font-weight: 500; font-size: 0.9rem;">Filtrer par :</label>

        <select name="compte_id" class="form-control" style="width: auto; min-width: 200px;" onchange="this.form.submit()">
            <option value="">Tous les comptes bancaires</option>
            <?php foreach ($comptes as $c): ?>
                <option value="<?= (int)$c['id_compte'] ?>" <?= $compteFilter === (int)$c['id_compte'] ? 'selected' : '' ?>>
                    <?= escape($c['nom_compte']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="categorie_id" class="form-control" style="width: auto; min-width: 200px;" onchange="this.form.submit()">
            <option value="">Toutes les catégories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id_categorie'] ?>" <?= $categorieFilter === (int)$cat['id_categorie'] ? 'selected' : '' ?>>
                    <?= escape($cat['icone'] . ' ' . $cat['nom_categorie']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ($compteFilter > 0 || $categorieFilter > 0): ?>
            <a href="<?= $baseUrl ?>transactions/list.php" class="btn btn-secondary btn-sm">Réinitialiser</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Historique des transactions (<?= count($transactions) ?>)</h2>
    </div>

    <?php if (empty($transactions)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">💸</div>
            <h3 class="empty-state-title">Aucune transaction trouvée</h3>
            <p>Enregistrez un débit ou un crédit sur l'un de vos comptes bancaires.</p>
            <div style="margin-top: 1rem;">
                <a href="<?= $baseUrl ?>transactions/add.php" class="btn btn-primary">Créer une transaction</a>
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Compte bancaire</th>
                        <th>Solde actuel</th>
                        <th>Catégorie</th>
                        <th>Montant</th>
                        <th>Type</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td>
                                <strong><?= formatDate($tx['date_transaction']) ?></strong>
                            </td>
                            <td>
                                <strong><?= escape($tx['description'] ?: 'Transaction') ?></strong>
                            </td>
                            <td>
                                🏦 <a href="<?= $baseUrl ?>comptes/edit.php?id=<?= (int)$tx['id_compte'] ?>" style="color: inherit; text-decoration: none; font-weight: 500;">
                                    <?= escape($tx['nom_compte']) ?>
                                </a>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">
                                    <?= escape($tx['prenom_user'] . ' ' . $tx['nom_user']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="<?= $tx['solde_compte'] >= 0 ? 'amount-credit' : 'amount-debit' ?>" style="font-size: 0.88rem;">
                                    <?= formatMoney($tx['solde_compte']) ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 1.1rem; vertical-align: middle; margin-right: 0.2rem;">
                                    <?= escape($tx['icone_cat']) ?>
                                </span>
                                <?= escape($tx['nom_categorie']) ?>
                            </td>
                            <td>
                                <span class="<?= $tx['type_transaction'] === 'credit' ? 'amount-credit' : 'amount-debit' ?>" style="font-size: 1rem;">
                                    <?= $tx['type_transaction'] === 'credit' ? '+' : '-' ?> <?= formatMoney($tx['montant']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($tx['type_transaction'] === 'credit'): ?>
                                    <span class="badge badge-credit">📈 Crédit</span>
                                <?php else: ?>
                                    <span class="badge badge-debit">📉 Débit</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div class="table-actions" style="justify-content: flex-end;">
                                    <a href="<?= $baseUrl ?>transactions/edit.php?id=<?= (int)$tx['id_transaction'] ?>" class="btn btn-secondary btn-sm" title="Modifier">
                                        ✏️ Modifier
                                    </a>
                                    <a href="<?= $baseUrl ?>transactions/delete.php?id=<?= (int)$tx['id_transaction'] ?>" 
                                       class="btn btn-danger btn-sm" 
                                       title="Supprimer"
                                       data-confirm="⚠️ Supprimer cette transaction annulera automatiquement son impact financier sur le solde du compte. Confirmer ?">
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
     Commentaires de test CRUD & Synchronisation du Solde — Transactions :
     - Règle n°2 & n°5 : Le solde du compte bancaire est recalculé immédiatement en base de données.
     - Pour tester l'Ajout :
       1. Noter le solde initial du compte choisi (ex: 1 500,00 €).
       2. Ajouter un Débit de 100,00 €.
       3. Constater que le solde du compte passe instantanément à 1 400,00 €.
     - Pour tester la Suppression :
       1. Supprimer ce débit de 100,00 €.
       2. Constater que le compte est automatiquement recrédité de 100,00 € pour revenir à 1 500,00 €.
     - Pour tester la Modification :
       1. Modifier le montant ou passer de débit à crédit : le différentiel s'applique avec rollback sécurisé en cas d'anomalie.
     ========================================================== -->

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
