<?php
/**
 * CRUD Transactions — Modification
 * Formulaire pré-rempli avec ajustement automatique et cohérent du solde bancaire
 * Gère l'annulation de l'ancien impact et l'application du nouvel impact, même si le compte a été changé.
 */
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Modifier la Transaction';
$activeNav = 'transactions';
$errors = [];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    setFlash('danger', "Identifiant de transaction invalide.");
    redirect(getBaseUrl() . 'transactions/list.php');
}

// 1. Récupération des données existantes
try {
    $stmtTx = $pdo->prepare("SELECT * FROM Transaction WHERE id_transaction = ?");
    $stmtTx->execute([$id]);
    $tx = $stmtTx->fetch();

    if (!$tx) {
        setFlash('danger', "Transaction introuvable.");
        redirect(getBaseUrl() . 'transactions/list.php');
    }

    $comptes = $pdo->query("
        SELECT c.id_compte, c.nom_compte, c.solde, u.nom, u.prenom
        FROM Compte_bancaire c
        INNER JOIN Utilisateur u ON c.id_utilisateur = u.id_utilisateur
        ORDER BY u.nom, c.nom_compte
    ")->fetchAll();

    $categories = $pdo->query("
        SELECT id_categorie, nom_categorie, type, icone
        FROM Categorie_depense
        ORDER BY type DESC, nom_categorie ASC
    ")->fetchAll();
} catch (PDOException $e) {
    setFlash('danger', "Erreur : " . $e->getMessage());
    redirect(getBaseUrl() . 'transactions/list.php');
}

$id_compte = $tx['id_compte'];
$id_categorie = $tx['id_categorie'];
$montant = $tx['montant'];
$type_transaction = $tx['type_transaction'];
$description = $tx['description'];
$date_transaction = $tx['date_transaction'];

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_id_compte = (int)($_POST['id_compte'] ?? 0);
    $new_id_categorie = (int)($_POST['id_categorie'] ?? 0);
    $new_montant = (float)str_replace(',', '.', trim($_POST['montant'] ?? '0'));
    $new_type_transaction = $_POST['type_transaction'] ?? 'debit';
    $new_description = trim($_POST['description'] ?? '');
    $new_date_transaction = trim($_POST['date_transaction'] ?? '');

    // Validations
    if ($new_id_compte <= 0) {
        $errors[] = "Veuillez sélectionner un compte bancaire.";
    }
    if ($new_id_categorie <= 0) {
        $errors[] = "Veuillez sélectionner une catégorie.";
    }
    if ($new_montant <= 0) {
        $errors[] = "Le montant doit être supérieur à zéro.";
    }
    if (!in_array($new_type_transaction, ['debit', 'credit'])) {
        $errors[] = "Type de transaction invalide.";
    }
    if (empty($new_date_transaction)) {
        $errors[] = "La date de la transaction est obligatoire.";
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // 1. Annulation de l'ancien impact sur l'ancien compte
            $old_compte_id = (int)$tx['id_compte'];
            $old_montant = (float)$tx['montant'];
            $old_type = $tx['type_transaction'];

            if ($old_type === 'credit') {
                $revertStmt = $pdo->prepare("UPDATE Compte_bancaire SET solde = solde - ? WHERE id_compte = ?");
            } else {
                $revertStmt = $pdo->prepare("UPDATE Compte_bancaire SET solde = solde + ? WHERE id_compte = ?");
            }
            $revertStmt->execute([$old_montant, $old_compte_id]);

            // 2. Application du nouvel impact sur le nouveau compte
            if ($new_type_transaction === 'credit') {
                $applyStmt = $pdo->prepare("UPDATE Compte_bancaire SET solde = solde + ? WHERE id_compte = ?");
            } else {
                $applyStmt = $pdo->prepare("UPDATE Compte_bancaire SET solde = solde - ? WHERE id_compte = ?");
            }
            $applyStmt->execute([$new_montant, $new_id_compte]);

            // 3. Mise à jour de la transaction
            $updateTx = $pdo->prepare("
                UPDATE Transaction 
                SET id_compte = ?, id_categorie = ?, montant = ?, type_transaction = ?, description = ?, date_transaction = ?
                WHERE id_transaction = ?
            ");
            $updateTx->execute([$new_id_compte, $new_id_categorie, $new_montant, $new_type_transaction, $new_description, $new_date_transaction, $id]);

            $pdo->commit();

            setFlash('success', "La transaction #{$id} a été mise à jour et le(s) solde(s) recalculé(s) avec succès.");
            redirect(getBaseUrl() . 'transactions/list.php?compte_id=' . $new_id_compte);
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = "Erreur lors de la modification : " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">✏️ Modifier la Transaction</h1>
        <p class="page-subtitle">Modification de l'opération #<?= (int)$id ?></p>
    </div>
    <a href="<?= $baseUrl ?>transactions/list.php" class="btn btn-secondary">
        ⬅️ Retour à la liste
    </a>
</div>

<div class="card form-container">
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
        <div class="form-group">
            <label for="id_compte" class="form-label required">Compte bancaire</label>
            <select id="id_compte" name="id_compte" class="form-control" required>
                <?php foreach ($comptes as $c): ?>
                    <option value="<?= (int)$c['id_compte'] ?>" <?= (int)$id_compte === (int)$c['id_compte'] ? 'selected' : '' ?>>
                        <?= escape($c['nom_compte']) ?> — <?= escape($c['prenom'] . ' ' . strtoupper($c['nom'])) ?> (Solde: <?= formatMoney($c['solde']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="id_categorie" class="form-label required">Catégorie</label>
            <select id="id_categorie" name="id_categorie" class="form-control" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id_categorie'] ?>" 
                            data-type="<?= escape($cat['type']) ?>"
                            <?= (int)$id_categorie === (int)$cat['id_categorie'] ? 'selected' : '' ?>>
                        <?= escape($cat['icone'] . ' ' . $cat['nom_categorie']) ?> (<?= $cat['type'] === 'revenu' ? 'Revenu' : 'Dépense' ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="montant" class="form-label required">Montant (€)</label>
                <input type="number" step="0.01" min="0.01" id="montant" name="montant" class="form-control" 
                       value="<?= escape((string)$montant) ?>" required>
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
                   value="<?= escape($description) ?>">
        </div>

        <div class="form-group">
            <label for="date_transaction" class="form-label required">Date de l'opération</label>
            <input type="date" id="date_transaction" name="date_transaction" class="form-control" 
                       value="<?= escape($date_transaction) ?>" required>
        </div>

        <div class="form-actions">
            <a href="<?= $baseUrl ?>transactions/list.php" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
