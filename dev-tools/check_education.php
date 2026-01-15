<?php
/**
 * Vérifier les données de la table education
 */

require_once __DIR__ . '/../includes/db.php';

try {
    $db = getDBConnection();
    
    echo "<h2>📚 Contenu de la table education :</h2>";
    
    $parcours = $db->query("SELECT * FROM education ORDER BY display_order ASC, start_date DESC")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($parcours)) {
        echo "<p style='color: red;'>❌ Aucun parcours dans la table education</p>";
    } else {
        echo "<p style='color: green;'>✅ " . count($parcours) . " parcours trouvé(s)</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #333; color: #fff;'>";
        echo "<th>ID</th><th>École</th><th>Diplôme</th><th>Spécialité</th><th>Début</th><th>Fin</th><th>En cours</th><th>Actif</th><th>Ordre</th>";
        echo "</tr>";
        
        foreach ($parcours as $row) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['school']) . "</td>";
            echo "<td>" . htmlspecialchars($row['degree']) . "</td>";
            echo "<td>" . htmlspecialchars($row['field'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['start_date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['end_date'] ?? '-') . "</td>";
            echo "<td>" . ($row['is_current'] ? '✓ Oui' : '✗ Non') . "</td>";
            echo "<td>" . ($row['active'] ? '✓ Oui' : '✗ Non') . "</td>";
            echo "<td>" . $row['display_order'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<br><br><h3>Structure de la table :</h3>";
    $columns = $db->query("DESCRIBE education")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr style='background: #333; color: #fff;'><th>Colonne</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . $col['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>

<br><br>
<a href="admin/education.php" style="padding: 10px 20px; background: #dc2626; color: #fff; text-decoration: none; border-radius: 4px;">Retour à l'admin</a>
