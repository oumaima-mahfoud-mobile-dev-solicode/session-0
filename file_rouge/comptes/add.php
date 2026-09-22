<?php
/**
 * CRUD Comptes Bancaires — Ajout
 * Formulaire d'ajout de compte avec sélection de l'utilisateur propriétaire dans un menu déroulant
 */
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Nouveau Compte Bancaire';
$activeNav = 'comptes';
$errors = [];

// Récupération de la liste des utilisateurs pour la liste déroulante
$utilisateurs = [];
try {
    $stmt = $pdo->query("SELECT id_utilisateur, nom, prenom, email FROM Utilisateur ORDER BY nom, prenom");
    $utilisateurs = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = "Erreur lors du chargement des utilisateurs : " . $e->getMessage();
}

$id_utilisateur = $_POST['id_utilisateur'] ?? '';
$nom_compte = trim($_POST['nom_compte'] ?? '');
$type_compte = $_POST['type_compte'] ?? 'courant';
$solde = $_POST['solde'] ?? '0.00';
$date_ouverture = $_POST['date_ouverture'] ?? date('Y-m-d');

// Traitement du formulaire POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_utilisateur = (int)$id_utilisateur;
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
        $errors[] = "Le solde initial doit être une valeur numérique valide.";
    }
    if (empty($date_ouverture)) {
        $errors[] = "La date d'ouverture est requise.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO Compte_bancaire (id_utilisateur, nom_compte, type_compte, solde, date_ouverture)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$id_utilisateur, $nom_compte, $type_compte, (float)$solde, $date_ouverture]);

            setFlash('success', "Le compte <strong>" . escape($nom_compte) . "</strong> a été créé avec succès.");
            redirect(getBaseUrl() . 'comptes/list.php');
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la création du compte : " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">➕ Nouveau Compte Bancaire</h1>
        <p class="page-subtitle">Ouvrir un nouveau compte rattaché à un utilisateur</p>
    </div>
    <a href="<?= $baseUrl ?>comptes/list.php" class="btn btn-secondary">
        ⬅️ Retour à la liste
    </a>
</div>

<div class="card form-container">
    <?php if (empty($utilisateurs)): ?>
        <div class="alert alert-warning">
            <strong>Aucun utilisateur existant :</strong> Vous devez d'abord créer au moins un utilisateur avant de pouvoir créer un compte bancaire.
            <div style="margin-top: 0.75rem;">
                <a href="<?= $baseUrl ?>utilisateurs/add.php" class="btn btn-primary btn-sm">Créer un utilisateur maintenant</a>
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
            <!-- Sélection de la clé étrangère Utilisateur -->
            <div class="form-group">
                <label for="id_utilisateur" class="form-label required">Titulaire du compte (Utilisateur)</label>
                <select id="id_utilisateur" name="id_utilisateur" class="form-control" required>
                    <option value="">-- Sélectionner un utilisateur --</option>
                    <?php foreach ($utilisateurs as $u): ?>
                        <option value="<?= (int)$u['id_utilisateur'] ?>" <?= $id_utilisateur == $u['id_utilisateur'] ? 'selected' : '' ?>>
                            <?= escape($u['prenom'] . ' ' . strtoupper($u['nom'])) ?> (<?= escape($u['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text">Ce compte appartiendra à l'utilisateur sélectionné (relation 1,n).</small>
            </div>

            <div class="form-group">
                <label for="nom_compte" class="form-label required">Nom / Libellé du compte</label>
                <input type="text" id="nom_compte" name="nom_compte" class="form-control" 
                       value="<?= escape($nom_compte) ?>" placeholder="Ex: Compte Courant BNP, Livret A..." required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="type_compte" class="form-label required">Type de compte</label>
                    <select id="type_compte" name="type_compte" class="form-control" required>
                        <option value="courant" <?= $type_compte === 'courant' ? 'selected' : '' ?>>Compte Courant</option>
                        <option value="epargne" <?= $type_compte === 'epargne' ? 'selected' : '' ?>>Livret / Épargne</option>
                        <option value="autre" <?= $type_compte === 'autre' ? 'selected' : '' ?>>Autre (Investissement, etc.)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="solde" class="form-label required">Solde initial (€)</label>
                    <input type="number" step="0.01" id="solde" name="solde" class="form-control" 
                           value="<?= escape($solde) ?>" required>
                    <small class="form-text">Ce solde sera mis à jour par les transactions futures.</small>
                </div>
            </div>

            <div class="form-group">
                <label for="date_ouverture" class="form-label required">Date d'ouverture</label>
                <input type="date" id="date_ouverture" name="date_ouverture" class="form-control" 
                       value="<?= escape($date_ouverture) ?>" required>
            </div>

            <div class="form-actions">
                <a href="<?= $baseUrl ?>comptes/list.php" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Créer le compte bancaire</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
