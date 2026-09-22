<?php
/**
 * CRUD Comptes Bancaires — Modification
 * Formulaire pré-rempli pour mettre à jour les propriétés du compte bancaire
 */
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Modifier le Compte Bancaire';
$activeNav = 'comptes';
$errors = [];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    setFlash('danger', "Identifiant de compte invalide.");
    redirect(getBaseUrl() . 'comptes/list.php');
}

// Récupération des utilisateurs pour le select
$utilisateurs = [];
try {
    $stmt = $pdo->query("SELECT id_utilisateur, nom, prenom, email FROM Utilisateur ORDER BY nom, prenom");
    $utilisateurs = $stmt->fetchAll();

    // Récupération du compte existant
    $stmt = $pdo->prepare("SELECT * FROM Compte_bancaire WHERE id_compte = ?");
    $stmt->execute([$id]);
    $compte = $stmt->fetch();

    if (!$compte) {
        setFlash('danger', "Compte bancaire introuvable.");
        redirect(getBaseUrl() . 'comptes/list.php');
    }
} catch (PDOException $e) {
    setFlash('danger', "Erreur : " . $e->getMessage());
    redirect(getBaseUrl() . 'comptes/list.php');
}

$id_utilisateur = $compte['id_utilisateur'];
$nom_compte = $compte['nom_compte'];
$type_compte = $compte['type_compte'];
$solde = $compte['solde'];
$date_ouverture = $compte['date_ouverture'];

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_utilisateur = (int)($_POST['id_utilisateur'] ?? 0);
    $nom_compte = trim($_POST['nom_compte'] ?? '');
    $type_compte = $_POST['type_compte'] ?? 'courant';
    $solde = trim($_POST['solde'] ?? '0');
    $date_ouverture = trim($_POST['date_ouverture'] ?? '');

    // Validations
    if ($id_utilisateur <= 0) {
        $errors[] = "Veuillez sélectionner le propriétaire du compte.";
    }
    if (empty($nom_compte)) {
        $errors[] = "Le nom du compte est obligatoire.";
    }
    if (!in_array($type_compte, ['courant', 'epargne', 'autre'])) {
        $errors[] = "Type de compte non valide.";
    }
    if (!is_numeric($solde)) {
        $errors[] = "Le solde doit être une valeur numérique valide.";
    }
    if (empty($date_ouverture)) {
        $errors[] = "La date d'ouverture est requise.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE Compte_bancaire
                SET id_utilisateur = ?, nom_compte = ?, type_compte = ?, solde = ?, date_ouverture = ?
                WHERE id_compte = ?
            ");
            $stmt->execute([$id_utilisateur, $nom_compte, $type_compte, (float)$solde, $date_ouverture, $id]);

            setFlash('success', "Le compte <strong>" . escape($nom_compte) . "</strong> a été mis à jour avec succès.");
            redirect(getBaseUrl() . 'comptes/list.php');
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">✏️ Modifier le Compte Bancaire</h1>
        <p class="page-subtitle">Modification du compte #<?= (int)$id ?> (<?= escape($compte['nom_compte']) ?>)</p>
    </div>
    <a href="<?= $baseUrl ?>comptes/list.php" class="btn btn-secondary">
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
            <label for="id_utilisateur" class="form-label required">Titulaire du compte</label>
            <select id="id_utilisateur" name="id_utilisateur" class="form-control" required>
                <?php foreach ($utilisateurs as $u): ?>
                    <option value="<?= (int)$u['id_utilisateur'] ?>" <?= $id_utilisateur == $u['id_utilisateur'] ? 'selected' : '' ?>>
                        <?= escape($u['prenom'] . ' ' . strtoupper($u['nom'])) ?> (<?= escape($u['email']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="nom_compte" class="form-label required">Nom / Libellé du compte</label>
            <input type="text" id="nom_compte" name="nom_compte" class="form-control" 
                   value="<?= escape($nom_compte) ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="type_compte" class="form-label required">Type de compte</label>
                <select id="type_compte" name="type_compte" class="form-control" required>
                    <option value="courant" <?= $type_compte === 'courant' ? 'selected' : '' ?>>Compte Courant</option>
                    <option value="epargne" <?= $type_compte === 'epargne' ? 'selected' : '' ?>>Livret / Épargne</option>
                    <option value="autre" <?= $type_compte === 'autre' ? 'selected' : '' ?>>Autre</option>
                </select>
            </div>
            <div class="form-group">
                <label for="solde" class="form-label required">Solde (€)</label>
                <input type="number" step="0.01" id="solde" name="solde" class="form-control" 
                       value="<?= escape($solde) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="date_ouverture" class="form-label required">Date d'ouverture</label>
            <input type="date" id="date_ouverture" name="date_ouverture" class="form-control" 
                   value="<?= escape($date_ouverture) ?>" required>
        </div>

        <div class="form-actions">
            <a href="<?= $baseUrl ?>comptes/list.php" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
