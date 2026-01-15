<?php
/**
 * Migration: Créer la table admin_sessions pour la gestion des sessions
 */

function migrate_add_admin_sessions() {
    try {
        $db = getDBConnection();
        
        // Créer la table admin_sessions
        $db->exec("
            CREATE TABLE IF NOT EXISTS admin_sessions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                admin_id INT NOT NULL,
                refresh_token VARCHAR(64) UNIQUE NOT NULL,
                session_token VARCHAR(64) UNIQUE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NOT NULL,
                ip_address VARCHAR(45),
                user_agent VARCHAR(255),
                FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE,
                INDEX idx_refresh_token (refresh_token),
                INDEX idx_session_token (session_token),
                INDEX idx_admin_id (admin_id),
                INDEX idx_expires_at (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        return true;
    } catch (Exception $e) {
        error_log("Migration 003 error: " . $e->getMessage());
        return false;
    }
}

return migrate_add_admin_sessions();
