<?php
/**
 * Configuration de la Base de Données et Utilitaires
 * Application : Gestion de Finances Personnelles
 */

// Démarrage de session pour les messages flash et l'état
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Paramètres de connexion MySQL
// Vous pouvez modifier ces valeurs selon votre configuration locale (WAMP, Laragon, XAMPP, MySQL Server)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'gestion_finances');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'Solicode@2');
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Génère des exceptions en cas d'erreur SQL
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retourne des tableaux associatifs
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Utilise les vraies requêtes préparées du SGBD
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Si la base n'existe pas encore ou que la connexion échoue, on affiche un message clair
    $dbError = $e->getMessage();
}

/**
 * Calcule l'URL de base du projet dynamiquement
 * Compatible avec : php -S localhost:8000 OU Apache sous-dossier (ex: localhost/file_rouge/)
 */
function getBaseUrl(): string {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $subfolders = ['/utilisateurs', '/comptes', '/categories', '/transactions', '/config', '/includes'];
    foreach ($subfolders as $sub) {
        if (str_ends_with($scriptDir, $sub)) {
            $scriptDir = substr($scriptDir, 0, -strlen($sub));
            break;
        }
    }
    $trimmed = rtrim($scriptDir, '/');
    return ($trimmed === '' ? '' : $trimmed) . '/';
}

/**
 * Enregistre un message flash en session
 * @param string $type success | danger | warning | info
 * @param string $message
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Récupère le message flash et le supprime de la session
 * @return array|null
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Redirection HTTP avec arrêt du script
 */
function redirect(string $url): void {
    header("Location: " . $url);
    exit;
}

/**
 * Protection contre les failles XSS
 */
function escape(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Formatage d'un montant en devise monétaire (ex: 1 500,00 €)
 */
function formatMoney($amount): string {
    return number_format((float)$amount, 2, ',', ' ') . ' €';
}

/**
 * Formatage d'une date (ex: 28/01/2024)
 */
function formatDate(?string $date): string {
    if (!$date) return '-';
    $timestamp = strtotime($date);
    return $timestamp ? date('d/m/Y', $timestamp) : $date;
}
