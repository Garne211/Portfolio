<?php
/**
 * Migration 005: Ajouter la colonne avatar_centered à la table profile
 */

$db = getDBConnection();

try {
    // Vérifier si la colonne existe déjà
    $columns = $db->query("DESCRIBE profile")->fetchAll(PDO::FETCH_COLUMN, 0);
    
    if (!in_array('avatar_centered', $columns)) {
        $db->exec("ALTER TABLE profile ADD COLUMN avatar_centered TINYINT(1) DEFAULT 0 AFTER avatar_path");
        echo "Colonne 'avatar_centered' ajoutée\n";
    } else {
        echo "Colonne 'avatar_centered' existe déjà\n";
    }
} catch (Exception $e) {
    throw new Exception("Erreur migration: " . $e->getMessage());
}
