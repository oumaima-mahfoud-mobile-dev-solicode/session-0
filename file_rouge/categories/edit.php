<?php
/**
 * CRUD Catégories — Modification
 * Formulaire pré-rempli pour mettre à jour une catégorie existante
 */
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Modifier la Catégorie';
$activeNav = 'categories';
$errors = [];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    setFlash('danger', "Identifiant de catégorie non valide.");
    redirect(getBaseUrl() . 'categories/list.php');
}

// Récupération de la catégorie
try {
    $stmt = $pdo->prepare("SELECT * FROM Categorie_depense WHERE id_categorie = ?");
    $stmt->execute([$id]);
    $cat = $stmt->fetch();

    if (!$cat) {
        setFlash('danger', "Catégorie introuvable.");
        redirect(getBaseUrl() . 'categories/list.php');
    }
} catch (PDOException $e) {
    setFlash('danger', "Erreur : " . $e->getMessage());
    redirect(getBaseUrl() . 'categories/list.php');
}

$nom_categorie = $cat['nom_categorie'];
$type = $cat['type'];
$icone = $cat['icone'];

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_categorie = trim($_POST['nom_categorie'] ?? '');
    $type = $_POST['type'] ?? 'depense';
    $icone = trim($_POST['icone'] ?? '');

    if (empty($nom_categorie)) {
        $errors[] = "Le nom de la catégorie est obligatoire.";
    }
    if (!in_array($type, ['depense', 'revenu'])) {
        $errors[] = "Type de catégorie non valide.";
    }
    if (empty($icone)) {
        $icone = '🏷️';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE Categorie_depense
                SET nom_categorie = ?, type = ?, icone = ?
                WHERE id_categorie = ?
            ");
            $stmt->execute([$nom_categorie, $type, $icone, $id]);

            setFlash('success', "La catégorie <strong>" . escape($icone . ' ' . $nom_categorie) . "</strong> a été mise à jour.");
            redirect(getBaseUrl() . 'categories/list.php');
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">✏️ Modifier la Catégorie</h1>
        <p class="page-subtitle">Modification de la catégorie #<?= (int)$id ?> (<?= escape($cat['nom_categorie']) ?>)</p>
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
                   value="<?= escape($nom_categorie) ?>" required>
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
                   value="<?= escape($icone) ?>" style="font-size: 1.2rem;">
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
        </div>

        <div class="form-actions">
            <a href="<?= $baseUrl ?>categories/list.php" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
