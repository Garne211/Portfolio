<?php
/**
 * Migration 001: Ajouter la colonne cv_path à la table profile
 */

$db = getDBConnection();

try {
    // Vérifier si la colonne existe déjà
    $columns = $db->query("DESCRIBE profile")->fetchAll(PDO::FETCH_COLUMN, 0);
    
    if (!in_array('cv_path', $columns)) {
        $db->exec("ALTER TABLE profile ADD COLUMN cv_path VARCHAR(255) NULL AFTER avatar_path");
        echo "Colonne 'cv_path' ajoutée\n";
    } else {
        echo "Colonne 'cv_path' existe déjà\n";
    }
} catch (Exception $e) {
    throw new Exception("Erreur migration: " . $e->getMessage());
}
