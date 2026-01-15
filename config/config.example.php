<?php
/**
 * Configuration exemple pour Portfolio
 * 
 * IMPORTANT: Copiez ce fichier en config.php et modifiez les valeurs selon votre environnement
 * 
 * Pour l'environnement de production:
 * 1. Copiez: cp config.example.php config.php
 * 2. Modifiez les valeurs ci-dessous
 * 3. Sécurisez: chmod 600 config.php
 * 4. Assurez-vous que config.php est dans .gitignore
 */

// ============================================================================
// CHEMINS DE L'APPLICATION
// ============================================================================

// Définir le répertoire racine
define('ROOT_DIR', dirname(__DIR__));
define('INCLUDES_DIR', ROOT_DIR . '/includes');
define('ADMIN_DIR', ROOT_DIR . '/admin');
define('API_DIR', ROOT_DIR . '/api');
define('PUBLIC_DIR', ROOT_DIR . '/public');
define('UPLOADS_DIR', ROOT_DIR . '/uploads');
define('DATABASE_DIR', ROOT_DIR . '/database');

// ============================================================================
// CONFIGURATION DE L'ENVIRONNEMENT
// ============================================================================

// Environnement: 'development' ou 'production'
define('APP_ENV', 'production');

// Fuseau horaire (voir: https://www.php.net/manual/fr/timezones.php)
date_default_timezone_set('Europe/Paris');

// ============================================================================
// GESTION DES ERREURS
// ============================================================================

if (APP_ENV === 'development') {
    // En développement: afficher toutes les erreurs
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    // En production: enregistrer les erreurs sans les afficher
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_DIR . '/logs/php-error.log');
}

// ============================================================================
// HEADERS DE SÉCURITÉ
// ============================================================================

if (!headers_sent()) {
    // Empêcher le navigateur de deviner le type MIME
    header('X-Content-Type-Options: nosniff');
    
    // Protéger contre le clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // Protection XSS
    header('X-XSS-Protection: 1; mode=block');
    
    // Politique de référent
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Politique de permissions
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    
    // En production, ajouter Content Security Policy
    if (APP_ENV === 'production') {
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:;");
    }
}

// ============================================================================
// CONFIGURATION PHP
// ============================================================================

// Taille maximale des fichiers uploadés (en Mo)
ini_set('upload_max_filesize', '20M');
ini_set('post_max_size', '20M');

// Durée maximale d'exécution (en secondes)
ini_set('max_execution_time', '300');

// Mémoire maximale
ini_set('memory_limit', '256M');

// ============================================================================
// CONFIGURATION DE SESSION
// ============================================================================

// Nom du cookie de session (personnalisez-le)
ini_set('session.name', 'PORTFOLIO_SESSION');

// Durée de vie du cookie de session (0 = jusqu'à fermeture du navigateur)
ini_set('session.cookie_lifetime', 0);

// Cookie accessible uniquement via HTTP (pas via JavaScript)
ini_set('session.cookie_httponly', 1);

// Cookie envoyé uniquement via HTTPS en production
if (APP_ENV === 'production') {
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_samesite', 'Strict');
}

// Chemin du cookie
ini_set('session.cookie_path', '/');

// ============================================================================
// CONSTANTES DE L'APPLICATION
// ============================================================================

// URL de base du site (sans slash final)
define('BASE_URL', APP_ENV === 'production' 
    ? 'https://votre-domaine.com' 
    : 'http://localhost/Portfolio');

// Nom de l'application
define('APP_NAME', 'Portfolio');

// Version de l'application
define('APP_VERSION', '1.0.0');

// Email de contact
define('CONTACT_EMAIL', 'contact@votre-domaine.com');

// ============================================================================
// CONFIGURATION DES UPLOADS
// ============================================================================

