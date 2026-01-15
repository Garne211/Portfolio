<?php
/**
 * Migration 002: Créer les tables experiences et education
 */

$db = getDBConnection();

try {
    // Créer la table experiences
    $db->exec("CREATE TABLE IF NOT EXISTS experiences (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        company VARCHAR(255) NOT NULL,
        description_fr TEXT,
        description_en TEXT,
        start_date DATE NOT NULL,
        end_date DATE,
        is_current TINYINT DEFAULT 0,
        type ENUM('experience', 'stage') DEFAULT 'experience',
        display_order INT DEFAULT 0,
        active TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "Table 'experiences' créée\n";
    
    // Créer la table education
    $db->exec("CREATE TABLE IF NOT EXISTS education (
        id INT PRIMARY KEY AUTO_INCREMENT,
        school VARCHAR(255) NOT NULL,
        degree VARCHAR(255) NOT NULL,
        field VARCHAR(255),
        description_fr TEXT,
        description_en TEXT,
        start_date DATE NOT NULL,
        end_date DATE,
        is_current TINYINT DEFAULT 0,
        display_order INT DEFAULT 0,
        active TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "Table 'education' créée\n";
    
} catch (Exception $e) {
    throw new Exception("Erreur migration: " . $e->getMessage());
}
