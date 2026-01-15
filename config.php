<?php
/**
 * Configuration centralisée du Portfolio
 * Définit les constantes de chemin uniquement
 * Les includes doivent être faits dans chaque fichier
 */

// Définir le répertoire racine
define('ROOT_DIR', __DIR__);
define('INCLUDES_DIR', ROOT_DIR . '/includes');
define('ADMIN_DIR', ROOT_DIR . '/admin');
define('API_DIR', ROOT_DIR . '/api');
define('PUBLIC_DIR', ROOT_DIR . '/public');
define('UPLOADS_DIR', ROOT_DIR . '/uploads');
define('DATABASE_DIR', ROOT_DIR . '/database');

// Fuseau horaire
date_default_timezone_set('America/Guadeloupe');

// Affichage des erreurs (à désactiver en production)
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
}

// Headers de sécurité
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
}

