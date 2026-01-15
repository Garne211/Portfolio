<?php
require_once __DIR__ . '/includes/db.php';

try {
    $db = getDBConnection();
    
    echo "<h2>Structure de la table education :</h2>";
    $columns = $db->query("DESCRIBE education")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr style='background: #333; color: #fff;'><th>Colonne</th><th>Type</th></tr>";
    foreach ($columns as $col) {
        $highlight = in_array($col['Field'], ['school_en', 'degree_en', 'field_en']) ? 'background: yellow; color: black;' : '';
        echo "<tr style='$highlight'>";
        echo "<td><strong>" . $col['Field'] . "</strong></td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><h3>Les colonnes de traduction anglaise existent-elles ?</h3>";
    $hasSchoolEn = false;
    $hasDegreeEn = false;
    $hasFieldEn = false;
    
    foreach ($columns as $col) {
        if ($col['Field'] === 'school_en') $hasSchoolEn = true;
        if ($col['Field'] === 'degree_en') $hasDegreeEn = true;
        if ($col['Field'] === 'field_en') $hasFieldEn = true;
    }
    
    echo "<p>school_en : " . ($hasSchoolEn ? "✅ OUI" : "❌ NON - <a href='migrate_education.php'>Exécuter la migration</a>") . "</p>";
    echo "<p>degree_en : " . ($hasDegreeEn ? "✅ OUI" : "❌ NON - <a href='migrate_education.php'>Exécuter la migration</a>") . "</p>";
    echo "<p>field_en : " . ($hasFieldEn ? "✅ OUI" : "❌ NON - <a href='migrate_education.php'>Exécuter la migration</a>") . "</p>";
    
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<br><br>
<a href="admin/education.php" style="padding: 10px 20px; background: #dc2626; color: #fff; text-decoration: none; border-radius: 4px;">Retour à l'admin</a>
