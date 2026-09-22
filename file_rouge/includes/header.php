<?php
/**
 * Header commun de l'application
 */
require_once __DIR__ . '/../config/db.php';

$baseUrl = getBaseUrl();
$pageTitle = $pageTitle ?? 'Gestion de Finances Personnelles';
$activeNav = $activeNav ?? '';
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($pageTitle) ?> — Gestion de Finances</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>assets/css/style.css">
</head>
<body>

    <!-- Barre de Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?= $baseUrl ?>index.php" class="brand">
                <span class="brand-icon">💳</span>
                <span>FinancesManager</span>
            </a>
            <ul class="nav-links">
                <li>
                    <a href="<?= $baseUrl ?>index.php" class="nav-link <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
                        📊 Tableau de bord
                    </a>
                </li>
                <li>
                    <a href="<?= $baseUrl ?>utilisateurs/list.php" class="nav-link <?= $activeNav === 'utilisateurs' ? 'active' : '' ?>">
                        👥 Utilisateurs
                    </a>
                </li>
                <li>
                    <a href="<?= $baseUrl ?>comptes/list.php" class="nav-link <?= $activeNav === 'comptes' ? 'active' : '' ?>">
                        🏦 Comptes
                    </a>
                </li>
                <li>
                    <a href="<?= $baseUrl ?>categories/list.php" class="nav-link <?= $activeNav === 'categories' ? 'active' : '' ?>">
                        🏷️ Catégories
                    </a>
                </li>
                <li>
                    <a href="<?= $baseUrl ?>transactions/list.php" class="nav-link <?= $activeNav === 'transactions' ? 'active' : '' ?>">
                        💸 Transactions
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Conteneur Principal -->
    <main class="main-content">

        <!-- Erreur éventuelle de connexion Base de données -->
        <?php if (isset($dbError)): ?>
            <div class="alert alert-danger">
                <div>
                    <strong>⚠️ Erreur de connexion à MySQL :</strong> <?= escape($dbError) ?><br>
                    <small>Vérifiez que le serveur MySQL est démarré et que le script <code>schema.sql</code> a été exécuté. (Paramètres configurables dans <code>config/db.php</code>)</small>
                </div>
            </div>
        <?php endif; ?>

        <!-- Message Flash (Succès, Erreur, Avertissement) -->
        <?php if ($flash): ?>
            <div class="alert alert-<?= escape($flash['type']) ?>">
                <div>
                    <?= escape($flash['message']) ?>
                </div>
                <button type="button" class="alert-close" aria-label="Fermer">&times;</button>
            </div>
        <?php endif; ?>