// Extensions autorisées pour les avatars
define('ALLOWED_AVATAR_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Taille maximale des avatars (en octets) - 5 Mo
define('MAX_AVATAR_SIZE', 5 * 1024 * 1024);

// Extensions autorisées pour les CV
define('ALLOWED_CV_EXTENSIONS', ['pdf', 'doc', 'docx']);

// Taille maximale des CV (en octets) - 10 Mo
define('MAX_CV_SIZE', 10 * 1024 * 1024);

// Extensions autorisées pour les images de projets
define('ALLOWED_PROJECT_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Taille maximale des images de projets (en octets) - 5 Mo
define('MAX_PROJECT_SIZE', 5 * 1024 * 1024);

// ============================================================================
// CONFIGURATION DE LA PAGINATION
// ============================================================================

// Nombre d'éléments par page dans l'administration
define('ADMIN_ITEMS_PER_PAGE', 20);

// ============================================================================
// CONFIGURATION DES LOGS
// ============================================================================

// Activer les logs personnalisés
define('ENABLE_CUSTOM_LOGS', true);

// Niveau de log: 'debug', 'info', 'warning', 'error'
define('LOG_LEVEL', APP_ENV === 'production' ? 'error' : 'debug');

// ============================================================================
// CONFIGURATION CSRF
// ============================================================================

// Durée de validité des tokens CSRF (en secondes) - 2 heures
define('CSRF_TOKEN_EXPIRE', 7200);

// ============================================================================
// CONFIGURATION DE LA SÉCURITÉ
// ============================================================================

// Nombre maximum de tentatives de connexion avant blocage
define('MAX_LOGIN_ATTEMPTS', 5);

// Durée du blocage après échec de connexion (en secondes) - 15 minutes
define('LOGIN_BLOCK_DURATION', 900);

// Durée de session inactivité (en secondes) - 30 minutes
define('SESSION_TIMEOUT', 1800);

// Durée avant warning de fin de session (en secondes) - 5 minutes
define('SESSION_WARNING_TIME', 300);

// ============================================================================
// CONFIGURATION DE LA BASE DE DONNÉES
// ============================================================================

// IMPORTANT: Ne mettez PAS les identifiants de base de données ici!
// Les identifiants doivent être dans includes/db.php
// Ce fichier contient uniquement la configuration générale

// Préfixe des tables (si nécessaire)
define('DB_TABLE_PREFIX', '');

// Charset de la base de données
define('DB_CHARSET', 'utf8mb4');

// Collation de la base de données
define('DB_COLLATION', 'utf8mb4_unicode_ci');

// ============================================================================
// CONFIGURATION DU CACHE
// ============================================================================

// Activer le cache (en production)
define('ENABLE_CACHE', APP_ENV === 'production');

// Durée du cache (en secondes) - 1 heure
define('CACHE_DURATION', 3600);

// ============================================================================
// CONFIGURATION DES EMAILS
// ============================================================================

// Méthode d'envoi: 'mail' (PHP mail) ou 'smtp'
define('EMAIL_METHOD', 'mail');

// Si SMTP, configurez ces paramètres:
define('SMTP_HOST', 'smtp.exemple.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'votre-email@exemple.com');
define('SMTP_PASSWORD', 'votre-mot-de-passe');
define('SMTP_ENCRYPTION', 'tls'); // 'tls' ou 'ssl'

// Email expéditeur par défaut
define('FROM_EMAIL', 'noreply@votre-domaine.com');
define('FROM_NAME', 'Portfolio');

// ============================================================================
// CONFIGURATION DEBUG
// ============================================================================

// Afficher les requêtes SQL (uniquement en développement)
define('DEBUG_SQL', APP_ENV === 'development');

// Afficher les informations de performance
define('DEBUG_PERFORMANCE', APP_ENV === 'development');

// ============================================================================
// NOTES IMPORTANTES
// ============================================================================

/*
 * APRÈS AVOIR COPIÉ CE FICHIER EN config.php:
 * 
 * 1. Modifiez toutes les valeurs selon votre environnement
 * 2. Assurez-vous que config.php a les bonnes permissions (600)
 * 3. Vérifiez que config.php est dans .gitignore
 * 4. Ne commitez JAMAIS config.php dans Git
 * 5. Gardez config.example.php à jour avec les nouvelles configurations
 * 
 * COMMANDES UTILES:
 * 
 * # Copier et créer le fichier de configuration
 * cp config.example.php config.php
 * 
 * # Sécuriser le fichier (Linux/Mac)
 * chmod 600 config.php
 * chown www-data:www-data config.php
 * 
 * # Vérifier que le fichier est ignoré par Git
 * git check-ignore config.php
 */
