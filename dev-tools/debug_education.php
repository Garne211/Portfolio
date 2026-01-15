<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/includes/db.php';

echo "<h1>🔍 Debug Education</h1>";

try {
    $db = getDBConnection();
    
    // 1. Vérifier les colonnes
    echo "<h2>1. Colonnes de la table education :</h2>";
    $columns = $db->query("DESCRIBE education")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr style='background: #333; color: #fff;'><th>Colonne</th><th>Type</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>" . $col['Field'] . "</td><td>" . $col['Type'] . "</td></tr>";
    }
    echo "</table>";
    
    // 2. Vérifier les données
    echo "<h2>2. Données dans la table :</h2>";
    $education = $db->query("SELECT * FROM education ORDER BY start_date DESC")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($education)) {
        echo "<p style='color: red;'>❌ Aucune donnée dans la table education</p>";
    } else {
        echo "<p style='color: green;'>✅ " . count($education) . " parcours trouvé(s)</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #333; color: #fff;'>";
        echo "<th>ID</th><th>École (FR)</th><th>École (EN)</th><th>Diplôme (FR)</th><th>Diplôme (EN)</th><th>Début</th><th>Actif</th>";
        echo "</tr>";
        
        foreach ($education as $edu) {
            echo "<tr>";
            echo "<td>" . $edu['id'] . "</td>";
            echo "<td>" . htmlspecialchars($edu['school'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($edu['school_en'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($edu['degree'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($edu['degree_en'] ?? 'N/A') . "</td>";
            echo "<td>" . $edu['start_date'] . "</td>";
            echo "<td>" . ($edu['active'] ? '✓' : '✗') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 3. Tester l'API
    echo "<h2>3. Test de l'API :</h2>";
    echo "<p><a href='/Portfolio/api/education.php?lang=fr' target='_blank'>📄 Ouvrir API (FR)</a></p>";
    echo "<p><a href='/Portfolio/api/education.php?lang=en' target='_blank'>📄 Ouvrir API (EN)</a></p>";
    
    echo "<h3>Réponse API (FR) :</h3>";
    $apiUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/Portfolio/api/education.php?lang=fr';
    $response = @file_get_contents($apiUrl);
    if ($response) {
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto;'>";
        echo htmlspecialchars($response);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>❌ Impossible de charger l'API</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>

<br><br>
<div style="background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;">
    <strong>💡 Instructions :</strong>
    <ol>
        <li>Vérifiez que les colonnes <code>school_en</code>, <code>degree_en</code>, <code>field_en</code> existent</li>
        <li>Vérifiez que les données sont bien remplies dans la table</li>
        <li>Vérifiez que l'API retourne les bonnes données</li>
        <li>Si tout est OK ici mais ça ne s'affiche pas, c'est un problème JavaScript → Ouvrez la console (F12)</li>
    </ol>
</div>

<a href="admin/education.php" style="padding: 10px 20px; background: #dc2626; color: #fff; text-decoration: none; border-radius: 4px; display: inline-block;">Aller dans l'admin</a>
<a href="public/index.php#education" style="padding: 10px 20px; background: #3b82f6; color: #fff; text-decoration: none; border-radius: 4px; display: inline-block; margin-left: 10px;">Voir page publique</a>
