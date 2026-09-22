<?php
/**
 * CRUD Transactions — Ajout
 * Formulaire d'ajout d'une transaction avec mise à jour AUTOMATIQUE du solde du compte bancaire
 * Règle n°2 & n°5 : Encapsulation dans une transaction PDO (ACID)
 */
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Nouvelle Transaction';
$activeNav = 'transactions';
$errors = [];

// 1. Récupération des comptes avec les noms des titulaires
$comptes = [];
$categories = [];
try {
    $stmtComptes = $pdo->query("
        SELECT c.id_compte, c.nom_compte, c.solde, u.nom, u.prenom
        FROM Compte_bancaire c
        INNER JOIN Utilisateur u ON c.id_utilisateur = u.id_utilisateur
        ORDER BY u.nom, c.nom_compte
    ");
    $comptes = $stmtComptes->fetchAll();

    $stmtCat = $pdo->query("
        SELECT id_categorie, nom_categorie, type, icone
        FROM Categorie_depense
        ORDER BY type DESC, nom_categorie ASC
    ");
    $categories = $stmtCat->fetchAll();
} catch (PDOException $e) {
    $errors[] = "Erreur de chargement des données : " . $e->getMessage();
}

$id_compte = $_POST['id_compte'] ?? ($_GET['compte_id'] ?? '');
$id_categorie = $_POST['id_categorie'] ?? '';
$montant = $_POST['montant'] ?? '';
$type_transaction = $_POST['type_transaction'] ?? 'debit';
$description = trim($_POST['description'] ?? '');
$date_transaction = $_POST['date_transaction'] ?? date('Y-m-d');

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_compte = (int)$id_compte;
    $id_categorie = (int)$id_categorie;
    $montant = (float)str_replace(',', '.', trim($_POST['montant'] ?? '0'));
    $type_transaction = $_POST['type_transaction'] ?? 'debit';
    $description = trim($_POST['description'] ?? '');
    $date_transaction = trim($_POST['date_transaction'] ?? '');

    // Validations
    if ($id_compte <= 0) {
        $errors[] = "Veuillez sélectionner un compte bancaire.";
    }
    if ($id_categorie <= 0) {
        $errors[] = "Veuillez sélectionner une catégorie.";
    }
    if ($montant <= 0) {
        $errors[] = "Le montant doit être strictement supérieur à zéro.";
    }
    if (!in_array($type_transaction, ['debit', 'credit'])) {
        $errors[] = "Type de transaction non valide (doit être débit ou crédit).";
    }
    if (empty($date_transaction)) {
        $errors[] = "La date de la transaction est obligatoire.";
    }

    if (empty($errors)) {
        try {
            // Début de la transaction SQL pour assurer l'atomicité
            $pdo->beginTransaction();

            // 1. Insertion de la transaction
            $stmtInsert = $pdo->prepare("
                INSERT INTO Transaction (id_compte, id_categorie, montant, type_transaction, description, date_transaction)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmtInsert->execute([$id_compte, $id_categorie, $montant, $type_transaction, $description, $date_transaction]);

            // 2. Mise à jour automatique du solde du compte bancaire
            if ($type_transaction === 'credit') {
                $stmtSolde = $pdo->prepare("UPDATE Compte_bancaire SET solde = solde + ? WHERE id_compte = ?");
            } else {
                $stmtSolde = $pdo->prepare("UPDATE Compte_bancaire SET solde = solde - ? WHERE id_compte = ?");
            }
            $stmtSolde->execute([$montant, $id_compte]);

            // Validation de la transaction SQL
            $pdo->commit();

            setFlash('success', "Transaction de <strong>" . formatMoney($montant) . "</strong> (" . ($type_transaction === 'credit' ? 'crédit' : 'débit') . ") enregistrée et solde mis à jour avec succès.");
            redirect(getBaseUrl() . 'transactions/list.php?compte_id=' . $id_compte);
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">➕ Nouvelle Transaction</h1>
        <p class="page-subtitle">Ajouter un flux financier avec ajustement automatique du solde</p>
    </div>
    <a href="<?= $baseUrl ?>transactions/list.php" class="btn btn-secondary">
        ⬅️ Retour à la liste
    </a>
</div>

<div class="card form-container">
    <?php if (empty($comptes)): ?>
        <div class="alert alert-warning">
            <strong>Aucun compte bancaire :</strong> Vous devez d'abord créer un compte bancaire avant de pouvoir enregistrer des transactions.
            <div style="margin-top: 0.75rem;">
                <a href="<?= $baseUrl ?>comptes/add.php" class="btn btn-primary btn-sm">Créer un compte</a>
            </div>
        </div>
    <?php elseif (empty($categories)): ?>
        <div class="alert alert-warning">
            <strong>Aucune catégorie :</strong> Veuillez d'abord créer une catégorie de dépense ou de revenu.
            <div style="margin-top: 0.75rem;">
                <a href="<?= $baseUrl ?>categories/add.php" class="btn btn-primary btn-sm">Créer une catégorie</a>
            </div>
        </div>
    <?php else: ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin-left: 1.25rem;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= escape($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="js-validate" novalidate>
            <!-- Sélection du compte bancaire -->
            <div class="form-group">
                <label for="id_compte" class="form-label required">Compte bancaire cible</label>
                <select id="id_compte" name="id_compte" class="form-control" required>
                    <option value="">-- Choisir un compte bancaire --</option>
                    <?php foreach ($comptes as $c): ?>
                        <option value="<?= (int)$c['id_compte'] ?>" <?= (int)$id_compte === (int)$c['id_compte'] ? 'selected' : '' ?>>
                            <?= escape($c['nom_compte']) ?> — <?= escape($c['prenom'] . ' ' . strtoupper($c['nom'])) ?> (Solde: <?= formatMoney($c['solde']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text">Le solde du compte sélectionné sera actualisé dès la validation.</small>
            </div>

            <!-- Sélection de la catégorie -->
            <div class="form-group">
                <label for="id_categorie" class="form-label required">Catégorie</label>
                <select id="id_categorie" name="id_categorie" class="form-control" required>
                    <option value="">-- Choisir une catégorie --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id_categorie'] ?>" 
                                data-type="<?= escape($cat['type']) ?>"
                                <?= (int)$id_categorie === (int)$cat['id_categorie'] ? 'selected' : '' ?>>
                            <?= escape($cat['icone'] . ' ' . $cat['nom_categorie']) ?> (<?= $cat['type'] === 'revenu' ? 'Revenu' : 'Dépense' ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text">L'icône et le type orientent automatiquement le choix crédit/débit.</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="montant" class="form-label required">Montant (€)</label>
                    <input type="number" step="0.01" min="0.01" id="montant" name="montant" class="form-control" 
                           value="<?= escape((string)$montant) ?>" placeholder="Ex: 45.50" required>
                </div>
                <div class="form-group">
                    <label for="type_transaction" class="form-label required">Type d'opération</label>
                    <select id="type_transaction" name="type_transaction" class="form-control" required>
                        <option value="debit" <?= $type_transaction === 'debit' ? 'selected' : '' ?>>📉 Débit (Diminue le solde)</option>
                        <option value="credit" <?= $type_transaction === 'credit' ? 'selected' : '' ?>>📈 Crédit (Augmente le solde)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description / Libellé</label>
                <input type="text" id="description" name="description" class="form-control" 
                       value="<?= escape($description) ?>" placeholder="Ex: Courses supermarché, Facture électricité, Virement salaire...">
            </div>

            <div class="form-group">
                <label for="date_transaction" class="form-label required">Date de l'opération</label>
                <input type="date" id="date_transaction" name="date_transaction" class="form-control" 
                       value="<?= escape($date_transaction) ?>" required>
            </div>

            <div class="form-actions">
                <a href="<?= $baseUrl ?>transactions/list.php" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Valider la transaction</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
