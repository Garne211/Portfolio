<?php
/**
 * Migration 006: Ajouter les colonnes de traduction anglaise pour education
 */

$db = getDBConnection();

try {
    // Vérifier si les colonnes existent déjà
    $columns = $db->query("DESCRIBE education")->fetchAll(PDO::FETCH_COLUMN, 0);
    
    if (!in_array('degree_en', $columns)) {
        $db->exec("ALTER TABLE education ADD COLUMN degree_en VARCHAR(255) NULL AFTER degree");
        echo "Colonne 'degree_en' ajoutée\n";
    } else {
        echo "Colonne 'degree_en' existe déjà\n";
    }
    
    if (!in_array('school_en', $columns)) {
        $db->exec("ALTER TABLE education ADD COLUMN school_en VARCHAR(255) NULL AFTER school");
        echo "Colonne 'school_en' ajoutée\n";
    } else {
        echo "Colonne 'school_en' existe déjà\n";
    }
    
    if (!in_array('field_en', $columns)) {
        $db->exec("ALTER TABLE education ADD COLUMN field_en VARCHAR(255) NULL AFTER field");
        echo "Colonne 'field_en' ajoutée\n";
    } else {
        echo "Colonne 'field_en' existe déjà\n";
    }
} catch (Exception $e) {
    throw new Exception("Erreur migration: " . $e->getMessage());
}
