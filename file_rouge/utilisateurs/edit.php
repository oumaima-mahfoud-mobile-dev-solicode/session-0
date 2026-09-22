<?php
/**
 * CRUD Utilisateurs — Modification
 * Formulaire de modification pré-rempli avec mise à jour sécurisée via requêtes préparées
 */
require_once __DIR__ . '/../config/db.php';

$pageTitle = "Modifier l'Utilisateur";
$activeNav = 'utilisateurs';
$errors = [];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    setFlash('danger', "Identifiant d'utilisateur invalide.");
    redirect(getBaseUrl() . 'utilisateurs/list.php');
}

// Récupération des informations de l'utilisateur existant
try {
    $stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE id_utilisateur = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) {
        setFlash('danger', "Utilisateur introuvable.");
        redirect(getBaseUrl() . 'utilisateurs/list.php');
    }
} catch (PDOException $e) {
    setFlash('danger', "Erreur : " . $e->getMessage());
    redirect(getBaseUrl() . 'utilisateurs/list.php');
}

$nom = $user['nom'];
$prenom = $user['prenom'];
$email = $user['email'];

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $nouveau_mdp = $_POST['mot_de_passe'] ?? '';

    // Validations
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    }
    if (empty($prenom)) {
        $errors[] = "Le prénom est obligatoire.";
    }
    if (empty($email)) {
        $errors[] = "L'adresse email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }

    if (!empty($nouveau_mdp) && strlen($nouveau_mdp) < 6) {
        $errors[] = "Le nouveau mot de passe doit comporter au moins 6 caractères.";
    }

    // Vérifier si le nouvel email n'appartient pas déjà à un autre utilisateur
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id_utilisateur FROM Utilisateur WHERE email = ? AND id_utilisateur != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                $errors[] = "Cette adresse email est déjà utilisée par un autre compte.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de base de données : " . $e->getMessage();
        }
    }

    // Mise à jour SQL
    if (empty($errors)) {
        try {
            if (!empty($nouveau_mdp)) {
                // Si l'administrateur ou l'utilisateur a saisi un nouveau mot de passe
                $hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE Utilisateur 
                    SET nom = ?, prenom = ?, email = ?, mot_de_passe = ? 
                    WHERE id_utilisateur = ?
                ");
                $stmt->execute([$nom, $prenom, $email, $hash, $id]);
            } else {
                // On conserve l'ancien mot de passe
                $stmt = $pdo->prepare("
                    UPDATE Utilisateur 
                    SET nom = ?, prenom = ?, email = ? 
                    WHERE id_utilisateur = ?
                ");
                $stmt->execute([$nom, $prenom, $email, $id]);
            }

            setFlash('success', "L'utilisateur <strong>" . escape($prenom . ' ' . $nom) . "</strong> a été mis à jour avec succès.");
            redirect(getBaseUrl() . 'utilisateurs/list.php');
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">✏️ Modifier l'Utilisateur</h1>
        <p class="page-subtitle">Modification des informations pour #<?= (int)$id ?> (<?= escape($user['prenom'] . ' ' . $user['nom']) ?>)</p>
    </div>
    <a href="<?= $baseUrl ?>utilisateurs/list.php" class="btn btn-secondary">
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
        <div class="form-row">
            <div class="form-group">
                <label for="prenom" class="form-label required">Prénom</label>
                <input type="text" id="prenom" name="prenom" class="form-control" 
                       value="<?= escape($prenom) ?>" required>
            </div>
            <div class="form-group">
                <label for="nom" class="form-label required">Nom</label>
                <input type="text" id="nom" name="nom" class="form-control" 
                       value="<?= escape($nom) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="email" class="form-label required">Adresse Email</label>
            <input type="email" id="email" name="email" class="form-control" 
                   value="<?= escape($email) ?>" required>
        </div>

        <div class="form-group">
            <label for="mot_de_passe" class="form-label">Nouveau mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" 
                   placeholder="Laisser vide pour conserver le mot de passe actuel">
            <small class="form-text">Renseignez ce champ uniquement si vous souhaitez changer le mot de passe.</small>
        </div>

        <div class="form-actions">
            <a href="<?= $baseUrl ?>utilisateurs/list.php" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
