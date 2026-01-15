<?php
require_once __DIR__ . '/includes/db.php';

try {
    $db = getDBConnection();
    
    // Récupérer la structure de toutes les tables
    $result = $db->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Tables dans la BD:</h2>";
    foreach ($tables as $table) {
        echo "📋 $table<br>";
    }
    
    // Chercher si messages existe
    if (!in_array('messages', $tables)) {
        echo "<h3 style='color: #dc2626;'>❌ Table 'messages' n'existe pas</h3>";
    } else {
        echo "<h3 style='color: #22c55e;'>✅ Table 'messages' existe</h3>";
        $cols = $db->query("DESCRIBE messages")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($cols);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
?>
