<?php
/**
 * Script de correction rapide pour les champs de traduction education
 */

require_once __DIR__ . '/includes/db.php';

try {
    $db = getDBConnection();
    
    echo "<h2>🔧 Correction des colonnes education</h2>";
    
    // Vérifier les colonnes
    $columns = $db->query("DESCRIBE education")->fetchAll(PDO::FETCH_COLUMN, 0);
    
    $added = [];
    
    // Ajouter school_en si manquant
    if (!in_array('school_en', $columns)) {
        $db->exec("ALTER TABLE education ADD COLUMN school_en VARCHAR(255) NULL AFTER school");
        $added[] = 'school_en';
    }
    
    // Ajouter degree_en si manquant
    if (!in_array('degree_en', $columns)) {
        $db->exec("ALTER TABLE education ADD COLUMN degree_en VARCHAR(255) NULL AFTER degree");
        $added[] = 'degree_en';
    }
    
    // Ajouter field_en si manquant
    if (!in_array('field_en', $columns)) {
        $db->exec("ALTER TABLE education ADD COLUMN field_en VARCHAR(255) NULL AFTER field");
        $added[] = 'field_en';
    }
    
    if (!empty($added)) {
        echo "<p style='color: green;'>✅ Colonnes ajoutées : " . implode(', ', $added) . "</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Toutes les colonnes existent déjà</p>";
    }
    
    // Afficher la structure finale
    echo "<br><h3>Structure finale de la table :</h3>";
    $finalColumns = $db->query("DESCRIBE education")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr style='background: #333; color: #fff;'><th>Colonne</th><th>Type</th></tr>";
    foreach ($finalColumns as $col) {
        $highlight = in_array($col['Field'], ['school', 'school_en', 'degree', 'degree_en', 'field', 'field_en']) 
            ? 'background: #d4edda;' : '';
        echo "<tr style='$highlight'>";
        echo "<td><strong>" . $col['Field'] . "</strong></td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><p style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
    echo "✅ <strong>Tout est prêt !</strong> Vous pouvez maintenant utiliser les champs de traduction dans l'admin.";
    echo "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>

<br>
<a href="admin/education.php" style="padding: 10px 20px; background: #dc2626; color: #fff; text-decoration: none; border-radius: 4px; display: inline-block;">Aller dans l'admin Education</a>
