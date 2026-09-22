<?php
/**
 * CRUD Utilisateurs — Ajout
 * Formulaire de création d'un utilisateur avec hachage du mot de passe (password_hash)
 */
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Ajouter un Utilisateur';
$activeNav = 'utilisateurs';
$errors = [];
$nom = '';
$prenom = '';
$email = '';

// Traitement du formulaire lors de la soumission POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

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
    if (empty($mot_de_passe)) {
        $errors[] = "Le mot de passe est obligatoire.";
    } elseif (strlen($mot_de_passe) < 6) {
        $errors[] = "Le mot de passe doit comporter au moins 6 caractères.";
    }

    // Vérification de l'unicité de l'email
    if (empty($errors) && isset($pdo)) {
        try {
            $stmt = $pdo->prepare("SELECT id_utilisateur FROM Utilisateur WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "Cette adresse email est déjà utilisée par un autre utilisateur.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de base de données : " . $e->getMessage();
        }
    }

    // Insertion sécurisée avec requête préparée
    if (empty($errors) && isset($pdo)) {
        try {
            // Hachage sécurisé du mot de passe avec l'algorithme par défaut recommandé (BCRYPT)
            $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO Utilisateur (nom, prenom, email, mot_de_passe, date_creation)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$nom, $prenom, $email, $hash]);

            setFlash('success', "L'utilisateur <strong>" . escape($prenom . ' ' . $nom) . "</strong> a été créé avec succès !");
            redirect(getBaseUrl() . 'utilisateurs/list.php');
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'insertion de l'utilisateur : " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">➕ Nouvel Utilisateur</h1>
        <p class="page-subtitle">Créer un profil utilisateur pour gérer ses comptes et transactions</p>
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

    <!-- Formulaire d'ajout avec validation JS -->
    <form action="" method="POST" class="js-validate" novalidate>
        <div class="form-row">
            <div class="form-group">
                <label for="prenom" class="form-label required">Prénom</label>
                <input type="text" id="prenom" name="prenom" class="form-control" 
                       value="<?= escape($prenom) ?>" placeholder="Ex: Thomas" required autofocus>
            </div>
            <div class="form-group">
                <label for="nom" class="form-label required">Nom</label>
                <input type="text" id="nom" name="nom" class="form-control" 
                       value="<?= escape($nom) ?>" placeholder="Ex: Dubois" required>
            </div>
        </div>

        <div class="form-group">
            <label for="email" class="form-label required">Adresse Email</label>
            <input type="email" id="email" name="email" class="form-control" 
                   value="<?= escape($email) ?>" placeholder="thomas.dubois@email.com" required>
            <small class="form-text">L'email doit être unique et servira d'identifiant de connexion.</small>
        </div>

        <div class="form-group">
            <label for="mot_de_passe" class="form-label required">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" 
                   placeholder="Au moins 6 caractères" required minlength="6">
            <small class="form-text">Le mot de passe sera automatiquement haché de manière sécurisée (password_hash BCRYPT).</small>
        </div>

        <div class="form-actions">
            <a href="<?= $baseUrl ?>utilisateurs/list.php" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer l'utilisateur</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
