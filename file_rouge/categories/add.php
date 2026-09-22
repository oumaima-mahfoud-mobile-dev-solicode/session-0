<?php
/**
 * CRUD Catégories — Ajout
 * Formulaire de création d'une catégorie (Dépense ou Revenu) avec choix d'icône/emoji
 */
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Nouvelle Catégorie';
$activeNav = 'categories';
$errors = [];

$nom_categorie = trim($_POST['nom_categorie'] ?? '');
$type = $_POST['type'] ?? 'depense';
$icone = trim($_POST['icone'] ?? '🛒');

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($nom_categorie)) {
        $errors[] = "Le nom de la catégorie est obligatoire.";
    }
    if (!in_array($type, ['depense', 'revenu'])) {
        $errors[] = "Type de catégorie non valide.";
    }
    if (empty($icone)) {
        $icone = '🏷️';
    }

    if (empty($errors) && isset($pdo)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO Categorie_depense (nom_categorie, type, icone)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$nom_categorie, $type, $icone]);

            setFlash('success', "La catégorie <strong>" . escape($icone . ' ' . $nom_categorie) . "</strong> a été créée avec succès.");
            redirect(getBaseUrl() . 'categories/list.php');
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la création de la catégorie : " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">➕ Nouvelle Catégorie</h1>
        <p class="page-subtitle">Créer une catégorie pour classer les flux financiers</p>
    </div>
    <a href="<?= $baseUrl ?>categories/list.php" class="btn btn-secondary">
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
            <label for="nom_categorie" class="form-label required">Nom de la catégorie</label>
            <input type="text" id="nom_categorie" name="nom_categorie" class="form-control" 
                   value="<?= escape($nom_categorie) ?>" placeholder="Ex: Alimentation, Abonnement, Cadeaux..." required autofocus>
        </div>

        <div class="form-group">
            <label for="type" class="form-label required">Type de catégorie</label>
            <select id="type" name="type" class="form-control" required>
                <option value="depense" <?= $type === 'depense' ? 'selected' : '' ?>>📉 Dépense (Sortie d'argent)</option>
                <option value="revenu" <?= $type === 'revenu' ? 'selected' : '' ?>>📈 Revenu (Entrée d'argent)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="icone" class="form-label">Icône / Emoji</label>
            <input type="text" id="icone" name="icone" class="form-control" 
                   value="<?= escape($icone) ?>" placeholder="Ex: 🛒" style="font-size: 1.2rem;">
            <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <?php
                $presets = ['🛒', '🚗', '🎬', '💼', '🏠', '💊', '🍔', '✈️', '💻', '🎁', '📚', '⚡', '☕', '👕'];
                foreach ($presets as $emoji):
                ?>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('icone').value='<?= $emoji ?>'">
                        <?= $emoji ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <small class="form-text">Cliquez sur une suggestion ou saisissez un emoji personnalisé.</small>
        </div>

        <div class="form-actions">
            <a href="<?= $baseUrl ?>categories/list.php" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Créer la catégorie</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
