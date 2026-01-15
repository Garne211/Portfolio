<?php
/**
 * Migration: Ajouter colonne role à admin_users
 */

require_once __DIR__ . '/../../includes/db.php';

function migrate_add_role_to_admin_users() {
    try {
        $db = getDBConnection();
        
        // Vérifier si la colonne existe déjà
        $checkStmt = $db->prepare("SHOW COLUMNS FROM admin_users LIKE 'role'");
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() === 0) {
            // Ajouter la colonne role
            $db->exec("
                ALTER TABLE admin_users 
                ADD COLUMN role VARCHAR(20) DEFAULT 'admin' NOT NULL 
                AFTER password
            ");
            
            return true;
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Migration 004 error: " . $e->getMessage());
        return false;
    }
}

return migrate_add_role_to_admin_users();
