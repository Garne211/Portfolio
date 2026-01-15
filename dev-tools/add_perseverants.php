<?php
/**
 * Ajouter ou mettre à jour le parcours Perseverants
 */

require_once __DIR__ . '/includes/db.php';

try {
    $db = getDBConnection();
    
    // Vérifier si le parcours Perseverants existe déjà
    $stmt = $db->prepare("SELECT * FROM education WHERE school LIKE '%Perseverants%' OR school LIKE '%Persévérants%'");
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        echo "<h2>✏️ Parcours Perseverants trouvé (ID: {$existing['id']})</h2>";
        echo "<p>Mise à jour des informations...</p>";
        
        // Mettre à jour
        $update = $db->prepare("
            UPDATE education SET
                school = 'Les Persévérants',
                degree = 'BTS SIO (Services Informatiques aux Organisations)',
                field = 'Option SISR - Solutions d\'Infrastructure, Systèmes et Réseaux',
                description_fr = 'Formation en informatique avec spécialisation en infrastructure et cybersécurité',
                description_en = 'IT training with specialization in infrastructure and cybersecurity',
                start_date = '2025-09-01',
                end_date = NULL,
                is_current = 1,
                active = 1,
                display_order = 0
            WHERE id = ?
        ");
        $update->execute([$existing['id']]);
        
        echo "<p style='color: green;'>✅ Parcours mis à jour avec succès !</p>";
    } else {
        echo "<h2>➕ Parcours Perseverants non trouvé</h2>";
        echo "<p>Création du parcours...</p>";
        
        // Insérer
        $insert = $db->prepare("
            INSERT INTO education (school, degree, field, description_fr, description_en, start_date, end_date, is_current, active, display_order)
            VALUES (?, ?, ?, ?, ?, ?, NULL, 1, 1, 0)
        ");
        $insert->execute([
            'Les Persévérants',
            'BTS SIO (Services Informatiques aux Organisations)',
            'Option SISR - Solutions d\'Infrastructure, Systèmes et Réseaux',
            'Formation en informatique avec spécialisation en infrastructure et cybersécurité',
            'IT training with specialization in infrastructure and cybersecurity',
            '2025-09-01'
        ]);
        
        echo "<p style='color: green;'>✅ Parcours créé avec succès !</p>";
    }
    
    // Afficher le résultat
    echo "<br><h3>Parcours actuel :</h3>";
    $parcours = $db->query("SELECT * FROM education WHERE school LIKE '%Perseverants%' OR school LIKE '%Persévérants%'")->fetch(PDO::FETCH_ASSOC);
    
    if ($parcours) {
        echo "<table border='1' style='border-collapse: collapse; margin-top: 20px;'>";
        echo "<tr><th>Champ</th><th>Valeur</th></tr>";
        foreach ($parcours as $key => $value) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($key) . "</strong></td>";
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>

<br><br>
<a href="admin/education.php" style="padding: 10px 20px; background: #dc2626; color: #fff; text-decoration: none; border-radius: 4px;">Voir dans l'admin</a>
