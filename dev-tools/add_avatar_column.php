<?php
/**
 * Script rapide pour ajouter la colonne avatar_centered
 */

require_once __DIR__ . '/includes/db.php';

try {
    $db = getDBConnection();
    
    // Vérifier si la colonne existe déjà
    $columns = $db->query("DESCRIBE profile")->fetchAll(PDO::FETCH_COLUMN, 0);
    
    if (!in_array('avatar_centered', $columns)) {
        $db->exec("ALTER TABLE profile ADD COLUMN avatar_centered TINYINT(1) DEFAULT 0 AFTER avatar_path");
        echo "✅ Colonne 'avatar_centered' ajoutée avec succès !<br>";
        echo "<a href='admin/profile.php'>Retour au profil</a>";
    } else {
        echo "✅ Colonne 'avatar_centered' existe déjà !<br>";
        echo "<a href='admin/profile.php'>Retour au profil</a>";
    }
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
