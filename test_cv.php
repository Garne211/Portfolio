<?php
/**
 * Test: Vérifier les fichiers uploadés
 */

require_once __DIR__ . '/includes/db.php';

echo "<h2>📁 Fichiers dans /uploads :</h2>";
$uploads = glob(__DIR__ . '/uploads/*');
foreach ($uploads as $file) {
    echo "- " . basename($file) . "<br>";
}

echo "<h2>📊 Profil en BD :</h2>";
try {
    $db = getDBConnection();
    $profile = $db->query("SELECT id, name, cv_path FROM profile LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    if ($profile) {
        echo "ID: " . $profile['id'] . "<br>";
        echo "Nom: " . $profile['name'] . "<br>";
        echo "CV Path: " . ($profile['cv_path'] ? $profile['cv_path'] : "❌ VIDE") . "<br>";
        
        if ($profile['cv_path']) {
            $full_path = __DIR__ . $profile['cv_path'];
            $exists = file_exists($full_path) ? "✅ EXISTE" : "❌ N'EXISTE PAS";
            echo "Fichier existe: $exists<br>";
            echo "Chemin complet: $full_path<br>";
        }
    } else {
        echo "❌ Pas de profil trouvé";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
?>
