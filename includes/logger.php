<?php
/**
 * Logging centralisé pour audit et sécurité
 */

class Logger {
    const LOG_DIR = __DIR__ . '/../uploads/logs/';
    const LOG_LEVEL_ERROR = 'ERROR';
    const LOG_LEVEL_WARNING = 'WARNING';
    const LOG_LEVEL_INFO = 'INFO';
    const LOG_LEVEL_DEBUG = 'DEBUG';
    const LOG_LEVEL_SECURITY = 'SECURITY';
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        if (!is_dir(self::LOG_DIR)) {
            mkdir(self::LOG_DIR, 0755, true);
        }
    }
    
    /**
     * Enregistrer une entrée de log
     */
    private function log($level, $message, $context = []) {
        // Construire le message de log
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user = $_SESSION['admin_username'] ?? 'guest';
        
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logMessage = "[$timestamp] [$level] [$ip] [$user] $message$contextStr\n";
        
        // Déterminer le fichier de log
        $logFile = self::LOG_DIR . strtolower($level) . '_' . date('Y-m-d') . '.log';
        
        // Écrire dans le fichier de log
        error_log($logMessage, 3, $logFile);
    }
    
    /**
     * Log ERROR
     */
    public static function error($message, $context = []) {
        self::getInstance()->log(self::LOG_LEVEL_ERROR, $message, $context);
    }
    
    /**
     * Log WARNING
     */
    public static function warning($message, $context = []) {
        self::getInstance()->log(self::LOG_LEVEL_WARNING, $message, $context);
    }
    
    /**
     * Log INFO
     */
    public static function info($message, $context = []) {
        self::getInstance()->log(self::LOG_LEVEL_INFO, $message, $context);
    }
    
    /**
     * Log DEBUG (seulement en développement)
     */
    public static function debug($message, $context = []) {
        if (getenv('APP_ENV') === 'development') {
            self::getInstance()->log(self::LOG_LEVEL_DEBUG, $message, $context);
        }
    }
    
    /**
     * Log SECURITY - Pour les événements de sécurité importants
     */
    public static function security($message, $context = []) {
        self::getInstance()->log(self::LOG_LEVEL_SECURITY, $message, $context);
    }
    
    /**
     * Log une tentative de connexion
     */
    public static function loginAttempt($username, $success, $ip = null) {
        $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $action = $success ? 'LOGIN_SUCCESS' : 'LOGIN_FAILED';
        self::security("User login attempt: $username", [
            'username' => $username,
            'success' => $success,
            'ip' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
    
    /**
     * Log une action de modification
     */
    public static function action($action, $table, $id, $changes = []) {
        self::info("$action on table $table (ID: $id)", $changes);
    }
    
    /**
     * Log un accès refusé
     */
    public static function accessDenied($action, $reason = '') {
        self::security("Access denied: $action", [
            'reason' => $reason,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user' => $_SESSION['admin_username'] ?? 'guest'
        ]);
    }
}

// Alias pour faciliter l'utilisation
function log_error($message, $context = []) {
    Logger::error($message, $context);
}

function log_warning($message, $context = []) {
    Logger::warning($message, $context);
}

function log_info($message, $context = []) {
    Logger::info($message, $context);
}

function log_security($message, $context = []) {
    Logger::security($message, $context);
}
